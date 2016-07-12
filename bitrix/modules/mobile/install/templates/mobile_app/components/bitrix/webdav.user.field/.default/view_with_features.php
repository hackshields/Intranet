<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (sizeof($arResult['FILES']) <= 0)
	return;
WDUFLoadStyle();
foreach ($arResult['FILES'] as $id => $arWDFile)
{
	if (CFile::IsImage($arWDFile['NAME']))
	{
		?><img src="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2N88f7jfwAJWAPJBTw90AAAAABJRU5ErkJggg==" <?
			?> border="0" <?
			?> data-preview-src="<?=$arWDFile['SMALL_SRC']?>" <?
			?> data-src="<?=$arWDFile['SRC']?>" <?
			?> title="<?=htmlspecialcharsbx($arWDFile['NAME'])?>" <?
			?> alt="<?=htmlspecialcharsbx($arWDFile['NAME'])?>" <?
			?> data-bx-image="<?=$arWDFile['PATH']?>" <?
			?> width="<?=round($arWDFile['WIDTH']/2)?>" <?
			?> height="<?=round($arWDFile['HEIGHT']/2)?>" /><?
	}
	else
	{
		?><a target="_blank" href="<?=htmlspecialcharsbx($arWDFile['PATH'])?>" <?
			?>id="wdif-doc-<?=$arWDFile['ID']?>" <?
			?>title="<?=htmlspecialcharsbx($arWDFile['NAVCHAIN'])?>" <?
			?>alt="<?=htmlspecialcharsbx($arWDFile['NAME'])?>" class="feed-com-file-wrap"><?
			?><span class="feed-com-file-icon feed-file-icon-<?=htmlspecialcharsbx($arWDFile['EXTENSION'])?>"></span><?
			?><span class="feed-com-file-name"><?=htmlspecialcharsbx($arWDFile['NAME'])?></span><?
			?><span class="feed-com-file-size">(<?=$arWDFile['SIZE']?>)</span><?
		?></a><?
	}
}
?>