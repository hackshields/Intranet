<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (count($arResult["ITEMS"]) < 1)
	return;

$this->SetViewTarget("sidebar", 100);
?>

<div class="sidebar-widget sidebar-widget-calendar">
	<div class="sidebar-widget-top">
		<div class="sidebar-widget-top-title"><?=GetMessage("WIDGET_CALENDAR_TITLE")?></div>
		<a href="<?=$arParams["DETAIL_URL"]?>?EVENT_ID=NEW" class="plus-icon"></a>
	</div>
	<div class="sidebar-widget-content">
	<?
	foreach($arResult["ITEMS"] as $i => $arItem):?>
		<a  href="<?=$arItem["_DETAIL_URL"]?>" class="sidebar-widget-item<?if($i == 0):?> widget-first-item<?endif?><?if($i == count($arResult["ITEMS"])-1):?> widget-last-item<?endif?>">
			<span class="calendar-item-date"><?=$arItem["DATE_FROM"]?></span>
			<span class="calendar-item-text">
				<span class="calendar-item-link"><?=htmlspecialcharsbx($arItem["NAME"])?></span>
			</span>
			<span class="calendar-item-icon"></span>
		</a>
	<?endforeach?>
	</div>
</div>