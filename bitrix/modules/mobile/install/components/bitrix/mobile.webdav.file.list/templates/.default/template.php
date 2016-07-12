<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();

$APPLICATION->SetPageProperty("BodyClass","file-card-page");
//print_r($arResult);
?>
<div class="file-card-wrap">
	<div class="file-card-name"><span class="file-card-name-icon" style="background-image:url(<? echo $arResult["IMAGE"]; ?>)"></span><? echo htmlspecialcharsbx($arResult["NAME"]); ?></div>
	<div class="file-card-block">
		<div class="file-card-description">
			<? echo htmlspecialcharsbx($arResult["DESCRIPTION"]); ?>
		</div>
		<div class="file-card-description-row">
			<span class="file-card-description-left"><? echo GetMessage("WD_MOBILE_SIZE"); ?></span><span class="file-card-description-right"><?  echo CFile::FormatSize(intval($arResult["FILE_SIZE"])); ?></span>
		</div>
		<div class="file-card-description-row">
			<span class="file-card-description-left"><? echo GetMessage("WD_MOBILE_CREATE"); ?></span><span class="file-card-description-right"><?  echo $arResult["DATE_CREATE"]; ?></span>
		</div>
		<div class="file-card-description-row">
			<span class="file-card-description-left"><? echo GetMessage("WD_MOBILE_MODIFIED"); ?></span><span class="file-card-description-right"><?  echo $arResult["DATE_MODIFIED"]; ?></span>
		</div>
	</div>
	
	<div class="file-card-informers">
	<?
	/*
		<div class="post-item-informers post-item-inform-comments">
			<div class="post-item-inform-left"></div><div class="post-item-inform-right"><span>3</span><span>+31</span></div>
		</div>
		<div class="post-item-informers post-item-inform-likes">
			<div class="post-item-inform-left"></div><div class="post-item-inform-right">+20</div>
		</div>
		<div class="post-item-informers post-item-inform-likes-active">
			<div class="post-item-inform-left"></div><div class="post-item-inform-right">+20</div>
		</div>
		<div class="post-item-informers post-item-inform-likes">
			<div class="post-item-inform-left"></div><div class="post-item-inform-right"><span>3</span></div>
		</div>
	*/
	?>
	</div>
	
	<div class="file-card-review-btn" onclick="app.openDocument({'url' : '<? echo $arResult["URL"]; ?>'});" ><? echo GetMessage("WD_MOBILE_VIEW_FILE"); ?></div>
</div>