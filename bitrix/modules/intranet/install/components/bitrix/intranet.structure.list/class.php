<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

class CIntranetStructureListComponent extends CBitrixComponent
{
	const ADMIN_GROUP_ID = 1;
	const MAX_EXEC_RESIZE_TIME = 3;
	const LAST_ACTIVITY = 120;

	/**
	 * @var null|array
	 */
	protected $externalValues = null;
	protected $arFilter = array();
	/**
	 * @var null|CPHPCache
	 */
	protected $obCache = null;
	public $bExcel = null;

	public function __construct($component = null)
	{
		$this->bExcel = $_GET['excel'] == 'yes';

		parent::__construct($component);
	}

	protected function initExternalValues($filterName)
	{
		$this->externalValues = array(
			'UF_DEPARTMENT' => $_REQUEST[$filterName . '_UF_DEPARTMENT'],
			'POST' => isset($_REQUEST[$filterName . '_POST']) ? $_REQUEST[$filterName . '_POST'] : null,
			'COMPANY' => isset($_REQUEST[$filterName . '_COMPANY']) ? $_REQUEST[$filterName . '_COMPANY'] : null,
			'EMAIL' => isset($_REQUEST[$filterName . '_EMAIL']) ? $_REQUEST[$filterName . '_EMAIL'] : null,
			'FIO' => isset($_REQUEST[$filterName . '_FIO']) ? $_REQUEST[$filterName . '_FIO'] : null,
			'PHONE' => isset($_REQUEST[$filterName . '_PHONE']) ? $_REQUEST[$filterName . '_PHONE'] : null,
			'UF_PHONE_INNER' => isset($_REQUEST[$filterName . '_UF_PHONE_INNER']) ? $_REQUEST[$filterName . '_UF_PHONE_INNER'] : null,
			'KEYWORDS' => isset($_REQUEST[$filterName . '_KEYWORDS']) ? $_REQUEST[$filterName . '_KEYWORDS'] : null,
			'IS_ONLINE' => isset($_REQUEST[$filterName . '_IS_ONLINE']) ? $_REQUEST[$filterName . '_IS_ONLINE'] : null,
			'LAST_NAME' => isset($_REQUEST[$filterName . '_LAST_NAME']) ? $_REQUEST[$filterName . '_LAST_NAME'] : null,
			'LAST_NAME_RANGE' => isset($_REQUEST[$filterName . '_LAST_NAME_RANGE']) ? $_REQUEST[$filterName . '_LAST_NAME_RANGE'] : null,
		);

		if($this->externalValues['UF_DEPARTMENT'] !== null)
		{
			if(!is_array($this->externalValues['UF_DEPARTMENT']))
			{
				$this->externalValues['UF_DEPARTMENT'] = array($this->externalValues['UF_DEPARTMENT']);
			}
			array_walk($this->externalValues['UF_DEPARTMENT'], 'intval');
		}
		else
		{
			$this->externalValues['UF_DEPARTMENT'] = array();
		}
	}

	public function onPrepareComponentParams($arParams)
	{
		$arParams['FILTER_NAME'] = $this->initFilterName($arParams['FILTER_NAME']);
		$this->initExternalValues($arParams['FILTER_NAME']);

		$arParams['USERS_PER_PAGE']      = intval($arParams['USERS_PER_PAGE']);
		$arParams['NAV_TITLE']           = !empty($arParams['NAV_TITLE']) ? $arParams['NAV_TITLE'] : GetMessage('INTR_ISL_PARAM_NAV_TITLE_DEFAULT');
		$arParams['DATE_FORMAT']         = !empty($arParams['DATE_FORMAT']) ? $arParams['DATE_FORMAT'] : CComponentUtil::GetDateFormatDefault(false);
		$arParams['DATE_FORMAT_NO_YEAR'] = !empty($arParams['DATE_FORMAT_NO_YEAR']) ? $arParams['DATE_FORMAT_NO_YEAR'] : CComponentUtil::GetDateFormatDefault(true);

		//display photo? In old templates default value set to "false"
		$arParams['DISPLAY_USER_PHOTO'] = empty($arParams['DISPLAY_USER_PHOTO']) || $arParams['DISPLAY_USER_PHOTO'] != 'N'? 'Y' : 'N';
		InitBVar($arParams['FILTER_1C_USERS']);
		InitBVar($arParams['FILTER_SECTION_CURONLY']);
		InitBVar($arParams['SHOW_NAV_TOP']);
		InitBVar($arParams['SHOW_NAV_BOTTOM']);
		InitBVar($arParams['SHOW_UNFILTERED_LIST']);
		InitBVar($arParams['SHOW_DEP_HEAD_ADDITIONAL']);

		!isset($arParams["CACHE_TIME"]) && $arParams["CACHE_TIME"] = 3600;

		if ($arParams['CACHE_TYPE'] == 'A')
		{
			$arParams['CACHE_TYPE'] = COption::GetOptionString("main", "component_cache_on", "Y");
		}
		$arParams['DETAIL_URL'] = COption::GetOptionString('intranet', 'search_user_url', '/user/#ID#/');

		if (!array_key_exists("PM_URL", $arParams))
		{
			$arParams["PM_URL"] = "/company/personal/messages/chat/#USER_ID#/";
		}
		if (!array_key_exists("PATH_TO_USER_EDIT", $arParams))
		{
			$arParams["PATH_TO_USER_EDIT"] = '/company/personal/user/#user_id#/edit/';
		}
		if (!array_key_exists("PATH_TO_CONPANY_DEPARTMENT", $arParams))
		{
			$arParams["PATH_TO_CONPANY_DEPARTMENT"] = "/company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#";
		}
		if (IsModuleInstalled("video") && !array_key_exists("PATH_TO_VIDEO_CALL", $arParams))
		{
			$arParams["PATH_TO_VIDEO_CALL"] = "/company/personal/video/#USER_ID#/";
		}
		if (!$this->getUser()->CanDoOperation("edit_all_users") && isset($arParams["SHOW_USER"]) && $arParams["SHOW_USER"] != "fired")
		{
			$arParams["SHOW_USER"] = "active";
		}

		return parent::onPrepareComponentParams($arParams);
	}

	protected function fillFilterByExtranet()
	{
		$this->arFilter["ACTIVE"] = "Y";
		if ($this->arParams["EXTRANET_TYPE"] == "employees")
		{
			$this->arFilter["!UF_DEPARTMENT"] = false;
		}
		else
		{
			$this->arFilter["UF_DEPARTMENT"] = false;
		}
	}

	protected function fillFilterByIntranet()
	{
		if (!isset($this->arParams["SHOW_USER"]))
		{
			$this->arFilter = array('ACTIVE' => 'Y');
		}
		else
		{
			switch ($this->arParams["SHOW_USER"])
			{
				case "fired":
					$this->arFilter = array('ACTIVE' => 'N');
					break;
				case "inactive":
					$this->arFilter = array('ACTIVE'     => 'Y',
											'LAST_LOGIN' => false);
					break;
				case "extranet":
					if (CModule::IncludeModule('extranet'))
					{
						if (IsModuleInstalled("bitrix24"))
						{
							$this->arFilter = array('ACTIVE'      => 'Y',
													'GROUPS_ID'   => CExtranet::GetExtranetUserGroupID(),
													'!LAST_LOGIN' => false);
						}
						else
						{
							$this->arFilter = array('ACTIVE'    => 'Y',
													'GROUPS_ID' => CExtranet::GetExtranetUserGroupID());
						}
					}
					break;
				case "active":
					if (IsModuleInstalled("bitrix24"))
					{
						$this->arFilter = array('ACTIVE'      => 'Y',
												'!LAST_LOGIN' => false);
					}
					else
					{
						$this->arFilter = array('ACTIVE' => 'Y');
					}
					break;
			}
			$this->arResult["SHOW_USER"] = $this->arParams["SHOW_USER"];
		}
	}

	protected function fillFilter()
	{
		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		{
			$this->fillFilterByExtranet();
		}
		else
		{
			$this->fillFilterByIntranet();
		}

		if ($this->arParams['FILTER_1C_USERS'] == 'Y')
		{
			$this->arFilter['UF_1C'] = 1;
		}

		if ($this->externalValues['UF_DEPARTMENT'])
		{
			$this->arFilter['UF_DEPARTMENT'] = $this->arParams['FILTER_SECTION_CURONLY'] == 'N'?
				CIntranetUtils::GetIBlockSectionChildren($this->externalValues['UF_DEPARTMENT']) :
				$this->externalValues['UF_DEPARTMENT'];
		}
		elseif ((!CModule::IncludeModule('extranet') || !CExtranet::IsExtranetSite()) && $this->arParams["SHOW_USER"] != "all")
		{
			// only employees for an intranet site
			if ($this->arParams["SHOW_USER"] == "extranet")
			{
				$this->arFilter["UF_DEPARTMENT"] = false;
			}
			elseif ($this->arParams["SHOW_USER"] != "inactive" && $this->arParams["SHOW_USER"] != "fired")
			{
				$this->arFilter["!UF_DEPARTMENT"] = false;
			}
		}

		//items equal to FALSE (see converting to boolean in PHP) will be removed (see array_filter()). After merge with $this->arFilter
		$this->arFilter = array_merge(
			$this->arFilter,
			array_filter(array(
				'WORK_POSITION'   => $this->externalValues['POST'],
				'WORK_PHONE'      => $this->externalValues['PHONE'],
				'UF_PHONE_INNER'  => $this->externalValues['UF_PHONE_INNER'],
				'WORK_COMPANY'    => $this->externalValues['COMPANY'],
				'EMAIL'           => $this->externalValues['EMAIL'],
				'NAME'            => $this->externalValues['FIO'],
				'KEYWORDS'        => $this->externalValues['KEYWORDS'],
				'LAST_NAME'       => $this->externalValues['LAST_NAME'],
				'LAST_NAME_RANGE' => $this->externalValues['LAST_NAME_RANGE'],
		)));

		if ($this->externalValues['IS_ONLINE'] == 'Y')
		{
			$this->arFilter['LAST_ACTIVITY'] = static::LAST_ACTIVITY;
		}
		if ($this->externalValues['LAST_NAME'])
		{
			$this->arFilter['LAST_NAME_EXACT_MATCH'] = 'Y';
		}

		if($this->arFilter['LAST_NAME_RANGE'])
		{
			//input format: a-z (letter - letter)
			$letterRange      = explode('-', $this->arFilter['LAST_NAME_RANGE'], 2);
			$startLetterRange = array_shift($letterRange);
			$endLetterRange   = array_shift($letterRange);

			$this->arFilter[] = array(
				'LOGIC' => 'OR',
				array(
					'><F_LAST_NAME' => array(toUpper($startLetterRange), toUpper($endLetterRange)),
				),
				array(
					'><F_LAST_NAME' => array(toLower($startLetterRange), toLower($endLetterRange)),
				),
			);
			unset($this->arFilter['LAST_NAME_RANGE']);
		}

	}

	/**
	 * @param $filterName
	 * @return string
	 */
	protected function initFilterName($filterName)
	{
		if (strlen($filterName) <= 0 || !preg_match("/^[A-Za-z_][A-Za-z0-9_]*$/", $filterName))
		{
			return 'find_';
		}

		return $filterName;
	}

	/**
	 * @param bool $reload
	 * @return array
	 */
	protected function getCacheIdWithDepartment($reload = false)
	{
		// we'll cache all variants of selection by UF_DEPARTMENT (and GROUPS_ID with extranet)
		static $cntStartCacheId = '';
		static $cacheCount = null;

		if($cntStartCacheId && !$reload)
		{
			return array($cntStartCacheId, $cacheCount);
		}

		$cacheCount = count($this->arFilter);
		foreach ($this->arFilter as $key => $value)
		{
			$cntStartCacheId .= '|'.$key.':'.preg_replace("/[\s]*/", "", var_export($value, true));
		}

		return array($cntStartCacheId, $cacheCount);
	}

	/**
	 * Init CPHPCache and return status of initialization
	 * @param $cntStartCacheId
	 * @return bool
	 */
	protected function initCache($cntStartCacheId)
	{
		$cacheDir  = '/'  . SITE_ID . $this->getRelativePath()
					. '/' . substr(md5($cntStartCacheId), 0, 5)
					. '/' . trim(CDBResult::NavStringForCache($this->arParams['USERS_PER_PAGE'], false), '|');

		$cacheId = $this->getName() . '|' . SITE_ID;

		if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
		{
			$cacheId .= '|' . $this->getUser()->GetID() . '|' . $this->arParams['EXTRANET_TYPE'];
		}
		$cacheId .= CDBResult::NavStringForCache($this->arParams['USERS_PER_PAGE'], false)
				. $cntStartCacheId . "|" . $this->arParams['USERS_PER_PAGE'];

		$this->obCache = new CPHPCache;

		return $this->obCache->initCache($this->arParams['CACHE_TIME'], $cacheId, $cacheDir);
	}

	public function executeComponent()
	{
		if (!CModule::IncludeModule('intranet'))
		{
			ShowError(GetMessage('INTR_ISL_INTRANET_MODULE_NOT_INSTALLED'));
			return;
		}
		if (!CModule::IncludeModule('socialnetwork'))
			return;

		$showDepHeadAdditional = $this->arParams['SHOW_DEP_HEAD_ADDITIONAL'] == 'Y';
		$bNav                  = $this->arParams['SHOW_NAV_TOP'] == 'Y' || $this->arParams['SHOW_NAV_BOTTOM'] == 'Y';

		$this->fillFilter();

		list($cntStartCacheId, $cntStart) = $this->getCacheIdWithDepartment();

		if ($this->arParams['SHOW_UNFILTERED_LIST'] == 'N' && !$this->bExcel && $cntStart == count($this->arFilter) && !$this->arFilter['UF_DEPARTMENT'])
		{
			$this->arResult['EMPTY_UNFILTERED_LIST'] = 'Y';
			$this->includeComponentTemplate();

			return;
		}

		$this->arParams['bCache'] =
			$cntStart == count($this->arFilter) // we cache only unfiltered list
			&& !$this->bExcel
			&& $this->arParams['CACHE_TYPE'] == 'Y' && $this->arParams['CACHE_TIME'] > 0;

		$this->arResult['FILTER_VALUES'] = $this->arFilter;

		if (!$this->bExcel && $bNav)
		{
			CPageOption::SetOptionString("main", "nav_page_in_session", "N");
		}

		$bFromCache = false;
		if ($this->arParams['bCache'])
		{
			if($bFromCache = $this->initCache($cntStartCacheId))
			{
				$vars                              = $this->obCache->getVars();
				$this->arResult['USERS']           = $vars['USERS'];
				$this->arResult['DEPARTMENTS']     = $vars['DEPARTMENTS'];
				$this->arResult['DEPARTMENT_HEAD'] = $vars['DEPARTMENT_HEAD'];
				$this->arResult['USERS_NAV']       = $vars['USERS_NAV'];
				$strUserIDs                        = $vars['STR_USER_ID'];
			}
			else
			{
				$this->obCache->startDataCache();
				$this->getCacheManager()->startTagCache($this->obCache->initdir);
				$this->getCacheManager()->registerTag('intranet_users');
			}
		}

		if(!$bFromCache)
		{
			// get users list
			$obUser = new CUser();
			$arSelect = array('ID', 'ACTIVE', 'DEP_HEAD', 'GROUP_ID', 'NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN', 'EMAIL',
				'LID', 'DATE_REGISTER',  'PERSONAL_PROFESSION', 'PERSONAL_WWW', 'PERSONAL_ICQ', 'PERSONAL_GENDER', 'PERSONAL_BIRTHDATE',
				'PERSONAL_PHOTO', 'PERSONAL_PHONE', 'PERSONAL_FAX', 'PERSONAL_MOBILE', 'PERSONAL_PAGER', 'PERSONAL_STREET', 'PERSONAL_MAILBOX',
				'PERSONAL_CITY', 'PERSONAL_STATE', 'PERSONAL_ZIP', 'PERSONAL_COUNTRY', 'PERSONAL_NOTES', 'WORK_COMPANY', 'WORK_DEPARTMENT',
				'WORK_POSITION', 'WORK_WWW', 'WORK_PHONE', 'WORK_FAX', 'WORK_PAGER', 'WORK_STREET', 'WORK_MAILBOX', 'WORK_CITY', 'WORK_STATE',
				'WORK_ZIP', 'WORK_COUNTRY', 'WORK_PROFILE', 'WORK_LOGO', 'WORK_NOTES', 'PERSONAL_BIRTHDAY', 'LAST_ACTIVITY_DATE', 'LAST_LOGIN', 'IS_ONLINE');

			$this->arResult['USERS']           = array();
			$this->arResult['DEPARTMENTS']     = array();
			$this->arResult['DEPARTMENT_HEAD'] = 0;
			//disable/enable appearing of department head on page
			if( ($showDepHeadAdditional || count($this->arFilter) <= 2) && !empty($this->arFilter['UF_DEPARTMENT']) && is_array($this->arFilter['UF_DEPARTMENT']) )
			{
				if ($this->arParams['bCache'])
				{
					$this->getCacheManager()->registerTag('intranet_department_' . $this->arFilter['UF_DEPARTMENT'][0]);
				}

				$appendManager = CIntranetUtils::GetDepartmentManager(array($this->arFilter['UF_DEPARTMENT'][0]));
				$appendManager = array_pop($appendManager);
				if($appendManager)
				{
					$this->arResult['DEPARTMENT_HEAD']     = $appendManager['ID'];
					$this->arFilter['!ID']                 = $appendManager['ID'];
					$this->arResult['USERS'][$appendManager['ID']] = $appendManager;
				}
			}

			$bDisable = false;
			if (CModule::IncludeModule('extranet'))
			{
				if (CExtranet::IsExtranetSite() && !CExtranet::IsExtranetAdmin())
				{
					$arIDs = array_merge(CExtranet::GetMyGroupsUsers(SITE_ID), CExtranet::GetPublicUsers());

					if ($this->arParams['bCache'])
					{
						$this->getCacheManager()->registerTag('extranet_public');
						$this->getCacheManager()->registerTag('extranet_user_'.$this->getUser()->getID());
					}

					if (false !== ($key = array_search($this->getUser()->getID(), $arIDs)))
						unset($arIDs[$key]);

					if (count($arIDs) > 0)
					{
						$this->arFilter['ID'] = implode('|', array_unique($arIDs));
					}
					else
					{
						$bDisable = true;
					}
				}
			}

			if ($bDisable)
			{
				$dbUsers = new CDBResult();
				$dbUsers->initFromArray(array());
			}
			else
			{
				$arListParams = array('SELECT' => array('UF_*'), 'ONLINE_INTERVAL' => static::LAST_ACTIVITY);
				if (!$this->bExcel && $this->arParams['USERS_PER_PAGE'] > 0)
				{
					$arListParams['NAV_PARAMS'] = array('nPageSize' => $this->arParams['USERS_PER_PAGE'], 'bShowAll' => false);
				}
				$dbUsers = $obUser->GetList($sortBy = 'last_name', $sortDir = 'asc', $this->arFilter, $arListParams);
			}

			$strUserIDs = '';
			while ($arUser = $dbUsers->Fetch())
			{
				$this->arResult['USERS'][$arUser['ID']] = $arUser;
				$strUserIDs .= ($strUserIDs === '' ? '' : '|').$arUser['ID'];
			}

			$structure = CIntranetUtils::getStructure();
			$this->arResult['DEPARTMENTS'] = $structure['DATA']
			;
			$this->setDepWhereUserIsHead();

			$arAdmins = array();
			/** @noinspection PhpUndefinedVariableInspection */
			$rsUsers  = CUser::GetList($o, $b, array("GROUPS_ID" => array(static::ADMIN_GROUP_ID)), array("SELECT"=>array("ID")));
			while ($ar = $rsUsers->Fetch())
			{
				$arAdmins[$ar["ID"]] = $ar["ID"];
			}

			$extranetUsers = array();
			if (CModule::IncludeModule('extranet') && $extranetGroupID = CExtranet::GetExtranetUserGroupID())
			{
				$rsUsers = CUser::GetList($o, $b, array("GROUPS_ID" => array($extranetGroupID)), array("SELECT"=>array("ID")));
				while ($ar = $rsUsers->Fetch())
				{
					$extranetUsers[$ar["ID"]] = $ar["ID"];
				}
			}

			$displayPhoto = $this->displayPersonalPhoto();
			foreach ($this->arResult['USERS'] as $key => &$arUser)
			{
				// cache optimization
				foreach ($arUser as $k => $value)
				{
					if (
						is_array($value) && count($value) <= 0
						|| !is_array($value) && strlen($value) <= 0
						|| !in_array($k, $arSelect) && substr($k, 0, 3) != 'UF_'
					)
					{
						unset($arUser[$k]);
					}
					elseif ($k == "PERSONAL_COUNTRY" || $k == "WORK_COUNTRY")
					{
						$arUser[$k] = GetCountryByID($value);
					}
				}

				$arUser['IS_ONLINE'] = $arUser['IS_ONLINE'] == 'Y'? true : false;
				if ($this->arParams['bCache'])
				{
					$this->getCacheManager()->registerTag('intranet_user_'.$arUser['ID']);
				}

				$arUser['DETAIL_URL']      = str_replace(array('#ID#', '#USER_ID#'), $arUser['ID'], $this->arParams['DETAIL_URL']);
				$arUser['ADMIN']           = isset($arAdmins[$arUser['ID']]); //is user admin/extranet
				$arUser['ACTIVITY_STATUS'] = 'active';
				$arUser['EXTRANET']        = false;
				if (isset($extranetUsers[$arUser['ID']]) && count($arUser['UF_DEPARTMENT']) <= 0)
				{
					$arUser["ACTIVITY_STATUS"] = 'extranet';
					$arUser['EXTRANET']        = true;
				}
				if ($arUser["ACTIVE"] == "N")
				{
					$arUser["ACTIVITY_STATUS"] = 'fired';
				}
				if (IsModuleInstalled("bitrix24") && empty($arUser["LAST_LOGIN"]))
				{
					$arUser["ACTIVITY_STATUS"] = 'inactive';
				}
				$arUser['SHOW_USER']   = $this->arParams["SHOW_USER"];
				$arUser['IS_FEATURED'] = CIntranetUtils::IsUserHonoured($arUser['ID']);

				$arDep = array();
				foreach ((array)$arUser['UF_DEPARTMENT'] as $sect)
				{
					$arDep[$sect] = $this->arResult['DEPARTMENTS'][$sect]['NAME'];
				}
				$arUser['UF_DEPARTMENT'] = $arDep;
				if(!$this->bExcel && $displayPhoto)
				{
					$this->resizePersonalPhoto($arUser);
				}
			}
			unset($arUser, $key);

			$this->arResult["USERS_NAV"] = $bNav ? $dbUsers->GetPageNavStringEx($navComponentObject=null, $this->arParams["NAV_TITLE"]) : '';

			if ($this->arParams['bCache'])
			{
				$this->getCacheManager()->endTagCache();
				$this->obCache->endDataCache(array( 'USERS'           => $this->arResult['USERS'],
													'STR_USER_ID'     => $strUserIDs,
													'DEPARTMENTS'     => $this->arResult['DEPARTMENTS'],
													'DEPARTMENT_HEAD' => $this->arResult['DEPARTMENT_HEAD'],
													'USERS_NAV'       => $this->arResult['USERS_NAV']));
			}
		}

		$this->initSonetUserPerms(array_keys($this->arResult['USERS']));
		$this->workWithNonCacheAttr($bFromCache, $strUserIDs);

		if (!$this->bExcel)
		{
			$this->arResult['bAdmin'] = $this->getUser()->canDoOperation('edit_all_users') || $this->getUser()->canDoOperation('edit_subordinate_users');
			$this->IncludeComponentTemplate();
		}
		else
		{
			$this->getApplication()->restartBuffer();
			// hack. any '.default' customized template should contain 'excel' page
			$this->setTemplateName('.default');

			Header("Content-Type: application/force-download");
			Header("Content-Type: application/octet-stream");
			Header("Content-Type: application/download");
			Header("Content-Disposition: attachment;filename=users.xls");
			Header("Content-Transfer-Encoding: binary");

			$this->IncludeComponentTemplate('excel');

			die;
		}

		return;
	}

	protected function setDepWhereUserIsHead()
	{
		foreach ($this->arResult['DEPARTMENTS'] as &$dep)
		{
			if(!isset($dep['USERS']))
			{
				//structure for compatibility
				$dep['USERS'] = array();
			}

			if(!isset($this->arResult['USERS'][$dep['UF_HEAD']]))
				continue;

			$this->arResult['USERS'][$dep['UF_HEAD']]["DEP_HEAD"][$dep['ID']] = $dep['NAME'];

		}
		unset($dep);
	}

	/**
	 * Show column PHOTO
	 * @return bool
	 */
	protected function displayPersonalPhoto()
	{
		return isset($this->arParams['DISPLAY_USER_PHOTO']) &&  $this->arParams['DISPLAY_USER_PHOTO'] == 'Y';
	}

	/**
	 * Resize photo
	 * @return bool
	 */
	protected function isRunResizePersonalPhoto()
	{
		return true;
	}

	/**
	 * Get default picture for gender (socialnetwork)
	 * @param $gender
	 * @return string
	 */
	protected function getDefaultPictureSonet($gender)
	{
		static $defaultPicture = array();
		if(empty($defaultPicture))
		{
			$defaultPicture = array(
				'M'       => COption::GetOptionInt('socialnetwork', 'default_user_picture_male', false, SITE_ID),
				'F'       => COption::GetOptionInt('socialnetwork', 'default_user_picture_female', false, SITE_ID),
				'unknown' => COption::GetOptionInt('socialnetwork', 'default_user_picture_unknown', false, SITE_ID),
			);
		}

		if(!isset($defaultPicture[$gender]))
		{
			$gender = 'unknown';
		}
		return $defaultPicture[$gender];
	}

	/**
	 * Resize users photo. Time is limited.
	 * @param array $arUser
	 * @return bool If modify photo
	 */
	protected function resizePersonalPhoto(array &$arUser)
	{
		static $startTime = null;

		if($startTime === null)
		{
			$startTime = getmicrotime();
		}

		//photo for current user not resized. Do it!
		if(empty($arUser['PERSONAL_PHOTO_RESIZED']))
		{
			if (!$arUser['PERSONAL_PHOTO'])
			{
				$arUser['PERSONAL_PHOTO'] = $this->getDefaultPictureSonet($arUser['PERSONAL_GENDER']);
			}

			if(empty($arUser['PERSONAL_PHOTO_SOURCE']))
			{
				$arUser['PERSONAL_PHOTO_SOURCE'] = $arUser['PERSONAL_PHOTO'];
			}

			//if not run resize photo or we resize photo long time and we want stop it
			if (!$this->isRunResizePersonalPhoto() || round(getmicrotime()-$startTime, 3) > static::MAX_EXEC_RESIZE_TIME)
			{
				$arUser['PERSONAL_PHOTO']         = CFile::ShowImage($arUser['PERSONAL_PHOTO_SOURCE'], 9999, 100);
				$arUser['PERSONAL_PHOTO_RESIZED'] = false;

				return false;
			}

			$arImage = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO_SOURCE'], 100);
			$arUser['PERSONAL_PHOTO'] = $arImage['IMG'];
			$arUser['PERSONAL_PHOTO_RESIZED'] = true;

			return true;
		}

		return false;
	}

	/**
	 * Set mutable attributes
	 * @param bool   $bFromCache
	 * @param string $strUserIds
	 */
	protected function workWithNonCacheAttr($bFromCache = false, $strUserIds = '')
	{
		//if list of users in cache - get last activity
		if ($bFromCache && $strUserIds)
		{
			$dbRes = CUser::getList($by='id', $order='asc', array('ID' => $strUserIds, 'LAST_ACTIVITY' => static::LAST_ACTIVITY), array('FIELDS' => array('ID')));
			while ($arRes = $dbRes->fetch())
			{
				if ($this->arResult['USERS'][$arRes['ID']])
				{
					$this->arResult['USERS'][$arRes['ID']]['IS_ONLINE'] = true;
				}
			}
			unset($dbRes, $arRes);
		}

		$buildResizedPhoto = false;
		$displayPhoto      = $this->displayPersonalPhoto();
		foreach ($this->arResult['USERS'] as &$arUser)
		{
			if($this->bExcel && $displayPhoto)
			{
				//if export in excel, then method $this->resizePersonalPhoto() not run. And not modify PERSONAL_PHOTO
				if(!$arUser['PERSONAL_PHOTO'])
				{
					$arUser['PERSONAL_PHOTO'] = $this->getDefaultPictureSonet($arUser['PERSONAL_GENDER']);
				}
				$arUser['PERSONAL_PHOTO_SOURCE'] = $arUser['PERSONAL_PHOTO'];
				$arUser['PERSONAL_PHOTO']        = CFile::GetPath($arUser['PERSONAL_PHOTO']);
			}
			elseif($bFromCache && $displayPhoto)
			{
				$buildResizedPhoto = $this->resizePersonalPhoto($arUser) || $buildResizedPhoto;
			}
			$arUser['IS_BIRTHDAY'] = CIntranetUtils::IsToday($arUser['PERSONAL_BIRTHDAY']);
			$arUser['IS_ABSENT']   = CIntranetUtils::IsUserAbsent($arUser['ID']);
		}

		//rewrite cache if we build new resized photo
		if($buildResizedPhoto)
		{
			$this->obCache->clean($this->obCache->uniq_str, $this->obCache->initdir);

			$this->obCache->startDataCache();
			$this->obCache->endDataCache(array(
											'USERS'          => $this->arResult['USERS'],
											'STR_USER_ID'     => $strUserIds,
											'DEPARTMENTS'     => $this->arResult['DEPARTMENTS'],
											'DEPARTMENT_HEAD' => $this->arResult['DEPARTMENT_HEAD'],
											'USERS_NAV'       => $this->arResult['USERS_NAV']));
		}
	}

	protected function initSonetUserPerms($arUserID)
	{
		if (!is_array($arUserID))
			$arUserID = array(intval($arUserID));

		CSocNetUserPerms::GetOperationPerms($arUserID, "viewprofile");
	}

	/**
	 * @return CAllUser
	 */
	public function getUser()
	{
		global $USER;

		return $USER;
	}

	/**
	 * @return CAllMain
	 */
	public function getApplication()
	{
		global $APPLICATION;

		return $APPLICATION;
	}

	/**
	 * @return CCacheManager
	 */
	public function getCacheManager()
	{
		global $CACHE_MANAGER;

		return $CACHE_MANAGER;
	}
}