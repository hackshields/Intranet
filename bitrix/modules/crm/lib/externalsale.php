<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Crm;

use Bitrix\Main\Entity;

class ExternalSaleTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getTableName()
	{
		return 'b_crm_external_sale';
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'NAME' => array(
				'data_type' => 'string'
			)
		);
	}
}
