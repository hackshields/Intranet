<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if ($arResult['INCLUDE_LANG'])
	__IncludeLang($_SERVER['DOCUMENT_ROOT'].$this->GetFolder().'/lang/'.LANGUAGE_ID.'/template.php');

$APPLICATION->IncludeComponent(
	'bitrix:tasks.list', 
	'', 
	array(
		"FILTER" => array('ID' => count($arResult['ITEM']['TASKS']) > 0 ? $arResult['ITEM']['TASKS'] : array(-1)),
		"HIDE_VIEWS" => "Y",
		"AJAX_MODE" => "Y",
		"AJAX_OPTION_SCROLL" => "N",
		"ITEMS_COUNT" => "10",
		"SET_NAVCHAIN" => "N",
		"PATH_TO_USER_PROFILE" => str_replace(
			array('#USER_ID#', '#ID#'), 
			array('#user_id#', '#id#'), 
			COption::GetOptionString('intranet', 'path_user', '', SITE_ID)
		),
	), 
	null, array('HIDE_ICONS' => 'Y')
);
?>
<script type="text/javascript">
<?
foreach ($arResult['ITEM']['TASKS'] as $task_id):
?>
if (tasksMenuPopup[<?=$task_id?>])
{
	tasksMenuPopup[<?=$task_id?>].push({text:'<?=CUtil::JSEscape(GetMessage('MI_TASK_DETACH'))?>',title:'<?=CUtil::JSEscape(GetMessage('MI_TASK_DETACH_TITLE'))?>',className:"task-menu-popup-item-delete",onclick:function(e){detachTask(<?=$task_id?>); this.popupWindow.close();}});
}
<?
endforeach;
?>
</script>