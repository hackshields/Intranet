<?php
/*
 * CCrmWebDavHelper - integration with webdav module.
 * IBlock implementation only supported.
 * */
class CCrmWebDavHelper
{
	private static $SOC_NET_EVENT = null;
	private static $SHARED_FILES_INFO = array();
	private static function GetSocNetEvent()
	{
		if (!(IsModuleInstalled('webdav')
			&& CModule::IncludeModule('webdav')))
		{
			return null;
		}

		if(!self::$SOC_NET_EVENT)
		{
			self::$SOC_NET_EVENT = CWebDavSocNetEvent::GetRuntime();
		}

		return self::$SOC_NET_EVENT;
	}

	private static $IBLOCK = array();
	private static function GetIBlock($iblockID)
	{
		if (!(IsModuleInstalled('iblock')
			&& IsModuleInstalled('webdav')
			&& CModule::IncludeModule('iblock')
			&& CModule::IncludeModule('webdav')))
		{
			return null;
		}

		$iblockID = intval($iblockID);
		if(!isset(self::$IBLOCK[$iblockID]))
		{
			self::$IBLOCK[$iblockID] = new CWebDavIblock($iblockID, '');
		}

		return self::$IBLOCK[$iblockID];
	}

	private static function GetElement($elementID)
	{
		if (!(IsModuleInstalled('iblock')
			&& CModule::IncludeModule('iblock')))
		{
			return null;
		}

		$elementID = intval($elementID);

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

		return $dbElement ? $dbElement->Fetch() : null;
	}

	public static function CheckElementReadPermission($elementID)
	{
		if (!(IsModuleInstalled('iblock')
			&& IsModuleInstalled('webdav')
			&& CModule::IncludeModule('iblock')
			&& CModule::IncludeModule('webdav')))
		{
			return false;
		}

		$arElement = self::GetElement($elementID);
		if(!$arElement)
		{
			return false;
		}

		$arIblock = self::GetIBlock($arElement['IBLOCK_ID']);
		if(!$arIblock)
		{
			return false;
		}

		return $arIblock->CheckWebRights(
			'',
			array(
				'action' => 'read',
				'arElement' =>
					array(
						'ID' => $elementID,
						'item_id' => $elementID,
						'is_dir' => false,
						'not_found' => false
					)
			),
			false
		);
	}

	public static function GetElementInfo($elementID, $checkPermissions = true)
	{
		if (!(IsModuleInstalled('iblock')
			&& IsModuleInstalled('webdav')
			&& CModule::IncludeModule('iblock')
			&& CModule::IncludeModule('webdav')))
		{
			return array();
		}

		if($checkPermissions && !self::CheckElementReadPermission($elementID))
		{
			return array();
		}

		$arElement = self::GetElement($elementID);
		if(!$arElement)
		{
			return array();
		}

		$template = CWebDavIblock::LibOptions('lib_paths', true, $arElement['IBLOCK_ID']);
		if(!is_string($template))
		{
			$template = '';
		}

		$showUrl = self::PrepareUrl($template, $arElement);
		$viewUrl = str_replace('view', 'historyget', $showUrl);

		$editUrl = $deleteUrl = (strlen($showUrl) > 0 && !preg_match('/\/$/', $showUrl)) ? $showUrl.'/' : $showUrl;
		$editUrl = str_replace('view', 'edit', $editUrl).'EDIT/';
		$deleteUrl = preg_match('/\/docs\/shared\//i', $deleteUrl) ? '' : str_replace('view', 'edit', $deleteUrl).'DELETE_DROPPED/';

		$size = '';
		$dbSize = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $arElement['ID'], array(), array('CODE' => 'WEBDAV_SIZE'));
		if ($dbSize && $arSize=$dbSize->Fetch())
		{
			$size = CFile::FormatSize($arSize['VALUE']);
		}

		return array(
			'ID' => $elementID,
			'NAME' => $arElement['NAME'],
			'EDIT_URL' => $editUrl,
			'VIEW_URL' => $viewUrl,
			'DELETE_URL' => $deleteUrl,
			'SHOW_URL' => $showUrl,
			'SIZE' => $size
		);
	}

	private static function PrepareUrl($template, &$arElement)
	{
		if (!(IsModuleInstalled('iblock')
			&& CModule::IncludeModule('iblock')))
		{
			return '';
		}

		$template = strval($template);
		if($template === '' || !is_array($arElement))
		{
			return '';
		}

		$elementID = isset($arElement['ID']) ? intval($arElement['ID']) : 0;
		$authorID = isset($arElement['CREATED_BY']) ? intval($arElement['CREATED_BY']) : 0;

		$socnetGroupID = 0;
		$dbNav = CIBlockSection::GetNavChain($arElement['IBLOCK_ID'], $arElement['IBLOCK_SECTION_ID']);
		if($arSection = $dbNav->Fetch())
		{
			$socnetGroupID = isset($arSection['SOCNET_GROUP_ID']) ? intval($arSection['SOCNET_GROUP_ID']) : 0;
		}

		$url = $template;

		$url = str_replace(
			array(
				'#ELEMENT_ID#',
				'#element_id#',
				'#ID#',
				'#id#'
			),
			$elementID,
			$url
		);

		$url = str_replace(
			array(
				'#SOCNET_USER_ID#',
				'#socnet_user_id#',
				'#USER_ID#',
				'#user_id#'
			),
			$authorID,
			$url
		);

		$url = str_replace(
			array(
				'#SOCNET_GROUP_ID#',
				'#socnet_group_id#',
				'#GROUP_ID#',
				'#group_id#',
			),
			$socnetGroupID,
			$url
		);

		$url = str_replace(
			array(
				'#SOCNET_OBJECT#',
				'#socnet_object#'
			),
			$socnetGroupID > 0 ? 'group' : 'user',
			$url
		);

		$url = str_replace(
			array(
				'#SOCNET_OBJECT_ID#',
				'#socnet_object_id#'
			),
			$socnetGroupID > 0 ? $socnetGroupID : $authorID,
			$url
		);

		return str_replace(array("///","//"),"/", $url);
	}

	public static function GetElementFileID($elementID)
	{
		if (!(IsModuleInstalled('iblock')
			&& IsModuleInstalled('webdav')
			&& CModule::IncludeModule('iblock')
			&& CModule::IncludeModule('webdav')))
		{
			return 0;
		}

		$elementID = intval($elementID);

		$dbElement = CIBlockElement::GetList(
			array(),
			array('ID' => $elementID),
			false,
			false,
			array('IBLOCK_ID')
		);

		$arElement = $dbElement ? $dbElement->Fetch() : null;
		return $arElement ? self::GetIBlock($arElement['IBLOCK_ID'])->GetHistoryFileID($elementID) : 0;
	}

	public static function MakeElementFileArray($elementID)
	{
		if (!(IsModuleInstalled('iblock')
			&& IsModuleInstalled('webdav')
			&& CModule::IncludeModule('iblock')
			&& CModule::IncludeModule('webdav')))
		{
			return 0;
		}

		$elementID = intval($elementID);

		$arElement = self::GetElement($elementID);
		if(!$arElement)
		{
			return null;
		}

		$fileID = self::GetIBlock($arElement['IBLOCK_ID'])->GetHistoryFileID($elementID);
		if($fileID <= 0)
		{
			return null;
		}

		$arRawFile = CFile::MakeFileArray($fileID);
		if(is_array($arRawFile) && !empty($arElement['NAME']))
		{
			$arRawFile['name'] = $arElement['NAME'];
		}

		return $arRawFile;
	}

	public static function GetPaths()
	{
		if (!(IsModuleInstalled('webdav')
			&& CModule::IncludeModule('webdav')))
		{
			return array();
		}

		$event = self::GetSocNetEvent();
		if(!$event)
		{
			return array();
		}

		return array(
			'PATH_TO_FILES' => isset($event->arPath['PATH_TO_FILES'])
				? $event->arPath['PATH_TO_FILES'] : '',
			'ELEMENT_UPLOAD_URL' => isset($event->arPath['ELEMENT_UPLOAD_URL'])
				? $event->arPath['ELEMENT_UPLOAD_URL'] : '',
			'ELEMENT_SHOW_INLINE_URL' => isset($event->arPath['ELEMENT_SHOW_INLINE_URL'])
				? $event->arPath['ELEMENT_SHOW_INLINE_URL'] : ''
		);
	}

	public static function OnWebDavFileDelete($arEventArgs)
	{
		$elementInfo = is_array($arEventArgs) && isset($arEventArgs['ELEMENT']) ? $arEventArgs['ELEMENT'] : null;
		if(!is_array($elementInfo))
		{
			return;
		}

		$elementID = isset($elementInfo['id']) ? intval($elementInfo['id']) : 0;
		if($elementID <= 0)
		{
			return;
		}

		CCrmActivity::HandleStorageElementDeletion(CCrmActivityStorageType::WebDav, $elementID);
	}

	public static function SaveEmailAttachment($arFile, $siteID)
	{
		if (!(IsModuleInstalled('iblock')
			&& CModule::IncludeModule('iblock')))
		{
			return false;
		}

		$siteID = strval($siteID);
		if($siteID === '')
		{
			if(!(defined('ADMIN_SECTION') && ADMIN_SECTION))
			{
				$siteID = SITE_ID;
			}
			else
			{
				$dbSites = CSite::GetList($by = 'sort', $order = 'desc', array('DEF' => 'Y'));
				while($arSite = $dbSites->Fetch())
				{
					$siteID = $arSite['LID'];
				}
			}
		}

		if($siteID === '')
		{
			return false;
		}

		if(!isset(self::$SHARED_FILES_INFO[$siteID]))
		{
			self::$SHARED_FILES_INFO[$siteID] = array();
		}

		$info = self::$SHARED_FILES_INFO[$siteID];

		$blockID = 0;
		if(isset($info['IBLOCK_ID']))
		{
			$blockID = $info['IBLOCK_ID'];
		}
		else
		{
			$sharedFilesSettings = unserialize(COption::GetOptionString('webdav', 'shared_files', ''));
			if(isset($sharedFilesSettings[$siteID]))
			{
				$siteSettings = $sharedFilesSettings[$siteID];
				$blockID = isset($siteSettings['id']) ? intval($siteSettings['id']) : 0;
			}

			if($blockID <= 0)
			{
				$dbIBlock = CIBlock::GetList(array(), array('XML_ID' => "shared_files_{$siteID}", 'TYPE' => 'library'));
				if ($arIBlock = $dbIBlock->Fetch())
				{
					$blockID = $arIBlock['ID'];
				}
			}

			self::$SHARED_FILES_INFO[$siteID]['IBLOCK_ID'] = $blockID;
		}


		if($blockID <= 0)
		{
			return false;
		}

		$blockSectionID = 0;
		if(isset($info['IBLOCK_SECTION_ID']))
		{
			$blockSectionID = $info['IBLOCK_SECTION_ID'];
		}
		else
		{
			$blockSection = new CIBlockSection();
			$dbSections = $blockSection->GetList(array(), array('XML_ID' => 'CRM_EMAIL_ATTACHMENTS', 'IBLOCK_ID'=> $blockID, 'CHECK_PERMISSIONS' => 'N'), false, array('ID'));
			$arSection = $dbSections->Fetch();
			if(is_array($arSection))
			{
				$blockSectionID = intval($arSection['ID']);
			}

			if($blockSectionID <= 0)
			{
				$dbSite = CSite::GetByID($siteID);
				$arSite = $dbSite->Fetch();
				IncludeModuleLangFile(__FILE__, $arSite && isset($arSite['LANGUAGE_ID']) ? $arSite['LANGUAGE_ID'] : false);

				$sectionName = GetMessage('CRM_WEBDAV_EMAIL_SECTION');
				if($sectionName === '')
				{
					$sectionName = 'E-mail Attachments';
				}

				$blockSectionID = $blockSection->Add(
					array(
						'IBLOCK_ID' => $blockID,
						'ACTIVE' => 'Y',
						'NAME' => $sectionName,
						'IBLOCK_SECTION_ID' => 0,
						'CHECK_PERMISSIONS' => 'N',
						'XML_ID' => 'CRM_EMAIL_ATTACHMENTS'
					)
				);
			}

			self::$SHARED_FILES_INFO[$siteID]['IBLOCK_SECTION_ID'] = $blockSectionID;
		}

		$elementName = $arFile['ORIGINAL_NAME'];
		$fileInfo = pathinfo($elementName);
		$element = new CIBlockElement();
		$alreadyExists = false;
		$i = 0;
		do
		{
			if($alreadyExists)
			{
				$i++;
				$elementName  = isset($fileInfo['extension']) ? "{$fileInfo['filename']}_{$i}.{$fileInfo['extension']}" : "{$fileInfo['filename']}_{$i}";
			}

			$dbRes = $element->GetList(array(), array('=NAME' => $elementName, 'IBLOCK_ID'=> $blockID, 'IBLOCK_SECTION_ID'=> $blockSectionID), false, array('nTopCount'=>1), array('ID'));
			$arRes = $dbRes ? $dbRes->Fetch() : false;
			$alreadyExists = $arRes !== false;
		} while($alreadyExists);

		$arFields = array(
			'ACTIVE' => 'Y',
			'IBLOCK_ID' => $blockID,
			'IBLOCK_SECTION_ID' => $blockSectionID,
			'NAME' => $elementName,
			//'TAGS' => '',
			//'MODIFIED_BY' => $GLOBALS['USER']->GetID(),
			//'PREVIEW_TEXT_TYPE' => 'html',
			//'PREVIEW_TEXT' => '',
			'WF_COMMENTS' => '',
			'PROPERTY_VALUES' => array(
				'FILE' => $arFile,
				'WEBDAV_SIZE' => $arFile['FILE_SIZE']
			),
		);
		return $element->Add($arFields, false, true, false);
	}
}