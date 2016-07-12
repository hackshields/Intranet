<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$arResult["ITEMS_TOTAL"] = 0;
$arResult["ITEMS_MESSAGES"] = 0;
$arResult["ITEMS_REQUESTS_USER"] = 0;
$arResult["ITEMS_REQUESTS_GROUP"] = 0;

/* group requests */
$arFilter = array(
		"USER_ID" => $GLOBALS["USER"]->GetID(),
		"ROLE" => SONET_ROLES_REQUEST,
		"INITIATED_BY_TYPE" => SONET_INITIATED_BY_GROUP,
);

$dbUserRequests = CSocNetUserToGroup::GetList(
	array(),
	$arFilter,
	array("USER_ID"),
	false,
	array("COUNT" => "ID")
);
if ($arUserRequests = $dbUserRequests->Fetch())
{
	$arResult["ITEMS_TOTAL"] += intval($arUserRequests["CNT"]);
	$arResult["ITEMS_REQUESTS_GROUP"] += intval($arUserRequests["CNT"]);
}
?>