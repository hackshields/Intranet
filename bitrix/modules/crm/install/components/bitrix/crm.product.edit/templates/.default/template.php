<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

global $APPLICATION;
$arTabs = array();
$arTabs[] = array(
	'id' => 'tab_1',
	'name' => GetMessage('CRM_TAB_1'),
	'title' => GetMessage('CRM_TAB_1_TITLE'),
	'icon' => '',
	'fields'=> $arResult['FIELDS']['tab_1']
);

CCrmGridOptions::SetTabNames($arResult['FORM_ID'], $arTabs);

$APPLICATION->IncludeComponent(
	'bitrix:main.interface.form',
	'',
	array(
		'FORM_ID' => $arResult['FORM_ID'],
		'TABS' => $arTabs,
		'BUTTONS' => array(
			'standard_buttons' => true,
			'back_url' => $arResult['BACK_URL'],
			'custom_html' => '<input type="hidden" name="product_id" value="'.$arResult['PRODUCT_ID'].'"/>'
		),
		'DATA' => $arResult['PRODUCT'],
		'SHOW_SETTINGS' => 'Y',
		'THEME_GRID_ID' => $arResult['GRID_ID'],
		'SHOW_FORM_TAG' => 'Y'
	),
	$component,
	array('HIDE_ICONS' => 'Y')
);
?>