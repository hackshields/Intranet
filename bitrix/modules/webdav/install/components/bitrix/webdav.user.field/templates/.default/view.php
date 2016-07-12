<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (empty($arResult['IMAGES']) && empty($arResult['FILES']))
	return;

WDUFLoadStyle();

if (sizeof($arResult['IMAGES']) > 0)
{
?><div class="feed-com-files">
	<div class="feed-com-files-title"><?=GetMessage("WDUF_PHOTO")?></div>
	<div class="feed-com-files-cont"><?
		foreach($arResult['IMAGES'] as $id => $arWDFile)
		{
		?><span class="feed-com-files-photo feed-com-files-photo-load" style="width:<?=$arWDFile["thumb"]["width"]?>px;height:<?=$arWDFile["thumb"]["height"]?>px;"><?
			?><img onload="this.parentNode.className='feed-com-files-photo';" <?
			?> src="<?=$arWDFile["thumb"]["src"]?>" <?
			?> width="<?=$arWDFile["thumb"]["width"]?>"<?
			?> height="<?=$arWDFile["thumb"]["height"]?>"<?
			?> border="0"<?
			?> data-bx-viewer="image"<?
			?> data-bx-title="<?=htmlspecialcharsbx($arWDFile["NAME"])?>"<?
			?> data-bx-src="<?=$arWDFile["basic"]["src"] ?>"<?
			?> data-bx-download="<?=$arWDFile["VIEW"] . '?&force_download=1'?>"<?
			?> data-bx-width="<?=$arWDFile["basic"]["width"]?>"<?
			?> data-bx-height="<?=$arWDFile["basic"]["height"]?>"<?
			if (!empty($arWDFile["original"])) {
				?> data-bx-full="<?=$arWDFile["original"]["src"]?>"<?
				?> data-bx-full-width="<?=$arWDFile["original"]["width"]?>" <?
				?> data-bx-full-height="<?=$arWDFile["original"]["height"]?>"<?
				?> data-bx-full-size="<?=$arWDFile["SIZE"]?>"<? }
			?> /><?
		?></span><?
		}
	?></div>
</div><?
}

if (sizeof($arResult['FILES']) > 0)
{
?>
<div id="wdif-block-<?=$arResult['UID']?>" class="feed-com-files">
	<div class="feed-com-files-title"><?=GetMessage('WDUF_FILES')?></div>
	<div class="feed-com-files-cont"><?
		foreach ($arResult['FILES'] as $id => $arWDFile)
		{
			if(isset($arResult['allowExtDocServices']) && $arResult['allowExtDocServices'] && in_array(ltrim($arWDFile["EXTENSION"], '.'), CWebDavExtLinks::$allowedExtensionsGoogleViewer) && $arWDFile["FILE"]['FILE_SIZE'] < CWebDavExtLinks::$maxSizeForView):
		?><a target="_blank" href="<?=htmlspecialcharsbx($arWDFile['PATH'])?>" <?
			?>id="wdif-doc-<?=$arWDFile['ID']?>" <?
			?>title="<?=htmlspecialcharsbx($arWDFile['NAVCHAIN'])?>" <?
			?> data-bx-viewer="iframe"<?
			?> data-bx-title="<?=htmlspecialcharsbx($arWDFile["NAME"])?>"<?
			?> data-bx-src="<?=$arWDFile["VIEW"] . '?showInViewer=1'?>"<?
			?> data-bx-download="<?=$arWDFile["PATH"]?>"<?
			?>alt="<?=htmlspecialcharsbx($arWDFile['NAME'])?>" class="feed-com-file-wrap"><?
			?><span class="feed-com-file-icon feed-file-icon-<?=htmlspecialcharsbx($arWDFile['EXTENSION'])?>"></span><?
			?><span class="feed-com-file-name"><?=htmlspecialcharsbx($arWDFile['NAME'])?></span><?
			?><span class="feed-com-file-size">(<?=$arWDFile['SIZE']?>)</span><?
		?></a>
				<? else: ?>
			<a target="_blank" href="<?=htmlspecialcharsbx($arWDFile['PATH'])?>" <?
			?>id="wdif-doc-<?=$arWDFile['ID']?>" <?
			?>title="<?=htmlspecialcharsbx($arWDFile['NAVCHAIN'])?>" <?
			?> data-bx-viewer="unknown"<?
			?> data-bx-title="<?=htmlspecialcharsbx($arWDFile["NAME"])?>"<?
			?> data-bx-src="<?=$arWDFile["PATH"]?>"<?
			?> data-bx-download="<?=$arWDFile["PATH"]?>"<?
			?> data-bx-size="<?=htmlspecialcharsbx(CFile::FormatSize($arWDFile["FILE"]['FILE_SIZE']))?>"<?
			?> data-bx-owner="<?=htmlspecialcharsbx($arWDFile["CREATED_USER_NAME"])?>"<?
			?> data-bx-dateModify="<?=htmlspecialcharsbx($arWDFile['FILE']["TIMESTAMP_X"])?>"<?
			?> data-bx-tooBigSizeMsg="<?= $arWDFile["FILE"]['FILE_SIZE'] > CWebDavExtLinks::$maxSizeForView ?>"<?
			?>alt="<?=htmlspecialcharsbx($arWDFile['NAME'])?>" class="feed-com-file-wrap"><?
			?><span class="feed-com-file-icon feed-file-icon-<?=htmlspecialcharsbx($arWDFile['EXTENSION'])?>"></span><?
			?><span class="feed-com-file-name"><?=htmlspecialcharsbx($arWDFile['NAME'])?></span><?
			?><span class="feed-com-file-size">(<?=$arWDFile['SIZE']?>)</span><?
		?></a>
				<? endif; ?>
				<?
		}?>
	</div>
</div>
<?
}


?>