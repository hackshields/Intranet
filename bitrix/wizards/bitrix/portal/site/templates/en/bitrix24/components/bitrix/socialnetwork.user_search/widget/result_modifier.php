<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
foreach ($arResult["SEARCH_RESULT"] as $i => $arUser)
{
	if (array_key_exists("IMAGE_FILE", $arUser) && is_array($arUser["IMAGE_FILE"]))
	{
		$arResult["SEARCH_RESULT"][$i]["IMAGE_FILE"] = CFile::ResizeImageGet(
			$arUser["IMAGE_FILE"],
			array("width" => 42, "height" => 42),
				BX_RESIZE_IMAGE_EXACT,
				true
		);
	}
	else
		$arResult["SEARCH_RESULT"][$i]["IMAGE_FILE"] = false;
}