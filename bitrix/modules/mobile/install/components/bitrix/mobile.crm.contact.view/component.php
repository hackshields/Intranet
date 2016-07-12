<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

$userPerms = CCrmPerms::GetCurrentUserPermissions();
if ($userPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE, 'READ'))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

global $APPLICATION;

$arParams['ACTIVITY_LIST_URL_TEMPLATE'] =  isset($arParams['ACTIVITY_LIST_URL_TEMPLATE']) ? $arParams['ACTIVITY_LIST_URL_TEMPLATE'] : '';
$arParams['COMMUNICATION_LIST_URL_TEMPLATE'] =  isset($arParams['COMMUNICATION_LIST_URL_TEMPLATE']) ? $arParams['COMMUNICATION_LIST_URL_TEMPLATE'] : '';
$arParams['EVENT_LIST_URL_TEMPLATE'] =  isset($arParams['EVENT_LIST_URL_TEMPLATE']) ? $arParams['EVENT_LIST_URL_TEMPLATE'] : '';
$arParams['DEAL_LIST_URL_TEMPLATE'] =  isset($arParams['DEAL_LIST_URL_TEMPLATE']) ? $arParams['DEAL_LIST_URL_TEMPLATE'] : '';
$arParams['USER_PROFILE_URL_TEMPLATE'] = isset($arParams['USER_PROFILE_URL_TEMPLATE']) ? $arParams['USER_PROFILE_URL_TEMPLATE'] : '';;

$entityID = $arParams['ENTITY_ID'] = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
if($entityID <= 0 && isset($_GET['contact_id']))
{
	$entityID = $arParams['ENTITY_ID'] = intval($_GET['contact_id']);
}
$arResult['ENTITY_ID'] = $entityID;

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array('#NOBR#','#/NOBR#'), array('', ''), $arParams['NAME_TEMPLATE']);

$arResult['USER_ID'] = intval(CCrmSecurityHelper::GetCurrentUserID());
$arParams['UID'] = isset($arParams['UID']) ? $arParams['UID'] : '';
if(!isset($arParams['UID']) || $arParams['UID'] === '')
{
	$arParams['UID'] = 'mobile_crm_contact_view';
}
$arResult['UID'] = $arParams['UID'];

$dbFields = CCrmContact::GetListEx(array(), array('ID' => $entityID));
$arFields = $dbFields->GetNext();

if(!$arFields)
{
	ShowError(GetMessage('CRM_CONTACT_VIEW_NOT_FOUND', array('#ID#' => $arParams['ENTITY_ID'])));
	return;
}

$arFields['~NAME'] = isset($arFields['~NAME']) ? $arFields['~NAME'] : '';
$arFields['~LAST_NAME'] = isset($arFields['~LAST_NAME']) ? $arFields['~LAST_NAME'] : '';
$arFields['~SECOND_NAME'] = isset($arFields['~SECOND_NAME']) ? $arFields['~SECOND_NAME'] : '';
$arFields['~POST'] = isset($arFields['~POST']) ? $arFields['~POST'] : '';
$arFields['~PHOTO'] = isset($arFields['~PHOTO']) ? intval($arFields['~PHOTO']) : 0;

$arFields['~COMPANY_ID'] = isset($arFields['~COMPANY_ID']) ? intval($arFields['~COMPANY_ID']) : 0;
$arFields['~COMPANY_TITLE'] = isset($arFields['~COMPANY_TITLE']) ? $arFields['~COMPANY_TITLE'] : '';

$arFields['~ASSIGNED_BY_ID'] = isset($arFields['~ASSIGNED_BY_ID']) ? intval($arFields['~ASSIGNED_BY_ID']) : 0;
$arFields['~ASSIGNED_BY_LOGIN'] = isset($arFields['~ASSIGNED_BY_LOGIN']) ? $arFields['~ASSIGNED_BY_LOGIN'] : '';
$arFields['~ASSIGNED_BY_NAME'] = isset($arFields['~ASSIGNED_BY_NAME']) ? $arFields['~ASSIGNED_BY_NAME'] : '';
$arFields['~ASSIGNED_BY_LAST_NAME'] = isset($arFields['~ASSIGNED_BY_LAST_NAME']) ? $arFields['~ASSIGNED_BY_LAST_NAME'] : '';
$arFields['~ASSIGNED_BY_SECOND_NAME'] = isset($arFields['~ASSIGNED_BY_SECOND_NAME']) ? $arFields['~ASSIGNED_BY_SECOND_NAME'] : '';

$arFields['~TYPE_ID'] = isset($arFields['~TYPE_ID']) ? $arFields['~TYPE_ID'] : '';
$arFields['~ADDRESS'] = isset($arFields['~ADDRESS']) ? $arFields['~ADDRESS'] : '';
$arFields['~SOURCE_ID'] = isset($arFields['~SOURCE_ID']) ? $arFields['~SOURCE_ID'] : '';
$arFields['~SOURCE_DESCRIPTION'] = isset($arFields['~SOURCE_DESCRIPTION']) ? $arFields['~SOURCE_DESCRIPTION'] : '';

$arFields['~COMMENTS'] = isset($arFields['~COMMENTS']) ? $arFields['~COMMENTS'] : '';

$arFields['FM'] = array();
$dbMultiFields = CCrmFieldMulti::GetList(
	array('ID' => 'asc'),
	array('ENTITY_ID' => 'CONTACT', 'ELEMENT_ID' => $entityID)
);
if($dbMultiFields)
{
	while($multiFields = $dbMultiFields->Fetch())
	{
		$arFields['FM'][$multiFields['TYPE_ID']][] = array('VALUE' => $multiFields['VALUE'], 'VALUE_TYPE' => $multiFields['VALUE_TYPE']);
	}
}

$arResult['CALLTO'] = CCrmMobileHelper::PrepareCalltoParams(
	array(
		'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
		'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
		'ENTITY_ID' => $entityID,
		'FM' => $arFields['FM']
	)
);

$arResult['MAILTO'] = CCrmMobileHelper::PrepareMailtoParams(
	array(
		'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
		'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
		'ENTITY_ID' => $entityID,
		'FM' => $arFields['FM']
	)
);

$arResult['TYPE_LIST'] = CCrmStatus::GetStatusListEx('CONTACT_TYPE');
$arResult['SOURCE_LIST'] = CCrmStatus::GetStatusListEx('SOURCE');

$arFields['~FORMATTED_NAME'] = CUser::FormatName(
	$arParams['NAME_TEMPLATE'],
	array(
		'LOGIN' => '',
		'NAME' => $arFields['~NAME'],
		'LAST_NAME' => $arFields['~LAST_NAME'],
		'SECOND_NAME' => $arFields['~SECOND_NAME']
	),
	false, false
);

$arFields['FORMATTED_NAME'] = htmlspecialcharsbx($arFields['~FORMATTED_NAME']);

$arFields['ASSIGNED_BY_SHOW_URL'] = '';
$arFields['~ASSIGNED_BY_FORMATTED_NAME'] = '';

if($arFields['~ASSIGNED_BY_ID'] <= 0)
{
	$arFields['~ASSIGNED_BY_FORMATTED_NAME'] = GetMessage('CRM_CONTACT_VIEW_RESPONSIBLE_NOT_ASSIGNED');
}
else
{
	$arFields['ASSIGNED_BY_SHOW_URL'] = $arParams['USER_PROFILE_URL_TEMPLATE'] !== ''
		? CComponentEngine::MakePathFromTemplate(
			$arParams['USER_PROFILE_URL_TEMPLATE'],
			array('user_id' => $arFields['~ASSIGNED_BY_ID'])
		) : '';

	$arFields['~ASSIGNED_BY_FORMATTED_NAME'] = CUser::FormatName(
			$arParams['NAME_TEMPLATE'],
			array(
				'LOGIN' => $arFields['~ASSIGNED_BY_LOGIN'],
				'NAME' => $arFields['~ASSIGNED_BY_NAME'],
				'LAST_NAME' => $arFields['~ASSIGNED_BY_LAST_NAME'],
				'SECOND_NAME' => $arFields['~ASSIGNED_BY_SECOND_NAME']
			),
			true, false
		);
}

$arFields['ASSIGNED_BY_FORMATTED_NAME'] = htmlspecialcharsbx($arFields['~ASSIGNED_BY_FORMATTED_NAME']);

$arFields['ACTITITY_QUANTITY'] = CAllCrmActivity::GetCount(
	array(
		'BINDINGS' => array(
			array(
				'OWNER_TYPE_ID' => CCrmOwnerType::Contact,
				'OWNER_ID' => $entityID
			)
		)
	)
);

$arFields['ACTIVITY_LIST_URL'] =  $arParams['ACTIVITY_LIST_URL_TEMPLATE'] !== ''
	? CComponentEngine::MakePathFromTemplate(
		$arParams['ACTIVITY_LIST_URL_TEMPLATE'],
		array('entity_type_id' => CCrmOwnerType::Contact, 'entity_id' => $entityID)
	) : '';

$arFields['DEAL_QUANTITY'] = CAllCrmDeal::GetCount(array('CONTACT_ID' => $entityID));

$arFields['DEAL_LIST_URL'] =  $arParams['DEAL_LIST_URL_TEMPLATE'] !== ''
	? CComponentEngine::MakePathFromTemplate(
		$arParams['DEAL_LIST_URL_TEMPLATE'],
		array('contact_id' => $entityID)
	) : '';

$arFields['EVENT_LIST_URL'] =  $arParams['EVENT_LIST_URL_TEMPLATE'] !== ''
	? CComponentEngine::MakePathFromTemplate(
		$arParams['EVENT_LIST_URL_TEMPLATE'],
		array('entity_type_id' => CCrmOwnerType::Contact, 'entity_id' => $entityID)
	) : '';

$typeID = isset($arFields['~TYPE_ID']) ? $arFields['~TYPE_ID'] : '';
$arFields['~TYPE_NAME'] = $typeID !== '' && isset($arResult['TYPE_LIST'][$typeID])
	? $arResult['TYPE_LIST'][$typeID] : '';
$arFields['TYPE_NAME'] = htmlspecialcharsbx($arFields['~TYPE_NAME']);

$sourceID = isset($arFields['~SOURCE_ID']) ? $arFields['~SOURCE_ID'] : '';
$arFields['~SOURCE_NAME'] = $sourceID !== '' && isset($arResult['SOURCE_LIST'][$sourceID])
	? $arResult['SOURCE_LIST'][$sourceID] : '';
$arFields['SOURCE_NAME'] = htmlspecialcharsbx($arFields['~SOURCE_NAME']);

$arResult['ENTITY'] = &$arFields;
unset($arFields);

$this->IncludeComponentTemplate();



