<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

$userID = $arResult['USER_ID'] = intval(CCrmSecurityHelper::GetCurrentUserID());
//$userPerms = CCrmPerms::GetCurrentUserPermissions();
if (!CCrmPerms::IsAccessEnabled())
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

global $APPLICATION;

$arParams['LEAD_SHOW_URL_TEMPLATE'] =  isset($arParams['LEAD_SHOW_URL_TEMPLATE']) ? $arParams['LEAD_SHOW_URL_TEMPLATE'] : '';
$arParams['DEAL_SHOW_URL_TEMPLATE'] =  isset($arParams['DEAL_SHOW_URL_TEMPLATE']) ? $arParams['DEAL_SHOW_URL_TEMPLATE'] : '';
$arParams['CONTACT_SHOW_URL_TEMPLATE'] =  isset($arParams['CONTACT_SHOW_URL_TEMPLATE']) ? $arParams['CONTACT_SHOW_URL_TEMPLATE'] : '';
$arParams['COMPANY_SHOW_URL_TEMPLATE'] =  isset($arParams['COMPANY_SHOW_URL_TEMPLATE']) ? $arParams['COMPANY_SHOW_URL_TEMPLATE'] : '';
$arParams['USER_PROFILE_URL_TEMPLATE'] = isset($arParams['USER_PROFILE_URL_TEMPLATE']) ? $arParams['USER_PROFILE_URL_TEMPLATE'] : '';
$arParams['COMMUNICATION_LIST_URL_TEMPLATE'] =  isset($arParams['COMMUNICATION_LIST_URL_TEMPLATE']) ? $arParams['COMMUNICATION_LIST_URL_TEMPLATE'] : '';
$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array('#NOBR#','#/NOBR#'), array('', ''), $arParams['NAME_TEMPLATE']);

$entityID = $arParams['ENTITY_ID'] = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
if($entityID <= 0 && isset($_GET['activity_id']))
{
	$entityID = $arParams['ENTITY_ID'] = intval($_GET['activity_id']);
}
$arResult['ENTITY_ID'] = $entityID;

$arParams['UID'] = isset($arParams['UID']) ? $arParams['UID'] : '';
if(!isset($arParams['UID']) || $arParams['UID'] === '')
{
	$arParams['UID'] = 'mobile_crm_activity_view';
}
$arResult['UID'] = $arParams['UID'];

$dbFields = CCrmActivity::GetList(array(), array('ID' => $entityID));
$arFields = $dbFields->Fetch();

if(!$arFields)
{
	ShowError(GetMessage('CRM_ACTIVITY_VIEW_NOT_FOUND', array('#ID#' => $arParams['ENTITY_ID'])));
	return;
}

CCrmMobileHelper::PrepareActivityItem($arFields, $arParams);

//COMMUNICATION
$arFields['CLIENT_TITLE'] = '';
$arFields['CLIENT_SHOW_URL'] = '';
$arFields['CLIENT_IMAGE_URL'] = '';
$arFields['CLIENT_LEGEND'] = '';
$arFields['CLIENT_COMPANY_TITLE'] = '';
$arFields['CLIENT_COMPANY_SHOW_URL'] = '';
$arFields['CLIENT_COMMUNICATION_VALUE'] = '';

$comm = is_array($arFields['COMMUNICATIONS'])
	&& isset($arFields['COMMUNICATIONS'][0])
	? $arFields['COMMUNICATIONS'][0] : null;

if($comm)
{
	$arFields['CLIENT_COMMUNICATION_VALUE'] = isset($comm['VALUE']) ? $comm['VALUE'] : '';

	$commOwnerTypeID = isset($comm['ENTITY_TYPE_ID']) ? intval($comm['ENTITY_TYPE_ID']) : 0;
	$commOwnerID = isset($comm['ENTITY_ID']) ? intval($comm['ENTITY_ID']) : 0;

	if($commOwnerTypeID === CCrmOwnerType::Company)
	{
		$dbRes = CCrmCompany::GetListEx(
			array(),
			array('=ID' => $commOwnerID),
			false,
			false,
			array('TITLE', 'LOGO')
		);
		$arCompany = $dbRes ? $dbRes->Fetch() : null;
		if($arCompany)
		{
			$arFields['CLIENT_TITLE'] = isset($arCompany['TITLE']) ? $arCompany['TITLE'] : '';
			$arFields['CLIENT_SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
				$arParams['COMPANY_SHOW_URL_TEMPLATE'],
				array('company_id' => $commOwnerID)
			);

			$arFields['CLIENT_IMAGE_URL'] = SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_company_big.png?ver=1';
			$imageID = isset($arCompany['LOGO']) ? intval($arCompany['LOGO']) : 0;
			if($imageID > 0)
			{
				$imageInfo = CFile::ResizeImageGet(
					$imageID, array('width' => 55, 'height' => 55), BX_RESIZE_IMAGE_EXACT);
				if($imageInfo && isset($imageInfo['src']))
				{
					$arFields['CLIENT_IMAGE_URL'] = $imageInfo['src'];
				}
			}

			$arMultiFields = array();
			$dbMultiFields = CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $commOwnerID)
			);
			if($dbMultiFields)
			{
				while($multiFields = $dbMultiFields->Fetch())
				{
					$arMultiFields[$multiFields['TYPE_ID']][] = array('VALUE' => $multiFields['VALUE'], 'VALUE_TYPE' => $multiFields['VALUE_TYPE']);
				}
			}

			$arFields['CLIENT_CALLTO'] = CCrmMobileHelper::PrepareCalltoParams(
				array(
					'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
					'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
					'ENTITY_ID' => $commOwnerID,
					'FM' => $arMultiFields
				)
			);

			$arFields['CLIENT_MAILTO'] = CCrmMobileHelper::PrepareMailtoParams(
				array(
					'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
					'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
					'ENTITY_ID' => $commOwnerID,
					'FM' => $arMultiFields
				)
			);
		}
	}
	elseif($commOwnerTypeID === CCrmOwnerType::Contact)
	{
		$dbRes = CCrmContact::GetListEx(
			array(),
			array('=ID' => $commOwnerID),
			false,
			false,
			array('NAME', 'LAST_NAME', 'SECOND_NAME', 'PHOTO', 'POST', 'COMPANY_ID', 'COMPANY_TITLE')
		);
		$arContact = $dbRes ? $dbRes->Fetch() : null;
		if($arContact)
		{
			$arFields['CLIENT_TITLE'] = CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => '',
					'NAME' => isset($arContact['NAME']) ? $arContact['NAME'] : '',
					'LAST_NAME' => isset($arContact['LAST_NAME']) ? $arContact['LAST_NAME'] : '',
					'SECOND_NAME' => isset($arContact['SECOND_NAME']) ? $arContact['SECOND_NAME'] : ''
				),
				false, false
			);

			$arFields['CLIENT_SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
				$arParams['CONTACT_SHOW_URL_TEMPLATE'],
				array('contact_id' => $commOwnerID)
			);

			$arFields['CLIENT_IMAGE_URL'] = SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_contact_big.png?ver=1';
			$imageID = isset($arContact['PHOTO']) ? intval($arContact['PHOTO']) : 0;
			if($imageID > 0)
			{
				$imageInfo = CFile::ResizeImageGet(
					$imageID, array('width' => 55, 'height' => 55), BX_RESIZE_IMAGE_EXACT);
				if($imageInfo && isset($imageInfo['src']))
				{
					$arFields['CLIENT_IMAGE_URL'] = $imageInfo['src'];
				}
			}

			$arFields['CLIENT_LEGEND'] = isset($arContact['POST']) ? $arContact['POST'] : '';
			$company = isset($arContact['COMPANY_ID']) ? intval($arContact['COMPANY_ID']) : 0;
			if($company > 0)
			{
				$arFields['CLIENT_COMPANY_TITLE'] = isset($arContact['COMPANY_TITLE']) ? $arContact['COMPANY_TITLE'] : '';
				$arFields['CLIENT_COMPANY_SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
					$arParams['COMPANY_SHOW_URL_TEMPLATE'],
					array('company_id' => $company)
				);
			}

			$arMultiFields = array();
			$dbMultiFields = CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array('ENTITY_ID' => 'CONTACT', 'ELEMENT_ID' => $commOwnerID)
			);
			if($dbMultiFields)
			{
				while($multiFields = $dbMultiFields->Fetch())
				{
					$arMultiFields[$multiFields['TYPE_ID']][] = array('VALUE' => $multiFields['VALUE'], 'VALUE_TYPE' => $multiFields['VALUE_TYPE']);
				}
			}

			$arFields['CLIENT_CALLTO'] = CCrmMobileHelper::PrepareCalltoParams(
				array(
					'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
					'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
					'ENTITY_ID' => $commOwnerID,
					'FM' => $arMultiFields
				)
			);

			$arFields['CLIENT_MAILTO'] = CCrmMobileHelper::PrepareMailtoParams(
				array(
					'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
					'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
					'ENTITY_ID' => $commOwnerID,
					'FM' => $arMultiFields
				)
			);
		}
	}
	elseif($commOwnerTypeID === CCrmOwnerType::Lead)
	{
		$dbRes = CCrmLead::GetListEx(
			array(),
			array('=ID' => $commOwnerID),
			false,
			false,
			array('NAME', 'LAST_NAME', 'SECOND_NAME', 'POST')
		);
		$arLead = $dbRes ? $dbRes->Fetch() : null;
		if($arLead)
		{
			$arFields['CLIENT_TITLE'] = CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => '',
					'NAME' => isset($arLead['NAME']) ? $arLead['NAME'] : '',
					'LAST_NAME' => isset($arLead['LAST_NAME']) ? $arLead['LAST_NAME'] : '',
					'SECOND_NAME' => isset($arLead['SECOND_NAME']) ? $arLead['SECOND_NAME'] : ''
				),
				false, false
			);

			$arFields['CLIENT_SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
				$arParams['LEAD_SHOW_URL_TEMPLATE'],
				array('lead_id' => $commOwnerID)
			);

			$arFields['CLIENT_IMAGE_URL'] = SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_lead_big.png?ver=1';
			$arFields['CLIENT_LEGEND'] = isset($arLead['POST']) ? $arLead['POST'] : '';

			$arMultiFields = array();
			$dbMultiFields = CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array('ENTITY_ID' => 'LEAD', 'ELEMENT_ID' => $commOwnerID)
			);

			if($dbMultiFields)
			{
				while($multiFields = $dbMultiFields->Fetch())
				{
					$arMultiFields[$multiFields['TYPE_ID']][] = array('VALUE' => $multiFields['VALUE'], 'VALUE_TYPE' => $multiFields['VALUE_TYPE']);
				}
			}

			$arFields['CLIENT_CALLTO'] = CCrmMobileHelper::PrepareCalltoParams(
				array(
					'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
					'ENTITY_TYPE_ID' => CCrmOwnerType::Lead,
					'ENTITY_ID' => $commOwnerID,
					'FM' => $arMultiFields
				)
			);

			$arFields['CLIENT_MAILTO'] = CCrmMobileHelper::PrepareMailtoParams(
				array(
					'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
					'ENTITY_TYPE_ID' => CCrmOwnerType::Lead,
					'ENTITY_ID' => $commOwnerID,
					'FM' => $arMultiFields
				)
			);
		}
	}
}

$storageTypeID = $arFields['STORAGE_TYPE_ID'];
$arFields['FILES'] = array();
$arFields['WEBDAV_ELEMENTS'] = array();

if($storageTypeID === CCrmActivityStorageType::File)
{
	CCrmActivity::PrepareStorageElementIDs($arFields);
	$arFileID = $arFields['STORAGE_ELEMENT_IDS'];
	$fileCount = is_array($arFileID) ? count($arFileID) : 0;
	for($i = 0; $i < $fileCount; $i++)
	{
		if(is_array($arData = CFile::GetFileArray($arFileID[$i])))
		{
			$arFields['FILES'][] = array(
				'fileID' => $arData['ID'],
				'fileName' => $arData['FILE_NAME'],
				'fileURL' => CCrmUrlUtil::UrnEncode($arData['SRC']), // Cyrilic characters must be encoded
				'fileSize' => CFile::FormatSize($arData['FILE_SIZE'])
			);
		}
	}
}
elseif($storageTypeID === CCrmActivityStorageType::WebDav)
{
	CCrmActivity::PrepareStorageElementIDs($arFields);
	$arElementID = $arFields['STORAGE_ELEMENT_IDS'];
	$elementCount = is_array($arElementID) ? count($arElementID) : 0;
	for($i = 0; $i < $elementCount; $i++)
	{
		$arFields['WEBDAV_ELEMENTS'][] = CCrmWebDavHelper::GetElementInfo($arElementID[$i]);
	}
}

$arResult['ENTITY'] = &$arFields;
unset($arFields);

$this->IncludeComponentTemplate();
