<?
global $MESS;
$strPath2Lang = str_replace("\\", "/", __FILE__);
$strPath2Lang = substr($strPath2Lang, 0, strlen($strPath2Lang)-18);
include(GetLangFileName($strPath2Lang."/lang/", "/install/index.php"));

Class xmpp extends CModule
{
	var $MODULE_ID = "xmpp";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;

	function xmpp()
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
			$this->MODULE_VERSION = XMPP_VERSION;
			$this->MODULE_VERSION_DATE = XMPP_VERSION_DATE;
		}

		$this->MODULE_NAME = GetMessage("XMPP_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("XMPP_MODULE_DESC");
	}

	function InstallDB($arParams = array())
	{
		RegisterModule("xmpp");
		RegisterModuleDependences("socialnetwork", "OnSocNetMessagesAdd", "xmpp", "CXMPPFactory", "OnSocNetMessagesAdd");
		RegisterModuleDependences("im", "OnAfterMessagesAdd", "xmpp", "CXMPPFactory", "OnSocNetMessagesAdd");
		RegisterModuleDependences("im", "OnAfterNotifyAdd", "xmpp", "CXMPPFactory", "OnSocNetMessagesAdd");
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences("socialnetwork", "OnSocNetMessagesAdd", "xmpp", "CXMPPFactory", "OnSocNetMessagesAdd");
		UnRegisterModuleDependences("im", "OnAfterMessagesAdd", "xmpp", "CXMPPFactory", "OnSocNetMessagesAdd");
		UnRegisterModuleDependences("im", "OnAfterNotifyAdd", "xmpp", "CXMPPFactory", "OnSocNetMessagesAdd");
		UnRegisterModule("xmpp");
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles()
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/xmpp/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/xmpp/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);

		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/xmpp/install/admin", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/xmpp/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes");

		return true;
	}

	function DoInstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;

		if (IsModuleInstalled("xmpp"))
			return false;
		if (!check_bitrix_sessid())
			return false;

		$this->InstallDB();
		$this->InstallEvents();
		$this->InstallFiles();

		$APPLICATION->IncludeAdminFile(GetMessage("XMPP_INSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/compression/install/step.php");
	}

	function DoUninstall()
	{
		global $DOCUMENT_ROOT, $APPLICATION;

		if (!check_bitrix_sessid())
			return false;

		$this->UnInstallDB();
		$this->UnInstallEvents();
		$this->UnInstallFiles();

		$APPLICATION->IncludeAdminFile(GetMessage("XMPP_UNINSTALL_TITLE"), $DOCUMENT_ROOT."/bitrix/modules/compression/install/unstep.php");
	}
}
?>
