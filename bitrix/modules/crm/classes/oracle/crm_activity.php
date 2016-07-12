<?php
class CCrmActivity extends CAllCrmActivity
{
	const TABLE_NAME = 'B_CRM_ACT';
	const BINDING_TABLE_NAME = 'B_CRM_ACT_BIND';
	const COMMUNICATION_TABLE_NAME = 'B_CRM_ACT_COMM';
	const ELEMENT_TABLE_NAME = 'B_CRM_ACT_ELEM';
	const USER_ACTIVITY_TABLE_NAME = 'B_CRM_USR_ACT';
	const FIELD_MULTI_TABLE_NAME = 'B_CRM_FIELD_MULTI';
	const DB_TYPE = 'ORACLE';

	public static function DoSaveBindings($ID, &$arBindings)
	{
		global $DB;

		$ID = intval($ID);
		if($ID <= 0 || !is_array($arBindings))
		{
			self::RegisterError(array('text' => 'Invalid arguments are supplied.'));
			return false;
		}

		if(!is_array($arPresentComms = self::GetBindings($ID)))
		{
			self::RegisterError(array('text' => self::GetLastErroMessage()));
			return false;
		}

		$ar2Add = array();
		$ar2Delete = array();
		self::PrepareAssociationsSave($arBindings, $arPresentComms, $ar2Add, $ar2Delete);

		if($ID > 0)
		{
			self::DeleteBindings($ID);
		}

		if(count($arBindings) == 0)
		{
			return true;
		}

		$bulkColumns = '';
		$bulkValues = array();

		foreach($arBindings as &$arBinding)
		{
			if(isset($arBinding['ID']))
			{
				unset($arBinding['ID']);
			}
			
			$data = $DB->PrepareInsert(self::BINDING_TABLE_NAME, $arBinding);
			if(strlen($bulkColumns) == 0)
			{
				$bulkColumns = $data[0];
			}

			$bulkValues[] = $data[1];
		}
		unset($arComm);

		if(count($bulkValues) == 0)
		{
			self::RegisterError(array('text' => 'There are no values for insert.'));
			return false;
		}

		$query = '';
		foreach($bulkValues as &$value)
		{
			$query .= ($query !== '' ? ' UNION ALL ' : '').'SELECT '.$value.' FROM dual';
		}

		if(strlen($query) == 0)
		{
			self::RegisterError(array('text' => 'Could not build query.'));
			return false;
		}

		$DB->Query(
			'INSERT INTO '.self::BINDING_TABLE_NAME.'('.$bulkColumns.') '.$query,
			false,
			'File: '.__FILE__.'<br/>Line: '.__LINE__
		);
		return true;
	}
	public static function PrepareBindingsFilterSql(&$arBindings, $tableAlias = '')
	{
		if(!is_array($arBindings))
		{
			return '';
		}
		
		$tableAlias = strval($tableAlias);
		if($tableAlias === '')
		{
			$tableAlias = CAllCrmActivity::TABLE_ALIAS;
		}

		$bindingTableName = self::BINDING_TABLE_NAME;

		$sql = '';
		foreach($arBindings as &$binding)
		{
			$ownerTypeID = isset($binding['OWNER_TYPE_ID']) ? intval($binding['OWNER_TYPE_ID']) : 0;
			if($ownerTypeID <= 0)
			{
				continue;
			}

			$s = "B.OWNER_TYPE_ID = {$ownerTypeID}";
			$ownerID = isset($binding['OWNER_ID']) ? intval($binding['OWNER_ID']) : 0;
			if($ownerID > 0)
			{
				$s .= " AND B.OWNER_ID = {$ownerID}";
			}

			if($sql !== '')
			{
				$sql .= ' AND ';
			}

			if($ownerID > 0)
			{
				$sql .= "(({$tableAlias}.OWNER_TYPE_ID = {$ownerTypeID} AND {$tableAlias}.OWNER_ID = {$ownerID}) OR EXISTS (SELECT B.ID FROM {$bindingTableName} B WHERE B.ACTIVITY_ID = {$tableAlias}.ID AND {$s} AND ROWNUM = 1))";
			}
			else
			{
				$sql .= "({$tableAlias}.OWNER_TYPE_ID = {$ownerTypeID} OR EXISTS (SELECT B.ID FROM {$bindingTableName} B WHERE B.ACTIVITY_ID = {$tableAlias}.ID AND {$s} AND ROWNUM = 1))";
			}
		}
		unset($binding);

		return $sql;
	}
	public static function DoSaveCommunications($ID, &$arComms, $arFields = array(), $registerEvents = true, $checkPerms = true)
	{
		global $DB;

		$ID = intval($ID);
		if($ID <= 0 || !is_array($arComms))
		{
			self::RegisterError(array('text' => 'Invalid arguments are supplied.'));
			return false;
		}

		if(!is_array($arPresentComms = self::GetCommunications($ID)))
		{
			self::RegisterError(array('text' => self::GetLastErroMessage()));
			return false;
		}

		$ar2Delete = array();
		$ar2Add = array();
		foreach($arComms as $arComm)
		{
			$commID = isset($arComm['ID']) ? intval($arComm['ID']) : 0;
			if($commID <= 0)
			{
				$ar2Add[] = $arComm;
				continue;
			}
		}

		foreach($arPresentComms as $arPresentComm)
		{
			$presentCommID = intval($arPresentComm['ID']);
			$found = false;
			foreach($arComms as $arComm)
			{
				$commID = isset($arComm['ID']) ? intval($arComm['ID']) : 0;
				if($commID === $presentCommID)
				{
					$found = true;
					break;
				}
			}

			if(!$found)
			{
				$ar2Delete[] = $arPresentComm;
			}
		}


		if($ID > 0)
		{
			self::DeleteCommunications($ID);
		}

		if($registerEvents)
		{
			foreach($ar2Delete as $arComm)
			{
				self::RegisterCommunicationEvent(
					$ID,
					$arFields,
					$arComm,
					'REMOVE',
					$checkPerms
				);
			}
		}

		if(count($arComms) == 0)
		{
			return true;
		}

		foreach($arComms as &$arComm)
		{
			if(isset($arComm['ID']))
			{
				unset($arComm['ID']);
			}

			$data = $DB->PrepareInsert(self::COMMUNICATION_TABLE_NAME, $arComm);

			// ENTITY_SETTINGS is CLOB field - we have to call the $DB->QueryBind.
			$DB->QueryBind(
				'INSERT INTO '.self::COMMUNICATION_TABLE_NAME.'('.$data[0].') VALUES('.$data[1].')',
				array(
					'ENTITY_SETTINGS' => isset($arComm['ENTITY_SETTINGS']) ? $arComm['ENTITY_SETTINGS'] : ''
				),
				false,
				'File: '.__FILE__.'<br/>Line: '.__LINE__
			);
		}
		unset($arComm);

		if($registerEvents)
		{
			foreach($ar2Add as $arComm)
			{
				self::RegisterCommunicationEvent(
					$ID,
					$arFields,
					$arComm,
					'ADD',
					$checkPerms
				);
			}
		}
		return true;
	}
	public static function DoSaveElementIDs($ID, $storageTypeID, $arElementIDs)
	{
		global $DB;

		$ID = intval($ID);
		$storageTypeID = intval($storageTypeID);
		if($ID <= 0 || !CCrmActivityStorageType::IsDefined($storageTypeID) || !is_array($arElementIDs))
		{
			self::RegisterError(array('text' => 'Invalid arguments are supplied.'));
			return false;
		}

		$DB->Query(
			'DELETE FROM '.self::ELEMENT_TABLE_NAME.' WHERE ACTIVITY_ID = '.$ID,
			false,
			'File: '.__FILE__.'<br/>Line: '.__LINE__
		);

		if(empty($arElementIDs))
		{
			return true;
		}

		$arRows = array();
		foreach($arElementIDs as $elementID)
		{
			$arRows[] = array(
				'ACTIVITY_ID'=> $ID,
				'STORAGE_TYPE_ID' => $storageTypeID,
				'ELEMENT_ID' => $elementID
			);
		}

		$bulkColumns = '';
		$bulkValues = array();

		foreach($arRows as &$row)
		{
			$data = $DB->PrepareInsert(self::ELEMENT_TABLE_NAME, $row);
			if($bulkColumns === '')
			{
				$bulkColumns = $data[0];
			}

			$bulkValues[] = $data[1];
		}
		unset($row);

		$query = '';
		foreach($bulkValues as &$value)
		{
			$query .= ($query !== '' ? ' UNION ALL ' : '').'SELECT '.$value.' FROM dual';
		}

		if($query !== '')
		{
			$DB->Query(
				'INSERT INTO '.self::ELEMENT_TABLE_NAME.'('.$bulkColumns.') '.$query,
				false,
				'File: '.__FILE__.'<br/>Line: '.__LINE__
			);
		}

		return true;
	}
	public static function DoSaveNearestUserActivity($arFields)
	{
		global $DB;
		$userID = isset($arFields['USER_ID']) ? intval($arFields['USER_ID']) : 0;
		$ownerID = isset($arFields['OWNER_ID']) ? intval($arFields['OWNER_ID']) : 0;
		$ownerTypeID = isset($arFields['OWNER_TYPE_ID']) ? intval($arFields['OWNER_TYPE_ID']) : 0;
		$activityID = isset($arFields['ACTIVITY_ID']) ? intval($arFields['ACTIVITY_ID']) : 0;
		$activityTime = isset($arFields['ACTIVITY_TIME']) ? $arFields['ACTIVITY_TIME'] : '';
		if($activityTime !== '')
		{
			$activityTime = $DB->CharToDateFunction($DB->ForSql($activityTime), 'FULL');
		}

		$sql = "MERGE INTO B_CRM_USR_ACT USING (SELECT {$userID} USER_ID, {$ownerID} OWNER_ID, {$ownerTypeID} OWNER_TYPE_ID FROM dual)
			source ON
			(
				source.USER_ID = B_CRM_USR_ACT.USER_ID
				AND source.OWNER_ID = B_CRM_USR_ACT.OWNER_ID
				AND source.OWNER_TYPE_ID = B_CRM_USR_ACT.OWNER_TYPE_ID
			)
			WHEN MATCHED THEN
				UPDATE SET B_CRM_USR_ACT.ACTIVITY_TIME = {$activityTime}, B_CRM_USR_ACT.ACTIVITY_ID = {$activityID}
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, OWNER_ID, OWNER_TYPE_ID, ACTIVITY_TIME, ACTIVITY_ID, DEPARTMENT_ID)
				VALUES ({$userID}, {$ownerID}, {$ownerTypeID}, {$activityTime}, {$activityID}, 0)";

		$DB->Query($sql, false, 'File: '.__FILE__.'<br/>Line: '.__LINE__);
	}
}

