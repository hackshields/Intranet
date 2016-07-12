<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("tasks"))
{
	ShowError(GetMessage("TASKS_MODULE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("FORUM_MODULE_NOT_INSTALLED"));
	return;
}
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SOCNET_MODULE_NOT_INSTALLED"));
	return;
}

global $USER, $APPLICATION;

__checkForum($arParams["FORUM_ID"]);

$arParams["TASK_VAR"] = trim($arParams["TASK_VAR"]);
if (strlen($arParams["TASK_VAR"]) <= 0)
	$arParams["TASK_VAR"] = "task_id";

$arParams["GROUP_VAR"] = isset($arParams["GROUP_VAR"]) ? trim($arParams["GROUP_VAR"]) : "";
if (strlen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["ACTION_VAR"] = trim($arParams["ACTION_VAR"]);
if (strlen($arParams["ACTION_VAR"]) <= 0)
	$arParams["ACTION_VAR"] = "action";

if (strlen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["TASK_ID"] = intval($arParams["TASK_ID"]);
if (!$arParams["TASK_ID"])
{
	ShowError(GetMessage("TASKS_BAD_TASK_ID"));
	return;
}

$arParams["USER_ID"] = intval($arParams["USER_ID"]) > 0 ? intval($arParams["USER_ID"]) : $USER->GetID();

$arParams["GROUP_ID"] = isset($arParams["GROUP_ID"]) ? intval($arParams["GROUP_ID"]) : 0;

$arResult["TASK_TYPE"] = $taskType = ($arParams["GROUP_ID"] > 0 ? "group" : "user");

$arResult["IS_IFRAME"] = (isset($_GET["IFRAME"]) && $_GET["IFRAME"] == "Y");
if (isset($_GET["CALLBACK"]) && ($_GET["CALLBACK"] == "ADDED" || $_GET["CALLBACK"] == "CHANGED"))
{
	$arResult["CALLBACK"] = $_GET["CALLBACK"];
}

//user paths
$arParams["PATH_TO_USER_TASKS"] = trim($arParams["PATH_TO_USER_TASKS"]);
if (strlen($arParams["PATH_TO_USER_TASKS"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS"] = COption::GetOptionString("tasks", "paths_task_user", null, SITE_ID);
}
$arParams["PATH_TO_USER_TASKS_TASK"] = trim($arParams["PATH_TO_USER_TASKS_TASK"]);
if (strlen($arParams["PATH_TO_USER_TASKS_TASK"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS_TASK"] = COption::GetOptionString("tasks", "paths_task_user_action", null, SITE_ID);
}

//group paths
$arParams["PATH_TO_GROUP_TASKS"] = trim($arParams["PATH_TO_GROUP_TASKS"]);
if (strlen($arParams["PATH_TO_GROUP_TASKS"]) <= 0)
{
	$arParams["PATH_TO_GROUP_TASKS"] = COption::GetOptionString("tasks", "paths_task_group", null, SITE_ID);
}
$arParams["PATH_TO_GROUP_TASKS_TASK"] = isset($arParams["PATH_TO_GROUP_TASKS_TASK"]) ? trim($arParams["PATH_TO_GROUP_TASKS_TASK"]) : "";
if (strlen($arParams["PATH_TO_GROUP_TASKS_TASK"]) <= 0)
{
	$arParams["PATH_TO_GROUP_TASKS_TASK"] = COption::GetOptionString("tasks", "paths_task_group_action", null, SITE_ID);
}
$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = trim($arParams["PATH_TO_USER_TASKS_TEMPLATES"]);
if (strlen($arParams["PATH_TO_USER_TASKS_TEMPLATES"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_tasks_templates&".$arParams["USER_VAR"]."=#user_id#");
}
$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = trim($arParams["PATH_TO_USER_TASKS_TEMPLATES"]);
$arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = trim($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]);
if (strlen($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]) <= 0)
{
	$arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_templates_template&".$arParams["USER_VAR"]."=#user_id#&".$arParams["TEMPLATE_VAR"]."=#template_id#&".$arParams["ACTION_VAR"]."=#action#");
}

$arParams["PATH_TO_TEMPLATES_TEMPLATE"] = str_replace("#user_id#", $USER->GetID(), $arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]);
$arParams["PATH_TO_TASKS_TEMPLATES"] = str_replace("#user_id#", $USER->GetID(), $arParams["PATH_TO_USER_TASKS_TEMPLATES"]);

// Must be equal to MESSAGES_PER_PAGE in mobile.tasks.topic.reviews
if (!isset($arParams["ITEM_DETAIL_COUNT"]))
{
	$arParams["ITEM_DETAIL_COUNT"] = 10;
}

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

if ($taskType == "user")
{
	$arParams["PATH_TO_TASKS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS"]);
	$arParams["PATH_TO_TASKS_TASK"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TASK"]);

	$rsUser = CUser::GetByID($arParams["USER_ID"]);
	if ($user = $rsUser->GetNext())
	{
		$arResult["USER"] = $user;
	}
	else
	{
		ShowError(GetMessage("TASKS_USER_NOT_FOUND"));
		return;
	}
}
else
{
	$arParams["PATH_TO_TASKS"] = str_replace("#group_id#", $arParams["GROUP_ID"], $arParams["PATH_TO_GROUP_TASKS"]);
	$arParams["PATH_TO_TASKS_TASK"] = str_replace("#group_id#", $arParams["GROUP_ID"], $arParams["PATH_TO_GROUP_TASKS_TASK"]);

	$arResult["GROUP"] = CSocNetGroup::GetByID($arParams["GROUP_ID"]);
	if (!$arResult["GROUP"])
	{
		return;
	}
}

if (!$arResult["USER"])
{
	$rsUser = CUser::GetByID($USER->GetID());
	$arResult["USER"] = $rsUser->GetNext();
}

$arResult['MAX_UPLOAD_FILES_IN_COMMENTS'] = (int) COption::GetOptionString('tasks', 'MAX_UPLOAD_FILES_IN_COMMENTS');

$rsTask = CTasks::GetByID($arParams["TASK_ID"]);
$bChanged = false;

if (isset($_REQUEST["ACTION"]) && check_bitrix_sessid())
{
	if ($arTask = $rsTask->Fetch())
	{
		$taskID = $arTask["ID"];

		if ($_REQUEST["ACTION"] == "delete")
		{
			if (CTasks::CanCurrentUserDelete($arTask))
			{
				$task = new CTasks();
				$rc = $task->Delete($arParams["TASK_ID"]);
				if ($rc === false)
				{
					$strError = GetMessage('TASKS_TASK_UNABLE_TO_DELETE') . ': ';
					if($ex = $APPLICATION->GetException())
						$strError .= $ex->GetString();

					ShowError($strError);
					return;
				}

				if ($_REQUEST["back_url"])
				{
					LocalRedirect($_REQUEST["back_url"]);
				}
				else
				{
					LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS"]));
				}
			}
		}
		elseif ($_REQUEST["ACTION"] == "elapsed_add" && CTaskElapsedTime::CanCurrentUserAdd($arTask))
		{
			$MINUTES = intval($_POST["HOURS"]) * 60 + intval($_POST["MINUTES"]);
			if ($MINUTES > 0)
			{
				$arFields = array(
					"USER_ID" => $USER->GetID(),
					"TASK_ID" => $arParams["TASK_ID"],
					"MINUTES" => $MINUTES,
					"COMMENT_TEXT" => trim($_POST["COMMENT_TEXT"])
				);
				$obElapsed = new CTaskElapsedTime();
				$obElapsed->Add($arFields);
			}
			LocalRedirect($APPLICATION->GetCurPageParam(RandString(8), array("ACTION", "sessid"))."#elapsed");
		}
		elseif ($_REQUEST["ACTION"] == "elapsed_update")
		{
			$MINUTES = intval($_POST["HOURS"]) * 60 + intval($_POST["MINUTES"]);
			$ID = intval($_POST["ELAPSED_ID"]);
			if ($MINUTES > 0 && $ID > 0)
			{
				$rsElapsedTime = CTaskElapsedTime::GetByID($ID);
				if (($arElapsedTime = $rsElapsedTime->Fetch()) && $arElapsedTime["USER_ID"] == $USER->GetID())
				{
					$arFields = array(
						"TASK_ID" => $arParams["TASK_ID"],
						"MINUTES" => $MINUTES,
						"COMMENT_TEXT" => trim($_POST["COMMENT_TEXT"])
					);
					$obElapsed = new CTaskElapsedTime();
					$obElapsed->Update($ID, $arFields);
				}
			}
			LocalRedirect($APPLICATION->GetCurPageParam("", array("ACTION", "sessid"))."#elapsed");
		}
		elseif ($_REQUEST["ACTION"] == "elapsed_delete")
		{
			$ID = intval($_GET["ELAPSED_ID"]);
			if ($ID > 0)
			{
				$rsElapsedTime = CTaskElapsedTime::GetByID($ID);
				if (($arElapsedTime = $rsElapsedTime->Fetch()) && $arElapsedTime["USER_ID"] == $USER->GetID())
				{
					$obElapsed = new CTaskElapsedTime();
					$obElapsed->Delete($ID);
				}
			}
			LocalRedirect($APPLICATION->GetCurPageParam("", array("ACTION", "sessid", "ELAPSED_ID"))."#elapsed");
		}
		else
		{
			$task = new CTasks();
			$arFields = array();
			if ($_REQUEST["ACTION"] == "close")
			{
				if (($arTask["RESPONSIBLE_ID"] == $USER->GetID() && in_array($arTask["REAL_STATUS"], array(2, 3, 6))) || ($arTask["CREATED_BY"] == $USER->GetID() && in_array($arTask["REAL_STATUS"], array(2, 3, 4, 6, 7))))
				{
					if ($arTask["CREATED_BY"] == $USER->GetID() || $arTask["TASK_CONTROL"] == "N")
					{
						$arFields["STATUS"] = CTasks::STATE_COMPLETED;
					}
					else
					{
						$arFields["STATUS"] = CTasks::STATE_SUPPOSEDLY_COMPLETED;
					}
				}
			}
			elseif ($_REQUEST["ACTION"] == "start")
			{
				if ($arTask["RESPONSIBLE_ID"] == $USER->GetID() && ($arTask["REAL_STATUS"] == 1 || $arTask["REAL_STATUS"] == 2 || $arTask["REAL_STATUS"] == 4 || $arTask["REAL_STATUS"] == 5 || $arTask["REAL_STATUS"] == 6))
				{
					$arFields["STATUS"] = CTasks::STATE_IN_PROGRESS;
				}
			}
			elseif ($_REQUEST["ACTION"] == "accept")
			{
				if (
					($arTask["RESPONSIBLE_ID"] == $USER->GetID() && $arTask["REAL_STATUS"] == 1)
					||
					(($arTask["REAL_STATUS"] == 4 || $arTask["REAL_STATUS"] == 5 || $arTask["REAL_STATUS"] == 6) && $arTask["CREATED_BY"] == $USER->GetID() && $arTask["SUBORDINATE"] == "Y")
				)
				{
					$arFields["STATUS"] = CTasks::STATE_PENDING;
				}
			}
			elseif ($_REQUEST["ACTION"] == "renew")
			{
				if (($arTask["REAL_STATUS"] == 4 || $arTask["REAL_STATUS"] == 5 || $arTask["REAL_STATUS"] == 7) && $arTask["CREATED_BY"] == $USER->GetID())
				{
					$arFields["STATUS"] = CTasks::STATE_NEW;
				}
			}
			elseif ($_REQUEST["ACTION"] == "defer")
			{
				if (($arTask["REAL_STATUS"] == 2 || $arTask["REAL_STATUS"] == 3) && $arTask["RESPONSIBLE_ID"] == $USER->GetID())
				{
					$arFields["STATUS"] = CTasks::STATE_DEFERRED;
				}
			}
			elseif ($_REQUEST["ACTION"] == "decline")
			{
				if ($arTask["RESPONSIBLE_ID"] == $USER->GetID() && $arTask["REAL_STATUS"] == 1)
				{
					$arFields["STATUS"] = CTasks::STATE_DECLINED;
					$arFields["DECLINE_REASON"] = $_POST["REASON"];
				}
			}
			elseif ($_REQUEST["ACTION"] == "delegate")
			{
				if (intval($_REQUEST["USER_ID"]) != $USER->GetID() 
					&& CTasks::IsSubordinate(intval($_REQUEST["USER_ID"]), $USER->GetID())
				)
				{
					$arFields["RESPONSIBLE_ID"] = intval($_REQUEST["USER_ID"]);
					$arFields["STATUS"] = CTasks::STATE_PENDING;
					if (sizeof($arTask["AUDITORS"]) > 0)
					{
						if ( ! in_array($USER->GetID(), $arTask["AUDITORS"]) )
						{
							$arFields["AUDITORS"] = $arTask["AUDITORS"];
							$arFields["AUDITORS"][] = $USER->GetID();
						}
					}
					else
					{
						$arFields["AUDITORS"] = array($USER->GetID());
					}
				}
			}

			$arFields["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"];
			
			if ($arFields)
			{
				$bChanged = $task->Update($arParams["TASK_ID"], $arFields);
			}
		}
	}
	LocalRedirect($APPLICATION->GetCurPageParam($bChanged ? "CALLBACK=CHANGED" : "", array("ACTION", "sessid", "ELAPSED_ID")));
}

if ($arTask = $rsTask->GetNext())
{
	CTasks::UpdateViewed($arTask["ID"], $USER->GetID());

	$oTaskItem = new CTaskItem( (int) $arTask['ID'], (int) $USER->GetID() );
	$arTask['META:ALLOWED_ACTIONS_CODES'] = $oTaskItem->getAllowedTaskActions();
	$arTask['META:ALLOWED_ACTIONS'] = $oTaskItem->getAllowedTaskActionsAsStrings();

	$arTask['META:IN_DAY_PLAN'] = 'N';
	$arTask['META:CAN_ADD_TO_DAY_PLAN'] = 'N';
	if (
		(
			($arTask["RESPONSIBLE_ID"] == $USER->GetID())
			|| (in_array($USER->GetID(), $arTask['ACCOMPLICES']))
		)
		&& CModule::IncludeModule("timeman") 
		&& (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()))
	{
		$arTask['META:CAN_ADD_TO_DAY_PLAN'] = 'Y';

		$arTasksInPlan = CTaskPlannerMaintance::getCurrentTasksList();

		// If in day plan already
		if (
			is_array($arTasksInPlan)
			&& in_array($arTask["ID"], $arTasksInPlan)
		)
		{
			$arTask['META:IN_DAY_PLAN'] = 'Y';
			$arTask['META:CAN_ADD_TO_DAY_PLAN'] = 'N';
		}
	}



	if (!$arTask["CHANGED_DATE"])
	{
		$arTask["CHANGED_DATE"] = $arTask["CREATED_DATE"];
		$arTask["CHANGED_BY"] = $arTask["CREATED_BY"];
	}

	// Temporary fix for http://jabber.bx/view.php?id=29741
	if (strpos($arTask['DESCRIPTION'], 'player/mediaplayer/player.swf') !== false)
	{
		$arTask['~DESCRIPTION'] = str_replace(
			' src="/bitrix/components/bitrix/player/mediaplayer/player.swf" ', 
			' src="/bitrix/components/bitrix/player/mediaplayer/player" ',
			$arTask['~DESCRIPTION']
		);
		$arTask['DESCRIPTION'] = str_replace(
			' src=&quot;/bitrix/components/bitrix/player/mediaplayer/player.swf&quot; ', 
			' src=&quot;/bitrix/components/bitrix/player/mediaplayer/player&quot; ', 
			$arTask['DESCRIPTION']
		);
	}

	// HTML-format must be supported in future, because old tasks' data not converted from HTML to BB
	if ($arTask['DESCRIPTION_IN_BBCODE'] === 'N')
	{
		// HTML detected, sanitize if need
		$arTask['~DESCRIPTION'] = CTasksTools::SanitizeHtmlDescriptionIfNeed($arTask['~DESCRIPTION']);
	}
	else
	{
		$arTask['META:DESCRIPTION_FOR_BBCODE'] = $arTask['~DESCRIPTION'];
		$parser = new CTextParser();
		$arTask['~DESCRIPTION'] = str_replace(
			"\t",
			' &nbsp; &nbsp;',
			$parser->convertText($arTask['META:DESCRIPTION_FOR_BBCODE'])
		);

		$arTask['DESCRIPTION'] = $arTask['~DESCRIPTION'];
	}

	// group
	if ($arTask["GROUP_ID"])
	{
		$arGroup = CSocNetGroup::GetByID($arTask["GROUP_ID"]);
		$arTask["GROUP_NAME"] = $arGroup["NAME"];
	}

	// avatars
	if ($arTask["CREATED_BY_PHOTO"] > 0)
	{
		$imageFile = CFile::GetFileArray($arTask["CREATED_BY_PHOTO"]);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array("width" => 30, "height" => 30),
				BX_RESIZE_IMAGE_EXACT,
				false
			);
			$arTask["CREATED_BY_PHOTO"] = $arFileTmp["src"];
		}
	}
	if ($arTask["RESPONSIBLE_PHOTO"] > 0)
	{
		$imageFile = CFile::GetFileArray($arTask["RESPONSIBLE_PHOTO"]);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array("width" => 30, "height" => 30),
				BX_RESIZE_IMAGE_EXACT,
				false
			);
			$arTask["RESPONSIBLE_PHOTO"] = $arFileTmp["src"];
		}
	}

	if ($arTask["FILES"])
	{
		$rsFiles = CFile::GetList(array(), array("@ID" => implode(",", $arTask["FILES"])));
		$arTask["FILES"] = array();
		while($file = $rsFiles->GetNext())
		{
			$arTask["FILES"][] = $file;
		}
	}


	// comments files
	$arTask["FORUM_FILES"] = array();
	if ($arTask["FORUM_TOPIC_ID"])
	{
		$rsFiles = CForumFiles::GetList(array("ID"=>"ASC"), array("TOPIC_ID" => $arTask["FORUM_TOPIC_ID"]));
		while($arFile = $rsFiles->GetNext())
		{
			$arTask["FORUM_FILES"][] = $arFile;
		}
	}

	// repeating
	$rsTemplate = CTaskTemplates::GetList(array(), array("TASK_ID"=>$arParams["TASK_ID"]));
	if ($arTemplate = $rsTemplate->Fetch())
	{
		$arTemplate["REPLICATE_PARAMS"] = unserialize($arTemplate["REPLICATE_PARAMS"]);
		$arTask["TEMPLATE"] = $arTemplate;
	}

	// Was task created from template?
	if ($arTask['FORKED_BY_TEMPLATE_ID'])
	{
		$rsTemplate = CTaskTemplates::GetByID($arTask['FORKED_BY_TEMPLATE_ID']);

		if ($arTemplate = $rsTemplate->Fetch())
		{
			$arTemplate['REPLICATE_PARAMS'] = unserialize($arTemplate['REPLICATE_PARAMS']);
			$arTask['FORKED_BY_TEMPLATE'] = $arTemplate;
		}
	}

	$arResult["TASK"] = $arTask;

	// templates
	$rsTemplates = CTaskTemplates::GetList(array("ID" => "DESC"), array("CREATED_BY" => $USER->GetID()));
	$rsTemplates->NavStart(10);
	$arResult["TEMPLATES"] = array();
	while($arTask = $rsTemplates->GetNext())
	{
		$arResult["TEMPLATES"][] = $arTask;
	}

	$arTasksIDs = array();
	$arGroupsIDs = array();

	// subtasks
	$rsSubtasks = CTasks::GetList(array("GROUP_ID" => "ASC"), array("PARENT_ID" => $arParams["TASK_ID"]));
	$arResult["SUBTASKS"] = array();
	while($arSubTask = $rsSubtasks->GetNext())
	{
		$arResult["SUBTASKS"][] = $arSubTask;
		$arTasksIDs[] = $arSubTask["ID"];
		if ($arSubTask["GROUP_ID"] && !in_array($arSubTask["GROUP_ID"], $arGroupsIDs))
		{
			$arGroupsIDs[] = $arSubTask["GROUP_ID"];
		}
	}

	// previous tasks
	$rsPrevtasks = CTasks::GetList(array("GROUP_ID" => "ASC"), array("DEPENDS_ON" => $arParams["TASK_ID"]));
	$arResult["PREV_TASKS"] = array();
	while($arPrevTask = $rsPrevtasks->GetNext())
	{
		$arResult["PREV_TASKS"][] = $arPrevTask;
		$arTasksIDs[] = $arPrevTask["ID"];
		if ($arPrevTask["GROUP_ID"] && !in_array($arPrevTask["GROUP_ID"], $arGroupsIDs))
		{
			$arGroupsIDs[] = $arPrevTask["GROUP_ID"];
		}
	}

	$rsChildrenCount = CTasks::GetChildrenCount(array(), $arTasksIDs);
	if ($rsChildrenCount)
	{
		while($arChildrenCount = $rsChildrenCount->Fetch())
		{
			$arResult["CHILDREN_COUNT"]["PARENT_".$arChildrenCount["PARENT_ID"]] = $arChildrenCount["CNT"];
		}
	}

	// groups
	$arResult["GROUPS"] = array();
	$arOpenedProjects =  CUserOptions::GetOption("tasks", "opened_projects", array());
	if ($arResult["TASK_TYPE"] != "group" && sizeof($arGroupsIDs))
	{
		$rsGroups = CSocNetGroup::GetList(array("ID" => "ASC"), array("ID" => $arGroupsIDs));
		while($arGroup = $rsGroups->GetNext())
		{
			$arGroup["EXPANDED"] = array_key_exists($arGroup["ID"], $arOpenedProjects) && $arOpenedProjects[$arGroup["ID"]] == "false" ? false : true;
			$arResult["GROUPS"][$arGroup["ID"]] = $arGroup;
		}
	}
	// log
	$arResult["LOG"] = array();
	$rsLog = CTaskLog::GetList(
		array('CREATED_DATE' => 'DESC'),
		array("TASK_ID" => $arResult["TASK"]["ID"])
	);

	while($arLog = $rsLog->GetNext())
		$arResult["LOG"][] = $arLog;

	// elapsed time
	$arResult["ELAPSED_TIME"] = array();
	$arResult["FULL_ELAPSED_TIME"] = 0;
	$rsElapsedtime = CTaskElapsedTime::GetList(array(), array("TASK_ID" => $arResult["TASK"]["ID"]));
	while($arElapsedtime = $rsElapsedtime->GetNext())
	{
		$arResult["ELAPSED_TIME"][] = $arElapsedtime;
		$arResult["FULL_ELAPSED_TIME"] += $arElapsedtime["MINUTES"];
	}

	// user fields
	$arResult["USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("TASKS_TASK", $arParams["TASK_ID"], LANGUAGE_ID);
	$arResult["SHOW_USER_FIELDS"] = false;
	foreach($arResult["USER_FIELDS"] as $arUserField)
	{
		if ($arUserField["VALUE"] !== false)
		{
			$arResult["SHOW_USER_FIELDS"] = true;
			break;
		}
	}

	// reminders
	$arResult["REMINDERS"] = array();
	$rsReminders = CTaskReminders::GetList(array("date" => "asc"), array("USER_ID" => $USER->GetID(), "TASK_ID" => $arParams["TASK_ID"]));
	while($arReminder = $rsReminders->Fetch())
	{
		$arResult["REMINDERS"][] = array(
			"date" => $arReminder["REMIND_DATE"],
			"type" => $arReminder["TYPE"],
			"transport" => $arReminder["TRANSPORT"]
		);
	}
}
else
{
	if ($arResult["IS_IFRAME"])
		ShowInFrame($this, true, GetMessage("TASKS_TASK_NOT_FOUND"));
	else
		ShowError(GetMessage("TASKS_TASK_NOT_FOUND"));
	return;
}

$sTitle = str_replace("#TASK_NUM#", $arResult["TASK"]["ID"], GetMessage("TASKS_TASK_NUM")) . ' - ' . $arResult["TASK"]['TITLE'];

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle($sTitle);
}

if (!isset($arParams["SET_NAVCHAIN"]) || $arParams["SET_NAVCHAIN"] != "N")
{
	if ($taskType == "user")
	{
		$APPLICATION->AddChainItem(CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"]), CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER_PROFILE"], array("user_id" => $arParams["USER_ID"])));
		$APPLICATION->AddChainItem($sTitle);
	}
	else
	{
		$APPLICATION->AddChainItem($arResult["GROUP"]["NAME"], CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"])));
		$APPLICATION->AddChainItem($sTitle);
	}
}

if ($arResult["IS_IFRAME"])
{
	ShowInFrame($this);
}
else
{
	$this->IncludeComponentTemplate();
}
?>