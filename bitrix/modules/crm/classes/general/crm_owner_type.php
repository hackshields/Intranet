<?php
class CCrmOwnerType
{
	const Undefined = 0;
	const Lead = 1;
	const Deal = 2;
	const Contact = 3;
	const Company = 4;
	private static $ALL_DESCRIPTIONS = array();
	private static $CAPTIONS = array();


	public static function IsDefined($typeID)
	{
		if(!is_numeric($typeID))
		{
			return false;
		}

		$typeID = intval($typeID);
		return $typeID >= self::Lead && $typeID <= self::Company;
	}

	public static function ResolveID($name)
	{
		$name = strtoupper(trim(strval($name)));
		if(strlen($name) == 0)
		{
			return self::Undefined;
		}

		switch($name)
		{
			case CCrmOwnerTypeAbbr::Lead:
			case 'LEAD':
				return self::Lead;
			case CCrmOwnerTypeAbbr::Deal:
			case 'DEAL':
				return self::Deal;
			case CCrmOwnerTypeAbbr::Contact:
			case 'CONTACT':
				return self::Contact;
			case CCrmOwnerTypeAbbr::Company:
			case 'COMPANY':
				return self::Company;
			default:
				return self::Undefined;
		}
	}

	public static function ResolveName($typeID)
	{
		$typeID = intval($typeID);
		if($typeID <= 0)
		{
			return '';
		}

		switch($typeID)
		{
			case self::Lead:
				return 'LEAD';
			case self::Deal:
				return 'DEAL';
			case self::Contact:
				return 'CONTACT';
			case self::Company:
				return 'COMPANY';
			case self::Undefined:
			default:
				return '';
		}
	}

	public static function GetAllNames()
	{
		return array('CONTACT', 'COMPANY', 'LEAD', 'DEAL');
	}

	public static function GetAll()
	{
		return array(self::Contact, self::Company, self::Lead, self::Deal);
	}

	public static function GetAllDescriptions()
	{
		if(!self::$ALL_DESCRIPTIONS[LANGUAGE_ID])
		{
			IncludeModuleLangFile(__FILE__);
			self::$ALL_DESCRIPTIONS[LANGUAGE_ID] = array(
				self::Lead => GetMessage('CRM_OWNER_TYPE_LEAD'),
				self::Deal => GetMessage('CRM_OWNER_TYPE_DEAL'),
				self::Contact => GetMessage('CRM_OWNER_TYPE_CONTACT'),
				self::Company => GetMessage('CRM_OWNER_TYPE_COMPANY'),
			);
		}

		return self::$ALL_DESCRIPTIONS[LANGUAGE_ID];
	}

	public static function GetDescription($typeID)
	{
		$typeID = intval($typeID);
		$all = self::GetAllDescriptions();
		return isset($all[$typeID]) ? $all[$typeID] : '';
	}

	public static function GetShowUrl($typeID, $ID)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);

		switch($typeID)
		{
			case self::Lead:
			{
				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_lead_show'),
					array('lead_id' => $ID)
				);
			}
			case self::Contact:
			{
				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_contact_show'),
					array('contact_id' => $ID)
				);
			}
			case self::Company:
			{
				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_company_show'),
					array('company_id' => $ID)
				);
			}
			case self::Deal:
			{
				return CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_deal_show'),
					array('deal_id' => $ID)
				);
			}
			default:
				return '';
		}
	}

	public static function GetCaption($typeID, $ID)
	{
		$typeID = intval($typeID);
		$ID = intval($ID);
		$key = "{$typeID}_{$ID}";

		if(isset(self::$CAPTIONS[$key]))
		{
			return self::$CAPTIONS[$key];
		}

		switch($typeID)
		{
			case self::Lead:
			{
				$dbRes = CCrmLead::GetListEx(array(), array('=ID' => $ID), false, false, array('TITLE'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if($arRes)
				{
					return (self::$CAPTIONS[$key] = $arRes['TITLE']);
				}
			}
			case self::Contact:
			{
				$dbRes = CCrmContact::GetListEx(array(), array('=ID' => $ID), false, false, array('NAME', 'SECOND_NAME', 'LAST_NAME'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if($arRes)
				{
					return (self::$CAPTIONS[$key] = CUser::FormatName(
						CSite::GetNameFormat(false),
						array(
							'LOGIN' => '',
							'NAME' => isset($arRes['NAME']) ? $arRes['NAME'] : '',
							'SECOND_NAME' => isset($arRes['SECOND_NAME']) ? $arRes['SECOND_NAME'] : '',
							'LAST_NAME' => isset($arRes['LAST_NAME']) ? $arRes['LAST_NAME'] : ''
						),
						false,
						false
					));
				}
			}
			case self::Company:
			{
				$dbRes = CCrmCompany::GetListEx(array(), array('=ID' => $ID), false, false, array('TITLE'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if($arRes)
				{
					return (self::$CAPTIONS[$key] = $arRes['TITLE']);
				}
			}
			case self::Deal:
			{
				$dbRes = CCrmDeal::GetListEx(array(), array('=ID' => $ID), false, false, array('TITLE'));
				$arRes = $dbRes ? $dbRes->Fetch() : null;
				if($arRes)
				{
					return (self::$CAPTIONS[$key] = $arRes['TITLE']);
				}
			}
		}

		return '';
	}

	public static function ResolveUserFieldEntityID($typeID)
	{
		$typeID = intval($typeID);
		if($typeID <= 0)
		{
			return '';
		}

		switch($typeID)
		{
			case self::Lead:
				return CAllCrmLead::$sUFEntityID;
			case self::Deal:
				return CAllCrmDeal::$sUFEntityID;
			case self::Contact:
				return CAllCrmContact::$sUFEntityID;
			case self::Company:
				return CAllCrmCompany::$sUFEntityID;
			case self::Undefined:
			default:
				return '';
		}
	}
}

class CCrmOwnerTypeAbbr
{
	const Undefined = '';
	const Lead = 'L';
	const Deal = 'D';
	const Contact = 'C';
	const Company = 'CO';

	public static function ResolveByTypeID($typeID)
	{
		$typeID = intval($typeID);

		switch($typeID)
		{
			case CCrmOwnerType::Lead:
				return self::Lead;
			case CCrmOwnerType::Deal:
				return self::Deal;
			case CCrmOwnerType::Contact:
				return self::Contact;
			case CCrmOwnerType::Company:
				return self::Company;
			default:
				return self::Undefined;
		}
	}

	public static function ResolveName($typeAbbr)
	{
		$typeAbbr = strtoupper(trim(strval($typeAbbr)));
		if($typeAbbr === '')
		{
			return '';
		}

		switch($typeAbbr)
		{
			case self::Lead:
				return 'LEAD';
			case self::Deal:
				return 'DEAL';
			case self::Contact:
				return 'CONTACT';
			case self::Company:
				return 'COMPANY';
			default:
				return '';
		}
	}
}