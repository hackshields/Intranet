<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (intval($USER->GetID()) <= 0)
	return;

if (!CModule::IncludeModule('im'))
	return;


$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "ml-page");
if (isset($arParams['TEMPLATE_POPUP']) && $arParams['TEMPLATE_POPUP'] == 'Y')
{
	$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "ml-page ml-page-popup");
	$GLOBALS["APPLICATION"]->SetPageProperty("Viewport", "user-scalable=no, initial-scale=1.0, maximum-scale=1.0, width=290");
}

$arResult['ELEMENTS'] = CIMContactList::GetRecentList(Array(
	'LOAD_LAST_MESSAGE' => 'Y',
	'LOAD_UNREAD_MESSAGE' => 'Y'
));
$arResult['COUNTERS'] = Array();
foreach ($arResult['ELEMENTS'] as $userId => $value)
{
	if (isset($value['MESSAGE']['counter']) && $value['MESSAGE']['counter'] > 0)
		$arResult['COUNTERS'][$userId] = $value['MESSAGE']['counter'];
}

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>