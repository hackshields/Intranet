<?
$_SERVER['DOCUMENT_ROOT'] = DirName(__FILE__);
$_SERVER['DOCUMENT_ROOT'] = SubStr($_SERVER['DOCUMENT_ROOT'], 0, StrLen($_SERVER['DOCUMENT_ROOT']) - StrLen("/bitrix/modules/xmpp"));

define('NOT_CHECK_PERMISSIONS', true);
define('BX_BUFFER_USED',false);
define("BX_NO_ACCELERATOR_RESET", true);

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('xmpp'))
	die('XMPP module is not installed');

CXMPPServer::Run();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');
?>