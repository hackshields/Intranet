<?
IncludeModuleLangFile(__FILE__);
if (!CModule::IncludeModule("bizproc"))
	return;

class CBPWebDavCanUserOperateOperation extends CBPCanUserOperateOperation
{
	const DeleteDocument = 10;
}

class CIBlockDocumentWebdav extends CIBlockDocument
{
	/**
	* ����� �� ���� ��������� ���������� ������ �� �������� ��������� � ���������������� �����.
	*
	* @param string $documentId - ��� ���������.
	* @return string - ������ �� �������� ��������� � ���������������� �����.
	*/
	public function GetDocumentAdminPage($documentId)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$db_res = CIBlockElement::GetList(
			array(),
			array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y"),
			false,
			false,
			array("ID", "CODE", "EXTERNAL_ID", "IBLOCK_ID", "IBLOCK_TYPE_ID", "IBLOCK_SECTION_ID"));
		if ($db_res && $arElement = $db_res->Fetch())
		{
			$db_res = CIBlock::GetList(array(), array("ID" => $arElement["IBLOCK_ID"], "SITE_ID" => SITE_ID, "CHECK_PERMISSIONS" => "N"));
			if ($db_res && $arIblock = $db_res->Fetch())
			{
				$arr = array(
					"LANG_DIR" => SITE_ID,
					"ID" => $documentId,
					"CODE" => $arElement["CODE"],
					"EXTERNAL_ID" => $arElement["EXTERNAL_ID"],
					"IBLOCK_TYPE_ID" => $arIblock["IBLOCK_TYPE_ID"],
					"IBLOCK_ID" => $arIblock["IBLOCK_ID"],
					"IBLOCK_CODE" => $arIblock["IBLOCK_CODE"],
					"IBLOCK_EXTERNAL_ID" => $arIblock["IBLOCK_EXTERNAL_ID"],
					"SECTION_ID" => $arElement["IBLOCK_SECTION_ID"]
					);
				return CIBlock::ReplaceDetailUrl($arIblock["DETAIL_PAGE_URL"], $arr, true, "E");
			}
		}
		return null;
	}
	/**
	* ����� ���������� �������� (����) ��������� � ���� �������������� ������� ���� array(���_�������� => ��������, ...). ���������� ��� ��������, ������� ���������� ����� GetDocumentFields.
	*
	* @param string $documentId - ��� ���������.
	* @return array - ������ ������� ���������.
	*/
	public function GetDocument($documentId)
	{
		if (!function_exists("__get_user_fullname"))
		{
			function __get_user_fullname($ID)
			{
				$ID = intVal($ID);
				$result = "";
				if ($ID > 0)
				{
					if (!array_key_exists("User".$ID, $GLOBALS["WEBDAV"]["CACHE"]))
					{
						$db = CUser::GetByID($ID);
						if ($db && $res = $db->GetNext())
						{
							$result = CUser::FormatName(CSite::GetNameFormat(false), $res);
							$result = (empty($result) ? $res["LOGIN"] : $result);
						}
						$GLOBALS["WEBDAV"]["CACHE"]["User".$ID] = $result;
					}
					else
					{
						$result = $GLOBALS["WEBDAV"]["CACHE"]["User".$ID];
					}
				}
				return $result;
			}
		}

		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$arResult = null;

		$dbDocumentList = CIBlockElement::GetList(
			array(),
			array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y")
		);
		if ($objDocument = $dbDocumentList->GetNextElement())
		{
			$arDocumentFields = $objDocument->GetFields();
			$arDocumentProperties = $objDocument->GetProperties();

			foreach ($arDocumentFields as $fieldKey => $fieldValue)
			{
				if ($fieldKey == "MODIFIED_BY" || $fieldKey == "CREATED_BY")
				{
					$arResult[$fieldKey] = "user_".$fieldValue;
					$arResult[$fieldKey."_PRINTABLE"] = __get_user_fullname($fieldValue);
				}
				else
				{
					if (substr($fieldKey, 0, 1) != "~")
						$arResult[$fieldKey] = $fieldValue;
				}
			}

			foreach ($arDocumentProperties as $propertyKey => $propertyValue)
			{
				$valueNew = null;

				if (strlen($propertyValue["USER_TYPE"]) > 0)
				{
					$arPropertyValue = (is_array($propertyValue["VALUE"]) ? $propertyValue["VALUE"] : array($propertyValue["VALUE"]));
					if ($propertyValue["USER_TYPE"] == "UserID")
					{
						$arUserName = array();
						foreach ($arPropertyValue as $userId)
						{
							$valueNew[$userId] = "user_".$userId;
							$sUserName = __get_user_fullname($userId);
							if (!empty($sUserName))
								$arUserName[] = $sUserName;
						}
						$arResult["PROPERTY_".$propertyKey."_PRINTABLE"] = implode(", ", $arUserName);
					}
					else
					{
						$valueNew = $propertyValue["VALUE"];
					}
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "L")
				{
					$arPropertyValue = $propertyValue["VALUE"];
					$arPropertyKey = $propertyValue["VALUE_ENUM_ID"];
					if (!is_array($arPropertyValue))
					{
						$arPropertyValue = array($arPropertyValue);
						$arPropertyKey = array($arPropertyKey);
					}

					for ($i = 0, $cnt = count($arPropertyValue); $i < $cnt; $i++)
						$valueNew[$arPropertyKey[$i]] = $arPropertyValue[$i];
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "F")
				{
					$arPropertyValue = $propertyValue["VALUE"];
					if (!is_array($arPropertyValue))
						$arPropertyValue = array($arPropertyValue);

					foreach ($arPropertyValue as $v)
					{
						$ar = CFile::GetFileArray($v);
						if ($ar)
							$valueNew[intval($v)] = $ar["SRC"];
					}
				}
				else
				{
					$valueNew = $propertyValue["VALUE"];
				}

				$arResult["PROPERTY_".$propertyKey] = $valueNew;
			}
		}
		unset($arResult["WF_NEW"]);
		return $arResult;
	}

	/**
	* ����� ���������� ������ ������� (�����), ������� ����� �������� ������� ����. ����� GetDocument ���������� �������� ������� ��� ��������� ���������.
	*
	* @param string $documentType - ��� ���������.
	* @return array - ������ ������� ���� array(���_�������� => array("NAME" => ��������_��������, "TYPE" => ���_��������), ...).
	*/
	public function GetDocumentFields($documentType)
	{
		$iblockId = intval(substr($documentType, strlen("iblock_")));
		if ($iblockId <= 0)
			throw new CBPArgumentOutOfRangeException("documentType", $documentType);

		$arResult = array(
			"ID" => array(
				"Name" => GetMessage("IBLOCK_FIELD_ID"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"TIMESTAMP_X" => array(
				"Name" => GetMessage("IBLOCK_FIELD_TIMESTAMP_X"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"MODIFIED_BY" => array(
				"Name" => GetMessage("IBD_FIELD_MODYFIED").GetMessage("IBD_FIELD_IDENTIFICATOR"),
				"Type" => "user",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"MODIFIED_BY_PRINTABLE" => array(
				"Name" => GetMessage("IBD_FIELD_MODYFIED").GetMessage("IBD_FIELD_NAME_LASTNAME"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"DATE_CREATE" => array(
				"Name" => GetMessage("IBLOCK_FIELD_DATE_CREATE"),
				"Type" => "datetime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"CREATED_BY" => array(
				"Name" => GetMessage("IBD_FIELD_CREATED").GetMessage("IBD_FIELD_IDENTIFICATOR"),
				"Type" => "user",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"CREATED_BY_PRINTABLE" => array(
				"Name" => GetMessage("IBD_FIELD_CREATED").GetMessage("IBD_FIELD_NAME_LASTNAME"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"IBLOCK_ID" => array(
				"Name" => GetMessage("IBLOCK_FIELD_IBLOCK_ID"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"ACTIVE" => array(
				"Name" => GetMessage("IBLOCK_FIELD_ACTIVE"),
				"Type" => "bool",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"BP_PUBLISHED" => array(
				"Name" => GetMessage("IBLOCK_FIELD_BP_PUBLISHED"),
				"Type" => "bool",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			),
			"NAME" => array(
				"Name" => GetMessage("IBLOCK_FIELD_FILE_NAME"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
			),
			"FILE_SIZE" => array(
				"Name" => GetMessage("IBLOCK_FIELD_FILE_SIZE"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			),
			"PREVIEW_TEXT" => array(
				"Name" => GetMessage("IBLOCK_FIELD_FILE_DESCRIPTION"),
				"Type" => "text",
				"Filterable" => false,
				"Editable" => true,
				"Required" => false,
			)
		);

		$arKeys = array_keys($arResult);
		foreach ($arKeys as $key)
			$arResult[$key]["Multiple"] = false;

		$dbProperties = CIBlockProperty::GetList(
			array("sort" => "asc", "name" => "asc"),
			array("IBLOCK_ID" => $iblockId)
		);
		while ($arProperty = $dbProperties->Fetch())
		{
			if (strlen(trim($arProperty["CODE"])) > 0)
				$key = "PROPERTY_".$arProperty["CODE"];
			else
				$key = "PROPERTY_".$arProperty["ID"];

			$arResult[$key] = array(
				"Name" => $arProperty["NAME"],
				"Filterable" => ($arProperty["FILTRABLE"] == "Y"),
				"Editable" => true,
				"Required" => ($arProperty["IS_REQUIRED"] == "Y"),
				"Multiple" => ($arProperty["MULTIPLE"] == "Y"),
			);

			if (strlen($arProperty["USER_TYPE"]) > 0)
			{
				if ($arProperty["USER_TYPE"] == "UserID")
				{
					$arResult[$key]["Name"] .= GetMessage("IBD_FIELD_IDENTIFICATOR");
					$arResult[$key]["Type"] = "user";
					$arResult[$key."_PRINTABLE"] = array(
						"Name" => $arProperty["NAME"].GetMessage("IBD_FIELD_NAME_LASTNAME"),
						"Type" => "string",
						"Filterable" => ($arProperty["FILTRABLE"] == "Y"),
						"Editable" => false,
						"Required" => ($arProperty["IS_REQUIRED"] == "Y"),
						"Multiple" => ($arProperty["MULTIPLE"] == "Y"),
					);
				}
				else
					$arResult[$key]["Type"] = "string";
			}
			elseif ($arProperty["PROPERTY_TYPE"] == "L")
			{
				$arResult[$key]["Type"] = "select";

				$arResult[$key]["Options"] = array();
				$dbPropertyEnums = CIBlockProperty::GetPropertyEnum($arProperty["ID"]);
				while ($arPropertyEnum = $dbPropertyEnums->GetNext())
					$arResult[$key]["Options"][$arPropertyEnum["ID"]] = $arPropertyEnum["VALUE"];
			}
			elseif ($arProperty["PROPERTY_TYPE"] == "N")
			{
				$arResult[$key]["Type"] = "int";
			}
			elseif ($arProperty["PROPERTY_TYPE"] == "F")
			{
				$arResult[$key]["Type"] = "file";
			}
			elseif ($arProperty["PROPERTY_TYPE"] == "S")
			{
				$arResult[$key]["Type"] = "string";
			}
			else
			{
				$arResult[$key]["Type"] = "string";
			}
		}

		return $arResult;
	}

	/**
	* ����� ���������� ������ ������������ ���������, ���������� ��� ���������� � ���������. �� ����� ������� �������� ����������������� ������� RecoverDocumentFromHistory.
	*
	* @param string $documentId - ��� ���������.
	* @return array - ������ ���������.
	*/
	public function GetDocumentForHistory($documentId, $historyIndex, $update = false)
	{
		$documentId = intval($documentId);
		if ($documentId <= 0)
			throw new CBPArgumentNullException("documentId");

		$arResult = null;

		$arDocFilter = array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y");
		$dbDoc = CIBlockElement::GetList(
			array(),
			$arDocFilter,
			false,
			false,
			array('IBLOCK_ID')
		);
		if ($arDoc = $dbDoc->Fetch())
		{
			$arDocFilter['IBLOCK_ID'] = $arDoc['IBLOCK_ID']; // required for iblock 2.0
		}
		
		$dbDocumentList = CIBlockElement::GetList(
			array(),
			$arDocFilter
		);
		if ($objDocument = $dbDocumentList->GetNextElement())
		{
			$arDocumentFields = $objDocument->GetFields();
			$arDocumentProperties = $objDocument->GetProperties();

			$arResult["NAME"] = $arDocumentFields["~NAME"];

			$arResult["FIELDS"] = array();
			foreach ($arDocumentFields as $fieldKey => $fieldValue)
			{
				if ($fieldKey == "~PREVIEW_PICTURE" || $fieldKey == "~DETAIL_PICTURE")
				{
					$arResult["FIELDS"][substr($fieldKey, 1)] = CBPDocument::PrepareFileForHistory(
						array("webdav", "CIBlockDocumentWebdav", $documentId),
						$fieldValue,
						$historyIndex
					);
				}
				elseif (substr($fieldKey, 0, 1) == "~")
				{
					$arResult["FIELDS"][substr($fieldKey, 1)] = $fieldValue;
				}
			}

			$arResult["PROPERTIES"] = array();
			foreach ($arDocumentProperties as $propertyKey => $propertyValue)
			{
				if (strlen($propertyValue["USER_TYPE"]) > 0)
				{
					$arResult["PROPERTIES"][$propertyKey] = array(
						"VALUE" => $propertyValue["VALUE"],
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "L")
				{
					$arResult["PROPERTIES"][$propertyKey] = array(
						"VALUE" => $propertyValue["VALUE_ENUM_ID"],
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "F" && $propertyKey == 'FILE') // primary webdav file
				{
					$arDocID = $documentId;
					if (!is_array($documentId))
						$arDocID = array("webdav", "CIBlockDocumentWebdav", $documentId);

					$arResult['PROPERTIES'][$propertyKey] = CWebdavDocumentHistory::GetFileForHistory($arDocID, $propertyValue, $historyIndex);

					if ($update)
						$historyGlueState = CWebdavDocumentHistory::GetHistoryState($arDocID, null, null, array('CHECK_TIME'=>'Y'));
					else
						$historyGlueState = CWebdavDocumentHistory::GetHistoryState($arDocID, null, null, array('NEW'=>'Y', 'CHECK_TIME'=>'Y'));

					$arResult['PROPERTIES'][$propertyKey]['HISTORYGLUE'] = $historyGlueState;
				}
				elseif ($propertyValue["PROPERTY_TYPE"] == "F")
				{
					$arResult["PROPERTIES"][$propertyKey] = array(
						"VALUE" => CBPDocument::PrepareFileForHistory(
							array("webdav", "CIBlockDocumentWebdav", $documentId),
							$propertyValue["VALUE"],
							$historyIndex
						),
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
				else
				{
					$arResult["PROPERTIES"][$propertyKey] = array(
						"VALUE" => $propertyValue["VALUE"],
						"DESCRIPTION" => $propertyValue["DESCRIPTION"]
					);
				}
			}
		}

		return $arResult;
	}

	/**
	* ����� ������������ ��������� ��������. ��� ������������� ���������� ����������� ������� ���� "��������_OnUnlockDocument", ������� �������� ���������� ���������� ��� ���������.
	*
	* @param string $documentId - ��� ���������
	* @param string $workflowId - ��� �������� ������
	* @return bool - ���� ������� �������������� ��������, �� ������������ true, ����� - false.
	*/
	public function UnlockDocument($documentId, $workflowId)
	{
		global $DB;

		$strSql = "
			SELECT * FROM b_iblock_element_lock
			WHERE IBLOCK_ELEMENT_ID = ".intval($documentId)."
		";
		$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
		if($z->Fetch())
		{
			$strSql = "
				DELETE FROM b_iblock_element_lock
				WHERE IBLOCK_ELEMENT_ID = ".intval($documentId)."
				AND (LOCKED_BY = '".$DB->ForSQL($workflowId, 32)."' OR '".$DB->ForSQL($workflowId, 32)."' = '')
			";
			$z = $DB->Query($strSql, false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			$result = $z->AffectedRowsCount();
		}
		else
		{//Success unlock when there is no locks at all
			$result = 1;
		}

		if ($result > 0)
		{
			$db_events = GetModuleEvents("webdav", "CIBlockDocumentWebdav_OnUnlockDocument");
			while ($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, array("webdav", "CIBlockDocumentWebdav", $documentId));
		}

		return $result > 0;
	}

	function GetUserGroups($documentType = null, $documentId = null, $userId = 0)
	{
		static $arUserGroups = array();
		static $arDocumentInfo = array();
		$documentType = ($documentType == null || empty($documentType) ? null : $documentType);
		if ($documentType != null)
			$documentType = trim(is_array($documentType) ? $documentType[2] : $documentType);

		$userId = intVal($userId);
		$documentIdReal = $documentId = (is_array($documentId) ? $documentId[2] : $documentId);
		$documentId = intVal($documentId);

		if (!array_key_exists($userId, $arUserGroups))
			$arUserGroups[$userId] = ($userId == $GLOBALS["USER"]->GetID() ?
				$GLOBALS["USER"]->GetUserGroupArray() : CUser::GetUserGroup($userId));

		$result = $arUserGroups[$userId];

		if ($documentId > 0 && $userId > 0)
		{
			if (!array_key_exists($documentId, $arDocumentInfo))
			{
				$dbElementList = CIBlockElement::GetList(
					array(),
					array("ID" => $documentId, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y"),
					false,
					false,
					array("ID", "IBLOCK_ID", "CREATED_BY")
				);
				$arDocumentInfo[$documentId] = $dbElementList->Fetch();
			}
			if ($arDocumentInfo[$documentId]["CREATED_BY"] == $userId)
				$result[] = "author";
		}
		return $result;
	}

	static function GetIBRights($type, $iblockID, $id = 0)
	{
		static $arRightModes = array();

		if (!isset($arRightModes[$iblockID]))
			$arRightModes[$iblockID] = CIBlock::GetArrayByID($iblockID, "RIGHTS_MODE");

		if (($type == 'SECTION') && ($id == 0))
			$type = 'IBLOCK';

		$result = false;
		if ($arRightModes[$iblockID] === "E")
		{
			if ($type == 'IBLOCK')
				$id = $iblockID;
			$result = CWebDavIblock::GetPermissions($type, $id, $iblockID);
		}
		else
		{
			$result = CIBlock::GetPermission($iblockID);
		}

		if ($GLOBALS['USER']->CanDoOperation('webdav_change_settings'))
			$result = 'X';

		return $result;
	}

	/**
	* ����� ��������� ����� �� ���������� �������� ��� �������� ����������. ����������� �������� 0 - �������� ������ �������� ������, 1 - ������ �������� ������, 2 - ����� �������� ��������, 3 - ����� �������� ��������.
	*
	* @param int $operation - ��������.
	* @param int $userId - ��� ������������, ��� �������� ����������� ����� �� ���������� ��������.
	* @param string $documentId - ��� ���������, � �������� ����������� ��������.
	* @param array $arParameters - ������������� ������ ��������������� ����������. ������������ ��� ����, ����� �� ������������ ������ �� ����������� ��������, ������� ��� �������� �� ������ ������ ������. ������������ �������� ����� ������� DocumentStates - ������ ��������� ������� ������� ������� ���������, WorkflowId - ��� �������� ������ (���� ��������� ��������� �������� �� ����� ������� ������). ������ ����� ���� �������� ������� ������������� �������.
	* @return bool
	*/
	function CanUserOperateDocument($operation, $userId, $documentId, $arParameters = array())
	{
		static $arElements = array();
		$documentId = trim($documentId);
		if (strlen($documentId) <= 0)
			return false;
		// ���� ��� ���� �� �������, � ��� �����, �� ������ ��� ��������� � ������ ��������
		if (!array_key_exists("IBlockId", $arParameters) && (!array_key_exists("IBlockPermission", $arParameters) ||
			!array_key_exists("DocumentStates", $arParameters))
			||
				(!array_key_exists("CreatedBy", $arParameters) && !array_key_exists("AllUserGroups", $arParameters))
			||
				($operation == CBPWebDavCanUserOperateOperation::ReadDocument))
		{
			if (!array_key_exists($documentId, $arElements))
			{
				$dbElementList = CIBlockElement::GetList(
					array(),
					array("ID" => $documentId, "SHOW_NEW" => "Y", "SHOW_HISTORY" => "Y"),
					false,
					false,
					array("ID", "IBLOCK_ID", "CREATED_BY", "WF_STATUS_ID", "WF_PARENT_ELEMENT_ID")
				);
				$arElements[$documentId] = $dbElementList->Fetch();
			}
			$arElement = $arElements[$documentId];

			if (!$arElement)
				return false;

			$arParameters["IBlockId"] = $arElement["IBLOCK_ID"];
			$arParameters["CreatedBy"] = $arElement["CREATED_BY"];
			$arParameters["Published"] = ((intVal($arElement["WF_STATUS_ID"]) == 1 && intVal($arElement["WF_PARENT_ELEMENT_ID"]) <= 0) ? "Y" : "N");
		}

		// ���� ��� ���� �� �������, �� ������ ����������� �����
		if (!array_key_exists("IBlockPermission", $arParameters))
		{
			//$arParameters["IBlockPermission"] = CIBlock::GetPermission($arParameters["IBlockId"], $userId);
			$arParameters["IBlockPermission"] = CIBlockDocumentWebdav::GetIBRights('ELEMENT', $arParameters["IBlockId"], $documentId);
		}

		if (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_read") < "R")
			return false;
		elseif ($operation != CBPWebDavCanUserOperateOperation::DeleteDocument && CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") >= "W")
			return true;
		elseif ($operation == CBPWebDavCanUserOperateOperation::DeleteDocument && CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_delete") >= "X")
			return true;
		elseif ($operation == CBPWebDavCanUserOperateOperation::ReadDocument && $arParameters["Published"] == "Y")
			return true;

		// ���� �� ���, �� ����������� ����� ����� U

		// ���� ��� ���� �� �������, �� ������ ������ ������������
		$userId = intval($userId);
		if (!array_key_exists("AllUserGroups", $arParameters))
		{
			if (!array_key_exists("UserGroups", $arParameters))
				$arParameters["UserGroups"] = CUser::GetUserGroup($userId);

			$arParameters["AllUserGroups"] = $arParameters["UserGroups"];
			if ($userId == $arParameters["CreatedBy"])
				$arParameters["AllUserGroups"][] = "Author";
		}

		// ���� ��� ���� �� �������, �� ������ ������� ������� ���������
		if (!array_key_exists("DocumentStates", $arParameters))
		{
			$arParameters["DocumentStates"] = CBPDocument::GetDocumentStates(
				array("webdav", "CIBlockDocumentWebdav", "iblock_".$arParameters["IBlockId"]),
				array("webdav", "CIBlockDocumentWebdav", $documentId)
			);
		}

		// ���� ����� ��������� ������ ��� ������ �������� ������
		if (array_key_exists("WorkflowId", $arParameters) && !empty($arParameters["WorkflowId"]))
		{
			if (array_key_exists($arParameters["WorkflowId"], $arParameters["DocumentStates"]))
				$arParameters["DocumentStates"] = array($arParameters["WorkflowId"] => $arParameters["DocumentStates"][$arParameters["WorkflowId"]]);
			else
				return false;
		}

		$arAllowableOperations = CBPDocument::GetAllowableOperations(
			$userId,
			$arParameters["AllUserGroups"],
			$arParameters["DocumentStates"]
		);

		// $arAllowableOperations == null - ����� �� �������� ���������
		// $arAllowableOperations == array() - � �������� ��� ���������� ��������
		// $arAllowableOperations == array("read", ...) - ���������� ��������

		if (!is_array($arAllowableOperations))
			return false;

		$r = false;

		switch ($operation)
		{
			case CBPWebDavCanUserOperateOperation::ViewWorkflow:
				// ����� �� �������� ������-�������� ���� ������ � �������������, ������� ��������� ������
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_read") > "U" || in_array("read", $arAllowableOperations));
				break;
			case CBPWebDavCanUserOperateOperation::StartWorkflow:
				// ����� �� ������ ������-�������� ���� � ������� ����� "W",
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_bizproc_start") > "U" || in_array("write", $arAllowableOperations));
				// ���� ����� ���������� ������ ������, �� ��������� ���
				if ($r && CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_bizproc_start") <= "U" && $arParameters["WorkflowTemplateId"] > 0)
				{
					// �������� ��� ������� ��� ����, ����� ���������� ��� �������
					// ��� ��� ��� ���������������� ��������� �� ���� ����� �� ������
					// ������-�������� ��� �������� ���� �� ������.
					if (!array_key_exists("WorkflowTemplateList".$arParameters["IBlockId"], $GLOBALS["WEBDAV"]["CACHE"]))
					{
						if (array_key_exists("WorkflowTemplateList", $arParameters) && is_array($arParameters["WorkflowTemplateList"]))
						{
							$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]] = array();
							foreach ($arParameters["WorkflowTemplateList"] as $res)
								$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]][$res["ID"]] = $res;
						}
						else
						{
							$res = array();
							$db_res = CBPWorkflowTemplateLoader::GetList(
								array(),
								array("DOCUMENT_TYPE" => array("webdav", "CIBlockDocumentWebdav", "iblock_".$arParameters["IBlockId"]), "ACTIVE"=>"Y"),
								false,
								false,
								array("ID", "NAME", "DESCRIPTION", "TEMPLATE", "PARAMETERS")
							);
							while ($arWorkflowTemplate = $db_res->GetNext())
							{
								$res[$arWorkflowTemplate["ID"]] = $arWorkflowTemplate;
							}
							$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]] = $res;
						}
					}
					$arWorkflowTemplateList = $GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]];


					if (array_key_exists($arParameters["WorkflowTemplateId"], $arWorkflowTemplateList))
					{
						$arTemplate = $arWorkflowTemplateList[$arParameters["WorkflowTemplateId"]];
						// ���� ��� ������ ���������, �� ��������� ������ ���������
						if ($arTemplate["TEMPLATE"][0]["Type"] == "StateMachineWorkflowActivity")
						{
							// �������� �������� ����������� ������� �������� ������
							if (array_key_exists($arParameters["WorkflowTemplateId"], $arParameters["DocumentStates"]))
							{
								$arDocumentStates = $arParameters["DocumentStates"][$arParameters["WorkflowTemplateId"]];
							}
							else
							{
								if (!array_key_exists("WorkflowTemplate".$arParameters["WorkflowTemplateId"], $GLOBALS["WEBDAV"]["CACHE"]))
									$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplate".$arParameters["WorkflowTemplateId"]] =
										CBPWorkflowTemplateLoader::GetTemplateState($arParameters["WorkflowTemplateId"]);
								$arDocumentStates = $GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplate".$arParameters["WorkflowTemplateId"]];
							}
							$arAllowableOperations = CBPDocument::GetAllowableOperations(
								$userId,
								$arParameters["AllUserGroups"],
								array($arParameters["WorkflowTemplateId"] => $arDocumentStates)
							);
							$r = (is_array($arAllowableOperations) && in_array("write", $arAllowableOperations));
						}
					}
				}
				break;
			case CBPWebDavCanUserOperateOperation::CreateWorkflow:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") > "U");
				break;
			case CBPWebDavCanUserOperateOperation::WriteDocument:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") > "U" || in_array("write", $arAllowableOperations));
				break;
			case CBPWebDavCanUserOperateOperation::DeleteDocument:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_delete") >= "X" || in_array("delete", $arAllowableOperations));
				break;
			case CBPWebDavCanUserOperateOperation::ReadDocument:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") > "U" || in_array("read", $arAllowableOperations) || in_array("write", $arAllowableOperations));
				break;
			default:
				$r = false;
		}

		return $r;
	}

	/**
	* ����� ��������� ����� �� ���������� �������� ��� ����������� ��������� 
	* ����. ����������� �������� 4 - ����� �������� ������� ������� ������� ��� 
	* ������� ���� ���������.
	*
	* @param int $operation - ��������.
	* @param int $userId - ��� ������������, ��� �������� ����������� ����� �� ���������� ��������.
	* @param string $documentId - ��� ���� ���������, � �������� ����������� ��������.
	* @param array $arParameters - ������������� ������ ��������������� 
	* ����������. ������������ ��� ����, ����� �� ������������ ������ �� 
	* ����������� ��������, ������� ��� �������� �� ������ ������ ������. 
	* ������������ �������� ����� ������� DocumentStates - ������ ��������� 
	* ������� ������� ������� ���������, WorkflowId - ��� �������� ������ (���� 
	* ��������� ��������� �������� �� ����� ������� ������). ������ ����� ���� 
	* �������� ������� ������������� �������.
	* @return bool
	*/
	function CanUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array())
	{
		$documentType = trim($documentType);
		if (strlen($documentType) <= 0)
			return false;

		$arParameters["IBlockId"] = intval(substr($documentType, strlen("iblock_")));

		// ���� ��� ���� �� �������, �� ������ ����������� �����
		if (!array_key_exists("IBlockPermission", $arParameters))
		{
			if (isset($arParameters['SectionId']))
			{
				$arParameters['SectionId'] = intval($arParameters['SectionId']);
				$arParameters["IBlockPermission"] = CIBlockDocumentWebdav::GetIBRights('SECTION', $arParameters["IBlockId"], $arParameters['SectionId']);
			}
			else
			{
				$arParameters["IBlockPermission"] = CIBlockDocumentWebdav::GetIBRights('IBLOCK', $arParameters["IBlockId"]);
			}
		}

		if (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_read") < "R")
			return false;
		elseif (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") >= "W")
			return true;

		// ���� �� ���, �� ����������� ����� ����� U

		// ���� ��� ���� �� �������, �� ������ ������ ������������
		$userId = intval($userId);
		if (!array_key_exists("AllUserGroups", $arParameters))
		{
			if (!array_key_exists("UserGroups", $arParameters))
				$arParameters["UserGroups"] = CUser::GetUserGroup($userId);

			$arParameters["AllUserGroups"] = $arParameters["UserGroups"];
			$arParameters["AllUserGroups"][] = "Author";
		}

		// ���� ��� ���� �� �������, �� ������ ������� ������� ���������
		if (!array_key_exists("DocumentStates", $arParameters))
		{
			$arParameters["DocumentStates"] = CBPDocument::GetDocumentStates(
				array("webdav", "CIBlockDocumentWebdav", "iblock_".$arParameters["IBlockId"]),
				null
			);
		}

		// ���� ����� ��������� ������ ��� ������ �������� ������
		if (array_key_exists("WorkflowId", $arParameters) && !empty($arParameters["WorkflowId"]))
		{
			if (array_key_exists($arParameters["WorkflowId"], $arParameters["DocumentStates"]))
				$arParameters["DocumentStates"] = array($arParameters["WorkflowId"] => $arParameters["DocumentStates"][$arParameters["WorkflowId"]]);
			else
				return false;
		}

		$arAllowableOperations = CBPDocument::GetAllowableOperations(
			$userId,
			$arParameters["AllUserGroups"],
			$arParameters["DocumentStates"]
		);

		// $arAllowableOperations == null - ����� �� �������� ���������
		// $arAllowableOperations == array() - � �������� ��� ���������� ��������
		// $arAllowableOperations == array("read", ...) - ���������� ��������
		if (!is_array($arAllowableOperations))
			return false;

		$r = false;
		switch ($operation)
		{
			case CBPCanUserOperateOperation::ViewWorkflow:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_read") > "U" || in_array("read", $arAllowableOperations));
				break;
			case CBPCanUserOperateOperation::StartWorkflow:
				// ����� �� ������ ������-�������� ���� � ������� ����� "W",
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_bizproc_start") > "U" || in_array("write", $arAllowableOperations));
				// ���� ����� ���������� ������ ������, �� ��������� ���
				if ($r && CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_bizproc_start") <= "U" && $arParameters["WorkflowTemplateId"] > 0)
				{
					// �������� ��� ������� ��� ����, ����� ���������� ��� �������
					// ��� ��� ��� ���������������� ��������� �� ���� ����� �� ������
					// ������-�������� ��� �������� ���� �� ������.
					if (!array_key_exists("WorkflowTemplateList".$arParameters["IBlockId"], $GLOBALS["WEBDAV"]["CACHE"]))
					{
						if (array_key_exists("WorkflowTemplateList", $arParameters))
						{
							$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]] = array();
							foreach ($arParameters["WorkflowTemplateList"] as $res)
								$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]][$res["ID"]] = $res;
						}
						else
						{
							$res = array();
							$db_res = CBPWorkflowTemplateLoader::GetList(
								array(),
								array("DOCUMENT_TYPE" => array("webdav", "CIBlockDocumentWebdav", "iblock_".$arParameters["IBlockId"]), "ACTIVE"=>"Y"),
								false,
								false,
								array("ID", "NAME", "DESCRIPTION", "TEMPLATE", "PARAMETERS")
							);
							while ($arWorkflowTemplate = $db_res->GetNext())
							{
								$res[$arWorkflowTemplate["ID"]] = $arWorkflowTemplate;
							}
							$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]] = $res;
						}
					}
					$arWorkflowTemplateList = $GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplateList".$arParameters["IBlockId"]];


					if (array_key_exists($arParameters["WorkflowTemplateId"], $arWorkflowTemplateList))
					{
						$arTemplate = $arWorkflowTemplateList[$arParameters["WorkflowTemplateId"]];
						// ���� ��� ������ ���������, �� ��������� ������ ���������
						if ($arTemplate["TEMPLATE"][0]["Type"] == "StateMachineWorkflowActivity")
						{
							// �������� �������� ����������� ������� �������� ������
							if (array_key_exists($arParameters["WorkflowTemplateId"], $arParameters["DocumentStates"]))
							{
								$arDocumentStates = $arParameters["DocumentStates"][$arParameters["WorkflowTemplateId"]];
							}
							else
							{
								if (!array_key_exists("WorkflowTemplate".$arParameters["WorkflowTemplateId"], $GLOBALS["WEBDAV"]["CACHE"]))
									$GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplate".$arParameters["WorkflowTemplateId"]] =
										CBPWorkflowTemplateLoader::GetTemplateState($arParameters["WorkflowTemplateId"]);
								$arDocumentStates = $GLOBALS["WEBDAV"]["CACHE"]["WorkflowTemplate".$arParameters["WorkflowTemplateId"]];
							}
							$arAllowableOperations = CBPDocument::GetAllowableOperations(
								$userId,
								$arParameters["AllUserGroups"],
								array($arParameters["WorkflowTemplateId"] => $arDocumentStates)
							);
							$r = (is_array($arAllowableOperations) && in_array("write", $arAllowableOperations));
						}
					}
				}
				break;
			case CBPCanUserOperateOperation::CreateWorkflow:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") > "U");
				break;
			case CBPCanUserOperateOperation::WriteDocument:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") > "U" || in_array("write", $arAllowableOperations));
				break;
			case CBPWebDavCanUserOperateOperation::ReadDocument:
				$r = (CWebDavIblock::CheckRight($arParameters["IBlockPermission"], "element_edit") > "U" || in_array("read", $arAllowableOperations));
				break;
			default:
				$r = false;
		}

		return $r;
	}

	/**
	* ����� ��������� ��������. �� ���� ������ ��� ��������� � ��������� ����� �����.
	*
	* @param string $documentId - ��� ���������.
	*/
	public function PublishDocument($documentId)
	{
		global $DB;
		$ID = intval($documentId);
		$db_element = CIBlockElement::GetList(array(), array("ID"=>$ID, "SHOW_HISTORY"=>"Y"), false, false,
			array(
				"ID",
				"NAME",
				"WF_PARENT_ELEMENT_ID",
			)
		);
		$PARENT_ID = 0; $arParent = array();
		if($ar_element = $db_element->Fetch())
		{
			$PARENT_ID = intval($ar_element["WF_PARENT_ELEMENT_ID"]);
			if ($PARENT_ID > 0)
			{
				CBPDocument::MergeDocuments(
					array("webdav", "CIBlockDocumentWebdav", $PARENT_ID),
					array("webdav", "CIBlockDocumentWebdav", $documentId));
				$db_res = CIBlockElement::GetList(
					array(),
					array("ID" => $PARENT_ID, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y"),
					false,
					false,
					array("IBLOCK_ID", "ID", "NAME"));
				$arParent = $db_res->Fetch();
			}
		}
		parent::PublishDocument($documentId);
		if ($PARENT_ID > 0)
		{
			CBPDocument::AddDocumentToHistory(
				array("webdav", "CIBlockDocumentWebdav", $PARENT_ID),
				str_replace(
					array("#PARENT_ID#", "#PARENT_NAME#", "#ID#", "#NAME#"),
					array($PARENT_ID, $arParent["NAME"], $documentId, $ar_element["NAME"]),
					GetMessage("IBD_TEXT_001")),
				$GLOBALS["USER"]->GetID());
		}


		if ($ar_element)
		{
			$rsEvents = GetModuleEvents("webdav", "OnBizprocPublishDocument");
			while ($arEvent = $rsEvents->Fetch())
			{
				ExecuteModuleEventEx($arEvent, array($ar_element['ID']));
			}
		}
	}
	/**
	* ����� ��������� ��������.
	*
	* @param string $documentId - ID ���������.
	* @param string $arFields - ���� ��� ������.
	*/
	public function CloneElement($ID, $arFields = array())
	{
		global $DB;
		$ID = intval($ID);

		$CHILD_ID = parent::CloneElement($ID, $arFields);
		if ($CHILD_ID > 0)
		{
			$db_res = CIBlockElement::GetList(
				array(),
				array("ID" => $ID, "SHOW_NEW"=>"Y", "SHOW_HISTORY" => "Y"),
				false,
				false,
				array("IBLOCK_ID", "ID", "NAME"));
			$arParent = $db_res->Fetch();
			CBPDocument::AddDocumentToHistory(
				array("webdav", "CIBlockDocumentWebdav", $CHILD_ID),
				str_replace(
					array("#ID#", "#NAME#", "#PARENT_ID#", "#PARENT_NAME#"),
					array($CHILD_ID, $arFields["NAME"], $ID, $arParent["NAME"]),
					GetMessage("IBD_TEXT_002")),
				$GLOBALS["USER"]->GetID());
		}
		return $CHILD_ID;
	}

	// array("1" => "������", 2 => "�����", 3 => ..., "Author" => "�����")
	public function GetAllowableUserGroups($documentType)
	{
		$documentType = trim($documentType);
		if (strlen($documentType) <= 0)
			return false;

		$iblockId = intval(substr($documentType, strlen("iblock_")));

		$arResult = array("Author" => GetMessage("IBD_DOCUMENT_AUTHOR"));

		$arRes = array(1);

		if(CIBlock::GetArrayByID($iblockId, "RIGHTS_MODE") === "E")
		{
			$obRights = new CIBlockRights($iblockId);
			foreach($obRights->GetGroups("element_bizproc_start") as $GROUP_CODE)
				if(preg_match("/^G(\\d+)\$/", $GROUP_CODE, $match))
					$arRes[] = $match[1];
		}
		else
		{
			$arGroups = CIBlock::GetGroupPermissions($iblockId);
			foreach ($arGroups as $groupId => $perm)
			{
				if ($perm >= "R")
					$arRes[] = $groupId;
			}
		}

		$dbGroupsList = CGroup::GetListEx(array("NAME" => "ASC"), array("ID" => $arRes));
		while ($arGroup = $dbGroupsList->Fetch())
			$arResult[$arGroup["ID"]] = $arGroup["NAME"];

		return $arResult;
	}

	/**
	* ����� - ������ ��������� �������� �� �������� ������������ ������� ������
	*
	* @param string $documentType - ��� ���������.
	* @param array $arDocumentStates - ������ �������� �� ���������.
	* @param array $arBizProcParametersValues - ������ ������� ���������� ��� ��������.
	* @param array $arErrors - ������ ������.
	*/
	public function StartWorkflowsParametersValidate($documentType, $arDocumentStates, &$arBizProcParametersValues, &$arErrors)
	{
		$arBizProcParametersValues = array();
		$arDocumentStates = (is_array($arDocumentStates) ? $arDocumentStates : array());

		foreach ($arDocumentStates as $arDocumentState)
		{
			if (strlen($arDocumentState["ID"]) <= 0)
			{
				$arErrorsTmp = array();
				$arBizProcParametersValues[$arDocumentState["TEMPLATE_ID"]] =
					CBPDocument::StartWorkflowParametersValidate(
						$arDocumentState["TEMPLATE_ID"],
						$arDocumentState["TEMPLATE_PARAMETERS"],
						$documentType,
						$arErrorsTmp
					);

				foreach ($arErrorsTmp as $e)
					$arErrors[] = array("id" => "bizproc_validate", "text" => $e["message"]);
			}
		}
		return empty($arErrors);
	}

	/**
	* ����� - ������ ��������� ��������, ����������� �� � ����������� �������
	*
	* @param string $documentId - ��� ���������.
	* @param array $arDocumentStates - ������ �������� �� ���������.
	* @param array $arBizProcParametersValues - ������ ������� ���������� ��� ��������.
	* @param array $arUserGroups - ������ ����� �������������.
	* @param array $arErrors - ������ ������.
	*/
	public function StartWorkflowsExecuting($documentId, $arDocumentStates, $arBizProcParametersValues, $arUserGroups, &$arErrors)
	{
		$documentId = $documentId;
		$arErrors = array();
		$arBizProcWorkflowId = array();
		$arDocumentStates = (is_array($arDocumentStates) ? $arDocumentStates : array());
		$arBizProcParametersValues = (is_array($arBizProcParametersValues) ? $arBizProcParametersValues : array());
		$arUserGroups = (is_array($arUserGroups) ? $arUserGroups : array());

		foreach ($arDocumentStates as $arDocumentState)
		{
			if (strlen($arDocumentState["ID"]) <= 0)
			{
				$arErrorsTmp = array();
				$arBizProcWorkflowId[$arDocumentState["TEMPLATE_ID"]] = CBPDocument::StartWorkflow(
					$arDocumentState["TEMPLATE_ID"],
					$documentId,
					$arBizProcParametersValues[$arDocumentState["TEMPLATE_ID"]],
					$arErrorsTmp);

				foreach ($arErrorsTmp as $e)
					$arError[] = array(
						"id" => "bizproc_start_workflow",
						"text" => $e["message"]);
			}
		}

		if (empty($arError) && intval($_REQUEST["bizproc_index"]) > 0)
		{
			if (empty($arUserGroups))
			{
				$arUserGroups = call_user_func_array(
					array($this->wfParams['DOCUMENT_TYPE'][1], "GetUserGroups"),
					array(null, $documentId, $GLOBALS["USER"]->GetID()));
			}

			$bizprocIndex = intval($_REQUEST["bizproc_index"]);
			for ($i = 1; $i <= $bizprocIndex; $i++)
			{
				$bpId = trim($_REQUEST["bizproc_id_".$i]);
				$bpTemplateId = intval($_REQUEST["bizproc_template_id_".$i]);
				$bpEvent = trim($_REQUEST["bizproc_event_".$i]);
				if (strlen($bpEvent) > 0)
				{
					if (strlen($bpId) > 0)
					{
						if (!array_key_exists($bpId, $arDocumentStates))
							continue;
					}
					else
					{
						if (!array_key_exists($bpTemplateId, $arDocumentStates))
							continue;
						$bpId = $arBizProcWorkflowId[$bpTemplateId];
					}
					$arErrorTmp = array();
					CBPDocument::SendExternalEvent(
						$bpId,
						$bpEvent,
						array("Groups" => $arUserGroups, "User" => $GLOBALS["USER"]->GetID()),
						$arErrorTmp);
					foreach ($arErrorsTmp as $e)
						$arError[] = array(
							"id" => "bizproc_send_external_event",
							"text" => $e["message"]);
				}
			}
		}
		return empty($arError);
	}

	public static function TruncateHistory($arDocType, $docID)
	{
		$maxCount = COption::GetOptionInt("webdav", "bp_history_size", 50);
		if ($maxCount <= 0)
			return;

		$documentId = array($arDocType[0], $arDocType[1], $docID);
		$history = new CBPHistoryService();
		$db_res = $history->GetHistoryList(
			array("ID" => "DESC"),
			array("DOCUMENT_ID" => $documentId),
			false,
			false,
			array("ID")
		);

		$count = 1;
		if ($db_res)
		{
			while ($arr = $db_res->Fetch())
			{
				if ($count++ > $maxCount)
					CBPHistoryService::Delete($arr["ID"], $documentId);
			}
		}
	}

	// RegisterModuleDependences("bizproc", "OnAddToHistory", "webdav", "CIBlockDocumentWebdav", "OnAddToHistory", 100);
	public static function OnAddToHistory($arParams)
	{
		$docType = $arParams['DOCUMENT_ID'];
		if (!(($docType[0] === 'webdav') && (strpos($docType[1], "Webdav") !== false)))
			return;

		CIBlockDocumentWebdav::TruncateHistory($docType, $docType[2]);
	}

	// RegisterModuleDependences("iblock", "OnAfterIBlockElementDelete", "webdav", "CIBlockDocumentWebdav", "OnAfterIBlockElementDelete", 100);
	public static function OnAfterIBlockElementDelete($arFields)
	{
		CBPDocument::OnDocumentDelete(array("webdav", "CIBlockDocumentWebdav", $arFields["ID"]), $arErrorsTmp);
		CBPDocument::OnDocumentDelete(array("webdav", "CIBlockDocumentWebdavSocnet", $arFields["ID"]), $arErrorsTmp);
	}
}
?>
