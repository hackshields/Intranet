<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Companies");
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.company",
	"",
	Array(
		"SEF_MODE" => "Y",
		"PATH_TO_LEAD_SHOW" => "#SITE_DIR#crm/lead/show/#lead_id#/",
		"PATH_TO_LEAD_EDIT" => "#SITE_DIR#crm/lead/edit/#lead_id#/",
		"PATH_TO_LEAD_CONVERT" => "#SITE_DIR#crm/lead/convert/#lead_id#/",		
		"PATH_TO_CONTACT_SHOW" => "#SITE_DIR#crm/contact/show/#contact_id#/",
		"PATH_TO_CONTACT_EDIT" => "#SITE_DIR#crm/contact/edit/#contact_id#/",
		"PATH_TO_DEAL_SHOW" => "#SITE_DIR#crm/deal/show/#deal_id#/",
		"PATH_TO_DEAL_EDIT" => "#SITE_DIR#crm/deal/edit/#deal_id#/",
		"PATH_TO_USER_PROFILE" => "#SITE_DIR#company/personal/user/#user_id#/",
		"ELEMENT_ID" => $_REQUEST["company_id"],
		"SEF_FOLDER" => "#SITE_DIR#crm/company/",
		"SEF_URL_TEMPLATES" => Array(
			"index" => "index.php",
			"list" => "list/",
			"import" => "import/",
			"edit" => "edit/#company_id#/",
			"show" => "show/#company_id#/"
		),
		"VARIABLE_ALIASES" => Array(
			"index" => Array(),
			"list" => Array(),
			"import" => Array(),
			"edit" => Array(),
			"show" => Array(),
		)
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>