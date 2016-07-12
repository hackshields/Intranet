<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

foreach ($arResult["ELEMENTS_LIST"] as $key => $arItem)
{
	if (intval($arParams["THUMBNAIL_SIZE"]) > 0)
	{
		$image_resize = CFile::ResizeImageGet(
			($arParams["LIVEFEED_EVENT_ID"] == "photo_photo" ? $arItem["PROPERTIES"]["REAL_PICTURE"]["VALUE"] : $arItem["~PREVIEW_PICTURE"]), 
			array(
				"width" => $arParams["THUMBNAIL_SIZE"], 
				"height" => $arParams["THUMBNAIL_SIZE"]
			),
			($arParams["THUMBNAIL_RESIZE_METHOD"] == "EXACT" ? BX_RESIZE_IMAGE_EXACT : BX_RESIZE_IMAGE_PROPORTIONAL)
		);
		$arResult["ELEMENTS_LIST"][$key]["PREVIEW_PICTURE"]["SRC"] = $image_resize["src"];
	}
}
?>