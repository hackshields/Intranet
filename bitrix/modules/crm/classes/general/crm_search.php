<?php

IncludeModuleLangFile(__FILE__);

class CCrmSearch
{
	static $bReIndex = false;
	static $oCallback = null;
	static $callback_method = '';
	static $arMess = array();

	static public function UpdateSearch($arFilter, $ENTITY_TYPE, $bOverWrite = false)
	{
		if (!CModule::IncludeModule('search'))
			return;

		switch ($ENTITY_TYPE)
		{
			case 'CONTACT': $obRes = CCrmContact::GetList(array('ID' => 'ASC'), $arFilter, array(), 1000); $sTitleID = 'FULL_NAME'; break;
			case 'DEAL': $obRes = CCrmDeal::GetList(array('ID' => 'ASC'), $arFilter, array(), 1000); $sTitleID = 'TITLE'; break;
			case 'COMPANY': $obRes = CCrmCompany::GetList(array('ID' => 'ASC'), $arFilter, array(), 1000); $sTitleID = 'TITLE'; break;
			default:
			case 'LEAD': $obRes = CCrmLead::GetList(array('ID' => 'ASC'), $arFilter, array(), 1000); $sTitleID = 'TITLE'; $ENTITY_TYPE = 'LEAD'; break;
		}

		if (!isset(self::$arMess[$ENTITY_TYPE]))
			self::$arMess[$ENTITY_TYPE] = __IncludeLang($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/components/bitrix/crm.'.strtolower($ENTITY_TYPE).'.show/lang/'.LANGUAGE_ID.'/component.php', true);

		$arAllResult = array();
		$i = 0;
		while (($arRow = $obRes->Fetch()) !== false)
		{
			$arResult = self::_buildEntityCard($arRow, $sTitleID, $ENTITY_TYPE);
			if (self::$bReIndex)
			{
				if (self::$oCallback)
				{
					$res = call_user_func(array(self::$oCallback, self::$callback_method), $arResult);
					if(!$res)
						return $ENTITY_TYPE.'.'.$arRow['ID'];
				}
			}
			else
			{
				CSearch::Index(
					'crm',
					$ENTITY_TYPE.'.'.$arRow['ID'],
					$arResult,
					$bOverWrite
				);
			}

			$arAllResult[] = $arResult;
			$i++;
		}

		if (!self::$bReIndex && !empty($arFilter['ID']) && $i = 0)
			CSearch::DeleteIndex('crm', (int) $arFilter['ID']);

		return $arAllResult;
	}

	static protected function _buildEntityCard($arEntity, $sTitle, $ENTITY_TYPE)
	{
		static $arEntityGroup = array();
		static $arStatuses = array();
		static $arSite = array();

		$sBody = $arEntity[$sTitle]."\n";
		$arField2status = array(
			'STATUS_ID' => 'STATUS',
			'SOURCE_ID' => 'SOURCE',
			'CURRENCY_ID' => 'CURRENCY',
			'PRODUCT_ID' => 'PRODUCT',
			'TYPE_ID' => 'CONTACT_TYPE',
			'STAGE_ID' => 'DEAL_STAGE',
			'EVENT_ID' => 'EVENT_TYPE',
			'COMPANY_TYPE' => 'COMPANY_TYPE',
			'EMPLOYEES' => 'EMPLOYEES',
			'INDUSTRY' => 'INDUSTRY'
		);
		foreach ($arEntity as $_k => $_v)
		{
			if ($_k == $sTitle || strpos($_k, '_BY_') !== false || strpos($_k, 'DATE_') === 0 || strpos($_k, 'UF_') === 0)
				continue ;

			if (is_array($_v))
				continue ;

			$_v = trim($_v);

			if (isset($arField2status[$_k]))
			{
				if (!isset($arStatuses[$_k]))
					$arStatuses[$_k] = CCrmStatus::GetStatusList($arField2status[$_k]);
				$_v = $arStatuses[$_k][$_v];
			}

			if (!empty($_v) && !is_numeric($_v) && $_v != 'N' && $_v != 'Y')
				$sBody .= self::$arMess[$ENTITY_TYPE]['CRM_FIELD_'.$_k].": $_v\n";
		}

		$sDetailURL = CComponentEngine::MakePathFromTemplate(COption::GetOptionString('crm', 'path_to_'.strtolower($ENTITY_TYPE).'_show'),
			array(
				strtolower($ENTITY_TYPE).'_id' => $arEntity['ID']
			)
		);

		$_arAttr = CCrmPerms::GetEntityAttr($ENTITY_TYPE, $arEntity['ID']);

		if (empty($arSite))
		{
			$rsSite = CSite::GetList(($by="sort"),($order="asc"));
			while ($_arSite = $rsSite->Fetch())
				$arSite[] = $_arSite['ID'];
		}

		$arAttr = array();
		$sattr_d = '';
		$sattr_s = '';
		$sattr_u = '';
		$sattr_o = '';
		$sattr = '';
		$arAttr = array();
		if (!isset($_arAttr[$arEntity['ID']]))
			$_arAttr[$arEntity['ID']] = array();

		$arAttr[] = $ENTITY_TYPE; // for perm X
		foreach ($_arAttr[$arEntity['ID']] as $_s)
		{
			if ($_s[0] == 'U')
				$sattr_u = $_s;
			else if ($_s[0] == 'D')
				$sattr_d = $_s;
			else if ($_s[0] == 'S')
				$sattr_s = $_s;
			else if ($_s[0] == 'O')
				$sattr_o = $_s;
			$arAttr[] = $ENTITY_TYPE.'_'.$_s;
		}
		$sattr = $ENTITY_TYPE.'_'.$sattr_u;
		if (!empty($sattr_d))
		{
			$sattr .= '_'.$sattr_d;
			$arAttr[] = $sattr;
		}
		if (!empty($sattr_s))
		{
			$sattr2 = $sattr.'_'.$sattr_s;
			$arAttr[] = $sattr2;
			$arAttr[] = $ENTITY_TYPE.'_'.$sattr_s;  // for perm X in status
		}
		if (!empty($sattr_o))
		{
			$sattr  .= '_'.$sattr_o;
			$sattr3 = $sattr2.'_'.$sattr_o;
			$arAttr[] = $sattr3;
			$arAttr[] = $sattr;
		}

		$arSitePath = array();
		foreach ($arSite as $sSite)
			$arSitePath[$sSite] = $sDetailURL;

		$arResult = Array(
			'LAST_MODIFIED' => $arEntity['DATE_MODIFY'],
			'DATE_FROM' => $arEntity['DATE_CREATE'],
			'TITLE' => GetMessage('CRM_'.$ENTITY_TYPE).': '.$arEntity[$sTitle],
			'PARAM1' => $ENTITY_TYPE,
			'PARAM2' => $arEntity['ID'],
			'SITE_ID' => $arSitePath,
			'PERMISSIONS' => $arAttr,
			'BODY' => $sBody,
			'TAGS' => 'crm,'.strtolower($ENTITY_TYPE).','.GetMessage('CRM_'.$ENTITY_TYPE)
		);

		if (self::$bReIndex)
			$arResult['ID'] = $arEntity['ID'];

		return $arResult;
	}

	static public function OnSearchReindex($NS = Array(), $oCallback = NULL, $callback_method = '')
	{
		$arFilter = array();
		$ENTITY_TYPE = 'LEAD';
		if (isset($NS['ID']) && strlen($NS['ID']) > 0 && preg_match('/^[A-Z]+\.\d+$/'.BX_UTF_PCRE_MODIFIER, $NS['ID']))
		{
			$arTemp = explode('.', $NS['ID']);
			$ENTITY_TYPE = $arTemp[0];
			$arFilter['>=ID'] = (int) $arTemp[1];
		}

		self::$oCallback = $oCallback;
		self::$callback_method = $callback_method;
		self::$bReIndex = true;

		$arAllResult = array();
		if ($ENTITY_TYPE == 'LEAD')
		{
			$arResult = self::UpdateSearch($arFilter, $ENTITY_TYPE);
			if (!is_array($arResult))
				return $arResult;
			else
			{
				$ENTITY_TYPE = 'CONTACT';
				$arAllResult = array_merge($arAllResult, $arResult);
			}
		}
		if ($ENTITY_TYPE == 'CONTACT')
		{
			$arResult = self::UpdateSearch($arFilter, $ENTITY_TYPE);
			if (!is_array($arResult))
				return $arResult;
			else
			{
				$ENTITY_TYPE = 'COMPANY';
				$arAllResult = array_merge($arAllResult, $arResult);
			}
		}
		if ($ENTITY_TYPE == 'COMPANY')
		{
			$arResult = self::UpdateSearch($arFilter, $ENTITY_TYPE);
			if (!is_array($arResult))
				return $arResult;
			else
			{
				$ENTITY_TYPE = 'DEAL';
				$arAllResult = array_merge($arAllResult, $arResult);
			}
		}
		if ($ENTITY_TYPE == 'DEAL')
		{
			$arResult = self::UpdateSearch($arFilter, $ENTITY_TYPE);
			if (!is_array($arResult))
				return $arResult;
			else
				$arAllResult = array_merge($arAllResult, $arResult);
		}
		self::$bReIndex = false;
		self::$oCallback = null;
		self::$callback_method = '';

		if($oCallback)
			return false;
		return $arAllResult;
	}

	function OnSearchCheckPermissions($FIELD)
	{
		global $USER;

		$CCrmPerms = new CCrmPerms($USER->GetID());
		$arAttr['LEAD'] = $CCrmPerms->GetUserAttrForSelectEntity('LEAD', 'READ');
		$arAttr['DEAL'] = $CCrmPerms->GetUserAttrForSelectEntity('DEAL', 'READ');
		$arAttr['CONTACT'] = $CCrmPerms->GetUserAttrForSelectEntity('CONTACT', 'READ');
		$arAttr['COMPANY'] = $CCrmPerms->GetUserAttrForSelectEntity('COMPANY', 'READ');

		$arRel = array();
		foreach ($arAttr as $ENTITY_TYPE => $_arRel)
		{
			foreach ($_arRel as $arRelType)
			{
				if (empty($arRelType))
				{
					$arRel[] = $ENTITY_TYPE;
					continue ;
				}
				$arattr_d = array();
				$sattr_s = '';
				$sattr_u = '';
				$sattr_o = '';
				foreach ($arRelType as $_s)
				{
					if ($_s[0] == 'U')
						$sattr_u = $_s;
					else if ($_s[0] == 'D')
						$arattr_d[] = $_s;
					else if ($_s[0] == 'S')
						$sattr_s = $_s;
					else if ($_s[0] == 'O')
						$sattr_o = $_s;
				}

				$sattr = $ENTITY_TYPE;
				if (!empty($arattr_d))
				{
					foreach ($arattr_d as $sattr_d)
					{
						$sattr = $ENTITY_TYPE.'_'.$sattr_u.'_'.$sattr_d;
						if (!empty($sattr_s))
							$sattr .= '_'.$sattr_s;
						$arRel[] = $sattr;
					}
					if (!empty($sattr_o))
					{
						$sattr  .= '_'.$sattr_o;
						$arRel[] = $sattr;
					}
				}
				else
				{
					if (!empty($sattr_u))
						$sattr .= '_'.$sattr_u;
					if (!empty($sattr_s))
						$sattr .= '_'.$sattr_s;
					if (!empty($sattr_o))
						$sattr .= '_'.$sattr_o;
					$arRel[] = $sattr;
				}
			}
		}

		return $arRel;
	}

	static public function DeleteSearch($ENTITY_TYPE, $ENTITY_ID)
	{
		if (CModule::IncludeModule('search'))
		{
			CSearch::DeleteIndex('crm', $ENTITY_TYPE.'.'.$ENTITY_ID);
		}
	}
}

?>
