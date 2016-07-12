<?php
if (!CModule::IncludeModule('report'))
	return;

use Bitrix\Crm;

class CCrmReportManager
{
	private static $OWNER_INFOS = null;
	private static $REPORT_CURRENCY_ID = null;

	public static function GetReportCurrencyID()
	{
		if(!self::$REPORT_CURRENCY_ID)
		{
			self::$REPORT_CURRENCY_ID = CUserOptions::GetOption('crm', 'report_currency_id', '');
			if(!isset(self::$REPORT_CURRENCY_ID[0]))
			{
				self::$REPORT_CURRENCY_ID = CCrmCurrency::GetBaseCurrencyID();
			}
		}

		return self::$REPORT_CURRENCY_ID;
	}

	public static function SetReportCurrencyID($currencyID)
	{
		$currencyID = strval($currencyID);

		if(!isset($currencyID[0]))
		{
			$currencyID = CCrmCurrency::GetBaseCurrencyID();
		}

		if($currencyID === self::$REPORT_CURRENCY_ID)
		{
			return;
		}

		self::$REPORT_CURRENCY_ID = $currencyID;
		CUserOptions::SetOption('crm', 'report_currency_id', $currencyID);
	}

	private static function createOwnerInfo($ID, $className, $title)
	{
		return array(
			'ID' => $ID,
			'HELPER_CLASS' => $className,
			'TITLE' => $title
		);
	}
	public static function getOwnerInfos()
	{
		if(self::$OWNER_INFOS)
		{
			return self::$OWNER_INFOS;
		}

		IncludeModuleLangFile(__FILE__);

		self::$OWNER_INFOS = array();
		self::$OWNER_INFOS[] = self::createOwnerInfo(
			CCrmReportHelper::getOwnerId(),
			'CCrmReportHelper',
			GetMessage('CRM_REPORT_OWNER_TITLE_'.strtoupper(CCrmReportHelper::getOwnerId()))
		);
		self::$OWNER_INFOS[] = self::createOwnerInfo(
			CCrmProductReportHelper::getOwnerId(),
			'CCrmProductReportHelper',
			GetMessage('CRM_REPORT_OWNER_TITLE_'.strtoupper(CCrmProductReportHelper::getOwnerId()))
		);
		return self::$OWNER_INFOS;
	}
	public static function getOwnerInfo($ownerID)
	{
		$ownerID = strval($ownerID);
		if($ownerID === '')
		{
			return null;
		}

		$infos = self::getOwnerInfos();
		foreach($infos as $info)
		{
			if($info['ID'] === $ownerID)
			{
				return $info;
			}
		}
		return null;
	}
	public static function getOwnerHelperClassName($ownerID)
	{
		$info = self::getOwnerInfo($ownerID);
		return $info ? $info['HELPER_CLASS'] : '';
	}
	public static function getReportData($reportID)
	{
		$reportID = intval($reportID);
		return $reportID > 0
			? Bitrix\Report\ReportTable::getById($reportID)->NavNext(false):
			//? Bitrix\Report\ReportEntity::getById($reportID)->NavNext(false):
			null;
	}
}

abstract class CCrmReportHelperBase extends CReportHelper
{
	protected static $CURRENT_RESULT_ROWS = null;
	protected static $CURRENT_RESULT_ROW = null;
	public static function getCurrentVersion()
	{
		global $arModuleVersion;

		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/crm/install/version.php");
		return $arModuleVersion['VERSION'];
	}
	public static function fillFilterReferenceColumn(&$filterElement, &$field)
	{
		if ($field->GetDataType() == 'Bitrix\Crm\Company')
		{
			// CrmCompany
			if ($filterElement['value'])
			{
				$entity = CCrmCompany::GetById($filterElement['value']);
				if ($entity)
				{
					$filterElement['value'] = array('id' => $entity['ID'], 'name' => $entity['TITLE']);
				}
				else
				{
					$filterElement['value'] = array('id' => $filterElement['value'], 'name' => GetMessage('CRM_COMPANY_NOT_FOUND'));
				}
			}
			else
			{
				$filterElement['value'] = array('id' => '');
			}
		}
		elseif ($field->GetDataType() == 'Bitrix\Crm\Contact')
		{
			// CrmContact
			if ($filterElement['value'])
			{
				$entity = CCrmContact::GetById($filterElement['value']);
				if ($entity)
				{
					$filterElement['value'] = array('id' => $entity['ID'], 'name' => $entity['FULL_NAME']);
				}
				else
				{
					$filterElement['value'] = array('id' => $filterElement['value'], 'name' => GetMessage('CRM_CONTACT_NOT_FOUND'));
				}
			}
			else
			{
				$filterElement['value'] = array('id' => '');
			}
		}
		elseif ($field->GetDataType() == 'Bitrix\Crm\Lead')
		{
			// CrmLead
			if ($filterElement['value'])
			{
				$entity = CCrmLead::GetById($filterElement['value']);
				if ($entity)
				{
					$filterElement['value'] = array('id' => $entity['ID'], 'name' => $entity['TITLE']);
				}
				else
				{
					$filterElement['value'] = array('id' => $filterElement['value'], 'name' => GetMessage('CRM_LEAD_NOT_FOUND'));
				}
			}
			else
			{
				$filterElement['value'] = array('id' => '');
			}
		}
		parent::fillFilterReferenceColumn($filterElement, $field);
	}
	public static function formatResults(&$rows, &$columnInfo, $total)
	{
		self::$CURRENT_RESULT_ROWS = $rows;
		foreach ($rows as &$row)
		{
			self::$CURRENT_RESULT_ROW = $row;
			foreach ($row as $k => &$v)
			{
				if (!array_key_exists($k, $columnInfo))
				{
					continue;
				}

				$cInfo = $columnInfo[$k];

				if (is_array($v))
				{
					foreach ($v as &$subv)
					{
						static::formatResultValue($k, $subv, $row, $cInfo, $total);
					}
				}
				else
				{
					static::formatResultValue($k, $v, $row, $cInfo, $total);
				}
			}
		}

		unset($row, $v, $subv);
		self::$CURRENT_RESULT_ROWS = self::$CURRENT_RESULT_ROW = null;
	}
	public static function formatResultsTotal(&$total, &$columnInfo)
	{
		foreach($total as $k => &$v)
		{
			if(preg_match('/_OPPORTUNITY$/', $k)
				|| preg_match('/_ACCOUNT$/', $k)
				|| preg_match('/_AMOUNT$/', $k))
			{
				$v = self::MoneyToString(doubleval($v));
			}
		}

		parent::formatResultsTotal($total, $columnInfo);
	}
	protected static function MoneyToString($sum)
	{
		return str_replace(
			' ',
			'&nbsp;',
			CCrmCurrency::MoneyToString($sum, CCrmCurrency::GetAccountCurrencyID(), '#')
		);
	}
	protected static function prepareDealTitleHtml($dealID, $title)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('crm', 'path_to_deal_show'),
			array('deal_id' => $dealID)
		);

		return '<a target="_blank" href="'.htmlspecialcharsbx($url).'">'.htmlspecialcharsbx($title).'</a>';
	}
	protected static function getStatusName($code, $type, $htmlEncode = false)
	{
		$code = strval($code);
		$type = strval($type);
		if($code === '' || $type === '')
		{
			return '';
		}

		$statuses = CCrmStatus::GetStatus($type);
		$name = array_key_exists($code, $statuses) ? $statuses[$code]['NAME'] : $code;
		return $htmlEncode ? htmlspecialcharsbx($name) : $name;
	}
	protected static function getDealStageName($code, $htmlEncode = false)
	{
		return self::getStatusName($code, 'DEAL_STAGE', $htmlEncode);
	}
	protected static function getDealTypeName($code, $htmlEncode = false)
	{
		return self::getStatusName($code, 'DEAL_TYPE', $htmlEncode);
	}
	protected static function getEventTypeName($code, $htmlEncode = false)
	{
		return self::getStatusName($code, 'EVENT_TYPE', $htmlEncode);
	}
	protected static function getCurrencyName($ID, $htmlEncode = false)
	{
		$currency = CCrmCurrency::GetByID($ID);
		if($currency)
		{
			return $currency['FULL_NAME'];
		}

		// Old style (for compatibility only)
		$statuses =  CCrmStatus::GetStatus('CURRENCY');
		$name = array_key_exists($ID, $statuses) ? $statuses[$ID]['NAME'] : $ID;
		return $htmlEncode ? htmlspecialcharsbx($name) : $name;
	}
	protected static function getDealOriginatorName($originatorID, $htmlEncode = false)
	{
		$rsSaleSttings = CCrmExternalSale::GetList(array(), array('ID' => intval($originatorID)));
		$arSaleSettings = $rsSaleSttings->Fetch();
		if(!is_array($arSaleSettings))
		{
			return $originatorID;
		}

		$name = isset($arSaleSettings['NAME']) ? strval($arSaleSettings['NAME']) : '';
		if($name === '')
		{
			$name = isset($arSaleSettings['SERVER']) ? strval($arSaleSettings['SERVER']) : '';
		}

		return $htmlEncode ? htmlspecialcharsbx($name) : $name;
	}
	protected static function prepareCompanyTitleHtml($companyID, $title)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('crm', 'path_to_company_show'),
			array('company_id' => $companyID)
		);

		return '<a target="_blank" href="'.htmlspecialcharsbx($url).'">'.htmlspecialcharsbx($title).'</a>';
	}
	protected static function prepareContactTitleHtml($contactID, $title)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('crm', 'path_to_contact_show'),
			array('contact_id' => $contactID)
		);

		return '<a target="_blank" href="'.htmlspecialcharsbx($url).'">'.htmlspecialcharsbx($title).'</a>';
	}
	protected static function prepareProductNameHtml($productID, $name)
	{
		$url = CComponentEngine::MakePathFromTemplate(
			COption::GetOptionString('crm', 'path_to_product_show'),
			array('product_id' => $productID)
		);

		return '<a target="_blank" href="'.htmlspecialcharsbx($url).'">'.htmlspecialcharsbx($name).'</a>';
	}
}

class CCrmReportHelper extends CCrmReportHelperBase
{
	public static function GetReportCurrencyID()
	{
		return CCrmReportManager::GetReportCurrencyID();
	}

	public static function SetReportCurrencyID($currencyID)
	{
		CCrmReportManager::SetReportCurrencyID($currencyID);
	}

	public static function getEntityName()
	{
		return 'Bitrix\Crm\Deal';
	}
	public static function getOwnerId()
	{
		return 'crm';
	}
	public static function getColumnList()
	{
		IncludeModuleLangFile(__FILE__);

		return array(
			'ID',
			'TITLE',
			'COMMENTS',
			'STAGE_ID',
			'STAGE_SUB' => array(
				'IS_WORK',
				'IS_WON',
				'IS_LOSE'
			),
			'CLOSED',
			'TYPE_ID',
			'PROBABILITY',
			'OPPORTUNITY',
			'CURRENCY_ID',
			'OPPORTUNITY_ACCOUNT',
			//'ACCOUNT_CURRENCY_ID', //Is always same for all deals
			'RECEIVED_AMOUNT',
			'LOST_AMOUNT',
			'BEGINDATE',
			'CLOSEDATE',
			'EVENT_ID',
			'EVENT_DATE',
			'EVENT_DESCRIPTION',
			'ASSIGNED_BY' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'DATE_CREATE',
			'CREATED_BY' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'DATE_MODIFY',
			'MODIFY_BY' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'LEAD_BY' => array(
				'ID',
				'TITLE',
				'STATUS_BY.STATUS_ID',
				'STATUS_DESCRIPTION',
				'OPPORTUNITY',
				'CURRENCY_ID',
				'COMMENTS',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'COMPANY_TITLE',
				'POST',
				'ADDRESS',
				'SOURCE_BY.STATUS_ID',
				'SOURCE_DESCRIPTION',
				'DATE_CREATE',
				'DATE_MODIFY',
				'ASSIGNED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'CREATED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'MODIFY_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				)
			),
			'CONTACT_BY' => array(
				'ID',
				'NAME',
				'LAST_NAME',
				'SECOND_NAME',
				'POST',
				'ADDRESS',
				'TYPE_BY.STATUS_ID',
				'COMMENTS',
				'SOURCE_BY.STATUS_ID',
				'SOURCE_DESCRIPTION',
				'DATE_CREATE',
				'DATE_MODIFY',
				'ASSIGNED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'CREATED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'MODIFY_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				)
			),
			'COMPANY_BY' => array(
				'ID',
				'TITLE',
				'COMPANY_TYPE_BY.STATUS_ID',
				'INDUSTRY_BY.STATUS_ID',
				'EMPLOYEES_BY.STATUS_ID',
				'REVENUE',
				'CURRENCY_ID',
				'COMMENTS',
				'ADDRESS',
				'ADDRESS_LEGAL',
				'BANKING_DETAILS',
				'DATE_CREATE',
				'DATE_MODIFY',
				'CREATED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'MODIFY_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				)
			),
			'HAS_PRODUCTS',
			'PRODUCT_ROW' => array(
				'ProductRow:DEAL_OWNER.PRODUCT.ID',
				'ProductRow:DEAL_OWNER.PRODUCT.IBLOCK_ELEMENT.NAME',
				'ProductRow:DEAL_OWNER.PRICE_ACCOUNT',
				'ProductRow:DEAL_OWNER.QUANTITY',
				'ProductRow:DEAL_OWNER.SUM_ACCOUNT'
			),
			'ProductRow:DEAL_OWNER.PRODUCT.IBLOCK_ELEMENT_GRC.NAME',
			'ORIGINATOR_BY.ID'
		);
	}
	//Enable grouping by product name
	public static function getGrcColumns()
	{
		return array('ProductRow:DEAL_OWNER.PRODUCT.IBLOCK_ELEMENT_GRC.NAME');
	}
	public static function getDefaultColumns()
	{
		return array(
			array('name' => 'TITLE'),
			array('name' => 'STAGE_ID'),
			array('name' => 'ASSIGNED_BY.SHORT_NAME'),
			array('name' => 'BEGINDATE')
		);
	}
	public static function getCalcVariations()
	{
		return array_merge(
			parent::getCalcVariations(),
			array(
				'IS_WORK' => array('SUM'),
				'IS_LOSE' => array('SUM'),
				'IS_WON' => array('SUM')
			)
		);
	}
	public static function getCompareVariations()
	{
		return array_merge(
			parent::getCompareVariations(),
			array(
				'STAGE_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'TYPE_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'CURRENCY_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'EVENT_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'LEAD_BY' => array(
					'EQUAL'
				),
				'CONTACT_BY' => array(
					'EQUAL'
				),
				'COMPANY_BY' => array(
					'EQUAL'
				),
				'LEAD_BY.STATUS_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'LEAD_BY.SOURCE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'CONTACT_BY.TYPE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'CONTACT_BY.SOURCE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'COMPANY_BY.INDUSTRY_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'COMPANY_BY.EMPLOYEES_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				)
			)
		);
	}
	public static function beforeViewDataQuery(&$select, &$filter, &$group, &$order, &$limit, &$options, &$runtime)
	{
		// Dynamic data setup
		Crm\DealTable::ProcessQueryOptions($options);

		if(!isset($select['CRM_DEAL_COMPANY_BY_ID']))
		{
			foreach($select as $k => $v)
			{
				if(strpos($k, 'CRM_DEAL_COMPANY_BY_') === 0)
				{
					$select['CRM_DEAL_COMPANY_BY_ID'] = 'COMPANY_BY.ID';
					break;
				}
			}
		}

		// HACK: Switch to order by STAGE_BY.SORT instead STAGE_BY.STATUS_ID
		// We are trying to adhere user defined sort rules.
		if(isset($order['STAGE_ID']))
		{
			$select['CRM_DEAL_STAGE_BY_SORT'] = 'STAGE_BY.SORT';
			$order['CRM_DEAL_STAGE_BY_SORT'] = $order['STAGE_ID'];
			unset($order['STAGE_ID']);
		}

		if(!isset($select['CRM_DEAL_CONTACT_BY_ID']))
		{
			foreach($select as $k => $v)
			{
				if(strpos($k, 'CRM_DEAL_CONTACT_BY_') === 0)
				{
					$select['CRM_DEAL_CONTACT_BY_ID'] = 'CONTACT_BY.ID';
					break;
				}
			}
		}

		if(!isset($select['CRM_DEAL_CRM_PRODUCT_ROW_DEAL_OWNER_PRODUCT_ID']))
		{
			foreach($select as $k => $v)
			{
				if(strpos($k, 'CRM_DEAL_CRM_PRODUCT_ROW_DEAL_OWNER_PRODUCT_') === 0)
				{
					$select['CRM_DEAL_CRM_PRODUCT_ROW_DEAL_OWNER_PRODUCT_ID'] = 'ProductRow:DEAL_OWNER.PRODUCT.ID';
					break;
				}
			}

		}

		// permission
		$addClause = CCrmDeal::BuildPermSql('crm_deal');

		if (!empty($addClause))
		{
			global $DB;
			// HACK: add escape chars for ORM
			$addClause = str_replace('crm_deal.ID', $DB->escL.'crm_deal'.$DB->escR.'.ID', $addClause);

			$filter = array($filter,
				'=IS_ALLOWED' => '1'
			);

			$runtime['IS_ALLOWED'] = array(
				'data_type' => 'integer',
				'expression' => array('CASE WHEN '.$addClause.' THEN 1 ELSE 0 END')
			);
		}
	}

	public static function formatResultValue($k, &$v, &$row, &$cInfo, $total)
	{
		// HACK: detect if 'report.view' component is rendering excel spreadsheet
		$isHtml = !(isset($_GET['EXCEL']) && $_GET['EXCEL'] === 'Y');

		$field = $cInfo['field'];
		$fieldName = isset($cInfo['fieldName']) ? $cInfo['fieldName'] : $field->GetName();
		$prcnt = isset($cInfo['prcnt']) ? $cInfo['prcnt'] : '';

		if(!isset($prcnt[0])
			&& ($fieldName === 'OPPORTUNITY'
				|| $fieldName === 'OPPORTUNITY_ACCOUNT'
				|| $fieldName === 'RECEIVED_AMOUNT'
				|| $fieldName === 'LOST_AMOUNT'
				|| $fieldName === 'ProductRow:DEAL_OWNER.SUM_ACCOUNT'
				|| $fieldName === 'ProductRow:DEAL_OWNER.PRICE_ACCOUNT'
				|| $fieldName === 'COMPANY_BY.REVENUE'))
		{
			$v = self::MoneyToString(doubleval($v));
		}
		elseif($fieldName === 'TITLE')
		{
			if($isHtml && strlen($v) > 0 && self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['ID']))
			{
				$v = self::prepareDealTitleHtml(self::$CURRENT_RESULT_ROW['ID'], $v);
			}
		}
		elseif($fieldName === 'STAGE_ID')
		{
			if($v !== '')
			{
				$v = self::getDealStageName($v, $isHtml);
			}
		}
		elseif($fieldName === 'TYPE_ID')
		{
			if($v !== '')
			{
				$v = self::getDealTypeName($v, $isHtml);
			}
		}
		elseif($fieldName === 'CURRENCY_ID' || $fieldName === 'LEAD_BY.CURRENCY_ID' || $fieldName === 'COMPANY_BY.CURRENCY_ID')
		{
			if($v !== '')
			{
				$v = self::getCurrencyName($v, $isHtml);
			}
		}
		elseif($fieldName === 'EVENT_ID')
		{
			if($v !== '')
			{
				$v = self::getEventTypeName($v, $isHtml);
			}
		}
		elseif($fieldName === 'ORIGINATOR_BY.ID')
		{
			$v = self::getDealOriginatorName($v, $isHtml);
		}
		elseif($fieldName === 'LEAD_BY.STATUS_BY.STATUS_ID')
		{
			if($v !== '')
			{
				$v = self::getStatusName($v, 'STATUS', $isHtml);
			}
		}
		elseif($fieldName === 'LEAD_BY.SOURCE_BY.STATUS_ID' || $fieldName === 'CONTACT_BY.SOURCE_BY.STATUS_ID')
		{
			if($v !== '')
			{
				$v = self::getStatusName($v, 'SOURCE', $isHtml);
			}
		}
		elseif(strpos($fieldName, 'COMPANY_BY.') === 0)
		{
			if($v === '' || trim($v) === '.')
			{
				if(strpos($fieldName, 'COMPANY_BY.COMPANY_TYPE_BY') !== 0
					&& strpos($fieldName, 'COMPANY_BY.INDUSTRY_BY') !== 0
					&& strpos($fieldName, 'COMPANY_BY.EMPLOYEES_BY') !== 0)
				{
					$v = GetMessage('CRM_DEAL_COMPANY_NOT_ASSIGNED');
				}
			}
			elseif($fieldName === 'COMPANY_BY.TITLE')
			{
				if($isHtml && self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['CRM_DEAL_COMPANY_BY_ID']))
				{
					$v = self::prepareCompanyTitleHtml(self::$CURRENT_RESULT_ROW['CRM_DEAL_COMPANY_BY_ID'], $v);
				}
			}
			elseif($fieldName === 'COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'COMPANY_TYPE', $isHtml);
				}
			}
			elseif($fieldName === 'COMPANY_BY.INDUSTRY_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'INDUSTRY', $isHtml);
				}
			}
			elseif($fieldName === 'COMPANY_BY.EMPLOYEES_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'EMPLOYEES', $isHtml);
				}
			}
		}
		elseif(strpos($fieldName, 'CONTACT_BY.') === 0)
		{
			if($v === '' || trim($v) === '.')
			{
				if(strpos($fieldName, 'CONTACT_BY.TYPE_BY') !== 0)
				{
					$v = GetMessage('CRM_DEAL_CONTACT_NOT_ASSIGNED');
				}
			}
			elseif($fieldName === 'CONTACT_BY.TYPE_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'CONTACT_TYPE', $isHtml);
				}
			}
			elseif($fieldName === 'CONTACT_BY.NAME'
				|| $fieldName === 'CONTACT_BY.LAST_NAME'
				|| $fieldName === 'CONTACT_BY.SECOND_NAME'
				|| $fieldName === 'CONTACT_BY.ADDRESS')
			{
				if($isHtml && self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['CRM_DEAL_CONTACT_BY_ID']))
				{
					self::prepareContactTitleHtml(self::$CURRENT_RESULT_ROW['CRM_DEAL_CONTACT_BY_ID'], $v);
				}
			}
		}
		elseif(strpos($fieldName, 'ASSIGNED_BY.') === 0)
		{
			if(strlen($v) === 0 || trim($v) === '.')
			{
				$v = GetMessage('CRM_DEAL_RESPONSIBLE_NOT_ASSIGNED');
			}
			elseif($isHtml)
			{
				$v = htmlspecialcharsbx($v);
			}
		}
		elseif(strpos($fieldName, 'ProductRow:DEAL_OWNER.PRODUCT.IBLOCK_ELEMENT.') === 0)
		{
			if($isHtml)
			{
				if(self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['CRM_DEAL_CRM_PRODUCT_ROW_DEAL_OWNER_PRODUCT_ID']))
				{
					$v = self::prepareProductNameHtml(self::$CURRENT_RESULT_ROW['CRM_DEAL_CRM_PRODUCT_ROW_DEAL_OWNER_PRODUCT_ID'], $v);
				}
				else
				{
					$v = htmlspecialcharsbx($v);
				}
			}
		}
		elseif($fieldName !== 'COMMENTS') // Leave 'COMMENTS' as is for HTML display.
		{
			parent::formatResultValue($k, $v, $row, $cInfo, $total);
		}
	}

	public static function getPeriodFilter($date_from, $date_to)
	{
		if(is_null($date_from) && is_null($date_to))
		{
			return array(); // Empty filter for empty time interval.
		}

		//$now = ConvertTimeStamp(time(), 'FULL');
		$filter = array('LOGIC' => 'AND');
		if(!is_null($date_to))
		{
			$filter[] = array(
				'LOGIC' => 'OR',
				'<=BEGINDATE' => $date_to,
				'=BEGINDATE' => null
			);
			//$filter['<=BEGINDATE'] = $date_to;
		}

		if(!is_null($date_from))
		{
			$filter[] = array(
				'LOGIC' => 'OR',
				'>=CLOSEDATE' => $date_from,
				'=CLOSEDATE' => null
			);
			//$filter['>=CLOSEDATE'] = $date_from;
		}

		return $filter;
	}

	public static function clearMenuCache()
	{
		CrmClearMenuCache();
	}

	public static function formatResultsTotal(&$total, &$columnInfo)
	{
		parent::formatResultsTotal($total, $columnInfo);

		if(isset($total['TOTAL_PROBABILITY']))
		{
			// Suppress PROBABILITY (%) aggregation
			unset($total['TOTAL_PROBABILITY']);
		}
	}

	public static function getDefaultReports()
	{
		IncludeModuleLangFile(__FILE__);

		$reports = array(
			'11.0.6' => array(
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_WON_DEALS'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_WON_DEALS_DESCR'),
					'mark_default' => 1,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:8:{i:0;a:2:{s:4:"name";s:5:"TITLE";s:5:"alias";s:0:"";}i:20;a:2:{s:4:"name";s:7:"TYPE_ID";s:5:"alias";s:0:"";}i:2;a:2:{s:4:"name";s:22:"ASSIGNED_BY.SHORT_NAME";s:5:"alias";s:0:"";}i:7;a:2:{s:4:"name";s:16:"COMPANY_BY.TITLE";s:5:"alias";s:0:"";}i:23;a:2:{s:4:"name";s:36:"COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:5:"alias";s:0:"";}i:6;a:2:{s:4:"name";s:20:"CONTACT_BY.LAST_NAME";s:5:"alias";s:0:"";}i:27;a:2:{s:4:"name";s:15:"RECEIVED_AMOUNT";s:5:"alias";s:0:"";}i:4;a:2:{s:4:"name";s:9:"CLOSEDATE";s:5:"alias";s:0:"";}}s:6:"filter";a:1:{i:0;a:7:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:7:"compare";s:16:"GREATER_OR_EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"STAGE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:3:"WON";s:10:"changeable";s:1:"0";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:4;s:9:"sort_type";s:4:"DESC";s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_PRODUCTS_PROFIT'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_PRODUCTS_PROFIT_DESCR'),
					'mark_default' => 2,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:4:{i:4;a:2:{s:4:"name";s:49:"ProductRow:DEAL_OWNER.PRODUCT.IBLOCK_ELEMENT.NAME";s:5:"alias";s:0:"";}i:5;a:3:{s:4:"name";s:2:"ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";}i:6;a:3:{s:4:"name";s:30:"ProductRow:DEAL_OWNER.QUANTITY";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:7;a:3:{s:4:"name";s:33:"ProductRow:DEAL_OWNER.SUM_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:9:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:53:"ProductRow:DEAL_OWNER.PRODUCT.IBLOCK_ELEMENT_GRC.NAME";s:7:"compare";s:8:"CONTAINS";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"STAGE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:3:"WON";s:10:"changeable";s:1:"0";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:36:"COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:28:"CONTACT_BY.TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:6;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:7;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:7;s:9:"sort_type";s:4:"DESC";s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_VOLUME_BY_CONTACTS'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_VOLUME_BY_CONTACTS_DESCR'),
					'mark_default' => 3,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:8:{i:4;a:2:{s:4:"name";s:20:"CONTACT_BY.LAST_NAME";s:5:"alias";s:0:"";}i:5;a:3:{s:4:"name";s:2:"ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";}i:6;a:3:{s:4:"name";s:7:"IS_WORK";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:8;a:4:{s:4:"name";s:7:"IS_LOSE";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"5";}i:7;a:4:{s:4:"name";s:6:"IS_WON";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"5";}i:9;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"AVG";}i:10;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:12;a:3:{s:4:"name";s:15:"RECEIVED_AMOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:6:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:28:"CONTACT_BY.TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:13:"CONTACT_BY.ID";s:7:"compare";s:7:"GREATER";s:5:"value";s:1:"0";s:10:"changeable";s:1:"0";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:12;s:9:"sort_type";s:4:"DESC";s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_VOLUME_BY_COMPANIES'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_VOLUME_BY_COMPANIES_DESCR'),
					'mark_default' => 4,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:8:{i:4;a:1:{s:4:"name";s:16:"COMPANY_BY.TITLE";}i:5;a:3:{s:4:"name";s:2:"ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";}i:6;a:3:{s:4:"name";s:7:"IS_WORK";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:8;a:4:{s:4:"name";s:7:"IS_LOSE";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"5";}i:7;a:4:{s:4:"name";s:6:"IS_WON";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"5";}i:9;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"AVG";}i:10;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:12;a:3:{s:4:"name";s:15:"RECEIVED_AMOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:6:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:36:"COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:13:"COMPANY_BY.ID";s:7:"compare";s:7:"GREATER";s:5:"value";s:1:"0";s:10:"changeable";s:1:"0";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:12;s:9:"sort_type";s:4:"DESC";s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_VOLUME_BY_MANAGERS'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_VOLUME_BY_MANAGERS_DESCR'),
					'mark_default' => 5,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:8:{i:2;a:2:{s:4:"name";s:22:"ASSIGNED_BY.SHORT_NAME";s:5:"alias";s:0:"";}i:4;a:3:{s:4:"name";s:2:"ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";}i:5;a:3:{s:4:"name";s:7:"IS_WORK";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:7;a:4:{s:4:"name";s:7:"IS_LOSE";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"4";}i:6;a:4:{s:4:"name";s:6:"IS_WON";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"4";}i:11;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"AVG";}i:10;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:9;a:3:{s:4:"name";s:15:"RECEIVED_AMOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:7:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:28:"CONTACT_BY.TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:36:"COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:9;s:9:"sort_type";s:4:"DESC";s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_EXPECTED_SALES'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_EXPECTED_SALES_DESCR'),
					'mark_default' => 6,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:9:{i:0;a:2:{s:4:"name";s:5:"TITLE";s:5:"alias";s:0:"";}i:2;a:2:{s:4:"name";s:22:"ASSIGNED_BY.SHORT_NAME";s:5:"alias";s:0:"";}i:1;a:1:{s:4:"name";s:8:"STAGE_ID";}i:15;a:1:{s:4:"name";s:11:"PROBABILITY";}i:7;a:2:{s:4:"name";s:16:"COMPANY_BY.TITLE";s:5:"alias";s:0:"";}i:6;a:2:{s:4:"name";s:20:"CONTACT_BY.LAST_NAME";s:5:"alias";s:0:"";}i:3;a:1:{s:4:"name";s:9:"BEGINDATE";}i:4;a:1:{s:4:"name";s:9:"CLOSEDATE";}i:14;a:2:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";}}s:6:"filter";a:1:{i:0;a:10:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"STAGE_ID";s:7:"compare";s:9:"NOT_EQUAL";s:5:"value";s:3:"WON";s:10:"changeable";s:1:"0";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"STAGE_ID";s:7:"compare";s:9:"NOT_EQUAL";s:5:"value";s:4:"LOSE";s:10:"changeable";s:1:"0";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:7:"compare";s:16:"GREATER_OR_EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"PROBABILITY";s:7:"compare";s:16:"GREATER_OR_EQUAL";s:5:"value";s:2:"50";s:10:"changeable";s:1:"1";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:6;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:7;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:8;a:5:{s:4:"type";s:5:"field";s:4:"name";s:9:"CLOSEDATE";s:7:"compare";s:13:"LESS_OR_EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"0";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:4;s:9:"sort_type";s:3:"ASC";s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_DELAYED_DEALS'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_DELAYED_DEALS_DESCR'),
					'mark_default' => 7,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:3:"all";s:5:"value";N;}s:6:"select";a:10:{i:0;a:2:{s:4:"name";s:5:"TITLE";s:5:"alias";s:0:"";}i:2;a:2:{s:4:"name";s:22:"ASSIGNED_BY.SHORT_NAME";s:5:"alias";s:0:"";}i:15;a:2:{s:4:"name";s:7:"TYPE_ID";s:5:"alias";s:0:"";}i:1;a:1:{s:4:"name";s:8:"STAGE_ID";}i:6;a:1:{s:4:"name";s:11:"PROBABILITY";}i:7;a:2:{s:4:"name";s:20:"CONTACT_BY.LAST_NAME";s:5:"alias";s:0:"";}i:8;a:2:{s:4:"name";s:16:"COMPANY_BY.TITLE";s:5:"alias";s:0:"";}i:3;a:1:{s:4:"name";s:9:"BEGINDATE";}i:4;a:1:{s:4:"name";s:9:"CLOSEDATE";}i:14;a:2:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";}}s:6:"filter";a:1:{i:0;a:11:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"STAGE_ID";s:7:"compare";s:9:"NOT_EQUAL";s:5:"value";s:3:"WON";s:10:"changeable";s:1:"0";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"STAGE_ID";s:7:"compare";s:9:"NOT_EQUAL";s:5:"value";s:4:"LOSE";s:10:"changeable";s:1:"0";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:6:"CLOSED";s:7:"compare";s:5:"EQUAL";s:5:"value";s:5:"false";s:10:"changeable";s:1:"0";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:7:"compare";s:16:"GREATER_OR_EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"PROBABILITY";s:7:"compare";s:16:"GREATER_OR_EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:6;a:5:{s:4:"type";s:5:"field";s:4:"name";s:36:"COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:7;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:8;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:9;a:5:{s:4:"type";s:5:"field";s:4:"name";s:9:"CLOSEDATE";s:7:"compare";s:13:"LESS_OR_EQUAL";s:5:"value";s:5:"today";s:10:"changeable";s:1:"0";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:4;s:9:"sort_type";s:3:"ASC";s:5:"limit";N;}')
				),

				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_DISTRIBUTION_BY_STAGE'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_DISTRIBUTION_BY_STAGE_DESCR'),
					'mark_default' => 8,
					'settings' => unserialize('a:7:{s:6:"entity";s:7:"CrmDeal";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:5:{i:8;a:1:{s:4:"name";s:8:"STAGE_ID";}i:7;a:3:{s:4:"name";s:2:"ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";}i:12;a:4:{s:4:"name";s:2:"ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";s:5:"prcnt";s:11:"self_column";}i:9;a:3:{s:4:"name";s:19:"OPPORTUNITY_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:11;a:3:{s:4:"name";s:15:"RECEIVED_AMOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:7:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:7:"TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:36:"COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:28:"CONTACT_BY.TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:10:"CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:8;s:9:"sort_type";s:3:"ASC";s:5:"limit";N;}')
				)
			)
		);

		global $DB;
		$dbType = strtoupper($DB->type);
		if($dbType === 'MSSQL')
		{
			unset($reports['11.0.6'][1]); //PRODUCTS_PROFIT is not supported in MSSQL
		}

		foreach ($reports as &$vreports)
		{
			foreach ($vreports as &$report)
			{
				if ($report['mark_default'] === 1)
				{
					$report['settings']['select'][0]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEAL');
					$report['settings']['select'][20]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEAL_TYPE');
					$report['settings']['select'][2]['alias'] = GetMessage('CRM_REPORT_ALIAS_RESPONSIBLE');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_COMPANY');
					$report['settings']['select'][23]['alias'] = GetMessage('CRM_REPORT_ALIAS_COMPANY_TYPE');
					$report['settings']['select'][6]['alias'] = GetMessage('CRM_REPORT_ALIAS_CONTACT');
					$report['settings']['select'][27]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_PROFIT');
					$report['settings']['select'][4]['alias'] = GetMessage('CRM_REPORT_ALIAS_CLOSING_DATE');
				}
				elseif ($report['mark_default'] === 2)
				{
					$report['settings']['select'][4]['alias'] = GetMessage('CRM_REPORT_ALIAS_PRODUCT');
					$report['settings']['select'][5]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_QUANTITY');
					$report['settings']['select'][6]['alias'] = GetMessage('CRM_REPORT_ALIAS_SOLD_PRODUCTS_QUANTITY');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_SALES_PROFIT');
				}
				elseif ($report['mark_default'] === 3)
				{
					$report['settings']['select'][4]['alias'] = GetMessage('CRM_REPORT_ALIAS_LAST_NAME');
					$report['settings']['select'][5]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_QUANTITY');
					$report['settings']['select'][6]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_INPROCESS_QUANTITY');
					$report['settings']['select'][8]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_LOSE_QUANTITY');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_WON_QUANTITY');
					$report['settings']['select'][9]['alias'] = GetMessage('CRM_REPORT_ALIAS_AVERAGE_DEAL');
					$report['settings']['select'][10]['alias'] = GetMessage('CRM_REPORT_ALIAS_OPPORTUNITY_AMOUNT');
					$report['settings']['select'][12]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_PROFIT');
				}
				elseif ($report['mark_default'] === 4)
				{
					$report['settings']['select'][4]['alias'] = GetMessage('CRM_REPORT_ALIAS_COMPANY');
					$report['settings']['select'][5]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_QUANTITY');
					$report['settings']['select'][6]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_INPROCESS_QUANTITY');
					$report['settings']['select'][8]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_LOSE_QUANTITY');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_WON_QUANTITY');
					$report['settings']['select'][9]['alias'] = GetMessage('CRM_REPORT_ALIAS_AVERAGE_DEAL');
					$report['settings']['select'][10]['alias'] = GetMessage('CRM_REPORT_ALIAS_OPPORTUNITY_AMOUNT');
					$report['settings']['select'][12]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_PROFIT');
				}
				elseif ($report['mark_default'] === 5)
				{
					$report['settings']['select'][2]['alias'] = GetMessage('CRM_REPORT_ALIAS_RESPONSIBLE');
					$report['settings']['select'][4]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_QUANTITY');
					$report['settings']['select'][5]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_INPROCESS_QUANTITY');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_LOSE_QUANTITY');
					$report['settings']['select'][6]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_WON_QUANTITY');
					$report['settings']['select'][11]['alias'] = GetMessage('CRM_REPORT_ALIAS_AVERAGE_DEAL');
					$report['settings']['select'][10]['alias'] = GetMessage('CRM_REPORT_ALIAS_OPPORTUNITY_AMOUNT');
					$report['settings']['select'][9]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_PROFIT');
				}
				elseif ($report['mark_default'] === 6)
				{
					$report['settings']['select'][0]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEAL');
					$report['settings']['select'][2]['alias'] = GetMessage('CRM_REPORT_ALIAS_RESPONSIBLE');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_COMPANY');
					$report['settings']['select'][6]['alias'] = GetMessage('CRM_REPORT_ALIAS_CONTACT');
					$report['settings']['select'][14]['alias'] = GetMessage('CRM_REPORT_ALIAS_OPPORTUNITY_AMOUNT');
				}
				elseif ($report['mark_default'] === 7)
				{
					$report['settings']['select'][0]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEAL');
					$report['settings']['select'][2]['alias'] = GetMessage('CRM_REPORT_ALIAS_RESPONSIBLE');
					$report['settings']['select'][15]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEAL_TYPE');
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_CONTACT');
					$report['settings']['select'][8]['alias'] = GetMessage('CRM_REPORT_ALIAS_COMPANY');
					$report['settings']['select'][14]['alias'] = GetMessage('CRM_REPORT_ALIAS_OPPORTUNITY_AMOUNT');
				}
				elseif ($report['mark_default'] === 8)
				{
					$report['settings']['select'][7]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_QUANTITY');
					$report['settings']['select'][12]['alias'] = GetMessage('CRM_REPORT_ALIAS_PROPORTION');
					$report['settings']['select'][9]['alias'] = GetMessage('CRM_REPORT_ALIAS_OPPORTUNITY_AMOUNT');
					$report['settings']['select'][11]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_PROFIT');
				}
			}
			unset($report);
		}

		return $reports;
	}

	public static function getFirstVersion()
	{
		return '11.0.6';
	}
}

class CCrmProductReportHelper extends CCrmReportHelperBase
{
	public static function GetReportCurrencyID()
	{
		return CCrmReportManager::GetReportCurrencyID();
	}

	public static function SetReportCurrencyID($currencyID)
	{
		CCrmReportManager::SetReportCurrencyID($currencyID);
	}

	public static function getEntityName()
	{
		return 'Bitrix\Crm\ProductRow';
	}
	public static function getOwnerId()
	{
		return 'crm_product_row';
	}
	public static function getDefaultColumns()
	{
		return array(
			array('name' => 'PRODUCT.IBLOCK_ELEMENT.NAME')
		);
	}
	public static function getColumnList()
	{
		IncludeModuleLangFile(__FILE__);

		return array(
			'PRODUCT.IBLOCK_ELEMENT.NAME',
			'PRICE_ACCOUNT',
			'QUANTITY',
			'SUM_ACCOUNT',
			'DEAL_OWNER' => array(
				'ID',
				'TITLE',
				'COMMENTS',
				'STAGE_ID',
				'CLOSED',
				'TYPE_ID',
				'PROBABILITY',
				'OPPORTUNITY_ACCOUNT',
				'BEGINDATE',
				'CLOSEDATE',
				'ASSIGNED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'DATE_CREATE',
				'CREATED_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'DATE_MODIFY',
				'MODIFY_BY' => array(
					'ID',
					'SHORT_NAME',
					'NAME',
					'LAST_NAME',
					'WORK_POSITION'
				),
				'CONTACT_BY' => array(
					'ID',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME',
					'POST',
					'ADDRESS',
					'TYPE_BY.STATUS_ID',
					'COMMENTS',
					'SOURCE_BY.STATUS_ID',
					'SOURCE_DESCRIPTION',
					'DATE_CREATE',
					'DATE_MODIFY',
					'ASSIGNED_BY' => array(
						'ID',
						'SHORT_NAME',
						'NAME',
						'LAST_NAME',
						'WORK_POSITION'
					),
					'CREATED_BY' => array(
						'ID',
						'SHORT_NAME',
						'NAME',
						'LAST_NAME',
						'WORK_POSITION'
					),
					'MODIFY_BY' => array(
						'ID',
						'SHORT_NAME',
						'NAME',
						'LAST_NAME',
						'WORK_POSITION'
					)
				),
				'COMPANY_BY' => array(
					'ID',
					'TITLE',
					'COMPANY_TYPE_BY.STATUS_ID',
					'INDUSTRY_BY.STATUS_ID',
					'EMPLOYEES_BY.STATUS_ID',
					'REVENUE',
					'CURRENCY_ID',
					'COMMENTS',
					'ADDRESS',
					'ADDRESS_LEGAL',
					'BANKING_DETAILS',
					'DATE_CREATE',
					'DATE_MODIFY',
					'CREATED_BY' => array(
						'ID',
						'SHORT_NAME',
						'NAME',
						'LAST_NAME',
						'WORK_POSITION'
					),
					'MODIFY_BY' => array(
						'ID',
						'SHORT_NAME',
						'NAME',
						'LAST_NAME',
						'WORK_POSITION'
					)
				),
				'ORIGINATOR_BY.ID'
			)
		);
	}
	public static function getPeriodFilter($date_from, $date_to)
	{
		if(is_null($date_from) && is_null($date_to))
		{
			return array(); // Empty filter for empty time interval.
		}

		$filter = array('LOGIC' => 'AND');
		if(!is_null($date_to))
		{
			$filter[] = array(
				'LOGIC' => 'OR',
				'<=DEAL_OWNER.BEGINDATE' => $date_to,
				'=DEAL_OWNER.BEGINDATE' => null
			);
		}

		if(!is_null($date_from))
		{
			$filter[] = array(
				'LOGIC' => 'OR',
				'>=DEAL_OWNER.CLOSEDATE' => $date_from,
				'=DEAL_OWNER.CLOSEDATE' => null
			);
		}

		return $filter;
	}
	public static function getCompareVariations()
	{
		return array_merge(
			parent::getCompareVariations(),
			array(
				'DEAL_OWNER' => array(
					'EQUAL'
				),
				'DEAL_OWNER.STAGE_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.TYPE_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.CURRENCY_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.EVENT_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.LEAD_BY' => array(
					'EQUAL'
				),
				'DEAL_OWNER.CONTACT_BY' => array(
					'EQUAL'
				),
				'DEAL_OWNER.COMPANY_BY' => array(
					'EQUAL'
				),
				'DEAL_OWNER.LEAD_BY.STATUS_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.CONTACT_BY.TYPE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.CONTACT_BY.SOURCE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.COMPANY_BY.INDUSTRY_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				),
				'DEAL_OWNER.COMPANY_BY.EMPLOYEES_BY.STATUS_ID' => array(
					'EQUAL',
					'NOT_EQUAL'
				)
			)
		);
	}
	public static function formatResultValue($k, &$v, &$row, &$cInfo, $total)
	{
		// HACK: detect if 'report.view' component is rendering excel spreadsheet
		$isHtml = !(isset($_GET['EXCEL']) && $_GET['EXCEL'] === 'Y');

		$field = $cInfo['field'];
		$fieldName = isset($cInfo['fieldName']) ? $cInfo['fieldName'] : $field->GetName();
		$prcnt = isset($cInfo['prcnt']) ? $cInfo['prcnt'] : '';

		if(!isset($prcnt[0])
			&& ($fieldName === 'DEAL_OWNER.OPPORTUNITY'
				|| $fieldName === 'DEAL_OWNER.OPPORTUNITY_ACCOUNT'
				|| $fieldName === 'DEAL_OWNER.RECEIVED_AMOUNT'
				|| $fieldName === 'DEAL_OWNER.LOST_AMOUNT'
				|| $fieldName === 'SUM_ACCOUNT'
				|| $fieldName === 'PRICE_ACCOUNT'
				|| $fieldName === 'DEAL_OWNER.COMPANY_BY.REVENUE'))
		{
			$v = self::MoneyToString(doubleval($v));
		}
		elseif($fieldName === 'DEAL_OWNER.TITLE')
		{
			if($isHtml && strlen($v) > 0 && self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['ID']))
			{
				$v = self::prepareDealTitleHtml(self::$CURRENT_RESULT_ROW['ID'], $v);
			}
		}
		elseif($fieldName === 'DEAL_OWNER.STAGE_ID')
		{
			if($v !== '')
			{
				$v = self::getDealStageName($v, $isHtml);
			}
		}
		elseif($fieldName === 'DEAL_OWNER.TYPE_ID')
		{
			if($v !== '')
			{
				$v = self::getDealTypeName($v, $isHtml);
			}
		}
		elseif($fieldName === 'DEAL_OWNER.CURRENCY_ID' || $fieldName === 'DEAL_OWNER.COMPANY_BY.CURRENCY_ID')
		{
			if($v !== '')
			{
				$v = self::getCurrencyName($v, $isHtml);
			}
		}
		elseif($fieldName === 'DEAL_OWNER.EVENT_ID')
		{
			if($v !== '')
			{
				$v = self::getEventTypeName($v, $isHtml);
			}
		}
		elseif($fieldName === 'DEAL_OWNER.ORIGINATOR_BY.ID')
		{
			$v = self::getDealOriginatorName($v, $isHtml);
		}
		elseif($fieldName === 'DEAL_OWNER.CONTACT_BY.SOURCE_BY.STATUS_ID')
		{
			if($v !== '')
			{
				$v = self::getStatusName($v, 'SOURCE', $isHtml);
			}
		}
		elseif(strpos($fieldName, 'DEAL_OWNER.COMPANY_BY.') === 0)
		{
			if(strlen($v) === 0 || trim($v) === '.')
			{
				if(strpos($fieldName, 'DEAL_OWNER.COMPANY_BY.COMPANY_TYPE_BY') !== 0
					&& strpos($fieldName, 'DEAL_OWNER.COMPANY_BY.INDUSTRY_BY') !== 0
					&& strpos($fieldName, 'DEAL_OWNER.COMPANY_BY.EMPLOYEES_BY') !== 0)
				{
					$v = GetMessage('CRM_DEAL_COMPANY_NOT_ASSIGNED');
				}
			}
			elseif($fieldName === 'DEAL_OWNER.COMPANY_BY.TITLE')
			{
				if($isHtml && self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['CRM_PRODUCT_ROW_DEAL_OWNER_COMPANY_BY_ID']))
				{
					$v = self::prepareCompanyTitleHtml(self::$CURRENT_RESULT_ROW['CRM_PRODUCT_ROW_DEAL_OWNER_COMPANY_BY_ID'], $v);
				}
			}
			elseif($fieldName === 'DEAL_OWNER.COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'COMPANY_TYPE', $isHtml);
				}
			}
			elseif($fieldName === 'DEAL_OWNER.COMPANY_BY.INDUSTRY_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'INDUSTRY', $isHtml);
				}
			}
			elseif($fieldName === 'DEAL_OWNER.COMPANY_BY.EMPLOYEES_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'EMPLOYEES', $isHtml);
				}
			}
		}
		elseif(strpos($fieldName, 'DEAL_OWNER.CONTACT_BY.') === 0)
		{
			if($v === '' || trim($v) === '.')
			{
				if(strpos($fieldName, 'DEAL_OWNER.CONTACT_BY.TYPE_BY') !== 0)
				{
					$v = GetMessage('CRM_DEAL_CONTACT_NOT_ASSIGNED');
				}
			}
			elseif($fieldName === 'DEAL_OWNER.CONTACT_BY.TYPE_BY.STATUS_ID')
			{
				if($v !== '')
				{
					$v = self::getStatusName($v, 'CONTACT_TYPE', $isHtml);
				}
			}
			elseif($fieldName === 'DEAL_OWNER.CONTACT_BY.NAME'
				|| $fieldName === 'DEAL_OWNER.CONTACT_BY.LAST_NAME'
				|| $fieldName === 'DEAL_OWNER.CONTACT_BY.SECOND_NAME'
				|| $fieldName === 'DEAL_OWNER.CONTACT_BY.ADDRESS')
			{
				if($isHtml && self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['CRM_PRODUCT_ROW_DEAL_OWNER_CONTACT_BY_ID']))
				{
					self::prepareContactTitleHtml(self::$CURRENT_RESULT_ROW['CRM_PRODUCT_ROW_DEAL_OWNER_CONTACT_BY_ID'], $v);
				}
			}
		}
		elseif(strpos($fieldName, 'DEAL_OWNER.ASSIGNED_BY.') === 0)
		{
			if(strlen($v) === 0 || trim($v) === '.')
			{
				$v = GetMessage('CRM_DEAL_RESPONSIBLE_NOT_ASSIGNED');
			}
			elseif($isHtml)
			{
				$v = htmlspecialcharsbx($v);
			}
		}
		elseif(strpos($fieldName, 'PRODUCT.IBLOCK_ELEMENT.') === 0)
		{
			if($isHtml)
			{
				if(self::$CURRENT_RESULT_ROW && isset(self::$CURRENT_RESULT_ROW['CRM_PRODUCT_ROW_PRODUCT_IBLOCK_ELEMENT_ID']))
				{
					$v = self::prepareProductNameHtml(self::$CURRENT_RESULT_ROW['CRM_PRODUCT_ROW_PRODUCT_IBLOCK_ELEMENT_ID'], $v);
				}
				else
				{
					$v = htmlspecialcharsbx($v);
				}
			}
		}
		else
		{
			parent::formatResultValue($k, $v, $row, $cInfo, $total);
		}
	}
	public static function formatResultsTotal(&$total, &$columnInfo)
	{
		parent::formatResultsTotal($total, $columnInfo);

		if(isset($total['TOTAL_CRM_PRODUCT_ROW_DEAL_OWNER_PROBABILITY']))
		{
			// Suppress PROBABILITY (%) aggregation
			unset($total['TOTAL_CRM_PRODUCT_ROW_DEAL_OWNER_PROBABILITY']);
		}
	}
	public static function getDefaultReports()
	{
		IncludeModuleLangFile(__FILE__);

		$reports = array(
			'12.0.9' => array(
				array(
					'title' => GetMessage('CRM_REPORT_DEFAULT_PRODUCTS_PROFIT'),
					'description' => GetMessage('CRM_REPORT_DEFAULT_PRODUCTS_PROFIT_DESCR'),
					'mark_default' => 1,
					'settings' => unserialize('a:7:{s:6:"entity";s:21:"Bitrix\Crm\ProductRow";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:4:{i:0;a:2:{s:4:"name";s:27:"PRODUCT.IBLOCK_ELEMENT.NAME";s:5:"alias";s:0:"";}i:1;a:3:{s:4:"name";s:13:"DEAL_OWNER.ID";s:5:"alias";s:0:"";s:4:"aggr";s:14:"COUNT_DISTINCT";}i:2;a:3:{s:4:"name";s:8:"QUANTITY";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}i:3;a:3:{s:4:"name";s:11:"SUM_ACCOUNT";s:5:"alias";s:0:"";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:8:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:19:"DEAL_OWNER.STAGE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:3:"WON";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:18:"DEAL_OWNER.TYPE_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:47:"DEAL_OWNER.COMPANY_BY.COMPANY_TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:21:"DEAL_OWNER.COMPANY_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:4;a:5:{s:4:"type";s:5:"field";s:4:"name";s:39:"DEAL_OWNER.CONTACT_BY.TYPE_BY.STATUS_ID";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:5;a:5:{s:4:"type";s:5:"field";s:4:"name";s:21:"DEAL_OWNER.CONTACT_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:6;a:5:{s:4:"type";s:5:"field";s:4:"name";s:22:"DEAL_OWNER.ASSIGNED_BY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:3;s:9:"sort_type";s:4:"DESC";s:5:"limit";N;}')
				)
			)
		);

		foreach ($reports as &$reportByVersion)
		{
			foreach ($reportByVersion as &$report)
			{
				if ($report['mark_default'] === 1)
				{
					$report['settings']['select'][0]['alias'] = GetMessage('CRM_REPORT_ALIAS_PRODUCT');
					$report['settings']['select'][1]['alias'] = GetMessage('CRM_REPORT_ALIAS_DEALS_QUANTITY');
					$report['settings']['select'][2]['alias'] = GetMessage('CRM_REPORT_ALIAS_SOLD_PRODUCTS_QUANTITY');
					$report['settings']['select'][3]['alias'] = GetMessage('CRM_REPORT_ALIAS_SALES_PROFIT');
				}
			}
			unset($report);
		}
		unset($reportByVersion);

		return $reports;
	}
	public static function getFirstVersion()
	{
		return '12.0.9';
	}
}
?>