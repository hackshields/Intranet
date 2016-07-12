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

$arParams["TASK_VAR"] = trim($arParams["TASK_VAR"]);
if (strlen($arParams["TASK_VAR"]) <= 0)
	$arParams["TASK_VAR"] = "task_id";

$arParams["GROUP_VAR"] = trim($arParams["GROUP_VAR"]);
if (strlen($arParams["GROUP_VAR"]) <= 0)
	$arParams["GROUP_VAR"] = "group_id";

$arParams["ACTION_VAR"] = trim($arParams["ACTION_VAR"]);
if (strlen($arParams["ACTION_VAR"]) <= 0)
	$arParams["ACTION_VAR"] = "action";

if (strlen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";

$arParams["TEMPLATE_VAR"] = trim($arParams["TEMPLATE_VAR"]);
if (strlen($arParams["TASK_VAR"]) <= 0)
	$arParams["TEMPLATE_VAR"] = "template_id";

$arParams["TEMPLATE_ID"] = intval($arParams["TEMPLATE_ID"]);

$arResult["ACTION"] = ($arParams["TEMPLATE_ID"] > 0 ? "edit" : "create");

$arParams["USER_ID"] = intval($arParams["USER_ID"]) > 0 ? intval($arParams["USER_ID"]) : $USER->GetID();

$arParams["GROUP_ID"] = intval($arParams["GROUP_ID"]);

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

$arParams["PATH_TO_TASKS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS"]);
$arParams["PATH_TO_TASKS_TASK"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TASK"]);
$arParams["PATH_TO_TASKS_TEMPLATES"] = str_replace("#user_id#", $USER->GetID(), $arParams["PATH_TO_USER_TASKS_TEMPLATES"]);
$arParams["PATH_TO_TEMPLATES_TEMPLATE"] = str_replace("#user_id#", $USER->GetID(), $arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]);

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$rsUser = CUser::GetByID($arParams["USER_ID"]);
if ($user = $rsUser->GetNext())
{
	$arResult["USER"] = $user;
}
else
{
	return;
}

if (array_key_exists("back_url", $_REQUEST) && strlen($_REQUEST["back_url"]) > 0)
{
	$arResult["RETURN_URL"] = htmlspecialcharsbx(trim($_REQUEST["back_url"]));
}
else
{
	$arResult["RETURN_URL"] = $arParams["PATH_TO_TASKS_TEMPLATES"];
}

$arData = array();
if ($arResult["ACTION"] == "edit")
{
	$rsTemplate = CTaskTemplates::GetByID($arParams["TEMPLATE_ID"]);

	if (!($arData = $rsTemplate->GetNext()))
	{
		ShowError(GetMessage("TASKS_TEMPLATE_NOT_FOUND"));
		return;
	}
	elseif($arData["CREATED_BY"] != $USER->GetID())
	{
		ShowError(GetMessage("TASKS_TEMPLATE_NOT_FOUND"));
		return;
	}
	else
	{
		$arData["ACCOMPLICES"] = $arData["~ACCOMPLICES"] ? unserialize($arData["~ACCOMPLICES"]) : array();
		$arData["AUDITORS"] = $arData["~AUDITORS"] ? unserialize($arData["~AUDITORS"]) : array();
		$arData["RESPONSIBLES"] = $arData["~RESPONSIBLES"] ? unserialize($arData["~RESPONSIBLES"]) : array();
		$arData["FILES"] = $arData["~FILES"] ? unserialize($arData["~FILES"]) : array();
		$arData["TAGS"] = $arData["~TAGS"] ? unserialize($arData["~TAGS"]) : "";
		$arData["DEPENDS_ON"] = $arData["~DEPENDS_ON"] ? unserialize($arData["~DEPENDS_ON"]) : array();
		$arData["DESCRIPTION"] = $arData["~DESCRIPTION"];

		$arData["CREATED_BY_NAME"] = $arData["~CREATED_BY_NAME"];
		$arData["CREATED_BY_LAST_NAME"] = $arData["~CREATED_BY_LAST_NAME"];
		$arData["CREATED_BY_SECOND_NAME"] = $arData["~CREATED_BY_SECOND_NAME"];
		$arData["CREATED_BY_LOGIN"] = $arData["~CREATED_BY_LOGIN"];

		$arData["DEADLINE_AFTER"] = $arData["~DEADLINE_AFTER"] / (24 * 60 * 60);

		$arData["REPLICATE_PARAMS"] = unserialize($arData["~REPLICATE_PARAMS"]);
		$arReplicateParams = array(
			"REPLICATE_PERIOD" => $arData["REPLICATE_PARAMS"]["PERIOD"],
			"REPLICATE_EVERY_DAY" => $arData["REPLICATE_PARAMS"]["EVERY_DAY"],
			"REPLICATE_WORKDAY_ONLY" => $arData["REPLICATE_PARAMS"]["WORKDAY_ONLY"],
			"REPLICATE_EVERY_WEEK" => $arData["REPLICATE_PARAMS"]["EVERY_WEEK"],
			"REPLICATE_WEEK_DAYS" => $arData["REPLICATE_PARAMS"]["WEEK_DAYS"],
			"REPLICATE_MONTHLY_TYPE" => $arData["REPLICATE_PARAMS"]["MONTHLY_TYPE"],
			"REPLICATE_MONTHLY_DAY_NUM" => $arData["REPLICATE_PARAMS"]["MONTHLY_DAY_NUM"],
			"REPLICATE_MONTHLY_MONTH_NUM_1" => $arData["REPLICATE_PARAMS"]["MONTHLY_MONTH_NUM_1"],
			"REPLICATE_MONTHLY_WEEK_DAY_NUM" => $arData["REPLICATE_PARAMS"]["MONTHLY_WEEK_DAY_NUM"],
			"REPLICATE_MONTHLY_WEEK_DAY" => $arData["REPLICATE_PARAMS"]["MONTHLY_WEEK_DAY"],
			"REPLICATE_MONTHLY_MONTH_NUM_2" => $arData["REPLICATE_PARAMS"]["MONTHLY_MONTH_NUM_2"],
			"REPLICATE_YEARLY_TYPE" => $arData["REPLICATE_PARAMS"]["YEARLY_TYPE"],
			"REPLICATE_YEARLY_DAY_NUM" => $arData["REPLICATE_PARAMS"]["YEARLY_DAY_NUM"],
			"REPLICATE_YEARLY_MONTH_1" => $arData["REPLICATE_PARAMS"]["YEARLY_MONTH_1"],
			"REPLICATE_YEARLY_WEEK_DAY_NUM" => $arData["REPLICATE_PARAMS"]["YEARLY_WEEK_DAY_NUM"],
			"REPLICATE_YEARLY_WEEK_DAY" => $arData["REPLICATE_PARAMS"]["YEARLY_WEEK_DAY"],
			"REPLICATE_YEARLY_MONTH_2" => $arData["REPLICATE_PARAMS"]["YEARLY_MONTH_2"],
			"REPLICATE_START_DATE" => $arData["REPLICATE_PARAMS"]["START_DATE"],
			"REPLICATE_END_DATE" => $arData["REPLICATE_PARAMS"]["END_DATE"]
		);

		$arData = array_merge($arData, $arReplicateParams);
	}
}
else
{
	$arData["PRIORITY"] = 1;
}

//Form submitted
if($_SERVER["REQUEST_METHOD"] == "POST" && check_bitrix_sessid() && ($arResult["ACTION"] == "create" || $arResult["ACTION"] == "edit"))
{

	$_POST["WEEK_DAYS"] = explode(",", $_POST["WEEK_DAYS"]);

	if(isset($_POST["save"]) || isset($_POST["apply"]))
	{
		$_POST["TAGS"] = array_filter(explode(",", $_POST["TAGS"]));
		$_POST["ACCOMPLICES"] = array_filter(explode(",", $_POST["ACCOMPLICES_IDS"]));
		$_POST["RESPONSIBLES"] = array_filter(explode(",", $_POST["RESPONSIBLES_IDS"]));
		$_POST["DEPENDS_ON"] = array_filter(explode(",", $_POST["PREV_TASKS_IDS"]));
		$_POST["REPLICATE_WEEK_DAYS"] = array_filter(explode(",", $_POST["REPLICATE_WEEK_DAYS"]));

		$arFields = array(
			"TITLE" => trim($_POST["TITLE"]),
			"DESCRIPTION" => $_POST["DESCRIPTION"],
			"DEADLINE" => $_POST["DEADLINE"],
			"START_DATE_PLAN" => $_POST["START_DATE_PLAN"],
			"END_DATE_PLAN" => $_POST["END_DATE_PLAN"],
			"DURATION_PLAN" => $_POST["DURATION_PLAN"],
			"DURATION_TYPE" => $_POST["DURATION_TYPE"],
			"PRIORITY" => $_POST["PRIORITY"],
			"ACCOMPLICES" => sizeof($_POST["ACCOMPLICES"]) > 0 ? serialize($_POST["ACCOMPLICES"]) : false,
			"AUDITORS" => sizeof($_POST["AUDITORS"]) > 0 ? serialize($_POST["AUDITORS"]) : false,
			"TAGS" => sizeof($_POST["TAGS"]) > 0 ? serialize($_POST["TAGS"]) : false,
			"RESPONSIBLES" => sizeof($_POST["RESPONSIBLES"]) > 0 ? serialize($_POST["RESPONSIBLES"]) : false,
			"DEPENDS_ON" => sizeof($_POST["DEPENDS_ON"]) > 0 ? serialize($_POST["DEPENDS_ON"]) : false,
			"FILES" => sizeof($_POST["FILES"]) > 0 ? serialize($_POST["FILES"]) : false,
			"ALLOW_CHANGE_DEADLINE" => isset($_POST["ALLOW_CHANGE_DEADLINE"]) ? "Y" : "N",
			"TASK_CONTROL" => isset($_POST["TASK_CONTROL"]) ? "Y" : "N",
			"ADD_IN_REPORT" => isset($_POST["ADD_IN_REPORT"]) ? "Y" : "N",
			"PARENT_ID" => intval($_POST["PARENT_ID"]) > 0 ? intval($_POST["PARENT_ID"]) : false,
			"GROUP_ID" => intval($_POST["GROUP_ID"]) > 0 && CSocNetFeaturesPerms::CurrentUserCanPerformOperation(SONET_ENTITY_GROUP, intval($_POST["GROUP_ID"]), "tasks", "create_tasks") ? intval($_POST["GROUP_ID"]) : false,
			"REPLICATE" => isset($_POST["REPLICATE"]) ? "Y" : "N",
			"DEADLINE_AFTER" => intval($_POST["DEADLINE_AFTER"]) > 0 ? $_POST["DEADLINE_AFTER"] * 24 * 60 * 60 : false
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

		$arFields["REPLICATE_PARAMS"] = array();
		foreach ($_POST as $field=>$value)
		{
			if (substr($field, 0, 10) == "REPLICATE_") // parameters of replication
			{
				$arFields["REPLICATE_PARAMS"][substr($field, 10)] = substr($field, -5) == "_DATE" ?  ConvertDateTime($value) : $value;
			}
		}
		$arFields["REPLICATE_PARAMS"] = serialize($arFields["REPLICATE_PARAMS"]);

		$arFields["SITE_ID"] = SITE_ID;

		$arSectionIDs = CTasks::GetSubordinateDeps();

		if ($_POST["MULTITASK"] == "Y" && sizeof($_POST["RESPONSIBLES"]) > 0)
		{
			$arFields["MULTITASK"] = "Y";
			$arFields["CREATED_BY"] = $arFields["RESPONSIBLE_ID"] = $USER->GetID();
		}
		else
		{
			$arFields["MULTITASK"] = "N";

			$arFields["CREATED_BY"] = $_POST["CREATED_BY"];

			if ($arFields["CREATED_BY"] != $USER->GetID())
			{
				$arFields["RESPONSIBLE_ID"] = $USER->GetID();
			}
			else
			{
				$arFields["RESPONSIBLE_ID"] = $_POST["RESPONSIBLE_ID"];
			}
		}

		if ($arFields["RESPONSIBLE_ID"] != $USER->GetID())
		{
			$rsUser = CUser::GetByID($arFields["RESPONSIBLE_ID"]);
			if ($arUser = $rsUser->Fetch())
			{
				if (!sizeof(array_intersect($arSectionIDs, $arUser["UF_DEPARTMENT"])))
				{
					$arFields["ADD_IN_REPORT"] = "N";
				}
			}
		}

		$template = new CTaskTemplates();
		if ($arResult["ACTION"] == "edit")
		{
			$arFields["RESPONSIBLE_ID"] = $_POST["RESPONSIBLE_ID"];

			if (isset($_POST["FILES_TO_DELETE"]) && sizeof($_POST["FILES_TO_DELETE"]))
			{
				$arFilesToUnlink = array();
				foreach($_POST["FILES_TO_DELETE"] as $file)
				{
					if (in_array($file, $arData["FILES"]))
					{
						// Skip files, that attached to some existing tasks
						$rsFiles = CTaskFiles::GetList(
							array(),
							array('FILE_ID' => $file)
						);

						// There is no tasks with this file, so it can be removed
						if (!$arFile = $rsFiles->Fetch())
							$arFilesToUnlink[] = $file;
					}
				}

				foreach ($arFilesToUnlink as $file)
					CFile::Delete($file);
			}
			$result = $template->Update(
				$arParams["TEMPLATE_ID"],
				$arFields,
				array(
					'CHECK_RIGHTS_ON_FILES' => true,
					'USER_ID'               => (int) $USER->getId()
				)
			);
			$templateID = $arParams["TEMPLATE_ID"];
		}
		else
		{
			$templateID = $result = $template->Add(
				$arFields,
				array(
					'CHECK_RIGHTS_ON_FILES' => true,
					'USER_ID'               => (int) $USER->getId()
				)
			);
		}

		$arResult["ERRORS"] = $template->GetErrors();
		if (sizeof($arResult["ERRORS"]) == 0)
		{
			$arUploadedFils = unserialize($arFields["FILES"]);

			if (is_array($arUploadedFils) && count($arUploadedFils))
			{
				$userId = (int) $USER->GetID();

				foreach ($arUploadedFils as $fileId)
					CTaskFiles::removeTemporaryFile($userId, (int) $fileId);
			}

			if (strlen($_POST["save"]) > 0)
			{
				$redirectPath = $arResult["RETURN_URL"];
			}
			else
			{
				$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TEMPLATES"], array());
			}
			LocalRedirect($redirectPath);
		}
		else
		{
			$arData = $_POST;
		}
	}
}
else
{
	// use BB-code for new tasks
	if ($arResult["ACTION"] == "create")
		$arData['DESCRIPTION_IN_BBCODE'] = 'Y';	// create all new tasks in BB-code
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
	$rsResponsible = CUser::GetByID($arData["CREATED_BY"]);
	if ($arResponsible = $rsResponsible->Fetch())
	{
		$arData["CREATED_BY_NAME"] = $arResponsible["NAME"];
		$arData["CREATED_BY_LAST_NAME"] = $arResponsible["LAST_NAME"];
		$arData["CREATED_BY_SECOND_NAME"] = $arResponsible["SECOND_NAME"];
		$arData["CREATED_BY_LOGIN"] = $arResponsible["LOGIN"];
	}
	else
	{
		unset($arData["RESPONSIBLE_ID"]);
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
if ($arResult["ACTION"] == "edit")
{
	$sTitle = str_replace("#TEMPLATE_ID#", $arParams["TEMPLATE_ID"], GetMessage("TASKS_TITLE_EDIT_TEMPLATE"));
}
else
{
	$sTitle = GetMessage("TASKS_TITLE_CREATE_TEMPLATE");
}
if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle($sTitle);
}

if ($arParams["SET_NAVCHAIN"] != "N")
{
	$APPLICATION->AddChainItem(CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"]), CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER_PROFILE"], array("user_id" => $arParams["USER_ID"])));
	$APPLICATION->AddChainItem($sTitle);
}

$this->IncludeComponentTemplate();
?>