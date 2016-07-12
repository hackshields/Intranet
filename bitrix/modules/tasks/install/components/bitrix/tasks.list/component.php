<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CBXFeatures::IsFeatureEnabled('Tasks'))
{
	ShowError(GetMessage('TASKS_MODULE_NOT_AVAILABLE_IN_THIS_EDITION'));
	return;
}

if (!CModule::IncludeModule("tasks"))
{
	ShowError(GetMessage("TASKS_MODULE_NOT_FOUND"));
	return;
}

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SOCNET_MODULE_NOT_INSTALLED"));
	return;
}

if (!CModule::IncludeModule("forum"))
{
	ShowError(GetMessage("FORUM_MODULE_NOT_INSTALLED"));
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

if ( ! isset($arParams['USE_FILTER_V2']) )
	$arParams['USE_FILTER_V2'] = (COption::GetOptionString('tasks', '~use_filter_v1', null) != '1');
else
	$arParams['USE_FILTER_V2'] = ($arParams['USE_FILTER_V2'] === 'Y') ? true : false;

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arParams["TASK_ID"] = isset($arParams["TASK_ID"]) ? intval($arParams["TASK_ID"]) : 0;

$arResult["ACTION"] = ($arParams["TASK_ID"] > 0 ? "edit" : "create");

$arParams["USER_ID"] = intval($arParams["USER_ID"]) > 0 ? intval($arParams["USER_ID"]) : $USER->GetID();
$loggedInUserId = (int) $USER->GetID();

$arParams["GROUP_ID"] = isset($arParams["GROUP_ID"]) ? intval($arParams["GROUP_ID"]) : 0;

$bAttachUserFields = false;
if (isset($arParams['ATTACH_USER_FIELDS']) && ($arParams['ATTACH_USER_FIELDS'] === 'Y'))
	$bAttachUserFields = true;

if ($bAttachUserFields)
	$arResult['USER_FIELDS'] = array();
else
	$arResult['USER_FIELDS'] = false;

$arResult["TASK_TYPE"] = $taskType = ($arParams["GROUP_ID"] > 0 ? "group" : "user");

$bExcel = isset($_GET["EXCEL"]) && $_GET["EXCEL"] == "Y";

$viewType = "tree";
if ((isset($_GET["VIEW"]) && $_GET["VIEW"] == "1") || $bExcel)
{
	$viewType = "list";
}
elseif(isset($_GET["VIEW"]) && $_GET["VIEW"] == "2")
{
	$viewType = "gantt";
}
$arResult["VIEW_TYPE"] = $viewType;

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
$arParams["PATH_TO_USER_TASKS_REPORT"] = trim($arParams["PATH_TO_USER_TASKS_REPORT"]);
if (strlen($arParams["PATH_TO_USER_TASKS_REPORT"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS_REPORT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_tasks_report&".$arParams["USER_VAR"]."=#user_id#");
}
$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = trim($arParams["PATH_TO_USER_TASKS_TEMPLATES"]);
if (strlen($arParams["PATH_TO_USER_TASKS_TEMPLATES"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_tasks_templates&".$arParams["USER_VAR"]."=#user_id#");
}
$arParams["PATH_TO_USER_PROFILE"] = trim($arParams["PATH_TO_USER_PROFILE"]);

//group paths
$arParams["PATH_TO_GROUP_TASKS"] = trim($arParams["PATH_TO_GROUP_TASKS"]);
if (strlen($arParams["PATH_TO_GROUP_TASKS"]) <= 0)
{
	$arParams["PATH_TO_GROUP_TASKS"] = COption::GetOptionString("tasks", "paths_task_group", null, SITE_ID);
}
$arParams["PATH_TO_GROUP_TASKS_TASK"] = trim($arParams["PATH_TO_GROUP_TASKS_TASK"]);
if (strlen($arParams["PATH_TO_GROUP_TASKS_TASK"]) <= 0)
{
	$arParams["PATH_TO_GROUP_TASKS_TASK"] = COption::GetOptionString("tasks", "paths_task_group_action", null, SITE_ID);
}
$arParams["PATH_TO_GROUP_TASKS_REPORT"] = trim($arParams["PATH_TO_GROUP_TASKS_REPORT"]);
if (strlen($arParams["PATH_TO_GROUP_TASKS_REPORT"]) <= 0)
{
	$arParams["PATH_TO_GROUP_TASKS_REPORT"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=group_tasks_report&".$arParams["GROUP_VAR"]."=#group_id#");
}
$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = isset($arParams["PATH_TO_USER_TASKS_TEMPLATES"]) ? trim($arParams["PATH_TO_USER_TASKS_TEMPLATES"]) : "";
$arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = isset($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]) ? trim($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]) : "";
if (strlen($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]) <= 0)
{
	if (!isset($arParams["TEMPLATE_VAR"]))
	{
		$arParams["TEMPLATE_VAR"] = "template_id";
	}
	$arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_templates_template&".$arParams["USER_VAR"]."=#user_id#&".$arParams["TEMPLATE_VAR"]."=#template_id#&".$arParams["ACTION_VAR"]."=#action#");
}
$arParams["PATH_TO_TASKS_TEMPLATES"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TEMPLATES"]);

$arParams["NAV_TEMPLATE"] = (isset($arParams["NAV_TEMPLATE"]) && strlen($arParams["NAV_TEMPLATE"]) > 0 ? $arParams["NAV_TEMPLATE"] : "arrows");

$arResult["ADVANCED_STATUSES"] = array(
	array("TITLE" => GetMessage("TASKS_FILTER_ALL"), "FILTER" => array()),
	array("TITLE" => GetMessage("TASKS_FILTER_ACTIVE"), "FILTER" => array("STATUS" => array(-2, -1, 1, 2, 3))),
	array("TITLE" => GetMessage("TASKS_FILTER_NEW"), "FILTER" => array("STATUS" => array(-2, 1))),
	array("TITLE" => GetMessage("TASKS_FILTER_IN_CONTROL"), "FILTER" => array("STATUS" => array(4, 7))),
	array("TITLE" => GetMessage("TASKS_FILTER_IN_PROGRESS"), "FILTER" => array("STATUS" => 3)),
	array("TITLE" => GetMessage("TASKS_FILTER_ACCEPTED"), "FILTER" => array("STATUS" => 2)),
	array("TITLE" => GetMessage("TASKS_FILTER_OVERDUE"), "FILTER" => array("STATUS" => -1)),
	array("TITLE" => GetMessage("TASKS_FILTER_DELAYED"), "FILTER" => array("STATUS" => 6)),
	array("TITLE" => GetMessage("TASKS_FILTER_DECLINED"), "FILTER" => array("STATUS" => 7)),
	array("TITLE" => GetMessage("TASKS_FILTER_CLOSED"), "FILTER" => array("STATUS" => array(4, 5)))
);

if ($taskType == "user")
{
	$arParams["PATH_TO_TASKS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS"]);
	$arParams["PATH_TO_TASKS_TASK"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TASK"]);
	$arParams["PATH_TO_REPORTS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_REPORT"]);

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
	$arParams["PATH_TO_REPORTS"] = str_replace("#group_id#", $arParams["GROUP_ID"], $arParams["PATH_TO_GROUP_TASKS_REPORT"]);

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

$arParams["PATH_TO_TEMPLATES"] = str_replace("#user_id#", $USER->GetID(), $arParams["PATH_TO_USER_TASKS_TEMPLATES"]);

// filter

if ($taskType == "group" || $arParams["USER_ID"] == $USER->GetID())
{
	$arResult["ROLE_FILTER_SUFFIX"] = "";
}
else
{
	if ($arResult["USER"]["PERSONAL_GENDER"] == "F")
	{
		$arResult["ROLE_FILTER_SUFFIX"] = "_F";
	}
	else
	{
		$arResult["ROLE_FILTER_SUFFIX"] = "_M";
	}
}

$arPreDefindFilters = tasksPredefinedFilters($arParams["USER_ID"], $arResult["ROLE_FILTER_SUFFIX"]);

$preDefinedFilterRole = &$arPreDefindFilters["ROLE"];
$preDefinedFilterStatus = &$arPreDefindFilters["STATUS"][0];

$arCommonFilter = array();

if ($viewType != "list")
{
	$arCommonFilter["ONLY_ROOT_TASKS"] = "Y";
	$arCommonFilter["SAME_GROUP_PARENT"] = "Y";
}

if ($taskType == "group")
{
	$preDefinedFilterRole[7]["FILTER"] = array();
	$arCommonFilter["GROUP_ID"] = $arParams["GROUP_ID"];
}

if (isset($_GET["F_SEARCH"]))
{
	if (is_numeric($_GET["F_SEARCH"]) && intval($_GET["F_SEARCH"]) > 0 && ($rsSearch = CTasks::GetByID(intval($_GET["F_SEARCH"]))) && $rsSearch->Fetch())
	{
		$_GET["F_META::ID_OR_NAME"] = intval($_GET["F_SEARCH"]);
	}
	elseif (strlen(trim($_GET["F_SEARCH"])))
	{
		$_GET["F_TITLE"] = $_GET["F_SEARCH"];
	}
	else
	{
		$_GET["F_ADVANCED"] = "N";
		$_SESSION["FILTER"] = array();
	}
}

if ((isset($_GET["F_CANCEL"]) && $_GET["F_CANCEL"] == "Y") || isset($_GET["FILTERR"]) || isset($_GET["FILTERS"]) || (isset($_GET["F_ADVANCED"]) && $_GET["F_ADVANCED"] == "Y"))
{
	$_SESSION["FILTER"] = array();
}

if ((isset($_GET["F_ADVANCED"]) && $_GET["F_ADVANCED"] == "Y") || (isset($_SESSION["FILTER"]["F_ADVANCED"]) && $_SESSION["FILTER"]["F_ADVANCED"] == "Y")) // advanced filter
{
	$arResult["ADV_FILTER"]["F_ADVANCED"] = $_SESSION["FILTER"]["F_ADVANCED"] = "Y";
	$arFilter = array();

	if (intval($fID = tasksGetFilter("F_META::ID_OR_NAME")) > 0)
	{
		$arFilter["META::ID_OR_NAME"] = $fID;
		$arResult["ADV_FILTER"]["F_META::ID_OR_NAME"] = $fID;
	}

	if (intval($fID = tasksGetFilter("F_ID")) > 0)
	{
		$arFilter["ID"] = $fID;
		$arResult["ADV_FILTER"]["F_ID"] = $fID;
	}

	if (strlen($fTitle = tasksGetFilter("F_TITLE")) > 0)
	{
		$arFilter["%TITLE"] = $fTitle;
		$arResult["ADV_FILTER"]["F_TITLE"] = $fTitle;
	}

	if (intval($fResponsible = tasksGetFilter("F_RESPONSIBLE")) > 0)
	{
		$arFilter["RESPONSIBLE_ID"] = $fResponsible;
		$arResult["ADV_FILTER"]["F_RESPONSIBLE"] = $fResponsible;
	}

	if (intval($fCreatedBy = tasksGetFilter("F_CREATED_BY")) > 0)
	{
		$arFilter["CREATED_BY"] = $fCreatedBy;
		$arResult["ADV_FILTER"]["F_CREATED_BY"] = $fCreatedBy;
	}

	if (intval($fAccomplice = tasksGetFilter("F_ACCOMPLICE")) > 0)
	{
		$arFilter["ACCOMPLICE"] = $fAccomplice;
		$arResult["ADV_FILTER"]["F_ACCOMPLICE"] = $fAccomplice;
	}

	if (intval($fAuditor = tasksGetFilter("F_AUDITOR")) > 0)
	{
		$arFilter["AUDITOR"] = $fAuditor;
		$arResult["ADV_FILTER"]["F_AUDITOR"] = $fAuditor;
	}

	if (strlen($fTags = tasksGetFilter("F_TAGS")) > 0)
	{
		$arFilter["TAG"] = array_map("trim", explode(",", $fTags));
		$arResult["ADV_FILTER"]["F_TAGS"] = $fTags;
	}

	if (strlen($fDateFrom = tasksGetFilter("F_DATE_FROM")) > 0)
	{
		$arFilter[">=CREATED_DATE"] = $fDateFrom;
		$arResult["ADV_FILTER"]["F_DATE_FROM"] = $fDateFrom;
	}

	if (strlen($fDateTo = tasksGetFilter("F_DATE_TO")) > 0)
	{
		$arFilter["<=CREATED_DATE"] = $fDateTo;
		$arResult["ADV_FILTER"]["F_DATE_TO"] = $fDateTo;
	}

	if (strlen($fClosedFrom = tasksGetFilter("F_CLOSED_FROM")) > 0)
	{
		$arFilter[">=CLOSED_DATE"] = $fClosedFrom;
		$arResult["ADV_FILTER"]["F_CLOSED_FROM"] = $fClosedFrom;
	}

	if (strlen($fClosedTo = tasksGetFilter("F_CLOSED_TO")) > 0)
	{
		$arFilter["<=CLOSED_DATE"] = $fClosedTo;
		$arResult["ADV_FILTER"]["F_CLOSED_TO"] = $fClosedTo;
	}

	if (strlen($fActiveFrom = tasksGetFilter("F_ACTIVE_FROM")) > 0)
	{
		$arFilter["ACTIVE"]["START"] = $fActiveFrom;
		$arResult["ADV_FILTER"]["F_ACTIVE_FROM"] = $fActiveFrom;
	}

	if (strlen($fActiveTo = tasksGetFilter("F_ACTIVE_TO")) > 0)
	{
		$arFilter["ACTIVE"]["END"] = $fActiveTo;
		$arResult["ADV_FILTER"]["F_ACTIVE_TO"] = $fActiveTo;
	}

	if (($fStatus = tasksGetFilter("F_STATUS")) && array_key_exists($fStatus, $arResult["ADVANCED_STATUSES"]) > 0)
	{
		$arFilter = array_merge($arFilter, $arResult["ADVANCED_STATUSES"][$fStatus]["FILTER"]);
		$arResult["ADV_FILTER"]["F_STATUS"] = $fStatus;
	}

	if ($_GET["F_SUBORDINATE"] == "Y")
	{
		$arResult["ADV_FILTER"]["F_SUBORDINATE"] = "Y";
		$arResult["ADV_FILTER"]["F_ANY_TASK"] = "N";

		// Don't set SUBORDINATE_TASKS for admin, it will cause all tasks to be showed
		if ( ! ($USER->IsAdmin() || CTasksTools::IsPortalB24Admin()) )
			$arFilter["SUBORDINATE_TASKS"] = "Y";
	}
	elseif ($_GET["F_ANY_TASK"] == "Y")
	{
		$arResult["ADV_FILTER"]["F_SUBORDINATE"] = "N";
		$arResult["ADV_FILTER"]["F_ANY_TASK"] = "Y";
	}
	else
	{
		$arFilter["MEMBER"] = $arParams["USER_ID"];
	}

	if ($_GET["F_MARKED"] == "Y")
	{
		$arResult["ADV_FILTER"]["F_MARKED"] = "Y";
		$arFilter["!MARK"] = false;
	}

	if ($_GET["F_OVERDUED"] == "Y")
	{
		$arResult["ADV_FILTER"]["F_OVERDUED"] = "Y";
		$arFilter["OVERDUED"] = "Y";
	}

	if ($_GET["F_IN_REPORT"] == "Y")
	{
		$arResult["ADV_FILTER"]["F_IN_REPORT"] = "Y";
		$arFilter["ADD_IN_REPORT"] = "Y";
	}

	if (intval($fGroupId = tasksGetFilter("F_GROUP_ID")) > 0 && $taskType != "group")
	{
		$arFilter["GROUP_ID"] = $fGroupId;
		$arResult["ADV_FILTER"]["F_GROUP_ID"] = $fGroupId;
	}
}
elseif (isset($arParams["FILTER"]) && is_array($arParams["FILTER"]))
{
	$arFilter = $arParams["FILTER"];
}
elseif ($arParams['USE_FILTER_V2'])
{
	$bGroupMode = ($taskType === 'group');
	$oFilter = CTaskFilterCtrl::GetInstance($arParams['USER_ID'], $bGroupMode);

	if (isset($_GET['F_FILTER_SWITCH_PRESET']))
	{
		$curFilterId = $oFilter->GetSelectedFilterPresetId();
		$newFilterId = (int) $_GET['F_FILTER_SWITCH_PRESET'];

		if ($newFilterId !== $curFilterId)
		{
			try
			{
				$oFilter->SwitchFilterPreset($newFilterId);
			}
			catch (Exception $e)
			{
				$oFilter->SwitchFilterPreset(CTaskFilterCtrl::STD_PRESET_ALIAS_TO_DEFAULT);
			}
		}
	}

	// Do fallback if CTasks::GetList() dies two times
	$fallbackIndex = CUserCounter::GetValue($loggedInUserId, 'fallbackFilterV2');

	if ($fallbackIndex < 2)
		CUserCounter::Increment($loggedInUserId, 'fallbackFilterV2');
	else
		$oFilter->SwitchFilterPreset(CTaskFilterCtrl::STD_PRESET_ALIAS_TO_DEFAULT);

	$arFilter = $oFilter->GetSelectedFilterPresetCondition();
	$arResult['SELECTED_PRESET_NAME'] = $oFilter->GetSelectedFilterPresetName();
	$arResult['SELECTED_PRESET_ID']   = $oFilter->GetSelectedFilterPresetId();
}
else  // predefined filter
{
	if ($taskType == "group")
	{
		$roleFilter = 7;
	}
	else
	{
		if (isset($_GET["FILTERR"]) && array_key_exists($_GET["FILTERR"], $preDefinedFilterRole))
		{
			$roleFilter = $_GET["FILTERR"];
		}
		elseif (isset($_SESSION["FILTER"]["FILTERR"]) && array_key_exists($_SESSION["FILTER"]["FILTERR"], $preDefinedFilterRole))
		{
			$roleFilter = $_SESSION["FILTER"]["FILTERR"];
		}
		else
		{
			$roleFilter = 0;
		}
	}
	$_SESSION["FILTER"]["FILTERR"] = $roleFilter;

	$preDefinedFilterRole[$roleFilter]["SELECTED"] = true;

	if ($roleFilter == 4 || $roleFilter == 5)
	{
		$preDefinedFilterStatus = &$arPreDefindFilters["STATUS"][1];
	}

	if (isset($_GET["FILTERS"]) && array_key_exists($_GET["FILTERS"], $preDefinedFilterStatus))
	{
		$statusFilter = $_GET["FILTERS"];
	}
	elseif (isset($_SESSION["FILTER"]["FILTERS"]) && array_key_exists($_SESSION["FILTER"]["FILTERS"], $preDefinedFilterStatus))
	{
		$statusFilter = $_SESSION["FILTER"]["FILTERS"];
	}
	else
	{
		$statusFilter = 0;
	}
	$_SESSION["FILTER"]["FILTERS"] = $statusFilter;

	$preDefinedFilterStatus[$statusFilter]["SELECTED"] = true;

	$arFilter = array_merge($preDefinedFilterRole[$roleFilter]["FILTER"], $preDefinedFilterStatus[$statusFilter]["FILTER"]);
}

$arResult["PREDEFINED_FILTERS"] = array(
	"ROLE" => $preDefinedFilterRole,
	"STATUS" => $preDefinedFilterStatus
);

$arFilter = array_merge($arFilter, $arCommonFilter);
$arResult["COMMON_FILTER"] = $arCommonFilter;

// order
if (isset($_GET["SORTF"]) && in_array($_GET["SORTF"], array("TITLE", "DEADLINE", "CREATED_BY", "RESPONSIBLE_ID")) && isset($_GET["SORTD"]) && in_array($_GET["SORTD"], array("ASC", "DESC")))
{
	$arResult["ORDER"] = $arOrder = array($_GET["SORTF"] => $_GET["SORTD"]);
}
elseif (isset($arParams["ORDER"]))
{
	$arOrder = $arParams["ORDER"];
}
else
{
	$arOrder = array("STATUS" => "ASC", "DEADLINE" => "DESC", "PRIORITY" => "DESC", "ID" => "DESC");
}

$arResult["ORDER"] = $arOrder;

$arOrder = array_merge(array("GROUP_ID" => "ASC"), $arOrder);
$arSelect = array();

// use pagination by default
if ( ! isset($arParams['USE_PAGINATION']) )
	$arParams['USE_PAGINATION'] = 'Y';

$arGetListParams  = null;
$arNavStartParams = null;
$itemsCount       = 10;		// show 10 items by default

if ($arParams['ITEMS_COUNT'] > 0)
	$itemsCount = (int) abs($arParams['ITEMS_COUNT']);

if ($arParams['USE_PAGINATION'] === 'Y')
{
	$arNavStartParams = array('nPageSize' => $itemsCount);

	if ($bExcel)
		$arNavStartParams['NavShowAll'] = true;

	$arGetListParams = array('NAV_PARAMS' => $arNavStartParams);
}
else
{
	// This will be interpreted by CTasks::GetList as nPageTop
	$arGetListParams = $itemsCount;
}

$arResult["FILTER"] = $arFilter;
unset($arResult["FILTER"]["ONLY_ROOT_TASKS"]);

$rsTasks = CTasks::GetList($arOrder, $arFilter, $arSelect, $arGetListParams);

if ($arParams['USE_FILTER_V2'])
	CUserCounter::Clear($loggedInUserId, 'fallbackFilterV2');

$arResult["NAV_STRING"] = $rsTasks->GetPageNavString(GetMessage("TASKS_TITLE_TASKS"), $arParams["NAV_TEMPLATE"]);
$arResult["NAV_PARAMS"] = $rsTasks->GetNavParams();

$arResult["TASKS"] = array();
$arTasksIDs = array();
$arForumTopicsIDs = array();
$arGroupsIDs = array();
$arViewed = array();
while($task = $rsTasks->GetNext())
{
	$taskId = (int) $task['ID'];

	if ($bAttachUserFields)
	{
		$arResult['USER_FIELDS'][$taskId] = $GLOBALS["USER_FIELD_MANAGER"]
			->GetUserFields('TASKS_TASK', $taskId, LANGUAGE_ID);
	}

	$task["ACCOMPLICES"] = $task["AUDITORS"] = array();	// will be filled below

	$arTasksIDs[] = $taskId;
	if ($task["FORUM_TOPIC_ID"])
		$arForumTopicsIDs[$task["FORUM_TOPIC_ID"]] = $taskId;

	if ($task["GROUP_ID"] && !in_array($task["GROUP_ID"], $arGroupsIDs))
		$arGroupsIDs[] = $task["GROUP_ID"];

	$arViewed[$taskId] = $task["VIEWED_DATE"] ? $task["VIEWED_DATE"] : $task["CREATED_DATE"];

	// HTML-format must be supported in future, because old tasks' data not converted from HTML to BB
	if ($task['DESCRIPTION_IN_BBCODE'] === 'N')
	{
		// HTML detected, sanitize if need
		$task['~DESCRIPTION'] = CTasksTools::SanitizeHtmlDescriptionIfNeed($task['~DESCRIPTION']);
	}
	else
	{
		$task['META:DESCRIPTION_FOR_BBCODE'] = $task['DESCRIPTION'];

		$oParser = new CTextParser();
		$task['~DESCRIPTION'] = str_replace(
			"\t",
			' &nbsp; &nbsp;',
			$oParser->convertText($task['META:DESCRIPTION_FOR_BBCODE'])
		);
		unset($oParser);

		$task['DESCRIPTION'] = $task['~DESCRIPTION'];
	}

	$task["FILES"] = array();
	$arResult["TASKS"][$taskId] = $task;
}

// Fill accomplices and auditors now
if (count($arTasksIDs))
{
	$rsMembers = CTaskMembers::GetList(array(), array("TASK_ID" => $arTasksIDs));
	while ($arMember = $rsMembers->Fetch())
	{
		if (in_array($arMember['TASK_ID'], $arTasksIDs))
		{
			if ($arMember["TYPE"] == "A")
				$arResult["TASKS"][$arMember['TASK_ID']]["ACCOMPLICES"][] = $arMember["USER_ID"];
			elseif ($arMember["TYPE"] == "U")
				$arResult["TASKS"][$arMember['TASK_ID']]["AUDITORS"][] = $arMember["USER_ID"];
		}
	}

	$arFiles2TasksMap = array();	// Mapped FILE_ID to array of TASK_ID, that contains this file
	$arFilesIds = array();

	$rsTaskFiles = CTaskFiles::GetList(array(), array("TASK_ID" => $arTasksIDs));
	while ($arTaskFile = $rsTaskFiles->Fetch())
	{
		$fileId = (int) $arTaskFile['FILE_ID'];
		$taskId = (int) $arTaskFile['TASK_ID'];
		$arFilesIds[] = $fileId;

		if ( ! isset($arFiles2TasksMap['f' . $fileId]) )
			$arFiles2TasksMap['f' . $fileId] = array();

		$arFiles2TasksMap['f' . $fileId][] = $taskId;
	}

	$arFilesIds = array_unique($arFilesIds);

	$rsFiles = CFile::GetList(array(), array('@ID' => implode(',', $arFilesIds)));
	while ($arFile = $rsFiles->Fetch())
	{
		$arTasksIdsWithFile = array_unique($arFiles2TasksMap['f' . $arFile['ID']]);

		foreach ($arTasksIdsWithFile as $taskId)
			$arResult['TASKS'][$taskId]['FILES'][] = $arFile;
	}
}

$arResult["GROUPS"] = array();
$arOpenedProjects =  CUserOptions::GetOption("tasks", "opened_projects", array());
if (($arResult["VIEW_TYPE"] == "gantt" || $arResult["TASK_TYPE"] != "group") && sizeof($arGroupsIDs))
{
	$rsGroups = CSocNetGroup::GetList(array("ID" => "ASC"), array("ID" => $arGroupsIDs));
	while($arGroup = $rsGroups->GetNext())
	{
		$arGroup["EXPANDED"] = array_key_exists($arGroup["ID"], $arOpenedProjects) && $arOpenedProjects[$arGroup["ID"]] == "false" ? false : true;
		$arResult["GROUPS"][$arGroup["ID"]] = $arGroup;
	}
}


$arResult["CHILDREN_COUNT"] = array();
$rsChildrenCount = CTasks::GetChildrenCount($arFilter, $arTasksIDs);
if ($rsChildrenCount)
{
	while($arChildrenCount = $rsChildrenCount->Fetch())
	{
		$arResult["CHILDREN_COUNT"]["PARENT_".$arChildrenCount["PARENT_ID"]] = $arChildrenCount["CNT"];
	}
}

$arResult["UPDATES_COUNT"] = CTasks::GetUpdatesCount($arViewed);

$rsTemplates = CTaskTemplates::GetList(array("ID" => "DESC"), array("CREATED_BY" => $USER->GetID()));
$rsTemplates->NavStart(10);
$arResult["TEMPLATES"] = array();
while($template = $rsTemplates->GetNext())
{
	$arResult["TEMPLATES"][] = $template;
}

$sTitle = "";
if ($taskType == "group")
{
	$sTitle = GetMessage("TASKS_TITLE_GROUP_TASKS");
}
else
{
	if ($arParams["USER_ID"] == $USER->GetID())
	{
		$sTitle = GetMessage("TASKS_TITLE_MY_TASKS");
	}
	else
	{
		$sTitle = CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"], true, false).": ".GetMessage("TASKS_TITLE_TASKS");
	}
}
if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle($sTitle);
}

if (isset($arParams["SET_NAVCHAIN"]) && $arParams["SET_NAVCHAIN"] != "N")
{
	if ($taskType == "user")
	{
		$APPLICATION->AddChainItem(CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"]), CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER_PROFILE"], array("user_id" => $arParams["USER_ID"])));
		$APPLICATION->AddChainItem(GetMessage("TASKS_TITLE_TASKS"));
	}
	else
	{
		$APPLICATION->AddChainItem($arResult["GROUP"]["NAME"], CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_GROUP"], array("group_id" => $arParams["GROUP_ID"])));
		$APPLICATION->AddChainItem(GetMessage("TASKS_TITLE_TASKS"));
	}
}

if ($bExcel)
{
	$APPLICATION->RestartBuffer();

	// hack. any '.default' customized template should contain 'excel' page
	$this->__templateName = '.default';

	Header("Content-Type: application/force-download");
	Header("Content-Type: application/octet-stream");
	Header("Content-Type: application/download");
	Header("Content-Disposition: attachment;filename=tasks.xls");
	Header("Content-Transfer-Encoding: binary");

	$this->IncludeComponentTemplate('excel');

	die();
}
elseif ($viewType == "gantt")
{
	// hack. any '.default' customized template should contain 'gantt' page
	$this->__templateName = '.default';

	$this->IncludeComponentTemplate('gantt');
}
else
{
	$this->IncludeComponentTemplate();
}
