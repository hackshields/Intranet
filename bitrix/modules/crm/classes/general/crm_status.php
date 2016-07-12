<?php

if(!defined('CACHED_b_crm_status')) define('CACHED_b_crm_status', 360000);

IncludeModuleLangFile(__FILE__);

class CCrmStatus
{
	protected $cdb = null;

	protected $entityId = '';

	protected $arStatus = array();

	function __construct($entityId)
	{
		global $DB;

		$this->cdb = $DB;

		$this->entityId = $entityId;

		$this->arStatus = self::GetStatus($entityId);
	}

	public static function GetEntityTypes()
	{
		$arEntityType = Array(
			'STATUS'		=> array( 'ID' =>'STATUS', 'NAME' => GetMessage('CRM_STATUS_TYPE_STATUS')),
			'SOURCE'		=> array( 'ID' =>'SOURCE', 'NAME' => GetMessage('CRM_STATUS_TYPE_SOURCE')),
			'PRODUCT'		=> array( 'ID' =>'PRODUCT', 'NAME' => GetMessage('CRM_STATUS_TYPE_PRODUCT')),
			'CONTACT_TYPE'	=> array( 'ID' =>'CONTACT_TYPE', 'NAME' => GetMessage('CRM_STATUS_TYPE_CONTACT_TYPE')),
			'COMPANY_TYPE'	=> array( 'ID' =>'COMPANY_TYPE', 'NAME' => GetMessage('CRM_STATUS_TYPE_COMPANY_TYPE')),
			'EMPLOYEES'		=> array( 'ID' =>'EMPLOYEES', 'NAME' => GetMessage('CRM_STATUS_TYPE_EMPLOYEES')),
			'INDUSTRY'		=> array( 'ID' =>'INDUSTRY', 'NAME' => GetMessage('CRM_STATUS_TYPE_INDUSTRY')),
			'DEAL_TYPE'		=> array( 'ID' =>'DEAL_TYPE', 'NAME' => GetMessage('CRM_STATUS_TYPE_DEAL_TYPE')),
			'DEAL_STAGE'	=> array( 'ID' =>'DEAL_STAGE', 'NAME' => GetMessage('CRM_STATUS_TYPE_DEAL_STAGE')),
			'CURRENCY'		=> array( 'ID' =>'CURRENCY', 'NAME' => GetMessage('CRM_STATUS_TYPE_CURRENCY')),
			'EVENT_TYPE'	=> array( 'ID' =>'EVENT_TYPE', 'NAME' => GetMessage('CRM_STATUS_TYPE_EVENT_TYPE')),
		);

		$events = GetModuleEvents("crm", "OnGetEntityTypes");
		while($arEvent = $events->Fetch())
			$arEntityType = ExecuteModuleEventEx($arEvent, array($arEntityType));

		return $arEntityType;
	}

	public function Add($arFields, $bCheckStatusId = true)
	{
		$err_mess = (self::err_mess()).'<br />Function: Add<br />Line: ';

		if (!$this->CheckFields($arFields, $bCheckStatusId))
			return false;

		if (!is_set($arFields['SORT']) ||
			(is_set($arFields['SORT']) && !intval($arFields['SORT']) > 0))
			$arFields['SORT'] = 10;

		if (!is_set($arFields, 'STATUS_ID'))
			$arFields['STATUS_ID'] = '';

		if (!is_set($arFields, 'SYSTEM'))
			$arFields['SYSTEM'] = 'N';

		$arFields_i = Array(
			'ENTITY_ID'	=> $this->entityId,
			'STATUS_ID'	=> !empty($arFields['STATUS_ID']) ? $arFields['STATUS_ID'] : $this->GetNextStatusId(),
			'NAME'		=> $arFields['NAME'],
			'NAME_INIT'	=> $arFields['SYSTEM'] == 'Y' ? $arFields['NAME'] : '',
			'SORT'		=> IntVal($arFields['SORT']),
			'SYSTEM'	=> $arFields['SYSTEM'] == 'Y'? 'Y': 'N',
		);
		$ID = $this->cdb->Add('b_crm_status', $arFields_i);

		global $stackCacheManager;
		$strID = 'b'.$this->entityId;
		$stackCacheManager->Clear('b_crm_status', $strID);

		return $ID;
	}

	public function Update($ID, $arFields)
	{
		$err_mess = (self::err_mess()).'<br />Function: Update<br />Line: ';

		if (!$this->CheckFields($arFields))
			return false;

		$ID = IntVal($ID);

		if (!is_set($arFields['SORT']) ||
			(is_set($arFields['SORT']) && !intval($arFields['SORT']) > 0))
			$arFields['SORT'] = 10;

		$arFields_u = Array(
			'NAME'		=> $arFields['NAME'],
			'SORT'		=> IntVal($arFields['SORT']),
		);
		if (is_set($arFields, 'SYSTEM'))
			$arFields_u['SYSTEM'] == 'Y'? 'Y': 'N';

		$strUpdate = $this->cdb->PrepareUpdate('b_crm_status', $arFields_u);
		if(!$this->cdb->Query('UPDATE b_crm_status SET '.$strUpdate.' WHERE ID='.$ID, false, $err_mess.__LINE__))
			return false;

		global $stackCacheManager;
		$strID = 'b'.$this->entityId;
		$stackCacheManager->Clear('b_crm_status', $strID);

		return $ID;
	}

	public function Delete($ID)
	{
		$err_mess = (self::err_mess()).'<br />Function: Delete<br />Line: ';

		$ID = IntVal($ID);

		$res = $this->cdb->Query("DELETE FROM b_crm_status WHERE ID=$ID", false, $err_mess.__LINE__);

		global $stackCacheManager;
		$strID = 'b'.$this->entityId;
		$stackCacheManager->Clear('b_crm_status', $strID);

		return $res;
	}

	public static function GetList($arSort=array(), $arFilter=Array())
	{
		global $DB;

		$arSqlSearch = Array();
		$strSqlSearch = '';
		$err_mess = (self::err_mess()).'<br />Function: GetList<br />Line: ';

		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			for ($i=0, $ic=count($filter_keys); $i<$ic; $i++)
			{
				$val = $arFilter[$filter_keys[$i]];
				if (strlen($val)<=0 || $val=='NOT_REF') continue;
				switch(strtoupper($filter_keys[$i]))
				{
					case 'ID':
						$arSqlSearch[] = GetFilterQuery('CS.ID', $val, 'N');
					break;
					case 'ENTITY_ID':
						$arSqlSearch[] = GetFilterQuery('CS.ENTITY_ID', $val);
					break;
					case 'STATUS_ID':
						$arSqlSearch[] = GetFilterQuery('CS.STATUS_ID', $val);
					break;
					case 'NAME':
						$arSqlSearch[] = GetFilterQuery('CS.NAME', $val);
					break;
					case 'SORT':
						$arSqlSearch[] = GetFilterQuery('CS.SORT', $val);
					break;
					case 'SYSTEM':
						$arSqlSearch[] = ($val=='Y') ? "CS.SYSTEM='Y'" : "CS.SYSTEM='N'";
					break;
				}
			}
		}

		$sOrder = '';
		foreach($arSort as $key=>$val)
		{
			$ord = (strtoupper($val) <> 'ASC'? 'DESC':'ASC');
			switch (strtoupper($key))
			{
				case 'ID':		$sOrder .= ', CS.ID '.$ord; break;
				case 'ENTITY_ID':	$sOrder .= ', CS.ENTITY_ID '.$ord; break;
				case 'STATUS_ID':	$sOrder .= ', CS.STATUS_ID '.$ord; break;
				case 'NAME':	$sOrder .= ', CS.NAME '.$ord; break;
				case 'SORT':	$sOrder .= ', CS.SORT '.$ord; break;
				case 'SYSTEM':	$sOrder .= ', CS.SYSTEM '.$ord; break;
			}
		}

		if (strlen($sOrder)<=0)
			$sOrder = 'CS.ID DESC';

		$strSqlOrder = ' ORDER BY '.TrimEx($sOrder,',');

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				CS.ID, CS.ENTITY_ID, CS.STATUS_ID, CS.NAME, CS.NAME_INIT, CS.SORT, CS.SYSTEM
			FROM
				b_crm_status CS
			WHERE
			$strSqlSearch
			$strSqlOrder";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);

		return $res;
	}

	private function CheckStatusId($statusId)
	{
		return isset($this->arStatus[$statusId])? true: false;
	}

	public function GetNextStatusId()
	{
		static $arMaxId = array();
		if (!isset($arMaxId[$this->entityId]))
		{
			foreach($this->arStatus as $stId => $stAr)
				if (is_numeric($stId) && $arMaxId[$this->entityId] < $stId)
					$arMaxId[$this->entityId] = $stId;
		}
		return ++$arMaxId[$this->entityId];
	}

	public static function GetStatus($entityId)
	{
		global $DB;
		$arStatus = Array();
		if(CACHED_b_crm_status===false)
		{
			$squery = "
				SELECT *
				FROM b_crm_status
				WHERE ENTITY_ID = '".$DB->ForSql($entityId)."'
				ORDER BY SORT ASC
			";
			$res = $DB->Query($squery, false, $err_mess.__LINE__);
			while ($row = $res->Fetch())
				$arStatus[$row['STATUS_ID']] = $row;

			return $arStatus;
		}
		else
		{
			global $stackCacheManager;
			$stackCacheManager->SetLength('b_crm_status', 100);
			$stackCacheManager->SetTTL('b_crm_status', CACHED_b_crm_status);

			$strID = 'b'.$entityId;
			if($stackCacheManager->Exist('b_crm_status', $strID))
				$arResult = $stackCacheManager->Get('b_crm_status', $strID);
			else
			{
				$arResult = array();
				$squery = "
					SELECT *
					FROM b_crm_status
					WHERE ENTITY_ID = '".$DB->ForSql($entityId)."'
					ORDER BY SORT ASC
				";
				$res = $DB->Query($squery, false, $err_mess.__LINE__);
				while($row = $res->Fetch())
					$arResult[$row['STATUS_ID']] = $row;

				$stackCacheManager->Set('b_crm_status', $strID, $arResult);
			}
			foreach($arResult as $arConfig)
				$arStatus[$arConfig['STATUS_ID']] = $arConfig;

			return $arStatus;
		}
	}

	public static function GetStatusList2($entityId)
	{
		$arStatusList = Array();
		$ar = self::GetStatus($entityId);
		foreach($ar as $arStatus)
			$arStatusList[] = array('ID' => $arStatus['STATUS_ID'], 'VALUE' => $arStatus['NAME']);
		$cr = new CDBResult();
		$cr->InitFromArray($arStatusList);
		return $cr;
	}

	public static function GetStatusList($entityId)
	{
		$arStatusList = Array();
		$ar = self::GetStatus($entityId);
		foreach($ar as $arStatus)
			$arStatusList[$arStatus['STATUS_ID']] = $arStatus['NAME'];

		return $arStatusList;
	}

	public static function GetStatusListEx($entityId)
	{
		$arStatusList = Array();
		$ar = self::GetStatus($entityId);
		foreach($ar as $arStatus)
			$arStatusList[$arStatus['STATUS_ID']] = htmlspecialcharsbx($arStatus['NAME']);

		return $arStatusList;
	}

	public function GetStatusById($ID)
	{
		static $arStatus = array();
		if (!isset($arStatus[$this->entityId]))
		{
			foreach($this->arStatus as $ar)
				$arStatus[$this->entityId][$ar['ID']] = $ar;
		}
		return isset($arStatus[$this->entityId][$ID])? $arStatus[$this->entityId][$ID]: false;
	}

	public function GetStatusByStatusId($statusId)
	{
		return isset($this->arStatus[$statusId])? $this->arStatus[$statusId]: false;
	}

	private function CheckFields($arFields, $bCheckStatusId = true)
	{
		$aMsg = array();

		if(is_set($arFields, 'NAME') && trim($arFields['NAME'])=='')
			$aMsg[] = array('id'=>'NAME', 'text'=>GetMessage('CRM_STATUS_ERR_NAME'));
		if(is_set($arFields, 'SYSTEM') && !($arFields['SYSTEM'] == 'Y' || $arFields['SYSTEM'] == 'N'))
			$aMsg[] = array('id'=>'SYSTEM', 'text'=>GetMessage('CRM_STATUS_ERR_SYSTEM'));
		if(is_set($arFields, 'STATUS_ID') && trim($arFields['STATUS_ID'])=='')
			$aMsg[] = array('id'=>'STATUS_ID', 'text'=>GetMessage('CRM_STATUS_ERR_STATUS_ID'));
		if (is_set($arFields, 'STATUS_ID') && $bCheckStatusId && $this->CheckStatusId($arFields['STATUS_ID']))
			$aMsg[] = array('id'=>'STATUS_ID', 'text'=>GetMessage('CRM_STATUS_ERR_DUPLICATE_STATUS_ID'));

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$GLOBALS['APPLICATION']->ThrowException($e);
			return false;
		}

		return true;
	}

	private static function err_mess()
	{
		return '<br />Class: CCrmStatus<br />File: '.__FILE__;
	}
}

?>
