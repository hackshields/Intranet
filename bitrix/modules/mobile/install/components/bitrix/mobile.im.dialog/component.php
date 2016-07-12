<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_REQUEST['AJAX_CALL']) && $_REQUEST['AJAX_CALL'] == 'Y')
	return;

if (intval($USER->GetID()) <= 0)
	return;

if (!CModule::IncludeModule('im'))
	return;

$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "im-page");

$arResult = Array();
if (substr($_GET['id'], 0, 4) == 'chat')
{
	$chatId = intval(substr($_GET['id'], 4));

	$CIMChat = new CIMChat();
	$arMessage = $CIMChat->GetLastMessage($chatId, false, Array('DEPARTMENT' => 'N'));

	$CIMChat->SetReadMessage($chatId);
	$arResult['ID'] = 'chat'.$chatId;
	$arMessage['chat'][$chatId]['id'] = $arResult['ID'];
	$arResult['CHAT_ID'] = $chatId;
	$arResult['IS_CHAT'] = true;
	$arResult['DIALOG'] = $arMessage['chat'][$chatId];
}
else
{
	$CIMMessage = new CIMMessage(false, Array(
		'hide_link' => false
	));
	$arMessage = $CIMMessage->GetLastMessage($_GET['id'], false, Array('DEPARTMENT' => 'N'));

	$CIMMessage->SetReadMessage($_GET['id']);
	$arResult['ID'] = intval($_GET['id']);
	$arResult['CHAT_ID'] = 0;
	$arResult['IS_CHAT'] = false;
	$arResult['DIALOG'] = $arMessage['users'][$arResult['ID']];
}

$arResult['MESSAGES'] = $arMessage['message'];
$arResult['USERS'] = $arMessage['users'];

CJSCore::Init(array('date'));

if (!(isset($arParams['TEMPLATE_HIDE']) && $arParams['TEMPLATE_HIDE'] == 'Y'))
	$this->IncludeComponentTemplate();

return $arResult;

?>