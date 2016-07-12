<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

include(dirname(__FILE__)."/functions.php");
$arActions = Array(
	"checkout",
	"get_app_map",
	"save_device_token",
	"get_counters",
	"get_user_list",
	'get_subordinated_user_list',
	"get_group_list",
	"get_usergroup_list",
	"get_likes",
	"logout",
	"calendar"
);

if (!$USER->IsAuthorized())
{
	echo json_encode(Array("status"=>"failed"));
	die();
}

if($_REQUEST["api_version"])
{
	$APPLICATION->set_cookie("MOBILE_APP_VERSION", intval($_REQUEST["api_version"]), time()+60*60*24*30*12*2);
	$api_version = intval($_REQUEST["api_version"]);
}
else
{
	$api_version = $APPLICATION->get_cookie("MOBILE_APP_VERSION");
	if(!$api_version)
		$api_version = 1;
}

$APPLICATION->SetPageProperty("api_version", $api_version);
if ($_REQUEST["mobile_action"])
{
	header("Content-Type: application/x-javascript");
	$data = Array("error"=>"unknow data request action");
	$action = $_REQUEST["mobile_action"];
	if (in_array($action, $arActions))
	{
		switch ($action)
		{
			case "checkout": //this is authorization checkout, !do not delete!
				include(dirname(__FILE__)."/actions/checkout.php");
				break;
			case "logout":
				include(dirname(__FILE__)."/actions/logout.php");
				break;
			case "save_device_token":
				include(dirname(__FILE__)."/actions/save_device_token.php");
				break;
			case "get_likes":
				include(dirname(__FILE__)."/actions/get_likes.php");
				break;
			case "get_user_list":
			case "get_group_list": // get_group_list and get_usergroup_list - groups just for blog post
			case "get_usergroup_list":
				include(dirname(__FILE__)."/actions/users_groups.php");
				break;
			case 'get_subordinated_user_list':
				require(dirname(__FILE__) . '/actions/users_subordinates.php');
				break;
			case 'calendar':
				require(dirname(__FILE__) . '/actions/calendar.php');
				break;
		}
	}

	$APPLICATION->RestartBuffer();
	echo json_encode($data);
	die();
}

$this->IncludeComponentTemplate();
?>