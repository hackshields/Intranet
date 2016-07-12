<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CUtil::InitJSCore(array("popup"));

if ($USER->IsAuthorized() && $arResult["User"]["ID"] == $USER->GetID() && $arParams["PAGE_ID"] != "user")
	return;

$this->SetViewTarget("topblock", 100);
?>

<div class="profile-menu">
	<div class="profile-menu-background"></div>
	<a href="<?=$arResult["Urls"]["main"]?>" class="profile-menu-avatar<?if (!array_key_exists("IS_ONLINE", $arResult) ||! $arResult["IS_ONLINE"]):?> profile-menu-avatar-offline<?endif?>"<?if (strlen($arResult["User"]["PersonalPhotoFile"]["src"]) > 0):?> style="background:url('<?=$arResult["User"]["PersonalPhotoFile"]["src"]?>') no-repeat center center; <?endif;?>"><i></i></a>
	<div class="profile-menu-info">
		<a href="<?=$arResult["Urls"]["main"]?>" class="profile-menu-name"><?=$arResult["User"]["NAME_FORMATTED"]?></a><?if (array_key_exists("IS_ABSENT", $arResult) && $arResult["IS_ABSENT"]):?><span class="profile-menu-status"><?=GetMessage("SONET_UM_ABSENT")?></span><?endif;?><span class="profile-menu-user-menu" onclick="openProfileMenuPopup(this);"></span><?if(strlen($arResult["User"]["WORK_POSITION"]) > 0):?><span class="profile-menu-description"><?=$arResult["User"]["WORK_POSITION"]?></span><?endif?><?if(array_key_exists("IS_BIRTHDAY", $arResult) && $arResult["IS_BIRTHDAY"]):?><span
		class="profile-menu-birthday-icon" title="<?=GetMessage("SONET_UM_BIRTHDAY")?>"></span><?endif?><?if(array_key_exists("IS_HONOURED", $arResult) && $arResult["IS_HONOURED"]):?><span class="profile-menu-leaderboard-icon" title="<?=GetMessage("SONET_UM_HONOUR")?>"></span><?endif?>
	</div>
	<div id="profile-menu-filter" class="profile-menu-filter"><?
		?><a href="<?=$arResult["Urls"]["Main"]?>" class="filter-but-wrap<?if ($arParams["PAGE_ID"] == "user"):?> filter-but-act<?endif?>"><span class="filter-but-left"></span><span class="filter-but-text-block"><?=GetMessage("SONET_UM_GENERAL")?></span><span class="filter-but-right"></span></a><?
		if (is_array($arResult["CanView"]))
		{
			foreach ($arResult["CanView"] as $key => $val)
			{
				if (!$val)
					continue;
				?><a href="<?=$arResult["Urls"][$key]?>" class="filter-but-wrap<?if ($arParams["PAGE_ID"] == "user_".$key):?> filter-but-act<?endif?>"><span class="filter-but-left"></span><span class="filter-but-text-block"><?=$arResult["Title"][$key]?></span><span class="filter-but-right"></span></a><?
			}
		}
		?>
	</div>
</div>

<script type="text/javascript">
function openProfileMenuPopup(bindElement)
{
	BX.addClass(bindElement, "profile-menu-user-active");

	var menu = [];

	<?if ($arResult["CAN_MESSAGE"]):?>
		<? if ($arResult["User"]["ACTIVE"] != "N"):?>
		menu.push({ text : "<?=GetMessage("SONET_UM_SEND_MESSAGE")?>", className : "profile-menu-message", onclick : function() { this.popupWindow.close(); BXIM.openMessenger(<?=$arResult["User"]["ID"]?>);} });
		<?endif?>
		menu.push({ text : "<?=GetMessage("SONET_UM_MESSAGE_HISTORY")?>", className : "profile-menu-history", onclick : function() { this.popupWindow.close(); BXIM.openHistory(<?=$arResult["User"]["ID"]?>);} });
	<?endif?>
	<?if ($arResult["CurrentUserPerms"]["Operations"]["modifyuser"]):?>
	<?if ($arResult["CurrentUserPerms"]["Operations"]["modifyuser_main"]):?>
	menu.push({ text : "<?=GetMessage("SONET_UM_EDIT_PROFILE")?>", title: "<?=GetMessage("SONET_UM_EDIT_PROFILE")?>", className : "profile-menu-profiledit", href: "<?=CUtil::JSUrlEscape($arResult["Urls"]["Edit"])?>"});
	menu.push({ text : "<?=GetMessage("SONET_UM_REQUESTS")?>", title: "<?=GetMessage("SONET_UM_REQUESTS")?>", className : "profile-menu-requests", href: "<?=CUtil::JSUrlEscape($arResult["Urls"]["UserRequests"])?>"});
	<?endif;?>
	<?endif;?>
	<?if ($arResult["CurrentUserPerms"]["IsCurrentUser"] || $arResult["CurrentUserPerms"]["Operations"]["viewprofile"]):?>
	menu.push({ text : "<?=GetMessage("SONET_UM_SUBSCRIBE")?>", title: "<?=GetMessage("SONET_UM_SUBSCRIBE")?>", className : "profile-menu-subscribe", href: "<?=CUtil::JSUrlEscape($arResult["Urls"]["Subscribe"])?>"});
	<?endif;?>
	<?if ($arResult["CurrentUserPerms"]["Operations"]["videocall"] && $arParams['PATH_TO_VIDEO_CALL']):?>
	menu.push({ text : "<?=GetMessage("SONET_UM_VIDEO_CALL")?>", className : "profile-menu-videocall", onclick : function() {window.open('<?echo $arResult["Urls"]["VideoCall"] ?>', '', 'status=no,scrollbars=yes,resizable=yes,width=1000,height=600,top='+Math.floor((screen.height - 600)/2-14)+',left='+Math.floor((screen.width - 1000)/2-5)); return false;}});
	<?endif?>

	BX.PopupMenu.show("user-menu-profile", bindElement, menu, {
		offsetTop: 5,
		offsetLeft : 12,
		angle : true,
		events : {
			onPopupClose : function() {
				BX.removeClass(this.bindElement, "profile-menu-user-active");
			}
		}
	});
}


</script>
<?$this->EndViewTarget();?>