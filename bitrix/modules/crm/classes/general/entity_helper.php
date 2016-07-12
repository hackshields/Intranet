<?php
class CCrmEntityHelper
{
	private static $ENTITY_KEY = '/^\s*(L|D|C|CO)_([0-9]+)\s*$/i';
	public static function IsEntityKey($key)
	{
		return preg_match(self::$ENTITY_KEY, strval($key)) === 1;
	}
	public static function ParseEntityKey($key, &$entityInfo)
	{
		if(preg_match(self::$ENTITY_KEY, strval($key), $match) !== 1)
		{
			$entityInfo = array();
			return false;
		}

		$entityTypeAbbr = strtoupper($match[1]);
		$entityID = intval($match[2]);
		$entityTypeID = CCrmOwnerType::ResolveID($entityTypeAbbr);
		$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);

		$entityInfo = array(
			'ENTITY_TYPE_ABBR' => $entityTypeAbbr,
			'ENTITY_TYPE_ID' => $entityTypeID,
			'ENTITY_TYPE_NAME' => $entityTypeName,
			'ENTITY_ID' => $entityID
		);
		return true;
	}
	private static function GetCache($sName)
	{
		if(!isset($GLOBALS[$sName]))
		{
			$GLOBALS[$sName] = array();
		}
		return $GLOBALS[$sName];
	}
	public static function GetCached($sCacheName, $sKey)
	{
		$arCache = self::GetCache($sCacheName);

		if(is_set($arCache[$sKey]))
		{
			return 	$arCache[$sKey];
		}

		return false;
	}
	public static function SetCached($sCacheName, $sKey, $value)
	{
		$arCache = self::GetCache($sCacheName);
		$arCache[$sKey] = $value;
	}
	public static function RemoveCached($sCacheName, $sKey)
	{
		$arCache = self::GetCache($sCacheName);
		if(is_set($arCache[$sKey]))
		{
			unset($arCache[$sKey]);
		}
	}
	public static function NormalizeUserFields(&$arFields, $entityID, $manager = null)
	{
		$entityID = strval($entityID);

		if(!$manager)
		{
			$manager = $GLOBALS['USER_FIELD_MANAGER'];
		}

		$userType = new CCrmUserType($manager, $entityID);
		$userType->PrepareUpdate($arFields);
	}
	public static function PrepareMultiFieldFilter(&$arFilter, $arFieldTypes = array(), $comparisonType = '%')
	{
		if(!is_array($arFieldTypes))
		{
			$arFieldTypes = array(strval($arFieldTypes));
		}

		static $defaultFieldTypes = array('EMAIL', 'WEB', 'PHONE', 'IM');
		if(count($arFieldTypes) === 0)
		{
			// Default field types
			$arFieldTypes = $defaultFieldTypes;
		}

		$comparisonType = strval($comparisonType);
		if($comparisonType === '')
		{
			$comparisonType = '%';
		}

		foreach($arFieldTypes as $fieldType)
		{
			if(!isset($arFilter[$fieldType]))
			{
				continue;
			}

			$fieldValue = $arFilter[$fieldType];
			$arFilter['FM'][] = array(
				'TYPE_ID' => $fieldType,
				"{$comparisonType}VALUE" => $fieldValue
			);

			unset($arFilter[$fieldType]);
		}
	}
}
