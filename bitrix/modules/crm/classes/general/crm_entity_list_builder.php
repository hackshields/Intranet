<?php
class CCrmEntityListBuilder
{
	private $dbType = '';
	private $tableName = '';
	private $tableAlias = '';
	private $fields = array();
	private $ufEntityID = '';
	private $userFields = array();
	private $fmEntityID = '';
	private $permissionCallback = array();
	private $afterPrepareSqlCallback = array();
	private $sqlData = array();

	function __construct($dbType, $tableName, $tableAlias, $fields, $ufEntityID = '', $fmEntityID = '',  $permissionCallback = array(), $afterPrepareSqlCallback = array())
	{
		global $DBType;

		$this->dbType = strval($dbType);
		if($this->dbType === '')
		{
			$this->dbType = $DBType;
		}

		$this->tableName = strval($tableName);
		$this->tableAlias = strval($tableAlias);

		if(is_array($fields))
		{
			$this->fields = $fields;
		}

		$this->ufEntityID = strval($ufEntityID);
		$this->fmEntityID = strval($fmEntityID);

		if(is_array($permissionCallback))
		{
			$this->permissionCallback = $permissionCallback;
		}

		if(is_array($afterPrepareSqlCallback))
		{
			$this->afterPrepareSqlCallback = $afterPrepareSqlCallback;
		}
	}

	public function GetTableName()
	{
		return $this->tableName;
	}

	public function GetTableAlias()
	{
		return $this->tableAlias;
	}

	public function GetFields()
	{
		return $this->fields;
	}

	public function GetSqlData()
	{
		return $this->sqlData;
	}

	//Override user fields
	public function SetUserFields($fields)
	{
		if(is_array($fields))
		{
			$this->userFields = $fields;
		}
	}

	public function GetUserFields()
	{
		return $this->userFields;
	}

	private function Add2SqlData($sql, $type, $add2Start = false, $replace = '')
	{
		$sql = strval($sql);
		if($sql === '')
		{
			return;
		}

		if($type === 'SELECT')
		{
			if (isset($this->sqlData['SELECT']) && $this->sqlData['SELECT'] !== '')
			{
				$this->sqlData['SELECT'] .= $sql;
			}
			else
			{
				$this->sqlData['SELECT'] = $sql;
			}
		}
		elseif($type === 'FROM')
		{
			if (!isset($this->sqlData['FROM']) || $this->sqlData['FROM'] === '')
			{
				$this->sqlData['FROM'] = $sql;
			}
			else
			{
				if($replace !== '' && strpos($this->sqlData['FROM'], $replace) !== false)
				{
					$this->sqlData['FROM'] = str_replace($replace, $sql, $this->sqlData['FROM']);
				}
				else
				{
					if($add2Start)
					{
						$this->sqlData['FROM'] = $sql.' '.$this->sqlData['FROM'];
					}
					else
					{
						$this->sqlData['FROM'] .= ' '.$sql;
					}
				}
			}
		}
		elseif($type === 'WHERE')
		{
			if (isset($this->sqlData['WHERE']) && $this->sqlData['WHERE'] !== '')
			{
				$this->sqlData['WHERE'] = "({$this->sqlData['WHERE']}) AND ($sql)";
			}
			else
			{
				$this->sqlData['WHERE'] = $sql;
			}
		}
		elseif($type === 'ORDERBY')
		{
			if (isset($this->sqlData['ORDERBY']) && $this->sqlData['ORDERBY'] !== '')
			{
				$this->sqlData['ORDERBY'] .= ', '.$sql;
			}
			else
			{
				$this->sqlData['ORDERBY'] = $sql;
			}
		}
	}

	public function Prepare($arOrder = array(), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array(), $arOptions = array())
	{
		global $DB, $USER;

		// ID must present in select (If select is empty it will be filled by CSqlHelper::PrepareSql)
		if(!is_array($arSelectFields))
		{
			$arSelectFields = array();
		}

		if(count($arSelectFields) > 0 && !in_array('*', $arSelectFields, true) && !in_array('ID', $arSelectFields, true))
		{
			$arSelectFields[] = 'ID';
		}

		// 'Joins' implement custom filter logic
		$joins = array();
		if(isset($arFilter['__JOINS']) && is_array($arFilter['__JOINS']))
		{
			$joins = $arFilter['__JOINS'];
			unset($arFilter['__JOINS']);
		}

		// Processing of special fields
		if ($this->fmEntityID !== '' && isset($arFilter['FM']) && !empty($arFilter['FM']))
		{
			// TODO: Get rid of extra SQL query. Implement filtration by INNER JOIN.
			$ids = array();
			$res = CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array(
					'ENTITY_ID' => $this->fmEntityID,
					'FILTER' => $arFilter['FM'])
			);

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

		// Processing user fields
		$ufSql = null;
		if($this->ufEntityID !== '')
		{
			$ufSql = new CUserTypeSQL;
			$ufSql->SetEntity($this->ufEntityID, $this->tableAlias.'.ID');
			$ufSql->SetSelect($arSelectFields);
			$ufSql->SetFilter($arFilter);
			$ufSql->SetOrder($arOrder);

			$userType = new CCrmUserType($GLOBALS['USER_FIELD_MANAGER'], $this->ufEntityID);
			$userType->ListPrepareFilter($arFilter);
		}

		$this->sqlData = CSqlHelper::PrepareSql($this->fields, $arOrder, $arFilter, $arGroupBy, $arSelectFields, $arOptions);
		$this->sqlData['SELECT'] = str_replace('%%_DISTINCT_%% ', '', $this->sqlData['SELECT']);

		if(count($joins) >0)
		{
			foreach($joins as &$join)
			{
				// INNER JOINs will be added tostart
				$this->Add2SqlData($join['SQL'], 'FROM', (!isset($join['TYPE']) || $join['TYPE'] === 'INNER'), (isset($join['REPLACE']) ? $join['REPLACE'] : ''));
			}
			unset($join);
		}

		// Apply user permission logic
		if(count($this->permissionCallback) > 0)
		{
			if (!CCrmPerms::IsAdmin()
				&& (!array_key_exists('CHECK_PERMISSIONS', $arFilter) || $arFilter['CHECK_PERMISSIONS'] !== 'N'))
			{
				$arPermType = !isset($arFilter['PERMISSION']) ? 'READ' : (is_array($arFilter['PERMISSION']) ? $arFilter['PERMISSION'] : array($arFilter['PERMISSION']));
				$permissionSql = call_user_func_array($this->permissionCallback, array($this->tableAlias, $arPermType));

				if(is_bool($permissionSql) && !$permissionSql)
				{
					$CDBResult = new CDBResult();
					$CDBResult->InitFromArray(array());
					return $CDBResult;
				}

				if($permissionSql !== '')
				{
					$this->Add2SqlData($permissionSql, 'WHERE');
				}
			}
		}

		// Apply custom SQL logic
		if(count($this->afterPrepareSqlCallback) > 0)
		{
			$arUserSql = call_user_func_array($this->afterPrepareSqlCallback, array($this, $arOrder, $arFilter, $arGroupBy, $arSelectFields));
			if(is_array($arUserSql))
			{
				if(isset($arUserSql['FROM']))
				{
					$this->Add2SqlData($arUserSql['FROM'], 'FROM');
				}

				if(isset($arUserSql['WHERE']))
				{
					$this->Add2SqlData($arUserSql['WHERE'], 'WHERE');
				}
			}
		}

		if($ufSql)
		{
			// Adding user fields to SELECT
			$this->Add2SqlData($ufSql->GetSelect(), 'SELECT');

			// Adding user fields to WHERE
			$this->Add2SqlData($ufSql->GetFilter(), 'WHERE');

			// Adding user fields to ORDER BY
			if(is_array($arOrder))
			{
				foreach ($arOrder as $orderKey => $order)
				{
					$orderSql = $ufSql->GetOrder($orderKey);
					if(!is_string($orderSql) || $orderSql === '')
					{
						continue;
					}

					$order = strtoupper($order);
					if($order !== 'ASC' && $order !== 'DESC')
					{
						$order = 'ASC';
					}

					$this->Add2SqlData("$orderSql $order", 'ORDERBY');
				}
			}

			// Adding user fields to joins
			$this->Add2SqlData($ufSql->GetJoin($this->tableAlias.'.ID'), 'FROM');
		}

		//Get count only
		if (is_array($arGroupBy) && count($arGroupBy) == 0)
		{
			$sql = 'SELECT '
				.$this->sqlData['SELECT']
				.' FROM '.$this->tableName.' '.$this->tableAlias;

			if (isset($this->sqlData['FROM'][0]))
			{
				$sql .= ' '.$this->sqlData['FROM'];
			}

			if (isset($this->sqlData['WHERE'][0]))
			{
				$sql .= ' WHERE '.$this->sqlData['WHERE'];
			}

			$dbRes = $DB->Query($sql, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
			if ($arRes = $dbRes->Fetch())
			{
				return intval($arRes['CNT']);
			}

			return false;
		}

		$sql = 'SELECT '.$this->sqlData['SELECT'].' FROM '.$this->tableName.' '.$this->tableAlias;

		if (isset($this->sqlData['FROM'][0]))
		{
			$sql .= ' '.$this->sqlData['FROM'];
		}

		if (isset($this->sqlData['WHERE'][0]))
		{
			$sql .= ' WHERE '.$this->sqlData['WHERE'];
		}

		if (isset($this->sqlData['GROUPBY'][0]))
		{
			$sql .= ' GROUP BY '.$this->sqlData['GROUPBY'];
		}

		if (isset($this->sqlData['ORDERBY'][0]))
		{
			$sql .= ' ORDER BY '.$this->sqlData['ORDERBY'];
		}

		$enableNavigation = is_array($arNavStartParams);
		$top = $enableNavigation && isset($arNavStartParams['nTopCount']) ? intval($arNavStartParams['nTopCount']) : 0;
		if ($enableNavigation && $top <= 0)
		{
			$cnt = CSqlHelper::GetRowCount($this->sqlData, $this->tableName, $this->tableAlias, $this->dbType);
			$dbRes = new CDBResult();

			if($this->ufEntityID !== '')
			{
				$dbRes->SetUserFields($GLOBALS['USER_FIELD_MANAGER']->GetUserFields($this->ufEntityID));
			}
			elseif(!empty($this->userFields))
			{
				$dbRes->SetUserFields($this->userFields);
			}

			//trace('CCrmEntityListBuilder::Prepare, SQL', $sql, 1);
			$dbRes->NavQuery($sql, $cnt, $arNavStartParams);
		}
		else
		{
			if($enableNavigation && $top > 0)
			{
				CSqlHelper::PrepareSelectTop($sql, $top, $this->dbType);
			}

			//trace('CCrmEntityListBuilder::Prepare, SQL', $sql, 1);
			$dbRes = $DB->Query($sql, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
			if($this->ufEntityID !== '')
			{
				$dbRes->SetUserFields($GLOBALS['USER_FIELD_MANAGER']->GetUserFields($this->ufEntityID));
			}
			elseif(!empty($this->userFields))
			{
				$dbRes->SetUserFields($this->userFields);
			}
		}
		return $dbRes;
	}
}
