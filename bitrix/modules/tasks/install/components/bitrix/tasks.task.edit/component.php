<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("tasks"))
{
	ShowError(GetMessage("TASKS_MODULE_NOT_INSTALLED"));
	return;
}
if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SOCNET_MODULE_NOT_INSTALLED"));
	return;
}

CModule::IncludeModule("fileman");

global $USER, $APPLICATION;

__checkForum($arParams["FORUM_ID"]);

if (!is_object($USER) || !$USER->IsAuthorized())
{
	$APPLICATION->AuthForm("");
	return;
}

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

$arResult["ACTION"] = ($arParams["TASK_ID"] > 0 ? "edit" : "create");

$arParams["USER_ID"] = intval($arParams["USER_ID"]) > 0 ? intval($arParams["USER_ID"]) : $USER->GetID();

$arParams["GROUP_ID"] = isset($arParams["GROUP_ID"]) ? intval($arParams["GROUP_ID"]) : 0;

$taskType = $arResult["TASK_TYPE"] = ($arParams["GROUP_ID"] > 0 ? "group" : "user");

$arResult["IS_IFRAME"] = (isset($_GET["IFRAME"]) && $_GET["IFRAME"] == "Y");
if (isset($_GET["CALLBACK"]) && ($_GET["CALLBACK"] == "ADDED" || $_GET["CALLBACK"] == "CHANGED"))
{
	$arResult["CALLBACK"] = $_GET["CALLBACK"];
}

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

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

$loggedInUserId = (int) $USER->getId();

if (!$arResult["USER"])
{
	$rsUser = CUser::GetByID($loggedInUserId);
	$arResult["USER"] = $rsUser->GetNext();
}

$arResult["bVarsFromForm"] = false;

if ($arResult["ACTION"] == "edit")
{
	$rsTask = CTasks::GetByID($arParams["TASK_ID"]);

	if (!($arTask = $rsTask->GetNext()) || !CTasks::CanCurrentUserEdit($arTask))
	{
		ShowError(GetMessage("TASKS_TASK_NOT_FOUND"));
		return;
	}
}

if (array_key_exists("back_url", $_REQUEST) && strlen($_REQUEST["back_url"]) > 0)
{
	$arResult["RETURN_URL"] = htmlspecialcharsbx(trim($_REQUEST["back_url"]));
}

$arData = array();

$arResult['FORM_GUID'] = CTasksTools::genUuid();

$arResult["USER_FIELDS"] = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("TASKS_TASK", $arParams["TASK_ID"] ? $arParams["TASK_ID"] : 0, LANGUAGE_ID);

//Form submitted
$arResult['needStep'] = false;
if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid() && ($arResult["ACTION"] == "create" || $arResult["ACTION"] == "edit"))
{
	$funcCreateSubtasks = function($arFields, $arAllResponsibles, $index, $loggedInUserId, $woStepper = false)
	{
		$allResponsiblesCount = count($arAllResponsibles);
		$arResponsibles = array_slice($arAllResponsibles, $index);

		$cutoffTime = microtime(true) + 5;

		foreach($arResponsibles as $responsible)
		{
			$arFields['RESPONSIBLE_ID'] = $responsible;

			++$index;

			try
			{
				$oTask = CTaskItem::add($arFields, $loggedInUserId);
			}
			catch (Exception $e)
			{
			}

			// Timeout only if multistepper can be used
			if (
				( ! $woStepper )
				&& (microtime(true) > $cutoffTime)
			)
			{
				break;
			}
		}

		if ($woStepper)
			$needStep = false;
		else
		{
			$needStep = true;

			if ($index >= $allResponsiblesCount)
				$needStep = false;
		}

		return (array(
			$needStep,
			$index,
			$allResponsiblesCount
		));
	};

	if (isset($_POST['FORM_GUID']))
		$arResult['PREV_FORM_GUID'] = $_POST['FORM_GUID'];

	if (
		isset($_POST['FORM_GUID'])
		&& isset($_POST['_JS_STEPPER_DO_NEXT_STEP'])
		&& ($_POST['_JS_STEPPER_DO_NEXT_STEP'] === 'Y')
	)
	{
		$arFields       = $_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['arFields'];
		$arResponsibles = $_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['RESPONSIBLES'];
		$index          = $_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['index'];
		$redirectPath   = $_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['redirectPath'];

		list(
			$arResult['needStep'],
			$arResult['stepIndex'],
			$arResult['stepIndexesTotal']
		) = $funcCreateSubtasks($arFields, $arResponsibles, $index, $loggedInUserId);

		$_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['index'] = $arResult['stepIndex'];

		if ($arResult['needStep'])
		{
			if ($arResult['IS_IFRAME'])
				ShowInFrame($this);
			else
				$this->IncludeComponentTemplate();

			exit();
		}
		else
		{
			unset($_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]);
			LocalRedirect($redirectPath);
		}
	}

	$_POST["WEEK_DAYS"] = explode(",", $_POST["WEEK_DAYS"]);

	// Prevent duplicated POSTs
	$bDuplicatePostRequest = false;
	$parentTaskGUID = false;
	if (
		isset($_POST['FORM_GUID'])
		&& ($arResult['ACTION'] === 'create')
		&& ($_POST["MULTITASK"] == "Y")
		&& (
			(
				isset($_POST['RESPONSIBLES_IDS'])
				&& strlen($_POST['RESPONSIBLES_IDS'])
			)
			||
			(
				isset($_POST['RESPONSIBLES'])
				&& (count($_POST["RESPONSIBLES"]) > 0)
			)
		)		
	)
	{
		$parentTaskGUID = $_POST['FORM_GUID'];
		$rs = CTasks::GetList(array(), array('GUID' => $parentTaskGUID));
		if ($ar = $rs->Fetch())
			$bDuplicatePostRequest = true;
	}

	if (!$bDuplicatePostRequest)
	{
		$arResult['needStep'] = false;

		if (isset($_POST["save"]) || isset($_POST["apply"]))
		{
			$_POST["ACCOMPLICES"] = array_filter(explode(",", $_POST["ACCOMPLICES_IDS"]));
			$_POST["RESPONSIBLES"] = array_filter(explode(",", $_POST["RESPONSIBLES_IDS"]));
			$_POST["DEPENDS_ON"] = array_filter(explode(",", $_POST["PREV_TASKS_IDS"]));
			$_POST["REPLICATE_WEEK_DAYS"] = array_filter(explode(",", $_POST["REPLICATE_WEEK_DAYS"]));

			$arFields = array(
				"TITLE" => trim($_POST["TITLE"]),
				"DESCRIPTION" => $_POST["DESCRIPTION"],
				"DEADLINE" => ConvertDateTime($_POST["DEADLINE"]),
				"START_DATE_PLAN" => ConvertDateTime($_POST["START_DATE_PLAN"]),
				"END_DATE_PLAN" => ConvertDateTime($_POST["END_DATE_PLAN"]),
				"DURATION_PLAN" => $_POST["DURATION_PLAN"],
				"DURATION_TYPE" => $_POST["DURATION_TYPE"],
				"PRIORITY" => $_POST["PRIORITY"],
				"ACCOMPLICES" => $_POST["ACCOMPLICES"],
				"AUDITORS" => sizeof($_POST["AUDITORS"]) > 0 ? array_filter($_POST["AUDITORS"]) : array(),
				"TAGS" => $_POST["TAGS"],
				"ALLOW_CHANGE_DEADLINE" => isset($_POST["ALLOW_CHANGE_DEADLINE"]) ? "Y" : "N",
				"TASK_CONTROL" => isset($_POST["TASK_CONTROL"]) ? "Y" : "N",
				"ADD_IN_REPORT" => isset($_POST["ADD_IN_REPORT"]) ? "Y" : "N",
				"FILES" => $_POST["FILES"] ? $_POST["FILES"] : array(),
				"PARENT_ID" => intval($_POST["PARENT_ID"]) > 0 ? intval($_POST["PARENT_ID"]) : false,
				"DEPENDS_ON" => $_POST["DEPENDS_ON"],
				"REPLICATE" => isset($_POST["REPLICATE"]) ? "Y" : "N",
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
			);

			if (
				isset($_POST['DESCRIPTION_IN_BBCODE'])
				&& in_array($_POST['DESCRIPTION_IN_BBCODE'], array('Y', 'N'))
			)
			{
				$arFields['DESCRIPTION_IN_BBCODE'] = $_POST['DESCRIPTION_IN_BBCODE'];
			}
			else
				$arFields['DESCRIPTION_IN_BBCODE'] = 'N';	// for compatibility

			if (isset($_POST["GROUP_ID"]))
			{
				if (($groupID = intval($_POST["GROUP_ID"])) > 0)
				{
					if (CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $groupID, "tasks", "create_tasks"))
					{
						$arFields["GROUP_ID"] = $groupID;
					}
				}
				else
				{
					$arFields["GROUP_ID"] = false;
				}
			}

			$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields('TASKS_TASK', $arFields);

			foreach ($arResult["USER_FIELDS"] as $ufName => $ufMetaData)
			{
				if ($ufMetaData['USER_TYPE_ID'] !== 'file')
					continue;

				if (isset($arFields[$ufName]))
				{
					if ($ufMetaData['MULTIPLE'] === 'Y')
					{
						foreach ($arFields[$ufName] as $key => $value)
						{
							if ( ! is_array($value) )
								$arFields[$ufName][$key] = '';
						}
					}
					else
					{
						if ( ! is_array($arFields[$ufName]) )
							$arFields[$ufName] = '';
					}
				}
			}

			$arFields["REPLICATE_PARAMS"] = array();
			foreach ($_POST as $field=>$value)
			{
				if (substr($field, 0, 10) == "REPLICATE_") // parameters of replication
				{
					$arFields["REPLICATE_PARAMS"][substr($field, 10)] = substr($field, -5) == "_DATE" ?  ConvertDateTime($value) : $value;
				}
			}

			$arResult["ERRORS"] = array();
			if ($arResult["ACTION"] == "edit")
			{
				$arFields["RESPONSIBLE_ID"] = $_POST["RESPONSIBLE_ID"];

				if (
					$USER->IsAdmin()
					|| CTasksTools::IsPortalB24Admin()
					|| ($USER->GetID() == $arTask['CREATED_BY'])
				)
				{
					$arFields["CREATED_BY"] = $_POST["CREATED_BY"];
				}

				try
				{
					$oTask = new CTaskItem($arParams['TASK_ID'], $USER->GetID());
					$oTask->update($arFields);
				}
				catch (Exception $e)
				{
					if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
						$arResult['ERRORS'] = unserialize($e->GetMessage());
					else
					{
						$arResult['ERRORS'][] = array(
							'text' => 'UNKNOWN ERROR OCCURED',
							'id'   => 'ERROR_TASKS_UNKNOWN'
						);
					}
				}

				$taskID = $arParams['TASK_ID'];
			}
			else
			{
				$arSectionIDs = CTasks::GetSubordinateDeps();

				if ($_POST["MULTITASK"] == "Y" && sizeof($_POST["RESPONSIBLES"]) > 0)
				{
					$arFields["MULTITASK"] = "Y";
					$arFields["RESPONSIBLE_ID"] = $USER->GetID();

					if ($USER->IsAdmin() || CTasksTools::IsPortalB24Admin())
						$arFields["CREATED_BY"] = $_POST["CREATED_BY"];
				}
				else
				{
					$arFields["MULTITASK"] = "N";

					$arFields["CREATED_BY"] = $_POST["CREATED_BY"];

					if ($arFields["CREATED_BY"] != $USER->GetID()
						&& !$USER->IsAdmin()
						&& !CTasksTools::IsPortalB24Admin()
					)
					{
						$arFields["RESPONSIBLE_ID"] = $USER->GetID();
					}
					else
					{
						$arFields["RESPONSIBLE_ID"] = $_POST["RESPONSIBLE_ID"];
					}
				}

				$arFields["SITE_ID"] = SITE_ID;

				try
				{
					$arParentTaskFields = $arFields;

					if (($arFields["MULTITASK"] = "Y") && ($parentTaskGUID !== false))
						$arParentTaskFields['GUID'] = $parentTaskGUID;

					$oTask = CTaskItem::add($arParentTaskFields, $USER->GetID());
					$taskID = $oTask->getId();
				}
				catch (Exception $e)
				{
					$taskID = false;

					if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
						$arResult['ERRORS'] = unserialize($e->GetMessage());
					else
					{
						$arResult['ERRORS'][] = array(
							'text' => 'UNKNOWN ERROR OCCURED',
							'id'   => 'ERROR_TASKS_UNKNOWN'
						);
					}
				}

				$arTemplateFields = $arFields;

				if ($arTemplateFields["MULTITASK"] == "Y")
				{
					$arTemplateFields["RESPONSIBLES"] = serialize($_POST["RESPONSIBLES"]);
				}

				if ($taskID)
				{
					if ($_POST["ADD_TO_TIMEMAN"] == "Y")
					{
						CTaskPlannerMaintance::plannerActions(array('add' => array($taskID)));
					}

					if ($arFields["REPLICATE"] == "Y")
					{
						unset(
							$arTemplateFields["DEADLINE"],
							$arTemplateFields["START_DATE_PLAN"],
							$arTemplateFields["END_DATE_PLAN"]
						);

						$arTemplateFields["TASK_ID"] = $taskID;
						$arTemplateFields["ACCOMPLICES"] = sizeof($arTemplateFields["ACCOMPLICES"]) ?  serialize($arTemplateFields["ACCOMPLICES"]) : false;
						$arTemplateFields["AUDITORS"] = sizeof($arTemplateFields["AUDITORS"]) ?  serialize($arTemplateFields["AUDITORS"]) : false;
						$arTemplateFields["TAGS"] = strlen(trim($arTemplateFields["TAGS"])) > 0 ?  serialize(explode(",", $arTemplateFields["TAGS"])) : false;
						$arTemplateFields["FILES"] = sizeof($arTemplateFields["FILES"]) ?  serialize($arTemplateFields["FILES"]) : false;
						$arTemplateFields["DEPENDS_ON"] = sizeof($arTemplateFields["DEPENDS_ON"]) ?  serialize($arTemplateFields["DEPENDS_ON"]) : false;
						$arTemplateFields["REPLICATE_PARAMS"] = serialize($arTemplateFields["REPLICATE_PARAMS"]);

						$taskTemplate = new CTaskTemplates();
						$taskTemplate->Add($arTemplateFields);
					}

					$arFields["MULTITASK"] = $arFields["REPLICATE"] = "N";
					$arFields["PARENT_ID"] = $taskID;

					if (is_array($arFields["ACCOMPLICES"]))
					{
						if (!in_array($USER->GetID(), $arFields["ACCOMPLICES"]))
						{
							$arFields["ACCOMPLICES"][] = $USER->GetID();
						}
					}
					else
					{
						$arFields["ACCOMPLICES"] = array($USER->GetID());
					}

					// If multistep supported and multitask creation in process, store data in $_SESSION
					$responsiblesCount = count($_POST['RESPONSIBLES']);
					if (
						isset($_POST['_JS_STEPPER_SUPPORTED'])
						&& ($_POST['_JS_STEPPER_SUPPORTED'] === 'Y')
						&& isset($_POST['FORM_GUID'])
						&& $responsiblesCount
					)
					{
						$_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']] = array(
							'arFields'     => $arFields,
							'RESPONSIBLES' => $_POST['RESPONSIBLES'],
							'index'        => 0
						);

						list(
							$arResult['needStep'],
							$arResult['stepIndex'],
							$arResult['stepIndexesTotal']
						) = $funcCreateSubtasks($arFields, $_POST['RESPONSIBLES'], 0, $loggedInUserId);

						$_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['index'] = $arResult['stepIndex'];
					}
					else
						$funcCreateSubtasks($arFields, $_POST['RESPONSIBLES'], 0, $loggedInUserId, $woStepper = true);
				}
			}

			if (sizeof($arResult["ERRORS"]) == 0)
			{
				if (is_array($arFields["FILES"]) && count($arFields["FILES"]))
				{
					$userId = (int) $USER->GetID();

					foreach ($arFields["FILES"] as $fileId)
						CTaskFiles::removeTemporaryFile($userId, (int) $fileId);
				}

				if (sizeof($_POST["REMINDERS"]))
				{
					if ($arResult["ACTION"] == "edit")
					{
						CTaskReminders::Delete(array(
							"TASK_ID" => $taskID,
							"USER_ID" => $USER->GetID()
						));
					}
					$obReminder = new CTaskReminders();
					foreach($_POST["REMINDERS"] as $reminder)
					{
						$arReminderFields = array(
							"TASK_ID" => $taskID,
							"USER_ID" => $USER->GetID(),
							"REMIND_DATE" => $reminder["date"],
							"TYPE" => $reminder["type"],
							"TRANSPORT" => $reminder["transport"]
						);
						$obReminder->Add($arReminderFields);
					}
				}
				if ($arResult["ACTION"] == "create" && $_POST["apply"] == "save_and_back")
				{
					$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit"));
					if ($arResult["IS_IFRAME"])
					{
						$redirectPath .= (strpos($redirectPath, "?") === false ? "?" :  "&")."IFRAME=Y";
						$redirectPath .= "&CALLBACK=".($arResult["ACTION"] == "edit" ? "CHANGED" : "ADDED");
						$redirectPath .= "&TASK_ID=" . $taskID;
					}
				}
				elseif ($arResult['ACTION'] == 'create' && $_POST['apply'] == 'save_and_create_new')
				{
					$redirectPath = CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_TASKS_TASK'], array('task_id' => 0, 'action' => 'edit'));
					if ($arResult['IS_IFRAME'])
					{
						if (strpos($redirectPath, '?') === false)
							$redirectPath .= '?';
						else
							$redirectPath .= '&';

						$redirectPath .= 'IFRAME=Y';
					}
				}
				elseif (strlen($arResult["RETURN_URL"]) > 0)
				{
					$redirectPath = $arResult["RETURN_URL"];
				}
				else
				{
					$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $taskID, "action" => "view"));
					if ($arResult["IS_IFRAME"])
					{
						$redirectPath .= (strpos($redirectPath, "?") === false ? "?" :  "&")."IFRAME=Y";
						$redirectPath .= "&CALLBACK=".($arResult["ACTION"] == "edit" ? "CHANGED" : "ADDED");
					}
				}

				// break execution here, template will resend POST-request to current page
				// for next step of subtasks creation
				if ($arResult['needStep'])
				{
					$_SESSION['TASKS']['EDIT_COMPONENT']['STEPPER'][$_POST['FORM_GUID']]['redirectPath'] = $redirectPath;

					if ($arResult['IS_IFRAME'])
						ShowInFrame($this);
					else
						$this->IncludeComponentTemplate();

					exit();
				}

				LocalRedirect($redirectPath);
			}
			else
			{
				$arResult["bVarsFromForm"] = true;
				$arData = $_POST;
			}
		}
	}
	else
	{
		$arResult['ERRORS'] = array();
		$arResult['ERRORS'][] = array(
			'text' => 'Duplicate POST-request',
			'id'   => 'ERROR_TASKS_DUPLICATE_POST_REQUEST'
		);

		$arResult["bVarsFromForm"] = true;
		$arData = $_POST;
	}
}
else
{
	if (isset($arResult["CALLBACK"]) && $arResult["CALLBACK"] && intval($_GET["TASK_ID"]) > 0)
	{
		$rsTask = CTasks::GetByID(intval($_GET["TASK_ID"]));
		if ($callbackTask = $rsTask->GetNext())
		{
			$arResult["TASK"] = $callbackTask;
			$rsChildrenCount = CTasks::GetChildrenCount(array(), ($arResult["TASK"]["ID"]));
			if ($arChildrenCount = $rsChildrenCount->Fetch())
			{
				$arResult["TASK"]["CHILDREN_COUNT"] = $arChildrenCount["CNT"];
			}
			$rsTaskFiles = CTaskFiles::GetList(array(), array("TASK_ID" => $arResult["TASK"]["ID"]));
			$arResult["TASK"]["FILES"] = array();
			while ($arTaskFile = $rsTaskFiles->Fetch())
			{
				$rsFile = CFile::GetByID($arTaskFile["FILE_ID"]);
				if ($arFile = $rsFile->Fetch())
				{
					$arResult["TASK"]["FILES"][] = $arFile;
				}
			}
		}
	}
	if ($arResult["ACTION"] == "edit")
	{
		$arData = $arTask;
		$arData["DESCRIPTION"] = $arData["~DESCRIPTION"];
		$arData["CREATED_BY_NAME"] = $arData["~CREATED_BY_NAME"];
		$arData["CREATED_BY_LAST_NAME"] = $arData["~CREATED_BY_LAST_NAME"];
		$arData["CREATED_BY_SECOND_NAME"] = $arData["~CREATED_BY_SECOND_NAME"];
		$arData["CREATED_BY_LOGIN"] = $arData["~CREATED_BY_LOGIN"];

		// reminders
		$arData["REMINDERS"] = array();
		$rsReminders = CTaskReminders::GetList(array("date" => "asc"), array("USER_ID" => $USER->GetID(), "TASK_ID" => $arTask["ID"]));
		while($arReminder = $rsReminders->Fetch())
		{
			$arData["REMINDERS"][] = array(
				"date" => $arReminder["REMIND_DATE"],
				"type" => $arReminder["TYPE"],
				"transport" => $arReminder["TRANSPORT"]
			);
		}
	}
	else	// case when $arResult['ACTION'] === 'create'
	{
		if (intval($_GET["TEMPLATE"]) > 0) // create task from a template
		{
			$rsTemplate = CTaskTemplates::GetByID(intval($_GET["TEMPLATE"]));
			if ($arTemplate = $rsTemplate->GetNext())
			{
				if ($arTemplate["CREATED_BY"] == $USER->GetID())
				{
					if (isset($arTemplate["~DESCRIPTION_IN_BBCODE"]))
						$arTemplate["DESCRIPTION_IN_BBCODE"] = $arTemplate["~DESCRIPTION_IN_BBCODE"];

					$arTemplate["ACCOMPLICES"] = $arTemplate["~ACCOMPLICES"] ? unserialize($arTemplate["~ACCOMPLICES"]) : array();
					$arTemplate["AUDITORS"] = $arTemplate["~AUDITORS"] ? unserialize($arTemplate["~AUDITORS"]) : array();
					$arTemplate["RESPONSIBLES"] = $arTemplate["~RESPONSIBLES"] ? unserialize($arTemplate["~RESPONSIBLES"]) : array();
					$arTemplate["FILES"] = $arTemplate["~FILES"] ? unserialize($arTemplate["~FILES"]) : array();
					$arTemplate["TAGS"] = $arTemplate["~TAGS"] = $arTemplate["~TAGS"] ? unserialize($arTemplate["~TAGS"]) : "";
					$arTemplate["DEPENDS_ON"] = $arTemplate["~DEPENDS_ON"] ? unserialize($arTemplate["~DEPENDS_ON"]) : array();
					$arTemplate["DESCRIPTION"] = $arTemplate["~DESCRIPTION"];
					$arTemplate["CREATED_BY_NAME"] = $arTemplate["~CREATED_BY_NAME"];
					$arTemplate["CREATED_BY_LAST_NAME"] = $arTemplate["~CREATED_BY_LAST_NAME"];
					$arTemplate["CREATED_BY_SECOND_NAME"] = $arTemplate["~CREATED_BY_SECOND_NAME"];
					$arTemplate["CREATED_BY_LOGIN"] = $arTemplate["~CREATED_BY_LOGIN"];

					if (sizeof($arTemplate["FILES"]))
					{
						foreach($arTemplate["FILES"] as $key=>$file)
						{
							$newFile = CFile::CopyFile($file);
							$arTemplate["FILES"][$key] = $newFile;
						}
					}

					$arTemplate["REPLICATE_PARAMS"] = unserialize($arTemplate["~REPLICATE_PARAMS"]);
					foreach($arTemplate["REPLICATE_PARAMS"] as $field=>$value)
					{
						$arTemplate["REPLICATE_".$field] = $value;
					}

					if ($arTemplate["DEADLINE_AFTER"])
					{
						$deadlineAfter = $arTemplate["DEADLINE_AFTER"] / (24 * 60 * 60);
						$arTemplate["DEADLINE"] = date($DB->DateFormatToPHP(CSite::GetDateFormat("SHORT")), strtotime(date("Y-m-d 00:00")." +".$deadlineAfter." days"));
					}

					$arData = $arTemplate;

					// Remove replication data from task created by matrix
					// Due to http://jabber.bx/view.php?id=29556
					{
						$arData['REPLICATE']         = 'N';
						$arData['~REPLICATE']        = 'N';
						$arData['REPLICATE_PARAMS']  = array();
						$arData['~REPLICATE_PARAMS'] = array();

						foreach ($arData as $key => $value)
						{
							if (substr($key, 0, 10) === 'REPLICATE_')
								unset ($arData[$key]);
						}
					}
				}
			}
		}
		elseif (intval($_GET["COPY"]) > 0) // copy task
		{
			$rsCopy = CTasks::GetByID(intval($_GET["COPY"]));
			if ($arCopy = $rsCopy->GetNext())
			{
				if (isset($arCopy["~DESCRIPTION_IN_BBCODE"]))
					$arCopy["DESCRIPTION_IN_BBCODE"] = $arCopy["~DESCRIPTION_IN_BBCODE"];

				$arCopy["DESCRIPTION"] = $arCopy["~DESCRIPTION"];
				$arCopy["CREATED_BY_NAME"] = $arCopy["~CREATED_BY_NAME"];
				$arCopy["CREATED_BY_LAST_NAME"] = $arCopy["~CREATED_BY_LAST_NAME"];
				$arCopy["CREATED_BY_SECOND_NAME"] = $arCopy["~CREATED_BY_SECOND_NAME"];
				$arCopy["CREATED_BY_LOGIN"] = $arCopy["~CREATED_BY_LOGIN"];
				$arCopy["MULTITASK"] = "N";

				if (sizeof($arCopy["FILES"]))
				{
					foreach($arCopy["FILES"] as $key=>$file)
					{
						$newFile = CFile::CopyFile($file);
						$arCopy["FILES"][$key] = $newFile;
					}
				}

				$arCopy["PARENT_ID"] = intval($_GET["COPY"]);

				$arData = $arCopy;
			}
		}
		elseif (intval($_GET["PARENT_ID"]) > 0) // copy task
		{
			$rsParent = CTasks::GetByID(intval($_GET["PARENT_ID"]));
			if ($rsParent = $rsParent->GetNext())
			{
				$arData["GROUP_ID"] = $rsParent["GROUP_ID"];

				if (isset($rsParent["DESCRIPTION_IN_BBCODE"]))
					$arData["DESCRIPTION_IN_BBCODE"] = $rsParent["DESCRIPTION_IN_BBCODE"];
			}
		}

		$bNeedDecodeUtf8 = false;
		if (isset($_GET['UTF8encoded']) && (ToUpper(SITE_CHARSET) !== 'UTF-8'))
			$bNeedDecodeUtf8 = true;

		$arGotData = array();

		foreach($_GET as $key=>$val)
		{
			if ($key === 'UTF8encoded')
				continue;
			elseif ($key === 'ACCOMPLICES_IDS')
			{
				if (strlen($val))
					$arGotData['ACCOMPLICES'] = array_map('intval', explode(',', $val));
			}
			elseif ($key === 'AUDITORS_IDS')
				$arGotData['AUDITORS'] = explode(',', array_map('intval', $val));
			elseif (!is_int($val))
			{
				if ($bNeedDecodeUtf8)
					$val = $APPLICATION->ConvertCharset($val, 'utf-8', SITE_CHARSET);

				// Description field always expected as unescaped, because of backward compatibility
				if ($key === 'DESCRIPTION')
					$arGotData[$key] = $val;
				else
					$arGotData[$key] = htmlspecialcharsbx($val);
			}
		}

		$arData = array_merge($arData, $arGotData);

		foreach (array_keys($arData) as $fieldName)
		{
			if (substr($fieldName, 0, 3) === 'UF_')
			{
				$arResult["bVarsFromForm"] = true;
				break;
			}
		}
	}

	// use BB-code for new tasks (but still use HTML for task which created from template or other task with HTML description)
	if ($arResult["ACTION"] == "create")
	{
		if (isset($arData['DESCRIPTION_IN_BBCODE']) && ($arData['DESCRIPTION_IN_BBCODE'] === 'N'))
			$arData['DESCRIPTION_IN_BBCODE'] = 'N';
		else
			$arData['DESCRIPTION_IN_BBCODE'] = 'Y';
	}

	if ($arResult["TASK_TYPE"] == "group" && !isset($arData["GROUP_ID"]))
	{
		$arData["GROUP_ID"] = $arParams["GROUP_ID"];
	}

	if (!isset($arData["PRIORITY"]))
	{
		$arData["PRIORITY"] = 1;
	}
}

if ($arData["RESPONSIBLE_ID"] && !$arData["RESPONSIBLE_NAME"] && !$arData["RESPONSIBLE_LAST_NAME"] && !$arData["RESPONSIBLE_LOGIN"])
{
	$rsResponsible = CUser::GetByID($arData["RESPONSIBLE_ID"]);
	if ($arResponsible = $rsResponsible->GetNext())
	{
		$arData["RESPONSIBLE_NAME"] = $arResponsible["NAME"];
		$arData["RESPONSIBLE_LAST_NAME"] = $arResponsible["LAST_NAME"];
		$arData["RESPONSIBLE_SECOND_NAME"] = $arResponsible["SECOND_NAME"];
		$arData["RESPONSIBLE_LOGIN"] = $arResponsible["LOGIN"];
	}
	else
	{
		unset($arData["RESPONSIBLE_ID"]);
	}
}

if ($arData["CREATED_BY"] && !$arData["CREATED_BY_NAME"] && !$arData["CREATED_BY_LAST_NAME"] && !$arData["CREATED_BY_LOGIN"])
{
	$rsAuthor = CUser::GetByID($arData["CREATED_BY"]);
	if ($arAuthor = $rsAuthor->Fetch())
	{
		$arData["CREATED_BY_NAME"] = $arAuthor["NAME"];
		$arData["CREATED_BY_LAST_NAME"] = $arAuthor["LAST_NAME"];
		$arData["CREATED_BY_SECOND_NAME"] = $arAuthor["SECOND_NAME"];
		$arData["CREATED_BY_LOGIN"] = $arAuthor["LOGIN"];
	}
	else
	{
		unset($arData["CREATED_BY"]);
	}
}

if (!$arData["RESPONSIBLE_ID"])
{
	if (($arData["CREATED_BY"] && $arData["CREATED_BY"] != $USER->GetID()))
	{
		$arData["RESPONSIBLE_ID"] = $USER->GetID();
		$arData["RESPONSIBLE_NAME"] = $USER->GetFirstName();
		$arData["RESPONSIBLE_LAST_NAME"] = $USER->GetLastName();
		$arData["RESPONSIBLE_SECOND_NAME"] = $USER->GetSecondName();
		$arData["RESPONSIBLE_LOGIN"] = $USER->GetLogin();
	}
	else
	{
		$arData["RESPONSIBLE_ID"] = $arResult["USER"]["ID"];
		$arData["RESPONSIBLE_NAME"] = $arResult["USER"]["NAME"];
		$arData["RESPONSIBLE_LAST_NAME"] = $arResult["USER"]["LAST_NAME"];
		$arData["RESPONSIBLE_SECOND_NAME"] = $arResult["USER"]["SECOND_NAME"];
		$arData["RESPONSIBLE_LOGIN"] = $arResult["USER"]["LOGIN"];
	}
}
if (!$arData["CREATED_BY"])
{
	$arData["CREATED_BY"] = $USER->GetID();
	$arData["CREATED_BY_NAME"] = $USER->GetFirstName();
	$arData["CREATED_BY_LAST_NAME"] = $USER->GetLastName();
	$arData["CREATED_BY_SECOND_NAME"] = $USER->GetSecondName();
	$arData["CREATED_BY_LOGIN"] = $USER->GetLogin();
}

// HTML-format must be supported in future, because old tasks' data not converted from HTML to BB
if ($arData['DESCRIPTION_IN_BBCODE'] !== 'Y')
{
	if (array_key_exists('DESCRIPTION', $arData))
		$arData['DESCRIPTION'] = CTasksTools::SanitizeHtmlDescriptionIfNeed($arData['DESCRIPTION']);
	if (array_key_exists('~DESCRIPTION', $arData))
		$arData['~DESCRIPTION'] = CTasksTools::SanitizeHtmlDescriptionIfNeed($arData['~DESCRIPTION']);
}
else
{
	$arData['META:DESCRIPTION_FOR_BBCODE'] = $arData['DESCRIPTION'];
	$parser = new CTextParser();
	$arData['~DESCRIPTION'] = $parser->convertText($arData['META:DESCRIPTION_FOR_BBCODE']);
	$arData['DESCRIPTION'] = $arData['~DESCRIPTION'];
}

$arResult["DATA"] = $arData;

// groups
$rsGroups = CSocNetGroup::GetList(array("NAME" => "ASC"), array("SITE_ID" => SITE_ID));
$arResult["GROUPS"] = array();
$groupIDs = array();
while($group = $rsGroups->GetNext())
{
	$arResult["GROUPS"][] = $group;
	$groupIDs[] = $group["ID"];
}

if (sizeof($groupIDs) > 0)
{
	$arGroupsPerms = CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, $groupIDs, "tasks", "create_tasks");
	foreach ($arResult["GROUPS"] as $key=>$group)
	{
		if (!$arGroupsPerms[$group["ID"]])
		{
			unset($arResult["GROUPS"][$key]);
		}
	}
}

$sTitle = "";
$arResult['META:ENVIRONMENT'] = array(
	'TIMEMAN_AVAILABLE' => (!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite())
);
if ($arResult["ACTION"] == "edit")
{
	$sTitle = str_replace("#TASK_ID#", $arParams["TASK_ID"], GetMessage("TASKS_TITLE_EDIT_TASK"));
}
else
{
	$sTitle = GetMessage("TASKS_TITLE_CREATE_TASK");

}
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