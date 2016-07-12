<?
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/lang.php");

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/tasks/tools.php");

CModule::IncludeModule("iblock");

CModule::AddAutoloadClasses(
	'tasks',
	array(
		'CTasks'                 => 'classes/general/task.php',
		'CTaskMembers'           => 'classes/general/taskmembers.php',
		'CTaskTags'              => 'classes/general/tasktags.php',
		'CTaskFiles'             => 'classes/general/taskfiles.php',
		'CTaskDependence'        => 'classes/general/taskdependence.php',
		'CTaskTemplates'         => 'classes/general/tasktemplates.php',
		'CTaskSync'              => 'classes/general/tasksync.php',
		'CTaskReport'            => 'classes/general/taskreport.php',
		'CTasksWebService'       => 'classes/general/taskwebservice.php',
		'CTaskLog'               => 'classes/general/tasklog.php',
		'CTaskNotifications'     => 'classes/general/tasknotifications.php',
		'CTaskElapsedTime'       => 'classes/general/taskelapsed.php',
		'CTaskReminders'         => 'classes/general/taskreminders.php',
		'CTasksReportHelper'     => 'classes/general/tasks_report_helper.php',
		'CTasksNotifySchema'     => 'classes/general/tasks_notify_schema.php',
		'CTasksPullSchema'       => 'classes/general/tasks_notify_schema.php',
		'CTaskComments'          => 'classes/general/taskcomments.php',
		'CTaskFilterCtrl'        => 'classes/general/taskfilterctrl.php',
		'CTaskFilterEntity'      => 'classes/general/taskfilterbuilder.php',
		'CTaskFilterEntityUser'  => 'classes/general/taskfilterbuilder.php',
		'CTaskFilterEntityGroup' => 'classes/general/taskfilterbuilder.php',
		'CTaskFilterEntityDate'  => 'classes/general/taskfilterbuilder.php',
		'CTaskAssert'            => 'classes/general/taskassert.php',
		'CTaskItemInterface'     => 'classes/general/taskitem.php',
		'CTaskItem'              => 'classes/general/taskitem.php',
		'CTaskPlannerMaintance'  => 'classes/general/taskplannermaintance.php',
		'CTasksTools'            => 'classes/general/tasktools.php',
		'Bitrix\Tasks\TaskTable'         => 'lib/task.php',
		'Bitrix\Tasks\ElapsedTimeTable'  => 'lib/elapsedtime.php',
		'Bitrix\Tasks\MemberTable'       => 'lib/member.php',
		'Bitrix\Tasks\TagTable'          => 'lib/tag.php',
		'\Bitrix\Tasks\TaskTable'        => 'lib/task.php',
		'\Bitrix\Tasks\ElapsedTimeTable' => 'lib/elapsedtime.php',
		'\Bitrix\Tasks\MemberTable'      => 'lib/member.php',
		'\Bitrix\Tasks\TagTable'         => 'lib/tag.php'
	)
);

CJSCore::RegisterExt(
	'CJSTask',
	array(
		'js'  => '/bitrix/js/tasks/cjstask.js',
		'rel' =>  array('ajax', 'json')
	)
);

CJSCore::RegisterExt(
	'taskQuickPopups',
	array(
		'js'  => '/bitrix/js/tasks/task-quick-popups.js',
		'rel' =>  array('popup', 'ajax', 'json', 'CJSTask')
	)
);

//CTaskAssert::enableLogging();
