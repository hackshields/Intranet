<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

CUtil::InitJSCore(array('popup', 'tooltip'));

$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/tasks.templates.list/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/tasks/task-popups.js");

$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/intranet/intranet-common.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/main/core/css/core_popup.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/tasks/css/tasks.css");

$GLOBALS["APPLICATION"]->IncludeComponent(
	'bitrix:main.calendar',
	'',
	array(
		'SILENT' => 'Y',
	),
	null,
	array('HIDE_ICONS' => 'Y')
);

$arPaths = array(
	"PATH_TO_TEMPLATES_TEMPLATE" => $arParams["PATH_TO_TEMPLATES_TEMPLATE"],
	"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
	"PATH_TO_TASKS_TASK" => $arParams["PATH_TO_TASKS_TASK"]	
);
?>
<script type="text/javascript">
var ajaxUrl = "/bitrix/components/bitrix/tasks.templates.list/ajax.php?SITE_ID=<?php echo SITE_ID?>";
</script>
<div class="task-list">
	<div class="task-list-left-corner"></div>
	<div class="task-list-right-corner"></div>
	<table class="task-list-table" cellspacing="0" id="task-list-table">

		<colgroup>
			<col class="task-title-column" />
			<col class="task-menu-column" />
			<col class="task-flag-column" />
			<col class="task-priority-column" />
			<col class="task-deadline-column" />
			<col class="task-responsible-column" />
			<col class="task-director-column" />
			<col class="task-grade-column" />
			<col class="task-complete-column" />
		</colgroup>

		<thead>
			<tr>
				<th class="task-title-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "TITLE"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>"  colspan="4" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=TITLE&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "TITLE" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell">
						<span class="task-head-cell-sort-order"></span>
						<span class="task-head-cell-title"><?php echo GetMessage("TASKS_TITLE")?></span>
					</div>
				</th>
				<th class="task-deadline-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "DEADLINE"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=DEADLINE&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "DEADLINE" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_DEADLINE")?></span></div></th>
				<th class="task-responsible-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "RESPONSIBLE_ID"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=RESPONSIBLE_ID&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "RESPONSIBLE_ID" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_RESPONSIBLE")?></span></div></th>
				<th  class="task-director-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "CREATED_BY"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>"  onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=CREATED_BY&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "CREATED_BY" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_CREATOR")?></span></div></th>

				<th class="task-grade-column">&nbsp;</th>
				<th class="task-complete-column">&nbsp;</th>

			</tr>
		</thead>
		<tbody>
			<?php if (sizeof($arResult["TEMPLATES"]) > 0):?>
				<?php foreach($arResult["TEMPLATES"] as $key=>$template):?>
					<?php templatesRenderListItem($template, $arPaths, 0, true, false, $arParams["NAME_TEMPLATE"])?>
				<?php endforeach?>
			<?php else:?>
				<tr class="task-list-item" id="task-list-no-tasks"><td class="task-new-item-column" colspan="9" style="text-align: center"><?php echo GetMessage("TASKS_NO_TEMPLATES")?></td></tr>
			<?php endif?>
		</tbody>
	</table>
</div>

<br />
<?php echo $arResult["NAV_STRING"]?>
<?php

$arComponentParams = array(
	'SHOW_TASK_LIST_MODES'   => 'N',
	'SHOW_HELP_ICON'         => 'N',
	'SHOW_SEARCH_FIELD'      => 'N',
	'SHOW_TEMPLATES_TOOLBAR' => 'N',
	'SHOW_QUICK_TASK_ADD'    => 'N',
	'SHOW_ADD_TASK_BUTTON'   => 'N',
	'SHOW_FILTER_BUTTON'     => 'N',
	'CUSTOM_ELEMENTS' => array(
		'ADD_BUTTON' => array(
			'name'    =>  GetMessage('TASKS_ADD_TEMPLATE'),
			'onclick' =>  null,
			'url'     =>  CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TEMPLATES_TEMPLATE"], array("template_id" => 0, "action" => "edit"))
		),
		'BACK_BUTTON' => array(
			'name'    =>  GetMessage('TASKS_ADD_BACK_TO_TASKS_LIST'),
			'onclick' =>  null,
			'url'     =>  CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS"], array())
		)
	)
);

$APPLICATION->IncludeComponent(
	'bitrix:tasks.list.controls',
	((defined('SITE_TEMPLATE_ID') && (SITE_TEMPLATE_ID === 'bitrix24')) ? 'default_b24' : '.default'),
	$arComponentParams,
	null,
	array('HIDE_ICONS' => 'Y')
);
