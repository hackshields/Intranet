<?
if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "fl-page");

CUtil::InitJSCore(array('ajax'));

if (intval($arParams["THUMBNAIL_SIZE"]) <= 0)
	$arParams["THUMBNAIL_SIZE"] = 100;

$arResult = array(
	"FILES" => array()
);

if (is_array($_SESSION["MFU_UPLOADED_FILES"]))
{
	foreach($_SESSION["MFU_UPLOADED_FILES"] as $fileID)
	{
		$rsFile = CFile::GetByID($fileID);
		if ($arFile = $rsFile->Fetch())
		{
			$image_resize = CFile::ResizeImageGet(
				$arFile, 
				array(
					"width" => $arParams["THUMBNAIL_SIZE"], 
					"height" => $arParams["THUMBNAIL_SIZE"]
				),
				($arParams["THUMBNAIL_RESIZE_METHOD"] == "EXACT" ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL),
				true,
				false,
				false
			);

			$arResult["FILES"][] = array(
				"id" => $fileID,
				"src" => $image_resize["src"],
				"name" => $arFile["ORIGINAL_NAME"]
			);
		}
	}
}

$this->IncludeComponentTemplate();
?>