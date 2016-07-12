<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

$site_id = isset($_REQUEST["site"]) && is_string($_REQUEST["site"]) ? trim($_REQUEST["site"]) : "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = isset($_REQUEST["action"]) && is_string($_REQUEST["action"]) ? trim($_REQUEST["action"]) : "";

$lng = isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"]) ? trim($_REQUEST["lang"]) : "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && is_string($_REQUEST["ls"]) ? trim($_REQUEST["ls"]) : "";
$ls_arr = isset($_REQUEST["ls_arr"])? $_REQUEST["ls_arr"]: "";

$as = isset($_REQUEST["as"]) ? intval($_REQUEST["as"]) : 58;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
else
	define("LANGUAGE_ID", "en");

$APPLICATION->IncludeComponent("bitrix:mobile.data", "", Array(
		"START_PAGE" => "/mobile/index.php",
		"MENU_PAGE" => "/mobile/left.php"
	),
	false,
	Array("HIDE_ICONS" => "Y")
);

__IncludeLang(dirname(__FILE__)."/lang/".$lng."/ajax.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = array();

	if (!$GLOBALS["USER"]->IsAuthorized())
		$arResult[0] = "*";
	elseif (!check_bitrix_sessid())
		$arResult[0] = "*";
	elseif ($action == "change_favorites")
	{
		$log_id = $_REQUEST["log_id"];
		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			if ($strRes = CSocNetLogFavorites::Change($GLOBALS["USER"]->GetID(), $log_id))
			{
				$arResult["bResult"] = $strRes;
				if ($strRes == "Y")
					CSocNetLogFollow::Set($GLOBALS["USER"]->GetID(), "L".$log_id, "Y");
			}
			else
			{
				if($e = $GLOBALS["APPLICATION"]->GetException())
					$arResult["strMessage"] = $e->GetString();
				else
					$arResult["strMessage"] = GetMessage("SONET_LOG_FAVORITES_CANNOT_CHANGE");
				$arResult["bResult"] = "E";
			}
		}
		else
		{
			$arResult["strMessage"] = GetMessage("SONET_LOG_FAVORITES_INCORRECT_LOG_ID");
			$arResult["bResult"] = "E";
		}
	}
	elseif ($action == "change_follow")
	{
		$log_id = intval($_REQUEST["log_id"]);

		if (
			($log_id > 0)
			&& $strRes = CSocNetLogFollow::Set($GLOBALS["USER"]->GetID(), "L".$log_id, ($_REQUEST["follow"] == "Y" ? "Y" : "N"))
		)
			$arResult["SUCCESS"] = "Y";
		else
			$arResult["SUCCESS"] = "N";
	}

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>