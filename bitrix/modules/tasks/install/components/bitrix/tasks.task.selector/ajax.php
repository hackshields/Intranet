<?
define('STOP_STATISTICS',    true);
define('NO_AGENT_CHECK',     true);
define('DisableEventsCheck', true);

define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CModule::IncludeModule('tasks');

__IncludeLang(dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/'.basename(__FILE__));

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;

if ($_REQUEST['MODE'] == 'SEARCH')
{
	CUtil::JSPostUnescape();
	$APPLICATION->RestartBuffer();

	$search = $_REQUEST['SEARCH_STRING'];
	$arFilter = array("%TITLE" => $search);

	if (isset($_GET["FILTER"]))
		$arFilter = array_merge($arFilter, $_GET["FILTER"]);

	$dbRes = CTasks::GetList(
		array('TITLE' => 'ASC'), 
		$arFilter,
		array(),	// fields to be selected (empty array => all fields)
		10			// nPageTop
		);

	$arTasks = array();
	while ($arRes = $dbRes->fetch())
	{
		$arTasks[] = array(
			"ID" => $arRes["ID"],
			"TITLE" => $arRes["TITLE"],
			"STATUS" => $arRes["STATUS"]
		);
	}

	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJsObject($arTasks);
	die();
}
?>