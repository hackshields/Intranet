<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage intranet
 * @copyright 2001-2013 Bitrix
 */

IncludeModuleLangFile(__FILE__);

class CIntranetAuthProvider extends CAuthProvider implements IProviderInterface
{
	public function __construct()
	{
		$this->id = 'intranet';
	}

	public function UpdateCodes($USER_ID)
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $DB, $USER_FIELD_MANAGER;

		$USER_ID = intval($USER_ID);

		$arDep = $USER_FIELD_MANAGER->GetUserFieldValue("USER", "UF_DEPARTMENT", $USER_ID);

		if(is_array($arDep) && !empty($arDep))
		{
			$iblockId = COption::GetOptionInt('intranet', 'iblock_structure', 0);

			$DB->Query("
				INSERT INTO b_user_access (USER_ID, PROVIDER_ID, ACCESS_CODE)
				SELECT ".$USER_ID.", '".$DB->ForSQL($this->id)."', ".$DB->Concat("T1.ROLE", ($DB->type == "MSSQL" ? "CAST(T1.ID as varchar(17))": "T1.ID"))."
				FROM (
					SELECT DISTINCT BS2.ID ID, (case when BS.ID = BS2.ID then 'D' else 'DR' end) ROLE
					FROM b_iblock_section BS
						LEFT JOIN b_iblock_section BS2 ON BS2.IBLOCK_ID = BS.IBLOCK_ID AND BS2.LEFT_MARGIN <= BS.LEFT_MARGIN AND BS2.RIGHT_MARGIN >= BS.RIGHT_MARGIN
					WHERE BS.ID IN (".implode(",", $arDep).")
						AND BS.IBLOCK_ID = ".$iblockId."
						AND BS2.GLOBAL_ACTIVE = 'Y'
					UNION
					SELECT BS.ID ID, 'DR' ROLE
					FROM b_iblock_section BS
					WHERE BS.ID IN (".implode(",", $arDep).")
						AND BS.IBLOCK_ID = ".$iblockId."
						AND BS.GLOBAL_ACTIVE = 'Y'
				) T1
			");
		}
	}

	function OnSearchCheckPermissions($FIELD)
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $DB, $USER, $USER_FIELD_MANAGER;
		$USER_ID = intval($USER->getID());
		$arResult = array();

		$arDep = $USER_FIELD_MANAGER->getUserFieldValue("USER", "UF_DEPARTMENT", $USER_ID);

		if (is_array($arDep) && !empty($arDep))
		{
			$iblockId = COption::getOptionInt('intranet', 'iblock_structure', 0);

			$rs = $DB->Query("
				SELECT ".$DB->Concat("T1.ROLE", ($DB->type == "MSSQL" ? "CAST(T1.ID as varchar(17))": "T1.ID"))." ACCESS_CODE
				FROM (
					SELECT DISTINCT BS2.ID ID, (case when BS.ID = BS2.ID then 'D' else 'DR' end) ROLE
					FROM b_iblock_section BS
						LEFT JOIN b_iblock_section BS2 ON BS2.IBLOCK_ID = BS.IBLOCK_ID AND BS2.LEFT_MARGIN <= BS.LEFT_MARGIN AND BS2.RIGHT_MARGIN >= BS.RIGHT_MARGIN
					WHERE BS.ID IN (".implode(",", $arDep).")
						AND BS.IBLOCK_ID = ".$iblockId."
						AND BS2.GLOBAL_ACTIVE = 'Y'
					UNION
					SELECT BS.ID ID, 'DR' ROLE
					FROM b_iblock_section BS
					WHERE BS.ID IN (".implode(",", $arDep).")
						AND BS.IBLOCK_ID = ".$iblockId."
						AND BS.GLOBAL_ACTIVE = 'Y'
				) T1
			");
			while ($ar = $rs->fetch())
				$arResult[] = $ar["ACCESS_CODE"];
		}

		return $arResult;
	}

	public function AjaxRequest($arParams=false)
	{
		global $USER;
		if(!$USER->IsAuthorized() || CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser($arParams["SITE_ID"]))
			return false;

		$elements = "";
		if ($_REQUEST['action'] == 'structure-item')
		{
			$arFinderParams = Array(
				"PROVIDER" => $this->id,
				"TYPE" => 'structure-item',
			);
			//be careful with field list because of CUser::FormatName()
			$dbRes = CUser::GetList(($by = 'last_name'), ($order = 'asc'),
				array('ACTIVE' => 'Y', 'UF_DEPARTMENT' => intval($_REQUEST['item'])),
				array("SELECT" => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'PERSONAL_PHOTO', 'PERSONAL_GENDER', 'WORK_POSITION', 'PERSONAL_PROFESSION'))
			);
			while ($arUser = $dbRes->Fetch())
			{
				$arPhoto = array('IMG' => '');

				if (!$arUser['PERSONAL_PHOTO'])
				{
					switch ($arUser['PERSONAL_GENDER'])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$arUser['PERSONAL_PHOTO'] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
				}

				if ($arUser['PERSONAL_PHOTO'] > 0)
				{
					$arPhoto = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 30);
				}
				$arItem = Array(
					"ID" => "U".$arUser["ID"],
					"NAME" => CUser::FormatName(CSite::GetNameFormat(false), $arUser, true, false),
					"AVATAR" => $arPhoto['CACHE']['src'],
					"DESC" => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : $arUser['PERSONAL_PROFESSION'],
				);
				$elements .= CFinder::GetFinderItem($arFinderParams, $arItem);
			}
		}
		else
		{
			$search = urldecode($_REQUEST['search']);

			$arFinderParams = Array(
				"PROVIDER" => $this->id,
				"TYPE" => 4,
			);

			$dbRes = CIBlockSection::GetList(
				array('ID' => 'ASC'),
				array('IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'), '%NAME' => $search),
				false,
				array('ID', 'NAME')
			);
			$dbRes->NavStart(7);
			while ($arSection = $dbRes->NavNext(false))
			{
				$arItem = Array(
					"ID" => $arSection["ID"],
					"AVATAR" => "/bitrix/js/main/core/images/access/avatar-user-everyone.png",
					"NAME" => $arSection["NAME"],
					"DESC" => GetMessage("authprov_group"),
					"CHECKBOX" => array(
						"D#ID#" => GetMessage("authprov_check_d"),
						"DR#ID#" => GetMessage("authprov_check_dr"),
					),
				);
				$elements .= CFinder::GetFinderItem($arFinderParams, $arItem);
			}

			$arFinderParams = Array(
				"PROVIDER" => $this->id,
				"TYPE" => 3,
			);

			$strUserIDs = '';
			$dbRes = CUser::SearchUserByName(explode(' ', $search), '', true);
			if ($dbRes)
				while ($arRes = $dbRes->Fetch())
					$strUserIDs .= ($strUserIDs == '' ? '' : '|').$arRes['ID'];

			//be careful with field list because of CUser::FormatName()
			$dbRes = CUser::GetList(($by = 'last_name'), ($order = 'asc'),
				array('ACTIVE' => 'Y', 'NAME_SEARCH' => $search, 'ID' => $strUserIDs),
				array("SELECT" => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'PERSONAL_PHOTO', 'PERSONAL_GENDER', 'WORK_POSITION', 'PERSONAL_PROFESSION'))
			);
			$dbRes->NavStart(7);
			while ($arUser = $dbRes->NavNext(false))
			{
				$arPhoto = array('IMG' => '');

				if (!$arUser['PERSONAL_PHOTO'])
				{
					switch ($arUser['PERSONAL_GENDER'])
					{
						case "M":
							$suffix = "male";
							break;
						case "F":
							$suffix = "female";
							break;
						default:
							$suffix = "unknown";
					}
					$arUser['PERSONAL_PHOTO'] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
				}

				if ($arUser['PERSONAL_PHOTO'] > 0)
				{
					$arPhoto = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 30);
				}
				$arItem = Array(
					"ID" => "U".$arUser["ID"],
					"NAME" => CUser::FormatName(CSite::GetNameFormat(false), $arUser, true, false),
					"AVATAR" => $arPhoto['CACHE']['src'],
					"DESC" => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : $arUser['PERSONAL_PROFESSION'],
				);
				$elements .= CFinder::GetFinderItem($arFinderParams, $arItem);
			}
		}

		return $elements;
	}

	public function GetFormHtml($arParams=false)
	{
		global $USER;
		if(!$USER->IsAuthorized() || CModule::IncludeModule('extranet') && !CExtranet::IsIntranetUser($arParams["SITE_ID"]))
			return false;

		$elements = '';
		$arElement = array();
		$arElements = array();

		$arLRU = CAccess::GetLastRecentlyUsed($this->id);
		if(!empty($arLRU))
		{
			$arFinderParams = Array(
				'PROVIDER' => $this->id,
				'TYPE' => 3,
			);
			$arLast = array();
			$arLastID = array();
			foreach($arLRU as $val)
			{
				if (substr($val, 0, 2) == 'DR')
				{
					$id = substr($val, 2);
					$arLast['DR'][] = $id;
					$arLastID[$id] = $id;
				}
				else if (substr($val, 0, 1) == 'D')
				{
					$id = substr($val, 1);
					$arLast['D'][] = $id;
					$arLastID[$id] = $id;
				}
				else if (substr($val, 0, 1) == 'U')
					$arLast['U'][] = substr($val, 1);
			}
			$dbRes = CIBlockSection::GetList(
				array('ID' => 'ASC'),
				array('IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'), 'ID' => $arLastID),
				false,
				array('ID', 'NAME')
			);
			while ($arSection = $dbRes->Fetch())
			{
				$arElement[$arSection['ID']] = $arSection;
			}
			if (!empty($arLast['DR']))
			{
				foreach ($arLast['DR'] as $value)
				{
					$arItem = Array(
						"ID" => 'DR'.$arElement[$value]['ID'],
						"NAME" => $arElement[$value]['NAME'].': '.GetMessage("authprov_check_dr"),
						"AVATAR" => '/bitrix/js/main/core/images/access/avatar-user-everyone.png',
					);
					$arElements['DR'.$value] = CFinder::GetFinderItem($arFinderParams, $arItem);
				}
			}
			if (!empty($arLast['D']))
			{
				foreach ($arLast['D'] as $value)
				{
					$arItem = Array(
						"ID" => 'D'.$arElement[$value]['ID'],
						"NAME" => $arElement[$value]['NAME'].': '.GetMessage("authprov_check_d"),
						"AVATAR" => '/bitrix/js/main/core/images/access/avatar-user-everyone.png',
					);
					$arElements['D'.$value] = CFinder::GetFinderItem($arFinderParams, $arItem);
				}
			}
			if (!empty($arLast['U']))
			{
				//be careful with field list because of CUser::FormatName()
				$res = CUser::GetList(($by="LAST_NAME"), ($order="asc"),
					array("ID"=>implode("|", $arLast['U'])),
					array("SELECT" => array('ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL', 'PERSONAL_PHOTO', 'PERSONAL_GENDER', 'WORK_POSITION', 'PERSONAL_PROFESSION'))
				);
				while($arUser = $res->Fetch())
				{
					$arPhoto = array('IMG' => '');

					if (!$arUser['PERSONAL_PHOTO'])
					{
						switch ($arUser['PERSONAL_GENDER'])
						{
							case "M":
								$suffix = "male";
								break;
							case "F":
								$suffix = "female";
								break;
							default:
								$suffix = "unknown";
						}
						$arUser['PERSONAL_PHOTO'] = COption::GetOptionInt("socialnetwork", "default_user_picture_".$suffix, false, SITE_ID);
					}

					if ($arUser['PERSONAL_PHOTO'] > 0)
					{
						$arPhoto = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 30);
					}
					$arItem = Array(
						"ID" => "U".$arUser["ID"],
						"NAME" => CUser::FormatName(CSite::GetNameFormat(false), $arUser, true, false),
						"AVATAR" => $arPhoto['CACHE']['src'],
						"DESC" => $arUser['WORK_POSITION'] ? $arUser['WORK_POSITION'] : $arUser['PERSONAL_PROFESSION'],
					);
					$elements .= CFinder::GetFinderItem($arFinderParams, $arItem);
				}
			}

			foreach($arLRU as $val)
			{
				$elements .= $arElements[$val];
			}
		}

		$arFinderParams = Array(
			'PROVIDER' => 'intranet',
			'TYPE' => 'structure',
		);
		$obCache = new CPHPCache();
		$IBlockID = COption::GetOptionInt('intranet', 'iblock_structure');
		$arSecFilter = array('IBLOCK_ID' => $IBlockID);
		$arStructure = array();
		$arSections = array();

		if (!CModule::IncludeModule('extranet') || CExtranet::IsIntranetUser())
		{
			$cache_id = md5(serialize($arSecFilter));
			$cacheDir = '/intranet';
			if($obCache->InitCache(30*86400, $cache_id, $cacheDir))
			{
				$vars = $obCache->GetVars();
				$arSections = $vars["SECTIONS"];
				$arStructure = $vars["STRUCTURE"];
			}
			elseif ($obCache->StartDataCache())
			{
				global $CACHE_MANAGER;
				$CACHE_MANAGER->StartTagCache($cacheDir);
				$CACHE_MANAGER->RegisterTag("iblock_id_".$IBlockID);

				$dbRes = CIBlockSection::GetTreeList($arSecFilter);

				while ($arRes = $dbRes->Fetch())
				{
					$iblockSectionID = intval($arRes['IBLOCK_SECTION_ID']);

					if (!is_array($arStructure[$iblockSectionID]))
						$arStructure[$iblockSectionID] = array($arRes['ID']);
					else
						$arStructure[$iblockSectionID][] = $arRes['ID'];

					$arSections[$arRes['ID']] = $arRes;
				}
				$CACHE_MANAGER->EndTagCache();
				$obCache->EndDataCache(array("SECTIONS" => $arSections, "STRUCTURE" => $arStructure));
			}
		}
		if (CModule::IncludeModule('extranet'))
		{
			$arStructure[0][] = "extranet";
			$arSections["extranet"] = array("ID" => "extranet", "NAME" => GetMessage("authprov_group_extranet"));
		}

		$arItem = self::InEmployeeDrawStructure($arStructure, $arSections, 0);
		$elementsStructure = CFinder::GetFinderItem($arFinderParams, $arItem);

		$arPanels = Array(
			Array(
				"NAME" => GetMessage("authprov_panel_last"),
				"ELEMENTS" => $elements,
			),
			Array(
				"NAME" => GetMessage("authprov_panel_group"),
				"ELEMENTS" => $elementsStructure,
			),
			Array(
				"NAME" => GetMessage("authprov_panel_search"),
				"ELEMENTS" => CFinder::GetFinderItem(Array("TYPE" => "text"), Array("TEXT" => GetMessage("authprov_panel_search_text"))),
				"SEARCH" => "Y",
			),
		);
		$html = CFinder::GetFinderAppearance($arFinderParams, $arPanels);

		return array("HTML"=>$html);
	}

	public function GetNames($arCodes)
	{
		$arID = array();
		foreach($arCodes as $code)
		{
			if(preg_match('/^U([0-9]+)$/', $code, $match))
				$arID['U'][] = $match[1];
			else if(preg_match('/^(D|DR)([0-9]+)$/', $code, $match))
				$arID['D'][] = $match[2];
		}

		$arResult = array();
		if(!empty($arID['D']))
		{
			$res = CIBlockSection::GetList(
				array('ID' => 'ASC'),
				array('IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure'), 'ID'=>$arID['D']),
				false,
				array("ID", "NAME")
			);
			while($arSec = $res->Fetch())
			{
				$arResult["D".$arSec["ID"]] = array("provider" => GetMessage("authprov_name_out_group"), "name"=>$arSec["NAME"].": ".GetMessage("authprov_check_d"));
				$arResult["DR".$arSec["ID"]] = array("provider" => GetMessage("authprov_name_out_group"), "name"=>$arSec["NAME"].": ".GetMessage("authprov_check_dr"));
			}
		}
		if(!empty($arID['U']))
		{
			$res = CUser::GetList(($by="id"), ($order=""), array("ID"=>implode("|", $arID['U'])), array("FIELDS"=>array('ID', 'EMAIL', 'LOGIN', 'SECOND_NAME', 'LAST_NAME', 'NAME')));
			while($arUser = $res->Fetch())
				$arResult["U".$arUser["ID"]] = array("provider"=>GetMessage("authprov_name_out_user"), "name"=>CUser::FormatName(CSite::GetNameFormat(false), $arUser, true, false));
		}
		return !empty($arResult)? $arResult: false;
	}

	public static function GetProviders()
	{
		return array(
			array(
				"ID" => "intranet",
				"NAME" => GetMessage("authprov_name"),
				"PROVIDER_NAME" => "",
				"SORT" => 300,
				"CLASS" => "CIntranetAuthProvider",
			),
		);
	}

	private static function InEmployeeDrawStructure($arStructure, $arSections, $key)
	{
		$bOpen = $key == 0? true: false;
		$bHideItem = $key == 0? true: false;
		$arItems = Array();
		foreach ($arStructure[$key] as $ID)
		{
			$arRes = $arSections[$ID];

			$arItem = Array(
				'TYPE' => 'category',
				'ID' => $arRes['ID'],
				'NAME' => $arRes['NAME'],
				'OPEN' => $ID != 'extranet'? $bOpen: false,
				'HIDE_ITEM' => $bHideItem,
				'CHECKBOX' => array(
					"D#ID#" => GetMessage("authprov_check_d"),
					"DR#ID#" => GetMessage("authprov_check_dr"),
				),
			);
			if (is_array($arStructure[$ID]))
			{
				$arItem['CHILD'] = self::InEmployeeDrawStructure($arStructure, $arSections, $ID);
			}
			$arItems[] = $arItem;
		}


		return $arItems;
	}

	public static function OnAfterUserUpdate(&$arFields)
	{
		if(array_key_exists("UF_DEPARTMENT", $arFields))
		{
			$provider = new CIntranetAuthProvider();
			$provider->DeleteByUser($arFields["ID"]);
		}
	}

	public static function OnBeforeIBlockSectionUpdate(&$arFields)
	{
		if(COption::GetOptionString('intranet', 'iblock_structure', '') == $arFields['IBLOCK_ID'])
		{
			if(isset($arFields["IBLOCK_SECTION_ID"]) || isset($arFields["ACTIVE"]))
			{
				$res = CIBlockSection::GetByID($arFields['ID']);
				if($arSect = $res->Fetch())
				{
					if(
						isset($arFields["IBLOCK_SECTION_ID"]) && $arSect["IBLOCK_SECTION_ID"] <> intval($arFields["IBLOCK_SECTION_ID"])
						|| isset($arFields["ACTIVE"]) && $arSect["ACTIVE"] <> $arFields["ACTIVE"]
					)
					{
						$provider = new CIntranetAuthProvider();
						$provider->DeleteAll();
					}
				}
			}
		}
		return true;
	}

	public static function OnAfterIBlockSectionDelete($arFields)
	{
		if(COption::GetOptionString('intranet', 'iblock_structure', '') == $arFields['IBLOCK_ID'])
		{
			$provider = new CIntranetAuthProvider();
			$provider->DeleteAll();
		}
	}
}
