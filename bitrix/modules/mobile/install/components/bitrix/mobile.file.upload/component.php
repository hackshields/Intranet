<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$arParams['MAX_FILE_SIZE'] = intval($arParams['MAX_FILE_SIZE']);
$arParams['MODULE_ID'] = $arParams['MODULE_ID'] && IsModuleInstalled($arParams['MODULE_ID']) ? $arParams['MODULE_ID'] : false;
// ALLOW_UPLOAD = 'A'll files | 'I'mages | 'F'iles with selected extensions
// ALLOW_UPLOAD_EXT = comma-separated list of allowed file extensions (ALLOW_UPLOAD='F')

if (
	$arParams['ALLOW_UPLOAD'] != 'I' &&
	(
		$arParams['ALLOW_UPLOAD'] != 'F' || strlen($arParams['ALLOW_UPLOAD_EXT']) <= 0
	)
)
	$arParams['ALLOW_UPLOAD'] = 'A';

if (
	$_POST['mfu_mode']
	||
	(
		is_array($_FILES)
		&& count($_FILES) > 0
		&& array_key_exists("file", $_FILES)
	)
)
{
	$APPLICATION->RestartBuffer();
	while(ob_end_clean()); // hack!

	if (!check_bitrix_sessid())
		die();

	Header('Content-Type: text/html; charset='.LANG_CHARSET);

	if (
		is_array($_FILES)
		&& count($_FILES) > 0
		&& array_key_exists("file", $_FILES)
	)
	{
		$mid = $arParams['MODULE_ID'];
		$max_file_size = $arParams['MAX_FILE_SIZE'];

		if (!$mid || !IsModuleInstalled($mid))
			$mid = 'main';
			
		$arFile = $_FILES["file"];
		$arFile["MODULE_ID"] = $mid;

		$res = '';
		if ($arParams["ALLOW_UPLOAD"] == "I"):
			$res = ''.CFile::CheckImageFile($arFile, $max_file_size, 0, 0);
		elseif ($arParams["ALLOW_UPLOAD"] == "F"):
			$res = CFile::CheckFile($arFile, $max_file_size, false, $arParams["ALLOW_UPLOAD_EXT"]);
		else:
			$res = CFile::CheckFile($arFile, $max_file_size, false, false);
		endif;

		if ($res === '')
		{
			$fileID = CFile::SaveFile($arFile, $mid);

			if ($fileID)
			{
				if (!isset($_SESSION["MFU_UPLOADED_FILES"]))
					$_SESSION["MFU_UPLOADED_FILES"] = array($fileID);
				else
					$_SESSION["MFU_UPLOADED_FILES"][] = $fileID;
					
				$arResult = $fileID;
			}
		}

		echo intval($arResult);
		die();
	}
	elseif ($_POST['mfu_mode'] == 'delete')
	{
		$fid = intval($_POST["fileID"]);
		if (isset($_SESSION["MFU_UPLOADED_FILES"]) && in_array($fid, $_SESSION["MFU_UPLOADED_FILE"]))
		{
			CFile::Delete($fid);
			$key = array_search(intval($fid), $_SESSION["MFU_UPLOADED_FILES"]);
			unset($_SESSION["MFU_UPLOADED_FILES"][$key]);
		}
	}

	die();
}

if ($arParams['SILENT'])
	return;

if (substr($arParams['INPUT_NAME'], -2) == '[]')
	$arParams['INPUT_NAME'] = substr($arParams['INPUT_NAME'], 0, -2);
if (substr($arParams['INPUT_NAME_UNSAVED'], -2) == '[]')
	$arParams['INPUT_NAME_UNSAVED'] = substr($arParams['INPUT_NAME_UNSAVED'], 0, -2);
if (!is_array($arParams['INPUT_VALUE']) && intval($arParams['INPUT_VALUE']) > 0)
	$arParams['INPUT_VALUE'] = array($arParams['INPUT_VALUE']);

$arParams['INPUT_NAME'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME']) ? $arParams['INPUT_NAME'] : false;
$arParams['INPUT_NAME_UNSAVED'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['INPUT_NAME_UNSAVED']) ? $arParams['INPUT_NAME_UNSAVED'] : '';
$arParams['CONTROL_ID'] = preg_match('/^[a-zA-Z0-9_]+$/', $arParams['CONTROL_ID']) ? $arParams['CONTROL_ID'] : randString();

$arParams['INPUT_CAPTION'] = $arParams['INPUT_CAPTION'] ? $arParams['INPUT_CAPTION'] : GetMessage('MFI_INPUT_CAPTION_DEFAULT');

$arParams['MULTIPLE'] = $arParams['MULTIPLE'] == 'N' ? 'N' : 'Y';

if (!$arParams['INPUT_NAME'])
{
	showError(GetMessage('MFI_ERR_NO_INPUT_NAME'));
	return false;
}


$arResult['FILES'] = array();

if (is_array($arParams['INPUT_VALUE']))
{
	$dbRes = CFile::GetList(array(), array("@ID" => implode(",", $arParams["INPUT_VALUE"])));
	while ($arFile = $dbRes->GetNext())
	{
		$arFile['URL'] = CHTTP::URN2URI($APPLICATION->GetCurPageParam("mfi_mode=down&fileID=".$arFile['ID']."&cid=".$arResult['CONTROL_UID']."&".bitrix_sessid_get(), array("mfi_mode", "fileID", "cid")));
		$arFile['FILE_SIZE_FORMATTED'] = CFile::FormatSize($arFile['FILE_SIZE']);
		$arResult['FILES'][$arFile['ID']] = $arFile;
//		$_SESSION["MFU_UPLOADED_FILES_".$arResult['CONTROL_UID']][] = $arFile['ID'];
	}
}

CUtil::InitJSCore(array('ajax'));

$this->IncludeComponentTemplate();

return $arParams['CONTROL_ID'];
?>
