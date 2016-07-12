<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (!CModule::IncludeModule("tasks"))
{
	ShowError(GetMessage("TASKS_MODULE_NOT_FOUND"));
	return;
}

if (
	isset($arParams['LOAD_TEMPLATE_INSTANTLY'])
	&& (
		($arParams['LOAD_TEMPLATE_INSTANTLY'] === 'Y')
		|| ($arParams['LOAD_TEMPLATE_INSTANTLY'] === true)
	)
)
{
	$this->IncludeComponentTemplate();
	return;
}

$arParams['GROUP_ID'] = intval($arParams['GROUP_ID']);
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

$arResult["ADVANCED_STATUSES"] = array(
	array("TITLE" => GetMessage("TASKS_FILTER_ALL"), "FILTER" => array()),
	array(
		"TITLE" => GetMessage("TASKS_FILTER_ACTIVE"),
		"FILTER" => array("STATUS" => array(
			CTasks::METASTATE_VIRGIN_NEW,
			CTasks::METASTATE_EXPIRED,
			CTasks::STATE_NEW,
			CTasks::STATE_PENDING,
			CTasks::STATE_IN_PROGRESS
	))),
	array(
		"TITLE" => GetMessage("TASKS_FILTER_NEW"),
		"FILTER" => array("STATUS" => array(
			CTasks::METASTATE_VIRGIN_NEW,
			CTasks::STATE_NEW
	))),
	array(
		"TITLE" => GetMessage("TASKS_FILTER_IN_CONTROL"),
		"FILTER" => array("STATUS" => array(
			CTasks::STATE_SUPPOSEDLY_COMPLETED,
			CTasks::STATE_DECLINED
	))),
	array("TITLE" => GetMessage("TASKS_FILTER_IN_PROGRESS"), "FILTER" => array("STATUS" => CTasks::STATE_IN_PROGRESS)),
	array("TITLE" => GetMessage("TASKS_FILTER_ACCEPTED"), "FILTER" => array("STATUS" => CTasks::STATE_PENDING)),
	array("TITLE" => GetMessage("TASKS_FILTER_OVERDUE"), "FILTER" => array("STATUS" => CTasks::METASTATE_EXPIRED)),
	array("TITLE" => GetMessage("TASKS_FILTER_DELAYED"), "FILTER" => array("STATUS" => CTasks::STATE_DEFERRED)),
	array("TITLE" => GetMessage("TASKS_FILTER_DECLINED"), "FILTER" => array("STATUS" => CTasks::STATE_DECLINED)),
	array(
		"TITLE" => GetMessage("TASKS_FILTER_CLOSED"),
		"FILTER" => array("STATUS" => array(
			CTasks::STATE_SUPPOSEDLY_COMPLETED,
			CTasks::STATE_COMPLETED
	)))
);

$arResult["TASK_TYPE"] = $taskType = ($arParams["GROUP_ID"] > 0 ? "group" : "user");
$arResult['LOGGED_IN_USER'] = (int) $USER->GetID();

$bGroupMode = ($taskType === 'group');
$oFilter = CTaskFilterCtrl::GetInstance($arResult['LOGGED_IN_USER'], $bGroupMode);
$arResult['PRESETS_TREE']         = $oFilter->ListFilterPresets($bTreeMode = true);
$arResult['PRESETS_LIST']         = $oFilter->ListFilterPresets($bTreeMode = false);
$arResult['SELECTED_PRESET_NAME'] = $oFilter->GetSelectedFilterPresetName();
$arResult['SELECTED_PRESET_ID']   = $oFilter->GetSelectedFilterPresetId();

// calculate items count for each filter
$obCache = new CPHPCache();
$lifeTime = 31536000;		// 365 days
$cacheDir = "/tasks/filter_tt_" . $taskType;
$arFiltersCount = array();
$bDataNotCached = true;
$arCacheTags = array('tasks_filter_presets');

$cacheID = sha1($arResult['LOGGED_IN_USER'] . "user" . $arParams["USER_ID"]);
$arCacheTags[] = "tasks_user_" . $arResult['LOGGED_IN_USER'];

if ($taskType == "group")
{
	$cacheID .= '|' . (int) $arParams["GROUP_ID"];
	$arCacheTags[] = "tasks_group_" . (int) $arParams["GROUP_ID"];
}

if(defined('BX_COMP_MANAGED_CACHE') && $obCache->InitCache($lifeTime, $cacheID, $cacheDir))
{
	$arFiltersCount = $obCache->GetVars();
	$bDataNotCached = false;
}
else
{
	foreach ($arResult['PRESETS_LIST'] as $presetId => $presetData)
	{
		// We can't cache counters for user-defined filters
		if ($presetId > 0)
			continue;

		$arFilter = $oFilter->GetFilterPresetConditionById($presetId);

		if (($taskType === 'group') && ($presetId <= 0))
			$arFilter['GROUP_ID'] = (int) $arParams['GROUP_ID'];

		if ($arFilter === false)
			$arFiltersCount[$presetId] = 0;
		else
		{
			$count = 0;

			$rsCount = CTasks::GetCount($arFilter, array('bIgnoreDbErrors' => true));
			if ($rsCount !== false)
			{
				if ($arCount = $rsCount->fetch())
					$count = (int) $arCount['CNT'];
			}

			$arFiltersCount[$presetId] = $count;
		}
	}
}

$arResult['COUNTS'] = $arFiltersCount;

if ($bDataNotCached)
{
	if (defined('BX_COMP_MANAGED_CACHE') && $obCache->StartDataCache())
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->StartTagCache($cacheDir);

		foreach ($arCacheTags as $cacheTag)
			$CACHE_MANAGER->RegisterTag($cacheTag);

		$CACHE_MANAGER->EndTagCache();
		$obCache->EndDataCache($arFiltersCount);
	}
}

$this->IncludeComponentTemplate();
