<?
IncludeModuleLangFile(__FILE__);

class CUserTypeWebdavElement
{
	static $UF_TYPE_BLOG_POST = 'BLOG_POST';
	static $UF_EID_BLOG_POST = 'UF_BLOG_POST_FILE';
	static $UF_TYPE_BLOG_COMMENT = 'BLOG_COMMENT';
	static $UF_EID_BLOG_COMMENT = 'UF_BLOG_COMMENT_FILE';
	static $UF_MOVED = array();

	function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => "webdav_element",
			"CLASS_NAME" => __CLASS__,
			"DESCRIPTION" => GetMessage("USER_TYPE_WEBDAV_FILE_DESCRIPTION"),
			"BASE_TYPE" => "int",
		);
	}

	function GetDBColumnType($arUserField)
	{
		global $DB;
		switch(strtolower($DB->type))
		{
			case "mysql":
				return "int(18)";
			case "oracle":
				return "number(18)";
			case "mssql":
				return "int";
		}
	}

	function PrepareSettings($arUserField)
	{
		$iblockID = intval($arUserField["SETTINGS"]["IBLOCK_ID"]);
		$sectionID = intval($arUserField["SETTINGS"]["SECTION_ID"]);

		return array(
			"IBLOCK_ID" => $iblockID,
			"SECTION_ID" => $sectionID,
		);
	}

	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if($bVarsFromForm)
			$iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
		elseif(is_array($arUserField))
			$iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
		else
			$iblock_id = "";

		if(CModule::IncludeModule('iblock'))
		{
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_WEBDAV_FILE_IBLOCK_ID").':</td>
				<td>
					'.GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"].'[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"].'[IBLOCK_ID]', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"').'
				</td>
			</tr>
			';
		}
		else
		{
			$result .= '
			<tr>
				<td>'.GetMessage("USER_TYPE_WEBDAV_FILE_IBLOCK_ID").':</td>
				<td>
					<input type="text" size="6" name="'.$arHtmlControl["NAME"].'[IBLOCK_ID]" value="'.htmlspecialcharsbx($value).'">
				</td>
			</tr>
			';
		}

		/*if($bVarsFromForm)
			$SECTION_ID = $GLOBALS[$arHtmlControl["NAME"]]["SECTION_ID"] === "Y"? "Y": "N";
		elseif(is_array($arUserField))
			$SECTION_ID = $arUserField["SETTINGS"]["SECTION_ID"] === "Y"? "Y": "N";
		else
			$SECTION_ID = "N";*/

		if($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
		elseif(is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		else
			$value = "";

		return $result;
	}

	function GetEditFormHTML($arUserField, $arHtmlControl)
	{
		return "&nbsp;";
	}

	function GetFilterHTML($arUserField, $arHtmlControl)
	{
		return '&nbsp;';
	}

	function GetAdminListViewHTML($arUserField, $arHtmlControl)
	{
		return "&nbsp;";
	}

	function GetAdminListEditHTML($arUserField, $arHtmlControl)
	{
		return "&nbsp;";
	}

	function GetAdminListEditHTMLMulty($arUserField, $arHtmlControl)
	{
		return "&nbsp;";
	}

	function OnSearchIndex($arUserField)
	{
		$res = '';

		if (CModule::IncludeModule('iblock'))
		{
			if(is_array($arUserField["VALUE"]))
				$val = $arUserField["VALUE"];
			else
				$val = array($arUserField["VALUE"]);

			$val = array_filter($val, "intval");
			if (count($val))
			{
				$arBFile = array();
				$arFileName = array();
				$dbElements = CIBlockElement::GetList(array(), array('ID'=>$val), false, false, array('ID', 'NAME', 'IBLOCK_ID', 'IBLOCK_SECTION_ID'));
				if ($dbElements)
				{
					while ($arElement = $dbElements->Fetch())
					{
						if (self::_isDropped($arElement['IBLOCK_ID'], $arElement['IBLOCK_SECTION_ID']))
						{
							$dbFile = CIBlockElement::GetProperty($arElement['IBLOCK_ID'], $arElement['ID'], array(), array('CODE' => 'FILE'));
							if ($dbFile && $arFile = $dbFile->Fetch())
							{
								if ($arFile['VALUE'] > 0)
								{
									$arBFile[] = $arFile['VALUE'];
									$arFileName[] = $arElement['NAME'];
								}
								CSearch::DeleteIndex("socialnetwork", $arElement['ID']);
							}
						}
					}
				}

				if(count($arBFile))
				{
					$arBFile = array_map(array("CUserTypeFile", "__GetFileContent"), $arBFile);
					$res = implode("\r\n", $arFileName);
					$res .= "\r\n" . implode("\r\n", $arBFile);
				}
			}
		}
		return $res;
	}

	static function CheckRights($id)
	{
		$id = intval($id);
		if($id > 0)
		{
			$dbWDFile = CIBlockElement::GetList(array(), array('ID' => $id), false, false, array('IBLOCK_ID'));
			if ($dbWDFile && ($arWDFile = $dbWDFile->Fetch()))
			{
				$iBlockID = intval($arWDFile['IBLOCK_ID']); 
				$resT = CWebDavIblock::CheckUserIBlockPermission("element_read", CWebDavIblock::OBJ_TYPE_ELEMENT, $iBlockID, $id);
				if($resT)
				{
					return true;
				}
			}
		}
		return false;	
	}
	
	function CheckFields($arUserField, $value)
	{
		static $arRootID = array();

		$fileExists = false;
		$arError = array();
		if (! self::_checkRequiredModules())
			$arError[] = array('id' => 'WD_ERR_MODULES', 'text' =>GetMessage('WD_ERR_MODULES'));
		$arFile = self::_fileUnserialize($value);
		if ($arFile === false)
			$arError[] = array('id' => 'WD_ERR_PARSE_FILE', 'text' =>GetMessage('WD_ERR_PARSE_FILE'));
		
		if(!self::CheckRights($arFile['id']))
		{
			$arError[] = array('id' => 'WD_ERR_IBLOCK404', 'text' =>GetMessage('WD_ERR_IBLOCK404'));
		}
				
		if ((sizeof($arError) <= 0) && isset($arFile['dest_section'])) // move to section
		{
			$ibe = new CIBlockElement();
			$dbWDFile = $ibe->GetList(array(), array('ID' => $arFile['id']), false, false, array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'IBLOCK_ID'));
			if ($dbWDFile && $arWDFile = $dbWDFile->Fetch())
			{
				$arFile['iblock'] = $arWDFile['IBLOCK_ID'];
				if ($arFile['dest_iblock'] != $arWDFile['IBLOCK_ID'])
				{
					$dbIBlock = CIBlock::GetList(array(), array('ID' => $arFile['dest_iblock'], 'CHECK_PERMISSIONS' => 'N'));
					if ($dbIBlock && $arIBlock=$dbIBlock->Fetch())
					{
						$arFile['iblock'] = $arIBlock['ID'];
					}
					else
					{
						$arError[] = array('id' => 'WD_ERR_IBLOCK404', 'text' =>GetMessage('WD_ERR_IBLOCK404'));
					}
				}
				$arFile['section'] = $arWDFile['IBLOCK_SECTION_ID'];
				if (($arFile['dest_section'] != $arWDFile['IBLOCK_SECTION_ID']) || ($arFile['iblock'] != $arWDFile['IBLOCK_ID']))
				{
					$arFile['section'] = false;
					if ($arFile['dest_section'] === 0)
					{
						$arFile['section'] = 0;
					}
					else
					{
						$dbWDSection = CIBlockSection::GetList(
							array(),
							array(
								'ID' => $arFile['dest_section'],
								'IBLOCK_ID' => $arFile['iblock'],
								'CHECK_PERMISSIONS' => 'Y'
							)
						);
						if ($dbWDSection && $arWDSection = $dbWDSection->Fetch())
						{
							$arFile['section'] = $arWDSection['ID'];
						}
						else
						{
							$dbWDSection = CIBlockSection::GetList(
								array(),
								array(
									'ID' => $arFile['dest_section'],
									'IBLOCK_ID' => $arFile['iblock'],
									'CHECK_PERMISSIONS' => 'N'
								)
							);
							if ($dbWDSection && $arWDSection = $dbWDSection->Fetch())
							{
								$arError[] = array('id' => 'WD_ERR_SECTION403', 'text' => GetMessage('WD_ERR_SECTION403'));
							}
							else
							{
								$arError[] = array('id' => 'WD_ERR_SECTION404', 'text' => GetMessage('WD_ERR_SECTION404'));
							}
						}
					}
					if (sizeof($arError) <= 0)
					{
						$arFileExistSearch = array(
								'NAME' => $arWDFile['NAME'],
								'IBLOCK_ID' => $arFile['dest_iblock'],
								'SECTION_ID' => $arFile['dest_section']
							);
						$dbFileExist = CIBlockElement::GetList( array(), $arFileExistSearch, false, false, array('ID', 'PROPERTY_FILE'));
						if ($dbFileExist && $arFileExists = $dbFileExist->Fetch())
						{
							$fileExists = $arFileExists;
							//$arError[] = array('id' => 'WD_ERR_FILE_EXISTS', 'text' =>GetMessage('WD_ERR_FILE_EXISTS'));
						}
					}
				}

				if (sizeof($arError) <= 0)
				{
					if ($fileExists === false)
					{
						if ($arFile['iblock'] != $arWDFile['IBLOCK_ID'])
						{
							$newID = CWebDavIblock::_move_from_iblock_to_iblock($arWDFile['ID'], $arFile['iblock'], $arFile['section']);
							if (!$newID)
							{
								$arError[] = array('id' => 'WD_ERR_ELEMENT_MOVE', 'text' => GetMessage('WD_ERR_PARSE_FILE'));
							}
							else
							{
								self::$UF_MOVED[$arWDFile['ID']] = $newID;
							}
						}
						elseif( $arFile['section'] != $arWDFile['IBLOCK_SECTION_ID'] )
						{
							if (! $ibe->Update($arFile['id'], array('IBLOCK_SECTION' => $arFile['dest_section'])))
							{
								$arError[] = array('id' => 'WD_ERR_IBLOCK_ELEMENT_UPDATE', 'text' => $ibe->LAST_MESSAGE);
							}
						}
					}
					else
					{
						if (!isset($arRootID[$arFile['iblock']]))
						{
							$userIBlockID = CWebDavIblock::LibOptions('user_files', false, SITE_ID);
							$userIBlockID = $userIBlockID['id'];

							$groupIBlockID = CWebDavIblock::LibOptions('group_files', false, SITE_ID);
							$groupIBlockID = $groupIBlockID['id'];
							if ($arFile['iblock'] == $userIBlockID || $arFile['iblock'] == $groupIBlockID)
							{
								$dbChain = CIBlockSection::GetNavChain($arFile['iblock'], $arFile['section']);
								if ($dbChain && $arChain = $dbChain->Fetch())
									$arRootID[$arFile['iblock']] = $arChain['ID'];
							}
							else
							{
								$arRootID[$arFile['iblock']] = false;
							}
						}

						$rootID = $arRootID[$arFile['iblock']];
						if ($arFile['iblock'] == $userIBlockID)
						{
							$dbSocNetSection = CIBlockSection::GetList(array(), array('ID' => $rootID));
							if ($dbSocNetSection && $arSocNetSection = $dbSocNetSection->Fetch())
							{
								$ob = new CWebDavIblock($arFile['iblock'], '',
									array(
										"ROOT_SECTION_ID" => $rootID,
										'DOCUMENT_TYPE' => array("webdav", "CIBlockDocumentWebdavSocnet", "iblock_".$arFile['iblock']."_user_".$arSocNetSection['CREATED_BY'])
									)
								);
							}
						}
						elseif ($arFile['iblock'] == $groupIBlockID)
						{
							$dbSocNetSection = CIBlockSection::GetList(array(), array('ID' => $rootID));
							if ($dbSocNetSection && $arSocNetSection = $dbSocNetSection->Fetch())
							{
								$ob = new CWebDavIblock($arFile['iblock'], '',
									array(
										"ROOT_SECTION_ID" => $rootID,
										'DOCUMENT_TYPE' => array("webdav", "CIBlockDocumentWebdavSocnet", "iblock_".$arFile['iblock']."_group_".$arSocNetSection['SOCNET_GROUP_ID'])
									)
								);
							}
						}
						else
						{
							$ob = new CWebDavIblock($arFile['iblock'], '', array());
						}

						$dbFileNew = $ibe->GetList(
							array(),
							array(
								'ID' => $arWDFile['ID'],
								'IBLOCK_ID' => $arWDFile['IBLOCK_ID'],
							), false, false, array('ID', 'PROPERTY_FILE'));
						if ($dbFileNew && $arFileNew = $dbFileNew->Fetch())
						{
							$fileNew = $arFileNew;
							//$arError[] = array('id' => 'WD_ERR_FILE_EXISTS', 'text' =>GetMessage('WD_ERR_FILE_EXISTS'));
						}

						$cFile = CFile::MakeFileArray($fileNew['PROPERTY_FILE_VALUE']);

						$options = array(
							"new" => false, 
							"FILE_NAME" => $arWDFile['NAME'], 
							"IBLOCK_ID" => $arFile['iblock'],
							"IBLOCK_SECTION_ID" => $arFile['section'],
							"ELEMENT_ID" => $fileExists['ID'],
							"arFile" => $cFile
						);


						$GLOBALS["DB"]->StartTransaction();

						if (!$ob->put_commit($options))
						{
							$arError[] = array(
								"id" => "error_put",
								"text" => $ob->LAST_ERROR);
							$GLOBALS["DB"]->Rollback();
						}
						else
						{
							$GLOBALS["DB"]->Commit();
							self::$UF_MOVED[$arWDFile['ID']] = $options['ELEMENT_ID'];
						}
					}
				}
			}
		}

		return $arError;
	}

	function OnBeforeSave($arUserField, $value)
	{
		$value = intval($value);

		if ($value > 0)
		{
			if (isset(self::$UF_MOVED[$value]))
				$value = self::$UF_MOVED[$value];
		}
		else
		{
			$value = '';
		}

		return $value;
	}

	function _checkRequiredModules()
	{
		return (CModule::IncludeModule('iblock') && CModule::IncludeModule('webdav'));
	}

	function _fileUnserialize($sIndex)
	{
		$arFile = array();

		$arIndex = explode('|', $sIndex);
		if (sizeof($arIndex) < 1)
			return false;

		$arFile['id'] = intval($arIndex[0]);
		if (sizeof($arIndex) > 1)
			$arFile['dest_section'] = intval($arIndex[1]);
		if (sizeof($arIndex) > 2)
			$arFile['dest_iblock'] = intval($arIndex[2]);

		if ($arFile['id'] <= 0)
			return false;

		return $arFile;
	}

	function _deleteDroppedFiles($arFiles)
	{
		if (!is_array($arFiles) || sizeof($arFiles) <= 0)
			return false;

		static $arRootID = array();
		$ibe = new CIBlockElement();
		$dbWDFile = $ibe->GetList(array(), array('ID' => $arFiles), false, false, array('ID', 'NAME', 'IBLOCK_SECTION_ID', 'IBLOCK_ID'));
		if ($dbWDFile)
		{
			while ($arWDFile = $dbWDFile->Fetch())
			{
				$id = $arWDFile['ID'];

				if (!isset($arRootID[$arWDFile['IBLOCK_ID']]))
				{
					$dbChain = CIBlockSection::GetNavChain($arWDFile['IBLOCK_ID'], $arWDFile['IBLOCK_SECTION_ID']);
					if ($dbChain && $arChain = $dbChain->Fetch())
						$arRootID[$arWDFile['IBLOCK_ID']] = $arChain['ID'];
				}
				if (isset($arRootID[$arWDFile['IBLOCK_ID']]))
				{
					$rootID = $arRootID[$arWDFile['IBLOCK_ID']];
					$ob = new CWebDavIblock($arWDFile['IBLOCK_ID'], '', array("ROOT_SECTION_ID" => $rootID)); // for user .dropped files
					$ob->DeleteDroppedFile($id);
				}
			}
		}
		foreach($arRootID as $iblockID=>$rootID)
		{
			$ob = new CWebDavIblock($iblockID, '', array("ROOT_SECTION_ID" => $rootID));
			$ob->CleanUpDropped();
		}
	}

	function _updateRights($files, $rights)
	{
		static $arIBlock = array();
		static $op_X = 'element_rights_edit';
		static $arTasks = null;

		if (!is_array($rights) || sizeof($rights) <= 0)
			return false;
		if ($files===null || $files===false)
			return false;
		if (!is_array($files))
			$files = array($files);
		if (sizeof($files) <= 0)
			return false;
		if (!CModule::IncludeModule('iblock'))
			return false;

		$arFiles = array();
		foreach($files as $id)
		{
			$id = intval($id);
			if (intval($id) > 0)
				$arFiles[] = $id;
		}

		if (sizeof($arFiles) <= 0)
			return false;

		if ($arTasks == null)
			$arTasks = CWebDavIblock::GetTasks();

		$i=0;
		$arRights = array();
		$curUserID = 'U'.$GLOBALS['USER']->GetID();
		foreach($rights as $right)
		{
			if ($curUserID == $right) // do not override owner's rights
				continue;
			$arRights['n'.$i++] = array(
				'GROUP_CODE' => $right,
				'TASK_ID' => $arTasks['R']
			);
		}

		$ibe = new CIBlockElement();
		$dbWDFile = $ibe->GetList(array(), array('ID' => $arFiles, 'SHOW_NEW' => 'Y'), false, false, array('ID', 'NAME', 'SECTION_ID', 'IBLOCK_ID', 'WF_NEW'));
		if ($dbWDFile)
		{
			while ($arWDFile = $dbWDFile->Fetch())
			{
				$id = $arWDFile['ID'];

				if ($arWDFile['WF_NEW'] == 'Y')
					$ibe->Update($id, array('BP_PUBLISHED' => 'Y'));

				if (CIBlock::GetArrayByID($arWDFile['IBLOCK_ID'], "RIGHTS_MODE") === "E")
				{
					$dropped = false;
					$ibRights = CWebDavIblock::_get_ib_rights_object('ELEMENT', $id, $arWDFile['IBLOCK_ID']);

					// change rights on comment files if they are 'attached to the post'
					$dropped = self::_isDropped($arWDFile['IBLOCK_ID'], $arWDFile['IBLOCK_SECTION_ID']);

					if (!$ibRights->UserHasRightTo($arWDFile['IBLOCK_ID'], $id, $op_X) && !$dropped)
					{
						continue;
					}

					$ibRights->SetRights($arRights);
				}
			}
		}
	}

	function _isDropped($iblockID, $sectionID)
	{
		if (! CModule::IncludeModule('iblock'))
			return false;

		$dropped = false;
		$dbChain = CIBlockSection::GetNavChain($iblockID, $sectionID);
		if ($dbChain)
		{
			if ($arChain = $dbChain->Fetch())
			{
				if ($arChain["NAME"] == ".Dropped")
				{
					$dropped = true;
				}
				else
				{
					if ($arChain = $dbChain->Fetch())
					{
						if ($arChain["NAME"] == ".Dropped")
						{
							$dropped = true;
						}
					}
				}
			}
		}
		return $dropped;
	}

	function _getBlogPostCommentFiles($postID)
	{
		$arCommentID = array();
		$dbComments = CBlogComment::GetList(array(), array("POST_ID" => intval($postID)), false, false, array("ID"));
		if ($dbComments)
		{
			while($arComment = $dbComments->Fetch())
			{
				$arCommentID[] = $arComment['ID'];
			}
		}

		$arFiles = array();
		foreach($arCommentID as $commentID)
		{
			$entity_type = self::$UF_TYPE_BLOG_COMMENT;
			$entity_id = self::$UF_EID_BLOG_COMMENT;
			$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entity_type, $commentID);
			if (isset($arUF[$entity_id]) &&
				is_array($arUF[$entity_id]['VALUE']) &&
				(sizeof($arUF[$entity_id]['VALUE']) > 0))
			{
				$arFiles += $arUF[$entity_id]['VALUE'];
			}
		}
		return $arFiles;
	}

	function OnEntityAdd($entity_type, $entity_id, $element_id, $arParams)
	{
		if (!isset($arParams[$entity_id]))
			return;
		$arRights = array();
		$arFiles = array();

		if (isset($arParams['SC_PERM']) && is_array($arParams['SC_PERM']) && (sizeof($arParams['SC_PERM']) > 0))
		{
			$arRights = $arParams['SC_PERM'];
		}
		$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entity_type, $element_id);
		if (isset($arUF[$entity_id]) &&
			is_array($arUF[$entity_id]['VALUE']) &&
			(sizeof($arUF[$entity_id]['VALUE']) > 0))
		{
			$arFiles = $arUF[$entity_id]['VALUE'];
		}

		self::_updateRights($arFiles, $arRights);
	}

	function OnBeforeEntityDelete($entity_type, $entity_id, $element_id)
	{
		$arUF = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields($entity_type, $element_id);

		if (isset($arUF[$entity_id]) &&
			is_array($arUF[$entity_id]['VALUE']) &&
			(sizeof($arUF[$entity_id]['VALUE']) > 0))
		{
			$arFiles = array();
			foreach($arUF[$entity_id]['VALUE'] as $id)
			{
				$id = intval($id);
				if (intval($id) > 0)
					$arFiles[] = $id;
			}
			self::_deleteDroppedFiles($arFiles);
		}
	}

	function OnPostAdd($id, &$arParams)
	{
		return self::OnEntityAdd(self::$UF_TYPE_BLOG_POST, self::$UF_EID_BLOG_POST, $id, $arParams);
	}

	function OnPostUpdate($id, &$arParams)
	{
		// we only extend file permissions, 
		// also we cannot figure out if the current file permissions are originally from file or from post
		// some users who had access earlier might already download the file,

		self::OnPostAdd($id, $arParams);
		if (isset($arParams['SC_PERM']))
		{
			$arFiles = self::_getBlogPostCommentFiles($id);
			if (sizeof($arFiles) <= 0)
				return;

			$arRights = array();
			if (is_array($arParams['SC_PERM']) && (sizeof($arParams['SC_PERM']) > 0))
			{
				$arRights = $arParams['SC_PERM'];
			}

			self::_updateRights($arFiles, $arRights);
		}
	}
	function OnBeforePostDelete($id)
	{
		return self::OnBeforeEntityDelete(self::$UF_TYPE_BLOG_POST, self::$UF_EID_BLOG_POST, $id);
	}

	function OnCommentAdd($id, &$arParams)
	{
		return self::OnEntityAdd(self::$UF_TYPE_BLOG_COMMENT, self::$UF_EID_BLOG_COMMENT, $id, $arParams);
	}

	function OnCommentUpdate($id, &$arParams)
	{
		return self::OnCommentAdd($id, $arParams);
	}

	function OnBeforeCommentDelete($id)
	{
		return self::OnBeforeEntityDelete(self::$UF_TYPE_BLOG_COMMENT, self::$UF_EID_BLOG_COMMENT, $id);
	}

	function GetPublicViewHTML($arUserField, $id, $params = "")
	{
		if ($params != '' && is_string($params) && preg_match_all("/(width|height)=(\d+)/is", $params, $matches))
			$params = array_combine($matches[1], $matches[2]);
		ob_start();
			CWebDavInterface::UserFieldViewThumb(
				$arParams = array("arUserField" => $arUserField),
				$arResult = array("VALUE" => array($id)),
				null,
				array($id => $params));
		return ob_get_clean();
	}

	//RegisterModuleDependences('blog', 'OnPostAdd', 'webdav', 'CUserTypeWebdavElement', 'OnPostAdd');
	//RegisterModuleDependences('blog', 'OnPostUpdate', 'webdav', 'CUserTypeWebdavElement', 'OnPostUpdate');
	//RegisterModuleDependences('blog', 'OnBeforePostDelete', 'webdav', 'CUserTypeWebdavElement', 'OnBeforePostDelete');
	//RegisterModuleDependences("blog", "OnCommentAdd", 'webdav', 'CUserTypeWebdavElement', "OnCommentAdd");
	//RegisterModuleDependences("blog", "OnCommentUpdate", 'webdav', 'CUserTypeWebdavElement', "OnCommentUpdate");
	//RegisterModuleDependences("blog", "OnBeforeCommentDelete", 'webdav', 'CUserTypeWebdavElement', "OnBeforeCommentDelete");
}
?>
