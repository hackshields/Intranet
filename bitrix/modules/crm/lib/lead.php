<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage crm
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Crm;

if (!\CModule::IncludeModule('report'))
	return;

use Bitrix\Main\Entity;

class LeadTable extends Entity\DataManager
{
	public static function getFilePath()
	{
		return __FILE__;
	}

	public static function getMap()
	{
		return array(
			'ID' => array(
				'data_type' => 'integer',
				'primary' => true,
				'autocomplete' => true,
			),
			'TITLE' => array(
				'data_type' => 'string'
			),
			'STATUS_ID' => array(
				'data_type' => 'string'
			),
			'STATUS_BY' => array(
				'data_type' => 'Status',
				'reference' => array(
					'=this.STATUS_ID' => 'ref.STATUS_ID',
					'=ref.ENTITY_ID' => array('?', 'STATUS')
				)
			),
			'STATUS_DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'PRODUCT_ID' => array(
				'data_type' => 'string'
			),
//			'PRODUCT_BY' => array(
//				'data_type' => 'CrmStatus',
//				'reference' => array('=this.PRODUCT_ID' => 'ref.STATUS_ID')
//			),
			'OPPORTUNITY' => array(
				'data_type' => 'integer'
			),
			'CURRENCY_ID' => array(
				'data_type' => 'string'
			),
//			'CURRENCY_BY' => array(
//				'data_type' => 'CrmStatus',
//				'reference' => array('CURRENCY_ID', 'STATUS_ID')
//			),
			'COMMENTS' => array(
				'data_type' => 'string'
			),
			'NAME' => array(
				'data_type' => 'string'
			),
			'LAST_NAME' => array(
				'data_type' => 'string'
			),
			'SECOND_NAME' => array(
				'data_type' => 'string'
			),
			'COMPANY_TITLE' => array(
				'data_type' => 'string'
			),
			'POST' => array(
				'data_type' => 'string'
			),
			'ADDRESS' => array(
				'data_type' => 'string'
			),
			'SOURCE_ID' => array(
				'data_type' => 'string'
			),
			'SOURCE_BY' => array(
				'data_type' => 'Status',
				'reference' => array(
					'=this.SOURCE_ID' => 'ref.STATUS_ID',
					'=ref.ENTITY_ID' => array('?', 'SOURCE')
				)
			),
			'SOURCE_DESCRIPTION' => array(
				'data_type' => 'string'
			),
			'DATE_CREATE' => array(
				'data_type' => 'datetime'
			),
			'DATE_MODIFY' => array(
				'data_type' => 'datetime'
			),
			'ASSIGNED_BY_ID' => array(
				'data_type' => 'integer'
			),
			'ASSIGNED_BY' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.ASSIGNED_BY_ID' => 'ref.ID')
			),
			'CREATED_BY_ID' => array(
				'data_type' => 'integer'
			),
			'CREATED_BY' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.CREATED_BY_ID' => 'ref.ID')
			),
			'MODIFY_BY_ID' => array(
				'data_type' => 'integer'
			),
			'MODIFY_BY' => array(
				'data_type' => 'Bitrix\Main\User',
				'reference' => array('=this.MODIFY_BY_ID' => 'ref.ID')
			),
			'EVENT_RELATION' => array(
				'data_type' => 'EventRelations',
				'reference' => array('=this.ID' => 'ref.ENTITY_ID')
			)
		);
	}
}
