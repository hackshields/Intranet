<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("iblock")):
	ShowError(GetMessage("F_NO_MODULE_IBLOCK"));
	return 0;
elseif (!CModule::IncludeModule("webdav")):
	ShowError(GetMessage("F_NO_MODULE_WEBDAV"));
	return 0;
endif;

$path = dirname(__FILE__);
include_once($path . '/functions.php');

$componentPage = 'edit';
/********************************************************************
				Input params
********************************************************************/

$arParams["EDIT"] = (($arParams["EDIT"] == 'Y') ? $arParams["EDIT"] : 'N');
$arParams["PARAMS"] = (is_array($arParams["PARAMS"]) ? $arParams["PARAMS"] : array());
$arParams["RESULT"] = (is_array($arParams["RESULT"]) ? $arParams["RESULT"] : array());
$arParams["DATE_TIME_FORMAT"] = trim(empty($arParams["DATE_TIME_FORMAT"]) ? $DB->DateFormatToPHP(CSite::GetDateFormat("FULL")):$arParams["DATE_TIME_FORMAT"]);
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array("#NOBR#","#/NOBR#"), array("",""), $arParams["NAME_TEMPLATE"]);

/********************************************************************
				/Input params
********************************************************************/

$arResult['UID'] = randString(5);

$arResult['allowExtDocServices'] = 'Y' ==
		COption::GetOptionString('webdav', 'webdav_allow_ext_doc_services_global', CWebDavIblock::resolveDefaultUseExtServices());

if ($arParams['EDIT'] == 'Y')
{
	WDUFUserFieldEdit($arParams["PARAMS"], $arParams["RESULT"]);
}
else
{
	$componentPage = ($arParams['VIEW_THUMB'] == 'Y' ? 'view_with_features' : 'view');
	WDUFUserFieldView($arParams["PARAMS"], $arParams["RESULT"]);
}
$arResult = array_merge($arResult, $arParams['RESULT']);
CJSCore::Init(array('viewer'));
$this->IncludeComponentTemplate($componentPage);
?>