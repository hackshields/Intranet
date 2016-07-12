<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Meeting Room Booking");
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.reserve_meeting",
	".default",
	array(
		"IBLOCK_TYPE" => "events",
		"IBLOCK_ID" => "#CALENDAR_RES_IBLOCK_ID#",
		"USERGROUPS_MODIFY" => array(),
		"USERGROUPS_RESERVE" => array(),
		"USERGROUPS_CLEAR" => array(),
		"SEF_MODE" => "N",
		"SET_NAVCHAIN" => "Y",
		"SET_TITLE" => "Y",
		"WEEK_HOLIDAYS" => array(0=>"5",1=>"6",),
	),
	false
);
?></p>

<p><a href="/services/res_c.php">Meeting Room Booking Calendar View</a><br /></p>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>