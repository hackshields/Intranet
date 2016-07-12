<?php
define('STOP_STATISTICS',    true);
define('NO_AGENT_CHECK',     true);
define('DisableEventsCheck', true);

define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CUtil::JSPostUnescape();

CModule::IncludeModule('tasks');

CModule::IncludeModule('socialnetwork');

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;

$GROUP_ID = intval($_GET["GROUP_ID"]) > 0 ? intval($_GET["GROUP_ID"]) : 0;

if (isset($_GET["nt"]))
{
	preg_match_all("/(#NAME#)|(#NOBR#)|(#\/NOBR#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/", urldecode($_GET["nt"]), $matches);
	$nameTemplate = implode("", $matches[0]);
}
else
	$nameTemplate = CSite::GetNameFormat(false);

if (check_bitrix_sessid())
{
	$arPaths = array(
		"PATH_TO_TASKS_TASK" => $_POST["path_to_task"],
		"PATH_TO_USER_PROFILE" => $_POST["path_to_user"],
		"PATH_TO_USER_TASKS_TASK" => $_POST["path_to_user_tasks_task"]
	);

	if (intval($_POST["id"]) > 0)
	{
		if ($_POST["mode"] == "load")
		{
			$arOrder = $_POST["order"] ? $_POST["order"] : array();
			$arFilter = $_POST["filter"] ? $_POST["filter"] : array();
			$arFilter["PARENT_ID"] = intval($_POST["id"]);
			$depth = intval($_POST["depth"]) + 1;
			$rsTasks = CTasks::GetList($arOrder, $arFilter);
			$arTasks = array();
			$arTasksIDs = array();
			$arViewed = array();
			while ($task = $rsTasks->GetNext())
			{
				$arTasks[$task["ID"]] = $task;
				$arTasksIDs[] = $task["ID"];
				$arViewed[$task["ID"]] = $task["VIEWED_DATE"] ? $task["VIEWED_DATE"] : $task["CREATED_DATE"];

				$rsTaskFiles = CTaskFiles::GetList(array(), array("TASK_ID" => $task["ID"]));
				$task["FILES"] = array();
				while ($arTaskFile = $rsTaskFiles->Fetch())
				{
					$rsFile = CFile::GetByID($arTaskFile["FILE_ID"]);
					if ($arFile = $rsFile->Fetch())
					{
						$task["FILES"][] = $arFile;
					}
				}

				$arTasks[$task["ID"]] = $task;
			}
			unset($arFilter["PARENT_ID"]);
			$rsChildrenCount = CTasks::GetChildrenCount($arFilter, $arTasksIDs);
			if ($rsChildrenCount)
			{
				while ($arChildrens = $rsChildrenCount->Fetch())
				{
					$arChildrenCount["PARENT_" . $arChildrens["PARENT_ID"]] = $arChildrens["CNT"];
				}
			}

			$arUpdatesCount = CTasks::GetUpdatesCount($arViewed);

			$APPLICATION->RestartBuffer();
			Header('Content-Type: text/html; charset=' . LANG_CHARSET);

			$arGroups = array();

			$i = 0;
			$bIsJSON = $_POST["type"] == "json";
			if ($bIsJSON)
			{
				echo "[";
			}
			foreach ($arTasks as $task)
			{
				$i++;
				if ($task["GROUP_ID"])
				{
					if (!$arGroups[$task["GROUP_ID"]])
					{
						$arGroups[$task["GROUP_ID"]] = CSocNetGroup::GetByID($task["GROUP_ID"]);
					}
					$arGroup = $arGroups[$task["GROUP_ID"]];
					if ($arGroup)
					{
						$task["GROUP_NAME"] = $arGroup["NAME"];
					}
				}
				if ($bIsJSON)
				{
					tasksRenderJSON($task, $arChildrenCount["PARENT_" . $task["ID"]], $arPaths, true, true, false, $nameTemplate);
					if ($i != sizeof($arTasks))
					{
						echo ",";
					}

				}
				else
				{
					tasksRenderListItem($task, $arChildrenCount["PARENT_" . $task["ID"]], $arPaths, $depth, false, true, $SITE_ID, $arUpdatesCount[$task["ID"]], true ,false, "bitrix:tasks.list.item", ".default", $nameTemplate);
				}
			}
			if ($bIsJSON)
			{
				echo "]";
			}
		}
		else
		{
			$rsTask = CTasks::GetByID(intval($_POST["id"]));
			if ($arTask = $rsTask->Fetch())
			{
				$task = new CTasks();
				if ($_POST["mode"] == "delete" && CTasks::CanCurrentUserDelete($arTask, $SITE_ID))
				{
					$rc = $task->Delete(intval($_POST["id"]));
					if ($rc === false)
					{
						$strError = 'Error';
						if($ex = $APPLICATION->GetException())
							$strError = $ex->GetString();

						if ($_POST["type"] == "json")
						{
							echo "['strError' : '" 
								. CUtil::JSEscape(htmlspecialcharsbx($strError)) 
								. "']";
						}
						else
							echo htmlspecialcharsbx($strError);
					}
				}
				elseif ($_POST["mode"] == "reminders")
				{
					CTaskReminders::Delete(array(
						"TASK_ID" => intval($_POST["id"]),
						"USER_ID" => $USER->GetID()
					));
					if (isset($_POST["reminders"]))
					{
						$obReminder = new CTaskReminders();
						foreach($_POST["reminders"] as $reminder)
						{
							$arFields = array(
								"TASK_ID" => intval($_POST["id"]),
								"USER_ID" => $USER->GetID(),
								"REMIND_DATE" => $reminder["r_date"],
								"TYPE" => $reminder["type"],
								"TRANSPORT" => $reminder["transport"]
							);
							$obReminder->Add($arFields);
						}
					}
				}
				else
				{
					$arFields = array();
					if ($_POST["mode"] == "mark" && in_array($_POST["mark"], array("NULL", "P", "N")))
					{
						if (($arTask["SUBORDINATE"] == "Y" || $arTask["CREATED_BY"] == $USER->GetID()) && $arTask["RESPONSIBLE_ID"] != $USER->GetID())
						{
							$arFields = array();
							if ($_POST["mark"] == "NULL")
							{
								$arFields["MARK"] = false;
							}
							else
							{
								$arFields["MARK"] = $_POST["mark"];
							}
							if ($arTask["SUBORDINATE"] == "Y" && $arTask["RESPONSIBLE_ID"] != $USER->GetID() && isset($_POST["report"]))
							{
								$arFields["ADD_IN_REPORT"] = $_POST["report"] == "true" ? "Y" : "N";
							}
						}
					}
					elseif ($_POST["mode"] == "report" && isset($_POST["report"]))
					{
						if ($arTask["SUBORDINATE"] == "Y" && $arTask["RESPONSIBLE_ID"] != $USER->GetID())
						{
							$arFields["ADD_IN_REPORT"] = $_POST["report"] == "true" ? "Y" : "N";
						}
					}
					elseif ($_POST["mode"] == "deadline" && isset($_POST["deadline"]))
					{
						if ($arTask["CREATED_BY"] == $USER->GetID() 
							||
							(
								(
									$arTask["RESPONSIBLE_ID"] == $USER->GetID() 
									|| CTasks::CanCurrentUserEdit($arTask)
								)
								&& $arTask["ALLOW_CHANGE_DEADLINE"] == "Y"
							)
						)
						{
							$arFields["DEADLINE"] = $_POST["deadline"] ? $_POST["deadline"] : false;
						}
					}
					elseif ($_POST["mode"] == "priority" && in_array($_POST["priority"], array(0, 1, 2)))
					{
						if ($arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields = array("PRIORITY" => $_POST["priority"]);
						}
					}
					elseif ($_POST["mode"] == "spent" && isset($_POST["hours"]))
					{
						if ($arTask["RESPONSIBLE_ID"] == $USER->GetID())
						{
							$arFields["DURATION_FACT"] = intval($_POST["hours"]);
						}
					}
					elseif ($_POST["mode"] == "plan_dates" && (isset($_POST["start_date"]) || isset($_POST["end_date"])))
					{
						if ($arTask["CREATED_BY"] == $USER->GetID() 
							||
							(
								(
									$arTask["RESPONSIBLE_ID"] == $USER->GetID() 
									|| CTasks::CanCurrentUserEdit($arTask)
								)
								&& $arTask["ALLOW_CHANGE_DEADLINE"] == "Y"
							)
						)
						{
							if ($_POST["start_date"])
							{
								$arFields["START_DATE_PLAN"] = $_POST["start_date"];
							}
							if ($_POST["end_date"])
							{
								$arFields["END_DATE_PLAN"] = $_POST["end_date"];
							}
						}
					}
					elseif ($_POST["mode"] == "tags" && isset($_POST["tags"]))
					{
						if ($arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields["TAGS"] = $_POST["tags"];
						}
					}
					elseif ($_POST["mode"] == "group" && isset($_POST["groupId"]))
					{
						if ($arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields["GROUP_ID"] = intval($_POST["groupId"]);
						}
					}
					elseif ($_POST["mode"] == "close")
					{
						if (($arTask["RESPONSIBLE_ID"] == $USER->GetID() && in_array($arTask["REAL_STATUS"], array(2, 3, 6))) || ($arTask["CREATED_BY"] == $USER->GetID() && in_array($arTask["REAL_STATUS"], array(2, 3, 4, 6, 7))))
						{
							if ($arTask["CREATED_BY"] == $USER->GetID() || $arTask["TASK_CONTROL"] == "N")
							{
								$arFields["STATUS"] = 5;
							}
							else
							{
								$arFields["STATUS"] = 4;
							}
						}
					}
					elseif ($_POST["mode"] == "start")
					{
						if ($arTask["RESPONSIBLE_ID"] == $USER->GetID() && ($arTask["REAL_STATUS"] == 1 || $arTask["REAL_STATUS"] == 2 || $arTask["REAL_STATUS"] == 4 || $arTask["REAL_STATUS"] == 5 || $arTask["REAL_STATUS"] == 6))
						{
							$arFields["STATUS"] = 3;
						}
					}
					elseif ($_POST["mode"] == "accept")
					{
						if (
							($arTask["RESPONSIBLE_ID"] == $USER->GetID() && $arTask["REAL_STATUS"] == 1)
							||
							(($arTask["REAL_STATUS"] == 4 || $arTask["REAL_STATUS"] == 5 || $arTask["REAL_STATUS"] == 6 || $arTask["REAL_STATUS"] == 7) && $arTask["CREATED_BY"] == $USER->GetID() && $arTask["SUBORDINATE"] == "Y")
						)
						{
							$arFields["STATUS"] = 2;
						}
					}
					elseif ($_POST["mode"] == "renew")
					{
						if (($arTask["REAL_STATUS"] == 4 || $arTask["REAL_STATUS"] == 5 || $arTask["REAL_STATUS"] == 7) && $arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields["STATUS"] = 1;
						}
					}
					elseif ($_POST["mode"] == "defer")
					{
						if (($arTask["REAL_STATUS"] == 2 || $arTask["REAL_STATUS"] == 3) && $arTask["RESPONSIBLE_ID"] == $USER->GetID())
						{
							$arFields["STATUS"] = 6;
						}
					}
					elseif ($_POST["mode"] == "decline")
					{
						if ($arTask["RESPONSIBLE_ID"] == $USER->GetID() && $arTask["REAL_STATUS"] == 1)
						{
							$arFields["STATUS"] = 7;
							$arFields["DECLINE_REASON"] = $_POST["reason"];
						}
					}
					elseif ($_POST["mode"] == "responsible")
					{
						if ($arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields["RESPONSIBLE_ID"] = intval($_POST["responsible"]);
						}
					}
					elseif ($_POST["mode"] == "accomplices")
					{
						if ($arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields["ACCOMPLICES"] = array_filter($_POST["accomplices"]);
							if (!$arFields["ACCOMPLICES"])
							{
								$arFields["ACCOMPLICES"] = array();
							}
						}
					}
					elseif ($_POST["mode"] == "auditors")
					{
						if ($arTask["CREATED_BY"] == $USER->GetID())
						{
							$arFields["AUDITORS"] = array_filter($_POST["auditors"]);
							if (!$arFields["AUDITORS"])
							{
								$arFields["AUDITORS"] = array();
							}
						}
					}

					$arFields["NAME_TEMPLATE"] = $nameTemplate;

					if ($arFields)
					{
						if ( ! isset($arFields['CREATED_BY']) )
							$arFields['CREATED_BY'] = $arTask['CREATED_BY'];

						if ( ! isset($arFields['RESPONSIBLE_ID']) )
							$arFields['RESPONSIBLE_ID'] = $arTask['RESPONSIBLE_ID'];

						$task->Update(intval($_POST["id"]), $arFields);

						if (strlen($arFields["GROUP_ID"]) > 0)
							CSocNetGroup::SetLastActivity($arFields["GROUP_ID"]);
					}
				}
			}
		}
	}
	elseif ($_POST["mode"] == "add" && strlen(trim($_POST["title"])) > 0 && intval($_POST["responsible"]) > 0 && in_array($_POST["priority"], array(0, 1, 2)) && is_object($USER) && $USER->IsAuthorized())
	{
		$arFields = array(
			"TITLE" => trim($_POST["title"]),
			"DESCRIPTION" => trim($_POST["description"]),
			"RESPONSIBLE_ID" => intval($_POST["responsible"]),
			"PRIORITY" => $_POST["priority"],
			"SITE_ID" => $SITE_ID,
			"NAME_TEMPLATE" => $nameTemplate,
			'DESCRIPTION_IN_BBCODE' => 'Y'
		);

		if (isset($_POST['group']))
		{
			$groupID = intval($_POST["group"]);
			if ($groupID > 0)
			{
				if (CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $groupID, "tasks", "create_tasks"))
				{
					$arFields["GROUP_ID"] = $groupID;
				}
			}
		}
		elseif ($GROUP_ID > 0)
		{
			$arFields["GROUP_ID"] = $GROUP_ID;
		}


		if ($DB->FormatDate($_POST["deadline"], CSite::GetDateFormat("FULL")))
		{
			$arFields["DEADLINE"] = $_POST["deadline"];
		}

		$depth = intval($_POST["depth"]);

		if (intval($_POST["parent"]) > 0)
		{
			$arFields["PARENT_ID"] = intval($_POST["parent"]);
		}

		$arFields["STATUS"] = $status;
		$task = new CTasks();
		$ID = $task->Add($arFields);
		if ($ID)
		{
			$rsTask = CTasks::GetByID($ID);
			if ($task = $rsTask->GetNext())
			{
				$APPLICATION->RestartBuffer();

				ob_start();
				if ($task["GROUP_ID"])
				{
					$arGroup = CSocNetGroup::GetByID($task["GROUP_ID"]);
					if ($arGroup)
					{
						$task["GROUP_NAME"] = $arGroup["NAME"];
					}

					CSocNetGroup::SetLastActivity($task["GROUP_ID"]);
				}

				tasksRenderListItem($task, 0, $arPaths, $depth, false, true, $SITE_ID, 0, true, true, "bitrix:tasks.list.item", ".default", $nameTemplate);
				$html = ob_get_clean();

				if (
					isset($_POST['type']) 
					&& (
						($_POST['type'] === 'json_with_html')
						|| ($_POST['type'] === 'json')
					)
				)
				{
					header('Content-Type: text/html; charset=' . LANG_CHARSET);

					$arAdditionalFields = array();
					if ($_POST['type'] === 'json_with_html')
					{
						$arAdditionalFields = array(
							'html' => "'" . CUtil::JSEscape($html) . "'"
						);
					}

					tasksRenderJSON($task, $arChildrenCount["PARENT_" . $task["ID"]], $arPaths, true, true, false, $nameTemplate, $arAdditionalFields);
				}
				else
				{
					header('Content-Type: text/html; charset=' . LANG_CHARSET);
					echo $html;
				}
			}
		}
	}
}
