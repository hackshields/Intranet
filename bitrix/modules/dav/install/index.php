<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-strlen("/install/index.php"));
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class dav extends CModule
{
	var $MODULE_ID = "dav";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "Y";

	function dav()
	{
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("DAV_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("DAV_INSTALL_DESCRIPTION");
	}


	function InstallDB($install_wizard = true)
	{
		global $DB, $DBType, $APPLICATION;

		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;

		$errors = null;
		if (!$DB->Query("SELECT 'x' FROM b_dav_locks", true))
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/db/".$DBType."/install.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors)); 
			return false;
		}

		RegisterModule("dav");
		RegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", 50, "/modules/dav/prolog_before.php");

		return true;
	}

	function UnInstallDB($arParams = Array())
	{
		global $DB, $DBType, $APPLICATION;

		$errors = null;
		if(array_key_exists("savedata", $arParams) && $arParams["savedata"] != "Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/db/".$DBType."/uninstall.sql");

			if (!empty($errors))
			{
				$APPLICATION->ThrowException(implode("", $errors)); 
				return false;
			}
		}

		UnRegisterModuleDependences("main", "OnBeforeProlog", "main", "", "", "/modules/dav/prolog_before.php");
		UnRegisterModule("dav");

		return true;
	}

	function InstallEvents()
	{
		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;

		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;

		if($_ENV["COMPUTERNAME"]!='BX')
		{
			//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true);
			//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/components", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components", true, true);
			//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", false, true);
			//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/templates/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/", true, true);
			//CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js", true, true);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/images",  $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/dav", true, True);
			CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/bitrix",  $_SERVER["DOCUMENT_ROOT"]."/bitrix", true, True);
		}

		return true;
	}

	function InstallPublic()
	{
		$arCurPhpVer = Explode(".", PhpVersion());
		if (IntVal($arCurPhpVer[0]) < 5)
			return true;
	}

	function UnInstallFiles()
	{
		if($_ENV["COMPUTERNAME"]!='BX')
		{
			//DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
			//DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
			DeleteDirFilesEx("/bitrix/images/dav/");
			//DeleteDirFilesEx("/bitrix/js/dav/");
		}

		return true;
	}

	function DoInstall()
	{
		global $APPLICATION, $step;

		$this->errors = null;

		$curPhpVer = PhpVersion();
		$arCurPhpVer = Explode(".", $curPhpVer);
		if (IntVal($arCurPhpVer[0]) < 5)
		{
			$this->errors = array(GetMessage("DAV_PHP_L439", array("#VERS#" => $curPhpVer)));
		}
		elseif (!CBXFeatures::IsFeatureEditable("DAV"))
		{
			$this->errors = array(GetMessage("DAV_ERROR_EDITABLE"));
		}
		else
		{
			$this->InstallFiles();
			$this->InstallDB(false);
			$this->InstallEvents();
			$this->InstallPublic();
			CBXFeatures::SetFeatureEnabled("DAV", true);
		}

		$GLOBALS["errors"] = $this->errors;
		$APPLICATION->IncludeAdminFile(GetMessage("DAV_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/step2.php");
	}

	function DoUninstall()
	{
		global $APPLICATION, $step;

		$this->errors = null;

		$step = IntVal($step);
		if($step<2)
		{
			$APPLICATION->IncludeAdminFile(GetMessage("DAV_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/unstep1.php");
		}
		elseif($step==2)
		{
			$this->UnInstallDB(array(
				"savedata" => $_REQUEST["savedata"],
			));
			$this->UnInstallFiles();
			
			$this->UnInstallEvents();

			CBXFeatures::SetFeatureEnabled("DAV", false);
			$GLOBALS["errors"] = $this->errors;

			$APPLICATION->IncludeAdminFile(GetMessage("DAV_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav/install/unstep2.php");
		}
	}

	function GetModuleRightList()
	{
		$arr = array(
			"reference_id" => array("D", "R", "W"),
			"reference" => array(
					"[D] ".GetMessage("DAV_PERM_D"),
					"[R] ".GetMessage("DAV_PERM_R"),
					"[W] ".GetMessage("DAV_PERM_W")
				)
			);
		return $arr;
	}
}
?>