<?
define("GW_DEBUG", false); // Debug
define("DAV_EXCH_DEBUG", false); // Log

global $DB;
$db_type = strtolower($DB->type);
$arClasses = array(
	"CDavRequest" => "classes/general/request.php",
	"CDavResponse" => "classes/general/response.php",
	"CDav" => "classes/general/dav.php",
	"CDavWebDav" => "classes/general/webdav.php",
	"CDavGroupDav" => "classes/general/groupdav.php",
	"CDavResource" => "classes/general/resource.php",
	"CDavAccount" => "classes/general/account.php",
	"CDavPrincipal" => "classes/general/principal.php",
	"CDavGroupdavHandler" => "classes/general/groupdavhandler.php",
	"CDavCalendarHandler" => "classes/general/calendarhandler.php",
	"CDavAddressbookHandler" => "classes/general/addressbookhandler.php",
	"CDavVirtualFileSystem" => "classes/".$db_type."/virtualfilesystem.php",
	"CDavPrincipalsHandler" => "classes/general/principalshandler.php",
	"CDavXmlDocument" => "classes/general/xmldocument.php",
	"CDavXMLParsingException" => "classes/general/xmldocument.php",
	"CDavXmlNode" => "classes/general/xmlnode.php",
	"CDavICalendar" => "classes/general/icalendar.php",
	"CDavICalendarComponent" => "classes/general/icalendar.php",
	"CDavICalendarProperty" => "classes/general/icalendar.php",
	"CDavICalendarTimeZone" => "classes/general/icalendartimezone.php",
	"CDavGroupdavClient" => "classes/general/groupdavclient.php",
	"CDavGroupdavClientCalendar" => "classes/general/groupdavclientcalendar.php",
	"CDavGroupdavClientRequest" => "classes/general/groupdavclientrequest.php",
	"CDavGroupdavClientResponce" => "classes/general/groupdavclientresponse.php",
	"CDavExchangeClientResponce" => "classes/general/exchangeclientresponse.php",
	"CDavExchangeClientRequest" => "classes/general/exchangeclientrequest.php",
	"CDavExchangeClient" => "classes/general/exchangeclient.php",
	"CDavExchangeCalendar" => "classes/general/exchangecalendar.php",
	"CDavExchangeContacts" => "classes/general/exchangecontacts.php",
	"CDavExchangeTasks" => "classes/general/exchangetasks.php",
	"CDavExchangeMail" => "classes/general/exchangemail.php",
	"CDavConnection" => "classes/".$db_type."/connection.php",
	"CDavConnectionResult" => "classes/".$db_type."/connection.php",
	"CDavArgumentException" => "classes/general/exception.php",
	"CDavArgumentNullException" => "classes/general/exception.php",
	"CDavArgumentOutOfRangeException" => "classes/general/exception.php",
	"CDavArgumentTypeException" => "classes/general/exception.php",
	"CDavInvalidOperationException" => "classes/general/exception.php",
);
CModule::AddAutoloadClasses("dav", $arClasses);
?>