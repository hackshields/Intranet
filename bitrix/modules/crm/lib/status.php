<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Crm;

use Bitrix\Main\Entity;

class StatusTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_crm_status';
	}

	public static function getMap()
	{
		return array(
			'ENTITY_ID' => array(
				'data_type' => 'integer',
				'primary' => true
			),
			'STATUS_ID' => array(
				'data_type' => 'string',
				'primary' => true
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'SORT' => array(
				'data_type' => 'integer'
			)
		);
	}
}
