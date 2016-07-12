<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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
if (!is_object($USER) || !$USER->IsAuthorized())
{
	$APPLICATION->AuthForm("");
	return;
}

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

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arParams["TASK_ID"] = intval($arParams["TASK_ID"]);

$arResult["ACTION"] = ($arParams["TASK_ID"] > 0 ? "edit" : "create");

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
$arParams["PATH_TO_USER_TASKS_TEMPLATES"] = trim($arParams["PATH_TO_USER_TASKS_TEMPLATES"]);
$arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = trim($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]);
if (strlen($arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]) <= 0)
{
	$arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=user_templates_template&".$arParams["USER_VAR"]."=#user_id#&".$arParams["TEMPLATE_VAR"]."=#template_id#&".$arParams["ACTION_VAR"]."=#action#");
}
$arParams["PATH_TO_USER_PROFILE"] = trim($arParams["PATH_TO_USER_PROFILE"]);

$arParams["PATH_TO_TASKS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS"]);
$arParams["PATH_TO_TASKS_TASK"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TASK"]);
$arParams["PATH_TO_REPORTS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_REPORT"]);
$arParams["PATH_TO_TEMPLATES_TEMPLATE"] = str_replace("#user_id#", $USER->GetID(), $arParams["PATH_TO_USER_TEMPLATES_TEMPLATE"]);

$rsUser = CUser::GetByID($arParams["USER_ID"]);
if ($user = $rsUser->Fetch())
{
	$arResult["USER"] = $user;
}
else
{
	return;
}

$arParams["PATH_TO_TEMPLATES"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TEMPLATES"]);

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
	$arOrder = array("TITLE" => "ASC");
}

$arResult["ORDER"] = $arOrder;

$rsTemplates = CTaskTemplates::GetList($arOrder, array("CREATED_BY" => $USER->GetID()));

$rsTemplates->NavStart(intval($arParams["ITEMS_COUNT"]) > 0 ? $arParams["ITEMS_COUNT"] : 10);
$arResult["NAV_STRING"] = $rsTemplates->GetPageNavString(GetMessage("TASKS_TITLE_TEMPLATES"), "arrows");
$arResult["NAV_PARAMS"] = $rsTemplates->GetNavParams();

$arResult["TEMPLATES"] = array();
while($template = $rsTemplates->GetNext())
{
	$arResult["TEMPLATES"][] = $template;
}

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(GetMessage("TASKS_TITLE_MY_TEMPLATES"));
}

if ($arParams["SET_NAVCHAIN"] != "N")
{
	$APPLICATION->AddChainItem(CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"]), CComponentEngine::MakePathFromTemplate($arParams["~PATH_TO_USER_PROFILE"], array("user_id" => $arParams["USER_ID"])));
	$APPLICATION->AddChainItem(GetMessage("TASKS_TITLE_TEMPLATES"));
}

$this->IncludeComponentTemplate();
?>