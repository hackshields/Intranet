<?
if (!CModule::IncludeModule('bizproc'))
	return;

IncludeModuleLangFile(dirname(__FILE__)."/crm_document.php");

class CCrmDocumentDeal extends CCrmDocument
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
			/*'ORIGINATOR_ID' => array(
				'Name' => GetMessage('CRM_FIELD_ORIGINATOR_ID'),
				'Type' => 'int',
				'Filterable' => true,
				'Editable' => false,
				'Required' => false,
			),*/
			'TITLE' => array(
				'Name' => GetMessage('CRM_FIELD_TITLE_DEAL'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => true,
			),
//			'PRODUCT_ID' => array(
//				'Name' => GetMessage('CRM_FIELD_PRODUCT_ID'),
//				'Type' => 'select',
//				'Options' => CCrmStatus::GetStatusListEx('PRODUCT'),
//				'Filterable' => true,
//				'Editable' => true,
//				'Required' => false,
//			),
			'OPPORTUNITY' => array(
				'Name' => GetMessage('CRM_FIELD_OPPORTUNITY'),
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
			'OPPORTUNITY_ACCOUNT' => array(
				'Name' => GetMessage('CRM_FIELD_OPPORTUNITY_ACCOUNT'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ACCOUNT_CURRENCY_ID' => array(
				'Name' => GetMessage('CRM_FIELD_ACCOUNT_CURRENCY_ID'),
				'Type' => 'select',
				'Options' => CCrmCurrencyHelper::PrepareListItems(),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'PROBABILITY' => array(
				'Name' => GetMessage('CRM_FIELD_PROBABILITY'),
				'Type' => 'string',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'ASSIGNED_BY_ID' => array(
				'Name' => GetMessage('CRM_FIELD_ASSIGNED_BY_ID'),
				'Type' => 'user',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'STAGE_ID' => array(
				'Name' => GetMessage('CRM_FIELD_STAGE_ID'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('DEAL_STAGE'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'CLOSED' => array(
				'Name' => GetMessage('CRM_FIELD_CLOSED'),
				'Type' => 'bool',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'TYPE_ID' => array(
				'Name' => GetMessage('CRM_FIELD_TYPE_ID'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('DEAL_TYPE'),
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
			'BEGINDATE' => array(
				'Name' => GetMessage('CRM_FIELD_BEGINDATE'),
				'Type' => 'datetime',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'CLOSEDATE' => array(
				'Name' => GetMessage('CRM_FIELD_CLOSEDATE'),
				'Type' => 'datetime',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'EVENT_DATE' => array(
				'Name' => GetMessage('CRM_FIELD_EVENT_DATE'),
				'Type' => 'datetime',
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'EVENT_ID' => array(
				'Name' => GetMessage('CRM_FIELD_EVENT_ID'),
				'Type' => 'select',
				'Options' => CCrmStatus::GetStatusListEx('EVENT_TYPE'),
				'Filterable' => true,
				'Editable' => true,
				'Required' => false,
			),
			'EVENT_DESCRIPTION' => array(
				'Name' => GetMessage('CRM_FIELD_EVENT_DESCRIPTION'),
				'Type' => 'text',
				'Filterable' => false,
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
			"COMPANY_ID" => array(
				"Name" => GetMessage("CRM_FIELD_COMPANY_ID"),
				"Type" => "UF:crm",
				"Options" => array('COMPANY' => 'Y'),
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
			),
		);

		/*
		$ar =  CCrmFieldMulti::GetEntityTypeList();
		foreach ($ar as $typeId => $arFields)
		{
			foreach ($arFields as $valueType => $valueName)
			{
				$arResult[$typeId.'_'.$valueType] = array(
					'Name' => $valueName,
					'Type' => 'string',
					"Filterable" => true,
					"Editable" => true,
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
		*/

		global $USER_FIELD_MANAGER;
		$CCrmUserType = new CCrmUserType($USER_FIELD_MANAGER, 'CRM_DEAL');
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
						$a1 = self::GetUsersFromUserGroup($v1, "DEAL_0");
						foreach ($a1 as $a11)
							$ar[] = $a11;
					}
				}

				$arFields[$key] = $ar;
			}
			elseif ($arDocumentFields[$key]["Type"] == "select" && substr($key, 0, 3) == "UF_")
			{
				$db = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_DEAL", "FIELD_NAME" => $key));
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

		/*if (isset($arFields['CONTACT_ID']) && !is_array($arFields['CONTACT_ID']))
			$arFields['CONTACT_ID'] = array($arFields['CONTACT_ID']);
		if (isset($arFields['COMPANY_ID']) && !is_array($arFields['COMPANY_ID']))
			$arFields['COMPANY_ID'] = array($arFields['COMPANY_ID']);*/

		$DB->StartTransaction();

		$CCrmEntity = new CCrmDeal(false);
		$id = $CCrmEntity->Add($arFields);

		if (!$id || $id <= 0)
		{
			$DB->Rollback();
			throw new Exception($CCrmEntity->LAST_ERROR);
		}

		if (COption::GetOptionString("crm", "start_bp_within_bp", "N") == "Y")
		{
			$CCrmBizProc = new CCrmBizProc('DEAL');
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

		$dbDocumentList = CCrmDeal::GetList(
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
				$db = CUserTypeEntity::GetList(array(), array("ENTITY_ID" => "CRM_DEAL", "FIELD_NAME" => $key));
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

		/*if (isset($arFields['CONTACT_ID']) && !is_array($arFields['CONTACT_ID']))
			$arFields['CONTACT_ID'] = array($arFields['CONTACT_ID']);
		if (isset($arFields['COMPANY_ID']) && !is_array($arFields['COMPANY_ID']))
			$arFields['COMPANY_ID'] = array($arFields['COMPANY_ID']);*/

		$DB->StartTransaction();

		$CCrmEntity = new CCrmDeal(false);
		$res = $CCrmEntity->Update($arDocumentID['ID'], $arFields);

		if (!$res)
		{
			$DB->Rollback();
			throw new Exception($CCrmEntity->LAST_ERROR);
		}

		if (COption::GetOptionString("crm", "start_bp_within_bp", "N") == "Y")
		{
			$CCrmBizProc = new CCrmBizProc('DEAL');
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
