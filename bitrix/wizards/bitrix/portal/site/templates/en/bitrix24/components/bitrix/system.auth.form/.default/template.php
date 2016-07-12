<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if ($USER->IsAuthorized()):

$UserID =  $USER->GetID();
?>

<script type="text/javascript">
BX.message({	
	B24_HELP_PREV: "<?=GetMessageJS("B24_HELP_PREV")?>",
	B24_HELP_NEXT: "<?=GetMessageJS("B24_HELP_NEXT")?>",
	B24_HELP_CLASSNAME: "b24-help-popup",
	B24_HELP_TITLE: "<?=GetMessageJS("B24_HELP_TITLE")?>",
	B24_HELP_POPUP_TITLE: "<?=GetMessageJS("B24_HELP_POPUP_TITLE")?>"
});

function showHelpPopup()
{
	<?if (strlen(GetMessage("BITRIX24_HELP_VIDEO_1")) > 0 ):?>

		BX.B24VideoPopup.show(
			[
				{ title : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_TITLE_1")?>', content : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_1", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
				{ title : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_TITLE_2")?>', content : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_2", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
				{ title : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_TITLE_3")?>', content : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_3", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
				{ title : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_TITLE_4")?>', content : '<?=GetMessageJS("BITRIX24_HELP_VIDEO_4", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'}
			],
		null);

	<?else:?>

		BX.B24HelpPopup.show(
		[
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_1")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_1", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_2")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_2", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_3")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_3", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_4")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_4", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			<?if (SITE_ID != "ex"):?>
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_5")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_5", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_6")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_6", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			<?endif;?>
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_7")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_7", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_8")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_8", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_9")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_9", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'},
			{ title : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_TITLE_10")?>', content : '<?=GetMessageJS("BITRIX24_HELP_CONTENT_10", array("#SITE_TEMPLATE_PATH#" => SITE_TEMPLATE_PATH, "#LANG#" => LANGUAGE_ID))?>'}
		],
		null, { });

	<?endif?>
}		

function showUserMenu(bindElement)
{
	BX.addClass(bindElement, "user-block-active");
	BX.PopupMenu.show("user-menu", bindElement, [
		{ text : "<?=GetMessageJS("AUTH_PROFILE")?>", className : "user-menu-myPage", href : "<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SONET_PROFILE'], array("user_id" => $UserID ))?>"},
		{ text : "<?=GetMessageJS("AUTH_CHANGE_PROFILE")?>", className : "user-menu-edit-data", href : "<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_SONET_PROFILE_EDIT'], array("user_id" => $UserID ))?>"},
		{ text : "<?=GetMessageJS("AUTH_LOGOUT")?>", className : "user-menu-logOut", href : "<?$params = DeleteParam(array("logout", "login", "back_url_pub")); echo $logoutUrl = $APPLICATION->GetCurPage()."?logout=yes".htmlspecialcharsbx($params == ""? "":"&".$params);?>" }
	],
	{
		offsetTop:9,
		offsetLeft : 43,
		angle : true,
		events : {
			onPopupClose : function() {
				BX.removeClass(this.bindElement, "user-block-active");
			}
		}
	});
}

<?if (IsModuleInstalled("bitrix24") && !(CUserOptions::GetOption("bitrix24", "first_activity") == "Y")):
	CUserOptions::SetOption("bitrix24", "first_activity", "Y");
?>
BX.ready(function() { showHelpPopup(); } );
<?endif;?>

</script>

<div class="user-block" onclick="showUserMenu(this)"><div class="user-block-before"></div><span class="user-img"<?if (strlen($arResult["USER_PERSONAL_PHOTO_SRC"]) > 0):?> style="background: url('<?=$arResult["USER_PERSONAL_PHOTO_SRC"]?>') no-repeat center;"<?endif;?>></span><span class="user-name"><?=$arResult["USER_FULL_NAME"]?></span></div>
<?if (IsModuleInstalled("bitrix24")):?><div class="help-block" id="help-block" title="<?=GetMessage("AUTH_HELP")?>" onclick="showHelpPopup();"></div><?endif?>
<?else://$USER->IsAuthorized()?>

<div class="authorization-block"><a href="<?=(SITE_DIR."auth/?backurl=".$arResult["BACKURL"])?>" class="authorization-text"><?=GetMessage("AUTH_AUTH")?></a></div>

<?endif;?>