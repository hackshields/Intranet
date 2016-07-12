<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$aMenuLinks = Array(

	Array(
		"Meeting Room Booking", 
		"#SITE_DIR#services/index.php", 
		Array("#SITE_DIR#services/res_c.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('MeetingRoomBookingSystem')" 
	),
	Array(
		"Meetings and Briefings", 
		"#SITE_DIR#services/meeting/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Meeting')" 
	),
	Array(
		"Ideas", 
		"#SITE_DIR#services/idea/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Idea')" 
	),  
	Array(
		"Lists", 
		"#SITE_DIR#services/lists/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Lists')" 
	),
	Array(
		"Business Processes",
		"#SITE_DIR#services/bp/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('BizProc')" 
	),
	Array(
		"e-Orders", 
		"#SITE_DIR#services/requests/", 
		Array(), 
		Array(), 
		(!IsModuleInstalled("form"))?"false":"CBXFeatures::IsFeatureEnabled('Requests')" 
	),
	Array(
		"Training", 
		"#SITE_DIR#services/learning/", 
		Array("/services/course.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Learning')" 
	),
	Array(
		"Wiki", 
		"#SITE_DIR#services/wiki/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Wiki')" 
	),
	Array(
		"FAQ", 
		"#SITE_DIR#services/faq/", 
		Array(), 
		Array(), 
		"" 
	),
	Array(
		"Polls", 
		"#SITE_DIR#services/votes.php", 
		Array("#SITE_DIR#services/vote_new.php", "#SITE_DIR#services/vote_result.php"), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Vote')" 
	),
	Array(
		"Technical Support", 
		"#SITE_DIR#services/support.php?show_wizard=Y", 
		Array("#SITE_DIR#services/support.php"), 
		Array(), 
		(!IsModuleInstalled("support"))?"false":"CBXFeatures::IsFeatureEnabled('Support')"
	),
	Array(
		"Link Directory", 
		"#SITE_DIR#services/links.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('WebLink')" 
	),
	Array(
		"Subscription", 
		"#SITE_DIR#services/subscr_edit.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Subscribe')" 
	),
	Array(
		"Video Conferencing", 
		"#SITE_DIR#services/video/", 
		Array(), 
		Array(), 
		(!IsModuleInstalled("video"))?"false":"CBXFeatures::IsFeatureEnabled('VideoConference')" 
	),
	Array(
		"Change Log", 
		"#SITE_DIR#services/event_list.php", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('EventList')" 
	),
	Array(
		"Classifieds", 
		"#SITE_DIR#services/board/", 
		Array(), 
		Array(), 
		"CBXFeatures::IsFeatureEnabled('Board')" 
	),
);
?>