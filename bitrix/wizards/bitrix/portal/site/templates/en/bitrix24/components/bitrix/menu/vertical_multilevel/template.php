<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<? CJSCore::Init("fx");?>
<script type="text/javascript">

function toggleMenuBlock(menu_item)
{
		var menu_block = BX.findChild(menu_item.parentNode, {tagName:'ul'}, false, false);
		
		if (menu_block.style.display == "none") 
			return;

		var menu_itemsList = BX.findChildren(menu_block, {tagName : "li"}, false);
		if (!menu_itemsList)
			return;

		var max_height =  menu_itemsList.length * 33;
		var toggleText = BX.findChild(menu_item, {className:'menu-toggle-text'}, true, false);
		if (!toggleText)
			return;

		var ieVersion = 0;
		/*@cc_on
		@if (@_jscript_version == 10)
			ieVersion = 10;
		@elif (@_jscript_version == 9)
			ieVersion = 9;
		@elif (@_jscript_version == 5.8)
			ieVersion = 8;
		@elif (@_jscript_version == 5.7)
			ieVersion = 7;
		@end
		@*/

		if (BX.hasClass(menu_block, "menu-items-close"))
		{
			var toggleOpen = new BX.fx({
				start:{opacity:0, height:0},
				finish:{opacity:100, height:max_height},
				time:0.3,
				callback: function(state){

					if (ieVersion == 7 || ieVersion == 8)
						menu_block.style.filter = 'alpha(opacity='+state.opacity+')';
					else
						menu_block.style.opacity = state.opacity/100;

					menu_block.style.height = state.height + 'px';
				},
				callback_complete:function() {
					menu_block.style.filter = '';
				}
			});

			toggleOpen.start();
			BX.removeClass(menu_block, "menu-items-close");

			BX.userOptions.save("bitrix24", menu_item.id, "hide", "N");
			toggleText.innerHTML = "<?=GetMessage("MENU_HIDE")?>";
		}
		else if (parseInt(BX.style(menu_block, 'height')) > 0 || BX.style(menu_block, 'height') == 'auto')
		{
			var toggleClose = new BX.fx({
				start:{opacity:100, height:max_height},
				finish:{opacity:0, height:0},
				time:0.3,
				callback: function(state) {

					if (!BX.browser.IsIE9())
						menu_block.style.filter = 'alpha(opacity='+state.opacity+')';
					else
						menu_block.style.opacity =  state.opacity/100;

					menu_block.style.height = state.height + "px";
				},
				callback_complete:function(){
					BX.addClass(menu_block, "menu-items-close");
					menu_block.style.filter = "";
				}
			});
			
			toggleClose.start();

			toggleText.innerHTML = "<?=GetMessage("MENU_SHOW")?>";
			BX.userOptions.save("bitrix24", menu_item.id, "hide", "Y");
		}     
}

BX.ready(function(){

	BX.addCustomEvent(window, "onImUpdateCounter", function(counters){

		if (!counters)
			return;

		for (var id in counters)
		{
			var counter = BX(id == "**" ? "menu-counter-live-feed" : "menu-counter-" + id.toLowerCase(), true);
			if (!counter)
				continue;

			if (counters[id] > 0)
			{
				counter.innerHTML = counters[id] > 50 ? "50+" : counters[id];
				BX.addClass(counter.parentNode.parentNode.parentNode.parentNode, "menu-item-with-index");

			}
			else
				BX.removeClass(counter.parentNode.parentNode.parentNode.parentNode, "menu-item-with-index");

		}
	});
});

</script>

<?
function IsSubItemSelected($begin, $ITEMS)
{
	for($i = $begin; $i < count($ITEMS); $i++)
	{
		if ($ITEMS[$i]["IS_PARENT"] || (array_key_exists("IS_PARENT", $ITEMS[$i]["PARAMS"]) && $ITEMS[$i]["PARAMS"]["IS_PARENT"] === true) )
			return false;
		if ($ITEMS[$i]["SELECTED"])
			return true;
	}
	return false;
}
?>

<?if (!empty($arResult)):

$previousLevel = 0;

foreach($arResult as $index => $arItem):
	if (IsModuleInstalled("bitrix24")) :
		if (isset($arItem["PARAMS"]["class"]))  
		{
			$arItem["DEPTH_LEVEL"] = 1;
			$arItem["IS_PARENT"] = true;
		}
		else
			$arItem["DEPTH_LEVEL"] = 2;
	endif;
?>

	<?if ($previousLevel && ($arItem["DEPTH_LEVEL"] < $previousLevel /*|| $arItem["DEPTH_LEVEL"] == $previousLevel && $arItem["IS_PARENT"]*/)):?>
		</ul></div>
	<?endif?>

	<?if ($arItem["IS_PARENT"] && $arItem["DEPTH_LEVEL"] == 1):?>
		<?
		$hideOption = CUserOptions::GetOption("bitrix24", $arItem["PARAMS"]["class"]);
		$SubItemSelected = false;
		if (!is_array($hideOption) || $hideOption["hide"] == "Y")
			$SubItemSelected = IsSubItemSelected($index+1, $arResult) ? true : false;

		if (IsModuleInstalled("bitrix24"))
			$disabled = (!is_array($hideOption) && $arItem["PARAMS"]["class"]=="menu-crm" && !$SubItemSelected) || (is_array($hideOption) && $hideOption["hide"] == "Y" && !$SubItemSelected);
		else
			$disabled = (!is_array($hideOption) && $arItem["PARAMS"]["class"]!="menu-favorites" && !$SubItemSelected) || (is_array($hideOption) && $hideOption["hide"] == "Y" && !$SubItemSelected);
		?>
		<div class="menu-items-block <?=$arItem["PARAMS"]["class"]?>">
			<?if ($arItem["DEPTH_LEVEL"] == 1):?>
				<div id="<?=$arItem["PARAMS"]["class"]?>" class="menu-items-title <?=$arItem["PARAMS"]["class"]?>"  <?if ($arItem["PARAMS"]["class"] != "menu-favorites"):?> onclick="toggleMenuBlock(this)"<?endif;?>><?
					echo $arItem["TEXT"]?><?if ($arItem["PARAMS"]["class"] != "menu-favorites"):?><span class="menu-toggle-text"><?=($disabled ? GetMessage("MENU_SHOW") : GetMessage("MENU_HIDE"))?></span><?endif;?>
				</div>
				<ul class="menu-items<?if ($disabled):?> menu-items-close<?endif;?>">
			<?endif?>
	<?elseif (!$arItem["IS_PARENT"] && $arItem["DEPTH_LEVEL"] == 1):?>
		<div class="menu-items-block <?=$arItem["PARAMS"]["class"]?>">
			<a class="menu-items-block-link"  href="<?=$arItem["LINK"]?>">
				<div  class="menu-items-title <?=$arItem["PARAMS"]["class"]?>">
					<?echo $arItem["TEXT"]?>
				</div>
			</a>
		</div>
	<?else:?>
		<?if ($arItem["PERMISSION"] > "D"):
			$couterId = "";
			$counter = 0;
			if (array_key_exists("counter_id", $arItem["PARAMS"]) && strlen($arItem["PARAMS"]["counter_id"]) > 0)
			{
				$couterId = $arItem["PARAMS"]["counter_id"] == "live-feed" ? "**" : $arItem["PARAMS"]["counter_id"];
				$counter = isset($GLOBALS["LEFT_MENU_COUNTERS"]) && array_key_exists($couterId, $GLOBALS["LEFT_MENU_COUNTERS"]) ? $GLOBALS["LEFT_MENU_COUNTERS"][$couterId] : 0;
			}

			if ($couterId == "bp_tasks" && IsModuleInstalled("bitrix24"))
			{
				$showMenuItem = CUserOptions::GetOption("bitrix24", "show_bp_in_menu", false);
				if ($showMenuItem === false && $counter > 0)
				{
					CUserOptions::SetOption("bitrix24", "show_bp_in_menu", true);
					$showMenuItem = true;
				}

				if ($showMenuItem === false)
					continue;
			}
		?>
			<li class="menu-item-block<?if ($arItem["SELECTED"]):?> menu-item-active<?endif?><?if($counter > 0 && strlen($couterId) > 0 && (!$arItem["SELECTED"] || ($arItem["SELECTED"] && $couterId == "bp_tasks"))):?> menu-item-with-index<?endif?>">
				<a class="menu-item-link" href="<?=($arItem["LINK"]=="/index.php") ? "/" : $arItem["LINK"]?>"><span class="menu-item-link-text"><?=$arItem["TEXT"]?><?
					if (strlen($couterId) > 0):
						?><span class="menu-item-index-wrap"><span class="menu-item-index" id="menu-counter-<?=strtolower($arItem["PARAMS"]["counter_id"])?>"><?=($counter > 50 ? "50+" : $counter)?></span></span><?
					endif;
				?></span></a>
			</li>
		<?endif?>

	<?endif?>

	<?$previousLevel = $arItem["DEPTH_LEVEL"];?>
<?endforeach?>

<?if ($previousLevel > 1 || $arItem["DEPTH_LEVEL"] == 1 && $arItem["IS_PARENT"])://close last item tags?>
</ul></div>
<?endif?>

<?endif?>