<?
if (!CModule::IncludeModule('intranet'))
	return false;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile(dirname(__FILE__).'/install/index.php');

define('BX_TIMEMAN_ALLOWED_TIME_DELTA', 120); // allowed time delta - two minutes
define('BX_TIMEMAN_WRONG_DATE_CHECK', 86400);

define("SONET_TIMEMAN_ENTRY_ENTITY", "T");

if (!defined("CACHED_timeman_settings"))
	define("CACHED_timeman_settings", 2592000);

$GLOBALS['BX_TIMEMAN_TASKS_MIGRATION_RULES'] = array(
	1 => array(2,7),
	2 => array(3,4),
	3 => array(4,6),
	4 => array(2),
	5 => array(2),
	6 => array(4,2),
);
$GLOBALS['BX_TIMEMAN_RECENTLY_ADDED_TASK_ID'] = null;
$GLOBALS['BX_TIMEMAN_RECENTLY_ADDED_EVENT_ID'] = null;

global $DBType;

CModule::AddAutoloadClasses(
	"timeman",
	array(
		"CTimeMan" => "classes/general/timeman.php",
		"CTimeManCalendar" => "classes/general/timeman.php",
		"CTimeManUser" => "classes/general/timeman_user.php",
		"CTimeManEntry" => "classes/".$DBType."/timeman_entry.php",
		"CTimeManReport" => "classes/".$DBType."/timeman_report.php",
		"CTimeManReportDaily" => "classes/".$DBType."/timeman_report_daily.php",
		"CTimeManReportFull" => "classes/general/timeman_report_full.php",
		"CUserReportFull" => "classes/general/timeman_report_full.php",
		"CReportSettings" => "classes/general/timeman_report_full.php",
		"CReportNotifications" => "classes/general/timeman_report_full.php",

		"CTimeManAdminReport" => "classes/general/timeman_admin_report.php",
		"CTimeManNotify" => "classes/general/timeman_notify.php",
	)
);

CJSCore::RegisterExt('timeman', array(
	'js' => '/bitrix/js/timeman/core_timeman.js',
	'css' => '/bitrix/js/timeman/css/core_timeman.css',
	'lang' => '/bitrix/modules/timeman/lang/'.LANGUAGE_ID.'/js_core_timeman.php',
	'rel' => array('ajax', 'timer', 'popup', 'ls', 'planner')
));
?>