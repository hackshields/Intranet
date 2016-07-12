<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

$userPerms = CCrmPerms::GetCurrentUserPermissions();
if ($userPerms->HavePerm('DEAL', BX_CRM_PERM_NONE, 'READ'))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

global $APPLICATION;

$arParams['ACTIVITY_LIST_URL_TEMPLATE'] =  isset($arParams['ACTIVITY_LIST_URL_TEMPLATE']) ? $arParams['ACTIVITY_LIST_URL_TEMPLATE'] : '';
$arParams['COMMUNICATION_LIST_URL_TEMPLATE'] =  isset($arParams['COMMUNICATION_LIST_URL_TEMPLATE']) ? $arParams['COMMUNICATION_LIST_URL_TEMPLATE'] : '';
$arParams['EVENT_LIST_URL_TEMPLATE'] =  isset($arParams['EVENT_LIST_URL_TEMPLATE']) ? $arParams['EVENT_LIST_URL_TEMPLATE'] : '';
$arParams['PRODUCT_ROW_LIST_URL_TEMPLATE'] =  isset($arParams['PRODUCT_ROW_LIST_URL_TEMPLATE']) ? $arParams['PRODUCT_ROW_LIST_URL_TEMPLATE'] : '';
$arParams['COMPANY_SHOW_URL_TEMPLATE'] = isset($arParams['COMPANY_SHOW_URL_TEMPLATE']) ? $arParams['COMPANY_SHOW_URL_TEMPLATE'] : '';
$arParams['CONTACT_SHOW_URL_TEMPLATE'] = isset($arParams['CONTACT_SHOW_URL_TEMPLATE']) ? $arParams['CONTACT_SHOW_URL_TEMPLATE'] : '';
$arParams['USER_PROFILE_URL_TEMPLATE'] = isset($arParams['USER_PROFILE_URL_TEMPLATE']) ? $arParams['USER_PROFILE_URL_TEMPLATE'] : '';

$entityID = $arParams['ENTITY_ID'] = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
if($entityID <= 0 && isset($_GET['deal_id']))
{
	$entityID = $arParams['ENTITY_ID'] = intval($_GET['deal_id']);
}
$arResult['ENTITY_ID'] = $entityID;

$arParams['NAME_TEMPLATE'] = empty($arParams['NAME_TEMPLATE']) ? CSite::GetNameFormat(false) : str_replace(array('#NOBR#','#/NOBR#'), array('', ''), $arParams['NAME_TEMPLATE']);

$arResult['USER_ID'] = intval(CCrmSecurityHelper::GetCurrentUserID());
$arParams['UID'] = isset($arParams['UID']) ? $arParams['UID'] : '';
if(!isset($arParams['UID']) || $arParams['UID'] === '')
{
	$arParams['UID'] = 'mobile_crm_deal_view';
}
$arResult['UID'] = $arParams['UID'];
$arResult['STAGE_LIST'] = CCrmStatus::GetStatusListEx('DEAL_STAGE');
$arResult['TYPE_LIST'] = CCrmStatus::GetStatusListEx('DEAL_TYPE');
$arResult['CURRENCY_LIST'] = CCrmCurrencyHelper::PrepareListItems();

$dbFields = CCrmDeal::GetListEx(array(), array('ID' => $entityID));
$arFields = $dbFields->GetNext();

if(!$arFields)
{
	ShowError(GetMessage('CRM_DEAL_VIEW_NOT_FOUND', array('#ID#' => $arParams['ENTITY_ID'])));
	return;
}

$arFields['~CONTACT_ID'] = isset($arFields['~CONTACT_ID']) ? intval($arFields['~CONTACT_ID']) : 0;
$arFields['~CONTACT_NAME'] = isset($arFields['~CONTACT_NAME']) ? $arFields['~CONTACT_NAME'] : '';
$arFields['~CONTACT_LAST_NAME'] = isset($arFields['~CONTACT_LAST_NAME']) ? $arFields['~CONTACT_LAST_NAME'] : '';
$arFields['~CONTACT_SECOND_NAME'] = isset($arFields['~CONTACT_SECOND_NAME']) ? $arFields['~CONTACT_SECOND_NAME'] : '';
$arFields['~CONTACT_POST'] = isset($arFields['~CONTACT_POST']) ? $arFields['~CONTACT_POST'] : '';
$arFields['~CONTACT_PHOTO'] = isset($arFields['~CONTACT_PHOTO']) ? intval($arFields['~CONTACT_PHOTO']) : 0;

$arFields['~COMPANY_ID'] = isset($arFields['~COMPANY_ID']) ? intval($arFields['~COMPANY_ID']) : 0;
$arFields['~COMPANY_TITLE'] = isset($arFields['~COMPANY_TITLE']) ? $arFields['~COMPANY_TITLE'] : '';
$arFields['~COMPANY_LOGO'] = isset($arFields['~COMPANY_LOGO']) ? intval($arFields['~COMPANY_LOGO']) : 0;

$arFields['~ASSIGNED_BY_ID'] = isset($arFields['~ASSIGNED_BY_ID']) ? intval($arFields['~ASSIGNED_BY_ID']) : 0;
$arFields['~ASSIGNED_BY_LOGIN'] = isset($arFields['~ASSIGNED_BY_LOGIN']) ? $arFields['~ASSIGNED_BY_LOGIN'] : '';
$arFields['~ASSIGNED_BY_NAME'] = isset($arFields['~ASSIGNED_BY_NAME']) ? $arFields['~ASSIGNED_BY_NAME'] : '';
$arFields['~ASSIGNED_BY_LAST_NAME'] = isset($arFields['~ASSIGNED_BY_LAST_NAME']) ? $arFields['~ASSIGNED_BY_LAST_NAME'] : '';
$arFields['~ASSIGNED_BY_SECOND_NAME'] = isset($arFields['~ASSIGNED_BY_SECOND_NAME']) ? $arFields['~ASSIGNED_BY_SECOND_NAME'] : '';

$arFields['~COMMENTS'] = isset($arFields['~COMMENTS']) ? $arFields['~COMMENTS'] : '';

if(!isset($arFields['~OPPORTUNITY']))
{
	$arFields['~OPPORTUNITY'] = $arFields['OPPORTUNITY'] = 0;
}

if(!isset($arFields['~CURRENCY_ID']))
{
	$arFields['~CURRENCY_ID'] =  CCrmCurrency::GetBaseCurrencyID();
	$arFields['CURRENCY_ID'] = htmlspecialcharsbx($arFields['~CURRENCY_ID']);
}

$contactID = $arFields['~CONTACT_ID'];
$companyID = $arFields['~COMPANY_ID'];

if($contactID > 0)
{
	$arFields['CONTACT_FM'] = array();
	$dbMultiFields = CCrmFieldMulti::GetList(
		array('ID' => 'asc'),
		array('ENTITY_ID' => 'CONTACT', 'ELEMENT_ID' => $contactID)
	);

	if($dbMultiFields)
	{
		while($multiFields = $dbMultiFields->Fetch())
		{
			$arFields['CONTACT_FM'][$multiFields['TYPE_ID']][] = array('VALUE' => $multiFields['VALUE'], 'VALUE_TYPE' => $multiFields['VALUE_TYPE']);
		}
	}

	$arResult['CONTACT_CALLTO'] = CCrmMobileHelper::PrepareCalltoParams(
		array(
			'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
			'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
			'ENTITY_ID' => $contactID,
			'FM' => $arFields['CONTACT_FM']
		)
	);

	$arResult['CONTACT_MAILTO'] = CCrmMobileHelper::PrepareMailtoParams(
		array(
			'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
			'ENTITY_TYPE_ID' => CCrmOwnerType::Contact,
			'ENTITY_ID' => $contactID,
			'FM' => $arFields['CONTACT_FM']
		)
	);
}
elseif($companyID > 0)
{
	$arFields['COMPANY_FM'] = array();
	$dbMultiFields = CCrmFieldMulti::GetList(
		array('ID' => 'asc'),
		array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $companyID)
	);

	if($dbMultiFields)
	{
		while($multiFields = $dbMultiFields->Fetch())
		{
			$arFields['COMPANY_FM'][$multiFields['TYPE_ID']][] = array('VALUE' => $multiFields['VALUE'], 'VALUE_TYPE' => $multiFields['VALUE_TYPE']);
		}
	}

	$arResult['COMPANY_CALLTO'] = CCrmMobileHelper::PrepareCalltoParams(
		array(
			'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
			'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
			'ENTITY_ID' => $companyID,
			'FM' => $arFields['COMPANY_FM']
		)
	);

	$arResult['COMPANY_MAILTO'] = CCrmMobileHelper::PrepareMailtoParams(
		array(
			'COMMUNICATION_LIST_URL_TEMPLATE' => $arParams['COMMUNICATION_LIST_URL_TEMPLATE'],
			'ENTITY_TYPE_ID' => CCrmOwnerType::Company,
			'ENTITY_ID' => $companyID,
			'FM' => $arFields['COMPANY_FM']
		)
	);
}

$arFields['CONTACT_SHOW_URL'] = $contactID > 0
	? CComponentEngine::MakePathFromTemplate(
		$arParams['CONTACT_SHOW_URL_TEMPLATE'], array('contact_id' => $contactID)
	) : '';

$arFields['COMPANY_SHOW_URL'] = $companyID > 0
	? CComponentEngine::MakePathFromTemplate(
		$arParams['COMPANY_SHOW_URL_TEMPLATE'], array('company_id' => $companyID)
	) : '';

$arFields['~STAGE_TEXT'] = isset($arFields['~STAGE_ID'])
	&& isset($arResult['STAGE_LIST'][$arFields['~STAGE_ID']])
	? $arResult['STAGE_LIST'][$arFields['~STAGE_ID']] : '';
$arFields['STAGE_TEXT'] = htmlspecialcharsbx($arFields['~STAGE_TEXT']);

$arFields['~TYPE_NAME'] = isset($arFields['~TYPE_ID'])
	&& isset($arResult['TYPE_LIST'][$arFields['~TYPE_ID']])
	? $arResult['TYPE_LIST'][$arFields['~TYPE_ID']] : '';
$arFields['TYPE_NAME'] = htmlspecialcharsbx($arFields['~TYPE_NAME']);

$arFields['~FORMATTED_OPPORTUNITY'] = CCrmCurrency::MoneyToString($arFields['~OPPORTUNITY'], $arFields['~CURRENCY_ID']);
$arFields['FORMATTED_OPPORTUNITY'] = htmlspecialcharsbx($arFields['~FORMATTED_OPPORTUNITY']);

$arFields['~CONTACT_FORMATTED_NAME'] = CUser::FormatName(
	$arParams['NAME_TEMPLATE'],
	array(
		'LOGIN' => '',
		'NAME' => $arFields['~CONTACT_NAME'],
		'LAST_NAME' => $arFields['~CONTACT_LAST_NAME'],
		'SECOND_NAME' => $arFields['~CONTACT_SECOND_NAME']
	),
	false,
	false
);

$arFields['CONTACT_FORMATTED_NAME'] = htmlspecialcharsbx($arFields['~CONTACT_FORMATTED_NAME']);

$arFields['ASSIGNED_BY_SHOW_URL'] = '';
$arFields['~ASSIGNED_BY_FORMATTED_NAME'] = '';
if($arFields['~ASSIGNED_BY_ID'] <= 0)
{
	$arFields['~ASSIGNED_BY_FORMATTED_NAME'] = GetMessage('CRM_DEAL_VIEW_RESPONSIBLE_NOT_ASSIGNED');
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

$arFields['PRODUCT_ROWS_QUANTITY'] = CAllCrmProductRow::GetRowQuantity(
	CCrmOwnerTypeAbbr::ResolveByTypeID(CCrmOwnerType::Deal),
	$entityID
);

$arFields['PRODUCT_ROWS_URL'] = $arParams['PRODUCT_ROW_LIST_URL_TEMPLATE'] !== ''
	? CComponentEngine::MakePathFromTemplate(
		$arParams['PRODUCT_ROW_LIST_URL_TEMPLATE'],
		array('entity_type_id' => CCrmOwnerType::Deal, 'entity_id' => $entityID)
	) : '';

$arFields['ACTITITY_QUANTITY'] = CAllCrmActivity::GetCount(
	array(
		'BINDINGS' => array(
			array(
				'OWNER_TYPE_ID' => CCrmOwnerType::Deal,
				'OWNER_ID' => $entityID
			)
		)
	)
);

$arFields['ACTIVITY_LIST_URL'] =  $arParams['ACTIVITY_LIST_URL_TEMPLATE'] !== ''
	? CComponentEngine::MakePathFromTemplate(
		$arParams['ACTIVITY_LIST_URL_TEMPLATE'],
		array('entity_type_id' => CCrmOwnerType::Deal, 'entity_id' => $entityID)
	) : '';

$arFields['EVENT_LIST_URL'] =  $arParams['EVENT_LIST_URL_TEMPLATE'] !== ''
	? CComponentEngine::MakePathFromTemplate(
		$arParams['EVENT_LIST_URL_TEMPLATE'],
		array('entity_type_id' => CCrmOwnerType::Deal, 'entity_id' => $entityID)
	) : '';

$arFields['~BEGINDATE'] = !empty($arFields['~BEGINDATE'])
	? CCrmComponentHelper::TrimDateTimeString(
		ConvertTimeStamp(MakeTimeStamp($arFields['~BEGINDATE']), 'SHORT', SITE_ID)) : '';
$arFields['BEGINDATE'] = htmlspecialcharsbx($arFields['~BEGINDATE']);

$arFields['~CLOSEDATE'] = !empty($arFields['~CLOSEDATE'])
	? CCrmComponentHelper::TrimDateTimeString(
		ConvertTimeStamp(MakeTimeStamp($arFields['~CLOSEDATE']), 'SHORT', SITE_ID)) : '';
$arFields['CLOSEDATE'] = htmlspecialcharsbx($arFields['~CLOSEDATE']);

$arResult['ENTITY'] = &$arFields;

unset($arFields);

$this->IncludeComponentTemplate();
