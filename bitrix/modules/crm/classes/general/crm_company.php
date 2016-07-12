<?php
IncludeModuleLangFile(__FILE__);

class CAllCrmCompany
{
	static public $sUFEntityID = 'CRM_COMPANY';
	public $LAST_ERROR = '';
	public $cPerms = null;
	protected $bCheckPermission = true;
	const TABLE_ALIAS = 'L';
	protected static $TYPE_NAME = 'COMPANY';

	function __construct($bCheckPermission = true)
	{
		$this->bCheckPermission = $bCheckPermission;
		$this->cPerms = CCrmPerms::GetCurrentUserPermissions();
	}

	// Service -->
	public static function GetFields($arOptions = null)
	{
		$assignedByJoin = 'LEFT JOIN b_user U ON L.ASSIGNED_BY_ID = U.ID';
		$createdByJoin = 'LEFT JOIN b_user U2 ON L.CREATED_BY_ID = U2.ID';
		$modifyByJoin = 'LEFT JOIN b_user U3 ON L.MODIFY_BY_ID = U3.ID';

		$result = array(
			'ID' => array('FIELD' => 'L.ID', 'TYPE' => 'int'),
			'COMPANY_TYPE' => array('FIELD' => 'L.COMPANY_TYPE', 'TYPE' => 'string'),
			'TITLE' => array('FIELD' => 'L.TITLE', 'TYPE' => 'string'),
			'LOGO' => array('FIELD' => 'L.LOGO', 'TYPE' => 'string'),
			'LEAD_ID' => array('FIELD' => 'L.LEAD_ID', 'TYPE' => 'int'),

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

			'ADDRESS' => array('FIELD' => 'L.ADDRESS', 'TYPE' => 'string'),
			'ADDRESS_LEGAL' => array('FIELD' => 'L.ADDRESS_LEGAL', 'TYPE' => 'string'),
			'BANKING_DETAILS' => array('FIELD' => 'L.BANKING_DETAILS', 'TYPE' => 'string'),

			'INDUSTRY' => array('FIELD' => 'L.INDUSTRY', 'TYPE' => 'string'),
			'REVENUE' => array('FIELD' => 'L.REVENUE', 'TYPE' => 'string'),
			'CURRENCY_ID' => array('FIELD' => 'L.CURRENCY_ID', 'TYPE' => 'string'),
			'EMPLOYEES' => array('FIELD' => 'L.EMPLOYEES', 'TYPE' => 'string'),
			'COMMENTS' => array('FIELD' => 'L.COMMENTS', 'TYPE' => 'string'),

			'DATE_CREATE' => array('FIELD' => 'L.DATE_CREATE', 'TYPE' => 'datetime'),
			'DATE_MODIFY' => array('FIELD' => 'L.DATE_MODIFY', 'TYPE' => 'datetime'),

			'OPENED' => array('FIELD' => 'L.OPENED', 'TYPE' => 'char'),
			'ORIGINATOR_ID' => array('FIELD' => 'L.ORIGINATOR_ID', 'TYPE' => 'string'), //EXTERNAL SYSTEM THAT OWNS THIS ITEM
			'ORIGIN_ID' => array('FIELD' => 'L.ORIGIN_ID', 'TYPE' => 'string') //ITEM ID IN EXTERNAL SYSTEM
		);

		// Creation of field aliases
		$result['ASSIGNED_BY'] = $result['ASSIGNED_BY_ID'];
		$result['CREATED_BY'] = $result['CREATED_BY_ID'];
		$result['MODIFY_BY'] = $result['MODIFY_BY_ID'];

		$additionalFields = is_array($arOptions) && isset($arOptions['ADDITIONAL_FIELDS'])
			? $arOptions['ADDITIONAL_FIELDS'] : null;

		if(is_array($additionalFields))
		{
			if(in_array('ACTIVITY', $additionalFields, true))
			{
				$commonActivityJoin = CCrmActivity::PrepareJoin(0, CCrmOwnerType::Company, 'L', 'AC', 'UAC', 'ACUSR');

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
					$activityJoin = CCrmActivity::PrepareJoin($userID, CCrmOwnerType::Company, 'L', 'A', 'UA', '');

					$result['ACTIVITY_ID'] = array('FIELD' => 'UA.ACTIVITY_ID', 'TYPE' => 'int', 'FROM' => $activityJoin);
					$result['ACTIVITY_TIME'] = array('FIELD' => 'UA.ACTIVITY_TIME', 'TYPE' => 'datetime', 'FROM' => $activityJoin);
					$result['ACTIVITY_SUBJECT'] = array('FIELD' => 'A.SUBJECT', 'TYPE' => 'string', 'FROM' => $activityJoin);
				}
			}
		}

		return $result;
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
			CCrmCompany::DB_TYPE,
			CCrmCompany::TABLE_NAME,
			self::TABLE_ALIAS,
			self::GetFields(isset($arOptions['FIELD_OPTIONS']) ? $arOptions['FIELD_OPTIONS'] : null),
			self::$sUFEntityID,
			'COMPANY',
			array('CCrmCompany', 'BuildPermSql')
		);

		return $lb->Prepare($arOrder, $arFilter, $arGroupBy, $arNavStartParams, $arSelectFields, $arOptions);
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

	/**
	 *
	 * @param array $arOrder
	 * @param array $arFilter
	 * @param array $arSelect
	 * @return CDBResult
	 */
	public static function GetList($arOrder = Array('DATE_CREATE' => 'DESC'), $arFilter = Array(), $arSelect = Array(), $nPageTop = false)
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		// fields
		$arFields = array(
			'ID' => 'L.ID',
			'LEAD_ID' => 'L.LEAD_ID',
			'ADDRESS' => 'L.ADDRESS',
			'ADDRESS_LEGAL' => 'L.ADDRESS_LEGAL',
			'BANKING_DETAILS' => 'L.BANKING_DETAILS',
			'TITLE' => 'L.TITLE',
			'LOGO' => 'L.LOGO',
			'COMPANY_TYPE' => 'L.COMPANY_TYPE',
			'INDUSTRY' => 'L.INDUSTRY',
			'REVENUE' => 'L.REVENUE',
			'CURRENCY_ID' => 'L.CURRENCY_ID',
			'EMPLOYEES' => 'L.EMPLOYEES',
			'COMMENTS' => 'L.COMMENTS',
			'CREATED_BY' => 'L.CREATED_BY_ID',
			'CREATED_BY_ID' => 'L.CREATED_BY_ID',
			'MODIFY_BY' => 'L.MODIFY_BY_ID',
			'MODIFY_BY_ID' => 'L.MODIFY_BY_ID',
			'DATE_CREATE' => $DB->DateToCharFunction('L.DATE_CREATE'),
			'DATE_MODIFY' => $DB->DateToCharFunction('L.DATE_MODIFY'),
			'OPENED' => 'L.OPENED',
			'ORIGINATOR_ID' => 'L.ORIGINATOR_ID', //EXTERNAL SYSTEM THAT OWNS THIS ITEM
			'ORIGIN_ID' => 'L.ORIGIN_ID', //ITEM ID IN EXTERNAL SYSTEM
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

		if (in_array('CREATED_BY_LOGIN', $arFilterField) || in_array('CREATED_BY_LOGIN', $arFilterField))
		{
			$arSelect[] = 'CREATED_BY';
			$arSelect[] = 'CREATED_BY_LOGIN';
			$arSelect[] = 'CREATED_BY_NAME';
			$arSelect[] = 'CREATED_BY_LAST_NAME';
			$arSelect[] = 'CREATED_BY_SECOND_NAME';
			$sSqlJoin .= ' LEFT JOIN b_user U2 ON L.CREATED_BY_ID = U2.ID ';
		}
		if (in_array('MODIFY_BY_LOGIN', $arFilterField) || in_array('MODIFY_BY_LOGIN', $arFilterField))
		{
			$arSelect[] = 'MODIFY_BY';
			$arSelect[] = 'MODIFY_BY_LOGIN';
			$arSelect[] = 'MODIFY_BY_NAME';
			$arSelect[] = 'MODIFY_BY_LAST_NAME';
			$arSelect[] = 'MODIFY_BY_SECOND_NAME';
			$sSqlJoin .= ' LEFT JOIN b_user U3 ON  L.MODIFY_BY_ID = U3.ID ';
		}

		foreach($arSelect as $field)
		{
			$field = strtoupper($field);
			if(array_key_exists($field, $arFields))
				$arSqlSelect[$field] = $arFields[$field].($field != '*' ? ' AS '.$field : '');
		}

		if (!isset($arSqlSelect['ID']))
			$arSqlSelect['ID'] = $arFields['ID'];
		$sSqlSelect = implode(",\n", $arSqlSelect);

		if (isset($arFilter['FM']) && !empty($arFilter['FM']))
		{
			$res = CCrmFieldMulti::GetList(array('ID' => 'asc'), array('ENTITY_ID' => 'COMPANY', 'FILTER' => $arFilter['FM']));
			$ids = array();
			while($ar = $res->Fetch())
			{
				$ids[] = $ar['ELEMENT_ID'];
			}

			if(count($ids) == 0)
			{
				// Fix for #26789 (nothing found)
				$rs = new CDBResult();
				$rs->InitFromArray(array());
				return $rs;
			}

			$arFilter['ID'] = $ids;
		}

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
			'COMPANY_TYPE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.COMPANY_TYPE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'INDUSTRY' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.INDUSTRY',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'TITLE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.TITLE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'REVENUE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.REVENUE',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'CURRENCY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CURRENCY_ID',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'EMPLOYEES' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.EMPLOYEES',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'ADDRESS' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ADDRESS',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'BANKING_DETAILS' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.BANKING_DETAILS',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'ADDRESS_LEGAL' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.ADDRESS_LEGAL',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'COMMENTS' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.COMMENTS',
				'FIELD_TYPE' => 'string',
				'JOIN' => false
			),
			'DATE_CREATE' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.DATE_CREATE',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'DATE_MODIFY' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.DATE_MODIFY',
				'FIELD_TYPE' => 'datetime',
				'JOIN' => false
			),
			'CREATED_BY_ID' => array(
				'TABLE_ALIAS' => 'L',
				'FIELD_NAME' => 'L.CREATED_BY_ID',
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
			),
		);

		$obQueryWhere->SetFields($arWhereFields);
		if (!is_array($arFilter))
			$arFilter = array();
		$sQueryWhereFields = $obQueryWhere->GetQuery($arFilter);

		$sSqlSearch = '';
		foreach($arSqlSearch as $r)
			if(strlen($r) > 0)
				$sSqlSearch .= "\n\t\t\t\tAND  ($r) ";
		$CCrmUserType = new CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], self::$sUFEntityID);
		$CCrmUserType->ListPrepareFilter($arFilter);
		$r = $obUserFieldsSql->GetFilter();
		if (strlen($r) > 0)
			$sSqlSearch .= "\n\t\t\t\tAND ($r) ";

		if (!empty($sQueryWhereFields))
			$sSqlSearch .= "\n\t\t\t\tAND ($sQueryWhereFields) ";

		$arFieldsOrder = array(
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
			elseif(isset($arFields[$by]) && $by != 'ADDRESS')
				$arSqlOrder[$by] = " L.$by $order ";
			elseif($s = $obUserFieldsSql->GetOrder($by))
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
				b_crm_company L $sSqlJoin
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

		$dbRes = CCrmCompany::GetListEx(array(), $arFilter);
		return $dbRes->Fetch();
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
			$arUserAttr = array_merge($arUserAttr, $CCrmPerms->GetUserAttrForSelectEntity('COMPANY', $sPermType));

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
						WHERE {$sAliasPrefix}P.ENTITY = 'COMPANY' AND {$sAliasPrefix}.ID = {$sAliasPrefix}P.ENTITY_ID
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

		if (!isset($arFields['CREATED_BY_ID']) || intval($arFields['CREATED_BY_ID']) <= 0)
			$arFields['CREATED_BY_ID'] = $iUserId;
		if (!isset($arFields['MODIFY_BY_ID']) || intval($arFields['MODIFY_BY_ID']) <= 0)
			$arFields['MODIFY_BY_ID'] = $iUserId;
		if (!isset($arFields['ASSIGNED_BY_ID']) || intval($arFields['ASSIGNED_BY_ID']) <= 0)
			$arFields['ASSIGNED_BY_ID'] = $iUserId;

		if (isset($arFields['REVENUE']))
			$arFields['REVENUE'] = floatval($arFields['REVENUE']);

		$result = true;
		if (!$this->CheckFields($arFields, false, $options))
		{
			$result = false;
			$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
		}
		else
		{

			$arAttr = array();
			if (!empty($arFields['OPENED']))
				$arAttr['OPENED'] = $arFields['OPENED'];
			$arEntityAttr = self::BuildEntityAttr(
				$arAttr
			);

			$sPermission = 'ADD';
			if (isset($arFields['PERMISSION']))
			{
				if ($arFields['PERMISSION'] == 'IMPORT')
					$sPermission = 'IMPORT';
				unset($arFields['PERMISSION']);
			}

			$sEntityPerm = $this->cPerms->GetPermType('COMPANY', $sPermission, $arEntityAttr);
			$this->PrepareEntityAttrs($arEntityAttr, $sEntityPerm);

			if ($this->bCheckPermission)
			{
				if ($sEntityPerm == BX_CRM_PERM_NONE)
				{
					$this->LAST_ERROR = GetMessage('CRM_PERMISSION_DENIED');
					$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
					return false;
				}
				else if ($sEntityPerm == BX_CRM_PERM_OPEN)
					$arFields['OPENED'] = 'Y';
			}

			if (!empty($arFields['LOGO']) && strlen(CFile::CheckImageFile($arFields['LOGO'])) === 0)
			{
				$arFields['LOGO']['MODULE_ID'] = 'crm';
				CFile::SaveForDB($arFields, 'LOGO', 'crm');
			}
			$ID = intval($DB->Add('b_crm_company', $arFields, array(), 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__));
			CCrmEntityHelper::NormalizeUserFields($arFields, self::$sUFEntityID, $GLOBALS['USER_FIELD_MANAGER']);
			$GLOBALS['USER_FIELD_MANAGER']->Update(self::$sUFEntityID, $ID, $arFields);

			CCrmPerms::UpdateEntityAttr('COMPANY', $ID, $arEntityAttr);

			if($bUpdateSearch)
			{
				$arFilterTmp = Array('ID' => $ID);
				if (!$this->bCheckPermission)
					$arFilterTmp["CHECK_PERMISSIONS"] = "N";
				CCrmSearch::UpdateSearch($arFilterTmp, 'COMPANY', true);
			}

			$result = $ID;
			$arFields['ID'] = &$result;
			if (isset($arFields['CONTACT_ID']) && is_array($arFields['CONTACT_ID']))
			{
					$CCrmContact = new CCrmContact();
					$CCrmContact->UpdateCompanyId($arFields['CONTACT_ID'], $arFields['ID']);

					if (isset($GLOBALS["USER"]))
					{
						if (!class_exists('CUserOptions'))
							include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/'.$GLOBALS['DBType'].'/favorites.php');

						CUserOptions::SetOption('crm', 'crm_contact_search', array('last_selected' => implode(',', $arFields['CONTACT_ID'])));
					}
			}
			if (isset($arFields['FM']) && is_array($arFields['FM']))
			{
				$CCrmFieldMulti = new CCrmFieldMulti();
				$CCrmFieldMulti->SetFields('COMPANY', $ID, $arFields['FM']);
			}

			$events = GetModuleEvents('crm', 'OnAfterCrmCompanyAdd');
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array(&$arFields));
		}

		return $result;
	}

	static public function BuildEntityAttr($arAttr = array())
	{
		$_arAttr = array();

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

		$obRes = self::GetListEx(array(), $arFilterTmp);
		if (!($arRow = $obRes->Fetch()))
			return false;

		$iUserId = is_object($USER) ? intval($USER->GetID()) : 0;

		if (isset($arFields['DATE_CREATE']))
			unset($arFields['DATE_CREATE']);
		if (isset($arFields['DATE_MODIFY']))
			unset($arFields['DATE_MODIFY']);
		$arFields['~DATE_MODIFY'] = $DB->CurrentTimeFunction();

		if(!isset($arFields['MODIFY_BY_ID']) || (int)$arFields['MODIFY_BY_ID'] <= 0)
			$arFields['MODIFY_BY_ID'] = $iUserId;
		if (isset($arFields['REVENUE']))
			$arFields['REVENUE'] = floatval($arFields['REVENUE']);

		$bResult = false;
		if (!$this->CheckFields($arFields, $ID, $options))
			$arFields['RESULT_MESSAGE'] = &$this->LAST_ERROR;
		else
		{

			$arAttr = array();
			if (!empty($arFields['OPENED']))
				$arAttr['OPENED'] = $arFields['OPENED'];
			$arEntityAttr = self::BuildEntityAttr(
				$arAttr
			);

			$sEntityPerm = $this->cPerms->GetPermType('COMPANY', 'WRITE', $arEntityAttr);
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
				elseif ($sEntityPerm == BX_CRM_PERM_OPEN)
				{
					// Mark entity as OPENED for make it accessable for user restricted by BX_CRM_PERM_OPEN
					$arFields['OPENED'] = 'Y';

					$sWherePerm = " AND OPENED = 'Y'";
				}
			}

			if (!empty($arFields['LOGO']) && strlen(CFile::CheckImageFile($arFields['LOGO'])) === 0)
			{
				$arFields['LOGO']['MODULE_ID'] = 'crm';
				if($arFields['LOGO_del'] == 'Y' && !empty($arRow['LOGO']))
					CFile::Delete($arRow['LOGO']);
				CFile::SaveForDB($arFields, 'LOGO', 'crm');
				if($arFields['LOGO_del'] == 'Y' && !isset($arFields['LOGO']))
					$arFields['LOGO'] = '';
			}

			if ($bCompare)
			{
				$res = CCrmFieldMulti::GetList(
					array('ID' => 'asc'),
					array('ENTITY_ID' => 'COMPANY', 'ELEMENT_ID' => $ID)
				);
				$arRow['FM'] = array();
				while($ar = $res->Fetch())
					$arRow['FM'][$ar['TYPE_ID']][$ar['ID']] = array('VALUE' => $ar['VALUE'], 'VALUE_TYPE' => $ar['VALUE_TYPE']);

				$arEvents = self::CompareFields($arRow, $arFields);
				foreach($arEvents as $arEvent)
				{
					$arEvent['ENTITY_TYPE'] = 'COMPANY';
					$arEvent['ENTITY_ID'] = $ID;
					$arEvent['EVENT_TYPE'] = 1;
					if (!isset($arEvent['USER_ID']))
						$arEvent['USER_ID'] = $iUserId;

					$CCrmEvent = new CCrmEvent();
					$CCrmEvent->Add($arEvent, $this->bCheckPermission);
				}
			}

			unset($arFields["ID"]);

			$sUpdate = $DB->PrepareUpdate('b_crm_company', $arFields, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
			if (strlen($sUpdate) > 0)
			{
				$DB->Query("UPDATE b_crm_company SET $sUpdate WHERE ID = $ID $sWherePerm", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
				$bResult = true;
			}

			CCrmPerms::UpdateEntityAttr(
				'COMPANY',
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
				CCrmSearch::UpdateSearch($arFilterTmp, 'COMPANY', true);
			}

			$arFields['ID'] = $ID;

			if (isset($arFields['CONTACT_ID']) && is_array($arFields['CONTACT_ID']))
			{
				if (!empty($arFields['CONTACT_ID']))
				{
					$arCurrentContact = Array();
					$res = CCrmContact::GetContactByCompanyId($arFields['ID']);
					while($ar = $res->Fetch())
						$arCurrentContact[] = $ar['ID'];

					$arAdd = array_diff($arFields['CONTACT_ID'], $arCurrentContact);
					$arDelete = array_diff($arCurrentContact, $arFields['CONTACT_ID']);

					$CCrmContact = new CCrmContact();
					$CCrmContact->UpdateCompanyId($arAdd, $arFields['ID']);
					$CCrmContact->UpdateCompanyId($arDelete, 0);

					if (isset($GLOBALS["USER"]))
					{
						if (!class_exists('CUserOptions'))
							include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/main/classes/".$GLOBALS['DBType']."/favorites.php");

						CUserOptions::SetOption("crm", "crm_contact_search", array('last_selected' => implode(',', $arAdd)));
					}
				}
				else
				{
					$arDelete = Array();
					$res = CCrmContact::GetContactByCompanyId($arFields['ID']);
					while($ar = $res->Fetch())
						$arDelete[] = $ar['ID'];

					$CCrmContact = new CCrmContact();
					$CCrmContact->UpdateCompanyId($arDelete, 0);
				}
			}
			if (isset($arFields['FM']) && is_array($arFields['FM']))
			{
				$CCrmFieldMulti = new CCrmFieldMulti();
				$CCrmFieldMulti->SetFields('COMPANY', $ID, $arFields['FM']);
			}

			$events = GetModuleEvents('crm', 'OnAfterCrmCompanyUpdate');
			while ($arEvent = $events->Fetch())
				ExecuteModuleEventEx($arEvent, array(&$arFields));
		}
		return $bResult;
	}

	public function Delete($ID)
	{
		global $DB, $APPLICATION, $USER;

		$ID = intval($ID);

		if ($this->bCheckPermission)
		{
			$arEntityAttr = $this->cPerms->GetEntityAttr('COMPANY', $ID);
			$sWherePerm = '';
			$sEntityPerm = $this->cPerms->GetPermType('COMPANY', 'DELETE', $arEntityAttr[$ID]);
			if ($sEntityPerm == BX_CRM_PERM_NONE)
				return false;
			else if ($sEntityPerm == BX_CRM_PERM_OPEN)
				$sWherePerm = " AND OPENED = 'Y'";
		}

		$APPLICATION->ResetException();
		$events = GetModuleEvents('crm', 'OnBeforeCrmCompanyDelete');
		while ($arEvent = $events->Fetch())
			if(ExecuteModuleEventEx($arEvent, array($ID))===false)
			{
				$err = GetMessage("MAIN_BEFORE_DEL_ERR").' '.$arEvent['TO_NAME'];
				if($ex = $APPLICATION->GetException())
					$err .= ': '.$ex->GetString();
				$APPLICATION->throwException($err);
				return false;
			}

		$obRes = $DB->Query("DELETE FROM b_crm_company WHERE ID = $ID $sWherePerm", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		if ($obRes->AffectedRowsCount() > 0)
		{
			$DB->Query("DELETE FROM b_crm_entity_perms WHERE ENTITY='COMPANY' AND ENTITY_ID = $ID", false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
			$GLOBALS['USER_FIELD_MANAGER']->Delete(self::$sUFEntityID, $ID);
			$CCrmFieldMulti = new CCrmFieldMulti();
			$CCrmFieldMulti->DeleteByElement('COMPANY', $ID);
			$CCrmEvent = new CCrmEvent();
			$CCrmEvent->DeleteByElement('COMPANY', $ID);

			CCrmActivity::DeleteByOwner(CCrmOwnerType::Company, $ID);
			CCrmSearch::DeleteSearch('COMPANY', $ID);
		}

		return true;
	}

	public function CheckFields(&$arFields, $ID = false, $options = array())
	{
		global $APPLICATION, $USER_FIELD_MANAGER;
		$this->LAST_ERROR = '';

		if (($ID == false || isset($arFields['TITLE'])) && empty($arFields['TITLE']))
			$this->LAST_ERROR .= GetMessage('CRM_ERROR_FIELD_IS_MISSING', array('%FIELD_NAME%' => GetMessage('CRM_FIELD_TITLE')))."<br />";

		if (isset($arFields['FM']) && is_array($arFields['FM']))
		{
			$CCrmFieldMulti = new CCrmFieldMulti();
			if (!$CCrmFieldMulti->CheckComplexFields($arFields['FM']))
			{
				$this->LAST_ERROR .= $CCrmFieldMulti->LAST_ERROR;
			}
		}

		if (isset($arFields['LOGO']) && is_array($arFields['LOGO']))
		{
			if (($strError = CFile::CheckFile($arFields['LOGO'], 0, 0, CFile::GetImageExtensions())) != '')
				$this->LAST_ERROR .= $strError."<br />";
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

		if(strlen($this->LAST_ERROR) > 0)
			return false;

		return true;
	}

	public static function CompareFields($arFieldsOrig, $arFieldsModif)
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

		if (isset($arFieldsOrig['FM']) && isset($arFieldsModif['FM']))
			$arMsg = array_merge($arMsg, CCrmFieldMulti::CompareFields($arFieldsOrig['FM'], $arFieldsModif['FM']));

		if (isset($arFieldsOrig['ADDRESS']) && isset($arFieldsModif['ADDRESS'])
			&& $arFieldsOrig['ADDRESS'] != $arFieldsModif['ADDRESS'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'ADDRESS',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_ADDRESS'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['ADDRESS'])? $arFieldsOrig['ADDRESS']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['ADDRESS'])? $arFieldsModif['ADDRESS']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);

		if (isset($arFieldsOrig['ADDRESS_LEGAL']) && isset($arFieldsModif['ADDRESS_LEGAL'])
			&& $arFieldsOrig['ADDRESS_LEGAL'] != $arFieldsModif['ADDRESS_LEGAL'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'ADDRESS_LEGAL',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_ADDRESS_LEGAL'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['ADDRESS_LEGAL'])? $arFieldsOrig['ADDRESS_LEGAL']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['ADDRESS_LEGAL'])? $arFieldsModif['ADDRESS_LEGAL']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);

		if (isset($arFieldsOrig['BANKING_DETAILS']) && isset($arFieldsModif['BANKING_DETAILS'])
			&& $arFieldsOrig['BANKING_DETAILS'] != $arFieldsModif['BANKING_DETAILS'])
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'BANKING_DETAILS',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_BANKING_DETAILS'),
				'EVENT_TEXT_1' => !empty($arFieldsOrig['BANKING_DETAILS'])? $arFieldsOrig['BANKING_DETAILS']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
				'EVENT_TEXT_2' => !empty($arFieldsModif['BANKING_DETAILS'])? $arFieldsModif['BANKING_DETAILS']: GetMessage('CRM_FIELD_COMPARE_EMPTY'),
			);

		if (isset($arFieldsOrig['COMPANY_TYPE']) && isset($arFieldsModif['COMPANY_TYPE'])
			&& $arFieldsOrig['COMPANY_TYPE'] != $arFieldsModif['COMPANY_TYPE'])
		{
			$arStatus = CCrmStatus::GetStatusList('COMPANY_TYPE');
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'COMPANY_TYPE',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_COMPANY_TYPE'),
				'EVENT_TEXT_1' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsOrig['COMPANY_TYPE'])),
				'EVENT_TEXT_2' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsModif['COMPANY_TYPE']))
			);
		}
		if (isset($arFieldsOrig['INDUSTRY']) && isset($arFieldsModif['INDUSTRY'])
			&& $arFieldsOrig['INDUSTRY'] != $arFieldsModif['INDUSTRY'])
		{
			$arStatus = CCrmStatus::GetStatusList('INDUSTRY');
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'INDUSTRY',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_INDUSTRY'),
				'EVENT_TEXT_1' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsOrig['INDUSTRY'])),
				'EVENT_TEXT_2' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsModif['INDUSTRY']))
			);
		}
		if ((isset($arFieldsOrig['REVENUE']) && isset($arFieldsModif['REVENUE']) && $arFieldsOrig['REVENUE'] != $arFieldsModif['REVENUE'])
			|| (isset($arFieldsOrig['CURRENCY_ID']) && isset($arFieldsModif['CURRENCY_ID']) && $arFieldsOrig['CURRENCY_ID'] != $arFieldsModif['CURRENCY_ID']))
		{
			$arStatus = CCrmCurrencyHelper::PrepareListItems();
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'REVENUE',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_REVENUE'),
				'EVENT_TEXT_1' => floatval($arFieldsOrig['REVENUE']).(($val = CrmCompareFieldsList($arStatus, $arFieldsOrig['CURRENCY_ID'], '')) != '' ? ' ('.$val.')' : ''),
				'EVENT_TEXT_2' => floatval($arFieldsModif['REVENUE']).(($val = CrmCompareFieldsList($arStatus, $arFieldsModif['CURRENCY_ID'], '')) != '' ? ' ('.$val.')' : '')
			);
		}
		if (isset($arFieldsOrig['EMPLOYEES']) && isset($arFieldsModif['EMPLOYEES'])
			&& $arFieldsOrig['EMPLOYEES'] != $arFieldsModif['EMPLOYEES'])
		{
			$arStatus = CCrmStatus::GetStatusList('EMPLOYEES');
			$arMsg[] = Array(
				'ENTITY_FIELD' => 'EMPLOYEES',
				'EVENT_NAME' => GetMessage('CRM_FIELD_COMPARE_EMPLOYEES'),
				'EVENT_TEXT_1' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsOrig['EMPLOYEES'])),
				'EVENT_TEXT_2' => htmlspecialcharsbx(CrmCompareFieldsList($arStatus, $arFieldsModif['EMPLOYEES']))
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

		return $arMsg;
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

	public static function SetDefaultResponsible($safe = true)
	{
		global $DB;
		$tableName = CCrmCompany::TABLE_NAME;

		if($safe && !$DB->Query("SELECT ASSIGNED_BY_ID FROM {$tableName} WHERE 1=0", true))
		{
			return false;
		}

		$DB->Query(
			"UPDATE {$tableName} SET ASSIGNED_BY_ID = CREATED_BY_ID WHERE ASSIGNED_BY_ID IS NULL",
			false,
			'File: '.__FILE__.'<br/>Line: '.__LINE__
		);

		return true;
	}

	public static function PrepareFilter(&$arFilter, $arFilter2Logic = null)
	{
		if(!is_array($arFilter2Logic))
		{
			$arFilter2Logic = array('TITLE', 'ADDRESS_LEGAL', 'BANKING_DETAILS', 'ADDRESS', 'COMMENTS');
		}

		// converts data from filter
		if (isset($arFilter['FIND_list']) && !empty($arFilter['FIND']))
		{
			$arFilter[strtoupper($arFilter['FIND_list'])] = $arFilter['FIND'];
			unset($arFilter['FIND_list'], $arFilter['FIND']);
		}

		static $arImmutableFilters = array('FM', 'ID', 'CURRENCY_ID', 'CREATED_BY_ID', 'MODIFY_BY_ID');
		foreach ($arFilter as $k => $v)
		{
			if(in_array($k, $arImmutableFilters, true))
			{
				continue;
			}

			$arMatch = array();

			if($k === 'ORIGINATOR_ID')
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
			elseif ($k != 'ID' && $k != 'LOGIC' && strpos($k, 'UF_') !== 0)
			{
				$arFilter['%'.$k] = $v;
				unset($arFilter[$k]);
			}
		}
	}
}
?>
