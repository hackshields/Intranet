<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (strlen($arResult["ERROR_MESSAGE"]) > 0 || count($arResult["SEARCH_RESULT"]) < 1)
	return;

$this->SetViewTarget("sidebar", 400);
?>

<div class="sidebar-widget sidebar-widget-rating">
	<div class="sidebar-widget-title"><?=GetMessage("WIDGET_RATING_TITLE")?></div>
	<div class="sidebar-widget-content">

	<?foreach ($arResult["SEARCH_RESULT"] as $i => $arUser):?>

	<div class="sidebar-widget-user<?=($i == 0 ? " sidebar-widget-user-first" : "")?>">
		<div class="sidebar-widget-user-avatar"><img src="<?= ($arUser["IMAGE_FILE"] ? $arUser["IMAGE_FILE"]["src"] : SITE_TEMPLATE_PATH."/images/user-default-avatar.png") ?>" width="30" height="30" /></div>
		<div class="sidebar-widget-user-info">
			<a href="<?=$arUser["URL"]?>" class="sidebar-widget-user-name"><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, true, false);?></a>
		</div>
		<div class="sidebar-widget-user-rating"><?if (array_key_exists("RATING_".$arParams["RATING_ID"], $arUser)):?><?=round($arUser["RATING_".$arParams["RATING_ID"]])?><?else:?>-<?endif?></div>
	</div>
	
	<?endforeach?>

	</div>
</div>