<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
CUtil::InitJSCore(array("popup"));

$this->SetViewTarget("topblock", 100);

$APPLICATION->IncludeComponent(
	"bitrix:socialnetwork.group.iframe.popup",
	".default",
	array(
		"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
		"PATH_TO_GROUP_EDIT" => htmlspecialcharsback($arResult["Urls"]["Edit"]).(strpos($arResult["Urls"]["Edit"], "?") === false ? "?" : "&")."tab=edit",
		"PATH_TO_GROUP_FEATURES" => htmlspecialcharsback($arResult["Urls"]["Edit"]).(strpos($arResult["Urls"]["Edit"], "?") === false ? "?" : "&")."tab=features",
		"PATH_TO_GROUP_INVITE" => htmlspecialcharsback($arResult["Urls"]["Edit"]).(strpos($arResult["Urls"]["Edit"], "?") === false ? "?" : "&")."tab=invite",
		"ON_GROUP_ADDED" => "BX.DoNothing",
		"ON_GROUP_CHANGED" => "BX.DoNothing",
		"ON_GROUP_DELETED" => "BX.DoNothing"
	),
	null,
	array("HIDE_ICONS" => "Y")
);
?>

<div class="profile-menu profile-menu-group">
	<div class="profile-menu-background"></div>
	<a href="<?=$arResult["Urls"]["View"]?>" class="profile-menu-avatar"<?if (strlen($arResult["Group"]["IMAGE_FILE"]["src"]) > 0):?> style="background:url('<?=$arResult["Group"]["IMAGE_FILE"]["src"]?>') no-repeat center center; "<?endif;?>></a>
	<div class="profile-menu-info">
		<a href="<?=$arResult["Urls"]["View"]?>" class="profile-menu-name"><?=$arResult["Group"]["NAME"]?><?=($arResult["Group"]["IS_EXTRANET"] == "Y" ? GetMessage("SONET_UM_IS_EXTRANET") : "")?></a><?if($arResult["Group"]["CLOSED"] == "Y"):?><span class="profile-menu-description"><?=GetMessage("SONET_UM_ARCHIVE_GROUP")?></span><?endif?>
	</div>
	<div id="profile-menu-filter" class="profile-menu-filter"><?
		?><a href="<?=$arResult["Urls"]["View"]?>" class="filter-but-wrap<?if ($arParams["PAGE_ID"] == "group"):?> filter-but-act<?endif?>"><span class="filter-but-left"></span><span class="filter-but-text-block"><?=GetMessage("SONET_UM_GENERAL")?></span><span class="filter-but-right"></span></a><?
		foreach ($arResult["CanView"] as $key => $val)
		{

			if (!$val || $key == "content_search")
				continue;
			?><a href="<?=$arResult["Urls"][$key]?>" class="filter-but-wrap<?if ($arParams["PAGE_ID"] == "group_".$key):?> filter-but-act<?endif?>"><span class="filter-but-left"></span><span class="filter-but-text-block"><?=$arResult["Title"][$key]?></span><span class="filter-but-right"></span></a><?
		}
	?></div>
	<div class="profile-menu-search-block">
		<form method="get" action="<?=$arResult["Urls"]["content_search"]?>"><? if(array_key_exists("content_search", $arResult["CanView"]) && $arResult["CanView"]["content_search"]):?><input id="profile-menu-search-input" name="q" class="profile-menu-search-input" onblur="closeSearchTextbox(this)" type="text" /><span id="profile-menu-search-button" title="<?=GetMessage("SONET_UM_SEARCH_BUTTON_TITLE")?>" class="profile-menu-search-button" onclick="openSearchTextbox(this)"></span><?endif?><a href="" onclick="BX.eventReturnFalse(event); openProfileMenuPopup(this);" class="webform-small-button profile-menu-search-action"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?=GetMessage("SONET_UM_ACTIONS_BUTTON");?></span><span class="webform-small-button-icon"></span><span class="webform-small-button-right"></span></a></form>
	</div>
</div>

<script type="text/javascript">
function openProfileMenuPopup(bindElement)
{
	BX.addClass(bindElement, "webform-small-button-active");

	var menu = [];

	<?if ($GLOBALS["USER"]->IsAuthorized()):
		if ($arResult["CurrentUserPerms"]["UserCanInitiate"] && !$arResult["HideArchiveLinks"]):?>
			menu.push({
				text : "<?=GetMessage("SONET_UM_INVITE")?>",
				title : "<?=GetMessage("SONET_UM_INVITE")?>",
				className : "profile-menu-invite",
				onclick : function(event) { this.popupWindow.close(); sonetGroupIFramePopup.Invite(<?=$arParams["GROUP_ID"]?>, '<?=CUtil::JSEscape($arResult["Group"]["~NAME"])?>'); }
			});<?
		endif;
	endif;?>


	<?if ($arResult["CurrentUserPerms"]["UserCanModifyGroup"]):?>
		menu.push({
			text : "<?=GetMessage("SONET_UM_EDIT")?>",
			title : "<?=GetMessage("SONET_UM_EDIT")?>",
			className : "profile-menu-groupedit",
			onclick : function(event) { this.popupWindow.close(); sonetGroupIFramePopup.Edit(<?=$arParams["GROUP_ID"]?>, '<?=CUtil::JSEscape($arResult["Group"]["~NAME"])?>')}
		});<?

		if (!$arResult["HideArchiveLinks"]):?>
			menu.push({
				text : "<?=GetMessage("SONET_UM_FEATURES")?>",
				title : "<?=GetMessage("SONET_UM_FEATURES")?>",
				className : "profile-menu-editfeatures",
				href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["Features"])?>"
			});<?
		endif;?>
			menu.push({
				text : "<?=GetMessage("SONET_UM_DELETE")?>",
				title : "<?=GetMessage("SONET_UM_DELETE")?>",
				className : "profile-menu-groupdelete",
				href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["Delete"])?>"
			});<?
	endif;

	if ($arResult["CurrentUserPerms"]["UserCanModerateGroup"] && $GLOBALS["USER"]->IsAuthorized()):?>

		menu.push({
			text : "<?=GetMessage("SONET_UM_MEMBERS")?>",
			title : "<?=GetMessage("SONET_UM_MEMBERS")?>",
			className : "profile-menu-memberedit",
			href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupUsers"])?>"
		});<?
	else:?>
		menu.push({
			text : "<?=GetMessage("SONET_UM_MEMBERS1")?>",
			title : "<?=GetMessage("SONET_UM_MEMBERS1")?>",
			className : "profile-menu-memberview",
			href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupUsers"])?>"
		});<?
	endif;

	if ($GLOBALS["USER"]->IsAuthorized()):

		if ($arResult["CurrentUserPerms"]["UserCanInitiate"] && !$arResult["HideArchiveLinks"]):
			if (!CModule::IncludeModule('extranet') || ($arResult["Group"]["OPENED"] != "Y" && !CExtranet::IsExtranetSite())):?>
				menu.push({
					text : "<?=GetMessage("SONET_UM_REQUESTS")?>",
					title : "<?=GetMessage("SONET_UM_REQUESTS")?>",
					className : "profile-menu-requests",
					href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequests"])?>"
				});<?
			else:?>
				menu.push({
					text : "<?=GetMessage("SONET_UM_REQUESTS_OUT")?>",
					title : "<?=GetMessage("SONET_UM_REQUESTS_OUT")?>",
					className : "profile-menu-requests",
					href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["GroupRequests"])?>"
				});<?
			endif;
		endif;

		if ((!$arResult["CurrentUserPerms"]["UserRole"] || ($arGadgetParams["USER_ROLE"] == SONET_ROLES_REQUEST && $arResult["CurrentUserPerms"]["InitiatedByType"] == SONET_INITIATED_BY_GROUP)) && !$arResult["HideArchiveLinks"]):?>
			menu.push({
				text : "<?=GetMessage("SONET_UM_JOIN")?>",
				title : "<?=GetMessage("SONET_UM_JOIN")?>",
				className : "profile-menu-join",
				href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["UserRequestGroup"])?>"
			});<?
		endif;

		if ($arResult["CurrentUserPerms"]["UserIsMember"] && !$arResult["CurrentUserPerms"]["UserIsOwner"]):?>
			menu.push({
				text : "<?=GetMessage("SONET_UM_LEAVE")?>",
				title : "<?=GetMessage("SONET_UM_LEAVE")?>",
				className : "profile-menu-leave",
				href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["UserLeaveGroup"])?>"
			});<?
		endif;

		if (!$arResult["HideArchiveLinks"]):?>
			menu.push({
				text : "<?=GetMessage("SONET_UM_SUBSCRIBE")?>",
				title : "<?=GetMessage("SONET_UM_SUBSCRIBE")?>",
				className : "profile-menu-subscribe",
				href : "<?=CUtil::JSUrlEscape($arResult["Urls"]["Subscribe"])?>"
			});<?
		endif;
	endif;
?>
	
	
	BX.PopupMenu.show("group-profile-menu", bindElement, menu, {
		offsetTop: 5,
		offsetLeft : 12,
		angle : true,
		events : {
			onPopupClose : function() {
				BX.removeClass(this.bindElement, "webform-small-button-active");
			}
		}
	});

}


function openSearchTextbox(button)
{
	BX.addClass(button.parentNode, "profile-menu-search-active");
	BX("profile-menu-search-input", true).focus();
}

function closeSearchTextbox(textbox)
{
	if (textbox.value == "")
		BX.removeClass(textbox.parentNode, "profile-menu-search-active");
}

</script>
<?$this->EndViewTarget();?>