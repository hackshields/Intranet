<?php
class CAllCrmProductRow
{
	const CACHE_NAME = 'CRM_PRODUCT_ROW_CACHE';
	const TABLE_ALIAS = 'PR';
	protected static $LAST_ERROR = '';

	// CRUD -->
	public static function Add($arFields, $checkPerms = true, $regEvent = true)
	{
		global $DB;

		if (!self::CheckFields('ADD', $arFields, 0))
		{
			return false;
		}

		$ownerType = isset($arFields['OWNER_TYPE']) ? strval($arFields['OWNER_TYPE']) : '';
		$ownerID = isset($arFields['OWNER_ID']) ? intval($arFields['OWNER_ID']) : 0;

		if($ownerType !== '' && $ownerID > 0)
		{
			$accContext = self::PrepareAccountingContext($ownerType, $ownerID);
			if(isset($accContext['CURRENCY_ID']))
			{
				$arFields['CURRENCY_ID'] = $accContext['CURRENCY_ID'];
			}

			if(isset($accContext['EXCH_RATE']))
			{
				$arFields['EXCH_RATE'] = $accContext['EXCH_RATE'];
			}
		}

		// Calculation of Account Data
		if(isset($arFields['CURRENCY_ID']))
		{
			$accData = CCrmAccountingHelper::PrepareAccountingData(
				array(
					'CURRENCY_ID' => $arFields['CURRENCY_ID'],
					'SUM' => isset($arFields['PRICE']) ? $arFields['PRICE'] : null,
					'EXCH_RATE' => isset($arFields['EXCH_RATE']) ? $arFields['EXCH_RATE'] : null
				)
			);

			if(is_array($accData))
			{
				$arFields['PRICE_ACCOUNT'] = $accData['ACCOUNT_SUM'];
			}
		}

		$ID = $DB->Add(CCrmProductRow::TABLE_NAME, $arFields);
		if($ID === false)
		{
			self::RegisterError('DB connection was lost');
		}
		else
		{
			$arFields['ID'] = $ID;
			self::SynchronizeOwner($ownerType, $ownerID);

			if($regEvent)
			{
				self::RegisterAddEvent($ownerType, $ownerID, $arFields, $checkPerms);
			}
		}

		return $ID;
	}

	public static function Update($ID, $arFields, $checkPerms = true, $regEvent = true)
	{
		global $DB;

		if (!self::CheckFields('UPDATE', $arFields, $ID))
		{
			return false;
		}

		$arParams = self::GetByID($ID);
		if(!is_array($arParams))
		{
			self::RegisterError("Could not find CrmProductRow '$ID'!");
			return false;
		}

		$ownerType = isset($arFields['OWNER_TYPE']) ? strval($arFields['OWNER_TYPE']) : isset($arParams['OWNER_TYPE']) ? strval($arParams['OWNER_TYPE']) : '';
		$ownerID = isset($arFields['OWNER_ID']) ? intval($arFields['OWNER_ID']) : isset($arParams['OWNER_ID']) ? intval($arParams['OWNER_ID']) : 0;

		if($ownerType !== '' && $ownerID > 0)
		{
			$accContext = self::PrepareAccountingContext($ownerType, $ownerID);
			if(isset($accContext['CURRENCY_ID']))
			{
				$arFields['CURRENCY_ID'] = $accContext['CURRENCY_ID'];
			}

			if(isset($accContext['EXCH_RATE']))
			{
				$arFields['EXCH_RATE'] = $accContext['EXCH_RATE'];
			}
		}

		// Calculation of Account Data
		if(isset($arFields['CURRENCY_ID']))
		{
			$accData = CCrmAccountingHelper::PrepareAccountingData(
				array(
					'CURRENCY_ID' => $arFields['CURRENCY_ID'],
					'SUM' => isset($arFields['PRICE']) ? $arFields['PRICE'] : null,
					'EXCH_RATE' => isset($arFields['EXCH_RATE']) ? $arFields['EXCH_RATE'] : null
				)
			);

			if(is_array($accData))
			{
				$arFields['PRICE_ACCOUNT'] = $accData['ACCOUNT_SUM'];
			}
		}

		$sUpdate = trim($DB->PrepareUpdate(CCrmProductRow::TABLE_NAME, $arFields));
		if (!empty($sUpdate))
		{
			$sQuery = 'UPDATE '.CCrmProductRow::TABLE_NAME.' SET '.$sUpdate.' WHERE ID = '.$ID;
			$DB->Query($sQuery, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);

			CCrmEntityHelper::RemoveCached(self::CACHE_NAME, $ID);
		}

		if(isset($ownerType[0]) && $ownerID > 0)
		{
			self::SynchronizeOwner($ownerType,$ownerID);

			if($regEvent)
			{
				self::RegisterUpdateEvent($ownerType, $ownerID, $arFields, $arParams, $checkPerms);
			}
		}
		return true;
	}

	public static function Delete($ID, $checkPerms = true, $regEvent = true)
	{
		global $DB;

		$ID = intval($ID);
		$arParams = self::GetByID($ID);
		if(!is_array($arParams))
		{
			self::RegisterError("Could not find CrmProductRow($ID).");
			return false;
		}

		if(!$DB->Query('DELETE FROM '.CCrmProductRow::TABLE_NAME.' WHERE ID = '.$ID, true))
		{
			self::RegisterError("Could not delete CrmProductRow($ID).");
			return false;
		}

		CCrmEntityHelper::RemoveCached(self::CACHE_NAME, $ID);
		if(isset($arParams['OWNER_TYPE']) && isset($arParams['OWNER_ID']))
		{
			self::SynchronizeOwner($arParams['OWNER_TYPE'], $arParams['OWNER_ID']);

			if($regEvent)
			{
				self::RegisterRemoveEvent($arParams['OWNER_TYPE'], $arParams['OWNER_ID'], $arParams, $checkPerms);
			}
		}

		return true;
	}
	// <-- CRUD

	// Service -->
	protected static function GetFields()
	{
		return array(
			'ID' => array('FIELD' => 'PR.ID', 'TYPE' => 'int'),
			'OWNER_ID' => array('FIELD' => 'PR.OWNER_ID', 'TYPE' => 'int'),
			'OWNER_TYPE' => array('FIELD' => 'PR.OWNER_TYPE', 'TYPE' => 'string'),
			'PRODUCT_ID' => array('FIELD' => 'PR.PRODUCT_ID', 'TYPE' => 'int'),
			'PRODUCT_NAME' => array('FIELD' => 'E.NAME', 'TYPE' => 'string', 'FROM' => 'INNER JOIN b_iblock_element E ON PR.PRODUCT_ID = E.ID'),
			'PRODUCT_DESCRIPTION' => array('FIELD' => 'E.PREVIEW_TEXT', 'TYPE' => 'string', 'FROM' => 'INNER JOIN b_iblock_element E ON PR.PRODUCT_ID = E.ID'),
			'PRICE' => array('FIELD' => 'PR.PRICE', 'TYPE' => 'double'),
			'PRICE_ACCOUNT' => array('FIELD' => 'PR.PRICE_ACCOUNT', 'TYPE' => 'double'),
			'QUANTITY' => array('FIELD' => 'PR.QUANTITY', 'TYPE' => 'int')
			//'COMMENT' => array('FIELD' => 'PR.COMMENT', 'TYPE' => 'string')
		);
	}

	//Check fields before ADD and UPDATE.
	private static function CheckFields($sAction, &$arFields, $ID)
	{
		if($sAction == 'ADD')
		{
			if (!isset($arFields['OWNER_ID']))
			{
				self::RegisterError('Could not find Owner ID.');
				return false;
			}

			if (!isset($arFields['OWNER_TYPE']))
			{
				self::RegisterError('Could not find Owner Type.');
				return false;
			}

			if (!isset($arFields['PRODUCT_ID']))
			{
				self::RegisterError('Could not find Product ID.');
				return false;
			}

			if (!isset($arFields['PRICE']))
			{
				self::RegisterError('Could not find Price.');
				return false;
			}

			if (!isset($arFields['QUANTITY']))
			{
				self::RegisterError('Could not find Quantity.');
				return false;
			}
		}
		else//if($sAction == 'UPDATE')
		{
			if(!self::Exists($ID))
			{
				self::RegisterError("Could not find Product Row($ID).");
				return false;
			}
		}

		return true;
	}

	protected static function SynchronizeOwner($ownerType, $ownerID, $checkPerms = true)
	{
		$ownerType = strtoupper(strval($ownerType));
		$ownerID = intval($ownerID);

		if($ownerType === 'D')
		{
			CCrmDeal::SynchronizeProductRows($ownerID, $checkPerms);
		}
		elseif($ownerType === 'L')
		{
			CCrmLead::SynchronizeProductRows($ownerID);
		}
	}

	protected static function RegisterError($msg)
	{
		global $APPLICATION;
		$APPLICATION->ThrowException(new CAdminException(array(array('text' => $msg))));
		self::$LAST_ERROR = $msg;
	}
	// <-- Service

	// Contract -->
	public static function GetList($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		$lb = new CCrmEntityListBuilder(
			CCrmProductRow::DB_TYPE,
			CCrmProductRow::TABLE_NAME,
			self::TABLE_ALIAS,
			self::GetFields(),
			'',
			'',
			array()
		);

		return $lb->Prepare($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields);
	}

	public static function GetRowQuantity($ownerType, $ownerID)
	{
		$ownerType = strval($ownerType);
		$ownerID = intval($ownerID);

		return $ownerType !== '' && $ownerID > 0
			? self::GetList(array(), array('OWNER_TYPE' => $ownerType, 'OWNER_ID' => $ownerID), array())
			: 0;
	}

	public static function LoadRows($ownerType, $ownerID, $assoc = false)
	{
		$ownerType = strval($ownerType);
		$filter = array();

		if(isset($ownerType[0]))
		{
			$filter['OWNER_TYPE'] = $ownerType;
		}

		if(is_array($ownerID))
		{
			if(count($ownerID) > 0)
			{
				$filter['@OWNER_ID'] = $ownerID;
			}
		}
		else
		{
			$ownerID = intval($ownerID);
			if($ownerID > 0)
			{
				$filter['OWNER_ID'] = $ownerID;
			}
		}

		$dbRes = self::GetList(array(), $filter);
		$results = array();
		while($ary = $dbRes->Fetch())
		{
			$ary['PRODUCT_ID'] = intval($ary['PRODUCT_ID']);
			$ary['PRICE'] = round(doubleval($ary['PRICE']), 2);
			$ary['QUANTITY'] = intval($ary['QUANTITY']);

			if($assoc)
			{
				$results[intval($ary['ID'])] = $ary;
			}
			else
			{
				$results[] = $ary;
			}
		}
		return $results;
	}

	public static function SaveRows($ownerType, $ownerID, $arRows, $accountContext = null, $checkPerms = true, $regEvent = true)
	{
		$ownerType = strval($ownerType);
		$ownerID = intval($ownerID);

		if(!isset($ownerType[0]) || $ownerID <= 0 || !is_array($arRows))
		{
			self::RegisterError('Invalid arguments are supplied.');
			return false;
		}

		// Preparing accounting context -->
		if(!is_array($accountContext))
		{
			$accountContext = array();
			$owner = null;
			if($ownerType === 'D')
			{
				$owner = CCrmDeal::GetByID($ownerID, $checkPerms);
			}
			elseif($ownerType === 'L')
			{
				$owner = CCrmLead::GetByID($ownerID);
			}

			if(is_array($owner))
			{
				if(isset($owner['CURRENCY_ID']))
				{
					$accountContext['CURRENCY_ID'] = $owner['CURRENCY_ID'];
				}

				if(isset($owner['EXCH_RATE']))
				{
					$accountContext['EXCH_RATE'] = $owner['EXCH_RATE'];
				}
			}
		}

		$currencyID = isset($accountContext['CURRENCY_ID'])
			? $accountContext['CURRENCY_ID'] : CCrmCurrency::GetBaseCurrencyID();

		$exchRate = isset($accountContext['EXCH_RATE'])
			? $accountContext['EXCH_RATE'] : null;
		// <-- Preparing accounting context

		$arPresentRows = self::LoadRows($ownerType, $ownerID, true);

		// Registering events -->
		if($regEvent)
		{
			$arRowIDs = array();
			foreach($arRows as &$arRow)
			{
				if(isset($arRow['ID']))
				{
					$arRowIDs[] = intval($arRow['ID']);
				}

				$arRow['PRODUCT_ID'] = intval($arRow['PRODUCT_ID']);
				$arRow['PRICE'] = isset($arRow['PRICE']) ? round(doubleval($arRow['PRICE']), 2) : 0.0;
				$arRow['QUANTITY'] = isset($arRow['QUANTITY']) ? intval($arRow['QUANTITY']) : 1;

				$rowID = isset($arRow['ID']) ? intval($arRow['ID']) : 0;
				if($rowID <= 0)
				{
					// Row was added
					self::RegisterAddEvent($ownerType, $ownerID, $arRow, $checkPerms);
					continue;
				}

				$arPresentRow = isset($arPresentRows[$rowID]) ? $arPresentRows[$rowID] : null;
				if($arPresentRow)
				{
					// Row was modified
					self::RegisterUpdateEvent($ownerType, $ownerID, $arRow, $arPresentRow, $checkPerms);
				}
			}

			foreach($arPresentRows as $rowID => &$arPresentRow)
			{
				if(!in_array($rowID, $arRowIDs, true))
				{
					// Product  was removed
					self::RegisterRemoveEvent($ownerType, $ownerID, $arPresentRow, $checkPerms);
				}
			}
		}
		// <-- Registering events

		foreach($arRows as &$arRow)
		{
			if(isset($arRow['ID']))
			{
				unset($arRow['ID']);
			}

			$arRow['OWNER_TYPE'] = $ownerType;
			$arRow['OWNER_ID'] = $ownerID;

			$arRow['PRODUCT_ID'] = intval($arRow['PRODUCT_ID']);
			$arRow['PRICE'] = isset($arRow['PRICE']) ? round(doubleval($arRow['PRICE']), 2) : 0.0;
			$arRow['QUANTITY'] = isset($arRow['QUANTITY']) ? intval($arRow['QUANTITY']) : 1;

			$accData = CCrmAccountingHelper::PrepareAccountingData(
				array(
					'CURRENCY_ID' => $currencyID,
					'SUM' => $arRow['PRICE'],
					'EXCH_RATE' => $exchRate
				)
			);

			if(is_array($accData))
			{
				$arRow['PRICE_ACCOUNT'] = $accData['ACCOUNT_SUM'];
			}
		}

		$result = CCrmProductRow::DoSaveRows($ownerType, $ownerID, $arRows);
		// Disable sum synchronization if product rows are empty
		if($result && (count($arPresentRows) > 0 || count($arRows) > 0))
		{
			self::SynchronizeOwner($ownerType, $ownerID, $checkPerms);
		}
		return $result;
	}

	protected static function PrepareAccountingContext($ownerType, $ownerID)
	{
		$result = array();
		$owner = null;
		if($ownerType === 'D')
		{
			$owner = CCrmDeal::GetByID($ownerID, false);
		}
		elseif($ownerType === 'L')
		{
			$owner = CCrmLead::GetByID($ownerID, false);
		}

		if(is_array($owner))
		{
			if(isset($owner['CURRENCY_ID']))
			{
				$result['CURRENCY_ID'] = $owner['CURRENCY_ID'];
			}

			if(isset($owner['EXCH_RATE']))
			{
				$result['EXCH_RATE'] = $owner['EXCH_RATE'];
			}
		}

		return $result;
	}

	private static function RegisterAddEvent($ownerType, $ownerID, $arRow, $checkPerms)
	{
		IncludeModuleLangFile(__FILE__);

		$arFields = array(
			'EVENT_NAME' => GetMessage('CRM_EVENT_PROD_ROW_ADD'),
			'EVENT_TEXT_1' => self::GetProductNameByID($arRow['PRODUCT_ID']),
			'EVENT_TEXT_2' => ''
		);

		return self::RegisterEvents($ownerType, $ownerID, array($arFields), $checkPerms);
	}

	private static function RegisterUpdateEvent($ownerType, $ownerID, $arRow, $arPresentRow, $checkPerms)
	{
		IncludeModuleLangFile(__FILE__);

		$arEvents = array();
		if($arPresentRow['PRODUCT_ID'] !== $arRow['PRODUCT_ID'])
		{
			// Product was changed
			$arEvents[] = array(
				'EVENT_NAME' => GetMessage('CRM_EVENT_PROD_ROW_UPD'),
				'EVENT_TEXT_1' => self::GetProductNameByID($arPresentRow['PRODUCT_ID']),
				'EVENT_TEXT_2' => self::GetProductNameByID($arRow['PRODUCT_ID'])
			);
		}
		else
		{
			if($arPresentRow['PRICE'] !== $arRow['PRICE'])
			{
				// Product price was changed
				$arEvents[] = Array(
					'EVENT_NAME' => GetMessage('CRM_EVENT_PROD_ROW_PRICE_UPD', array('#NAME#' => self::GetProductNameByID($arRow['PRODUCT_ID']))),
					'EVENT_TEXT_1' => $arPresentRow['PRICE'],
					'EVENT_TEXT_2' => $arRow['PRICE']
				);
			}

			if($arPresentRow['QUANTITY'] !== $arRow['QUANTITY'])
			{
				// Product  quantity was changed
				$arEvents[] = Array(
					'EVENT_NAME' => GetMessage('CRM_EVENT_PROD_ROW_QTY_UPD', array('#NAME#' => self::GetProductNameByID($arRow['PRODUCT_ID']))),
					'EVENT_TEXT_1' => $arPresentRow['QUANTITY'],
					'EVENT_TEXT_2' => $arRow['QUANTITY']
				);
			}
		}

		return count($arEvents) > 0 ? self::RegisterEvents($ownerType, $ownerID, $arEvents, $checkPerms) : false;
	}

	private static function RegisterRemoveEvent($ownerType, $ownerID, $arPresentRow, $checkPerms)
	{
		IncludeModuleLangFile(__FILE__);

		$arFields = array(
			'EVENT_NAME' => GetMessage('CRM_EVENT_PROD_ROW_REM'),
			'EVENT_TEXT_1' => self::GetProductNameByID($arPresentRow['PRODUCT_ID']),
			'EVENT_TEXT_2' => ''
		);

		return self::RegisterEvents($ownerType, $ownerID, array($arFields), $checkPerms);
	}

	private static function RegisterEvents($ownerType, $ownerID, $arEvents, $checkPerms)
	{
		global $USER;
		$userID = isset($USER) && ($USER instanceof CUser) && ('CUser' === get_class($USER)) ? $USER->GetId() : 0;

		$CCrmEvent = new CCrmEvent();
		foreach($arEvents as $arEvent)
		{
			$arEvent['EVENT_TYPE'] = 1;
			$arEvent['ENTITY_TYPE'] = CCrmOwnerTypeAbbr::ResolveName($ownerType);
			$arEvent['ENTITY_ID'] = $ownerID;
			$arEvent['ENTITY_FIELD'] = 'PRODUCT_ROWS';

			if($userID > 0)
			{
				$arEvent['USER_ID']  = $userID;
			}

			$CCrmEvent->Add($arEvent, $checkPerms);
		}

		return true;
	}

	public static function GetByID($ID)
	{
		$ID = intval($ID);

		$arResult = CCrmEntityHelper::GetCached(self::CACHE_NAME, $ID);
		if (is_array($arResult))
		{
			return $arResult;
		}

		$dbRes = CCrmProductRow::GetList(array(), array('ID' => $ID));
		$arResult = $dbRes->Fetch();

		if(is_array($arResult))
		{
			CCrmEntityHelper::SetCached(self::CACHE_NAME, $ID, $arResult);
		}

		if(isset($arResult['OWNER_TYPE']))
		{
			// Remove space padding of CHAR column
			$arResult['OWNER_TYPE'] = trim($arResult['OWNER_TYPE']);
		}

		$arResult['PRODUCT_ID'] = intval($arResult['PRODUCT_ID']);
		$arResult['PRICE'] = round(doubleval($arResult['PRICE']), 2);
		$arResult['QUANTITY'] = intval($arResult['QUANTITY']);

		return $arResult;
	}

	public static function Exists($ID)
	{
		$dbRes = CCrmProductRow::GetList(array(), array('ID'=> $ID), false, false, array('ID'));
		return $dbRes->Fetch() ? true : false;
	}

	public static function GetTotalSum($arRows)
	{
		if(!is_array($arRows) || count($arRows) == 0)
		{
			return 0.0;
		}

		$ttl = 0.0;
		foreach($arRows as &$arRow)
		{
//			if(isset($arRow['ID']))
//			{
//				unset($arRow['ID']);
//			}

			$ttl += self::GetPrice($arRow) * self::GetQuantity($arRow);
		}

		return round($ttl, 2);
	}

	private static function GetProductNameByID($ID)
	{
		$prod = CCrmProduct::GetByID($ID);
		return is_array($prod) && isset($prod['NAME']) ? $prod['NAME'] : '['.$ID.']';
	}

	public static function GetProductName($arRow)
	{
		if(isset($arRow['PRODUCT_NAME']))
		{
			return $arRow['PRODUCT_NAME'];
		}

		if(isset($arRow['PRODUCT_ID']))
		{
			$rs = CCrmProduct::GetList(array(), array('ID' => $arRow['PRODUCT_ID']), false, false, array('NAME'));
			return ($ary = $rs->Fetch()) ? $ary['~NAME'] : $arRow['PRODUCT_ID'];
		}

		return $arRow['PRODUCT_ID'];
	}

	public static function GetPrice($arRow, $default = 0.0)
	{
		return isset($arRow['PRICE']) ? round(doubleval($arRow['PRICE']), 2) : $default;
	}

	public static function GetQuantity($arRow, $default = 0)
	{
		return isset($arRow['QUANTITY']) ? intval($arRow['QUANTITY']) : $default;
	}

	public static function RowsToString($arRows, $formatInfo = array('FORMAT' => '#NAME#', 'DELIMITER' => ', '))
	{
		if(!is_array($arRows) || count($arRows) == 0)
		{
			return '';
		}

		// Validation -->
		if(!is_array($formatInfo))
		{
			$formatInfo = array('FORMAT' => '#NAME#', 'DELIMITER' => ', ');
		}
		else
		{
			if(!isset($formatInfo['FORMAT']))
			{
				$formatInfo['FORMAT'] = '#NAME#';
			}

			if(!isset($formatInfo['DELIMITER']))
			{
				$formatInfo['DELIMITER'] = ', ';
			}
		}
		// <-- Validation

		$result = array();
		foreach($arRows as $row)
		{
			$result[] = str_replace(
				array(
					'#NAME#',
					'#PRICE#',
					'#QUANTITY#'
				),
				array(
					self::GetProductName($row),
					self::GetPrice($row),
					self::GetQuantity($row)
				),
				$formatInfo['FORMAT']
			);
		}

		return implode($formatInfo['DELIMITER'], $result);
	}

	public static function GetLastError()
	{
		return self::$LAST_ERROR;
	}
	// <-- Contract
}
