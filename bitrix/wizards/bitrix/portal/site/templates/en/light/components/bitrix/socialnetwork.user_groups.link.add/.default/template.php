<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($arParams["ALLOW_CREATE_GROUP"] == "Y")
	$GLOBALS["INTRANET_TOOLBAR"]->AddButton(array(
		'HREF' => $arParams["~HREF"],
		"TEXT" => GetMessage('SONET_C36_T_CREATE'),
		'ICON' => 'create',
		'SORT' => 1000,
		'ONCLICK' => 'AddPopupGroup(event)'
	));
?>