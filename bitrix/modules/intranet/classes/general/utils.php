<?
class CIntranetUtils
{
	private static $SECTIONS_SETTINGS_CACHE = null;

	public static function GetUserDepartments($USER_ID)
	{
		static $cache = array();
		$USER_ID = intval($USER_ID);
		if (!isset($cache[$USER_ID]))
		{
			$dbRes = CUser::GetList(
				$by='ID', $order='ASC',
				array('ID' => $USER_ID),
				array('SELECT' => array('UF_DEPARTMENT'), 'FIELDS' => array('ID'))
			);
			$arRes = $dbRes->Fetch();
			if ($arRes)
			{
				$cache[$USER_ID] = $arRes['UF_DEPARTMENT'];
			}
		}

		return $cache[$USER_ID];
	}

	function GetIBlockSectionChildren($arSections)
	{
		if (!is_array($arSections))
			$arSections = array($arSections);

		$dbRes = CIBlockSection::GetList(array('LEFT_MARGIN' => 'asc'), array('ID' => $arSections));

		$arChildren = array();
		while ($arSection = $dbRes->Fetch())
		{
			if ($arSection['RIGHT_MARGIN']-$arSection['LEFT_MARGIN'] > 1 && !in_array($arSection['ID'], $arChildren))
			{
				$dbChildren = CIBlockSection::GetList(
					array('id' => 'asc'),
					array(
						'IBLOCK_ID' => $arSection['IBLOCK_ID'],
						'ACTIVE' => 'Y',
						'>LEFT_BORDER' => $arSection['LEFT_MARGIN'],
						'<RIGHT_BORDER'=>$arSection['RIGHT_MARGIN']
					)
				);

				while ($arChild = $dbChildren->Fetch())
				{
					$arChildren[] = $arChild['ID'];
				}
			}
		}

		return array_unique(array_merge($arSections, $arChildren));
	}

	function GetIBlockTopSection($SECTION_ID)
	{
		if (is_array($SECTION_ID)) $SECTION_ID = $SECTION_ID[0];
		$dbRes = CIBlockSection::GetNavChain(0, $SECTION_ID);

		$arSection = $dbRes->Fetch(); // hack to check "virtual" root insted of a real one
		$arSection = $dbRes->Fetch();
		if ($arSection)
			return $arSection['ID'];
		else
			return $SECTION_ID;
	}

	function GetDepartmentsData($arDepartments)
	{
		global $INTR_DEPARTMENTS_CACHE, $INTR_DEPARTMENTS_CACHE_VALUE;

		$arDep = array();

		if (!is_array($arDepartments))
			return false;

		if (!is_array($INTR_DEPARTMENTS_CACHE))
			$INTR_DEPARTMENTS_CACHE = array();
		if (!is_array($INTR_DEPARTMENTS_CACHE_VALUE))
			$INTR_DEPARTMENTS_CACHE_VALUE = array();

		$arNewDep = array_diff($arDepartments, $INTR_DEPARTMENTS_CACHE);

		if (count($arNewDep) > 0)
		{
			$dbRes = CIBlockSection::GetList(array('SORT' => 'ASC'), array('ID' => $arNewDep));
			while ($arSect = $dbRes->Fetch())
			{
				$arParams['IBLOCK_ID'][] = $arSect['IBLOCK_ID'];
				$INTR_DEPARTMENTS_CACHE[] = $arSect['ID'];
				$INTR_DEPARTMENTS_CACHE_VALUE[$arSect['ID']] = $arSect['NAME'];
			}
		}

		foreach ($arDepartments as $key => $sect)
		{
			$arDep[$sect] = $INTR_DEPARTMENTS_CACHE_VALUE[$sect];
		}

		return $arDep;
	}

	function IsUserAbsent($USER_ID, $CALENDAR_IBLOCK_ID = null)
	{
		global $CACHE_ABSENCE;

		if (null === $CACHE_ABSENCE)
		{
			$cache_ttl = (24-date('G')) * 3600;
			$cache_uid = 'intranet_absence';
			$cache_dir = '/'.SITE_ID.'/intranet/absence';

			$obCache = new CPHPCache();
			if ($obCache->InitCache($cache_ttl, 'intranet_absence', $cache_dir))
			{
				$arAbsence = $obCache->GetVars();
			}
			else
			{
				if (null == $CALENDAR_IBLOCK_ID)
					$CALENDAR_IBLOCK_ID = COption::GetOptionInt('intranet', 'iblock_calendar', null);

				$dt = ConvertTimeStamp(false, 'SHORT');
				$arAbsence = CIntranetUtils::GetAbsenceData(
					array(
						'CALENDAR_IBLOCK_ID' => $CALENDAR_IBLOCK_ID,
						'DATE_START' => $dt,
						'DATE_FINISH' => $dt,
						'PER_USER' => true,
					)
				);

				$obCache->StartDataCache();
				$obCache->EndDataCache($arAbsence);
			}

			$CACHE_ABSENCE = is_array($arAbsence) ? $arAbsence : array();
		}
		else
		{
			$arAbsence = $CACHE_ABSENCE;
		}

		if (is_array($arAbsence[$USER_ID]))
		{
			$ts = time();
			foreach($arAbsence[$USER_ID] as $arEntry)
			{
				$ts_start = MakeTimeStamp($arEntry['DATE_FROM'], FORMAT_DATETIME);
				if ($ts_start < $ts)
				{
					$ts_finish = MakeTimeStamp($arEntry['DATE_TO'], FORMAT_DATETIME);

					if ($ts_finish > $ts)
						return true;

					if (($ts_start+date('Z')) % 86400 == 0 && $ts_start == $ts_finish)
						return true;
				}
			}
		}

		return false;
	}

	function IsUserHonoured($USER_ID)
	{
		global $CACHE_HONOUR, $CACHE_MANAGER;

		if (!is_array($CACHE_HONOUR))
		{
			$cache_ttl = (24-date('G')) * 3600;
			$cache_dir = '/'.SITE_ID.'/intranet/honour';

			$obCache = new CPHPCache();
			if ($obCache->InitCache($cache_ttl, 'intranet_honour', $cache_dir))
			{
				$CACHE_HONOUR = $obCache->GetVars();
			}
			else
			{
				$CACHE_HONOUR = array();
				$blockId = intval(COption::GetOptionInt('intranet', 'iblock_honour'));
				$arFilter = array(
					"IBLOCK_ID" => $blockId,
					"ACTIVE_DATE" => 'Y',
				);

				if ($arFilter['IBLOCK_ID'] <= 0)
				{
					return false;
				}

				$dbRes = CIBlockElement::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'IBLOCK_ID', 'PROPERTY_USER'));
				while ($arRes = $dbRes->Fetch())
				{
					$CACHE_HONOUR[] = $arRes;
				}

				$obCache->StartDataCache();
				$CACHE_MANAGER->StartTagCache($cache_dir);
				$CACHE_MANAGER->RegisterTag('iblock_id_' . $blockId);
				$CACHE_MANAGER->EndTagCache();
				$obCache->EndDataCache($CACHE_HONOUR);
			}
		}

		foreach ($CACHE_HONOUR as $arRes)
		{
			if ($arRes['PROPERTY_USER_VALUE'] == $USER_ID)
				return true;
		}

		return false;
	}

	function IsToday($date)
	{
		if ($date && ($arDate = ParseDateTime($date, CSite::GetDateFormat('SHORT'))))
		{
			if (isset($arDate["M"]))
			{
				if (is_numeric($arDate["M"]))
				{
					$arDate["MM"] = intval($arDate["M"]);
				}
				else
				{
					$arDate["MM"] = GetNumMonth($arDate["M"], true);
					if (!$arDate["MM"])
						$arDate["MM"] = intval(date('m', strtotime($arDate["M"])));
				}
			}
			elseif (isset($arDate["MMMM"]))
			{
				if (is_numeric($arDate["MMMM"]))
				{
					$arDate["MM"] = intval($arDate["MMMM"]);
				}
				else
				{
					$arDate["MM"] = GetNumMonth($arDate["MMMM"]);
					if (!$arDate["MM"])
						$arDate["MM"] = intval(date('m', strtotime($arDate["MMMM"])));
				}
			}
			return (intval($arDate['MM']) == date('n')) && (intval($arDate['DD']) == date('j'));
		}
		else
		{
			return false;
		}
	}

	function IsDateTime($ts)
	{
		return (($ts + date('Z', $ts)) % 86400 != 0);
	}

	function IsOnline($last_date, $interval = 120)
	{
		$ts = $last_date ? MakeTimeStamp($last_date, 'YYYY-MM-DD HH:MI:SS') : 0;
		if ($ts)
			return time() - $ts < $interval;
		else
			return false;
	}

	function InitImage($imageID, $imageWidth, $imageHeight = 0, $type = BX_RESIZE_IMAGE_PROPORTIONAL)
	{
		$imageFile = false;
		$imageImg = "";

		if(($imageWidth = intval($imageWidth)) <= 0) $imageWidth = 100;
		if(($imageHeight = intval($imageHeight)) <= 0) $imageHeight = $imageWidth;

		$imageID = intval($imageID);

		if($imageID > 0)
		{
			$imageFile = CFile::GetFileArray($imageID);
			if ($imageFile !== false)
			{
				$arFileTmp = CFile::ResizeImageGet(
					$imageFile,
					array("width" => $imageWidth, "height" => $imageHeight),
					$type,
					false
				);
				$imageImg = CFile::ShowImage($arFileTmp["src"], $imageWidth, $imageHeight, "border=0", "");
			}
		}

		return array("FILE" => $imageFile, "CACHE" => $arFileTmp, "IMG" => $imageImg);
	}

	function __absence_sort($a, $b)
	{
		if ($a['DATE_ACTIVE_FROM_TS'] == $b['DATE_ACTIVE_FROM_TS'])
			return 0;

		$check1 = $check2 = 0;

		if (date('Y-m-d', $a['DATE_ACTIVE_FROM_TS']) == date('Y-m-d', $a['DATE_ACTIVE_TO_TS']))
		{
			if (0!=($a['DATE_ACTIVE_FROM_TS']+date('Z'))%86400)
				$check1++;
		}
		if (date('Y-m-d', $b['DATE_ACTIVE_FROM_TS']) == date('Y-m-d', $b['DATE_ACTIVE_TO_TS']))
		{
			if (0!=($b['DATE_ACTIVE_FROM_TS']+date('Z'))%86400)
				$check2++;
		}

		if ($check1 != $check2)
			return ($check1 < $check2) ? 1 : -1;
		elseif ($check1 > 0)
			return ($a['DATE_ACTIVE_FROM_TS'] > $b['DATE_ACTIVE_FROM_TS']) ? 1 : -1;
		else
			return ($a['DATE_ACTIVE_FROM_TS'] < $b['DATE_ACTIVE_FROM_TS']) ? 1 : -1;


		// if ($a['DATE_TO'] == $b['DATE_TO'])
			// return 0;
		// else
			// return (MakeTimeStamp($a['DATE_TO']) > MakeTimeStamp($b['DATE_TO'])) ? 1 : -1;
	}

	/*
	$arParams = array(
		'CALENDAR_IBLOCK_ID' => ID of calendar iblock. Def. - false, no calendar entries will be selected
		'ABSENCE_IBLOCK_ID' => ID of absence iblock. Def. - ID from intranet module options
		'DATE_START' => starting datetime in current format. Def. - current month start
		'DATE_FINISH' => endind datetime in current format. Def. - current month finish
		'USERS' => array of user IDs to get; false means no users filter. Def. - all users (false)
		'PER_USER' => {true|false} - whether to return data as array(USER_ID=>array(USER_ENTRIES)) or simple list. Def. - true
	),
	$MODE may be one of the following: BX_INTRANET_ABSENCE_ALL, BX_INTRANET_ABSENCE_PERSONAL, BX_INTRANET_ABSENCE_HR (bit-masks)
	*/
	function GetAbsenceData($arParams = array(), $MODE = BX_INTRANET_ABSENCE_ALL)
	{
		global $DB;

		$arDefaultParams = array(
			'CALENDAR_IBLOCK_ID' => false,
			'ABSENCE_IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_absence'),
			'DATE_START' => date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), strtotime(date('Y-m-01'))),
			'DATE_FINISH' => date($DB->DateFormatToPHP(CSite::GetDateFormat('FULL')), strtotime('+1 month', strtotime(date('Y-m-01')))),
			'USERS' => false,
			'PER_USER' => true,
		);

		foreach ($arDefaultParams as $key => $value)
		{
			if (!isset($arParams[$key]))
				$arParams[$key] = $value;
		}

		$calendar2 = COption::GetOptionString("intranet", "calendar_2", "N") == "Y";
		$bLoadCalendar = ($arParams['CALENDAR_IBLOCK_ID'] > 0 || $calendar2) && (($MODE & BX_INTRANET_ABSENCE_PERSONAL) > 0);
		$bLoadAbsence = $arParams['ABSENCE_IBLOCK_ID'] > 0;

		$arResult = array();
		$arEntries = array();

		$format = $DB->DateFormatToPHP(CLang::GetDateFormat("FULL"));

		if ($bLoadCalendar)
		{
			$arMethodParams = array(
				'iblockId' => $arParams['CALENDAR_IBLOCK_ID'],
				'arUserIds' => $arParams['USERS'],
				'bList' => true,
			);

			if ($arParams['DATE_START'])
				$arMethodParams['fromLimit'] = date($format, MakeTimeStamp($arParams['DATE_START'], FORMAT_DATE));
			if ($arParams['DATE_FINISH'])
				$arMethodParams['toLimit'] = date($format, MakeTimeStamp($arParams['DATE_FINISH'], FORMAT_DATE) + 86399);

			//echo '<pre>'; print_r($arMethodParams); echo '</pre>';

			if ($calendar2 && CModule::IncludeModule('calendar'))
				$arCalendarEntries = CCalendar::GetAbsentEvents($arMethodParams);
			else
				$arCalendarEntries = CEventCalendar::GetAbsentEvents($arMethodParams);

			if (is_array($arCalendarEntries))
			{
				foreach ($arCalendarEntries as $key => $arEntry)
				{
					$arCalendarEntries[$key]['ENTRY_TYPE'] = BX_INTRANET_ABSENCE_PERSONAL;
				}
				$arEntries = array_merge($arEntries, $arCalendarEntries);
			}

		}

		if ($bLoadAbsence)
		{
			if ($arParams['USERS'] === false || (is_array($arParams['USERS']) && count($arParams['USERS']) > 0))
			{
				$arFilter = array(
					'IBLOCK_ID' => $arParams['ABSENCE_IBLOCK_ID'],
					'ACTIVE' => 'Y',
					//'PROPERTY_USER_ACTIVE' => 'Y',
				);

				if ($arParams['DATE_START'])
					$arFilter['>=DATE_ACTIVE_TO'] = date($format, MakeTimeStamp($arParams['DATE_START'], FORMAT_DATE));
				if ($arParams['DATE_FINISH'])
					$arFilter['<DATE_ACTIVE_FROM'] = date($format, MakeTimeStamp($arParams['DATE_FINISH'], FORMAT_DATE) + 86399);

				if (is_array($arParams['USERS']))
					$arFilter['PROPERTY_USER'] = $arParams['USERS'];

				$dbRes = CIBlockElement::GetList(
					array('DATE_ACTIVE_FROM' => 'ASC', 'DATE_ACTIVE_TO' => 'ASC'),
					$arFilter,
					false,
					false,
					array('ID', 'IBLOCK_ID', 'DATE_ACTIVE_FROM', 'DATE_ACTIVE_TO', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'PROPERTY_USER', 'PROPERTY_FINISH_STATE', 'PROPERTY_STATE', 'PROPERTY_ABSENCE_TYPE')
				);

				while ($arRes = $dbRes->Fetch())
				{
					$arRes['USER_ID'] = $arRes['PROPERTY_USER_VALUE'];
					$arRes['DATE_FROM'] = $arRes['DATE_ACTIVE_FROM'];
					$arRes['DATE_TO'] = $arRes['DATE_ACTIVE_TO'];
					$arRes['ENTRY_TYPE'] = BX_INTRANET_ABSENCE_HR;
					$arEntries[] = $arRes;
				}
			}
		}

		if ($arParams['PER_USER'])
		{
			foreach ($arEntries as $key => $arEntry)
			{
				if (!is_array($arResult[$arEntry['USER_ID']]))
					$arResult[$arEntry['USER_ID']] = array();

				$arResult[$arEntry['USER_ID']][] = $arEntry;
			}
		}
		else
		{
			$arResult = $arEntries;
		}

		return $arResult;
	}

	/* STATUS: deprecated */
	function FormatName($NAME_TEMPLATE, $arUser, $bHTMLSpec = true)
	{
		return CUser::FormatName($NAME_TEMPLATE, $arUser, true, $bHTMLSpec);
	}

	/* STATUS: deprecated */
	function GetDefaultNameTemplates()
	{
		return CComponentUtil::GetDefaultNameTemplates();
	}

	function makeGUID($data)
	{
		if (strlen($data) !== 32) return false;
		else return
			'{'.
				substr($data, 0, 8).'-'.substr($data, 8, 4).'-'.substr($data, 12, 4).'-'.substr($data, 16, 4).'-'.substr($data, 20).
			'}';
	}

	function checkGUID($data)
	{
		$data = str_replace(array('{', '-', '}'), '', $data);
		if (strlen($data) !== 32 || preg_match('/[^a-z0-9]/i', $data)) return false;
		else return $data;
	}

	/*
	$arSectionParams = array(
		'ID' => 'Section ID',
		'XML_ID' => 'Section external ID' [optional], for calendars
		'CODE' => 'Section external ID' [optional], for tasks
		'IBLOCK_ID' => 'Information block id' [optional],
		'NAME' => 'Calendar name' [optional],
		'PREFIX' => 'Calendar prefix',
		'LINK_URL' => 'Calendar URL' (/company/personal/user/666/calendar/),
	)

	if any of parameters 'XML_ID'|'CODE', 'IBLOCK_ID', 'NAME' are absent, they are taken from DB
	XML_ID|CODE must be 32-digit hexadimal number. if none or other, it would be (re-)generated and (re-)set
	*/
	function GetStsSyncURL($arSectionParams, $type = 'calendar', $employees = false)
	{
		if (!is_array($arSectionParams))
			$arSectionParams = array('ID' => intval($arSectionParams));

		//if (!$arSectionParams['ID'])
		//	return false;

		$arAllowedTypes = array('calendar', 'tasks', 'contacts');

		if (!in_array($type, $arAllowedTypes))
			$type = 'calendar';

		if ($type == 'calendar')
		{
			$calendar2 = COption::GetOptionString("intranet", "calendar_2", "N") == "Y" && CModule::IncludeModule("calendar");
			$fld_EXTERNAL_ID = 'XML_ID';

			if ($calendar2) // Module 'Calendar'
			{
				// $arSectionParams = array(
					// 'ID' => int
					// 'XML_ID' => string
					// 'NAME' => string
					// 'PREFIX' => string
					// 'LINK_URL' => string
					// 'TYPE' => string
				// )

				if (strlen($arSectionParams['XML_ID']) !== 32)
				{
					$arSectionParams[$fld_EXTERNAL_ID] = md5($arSectionParams['TYPE'].'_'.$arSectionParams['ID'].'_'.RandString(8));
					// Set XML_ID
					CCalendar::SaveSection(array('arFields' => Array('ID' => $arSectionParams['ID'],'XML_ID' => $arSectionParams[$fld_EXTERNAL_ID]), 'bAffectToDav' => false, 'bCheckPermissions' => false));
				}
			}
			else // Old version calendar on iblocks
			{
				if (!$arSectionParams['IBLOCK_ID'] || !$arSectionParams['NAME'] || !$arSectionParams[$fld_EXTERNAL_ID])
				{
					$dbRes = CIBlockSection::GetByID($arSectionParams['ID']);
					$arSection = $dbRes->Fetch();
					if ($arSection)
					{
						$arSectionParams['IBLOCK_ID'] = $arSection['IBLOCK_ID'];
						$arSectionParams['NAME'] = $arSection['NAME'];
						$arSectionParams[$fld_EXTERNAL_ID] = $arSection[$fld_EXTERNAL_ID];
					}
					else
					{
						return false;
					}
				}

				if (strlen($arSectionParams[$fld_EXTERNAL_ID]) !== 32)
				{
					$arSectionParams[$fld_EXTERNAL_ID] = md5($arSectionParams['IBLOCK_ID'].'_'.$arSectionParams['ID'].'_'.RandString(8));

					$obSect = new CIBlockSection();
					if (!$obSect->Update($arSectionParams['ID'], array($fld_EXTERNAL_ID => $arSectionParams[$fld_EXTERNAL_ID]), false, false))
						return false;
				}
			}

			if (!$arSectionParams['PREFIX'])
			{
				$rsSites = CSite::GetByID(SITE_ID);
				$arSite = $rsSites->Fetch();
				if (strlen($arSite["NAME"]) > 0)
					$arSectionParams['PREFIX'] = $arSite["NAME"];
				else
					$arSectionParams['PREFIX'] = COption::GetOptionString('main', 'site_name', GetMessage('INTR_OUTLOOK_PREFIX_CONTACTS'));
			}

			$GUID = CIntranetUtils::makeGUID($arSectionParams[$fld_EXTERNAL_ID]);
		}
		elseif($type == 'contacts')
		{
			if (!$arSectionParams['LINK_URL'])
			{
				if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
					$arSectionParams['LINK_URL'] = SITE_DIR.'contacts/';
				else
					$arSectionParams['LINK_URL'] = SITE_DIR.'company/';
			}

			if (!$arSectionParams['NAME'])
			{
				if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite() && !$employees)
					$arSectionParams['NAME'] = GetMessage('INTR_OUTLOOK_TITLE_CONTACTS_EXTRANET');
				else
					$arSectionParams['NAME'] = GetMessage('INTR_OUTLOOK_TITLE_CONTACTS');
			}
			if (!$arSectionParams['PREFIX'])
			{
				$rsSites = CSite::GetByID(SITE_ID);
				$arSite = $rsSites->Fetch();

				if (strlen($arSite["NAME"]) > 0)
					$arSectionParams['PREFIX'] = $arSite["NAME"];
				else
					$arSectionParams['PREFIX'] = COption::GetOptionString('main', 'site_name', GetMessage('INTR_OUTLOOK_PREFIX_CONTACTS'));
			}


			$SERVER_NAME = $_SERVER['SERVER_NAME'];
			$GUID_DATA = $SERVER_NAME.'|'.$type;
			if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
			{
				$GUID_DATA .= "|extranet";
				if ($employees)
					$GUID_DATA .= "|employees";
			}

			$GUID = CIntranetUtils::makeGUID(md5($GUID_DATA));
		}
		elseif($type == 'tasks')
		{
			if (!$arSectionParams['LINK_URL'])
			{
				if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
					$arSectionParams['LINK_URL'] = SITE_DIR.'contacts/personal/user/'.$USER->GetID().'/tasks/';
				else
					$arSectionParams['LINK_URL'] = SITE_DIR.'company/personal/user/'.$USER->GetID().'/tasks/';
			}

			if (!$arSectionParams['NAME'])
				$arSectionParams['NAME'] = GetMessage('INTR_OUTLOOK_TITLE_TASKS');


			if (!$arSectionParams['PREFIX'])
			{
				$rsSites = CSite::GetByID(SITE_ID);
				$arSite = $rsSites->Fetch();
				if (strlen($arSite["NAME"]) > 0)
					$arSectionParams['PREFIX'] = $arSite["NAME"];
				else
					$arSectionParams['PREFIX'] = COption::GetOptionString('main', 'site_name', GetMessage('INTR_OUTLOOK_PREFIX_CONTACTS'));
			}

			$SERVER_NAME = $_SERVER['SERVER_NAME'];
			$GUID_DATA = $SERVER_NAME.'|'.$type;

			if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
				$GUID_DATA .= "|extranet";
			$GUID = CIntranetUtils::makeGUID(md5($GUID_DATA));
		}

		if (substr($arSectionParams['LINK_URL'], -9) == 'index.php')
			$arSectionParams['LINK_URL'] = substr($arSectionParams['LINK_URL'], 0, -9);

		if (substr($arSectionParams['LINK_URL'], -4) != '.php' && substr($arSectionParams['LINK_URL'], -1) != '/')
			$arSectionParams['LINK_URL'] .= '/';

		// another dirty hack to avoid some M$ stssync protocol restrictions
		if (substr($arSectionParams['LINK_URL'], -1) != '/')
			$arSectionParams['LINK_URL'] .= '/';

		$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/intranet/outlook.js');

		$type_script = $type;
		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		{
			$type_script .= "_extranet";
			if ($employees)
				$type_script .= "_emp";
		}
		return 'jsOutlookUtils.Sync(\''.$type.'\', \'/bitrix/tools/ws_'.$type_script.'\', \''.$arSectionParams['LINK_URL'].'\', \''.CUtil::JSEscape(htmlspecialcharsbx($arSectionParams['PREFIX'])).'\', \''.CUtil::JSEscape($arSectionParams['NAME']).'\', \''.$GUID.'\')';
	}

	function UpdateOWSVersion($IBLOCK_ID, $ID, $value = null)
	{
		if (!defined('INTR_WS_OUTLOOK_UPDATE'))
		{
			if (null === $value)
			{
				$dbRes = CIBlockElement::GetProperty($IBLOCK_ID, $ID, 'sort', 'asc', array('CODE' => 'VERSION'));
				$arProperty = $dbRes->Fetch();
				if ($arProperty)
				{
					$value = intval($arProperty['VALUE']);
					if (!$value) $value = 1;
					$value++;
				}
			}

			if (null !== $value)
			{
				CIBlockElement::SetPropertyValues($ID, $IBLOCK_ID, $value, 'VERSION');
			}
		}
	}

	protected static function __dept_field_replace($str)
	{
		return preg_replace(
			'/<option([^>]*)>'.GetMessage('MAIN_NO').'<\/option>/i'.BX_UTF_PCRE_MODIFIER,
			'<option\\1>'.GetMessage('MAIN_ALL').'</option>',
			$str
		);
	}

	function ShowDepartmentFilter($arUserField, $bVarsFromForm, $bReturn = false, $ob_callback = array('CIntranetUtils', '__dept_field_replace'))
	{
		ob_start($ob_callback);

		$arUserField['SETTINGS']['ACTIVE_FILTER'] = 'Y';
		$arUserField['SETTINGS']["DEFAULT_VALUE"] = 0;

		$GLOBALS['APPLICATION']->IncludeComponent(
			'bitrix:system.field.edit',
			'iblock_section',
			array(
				"arUserField" => $arUserField,
				'bVarsFromForm' => $bVarsFromForm,
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);

		if ($bReturn)
		{
			$str = ob_get_contents();
			ob_end_flush();
			return $str;
		}

		ob_end_flush();
		return true;
	}

	function GetIBlockByID($ID)
	{
		if (!CModule::IncludeModule("iblock"))
			return false;

		$ID = IntVal($ID);

		$dbIBlock = CIBlock::GetByID($ID);
		$arIBlock = $dbIBlock->GetNext();
		if ($arIBlock)
		{
			$arIBlock["NAME_FORMATTED"] = $arIBlock["NAME"];
			return $arIBlock;
		}
		else
			return false;
	}

	function ShowIBlockByID($arEntityDesc, $strEntityURL, $arParams)
	{
		$url = str_replace("#SITE_DIR#", SITE_DIR, $arEntityDesc["LIST_PAGE_URL"]);
		if (strpos($url, "/") === 0)
			$url = "/".ltrim($url, "/");

		$name = "<a href=\"".$url."\">".$arEntityDesc["NAME"]."</a>";
		return $name;
	}

	public static function GetDeparmentsTree($section_id = 0, $bFlat = false)
	{
		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		if (!$section_id)
		{
			if (!$bFlat)
				return self::$SECTIONS_SETTINGS_CACHE['TREE'];
			else
				return array_keys(self::$SECTIONS_SETTINGS_CACHE['DATA']);
		}

		$arSections = self::$SECTIONS_SETTINGS_CACHE['TREE'][$section_id];

		if (is_array($arSections) && count($arSections) > 0)
		{
			if ($bFlat)
			{
				foreach ($arSections as $subsection_id)
				{
					$arSections = array_merge($arSections, self::GetDeparmentsTree($subsection_id, $bFlat));
				}
			}
			else
			{
				$arSections = array($section_id => $arSections);
				foreach ($arSections[$section_id] as $subsection_id)
				{
					$arSections += self::GetDeparmentsTree($subsection_id, $bFlat);
				}
			}
		}

		return is_array($arSections) ? $arSections : array();
	}

	public static function GetStructure()
	{
		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		return self::$SECTIONS_SETTINGS_CACHE;
	}

	public static function GetDepartmentManagerID($section_id)
	{
		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		return self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['UF_HEAD'];
	}

	public static function GetDepartmentManager($arDepartments, $skipUserId=false, $bRecursive=false)
	{
		if(!is_array($arDepartments) || empty($arDepartments))
			return array();

		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		$arManagers = array();
		$arManagerIDs = array();
		foreach ($arDepartments as $section_id)
		{
			$arSection = self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id];

			if ($arSection['UF_HEAD'] && $arSection['UF_HEAD'] != $skipUserId)
			{
				$arManagerIDs[] = $arSection['UF_HEAD'];
			}
		}

		if(count($arManagerIDs) > 0)
		{
			$dbRes = CUser::GetList($by = 'ID', $sort = 'ASC', array('ID' => implode('|', array_unique($arManagerIDs))));
			while($arUser = $dbRes->GetNext())
			{
				$arManagers[$arUser['ID']] = $arUser;
			}
		}

		foreach ($arDepartments as $section_id)
		{
			$arSection = self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id];

			$bFound = $arSection['UF_HEAD']
				&& $arSection['UF_HEAD'] != $skipUserId
				&& array_key_exists($arSection['UF_HEAD'], $arManagers);

			if (!$bFound && $bRecursive && $arSection['IBLOCK_SECTION_ID'])
			{
				$ar = CIntranetUtils::GetDepartmentManager(array($arSection['IBLOCK_SECTION_ID']), $skipUserId, $bRecursive);
				$arManagers = $arManagers + $ar;
			}
		}

		return $arManagers;
	}

	public static function GetEmployeesCountForSorting($section_id = 0, $amount = 0, $arAccessUsers = false)
	{
		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		if (is_array($arAccessUsers))
		{
			if (count($arAccessUsers) <= 0)
				return 0;
			if (in_array('*', $arAccessUsers))
				$arAccessUsers = false;
		}

		$cnt = 0;

		$arSection = self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id];

		if (is_array($arSection['EMPLOYEES']))
		{
			if (!is_array($arAccessUsers))
				$cnt = count($arSection['EMPLOYEES']);
			else
				$cnt += count(array_intersect($arSection['EMPLOYEES'], $arAccessUsers));
		}

		if ($arSection['UF_HEAD'] > 0 && !in_array($arSection['UF_HEAD'], $arSection['EMPLOYEES'])
				&& (!$arAccessUsers || in_array($arSection['UF_HEAD'], $arAccessUsers)))
			$cnt++;

		if (self::$SECTIONS_SETTINGS_CACHE['TREE'][$section_id])
		{
			foreach (self::$SECTIONS_SETTINGS_CACHE['TREE'][$section_id] as $dpt)
				$cnt += self::GetEmployeesCountForSorting ($dpt, 0, $arAccessUsers);
		}

		return $amount > 0 ? intval($cnt/$amount)+($cnt%$amount>0?1:0) : $cnt;
	}

	public static function GetEmployeesForSorting($page = 1, $amount = 50, $section_id = 0, $arAccessUsers = false)
	{
		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		if (is_array($arAccessUsers))
		{
			if (count($arAccessUsers) <= 0)
				return array();
			if (in_array('*', $arAccessUsers))
				$arAccessUsers = false;
		}

		$start = ($page-1) * $amount;
		$arUserIDs = array();

		self::_GetEmployeesForSorting($section_id, $amount, $start, $arUserIDs, $arAccessUsers);

		return $arUserIDs;
	}

	private static function _GetEmployeesForSorting($section_id, &$amount, &$start, &$arUserIDs, $arAccessUsers)
	{
		if (self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id])
		{
			if (self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['UF_HEAD'])
			{
				if (!$arAccessUsers || in_array(self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['UF_HEAD'], $arAccessUsers))
				{
					if ($start > 0)
					{
						$start--;
					}
					else if ($amount > 0)
					{
						$arUserIDs[$section_id][] = self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['UF_HEAD'];
						$amount--;
					}
					else
					{
						return false;
					}
				}
			}

			if (self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['EMPLOYEES'])
			{
				foreach (self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['EMPLOYEES'] as $ID)
				{
					if ($ID == self::$SECTIONS_SETTINGS_CACHE['DATA'][$section_id]['UF_HEAD'])
						continue;

					if ($arAccessUsers && !in_array($ID, $arAccessUsers))
						continue;

					if ($start > 0)
					{
						$start--;
					}
					else if ($amount > 0)
					{
						$arUserIDs[$section_id][] = $ID;
						$amount--;
					}
					else
					{
						return false;
					}
				}
			}
		}

		if (self::$SECTIONS_SETTINGS_CACHE['TREE'][$section_id])
		{
			foreach (self::$SECTIONS_SETTINGS_CACHE['TREE'][$section_id] as $dpt)
			{
				if (!self::_GetEmployeesForSorting($dpt, $amount, $start, $arUserIDs, $arAccessUsers))
					return false;
			}
		}
		return true;
	}

	private static function _GetDeparmentsTree()
	{
		global $USER_FIELD_MANAGER, $CACHE_MANAGER;

		self::$SECTIONS_SETTINGS_CACHE = array(
			'TREE' => array(),
			'DATA' => array(),
		);

		$ibDept = COption::GetOptionInt('intranet', 'iblock_structure', false);
		if ($ibDept <= 0)
			return;

		$cache_dir = '/intranet/structure';
		$cache_id = 'intranet|structure2|'.$ibDept;

		$obCache = new CPHPCache();

		if ($obCache->InitCache(30*86400, $cache_id, $cache_dir))
		{
			self::$SECTIONS_SETTINGS_CACHE = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();

			$CACHE_MANAGER->StartTagCache($cache_dir);

			$CACHE_MANAGER->RegisterTag("iblock_id_".$ibDept);
			$CACHE_MANAGER->RegisterTag("intranet_users");

			$arAllFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_'.$ibDept.'_SECTION');

			$arSettings = array('UF_HEAD');
			$dbRes = CIBlockSection::GetList(
				array("LEFT_MARGIN"=>"ASC"),
				array('IBLOCK_ID' => $ibDept, 'ACTIVE' => 'Y'),
				false,
				array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'UF_HEAD', 'SECTION_PAGE_URL', 'DEPTH_LEVEL',)
			);

			$arSectionsMap = array();
			while ($arRes = $dbRes->Fetch())
			{
				$CACHE_MANAGER->RegisterTag('intranet_department_'.$arRes['ID']);

				if (!$arRes['IBLOCK_SECTION_ID'])
					$arRes['IBLOCK_SECTION_ID'] = 0;

				if (!self::$SECTIONS_SETTINGS_CACHE['TREE'][$arRes['IBLOCK_SECTION_ID']])
					self::$SECTIONS_SETTINGS_CACHE['TREE'][$arRes['IBLOCK_SECTION_ID']] = array();

				self::$SECTIONS_SETTINGS_CACHE['TREE'][$arRes['IBLOCK_SECTION_ID']][] = $arRes['ID'];
				self::$SECTIONS_SETTINGS_CACHE['DATA'][$arRes['ID']] = array(
					'ID' => $arRes['ID'],
					'NAME' => $arRes['NAME'],
					'IBLOCK_SECTION_ID' => $arRes['IBLOCK_SECTION_ID'],
					'UF_HEAD' => $arRes['UF_HEAD'],
					'SECTION_PAGE_URL' => $arRes['SECTION_PAGE_URL'],
					'DEPTH_LEVEL' => $arRes['DEPTH_LEVEL'],
					'EMPLOYEES' => array()
				);
			}

			$dbRes = CUser::GetList(
				$by = 'ID', $order = 'ASC',
				array('ACTIVE' => 'Y', '!UF_DEPARTMENT' => false),
				array('SELECT' => array('ID', 'UF_DEPARTMENT'))
			);
			while ($arRes = $dbRes->Fetch())
			{
				if(!isset($arRes['UF_DEPARTMENT']))
				{
					continue;
				}

				foreach ($arRes['UF_DEPARTMENT'] as $dpt)
				{
					self::$SECTIONS_SETTINGS_CACHE['DATA'][$dpt]['EMPLOYEES'][] = $arRes['ID'];
				}
			}

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache(self::$SECTIONS_SETTINGS_CACHE);
		}
	}

	function GetDepartmentColleagues($USER_ID = null, $bRecursive = false, $bSkipSelf = false)
	{
		global $USER;

		if (!$USER_ID)
			$USER_ID = $USER->GetID();

		$arUsers = array();

		$dbRes = CUser::GetList($by='ID', $order='ASC', array('ID' => $USER_ID), array('SELECT' => array('UF_DEPARTMENT')));
		if (($arRes = $dbRes->Fetch()) && is_array($arRes['UF_DEPARTMENT']) && count($arRes['UF_DEPARTMENT']) > 0)
		{
			return CIntranetUtils::GetDepartmentEmployees($arRes['UF_DEPARTMENT'], $bRecursive, $bSkipSelf);
		}

		return new CDBResult();
	}

	function GetDepartmentEmployees($arDepartments, $bRecursive = false, $bSkipSelf = false)
	{
		if ($bRecursive)
			$arDepartments = CIntranetUtils::GetIBlockSectionChildren($arDepartments);

		$arFilter = array(
			'UF_DEPARTMENT' => $arDepartments,
			'ACTIVE' => 'Y'
		);

		if ($bSkipSelf)
		{
			$arFilter['!ID'] = $GLOBALS['USER']->GetID();
		}

		$dbRes = CUser::GetList($by='ID', $order='ASC', $arFilter, array('SELECT' => array('UF_*')));
		return $dbRes;
	}

	function GetSubordinateDepartments($USER_ID = null, $bRecursive = false)
	{
		global $USER;

		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetDeparmentsTree();

		if (!$USER_ID)
			$USER_ID = $USER->GetID();

		$arSections = array();
		foreach (self::$SECTIONS_SETTINGS_CACHE['DATA'] as $arSection)
		{
			if ($arSection['UF_HEAD'] == $USER_ID)
			{
				$arSections[] = $arSection['ID'];
			}
		}

		if ($bRecursive && count($arSections) > 0)
		{
			foreach ($arSections as $section_id)
			{
				$arSections  = array_merge($arSections, self::GetDeparmentsTree($section_id, true));
			}
		}

		return $arSections;
	}

	function GetSubordinateDepartmentsList($USER_ID)
	{
		return CIBlockSection::GetList(
			array('SORT' => 'ASC', 'NAME' => 'ASC'),
			array('IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure', 0), 'UF_HEAD' => $USER_ID, 'ACTIVE' => 'Y'),
			false,
			array('ID', 'NAME', 'UF_HEAD'));
	}

	function GetSubordinateEmployees($USER_ID = null, $bRecursive = false)
	{
		$arDepartments = CIntranetUtils::GetSubordinateDepartments($USER_ID, $bRecursive);
		return CIntranetUtils::GetDepartmentEmployees($arDepartments, false, true);
	}

	function GetSubordinateDepartmentsOld($USER_ID = null, $bRecursive = false)
	{
		global $USER;

		if (!$USER_ID)
			$USER_ID = $USER->GetID();

		$arDpts = array();
		$dbRes = CIntranetUtils::GetSubordinateDepartmentsList($USER_ID);
		while ($arRes = $dbRes->Fetch())
		{
			$arDpts[] = $arRes['ID'];
		}

		if ($bRecursive && count($arDpts) > 0)
		{
			$arDpts = CIntranetUtils::GetIBlockSectionChildren($arDpts);
		}

		return $arDpts;
	}


	function GetDepartmentManagerOld($arDepartments, $skipUserId=false, $bRecursive=false)
	{
		if(!is_array($arDepartments) || empty($arDepartments))
			return array();

		$arManagers = array();
		$dbSections = CIBlockSection::GetList(array('SORT' => 'ASC'), array('ID' =>$arDepartments, 'IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure', 0)), false, array('ID', 'UF_HEAD', 'IBLOCK_SECTION_ID'));
		while($arSection = $dbSections->Fetch())
		{
			$bFound = false;
			if($arSection["UF_HEAD"] > 0)
			{
				$dbUser = CUser::GetByID($arSection["UF_HEAD"]);
				$arUser = $dbUser->GetNext();
				if ($arUser)
				{
					if($arUser["ID"] <> $skipUserId)
					{
						$arManagers[$arUser["ID"]] = $arUser;
						$bFound = true;
					}
				}
			}
			if(!$bFound && $bRecursive && $arSection['IBLOCK_SECTION_ID'] > 0)
			{
				$ar = CIntranetUtils::GetDepartmentManagerOld(array($arSection['IBLOCK_SECTION_ID']), $skipUserId, $bRecursive);
				$arManagers = $arManagers + $ar;
			}
		}
		return $arManagers;
	}

	/**
	 * @param $fields
	 * @param $params
	 * @param $siteId
	 * @return string|null
	 */
	public static function createAvatar($fields, $params = array(), $siteId = SITE_ID)
	{
		if(!isset($params['AVATAR_SIZE']))
		{
			$params['AVATAR_SIZE'] = 30;
		}

		if (CModule::IncludeModule('socialnetwork'))
		{
			return CSocNetLogTools::FormatEvent_CreateAvatar($fields, $params, '', $siteId);
		}

		static $cachedAvatars = array();
		if (intval($fields['PERSONAL_PHOTO']) > 0)
		{
			if (empty($cachedAvatars[$params['AVATAR_SIZE']][$fields['PERSONAL_PHOTO']]))
			{
				$imageFile = CFile::getFileArray($fields['PERSONAL_PHOTO']);
				if ($imageFile !== false)
				{
					$file = CFile::resizeImageGet($imageFile, array(
																		"width"  => $params['AVATAR_SIZE'],
																		"height" => $params['AVATAR_SIZE']
																	), BX_RESIZE_IMAGE_EXACT, false);

					$avatarPath = $file['src'];
					$cachedAvatars[$params['AVATAR_SIZE']][$fields['PERSONAL_PHOTO']] = $avatarPath;
				}
			}
		}

		return empty($cachedAvatars[$params['AVATAR_SIZE']][$fields['PERSONAL_PHOTO']])? null : $cachedAvatars[$params['AVATAR_SIZE']][$fields['PERSONAL_PHOTO']];
	}
}
?>