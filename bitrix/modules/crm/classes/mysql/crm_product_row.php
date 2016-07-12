<?php
class CCrmProductRow extends CAllCrmProductRow
{
	const TABLE_NAME = 'b_crm_product_row';
	const DB_TYPE = 'MYSQL';

	// Contract -->
	public static function DeleteByOwner($ownerType, $ownerID)
	{
		global $DB;

		$DB->Query(
			sprintf('DELETE FROM %s WHERE OWNER_TYPE = \'%s\' AND OWNER_ID = %d', self::TABLE_NAME, strval($ownerType), intval($ownerID)),
			false,
			'File: '.__FILE__.'<br/>Line: '.__LINE__
		);
	}

	public static function DoSaveRows($ownerType, $ownerID, $arRows)
	{
		global $DB;

		self::DeleteByOwner($ownerType, $ownerID);

		if(count($arRows) == 0)
		{
			return true;
		}

		$bulkColumns = '';
		$bulkValues = array();
		foreach($arRows as &$arRow)
		{
			$data = $DB->PrepareInsert(self::TABLE_NAME, $arRow);

			$cols = $data[0];
			$vals = $data[1];

			if(!isset($vals[0])) //empty values
			{
				continue;
			}

			if(!isset($bulkColumns[0]))
			{
				$bulkColumns = $cols;
			}

			$bulkValues[] = $vals;
		}

		if(count($bulkValues) == 0)
		{
			self::RegisterError('There are no values for insert.');
			return false;
		}

		$query = '';
		foreach($bulkValues as &$value)
		{
			$query .= (isset($query[0]) ? ',' : '').'('.$value.')';
		}

		if(!isset($query[0]))
		{
			self::RegisterError('Could not build query.');
			return false;
		}

		$query = 'INSERT INTO '.self::TABLE_NAME.'('.$bulkColumns.') VALUES'.$query;
		$DB->Query(
			$query,
			false,
			'File: '.__FILE__.'<br/>Line: '.__LINE__
		);

		return true;
	}
	// <-- Contract
}
