<?
//**********************************************************************/
//**    DO NOT MODIFY THIS FILE                                       **/
//**    MODIFICATION OF THIS FILE WILL ENTAIL SITE FAILURE            **/
//**********************************************************************/
if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");

	if(!$USER->CanDoOperation('install_updates') || !check_bitrix_sessid())
		$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));
}

@set_time_limit(0);
ini_set("track_errors", "1");
ignore_user_abort(true);

IncludeModuleLangFile(__FILE__);

$errorMessage = "";

$stableVersionsOnly = COption::GetOptionString("main", "stable_versions_only", "Y");

$queryType = $_REQUEST["query_type"];
if (!in_array($queryType, array("search", "register", "coupon")))
	$queryType = "search";

/************************************/
if ($queryType == "search")
{
	$searchModule = $APPLICATION->UnJSEscape($_REQUEST["search_module"]);

	$arModules = CUpdateClientPartner::SearchModules($searchModule, LANG);

	if ($arModules)
	{
		if (array_key_exists("MODULE", $arModules) && is_array($arModules["MODULE"]))
		{
			foreach ($arModules["MODULE"] as $v)
			{
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["IMAGE"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["ID"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["NAME"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["DESCRIPTION"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["PARTNER"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["DATE_UPDATE"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["IMAGE_HEIGHT"])."~#~";
				echo preg_replace("/~#{1,2}~/", "", $v["@"]["IMAGE_WIDTH"])."~#~";
				echo "~##~";
			}
		}
	}
}
elseif ($queryType == "register")
{
	echo "Y";
}
elseif ($queryType == "coupon")
{
	$coupon = $APPLICATION->UnJSEscape($_REQUEST["COUPON"]);
	CUpdateClientPartner::AddMessage2Log("Coupon activated", "UPD_SUCCESS");
	echo "Y";
}


/************************************/

if (!defined("UPD_INTERNAL_CALL") || UPD_INTERNAL_CALL != "Y")
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin_after.php");
}
?>