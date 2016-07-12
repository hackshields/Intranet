<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class tasks extends CModule
{
	var $MODULE_ID = "tasks";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $errors;

	function tasks()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion["VERSION"];
			$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		}
		else
		{
			$this->MODULE_VERSION = TASKS_VERSION;
			$this->MODULE_VERSION_DATE = TASKS_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("TASKS_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("TASKS_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		// Database tables creation
		if(!$DB->Query("SELECT 'x' FROM b_tasks WHERE 1=0", true))
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/db/".strtolower($DB->type)."/install.sql");
		}

		if($this->errors !== false)
		{
			$APPLICATION->ThrowException(implode("<br>", $this->errors));
			return false;
		}

		RegisterModule("tasks");
		RegisterModuleDependences("search", "OnReindex", "tasks", "CTasks", "OnSearchReindex", 200);
		RegisterModuleDependences("main", "OnUserDelete", "tasks", "CTasks", "OnUserDelete");
		RegisterModuleDependences("im", "OnGetNotifySchema", "tasks", "CTasksNotifySchema", "OnGetNotifySchema");
		RegisterModuleDependences('main', 'OnBeforeUserDelete', 'tasks', 'CTasks', 'OnBeforeUserDelete');
		RegisterModuleDependences("pull", "OnGetDependentModule", "tasks", "CTasksPullSchema", "OnGetDependentModule");
		RegisterModuleDependences(
			'search',
			'BeforeIndex',
			'tasks',
			'CTasksTools',
			'FixForumCommentURL'
		);
		RegisterModuleDependences('intranet', 'OnPlannerInit', 'tasks',
			'CTaskPlannerMaintance', 'OnPlannerInit');
		RegisterModuleDependences('intranet', 'OnPlannerAction', 'tasks',
			'CTaskPlannerMaintance', 'OnPlannerAction');

		CAgent::AddAgent('CTaskReminders::SendAgent();','tasks', 'N', 10800);	// every 3 hours

		// If sanitize_level not set, set up it
		if (COption::GetOptionString('tasks', 'sanitize_level', 'not installed yet ))') === 'not installed yet ))')
			COption::SetOptionString('tasks', 'sanitize_level', CBXSanitizer::SECURE_LEVEL_LOW);

		return true;
	}

	function UnInstallDB($arParams = array())
	{
		global $DB, $DBType, $APPLICATION;

		$this->errors = false;

		if(!array_key_exists("savedata", $arParams) || $arParams["savedata"] != "Y")
		{
			$this->errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/db/".strtolower($DB->type)."/uninstall.sql");
		}

		//delete agents
		CAgent::RemoveModuleAgents("tasks");

		if (CModule::IncludeModule("search"))
			CSearch::DeleteIndex("tasks");

		UnRegisterModule("tasks");
		UnRegisterModuleDependences("search", "OnReindex", "tasks", "CTasks", "OnSearchReindex");
		UnRegisterModuleDependences("main", "OnUserDelete", "tasks", "CTasks", "OnUserDelete");
		UnRegisterModuleDependences("im", "OnGetNotifySchema", "tasks", "CTasksNotifySchema", "OnGetNotifySchema");
		UnRegisterModuleDependences('main', 'OnBeforeUserDelete', 'tasks', 'CTasks', 'OnBeforeUserDelete');
		UnRegisterModuleDependences("pull", "OnGetDependentModule", "tasks", "CTasksPullSchema", "OnGetDependentModule");
		UnRegisterModuleDependences(
			'search',
			'BeforeIndex',
			'tasks',
			'CTasksTools',
			'FixForumCommentURL'
		);
		UnRegisterModuleDependences('intranet', 'OnPlannerInit', 'tasks',
			'CTaskPlannerMaintance', 'OnPlannerInit');
		UnRegisterModuleDependences('intranet', 'OnPlannerAction', 'tasks',
			'CTaskPlannerMaintance', 'OnPlannerAction');

		return true;
	}

	function InstallEvents()
	{

		global $DB;
		$sIn = "'TASK_REMINDER'";
		$rs = $DB->Query("SELECT count(*) C FROM b_event_type WHERE EVENT_NAME IN (".$sIn.") ", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar = $rs->Fetch();
		if($ar["C"] <= 0)
		{
			include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/events/set_events.php");
		}
		return true;
	}

	function UnInstallEvents()
	{
		global $DB;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/events/del_events.php");
		return true;
	}

	function InstallFiles($arParams = array())
	{
		global $DB;

		if($_ENV["COMPUTERNAME"]!='BX')
		{
			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/admin", 
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", 
				false
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/components",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/components",
				true,
				true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/activities",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/activities",
				true,
				true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/public/js",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/js",
				true,
				true
			);

			CopyDirFiles(
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/public/tools",
				$_SERVER["DOCUMENT_ROOT"]."/bitrix/tools",
				true,
				true
			);
		}

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");

		DeleteDirFilesEx("/bitrix/js/tasks/");//scripts
		return true;
	}

	function DoInstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION;

		if (!CBXFeatures::IsFeatureEditable('Tasks'))
		{
			$this->errors = array(GetMessage('MAIN_FEATURE_ERROR_EDITABLE'));
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(
				GetMessage('TASKS_INSTALL_TITLE'),
				$_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/step1.php');
		}
		elseif (!IsModuleInstalled("tasks"))
		{
			$this->InstallFiles();
			$this->InstallDB();
			$this->InstallEvents();

			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("TASKS_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/step1.php");
		}
	}

	function DoUninstall()
	{
		global $DB, $DOCUMENT_ROOT, $APPLICATION, $step;
		$step = IntVal($step);
		if($step < 2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("TASKS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/unstep1.php");
		}
		elseif($step == 2)
		{
			$this->UnInstallDB(array(
					"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			$this->UnInstallEvents();
			$GLOBALS["errors"] = $this->errors;
			$APPLICATION->IncludeAdminFile(GetMessage("TASKS_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/unstep2.php");
		}
	}
}
?>