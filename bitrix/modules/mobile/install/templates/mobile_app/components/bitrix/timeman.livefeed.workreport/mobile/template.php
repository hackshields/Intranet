<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?><div class="lenta-info-block info-block-<?=($arParams["MARK"] == "G" ? "green" : ($arParams["MARK"] == "B" ? "red" : "blue"))?>">
	<div class="lenta-info-block-l">
		<div class="lenta-info-block-l-text"><?=GetMessage("REPORT_FROM")?>:</div>
		<div class="lenta-info-block-l-text"><?=GetMessage("REPORT_TO")?>:</div>
	</div>
	<div class="lenta-info-block-r">
		<div class="lenta-info-block-data">
			<div class="lenta-info-avatar avatar"<?=(strlen($arParams["USER"]["PHOTO"]) > 0 ? " style=\"background:url('".$arParams["USER"]["PHOTO"]."') no-repeat center center #FFFFFF; background-size: cover;\"" : "")?>></div>
			<div class="lenta-info-name">
				<a href="<?=$arParams["USER"]["URL"]?>" class="lenta-info-name-text"><?=$arParams["USER"]["NAME"]?></a>
				<div class="lenta-info-name-description"><?=htmlspecialcharsbx($arParams["USER"]["WORK_POSITION"])?></div>
			</div>
		</div>
		<div class="lenta-info-block-data">
			<div class="lenta-info-avatar avatar"<?=(strlen($arParams["MANAGER"]["PHOTO"]) > 0 ? " style=\"background:url('".$arParams["MANAGER"]["PHOTO"]."') no-repeat center center #FFFFFF; background-size: cover;\"" : "")?>></div>
			<div class="lenta-info-name">
				<a href="<?=$arParams["MANAGER"]["URL"]?>" class="lenta-info-name-text"><?=$arParams["MANAGER"]["NAME"]?></a>
				<div class="lenta-info-name-description"><?=htmlspecialcharsbx($arParams["MANAGER"]["WORK_POSITION"])?></div>
			</div>
		</div>
	</div>
	<i></i>
</div>