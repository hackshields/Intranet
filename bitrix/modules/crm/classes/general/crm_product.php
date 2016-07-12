<?php
if (!CModule::IncludeModule('iblock'))
{
	return false;
}

IncludeModuleLangFile(__FILE__);

/*
 * CRM Product.
 * It is based on IBlock module.
 * */
class CAllCrmProduct
{
	const CACHE_NAME = 'CRM_CATALOG_PRODUCT_CACHE';
	const TABLE_ALIAS = 'P';
	protected static $LAST_ERROR = '';
	// CRUD -->
	public static function Add($arFields)
	{
		global $DB;

		if (!isset($arFields['ID']))
		{
			//Try to create a CIBlockElement
			$element =  new CIBlockElement();
			$arElement = array();

			if(isset($arFields['NAME']))
			{
				$arElement['NAME'] = $arFields['NAME'];
			}

			if(isset($arFields['SORT']))
			{
				$arElement['SORT'] = $arFields['SORT'];
			}

			if(isset($arFields['ACTIVE']))
			{
				$arElement['ACTIVE'] = $arFields['ACTIVE'];
			}

			if(isset($arFields['DESCRIPTION']))
			{
				$arElement['PREVIEW_TEXT'] = $arFields['DESCRIPTION'];
			}

			if(isset($arFields['CATALOG_ID']))
			{
				$arElement['IBLOCK_ID'] = intval($arFields['CATALOG_ID']);
			}
			else
			{
				$arElement['IBLOCK_ID'] = $arFields['CATALOG_ID'] = CCrmCatalog::EnsureDefaultExists();
			}

			if(isset($arFields['SECTION_ID']))
			{
				$arElement['IBLOCK_SECTION_ID'] = $arFields['SECTION_ID'];
			}

			if(!$element->CheckFields($arElement))
			{
				self::RegisterError($element->LAST_ERROR);
				return false;
			}

			$ID = intval($element->Add($arElement));
			$arFields['ID'] = $ID;
		}

		if (!self::CheckFields('ADD', $arFields, 0))
		{
			return false;
		}

		$arInsert = $DB->PrepareInsert(CCrmProduct::TABLE_NAME, $arFields);
		$sQuery =
			'INSERT INTO '.CCrmProduct::TABLE_NAME.'('.$arInsert[0].') VALUES('.$arInsert[1].')';
		$DB->Query($sQuery, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);

		return $ID;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		if (!self::CheckFields('UPDATE', $arFields, $ID))
		{
			return false;
		}

		if(isset($arFields['NAME'])
			|| isset($arFields['SECTION_ID'])
			|| isset($arFields['SORT'])
			|| isset($arFields['ACTIVE'])
			|| isset($arFields['DESCRIPTION']))
		{
			$element =  new CIBlockElement();
			$obResult = $element->GetById($ID);
			if($arElement = $obResult->Fetch())
			{
				if(isset($arFields['NAME']))
				{
					$arElement['NAME'] = $arFields['NAME'];
				}

				if(isset($arFields['SECTION_ID']))
				{
					$arElement['IBLOCK_SECTION_ID'] = $arFields['SECTION_ID'];
				}

				if(isset($arFields['SORT']))
				{
					$arElement['SORT'] = $arFields['SORT'];
				}

				if(isset($arFields['ACTIVE']))
				{
					$arElement['ACTIVE'] = $arFields['ACTIVE'];
				}

				if(isset($arFields['DESCRIPTION']))
				{
					$arElement['PREVIEW_TEXT'] = $arFields['DESCRIPTION'];
				}

				$element->Update($ID, $arElement);
			}
		}

		$sUpdate = trim($DB->PrepareUpdate(CCrmProduct::TABLE_NAME, $arFields));
		if (!empty($sUpdate))
		{
			$sQuery = 'UPDATE '.CCrmProduct::TABLE_NAME.' SET '.$sUpdate.' WHERE ID = '.$ID;
			$DB->Query($sQuery, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);

			CCrmEntityHelper::RemoveCached(self::CACHE_NAME, $ID);
		}

		return true;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);

		$arProduct = self::GetByID($ID);
		if(!is_array($arProduct))
		{
			// Is no exists
			return true;
		}

		$rowsCount = CCrmProductRow::GetList(array(), array('PRODUCT_ID' => $ID), array(), false, array());
		if($rowsCount > 0)
		{
			self::RegisterError(GetMessage('CRM_COULD_NOT_DELETE_PRODUCT_ROWS_EXIST', array('#NAME#' => $arProduct['~NAME'])));
			return false;
		}

		$events = GetModuleEvents('crm', 'OnBeforeCrmProductDelete');
		while ($arEvent = $events->Fetch())
		{
			if (ExecuteModuleEventEx($arEvent, array($ID)) === false)
			{
				return false;
			}
		}

		//$DB->StartTransaction();
		//$APPLICATION->ResetException();


		$sql = 'DELETE FROM '.CCrmProduct::TABLE_NAME.' WHERE ID = '.$ID;
		if(!$DB->Query($sql, true))
		{
			//$DB->Rollback();
			return false;
		}

		CCrmEntityHelper::RemoveCached(self::CACHE_NAME, $ID);

		if(self::IsIBlockElementExists($ID))
		{
			$element = new CIBlockElement();
			if(!$element->Delete($ID))
			{
				//$DB->Rollback();
				if ($ex = $APPLICATION->GetException())
				{
					self::RegisterError($ex->GetString());
				}
				return false;
			}
		}

		//$DB->Commit();
		$events = GetModuleEvents('crm', 'OnCrmProductDelete');
		while ($arEvent = $events->Fetch())
		{
			ExecuteModuleEventEx($arEvent, array($ID));
		}
		return true;
	}
	//<-- CRUD

	// Contract -->
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
        $lb = new CCrmEntityListBuilder(
            CCrmProduct::DB_TYPE,
            CCrmProduct::TABLE_NAME,
            self::TABLE_ALIAS,
            self::GetFields(),
            '',
            '',
            array()
        );

        return $lb->Prepare($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
	}

	public static function Exists($ID)
	{
		$dbRes = CCrmProduct::GetList(array(), array('ID'=> $ID), false, false, array('ID'));
		return $dbRes->Fetch() ? true : false;
	}

	public static function GetByID($ID)
	{
		$arResult = CCrmEntityHelper::GetCached(self::CACHE_NAME, $ID);
		if (is_array($arResult))
		{
			return $arResult;
		}

		$dbRes = CCrmProduct::GetList(array(), array('ID' => intval($ID)));
		$arResult = $dbRes->GetNext();

		if(is_array($arResult))
		{
			CCrmEntityHelper::SetCached(self::CACHE_NAME, $ID, $arResult);
		}
		return $arResult;
	}

    public static function GetByName($name)
    {
        $dbRes = CCrmProduct::GetList(array(), array('NAME' => strval($name)));
        return $dbRes->GetNext();
    }

	public static function GetByOriginID($originID, $catalogID = 0)
	{
		$catalogID = intval($catalogID);
		if($catalogID <= 0)
		{
			$catalogID = CCrmCatalog::GetDefaultID();
		}

		if($catalogID <= 0)
		{
			return false;
		}

		$dbRes = CCrmProduct::GetList(array(), array('CATALOG_ID' => $catalogID, 'ORIGIN_ID' => $originID));
		return ($dbRes->GetNext());
	}


	public static function FormatPrice($arProduct)
	{
		$price = isset($arProduct['PRICE']) ? round(doubleval($arProduct['PRICE']), 2) : 0.00;
		if($price == 0.00)
		{
			return '';
		}

		$currencyID = isset($arProduct['CURRENCY_ID']) ? strval($arProduct['CURRENCY_ID']) : '';
		return CCrmCurrency::MoneyToString($price, $currencyID);
	}

	public static function GetLastError()
	{
		return self::$LAST_ERROR;
	}
	//<-- Contract

	//Service -->
	protected static function GetFields()
	{
		return array(
			'ID' => array('FIELD' => 'P.ID', 'TYPE' => 'int'),
			'CATALOG_ID' => array('FIELD' => 'P.CATALOG_ID', 'TYPE' => 'int'),
			'PRICE' => array('FIELD' => 'P.PRICE', 'TYPE' => 'double'),
			'CURRENCY_ID' => array('FIELD' => 'P.CURRENCY_ID', 'TYPE' => 'string'),
			'ORIGINATOR_ID' => array('FIELD' => 'P.ORIGINATOR_ID', 'TYPE' => 'string'),
			'ORIGIN_ID' => array('FIELD' => 'P.ORIGIN_ID', 'TYPE' => 'string'),
			'NAME' => array('FIELD' => 'E.NAME', 'TYPE' => 'string', 'FROM' => 'INNER JOIN b_iblock_element E ON P.ID = E.ID'),
			'ACTIVE' => array('FIELD' => 'E.ACTIVE', 'TYPE' => 'char', 'FROM' => 'INNER JOIN b_iblock_element E ON P.ID = E.ID'),
			'SECTION_ID' => array('FIELD' => 'E.IBLOCK_SECTION_ID', 'TYPE' => 'int', 'FROM' => 'INNER JOIN b_iblock_element E ON P.ID = E.ID'),
			'DESCRIPTION' => array('FIELD' => 'E.PREVIEW_TEXT', 'TYPE' => 'string', 'FROM' => 'INNER JOIN b_iblock_element E ON P.ID = E.ID'),
			'SORT' => array('FIELD' => 'E.SORT', 'TYPE' => 'int', 'FROM' => 'INNER JOIN b_iblock_element E ON P.ID = E.ID')
		);
	}

	//Check fields before ADD and UPDATE.
	private static function CheckFields($sAction, &$arFields, $ID)
	{
		if($sAction == 'ADD')
		{
			if (!is_set($arFields, 'ID'))
			{
				self::RegisterError('Could not find ID. ID that is treated as a IBLOCK_ELEMENT_ID.');
				return false;
			}

			$elementID = intval($arFields['ID']);
			if($elementID <= 0)
			{
				self::RegisterError('ID that is treated as a IBLOCK_ELEMENT_ID is invalid.');
				return false;
			}

			if (!self::IsIBlockElementExists($elementID))
			{
				self::RegisterError("Could not find IBlockElement(ID = $elementID).");
				return false;
			}

			if (!is_set($arFields, 'CATALOG_ID'))
			{
				self::RegisterError('Could not find CATALOG_ID. CATALOG_ID that is treated as a IBLOCK_ID.');
				return false;
			}

			$blockID = intval($arFields['CATALOG_ID']);
			if($blockID <= 0)
			{
				self::RegisterError('CATALOG_ID that is treated as a IBLOCK_ID is invalid.');
				return false;
			}

			$blocks = CIBlock::GetList(array(), array('ID' => $blockID), false, false, array('ID'));
			if (!($blocks = $blocks->Fetch()))
			{
				self::RegisterError("Could not find IBlock(ID = $blockID).");
				return false;
			}
		}
		else//if($sAction == 'UPDATE')
		{
			if(!self::Exists($ID))
			{
				self::RegisterError("Could not find CrmProduct(ID = $ID).");
				return false;
			}
		}

		return true;
	}

	private static function RegisterError($msg)
	{
		global $APPLICATION;
		$APPLICATION->ThrowException(new CAdminException(array(array('text' => $msg))));
		self::$LAST_ERROR = $msg;
	}

	private static function IsIBlockElementExists($ID)
	{
		$rsElements = CIBlockElement::GetList(array(), array('ID' => $ID), false, array('nTopCount' => 1), array('ID'));
		return $rsElements->Fetch() ? true : false;
	}

	// <-- Service

	// Event handlers -->
	public static function OnIBlockElementDelete($ID)
	{
		return CCrmProduct::Delete($ID);
	}
	// <-- Event handlers
}
