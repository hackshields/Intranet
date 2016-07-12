<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (isset($_GET["RELOAD"]) && $_GET["RELOAD"] == "Y")
	return; //Live Feed Ajax
else if (isset($_GET["IFRAME"]) && $_GET["IFRAME"] == "Y" && !isset($_GET["SONET"]))
{
	//For the task iframe popup
	$APPLICATION->SetPageProperty("BodyClass", "task-iframe-popup");
	$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/interface.css", true);
	$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/bitrix24.js", true);
	return;
}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<?IncludeTemplateLangFile(__FILE__);?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?=LANGUAGE_ID?>" lang="<?=LANGUAGE_ID?>">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge" />
<?if (IsModuleInstalled("bitrix24")):?>
<meta name="apple-itunes-app" content="app-id=561683423" />
<?endif;

$APPLICATION->ShowHead();
$APPLICATION->AddHeadString('<!--[if IE 7]><link rel="stylesheet" type="text/css" href="'.SITE_TEMPLATE_PATH.'/styleIE7.css" /><![endif]-->', false, true);
$APPLICATION->AddHeadString('<!--[if IE 8]><link rel="stylesheet" type="text/css" href="'.SITE_TEMPLATE_PATH.'/styleIE8.css" /><![endif]-->', false, true);

$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH."/interface.css", true);
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH."/bitrix24.js", true);


?><title><?$APPLICATION->ShowTitle()?></title>
</head>
<body class="<?$APPLICATION->ShowProperty("BodyClass");?><?if (IsModuleInstalled("bitrix24")):?> bitrix24<?endif?>">

<?$APPLICATION->ShowViewContent("im");?>

<?
if (!IsModuleInstalled("bitrix24") || $USER->IsAdmin())
	$APPLICATION->ShowPanel();

function BX24ShowPanel()
{
	if ($GLOBALS["APPLICATION"]->PanelShowed !== true)
		return "";
	$userOptions = CUserOptions::GetOption("admin_panel", "settings");
	return ' style="top:'.($userOptions["collapsed"] == "on" ? "39" : "147").'px"';
	//see bitrix24.js for javascript implementation
}
?>

<div id="header"<?$APPLICATION->AddBufferContent("BX24ShowPanel");?>>
	<div id="header-inner">
<?
		if(
			!CModule::IncludeModule("extranet")
			|| CExtranet::GetExtranetSiteID() != SITE_ID
		):
			if(
				!IsModuleInstalled("timeman")
				|| !$APPLICATION->IncludeComponent('bitrix:timeman', 'bitrix24', array(), false, array("HIDE_ICONS" => "Y" ))
			)
			{
				$APPLICATION->IncludeComponent('bitrix:planner', 'bitrix24', array(), false, array("HIDE_ICONS" => "Y" ));
			}
?>
		<?else:?>
			<?CJSCore::Init("timer");?>
			<div class="timeman-wrap timeman-simple">
				<span id="timeman-block" class="timeman-block">
					<span class="time" id="timeman-timer"><script type="text/javascript">document.write(B24Timemanager.formatCurrentTime(new Date().getHours(), new Date().getMinutes()))</script></span>
				</span>
			</div>
			<script type="text/javascript">BX.ready(function() {
				BX.timer.registerFormat("bitrix24_time", B24Timemanager.formatCurrentTime);
				BX.timer({
					container: BX("timeman-timer"),
					display : "bitrix24_time"
				});
			});</script>
		<?endif?>

		<div class="header-logo-block">
			<a href="<?=SITE_DIR?>" title="<?=GetMessage("BITRIX24_LOGO_TOOLTIP")?>" class="logo"><span class="logo-text"><?if (IsModuleInstalled("bitrix24")) echo htmlspecialcharsbx(COption::GetOptionString("bitrix24", "site_title", "")); else echo htmlspecialcharsbx(COption::GetOptionString("main", "site_name", ""));?></span><?if(COption::GetOptionString("bitrix24", "logo24show", "Y") !=="N"):?><span class="logo-color">24</span><?endif?></a><?if (CModule::IncludeModule("im") && CBXFeatures::IsFeatureEnabled('WebMessenger')) $APPLICATION->IncludeComponent("bitrix:im.messenger", "", Array(), false, Array("HIDE_ICONS" => "Y"));?>
		</div>

		<?$APPLICATION->IncludeComponent("bitrix:search.title", ".default", Array(
			"NUM_CATEGORIES" => "5",
			"TOP_COUNT" => "5",
			"CHECK_DATES" => "N",
			"SHOW_OTHERS" => "Y",
			"PAGE" => "#SITE_DIR#search/index.php",
			"CATEGORY_0_TITLE" => GetMessage("BITRIX24_SEARCH_EMPLOYEE"),
			"CATEGORY_0" => array(
				0 => "intranet",
			),
			"CATEGORY_1_TITLE" => GetMessage("BITRIX24_SEARCH_DOCUMENT"),
			"CATEGORY_1" => array(
				0 => "iblock_library",
			),
			"CATEGORY_1_iblock_library" => array(
				0 => "all",
			),
			"CATEGORY_2_TITLE" => GetMessage("BITRIX24_SEARCH_GROUP"),
			"CATEGORY_2" => array(
				0 => "socialnetwork",
			),
			"CATEGORY_2_socialnetwork" => array(
				0 => "all",
			),
			"CATEGORY_3_TITLE" => GetMessage("BITRIX24_SEARCH_MICROBLOG"),
			"CATEGORY_3" => array(
				0 => "microblog", 1 => "blog",
			),
			"CATEGORY_4_TITLE" => "CRM",
			"CATEGORY_4" => array(
				0 => "crm",
			),
			"CATEGORY_OTHERS_TITLE" => GetMessage("BITRIX24_SEARCH_OTHER"),
			"SHOW_INPUT" => "N",
			"INPUT_ID" => "search-textbox-input",
			"CONTAINER_ID" => "search",
			),
			false
		);?>


		<?
		$profile_link = SITE_DIR."company/personal";
		if (CModule::IncludeModule("extranet") && SITE_ID == CExtranet::GetExtranetSiteID())
			$profile_link = SITE_DIR."contacts/personal";
		$APPLICATION->IncludeComponent("bitrix:system.auth.form", "", array(
			"REGISTER_URL" => SITE_DIR."auth/",
			"PATH_TO_MYPORTAL" => SITE_DIR."desktop.php",
			"PATH_TO_SONET_PROFILE" => $profile_link."/user/#user_id#/",
			"PATH_TO_SONET_PROFILE_EDIT" => $profile_link."/user/#user_id#/edit/",
			"PATH_TO_SONET_MESSAGES" => $profile_link."/messages/",
			"PATH_TO_SONET_MESSAGE_FORM"	=>	$profile_link."/messages/form/#user_id#/",
			"PATH_TO_SONET_MESSAGE_FORM_MESS"	=>	$profile_link."/messages/form/#user_id#/#message_id#/",
			"PATH_TO_SONET_MESSAGES_CHAT"	=>	$profile_link."/messages/chat/#user_id#/",
			"PATH_TO_SONET_LOG" => $profile_link."/log/",
			"PATH_TO_SONET_GROUPS" => $profile_link."/user/#user_id#/groups/",
			"PATH_TO_CALENDAR" => $profile_link."/user/#user_id#/calendar/",
			"PATH_TO_TASKS" => $profile_link."/user/#user_id#/tasks/",
			"PATH_TO_PHOTO" => $profile_link."/user/#user_id#/photo/",
			"PATH_TO_BLOG" => $profile_link."/user/#user_id#/blog/",
			"PATH_TO_MICROBLOG" => $profile_link."/user/#user_id#/microblog/",
			"PATH_TO_FILES" => $profile_link."/user/#user_id#/files/lib/",
			),
			false
		);?>
	</div>
</div>
<div class="page-wrapper">
	<div id="page-inner">

		<div id="menu">

			<?
			if (CModule::IncludeModule('tasks') && CBXFeatures::IsFeatureEnabled('Tasks')):
				$APPLICATION->IncludeComponent(
					"bitrix:tasks.iframe.popup",
					".default",
					array(
						"ON_TASK_ADDED" => "#SHOW_ADDED_TASK_DETAIL#",
						"ON_TASK_CHANGED" => "BX.DoNothing",
						"ON_TASK_DELETED" => "BX.DoNothing"
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			endif;

			if (!(CModule::IncludeModule('extranet') && SITE_ID === CExtranet::GetExtranetSiteID()))
			{
				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.group.iframe.popup",
					".default",
					array(
						"PATH_TO_GROUP" => "/workgroups/group/#group_id#/",
						"PATH_TO_GROUP_CREATE" => "/company/personal/user/".$USER->GetID()."/groups/create/",
						"IFRAME_POPUP_VAR_NAME" => "groupCreatePopup",
						"ON_GROUP_ADDED" => "BX.DoNothing",
						"ON_GROUP_CHANGED" => "BX.DoNothing",
						"ON_GROUP_DELETED" => "BX.DoNothing"
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			}
			?>

			<?if ($USER->IsAuthorized() && (CBXFeatures::IsFeatureEnabled('Calendar') || CBXFeatures::IsFeatureEnabled('Workgroups') || CBXFeatures::IsFeatureEnabled('PersonalFiles') || CBXFeatures::IsFeatureEnabled('PersonalPhoto'))):?>
			<div class="menu-create-but" onclick="BX.addClass(this, 'menu-create-but-active');BX.PopupMenu.show('create-menu', this, [
				<?if(CModule::IncludeModule('bitrix24') && $USER->CanDoOperation('bitrix24_invite')):?>
				{ text : '<?=GetMessage("BITRIX24_INVITE")?>', className : 'invite-employee', onclick : function() { this.popupWindow.close(); <?=CBitrix24InviteDialog::ShowInviteDialogLink()?>} },
				<?endif?>
				<?if(CBXFeatures::IsFeatureEnabled('Tasks')):?>
				{ text : '<?=GetMessage("BITRIX24_TASK_CREATE")?>', className : 'create-task', onclick : function() { this.popupWindow.close(); BX.Tasks.lwPopup.showCreateForm(); }},
				<?endif?>
				<?if (!(CModule::IncludeModule('extranet') && SITE_ID === CExtranet::GetExtranetSiteID())):?>
					<?if (CBXFeatures::IsFeatureEnabled('Calendar')):?>
				{ text : '<?=GetMessage("BITRIX24_EVENT_CREATE")?>', className : 'create-event', href : '/company/personal/user/<?=$USER->GetID()?>/calendar/?EVENT_ID=NEW'},
					<?endif?>
				{ text : '<?=GetMessage("BITRIX24_BLOG_CREATE")?>', className : 'create-write-blog', href : '/company/personal/user/<?=$USER->GetID()?>/blog/edit/new/'},
					<?if (CBXFeatures::IsFeatureEnabled('Workgroups') && CModule::IncludeModule('socialnetwork') && (CSocNetUser::IsCurrentUserModuleAdmin() || $GLOBALS["APPLICATION"]->GetGroupRight("socialnetwork", false, "Y", "Y", array(SITE_ID, false)) >= "K")):?>
				{ text : '<?=GetMessage("BITRIX24_GROUP_CREATE")?>', className : 'create-group', onclick : function() {this.popupWindow.close(); groupCreatePopup.Add(); } },
					<?endif?>
					<?if (CBXFeatures::IsFeatureEnabled('PersonalFiles')):?>
				{ text : '<?=GetMessage("BITRIX24_FILE_CREATE")?>', className : 'create-download-files', href : '/company/personal/user/<?=$USER->GetID()?>/files/lib/?file_upload=Y' },
					<?endif?>
					<?if (CBXFeatures::IsFeatureEnabled('PersonalPhoto')):?>
				{ text : '<?=GetMessage("BITRIX24_PHOTO_CREATE")?>', className : 'create-download-photo', href : '/company/personal/user/<?=$USER->GetID()?>/photo/photo/0/action/upload/'}
					<?endif?>
				<?else:?>
				{ text : '<?=GetMessage("BITRIX24_BLOG_CREATE")?>', className : 'create-write-blog', href : '/extranet/contacts/personal/user/<?=$USER->GetID()?>/blog/edit/new/'},
					<?if (CBXFeatures::IsFeatureEnabled('PersonalFiles')):?>
				{ text : '<?=GetMessage("BITRIX24_FILE_CREATE")?>', className : 'create-download-files', href : '/extranet/contacts/personal/user/<?=$USER->GetID()?>/files/lib/?file_upload=Y' },
					<?endif?>
				<?endif;?>
				],
				{
					offsetLeft: 47,
					offsetTop: 10,
					angle : true,

					events : {
						onPopupClose : function(popupWindow)
						{
							BX.removeClass(this.bindElement, 'menu-create-but-active');
						}
					}
				})"><?=GetMessage("BITRIX24_CREATE")?></div>
				<?endif;?>

			<?if (IsModuleInstalled("bitrix24")) :?>
				<?$APPLICATION->IncludeComponent("bitrix:menu", "vertical_multilevel", array(
						"ROOT_MENU_TYPE" => "superleft",
						"MENU_CACHE_TYPE" => "Y",
						"MENU_CACHE_TIME" => "604800",
						"MENU_CACHE_USE_GROUPS" => "N",
						"MENU_CACHE_USE_USERS" => "Y",
						"CACHE_SELECTED_ITEMS" => "N",
						"MENU_CACHE_GET_VARS" => array(),
						"MAX_LEVEL" => "1",
						"CHILD_MENU_TYPE" => "superleft",
						"USE_EXT" => "Y",
						"DELAY" => "N",
						"ALLOW_MULTI_SELECT" => "N"
					),
					false
				);?>
			<?else:?>
				<?$APPLICATION->IncludeComponent("bitrix:menu", "vertical_multilevel", array(
						"ROOT_MENU_TYPE" => "top",
						"MENU_CACHE_TYPE" => "Y",
						"MENU_CACHE_TIME" => "604800",
						"MENU_CACHE_USE_GROUPS" => "N",
						"MENU_CACHE_USE_USERS" => "Y",
						"CACHE_SELECTED_ITEMS" => "N",
						"MENU_CACHE_GET_VARS" => array(),
						"MAX_LEVEL" => "2",
						"CHILD_MENU_TYPE" => "left",
						"USE_EXT" => "Y",
						"DELAY" => "N",
						"ALLOW_MULTI_SELECT" => "N"
					),
					false
				);?>
			<?endif;?>
		</div>

		<?if($APPLICATION->GetCurPage(true) == SITE_DIR."index.php"):?>
		<div id="sidebar">
			<?$APPLICATION->SetPageProperty("BodyClass", "start-page");?>
			<?$APPLICATION->ShowViewContent("sidebar")?>
			<?$APPLICATION->ShowViewContent("sidebar_tools_1")?>
			<?$APPLICATION->ShowViewContent("sidebar_tools_2")?>
		</div>
		<?endif?>

		<div class="pagetitle-wrap">
			<h1 class="pagetitle" id="pagetitle"><?$APPLICATION->ShowTitle(false);?></h1>
			<div class="pagetitle-menu" id="pagetitle-menu"><?$APPLICATION->ShowViewContent("pagetitle")?></div>
			<div class="pagetitle-content-topEnd">
				<div class="pagetitle-content-topEnd-corn"></div>
			</div>
		</div>
		<div id="workarea">
			<?if($APPLICATION->GetCurPage(true) != SITE_DIR."index.php" && $APPLICATION->GetProperty("HIDE_SIDEBAR", "N") != "Y"):
			?><div id="sidebar"><?
				if (IsModuleInstalled("bitrix24")):
					$GLOBALS['INTRANET_TOOLBAR']->Disable();
				else:
					$GLOBALS['INTRANET_TOOLBAR']->Enable();
					$GLOBALS['INTRANET_TOOLBAR']->Show();
				endif;

				$APPLICATION->ShowViewContent("sidebar");
				$APPLICATION->ShowViewContent("sidebar_tools_1");
				$APPLICATION->ShowViewContent("sidebar_tools_2");
			?></div>
			<?endif?>
			<div id="workarea-content">
			<?$APPLICATION->ShowViewContent("topblock")?>
			<?CPageOption::SetOptionString("main.interface", "use_themes", "N"); //For grids?>
