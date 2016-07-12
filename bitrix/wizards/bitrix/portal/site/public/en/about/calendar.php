<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Event Calendar");
?>

<?$APPLICATION->IncludeComponent(
	"bitrix:calendar.grid",
	"",
	Array(
		"CALENDAR_TYPE" => "company_calendar",
		"ALLOW_SUPERPOSE" => "Y",
		"ALLOW_RES_MEETING" => "Y"
	)
);?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
