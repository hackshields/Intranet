<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!$arResult["CurrentUserPerms"]["Operations"]["viewprofile"])
{
	$arResult["FatalError"] = GetMessage("SONET_P_USER_ACCESS_DENIED");
	return;
}

$arResult["USER_FULL_NAME"] = CUser::FormatName("#NAME# #LAST_NAME#", array(
	"NAME" => htmlspecialcharsback($arResult["User"]["NAME"]),
	"LAST_NAME" => htmlspecialcharsback($arResult["User"]["LAST_NAME"]),
	"SECOND_NAME" => htmlspecialcharsback($arResult["User"]["SECONS_NAME"]),
	"LOGIN" => htmlspecialcharsback($arResult["User"]["LOGIN"])
));

if (intval($arResult["User"]["PERSONAL_PHOTO"]) > 0)
{
	$imageFile = CFile::GetFileArray($arResult["User"]["PERSONAL_PHOTO"]);
	if ($imageFile !== false)
	{
		$arFileTmp = CFile::ResizeImageGet(
			$imageFile,
			array("width" => "154", "height" => "250"),
			BX_RESIZE_IMAGE_PROPORTIONAL ,
			true
		);
		$arResult["USER_PERSONAL_PHOTO_SRC"] = $arFileTmp;			
	}
}  
// subordinate	
if (
	(
		!CModule::IncludeModule("extranet")
		|| !CExtranet::IsExtranetSite()
		|| CExtranet::IsIntranetUser()
	)
	&& CModule::IncludeModule("iblock")
)
{
	$subordinate_users = array();
	if (is_array($arResult["DEPARTMENTS"]))
	{
		foreach ($arResult["DEPARTMENTS"] as $key => $dep)
		{		        
			$dbUsers = CUser::GetList(
				$o = "", $b="",
				array("!ID" => $arResult["User"]["ID"], 'UF_DEPARTMENT' => $dep["ID"], 'ACTIVE' => 'Y', '!LAST_ACTIVITY' => false),
				array('FIELDS' => array("ID", "NAME", "LAST_NAME", "SECOND_NAME", "LOGIN", "WORK_POSITION")));

			while ($arRes = $dbUsers->GetNext())
				$subordinate_users[$arRes["ID"]] = $arRes;
		}
	}
	$arResult["SUBORDINATE"] = $subordinate_users;
}

// user activity status
if ($arResult["User"]["ACTIVE"] == "Y")
	$arResult["User"]["ACTIVITY_STATUS"] = "active";
$obUser = new CUser();
$arGroups = $obUser->GetUserGroup($arResult["User"]['ID']);
if (in_array(1, $arGroups))
	$arResult["User"]["ACTIVITY_STATUS"] = "admin";
			
$arGroups = CUser::GetUserGroup($arResult["User"]['ID']);
if (CModule::IncludeModule('extranet') && in_array(CExtranet::GetExtranetUserGroupID(), $arGroups) && count($arResult["User"]['UF_DEPARTMENT']) <= 0)
{
	$arResult["User"]["ACTIVITY_STATUS"] = "extranet";
	$arResult["User"]["IS_EXTRANET"] = true;
}
else
	$arResult["User"]["IS_EXTRANET"] = false;

if ($arResult["User"]["ACTIVE"] == "N")
	$arResult["User"]["ACTIVITY_STATUS"] = "fired";

if (IsModuleInstalled("bitrix24") && $arResult["User"]["ACTIVE"] == "Y" && empty($arResult["User"]["LAST_ACTIVITY_DATE"]))
	$arResult["User"]["ACTIVITY_STATUS"] = "invited"; 
	
if (
	$arResult["User"]["ID"] == $GLOBALS["USER"]->GetID() 
	&& CSocNetUser::IsCurrentUserModuleAdmin(SITE_ID, false)
	&& !isset($_SESSION["SONET_ADMIN"])
)
	$arResult["SHOW_SONET_ADMIN"] = true;
?>