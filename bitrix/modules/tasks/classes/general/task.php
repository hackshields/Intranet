<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 * 
 * @global $USER_FIELD_MANAGER CUserTypeManager
 */
global $USER_FIELD_MANAGER;

class CTasks
{
	//Task statuses: 1 - New, 2 - Pending, 3 - In Progress, 4 - Supposedly completed, 5 - Completed, 6 - Deferred, 7 - Declined
	const METASTATE_VIRGIN_NEW       = -2;
	const METASTATE_EXPIRED          = -1;
	const STATE_NEW                  =  1;
	const STATE_PENDING              =  2;	// Pending === Accepted
	const STATE_IN_PROGRESS          =  3;
	const STATE_SUPPOSEDLY_COMPLETED =  4;
	const STATE_COMPLETED            =  5;
	const STATE_DEFERRED             =  6;
	const STATE_DECLINED             =  7;

	const PRIORITY_LOW     = 0;
	const PRIORITY_AVERAGE = 1;
	const PRIORITY_HIGH    = 2;

	private $_errors = array();


	function GetErrors()
	{
		return $this->_errors;
	}


	function CheckFields(&$arFields, $ID = false)
	{
		global $USER, $APPLICATION;

		if ((is_set($arFields, "TITLE") || $ID === false) && strlen($arFields["TITLE"]) <= 0)
		{
			$this->_errors[] = array("text" => GetMessage("TASKS_BAD_TITLE"), "id" => "ERROR_BAD_TASKS_TITLE");
		}

		// If plan dates setted
		if (isset($arFields['START_DATE_PLAN'])
			&& isset($arFields['END_DATE_PLAN'])
			&& ($arFields['START_DATE_PLAN'] != '')
			&& ($arFields['END_DATE_PLAN'] != '')
		)
		{
			$startDate = MakeTimeStamp($arFields['START_DATE_PLAN']);
			$endDate   = MakeTimeStamp($arFields['END_DATE_PLAN']);

			// and they are really setted
			if (($startDate > 0)
				&& ($endDate > 0)
			)
			{
				// and end date is before start date => then emit error
				if ($endDate < $startDate)
				{
					$this->_errors[] = array(
						'text' => GetMessage('TASKS_BAD_PLAN_DATES'),
						'id' => 'ERROR_BAD_TASKS_PLAN_DATES');
				}
			}
		}

		if ($ID === false && !is_set($arFields, "RESPONSIBLE_ID"))
		{
			$this->_errors[] = array("text" => GetMessage("TASKS_BAD_RESPONSIBLE_ID"), "id" => "ERROR_TASKS_BAD_RESPONSIBLE_ID");
		}

		if (is_set($arFields, "RESPONSIBLE_ID"))
		{
			$r = CUser::GetByID($arFields["RESPONSIBLE_ID"]);
			if ($arUser = $r->Fetch())
			{
				if ($ID)
				{
					$rsTask = CTasks::GetList(array(), array("ID" => $ID), array("RESPONSIBLE_ID"));
					if ($arTask = $rsTask->Fetch())
					{
						$currentResponsible = $arTask["RESPONSIBLE_ID"];
					}
				}

				if (!$ID || (isset($currentResponsible) && $currentResponsible != $arFields["RESPONSIBLE_ID"]))
				{
					$createdBy = $arFields["CREATED_BY"];

					$arSubDeps = CTasks::GetSubordinateDeps($createdBy);

					if ( ! is_array($arUser["UF_DEPARTMENT"]) )
						$bSubordinate = (sizeof(array_intersect($arSubDeps, array($arUser["UF_DEPARTMENT"]))) > 0);
					else
						$bSubordinate = (sizeof(array_intersect($arSubDeps, $arUser["UF_DEPARTMENT"])) > 0);

					if (!$arFields["STATUS"])
					{
						if ($arFields["RESPONSIBLE_ID"] == $createdBy || $bSubordinate)
						{
							$arFields["STATUS"] = "2";
						}
						else
						{
							$arFields["STATUS"] = "1";
						}
					}
					if (!$bSubordinate)
					{
						$arFields["ADD_IN_REPORT"] = "N";
					}

					$arFields["DECLINE_REASON"] = false;
				}
			}
			else
			{
				$this->_errors[] = array("text" => GetMessage("TASKS_BAD_RESPONSIBLE_ID_EX"), "id" => "ERROR_TASKS_BAD_RESPONSIBLE_ID_EX");
			}
		}

		if (is_set($arFields, "PARENT_ID") && intval($arFields["PARENT_ID"]) > 0)
		{
			$r = CTasks::GetByID($arFields["PARENT_ID"]);
			if (!$r->Fetch())
			{
				$this->_errors[] = array("text" => GetMessage("TASKS_BAD_PARENT_ID"), "id" => "ERROR_TASKS_BAD_PARENT_ID");
			}
		}

		if ($ID !== false && $ID == $arFields["PARENT_ID"])
		{
			$this->_errors[] = array("text" => GetMessage("TASKS_PARENT_SELF"), "id" => "ERROR_TASKS_PARENT_SELF");
		}

		if ($ID !== false && is_array($arFields["DEPENDS_ON"]) && in_array($ID, $arFields["DEPENDS_ON"]))
		{
			$this->_errors[] = array("text" => GetMessage("TASKS_DEPENDS_ON_SELF"), "id" => "ERROR_TASKS_DEPENDS_ON_SELF");
		}

		if (!empty($this->_errors))
		{
			$e = new CAdminException($this->_errors);
			$APPLICATION->ThrowException($e);
			return false;
		}

		//Defaults
		if (is_set($arFields, 'PRIORITY'))
		{
			if (
				! in_array(
					(string) $arFields['PRIORITY'],
					array(
						(string) self::PRIORITY_LOW,
						(string) self::PRIORITY_AVERAGE,
						(string) self::PRIORITY_HIGH
					),
					true	// strict check
				)
			)
			{
				$arFields['PRIORITY'] = self::PRIORITY_AVERAGE;
			}

			$arFields['PRIORITY'] = (int) $arFields['PRIORITY'];
		}

		return true;
	}


	/**
	 * This method is deprecated. Use CTaskItem::add() instead.
	 * @deprecated
	 */
	public function Add($arFields, $arParams = array())
	{
		global $DB, $USER, $USER_FIELD_MANAGER, $CACHE_MANAGER, $APPLICATION;

		if (isset($arFields['META::EVENT_GUID']))
		{
			$eventGUID = $arFields['META::EVENT_GUID'];
			unset($arFields['META::EVENT_GUID']);
		}
		else
			$eventGUID = sha1(uniqid('AUTOGUID', true));

		if ( ! array_key_exists('GUID', $arFields) )
			$arFields['GUID'] = CTasksTools::genUuid();

		if ( ! isset($arFields['SITE_ID']) )
			$arFields['SITE_ID'] = SITE_ID;

		$bWasFatalError = false;
		$spawnedByAgent = false;

		$userId = null;

		$bCheckRightsOnFiles = false;	// for backward compatibility

		if (is_array($arParams))
		{
			if (isset($arParams['SPAWNED_BY_AGENT'])
				&& (
					($arParams['SPAWNED_BY_AGENT'] === 'Y')
					|| ($arParams['SPAWNED_BY_AGENT'] === true)
					)
				)
			{
				$spawnedByAgent = true;
			}

			if (isset($arParams['USER_ID']) && ($arParams['USER_ID'] > 0))
				$userId = (int) $arParams['USER_ID'];

			if (isset($arParams['CHECK_RIGHTS_ON_FILES']))
			{
				if (
					($arParams['CHECK_RIGHTS_ON_FILES'] === 'Y')
					|| ($arParams['CHECK_RIGHTS_ON_FILES'] === true)
				)
				{
					$bCheckRightsOnFiles = true;
				}
				else
					$bCheckRightsOnFiles = false;
			}
		}

		if ($userId === null)
			$userId = is_object($USER) ? intval($USER->GetID()) : 1;

		if (
			( ! isset($arFields['CREATED_BY']) )
			|| ( ! $arFields['CREATED_BY'] )
		)
		{
			$arFields['CREATED_BY'] = $userId;
		}

		if ($this->CheckFields($arFields))
		{
			if ($USER_FIELD_MANAGER->CheckFields("TASKS_TASK", 0, $arFields))
			{
				if (!isset($arFields["CHANGED_BY"]))
				{
					$arFields["STATUS_CHANGED_BY"] = $arFields["CHANGED_BY"] = $arFields["CREATED_BY"];
					$arFields["STATUS_CHANGED_DATE"] = $arFields["CHANGED_DATE"] = $arFields["CREATED_DATE"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), time()+CTimeZone::GetOffset());
				}

				$arFields["OUTLOOK_VERSION"] = 1;

				foreach(GetModuleEvents('tasks', 'OnBeforeTaskAdd', true) as $arEvent)
				{
					if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
					{
						if (sizeof($this->_errors) == 0) {
							$this->_errors[] = array("text" => GetMessage("TASKS_UNKNOWN_ADD_ERROR"), "id" => "ERROR_UNKNOWN_ADD_TASK_ERROR");
						}
						return false;
					}
				}

				$ID = $DB->Add("b_tasks", $arFields, array("DESCRIPTION"), "tasks");

				$arFields["ACCOMPLICES"] = (array)$arFields["ACCOMPLICES"];
				$arFields["AUDITORS"] = (array)$arFields["AUDITORS"];

				if ($ID)
				{
					$rsTask = CTasks::GetByID($ID, false);
					if ($arTask = $rsTask->Fetch())
					{
						CTasks::AddAccomplices($ID, $arFields["ACCOMPLICES"]);
						CTasks::AddAuditors($ID, $arFields["AUDITORS"]);

						CTasks::AddFiles(
							$ID,
							$arFields["FILES"],
							array(
								'USER_ID'               => $userId,
								'CHECK_RIGHTS_ON_FILES' => $bCheckRightsOnFiles
							)
						);

						CTasks::AddTags($ID, $arTask["CREATED_BY"], $arFields["TAGS"]);

						CTasks::AddPrevious($ID, $arFields["DEPENDS_ON"]);

						$USER_FIELD_MANAGER->Update("TASKS_TASK", $ID, $arFields);

						$arFields["ID"] = $ID;

						CTaskNotifications::SendAddMessage(
							$arFields, 
							array('SPAWNED_BY_AGENT' => $spawnedByAgent)
						);

						CTaskSync::AddItem($arFields); // MS Exchange

						// changes log
						$arLogFields = array(
							"TASK_ID" => $ID,
							"USER_ID" => $arFields["CREATED_BY"],
							"CREATED_DATE" => $arFields["CREATED_DATE"],
							"FIELD" => "NEW"
						);
						$log = new CTaskLog();
						$log->Add($arLogFields);


						foreach(GetModuleEvents('tasks', 'OnTaskAdd', true) as $arEvent)
							ExecuteModuleEventEx($arEvent, array($ID, &$arFields));

						CTasks::Index($arTask, $arFields["TAGS"]); // search index

						// clear cache
						if ($arFields["GROUP_ID"])
						{
							$CACHE_MANAGER->ClearByTag("tasks_group_".$arFields["GROUP_ID"]);
						}
						$arParticipants = array_unique(array_merge(array($arFields["CREATED_BY"], $arFields["RESPONSIBLE_ID"]), $arFields["ACCOMPLICES"], $arFields["AUDITORS"]));
						foreach($arParticipants as $userId)
						{
							$CACHE_MANAGER->ClearByTag("tasks_user_".$userId);
						}

						// Emit pull event
						try
						{
							$arPullRecipients = array();

							foreach($arParticipants as $userId)
								$arPullRecipients[] = (int) $userId;

							$taskGroupId = 0;	// no group

							if (isset($arFields['GROUP_ID']) && ($arFields['GROUP_ID'] > 0))
								$taskGroupId = (int) $arFields['GROUP_ID'];

							$arPullData = array(
								'TASK_ID' => (int) $ID,
								'AFTER' => array(
									'GROUP_ID' => $taskGroupId
								),
								'TS' => time(),
								'event_GUID' => $eventGUID
							);

							self::EmitPullWithTagPrefix(
								$arPullRecipients, 
								'TASKS_GENERAL_', 
								'task_add', 
								$arPullData
							);

							self::EmitPullWithTag(
								$arPullRecipients, 
								'TASKS_TASK_' . (int) $ID, 
								'task_add', 
								$arPullData
							);
						}
						catch (Exception $e)
						{
							$bWasFatalError = true;
							$this->_errors[] = 'at line ' . $e->GetLine() 
								. ', ' . $e->GetMessage();
						}
					}
				}

				if ($bWasFatalError)
					soundex('push&pull: bWasFatalError === true');

				return $ID;
			}
			else
			{
				$e = $APPLICATION->GetException();
				foreach($e->messages as $msg)
				{
					$this->_errors[] = $msg;
				}
			}
		}

		if (sizeof($this->_errors) == 0) {
			$this->_errors[] = array("text" => GetMessage("TASKS_UNKNOWN_ADD_ERROR"), "id" => "ERROR_UNKNOWN_ADD_TASK_ERROR");
		}

		return false;
	}


	/**
	 * This method is deprecated. Use CTaskItem::update() instead.
	 * @deprecated
	 */
	public function Update($ID, $arFields, $arParams = array())
	{
		global $DB, $USER, $USER_FIELD_MANAGER, $CACHE_MANAGER, $APPLICATION;

		if (isset($arFields['META::EVENT_GUID']))
		{
			$eventGUID = $arFields['META::EVENT_GUID'];
			unset($arFields['META::EVENT_GUID']);
		}
		else
			$eventGUID = sha1(uniqid('AUTOGUID', true));

		$bWasFatalError = false;

		$ID = intval($ID);
		if ($ID < 1)
			return false;

		$userID = null;

		$bCheckRightsOnFiles = false;	// for backward compatibility

		if (is_array($arParams))
		{
			if (isset($arParams['USER_ID']) && ($arParams['USER_ID'] > 0))
				$userID = (int) $arParams['USER_ID'];

			if (isset($arParams['CHECK_RIGHTS_ON_FILES']))
			{
				if (
					($arParams['CHECK_RIGHTS_ON_FILES'] === 'Y')
					|| ($arParams['CHECK_RIGHTS_ON_FILES'] === true)
				)
				{
					$bCheckRightsOnFiles = true;
				}
				else
					$bCheckRightsOnFiles = false;
			}
		}

		if ($userID === null)
			$userID = is_object($USER) ? intval($USER->GetID()) : 1;

		$rsTask = CTasks::GetByID($ID, false);
		if ($arTask = $rsTask->Fetch())
		{
			if ($this->CheckFields($arFields, $ID))
			{
				if ($USER_FIELD_MANAGER->CheckFields("TASKS_TASK", $ID, $arFields))
				{
					unset($arFields["ID"]);

					$arBinds = array(
						"DESCRIPTION" => $arFields["DESCRIPTION"],
						"DECLINE_REASON" => $arFields["DECLINE_REASON"]
					);

					$time = time()+CTimeZone::GetOffset();

					$arFields["CHANGED_BY"] = $userID;
					$arFields["CHANGED_DATE"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $time);

					if (!$arFields["OUTLOOK_VERSION"])
					{
						$arFields["OUTLOOK_VERSION"] = ($arTask["OUTLOOK_VERSION"] ? $arTask["OUTLOOK_VERSION"] : 1) + 1;
					}

					// If new status code given AND new status code != current status => than update
					if (isset($arFields["STATUS"]) 
						&& ( (int) $arTask['STATUS'] !== (int) $arFields['STATUS'] )
					)
					{
						$arFields["STATUS_CHANGED_BY"] = $userID;
						$arFields["STATUS_CHANGED_DATE"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $time);

						if ($arFields["STATUS"] == 5 || $arFields["STATUS"] == 4)
						{
							$arFields["CLOSED_BY"] = $userID;
							$arFields["CLOSED_DATE"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $time);
						}
						else
						{
							$arFields["CLOSED_BY"] = false;
							$arFields["CLOSED_DATE"] = false;
							if ($arFields["STATUS"] == 3)
							{
								$arFields["DATE_START"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL")), $time);
							}
						}
					}

					if ($arFields["REPLICATE"] == "Y")
					{
						$arFields["REPLICATE_PARAMS"] = serialize($arFields["REPLICATE_PARAMS"]);
					}

					foreach(GetModuleEvents('tasks', 'OnBeforeTaskUpdate', true) as $arEvent)
					{
						if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
						{
							if (sizeof($this->_errors) == 0) {
								$this->_errors[] = array("text" => GetMessage("TASKS_UNKNOWN_UPDATE_ERROR"), "id" => "ERROR_UNKNOWN_UPDATE_TASK_ERROR");
							}
							return false;
						}
					}

					$strUpdate = $DB->PrepareUpdate("b_tasks", $arFields, "tasks");
					$strSql = "UPDATE b_tasks SET ".$strUpdate." WHERE ID=".$ID;
					$result = $DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

					if ($result)
					{
						$arParticipants = array_merge(
							array(
								$arTask['CREATED_BY'], 
								$arTask['RESPONSIBLE_ID']
							), 
							(array) $arTask['ACCOMPLICES'],
							(array) $arTask['AUDITORS']
						);

						if (isset($arFields['CREATED_BY']))
							$arParticipants[] = $arFields['CREATED_BY'];

						if (isset($arFields['RESPONSIBLE_ID']))
							$arParticipants[] = $arFields['RESPONSIBLE_ID'];

						if (isset($arFields['ACCOMPLICES']))
						{
							$arParticipants = array_merge(
								$arParticipants,
								(array) $arFields['ACCOMPLICES']
							);
						}

						if (isset($arFields['AUDITORS']))
						{
							$arParticipants = array_merge(
								$arParticipants,
								(array) $arFields['AUDITORS']
							);
						}

						$arParticipants = array_unique($arParticipants);

						// Emit pull event
						try
						{
							$arPullRecipients = array();

							foreach($arParticipants as $userId)
								$arPullRecipients[] = (int) $userId;

							$taskGroupId = 0;	// no group
							$taskGroupIdBeforeUpdate = 0;	// no group

							if (isset($arTask['GROUP_ID']) && ($arTask['GROUP_ID'] > 0))
								$taskGroupId = (int) $arTask['GROUP_ID'];

							// if $arFields['GROUP_ID'] not given, than it means, 
							// that group not changed during this update, so
							// we must take existing group_id (from $arTask)
							if ( ! array_key_exists('GROUP_ID', $arFields) )
							{
								if (isset($arTask['GROUP_ID']) && ($arTask['GROUP_ID'] > 0))
									$taskGroupIdBeforeUpdate = (int) $arTask['GROUP_ID'];
								else
									$taskGroupIdBeforeUpdate = 0;	// no group
							}
							else	// Group given, use it
							{
								if ($arFields['GROUP_ID'] > 0)
									$taskGroupIdBeforeUpdate = (int) $arFields['GROUP_ID'];
								else
									$taskGroupIdBeforeUpdate = 0;	// no group
							}
								
							$arPullData = array(
								'TASK_ID' => (int) $ID,
								'BEFORE' => array(
									'GROUP_ID' => $taskGroupId
								),
								'AFTER' => array(
									'GROUP_ID' => $taskGroupIdBeforeUpdate
								),
								'TS' => time(),
								'event_GUID' => $eventGUID
							);

							self::EmitPullWithTagPrefix(
								$arPullRecipients, 
								'TASKS_GENERAL_', 
								'task_update', 
								$arPullData
							);

							self::EmitPullWithTag(
								$arPullRecipients, 
								'TASKS_TASK_' . (int) $ID, 
								'task_update', 
								$arPullData
							);
						}
						catch (Exception $e)
						{
							$bWasFatalError = true;
							$this->_errors[] = 'at line ' . $e->GetLine() 
								. ', ' . $e->GetMessage();
						}

						if (($status = intval($arFields["STATUS"])) && $status > 0 && $status < 8
							&& ( (int) $arTask['STATUS'] !== (int) $arFields['STATUS'] )	// only if status changed
						)
						{
							if ($status == 7)
							{
								$arTask["DECLINE_REASON"] = $arFields["DECLINE_REASON"];
							}
							CTaskNotifications::SendStatusMessage($arTask, $status, $arFields);
						}
						CTaskNotifications::SendUpdateMessage($arFields, $arTask);

						// changes log
						$arTmp = array('arTask' => $arTask, 'arFields' => $arFields);

						if (isset($arFields['DURATION_PLAN']) && isset($arFields['DURATION_TYPE']))
						{
							if ($arFields['DURATION_TYPE'] === 'hours')
							{
								$arTmp['arFields']['DURATION_PLAN_SECONDS'] = $arFields['DURATION_PLAN'] * 3600;
								unset($arTmp['arFields']['DURATION_PLAN']);
							}
							elseif ($arFields['DURATION_TYPE'] === 'days')
							{
								$arTmp['arFields']['DURATION_PLAN_SECONDS'] = $arFields['DURATION_PLAN'] * 3600 * 24;
								unset($arTmp['arFields']['DURATION_PLAN']);
							}
						}

						if (isset($arTask['DURATION_PLAN']) && isset($arTask['DURATION_TYPE']))
						{
							if ($arTask['DURATION_TYPE'] === 'hours')
							{
								$arTmp['arTask']['DURATION_PLAN_SECONDS'] = $arTask['DURATION_PLAN'] * 3600;
								unset($arTmp['arTask']['DURATION_PLAN']);
							}
							elseif ($arTask['DURATION_TYPE'] === 'days')
							{
								$arTmp['arTask']['DURATION_PLAN_SECONDS'] = $arTask['DURATION_PLAN'] * 3600 * 24;
								unset($arTmp['arTask']['DURATION_PLAN']);
							}
						}

						$arChanges = CTaskLog::GetChanges($arTmp['arTask'], $arTmp['arFields']);

						unset($arTmp);

						foreach ($arChanges as $key => $value)
						{
							$arLogFields = array(
								"TASK_ID" => $ID,
								"USER_ID" => $arFields["CHANGED_BY"],
								"CREATED_DATE" => $arFields["CHANGED_DATE"],
								"FIELD" => $key,
								"FROM_VALUE" => $value["FROM_VALUE"],
								"TO_VALUE" => $value["TO_VALUE"],
							);
							$log = new CTaskLog();
							$log->Add($arLogFields);
						}

						if (isset($arFields["ACCOMPLICES"]) && isset($arChanges["ACCOMPLICES"]))
						{
							CTaskMembers::DeleteByTaskID($ID, "A");
							CTasks::AddAccomplices($ID, $arFields["ACCOMPLICES"]);
						}

						if (isset($arFields["AUDITORS"]) && isset($arChanges["AUDITORS"]))
						{
							CTaskMembers::DeleteByTaskID($ID, "U");
							CTasks::AddAuditors($ID, $arFields["AUDITORS"]);
						}

						if (isset($arFields["FILES"]) && (isset($arChanges["NEW_FILES"]) || isset($arChanges["DELETED_FILES"])))
						{
							$arNotDeleteFiles = $arFields["FILES"];
							CTaskFiles::DeleteByTaskID($ID, $arNotDeleteFiles);
							CTasks::AddFiles(
								$ID,
								$arFields["FILES"],
								array(
									'USER_ID'               => $userID,
									'CHECK_RIGHTS_ON_FILES' => $bCheckRightsOnFiles
								)
							);
						}

						if (isset($arFields["TAGS"]) && isset($arChanges["TAGS"]))
						{
							CTaskTags::DeleteByTaskID($ID);
							CTasks::AddTags($ID, $arTask["CREATED_BY"], $arFields["TAGS"]);
						}

						if (isset($arFields["DEPENDS_ON"]) && isset($arChanges["DEPENDS_ON"]))
						{
							CTaskDependence::DeleteByTaskID($ID);
							CTasks::AddPrevious($ID, $arFields["DEPENDS_ON"]);
						}

						$USER_FIELD_MANAGER->Update("TASKS_TASK", $ID, $arFields);

						$arFields["ID"] = $ID;

						$arMergedFields = array_merge($arTask, $arFields);

						CTaskSync::UpdateItem($arFields, $arTask); // MS Exchange

						foreach(GetModuleEvents('tasks', 'OnTaskUpdate', true) as $arEvent)
							ExecuteModuleEventEx($arEvent, array($ID, &$arFields));

						CTasks::Index($arMergedFields, $arFields["TAGS"]); // search index

						// clear cache
						if ($arTask["GROUP_ID"])
							$CACHE_MANAGER->ClearByTag("tasks_group_".$arTask["GROUP_ID"]);

						if ($arFields['GROUP_ID'] && ($arFields['GROUP_ID'] != $arTask['GROUP_ID']))
							$CACHE_MANAGER->ClearByTag('tasks_group_' . $arFields['GROUP_ID']);

						foreach($arParticipants as $userId)
							$CACHE_MANAGER->ClearByTag("tasks_user_".$userId);

						if ($bWasFatalError)
							soundex('push&pull: bWasFatalError === true');

						return true;
					}
				}
				else
				{
					$e = $APPLICATION->GetException();
					foreach($e->messages as $msg)
					{
						$this->_errors[] = $msg;
					}
				}
			}
		}

		if (sizeof($this->_errors) == 0)
			$this->_errors[] = array("text" => GetMessage("TASKS_UNKNOWN_UPDATE_ERROR"), "id" => "ERROR_UNKNOWN_UPDATE_TASK_ERROR");

		return false;
	}


	/**
	 * This method is deprecated. Use CTaskItem::delete() instead.
	 * @deprecated
	 */
	public static function Delete($ID, $arParams = array())
	{
		global $DB, $CACHE_MANAGER;

		if (isset($arParams['META::EVENT_GUID']))
		{
			$eventGUID = $arParams['META::EVENT_GUID'];
			unset($arParams['META::EVENT_GUID']);
		}
		else
			$eventGUID = sha1(uniqid('AUTOGUID', true));

		$paramSkipExchangeSync = false;

		if (is_array($arParams))
		{
			if (
				isset($arParams['skipExchangeSync'])
				&& (
					($arParams['skipExchangeSync'] === 'Y')
					|| ($arParams['skipExchangeSync'] === true)
				)
			)
			{
				$paramSkipExchangeSync = true;
			}
		}

		$ID = intval($ID);
		if ($ID < 1)
			return false;

		$rsTask = CTasks::GetByID($ID, false);
		if ($arTask = $rsTask->Fetch())
		{
			foreach(GetModuleEvents('tasks', 'OnBeforeTaskDelete', true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				{
					return false;
				}
			}

			CTaskMembers::DeleteByTaskID($ID);
			CTaskFiles::DeleteByTaskID($ID);
			CTaskDependence::DeleteByTaskID($ID);
			CTaskDependence::DeleteByDependsOnID($ID);
			CTaskTags::DeleteByTaskID($ID);
			CTaskLog::DeleteByTaskId ($ID);

			$strSql = "DELETE FROM b_tasks_viewed WHERE TASK_ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$strSql = "DELETE FROM b_tasks_reminder WHERE TASK_ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			// clear cache
			if ($arTask["GROUP_ID"])
			{
				$CACHE_MANAGER->ClearByTag("tasks_group_".$arTask["GROUP_ID"]);
			}
			$arParticipants = array_unique(array_merge(array($arTask["CREATED_BY"], $arTask["RESPONSIBLE_ID"]), $arTask["ACCOMPLICES"], $arTask["AUDITORS"]));
			foreach($arParticipants as $userId)
			{
				$CACHE_MANAGER->ClearByTag("tasks_user_".$userId);
			}

			$strSql = "UPDATE b_tasks_template SET TASK_ID = NULL WHERE TASK_ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$strSql = "UPDATE b_tasks SET PARENT_ID = ".($arTask["PARENT_ID"] ? $arTask["PARENT_ID"] : "NULL")." WHERE PARENT_ID = ".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$strSql = "DELETE FROM b_tasks WHERE ID = ".$ID;

			if ($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			{
				CTaskNotifications::SendDeleteMessage($arTask);

				if ($arTask["FORUM_TOPIC_ID"] && CModule::IncludeModule("forum"))
				{
					CForumTopic::Delete($arTask["FORUM_TOPIC_ID"]);
				}

				if ( ! $paramSkipExchangeSync )
					CTaskSync::DeleteItem($arTask); // MS Exchange

				// Emit pull event
				try
				{
					$arPullRecipients = array();

					foreach($arParticipants as $userId)
						$arPullRecipients[] = (int) $userId;

					$taskGroupId = 0;	// no group

					if (isset($arTask['GROUP_ID']) && ($arTask['GROUP_ID'] > 0))
						$taskGroupId = (int) $arTask['GROUP_ID'];

					$arPullData = array(
						'TASK_ID' => (int) $ID,
						'BEFORE' => array(
							'GROUP_ID' => $taskGroupId
						),
						'TS' => time(),
						'event_GUID' => $eventGUID
					);

					self::EmitPullWithTagPrefix(
						$arPullRecipients, 
						'TASKS_GENERAL_', 
						'task_remove', 
						$arPullData
					);

					self::EmitPullWithTag(
						$arPullRecipients, 
						'TASKS_TASK_' . (int) $ID, 
						'task_remove', 
						$arPullData
					);
				}
				catch (Exception $e)
				{
				}

				foreach(GetModuleEvents('tasks', 'OnTaskDelete', true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array($ID));

				if (CModule::IncludeModule("search"))
				{
					CSearch::DeleteIndex("tasks", $ID);
				}
			}

			return true;
		}

		return false;
	}


	protected static function GetSqlByFilter ($arFilter, $userID, $sAliasPrefix)
	{
		global $DB;

		if ( ! is_array($arFilter) )
			throw new Exception();

		$logicStr = ' AND ';

		if (isset($arFilter['::LOGIC']))
		{
			switch ($arFilter['::LOGIC'])
			{
				case 'AND':
					$logicStr = ' AND ';
				break;

				case 'OR':
					$logicStr = ' OR ';
				break;

				default:
					throw new Exception('Unknown logic in filter');
				break;
			}
		}

		$arSqlSearch = array();

		foreach ($arFilter as $key => $val)
		{
			// Skip meta-key
			if ($key === '::LOGIC')
				continue;

			// Subfilter?
			if (substr($key, 0, 12) === '::SUBFILTER-')
			{
				$arSqlSearch[] = self::GetSqlByFilter ($val, $userID, $sAliasPrefix);
				continue;
			}

			$key = ltrim($key);

			$res = CTasks::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = strtoupper($key);

			switch ($key)
			{
				case 'META::ID_OR_NAME':
					if (strtoupper($DB->type) == "ORACLE")
						$arSqlSearch[] = " (T.ID = " . $val . " OR T.TITLE LIKE '" . $DB->ForSqlLike($val) ."' ESCAPE '\\') ";
					else
						$arSqlSearch[] = " (T.ID = " . $val . " OR T.TITLE LIKE '" . $DB->ForSqlLike($val) . "') ";
					break;

				case "ID":
				case "PARENT_ID":
				case "GROUP_ID":
				case "CREATED_BY":
				case "STATUS_CHANGED_BY":
				case "PRIORITY":
				case "FORUM_TOPIC_ID":
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix."T.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case 'META:GROUP_ID_IS_NULL_OR_ZERO':
					$key = 'GROUP_ID';
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix."T.".$key, $val, "null_or_zero", $bFullJoin, $cOperationType, false);
					break;

				case "RESPONSIBLE_ID":
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix."T.RESPONSIBLE_ID", $val, "number", $bFullJoin, $cOperationType);
					break;

				case "CHANGED_BY":
					$arSqlSearch[] = CTasks::FilterCreate("CASE WHEN ".$sAliasPrefix."T.".$key." IS NULL THEN ".$sAliasPrefix."T.CREATED_BY ELSE ".$sAliasPrefix."T.".$key." END", $val, "number", $bFullJoin, $cOperationType);
					break;

				case 'GUID':				
				case "TITLE":
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix."T.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "TAG":
					if (!is_array($val))
					{
						$val = array($val);
					}
					$arConds = array();
					foreach ($val as $tag)
					{
						if ($tag)
						{
							$arConds[] = "(".$sAliasPrefix."TT.NAME = '".$DB->ForSql($tag)."')";
						}
					}
					if (sizeof($arConds))
					{
						$arSqlSearch[] = "EXISTS(
							SELECT
								'x'
							FROM
								b_tasks_tag ".$sAliasPrefix."TT
							WHERE
								(".implode(" OR ", $arConds).")
							AND
								".$sAliasPrefix."TT.TASK_ID = ".$sAliasPrefix."T.ID
						)";
					}
					break;

				case 'REAL_STATUS':
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix . "T.STATUS", $val, "number", $bFullJoin, $cOperationType);
				break;

				case "STATUS":
					$arSqlSearch[] = CTasks::FilterCreate("
						CASE
							WHEN
								".$sAliasPrefix."T.DEADLINE < ".$DB->CurrentTimeFunction()." AND ".$sAliasPrefix."T.STATUS != '4' AND ".$sAliasPrefix."T.STATUS != '5' AND (".$sAliasPrefix."T.STATUS != '7' OR ".$sAliasPrefix."T.RESPONSIBLE_ID != ".$userID.")
							THEN
								'-1'
							WHEN
								".$sAliasPrefix."TV.USER_ID IS NULL
								AND
								".$sAliasPrefix."T.CREATED_BY != ".$userID."
								AND
								(".$sAliasPrefix."T.STATUS = 1 OR ".$sAliasPrefix."T.STATUS = 2)
							THEN
								'-2'
							ELSE
								".$sAliasPrefix."T.STATUS
						END
					", $val, "number", $bFullJoin, $cOperationType);
					break;

				case "MARK":
				case "XML_ID":
				case "SITE_ID":
				case "ADD_IN_REPORT":
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix."T.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "DATE_START":
				case "DEADLINE":
				case "CREATED_DATE":
				case "CLOSED_DATE":
					$arSqlSearch[] = CTasks::FilterCreate($sAliasPrefix."T.".$key, $DB->CharToDateFunction($val), "date", $bFullJoin, $cOperationType);
					break;

				case "CHANGED_DATE":
					$arSqlSearch[] = CTasks::FilterCreate("CASE WHEN ".$sAliasPrefix."T.".$key." IS NULL THEN ".$sAliasPrefix."T.CREATED_DATE ELSE ".$sAliasPrefix."T.".$key." END", $DB->CharToDateFunction($val), "date", $bFullJoin, $cOperationType);
					break;

				case "ACCOMPLICE":
					if (!is_array($val))
					{
						$val = array($val);
					}
					$arConds = array();
					foreach ($val as $id)
					{
						if ($id)
						{
							$arConds[] = "(".$sAliasPrefix."TM.USER_ID = '".intval($id)."')";
						}
					}
					if (sizeof($arConds))
					{
						$arSqlSearch[] = "EXISTS(
							SELECT
								'x'
							FROM
								b_tasks_member ".$sAliasPrefix."TM
							WHERE
								(".implode(" OR ", $arConds).")
							AND
								".$sAliasPrefix."TM.TASK_ID = ".$sAliasPrefix."T.ID
							AND
								".$sAliasPrefix."TM.TYPE = 'A'
						)";
					}
					break;

				case "PERIOD":
				case "ACTIVE":
					if ($val["START"] || $val["END"])
					{
						$strDateStart = $strDateEnd = false;

						if (MakeTimeStamp($val['START']) > 0)
						{
							$strDateStart = $DB->CharToDateFunction(
								$DB->ForSql(
									CDatabase::FormatDate(
										$val['START'], 
										FORMAT_DATETIME
									)
								)
							);
						}

						if (MakeTimeStamp($val['END']))
						{
							$strDateEnd = $DB->CharToDateFunction(
								$DB->ForSql(
									CDatabase::FormatDate(
										$val['END'], 
										FORMAT_DATETIME
									)
								)
							);
						}

						if (($strDateStart !== false) && ($strDateEnd !== false))
						{
							$arSqlSearch[] = "(
									(T.CREATED_DATE >= $strDateStart AND T.CREATED_DATE <= $strDateEnd)
								OR
									(T.CLOSED_DATE >= $strDateStart AND T.CLOSED_DATE <= $strDateEnd)
								)";
						}
						elseif (($strDateStart !== false) && ($strDateEnd === false))
						{
							$arSqlSearch[] = "(
									(T.CREATED_DATE >= $strDateStart)
								OR
									(T.CLOSED_DATE >= $strDateStart)
								)";
						}
						elseif (($strDateStart === false) && ($strDateEnd !== false))
						{
							$arSqlSearch[] = "(
									(T.CREATED_DATE <= $strDateEnd)
								OR
									(T.CLOSED_DATE <= $strDateEnd)
								)";
						}
					}
					break;

				case "AUDITOR":
					if (!is_array($val))
					{
						$val = array($val);
					}
					$arConds = array();
					foreach ($val as $id)
					{
						if ($id)
						{
							$arConds[] = "(".$sAliasPrefix."TM.USER_ID = '".intval($id)."')";
						}
					}
					if (sizeof($arConds))
					{
						$arSqlSearch[] = "EXISTS(
							SELECT
								'x'
							FROM
								b_tasks_member ".$sAliasPrefix."TM
							WHERE
								(".implode(" OR ", $arConds).")
							AND
								".$sAliasPrefix."TM.TASK_ID = ".$sAliasPrefix."T.ID
							AND
								".$sAliasPrefix."TM.TYPE = 'U'
						)";
					}
					break;

				case "DOER":
					$val = intval($val);
					$arSqlSearch[] = "(".$sAliasPrefix."T.RESPONSIBLE_ID = ".$val." OR EXISTS(SELECT 'x' FROM b_tasks_member ".$sAliasPrefix."TM WHERE ".$sAliasPrefix."TM.TASK_ID = ".$sAliasPrefix."T.ID AND ".$sAliasPrefix."TM.USER_ID = '".$val."' AND ".$sAliasPrefix."TM.TYPE = 'A'))";
					break;

				case "MEMBER":
					$val = intval($val);
					$arSqlSearch[] = "(".$sAliasPrefix."T.CREATED_BY = ".intval($val)." OR ".$sAliasPrefix."T.RESPONSIBLE_ID = ".intval($val)." OR EXISTS(SELECT 'x' FROM b_tasks_member ".$sAliasPrefix."TM WHERE ".$sAliasPrefix."TM.TASK_ID = ".$sAliasPrefix."T.ID AND ".$sAliasPrefix."TM.USER_ID = '".$val."'))";
					break;

				case "DEPENDS_ON":
					if (!is_array($val))
					{
						$val = array($val);
					}
					$arConds = array();
					foreach ($val as $id)
					{
						if ($id)
						{
							$arConds[] = "(".$sAliasPrefix."TD.TASK_ID = '".intval($id)."')";
						}
					}
					if (sizeof($arConds))
					{
						$arSqlSearch[] = "EXISTS(
							SELECT
								'x'
							FROM
								b_tasks_dependence ".$sAliasPrefix."TD
							WHERE
								(".implode(" OR ", $arConds).")
							AND
								".$sAliasPrefix."TD.DEPENDS_ON_ID = ".$sAliasPrefix."T.ID
						)";
					}
					break;

				case "ONLY_ROOT_TASKS":
					if ($val == "Y")
					{
						$arSqlSearch[] = "(".$sAliasPrefix."T.PARENT_ID IS NULL OR NOT EXISTS (".CTasks::GetRootSubquery($arFilter)."))";
					}
					break;

				case "SUBORDINATE_TASKS":
					if ($val == "Y")
					{
						$arSubSqlSearch = array(
							$sAliasPrefix."T.CREATED_BY = ".$userID,
							$sAliasPrefix."T.RESPONSIBLE_ID = ".$userID,
							"EXISTS(SELECT 'x' FROM b_tasks_member ".$sAliasPrefix."TM WHERE ".$sAliasPrefix."TM.TASK_ID = ".$sAliasPrefix."T.ID AND ".$sAliasPrefix."TM.USER_ID = ".$userID.")"
						);
						// subordinate check
						if ($strSql = CTasks::GetSubordinateSql($sAliasPrefix, array('USER_ID' => $userID)))
						{
							$arSubSqlSearch[] = "EXISTS(".$strSql.")";
						}

						$arSqlSearch[] = "(".implode(" OR ", $arSubSqlSearch).")";
					}
					break;

				case "OVERDUED":
					if ($val == "Y")
					{
						$arSqlSearch[] = $sAliasPrefix."T.CLOSED_DATE IS NOT NULL AND ".$sAliasPrefix."T.DEADLINE IS NOT NULL AND ".$sAliasPrefix."T.DEADLINE < CLOSED_DATE";
					}
					break;

				case "SAME_GROUP_PARENT":
					if ($val == "Y" && !array_key_exists("ONLY_ROOT_TASKS", $arFilter))
					{
						$arSqlSearch[] = "EXISTS(
							SELECT
								'x'
							FROM
								b_tasks ".$sAliasPrefix."PT
							WHERE
								".$sAliasPrefix."T.PARENT_ID = ".$sAliasPrefix."PT.ID
							AND
								(" . $sAliasPrefix . "PT.GROUP_ID = " . $sAliasPrefix . "T.GROUP_ID 
								OR (" . $sAliasPrefix . "PT.GROUP_ID IS NULL AND " . $sAliasPrefix . "T.GROUP_ID IS NULL)
								OR (" . $sAliasPrefix . "PT.GROUP_ID = 0 AND " . $sAliasPrefix . "T.GROUP_ID IS NULL)
								OR (" . $sAliasPrefix . "PT.GROUP_ID IS NULL AND " . $sAliasPrefix . "T.GROUP_ID = 0)
								)
						)";
					}
					break;

				case "DEPARTMENT_ID":
					if ($strSql = CTasks::GetDeparmentSql($val, $sAliasPrefix))
					{
						$arSqlSearch[] = "EXISTS(".$strSql.")";
					}
					break;
			}
		}

		$sql = implode(
			$logicStr,
			array_filter(
				$arSqlSearch,
				function($p)
				{
					return ($p !== '');
				}
			)
		);

		if ($sql == '')
			$sql = '1=1';

		return ('(' . $sql . ')');
	}


	public static function GetFilter($arFilter, $sAliasPrefix = "", $arParams = false)
	{
		global $USER;

		if (!is_array($arFilter))
			$arFilter = array();

		$arSqlSearch = array();

		if (is_array($arParams) && array_key_exists('USER_ID', $arParams) && ($arParams['USER_ID'] > 0))
			$userID = (int) $arParams['USER_ID'];
		else
			$userID = is_object($USER) ? intval($USER->GetID()) : 0;

		$sql = self::GetSqlByFilter($arFilter, $userID, $sAliasPrefix);
		if (strlen($sql))
			$arSqlSearch[] = $sql;

		if (
			( ! CTasksTools::IsAdmin($userID) ) 	// not admin
			&& ( ! CTasksTools::IsPortalB24Admin($userID) )	// and not B24portal admin
			&& $arFilter["CHECK_PERMISSIONS"] != "N" 	// and not setted flag "skip permissions check"
			&& $arFilter["SUBORDINATE_TASKS"] != "Y")	// and not rights via subordination
		{
			$arSubSqlSearch = array(
				$sAliasPrefix."T.CREATED_BY = ".$userID,
				$sAliasPrefix."T.RESPONSIBLE_ID = ".$userID,
				"EXISTS(SELECT 'x' FROM b_tasks_member ".$sAliasPrefix."TM WHERE ".$sAliasPrefix."TM.TASK_ID = ".$sAliasPrefix."T.ID AND ".$sAliasPrefix."TM.USER_ID = ".$userID.")"
			);
			// subordinate check
			if ($strSql = CTasks::GetSubordinateSql($sAliasPrefix, $arParams))
			{
				$arSubSqlSearch[] = "EXISTS(".$strSql.")";
			}
			// group permission check
			if ($arAllowedGroups = CTasks::GetAllowedGroups($arParams))
			{
				$arSubSqlSearch[] = "(".$sAliasPrefix."T.GROUP_ID IN (".implode(",", $arAllowedGroups)."))";
			}

			$arSqlSearch[] = "(".implode(" OR ", $arSubSqlSearch).")";
		}

		return $arSqlSearch;
	}


	public static function MkOperationFilter($key)
	{
		$key = ltrim($key);

		if (substr($key, 0, 1) == "=") //Identical
		{
			$key = substr($key, 1);
			$cOperationType = "I";
		}
		elseif (substr($key, 0, 2) == "!=") //not Identical
		{
			$key = substr($key, 2);
			$cOperationType = "NI";
		}
		elseif (substr($key, 0, 1) == "%") //substring
		{
			$key = substr($key, 1);
			$cOperationType = "S";
		}
		elseif (substr($key, 0, 2) == "!%") //not substring
		{
			$key = substr($key, 2);
			$cOperationType = "NS";
		}
		elseif (substr($key, 0, 1) == "?") //logical
		{
			$key = substr($key, 1);
			$cOperationType = "?";
		}
		elseif (substr($key, 0, 2) == "><") //between
		{
			$key = substr($key, 2);
			$cOperationType = "B";
		}
		elseif (substr($key, 0, 3) == "!><") //not between
		{
			$key = substr($key, 3);
			$cOperationType = "NB";
		}
		elseif (substr($key, 0, 2) == ">=") //greater or equal
		{
			$key = substr($key, 2);
			$cOperationType = "GE";
		}
		elseif (substr($key, 0, 1) == ">")  //greater
		{
			$key = substr($key, 1);
			$cOperationType = "G";
		}
		elseif (substr($key, 0, 2) == "<=")  //less or equal
		{
			$key = substr($key, 2);
			$cOperationType = "LE";
		}
		elseif (substr($key, 0, 1) == "<")  //less
		{
			$key = substr($key, 1);
			$cOperationType = "L";
		}
		elseif (substr($key, 0, 1) == "!") // not field LIKE val
		{
			$key = substr($key, 1);
			$cOperationType = "N";
		}
		else
			$cOperationType = "E"; // field LIKE val

		return array("FIELD" => $key, "OPERATION" => $cOperationType);
	}


	public static function FilterCreate($fname, $vals, $type, &$bFullJoin, $cOperationType=false, $bSkipEmpty = true)
	{
		global $DB;
		if (!is_array($vals))
		{
			$vals = array($vals);
		}
		else
		{
			$vals = array_values($vals);
		}

		if (count($vals) < 1)
			return "";

		if (is_bool($cOperationType))
		{
			if ($cOperationType === true)
				$cOperationType = "N";
			else
				$cOperationType = "E";
		}

		if ($cOperationType == "G")
			$strOperation = ">";
		elseif ($cOperationType == "GE")
			$strOperation = ">=";
		elseif ($cOperationType == "LE")
			$strOperation = "<=";
		elseif ($cOperationType == "L")
			$strOperation = "<";
		else
			$strOperation = "=";

		$bFullJoin = false;
		$bWasLeftJoin = false;

		$res = array();
		for ($i = 0, $valsCnt = count($vals); $i < $valsCnt; $i++)
		{
			$val = $vals[$i];

			if ($type ==  "number" && !$val)
			{
				$val = 0;
			}

			if (!$bSkipEmpty || strlen($val) > 0 || (is_bool($val) && $val === false))
			{
				switch ($type)
				{
					case "string_equal":
						if (strlen($val) <= 0)
							$res[] =
									($cOperationType == "N" ? "NOT" : "").
									"(".
									$fname." IS NULL OR ".$DB->Length($fname).
									"<=0)";
						else
							$res[] =
									"(".
									($cOperationType == "N" ? " ".$fname." IS NULL OR NOT (" : "").
									$fname.$strOperation."'".$DB->ForSql($val)."'".
									($cOperationType == "N" ? ")" : "").
									")";
						break;

					case "string":
						if ($cOperationType == "?")
						{
							if (strlen($val) > 0)
								$res[] = GetFilterQuery($fname, $val, "Y", array(), "N");
						}
						elseif ($cOperationType == "S")
						{
							$res[] = "(UPPER(".$fname.") LIKE UPPER('%".$DB->ForSqlLike($val)."%'))";
						}
						elseif ($cOperationType == "NS")
						{
							$res[] = "(UPPER(".$fname.") NOT LIKE UPPER('%".$DB->ForSqlLike($val)."%'))";
						}
						elseif (strlen($val) <= 0)
						{
							$res[] = ($cOperationType == "N" ? "NOT" : "")."(".$fname." IS NULL OR ".$DB->Length($fname)."<=0)";
						}
						else
						{
							if ($strOperation == "=")
								$res[] =
										"(".
										($cOperationType == "N" ? " ".$fname." IS NULL OR NOT (" : "").
										(strtoupper($DB->type) == "ORACLE"
											?
											$fname." LIKE "."'".$DB->ForSqlLike($val)."'"." ESCAPE '\\'" 
											: 
											$fname." ".($strOperation == "=" ? "LIKE" : $strOperation)." '".$DB->ForSqlLike($val)."'").
										($cOperationType == "N" ? ")" : "").
										")";
							else
								$res[] =
										"(".
										($cOperationType == "N" ? " ".$fname." IS NULL OR NOT (" : "").
										(strtoupper($DB->type) == "ORACLE" ? $fname." ".$strOperation." "."'".$DB->ForSql($val)."'"." " : $fname." ".$strOperation." '".$DB->ForSql($val)."'").
										($cOperationType == "N" ? ")" : "").
										")";
						}
						break;

					case "date":
						if (strlen($val) <= 0)
							$res[] = ($cOperationType == "N" ? "NOT" : "")."(".$fname." IS NULL)";
						else
							$res[] =
									"(".
									($cOperationType == "N" ? " ".$fname." IS NULL OR NOT (" : "").
									$fname." ".$strOperation." ".$val."".
									($cOperationType == "N" ? ")" : "").
									")";
						break;

					case "number":
						if (strlen($val) <= 0)
							$res[] = ($cOperationType == "N" ? "NOT" : "")."(".$fname." IS NULL)";
						else
							$res[] =
									"(".
									($cOperationType == "N" ? " ".$fname." IS NULL OR NOT (" : "").
									$fname." ".$strOperation." '".DoubleVal($val).
									($cOperationType == "N" ? "')" : "'").
									")";
						break;

					case "null_or_zero":
						if ($cOperationType == "N")
							$res[] = "((" . $fname . " IS NOT NULL) AND (" . $fname . " != 0))";
						else
							$res[] = "((" . $fname . " IS NULL) OR (" . $fname . " = 0))";

						break;
				}

				// INNER JOIN in this case
				if (strlen($val) > 0 && $cOperationType != "N")
					$bFullJoin = true;
				else
					$bWasLeftJoin = true;
			}
		}

		$strResult = "";
		for ($i = 0, $resCnt = count($res); $i < $resCnt; $i++)
		{
			if ($i > 0)
				$strResult .= ( $cOperationType == "N" ? " AND " : " OR ");
			$strResult .= $res[$i];
		}

		if (count($res) > 1)
			$strResult = "(".$strResult.")";

		if ($bFullJoin && $bWasLeftJoin && $cOperationType != "N")
			$bFullJoin = false;

		return $strResult;
	}


	/**
	 * This method is deprecated. Use CTaskItem class instead.
	 * @deprecated
	 */
	public static function GetByID($ID, $bCheckPermissions = true, $arParams = array())
	{
		$arFilter = array("ID" => $ID);
		if (!$bCheckPermissions)
		{
			$arFilter["CHECK_PERMISSIONS"] = "N";
		}
		$res = CTasks::GetList(array(), $arFilter, array("*", "UF_*"), $arParams);
		if ($res && ($task = $res->Fetch()))
		{
			$task["ACCOMPLICES"] = $task["AUDITORS"] = array();
			$rsMembers = CTaskMembers::GetList(array(), array("TASK_ID" => $ID));
			while ($arMember = $rsMembers->Fetch())
			{
				if ($arMember["TYPE"] == "A")
				{
					$task["ACCOMPLICES"][] = $arMember["USER_ID"];
				}
				elseif ($arMember["TYPE"] == "U")
				{
					$task["AUDITORS"][] = $arMember["USER_ID"];
				}
			}

			$arTagsFilter = array("TASK_ID" => $ID);
			$arTagsOrder = array("NAME" => "ASC");
			$rsTags = CTaskTags::GetList($arTagsOrder, $arTagsFilter);
			$task["TAGS"] = array();
			while ($arTag = $rsTags->Fetch())
			{
				$task["TAGS"][] = $arTag["NAME"];
			}

			$rsFiles = CTaskFiles::GetList(array(), array("TASK_ID" => $ID));
			$task["FILES"] = array();
			while ($arFile = $rsFiles->Fetch())
			{
				$task["FILES"][] = $arFile["FILE_ID"];
			}

			$rsDependsOn = CTaskDependence::GetList(array(), array("TASK_ID" => $ID));
			$task["DEPENDS_ON"] = array();
			while ($arDependsOn = $rsDependsOn->Fetch())
			{
				$task["DEPENDS_ON"][] = $arDependsOn["DEPENDS_ON_ID"];
			}

			$rsTask = new CDBResult;
			$rsTask->InitFromarray(array($task));
			return $rsTask;
		}
		else
		{
			return $res;
		}
	}


	public static function GetSubordinateDeps($userID = null)
	{
		global $USER, $CACHE_MANAGER;

		if (!($userID = intval($userID)))
		{
			$userID = is_object($USER) ? intval($USER->GetID()) : 0;
		}

		$arSubordinateDeps = array();

		if ($userID > 0)
		{
			$obCache = new CPHPCache();
			$lifeTime = 31536000;		// 365 days
			$cacheDir = "/tasks/subordinatedeps";
			if($obCache->InitCache($lifeTime, md5("subordinatedeps".$userID), $cacheDir))
			{
				$arSubordinateDeps = $obCache->GetVars();
			}
			elseif ($obCache->StartDataCache())
			{
				$IBlockID = COption::GetOptionInt('intranet', 'iblock_structure', 0);

				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$IBlockID);

				$rsSections = CIBlockSection::GetList(array(), array("IBLOCK_ID" => $IBlockID, "UF_HEAD" => $userID, "ACTIVE" => "Y", "CHECK_PERMISSIONS" => "N"), false, array('UF_HEAD'));
				$arSectionIDs = array();
				while ($arSection = $rsSections->Fetch())
				{
					$arSectionIDs[] = $arSection["ID"];

					$arFilter = array(
						"IBLOCK_ID" => $IBlockID,
						"GLOBAL_ACTIVE" => "Y",
						">LEFT_MARGIN" => $arSection["LEFT_MARGIN"],
						"<RIGHT_MARGIN" => $arSection["RIGHT_MARGIN"],
						"!ID" => $arSection["ID"], // little hack because of the iblock module minor bug
						"CHECK_PERMISSIONS" => "N",
					);
					$rsChildSections = CIBlockSection::GetList(array('left_margin' => 'asc'), $arFilter, false, array('ID'));
					while ($arChildSection = $rsChildSections->Fetch())
					{
						$arSectionIDs[] = $arChildSection["ID"];
					}
				}
				$arSubordinateDeps = $arSectionIDs;

				$CACHE_MANAGER->EndTagCache();
				$obCache->EndDataCache($arSubordinateDeps);
			}
		}

		return $arSubordinateDeps;
	}


	public static function GetAllowedGroups($arParams = array())
	{
		global $DB;
		static $ALLOWED_GROUPS = array();

		$userId = null;

		if (is_array($arParams) && isset($arParams['USER_ID']))
			$userId = $arParams['USER_ID'];
		else
		{
			global $USER;

			if (is_object($USER))
				$userId = $USER->GetID();
		}

		if ( ! ($userId >= 1) )
			$userId = 0;

		if (!isset($ALLOWED_GROUPS[$userId]) && CModule::IncludeModule("socialnetwork"))
		{
			$strSql = "SELECT DISTINCT(T.GROUP_ID) FROM b_tasks T WHERE T.GROUP_ID IS NOT NULL";
			$rsGroups = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$ALLOWED_GROUPS[$userId] = $arGroupsWithTasks = array();
			while ($arGroup = $rsGroups->Fetch())
			{
				$arGroupsWithTasks[] = $arGroup["GROUP_ID"];
			}
			if (is_array($arGroupsWithTasks) && sizeof($arGroupsWithTasks))
			{
				if ($userId === 0)
					$featurePerms = CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $arGroupsWithTasks, "tasks", "view_all");
				else
					$featurePerms = CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $arGroupsWithTasks, "tasks", "view_all");

				if (is_array($featurePerms))
				{
					$ALLOWED_GROUPS[$userId] = array_keys(array_filter($featurePerms));
				}
			}
		}

		return $ALLOWED_GROUPS[$userId];
	}


	public static function GetDepartmentManagers($arDepartments, $skipUserId=false, $arSelectFields = array('ID'))
	{
		global $CACHE_MANAGER;

		if ( ( ! is_array($arDepartments) )
			|| empty($arDepartments) 
			|| ( ! is_array($arSelectFields) ) 
		)
		{
			return false;
		}

		// We need ID in any case
		if ( ! in_array('ID', $arSelectFields) )
			$arSelectFields[] = 'ID';

		$arManagers = array();
		$obCache = new CPHPCache();
		$lifeTime = 31536000;		// 365 days
		$cacheDir = "/tasks/subordinatedeps";
		$cacheFPrint = sha1(
			serialize($arDepartments) 
			. '|' . serialize($arSelectFields)
			);
		if($obCache->InitCache($lifeTime, $cacheFPrint, $cacheDir))
		{
			$arManagers = $obCache->GetVars();
		}
		elseif ($obCache->StartDataCache())
		{
			$IBlockID = COption::GetOptionInt('intranet', 'iblock_structure', 0);

			$CACHE_MANAGER->StartTagCache($cacheDir);
			$CACHE_MANAGER->RegisterTag("iblock_id_".$IBlockID);

			$arUserIDs = self::GetDepartmentManagersIDs($arDepartments, $IBlockID);

			if (count($arUserIDs) > 0)
			{
				$arFilter = array(
					'ID' => implode('|', $arUserIDs)
					);

				// Prevent using users, that doesn't activate it's account
				// http://jabber.bx/view.php?id=29118
				if (IsModuleInstalled('bitrix24'))
					$arFilter['!LAST_LOGIN'] = false;

				$dbUser = CUser::GetList(
					$by = 'ID', 
					$order = 'ASC', 
					$arFilter,
					array('FIELDS' => $arSelectFields)	// selects only $arSelectFields fields
					);
				while ($arUser = $dbUser->GetNext())
					$arManagers[(int) $arUser["ID"]] = $arUser;
			}

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache($arManagers);
		}

		// remove user to be skipped
		if ( ($skipUserId !== false) 
			&& (isset($arManagers[(int) $skipUserId]))
		)
		{
			unset ($arManagers[(int) $skipUserId]);
		}

		return $arManagers;
	}


	protected static function GetDepartmentManagersIDs($arDepartments, $IBlockID)
	{
		$dbSections = CIBlockSection::GetList(
			array('SORT' => 'ASC'), 
			array(
				'ID'                => $arDepartments, 
				'IBLOCK_ID'         => $IBlockID, 
				'CHECK_PERMISSIONS' => 'N'), 
			false, 								// don't count
			array(
				'ID', 
				'UF_HEAD', 
				'IBLOCK_SECTION_ID')
			);

		$arUserIDs = array();
		while ($arSection = $dbSections->Fetch())
		{
			if ($arSection['UF_HEAD'] > 0)
				$arUserIDs[] = $arSection['UF_HEAD'];

			if ($arSection['IBLOCK_SECTION_ID'] > 0)
			{
				$arUserIDs = array_merge(
					$arUserIDs,
					self::GetDepartmentManagersIDs(array($arSection['IBLOCK_SECTION_ID']), $IBlockID)
					);
			}
		}

		return $arUserIDs;
	}


	/**
	 * @param $employeeID1
	 * @param $employeeID2
	 * @return bool true if $employeeID2 is manager of $employeeID1
	 */
	public static function IsSubordinate($employeeID1, $employeeID2)
	{
		if ($employeeID1 == $employeeID2)
		{
			return false;
		}

		$dbRes = CUser::GetList(
			$by = 'ID', 
			$order = 'ASC', 
			array('ID' => $employeeID1), 
			array('SELECT' => array('UF_DEPARTMENT'))
		);

		if (
			($arRes = $dbRes->Fetch()) 
			&& is_array($arRes['UF_DEPARTMENT']) 
			&& (count($arRes['UF_DEPARTMENT']) > 0)
		)
		{
			$arManagers = array_keys(CTasks::GetDepartmentManagers($arRes['UF_DEPARTMENT'], $employeeID1));

			if (in_array($employeeID2, $arManagers))
				return true;
		}

		return false;
	}


	public static function GetList($arOrder=array(), $arFilter=array(), $arSelect = array(), $arParams = array())
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		$nPageTop = false;

		if ( ! is_array($arParams) )
		{
			$nPageTop = $arParams;
			$arParams = false;
		}

		$obUserFieldsSql = new CUserTypeSQL();
		$obUserFieldsSql->SetEntity("TASKS_TASK", "T.ID");
		$obUserFieldsSql->SetSelect($arSelect);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		if (is_array($arParams) && array_key_exists('USER_ID', $arParams) && ($arParams['USER_ID'] > 0))
			$userID = (int) $arParams['USER_ID'];
		else
			$userID = is_object($USER) ? intval($USER->GetID()) : 0;

		$arFields = array(
			"ID" => "T.ID",
			"TITLE" => "T.TITLE",
			"DESCRIPTION" => "T.DESCRIPTION",
			"DESCRIPTION_IN_BBCODE" => "T.DESCRIPTION_IN_BBCODE",
			"DECLINE_REASON" => "T.DECLINE_REASON",
			"PRIORITY" => "T.PRIORITY",
			"STATUS" => "
				CASE
					WHEN
						T.DEADLINE < ".$DB->CurrentTimeFunction()." AND T.STATUS != '4' AND T.STATUS != '5' AND (T.STATUS != '7' OR T.RESPONSIBLE_ID != ".$userID.")
					THEN
						'-1'
					WHEN
						TV.USER_ID IS NULL
						AND
						T.CREATED_BY != ".$userID."
						AND
						(T.STATUS = 1 OR T.STATUS = 2)
					THEN
						'-2'
					ELSE
						T.STATUS
				END
			",
			"REAL_STATUS" => "T.STATUS",
			"MULTITASK" => "T.MULTITASK",
			"RESPONSIBLE_ID" => "T.RESPONSIBLE_ID",
			"RESPONSIBLE_NAME" => "RU.NAME",
			"RESPONSIBLE_LAST_NAME" => "RU.LAST_NAME",
			"RESPONSIBLE_SECOND_NAME" => "RU.SECOND_NAME",
			"RESPONSIBLE_LOGIN" => "RU.LOGIN",
			"RESPONSIBLE_WORK_POSITION" => "RU.WORK_POSITION",
			"RESPONSIBLE_PHOTO" => "RU.PERSONAL_PHOTO",
			"DATE_START" => $DB->DateToCharFunction("T.DATE_START", "FULL"),
			"DURATION_PLAN" => "T.DURATION_PLAN",
			"DURATION_TYPE" => "T.DURATION_TYPE",
			"DURATION_FACT" => "(SELECT SUM(TE.MINUTES) FROM b_tasks_elapsed_time TE WHERE TE.TASK_ID = T.ID GROUP BY TE.TASK_ID)",
			"REPLICATE" => "T.REPLICATE",
			"DEADLINE" => $DB->DateToCharFunction("T.DEADLINE", "FULL"),
			"DEADLINE_ORIG" => "T.DEADLINE",
			"START_DATE_PLAN" => $DB->DateToCharFunction("T.START_DATE_PLAN", "FULL"),
			"END_DATE_PLAN" => $DB->DateToCharFunction("T.END_DATE_PLAN", "FULL"),
			"CREATED_BY" => "T.CREATED_BY",
			"CREATED_BY_NAME" => "CU.NAME",
			"CREATED_BY_LAST_NAME" => "CU.LAST_NAME",
			"CREATED_BY_SECOND_NAME" => "CU.SECOND_NAME",
			"CREATED_BY_LOGIN" => "CU.LOGIN",
			"CREATED_BY_WORK_POSITION" => "CU.WORK_POSITION",
			"CREATED_BY_PHOTO" => "CU.PERSONAL_PHOTO",
			"CREATED_DATE" => $DB->DateToCharFunction("T.CREATED_DATE", "FULL"),
			"CHANGED_BY" => "T.CHANGED_BY",
			"CHANGED_DATE" => $DB->DateToCharFunction("T.CHANGED_DATE", "FULL"),
			"STATUS_CHANGED_BY" => "T.CHANGED_BY",
			"STATUS_CHANGED_DATE" => 
				'CASE WHEN T.STATUS_CHANGED_DATE IS NULL THEN ' 
				. $DB->DateToCharFunction("T.CHANGED_DATE", "FULL") 
				. ' ELSE ' 
				. $DB->DateToCharFunction("T.STATUS_CHANGED_DATE", "FULL") 
				. ' END ',
			"CLOSED_BY" => "T.CLOSED_BY",
			"CLOSED_DATE" => $DB->DateToCharFunction("T.CLOSED_DATE", "FULL"),
			'GUID' => 'T.GUID',
			"XML_ID" => "T.XML_ID",
			"MARK" => "T.MARK",
			"ALLOW_CHANGE_DEADLINE" => "T.ALLOW_CHANGE_DEADLINE",
			"TASK_CONTROL" => "T.TASK_CONTROL",
			"ADD_IN_REPORT" => "T.ADD_IN_REPORT",
			"GROUP_ID" => "CASE WHEN T.GROUP_ID IS NULL THEN 0 ELSE T.GROUP_ID END",
			"FORUM_TOPIC_ID" => "T.FORUM_TOPIC_ID",
			"PARENT_ID" => "T.PARENT_ID",
			"COMMENTS_COUNT" => "FT.POSTS",
			"SITE_ID" => "T.SITE_ID",
			"SUBORDINATE" => ($strSql = CTasks::GetSubordinateSql('', $arParams)) ? "CASE WHEN EXISTS(".$strSql.") THEN 'Y' ELSE 'N' END" : "'N'",
			"EXCHANGE_MODIFIED" => "T.EXCHANGE_MODIFIED",
			"EXCHANGE_ID" => "T.EXCHANGE_ID",
			"OUTLOOK_VERSION" => "T.OUTLOOK_VERSION",
			"VIEWED_DATE" => $DB->DateToCharFunction("TV.VIEWED_DATE", "FULL"),
			'FORKED_BY_TEMPLATE_ID' => 'T.FORKED_BY_TEMPLATE_ID'
		);

		if (count($arSelect) <= 0 || in_array("*", $arSelect))
		{
			$arSelect = array_keys($arFields);
		}
		elseif (!in_array("ID", $arSelect))
		{
			$arSelect[] = "ID";
		}

		// If DESCRIPTION selected, than BBCODE flag must be selected too
		if (
			in_array('DESCRIPTION', $arSelect)
			&& ( ! in_array('DESCRIPTION_IN_BBCODE', $arSelect) )
		)
		{
			$arSelect[] = 'DESCRIPTION_IN_BBCODE';
		}

		if (!is_array($arOrder))
			$arOrder = array();

		foreach ($arOrder as $by => $order)
		{
			$needle = null;
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order != "asc")
				$order = "desc";

			switch ($by)
			{
				case 'id':
					$arSqlOrder[] = " ID ".$order." ";
				break;

				case 'title':
					$arSqlOrder[] = " TITLE ".$order." ";
					$needle = 'TITLE';
				break;

				case 'date_start':
					$arSqlOrder[] = " DATE_START ".$order." ";
					$needle = 'DATE_START';
				break;

				case 'deadline':
					$arSqlOrder[] = " DEADLINE_ORIG ".$order." ";
					$needle = 'DEADLINE_ORIG';
				break;

				case 'status':
					$arSqlOrder[] = " STATUS ".$order." ";
					$needle = 'STATUS';
				break;

				case 'priority':
					$arSqlOrder[] = " PRIORITY ".$order." ";
					$needle = 'PRIORITY';
				break;

				case 'mark':
					$arSqlOrder[] = " MARK ".$order." ";
					$needle = 'MARK';
				break;

				case 'created_by':
					$arSqlOrder[] = " CREATED_BY_LAST_NAME ".$order." ";
					$needle = 'CREATED_BY_LAST_NAME';
				break;

				case 'responsible_id':
					$arSqlOrder[] = " RESPONSIBLE_LAST_NAME ".$order." ";
					$needle = 'RESPONSIBLE_LAST_NAME';
				break;

				case 'group_id':
					$arSqlOrder[] = " GROUP_ID ".$order." ";
					$needle = 'GROUP_ID';
				break;

				default:
					if ($s = $obUserFieldsSql->GetOrder($by))
						$arSqlOrder[$by] = " ".$s." ".$order." ";
					else
						$arSqlOrder[] = " ID ".$order." ";
				break;
			}

			if (
				($needle !== null)
				&& ( ! in_array($needle, $arSelect) )
			)
			{
				$arSelect[] = $needle;
			}
		}

		$arSqlSelect = array();
		foreach ($arSelect as $field)
		{
			$field = strtoupper($field);
			if (array_key_exists($field, $arFields))
				$arSqlSelect[$field] = $arFields[$field]." AS ".$field;
		}

		if (!sizeof($arSqlSelect))
		{
			$arSqlSelect = "T.ID AS ID";
		}

		// First level logic MUST be 'AND', because of backward compatibility
		// and some requests for checking rights, attached at first level of filter.
		// Situtation when there is OR-logic at first level cannot be resolved
		// in general case.
		// So if there is OR-logic, it is FATAL error caused by programmer.
		// But, if you want to use OR-logic at the first level of filter, you
		// can do this by putting all your filter conditions to the ::SUBFILTER-xxx,
		// except CHECK_PERMISSIONS, SUBORDINATE_TASKS (if you don't know exactly, 
		// what are consequences of this fields in OR-logic of subfilters).
		if (isset($arFilter['::LOGIC']))
			CTaskAssert::assert($arFilter['::LOGIC'] === 'AND');

		$arSqlSearch = CTasks::GetFilter($arFilter, '', $arParams);

		$r = $obUserFieldsSql->GetFilter();
		if (strlen($r) > 0)
		{
			$arSqlSearch[] = "(".$r.")";
		}

		$strSql = "
			SELECT
				".implode(",\n", $arSqlSelect)."
				".$obUserFieldsSql->GetSelect();

		$strFrom = "
			FROM
				b_tasks T
			INNER JOIN b_user CU ON CU.ID = T.CREATED_BY 
			INNER JOIN b_user RU ON RU.ID = T.RESPONSIBLE_ID 
			LEFT JOIN b_tasks_viewed TV ON TV.TASK_ID = T.ID 
				AND TV.USER_ID = " . $userID . " " 
			. (in_array("COMMENTS_COUNT", $arSelect) ? " LEFT JOIN b_forum_topic FT ON FT.ID = T.FORUM_TOPIC_ID " : "")
			. $obUserFieldsSql->GetJoin("T.ID") . " "
			. (sizeof($arSqlSearch) ? " WHERE ".implode(" AND ", $arSqlSearch) : "") . " ";

		$strSql .= $strFrom;

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		for ($i = 0, $arSqlOrderCnt = count($arSqlOrder); $i < $arSqlOrderCnt; $i++)
		{
			if ($i == 0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		if (($nPageTop !== false) && is_numeric($nPageTop))
		{
			$strSql = $DB->TopSql($strSql, intval($nPageTop));
		}

		if (is_array($arParams) && array_key_exists("NAV_PARAMS", $arParams) && is_array($arParams["NAV_PARAMS"]))
		{
			$nTopCount = intval($arParams['NAV_PARAMS']['nTopCount']);
			if($nTopCount > 0)
			{
				$strSql = $DB->TopSql($strSql, $nTopCount);
				$res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("TASKS_TASK"));
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(T.ID) as C " . $strFrom);
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("TASKS_TASK"));
				$res->NavQuery($strSql, $res_cnt["C"], $arParams["NAV_PARAMS"]);
			}
		}
		else
		{
			$res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
			$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("TASKS_TASK"));
		}

		return $res;
	}


	function GetRootSubquery($arFilter=array())
	{
		global $USER;

		$userID = is_object($USER) ? intval($USER->GetID()) : 0;

		$arSqlSearch = array("(PT.ID = T.PARENT_ID)");

		if ($arFilter["SAME_GROUP_PARENT"] == "Y")
		{
			$arSqlSearch[] = "(PT.GROUP_ID = T.GROUP_ID 
				OR (PT.GROUP_ID IS NULL AND T.GROUP_ID IS NULL)
				OR (PT.GROUP_ID IS NULL AND T.GROUP_ID = 0)
				OR (PT.GROUP_ID = 0 AND T.GROUP_ID IS NULL)
			)";
		}
		unset($arFilter["ONLY_ROOT_TASKS"], $arFilter["SAME_GROUP_PARENT"]);

		$arSqlSearch = array_merge($arSqlSearch, CTasks::GetFilter($arFilter, "P"));

		$strSql = "
			SELECT
				'x'
			FROM
				b_tasks PT
			LEFT JOIN
				b_tasks_viewed PTV ON PTV.TASK_ID = PT.ID AND PTV.USER_ID = ".$userID."
			WHERE
				".implode(" AND ", $arSqlSearch)."
		";

		//echo $strSql;

		return $strSql;
	}


	public static function GetCount($arFilter=array(), $arParams = array())
	{
		/**
		 * @global CUser $USER
		 * @global CDatabase $DB
		 */
		global $DB, $USER;

		$bIgnoreDbErrors = false;

		if (isset($arParams['bIgnoreDbErrors']))
		{
			CTaskAssert::assert(is_bool($arParams['bIgnoreDbErrors']));
			$bIgnoreDbErrors = $arParams['bIgnoreDbErrors'];
		}

		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("TASKS_TASK", "T.ID");
		$obUserFieldsSql->SetFilter($arFilter);

		if (!is_array($arFilter))
			$arFilter = array();

		$userID = is_object($USER) ? intval($USER->GetID()) : 0;

		$arSqlSearch = CTasks::GetFilter($arFilter);

		$r = $obUserFieldsSql->GetFilter();
		if (strlen($r) > 0)
		{
			$arSqlSearch[] = "(".$r.")";
		}

		$strSql = "
			SELECT
				COUNT(T.ID) AS CNT
			FROM
				b_tasks T
			INNER JOIN b_user CU ON CU.ID = T.CREATED_BY
			INNER JOIN b_user RU ON RU.ID = T.RESPONSIBLE_ID
			LEFT JOIN
				b_tasks_viewed TV ON TV.TASK_ID = T.ID AND TV.USER_ID = ".$userID."
			".$obUserFieldsSql->GetJoin("T.ID")."
			".(sizeof($arSqlSearch) ? "WHERE ".implode(" AND ", $arSqlSearch) : "")."
		";

		$res = $DB->Query($strSql, $bIgnoreDbErrors, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $res;
	}


	function GetCountInt($arFilter=array())
	{
		$count = 0;

		$rsCount = CTasks::GetCount($arFilter);
		if ($arCount = $rsCount->Fetch())
		{
			$count = intval($arCount["CNT"]);
		}

		return $count;
	}


	function GetChildrenCount($arFilter, $arParentIDs)
	{
		global $DB, $USER;

		$obUserFieldsSql = new CUserTypeSQL;
		$obUserFieldsSql->SetEntity("TASKS_TASK", "T.ID");
		$obUserFieldsSql->SetFilter($arFilter);

		if (!is_array($arFilter))
			$arFilter = array();

		if (!$arParentIDs)
			return false;

		$arFilter["PARENT_ID"] = $arParentIDs;

		$userID = is_object($USER) ? intval($USER->GetID()) : 0;

		unset($arFilter["ONLY_ROOT_TASKS"]);
		$arSqlSearch = CTasks::GetFilter($arFilter);

		$r = $obUserFieldsSql->GetFilter();
		if (strlen($r) > 0)
		{
			$arSqlSearch[] = "(".$r.")";
		}

		$strSql = "
			SELECT
				T.PARENT_ID AS PARENT_ID,
				COUNT(T.ID) AS CNT
			FROM
				b_tasks T
			INNER JOIN b_user CU ON CU.ID = T.CREATED_BY
			INNER JOIN b_user RU ON RU.ID = T.RESPONSIBLE_ID
			LEFT JOIN
				b_tasks_viewed TV ON TV.TASK_ID = T.ID AND TV.USER_ID = ".$userID."
			".$obUserFieldsSql->GetJoin("T.ID")."
			".(sizeof($arSqlSearch) ? "WHERE ".implode(" AND ", $arSqlSearch) : "")."
			GROUP BY
				T.PARENT_ID
		";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $res;
	}


	public static function GetSubordinateSql($sAliasPrefix="", $arParams = array())
	{
		if (is_array($arParams) && isset($arParams['USER_ID']))
			$arDepsIDs = CTasks::GetSubordinateDeps($arParams['USER_ID']);
		else
			$arDepsIDs = CTasks::GetSubordinateDeps();

		if (sizeof($arDepsIDs))
		{
			$rsDepartmentField = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "USER", "FIELD_NAME" => "UF_DEPARTMENT"));
			if ($arDepartmentField = $rsDepartmentField->Fetch())
			{
				return CTasks::GetDeparmentSql($arDepsIDs, $sAliasPrefix);
			}
		}

		return false;
	}


	function GetDeparmentSql($arDepsIDs, $sAliasPrefix="")
	{
		global $DBType;

		if (!is_array($arDepsIDs))
		{
			$arDepsIDs = array(intval($arDepsIDs));
		}

		$rsDepartmentField = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "USER", "FIELD_NAME" => "UF_DEPARTMENT"));
		$cntOfDepartments = count($arDepsIDs);
		if ($cntOfDepartments && $arDepartmentField = $rsDepartmentField->Fetch())
		{
			if (
				($DBType === 'oracle')
				&& ($valuesLimit = 1000)
				&& ($cntOfDepartments > $valuesLimit)
			)
			{
				$arConstraints = array();
				$sliceIndex = 0;
				while ($sliceIndex < $cntOfDepartments)
				{
					$arConstraints[] = $sAliasPrefix . 'BUF1.VALUE_INT IN (' 
						. implode(',', array_slice($arDepsIDs, $sliceIndex, $valuesLimit))
						. ')';

					$sliceIndex += $valuesLimit;
				}

				$strConstraint = '(' . implode(' OR ', $arConstraints) . ')';
			}
			else
				$strConstraint = $sAliasPrefix . "BUF1.VALUE_INT IN (" . implode(",", $arDepsIDs) . ")";

			$strSql = "
				SELECT
					'x'
				FROM
					b_utm_user ".$sAliasPrefix."BUF1
				WHERE
					".$sAliasPrefix."BUF1.FIELD_ID = ".$arDepartmentField["ID"]."
				AND
					(" . $sAliasPrefix . "BUF1.VALUE_ID = " . $sAliasPrefix . "T.RESPONSIBLE_ID
						OR " . $sAliasPrefix . "BUF1.VALUE_ID = " . $sAliasPrefix . "T.CREATED_BY
					)
				AND
					" . $strConstraint . "
			";

			return $strSql;
		}

		return false;
	}


	function AddAccomplices($ID, $arAccompleces = array())
	{
		if ($arAccompleces)
		{
			$arAccompleces = array_unique($arAccompleces);
			foreach ($arAccompleces as $accomplice)
			{
				$arMember = array(
					"TASK_ID" => $ID,
					"USER_ID" => $accomplice,
					"TYPE" => "A"
				);
				$member = new CTaskMembers();
				$member->Add($arMember);
			}
		}
	}


	function AddAuditors($ID, $arAuditors = array())
	{
		if ($arAuditors)
		{
			$arAuditors = array_unique($arAuditors);
			foreach ($arAuditors as $auditor)
			{
				$arMember = array(
					"TASK_ID" => $ID,
					"USER_ID" => $auditor,
					"TYPE" => "U"
				);
				$member = new CTaskMembers();
				$member->Add($arMember);
			}
		}
	}


	function AddFiles($ID, $arFiles = array(), $arParams = array())
	{
		$arFilesIds = array();

		$userId = null;

		$bCheckRightsOnFiles = false;

		if (is_array($arParams))
		{
			if (isset($arParams['USER_ID']) && ($arParams['USER_ID'] > 0))
				$userId = (int) $arParams['USER_ID'];

			if (isset($arParams['CHECK_RIGHTS_ON_FILES']))
				$bCheckRightsOnFiles = $arParams['CHECK_RIGHTS_ON_FILES'];
		}

		if ($userId === null)
		{
			global $USER;
			$userId = is_object($USER) ? intval($USER->GetID()) : 1;
		}

		if ($arFiles)
		{
			foreach ($arFiles as $file)
				$arFilesIds[] = (int) $file;

			if (count($arFilesIds))
			{
				CTaskFiles::AddMultiple(
					$ID,
					$arFilesIds,
					array(
						'USER_ID'               => $userId,
						'CHECK_RIGHTS_ON_FILES' => $bCheckRightsOnFiles
					)
				);
			}
		}
	}


	function AddTags($ID, $USER_ID, $arTags = array())
	{
		if ($arTags)
		{
			if (!is_array($arTags))
			{
				$arTags = explode(",", $arTags);
			}
			$arTags = array_map("trim", $arTags);

			foreach ($arTags as $tag)
			{
				$arTag = array(
					"TASK_ID" => $ID,
					"USER_ID" => $USER_ID,
					"NAME" => $tag
				);
				$oTag = new CTaskTags();
				$oTag->Add($arTag);
			}
		}
	}


	function AddPrevious($ID, $arPrevious = array())
	{
		if ($arPrevious)
		{
			foreach ($arPrevious as $dependsOn)
			{
				$arDependsOn = array(
					"TASK_ID" => $ID,
					"DEPENDS_ON_ID" => $dependsOn
				);
				$oDependsOn = new CTaskDependence();
				$oDependsOn->Add($arDependsOn);
			}
		}
	}


	function Index($arTask, $tags)
	{
		if (CModule::IncludeModule("search"))
		{
			if (is_array($tags))
			{
				$tags = implode(",", $tags);
			}
			if ($arTask["GROUP_ID"] > 0)
			{
				$path = str_replace("#group_id#", $arTask["GROUP_ID"], COption::GetOptionString("tasks", "paths_task_group_entry", "/workgroups/group/#group_id#/tasks/task/view/#task_id#/", $arTask["SITE_ID"]));
			}
			else
			{
				$path = str_replace("#user_id#", $arTask["RESPONSIBLE_ID"], COption::GetOptionString("tasks", "paths_task_user_entry", "/company/personal/user/#user_id#/tasks/task/view/#task_id#/", $arTask["SITE_ID"]));
			}
			$path = str_replace("#task_id#", $arTask["ID"], $path);

			$arSearchIndex = array(
				"LAST_MODIFIED" => $arTask["CHANGED_DATE"] ? $arTask["CHANGED_DATE"] : $arTask["CREATED_DATE"],
				"TITLE" => $arTask["TITLE"],
				"BODY" => strip_tags($arTask["DESCRIPTION"]) ? strip_tags($arTask["DESCRIPTION"]) : $arTask["TITLE"],
				"TAGS" => $tags,
				"URL" => $path,
				"SITE_ID" => $arTask["SITE_ID"],
				"PERMISSIONS" => CTasks::__GetSearchPermissions($arTask),
			);

			$entity_type	= ($arTask["GROUP_ID"] != 0) ? "G" : "U";
			$entity_name	= ($entity_type == "G") ? "socnet_group" : "socnet_user";
			$entity_id		= ($entity_type == "G") ? $arTask["GROUP_ID"] : $arTask["RESPONSIBLE_ID"];
			$feature		= ($entity_type == "G") ? "view": "view_all";

			$arSearchIndex["PARAMS"] = array(
				"feature_id" => "S".$entity_type."_".$entity_id."_tasks_".$feature,
				$entity_name => $entity_id,
			);

			CSearch::Index("tasks", $arTask["ID"], $arSearchIndex, true);
		}
	}


	function OnSearchReindex($NS=array(), $oCallback=NULL, $callback_method="")
	{
		$arResult = array();
		$arOrder  = array('ID' => 'ASC');
		$arFilter = array();

		if (isset($NS['MODULE']) && ($NS['MODULE'] === 'tasks') 
			&& isset($NS['ID']) && ($NS['ID'] > 0)
		)
		{
			$arFilter['>ID'] = (int) $NS['ID'];
		}
		else
			$arFilter['>ID'] = 0;


		$rsTasks = CTasks::GetList($arOrder, $arFilter);
		while ($arTask = $rsTasks->Fetch())
		{
			$rsTags = CTaskTags::GetList(array(), array("TASK_ID" => $arTask["ID"]));
			$arTags = array();
			while ($arTag = $rsTags->Fetch())
			{
				$arTags[] = $arTag["NAME"];
			}

			$arTask["ACCOMPLICES"] = $arTask["AUDITORS"] = array();
			$rsMembers = CTaskMembers::GetList(array(), array("TASK_ID" => $arTask["ID"]));
			while ($arMember = $rsMembers->Fetch())
			{
				if ($arMember["TYPE"] == "A")
				{
					$arTask["ACCOMPLICES"][] = $arMember["USER_ID"];
				}
				elseif ($arMember["TYPE"] == "U")
				{
					$arTask["AUDITORS"][] = $arMember["USER_ID"];
				}
			}

			if ($arTask["GROUP_ID"] > 0)
			{
				$path = str_replace("#group_id#", $arTask["GROUP_ID"], COption::GetOptionString("tasks", "paths_task_group_entry", "/workgroups/group/#group_id#/tasks/task/view/#task_id#/", $arTask["SITE_ID"]));
			}
			else
			{
				$path = str_replace("#user_id#", $arTask["RESPONSIBLE_ID"], COption::GetOptionString("tasks", "paths_task_user_entry", "/company/personal/user/#user_id#/tasks/task/view/#task_id#/", $arTask["SITE_ID"]));
			}
			$path = str_replace("#task_id#", $arTask["ID"], $path);

			$arPermissions = CTasks::__GetSearchPermissions($arTask);
			$Result = array(
				"ID" => $arTask["ID"],
				"LAST_MODIFIED" => $arTask["CHANGED_DATE"] ? $arTask["CHANGED_DATE"] : $arTask["CREATED_DATE"],
				"TITLE" => $arTask["TITLE"],
				"BODY" => strip_tags($arTask["DESCRIPTION"]) ? strip_tags($arTask["DESCRIPTION"]) : $arTask["TITLE"],
				"TAGS" => implode(",", $arTags),
				"URL" => $path,
				"SITE_ID" => $arTask["SITE_ID"],
				"PERMISSIONS" => $arPermissions,
			);

			if ($oCallback)
			{
				$index_res = call_user_func(array($oCallback, $callback_method), $Result);
				if(!$index_res)
					return $Result["ID"];
			}
			else
				$arResult[] = $Result;

			CTasks::UpdateForumTopicIndex($arTask["FORUM_TOPIC_ID"], "U", $arTask["RESPONSIBLE_ID"], "tasks", "view_all", $path, $arPermissions, $arTask["SITE_ID"]);
		}

		if ($oCallback)
			return false;

		return $arResult;
	}


	function UpdateForumTopicIndex($topic_id, $entity_type, $entity_id, $feature, $operation, $path, $arPermissions, $siteID)
	{
		global $DB;

		if(!CModule::IncludeModule("forum"))
			return;

		$topic_id = intval($topic_id);

		$rsForumTopic = $DB->Query("SELECT FORUM_ID FROM b_forum_topic WHERE ID = ".$topic_id);
		$arForumTopic = $rsForumTopic->Fetch();
		if(!$arForumTopic)
			return;

		CSearch::ChangePermission("forum", $arPermissions, false, $arForumTopic["FORUM_ID"], $topic_id);

		$rsForumMessages = $DB->Query("
			SELECT ID
			FROM b_forum_message
			WHERE TOPIC_ID = ".$topic_id."
		");
		while($arMessage = $rsForumMessages->Fetch())
		{
			CSearch::ChangeSite("forum", array($siteID => $path), $arMessage["ID"]);
		}

		$arParams = array(
			"feature_id" => "S".$entity_type."_".$entity_id."_".$feature."_".$operation,
			"socnet_user" => $entity_id,
		);

		CSearch::ChangeIndex("forum", array("PARAMS" => $arParams), false, $arForumTopic["FORUM_ID"], $topic_id);
	}


	public static function __GetSearchPermissions($arTask)
	{
		$arPermissions = array();

		if (!isset($arTask['ACCOMPLICES']) || !isset($arTask['AUDITORS']))
		{
			if (!isset($arTask['ACCOMPLICES']))
				$arTask['ACCOMPLICES'] = array();
			if (!isset($arTask['AUDITORS']))
				$arTask['AUDITORS'] = array();
			$rsMembers = CTaskMembers::GetList(array(), array("TASK_ID" => $arTask["ID"]));
			while ($arMember = $rsMembers->Fetch())
			{
				if ($arMember["TYPE"] == "A")
					$arTask["ACCOMPLICES"][] = $arMember["USER_ID"];
				elseif ($arMember["TYPE"] == "U")
					$arTask["AUDITORS"][] = $arMember["USER_ID"];
			}
		}

		if ($arTask["GROUP_ID"] > 0 && CModule::IncludeModule("socialnetwork"))
		{
			$prefix = "SG".$arTask["GROUP_ID"]."_";
			$letter = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $arTask["GROUP_ID"], "tasks", "view_all");
			switch($letter)
			{
				case "N"://All
					$arPermissions[] = 'G2';
					break;
				case "L"://Authorized
					$arPermissions[] = 'AU';
					break;
				case "K"://Group members includes moderators and admins
					$arPermissions[] = $prefix.'K';
				case "E"://Moderators includes admins
					$arPermissions[] = $prefix.'E';
				case "A"://Admins
					$arPermissions[] = $prefix.'A';
					break;
			}
		}

		if (!in_array("G2", $arPermissions) && !in_array("AU", $arPermissions))
		{
			if (!$arTask["ACCOMPLICES"])
				$arTask["ACCOMPLICES"] = array();

			if (!$arTask["AUDITORS"])
				$arTask["AUDITORS"] = array();

			$arParticipants = array_unique(array_merge(array($arTask["CREATED_BY"], $arTask["RESPONSIBLE_ID"]), $arTask["ACCOMPLICES"], $arTask["AUDITORS"]));
			foreach($arParticipants as $userId)
				$arPermissions[] = "U".$userId;

			$arDepartments = array();

			$arSubUsers = array_unique(array($arTask['RESPONSIBLE_ID'], $arTask['CREATED_BY']));

			foreach ($arSubUsers as $subUserId)
			{
				$arUserDepartments = CTasks::GetUserDepartments($subUserId);

				if (is_array($arUserDepartments) && count($arUserDepartments))
					$arDepartments = array_merge($arDepartments, $arUserDepartments);
			}

			$arDepartments = array_unique($arDepartments);
			$arManagersTmp = CTasks::GetDepartmentManagers($arDepartments);

			if (is_array($arManagersTmp))
			{
				$arManagers = array_keys($arManagersTmp);

				// Remove $arSubUsers from $arManagers
				$arManagers = array_diff($arManagers, $arSubUsers);

				foreach($arManagers as $userId)
				{
					if (!in_array("U".$userId, $arPermissions))
						$arPermissions[] = "U".$userId;
				}
			}
		}

		return $arPermissions;
	}


	/**
	 * Agent handler for repeating tasks.
	 * Create new task based on given template.
	 * 
	 * @param integer $templateId - id of task template
	 * @param integer $flipFlop - this param needs for prevent duplicate names 
	 * 		of agent (when adding agent, there is still exists current agent,
	 * 		which will be removed later, when our function returns empty string),
	 * 		must be 1 or 0.
	 * 
	 * @return string empty string.
	 */
	public static function RepeatTaskByTemplateId ($templateId, $flipFlop = 0)
	{
		global $DB;

		$curFlipFlop = (int) $flipFlop;
		
		if ($curFlipFlop === 0)
			$newFlipFlop = 1;
		else
			$newFlipFlop = 0;

		$templateId = (int) $templateId;
		$arFilter   = array('ID' => $templateId);
		$rsTemplate = CTaskTemplates::GetList(array(), $arFilter);
		$arTemplate = $rsTemplate->Fetch();

		if ( ! $arTemplate )
			return ('');	// nothing to do

		if ($arTemplate['REPLICATE'] !== 'Y')
			return ('');	// nothing to do

		unset($arTemplate['STATUS']);

		$arFields = $arTemplate;

		$arFields['CREATED_DATE'] = date(
			$DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), 
			time() + CTimeZone::GetOffset()
		);

		$arFields['ACCOMPLICES']  = unserialize($arFields['ACCOMPLICES']);
		$arFields['AUDITORS']     = unserialize($arFields['AUDITORS']);
		$arFields['TAGS']         = unserialize($arFields['TAGS']);
		$arFields['FILES']        = unserialize($arFields['FILES']);
		$arFields['DEPENDS_ON']   = unserialize($arFields['DEPENDS_ON']);
		$arFields['REPLICATE']    = 'N';
		$arFields['CHANGED_BY']   = $arFields['CREATED_BY'];
		$arFields['CHANGED_DATE'] = $arFields['CREATED_DATE'];

		if ( ! $arFields['ACCOMPLICES'] )
			$arFields['ACCOMPLICES'] = array();

		if ( ! $arFields['AUDITORS'] )
			$arFields['AUDITORS'] = array();

		unset($arFields['ID'], $arFields['REPLICATE'], $arFields['REPLICATE_PARAMS']);

		if ($arFields['MULTITASK'] == 'Y' && sizeof($arFields['RESPONSIBLES']) > 0)
			$arFields['RESPONSIBLE_ID'] = $arFields['CREATED_BY'];

		if ($arTemplate['DEADLINE_AFTER'])
		{
			$deadlineAfter = $arTemplate['DEADLINE_AFTER'] / (24 * 60 * 60);
			$deadline = strtotime(date('Y-m-d 00:00') . ' +' . $deadlineAfter . ' days');
			$arFields['DEADLINE'] = date(
				$DB->DateFormatToPHP(CSite::GetDateFormat('SHORT')), 
				$deadline
			);
		}

		$arFields['FORKED_BY_TEMPLATE_ID'] = $templateId;

		$task = new CTasks();
		$taskId = $task->Add($arFields, array('SPAWNED_BY_AGENT' => true));

		$arFields['RESPONSIBLES'] = unserialize($arFields['RESPONSIBLES']);

		if (($taskId) && is_array($arFields['RESPONSIBLES']))
		{
			foreach ($arFields['RESPONSIBLES'] as $responsible)
			{
				$arFields['RESPONSIBLE_ID'] = $responsible;

				$task->Add($arFields);
			}
		}

		$arTemplate['REPLICATE_PARAMS'] = unserialize($arTemplate['REPLICATE_PARAMS']);

		$nextTime = CTasks::GetNextTime($arTemplate['REPLICATE_PARAMS']);
		if ($nextTime)
		{
			CTimeZone::Disable();

			$currentAgentCallStr = 'CTasks::RepeatTaskByTemplateId(' 
				. $templateId . ', ' . $curFlipFlop . ');';

			$newAgentCallStr     = 'CTasks::RepeatTaskByTemplateId(' 
				. $templateId . ', ' . $newFlipFlop . ');';

			CAgent::RemoveAgent($currentAgentCallStr);
			CAgent::AddAgent(
				$newAgentCallStr, 
				'tasks', 
				'N', 		// is periodic?
				86400, 		// interval
				$nextTime, 	// datecheck
				'Y', 		// is active?
				$nextTime	// next_exec
			);

			CTimeZone::Enable();
		}

		return ('');
	}


	/**
	 * @deprecated
	 *
	 * This function is deprecated and strongly discouraged to be used.
	 * But it will not be removed, because some agents can be still active for
	 * using this function in future for at least one year.
	 * Current date is: 06 Oct 2012, Sat. Code written, but updater not builded.
	 *
	 * @param $TASK_ID
	 * @param string $time
	 * @return string originally always returns an empty string
	 */
	function RepeatTask($TASK_ID, $time="")
	{
		$rsTemplate = CTaskTemplates::GetList(
			array(), 
			array('TASK_ID' => (int) $TASK_ID)
		);

		if ( ! ($arTemplate = $rsTemplate->Fetch()) )
			return ('');

		// Redirect call to new function
		if (isset($arTemplate['ID']) && ($arTemplate['ID'] > 0))
			self::RepeatTaskByTemplateId( (int) $arTemplate['ID'] );

		return ('');
	}


	public static function GetNextTime($arParams)
	{
		global $DB;

		if (!is_array($arParams))
		{
			return false;
		}

		$arPeriods = array("daily", "weekly", "monthly", "yearly");
		$arOrdinals = array("first", "second", "third", "fourth", "last");
		$arWeekDays = array("mon", "tue", "wed", "thu", "fri", "sat", "sun");

		$startDate = $arParams["START_DATE"];
		$endDate = $arParams["END_DATE"];

		if ($startDate)
		{
			$startDate = date("Y-m-d 00:00", MakeTimeStamp($startDate));
		}
		if ($endDate)
		{
			$endDate = date("Y-m-d 00:00", MakeTimeStamp($endDate));
		}

		if ($startDate < date("Y-m-d 00:00"))
		{
			$startDate = date("Y-m-d 00:00");
		}

		$type = in_array($arParams["PERIOD"], $arPeriods) ? $arParams["PERIOD"] : "daily";

		switch ($type)
		{
			case "daily":
				$workDays = $arParams["WORKDAY_ONLY"] == "Y";
				$num = $arParams["EVERY_DAY"];

				if ($workDays)
				{
					$startWeekDay = date("N", strtotime($startDate));
					if ($startWeekDay + $num == 6)
					{
						$num += 2;
					}
					elseif ($startWeekDay + $num == 7)
					{
						$num += 1;
					}
				}
				$date = strtotime($startDate." +".$num." days");
				break;

			case "weekly":
				$num = $arParams["EVERY_WEEK"];
				$days = is_array($arParams["WEEK_DAYS"]) && sizeof(array_filter($arParams["WEEK_DAYS"])) ? $arParams["WEEK_DAYS"] : array(1);

				$currentDay = date("N", strtotime($startDate));
				$nextDay = false;
				foreach ($days as $day)
				{
					if ($day > $currentDay)
					{
						$nextDay = $day;
						break;
					}
				}
				if ($nextDay)
				{
					$date = strtotime($startDate." +".($nextDay - $currentDay)." days");
				}
				else
				{
					$date = strtotime($startDate." +".$num." weeks ".($days[0] - $currentDay)." days");
				}
				break;

			case "monthly":
				$subType = $arParams["MONTHLY_TYPE"] == 2 ? "weekday" : "monthday";
				if ($subType == "weekday")
				{
					$ordinal = array_key_exists($arParams["MONTHLY_WEEK_DAY_NUM"], $arOrdinals) ? $arOrdinals[$arParams["MONTHLY_WEEK_DAY_NUM"]] : $arOrdinals[0];
					$weekDay = array_key_exists($arParams["MONTHLY_WEEK_DAY"], $arWeekDays) ? $arWeekDays[$arParams["MONTHLY_WEEK_DAY"]] : $arWeekDays[0];
					$num = intval($arParams["MONTHLY_MONTH_NUM_2"]) > 0 ? intval($arParams["MONTHLY_MONTH_NUM_2"]) : 1;

					$date = strtotime($ordinal." ".$weekDay." of this month");
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime($startDate." +".$num." months");
						$date = strtotime($ordinal." ".$weekDay." of ".date("Y-m-d", $date));
					}
				}
				else
				{
					$day = intval($arParams["MONTHLY_DAY_NUM"]) >= 1 && intval($arParams["MONTHLY_DAY_NUM"]) <= 31 ? intval($arParams["MONTHLY_DAY_NUM"]) : 1;
					$num = intval($arParams["MONTHLY_MONTH_NUM_1"]) > 0 ? intval($arParams["MONTHLY_MONTH_NUM_1"]) : 1;

					$date = strtotime(date("Y-m-".sprintf("%02d", $day), strtotime($startDate)));
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime($startDate." +".$num." months");
						$date = strtotime(date("Y-m-".sprintf("%02d", $day), $date));
					}
				}
				break;

			case "yearly":
				$subType = $arParams["YEARLY_TYPE"] == 2 ? "weekday" : "monthday";
				if ($subType == "weekday")
				{
					$ordinal = array_key_exists($arParams["YEARLY_WEEK_DAY_NUM"], $arOrdinals) ? $arOrdinals[$arParams["YEARLY_WEEK_DAY_NUM"]] : $arOrdinals[0];
					$weekDay = array_key_exists($arParams["YEARLY_WEEK_DAY"], $arWeekDays) ? $arWeekDays[$arParams["YEARLY_WEEK_DAY"]] : $arWeekDays[0];
					$month = intval($arParams["YEARLY_MONTH_2"]) >= 0 && intval($arParams["YEARLY_MONTH_2"]) < 12 ? intval($arParams["YEARLY_MONTH_2"]) : 0;
					$month += 1;

					$date = strtotime($ordinal." ".$weekDay." of ".date("Y", strtotime($startDate))."-".sprintf("%02d", $month)."-01");
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime($ordinal." ".$weekDay." of ".(date("Y", strtotime($startDate)) + 1)."-".sprintf("%02d", $month)."-01");
					}
				}
				else
				{
					$day = intval($arParams["YEARLY_DAY_NUM"]) >= 1 && intval($arParams["YEARLY_DAY_NUM"]) <= 31 ? intval($arParams["YEARLY_DAY_NUM"]) : 1;
					$month = intval($arParams["YEARLY_MONTH_1"]) >= 0 && intval($arParams["YEARLY_MONTH_1"]) < 12 ? intval($arParams["YEARLY_MONTH_1"]) : 0;
					$month += 1;

					$date = strtotime(date("Y", strtotime($startDate))."-".sprintf("%02d", $month)."-".sprintf("%02d", $day));
					if (strtotime($startDate) >= $date)
					{
						$date = strtotime((date("Y", strtotime($startDate)) + 1)."-".sprintf("%02d", $month)."-".sprintf("%02d", $day));
					}
				}
				break;
		}

		if ($date)
		{
			if ($endDate && $date > strtotime($endDate))
			{
				return false;
			}
			else
			{
				return date($DB->DateFormatToPHP(FORMAT_DATETIME), $date);
			}
		}
		else
		{
			return false;
		}
	}


	public static function CanGivenUserDelete($userId, $taskCreatedBy, $taskGroupId, $site_id = SITE_ID)
	{
		$userId = (int) $userId;
		$taskGroupId = (int) $taskGroupId;

		$site_id = null;	// not used, left in function declaration for backward compatibility

		if ($userId <= 0)
			throw new TasksException();

		if (
			CTasksTools::IsAdmin($userId)
			|| CTasksTools::IsPortalB24Admin($userId)
			|| ($userId == $taskCreatedBy)
		)
		{
			return (true);
		}
		elseif (($taskGroupId > 0) && CModule::IncludeModule('socialnetwork'))
		{
			return (boolean) CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $taskGroupId, "tasks", "delete_tasks");
		}

		return false;
	}


	public static function CanCurrentUserDelete($task, $site_id = SITE_ID)
	{
		global $USER;
		if (!$userID = $USER->GetID())
		{
			return false;
		}

		return (self::CanGivenUserDelete($userID, $task['CREATED_BY'], $task['GROUP_ID'], $site_id));
	}


	public static function CanGivenUserEdit($userId, $taskCreatedBy, $taskGroupId, $site_id = SITE_ID)
	{
		$userId = (int) $userId;
		$taskGroupId = (int) $taskGroupId;

		$site_id = null;	// not used, left in function declaration for backward compatibility

		if ($userId <= 0)
			throw new TasksException();

		if (
			CTasksTools::IsAdmin($userId)
			|| CTasksTools::IsPortalB24Admin($userId)
			|| ($userId == $taskCreatedBy)
		)
		{
			return (true);
		}
		elseif (($taskGroupId > 0) && CModule::IncludeModule('socialnetwork'))
		{
			return (boolean) CSocNetFeaturesPerms::CanPerformOperation($userId, SONET_ENTITY_GROUP, $taskGroupId, "tasks", "edit_tasks");
		}

		return false;
	}


	public static function CanCurrentUserEdit($task, $site_id = SITE_ID)
	{
		global $USER;
		if (!$userID = $USER->GetID())
		{
			return false;
		}

		return (self::CanGivenUserEdit($userID, $task['CREATED_BY'], $task['GROUP_ID'], $site_id));
	}


	function UpdateViewed($TASK_ID, $USER_ID)
	{
		global $DB;

		$USER_ID = intval($USER_ID);
		$TASK_ID = intval($TASK_ID);

		$rsViewed = $DB->Query("SELECT 'x' FROM b_tasks_viewed WHERE TASK_ID = ".$TASK_ID." AND USER_ID = ".$USER_ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($rsViewed->Fetch())
		{
			$DB->Query("UPDATE b_tasks_viewed SET VIEWED_DATE = ".$DB->CurrentTimeFunction()." WHERE TASK_ID = ".$TASK_ID." AND USER_ID = ".$USER_ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		else
		{
			$DB->Add("b_tasks_viewed", array("ID" => 1, "TASK_ID" => $TASK_ID, "USER_ID" => $USER_ID), array(), "tasks");
		}
	}


	function GetUpdatesCount($arViewed)
	{
		global $DB, $USER;
		if ($userID = $USER->GetID())
		{
			$arSqlSearch = array();
			$arUpdatesCount = array();
			foreach($arViewed as $key=>$val)
			{
				$arSqlSearch[] = "(CREATED_DATE > ".$DB->CharToDateFunction($val)." AND USER_ID != ".$userID." AND TASK_ID = ".intval($key).")";
				$arUpdatesCount[$key] = 0;
			}
			if (sizeof($arSqlSearch))
			{
				$strSql = "
					SELECT
						TL.TASK_ID AS TASK_ID,
						COUNT(TL.TASK_ID) AS CNT
					FROM
						b_tasks_log TL
					WHERE
						".implode(" OR ", $arSqlSearch)."
					GROUP BY
						TL.TASK_ID
				";

				$rsUpdatesCount = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				while($arUpdate = $rsUpdatesCount->Fetch())
				{
					$arUpdatesCount[$arUpdate["TASK_ID"]] = $arUpdate["CNT"];
				}

				return $arUpdatesCount;
			}
		}

		return false;
	}


	function GetFilesCount($arTasksIDs)
	{
		global $DB;

		$arFilesCount = array();

		$arTasksIDs = array_filter($arTasksIDs);

		if (sizeof($arTasksIDs))
		{
			$strSql = "
				SELECT
					TF.TASK_ID,
					COUNT(TF.FILE_ID) AS CNT
				FROM
					b_tasks_file TF
				WHERE
					TF.TASK_ID IN (".implode(",", $arTasksIDs).")
			";
			$rsFilesCount = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			while($arFile = $rsFilesCount->Fetch())
			{
				$arFilesCount[$arFile["TASK_ID"]] = $arFile["CNT"];
			}
		}

		return $arFilesCount;
	}


	function CanCurrentUserViewTopic($topicID)
	{
		global $USER;
		$isSocNetModuleIncluded = CModule::IncludeModule("socialnetwork");

		if (($topicID = intval($topicID)) && is_object($USER))
		{
			if ($USER->IsAdmin() || CTasksTools::IsPortalB24Admin())
				return true;

			$rsTask = $res = CTasks::GetList(array(), array("FORUM_TOPIC_ID" => $topicID));
			if ($arTask = $rsTask->Fetch())
			{
				if ( ((int)$arTask['GROUP_ID']) > 0 )
				{
					if (in_array(CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $arTask["GROUP_ID"], "tasks", "view_all"), array("G2", "AU")))
						return true;
					elseif (
						$isSocNetModuleIncluded
						&& (false !== CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $arTask['GROUP_ID'], 'tasks', 'view_all'))
					)
					{
						return (true);
					}
				}

				$arTask["ACCOMPLICES"] = $arTask["AUDITORS"] = array();
				$rsMembers = CTaskMembers::GetList(array(), array("TASK_ID" => $arTask["ID"]));
				while ($arMember = $rsMembers->Fetch())
				{
					if ($arMember["TYPE"] == "A")
					{
						$arTask["ACCOMPLICES"][] = $arMember["USER_ID"];
					}
					elseif ($arMember["TYPE"] == "U")
					{
						$arTask["AUDITORS"][] = $arMember["USER_ID"];
					}
				}

				if (in_array($USER->GetID(), array_unique(array_merge(array($arTask["CREATED_BY"], $arTask["RESPONSIBLE_ID"]), $arTask["ACCOMPLICES"], $arTask["AUDITORS"]))))
					return true;


				$dbRes = CUser::GetList($by='ID', $order='ASC', array('ID' => $arTask["RESPONSIBLE_ID"]), array('SELECT' => array('UF_DEPARTMENT')));

				if (($arRes = $dbRes->Fetch()) && is_array($arRes['UF_DEPARTMENT']) && count($arRes['UF_DEPARTMENT']) > 0)
					if (in_array($USER->GetID(), array_keys(CTasks::GetDepartmentManagers($arRes['UF_DEPARTMENT'], $arTask["RESPONSIBLE_ID"]))))
						return true;
			}
		}

		return false;
	}


	public static function GetUserDepartments($USER_ID)
	{
		static $cache = array();
		$USER_ID = (int) $USER_ID;

		if (!isset($cache[$USER_ID]))
		{
			$dbRes = CUser::GetList($by='ID', $order='ASC', array('ID' => $USER_ID), array('SELECT' => array('UF_DEPARTMENT')));

			if ($arRes = $dbRes->Fetch())
				$cache[$USER_ID] = $arRes['UF_DEPARTMENT'];
			else
				$cache[$USER_ID] = false;
		}

		return $cache[$USER_ID];
	}


	public static function OnBeforeUserDelete($inUserID)
	{
		global $DB, $APPLICATION;

		$bCanDelete = false;	// prohibit user deletion

		$userID = (int) $inUserID;
		if ( ! ($userID > 0) )
		{
			$APPLICATION->ThrowException(GetMessage('TASKS_BAD_USER_ID'));
			return (false);
		}

		$strSql =
			"SELECT ID AS TASK_ID
			FROM b_tasks 
			WHERE CREATED_BY = $userID 
				OR RESPONSIBLE_ID = $userID
			
			UNION
			
			SELECT TASK_ID 
			FROM b_tasks_member 
			WHERE USER_ID = $userID";
		
		$result = $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);
		if ($result === false)
		{
			$APPLICATION->ThrowException('EA_SQL_ERROR_OCCURED');
			return (false);
		}

		$arResult = $result->Fetch();
		
		// permit user deletion only when there is no tasks
		if ($arResult === false)
			$bCanDelete = true;
		else
			$APPLICATION->ThrowException(GetMessage('TASKS_ERR_USER_IN_USE'));

		return ($bCanDelete);
	}


	public static function OnUserDelete($USER_ID)
	{
		global $CACHE_MANAGER, $DB;
		$USER_ID = intval($USER_ID);
		$strSql = "
			SELECT RESPONSIBLE_ID AS USER_ID FROM b_tasks WHERE CREATED_BY = ".$USER_ID." AND CREATED_BY != RESPONSIBLE_ID
			UNION
			SELECT CREATED_BY AS USER_ID FROM b_tasks WHERE RESPONSIBLE_ID = ".$USER_ID." AND CREATED_BY != RESPONSIBLE_ID
			UNION
			SELECT USER_ID FROM b_tasks_member WHERE TASK_ID IN (SELECT TASK_ID FROM b_tasks_member WHERE USER_ID = ".$USER_ID.")
		";
		$result = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($arResult = $result->Fetch())
		{
			$CACHE_MANAGER->ClearByTag("tasks_user_".$arResult["USER_ID"]);
		}
	}


	public static function EmitPullWithTagPrefix($arRecipients, $tagPrefix, $cmd, $arParams)
	{
		if ( ! is_array($arRecipients) )
			throw new Exception('EA_PARAMS');

		$arRecipients = array_unique($arRecipients);

		if ( ! CModule::IncludeModule('pull') )
			return;

		/*
		$arEventData = array(
			'module_id' => 'tasks',
			'command'   => 'notify',
			'params'    => CIMNotify::GetFormatNotify(
				array(
					'ID' => -3
				)
			),
		);
		*/

		$bWasFatalError = false;

		foreach ($arRecipients as $userId)
		{
			$userId = (int) $userId;

			if ($userId < 1)
			{
				$bWasFatalError = true;
				continue;	// skip invalid items
			}

			//CPullStack::AddByUser($userId, $arEventData);
			CPullWatch::AddToStack(
				$tagPrefix . $userId, 
				array(
					'module_id'  => 'tasks',
					'command'    => $cmd,
					'params'     => $arParams
				)
			);
		}

		if ($bWasFatalError)
			throw new Exception();
	}


	public static function EmitPullWithTag($arRecipients, $tag, $cmd, $arParams)
	{
		if ( ! is_array($arRecipients) )
			throw new Exception('EA_PARAMS');

		$arRecipients = array_unique($arRecipients);

		if ( ! CModule::IncludeModule('pull') )
			return;

		$bWasFatalError = false;

		foreach ($arRecipients as $userId)
		{
			$userId = (int) $userId;

			if ($userId < 1)
			{
				$bWasFatalError = true;
				continue;	// skip invalid items
			}

			//CPullStack::AddByUser($userId, $arEventData);
			CPullWatch::AddToStack(
				$tag, 
				array(
					'module_id'  => 'tasks',
					'command'    => $cmd,
					'params'     => $arParams
				)
			);
		}

		if ($bWasFatalError)
			throw new Exception();
	}


	/**
	 * Get list of IDs groups, which contains tasks where given user is member
	 *
	 * @param integer $userId
	 * @throws TasksException
	 * @return array
	 */
	public static function GetGroupsWithTasksForUser($userId)
	{
		global $DB;

		$userId = (int) $userId;

		$rc = $DB->Query(
			"SELECT GROUP_ID 
			FROM b_tasks T
			WHERE (
				T.CREATED_BY = $userId 
				OR T.RESPONSIBLE_ID = $userId 
				OR EXISTS(
					SELECT 'x' 
					FROM b_tasks_member TM 
					WHERE TM.TASK_ID = T.ID 
						AND TM.USER_ID = $userId
				)
			)
			GROUP BY GROUP_ID
			"
		);

		if ( ! $rc )
			throw new TasksException();

		$arGroups = array();

		while ($ar = $rc->Fetch())
			$arGroups[] = (int) $ar['GROUP_ID'];

		return (array_unique($arGroups));
	}


	/**
	 * This is experimental code, don't relay on it.
	 * It can be removed or changed in future without any notifications.
	 * 
	 * Use CTaskItem::getAllowedTaskActions() and CTaskItem::getAllowedTaskActionsAsStrings() instead.
	 * 
	 * @deprecated
	 */
	public static function GetAllowedActions($arTask, $userId = null)
	{
		$arAllowedActions = array();

		if ($userId === null)
		{
			global $USER;
			$curUserId = (int) $USER->GetID();
		}
		else
			$curUserId = (int) $userId;

		$arSubDeps = CTasks::GetSubordinateDeps($curUserId);

		if ( ((int) $arTask['RESPONSIBLE_ID']) === $curUserId)
		{
			if ($arTask['REAL_STATUS'] == CTasks::STATE_NEW)
			{
				$arAllowedActions[] = array(
					'public_name' => 'accept',
					'system_name' => 'accept',
					'id'          => CTaskItem::ACTION_ACCEPT
				);

				$arAllowedActions[] = array(
					'public_name' => 'decline',
					'system_name' => 'decline',
					'id'          => CTaskItem::ACTION_DECLINE
				);
			}

			if (
				in_array(
					(int) $arTask['REAL_STATUS'], 
					array(
						CTasks::STATE_PENDING, 
						CTasks::STATE_IN_PROGRESS,
						CTasks::STATE_DEFERRED
					),
					true
				)
			)
			{
				$arAllowedActions[] = array(
					'public_name' => 'close',
					'system_name' => 'close',
					'id'          => CTaskItem::ACTION_COMPLETE
				);
			}

			if (
				in_array(
					(int) $arTask['REAL_STATUS'], 
					array(
						CTasks::STATE_NEW, 
						CTasks::STATE_PENDING,
						CTasks::STATE_DEFERRED
					),
					true
				)
			)
			{
				$arAllowedActions[] = array(
					'public_name' => 'start',
					'system_name' => 'start',
					'id'          => CTaskItem::ACTION_START
				);
			}

			if (
				( ((int) $arTask['CREATED_BY']) !== $curUserId )
				&& sizeof($arSubDeps)
			)
			{
				$arAllowedActions[] = array(
					'public_name' => 'delegate',
					'system_name' => 'delegate',
					'id'          => CTaskItem::ACTION_DELEGATE
				);
			}
		}

		if ( ((int) $arTask['CREATED_BY']) === $curUserId )
		{
			if ($arTask['REAL_STATUS'] == CTasks::STATE_SUPPOSEDLY_COMPLETED)
			{
				$arAllowedActions[] = array(
					'public_name' => 'approve',
					'system_name' => 'close',
					'id'          => CTaskItem::ACTION_APPROVE
				);

				$sysName = null;
				if ($arTask['SUBORDINATE'] === 'Y')
					$sysName = 'accept';
				else
					$sysName = 'renew';

				$arAllowedActions[] = array(
					'public_name' => 'redo',
					'system_name' => $sysName,
					'id'          => CTaskItem::ACTION_DISAPPROVE
				);
			}
		}

		if (CTasks::CanGivenUserDelete($curUserId, $arTask['CREATED_BY'], $arTask['GROUP_ID']))
		{
			$arAllowedActions[] = array(
				'public_name' => 'remove',
				'system_name' => 'remove',
				'id'          => CTaskItem::ACTION_REMOVE
			);
		}

		if (CTasks::CanGivenUserEdit($curUserId, $arTask['CREATED_BY'], $arTask['GROUP_ID']))
		{
			$arAllowedActions[] = array(
				'public_name' => 'edit',
				'system_name' => 'edit',
				'id'          => CTaskItem::ACTION_EDIT
			);
		}

		if (
			($arTask['RESPONSIBLE_ID'] != $curUserId)
			&& ($arTask['CREATED_BY'] == $curUserId)
			&& in_array(
				(int) $arTask['REAL_STATUS'], 
				array(
					CTasks::STATE_PENDING,
					CTasks::STATE_IN_PROGRESS,
					CTasks::STATE_DEFERRED,
					CTasks::STATE_DECLINED
				),
				true
			)
		)
		{
			$arAllowedActions[] = array(
				'public_name' => 'approve',
				'system_name' => 'close',
				'id'          => CTaskItem::ACTION_APPROVE
			);
		}

		if (
			in_array(
				(int) $arTask['REAL_STATUS'], 
				array(
					CTasks::STATE_PENDING,
					CTasks::STATE_IN_PROGRESS
				),
				true
			)
			&& ($arTask['RESPONSIBLE_ID'] == $curUserId)
		)
		{
			$arAllowedActions[] = array(
				'public_name' => 'pause',
				'system_name' => 'defer',
				'id'          => CTaskItem::ACTION_DEFER
			);
		}

		if (
			in_array(
				(int) $arTask['REAL_STATUS'], 
				array(
					CTasks::STATE_SUPPOSEDLY_COMPLETED,
					CTasks::STATE_COMPLETED
				),
				true
			)
			&& ($arTask['RESPONSIBLE_ID'] == $curUserId)
		)
		{
			$arAllowedActions[] = array(
				'public_name' => 'renew',
				'system_name' => 'start',
				'id'          => CTaskItem::ACTION_START
			);
		}
		elseif (
			in_array(
				(int) $arTask['REAL_STATUS'], 
				array(
					CTasks::STATE_COMPLETED,
					CTasks::STATE_DECLINED
				),
				true
			)
			&& ($arTask['CREATED_BY'] == $curUserId)
		)
		{
			$sysName = null;
			if ($arTask['SUBORDINATE'] === 'Y')
				$sysName = 'accept';
			else
				$sysName = 'renew';

			$arAllowedActions[] = array(
				'public_name' => 'renew',
				'system_name' => $sysName,
				'id'          => CTaskItem::ACTION_RENEW
			);
		}

		return ($arAllowedActions);
	}


	/**
	 * Convert every given string in array from BB-code to HTML
	 *
	 * @param array $arStringsInBbcode
	 *
	 * @throws TasksException
	 * @return array of strings converted to HTML, keys maintaned
	 */
	public static function convertBbcode2Html($arStringsInBbcode)
	{
		if ( ! is_array($arStringsInBbcode) )
			throw new TasksException();

		static $delimiter = '--------This is unique BB-code strings delimiter at high confidence level (CL)--------';
		
		$stringsCount = count($arStringsInBbcode);
		$arStringsKeys = array_keys($arStringsInBbcode);

		$concatenatedStrings = implode($delimiter, $arStringsInBbcode);

		// While not unique identifier, try to
		$i = -150;
		while (count(explode($delimiter, $concatenatedStrings)) !== $stringsCount)
		{
			// prevent an infinite loop
			if ( ! ($i++) )
				throw new TasksException();

			$delimiter = '--------' . sha1(uniqid()) . '--------';
			$concatenatedStrings = implode($delimiter, $arStringsInBbcode);
		}

		$oParser = new CTextParser();

		$arHtmlStringsWoKeys = explode(
			$delimiter, 
			str_replace(
				"\t",
				' &nbsp; &nbsp;',
				$oParser->convertText($concatenatedStrings)
			)
		);

		$arHtmlStrings = array();

		// Do job in compatibility mode, if count of resulted strings not match source
		if (count($arHtmlStringsWoKeys) !== $stringsCount)
		{
			foreach ($arStringsInBbcode as $key => $str)
			{
				$oParser = new CTextParser();
				$arHtmlStrings[$key] = str_replace(
					"\t",
					' &nbsp; &nbsp;',
					$oParser->convertText($str)
				);
				unset($oParser);
			}
		}
		else
		{
			// Maintain original array keys
			$i = 0;
			foreach ($arStringsKeys as $key)
				$arHtmlStrings[$key] = $arHtmlStringsWoKeys[$i++];
		}

		return ($arHtmlStrings);
	}
}
