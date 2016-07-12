<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (CModule::IncludeModule('crm'))
{
	GLOBAL $USER;
	$USER_ID = $USER->GetID();
	$CrmPerms = new CCrmPerms($USER_ID);
	$aMenuLinksExt = array();
	
	if (!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_NONE))
	{
		$aMenuLinksExt = array(
			Array(
				"Selection Lists",
				"#SITE_DIR#crm/configs/status/",
				Array(),
				Array(),
				""
			),
			Array(
				"Currencies",
				"#SITE_DIR#crm/configs/currency/",
				Array(),
				Array(),
				""
			),		
			Array(
				"Access Permissions",
				"#SITE_DIR#crm/configs/perms/",
				Array(),
				Array(),
				""
			),
			Array(
				"Business processes",
				"#SITE_DIR#crm/configs/bp/",
				Array(),
				Array(),
				"CModule::IncludeModule('bizproc') && CModule::IncludeModule('bizprocdesigner')"
			),
			Array(
				"Custom Fields",
				"#SITE_DIR#crm/configs/fields/",
				Array(),
				Array(),
				""
			),
			Array(
				"Email Settings",
				"#SITE_DIR#crm/configs/config/",
				Array(),
				Array(),
				"CModule::IncludeModule('subscribe')"
			),
			Array(
				"Send&Save Integration",
				"#SITE_DIR#crm/configs/sendsave/",
				Array(),
				Array(),
				"CModule::IncludeModule('mail')"
			),
			Array(
				"e-Stores",
				"#SITE_DIR#crm/configs/external_sale/",
				Array(),
				Array(),
				""
			)
		);
	}
	
	if ($CrmPerms->IsAccessEnabled())
	{
		$aMenuLinksExt[] = Array(
			"E-Mail Templates",
			"#SITE_DIR#crm/configs/mailtemplate/",
			Array(),
			Array(),
			""
		);		
	}

	$aMenuLinks = array_merge($aMenuLinks, $aMenuLinksExt);
}

?>