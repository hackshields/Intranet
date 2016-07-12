<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

$file_id = intval($_POST["file_id"]);
$action = $_POST["action"];

$APPLICATION->IncludeComponent("bitrix:mobile.data", "", Array(
		"START_PAGE" => "/mobile/index.php",
		"MENU_PAGE" => "/mobile/left.php"
	),
	false,
	Array("HIDE_ICONS" => "Y")
);

if (!$GLOBALS["USER"]->IsAuthorized())
{
	echo CUtil::PhpToJsObject(Array('ERROR' => 'CURRENT_USER_NOT_AUTH'));
	die();
}

if (check_bitrix_sessid())
{
	if ($action == "delete")
	{
		if (
			$file_id <= 0
			|| !in_array($file_id, $_SESSION["MFU_UPLOADED_FILES"])
		)
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'NO_FILE'));
			die();
		}

		$rsFile = CFile::GetByID($file_id);
		if ($arFile = $rsFile->Fetch())
		{
			CFile::Delete($file_id);
			foreach($_SESSION["MFU_UPLOADED_FILES"] as $key => $session_file_id)
			{
				if ($session_file_id == $file_id)
				{
					unset($_SESSION["MFU_UPLOADED_FILES"][$key]);
					break;
				}
			}
			
			echo CUtil::PhpToJsObject(Array('SUCCESS' => 'Y', "FILE_ID" => $file_id));
		}
		else
		{
			echo CUtil::PhpToJsObject(Array('ERROR' => 'NO_FILE'));
			die();
		}
	}
}
else
	echo CUtil::PhpToJsObject(Array('ERROR' => 'SESSION_ERROR'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>