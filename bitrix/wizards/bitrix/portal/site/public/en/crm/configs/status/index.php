<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Selection Lists");
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.config.status",
	"",
	Array(
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>