<?php
if (!CModule::IncludeModule('iblock'))
{
	return false;
}

class CCrmProductHelper
{
	public static function PreparePopupItems($currencyID = '', $count = 50)
	{
		$currencyID = strval($currencyID);
		if(!isset($currencyID[0]))
		{
			$currencyID = CCrmCurrency::GetBaseCurrencyID();
		}

		$count = intval($count);
		if($count <= 0)
		{
			$count = 50;
		}

		$rs = CCrmProduct::GetList(
			array('ID' => 'DESC'),
			array(
                'ACTIVE' => 'Y'/*,
                'CATALOG_ID' => CCrmCatalog::EnsureDefaultExists()*/
            ),
			array('ID', 'NAME', 'PRICE', 'CURRENCY_ID'),
			$count
		);

		$result = array();
		while ($ar = $rs->Fetch())
		{
			if($currencyID != $ar['CURRENCY_ID'])
			{
				$ar['PRICE'] = CCrmCurrency::ConvertMoney($ar['PRICE'], $ar['CURRENCY_ID'], $currencyID);
				$ar['CURRENCY_ID'] = $currencyID;
			}

			$result[] = array(
				'title' => $ar['NAME'],
				'desc' => CCrmProduct::FormatPrice($ar),
				'id' => $ar['ID'],
				'url' => '',
				'type'  => 'product',
				'selected' => false,
				'customData' => array('price' => $ar['PRICE'])
			);
		}

		return $result;
	}

	public static function PrepareCatalogListItems($addNotSelected = true)
	{
		IncludeModuleLangFile(__FILE__);

		$result = array();
		if($addNotSelected)
		{
			$result['0'] = GetMessage('CRM_PRODUCT_CATALOG_NOT_SELECTED');
		}

		$rs = CCrmCatalog::GetList(
			array('NAME' => 'ASC'),
			array(),
			array('ID', 'NAME')
		);

		while ($ar = $rs->Fetch())
		{
			$result[$ar['ID']] = $ar['NAME'];
		}

		return $result;
	}

	public static function PrepareListItems($catalogID = 0)
	{
		$catalogID = intval($catalogID);
		$result = array();
		$filter = array('ACTIVE' => 'Y');
		if($catalogID > 0)
		{
			$filter['CATALOG_ID'] = $catalogID;
		}

		$rs = CCrmProduct::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			$filter,
			array('ID', 'NAME')
		);

		while ($ar = $rs->Fetch())
		{
			$result[$ar['ID']] = $ar['NAME'];
		}

		return $result;
	}

	public static function PrepareSectionListItems($catalogID, $addNotSelected = true)
	{
		IncludeModuleLangFile(__FILE__);

		$result = array();

		if($addNotSelected)
		{
			$result['0'] = GetMessage('CRM_PRODUCT_SECTION_NOT_SELECTED');
		}

		$rs = CIBlockSection::GetList(
			array('left_margin' => 'asc'),
			array(
				'IBLOCK_ID' => $catalogID,
				'GLOBAL_ACTIVE' => 'Y',
				'CHECK_PERMISSIONS' => 'N'
			),
			false,
			array(
				'ID',
				'NAME',
				'DEPTH_LEVEL'
			)
		);

		while($ary = $rs->GetNext())
		{
			$result[$ary['ID']] = str_repeat(' . ', $ary['DEPTH_LEVEL']).$ary['~NAME'];
		}

		return $result;
	}
}
