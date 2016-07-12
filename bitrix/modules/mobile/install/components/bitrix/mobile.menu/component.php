<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arResult = Array();
$USER_ID = $USER->GetID();

$arResult["USER_FULL_NAME"] = CUser::FormatName("#NAME# #LAST_NAME#", array(
	"NAME"	 => $USER->GetFirstName(),
	"LAST_NAME" 	=> $USER->GetLastName(),
	"SECOND_NAME" => 	$USER->GetSecondName(),
	"LOGIN" => $USER->GetLogin()
));

$arResult["USER"] = $USER->GetByID($USER_ID)->GetNext();

$arResult["USER"]["AVATAR"] = false;
if ($arResult["USER"]["PERSONAL_PHOTO"])
{
	$imageFile = CFile::GetFileArray($arResult["USER"]["PERSONAL_PHOTO"]);
	if ($imageFile !== false)
		$arResult["USER"]["AVATAR"] = CFile::ResizeImageGet($imageFile, array("width" => 58, "height" => 58), BX_RESIZE_IMAGE_EXACT, false);
}

$aMenuLinksAdd = array();
if(defined("BX_COMP_MANAGED_CACHE"))
	$ttl = 2592000;
else
	$ttl = 600;

$extEnabled = false;
if (IsModuleInstalled('extranet'))
	$extEnabled = true;

$cache_id = 'bx_user_mobile_menu_'.$USER_ID.'_'.$extEnabled.'_'.LANGUAGE_ID;
$cache_dir = '/bx/user_mobile_menu';
$obCache = new CPHPCache;

if($obCache->InitCache($ttl, $cache_id, $cache_dir))
{
	$aMenuLinksAdd = $obCache->GetVars();
}
else
{
	$arSGGroup = array();
	$arExtSGGroup = array();

	global $CACHE_MANAGER;
	$CACHE_MANAGER->StartTagCache($cache_dir);

	if (CModule::IncludeModule("socialnetwork"))
	{
		$strGroupSubjectLinkTemplate = "/mobile/log/?group_id=#group_id#";

		/*$arExtSGGroup[] = array(
			GetMessage("MENU_GROUPS_EXTRANET"),
			"/workgroups/",
			Array(),
			Array("class" => "menu-groups-extranet", "IS_PARENT" => true),
			""
		);*/

		$arGroupFilterMy = array(
			"USER_ID" => $USER_ID,
			"<=ROLE" => SONET_ROLES_USER,
			"GROUP_ACTIVE" => "Y",
			"!GROUP_CLOSED" => "Y",
		);

		$extGroupID = array();

		// Extranet group
		if (CModule::IncludeModule("extranet"))
		{
			$arGroupFilterMy["GROUP_SITE_ID"] = CExtranet::GetExtranetSiteID();
			$dbGroups = CSocNetUserToGroup::GetList(
				array("GROUP_NAME" => "ASC"),
				$arGroupFilterMy,
				false,
				false,
				array('ID', 'GROUP_ID', 'GROUP_NAME', 'GROUP_SITE_ID')
			);

			while ($arGroups = $dbGroups->GetNext())
			{
				$arExtSGGroup[] = array(
					$arGroups["GROUP_NAME"],
					str_replace("#group_id#", $arGroups["GROUP_ID"], $strGroupSubjectLinkTemplate),
					array(),
					array("counter_id" => "SG".$arGroups["GROUP_ID"]),
					""
				);

				$extGroupID[] = $arGroups["ID"];
				//$CACHE_MANAGER->RegisterTag('sonet_group_'.$arGroups["ID"]);
			}
		}
		/*if (count($arExtSGGroup) < 2)
			$arExtSGGroup = array();*/

		// Socialnetwork
		$arGroupFilterMy["GROUP_SITE_ID"] = SITE_ID;
		$dbGroups = CSocNetUserToGroup::GetList(
			array("GROUP_NAME" => "ASC"),
			$arGroupFilterMy,
			false,
			false,
			array('ID', 'GROUP_ID', 'GROUP_NAME', 'GROUP_SITE_ID')
		);

		while ($arGroups = $dbGroups->GetNext())
		{
			if(in_array($arGroups['ID'], $extGroupID))
				continue;

			$arSGGroup[] = array(
				$arGroups["GROUP_NAME"],
				str_replace("#group_id#", $arGroups["GROUP_ID"], $strGroupSubjectLinkTemplate),
				array(),
				array("counter_id" => "SG".$arGroups["GROUP_ID"]),
				""
			);
			//$CACHE_MANAGER->RegisterTag('sonet_group_'.$arGroups["ID"]);
		}

		/*$arSGGroup[] = array(
			GetMessage("MENU_ALL_GROUPS"),
			"/workgroups/group/search/",
			Array(),
			Array(),
			""
		);*/
	}

	$CACHE_MANAGER->RegisterTag('sonet_group');
	$CACHE_MANAGER->RegisterTag('USER_CARD_'.intval($USER_ID/100));
	$CACHE_MANAGER->RegisterTag('sonet_user2group_U'.$USER_ID);
	$CACHE_MANAGER->EndTagCache();

	$aMenuLinksAdd = Array(
		"GROUP_MENU" => $arSGGroup,
		"EXTRANET_MENU" => $arExtSGGroup
	);

	if($obCache->StartDataCache())
	{
		$obCache->EndDataCache($aMenuLinksAdd);
		unset($arSGGroup, $arExtSGGroup);
	}
}

unset($obCache);
$arResult["GROUP_MENU"] = $aMenuLinksAdd["GROUP_MENU"];
$arResult["EXTRANET_MENU"] = $aMenuLinksAdd["EXTRANET_MENU"];
$this->IncludeComponentTemplate();
?>