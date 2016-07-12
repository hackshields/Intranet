<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Contacts");
?> <?$APPLICATION->IncludeComponent("bitrix:socialnetwork_user", ".default", array(
	"ITEM_DETAIL_COUNT" => "32",
	"ITEM_MAIN_COUNT" => "6",
	"DATE_TIME_FORMAT" => "F j, Y H:i:s",
	"NAME_TEMPLATE" => "",
	"PATH_TO_GROUP" => "#SITE_DIR#workgroups/group/#group_id#/",
	"PATH_TO_GROUP_SUBSCRIBE" => "#SITE_DIR#workgroups/group/#group_id#/subscribe/",
	"PATH_TO_GROUP_SEARCH" => "#SITE_DIR#workgroups/group/search/",
	"PATH_TO_SEARCH_EXTERNAL" => "#SITE_DIR#contacts/",
	"PATH_TO_CONPANY_DEPARTMENT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_GROUP_TASKS" => "#SITE_DIR#workgroups/group/#group_id#/tasks/",
	"PATH_TO_GROUP_TASKS_TASK" => "#SITE_DIR#workgroups/group/#group_id#/tasks/task/#action#/#task_id#/",
	"PATH_TO_GROUP_TASKS_VIEW" => "#SITE_DIR#workgroups/group/#group_id#/tasks/view/#action#/#view_id#/",
	"PATH_TO_GROUP_POST" => "#SITE_DIR#workgroups/group/#group_id#/blog/#post_id#/",
	"PATH_TO_CONPANY_DEPARTMENT" => "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_VIDEO_CALL" => "#SITE_DIR#contacts/personal/video/#USER_ID#/",
	"SEF_MODE" => "Y",
	"SEF_FOLDER" => "#SITE_DIR#contacts/personal/",
	"AJAX_MODE" => "N",
	"AJAX_OPTION_SHADOW" => "Y",
	"AJAX_OPTION_JUMP" => "N",
	"AJAX_OPTION_STYLE" => "Y",
	"AJAX_OPTION_HISTORY" => "Y",
	"CACHE_TYPE" => "A",
	"CACHE_TIME" => "3600",
	"CACHE_TIME_LONG" => "604800",
	"PATH_TO_SMILE" => "/bitrix/images/socialnetwork/smile/",
	"PATH_TO_BLOG_SMILE" => "/bitrix/images/blog/smile/",
	"PATH_TO_FORUM_SMILE" => "/bitrix/images/forum/smile/",
	"SONET_PATH_TO_FORUM_ICON" => "/bitrix/images/forum/icon/",
	"SET_TITLE" => "Y",
	"SET_NAV_CHAIN" => "Y",
	"USER_FIELDS_MAIN" => array(
		0 => "NAME",
		1 => "SECOND_NAME",
		2 => "LAST_NAME",
		3 => "LAST_LOGIN",
		4 => "PERSONAL_COUNTRY",
		5 => "PERSONAL_CITY",
		6 => "WORK_COMPANY",
		7 => "WORK_DEPARTMENT",
		8 => "WORK_POSITION",
		9 => "WORK_WWW",
		10 => "WORK_LOGO",
	),
	"USER_PROPERTY_MAIN" => array(
		0 => "UF_DEPARTMENT",
	),
	"USER_FIELDS_CONTACT" => array(
		0 => "EMAIL",
		1 => "PERSONAL_WWW",
		2 => "PERSONAL_ICQ",
		3 => "PERSONAL_PHONE",
		4 => "PERSONAL_FAX",
		5 => "PERSONAL_MOBILE",
		6 => "WORK_PHONE",
		7 => "WORK_FAX",
	),
	"USER_PROPERTY_CONTACT" => array(
	),
	"USER_FIELDS_PERSONAL" => array(
		0 => "PERSONAL_BIRTHDAY",
		1 => "PERSONAL_GENDER",
	),
	"USER_PROPERTY_PERSONAL" => array(
	),
	"AJAX_LONG_TIMEOUT" => "60",
	"EDITABLE_FIELDS" => array(
		0 => "LOGIN",
		1 => "NAME",
		2 => "SECOND_NAME",
		3 => "LAST_NAME",
		4 => "EMAIL",
		5 => "PASSWORD",
		6 => "PERSONAL_BIRTHDAY",
		7 => "PERSONAL_WWW",
		8 => "PERSONAL_ICQ",
		9 => "PERSONAL_GENDER",
		10 => "PERSONAL_PHOTO",
		11 => "PERSONAL_PHONE",
		12 => "PERSONAL_FAX",
		13 => "PERSONAL_MOBILE",
		14 => "PERSONAL_COUNTRY",
		15 => "PERSONAL_STATE",
		16 => "PERSONAL_CITY",
		17 => "PERSONAL_ZIP",
		18 => "PERSONAL_STREET",
		19 => "PERSONAL_MAILBOX",
		20 => "WORK_COMPANY",
		21 => "WORK_DEPARTMENT",
		22 => "WORK_POSITION",
		23 => "WORK_WWW",
		24 => "WORK_PROFILE",
		25 => "WORK_LOGO",
		26 => "WORK_PHONE",
		27 => "WORK_FAX",
		28 => "WORK_COUNTRY",
		29 => "WORK_STATE",
		30 => "WORK_CITY",
		31 => "WORK_ZIP",
		32 => "WORK_STREET",
	),
	"SHOW_YEAR" => "M",
	"USER_FIELDS_SEARCH_SIMPLE" => array(
		0 => "WORK_COMPANY",
	),
	"USER_PROPERTIES_SEARCH_SIMPLE" => array(
	),
	"USER_FIELDS_SEARCH_ADV" => array(
		0 => "PERSONAL_COUNTRY",
		1 => "PERSONAL_CITY",
		2 => "WORK_COMPANY",
	),
	"USER_PROPERTIES_SEARCH_ADV" => array(
	),
	"SONET_USER_FIELDS_LIST" => array(
		0 => "PERSONAL_BIRTHDAY",
		1 => "PERSONAL_GENDER",
		2 => "PERSONAL_CITY",
	),
	"SONET_USER_PROPERTY_LIST" => array(
	),
	"SONET_USER_FIELDS_SEARCHABLE" => array(
	),
	"SONET_USER_PROPERTY_SEARCHABLE" => array(
	),
	"BLOG_GROUP_ID" => "#BLOG_GROUP_ID#",
	"FORUM_ID" => "#FORUM_ID#",
	"CALENDAR_IBLOCK_TYPE" => "events",
	"CALENDAR_USER_IBLOCK_ID" => "#CALENDAR_USER_IBLOCK_ID#",
	"CALENDAR_WEEK_HOLIDAYS" => array(
		0 => "5",
		1 => "6",
	),
	"CALENDAR_YEAR_HOLIDAYS" => "1.01, 2.01, 7.01, 23.02, 8.03, 1.05, 9.05, 12.06, 4.11, 12.12",
	"CALENDAR_WORK_TIME_START" => "9",
	"CALENDAR_WORK_TIME_END" => "19",
	"CALENDAR_ALLOW_SUPERPOSE" => "Y",
	"CALENDAR_SUPERPOSE_CAL_IDS" => array(),
	"CALENDAR_SUPERPOSE_CUR_USER_CALS" => "Y",
	"CALENDAR_SUPERPOSE_USERS_CALS" => "Y",
	"CALENDAR_SUPERPOSE_GROUPS_CALS" => "Y",
	"CALENDAR_SUPERPOSE_GROUPS_IBLOCK_ID" => "#CALENDAR_GROUP_IBLOCK_ID#",
	"CALENDAR_ALLOW_RES_MEETING" => "N",
	"CALENDAR_ALLOW_VIDEO_MEETING" => "N",

	"TASK_IBLOCK_TYPE" => "services",
	"TASK_IBLOCK_ID" => "#TASKS_IBLOCK_ID#",
	"TASKS_FIELDS_SHOW" => array(
		0 => "ID",
		1 => "NAME",
		2 => "MODIFIED_BY",
		3 => "DATE_CREATE",
		4 => "CREATED_BY",
		5 => "DATE_ACTIVE_FROM",
		6 => "DATE_ACTIVE_TO",
		7 => "IBLOCK_SECTION",
		8 => "DETAIL_TEXT",
		9 => "TASKPRIORITY",
		10 => "TASKSTATUS",
		11 => "TASKCOMPLETE",
		12 => "TASKASSIGNEDTO",
		13 => "TASKALERT",
		14 => "TASKSIZE",
		15 => "TASKSIZEREAL",
		16 => "TASKFINISH",
		17 => "TASKFILES",
		18 => "TASKREPORT",
	),
	"TASK_FORUM_ID" => "#TASKS_FORUM_ID#",
	"FILES_USER_IBLOCK_TYPE" => "library",
	"FILES_USER_IBLOCK_ID" => "#FILES_USER_IBLOCK_ID#",
	"FILES_USE_AUTH" => "Y",
	"FILE_NAME_FILE_PROPERTY" => "FILE",
	"FILES_UPLOAD_MAX_FILESIZE" => "64",
	"FILES_UPLOAD_MAX_FILE" => "4",
	"FILES_USE_COMMENTS" => "Y",
	"FILES_FORUM_ID" => "#FILES_FORUM_ID#",
	"FILES_USE_CAPTCHA" => "Y",
	"PHOTO_USER_IBLOCK_TYPE" => "photos",
	"PHOTO_USER_IBLOCK_ID" => "#PHOTO_USER_IBLOCK_ID#",
	"PHOTO_UPLOAD_MAX_FILESIZE" => "64",
	"PHOTO_UPLOAD_MAX_FILE" => "4",
	"PHOTO_ALBUM_PHOTO_THUMBS_SIZE" => "100",
	"PHOTO_ALBUM_PHOTO_SIZE" => "100",
	"PHOTO_THUMBS_SIZE" => "120",
	"PHOTO_PREVIEW_SIZE" => "300",
	"PHOTO_WATERMARK_MIN_PICTURE_SIZE" => "200",
	"PHOTO_PATH_TO_FONT" => "",
	"PHOTO_USE_RATING" => "Y",
	"PHOTO_USE_COMMENTS" => "Y",
	"PHOTO_FORUM_ID" => "#PHOTOGALLERY_FORUM_ID#",
	"PHOTO_USE_CAPTCHA" => "N",
	"AJAX_OPTION_ADDITIONAL" => "",
	"SEF_URL_TEMPLATES" => array(
		"index" => "index.php",
		"user" => "user/#user_id#/",
		"user_friends" => "user/#user_id#/friends/",
		"user_friends_add" => "user/#user_id#/friends/add/",
		"user_friends_delete" => "user/#user_id#/friends/delete/",
		"user_groups" => "user/#user_id#/groups/",
		"user_groups_add" => "user/#user_id#/groups/add/",
//		"group_create" => "#SITE_DIR#workgroups/create/",
		"user_profile_edit" => "user/#user_id#/edit/",
		"user_settings_edit" => "user/#user_id#/settings/",
		"user_features" => "user/#user_id#/features/",
		"group_request_group_search" => "group/#user_id#/group_search/",
		"group_request_user" => "group/#group_id#/user/#user_id#/request/",
		"search" => "search.php",
		"message_form" => "messages/form/#user_id#/",
		"message_form_mess" => "messages/form/#user_id#/#message_id#/",
		"user_ban" => "messages/ban/",
		"messages_chat" => "messages/chat/#user_id#/",
		"messages_input" => "messages/input/",
		"messages_input_user" => "messages/input/#user_id#/",
		"messages_output" => "messages/output/",
		"messages_output_user" => "messages/output/#user_id#/",
		"messages_users" => "messages/",
		"messages_users_messages" => "messages/#user_id#/",
		"log" => "log/",
		"subscribe" => "subscribe/",
		"user_subscribe" => "user/#user_id#/subscribe/",
		"user_photo" => "user/#user_id#/photo/",
		"user_calendar" => "user/#user_id#/calendar/",
		"user_files" => "user/#user_id#/files/lib/#path#",
		"user_blog" => "user/#user_id#/blog/",
		"user_blog_post_edit" => "user/#user_id#/blog/edit/#post_id#/",
		"user_blog_rss" => "user/#user_id#/blog/rss/#type#/",
		"user_blog_draft" => "user/#user_id#/blog/draft/",
		"user_blog_post" => "user/#user_id#/blog/#post_id#/",
		"user_forum" => "user/#user_id#/forum/",
		"user_forum_topic_edit" => "user/#user_id#/forum/edit/#topic_id#/",
		"user_forum_topic" => "user/#user_id#/forum/#topic_id#/",
		"user_tasks" => "user/#user_id#/tasks/",
		"user_tasks_task" => "user/#user_id#/tasks/task/#action#/#task_id#/",
		"user_tasks_view" => "user/#user_id#/tasks/view/#action#/#view_id#/",
		"group_files" => "group/#group_id#/files/lib/#path#/",
	),
	"LOG_NEW_TEMPLATE" => "Y",
	"GROUP_USE_KEYWORDS" => "N"
	),
	false
);?> <?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>