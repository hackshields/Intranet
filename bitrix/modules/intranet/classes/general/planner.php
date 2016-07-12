<?
IncludeModuleLangFile(__FILE__);

class CIntranetPlanner
{
	const CACHE_TTL = 86400;
	const CACHE_TAG = 'intranet_planner_';
	const JS_CORE_EXT_RANDOM_NAME = 'planner_handler_';

	public static function getData($SITE_ID = SITE_ID, $bFull = false)
	{
		global $USER, $CACHE_MANAGER;

		$obCache = new CPHPCache();

		$cache_dir = '/intranet/planner';
		$cache_id = 'intranet|planner|'.$USER->GetID().'|'.$SITE_ID.'|'.intval($bFull).'|'.FORMAT_DATETIME.'|'.FORMAT_DATE;

		$arData = null;
		$today = ConvertTimeStamp();

		if ($obCache->InitCache(self::CACHE_TTL, $cache_id, $cache_dir))
		{
			$arData = $obCache->GetVars();

			if($today != $arData['TODAY'])
			{
				$arData = null;
				$obCache->Clean($cache_id, $cache_dir);
			}
			else
			{
				unset($arData['TODAY']);
				if(is_array($arData['SCRIPTS']))
				{
					foreach($arData['SCRIPTS'] as $key => $script)
					{
						if(is_array($script))
						{
							$arData['SCRIPTS'][$key] = self::JS_CORE_EXT_RANDOM_NAME.RandString(5);
							CJSCore::RegisterExt($arData['SCRIPTS'][$key], $script);
						}
					}
				}
			}
		}

		// cache expired or there's no cache
		if($obCache->StartDataCache() || $arData == null)
		{
			$arData = array(
				'SCRIPTS' => array(),
				'STYLES' => array(),
				'DATA' => array()
			);

			$CACHE_MANAGER->StartTagCache($cache_dir);
			$CACHE_MANAGER->RegisterTag(self::CACHE_TAG.$USER->GetID());

			$events = GetModuleEvents("intranet", "OnPlannerInit");
			while($arEvent = $events->Fetch())
			{
				$arEventData = ExecuteModuleEventEx(
					$arEvent,
					array(
						array(
							'SITE_ID' => SITE_ID,
							'FULL' => $bFull
						)
					)
				);


				if(is_array($arEventData))
				{
					if(is_array($arEventData['SCRIPTS']))
						$arData['SCRIPTS'] = array_merge($arData['SCRIPTS'], $arEventData['SCRIPTS']);
					if(is_array($arEventData['STYLES']))
						$arData['STYLES'] = array_merge($arData['STYLES'], $arEventData['STYLES']);
					if(is_array($arEventData['DATA']))
						$arData['DATA'] = array_merge($arData['DATA'], $arEventData['DATA']);
				}
			}

			$arCacheData = $arData;

			$arCacheData['TODAY'] = $today;

			if(is_array($arCacheData['SCRIPTS']))
			{
				foreach($arCacheData['SCRIPTS'] as $key => $script)
				{
					if(CJSCore::IsExtRegistered($script))
					{
						$arCacheData['SCRIPTS'][$key] = CJSCore::getExtInfo($script);
					}
				}
			}

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache($arCacheData);
		}

		return $arData;
	}

	public static function initScripts($arData)
	{
		global $APPLICATION;

		$arExt = array('planner');

		if(is_array($arData['SCRIPTS']))
		{
			foreach($arData['SCRIPTS'] as $script)
			{
				if(CJSCore::IsExtRegistered($script))
				{
					$arExt[] = $script;
				}
				else
				{
					$APPLICATION->AddHeadScript($script);
				}
			}
		}

		if(is_array($arData['STYLES']))
		{
			foreach($arData['STYLES'] as $style)
			{
				$APPLICATION->SetAdditionalCSS($style);
			}
		}

		CJSCore::Init($arExt);
	}

	public static function callAction($action, $site_id)
	{
		global $USER, $CACHE_MANAGER;

		$res = array();

		$events = GetModuleEvents("intranet", "OnPlannerAction");
		while($arEvent = $events->Fetch())
		{
			$eventRes = ExecuteModuleEventEx(
				$arEvent,
				array(
					$action,
					array(
						'SITE_ID' => $site_id
					)
				)
			);


			if(is_array($eventRes))
			{
				$res = array_merge($res, $eventRes);
			}
		}

		$CACHE_MANAGER->ClearByTag(self::CACHE_TAG.$USER->GetID());

		return $res;
	}
}
?>