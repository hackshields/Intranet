<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (CModule::IncludeModule('crm'))
{
	$arMenuCrm = Array();

	GLOBAL $USER;
	$USER_ID = $USER->GetID();

	$cacheTtl = defined('BX_COMP_MANAGED_CACHE') ? 2592000 : 600;
	$cacheID = 'bx_crm_user_menu_'.$USER_ID.'_'.LANGUAGE_ID;
	$cacheDir = '/crm/user_top_menu';
	$obCache = new CPHPCache;

	if($obCache->InitCache($cacheTtl, $cacheID, $cacheDir))
	{
		$arMenuCrm = $obCache->GetVars();
	}
	else
	{
		global $CACHE_MANAGER;
		$CACHE_MANAGER->StartTagCache($cacheDir);

		$CrmPerms = new CCrmPerms($USER_ID);
		
		$arMenuCrm[] = Array(
			"My Activities",
			"#SITE_DIR#crm/activity/",
			Array(),
			Array(),
			""
		);
		if (!$CrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Contacts",
				"#SITE_DIR#crm/contact/",
				Array(),
				Array(),
				""
			);
		}
		if (!$CrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Companies",
				"#SITE_DIR#crm/company/",
				Array(),
				Array(),
				""
			);
		}
		if (!$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Deals",
				"#SITE_DIR#crm/deal/",
				Array(),
				Array(),
				""
			);
		}
		if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Leads",
				"#SITE_DIR#crm/lead/",
				Array(),
				Array(),
				""
			);
		}
		if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Catalog",
				"#SITE_DIR#crm/product/",
				Array(),
				Array(),
				""
			);
		}
		if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE) ||
			!$CrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Events",
				"#SITE_DIR#crm/events/",
				Array(),
				Array(),
				""
			);
		}
		if (!$CrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE) ||
			!$CrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE) || !$CrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
		{
			$arMenuCrm[] = Array(
				"Reports",
				"#SITE_DIR#crm/reports/",
				Array(),
				Array(),
				""
			);
			$arMenuCrm[] = Array(
				"Help",
				"#SITE_DIR#crm/info/",
				Array(),
				Array(),
				""
			);
			
		}
		if ($CrmPerms->IsAccessEnabled())
		{
			$arMenuCrm[] = Array(
				"Settings",
				"#SITE_DIR#crm/configs/",
				Array(),
				Array(),
				""
			);
		}

		$CACHE_MANAGER->RegisterTag('crm_change_role');
		$CACHE_MANAGER->RegisterTag('USER_CARD_'.intval($USER_ID / 100));
		$CACHE_MANAGER->EndTagCache();

		if($obCache->StartDataCache())
		{
			$obCache->EndDataCache($arMenuCrm);
		}
	}

	$aMenuLinks = array_merge($arMenuCrm, $aMenuLinks);
	unset($arMenuCrm);
}
?>