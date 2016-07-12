<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<span id="sonet-log-filter" class="sonet-log-filter-block">
	<div class="filter-block-title"><?=GetMessage("SONET_C30_T_FILTER_TITLE")?></div>
	<form method="GET" name="log_filter" target="_self"><?
	$userName = "";
	if (intval($arParams["CREATED_BY_ID"]) > 0)
	{
		$rsUser = CUser::GetByID($arParams["CREATED_BY_ID"]);
		if ($arUser = $rsUser->Fetch())
			$userName = CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, ($arParams["SHOW_LOGIN"] != "N" ? true : false));
	}
	?><div class="filter-field">
		<label class="filter-field-title" for="filter-field-created-by"><?=GetMessage("SONET_C30_T_FILTER_CREATED_BY");?></label>
		<span class="webform-field webform-field-textbox<?=(!$arParams["CREATED_BY_ID"]?" webform-field-textbox-empty":"")?> webform-field-textbox-clearable">
			<span id="sonet-log-filter-created-by" class="webform-field-textbox-inner" style="width: 200px; padding: 0 20px 0 4px;">
				<input type="text" class="webform-field-textbox" id="filter-field-created-by" value="<?=$userName?>" style="height: 20px; width: 200px;"/>
				<a class="sonet-log-field-textbox-clear" href=""></a>
			</span>
		</span>
	</div>
	<input type="hidden" name="flt_created_by_id" value="<?=$arParams["CREATED_BY_ID"]?>" id="filter_field_createdby_hidden">
	<? $APPLICATION->IncludeComponent(
		"bitrix:intranet.user.selector.new", ".default", array(
			"MULTIPLE" => "N",
			"NAME" => "FILTER_CREATEDBY",
			"VALUE" => intval($arParams["CREATED_BY_ID"]),
			"POPUP" => "Y",
			"INPUT_NAME" => "filter-field-created-by",
			"ON_SELECT" => "onFilterCreatedBySelect",
			"SITE_ID" => SITE_ID,
			"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"SHOW_EXTRANET_USERS" => "FROM_MY_GROUPS"
		), null, array("HIDE_ICONS" => "Y")
	);

	if (array_key_exists("flt_comments", $_REQUEST) && $_REQUEST["flt_comments"] == "Y")
		$bChecked = true;
	else
		$bChecked = false;
	?><div id="flt_comments_cont" style="visibility: <?=(intval($arParams["CREATED_BY_ID"]) > 0 ? "visible" : "hidden")?>"><nobr><input type="checkbox" id="flt_comments" name="flt_comments" value="Y" <?=($bChecked ? "checked" : "")?>> <label for="flt_comments"><?=GetMessage("SONET_C30_T_FILTER_COMMENTS")?></label></nobr></div><?

	if ($arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP || intval($_REQUEST["flt_group_id"]) > 0)
	{
		?><div class="filter-field" style="padding-top: 10px;">
			<label class="filter-field-title" for="filter-field-group"><?=GetMessage("SONET_C30_T_FILTER_GROUP");?></label>
			<span class="webform-field webform-field-textbox<?=(!$arResult["Group"]["ID"]?" webform-field-textbox-empty":"")?> webform-field-textbox-clearable">
				<span id="sonet-log-filter-group" class="webform-field-textbox-inner" style="width: 200px; padding: 0 20px 0 4px;">
					<input type="text" class="webform-field-textbox" id="filter-field-group" value="<?=$arResult["Group"]["NAME"]?>" style="height: 20px; width: 200px;"/>
					<a class="sonet-log-field-textbox-clear" href=""></a>
				</span>
			</span>
		</div>
		<input type="hidden" name="flt_group_id" value="<?=$arResult["Group"]["ID"]?>" id="filter_field_group_hidden">
		<? $APPLICATION->IncludeComponent(
				"bitrix:socialnetwork.group.selector",
				".default",
				array(
					"BIND_ELEMENT" => "sonet-log-filter-group",
					"JS_OBJECT_NAME" => "filterGroupsPopup",
					"ON_SELECT" => "onFilterGroupSelect",
					"SEARCH_INPUT" => "filter-field-group",
					"SELECTED" => $arResult["Group"]["ID"] ? $arResult["Group"]["ID"] : 0
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
	}

	?><div class="sonet-log-filter-line"></div><?

	?><div class="filter-field filter-field-date-combobox">
		<label for="flt-date-datesel" class="filter-field-title"><?=GetMessage("SONET_C30_T_FILTER_DATE");?></label>
		<select name="flt_date_datesel" onchange="__logOnDateChange(this)" class="filter-dropdown" id="flt-date-datesel"><?
		foreach($arResult["DATE_FILTER"] as $k=>$v):
			?><option value="<?=$k?>"<?if($_REQUEST["flt_date_datesel"] == $k) echo ' selected="selected"'?>><?=$v?></option><?
		endforeach;
		?></select>
	</div>
	<span class="filter-field filter-day-interval" style="display:none" id="flt_date_day_span">
		<input type="text" name="flt_date_days" value="<?=htmlspecialcharsbx($_REQUEST["flt_date_days"])?>" class="filter-date-days" size="2" /> <?echo GetMessage("SONET_C30_DATE_FILTER_DAYS")?>
	</span>
	<span class="filter-date-interval filter-date-interval-after filter-date-interval-before">
		<span class="filter-field filter-date-interval-from" style="display:none" id="flt_date_from_span">
			<input type="text" name="flt_date_from" value="<?=(array_key_exists("LOG_DATE_FROM", $arParams) ? $arParams["LOG_DATE_FROM"] : "")?>" class="filter-date-interval-from" /><?
			$APPLICATION->IncludeComponent(
				"bitrix:main.calendar",
				"",
				array(
					"SHOW_INPUT"	=> "N",
					"INPUT_NAME"	=> "flt_date_from",
					"INPUT_VALUE"	=> (array_key_exists("LOG_DATE_FROM", $arParams) ? $arParams["LOG_DATE_FROM"] : ""),
					"FORM_NAME"		=> "log_filter",
				),
				$component,
				array("HIDE_ICONS"	=> true)
			);?></span>
		<span class="filter-date-interval-hellip" style="display:none" id="flt_date_hellip_span">&hellip;</span>
		<span class="filter-field filter-date-interval-to" style="display:none" id="flt_date_to_span">
			<input type="text" name="flt_date_to" value="<?=(array_key_exists("LOG_DATE_TO", $arParams) ? $arParams["LOG_DATE_TO"] : "")?>" class="filter-date-interval-to" /><?
			$APPLICATION->IncludeComponent(
				"bitrix:main.calendar",
				"",
				array(
					"SHOW_INPUT"	=> "N",
					"INPUT_NAME"	=> "flt_date_to",
					"INPUT_VALUE"	=> (array_key_exists("LOG_DATE_TO", $arParams) ? $arParams["LOG_DATE_TO"] : ""),
					"FORM_NAME"		=> "log_filter",
				),
				$component,
				array("HIDE_ICONS"	=> true)
			);?></span>
	</span>
	<script type="text/javascript">
		BX.ready(function(){
				BX.addCustomEvent('onAjaxInsertToNode', __logOnAjaxInsertToNode);
				__logOnDateChange(document.forms['log_filter'].flt_date_datesel);
				if (BX('sonet_log_comment_text'))
					BX('sonet_log_comment_text').onkeydown = BX.eventCancelBubble;
			}
		);
	</script>
	<div class="sonet-log-filter-line"></div><?
	if ($arParams["SUBSCRIBE_ONLY"] == "Y"):
		if (array_key_exists("flt_show_hidden", $_REQUEST) && $_REQUEST["flt_show_hidden"] == "Y")
			$bChecked = true;
		else
			$bChecked = false;
		?><div><nobr><input type="checkbox" id="flt_show_hidden" name="flt_show_hidden" value="Y" <?=($bChecked ? "checked" : "")?>> <label for="flt_show_hidden"><?=GetMessage("SONET_C30_T_SHOW_HIDDEN")?></label></nobr></div>
		<div class="sonet-log-filter-line"></div><?
	endif;
	?><div class="sonet-log-filter-submit"><input type="submit" name="log_filter_submit" value="<?=GetMessage("SONET_C30_T_SUBMIT")?>"></div>
	<input type="hidden" name="skip_subscribe" value="<?=($_REQUEST["skip_subscribe"] == "Y" ? "Y" : "N")?>">
	</form>
</span>
<?
if (
	(
		array_key_exists("SHOW_SETTINGS_LINK", $arParams)
		&& $arParams["SHOW_SETTINGS_LINK"] == "Y"
	)
	||
	is_array($arResult["PresetFilters"])
)
{
	$this->SetViewTarget((strlen($arParams["PAGETITLE_TARGET"]) > 0 ? $arParams["PAGETITLE_TARGET"] : "pagetitle"), 50);
	?>
	<div class="sonet-log-pagetitle-block" id="preset_filters"><?
		if (is_array($arResult["PresetFilters"]))
		{
			?><a id="preset_filter_all" href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("preset_filter_id=clearall", array("preset_filter_id"))?>"  class="sonet-log-pagetitle-button<?=(!$arResult["PresetFilterActive"] ? " sonet-log-pagetitle-button-active" : "")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=GetMessage("SONET_C30_PRESET_FILTER_ALL")?><?=((intval($arResult["LOG_COUNTER"]) > 0 && $arParams["ENTITY_TYPE"] != SONET_ENTITY_GROUP) ? "<span id='sonet_log_counter_preset' class='pagetitle-but-counter'>".$arResult["LOG_COUNTER"]."</span>" : "")?></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
			foreach($arResult["PresetFilters"] as $preset_filter_id => $arPresetFilter)
			{
				?><a id="preset_filter_<?=$preset_filter_id?>" href="<?=$GLOBALS["APPLICATION"]->GetCurPageParam("preset_filter_id=".$preset_filter_id, array("preset_filter_id"))?>" class="sonet-log-pagetitle-button<?=($arResult["PresetFilterActive"] == $preset_filter_id ? " sonet-log-pagetitle-button-active" : "")?>"><span class="sonet-log-pagetitle-button-left-s"></span><span class="sonet-log-pagetitle-button-text"><?=$arPresetFilter["NAME"]?></span><span class="sonet-log-pagetitle-button-right-s"></span></a><?
			}
		}
		?><a href="javascript:void(0)" class="sonet-log-pagetitle-button-settings"  onclick="return ShowFilterPopup(this);">
			<span title="<?=GetMessage("SONET_C30_T_FILTER_TITLE")?>" class="sonet-log-pagetitle-button-set-icon"></span>
		</a>
	</div><?
	$this->EndViewTarget();
}
?>