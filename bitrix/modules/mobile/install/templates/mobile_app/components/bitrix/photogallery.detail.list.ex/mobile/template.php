<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult["ELEMENTS_LIST"]))
	return true;

$arParams["ID"] = md5(serialize(array("default", $arParams["FILTER"], $arParams["SORTING"])));

/********************************************************************
				Input params
********************************************************************/

if (!empty($arResult["ERROR_MESSAGE"])):
	?><div class="photo-error"><?=ShowError($arResult["ERROR_MESSAGE"])?></div><?
endif;

if ($arParams["LIVEFEED_EVENT_ID"] == "photo")
{
	?><div class="post-item-attached-img-wrap"><?
	foreach ($arResult["ELEMENTS_LIST"] as $key => $arItem)
	{
		$arItem["TITLE"] = trim(htmlspecialcharsEx($arItem["~PREVIEW_TEXT"]), " -");
		?><div class="post-item-attached-img-block"><img class="post-item-attached-img" src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" border="0" title="<?= $arItem["TITLE"]?>" /></div><?
	};
	?></div><?
}
else
{
	foreach ($arResult["ELEMENTS_LIST"] as $key => $arItem)
	{
		$arItem["TITLE"] = trim(htmlspecialcharsEx($arItem["~PREVIEW_TEXT"]), " -");
		?><img class="post-item-post-img" src="<?=$arItem["PREVIEW_PICTURE"]["SRC"]?>" border="0" title="<?= $arItem["TITLE"]?>" /><?
	};
}
?>