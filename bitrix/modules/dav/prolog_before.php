<?
if (
//($_SERVER['REQUEST_URI'] == '/' || $_SERVER['REQUEST_URI'] == '/index.php')
//&&
($_SERVER['REQUEST_METHOD'] == 'PROPFIND' || $_SERVER['REQUEST_METHOD'] == 'OPTIONS')
&&
(strpos(strtolower($_SERVER['REQUEST_URI']), "/bitrix/groupdav.php") === false)
)
{
	if (preg_match("/(bitrix|coredav|iphone|davkit|dataaccess|sunbird|lightning|cfnetwork|zideone|webkit|khtml|neon|ical4ol|ios\\/5|ios\\/6|mac\sos)/i", $_SERVER['HTTP_USER_AGENT']))
	{
		CHTTP::SetStatus("302 Found");
		header('Location: /bitrix/groupdav.php/');
		die();
	}
}
?>