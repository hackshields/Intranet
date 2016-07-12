<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CModule::IncludeModule('crm'))
{
	$CrmPerms = new CCrmPerms($GLOBALS["USER"]->GetID());
	$arMenuCrm = Array();
	
	if (SITE_TEMPLATE_ID === "bitrix24")
		$arMenuCrm[] = Array(
			"CRM Desktop",
			"#SITE_DIR#crm/",
			Array(),
			Array(),
			""
		);
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

	$arMenuCrm[] = Array(
		"Catalog",
		"#SITE_DIR#crm/product/",
		Array(),
		Array(),
		""
	);
	
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
		if (IsModuleInstalled('report') || SITE_TEMPLATE_ID !== "bitrix24")
			$arMenuCrm[] = Array(
				"Reports",
				CModule::IncludeModule('report') ? "#SITE_DIR#crm/reports/report/" : "#SITE_DIR#crm/reports/",
				Array(),
				Array(),
				""
			);
			
		if (SITE_TEMPLATE_ID === "bitrix24")
			$arMenuCrm[] = Array(
				"Sales Funnel",
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
	$aMenuLinks = array_merge($arMenuCrm, $aMenuLinks);
}

?>