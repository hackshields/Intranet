<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CWebDavLogDeletedElementBase
{
	const TABLE_NAME = 'b_webdav_storage_delete_log';
	protected static $maxLengthBatch = 2048;

	public static function add(array $fields)
	{
		$t = static::TABLE_NAME;
		if(empty($fields['VERSION']))
		{
			$fields['VERSION'] = time();
		}
		//todo version is long int
		list($cols, $vals) = static::getDb()->prepareInsert($t, $fields);

		return static::getDb()->query("INSERT INTO {$t} ({$cols}) VALUES({$vals})");
	}

	public static function addBatch(array $items)
	{
		if(empty($items))
		{
			return;
		}
		foreach ($items as $item)
		{
			static::add($item);
		}
		unset($item);
	}

	public static function getList(array $order = array(), array $filter = array())
	{
		$t = static::TABLE_NAME;
		$columns = array(
			'IBLOCK_ID' => true,
			'SECTION_ID' => true,
			'IS_DIR' => true,
			'ELEMENT_ID' => true,
			'VERSION' => true,
		);
		$order = array_intersect_key($order, $columns);
		$where = array_intersect_key($filter, $columns);
		$sqlWhere = array();
		foreach ($where as $field => $value)
		{
			switch($field)
			{
				case 'IBLOCK_ID':
				case 'IS_DIR':
				case 'SECTION_ID':
					$value = (int)$value;
					$sqlWhere[] = $field . '=' . $value;
					break;
				case 'ELEMENT_ID':
					$value = static::getDb()->forSql($value);
					$sqlWhere[] = $field . '=' . '\'' . $value . '\'';
					break;
				case 'VERSION':
					//todo version is long int
					$value = (int)$value;
					$sqlWhere[] = $field . '>=' . $value;
					break;
			}
		}
		unset($value);

		if($sqlWhere)
		{
			$sqlWhere = ' WHERE ' . implode(' AND ', $sqlWhere);
		}
		else
		{
			$sqlWhere = '';
		}

		$sqlOrder = '';
		if($order)
		{
			$sqlOrder = array();
			foreach ($order as $by => $ord)
			{
				$by = strtoupper($by);
				$sqlOrder[] = $by . ' ' . (strtoupper($ord) == 'DESC' ? 'DESC' : 'ASC');
			}
			unset($by);
			$sqlOrder = ' ORDER BY ' . implode(', ', $sqlOrder);
		}

		return static::getDb()->query("SELECT * FROM {$t} {$sqlWhere} {$sqlOrder}");
	}

	public static function isAlreadyRemoved(array $fields)
	{
		if(!($query = static::getList(array('VERSION' => 'DESC'), $fields)))
		{
			return false;
		}
		$last = $query->fetch();

		return $last['VERSION'];
	}

	public function delete()
	{}

	/**
	 * @return CDatabase
	 */
	protected static function getDb()
	{
		global $DB;

		return $DB;
	}
}
