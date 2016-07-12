<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["PATH_TO_MYPORTAL"] = (isset($arParams["PATH_TO_MYPORTAL"]) ? $arParams["PATH_TO_MYPORTAL"] : SITE_DIR."desktop.php");
$arParams["PATH_TO_SONET_PROFILE"] = (isset($arParams["PATH_TO_SONET_PROFILE"]) ? $arParams["PATH_TO_SONET_PROFILE"] : SITE_DIR."company/personal/user/#user_id#/");
$arParams["PATH_TO_SONET_GROUP"] = (isset($arParams["PATH_TO_SONET_GROUP"]) ? $arParams["PATH_TO_SONET_GROUP"] : SITE_DIR."workgroups/group/#group_id#/");
$arParams["PATH_TO_SONET_MESSAGES"] = (isset($arParams["PATH_TO_SONET_MESSAGES"]) ? $arParams["PATH_TO_SONET_MESSAGES"] : SITE_DIR."company/personal/messages/");
$arParams["PATH_TO_SONET_MESSAGE_FORM"] = (isset($arParams["PATH_TO_SONET_MESSAGE_FORM"]) ? $arParams["PATH_TO_SONET_MESSAGE_FORM"] : SITE_DIR."company/personal/messages/form/#user_id#/");
$arParams["PATH_TO_SONET_MESSAGE_FORM_MESS"] = (isset($arParams["PATH_TO_SONET_MESSAGE_FORM_MESS"]) ? $arParams["PATH_TO_SONET_MESSAGE_FORM_MESS"] : SITE_DIR."company/personal/messages/form/#user_id#/#message_id#/");
$arParams["PATH_TO_SONET_MESSAGES_CHAT"] = (isset($arParams["PATH_TO_SONET_MESSAGES_CHAT"]) ? $arParams["PATH_TO_SONET_MESSAGES_CHAT"] : SITE_DIR."company/personal/messages/chat/#user_id#/");
$arParams["THUMBNAIL_SIZE"] = (isset($arParams["THUMBNAIL_SIZE"]) ? intval($arParams["THUMBNAIL_SIZE"]) : 37);                       

$arResult["USER_FULL_NAME"] = CUser::FormatName("#NAME# #LAST_NAME#", array(
	"NAME" => $USER->GetFirstName(),
	"LAST_NAME" => $USER->GetLastName(),
	"SECOND_NAME" => $USER->GetSecondName(),
	"LOGIN" => $USER->GetLogin()
));

$user_id = intval($GLOBALS["USER"]->GetID());
$aMenuLinksAdd = array();
if(defined("BX_COMP_MANAGED_CACHE"))
	$ttl = 2592000;
else			
	$ttl = 600;
$cache_id = 'user_avatar_'.$user_id;
$cache_dir = '/bx/user_avatar';
$obCache = new CPHPCache;

if($obCache->InitCache($ttl, $cache_id, $cache_dir))
{
	$arResult["USER_PERSONAL_PHOTO_SRC"] = $obCache->GetVars();
}
else
{
	if ($GLOBALS["USER"]->IsAuthorized())
	{
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache($cache_dir);
		}
		
		$dbUser = CUser::GetByID($GLOBALS["USER"]->GetID());
		$arUser = $dbUser->Fetch();
	
		$iSize = $arParams["THUMBNAIL_SIZE"];
		$imageFile = false;

		if (intval($arUser["PERSONAL_PHOTO"]) > 0)
		{
			$imageFile = CFile::GetFileArray($arUser["PERSONAL_PHOTO"]);
			if ($imageFile !== false)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$imageFile,
					array("width" => $arParams["THUMBNAIL_SIZE"], "height" => $arParams["THUMBNAIL_SIZE"]),
					BX_RESIZE_IMAGE_EXACT,
					false
				);
				$arResult["USER_PERSONAL_PHOTO_SRC"] = $arFileTmp["src"];			
			}
		}
		if(defined("BX_COMP_MANAGED_CACHE"))
		{
			$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($user_id / 100));
			$CACHE_MANAGER->EndTagCache();
		}
	}
	
	if($obCache->StartDataCache())
	{
		$obCache->EndDataCache($arResult["USER_PERSONAL_PHOTO_SRC"]);
	}
}
?>