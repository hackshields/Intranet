<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->SetViewTarget("pagetitle", 100);

$arFilters = Array(
	Array("NAME" => GetMessage("TASK_TOOLBAR_FILTER_TREE"),"TITLE"=> GetMessage("TASKS_TREE_LIST"), "TYPE" => "tree", "URL" => $APPLICATION->GetCurPageParam("", array("VIEW"))),
	Array("NAME" => GetMessage("TASK_TOOLBAR_FILTER_LIST"), "TITLE"=> GetMessage("TASKS_PLAIN_LIST"), "TYPE" => "list", "URL" => $APPLICATION->GetCurPageParam("VIEW=1", array("VIEW"))),
	Array("NAME" => GetMessage("TASK_TOOLBAR_FILTER_GANTT"),"TITLE"=> GetMessage("TASKS_GANTT"), "TYPE" => "gantt", "URL" => $APPLICATION->GetCurPageParam("VIEW=2", array("VIEW"))),
	Array("NAME" => GetMessage("TASK_TOOLBAR_FILTER_REPORTS"),"TITLE"=> GetMessage("TASK_TOOLBAR_FILTER_REPORTS"), "TYPE" => "reports", "URL" => $arParams["PATH_TO_REPORTS"])
);

$filterName = '';
if (strlen($arParams['SELECTED_PRESET_NAME']))
	$filterName .= ': ' . $arParams['SELECTED_PRESET_NAME'];

foreach ($arFilters as $filter):
	?><a href="<?=$filter["URL"]?>" title="<?=$filter["TITLE"]?>" class="pagetitle-but-wrap<?if ($arParams["VIEW_TYPE"] == $filter["TYPE"]):?> pagetitle-but-act<?endif?>"><span class="pagetitle-but-left"></span><span class="pagetitle-but-text"><?=$filter["NAME"]?></span><span class="pagetitle-but-right"></span><?if ($arParams["VIEW_TYPE"] == $filter["TYPE"]):?><span class="pagetitle-but-angle"></span><?endif?></a><?
endforeach?><span id="task-title-button-legend" onclick="ShowLegendPopup(this);" title="<? echo GetMessage("TASKS_HELP")?>" class="pagetitle-but-wrap task-list-toolbar-legenda"><span class="pagetitle-but-left"></span><span class="pagetitle-but-text"><span
	class="task-list-toolbar-legenda-icon"></span></span><span class="pagetitle-but-right"></span></span>

<?php
if ($arParams["VIEW_TYPE"] == "gantt")
{
	?><span class="webform-small-button task-list-toolbar-filter" onclick="showGanttFilter(this)"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php
		echo GetMessage("TASK_TOOLBAR_FILTER_BUTTON") . $filterName;
	?></span><span class="webform-small-button-icon"></span><span class="webform-small-button-right"></span></span><?
}
else
{
	?><span class="webform-small-button task-list-toolbar-filter" onclick="showTaskListFilter(this)"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php
		echo GetMessage("TASK_TOOLBAR_FILTER_BUTTON") . $filterName;
	?></span><span class="webform-small-button-icon"></span><span class="webform-small-button-right"></span></span><?
}
?>

<?$this->EndViewTarget();?>


<?$this->SetViewTarget("topblock", 200);?>

<?if (is_object($USER) && $USER->IsAuthorized()):?>

<div class="task-list-toolbar">
	<div class="task-list-toolbar-search"><form action="<?$arParams["PATH_TO_TASKS"]?>" method="GET" name="task-filter-title-form"><input class="task-list-toolbar-search-input" id="task-title-button-search-input" name="F_SEARCH" type="text"<? if(isset($_GET["F_SEARCH"]) && $_GET["F_SEARCH"]):?> value="<? echo isset($_GET["F_SEARCH"]) ? htmlspecialcharsbx($_GET["F_SEARCH"]) : ""?>"<?endif?> /><input type="hidden" name="VIEW" value="<? if ($arParams["VIEW_TYPE"] == "list") { echo 1; } elseif ($arParams["VIEW_TYPE"] == "gantt") { echo 2; } else { echo 0; }?>" /><input type="hidden"  name="F_ADVANCED" value="Y" /><? if(isset($_GET["F_SEARCH"]) && $_GET["F_SEARCH"]):?><a href="<? echo $APPLICATION->GetCurPageParam("F_CANCEL=Y", array("F_TITLE", "F_RESPONSIBLE", "F_CREATED_BY", "F_ACCOMPLICE", "F_AUDITOR", "F_DATE_FROM", "F_DATE_TO", "F_TAGS", "F_STATUS", "F_SUBORDINATE", "F_ADVANCED", "F_SEARCH"))?>" class="task-list-toolbar-search-reset"></a><? else:?><span class="task-list-toolbar-search-icon" id="task-title-button-search-icon"></span><? endif?></form></div>
	<div class="task-list-toolbar-actions">
		<a class="webform-small-button task-list-toolbar-create" href="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit"))?>" 
			onclick="<?
				$RESPONSIBLE_ID = (int) $USER->getId();
				
				if ($arParams['USER_ID'])
					$RESPONSIBLE_ID = (int) $arParams['USER_ID'];

				if($arParams["GROUP_ID"]):
					?>AddQuickPopupTask(event, {GROUP_ID: <?php echo $arParams["GROUP_ID"]; ?>, RESPONSIBLE_ID: <?php echo $RESPONSIBLE_ID; ?>});<?
				else:
					?>AddQuickPopupTask(event, {RESPONSIBLE_ID: <?php echo $RESPONSIBLE_ID; ?>})<?
				endif?>;"><span class="webform-small-button-left"></span><span class="webform-small-button-icon"></span><span class="webform-small-button-text"><?=GetMessage("TASKS_ADD_TASK")?></span><span class="webform-small-button-right"></span>
		</a><?if ($arParams["VIEW_TYPE"] != "gantt"):?><a class="webform-small-button task-list-toolbar-lightning" id="task-list-toolbar-lightning" href="" onclick="BX.PreventDefault(event); createQuickTask(<? if (isset($arParams["GROUP"]) && $arParams["GROUP"]):?>null, {group: {id: <?=$arParams["GROUP"]["ID"]?>, title: '<?=CUtil::JSEscape(htmlspecialcharsbx(htmlspecialcharsback(htmlspecialcharsback($arParams["GROUP"]["NAME"]))))?>'}}<?endif?>)" title="<?=GetMessage("TASKS_ADD_QUICK_TASK")?>"><span class="webform-small-button-left"></span><span class="webform-small-button-icon"></span><span class="webform-small-button-right"></span>
		</a><?endif?><a class="webform-small-button task-list-toolbar-templates" href="" onclick="BX.PreventDefault(event); return ShowTemplatesPopup(this)" title="<?=GetMessage("TASKS_ADD_TEMPLATE_TASK")?>"><span class="webform-small-button-left"></span><span class="webform-small-button-icon"></span><span class="webform-small-button-right"></span></a>
	</div>
</div>

<div class="task-popup-templates" id="task-popup-templates-popup-content" style="display:none;">
	<div class="task-popup-templates-title"><? echo GetMessage("TASKS_ADD_TEMPLATE_TASK")?></div>
	<div class="popup-window-hr"><i></i></div>
	<? if (sizeof($arParams["TEMPLATES"]) > 0):?>
	<ol class="task-popup-templates-items">
		<? $commonUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit"))?>
		<? foreach($arParams["TEMPLATES"] as $template):?>
		<? $createUrl = $commonUrl.(strpos($commonUrl, "?") === false ? "?" : "&")."TEMPLATE=".$template["ID"];?>
		<li class="task-popup-templates-item"><a class="task-popup-templates-item-link" href="<? echo $createUrl?>" onclick="AddPopupTemplateTask(<? echo $template["ID"]?>, event);"><? echo $template["TITLE"]?></a></li>
		<? endforeach?>
	</ol>
	<? else:?>
	<div class="task-popup-templates-empty"><? echo GetMessage("TASKS_NO_TEMPLATES")?></div>
	<? endif?>
	<div class="popup-window-hr"><i></i></div>
	<a class="task-popup-templates-item task-popup-templates-item-all" href="<?=CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TEMPLATES"], array())?>"><? echo GetMessage("TASKS_TEMPLATES_LIST")?></a>
</div>
<?endif?>
<?$this->EndViewTarget();?>

<script type="text/javascript">

	function createQuickTask(table, params)
	{
		BX.toggleClass(BX("task-list-toolbar-lightning", true), "webform-small-button-active");
		if (BX.hasClass(BX("task-list-toolbar-lightning", true), "webform-small-button-active"))
		{
			ShowQuickTask(table, params);
		}
		else
		{
			HideQuickTask();
		}
	};

	function showGanttFilter(bindElement)
	{
		BX.toggleClass(bindElement, "webform-small-button-active");
		TaskGanttFilterPopup.show(bindElement);
	};

	function showTaskListFilter(bindElement)
	{
		BX.toggleClass(bindElement, "webform-small-button-active");
		TaskListFilterPopup.show(bindElement);
	};

	//Override original HideQuickTask
	var HideQuickTask = (function() {
		var originalFunction = HideQuickTask;
		return function() {
			originalFunction();
			BX.removeClass(BX("task-list-toolbar-lightning", true), "webform-small-button-active")
		};
	})();

</script>