<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

$this->SetViewTarget("sidebar", 200);

?>

<div class="sidebar-widget sidebar-widget-tasks">
	<div class="sidebar-widget-top">
		<div class="sidebar-widget-top-title"><?=GetMessage("TASKS_FILTER_TITLE")?></div>
		<div class="plus-icon" onclick="BX.Tasks.lwPopup.showCreateForm();"></div>
	</div>
	<div class="sidebar-widget-item-wrap">
	<? foreach($arResult["PRESETS_LIST"] as $key=>$filter):
			if ($key >= 0)
				continue;
	?>
		<a href="<?= $arParams["PATH_TO_TASKS"]."?F_FILTER_SWITCH_PRESET=".$key?>" class="task-item<?if ($filter["Parent"] !== 0):?> task-item-sublevel<?endif?>"><span class="task-item-text"><?=$filter["Name"]?></span><span class="task-item-index"><?= $arResult["COUNTS"][$key]?></span></a>
	<? endforeach?>
	</div>
</div>
