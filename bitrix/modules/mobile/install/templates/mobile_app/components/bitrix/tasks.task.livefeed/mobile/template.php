<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
?>
<div class="lenta-info-block info-block-blue">
	<div class="lenta-info-block-l">
		<div class="lenta-info-block-l-text"><?=GetMessage("TASKS_SONET_LOG_RESPONSIBLE_ID")?>:</div>
	</div>
	<div class="lenta-info-block-r">
		<div class="lenta-info-block-data">
			<div class="lenta-info-avatar avatar"<?=($arResult["PHOTO"] ? " style=\"background:url('".$arResult["PHOTO"]["CACHE"]["src"]."') no-repeat center center #FFFFFF;  background-size: cover;\"" : "")?>></div>
			<div class="lenta-info-name">
				<a href="<?=$arResult["PATH_TO_USER"]?>" class="lenta-info-name-text"><?=htmlspecialcharsbx(CUser::FormatName($arParams["NAME_TEMPLATE"], $arResult["USER"]))?></a>
				<div class="lenta-info-name-description"><?=htmlspecialcharsbx($arResult["USER"]["WORK_POSITION"])?></div>
			</div>
		</div>
	</div>
	<i></i>
</div><?
if (strlen($arParams["MESSAGE_24_2"]) > 0 && strlen($arParams["CHANGES_24"]) > 0)
{
	?><div class="lenta-info-block-description">
		<div class="lenta-info-block-description-title"><?=htmlspecialcharsbx($arParams["MESSAGE_24_2"])?>:</div>
		<div class="lenta-info-block-description-text"><?=htmlspecialcharsbx($arParams["CHANGES_24"])?></div>
	</div><?
}
?>