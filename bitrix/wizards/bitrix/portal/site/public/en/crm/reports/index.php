<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Sales Funnel");
?><?$APPLICATION->IncludeComponent(
	"bitrix:crm.deal.funnel",
	"",
	Array(
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>