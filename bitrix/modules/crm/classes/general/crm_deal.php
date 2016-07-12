<?php

IncludeModuleLangFile(__FILE__);

class CAllCrmDeal
{
	static public $sUFEntityID = 'CRM_DEAL';
	public $LAST_ERROR = '';
	public $cPerms = null;
	protected $bCheckPermission = true;
	const TABLE_ALIAS = 'L';
	protected static $TYPE_NAME = 'DEAL';
	private static $DEAL_STAGES = null;

	function __construct($bCheckPermission = true)
	{
		$this->bCheckPermission = $bCheckPermission;
		$this->cPerms = CCrmPerms::GetCurrentUserPermissions();
	}

	// Service -->
	public static function GetFields($arOptions = null)
	{
		$companyJoin = 'LEFT JOIN b_crm_company CO ON L.COMPANY_ID = CO.ID';
		$contactJoin = 'LEFT JOIN b_crm_contact C ON L.CONTACT_ID = C.ID';
		$assignedByJoin = 'LEFT JOIN b_user U ON L.ASSIGNED_BY_ID = U.ID';
		$createdByJoin = 'LEFT JOIN b_user U2 ON L.CREATED_BY_ID = U2.ID';
		$modifyByJoin = 'LEFT JOIN b_user U3 ON L.MODIFY_BY_ID = U3.ID';

		$result = array(
			'ID' => array('FIELD' => 'L.ID', 'TYPE' => 'int'),
			'TITLE' => array('FIELD' => 'L.TITLE', 'TYPE' => 'string'),
			'TYPE_ID' => array('FIELD' => 'L.TYPE_ID', 'TYPE' => 'string'),
			'STAGE_ID' => array('FIELD' => 'L.STAGE_ID', 'TYPE' => 'string'),
			'PROBABILITY' => array('FIELD' => 'L.PROBABILITY', 'TYPE' => 'int'),
			'CURRENCY_ID' => array('FIELD' => 'L.CURRENCY_ID', 'TYPE' => 'string'),
			'EXCH_RATE' => array('FIELD' => 'L.EXCH_RATE', 'TYPE' => 'double'),
			'OPPORTUNITY' => array('FIELD' => 'L.OPPORTUNITY', 'TYPE' => 'double'),
			'ACCOUNT_CURRENCY_ID' => array('FIELD' => 'L.ACCOUNT_CURRENCY_ID', 'TYPE' => 'string'),
			'OPPORTUNITY_ACCOUNT' => array('FIELD' => 'L.OPPORTUNITY_ACCOUNT', 'TYPE' => 'double'),

			'LEAD_ID' => array('FIELD' => 'L.LEAD_ID', 'TYPE' => 'int'),
			'COMPANY_ID' => array('FIELD' => 'L.COMPANY_ID', 'TYPE' => 'int'),
			'COMPANY_TITLE' => array('FIELD' => 'CO.TITLE', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_INDUSTRY' => array('FIELD' => 'CO.INDUSTRY', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_EMPLOYEES' => array('FIELD' => 'CO.EMPLOYEES', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_REVENUE' => array('FIELD' => 'CO.REVENUE', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_CURRENCY_ID' => array('FIELD' => 'CO.CURRENCY_ID', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_TYPE' => array('FIELD' => 'CO.COMPANY_TYPE', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_ADDRESS' => array('FIELD' => 'CO.ADDRESS', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_ADDRESS_LEGAL' => array('FIELD' => 'CO.ADDRESS_LEGAL', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_BANKING_DETAILS' => array('FIELD' => 'CO.BANKING_DETAILS', 'TYPE' => 'string', 'FROM' => $companyJoin),
			'COMPANY_LOGO' => array('FIELD' => 'CO.LOGO', 'TYPE' => 'string', 'FROM' => $companyJoin),

			'CONTACT_ID' => array('FIELD' => 'L.CONTACT_ID', 'TYPE' => 'int'),
			'CONTACT_TYPE_ID' => array('FIELD' => 'C.TYPE_ID', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_NAME' => array('FIELD' => 'C.NAME', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_SECOND_NAME' => array('FIELD' => 'C.SECOND_NAME', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_LAST_NAME' => array('FIELD' => 'C.LAST_NAME', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_FULL_NAME' => array('FIELD' => 'C.FULL_NAME', 'TYPE' => 'string', 'FROM' => $contactJoin),

			'CONTACT_POST' => array('FIELD' => 'C.POST', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_ADDRESS' => array('FIELD' => 'C.ADDRESS', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_SOURCE_ID' => array('FIELD' => 'C.SOURCE_ID', 'TYPE' => 'string', 'FROM' => $contactJoin),
			'CONTACT_PHOTO' => array('FIELD' => 'C.PHOTO', 'TYPE' => 'string', 'FROM' => $contactJoin),

			'BEGINDATE' => array('FIELD' => 'L.BEGINDATE', 'TYPE' => 'datetime'),
			'CLOSEDATE' => array('FIELD' => 'L.CLOSEDATE', 'TYPE' => 'datetime'),

			'ASSIGNED_BY_ID' => array('FIELD' => 'L.ASSIGNED_BY_ID', 'TYPE' => 'int'),
			'ASSIGNED_BY_LOGIN' => array('FIELD' => 'U.LOGIN', 'TYPE' => 'string', 'FROM' => $assignedByJoin),
			'ASSIGNED_BY_NAME' => array('FIELD' => 'U.NAME', 'TYPE' => 'string', 'FROM' => $assignedByJoin),
			'ASSIGNED_BY_LAST_NAME' => array('FIELD' => 'U.LAST_NAME', 'TYPE' => 'string', 'FROM' => $assignedByJoin),
			'ASSIGNED_BY_SECOND_NAME' => array('FIELD' => 'U.SECOND_NAME', 'TYPE' => 'string', 'FROM' => $assignedByJoin),
			'ASSIGNED_BY_WORK_POSITION' => array('FIELD' => 'U.WORK_POSITION', 'TYPE' => 'string', 'FROM' => $assignedByJoin),
			'ASSIGNED_BY_PERSONAL_PHOTO' => array('FIELD' => 'U.PERSONAL_PHOTO', 'TYPE' => 'string', 'FROM' => $assignedByJoin),

			'CREATED_BY_ID' => array('FIELD' => 'L.CREATED_BY_ID', 'TYPE' => 'int'),
			'CREATED_BY_LOGIN' => array('FIELD' => 'U2.LOGIN', 'TYPE' => 'string', 'FROM' => $createdByJoin),
			'CREATED_BY_NAME' => array('FIELD' => 'U2.NAME', 'TYPE' => 'string', 'FROM' => $createdByJoin),
			'CREATED_BY_LAST_NAME' => array('FIELD' => 'U2.LAST_NAME', 'TYPE' => 'string', 'FROM' => $createdByJoin),
			'CREATED_BY_SECOND_NAME' => array('FIELD' => 'U2.SECOND_NAME', 'TYPE' => 'string', 'FROM' => $createdByJoin),

			'MODIFY_BY_ID' => array('FIELD' => 'L.MODIFY_BY_ID', 'TYPE' => 'int'),
			'MODIFY_BY_LOGIN' => array('FIELD' => 'U3.LOGIN', 'TYPE' => 'string', 'FROM' => $modifyByJoin),
			'MODIFY_BY_NAME' => array('FIELD' => 'U3.NAME', 'TYPE' => 'string', 'FROM' => $modifyByJoin),
			'MODIFY_BY_LAST_NAME' => array('FIELD' => 'U3.LAST_NAME', 'TYPE' => 'string', 'FROM' => $modifyByJoin),
			'MODIFY_BY_SECOND_NAME' => array('FIELD' => 'U3.SECOND_NAME', 'TYPE' => 'string', 'FROM' => $modifyByJoin),

			'DATE_CREATE' => array('FIELD' => 'L.DATE_CREATE', 'TYPE' => 'datetime'),
			'DATE_MODIFY' => array('FIELD' => 'L.DATE_MODIFY', 'TYPE' => 'datetime'),

			'OPENED' => array('FIELD' => 'L.OPENED', 'TYPE' => 'char'),
			'CLOSED' => array('FIELD' => 'L.CLOSED', 'TYPE' => 'char'),
			'COMMENTS' => array('FIELD' => 'L.COMMENTS', 'TYPE' => 'string'),
			'ADDITIONAL_INFO' => array('FIELD' => 'L.ADDITIONAL_INFO', 'TYPE' => 'string'),

			'ORIGINATOR_ID' => array('FIELD' => 'L.ORIGINATOR_ID', 'TYPE' => 'string'), //EXTERNAL SYSTEM THAT OWNS THIS ITEM
			'ORIGIN_ID' => array('FIELD' => 'L.ORIGIN_ID', 'TYPE' => 'string'), //ITEM ID IN EXTERNAL SYSTEM

			// For compatibility only
			'PRODUCT_ID' => array('FIELD' => 'L.PRODUCT_ID', 'TYPE' => 'string'),
			// Obsolete
			'EVENT_ID' => array('FIELD' => 'L.EVENT_ID', 'TYPE' => 'string'),
			'EVENT_DATE' => array('FIELD' => 'L.EVENT_DATE', 'TYPE' => 'datetime'),
			'EVENT_DESCRIPTION' => array('FIELD' => 'L.EVENT_DESCRIPTION', 'TYPE' => 'string')
		);

		// Creation of field aliases
		$result['ASSIGNED_BY'] = $result['ASSIGNED_BY_ID'];
		$result['CREATED_BY'] = $result['CREATED_BY_ID'];
		$result['MODIFY_BY'] = $result['MODIFY_BY_ID'];

		$additionalFields = is_array($arOptions) && isset($arOptions['ADDITIONAL_FIELDS'])
			? $arOptions['ADDITIONAL_FIELDS'] : null;

		if(is_array($additionalFields))
		{
			if(in_array('STAGE_SORT', $additionalFields, true))
			{
				$stageJoin = "LEFT JOIN b_crm_status ST ON ST.ENTITY_ID = 'DEAL_STAGE' AND L.STAGE_ID = ST.STATUS_ID";
				$result['STAGE_SORT'] = array('FIELD' => 'ST.SORT', 'TYPE' => 'int', 'FROM' => $stageJoin);
			}

			if(in_array('ACTIVITY', $additionalFields, true))
			{
				$commonActivityJoin = CCrmActivity::PrepareJoin(0, CCrmOwnerType::Deal, 'L', 'AC', 'UAC', 'ACUSR');

				$result['C_ACTIVITY_ID'] = array('FIELD' => 'UAC.ACTIVITY_ID', 'TYPE' => 'int', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_TIME'] = array('FIELD' => 'UAC.ACTIVITY_TIME', 'TYPE' => 'datetime', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_SUBJECT'] = array('FIELD' => 'AC.SUBJECT', 'TYPE' => 'string', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_RESP_ID'] = array('FIELD' => 'AC.RESPONSIBLE_ID', 'TYPE' => 'int', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_RESP_LOGIN'] = array('FIELD' => 'ACUSR.LOGIN', 'TYPE' => 'string', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_RESP_NAME'] = array('FIELD' => 'ACUSR.NAME', 'TYPE' => 'string', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_RESP_LAST_NAME'] = array('FIELD' => 'ACUSR.LAST_NAME', 'TYPE' => 'string', 'FROM' => $commonActivityJoin);
				$result['C_ACTIVITY_RESP_SECOND_NAME'] = array('FIELD' => 'ACUSR.SECOND_NAME', 'TYPE' => 'string', 'FROM' => $commonActivityJoin);

				$userID = CCrmPerms::GetCurrentUserID();
				if($userID > 0)
				{
					$activityJoin = CCrmActivity::PrepareJoin($userID, CCrmOwnerType::Deal, 'L', 'A', 'UA', '');

					$result['ACTIVITY_ID'] = array('FIELD' => 'UA.ACTIVITY_ID', 'TYPE' => 'int', 'FROM' => $activityJoin);
					$result['ACTIVITY_TIME'] = array('FIELD' => 'UA.ACTIVITY_TIME', 'TYPE' => 'datetime', 'FROM' => $activityJoin);
					$result['ACTIVITY_SUBJECT'] = array('FIELD' => 'A.SUBJECT', 'TYPE' => 'string', 'FROM' => $activityJoin);
				}
			}
		}

		return $result;
	}

	public static function __AfterPrepareSql(/*CCrmEntityListBuilder*/ $sender, $arOrder, $arFilter, $arGroupBy, $arSelectFields)
	{
		// Applying filter by PRODUCT_ID
		$prodID = isset($arFilter['PRODUCT_ROW_PRODUCT_ID']) ? intval($arFilter['PRODUCT_ROW_PRODUCT_ID']) : 0;
		if($prodID <= 0)
		{
			return false;
		}

		$a = $sender->GetTableAlias();
		return array(
			'WHERE' => "$a.ID IN (SELECT DP.OWNER_ID from b_crm_product_row DP where DP.OWNER_TYPE = 'D' and DP.OWNER_ID = $a.ID and DP.PRODUCT_ID = $prodID)"
		);
	}
	// <-- Service

	public static function GetUserFieldEntityID()
	{
		return self::$sUFEntityID;
	}

	public static function GetUserFields()
	{
		global $USER_FIELD_MANAGER;
		return $USER_FIELD_MANAGER->GetUserFields(self::$sUFEntityID);
	}

	// GetList with navigation support
	public static function GetListEx($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array(), $arOptions = array())
	{
		$lb = new CCrmEntityListBuilder(
			CCrmDeal::DB_TYPE,
			CCrmDeal::TABLE_NAME,
			self::TABLE_ALIAS,
			self::GetFields(isset($arOptions['FIELD_OPTIONS']) ? $arOptions['FIELD_OPTIONS'] : null),
			self::$sUFEntityID,
			'DEAL',
			array('CCrmDeal', 'BuildPermSql'),
			array('CCrmDeal', '__AfterPrepareSql')
		);

		return $lb->Prepare($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields, $arOptions);
	}

	/**
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param array $arSelect
	 * @return CDBResult
	 * Obsolete. Always select all record from database. Please use GetListEx instead.
	 */
	public static function GetList($arOrder = Array('DATE_CREATE' => 'DESC'), $arFilter = Array(), $arSelect = Array(), $nPageTop = false)
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		// fields
		$arFields = array(
			'ID' => 'L.ID',
			'COMMENTS' => 'L.COMMENTS',
			'ADDITIONAL_INFO' => 'L.ADDITIONAL_INFO',
			'TITLE' => 'L.TITLE',
			'LEAD_ID' => 'L.LEAD_ID',
			'COMPANY_ID' => 'L.COMPANY_ID',
			'COMPANY_TITLE' => 'C.TITLE',
			'CONTACT_ID' => 'L.CONTACT_ID',
			'CONTACT_FULL_NAME' => 'CT.FULL_NAME',
			/*'STATE_ID' => 'L.STATE_ID',*/
			'STAGE_ID' => 'L.STAGE_ID',
			'CLOSED' => 'L.CLOSED',
			'TYPE_ID' => 'L.TYPE_ID',
			'PRODUCT_ID' => 'L.PRODUCT_ID',
			'PROBABILITY' => 'L.PROBABILITY',
			'OPPORTUNITY' => 'L.OPPORTUNITY',
			'CURRENCY_ID' => 'L.CURRENCY_ID',
			'OPPORTUNITY_ACCOUNT' => 'L.OPPORTUNITY_ACCOUNT',
			'ACCOUNT_CURRENCY_ID' => 'L.ACCOUNT_CURRENCY_ID',
			'BEGINDATE' => $DB->DateToCharFunction('L.BEGINDATE'),
			'CLOSEDATE' => $DB->DateToCharFunction('L.CLOSEDATE'),
			'EVENT_ID' => 'L.EVENT_ID',
			'EVENT_DATE' => $DB->DateToCharFunction('L.EVENT_DATE'),
			'EVENT_DESCRIPTION' => 'L.EVENT_DESCRIPTION',
			'ASSIGNED_BY' => 'L.ASSIGNED_BY_ID',
			'ASSIGNED_BY_ID' => 'L.ASSIGNED_BY_ID',
			'CREATED_BY' => 'L.CREATED_BY_ID',
			'CREATED_BY_ID' => 'L.CREATED_BY_ID',
			'MODIFY_BY' => 'L.MODIFY_BY_ID',
			'MODIFY_BY_ID' => 'L.MODIFY_BY_ID',
			'DATE_CREATE' => $DB->DateToCharFunction('L.DATE_CREATE'),
			'DATE_MODIFY' => $DB->DateToCharFunction('L.DATE_MODIFY'),
			'OPENED' => 'L.OPENED',
			'EXCH_RATE' => 'L.EXCH_RATE',
			'ORIGINATOR_ID' => 'L.ORIGINATOR_ID', //EXTERNAL SYSTEM THAT OWNS THIS ITEM
			'ORIGIN_ID' => 'L.ORIGIN_ID', //ITEM ID IN EXTERNAL SYSTEM
			'ASSIGNED_BY_LOGIN' => 'U.LOGIN',
			'ASSIGNED_BY_NAME' => 'U.NAME',
			'ASSIGNED_BY_LAST_NAME' => 'U.LAST_NAME',
			'ASSIGNED_BY_SECOND_NAME' => 'U.SECOND_NAME',
			'CREATED_BY_LOGIN' => 'U2.LOGIN',
			'CREATED_BY_NAME' => 'U2.NAME',
			'CREATED_BY_LAST_NAME' => 'U2.LAST_NAME',
			'CREATED_BY_SECOND_NAME' => 'U2.SECOND_NAME',
			'MODIFY_BY_LOGIN' => 'U3.LOGIN',
			'MODIFY_BY_NAME' => 'U3.NAME',
			'MODIFY_BY_LAST_NAME' => 'U3.LAST_NAME',
			'MODIFY_BY_SECOND_NAME' => 'U3.SECOND_NAME'
		);

		$arSqlSelect = array();
		$sSqlJoin = '';
		if (count($arSelect) == 0)
			$arSelect = array_merge(array_keys($arFields), array('UF_*'));

		$obQueryWhere = new CSQLWhere();
		$arFilterField = $arSelect;
		foreach ($arFilter as $sKey => $sValue)
		{
			$arField = $obQueryWhere->MakeOperation($sKey);
			$arFilterField[] = $arField['FIELD'];
		}

		if (in_array('ASSIGNED_BY_LOGIN', $arFilterField) || in_array('ASSIGNED_BY', $arFilterField))
		{
			$arSelect[] = 'ASSIGNED_BY_LOGIN';
			$arSelect[] = 'ASSIGNED_BY_NAME';
			$arSelect[] = 'ASSIGNED_BY_LAST_NAME';
			$arSelect[] = 'ASSIGNED_BY_SECOND_NAME';
			$sSqlJoin .= ' LEFT JOIN b_user U ON L.ASSIGNED_BY_ID = U.ID ';
		}
		if (in_array('CREATED_BY_LOGIN', $arFilterField))
		{
			$arSelect[] = 'CREATED_BY';
			$arSelect[] = 'CREATED_BY_LOGIN';
			$arSelect[] = 'CREATED_BY_NAME';
			$arSelect[] = 'CREATED_BY_LAST_NAME';
			$arSelect[] = 'CREATED_BY_SECOND_NAME';
			$sSqlJoin .= ' LEFT JOIN b_user U2 ON L.CREATED_BY_ID = U2.ID ';
		}
		if (in_array('MODIFY_BY_LOGIN', $arFilterField))
		{
			$arSelect[] = 'MODIFY_BY';
			$arSelect[] = 'MODIFY_BY_LOGIN';
			$arSelect[] = 'MODIFY_BY_NAME';
			$arSelect[] = 'MODIFY_BY_LAST_NAME';
			$arSelect[] = 'MODIFY_BY_SECOND_NAME';
			$sSqlJoin .= ' LEFT JOIN b_user U3 ON  L.MODIFY_BY_ID = U3.ID ';
		}
		if (in_array('COMPANY_ID', $arFilterField) || in_array('COMPANY_TITLE', $arFilterField))
		{
			$arSelect[] = 'COMPANY_ID';
			$arSelect[] = 'COMPANY_TITLE';
			$sSqlJoin .= ' LEFT JOIN b_crm_company C ON L.COMPANY_ID = C.ID ';
		}
		if (in_array('CONTACT_ID', $arFilterField) || in_array('CONTACT_FULL_NAME', $arFilterField))
		{
			$arSelect[] = 'CONTACT_ID';
			$arSelect[] = 'CONTACT_FULL_NAME';
			$sSqlJoin .= ' LEFT JOIN b_crm_contact CT ON L.CONTACT_ID = CT.ID ';
		}


		foreach($arSelect as $field)
		{
			$field = strtoupper($field);
			if (array_key_exists($field, $arFields))
				$arSqlSelect[$field] = $arFields[$field].($field != '*' ? ' AS '.$field : '');
		}

		if (!isset($arSqlSelect['ID']))
			$arSqlSelect['ID'] = $arFields['ID'];
		$sSqlSelect = implode(",\n", $arSqlSelect);

		$obUserFieldsSql = new CUserTypeSQL();
		$obUserFieldsSql->SetEntity(self::$sUFEntityID, 'L.ID');
		$obUserFieldsSql->SetSelect($arSelect);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		$arSqlSearch = array();
		// check permissions
		$sSqlPerm = '';
		if (!(is_object($USER) && $USER->IsAdmin())
			&& (!array_key_exists('CHECK_PERMISSIONS', $arFilter) || $arFilter['CHECK_PERMISSIONS'] !== 'N')
		)
		{
			$arPermType = array();
			if (!isset($arFilter['PERMISSION']))
				$arPermType[] = 'READ';
			else
				$arPermType	= is_array($arFilter['PERMISSION']) ? $arFilter['PERMISSION'] : array($arFilter['PERMISSION']);

			$sSqlPerm = self::BuildPermSql('L', $arPermType);
			if ($sSqlPerm === false)
			{
				$CDBResult = new CDBResult();
				$CDBResult->InitFromArray(array());
				return $CDBResult;
			}
			if(strlen($sSqlPerm) > 0)
			{
				$sSqlPerm = ' AND '.$sSqlPerm;
			}
		}

		// where
		$arWhereFields = array(
			'ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'LEAD_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.LEAD_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'CONTACT_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CONTACT_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'CONTACT_FULL_NAME' => array(
				'TABLE_ALIAS' => 'CT',
				'FIELD_NAME' => 'CT.FULL_NAME',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'COMPANY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.COMPANY_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'COMPANY_TITLE' => array(
				'TABLE_ALIAS' => 'C',
				'FIELD_NAME' => 'C.TITLE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'STATE_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.STATE_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'STAGE_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.STAGE_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'TYPE_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.TYPE_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'PRODUCT_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.PRODUCT_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'CURRENCY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CURRENCY_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'OPPORTUNITY' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.OPPORTUNITY',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'ACCOUNT_CURRENCY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ACCOUNT_CURRENCY_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'OPPORTUNITY_ACCOUNT' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.OPPORTUNITY_ACCOUNT',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'TITLE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.TITLE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'CLOSED' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CLOSED',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'COMMENTS' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.COMMENTS',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'ADDITIONAL_INFO' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ADDITIONAL_INFO',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'DATE_CREATE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.DATE_CREATE',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'BEGINDATE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.BEGINDATE',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'CLOSEDATE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CLOSEDATE',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'EVENT_DATE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.EVENT_DATE',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'DATE_MODIFY' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.DATE_MODIFY',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'PROBABILITY' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.PROBABILITY',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'EVENT_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.EVENT_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'CREATED_BY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CREATED_BY_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'ASSIGNED_BY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ASSIGNED_BY_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'OPENED' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.OPENED',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'MODIFY_BY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.MODIFY_BY_ID',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'EXCH_RATE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.EXCH_RATE',
				'FIELD_TYPE' => 'int',
				'JOIN' => false
			),
			'ORIGINATOR_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ORIGINATOR_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'ORIGIN_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ORIGIN_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			)
		);

		$obQueryWhere->SetFields($arWhereFields);
		if (!is_array($arFilter))
			$arFilter = array();
		$sQueryWhereFields = $obQueryWhere->GetQuery($arFilter);

		$sSqlSearch = '';
		foreach($arSqlSearch as $r)
			if (strlen($r) > 0)
				$sSqlSearch .= "\n\t\t\t\tAND  ($r) ";
		$CCrmUserType = new CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], self::$sUFEntityID);
		$CCrmUserType->ListPrepareFilter($arFilter);
		$r = $obUserFieldsSql->GetFilter();
		if (strlen($r) > 0)
			$sSqlSearch .= "\n\t\t\t\tAND ($r) ";

		if (!empty($sQueryWhereFields))
			$sSqlSearch .= "\n\t\t\t\tAND ($sQueryWhereFields) ";

		$arFieldsOrder = array(
			'ASSIGNED_BY' => 'L.ASSIGNED_BY_ID',
			'CREATED_BY' => 'L.CREATED_BY_ID',
			'MODIFY_BY' => 'L.MODIFY_BY_ID',
			'DATE_CREATE' => 'L.DATE_CREATE',
			'DATE_MODIFY' => 'L.DATE_MODIFY'
		);

		// order
		$arSqlOrder = Array();
		if (!is_array($arOrder))
			$arOrder = Array('DATE_CREATE' => 'DESC');
		foreach($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			$order = strtolower($order);
			if ($order != 'asc')
				$order = 'desc';

			if (isset($arFieldsOrder[$by]))
				$arSqlOrder[$by] = " {$arFieldsOrder[$by]} $order ";
			else if (isset($arFields[$by]) && $by != 'ADDRESS')
				$arSqlOrder[$by] = " L.$by $order ";
			else if ($s = $obUserFieldsSql->GetOrder($by))
				$arSqlOrder[$by] = " $s $order ";
			else
			{
				$by = 'date_create';
				$arSqlOrder[$by] = " L.DATE_CREATE $order ";
			}
		}

		if (count($arSqlOrder) > 0)
			$sSqlOrder = "\n\t\t\t\tORDER BY ".implode(', ', $arSqlOrder);
		else
			$sSqlOrder = '';

		$sSql = "
			SELECT
				$sSqlSelect
				{$obUserFieldsSql->GetSelect()}
			FROM
				b_crm_deal L $sSqlJoin
				{$obUserFieldsSql->GetJoin('L.ID')}
			WHERE
				1=1 $sSqlSearch
				$sSqlPerm
			$sSqlOrder";

		if ($nPageTop !== false)
		{
			$nPageTop = (int) $nPageTop;
			$sSql = $DB->TopSql($sSql, $nPageTop);
		}

		$obRes = $DB->Query($sSql, false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		$obRes->SetUserFields($USER_FIELD_MANAGER->GetUserFields(self::$sUFEntityID));
		return $obRes;
	}

	public static function GetByID($ID, $bCheckPerms = true)
	{
		$arFilter = array('=ID' => intval($ID));
		if (!$bCheckPerms)
		{
			$arFilter['CHECK_PERMISSIONS'] = 'N';
		}

		$dbRes = CCrmDeal::GetListEx(array(), $arFilter);
		return $dbRes->Fetch();
	}

	public static function Exists($ID)
	{
		$ID = intval($ID);
		if($ID <= 0)
		{
			return false;
		}

		$dbRes = self::GetListEx(
			array(),
			array('ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
			false,
			false,
			array('ID')
		);

		return is_array($dbRes->Fetch());
	}

	public static function GetRightSiblingID($ID)
	{
		$ID = intval($ID);
		if($ID <= 0)
		{
			return 0;
		}

		$dbRes = self::GetListEx(
			array('ID' => 'ASC'),
			array('>ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
			false,
			array('nTopCount' => 1),
			array('ID')
		);

		$arRes =  $dbRes->Fetch();
		if(!is_array($arRes))
		{
			return 0;
		}

		return intval($arRes['ID']);
	}

	public static function GetLeftSiblingID($ID)
	{
		$ID = intval($ID);
		if($ID <= 0)
		{
			return 0;
		}

		$dbRes = self::GetListEx(
			array('ID' => 'DESC'),
			array('<ID' => $ID, 'CHECK_PERMISSIONS' => 'N'),
			false,
			array('nTopCount' => 1),
			array('ID')
		);

		$arRes =  $dbRes->Fetch();
		if(!is_array($arRes))
		{
			return 0;
		}

		return intval($arRes['ID']);
	}

	static public function BuildPermSql($sAliasPrefix = 'L', $mPermType = 'READ')
	{
		$sSqlPerm = '';
		if (CCrmPerms::IsAdmin())
			return $sSqlPerm;

		$CCrmPerms = CCrmPerms::GetCurrentUserPermissions();
		$arUserAttr = array();
		$arPermType	= is_array($mPermType) ? $mPermType : array($mPermType);

		foreach ($arPermType as $sPermType)
			$arUserAttr = array_merge($arUserAttr, $CCrmPerms->GetUserAttrForSelectEntity('DEAL', $sPermType));

		if (empty($arUserAttr))
			return false;

		$arUserPerm = array();
		foreach ($arUserAttr as $_arAttr)
		{
			if (empty($_arAttr))
				continue;
			$_icnt = count($_arAttr);
			$_idcnt = -1;
			foreach ($_arAttr as $sAttr)
				if ($sAttr[0] == 'D')
					$_idcnt++;
			if ($_icnt == 1 && ($_idcnt == 1 || $_idcnt == -1))
				$_idcnt = 0;

			$arUserPerm[] = "SUM(CASE WHEN {$sAliasPrefix}P.ATTR = '".implode("' or {$sAliasPrefix}P.ATTR = '", $_arAttr)."' THEN 1 ELSE 0 END) = ".($_icnt - $_idcnt);
		}

		if (!empty($arUserPerm))
			$sSqlPerm = "
					EXISTS(
						SELECT 1
						FROM b_crm_entity_perms {$sAliasPrefix}P
						WHERE {$sAliasPrefix}P.ENTITY = 'DEAL' AND {$sAliasPrefix}.ID = {$sAliasPrefix}P.ENTITY_ID
						GROUP BY {$sAliasPrefix}P.ENTITY, {$sAliasPrefix}P.ENTITY_ID
						HAVING ".implode(" \n\t\t\t\t\t\t\t\tOR ", $arUserPerm).'
					)';

		return $sSqlPerm;
	}

	public function Add(&$arFields, $bUpdateSearch = true, $options = array())
	{
		global $DB, $USER;

		$this->LAST_ERROR = '';
		$iUserId = is_object($USER) ? intval($USER->GetID()) : 0;

		if (isset($arFields['ID']))
			unset($arFields['ID']);

		if (isset($arFields['DATE_CREATE']))
			unset($arFields['DATE_CREATE']);
		$arFields['~DATE_CREATE'] = $DB->CurrentTimeFunction();
		$arFields['~DATE_MODIFY'] = $DB->CurrentTimeFunction();

		if (!isset($arFields['CREATED_BY_ID']) || (int)$arFields['CREATED_BY_ID'] <= 0)
			$arFields['CREATED_BY_ID'] = $iUserId;
		if (!isset($arFields['MODIFY_BY_ID']) || (int)$arFields['MODIFY_BY_ID'] <= 0)
			$arFields['MODIFY_BY_ID'] = $iUserId;

		if(isset($arFields['ASSIGNED_BY_ID']) && is_array($arFields['ASSIGNED_BY_ID']))
		{
			$arFields['ASSIGNED_BY_ID'] = count($arFields['ASSIGNED_BY_ID']) > 0 ? intval($arFields['ASSIGNED_BY_ID'][0]) : $iUserId;
		}

		if (!isset($arFields['ASSIGNED_BY_ID']) || (int)$arFields['ASSIGNED_BY_ID'] <= 0)
			$arFields['ASSIGNED_BY_ID'] = $iUserId;

		$result = true;
		if (!$this->CheckFields($arFields, false, $options))
		{
			$result = false;
			$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
		}
		else
		{
			if (!isset($arFields['STAGE_ID']))
				$arFields['STAGE_ID'] = 'NEW';
			$arAttr = array();
			if (!empty($arFields['STAGE_ID']))
				$arAttr['STAGE_ID'] = $arFields['STAGE_ID'];
			if (!empty($arFields['OPENED']))
				$arAttr['OPENED'] = $arFields['OPENED'];
			$arEntityAttr = self::BuildEntityAttr(
				$arFields['ASSIGNED_BY_ID'],
				$arAttr
			);

			$sPermission = 'ADD';
			if (isset($arFields['PERMISSION']))
			{
				if ($arFields['PERMISSION'] == 'IMPORT')
					$sPermission = 'IMPORT';
				unset($arFilter['PERMISSION']);
			}

			$sEntityPerm = $this->cPerms->GetPermType('DEAL', $sPermission, $arEntityAttr);
			$this->PrepareEntityAttrs($arEntityAttr, $sEntityPerm);

			if ($this->bCheckPermission)
			{
				if ($sEntityPerm == BX_CRM_PERM_NONE)
				{
					$this->LAST_ERROR = GetMessage('CRM_PERMISSION_DENIED');
					$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
					return false;
				}
				else if ($sEntityPerm == BX_CRM_PERM_SELF)
					$arFields['ASSIGNED_BY_ID'] = $iUserId;
				else if ($sEntityPerm == BX_CRM_PERM_OPEN)
					$arFields['OPENED'] = 'Y';
			}

			// Calculation of Account Data
			$accData = CCrmAccountingHelper::PrepareAccountingData(
				array(
					'CURRENCY_ID' => isset($arFields['CURRENCY_ID']) ? $arFields['CURRENCY_ID'] : null,
					'SUM' => isset($arFields['OPPORTUNITY']) ? $arFields['OPPORTUNITY'] : null,
					'EXCH_RATE' => isset($arFields['EXCH_RATE']) ? $arFields['EXCH_RATE'] : null
				)
			);

			if(is_array($accData))
			{
				$arFields['ACCOUNT_CURRENCY_ID'] = $accData['ACCOUNT_CURRENCY_ID'];
				$arFields['OPPORTUNITY_ACCOUNT'] = $accData['ACCOUNT_SUM'];
			}

			$arFields['CLOSED'] = ($arFields['STAGE_ID'] === 'WON' || $arFields['STAGE_ID'] === 'LOSE') ? 'Y' : 'N';

			$now = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL', SITE_ID);
			if (!isset($arFields['BEGINDATE'][0]))
			{
				$arFields['BEGINDATE'] = $now;
			}

			if($arFields['CLOSED'] === 'Y'
				&& (!isset($arFields['CLOSEDATE']) || $arFields['CLOSEDATE'] === ''))
			{
				$arFields['CLOSEDATE'] = $now;
			}

			$ID = intval($DB->Add('b_crm_deal', $arFields, array(), 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__));

			CCrmEntityHelper::NormalizeUserFields($arFields, self::$sUFEntityID, $GLOBALS['USER_FIELD_MANAGER']);
			$GLOBALS['USER_FIELD_MANAGER']->Update(self::$sUFEntityID, $ID, $arFields);
			CCrmPerms::UpdateEntityAttr('DEAL', $ID, $arEntityAttr);

			if($bUpdateSearch)
			{
				$arFilterTmp = Array('ID' => $ID);
				if (!$this->bCheckPermission)
					$arFilterTmp["CHECK_PERMISSIONS"] = "N";
				CCrmSearch::UpdateSearch($arFilterTmp, 'DEAL', true);
			}

			$result = $ID;
			$arFields['ID'] = &$result;

			if (isset($GLOBALS["USER"]) && isset($arFields['COMPANY_ID']) && intval($arFields['COMPANY_ID']) > 0)
			{
				if (!class_exists('CUserOptions'))
					include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/'.$GLOBALS['DBType'].'/favorites.php');

				CUserOptions::SetOption('crm', 'crm_company_search', array('last_selected' => $arFields['COMPANY_ID']));
			}

			if (isset($GLOBALS["USER"]) && isset($arFields['CONTACT_ID']) && intval($arFields['CONTACT_ID']) > 0)
			{
				if (!class_exists('CUserOptions'))
					include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/".$GLOBALS['DBType']."/favorites.php");

				CUserOptions::SetOption("crm", "crm_contact_search", array('last_selected' => $arFields['CONTACT_ID']));
			}

			if (isset($arFields['FM']) && is_array($arFields['FM']))
			{
				$CCrmFieldMulti = new CCrmFieldMulti();
				$CCrmFieldMulti->SetFields('DEAL', $ID, $arFields['FM']);
			}

			$events = GetModuleEvents('crm', 'OnAfterCrmDealAdd');
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array(&$arFields));
		}

		return $result;
	}

	public function CheckFields(&$arFields, $ID = false, $options = array())
	{
		global $APPLICATION, $USER_FIELD_MANAGER;
		$this->LAST_ERROR = '';

		if (($ID == false || isset($arFields['TITLE'])) && empty($arFields['TITLE']))
			$this->LAST_ERROR .= GetMessage('CRM_ERROR_FIELD_IS_MISSING', array('%FIELD_NAME%' => GetMessage('CRM_FIELD_TITLE')))."<br />\n";

		if (!empty($arFields['BEGINDATE']) && !CheckDateTime($arFields['BEGINDATE']))
			$this->LAST_ERROR .= GetMessage('CRM_ERROR_FIELD_INCORRECT', array('%FIELD_NAME%' => GetMessage('CRM_FIELD_BEGINDATE')))."<br />\n";

		if (!empty($arFields['CLOSEDATE']) && !CheckDateTime($arFields['CLOSEDATE']))
			$this->LAST_ERROR .= GetMessage('CRM_ERROR_FIELD_INCORRECT', array('%FIELD_NAME%' => GetMessage('CRM_FIELD_CLOSEDATE')))."<br />\n";

		if (!empty($arFields['EVENT_DATE']) && !CheckDateTime($arFields['EVENT_DATE']))
			$this->LAST_ERROR .= GetMessage('CRM_ERROR_FIELD_INCORRECT', array('%FIELD_NAME%' => GetMessage('CRM_FIELD_EVENT_DATE')))."<br />\n";

		if(is_string($arFields['OPPORTUNITY']) && $arFields['OPPORTUNITY'] !== '')
		{
			$arFields['OPPORTUNITY'] = str_replace(array(',', ' '), array('.', ''), $arFields['OPPORTUNITY']);
			//HACK: MSSQL returns '.00' for zero value
			if(strpos($arFields['OPPORTUNITY'], '.') === 0)
			{
				$arFields['OPPORTUNITY'] = '0'.$arFields['OPPORTUNITY'];
			}

			if (!preg_match('/^\d{1,}(\.\d{1,})?$/', $arFields['OPPORTUNITY']))
			{
				$this->LAST_ERROR .= GetMessage('CRM_DEAL_FIELD_OPPORTUNITY_INVALID')."<br />\n";
			}
		}

		if (!empty($arFields['PROBABILITY']))
		{
			$arFields['PROBABILITY'] = intval($arFields['PROBABILITY']);
			if ($arFields['PROBABILITY'] > 100)
				$arFields['PROBABILITY'] = 100;
		}

		$enableUserFildCheck = !(is_array($options) && isset($options['DISABLE_USER_FIELD_CHECK']) && $options['DISABLE_USER_FIELD_CHECK'] === true);
		if ($enableUserFildCheck)
		{
			// We have to prepare field data before check (issue #22966)
			CCrmEntityHelper::NormalizeUserFields($arFields, self::$sUFEntityID, $USER_FIELD_MANAGER);
			if(!$USER_FIELD_MANAGER->CheckFields(self::$sUFEntityID, $ID, $arFields))
			{
				$e = $APPLICATION->GetException();
				$this->LAST_ERROR .= $e->GetString();
			}
		}

		if (strlen($this->LAST_ERROR) > 0)
			return false;

		return true;
	}

	static public function BuildEntityAttr($USER_ID, $arAttr = array())
	{
		global $DB;

		$USER_ID = (int)$USER_ID;

		$arUserAttr = CCrmPerms::GetUserAttr($USER_ID);
		$_arAttr = array();
		$_arAttr[] = 'U'.$USER_ID;
		if (!isset($arAttr['INTRANET']))
			$arAttr['INTRANET'] = array();
		if (!empty($arUserAttr['INTRANET']))
			$arAttr['INTRANET'] = $arUserAttr['INTRANET'];
		$_arAttr = array_merge($_arAttr, $arAttr['INTRANET']);

		if (isset($arAttr['STAGE_ID']))
			$_arAttr[] = 'STAGE_ID'.$arAttr['STAGE_ID'];
		if (isset($arAttr['OPENED']) && $arAttr['OPENED'] == 'Y')
			$_arAttr[] = 'O';

		return 	$_arAttr;
	}

	private function PrepareEntityAttrs(&$arEntityAttr, $entutyPermType)
	{
		// Ensure that entity accessable for user restricted by BX_CRM_PERM_OPEN
		if($entutyPermType === BX_CRM_PERM_OPEN && !in_array('O', $arEntityAttr, true))
		{
			$arEntityAttr[] = 'O';
		}
	}

	public function Update($ID, &$arFields, $bCompare = true, $bUpdateSearch = true, $options = array())
	{
		global $USER, $DB;

		$this->LAST_ERROR = '';
		$ID = (int) $ID;

		$arFilterTmp = Array('ID' => $ID);
		if (!$this->bCheckPermission)
			$arFilterTmp["CHECK_PERMISSIONS"] = "N";

		$obRes = self::GetList(Array(), $arFilterTmp);
		if (!($arRow = $obRes->Fetch()))
			return false;

		$iUserId = is_object($USER) ? intval($USER->GetID()) : 0;

		if (isset($arFields['DATE_CREATE']))
			unset($arFields['DATE_CREATE']);
		if (isset($arFields['DATE_MODIFY']))
			unset($arFields['DATE_MODIFY']);
		$arFields['~DATE_MODIFY'] = $DB->CurrentTimeFunction();

		if (!isset($arFields['MODIFY_BY_ID']) || (int)$arFields['MODIFY_BY_ID'] <= 0)
			$arFields['MODIFY_BY_ID'] = $iUserId;
		if (isset($arFields['ASSIGNED_BY_ID']) && (int)$arFields['ASSIGNED_BY_ID'] <= 0)
			unset($arFields['ASSIGNED_BY_ID']);

		$bResult = false;
		if (!$this->CheckFields($arFields, $ID, $options))
			$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
		else
		{
			$arAttr = array();
			$arAttr['STAGE_ID'] = !empty($arFields['STAGE_ID']) ? $arFields['STAGE_ID'] : $arRow['STAGE_ID'];
			$arAttr['OPENED'] = !empty($arFields['OPENED']) ? $arFields['OPENED'] : $arRow['OPENED'];

			$arEntityAttr = self::BuildEntityAttr(
				!empty($arFields['ASSIGNED_BY_ID']) ? $arFields['ASSIGNED_BY_ID'] : $arRow['ASSIGNED_BY_ID'],
				$arAttr
			);

			$sEntityPerm = $this->cPerms->GetPermType('DEAL', 'WRITE', $arEntityAttr);
			$this->PrepareEntityAttrs($arEntityAttr, $sEntityPerm);

			if ($this->bCheckPermission)
			{
				$sWherePerm = '';
				if ($sEntityPerm == BX_CRM_PERM_NONE)
				{
					$this->LAST_ERROR = GetMessage('CRM_PERMISSION_DENIED');
					$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
					return false;
				}
				else if ($sEntityPerm == BX_CRM_PERM_SELF)
					$sWherePerm = ' AND ASSIGNED_BY_ID = '.$iUserId;
				else if ($sEntityPerm == BX_CRM_PERM_OPEN)
				{
					// Mark entity as OPENED for make it accessable for user restricted by BX_CRM_PERM_OPEN
					$arFields['OPENED'] = 'Y';

					$sWherePerm = " AND OPENED = 'Y'";
				}
			}

			if (isset($arFields['ASSIGNED_BY_ID']) && $arRow['ASSIGNED_BY_ID'] != $arFields['ASSIGNED_BY_ID'])
				CCrmEvent::SetAssignedByElement($arFields['ASSIGNED_BY_ID'], 'DEAL', $ID);

			if ($bCompare)
			{
				$arEvents = self::CompareFields($arRow, $arFields, $this->bCheckPermission);
				foreach($arEvents as $arEvent)
				{
					$arEvent['ENTITY_TYPE'] = 'DEAL';
					$arEvent['ENTITY_ID'] = $ID;
					$arEvent['EVENT_TYPE'] = 1;
					if (!isset($arEvent['USER_ID']))
						$arEvent['USER_ID'] = $iUserId;

					$CCrmEvent = new CCrmEvent();
					$CCrmEvent->Add($arEvent, $this->bCheckPermission);
				}
			}

			// Calculation of Account Data
			$accData = CCrmAccountingHelper::PrepareAccountingData(
				array(
					'CURRENCY_ID' => isset($arFields['CURRENCY_ID']) ? $arFields['CURRENCY_ID'] : (isset($arRow['CURRENCY_ID']) ? $arRow['CURRENCY_ID'] : null),
					'SUM' => isset($arFields['OPPORTUNITY']) ? $arFields['OPPORTUNITY'] : (isset($arRow['OPPORTUNITY']) ? $arRow['OPPORTUNITY'] : null),
					'EXCH_RATE' => isset($arFields['EXCH_RATE']) ? $arFields['EXCH_RATE'] : (isset($arRow['EXCH_RATE']) ? $arRow['EXCH_RATE'] : null)
				)
			);

			if(is_array($accData))
			{
				$arFields['ACCOUNT_CURRENCY_ID'] = $accData['ACCOUNT_CURRENCY_ID'];
				$arFields['OPPORTUNITY_ACCOUNT'] = $accData['ACCOUNT_SUM'];
			}

			if(isset($arFields['STAGE_ID']))
			{
				$arFields['CLOSED'] = ($arFields['STAGE_ID'] === 'WON' || $arFields['STAGE_ID'] === 'LOSE') ? 'Y' : 'N';
			}

			if (isset($arFields['BEGINDATE']) && !isset($arFields['BEGINDATE'][0]))
			{
				unset($arFields['BEGINDATE']);
			}

			if(isset($arFields['CLOSED'])
				&& $arFields['CLOSED'] === 'Y'
				&& (!isset($arFields['CLOSEDATE'])
					|| $arFields['CLOSEDATE'] === ''))
			{
				$arFields['CLOSEDATE'] = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL', SITE_ID);
			}

			unset($arFields['ID']);
			$sUpdate = $DB->PrepareUpdate('b_crm_deal', $arFields);


			if (strlen($sUpdate) > 0)
			{
				$DB->Query("UPDATE b_crm_deal SET $sUpdate WHERE ID = $ID $sWherePerm", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
				$bResult = true;
			}

			CCrmPerms::UpdateEntityAttr(
				'DEAL',
				$ID,
				$arEntityAttr
			);

			CCrmEntityHelper::NormalizeUserFields($arFields, self::$sUFEntityID, $GLOBALS['USER_FIELD_MANAGER']);
			$GLOBALS['USER_FIELD_MANAGER']->Update(self::$sUFEntityID, $ID, $arFields);

			if($bUpdateSearch)
			{
				$arFilterTmp = Array('ID' => $ID);
				if (!$this->bCheckPermission)
					$arFilterTmp["CHECK_PERMISSIONS"] = "N";
				CCrmSearch::UpdateSearch($arFilterTmp, 'DEAL', true);
			}

			$arFields['ID'] = $ID;

			if (isset($arFields['FM']) && is_array($arFields['FM']))
			{
				$CCrmFieldMulti = new CCrmFieldMulti();
				$CCrmFieldMulti->SetFields('DEAL', $ID, $arFields['FM']);
			}

			// Responsible user sync
			//CCrmActivity::Synchronize(CCrmOwnerType::Deal, $ID);

			$events = GetModuleEvents('crm', 'OnAfterCrmDealUpdate');
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array(&$arFields));

			self::PullChange('UPDATE', array('ID' => $ID));
		}
		return $bResult;
	}

	public function Delete($ID)
	{
		global $DB, $APPLICATION, $USER;

		$ID = intval($ID);
		$iUserId = is_object($USER) ? intval($USER->GetID()) : 0;

		if ($this->bCheckPermission)
		{
			$arEntityAttr = $this->cPerms->GetEntityAttr('DEAL', $ID);
			$sWherePerm = '';
			$sEntityPerm = $this->cPerms->GetPermType('DEAL', 'DELETE', $arEntityAttr[$ID]);
			if ($sEntityPerm == BX_CRM_PERM_NONE)
				return false;
			else if ($sEntityPerm == BX_CRM_PERM_SELF)
				$sWherePerm = ' AND ASSIGNED_BY_ID = '.$iUserId;
			else if ($sEntityPerm == BX_CRM_PERM_OPEN)
				$sWherePerm = " AND OPENED = 'Y'";
		}

		$APPLICATION->ResetException();
		$events = GetModuleEvents('crm', 'OnBeforeCrmDealDelete');
		while ($arEvent = $events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if ($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}

		$dbRes = $DB->Query("DELETE FROM b_crm_deal WHERE ID = $ID $sWherePerm", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		if ($dbRes->AffectedRowsCount() > 0)
		{
			$DB->Query("DELETE FROM b_crm_entity_perms WHERE ENTITY='DEAL' AND ENTITY_ID = $ID", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
			$GLOBALS['USER_FIELD_MANAGER']->Delete(self::$sUFEntityID, $ID);
			$CCrmFieldMulti = new CCrmFieldMulti();
			$CCrmFieldMulti->DeleteByElement('DEAL', $ID);
			$CCrmEvent = new CCrmEvent();
			$CCrmEvent->DeleteByElement('DEAL', $ID);

			CCrmSearch::DeleteSearch('DEAL', $ID);

			// Deletion of deal details
			CCrmProductRow::DeleteByOwner('D', $ID);
			CCrmActivity::DeleteByOwner(CCrmOwnerType::Deal, $ID);

			self::PullChange('DELETE', array('ID' => $ID));
		}
		return true;
	}

	public static function CompareFields($arFieldsOrig, $arFieldsModif, $bCheckPerms = true)
	{
		$arMsg = Array();

		if (isset($arFieldsOrig['TITLE']) && isset($arFieldsModif['TITLE'])
			&& $arFieldsOrig['TITLE'] != $arFieldsModif['TITLE'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'TITLE',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_TITLE'),
				'EVENT_TEXT_1' => $arFieldsOrig['TITLE'],
				'EVENT_TEXT_2' => $arFieldsModif['TITLE'],
			);

		if (isset($arFieldsOrig['COMPANY_ID']) && isset($arFieldsModif['COMPANY_ID'])
			&& (int)$arFieldsOrig['COMPANY_ID'] != (int)$arFieldsModif['COMPANY_ID'])
		{
			$arCompany = Array();

			$arFilterTmp = array('ID' => array($arFieldsOrig['COMPANY_ID'], $arFieldsModif['COMPANY_ID']));
			if (!$bCheckPerms)
				$arFilterTmp["CHECK_PERMISSIONS"] = "N";

			$dbRes = CCrmCompany::GetList(Array('TITLE'=>'ASC'), $arFilterTmp);
			while ($arRes = $dbRes->Fetch())
				$arCompany[$arRes['ID']] = $arRes['TITLE'];

			$arMsg[] = Array(
				'ENTITY_FIELD' => 'COMPANY_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_COMPANY_ID'),
				'EVENT_TEXT_1' => CrmCompareFieldsList($arCompany, $arFieldsOrig['COMPANY_ID']),
				'EVENT_TEXT_2' => CrmCompareFieldsList($arCompany, $arFieldsModif['COMPANY_ID'])
			);
		}

		if (isset($arFieldsOrig['CONTACT_ID']) && isset($arFieldsModif['CONTACT_ID'])
			&& (int)$arFieldsOrig['CONTACT_ID'] != (int)$arFieldsModif['CONTACT_ID'])
		{
			$arContact = Array();

			$arFilterTmp = array('ID' => array($arFieldsOrig['CONTACT_ID'], $arFieldsModif['CONTACT_ID']));
			if (!$bCheckPerms)
				$arFilterTmp["CHECK_PERMISSIONS"] = "N";

			$dbRes = CCrmContact::GetList(Array('LAST_NAME'=>'ASC', 'NAME' => 'ASC'), $arFilterTmp);
			while ($arRes = $dbRes->Fetch())
				$arContact[$arRes['ID']] = $arRes['LAST_NAME'].' '.$arRes['NAME'];

			$arMsg[] = Array(
				'ENTITY_FIELD' => 'CONTACT_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_CONTACT_ID'),
				'EVENT_TEXT_1' => CrmCompareFieldsList($arContact, $arFieldsOrig['CONTACT_ID']),
				'EVENT_TEXT_2' => CrmCompareFieldsList($arContact, $arFieldsModif['CONTACT_ID'])
			);
		}
		if (isset($arFieldsOrig['ASSIGNED_BY_ID']) && isset($arFieldsModif['ASSIGNED_BY_ID'])
			&& (int)$arFieldsOrig['ASSIGNED_BY_ID'] != (int)$arFieldsModif['ASSIGNED_BY_ID'])
		{
			$arUser = Array();
			$dbUsers = CUser::GetList(
				($sort_by = 'last_name'), ($sort_dir = 'asc'),
				array('ID' => implode('|', array(intval($arFieldsOrig['ASSIGNED_BY_ID']), intval($arFieldsModif['ASSIGNED_BY_ID'])))),
				array('SELECT' => array('NAME', 'SECOND_NAME', 'LAST_NAME', 'LOGIN', 'EMAIL'))
			);
			while ($arRes = $dbUsers->Fetch())
				$arUser[$arRes['ID']] = CUser::FormatName(CSite::GetNameFormat(false), $arRes);

			$arMsg[] = Array(
				'ENTITY_FIELD' => 'ASSIGNED_BY_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_ASSIGNED_BY_ID'),
				'EVENT_TEXT_1' => CrmCompareFieldsList($arUser, $arFieldsOrig['ASSIGNED_BY_ID']),
				'EVENT_TEXT_2' => CrmCompareFieldsList($arUser, $arFieldsModif['ASSIGNED_BY_ID'])
			);
		}
		if (isset($arFieldsOrig['STAGE_ID']) && isset($arFieldsModif['STAGE_ID'])
			&& $arFieldsOrig['STAGE_ID'] != $arFieldsModif['STAGE_ID'])
		{
			$arStatus = CCrmStatus::GetStatusList('DEAL_STAGE');
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'STAGE_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_DEAL_STAGE'),
				'EVENT_TEXT_1' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsOrig['STAGE_ID'])),
				'EVENT_TEXT_2' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsModif['STAGE_ID']))
			);
		}
		/*
		if (isset($arFieldsOrig['STATE_ID']) && isset($arFieldsModif['STATE_ID'])
			&& $arFieldsOrig['STATE_ID'] != $arFieldsModif['STATE_ID'])
		{
			$CCrmStatus = new CCrmStatus('DEAL_STATE');
			$arStatusOrig = $CCrmStatus->GetStatusByStatusId($arFieldsOrig['STATE_ID']);
			$arStatusModif = $CCrmStatus->GetStatusByStatusId($arFieldsModif['STATE_ID']);

			$arMsg[] = Array(
				'ENTITY_FIELD' => 'STATE_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_DEAL_STATE'),
				'EVENT_TEXT_1' => isset($arStatusOrig['NAME'])? $arStatusOrig['NAME']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => isset($arStatusModif['NAME'])? $arStatusModif['NAME']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);
		}
		*/
		if (isset($arFieldsOrig['TYPE_ID']) && isset($arFieldsModif['TYPE_ID'])
			&& $arFieldsOrig['TYPE_ID'] != $arFieldsModif['TYPE_ID'])
		{
			$arStatus = CCrmStatus::GetStatusList('DEAL_TYPE');
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'TYPE_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_DEAL_TYPE'),
				'EVENT_TEXT_1' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsOrig['TYPE_ID'])),
				'EVENT_TEXT_2' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsModif['TYPE_ID']))
			);
		}

		if (isset($arFieldsOrig['COMMENTS']) && isset($arFieldsModif['COMMENTS'])
			&& $arFieldsOrig['COMMENTS'] != $arFieldsModif['COMMENTS'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'COMMENTS',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_COMMENTS'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['COMMENTS'])? $arFieldsOrig['COMMENTS']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['COMMENTS'])? $arFieldsModif['COMMENTS']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);

//		if (isset($arFieldsOrig['PRODUCT_ID']) && isset($arFieldsModif['PRODUCT_ID'])
//			&& $arFieldsOrig['PRODUCT_ID'] != $arFieldsModif['PRODUCT_ID'])
//		{
//			$arStatus = CCrmStatus::GetStatusList('PRODUCT');
//			$arMsg[] = Array(
//				'ENTITY_FIELD' => 'PRODUCT_ID',
//				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_PRODUCT'),
//				'EVENT_TEXT_1' => CrmCompareFieldsList($arStatus, $arFieldsOrig['PRODUCT_ID']),
//				'EVENT_TEXT_2' => CrmCompareFieldsList($arStatus, $arFieldsModif['PRODUCT_ID'])
//			);
//		}

		if ((isset($arFieldsOrig['OPPORTUNITY']) && isset($arFieldsModif['OPPORTUNITY']) && $arFieldsOrig['OPPORTUNITY'] != $arFieldsModif['OPPORTUNITY'])
			|| (isset($arFieldsOrig['CURRENCY_ID']) && isset($arFieldsModif['CURRENCY_ID']) && $arFieldsOrig['CURRENCY_ID'] != $arFieldsModif['CURRENCY_ID']))
		{
			$arStatus = CCrmCurrencyHelper::PrepareListItems();
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'OPPORTUNITY',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_OPPORTUNITY'),
				'EVENT_TEXT_1' => floatval($arFieldsOrig['OPPORTUNITY']).(($val = CrmCompareFieldsList($arStatus, $arFieldsOrig['CURRENCY_ID'], '')) != '' ? ' ('.$val.')' : ''),
				'EVENT_TEXT_2' => floatval($arFieldsModif['OPPORTUNITY']).(($val = CrmCompareFieldsList($arStatus, $arFieldsModif['CURRENCY_ID'], '')) != '' ? ' ('.$val.')' : '')
			);
		}

		if (isset($arFieldsOrig['PROBABILITY']) && isset($arFieldsModif['PROBABILITY'])
			&& $arFieldsOrig['PROBABILITY'] != $arFieldsModif['PROBABILITY'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'PROBABILITY',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_PROBABILITY'),
				'EVENT_TEXT_1' => intval($arFieldsOrig['PROBABILITY']).'%',
				'EVENT_TEXT_2' => intval($arFieldsModif['PROBABILITY']).'%',
			);

		if (array_key_exists('BEGINDATE', $arFieldsOrig) && array_key_exists('BEGINDATE', $arFieldsModif) &&
			ConvertTimeStamp(strtotime($arFieldsOrig['BEGINDATE'])) != $arFieldsModif['BEGINDATE'] && $arFieldsOrig['BEGINDATE'] != $arFieldsModif['BEGINDATE'])
		{
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'BEGINDATE',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_BEGINDATE'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['BEGINDATE'])? ConvertTimeStamp(strtotime($arFieldsOrig['BEGINDATE'])): GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['BEGINDATE'])? $arFieldsModif['BEGINDATE']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);
		}
		if (array_key_exists('CLOSEDATE', $arFieldsOrig) && array_key_exists('CLOSEDATE', $arFieldsModif) &&
			ConvertTimeStamp(strtotime($arFieldsOrig['CLOSEDATE'])) != $arFieldsModif['CLOSEDATE'] && $arFieldsOrig['CLOSEDATE'] != $arFieldsModif['CLOSEDATE'])
		{
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'CLOSEDATE',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_CLOSEDATE'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['CLOSEDATE'])? ConvertTimeStamp(strtotime($arFieldsOrig['CLOSEDATE'])): GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['CLOSEDATE'])? $arFieldsModif['CLOSEDATE']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);
		}
		if (array_key_exists('EVENT_DATE', $arFieldsOrig) && array_key_exists('EVENT_DATE', $arFieldsModif) &&
			ConvertTimeStamp(strtotime($arFieldsOrig['EVENT_DATE'])) != $arFieldsModif['EVENT_DATE'] && $arFieldsOrig['EVENT_DATE'] != $arFieldsModif['EVENT_DATE'])
		{
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'EVENT_DATE',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_EVENT_DATE'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['EVENT_DATE'])? ConvertTimeStamp(strtotime($arFieldsOrig['EVENT_DATE'])): GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['EVENT_DATE'])? $arFieldsModif['EVENT_DATE']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);
		}
		if (isset($arFieldsOrig['EVENT_ID']) && isset($arFieldsModif['EVENT_ID'])
			&& $arFieldsOrig['EVENT_ID'] != $arFieldsModif['EVENT_ID'])
		{
			$arStatus = CCrmStatus::GetStatusList('EVENT_TYPE');
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'EVENT_ID',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_EVENT_ID'),
				'EVENT_TEXT_1' => CrmCompareFieldsList($arStatus, $arFieldsOrig['EVENT_ID']),
				'EVENT_TEXT_2' => CrmCompareFieldsList($arStatus, $arFieldsModif['EVENT_ID'])
			);
		}
		if (isset($arFieldsOrig['EVENT_DESCRIPTION']) && isset($arFieldsModif['EVENT_DESCRIPTION'])
			&& $arFieldsOrig['EVENT_DESCRIPTION'] != $arFieldsModif['EVENT_DESCRIPTION'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'EVENT_DESCRIPTION',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_EVENT_DESCRIPTION'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['EVENT_DESCRIPTION'])? $arFieldsOrig['EVENT_DESCRIPTION']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['EVENT_DESCRIPTION'])? $arFieldsModif['EVENT_DESCRIPTION']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);

		if (isset($arFieldsOrig['CLOSED']) && isset($arFieldsModif['CLOSED'])
			&& $arFieldsOrig['CLOSED'] != $arFieldsModif['CLOSED'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'CLOSED',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_CLOSED'),
				'EVENT_TEXT_1' => $arFieldsOrig['CLOSED'] == 'Y'? GetMessage('MAIN_YES'): GetMessage('MAIN_NO'),
				'EVENT_TEXT_2' => $arFieldsModif['CLOSED'] == 'Y'? GetMessage('MAIN_YES'): GetMessage('MAIN_NO'),
			);
		return $arMsg;
	}

	public static function LoadProductRows($ID)
	{
		return CCrmProductRow::LoadRows('D', $ID);
	}

	public static function SaveProductRows($ID, $arRows, $checkPerms = true, $regEvent = true)
	{
		$context = array();
		$arParams = self::GetByID($ID);
		if(is_array($arParams))
		{
			if(isset($arParams['CURRENCY_ID']))
			{
				$context['CURRENCY_ID'] = $arParams['CURRENCY_ID'];
			}

			if(isset($arParams['EXCH_RATE']))
			{
				$context['EXCH_RATE'] = $arParams['EXCH_RATE'];
			}
		}

		return CCrmProductRow::SaveRows('D', $ID, $arRows, $context, $checkPerms, $regEvent);
	}

	public static function OnAccountCurrencyChange()
	{
		$accountCurrencyID = CCrmCurrency::GetAccountCurrencyID();
		if(!isset($accountCurrencyID[0]))
		{
			return;
		}

		$rs = self::GetList(
			array('ID' => 'ASC'),
			//array('!ACCOUNT_CURRENCY_ID' => $accountCurrencyID),
			array(),
			array('ID', 'CURRENCY_ID', 'OPPORTUNITY', 'EXCH_RATE')
		);

		$entity = new CCrmDeal(false);
		while($arParams = $rs->Fetch())
		{
			$ID = intval($arParams['ID']);
			$entity->Update($ID, $arParams, false, false);
			$arRows = CCrmProductRow::LoadRows('D', $ID);

			$context = array();
			if(isset($arParams['CURRENCY_ID']))
			{
				$context['CURRENCY_ID'] = $arParams['CURRENCY_ID'];
			}

			if(isset($arParams['EXCH_RATE']))
			{
				$context['EXCH_RATE'] = $arParams['EXCH_RATE'];
			}

			if(count($arRows) > 0)
			{
				CCrmProductRow::SaveRows('D', $ID, $arRows, $context);
			}
		}
	}

	public function SynchronizeProductRows($ID, $checkPerms = true)
	{
		$arParams = CCrmDeal::GetByID($ID, $checkPerms);
		if(!is_array($arParams))
		{
			return;
		}

		$arRows = CCrmProductRow::LoadRows('D', $ID);
		$arFields = array(
			'OPPORTUNITY' => CCrmProductRow::GetTotalSum($arRows)
		);

		$entity = new CCrmDeal($checkPerms);
		$entity->Update($ID, $arFields);
	}
	public static function CheckCreatePermission()
	{
		return CCrmAuthorizationHelper::CheckCreatePermission(self::$TYPE_NAME);
	}

	public static function CheckUpdatePermission($ID)
	{
		return CCrmAuthorizationHelper::CheckUpdatePermission(self::$TYPE_NAME, $ID);
	}

	public static function CheckDeletePermission($ID)
	{
		return CCrmAuthorizationHelper::CheckDeletePermission(self::$TYPE_NAME, $ID);
	}

	public static function CheckReadPermission($ID = 0)
	{
		return CCrmAuthorizationHelper::CheckReadPermission(self::$TYPE_NAME, $ID);
	}

	public static function PrepareFilter(&$arFilter, $arFilter2Logic = null)
	{
		if(!is_array($arFilter2Logic))
		{
			$arFilter2Logic = array('TITLE', 'COMMENTS');
		}

		static $arImmutableFilters = array('FM', 'ID', 'ASSIGNED_BY_ID', 'CURRENCY_ID', 'CONTACT_ID', 'COMPANY_ID', 'CREATED_BY_ID', 'MODIFY_BY_ID', 'PRODUCT_ROW_PRODUCT_ID');
		foreach ($arFilter as $k => $v)
		{
			if(in_array($k, $arImmutableFilters, true))
			{
				continue;
			}

			$arMatch = array();

			if(in_array($k, array('PRODUCT_ID', 'TYPE_ID', 'STAGE_ID', 'COMPANY_ID', 'CONTACT_ID')))
			{
				// Bugfix #23121 - to suppress comparison by LIKE
				$arFilter['='.$k] = $v;
				unset($arFilter[$k]);
			}
			elseif($k === 'ORIGINATOR_ID')
			{
				// HACK: build filter by internal entities
				$arFilter['=ORIGINATOR_ID'] = $v !== '__INTERNAL' ? $v : null;
				unset($arFilter[$k]);
			}
			elseif (preg_match('/(.*)_from$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
			{
				if(strlen($v) > 0)
				{
					$arFilter['>='.$arMatch[1]] = $v;
				}
				unset($arFilter[$k]);
			}
			elseif (preg_match('/(.*)_to$/i'.BX_UTF_PCRE_MODIFIER, $k, $arMatch))
			{
				if(strlen($v) > 0)
				{
					if (($arMatch[1] == 'DATE_CREATE' || $arMatch[1] == 'DATE_MODIFY') && !preg_match('/\d{1,2}:\d{1,2}(:\d{1,2})?$/'.BX_UTF_PCRE_MODIFIER, $v))
					{
						$v .=  ' 23:59:59';
					}
					$arFilter['<='.$arMatch[1]] = $v;
				}
				unset($arFilter[$k]);
			}
			elseif (in_array($k, $arFilter2Logic))
			{
				// Bugfix #26956 - skip empty values in logical filter
				$v = trim($v);
				if($v !== '')
				{
					$arFilter['?'.$k] = $v;
				}
				unset($arFilter[$k]);
			}
			elseif (strpos($k, 'UF_') !== 0 && $k != 'LOGIC')
			{
				$arFilter['%'.$k] = $v;
				unset($arFilter[$k]);
			}
		}
	}

	public static function GetFinalStageID()
	{
		return 'WON';
	}

	public static function GetFinalStageSort()
	{
		return self::GetStageSort('WON');
	}

	public static function GetStageSort($stageID)
	{
		$stageID = strval($stageID);

		if($stageID === '')
		{
			return -1;
		}

		if(!self::$DEAL_STAGES)
		{
			self::$DEAL_STAGES = CCrmStatus::GetStatus('DEAL_STAGE');
		}

		$info = isset(self::$DEAL_STAGES[$stageID]) ? self::$DEAL_STAGES[$stageID] : null;
		return is_array($info) && isset($info['SORT']) ? intval($info['SORT']) : -1;
	}

	public static function PullChange($type, $arParams)
	{
		if(!CModule::IncludeModule('pull'))
		{
			return;
		}
		
		$type = strval($type);
		if($type === '')
		{
			$type = 'update';
		}
		else
		{
			$type = strtolower($type);
		}
		
		CPullWatch::AddToStack(
			'CRM_DEAL_CHANGE',
			array(
				'module_id'  => 'crm',
				'command'    => "crm_deal_{$type}",
				'params'     => $arParams
			)
		);
	}

	public static function GetCount($arFilter)
	{
		$fields = self::GetFields();
		return CSqlHelper::GetCount(CCrmDeal::TABLE_NAME, self::TABLE_ALIAS, $fields, $arFilter);
	}
}

?>
