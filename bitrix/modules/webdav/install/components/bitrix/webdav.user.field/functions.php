<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

function WDUFGetExtranetDir()
{
	global $APPLICATION, $USER;
	$URLPrefix = null;

	if ($URLPrefix == null)
	{
		$URLPrefix = '';
		if (
			CModule::IncludeModule('extranet')
			&& (strlen(CExtranet::GetExtranetSiteID()) > 0)
			&& $USER->IsAuthorized()
			&& !$USER->IsAdmin() &&
			!CExtranet::IsIntranetUser()
		)
		{
			$rsSites = CSite::GetByID(CExtranet::GetExtranetSiteID());
			if ($arExtranetSite  = $rsSites->Fetch())
			{
				$URLPrefix = $arExtranetSite["DIR"];
			}
		}
	}
	return $URLPrefix;
}

function WDUFLoadStyle()
{
	global $APPLICATION;
	static $styleLoaded = false;

	if (!$styleLoaded)
	{
		$APPLICATION->SetAdditionalCSS('/bitrix/components/bitrix/webdav/templates/.default/wdif.css');
		$styleLoaded = true;
	}
}

function WDUFUserFieldView(&$arParams, &$arResult)
{
	static $DROPPED = '.Dropped';
	if (!(CModule::IncludeModule('iblock') && CModule::IncludeModule('webdav')))
		return false;

	global $APPLICATION, $USER_FIELD_MANAGER;
	static $arIBlock = array();
	$result = array();
	$arIBlockCacheID = array();
	$arValue = array();
	$EVId = (is_array($arParams["arUserField"]) && $arParams["arUserField"]["ENTITY_VALUE_ID"] > 0 ?
		intval($arParams["arUserField"]["ENTITY_VALUE_ID"]) : 0);
	$arResult['VALUE'] = (is_array($arResult['VALUE']) ? $arResult['VALUE'] : array());

	foreach($arResult['VALUE'] as $val)
	{
		$val = intval($val);
		if ($val > 0)
			$arValue[] = $val;
	}

	if (sizeof($arValue) > 0)
	{
		// cache
		$obCache = new CPHPCache;
		$cacheID = ($EVId > 0 ? $EVId : md5(serialize($arValue)));
		$cachePath = SITE_ID."/webdav/inline";

		//$arRating = CRatings::GetRatingVoteResult('IBLOCK_ELEMENT', $arValue);
		//$sRatingVoteType = COption::GetOptionString("main", "rating_vote_type", "standart");
		//if ($sRatingVoteType == "like_graphic")
			//$RATING_TYPE = "like";
		//else if ($sRatingVoteType == "standart")
			//$RATING_TYPE = "standart_text";


		if($obCache->InitCache(30*86400, $cacheID, $cachePath))
		{
			$vars = $obCache->GetVars();
			$result = $vars["RESULT"];
		}
		if (empty($result) && $obCache->StartDataCache())
		{
			$ElementID = $arValue;
			if ($EVId > 0)
			{
				$ElementID = $USER_FIELD_MANAGER->GetUserFieldValue(
					$arParams["arUserField"]["ENTITY_ID"],
					$arParams["arUserField"]["FIELD_NAME"],
					$EVId);
				$ElementID = (empty($ElementID) ? $arValue : $ElementID);
			}

			// check file exists
			$ibe = new CIBlockElement();
			$dbWDFile = $ibe->GetList(array(), array('ID' => $ElementID), false, false,
				array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'IBLOCK_ID', 'PROPERTY_WEBDAV_SIZE', 'PROPERTY_FILE', 'CREATED_BY', 'CREATED_USER_NAME'));
			if ($dbWDFile)
			{
				while ($arWDFile = $dbWDFile->Fetch())
				{
					$id = intval($arWDFile['ID']);

					if (!isset($arIBlock[$arWDFile['IBLOCK_ID']]))
					{
						$dbWDIBlock = CIBlock::GetList(array(), array('ID' => $arWDFile['IBLOCK_ID'], 'CHECK_PERMISSIONS' => 'N'));
						if ($dbWDIBlock && $arWDIBlock = $dbWDIBlock->Fetch())
							$arIBlock[$arWDFile['IBLOCK_ID']] = $arWDIBlock;
					}

					if (isset($arIBlock[$arWDFile['IBLOCK_ID']]))
					{
						$arWDIBlock = $arIBlock[$arWDFile['IBLOCK_ID']];
						$arIBlockCacheID[] = $arWDFile['IBLOCK_ID'];

						// get path to document
						$detailPath = CWebDavIblock::LibOptions('lib_paths', true, $arWDFile['IBLOCK_ID']);
						if (!$detailPath)
						{
							$detailPath = $arWDIBlock['DETAIL_PAGE_URL'];
						}

						$bSonetUser = ((strpos($detailPath, "#user_id#") !== false) || (strpos($detailPath, "#USER_ID#") !== false));
						$bSonetGroup = ((strpos($detailPath, "#group_id#") !== false) || (strpos($detailPath, "#GROUP_ID#") !== false));

						$db_nav = CIBlockSection::GetNavChain($arWDFile['IBLOCK_ID'], $arWDFile['IBLOCK_SECTION_ID']);
						$arNavChain = array();
						if ($db_nav && ($arSection = $db_nav->Fetch()))
						{
							$arNavChain[] = $arSection;
							if ($arSection['NAME'] == '.Trash') // not show items from trash
								continue;

							if ($bSonetUser || $bSonetGroup)
							{
								$detailPath = str_replace(
									array(
										"#SOCNET_USER_ID#", "#USER_ID#", "#SOCNET_GROUP_ID#", "#GROUP_ID#", "#SOCNET_OBJECT#", "#SOCNET_OBJECT_ID#",
										"#socnet_user_id#", "#user_id#", "#socnet_group_id#", "#group_id#", "#socnet_object#", "#socnet_object_id#"
									),
									array(
										$arSection["CREATED_BY"], $arSection["CREATED_BY"], $arSection["SOCNET_GROUP_ID"], $arSection["SOCNET_GROUP_ID"],
										($arSection["SOCNET_GROUP_ID"] > 0 ? "group" : "user"),
										($arSection["SOCNET_GROUP_ID"] > 0 ? $arSection["SOCNET_GROUP_ID"] : $arSection["CREATED_BY"]),
										$arSection["CREATED_BY"], $arSection["CREATED_BY"], $arSection["SOCNET_GROUP_ID"], $arSection["SOCNET_GROUP_ID"],
										($arSection["SOCNET_GROUP_ID"] > 0 ? "group" : "user"),
										($arSection["SOCNET_GROUP_ID"] > 0 ? $arSection["SOCNET_GROUP_ID"] : $arSection["CREATED_BY"]),
									), $detailPath);
								// in group trash can be 1 level deeper
								if ($arSection = $db_nav->Fetch())
								{
									$arNavChain[] = $arSection;
									if ($arSection['NAME'] == '.Trash') // not show items from trash
										continue;
								}
							}
							while ($arSection = $db_nav->Fetch())
								$arNavChain[] = $arSection;
						}

						if (strpos("#SITE_DIR#", $detailPath) !== false)
						{
							$detailPath = str_replace(array("#SITE_DIR#"), SITE_DIR, $detailPath);
						}
						else
						{
							if (
								CModule::IncludeModule('extranet')
								&& (CExtranet::GetExtranetSiteID() == SITE_ID)
							)
							{
								$rsSites = CSite::GetByID(SITE_ID);
								if (
									($arExtranetSite  = $rsSites->Fetch())
									&& (strpos($detailPath, $arExtranetSite["DIR"]) === false)
								)
								{
									if ($bSonetUser)
									{
										$intranet_path = COption::GetOptionString("socialnetwork", "user_page", false, CSite::GetDefSite());
										$extranet_path = COption::GetOptionString("socialnetwork", "user_page", false, SITE_ID);
										if (strpos($detailPath, $intranet_path) === 0)
											$detailPath = str_replace($intranet_path, $extranet_path, $detailPath);
									}
									elseif ($bSonetGroup)
									{
										$intranet_path = COption::GetOptionString("socialnetwork", "workgroups_page", false, CSite::GetDefSite());
										$extranet_path = COption::GetOptionString("socialnetwork", "workgroups_page", false, SITE_ID);
										if (strpos($detailPath, $intranet_path) === 0)
											$detailPath = str_replace($intranet_path, $extranet_path, $detailPath);
									}
									else
										$detailPath = str_replace(array("///", "//"), "/", $arExtranetSite["DIR"] . $detailPath);
								}
							}
						}

						$arWDFile['VIEW'] = $detailPath;
						if($bSonetUser)
						{
							$arWDFile['VIEW'] = str_replace('element/view', "lib", $arWDFile['VIEW']);
						}
						elseif($bSonetGroup)
						{
							$arWDFile['VIEW'] = str_replace('element/view', "", $arWDFile['VIEW']);
						}
						else
						{
							$arWDFile['VIEW'] = str_replace('element/view', "", $arWDFile['VIEW']);
						}
						$arWDFile['VIEW'] = str_replace(array("#ID#", "#id#", "#ELEMENT_ID#", "#element_id#"), '', $arWDFile['VIEW']);

						$detailPath = str_replace(array("#ID#", "#id#", "#ELEMENT_ID#", "#element_id#"), $id, $detailPath);
						$detailPath = str_replace("view", "historyget", $detailPath);
						if (rtrim($detailPath, "/") != $detailPath)
							$detailPath .= $arWDFile["NAME"];

						$arWDFile['PATH'] = str_replace(array("///","//"),"/",$detailPath);
						$arWDFile['VIEW'] = str_replace(array("///","//"),"/",$arWDFile['VIEW']);

						// 'breadcrumb'
						$arSectionsChain = array();
						//to link on element
						$userIBlockID = CWebDavIblock::LibOptions('user_files', false, SITE_ID);
						$groupIBlockID = CWebDavIblock::LibOptions('group_files', false, SITE_ID);

						$arUrlSectionsChain = array();
						$i = 0;
						foreach ($arNavChain as $res)
						{
							$name = $res["NAME"];
							if (($i == 0) && !!$res["SOCNET_GROUP_ID"] && CModule::IncludeModule('socialnetwork') && strlen(GetMessage('SONET_GROUP_PREFIX')) > 0)
							{
								if ($name == GetMessage('SONET_GROUP_PREFIX')) // old bug with empty folder name in group
								{
									$arGroup = CSocNetGroup::GetByID($res["SOCNET_GROUP_ID"]);
									$name = GetMessage("SONET_GROUP_PREFIX").$arGroup['NAME'];
								}
							}

							//drop prefix storage name (1st level in section tree) if user or groups file. If shared docs - don't
							if($i != 0 || (!$bSonetUser && !$bSonetGroup))
							{
								$arUrlSectionsChain[] = $name;
							}
							if ($name != $DROPPED)
							{
								$arSectionsChain[] = $name;
								$i++;
							}
						}

						if ($arSectionsChain[$i] == $DROPPED)
						{
							$arWDFile['NAVCHAIN'] = GetMessage('WDUF_ATTACHED_TO_MESSAGE');
						}
						else
						{
							if ($userIBlockID && $groupIBlockID)
							{
								if (($arWDFile['IBLOCK_ID'] != $userIBlockID['id']) && ($arWDFile['IBLOCK_ID'] != $groupIBlockID['id']))
								{
									$name = CIBlock::GetArrayByID($arWDFile['IBLOCK_ID'], 'NAME');
									array_unshift($arSectionsChain, $name);
								}
							}

							$arWDFile['NAVCHAIN'] = implode("/", $arSectionsChain);
						}

						$arUrlSectionsChain[] = $arWDFile["NAME"];
						$arWDFile['VIEW'] .= implode('/', $arUrlSectionsChain);


						// extension
						$name = $arWDFile['NAME'];
						$ext = '';
						$dotpos = strrpos($name, ".");
						if (($dotpos !== false) && ($dotpos+1 < strlen($name)))
							$ext = substr($name, $dotpos+1);
						if (strlen($ext) < 3 || strlen($ext) > 5)
							$ext = '';
						$arWDFile['EXTENSION'] = $ext;

						// size
						$arWDFile['SIZE'] = 0;
						if ($arWDFile['PROPERTY_WEBDAV_SIZE_VALUE'])
							$arWDFile['SIZE'] = CFile::FormatSize(intval($arWDFile['PROPERTY_WEBDAV_SIZE_VALUE']), 0);

						// file
						$arWDFile['FILE'] = array();
						if ($arWDFile['PROPERTY_FILE_VALUE'])
							$arWDFile['FILE'] = CFile::GetFileArray($arWDFile['PROPERTY_FILE_VALUE']);

						if (strlen($arWDFile['PATH']) > 0)
						{
							$result[$id] = $arWDFile;
						}
					}
				}
			}

			global $CACHE_MANAGER;
			$CACHE_MANAGER->StartTagCache($cachePath);
			foreach ($arIBlockCacheID as $ibID)
				$CACHE_MANAGER->RegisterTag("iblock_id_".$ibID);
			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache(array("RESULT" => $result));
		}
		// not cached

		// check file access rights
		static $op = 'element_read';
		foreach($result as $id => $arWDFile)
		{
			if (!in_array($id, $arValue)) {
				unset($result[$id]);
				continue;
			} else if (!isset($arIBlock[$arWDFile['IBLOCK_ID']])) {
				continue;
			}

			$arWDIBlock = $arIBlock[$arWDFile['IBLOCK_ID']];
			if ($arWDIBlock['RIGHTS_MODE'] == 'E')
			{
				$ibRights = CWebDavIblock::_get_ib_rights_object('ELEMENT', $id, $arWDIBlock['ID']);
				if (!$ibRights->UserHasRightTo($arWDIBlock['ID'], $id, $op))
				{
					unset($result[$id]);
				}
			}
			else
			{
				if (CIBlock::GetPermission($arWDIBlock['ID']) < 'R')
					unset($result[$id]);
			}
		}

		// ratings
		//foreach($result as $id => $arWDFile)
		//{
			//ob_start();
			//$APPLICATION->IncludeComponent(
				//"bitrix:rating.vote", $RATING_TYPE,
				//Array(
					//"ENTITY_TYPE_ID" => "IBLOCK_ELEMENT",
					//"ENTITY_ID" => $id,
					////"OWNER_ID" => $data["data"]["CREATED_BY"]["ID"],
					//"USER_VOTE" => $arRating[$id]["USER_VOTE"],
					//"USER_HAS_VOTED" => $arRating[$id]["USER_HAS_VOTED"],
					//"TOTAL_VOTES" => $arRating[$id]["TOTAL_VOTES"],
					//"TOTAL_POSITIVE_VOTES" => $arRating[$id]["TOTAL_POSITIVE_VOTES"],
					//"TOTAL_NEGATIVE_VOTES" => $arRating[$id]["TOTAL_NEGATIVE_VOTES"],
					//"TOTAL_VALUE" => $arRating[$id]["TOTAL_VALUE"],
					////"PATH_TO_USER_PROFILE" => $arParams["USER_VIEW_URL"],
				//),
				//null,
				//array("HIDE_ICONS" => "Y")
			//);
			//$result[$id]['RATING'] = ob_get_clean();
		//}
	}

	//output
	$arResult['FILES'] = $result;
}

function WDUFUserFieldEdit(&$arParams, &$arResult)
{
	global $APPLICATION;
	static $arValidTypes = array('BLOG_POST', 'BLOG_COMMENT', 'TASKS_TASK', 'CRM_ACTIVITY');
	static $userIblockID = false;
	static $groupIblockID = false;
	static $iblockOptionTypes = array("group_files", "shared_files", "user_files");
	static $iblockOptions = array();
	static $arIBlock = array();


	if (!CModule::IncludeModule('webdav'))
		return false;

	//$APPLICATION->AddHeadString('<link href="/bitrix/components/bitrix/webdav/templates/.default/style.css" type="text/css" rel="stylesheet" />'); // for IE style debug
	if (in_array($arParams['arUserField']['ENTITY_ID'], $arValidTypes))
	{
		$arResult['controlName'] = $arParams['arUserField']['FIELD_NAME'];

		$arValue = $arParams['arUserField']['VALUE'];

		$arResult['ELEMENTS'] = array();
		if (is_array($arValue) && sizeof($arValue) > 0)
		{

			if (empty($iblockOptions))
			{

				foreach($iblockOptionTypes as $type)
				{
					$arOpt = CWebDavIblock::LibOptions($type, false, SITE_ID);

					if (is_set($arOpt, 'id') && (intval($arOpt['id']) > 0))
					{
						$iblockOptions[$type] = $arOpt['id'];
					}
				}

			}

			foreach ($arValue as $elementID)
			{
				$elementID = intval($elementID);
				if ($elementID <= 0)
					continue;

				$title = '';
				$dropped = false;

				$dbElement = CIBlockElement::GetList(
					array(),
					array('ID' => $elementID),
					false,
					false,
					array(
						'ID',
						'NAME',
						'IBLOCK_ID',
						'IBLOCK_SECTION_ID',
						'SOCNET_GROUP_ID',
						'CREATED_BY'
					)
				);

				if (
					$dbElement
					&& $arElement = $dbElement->Fetch()
				)
				{
					$arSectionTree = array();

					$db_nav = CIBlockSection::GetNavChain($arElement['IBLOCK_ID'], $arElement['IBLOCK_SECTION_ID']);
					while ($arSection = $db_nav->Fetch())
						$arSectionTree[] = $arSection;

					$dropped = false;

					if (
						(sizeof($arSectionTree) > 0)
						&& ($arSectionTree[0]['NAME'] == '.Dropped')
					)
					{
						$title = GetMessage('WD_LOCAL_COPY_ONLY');
						$dropped = true;
					}
					else
					{
						$type = array_search($arElement['IBLOCK_ID'], $iblockOptions);

						if ($type == 'group_files')
						{
							if ((sizeof($arSectionTree) > 0))
							{
								$title = $arSectionTree[0]['NAME'];
							}
						}
						elseif ($type == 'user_files')
						{
							if (
								(sizeof($arSectionTree) > 1)
								&& ($arSectionTree[1]['NAME'] == '.Dropped')
							)
							{
								$title = GetMessage('WD_LOCAL_COPY_ONLY');
								$dropped = true;
							}
							elseif (sizeof($arSectionTree) > 0)
							{
								$title = GetMessage('WD_MY_LIBRARY');
								/*$l = sizeof($arSectionTree);
								for($i = 1; $i < $l; $i++)
								{
									$title .= " / " .  $arSectionTree[$i]['NAME'];
								}*/

							}
						}
						else
						{

							if (!isset($arIBlock[$arElement['IBLOCK_ID']]))
							{
								$dbIB = CIBlock::GetList(array(), array('ID' => $arElement['IBLOCK_ID']));
								if ($dbIB && $arIB = $dbIB->Fetch())
								{
									$arIBlock[$arElement['IBLOCK_ID']] = $arIB;
								}
							}

							if (isset($arIBlock[$arElement['IBLOCK_ID']]))
							{
								$title = $arIBlock[$arElement['IBLOCK_ID']]['NAME'];
							}
						}
					}

					$arElement['FILE_SIZE'] = '';

					$dbSize = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $arElement['ID'], array(), array('CODE' => 'WEBDAV_SIZE'));
					if ($dbSize && $arSize=$dbSize->Fetch())
					{
						$arElement['FILE_SIZE'] = CFile::FormatSize(intval($arSize['VALUE']), 0);
					}

					$arElement['FILE'] = array();

					$dbSize = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $arElement['ID'], array(), array('CODE' => 'FILE'));
					if ($dbSize && $arSize=$dbSize->Fetch())
					{
						$arElement['FILE'] = CFile::GetFileArray($arSize['VALUE']);
					}

					$arElement['URL_EDIT'] = ''; $arElement['URL_GET'] = ''; $arElement['URL_PREVIEW'] = '';
					$detailPath = CWebDavIblock::LibOptions('lib_paths', true, $arElement['IBLOCK_ID']);
					$arSection =&$arSectionTree[0];

					if ($detailPath)
					{
						$detailPath = str_replace(
							array(
								"#SOCNET_USER_ID#", "#USER_ID#", "#SOCNET_GROUP_ID#", "#GROUP_ID#", "#SOCNET_OBJECT#", "#SOCNET_OBJECT_ID#",
								"#socnet_user_id#", "#user_id#", "#socnet_group_id#", "#group_id#", "#socnet_object#", "#socnet_object_id#"
							),
							array(
								$arElement["CREATED_BY"], $arElement["CREATED_BY"], $arSection["SOCNET_GROUP_ID"], $arSection["SOCNET_GROUP_ID"],
								($arSection["SOCNET_GROUP_ID"] > 0 ? "group" : "user"),
								($arSection["SOCNET_GROUP_ID"] > 0 ? $arSection["SOCNET_GROUP_ID"] : $arElement["CREATED_BY"]),
								$arElement["CREATED_BY"], $arElement["CREATED_BY"], $arSection["SOCNET_GROUP_ID"], $arSection["SOCNET_GROUP_ID"],
								($arSection["SOCNET_GROUP_ID"] > 0 ? "group" : "user"),
								($arSection["SOCNET_GROUP_ID"] > 0 ? $arSection["SOCNET_GROUP_ID"] : $arElement["CREATED_BY"]),
							), $detailPath
						);

						$detailPath = str_replace(array('#element_id#', '#ELEMENT_ID#'), $arElement['ID'], $detailPath);

						$arElement['URL_EDIT'] = str_replace(array('view', "///", "//"), array('edit', "/", "/"), $detailPath . '/EDIT/');
						$arElement['URL_GET'] = str_replace(array('view', "///", "//"), array('historyget', "/", "/"), $detailPath . '/' . $arElement["NAME"]);
						if (CFile::IsImage($arElement['NAME'], $arElement['FILE']["CONTENT_TYPE"]))
							$arElement['URL_PREVIEW'] = $arElement['URL_GET'] . (strpos($arElement['URL_GET'], "?") === false ? "?" : "&")."cache_image=Y&width=100&height=100";
					}

					$arElement['DROPPED'] = $dropped;
					$arElement['TITLE'] = $title;

					$arResult['ELEMENTS'][] = $arElement;
				}
			}
		}

		$arResult['JSON'] = array();

		// need to load Options for ajax dialogs
		$extDir = WDUFGetExtranetDir();
		if ($extDir !== '')
		{
			$groupIBlockID = CWebDavIblock::LibOptions('group_files', false, SITE_ID);

			if (
				!($groupIBlockID
				&& isset($groupIBlockID['id'])
				&& intval($groupIBlockID['id']) > 0)
			)
			{
				$arGroups = CIBlockWebdavSocnet::GetUserGroups(0, false);

				if (sizeof($arGroups) > 0)
				{
					$arGroup = array_pop($arGroups);
					$groupFilesUrl = str_replace(array("///","//"), "/", "/" . $extDir . '/workgroups/group/'.$arGroup['GROUP_ID'].'/files/');
					$arResult['JSON'][] = $groupFilesUrl;
				}
			}
		}
		else
		{
			$sharedLibID = CWebDavIblock::LibOptions('shared_files', false, SITE_ID);
			if (!($sharedLibID &&
				isset($sharedLibID['id']) &&
				intval($sharedLibID['id']) > 0 &&
				isset($sharedLibID['base_url']) &&
				strlen($sharedLibID['base_url']) > 0
			))
			{
				if (!(
					CModule::IncludeModule('extranet')
					&& (strlen(CExtranet::GetExtranetSiteID()) > 0)
					&& (SITE_ID == CExtranet::GetExtranetSiteID())
				))
				{
					$arResult['JSON'][] = '/docs/';
					$arResult['JSON'][] = '/docs/shared/';
				}
			}

			$userIBlockID = CWebDavIblock::LibOptions('user_files', false, SITE_ID);

			if (
				! (
					$userIBlockID
					&& isset($userIBlockID['id'])
					&& (intval($userIBlockID['id']) > 0)
				)
			)
			{
				$arResult['JSON'][] = '/company/personal/user/' . $GLOBALS['USER']->GetID() . '/files/lib/';
			}

			$groupIBlockID = CWebDavIblock::LibOptions('group_files', false, SITE_ID);

			if (
				! (
					$groupIBlockID
					&& isset($groupIBlockID['id'])
					&& (intval($groupIBlockID['id']) > 0)
				)
			)
			{
				$arGroups = CIBlockWebdavSocnet::GetUserGroups(0, false);
				if (sizeof($arGroups) > 0)
				{
					$arGroup = array_pop($arGroups);
					$arResult['JSON'][] = '/workgroups/group/' . $arGroup['GROUP_ID'] . '/files/';
				}
			}
		}
	}
}
?>
