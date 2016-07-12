<?php

IncludeModuleLangFile(__FILE__);

define('BX_CRM_PERM_NONE', '');
define('BX_CRM_PERM_SELF', 'A');
define('BX_CRM_PERM_DEPARTMENT', 'D');
define('BX_CRM_PERM_SUBDEPARTMENT', 'F');
define('BX_CRM_PERM_OPEN', 'O');
define('BX_CRM_PERM_ALL', 'X');
define('BX_CRM_PERM_CONFIG', 'C');

class CCrmPerms
{
	private static $CURRENT_USER = null;
	private static $IS_ADMIN = null;
	private static $ENTITY_ATTRS = array();
	protected $cdb = null;
	protected $userId = 0;
	protected $arUserPerms = array();

	function __construct($userId)
	{
		global $DB;
		$this->cdb = $DB;

		$this->userId = intval($userId);
		$this->arUserPerms = CCrmRole::GetUserPerms($this->userId);
	}

	public static function GetCurrentUserPermissions()
	{
		return new CCrmPerms(self::GetCurrentUserID());
	}

	private static function EnsureCurrentUser()
	{
		if(self::$CURRENT_USER)
		{
			return;
		}

		if(isset($USER) && ((get_class($USER) === 'CUser') || ($USER instanceof CUser)))
		{
			self::$CURRENT_USER = &$USER;
		}
		else
		{
			self::$CURRENT_USER = new CUser();
		}
	}

	public static function GetCurrentUserID()
	{
		self::EnsureCurrentUser();
		//CUser::GetID may return null
		return intval(self::$CURRENT_USER->GetID());
	}

	public static function IsAdmin()
	{
		if(self::$IS_ADMIN !== null)
		{
			return self::$IS_ADMIN;
		}

		self::EnsureCurrentUser();
		self::$IS_ADMIN = self::$CURRENT_USER->IsAdmin();

		if(self::$IS_ADMIN)
		{
			return self::$IS_ADMIN;
		}

		try
		{
			if(IsModuleInstalled('bitrix24') && CModule::IncludeModule('bitrix24'))
			{
				if(class_exists('CBitrix24') && method_exists('CBitrix24', 'IsPortalAdmin'))
				{
					// New style check
					self::$IS_ADMIN = CBitrix24::IsPortalAdmin(self::$CURRENT_USER->GetID());
				}
				else
				{
					// HACK: Check user group 1 ('Portal admins')
					$arGroups = self::$CURRENT_USER->GetUserGroup(self::$CURRENT_USER->GetID());
					self::$IS_ADMIN = in_array(1, $arGroups);
				}
			}
		}
		catch(Exception $e)
		{
		}

		return self::$IS_ADMIN;
	}

	public static function IsAuthorized()
	{
		self::EnsureCurrentUser();
		return self::$CURRENT_USER->IsAuthorized();
	}

	static public function GetUserAttr($iUserID)
	{
		static $arResult = array();
		if (!empty($arResult[$iUserID]))
		{
			return $arResult[$iUserID];
		}

		$iUserID = (int) $iUserID;

		$arResult[$iUserID] = array();

		$CAccess = new CAccess();
		$CAccess->UpdateCodes(array('USER_ID' => $iUserID));
		$obRes = CAccess::GetUserCodes($iUserID);
		while($arCode = $obRes->Fetch())
			if (strpos($arCode['ACCESS_CODE'], 'DR') !== 0)
				$arResult[$iUserID][strtoupper($arCode['PROVIDER_ID'])][] = $arCode['ACCESS_CODE'];

		if (IsModuleInstalled('intranet') && !empty($arResult[$iUserID]['INTRANET']))
		{
			foreach ($arResult[$iUserID]['INTRANET'] as $iDepartment)
			{
				$arTree = CIntranetUtils::GetDeparmentsTree(substr($iDepartment, 1), true);
				foreach ($arTree as $iSubDepartment)
					$arResult[$iUserID]['SUBINTRANET'][] = 'D'.$iSubDepartment;
			}
		}

		return $arResult[$iUserID];
	}


	public function GetUserPerms()
	{
		return $this->arUserPerms;
	}

	public function HavePerm($permEntity, $permAttr, $permType = 'READ')
	{
		// HACK: only for product and currency support
		$permType = strtoupper($permType);
		if ($permEntity == 'CONFIG' && $permAttr == BX_CRM_PERM_CONFIG && $permType == 'READ')
		{
			return true;
		}

		// HACK: Compatibility with CONFIG rights
		if ($permEntity == 'CONFIG')
			$permType = 'WRITE';

		if(self::IsAdmin())
		{
			return $permAttr != BX_CRM_PERM_NONE;
		}

		if (!isset($this->arUserPerms[$permEntity][$permType]))
			return $permAttr == BX_CRM_PERM_NONE;

		$icnt = count($this->arUserPerms[$permEntity][$permType]);
		if ($icnt > 1 && $this->arUserPerms[$permEntity][$permType]['-'] == BX_CRM_PERM_NONE)
		{
			foreach ($this->arUserPerms[$permEntity][$permType] as $sField => $arFieldValue)
			{
				if ($sField == '-')
					continue ;
				$sPrevPerm = $permAttr;
				foreach ($arFieldValue as $fieldValue => $sAttr)
					if ($sAttr > $permAttr)
						return $sAttr == BX_CRM_PERM_NONE;
				return $permAttr == BX_CRM_PERM_NONE;
			}
		}

		if ($permAttr == BX_CRM_PERM_NONE)
			return $this->arUserPerms[$permEntity][$permType]['-'] == BX_CRM_PERM_NONE;

		if ($this->arUserPerms[$permEntity][$permType]['-'] >= $permAttr)
			return true;

		return false;
	}

	public function GetPermType($permEntity, $permType = 'READ', $arEntityAttr = array())
	{
		if (self::IsAdmin())
			return BX_CRM_PERM_ALL;

		if (!isset($this->arUserPerms[$permEntity][$permType]))
			return BX_CRM_PERM_NONE;

		$icnt = count($this->arUserPerms[$permEntity][$permType]);

		if ($icnt == 1 && isset($this->arUserPerms[$permEntity][$permType]['-']))
			return $this->arUserPerms[$permEntity][$permType]['-'];
		else if ($icnt > 1)
		{
			foreach ($this->arUserPerms[$permEntity][$permType] as $sField => $arFieldValue)
			{
				if ($sField == '-')
					continue ;
				foreach ($arFieldValue as $fieldValue => $sAttr)
				{
					if (in_array($sField.$fieldValue, $arEntityAttr))
						return $sAttr;
				}
			}
			return $this->arUserPerms[$permEntity][$permType]['-'];
		}
		else
			return BX_CRM_PERM_NONE;
	}


	public static function GetEntityGroup($permEntity, $permAttr = BX_CRM_PERM_NONE, $permType = 'READ')
	{
		global $DB;

		$arResult = array();
		$arRole = CCrmRole::GetRoleByAttr($permEntity, $permAttr, $permType);

		if (!empty($arRole))
		{
			$sSql = 'SELECT RELATION FROM b_crm_role_relation WHERE RELATION LIKE \'G%\' AND ROLE_ID IN ('.implode(',', $arRole).')';
			$res = $DB->Query($sSql, false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
			while($row = $res->Fetch())
				$arResult[] = substr($row['RELATION'], 1);
		}
		return $arResult;
	}

	static public function IsAccessEnabled()
	{
		$CCrmPerms = new self(self::GetCurrentUserID());

		if (!$CCrmPerms->HavePerm('LEAD', BX_CRM_PERM_NONE) || !$CCrmPerms->HavePerm('CONTACT', BX_CRM_PERM_NONE) ||
			!$CCrmPerms->HavePerm('COMPANY', BX_CRM_PERM_NONE) || !$CCrmPerms->HavePerm('DEAL', BX_CRM_PERM_NONE))
			return true;
		else
			return false;
	}

	public function CheckEnityAccess($permEntity, $permType, $arEntityAttr)
	{
		if (!is_array($arEntityAttr))
			$arEntityAttr = array();

		$permAttr = $this->GetPermType($permEntity, $permType, $arEntityAttr);
		$arAttr = $this->GetUserAttr($this->userId);
		if ($permAttr == BX_CRM_PERM_NONE)
			return false;
		if ($permAttr == BX_CRM_PERM_ALL)
			return true;
		if ($permAttr == BX_CRM_PERM_OPEN && (in_array('O', $arEntityAttr)))
			return true;
		if ($permAttr >= BX_CRM_PERM_SELF && in_array('U'.$this->userId, $arEntityAttr))
			return true;
		if ($permAttr >= BX_CRM_PERM_DEPARTMENT && is_array($arAttr['INTRANET']))
		{
			$arAttr = self::GetUserAttr($this->userId);
			foreach ($arAttr['INTRANET'] as $iDepartment)
				if (in_array($iDepartment, $arEntityAttr))
					return true;
		}
		if ($permAttr >= BX_CRM_PERM_SUBDEPARTMENT && is_array($arAttr['SUBINTRANET']))
		{
			$arAttr = self::GetUserAttr($this->userId);
			foreach ($arAttr['SUBINTRANET'] as $iDepartment)
				if (in_array($iDepartment, $arEntityAttr))
					return true;
		}
		return false;
	}

	public function GetUserAttrForSelectEntity($permEntity, $permType, $bForcePermAll = false)
	{
		$arResult = array();
		if (!isset($this->arUserPerms[$permEntity][$permType]))
			return $arResult;

		$arAttr = self::GetUserAttr($this->userId);

		$sDefAttr = $this->arUserPerms[$permEntity][$permType]['-'];
		foreach ($this->arUserPerms[$permEntity][$permType] as $sField => $arFieldValue)
		{
			if ($sField == '-' && count($this->arUserPerms[$permEntity][$permType]) == 1)
			{
				$_arResult = array();
				$sAttr = $sDefAttr;
				if ($sAttr == BX_CRM_PERM_NONE)
					continue;
				if ($sAttr == BX_CRM_PERM_OPEN)
					$_arResult[] = 'O';
				else if ($sAttr != BX_CRM_PERM_ALL || ($sAttr == BX_CRM_PERM_ALL && $bForcePermAll))
				{
					if ($sAttr >= BX_CRM_PERM_SELF)
						foreach ($arAttr['USER'] as $iUser)
							$arResult[] = array($iUser);
					if ($sAttr >= BX_CRM_PERM_DEPARTMENT && is_array($arAttr['INTRANET']))
						foreach ($arAttr['INTRANET'] as $iDepartment)
							$_arResult[] = $iDepartment;
					if ($sAttr >= BX_CRM_PERM_SUBDEPARTMENT && is_array($arAttr['SUBINTRANET']))
						foreach ($arAttr['SUBINTRANET'] as $iDepartment)
							$_arResult[] = $iDepartment;
				}

				$arResult[] = array_unique($_arResult);
			}
			else
			{
				$arStatus = array();
				if ($permEntity == 'LEAD' && $sField == 'STATUS_ID')
					$arStatus = CCrmStatus::GetStatusList('STATUS');
				else if ($permEntity == 'DEAL' && $sField == 'STAGE_ID')
					$arStatus = CCrmStatus::GetStatusList('DEAL_STAGE');

				foreach ($arStatus as $fieldValue => $sTitle)
				{
					$_arResult = array();
					$sAttr = $sDefAttr;
					if (isset($this->arUserPerms[$permEntity][$permType][$sField][$fieldValue]))
						$sAttr = $this->arUserPerms[$permEntity][$permType][$sField][$fieldValue];
					if ($sAttr == BX_CRM_PERM_NONE)
						continue;
					$_arResult[] = $sField.$fieldValue;
					if ($sAttr == BX_CRM_PERM_OPEN)
						$_arResult[] = 'O';
					else if ($sAttr != BX_CRM_PERM_ALL)
					{
						if ($sAttr >= BX_CRM_PERM_SELF)
							foreach ($arAttr['USER'] as $iUser)
								$arResult[] = array($sField.$fieldValue, $iUser);
						if ($sAttr >= BX_CRM_PERM_DEPARTMENT)
							foreach ($arAttr['INTRANET'] as $iDepartment)
								$_arResult[] = $iDepartment;
						if ($sAttr >= BX_CRM_PERM_SUBDEPARTMENT)
							foreach ($arAttr['SUBINTRANET'] as $iDepartment)
								$_arResult[] = $iDepartment;
					}
					$arResult[] = array_unique($_arResult);
				}
			}
		}

		return $arResult;
	}

	static public function GetEntityAttr($permEntity, $arID)
	{
		$key = '';
		if (!is_array($arID))
		{
			$key = strtoupper($permEntity).'_'.intval($arID);
			if(isset(self::$ENTITY_ATTRS[$key]))
			{
				return self::$ENTITY_ATTRS[$key];
			}
		}

		global $DB;

		if (!is_array($arID))
			$arID = array($arID);
		foreach ($arID as &$ID)
			$ID = (int)$ID;

		$sWhere = '';
		if (!empty($arID))
			$sWhere = ' AND ENTITY_ID IN('.implode(',', $arID).')';

		$sSql = "
			SELECT ENTITY_ID, ATTR
			FROM b_crm_entity_perms
			WHERE ENTITY = '".$DB->ForSql($permEntity)."' $sWhere";
		$obRes = $DB->Query($sSql, false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
		$arResult = array();
		while($arRow = $obRes->Fetch())
			$arResult[$arRow['ENTITY_ID']][] = $arRow['ATTR'];

		if($key !== '')
		{
			self::$ENTITY_ATTRS[$key] = $arResult;
		}

		return 	$arResult;
	}

	static public function UpdateEntityAttr($ENTITY, $ENTITY_ID, $arAttr = array())
	{
		global $DB;
		$ENTITY_ID = intval($ENTITY_ID);
		$key = strtoupper($ENTITY).'_'.$ENTITY_ID;
		if(isset(self::$ENTITY_ATTRS[$key]))
		{
			unset(self::$ENTITY_ATTRS[$key]);
		}

		$ENTITY = $DB->ForSql($ENTITY);

		$sQuery = "DELETE FROM b_crm_entity_perms WHERE ENTITY = '$ENTITY' AND ENTITY_ID = $ENTITY_ID";
		$DB->Query($sQuery, false, $sQuery.'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);

		if (!empty($arAttr))
		{
			foreach ($arAttr as $sAttr)
			{
				$sQuery = "INSERT INTO b_crm_entity_perms(ENTITY, ENTITY_ID, ATTR) VALUES ('$ENTITY', $ENTITY_ID, '".$DB->ForSql($sAttr)."')";
				$DB->Query($sQuery, false, 'FILE: '.__FILE__.'<br /> LINE: '.__LINE__);
			}
		}
	}
}