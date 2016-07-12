<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */

IncludeModuleLangFile(__FILE__);

class CTaskNotifications
{
	function SendMessage($fromUserID, $arRecipientsIDs, $message, $taskID = 0, $message_email = null, $arEventData = array())
	{
		if (!(IsModuleInstalled("im") && CModule::IncludeModule("im")))
			return false;

		$message_email = is_null($message_email)? $message: $message_email;

		if ( ! ($fromUserID && $arRecipientsIDs && $message) )
			return (false);

		CTaskAssert::assert(is_array($arEventData));

		$arEventData['fromUserID']      = &$fromUserID;
		$arEventData['arRecipientsIDs'] = &$arRecipientsIDs;
		$arEventData['message']         = &$message;
		$arEventData['message_email']   = &$message_email;

		foreach(GetModuleEvents('tasks', 'OnBeforeTaskNotificationSend', true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($arEventData)) === false)
				return false;
		}

		$arSites = array();
		if (CModule::IncludeModule("extranet"))
		{
			$dbSite = CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));
			while($arSite = $dbSite->Fetch())
			{
				$arSites[($arSite["ID"] == CExtranet::GetExtranetSiteID() ? "EXTRANET" : "INTRANET")] = array(
					'SITE_ID' => $arSite['ID'],
					"DIR" => (strlen(trim($arSite["DIR"])) > 0 ? $arSite["DIR"] : "/"),
					"SERVER_NAME" => (strlen(trim($arSite["SERVER_NAME"])) > 0 ? $arSite["SERVER_NAME"] : COption::GetOptionString("main", "server_name", $_SERVER["HTTP_HOST"]))
				);
			}
		}

		if (is_array($arRecipientsIDs) && count($arRecipientsIDs))
		{
			$arRecipientsIDs = array_unique($arRecipientsIDs);
			$rsUser = CUser::GetList(
				$by = 'ID',
				$order = 'ASC',
				array('ID' => implode('|', $arRecipientsIDs)),
				array('FIELDS' => array('ID'))
			);

			while ($arUser = $rsUser->Fetch())
			{
				$pathToTask = CTaskNotifications::GetNotificationPath($arUser, $taskID, true, $arSites);
				$arMessageFields = array(
					"TO_USER_ID" => $arUser['ID'],
					"FROM_USER_ID" => $fromUserID, 
					"NOTIFY_TYPE" => IM_NOTIFY_FROM, 
					"NOTIFY_MODULE" => "tasks", 
					"NOTIFY_EVENT" => "manage", 
					"NOTIFY_MESSAGE" => str_replace("#PATH_TO_TASK#", $pathToTask, $message),
					"NOTIFY_MESSAGE_OUT" => str_replace("#PATH_TO_TASK#", $pathToTask, $message_email),
				);
				CIMNotify::Add($arMessageFields);
			}
		}

		return (null);
	}


	protected static function SendMessageToSocNet($arFields, $bSpawnedByAgent, $arChanges = null, $arTask = null)
	{
		global $USER, $DB;

		$loggedInUserId = false;
		if (is_object($USER) && method_exists($USER, 'getId'))
			$loggedInUserId = (int) $USER->getId();

		if ( ! CModule::IncludeModule('socialnetwork') )
			return (null);

		static $arCheckedUsers = array();		// users that checked for their existing
		static $cachedSiteTimeFormat = -1;

		if ($cachedSiteTimeFormat === -1)
			$cachedSiteTimeFormat = CSite::GetDateFormat('FULL', SITE_ID);

		static $cachedAllSitesIds = -1;

		if ($cachedAllSitesIds === -1)
		{
			$cachedAllSitesIds = array();

			$dbSite = CSite::GetList(
				$by = 'sort', 
				$order = 'desc', 
				array('ACTIVE' => 'Y')
			);

			while ($arSite = $dbSite->Fetch())
				$cachedAllSitesIds[] = $arSite['ID'];
		}

		// Check that user exists
		if ( ! in_array( (int) $arFields["CREATED_BY"], $arCheckedUsers, true) )
		{
			$rsUser = CUser::GetList(
				$by = 'ID',
				$order = 'ASC',
				array('ID' => $arFields["CREATED_BY"]), 
				array('FIELDS' => array('ID'))
			);

			if ( ! ($arUser = $rsUser->Fetch()) )
				return (false);

			$arCheckedUsers[] = (int) $arFields["CREATED_BY"];
		}

		if (is_array($arChanges))
		{
			if (count($arChanges) == 0)
			{
				$rsSocNetLogItems = CSocNetLog::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID" => "tasks",
						"SOURCE_ID" => $arTask["ID"]
					),
					false,
					false,
					array("ID", "ENTITY_TYPE", "ENTITY_ID")
				);

				while ($arRes = $rsSocNetLogItems->Fetch())
				{
					$authorUserId = false;
					if (isset($arFields['CREATED_BY']))
						$authorUserId = (int) $arFields['CREATED_BY'];
					elseif (isset($arTask['CREATED_BY']))
						$authorUserId = (int) $arTask['CREATED_BY'];

					// Add author to list of users that view log about task in livefeed
					// But only when some other person change task
					if ($authorUserId !== $loggedInUserId)
					{
						$authorGroupCode = 'U' . $authorUserId;

						$rsRights = CSocNetLogRights::GetList(
							array(),
							array(
								'LOG_ID'     => $arRes['ID'],
								'GROUP_CODE' => $authorGroupCode
							)
						);

						// If task's author hasn't rights yet, give them
						if ( ! ($arRights = $rsRights->fetch()) )
							CSocNetLogRights::Add($arRes["ID"], array($authorGroupCode));
					}
				}

				return (null);
			}
			elseif ((count($arChanges) == 1) && isset($arChanges['STATUS']))
				return (null);	// if only status changes - don't send message, because it will be send by SendStatusMessage()
		}

		if ($bSpawnedByAgent === 'Y')
			$bSpawnedByAgent = true;
		elseif ($bSpawnedByAgent === 'N')
			$bSpawnedByAgent = false;

		if ( ! is_bool($bSpawnedByAgent) )
			return (false);

		$taskId = false;
		if (is_array($arFields) && isset($arFields['ID']) && ($arFields['ID'] > 0))
			$taskId = $arFields['ID'];
		elseif (is_array($arTask) && isset($arTask['ID']) && ($arTask['ID'] > 0))
			$taskId = $arTask['ID'];

		// We will mark this to false, if we send update message and log item already exists
		$bSocNetAddNewItem = true;

		$logDate = $DB->CurrentTimeFunction();
		$curTimeTimestamp = time() + CTimeZone::GetOffset();
		$arSoFields = array(
			'EVENT_ID'  => 'tasks',
			'TITLE'     => $arFields['TITLE'],
			'MESSAGE'   => '',
			'MODULE_ID' => 'tasks'
		);

		// If changes and task data given => we are prepare "update" message,
		// or "add" message otherwise
		if (is_array($arChanges) && is_array($arTask))
		{	// Prepare "update" message here
			if (strlen($arFields["CHANGED_DATE"]) > 0)
			{
				$createdDateTimestamp = MakeTimeStamp(
					$arFields["CHANGED_DATE"], 
					$cachedSiteTimeFormat
				);

				if ($createdDateTimestamp > $curTimeTimestamp)
				{
					$logDate = $DB->CharToDateFunction(
						$arFields["CHANGED_DATE"], 
						"FULL", 
						SITE_ID
					);
				}
			}

			$arChangesFields = array_keys($arChanges);
			$arSoFields['TEXT_MESSAGE'] = str_replace(
				'#CHANGES#', 
				implode(
					', ', 
					CTaskNotifications::__Fields2Names($arChangesFields)
				),
				GetMessage('TASKS_SONET_TASK_CHANGED_MESSAGE')
			);

			// Determine, does item exists in sonet log
			$rsSocNetLogItems = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "tasks",
					"SOURCE_ID" => $arTask["ID"]
				),
				false,
				false,
				array("ID", "ENTITY_TYPE", "ENTITY_ID")
			);

			if (
				(($arFields["GROUP_ID"] === NULL) && $arTask['GROUP_ID'])	// If tasks has group and it not deleted
				|| ($arFields['GROUP_ID'])	// Or new group_id set
			)
			{
				$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_GROUP;
				$arSoFields["ENTITY_ID"] = $arFields["GROUP_ID"];
			}
			else
			{
				$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_USER;
				$arSoFields["ENTITY_ID"] = ($arFields["CREATED_BY"] ? $arFields["CREATED_BY"] : $arTask["CREATED_BY"]);
			}

			$arSoFields['PARAMS'] = serialize(
				array(
					'TYPE'           => 'modify', 
					'CHANGED_FIELDS' => $arChangesFields,
					'PREV_REAL_STATUS' => isset($arTask['REAL_STATUS']) ? $arTask['REAL_STATUS'] : false
				)
			);

			if ($rsSocNetLogItems->Fetch())
				$bSocNetAddNewItem = false;		// item already exists, update it, not create.
		}
		else	// Prepare "add" message here
		{
			if (strlen($arFields["CREATED_DATE"]) > 0)
			{
				$createdDateTimestamp = MakeTimeStamp(
					$arFields["CREATED_DATE"], 
					$cachedSiteTimeFormat
				);

				if ($createdDateTimestamp > $curTimeTimestamp)
				{
					$logDate = $DB->CharToDateFunction(
						$arFields["CREATED_DATE"], 
						"FULL", 
						SITE_ID
					);
				}
			}

			$arSoFields['TEXT_MESSAGE'] = GetMessage('TASKS_SONET_NEW_TASK_MESSAGE');

			if($arFields["GROUP_ID"])
			{
				$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_GROUP;
				$arSoFields["ENTITY_ID"] = $arFields["GROUP_ID"];
			}
			else
			{
				$arSoFields["ENTITY_TYPE"] = SONET_ENTITY_USER;
				$arSoFields["ENTITY_ID"] = $arFields["CREATED_BY"];
			}

			$arParamsLog = array(
				'TYPE' => 'create',
				'PREV_REAL_STATUS' => isset($arTask['REAL_STATUS']) ? $arTask['REAL_STATUS'] : false
			);

			if ( ! $bSpawnedByAgent )
			{
				if (is_object($USER) && $arFields["CREATED_BY"] != $USER->GetID())
					$arParamsLog["CREATED_BY"] = $USER->GetID();
			}

			$arSoFields['PARAMS'] = serialize($arParamsLog);
		}

		// Do we need add new item to socnet?
		// We adds new item, if it is not exists.
		$logID = false;

		if ($bSocNetAddNewItem)
		{
			$arSoFields['=LOG_DATE']       = $logDate;
			$arSoFields['CALLBACK_FUNC']   = false;
			$arSoFields['SOURCE_ID']       = $taskId;
			$arSoFields['ENABLE_COMMENTS'] = 'Y';
			$arSoFields['URL']             = CTaskNotifications::GetNotificationPath(
				array('ID' => (int) $arFields["CREATED_BY"]),
				$taskId,
				false
			);
			$arSoFields['USER_ID']         = $arFields['CREATED_BY'];
			$arSoFields['TITLE_TEMPLATE']  = '#TITLE#';

			// Set all sites because any user from any site may be
			// added to task in future. For example, new auditor, etc.
			$arSoFields['SITE_ID'] = $cachedAllSitesIds;

			$logID = CSocNetLog::Add($arSoFields, false);

			if (intval($logID) > 0)
			{
				CSocNetLog::Update($logID, array("TMP_ID" => $logID));
				$arTaskParticipant = CTaskNotifications::GetRecipientsIDs(
					$arFields, 
					false		// don't exclude current user
				);

				// Exclude author
				$arLogCanViewedBy = array_diff($arTaskParticipant, array($arFields['CREATED_BY']));

				$arRights = CTaskNotifications::__UserIDs2Rights($arLogCanViewedBy);

				if (isset($arFields['GROUP_ID']))
				{
					$arRights = array_merge(
						$arRights,
						self::prepareRightsCodesForViewInGroupLiveFeed($logID, $arFields['GROUP_ID'])
					);
				}

				CSocNetLogRights::Add($logID, $arRights);
				CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);
			}
		}
		else	// Update existing log item
		{
			$arSoFields['=LOG_DATE']   = $logDate;
			$arSoFields['=LOG_UPDATE'] = $logDate;
			$arSoFields['USER_ID']     = $arFields['CHANGED_BY'];

			$rsSocNetLogItems = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "tasks",
					"SOURCE_ID" => $arTask["ID"]
				),
				false,
				false,
				array("ID", "ENTITY_TYPE", "ENTITY_ID")
			);

			while ($arRes = $rsSocNetLogItems->Fetch())
			{
				CSocNetLog::Update($arRes["ID"], $arSoFields);

				$arTaskParticipant = CTaskNotifications::GetRecipientsIDs(
					$arFields,	// Only new tasks' participiants should view log event, fixed due to http://jabber.bx/view.php?id=34504
					false,		// don't exclude current user
					true		// exclude additional recipients (because there are previous members of task)
				);

				$bAuthorMustBeExcluded = false;

				$authorUserId = false;
				if (isset($arFields['CREATED_BY']))
					$authorUserId = (int) $arFields['CREATED_BY'];
				elseif (isset($arTask['CREATED_BY']))
					$authorUserId = (int) $arTask['CREATED_BY'];

				// Get current rights
				$rsRights = CSocNetLogRights::GetList(
					array(),
					array('LOG_ID' => $arRes['ID'])
				);

				$arCurrentRights = array();
				while ($arRights = $rsRights->fetch())
					$arCurrentRights[] = $arRights['GROUP_CODE'];

				// If author changes the task and author doesn't have
				// access to task yet, don't give access to him.
				if ($authorUserId === $loggedInUserId)
				{
					$authorGroupCode = 'U' . $authorUserId;

					// If task's author hasn't rights yet, still exclude him
					if ( ! in_array($authorGroupCode, $arCurrentRights, true) )
						$bAuthorMustBeExcluded = true;
				}

				if ($bAuthorMustBeExcluded)
					$arLogCanViewedBy = array_diff($arTaskParticipant, array($authorUserId));
				else
					$arLogCanViewedBy = $arTaskParticipant;

				$arNewRights = CTaskNotifications::__UserIDs2Rights($arLogCanViewedBy);

				$bGroupChanged = false;
				if (
					isset($arFields['GROUP_ID'], $arTask['GROUP_ID'])
					&& ($arFields['GROUP_ID'])
					&& ($arFields['GROUP_ID'] != $arTask['GROUP_ID'])
				)
				{
					$bGroupChanged = true;
				}

				// If rights really changed, update them
				if (
					count(array_diff($arCurrentRights, $arNewRights))
					|| count(array_diff($arNewRights, $arCurrentRights))
					|| $bGroupChanged
				)
				{
					if (isset($arFields['GROUP_ID']))
					{
						$arNewRights = array_merge(
							$arNewRights,
							self::prepareRightsCodesForViewInGroupLiveFeed($logID, $arFields['GROUP_ID'])
						);
					}

					CSocNetLogRights::DeleteByLogID($arRes["ID"], true);
					CSocNetLogRights::Add($arRes["ID"], $arNewRights);
				}
			}
		}

		return ($logID);
	}


	public static function SendAddMessage($arFields, $arParams = array())
	{
		global $USER;

		$spawnedByAgent = false;

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
		}

		$arUsers = CTaskNotifications::__GetUsers($arFields);

		$bExcludeLoggedUser = true;
		if ($spawnedByAgent)
			$bExcludeLoggedUser = false;

		$arRecipientsIDs = CTaskNotifications::GetRecipientsIDs($arFields, $bExcludeLoggedUser);

		$effectiveUserId = false;

		if ($spawnedByAgent)
		{
			if (isset($arFields['CREATED_BY']) && ($arFields['CREATED_BY'] > 0))
				$effectiveUserId = (int) $arFields['CREATED_BY'];
			else
				$effectiveUserId = 1;
		}
		elseif (is_object($USER) && $USER->GetID())
			$effectiveUserId = (int) $USER->GetID();
		elseif (isset($arFields['CREATED_BY']) && ($arFields['CREATED_BY'] > 0))
			$effectiveUserId = (int) $arFields['CREATED_BY'];

		if (sizeof($arRecipientsIDs) && ($effectiveUserId !== false))
		{
			$strResponsible = CTaskNotifications::__Users2String($arFields["RESPONSIBLE_ID"], $arUsers, $arFields["NAME_TEMPLATE"]);

			$strExtra = GetMessage("TASKS_MESSAGE_RESPONSIBLE_ID").': [COLOR=#000]'.$strResponsible."[/COLOR]\r\n";

			$plainDescription = HTMLToTxt($arFields["DESCRIPTION"]);
			if (strlen($plainDescription))
				$strExtra .= GetMessage("TASKS_MESSAGE_DESCRIPTION").": [COLOR=#000]" . $plainDescription . "[/COLOR]\r\n";

			if ($strAccomplices = CTaskNotifications::__Users2String($arFields["ACCOMPLICES"], $arUsers, $arFields["NAME_TEMPLATE"]))
			{
				$strExtra .= GetMessage("TASKS_MESSAGE_ACCOMPLICES").": [COLOR=#000]".$strAccomplices."[/COLOR]\r\n";
			}

			if ($strAuditors = CTaskNotifications::__Users2String($arFields["AUDITORS"], $arUsers, $arFields["NAME_TEMPLATE"]))
			{
				$strExtra .= GetMessage("TASKS_MESSAGE_AUDITORS").": [COLOR=#000]".$strAuditors."[/COLOR]\r\n";
			}

			if ($arFields["DEADLINE"])
			{
				// Skip invalid deadline
				if (MakeTimeStamp($arFields['DEADLINE']) > 0)
				{
					$strExtra .= GetMessage("TASKS_MESSAGE_DEADLINE")
						. ": [COLOR=#000]";

					// Don't format time, if it's 00:00
					if (date("H:i", strtotime($arFields["DEADLINE"])) == "00:00")
						$strExtra .= FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATE), MakeTimeStamp($arFields['DEADLINE']));
					else
						$strExtra .= FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATETIME), MakeTimeStamp($arFields['DEADLINE']));

					$strExtra .= "[/COLOR]\r\n";
				}
			}

			$message = str_replace(
				array("#TASK_TITLE#", "#TASK_EXTRA#"), 
				array('[URL=#PATH_TO_TASK#]'.$arFields["TITLE"].'[/URL]', $strExtra), 
				GetMessage("TASKS_NEW_TASK_MESSAGE")
			);
			$message_email = str_replace(
				array("#TASK_TITLE#", "#TASK_EXTRA#"), 
				array(strip_tags($arFields["TITLE"]), $strExtra."\r\n".GetMessage('TASKS_MESSAGE_LINK').': #PATH_TO_TASK#'), 
				GetMessage("TASKS_NEW_TASK_MESSAGE")
			);
			$fromUserID = $effectiveUserId;

			CTaskNotifications::SendMessage($fromUserID, $arRecipientsIDs, 
				$message, $arFields["ID"], $message_email,
				array(
					'ACTION'   => 'TASK_ADD',
					'arFields' => $arFields
				)
			);
		}

		// sonet log
		self::SendMessageToSocNet($arFields, $spawnedByAgent);
	}


	public static function SendUpdateMessage($arFields, $arTask, $bSpawnedByAgent = false)
	{
		global $USER;

		foreach (array('CREATED_BY', 'RESPONSIBLE_ID', 'ACCOMPLICES', 'AUDITORS', 'TITLE') as $field)
		{
			if ( ! isset($arFields[$field])
				&& isset($arTask[$field])
			)
			{
				$arFields[$field] = $arTask[$field];
			}
		}

		$arChanges = CTaskLog::GetChanges($arTask, $arFields);

		$arMerged = array(
			'ADDITIONAL_RECIPIENTS' => array()
		);

		// Pack prev users ids to ADDITIONAL_RECIPIENTS, to ensure, 
		// that they all will receive message
		{
			if (isset($arTask['CREATED_BY']))
				$arMerged['ADDITIONAL_RECIPIENTS'][] = $arTask['CREATED_BY'];

			if (isset($arTask['RESPONSIBLE_ID']))
				$arMerged['ADDITIONAL_RECIPIENTS'][] = $arTask['RESPONSIBLE_ID'];

			if (isset($arTask['ACCOMPLICES']) && is_array($arTask['ACCOMPLICES']))
				foreach ($arTask['ACCOMPLICES'] as $userId)
					$arMerged['ADDITIONAL_RECIPIENTS'][] = $userId;

			if (isset($arTask['AUDITORS']) && is_array($arTask['AUDITORS']))
				foreach ($arTask['AUDITORS'] as $userId)
					$arMerged['ADDITIONAL_RECIPIENTS'][] = $userId;
		}

		if (isset($arFields['ADDITIONAL_RECIPIENTS']))
		{
			$arFields['ADDITIONAL_RECIPIENTS'] = array_merge (
				$arFields['ADDITIONAL_RECIPIENTS'],
				$arMerged['ADDITIONAL_RECIPIENTS']
				);
		}
		else
		{
			$arFields['ADDITIONAL_RECIPIENTS'] = $arMerged['ADDITIONAL_RECIPIENTS'];
		}

		$arUsers = CTaskNotifications::__GetUsers($arFields);

		$arRecipientsIDs = CTaskNotifications::GetRecipientsIDs($arFields);

		if (
			(
				sizeof($arRecipientsIDs) 
				|| ($arFields['RESPONSIBLE_ID'] != $arTask['RESPONSIBLE_ID'])
			)
			&& ((is_object($USER) && $USER->GetID()) || $arFields["CREATED_BY"])
		)
		{
			$arChangesStrs = array();
			foreach ($arChanges as $key => $value)
			{
				if (isset($GLOBALS["MESS"]["TASKS_MESSAGE_".$key]))
				{
					$tmpStr = GetMessage("TASKS_MESSAGE_".$key).": [COLOR=#000]";
					switch ($key)
					{
						case "TITLE":
							$tmpStr .= $value["FROM_VALUE"]." -> ".$value["TO_VALUE"];
							break;

						case "RESPONSIBLE_ID":
							$tmpStr .= 
								CTaskNotifications::__Users2String($value["FROM_VALUE"], $arUsers, $arFields["NAME_TEMPLATE"])
								. ' -> '
								. CTaskNotifications::__Users2String($value["TO_VALUE"], $arUsers, $arFields["NAME_TEMPLATE"]);
							break;

						case "ACCOMPLICES":
						case "AUDITORS":
							$tmpStr .= 
								CTaskNotifications::__Users2String(explode(",", $value["FROM_VALUE"]), $arUsers, $arFields["NAME_TEMPLATE"])
								. ' -> '
								. CTaskNotifications::__Users2String(explode(",", $value["TO_VALUE"]), $arUsers, $arFields["NAME_TEMPLATE"])
								;
							break;

						case "DEADLINE":
						case "START_DATE_PLAN":
						case "END_DATE_PLAN":
							if (strlen($value["FROM_VALUE"]) > 0)
							{
								// Don't format time, if it's 00:00
								if (date('H:i', $value['FROM_VALUE']) == '00:00')
									$tmpStr .= FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATE), $value['FROM_VALUE']);
								else
									$tmpStr .= FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATETIME), $value['FROM_VALUE']);
							}

							$tmpStr .= ' -> ';

							if (strlen($value['TO_VALUE']) > 0)
							{
								// Don't format time, if it's 00:00
								if (date('H:i', $value['TO_VALUE']) == '00:00')
									$tmpStr .= FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATE), $value['TO_VALUE']);
								else
									$tmpStr .= FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATETIME), $value['TO_VALUE']);
							}

							break;

						case "DESCRIPTION":
							$tmpStr .= HTMLToTxt($arFields["DESCRIPTION"]);
							break;

						case "TAGS":
							$tmpStr .= ($value["FROM_VALUE"] ? str_replace(",", ", ", $value["FROM_VALUE"])." -> " : "").($value["TO_VALUE"] ? str_replace(",", ", ", $value["TO_VALUE"]) : GetMessage("TASKS_MESSAGE_NO_VALUE"));
							break;

						case "PRIORITY":
							$tmpStr .= GetMessage("TASKS_PRIORITY_".$value["FROM_VALUE"])." -> ".GetMessage("TASKS_PRIORITY_".$value["TO_VALUE"]);
							break;

						case "GROUP_ID":
							if ($value["FROM_VALUE"] && CSocNetGroup::CanUserViewGroup($USER->GetID(), $value["FROM_VALUE"]))
							{
								$arGroupFrom = CSocNetGroup::GetByID($value["FROM_VALUE"]);
								{
									if ($arGroupFrom)
									{
										$tmpStr .= $arGroupFrom["NAME"]." -> ";
									}
								}
							}
							if ($value["TO_VALUE"] && CSocNetGroup::CanUserViewGroup($USER->GetID(), $value["TO_VALUE"]))
							{
								$arGroupTo = CSocNetGroup::GetByID($value["TO_VALUE"]);
								{
									if ($arGroupTo)
									{
										$tmpStr .= $arGroupTo["NAME"];
									}
								}
							}
							else
							{
								$tmpStr .= GetMessage("TASKS_MESSAGE_NO_VALUE");
							}
							break;
						case "PARENT_ID":
							if ($value["FROM_VALUE"])
							{
								$rsTaskFrom = CTasks::GetByID($value["FROM_VALUE"]);
								{
									if ($arTaskFrom = $rsTaskFrom->GetNext())
									{
										$tmpStr .= $arTaskFrom["TITLE"]." -> ";
									}
								}
							}
							if ($value["TO_VALUE"])
							{
								$rsTaskTo = CTasks::GetByID($value["TO_VALUE"]);
								{
									if ($arTaskTo = $rsTaskTo->GetNext())
									{
										$tmpStr .= $arTaskTo["TITLE"];
									}
								}
							}
							else
							{
								$tmpStr .= GetMessage("TASKS_MESSAGE_NO_VALUE");
							}
							break;
						case "DEPENDS_ON":
							$arTasksFromStr = array();
							if ($value["FROM_VALUE"])
							{
								$rsTasksFrom = CTasks::GetList(array(), array("ID" => explode(",", $value["FROM_VALUE"])));
								while ($arTaskFrom = $rsTasksFrom->GetNext())
								{
									$arTasksFromStr[] = $arTaskFrom["TITLE"];
								}
							}
							$arTasksToStr = array();
							if ($value["TO_VALUE"])
							{
								$rsTasksTo = CTasks::GetList(array(), array("ID" => explode(",", $value["TO_VALUE"])));
								while ($arTaskTo = $rsTasksTo->GetNext())
								{
									$arTasksToStr[] = $arTaskTo["TITLE"];
								}
							}
							$tmpStr .= ($arTasksFromStr ? implode(", ", $arTasksFromStr)." -> " : "").($arTasksToStr ? implode(", ", $arTasksToStr) : GetMessage("TASKS_MESSAGE_NO_VALUE"));
							break;
						case "MARK":
							$tmpStr .= (!$value["FROM_VALUE"] ? GetMessage("TASKS_MARK_NONE") : GetMessage("TASKS_MARK_".$value["FROM_VALUE"]))." -> ".(!$value["TO_VALUE"] ? GetMessage("TASKS_MARK_NONE") : GetMessage("TASKS_MARK_".$value["TO_VALUE"]));
							break;
						case "ADD_IN_REPORT":
							$tmpStr .= ($value["FROM_VALUE"] == "Y" ? GetMessage("TASKS_MESSAGE_IN_REPORT_YES") : GetMessage("TASKS_MESSAGE_IN_REPORT_NO"))." -> ".($value["TO_VALUE"] == "Y" ? GetMessage("TASKS_MESSAGE_IN_REPORT_YES") : GetMessage("TASKS_MESSAGE_IN_REPORT_NO"));
							break;
						case "DELETED_FILES":
							$tmpStr .= $value["FROM_VALUE"];
							$tmpStr .= $value["TO_VALUE"];
							break;
						case "NEW_FILES":
							$tmpStr .= $value["TO_VALUE"];
							break;
					}
					$tmpStr .= "[/COLOR]";

					$arChangesStrs[] = $tmpStr;
				}
			}

			if (sizeof($arChangesStrs) && sizeof($arRecipientsIDs))
			{
				$strExtra = implode("\r\n", $arChangesStrs);

				$message = str_replace(
					array("#TASK_TITLE#", "#TASK_EXTRA#"), 
					array('[URL=#PATH_TO_TASK#]'.$arTask["TITLE"].'[/URL]', $strExtra), 
					GetMessage("TASKS_TASK_CHANGED_MESSAGE")
				);
				$message_email = str_replace(
					array("#TASK_TITLE#", "#TASK_EXTRA#"), 
					array($arTask["TITLE"], $strExtra."\r\n".GetMessage('TASKS_MESSAGE_LINK').': #PATH_TO_TASK#'), 
					GetMessage("TASKS_TASK_CHANGED_MESSAGE")
				);

				$fromUserID = is_object($USER) && $USER->GetID() ? $USER->GetID() : $arFields["CREATED_BY"];

				CTaskNotifications::SendMessage($fromUserID, $arRecipientsIDs, 
					$message, $arTask["ID"], $message_email,
					array(
						'ACTION'    => 'TASK_UPDATE',
						'arFields'  => $arFields,
						'arChanges' => $arChanges
					)
				);
			}
		}

		// sonet log
		self::SendMessageToSocNet($arFields, $bSpawnedByAgent, $arChanges, $arTask);
	}


	function SendDeleteMessage($arFields)
	{
		global $USER;

		$arRecipientsIDs = CTaskNotifications::GetRecipientsIDs($arFields);
		if (sizeof($arRecipientsIDs) && ((is_object($USER) && $USER->GetID()) || $arFields["CREATED_BY"]))
		{
			$message = str_replace("#TASK_TITLE#", $arFields["TITLE"], GetMessage("TASKS_TASK_DELETED_MESSAGE"));

			$fromUserID = is_object($USER) && $USER->GetID() ? $USER->GetID() : $arFields["CREATED_BY"];

			CTaskNotifications::SendMessage($fromUserID, $arRecipientsIDs, 
				$message, 0, null,
				array(
					'ACTION'   => 'TASK_DELETE',
					'arFields' => $arFields
				)
			);
		}

		// sonet log
		if (CModule::IncludeModule("socialnetwork"))
		{
			$dbRes = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "tasks",
					"SOURCE_ID" => $arFields["ID"]
				),
				false,
				false,
				array("ID")
			);
			while ($arRes = $dbRes->Fetch())
				CSocNetLog::Delete($arRes["ID"]);
		}
	}


	function SendStatusMessage($arTask, $status, $arFields = array())
	{
		global $USER, $DB;

		$status = intval($status);
		if ($status > 0 && $status < 8)
		{
			$arRecipientsIDs = CTaskNotifications::GetRecipientsIDs(array_merge($arTask, $arFields));
			if (sizeof($arRecipientsIDs) && ((is_object($USER) && $USER->GetID()) || $arTask["CREATED_BY"]))
			{
				// If task was redoed
				if (
					(
						($status == CTasks::STATE_NEW)
						|| ($status == CTasks::STATE_PENDING)
					)
					&& ($arTask['REAL_STATUS'] == CTasks::STATE_SUPPOSEDLY_COMPLETED)
				)
				{
					$message = str_replace("#TASK_TITLE#", '[URL=#PATH_TO_TASK#]'.$arTask["TITLE"].'[/URL]', GetMessage("TASKS_TASK_STATUS_MESSAGE_REDOED"));
					$message_email = str_replace("#TASK_TITLE#", $arTask["TITLE"], GetMessage("TASKS_TASK_STATUS_MESSAGE_REDOED")."\r\n".GetMessage('TASKS_MESSAGE_LINK').': #PATH_TO_TASK#');
				}
				else
				{
					$message = str_replace("#TASK_TITLE#", '[URL=#PATH_TO_TASK#]'.$arTask["TITLE"].'[/URL]', GetMessage("TASKS_TASK_STATUS_MESSAGE_".$status));
					$message_email = str_replace("#TASK_TITLE#", $arTask["TITLE"], GetMessage("TASKS_TASK_STATUS_MESSAGE_".$status)."\r\n".GetMessage('TASKS_MESSAGE_LINK').': #PATH_TO_TASK#');

					if ($status == CTasks::STATE_DECLINED)
					{
						$message = str_replace("#TASK_DECLINE_REASON#", $arTask["DECLINE_REASON"], $message);
						$message_email = str_replace("#TASK_DECLINE_REASON#", $arTask["DECLINE_REASON"], $message_email);
					}
				}

				$fromUserID = is_object($USER) && $USER->GetID() ? $USER->GetID() : $arTask["CREATED_BY"];

				CTaskNotifications::SendMessage($fromUserID, $arRecipientsIDs, 
					$message, $arTask["ID"], $message_email,
					array(
						'ACTION'   => 'TASK_STATUS_CHANGED_MESSAGE',
						'arTask'   => $arTask,
						'arFields' => $arFields
					)
				);
			}
		}

		// sonet log
		if (CModule::IncludeModule("socialnetwork"))
		{
			$message = GetMessage("TASKS_SONET_TASK_STATUS_MESSAGE_".$status);

			if ($status == CTasks::STATE_DECLINED)
				$message = str_replace("#TASK_DECLINE_REASON#", $arTask["DECLINE_REASON"], $message);

			$arSoFields = array(
				"TITLE" => $arTask["TITLE"],
				"=LOG_UPDATE" => (
					strlen($arTask["CHANGED_DATE"]) > 0?
						(MakeTimeStamp($arTask["CHANGED_DATE"], CSite::GetDateFormat("FULL", SITE_ID)) > time()+CTimeZone::GetOffset()?
							$DB->CharToDateFunction($arTask["CHANGED_DATE"], "FULL", SITE_ID) :
							$DB->CurrentTimeFunction()) :
						$DB->CurrentTimeFunction()
				),
				"MESSAGE" => "",
				"TEXT_MESSAGE" => $message,
				"PARAMS" => serialize(
					array(
						"TYPE" => "status",
						'PREV_REAL_STATUS' => isset($arTask['REAL_STATUS']) ? $arTask['REAL_STATUS'] : false
					)
				)
			);

			if ($arFields["CHANGED_BY"])
			{
				$arSoFields["USER_ID"] = $arFields["CHANGED_BY"];
			}

			$loggedInUserId = false;
			if (is_object($USER) && method_exists($USER, 'getId'))
				$loggedInUserId = (int) $USER->getId();

			$dbRes = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array(
					"EVENT_ID" => "tasks",
					"SOURCE_ID" => $arTask["ID"]
				),
				false,
				false,
				array("ID", "ENTITY_TYPE", "ENTITY_ID")
			);

			while ($arRes = $dbRes->Fetch())
			{
				CSocNetLog::Update($arRes['ID'], $arSoFields);

				$authorUserId = (int) $arTask['CREATED_BY'];

				// Add author to list of users that view log about task in livefeed
				// But only when some other person change task
				if ($authorUserId !== $loggedInUserId)
				{
					$authorGroupCode = 'U' . $authorUserId;

					$rsRights = CSocNetLogRights::GetList(
						array(),
						array(
							'LOG_ID'     => $arRes['ID'],
							'GROUP_CODE' => $authorGroupCode
						)
					);

					// If task's author hasn't rights yet, give them
					if ( ! ($arRights = $rsRights->fetch()) )
						CSocNetLogRights::Add($arRes["ID"], array($authorGroupCode));
				}
			}
		}
	}


	function GetRecipientsIDs($arFields, $bExcludeCurrent = true, $bExcludeAdditionalRecipients = false)
	{
		global $USER;

		if ($bExcludeAdditionalRecipients)
			$arFields['ADDITIONAL_RECIPIENTS'] = array();

		if ( ! isset($arFields['ADDITIONAL_RECIPIENTS']) )
			$arFields['ADDITIONAL_RECIPIENTS'] = array();

		$arRecipientsIDs = array_unique(
			array_filter(
				array_merge(
					array($arFields["CREATED_BY"], $arFields["RESPONSIBLE_ID"]), 
					(array) $arFields["ACCOMPLICES"], 
					(array) $arFields["AUDITORS"],
					(array) $arFields['ADDITIONAL_RECIPIENTS']
					)));

		if ($bExcludeCurrent && is_object($USER) && ($currentUserID = $USER->GetID()))
		{
			$currentUserPos = array_search($currentUserID, $arRecipientsIDs);
			if ($currentUserPos !== false)
			{
				unset($arRecipientsIDs[$currentUserPos]);
			}
		}

		return $arRecipientsIDs;
	}


	public static function GetNotificationPath($arUser, $taskID, $bUseServerName = true, $arSites = array())
	{
		$bExtranet = false;
		$siteID = false;
		$effectiveSiteId = (string) SITE_ID;
		$rsTask = CTasks::GetByID($taskID, false);
		if ($arTask = $rsTask->Fetch())
		{
			if (CModule::IncludeModule('extranet') 
				&& ( ! CTaskNotifications::__isIntranetUser($arUser["ID"]) )
			)
			{
				$bExtranet = true;
				$siteID = (string) CExtranet::GetExtranetSiteID();
			}

			if ($siteID)
				$effectiveSiteId = (string) $siteID;
			elseif (isset($arSites['INTRANET']['SITE_ID']))
				$effectiveSiteId = (string) $arSites['INTRANET']['SITE_ID'];

			if ( ! is_string($siteID) )
				$siteID = (string) SITE_ID;

			if ($arTask['GROUP_ID'] 
				&& CTasksTools::HasUserReadAccessToGroup(
					$arUser['ID'],
					$arTask['GROUP_ID']
				)
			)
			{
				$pathTemplate = str_replace(
					"#group_id#", 
					$arTask["GROUP_ID"], 
					CTasksTools::GetOptionPathTaskGroupEntry(
						$effectiveSiteId,
						"/workgroups/group/#group_id#/tasks/task/view/#task_id#/"
					)
				);
				$pathTemplate = str_replace(
					"#GROUP_ID#", 
					$arTask["GROUP_ID"], 
					$pathTemplate
				);
			}
			else
			{
				$pathTemplate = CTasksTools::GetOptionPathTaskUserEntry(
					$siteID,
					"/company/personal/user/#user_id#/tasks/task/view/#task_id#/"					
				);
			}

			$server_name_tmp = false;
			if ($arTask["GROUP_ID"] && count($arSites) > 0)
				$server_name_tmp = $arSites[($bExtranet ? "EXTRANET" : "INTRANET")]["SERVER_NAME"];

			$strUrl = ($bUseServerName ? tasksServerName($server_name_tmp) : "")
				. CComponentEngine::MakePathFromTemplate(
					$pathTemplate, 
					array(
						'user_id' => $arUser['ID'], 
						'USER_ID' => $arUser['ID'], 
						'task_id' => $taskID, 
						'TASK_ID' => $taskID, 
						'action'  => 'view'
					)
				);

			return ($strUrl);
		}

		return false;
	}


	private function __isIntranetUser($userID)
	{
		return (CTasksTools::IsIntranetUser($userID));
	}


	private function __GetUsers($arFields)
	{
		static $arParams = array(
			'FIELDS' => array(
				'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'ID'
			)
		);

		$arUsersIDs = array_unique(
			array_filter(
				array_merge(
					array(
						$arFields["CREATED_BY"], 
						$arFields["RESPONSIBLE_ID"]
					), 
					(array) $arFields["ACCOMPLICES"], 
					(array) $arFields["AUDITORS"], 
					(array) $arFields['ADDITIONAL_RECIPIENTS']
				)
			)
		);

		$rsUsers = CUser::GetList(
			$by = 'id', 
			$order = 'asc', 
			array("ID" => implode("|", $arUsersIDs)),
			$arParams
		);

		$arUsers = array();

		while ($user = $rsUsers->Fetch())
			$arUsers[$user["ID"]] = $user;

		return $arUsers;
	}


	private function __Users2String($arUserIDs, $arUsers, $nameTemplate = "")
	{
		$arUsersStrs = array();
		if (!is_array($arUserIDs))
			$arUserIDs = array($arUserIDs);

		$arUserIDs = array_unique(array_filter($arUserIDs));
		foreach ($arUserIDs as $userID)
		{
			if ($user = $arUsers[$userID])
				$arUsersStrs[] = CUser::FormatName(empty($nameTemplate) ? CSite::GetNameFormat(false) : $nameTemplate, $arUsers[$userID]);
		}

		return implode(", ", $arUsersStrs);
	}


	function __UserIDs2Rights($arUserIDs)
	{
		$arUserIDs = array_unique(array_filter($arUserIDs));
		$arRights = array();
		foreach($arUserIDs as $userID)
			$arRights[] = "U".$userID;

		return $arRights;
	}


	function __Fields2Names($arFields)
	{
		$arFields = array_unique(array_filter($arFields));
		$arNames = array();
		foreach($arFields as $field)
		{
			if ($field == "NEW_FILES" || $field == "DELETED_FILES")
				$field = "FILES";
			$arNames[] = GetMessage("TASKS_SONET_LOG_".$field);
		}

		return array_unique(array_filter($arNames));
	}


	public static function FormatTask4Log($arTask, $message = '', $message_24_1 = '', $message_24_2 = '', $changes_24 = '', $nameTemplate = '')
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		ob_start();
		$APPLICATION->IncludeComponent(
			'bitrix:tasks.task.livefeed', 
			'', 
			array(
				'TASK' => $arTask,
				'MESSAGE' => $message,
				'MESSAGE_24_1' => $message_24_1,
				'MESSAGE_24_2' => $message_24_2,
				'CHANGES_24' => $changes_24,
				'NAME_TEMPLATE'	=> $nameTemplate
			), 
			null, 
			array('HIDE_ICONS' => 'Y')
		);
		$html = ob_get_clean();

		return $html;
	}


	public static function FormatTask4SocialNetwork($arFields, $arParams, $bMail = false)
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;

		$task_datetime = null;

		if ( ! CModule::IncludeModule('socialnetwork') )
			return (false);

		$APPLICATION->SetAdditionalCSS('/bitrix/js/tasks/css/tasks.css');

		$arFields['PARAMS'] = unserialize($arFields['~PARAMS']);

		$arResult = array(
			'EVENT'           => $arFields,
			'CREATED_BY'      => CSocNetLogTools::FormatEvent_GetCreatedBy($arFields, $arParams, $bMail),
			'ENTITY'          => CSocNetLogTools::FormatEvent_GetEntity($arFields, $arParams, $bMail),
			'EVENT_FORMATTED' => array(),
			'CACHED_CSS_PATH' => '/bitrix/js/tasks/css/tasks.css'
		);

		if (!$bMail)
			$arResult["AVATAR_SRC"] = CSocNetLogTools::FormatEvent_CreateAvatar($arFields, $arParams);

		if (
			!$bMail
			&& $arParams["MOBILE"] != "Y"
			&& array_key_exists("URL", $arFields)
			&& strlen($arFields["URL"]) > 0
		)
			$task_tmp = '<a href="'.$arFields["URL"].'" onclick="if (taskIFramePopup.isLeftClick(event)) {taskIFramePopup.view('.$arFields["SOURCE_ID"].'); return false;}">'.$arFields["TITLE"].'</a>';
		else
			$task_tmp = $arFields["TITLE"];

		$title_tmp = str_replace(
			"#TITLE#",
			$task_tmp,
			GetMessage("TASKS_SONET_GL_EVENT_TITLE_TASK")
		);

		if($arFields["PARAMS"] && $arFields["PARAMS"]["CREATED_BY"])
		{
			$suffix = (
				is_array($GLOBALS["arExtranetUserID"]) 
				&& in_array($arFields["PARAMS"]["CREATED_BY"], $GLOBALS["arExtranetUserID"]) ? GetMessage("TASKS_SONET_LOG_EXTRANET_SUFFIX") : "");

			$rsUser = CUser::GetByID(intval($arFields["PARAMS"]["CREATED_BY"]));

			if ($arUser = $rsUser->Fetch())
			{
				$title_tmp .= " (" 
					. str_replace(
						"#USER_NAME#", 
						CUser::FormatName(CSite::GetNameFormat(false), $arUser) . $suffix,
						GetMessage("TASKS_SONET_GL_EVENT_TITLE_TASK_CREATED")
						)
					. ")";
			}
		}

		if ($bMail)
		{
			$title = str_replace(
				array("#TASK#", "#ENTITY#", "#CREATED_BY#"),
				array($title_tmp, $arResult["ENTITY"]["FORMATTED"], ($bMail ? $arResult["CREATED_BY"]["FORMATTED"] : "")),
				GetMessage(
					"SONET_GL_EVENT_TITLE_" .
					($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP ? "GROUP" : "USER") 
					. "_TASK_MAIL"
					)
			);
		}
		else
		{
			$title = $title_tmp;

			if (
				!is_array($arFields["PARAMS"])
				|| !array_key_exists("TYPE", $arFields["PARAMS"])
				|| strlen($arFields["PARAMS"]["TYPE"]) <= 0
			)
				$arFields["PARAMS"]["TYPE"] = "DEFAULT";

			switch ($arFields["CREATED_BY_PERSONAL_GENDER"])
			{
				case "M":
					$suffix = "_M";
					break;
				case "F":
					$suffix = "_F";
					break;
				default:
					$suffix = "";
			}

			$title_24 = str_replace(
				"#TITLE#", 
				$task_tmp, 
				GetMessage('TASKS_SONET_GL_EVENT_TITLE_TASK_'
					. strtoupper($arFields["PARAMS"]["TYPE"])
					. "_24" . $suffix)
				);
		}

		if (
			!$bMail 
			&& (
				in_array($arFields["PARAMS"]["TYPE"], array("create", "status"))
				|| ($arFields["PARAMS"]["TYPE"] == "modify")
			)
		)
		{
			if ( ! (
				isset($arFields['PARAMS']['CHANGED_FIELDS']) 
				&& is_array($arFields['PARAMS']['CHANGED_FIELDS']) 
			))
			{
				$arFields['PARAMS']['CHANGED_FIELDS'] = array();
			}

			$rsTask = CTasks::GetByID($arFields["SOURCE_ID"], false);
			if ($arTask = $rsTask->Fetch())
			{
				$task_datetime = $arTask["CHANGED_DATE"];
				if ($arFields["PARAMS"]["TYPE"] == "create")
				{
					if ($arParams["MOBILE"] == "Y")
					{
						$title_24     = GetMessage("TASKS_SONET_GL_TASKS2_NEW_TASK_MESSAGE");
						$message_24_1 = $task_tmp;
					}
					else
					{
						$message      = $message_24_1 = GetMessage("TASKS_SONET_GL_TASKS2_NEW_TASK_MESSAGE");
						$message_24_2 = $changes_24 = "";
					}
				}
				elseif ($arFields["PARAMS"]["TYPE"] == "modify")
				{
					$arChangesFields = $arFields["PARAMS"]["CHANGED_FIELDS"];
					$changes_24 = implode(", ", CTaskNotifications::__Fields2Names($arChangesFields));

					if ($arParams["MOBILE"] == "Y")
					{
						$title_24     = GetMessage("TASKS_SONET_GL_TASKS2_TASK_CHANGED_MESSAGE_24_1");
						$message_24_1 = $task_tmp;
					}
					else
					{
						$message = str_replace(
							"#CHANGES#", 
							implode(", ", CTaskNotifications::__Fields2Names($arChangesFields)), 
							GetMessage("TASKS_SONET_GL_TASKS2_TASK_CHANGED_MESSAGE")
							);
						$message_24_1 = GetMessage("TASKS_SONET_GL_TASKS2_TASK_CHANGED_MESSAGE_24_1");
						$message_24_2 = GetMessage("TASKS_SONET_GL_TASKS2_TASK_CHANGED_MESSAGE_24_2");
					}				
				}
				elseif ($arFields["PARAMS"]["TYPE"] == "status")
				{
					$message      = GetMessage("TASKS_SONET_GL_TASKS2_TASK_STATUS_MESSAGE_".$arTask["STATUS"]);
					$message_24_1 = GetMessage("TASKS_SONET_GL_TASKS2_TASK_STATUS_MESSAGE_".$arTask["STATUS"]."_24");

					if ($arTask["STATUS"] == 7)
					{
						$message      = str_replace("#TASK_DECLINE_REASON#", $arTask["DECLINE_REASON"], $message);
						$message_24_2 = GetMessage("TASKS_SONET_GL_TASKS2_TASK_STATUS_MESSAGE_".$arTask["STATUS"]."_24_2");
						$changes_24   = $arTask["DECLINE_REASON"];
					}
					elseif ($arTask["STATUS"] == 4)
					{
						$message_24_2 = GetMessage("TASKS_SONET_GL_TASKS2_TASK_STATUS_MESSAGE_".$arTask["STATUS"]."_24_2");			
						$changes_24   = GetMessage("TASKS_SONET_GL_TASKS2_TASK_STATUS_MESSAGE_4_24_CHANGES");
					}
					else
						$message_24_2 = $changes_24 = "";
				}

				$prevRealStatus = false;

				if (isset($arFields['PARAMS']['PREV_REAL_STATUS']))
					$prevRealStatus = $arFields['PARAMS']['PREV_REAL_STATUS'];

				ob_start();
				$GLOBALS['APPLICATION']->IncludeComponent(
					"bitrix:tasks.task.livefeed", 
					($arParams["MOBILE"] == "Y" ? 'mobile' : ''), 
					array(
						"MOBILE"        => ($arParams["MOBILE"] == "Y" ? "Y" : "N"),
						"TASK"          => $arTask,
						"MESSAGE"       => $message,
						"MESSAGE_24_1"  => $message_24_1,
						"MESSAGE_24_2"  => $message_24_2,
						"CHANGES_24"    => $changes_24,
						"NAME_TEMPLATE"	=> $arParams["NAME_TEMPLATE"],
						"PATH_TO_USER"	=> $arParams["PATH_TO_USER"],
						'TYPE'          => $arFields["PARAMS"]["TYPE"],
						'task_tmp'      => $task_tmp,
						'PREV_REAL_STATUS' => $prevRealStatus
					), 
					null, 
					array("HIDE_ICONS" => "Y")
				);
				$arFields["MESSAGE"] = ob_get_contents();
				ob_end_clean();
			}
		}

		if ($arParams["MOBILE"] == "Y")
		{
			$arResult["EVENT_FORMATTED"] = array(
				"TITLE"             => $title,
				"TITLE_24"          => $title_24,
				"MESSAGE"           => $arFields["MESSAGE"],
				"DESCRIPTION"       => $message_24_1,
				"DESCRIPTION_STYLE" => "task"
			);
		}
		else 
		{
			$strMessage = $strShortMessage = '';

			if ($bMail)
			{
				$strMessage = $strShortMessage = str_replace(
					array('<nobr>', '</nobr>'), 
					array('', ''), 
					$arFields['TEXT_MESSAGE']
					);
			}
			else
			{
				$strMessage      = $arFields['MESSAGE'];
				$strShortMessage = $arFields['~MESSAGE'];
			}

			$arResult["EVENT_FORMATTED"] = array(
				"TITLE"            => $title,
				//"TITLE_24"         => $title_24,
				"MESSAGE"          => $strMessage,
				"SHORT_MESSAGE"    => $strShortMessage,
				"IS_MESSAGE_SHORT" => true,
				"STYLE"            => 'tasks-info'
			);
		}

		if ($bMail)
		{
			$url = CSocNetLogTools::FormatEvent_GetURL($arFields);

			if (strlen($url) > 0)
				$arResult["EVENT_FORMATTED"]["URL"] = $url;
		}
		elseif ($arFields["ENTITY_TYPE"] == SONET_SUBSCRIBE_ENTITY_GROUP)
		{
			$arResult["EVENT_FORMATTED"]["DESTINATION"] = array(
				array(
					"STYLE" => "sonetgroups",
					"TITLE" => $arResult["ENTITY"]["FORMATTED"]["NAME"],
					"URL"   => $arResult["ENTITY"]["FORMATTED"]["URL"],
				)
			);
		}

		if (
			( ! $bMail )
			&& (strlen($task_datetime) > 0)
		)
		{
			$arResult["EVENT_FORMATTED"]["LOG_DATE_FORMAT"] = $task_datetime;
		}

		return $arResult;
	}


	private static function prepareRightsCodesForViewInGroupLiveFeed($logID, $groupId)
	{
		$arRights = array();

		if ( ! $groupId )
			return ($arRights);

		$perm = CSocNetFeaturesPerms::GetOperationPerm(SONET_ENTITY_GROUP, $groupId, 'tasks', 'view');

		if ($perm)
		{
			if ($perm == SONET_ROLES_OWNER)
				$arRights = array("SA", "S".SONET_ENTITY_GROUP.$groupId, "S".SONET_ENTITY_GROUP.$groupId."_".SONET_ROLES_OWNER);
			elseif ($perm == SONET_ROLES_MODERATOR)
				$arRights = array("SA", "S".SONET_ENTITY_GROUP.$groupId, "S".SONET_ENTITY_GROUP.$groupId."_".SONET_ROLES_OWNER, "S".SONET_ENTITY_GROUP.$groupId."_".SONET_ROLES_MODERATOR);
			elseif ($perm == SONET_ROLES_USER)
				$arRights = array("SA", "S".SONET_ENTITY_GROUP.$groupId, "S".SONET_ENTITY_GROUP.$groupId."_".SONET_ROLES_OWNER, "S".SONET_ENTITY_GROUP.$groupId."_".SONET_ROLES_MODERATOR, "S".SONET_ENTITY_GROUP.$groupId."_".SONET_ROLES_USER);
			elseif ($perm == SONET_ROLES_AUTHORIZED)
				$arRights = array("SA", "S".SONET_ENTITY_GROUP.$groupId, "AU");
			elseif ($perm == SONET_ROLES_ALL)
				$arRights = array("SA", "S".SONET_ENTITY_GROUP.$groupId, "G2");
		}

		return ($arRights);
	}
}
