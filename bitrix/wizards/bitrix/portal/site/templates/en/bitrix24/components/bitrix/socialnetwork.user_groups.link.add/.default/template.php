<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$this->SetViewTarget("pagetitle", 100);
	?><span class="sonet-groups-title-buttons"><?
	
		?><span class="sonet-groups-title-button-search"><form action="" method="POST" id="sonet_groups_search_form"><?
			?><input type="hidden" name="filter_my" value="<?=$arResult["filter_my"]?>"><?
			?><input type="hidden" name="filter_archive" value="<?=$arResult["filter_archive"]?>"><?
			?><input type="hidden" name="filter_extranet" value="<?=$arResult["filter_extranet"]?>"><?
			?><span class="sonet-groups-title-button-search-left"></span><?
			?><span class="sonet-groups-title-button-search-textbox"><?
				?><input name="filter_name" value="<?=$arResult["filter_name"]?>" type="text" onblur="BX.removeClass(this.parentNode.parentNode, 'sonet-groups-title-button-search-full'); /* this.value=''; */" onclick="BX.addClass(this.parentNode.parentNode, 'sonet-groups-title-button-search-full')" class="sonet-groups-title-button-search-input"><?
				?><span class="sonet-groups-title-button-search-icon" onclick="var form = BX('sonet_groups_search_form'); BX.submit(form);"></span><?
			?></span><?
			?><span class="sonet-groups-title-button-search-right"></span><?
		?></form></span><?

		if ($arParams["ALLOW_CREATE_GROUP"] == "Y")
		{
			?><span class="sonet-groups-create-group-button" onclick="AddPopupGroup(event);"><?
				?><span class="sonet-groups-create-left"></span><?
				?><span class="sonet-groups-create-text"><?=GetMessage("SONET_C36_T_CREATE")?></span><span class="sonet-groups-create-right"></span><?
			?></span><?
		}

	?></span><?
$this->EndViewTarget();
?>