<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule('crm'))
{
	ShowError(GetMessage('CRM_MODULE_NOT_INSTALLED'));
	return;
}

global $APPLICATION;

$entityTypeID = $arParams['ENTITY_TYPE_ID'] = isset($arParams['ENTITY_TYPE_ID']) ? intval($arParams['ENTITY_TYPE_ID']) : 0;
if($entityTypeID <= 0 && isset($_GET['entity_type_id']))
{
	$entityTypeID = $arParams['ENTITY_TYPE_ID'] = intval($_GET['entity_type_id']);
}
$arResult['ENTITY_TYPE_ID'] = $entityTypeID;

$entityID = $arParams['ENTITY_ID'] = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
if($entityID <= 0 && isset($_GET['entity_id']))
{
	$entityID = $arParams['ENTITY_ID'] = intval($_GET['entity_id']);
}
$arResult['ENTITY_ID'] = $entityID;


if($entityTypeID <= CCrmOwnerType::Undefined)
{
	ShowError(GetMessage('CRM_PRODUCT_LIST_OWNER_TYPE_NOT_DEFINED'));
	return;
}

if($entityTypeID !== CCrmOwnerType::Deal && $entityTypeID !== CCrmOwnerType::Lead)
{
	ShowError(GetMessage('CRM_PRODUCT_LIST_OWNER_TYPE_NOT_SUPPORTED'));
	return;
}

if($entityID <= 0)
{
	ShowError(GetMessage('CRM_PRODUCT_LIST_OWNER_ID_NOT_DEFINED'));
	return;
}

$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);
$userPerms = CCrmPerms::GetCurrentUserPermissions();
if ($userPerms->HavePerm($entityTypeName, BX_CRM_PERM_NONE, 'READ'))
{
	ShowError(GetMessage('CRM_PERMISSION_DENIED'));
	return;
}

$arParams['UID'] = isset($arParams['UID']) ? $arParams['UID'] : '';
if(!isset($arParams['UID']) || $arParams['UID'] === '')
{
	$arParams['UID'] = 'mobile_crm_product_row_list';
}
$arResult['UID'] = $arParams['UID'];

$sort = array('ID' => 'ASC');

$filter = array(
	'OWNER_TYPE' => CCrmOwnerTypeAbbr::ResolveByTypeID($entityTypeID),
	'OWNER_ID' => $entityID
);

$select = array(
	'PRODUCT_ID', 'PRODUCT_NAME',
	'PRICE', 'QUANTITY'
);

$arOwner = null;
if($entityTypeID === CCrmOwnerType::Deal)
{
	$dbRes = CCrmDeal::GetListEx(
		array(), array('ID' => $entityID), false, false,
		array('TITLE', 'CURRENCY_ID', 'OPPORTUNITY')
	);

	if($dbRes)
	{
		$arOwner = $dbRes->Fetch();
	}
}
elseif($entityTypeID === CCrmOwnerType::Lead)
{
	$dbRes = CCrmLead::GetListEx(
		array(), array('ID' => $entityID), false, false,
		array('TITLE', 'CURRENCY_ID', 'OPPORTUNITY')
	);

	if($dbRes)
	{
		$arOwner = $dbRes->Fetch();
	}
}

if(is_array($arOwner))
{
	$arResult['TITLE'] = isset($arOwner['TITLE'])
		? $arOwner['TITLE'] : '';

	$arResult['CURRENCY_ID'] = isset($arOwner['CURRENCY_ID'])
		? $arOwner['CURRENCY_ID'] : CCrmCurrency::GetBaseCurrencyID();

	$arResult['OPPORTUNITY'] = isset($arOwner['OPPORTUNITY'])
		? $arOwner['OPPORTUNITY'] : 0.0;
}
else
{
	$arResult['TITLE'] = '';
	$arResult['CURRENCY_ID'] = CCrmCurrency::GetBaseCurrencyID();
	$arResult['OPPORTUNITY'] = 0.0;
}

$arResult['FORMATTED_OPPORTUNITY'] =
	CCrmCurrency::MoneyToString($arResult['OPPORTUNITY'], $arResult['CURRENCY_ID']);

$arResult['ITEMS'] = array();
$dbRes = CCrmProductRow::GetList($sort, $filter, false, false, $select);
while($arFields = $dbRes->GetNext())
{
	$item = array(
		'PRODUCT_NAME' => CCrmProductRow::GetProductName($arFields),
		'PRICE' => CCrmProductRow::GetPrice($arFields),
		'QUANTITY' => CCrmProductRow::GetQuantity($arFields)
	);
	$item['FORMATTED_PRICE'] = CCrmCurrency::MoneyToString($item['PRICE'], $arResult['CURRENCY_ID']);

	$arResult['ITEMS'][] = &$item;
	unset($item);
}

$this->IncludeComponentTemplate();
