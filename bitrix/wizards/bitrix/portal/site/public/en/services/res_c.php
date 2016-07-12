<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Meeting Room Booking");
?>
<p><?$APPLICATION->IncludeComponent(
	"bitrix:intranet.event_calendar",
	".default",
	Array(
		"IBLOCK_TYPE" => "events", 
		"IBLOCK_ID" => "#CALENDAR_RES_IBLOCK_ID#", 
		"INIT_DATE" => "-Show Current Date-", 
		"WEEK_HOLIDAYS" => array(0=>"5",1=>"6",), 
		"YEAR_HOLIDAYS" => "1.01, 25.12", 
		"LOAD_MODE" => "ajax", 
		"EVENT_LIST_MODE" => "N", 
		"USERS_IBLOCK_ID" => "#CALENDAR_USERS_IBLOCK_ID#", 
		"PATH_TO_USER" => "#SITE_DIR#company/personal/user/#user_id#/", 
		"PATH_TO_USER_CALENDAR" => "#SITE_DIR#company/personal/user/#user_id#/calendar/",
		"WORK_TIME_START" => "9", 
		"WORK_TIME_END" => "19", 
		"ALLOW_SUPERPOSE" => "N", 
		"RESERVE_MEETING_READONLY_MODE" => "Y",
		"REINVITE_PARAMS_LIST" => array(
			0 => "from",
			1 => "to",
			2 => "location",
		),
		"ALLOW_RES_MEETING" => "N",
		"ALLOW_VIDEO_MEETING" => "N",	
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "3600" 
	)
);?></p>

<p>Whenever you want to book a meeting room, find the vacant time in the calendar; call your manager to confirm reservation and put it on the reservation schedule.</p>

<p><a href="#SITE_DIR#services/index.php">Meeting Room Booking Table View</a><br /></p>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>