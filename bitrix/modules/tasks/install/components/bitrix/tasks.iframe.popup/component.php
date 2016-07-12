<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("tasks"))
{
	ShowError(GetMessage("TASKS_MODULE_NOT_FOUND"));
	return;
}

$arParams["PATH_TO_USER_TASKS_TASK"] = isset($arParams["PATH_TO_USER_TASKS_TASK"]) ? trim($arParams["PATH_TO_USER_TASKS_TASK"]) : "";
if (strlen($arParams["PATH_TO_USER_TASKS_TASK"]) <= 0)
{
	$arParams["PATH_TO_USER_TASKS_TASK"] = COption::GetOptionString("tasks", "paths_task_user_action", null, SITE_ID);
}

$arParams["USER_ID"] = $USER->GetID();

$arParams["PATH_TO_TASKS"] = str_replace("#user_id#", $arParams["USER_ID"], $arParams["PATH_TO_USER_TASKS_TASK"]);

// Mark that we are called not first at this hit. Template will skip some work in this case.
$arResult['FIRST_RUN_AT_HIT'] = true;
$arParams['ALLOW_NOT_FIRST_RUN_OPTIMIZATION'] = isset($arParams['ALLOW_NOT_FIRST_RUN_OPTIMIZATION']) ? $arParams['ALLOW_NOT_FIRST_RUN_OPTIMIZATION'] : 'Y';

$bAlreadyRun = CTasksPerHitOption::get('tasks', 'componentTaskIframePopupAlreadyRunned');

if ($bAlreadyRun)
	$arResult['FIRST_RUN_AT_HIT'] = false;
else
	CTasksPerHitOption::set('tasks', 'componentTaskIframePopupAlreadyRunned', true);

$arResult['OPTIMIZE_REPEATED_RUN'] = false;
if ($arParams['ALLOW_NOT_FIRST_RUN_OPTIMIZATION'] === 'Y')
{
	// If it isn't first run => optimize
	if ($arResult['FIRST_RUN_AT_HIT'] === false)
		$arResult['OPTIMIZE_REPEATED_RUN'] = true;
}

$this->IncludeComponentTemplate();
