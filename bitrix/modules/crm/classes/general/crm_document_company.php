<?
if (!CModule::IncludeModule('bizproc'))
	return;

IncludeModuleLangFile(dirname(__FILE__)."/crm_document.php");

class CCrmDocumentCompany extends CCrmDocument
	implements IBPWorkflowDocument
{
	static public function GetDocumentFields($documentType)
	{
		$arDocumentID = self::GetDocumentInfo($documentType.'_0');
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		__IncludeLang($_SERVER['DOCUMENT_ROOT'].BX_ROOT.'/components/bitrix/crm.'.strtolower($arDocumentID['TYPE']).'.edit/lang/'.LANGUAGE_ID.'/component.php');

		$arResult = array(
			'ID' => array(
				'Name' => GetMessage('CRM_FIELD_ID'),
				'Type' => 'int',
				'Filterable' => true,
				'Editable' => false,
				'Required' => false,
			),
			'TITLE' => array(
				'Name' => GetMessage('CRM_FIELD_TITLE'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => true,
			),
			'COMPANY_TYPE' => array(
				'Name' => GetMessage('CRM_FIELD_COMPANY_TYPE'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('COMPANY_TYPE'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'INDUSTRY' => array(
				'Name' => GetMessage('CRM_FIELD_INDUSTRY'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('INDUSTRY'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'EMPLOYEES' => array(
				'Name' => GetMessage('CRM_FIELD_EMPLOYEES'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('EMPLOYEES'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'REVENUE' => array(
				'Name' => GetMessage('CRM_FIELD_REVENUE'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'CURRENCY_ID' => array(
				'Name' => GetMessage('CRM_FIELD_CURRENCY_ID'),
				'Type' => 'select',
				'Options' => CCrmCurrencyHelper::PrepareListItems(),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'COMMENTS' => array(
				'Name' => GetMessage('CRM_FIELD_COMMENTS'),
				'Type' => 'text',
				'Filterable' => false,
				'Editable' => true,
				'Required' => false,
			),
			'EMAIL' => array(
				'Name' => GetMessage('CRM_FIELD_EMAIL'),
				'Type' => 'email',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'PHONE' => array(
				'Name' => GetMessage('CRM_FIELD_PHONE'),
				'Type' => 'phone',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'WEB' => array(
				'Name' => GetMessage('CRM_FIELD_WEB'),
				'Type' => 'web',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'IM' => array(
				'Name' => GetMessage('CRM_FIELD_MESSENGER'),
				'Type' => 'im',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS' => array(
				'Name' => GetMessage('CRM_FIELD_ADDRESS'),
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ADDRESS_LEGAL' => array(
				'Name' => GetMessage('CRM_FIELD_ADDRESS_LEGAL'),
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'BANKING_DETAILS' => array(
				'Name' => GetMessage('CRM_FIELD_BANKING_DETAILS'),
				'Type' => 'text',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			"OPENED" => array(
				"Name" => GetMessage("CRM_FIELD_OPENED"),
				"Type" => "bool",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"LEAD_ID" => array(
				"Name" => GetMessage("CRM_FIELD_LEAD_ID"),
				"Type" => "int",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"ORIGINATOR_ID" => array(
				"Name" => GetMessage("CRM_FIELD_ORIGINATOR_ID"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"ORIGIN_ID" => array(
				"Name" => GetMessage("CRM_FIELD_ORIGIN_ID"),
				"Type" => "string",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
			),
			"CONTACT_ID" => array(
				"Name" => GetMessage("CRM_FIELD_CONTACT_ID"),
				"Type" => "UF:crm",
				"Options" => array('CONTACT' => 'Y'),
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
			),
		);

		$ar =  CCrmFieldMulti::GetEntityTypeList();
		foreach ($ar as $typeId => $arFields)
		{
			$arResult[$typeId.'_PRINTABLE'] = array(
				'Name' => GetMessage("CRM_FIELD_MULTI_".$typeId)." (".GetMessage("CRM_FIELD_BP_TEXT").")",
				'Type' => 'string',
				"Filterable" => true,
				"Editable" => false,
				"Required" => false,
			);
			foreach ($arFields as $valueType => $valueName)
			{
				$arResult[$typeId.'_'.$valueType] = array(
					'Name' => $valueName,
					'Type' => 'string',
					"Filterable" => true,
					"Editable" => false,
					"Required" => false,
				);
				$arResult[$typeId.'_'.$valueType.'_PRINTABLE'] = array(
					'Name' => $valueName." (".GetMessage("CRM_FIELD_BP_TEXT").")",
					'Type' => 'string',
					"Filterable" => true,
					"Editable" => false,
					"Required" => false,
				);
			}
		}

		global $USER_FIELD_MANAGER;
		$CCrmUserType = new CCrmUserType($USER_FIELD_MANAGER, 'CRM_COMPANY');
		$CCrmUserType->AddBPFields($arResult, false);

		return $arResult;
	}

	static public function CreateDocument($parentDocumentId, $arFields)
	{
		global $DB;
		$arDocumentID = self::GetDocumentInfo($parentDocumentId);
		if ($arDocumentID == false)
			$arDocumentID['TYPE'] = $parentDocumentId;

		$arFieldsPropertyValues = array();

		if (isset($arFields['PHONE']))
			$arFields['FM']['PHONE'] = $arFields['PHONE']['PHONE'];
		if (isset($arFields['EMAIL']))
			$arFields['FM']['EMAIL'] = $arFields['EMAIL']['EMAIL'];
		if (isset($arFields['IM']))
			$arFields['FM']['IM'] = $arFields['IM']['IM'];
		if (isset($arFields['WEB']))
			$arFields['FM']['WEB'] = $arFields['WEB']['WEB'];

		unset($arFields['PHONE'], $arFields['EMAIL'], $arFields['IM'], $arFields['WEB']);

		$arDocumentFields = self::GetDocumentFields($arDocumentID['TYPE']);

		$arKeys = array_keys($arFields);
		foreach ($arKeys as $key)
		{
			if (!array_key_exists($key, $arDocumentFields))
				continue;

			$arFields[$key] = (is_array($arFields[$key]) && !CBPHelper::IsAssociativeArray($arFields[$key])) ? $arFields[$key] : array($arFields[$key]);

			if ($arDocumentFields[$key]["Type"] == "user")
			{
				$ar = array();
				foreach ($arFields[$key] as $v1)
				{
					if (substr($v1, 0, strlen("user_")) == "user_")
					{
						$ar[] = substr($v1, strlen("user_"));
					}
					else
					{
						$a1 = self::GetUsersFromUserGroup($v1, "COMPANY_0");
						foreach ($a1 as $a11)
							$ar[] = $a11;
					}
				}

				$arFields[$key] = $ar;
			}
			elseif ($arDocumentFields[$key]["Type"] == "select" && substr($key, 0, 3) == "UF_")
			{
				$db = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_COMPANY", "FIELD_NAME" => $key));
				if ($ar = $db->Fetch())
				{
					$arV = array();
					$db = CUserTypeEnum::GetList($ar);
					while ($ar = $db->GetNext())
						$arV[$ar["XML_ID"]] = $ar["ID"];

					foreach ($arFields[$key] as &$value)
					{
						if (array_key_exists($value, $arV))
							$value = $arV[$value];
					}
				}
			}
			elseif ($arDocumentFields[$key]["Type"] == "file")
			{
				foreach ($arFields[$key] as &$value)
					$value = CFile::MakeFileArray($value);
			}
			elseif ($arDocumentFields[$key]["Type"] == "S:HTML")
			{
				foreach ($arFields[$key] as &$value)
					$value = array("VALUE" => $value);
			}

			if (!$arDocumentFields[$key]["Multiple"] && is_array($arFields[$key]))
			{
				if (count($arFields[$key]) > 0)
				{
					$a = array_values($arFields[$key]);
					$arFields[$key] = $a[0];
				}
				else
				{
					$arFields[$key] = null;
				}
			}
		}

		if (isset($arFields['CONTACT_ID']) && !is_array($arFields['CONTACT_ID']))
			$arFields['CONTACT_ID'] = array($arFields['CONTACT_ID']);

		$DB->StartTransaction();

		$CCrmEntity = new CCrmCompany(false);
		$id = $CCrmEntity->Add($arFields);

		if (!$id || $id <= 0)
		{
			$DB->Rollback();
			throw new Exception($CCrmEntity->LAST_ERROR);
		}

		if (COption::GetOptionString("crm", "start_bp_within_bp", "N") == "Y")
		{
			$CCrmBizProc = new CCrmBizProc('COMPANY');
			if (false === $CCrmBizProc->CheckFields(false, true))
				throw new Exception($CCrmBizProc->LAST_ERROR);

			if ($id && $id > 0 && !$CCrmBizProc->StartWorkflow($id))
			{
				$DB->Rollback();
				throw new Exception($CCrmBizProc->LAST_ERROR);
				$id = false;
			}
		}

		if ($id && $id > 0)
			$DB->Commit();

		return $id;
	}

	static public function UpdateDocument($documentId, $arFields)
	{
		global $DB;

		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$dbDocumentList = CCrmCompany::GetList(
			array(),
			array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
			array('ID')
		);

		$arResult = $dbDocumentList->Fetch();
		if (!$arResult)
			throw new Exception(GetMessage('CRM_DOCUMENT_ELEMENT_IS_NOT_FOUND'));

		if (isset($arFields['PHONE']))
			$arFields['FM']['PHONE'] = $arFields['PHONE']['PHONE'];
		if (isset($arFields['EMAIL']))
			$arFields['FM']['EMAIL'] = $arFields['EMAIL']['EMAIL'];
		if (isset($arFields['IM']))
			$arFields['FM']['IM'] = $arFields['IM']['IM'];
		if (isset($arFields['WEB']))
			$arFields['FM']['WEB'] = $arFields['WEB']['WEB'];
		unset($arFields['PHONE'], $arFields['EMAIL'], $arFields['IM'], $arFields['WEB']);

		$arDocumentFields = self::GetDocumentFields($arDocumentID['TYPE']);

		$arKeys = array_keys($arFields);
		foreach ($arKeys as $key)
		{
			if (!array_key_exists($key, $arDocumentFields))
				continue;

			$arFields[$key] = (is_array($arFields[$key]) && !CBPHelper::IsAssociativeArray($arFields[$key])) ? $arFields[$key] : array($arFields[$key]);

			if ($arDocumentFields[$key]["Type"] == "user")
			{
				$ar = array();
				foreach ($arFields[$key] as $v1)
				{
					if (substr($v1, 0, strlen("user_")) == "user_")
					{
						$ar[] = substr($v1, strlen("user_"));
					}
					else
					{
						$a1 = self::GetUsersFromUserGroup($v1, $documentId);
						foreach ($a1 as $a11)
							$ar[] = $a11;
					}
				}

				$arFields[$key] = $ar;
			}
			elseif ($arDocumentFields[$key]["Type"] == "select" && substr($key, 0, 3) == "UF_")
			{
				$db = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_COMPANY", "FIELD_NAME" => $key));
				if ($ar = $db->Fetch())
				{
					$arV = array();
					$db = CUserTypeEnum::GetList($ar);
					while ($ar = $db->GetNext())
						$arV[$ar["XML_ID"]] = $ar["ID"];

					foreach ($arFields[$key] as &$value)
					{
						if (array_key_exists($value, $arV))
							$value = $arV[$value];
					}
				}
			}
			elseif ($arDocumentFields[$key]["Type"] == "file")
			{
				foreach ($arFields[$key] as &$value)
					$value = CFile::MakeFileArray($value);
			}
			elseif ($arDocumentFields[$key]["Type"] == "S:HTML")
			{
				foreach ($arFields[$key] as &$value)
					$value = array("VALUE" => $value);
			}

			if (!$arDocumentFields[$key]["Multiple"] && is_array($arFields[$key]))
			{
				if (count($arFields[$key]) > 0)
				{
					$a = array_values($arFields[$key]);
					$arFields[$key] = $a[0];
				}
				else
				{
					$arFields[$key] = null;
				}
			}
		}

		if (isset($arFields['CONTACT_ID']) && !is_array($arFields['CONTACT_ID']))
			$arFields['CONTACT_ID'] = array($arFields['CONTACT_ID']);

		$DB->StartTransaction();
		$CCrmEntity = new CCrmCompany(false);

		$res = $CCrmEntity->Update($arDocumentID['ID'], $arFields);

		if (!$res)
		{
			$DB->Rollback();
			throw new Exception($CCrmEntity->LAST_ERROR);
		}

		if (COption::GetOptionString("crm", "start_bp_within_bp", "N") == "Y")
		{
			$CCrmBizProc = new CCrmBizProc('COMPANY');
			if (false === $CCrmBizProc->CheckFields($arDocumentID['ID'], true))
				throw new Exception($CCrmBizProc->LAST_ERROR);

			if ($res && !$CCrmBizProc->StartWorkflow($arDocumentID['ID']))
			{
				$DB->Rollback();
				throw new Exception($CCrmBizProc->LAST_ERROR);
			}
		}

		if ($res)
			$DB->Commit();
	}
}
