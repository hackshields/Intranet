<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arParams['NAME_TEMPLATE'] = $arParams['NAME_TEMPLATE'] ? $arParams['NAME_TEMPLATE'] : CSite::GetNameFormat();

if ($arParams['bShowFilter'])
{
	$dbCurrentUser = CUser::GetByID($GLOBALS['USER']->GetID());
	$arResult['CURRENT_USER'] = $dbCurrentUser->Fetch();
	if ($arParams['bShowFilter'] = !!($arResult['CURRENT_USER']['UF_DEPARTMENT']))
	{
		$arResult['CURRENT_USER']['DEPARTMENT_TOP'] = CIntranetUtils::GetIBlockTopSection($arResult['CURRENT_USER']['UF_DEPARTMENT']);
		if (intval($arResult['DEPARTMENT']) == $arResult['CURRENT_USER']['DEPARTMENT_TOP']) 
			$arResult['ONLY_MINE'] = 'Y';
	}
}

foreach ($arResult['USERS'] as $key => $arUser)
{
	if ($arUser['PERSONAL_PHOTO'])
	{
		$imageFile = CFile::GetFileArray($arUser['PERSONAL_PHOTO']);
		if ($imageFile !== false)
		{
			$arFileTmp = CFile::ResizeImageGet(
				$imageFile,
				array("width" => 42, "height" => 42),
				BX_RESIZE_IMAGE_EXACT,
				true
			);
		}

		if($arFileTmp && array_key_exists("src", $arFileTmp))
			$arUser["PERSONAL_PHOTO"] = CFile::ShowImage($arFileTmp["src"], 42, 42);
	}
	
	$arResult['USERS'][$key] = $arUser;
}
?>