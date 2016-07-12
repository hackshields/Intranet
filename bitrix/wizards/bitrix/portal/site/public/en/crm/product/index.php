<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
global $APPLICATION;
$APPLICATION->SetTitle("Catalog");
$APPLICATION->IncludeComponent(
	"bitrix:crm.product", 
	".default", 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/product/"
	),
	false
);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
