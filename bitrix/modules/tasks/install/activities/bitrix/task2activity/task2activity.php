<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

class CBPTask2Activity
	extends CBPActivity
	implements IBPEventActivity, IBPActivityExternalEventListener
{
	private $isInEventActivityMode = false;

	public function __construct($name)
	{
		parent::__construct($name);
		$this->arProperties = array(
			"Title"                   => "",
			"Fields"                  => null,
			"HoldToClose"             => false,
			"AUTO_LINK_TO_CRM_ENTITY" => true,
			"ClosedBy"                => null,
			"ClosedDate"              => null,
			"TaskId"                  => null,
		);
	}


	public function Cancel()
	{
		if (!$this->isInEventActivityMode && $this->HoldToClose)
			$this->Unsubscribe($this);

		return CBPActivityExecutionStatus::Closed;
	}


	private function __GetUsers($arUsersDraft, $bFirst = false)
	{
		$arUsers = array();

		$rootActivity = $this->GetRootActivity();
		$documentId = $rootActivity->GetDocumentId();

		$documentService = $this->workflow->GetService("DocumentService");

		$arUsersDraft = (is_array($arUsersDraft) ? $arUsersDraft : array($arUsersDraft));
		$l = strlen("user_");
		foreach ($arUsersDraft as $user)
		{
			if (substr($user, 0, $l) == "user_")
			{
				$user = intval(substr($user, $l));
				if ($user > 0)
					$arUsers[] = $user;
			}
			else
			{
				$arDSUsers = $documentService->GetUsersFromUserGroup($user, $documentId);
				foreach ($arDSUsers as $v)
				{
					$user = intval($v);
					if ($user > 0)
						$arUsers[] = $user;
				}
			}
		}

		if (!$bFirst)
			return $arUsers;

		if (count($arUsers) > 0)
			return $arUsers[0];

		return null;
	}


	public function Execute()
	{
		if (!CModule::IncludeModule("tasks"))
			return CBPActivityExecutionStatus::Closed;

		$arFields = $this->Fields;
		$arFields["CREATED_BY"] = $this->__GetUsers($this->Fields["CREATED_BY"], true);
		$arFields["RESPONSIBLE_ID"] = $this->__GetUsers($this->Fields["RESPONSIBLE_ID"], true);
		$arFields["ACCOMPLICES"] = $this->__GetUsers($this->Fields["ACCOMPLICES"]);
		$arFields["AUDITORS"] = $this->__GetUsers($this->Fields["AUDITORS"]);

		if (!$arFields["SITE_ID"])
		{
			$arFields["SITE_ID"] = SITE_ID;
		}

		if ($this->AUTO_LINK_TO_CRM_ENTITY && CModule::IncludeModule('crm'))
		{
			$rootActivity = $this->GetRootActivity();
			$documentId   = $rootActivity->GetDocumentId();
			$documentType = $rootActivity->GetDocumentType();

			$letter = CCrmOwnerTypeAbbr::ResolveByTypeID(CCrmOwnerType::ResolveID($documentType[2]));

			$arFields['UF_CRM_TASK'] = array(
				str_replace(
					$documentType[2],
					$letter,
					$documentId[2]
				)
			);
		}

		$arUnsetFields = array();
		foreach ($arFields as $fieldName => $fieldValue)
		{
			if (substr($fieldName, -5) === '_text')
			{
				$arFields[substr($fieldName, 0, -5)] = $fieldValue;
				$arUnsetFields[] = $fieldName;
			}
		}

		foreach ($arUnsetFields as $fieldName)
			unset($arFields[$fieldName]);

		$task = new CTasks;
		$result = $task->Add($arFields);

		if (!$result)
		{
			$arErrors = $task->GetErrors();
			if (count($arErrors) > 0)
				$this->WriteToTrackingService(GetMessage("BPSA_TRACK_ERROR"));

			return CBPActivityExecutionStatus::Closed;
		}

		$this->TaskId = $result;
		$this->WriteToTrackingService(str_replace("#VAL#", $result, GetMessage("BPSA_TRACK_OK")));

		if ($this->isInEventActivityMode || !$this->HoldToClose)
			return CBPActivityExecutionStatus::Closed;

		$this->Subscribe($this);
		$this->isInEventActivityMode = false;

		$this->WriteToTrackingService(GetMessage("BPSA_TRACK_SUBSCR"));

		return CBPActivityExecutionStatus::Executing;
	}


	public function Subscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$this->isInEventActivityMode = true;

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->SubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, "tasks", "OnTaskUpdate", $this->TaskId);

		$this->workflow->AddEventHandler($this->name, $eventHandler);
	}


	public function Unsubscribe(IBPActivityExternalEventListener $eventHandler)
	{
		if ($eventHandler == null)
			throw new Exception("eventHandler");

		$schedulerService = $this->workflow->GetService("SchedulerService");
		$schedulerService->UnSubscribeOnEvent($this->workflow->GetInstanceId(), $this->name, "tasks", "OnTaskUpdate", $this->TaskId);

		$this->workflow->RemoveEventHandler($this->name, $eventHandler);
	}


	public function OnExternalEvent($arEventParameters = array())
	{
		if ($this->TaskId != $arEventParameters[0])
			return;

		if ($this->executionStatus != CBPActivityExecutionStatus::Closed)
		{
			if ($arEventParameters[1]["STATUS"] == 5)
			{
				$this->ClosedBy = "user_".$arEventParameters[1]["CLOSED_BY"];
				$this->ClosedDate = $arEventParameters[1]["CLOSED_DATE"];

				$this->WriteToTrackingService(str_replace("#DATE#", $arEventParameters[1]["CLOSED_DATE"], GetMessage("BPSA_TRACK_CLOSED")));

				$this->Unsubscribe($this);
				$this->workflow->CloseActivity($this);
			}
		}
	}


	public function HandleFault(Exception $exception)
	{
		if ($exception == null)
			throw new Exception("exception");

		$status = $this->Cancel();
		if ($status == CBPActivityExecutionStatus::Canceling)
			return CBPActivityExecutionStatus::Faulting;

		return $status;
	}


	public static function GetPropertiesDialog($documentType, $activityName, $arWorkflowTemplate, $arWorkflowParameters, $arWorkflowVariables, $arCurrentValues = null, $formName = "")
	{
		$runtime = CBPRuntime::GetRuntime();

		if (!is_array($arWorkflowParameters))
			$arWorkflowParameters = array();
		if (!is_array($arWorkflowVariables))
			$arWorkflowVariables = array();

		$documentService = $runtime->GetService("DocumentService");

		if (!is_array($arCurrentValues))
		{
			$arCurrentValues = array();

			$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
			if (is_array($arCurrentActivity["Properties"])
				&& array_key_exists("Fields", $arCurrentActivity["Properties"])
				&& is_array($arCurrentActivity["Properties"]["Fields"]))
			{
				foreach ($arCurrentActivity["Properties"]["Fields"] as $k => $v)
				{
					$arCurrentValues[$k] = $v;

					if (in_array($k, array("CREATED_BY", "RESPONSIBLE_ID", "ACCOMPLICES", "AUDITORS")))
					{
						if (!is_array($arCurrentValues[$k]))
							$arCurrentValues[$k] = array($arCurrentValues[$k]);

						$ar = array();
						foreach ($arCurrentValues[$k] as $v)
						{
							if (intval($v)."!" == $v."!")
								$v = "user_".$v;
							$ar[] = $v;
						}

						$arCurrentValues[$k] = CBPHelper::UsersArrayToString($ar, $arWorkflowTemplate, $documentType);
					}
				}
			}

			$arCurrentValues["HOLD_TO_CLOSE"] = ($arCurrentActivity["Properties"]["HoldToClose"] ? "Y" : "N");
			$arCurrentValues["AUTO_LINK_TO_CRM_ENTITY"] = ($arCurrentActivity["Properties"]["AUTO_LINK_TO_CRM_ENTITY"] ? "Y" : "N");
		}
		else
		{
			$arFieldNames = array("TITLE", "CREATED_BY", "RESPONSIBLE_ID", "ACCOMPLICES", "START_DATE_PLAN", "END_DATE_PLAN", "DEADLINE", "DESCRIPTION", "PRIORITY", "GROUP_ID", "ALLOW_CHANGE_DEADLINE", "TASK_CONTROL", "ADD_IN_REPORT", "AUDITORS", );
			foreach ($arFieldNames as $field)
			{
				if ((!is_array($arCurrentValues[$field]) && (strlen($arCurrentValues[$field]) <= 0)
					|| is_array($arCurrentValues[$field]) && (count($arCurrentValues[$field]) <= 0))
					&& (strlen($arCurrentValues[$field."_text"]) > 0))
				{
					$arCurrentValues[$field] = $arCurrentValues[$field."_text"];
				}
			}
		}

		$arDocumentFields = self::__GetFields();

		return $runtime->ExecuteResourceFile(
			__FILE__, "properties_dialog.php", array(
				"arCurrentValues" => $arCurrentValues,
				"formName" => $formName,
				"documentType" => $documentType,
				"popupWindow" => &$popupWindow,
				"arDocumentFields" => $arDocumentFields,
			)
		);
	}


	public static function GetPropertiesDialogValues($documentType, $activityName, &$arWorkflowTemplate, &$arWorkflowParameters, &$arWorkflowVariables, $arCurrentValues, &$arErrors)
	{
		$arErrors = array();

		$arProperties = array("Fields" => array());

		$arTaskPriority = array(0, 1, 2);
		foreach ($arTaskPriority as $k => $v)
			$arTaskPriority[$v] = GetMessage("TASK_PRIORITY_".$v);

		$arGroups = array(GetMessage("TASK_EMPTY_GROUP"));
		if (CModule::IncludeModule("socialnetwork"))
		{
			$db = CSocNetGroup::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
			while ($ar = $db->GetNext())
				$arGroups[$ar["ID"]] = "[".$ar["ID"]."]".$ar["NAME"];
		}

		$arDF = self::__GetFields();

		$arFieldNames = array("TITLE", "CREATED_BY", "RESPONSIBLE_ID", "ACCOMPLICES", "START_DATE_PLAN", "END_DATE_PLAN", "DEADLINE", "DESCRIPTION", "PRIORITY", "GROUP_ID", "ALLOW_CHANGE_DEADLINE", "TASK_CONTROL", "ADD_IN_REPORT", "AUDITORS", );
		foreach ($arFieldNames as $field)
		{
			$r = null;

			if (in_array($field, array("CREATED_BY", "RESPONSIBLE_ID", "ACCOMPLICES", "AUDITORS")))
			{
				$value = $arCurrentValues[$field];
				if (strlen($value) > 0)
				{
					$arErrorsTmp = array();
					$r = CBPHelper::UsersStringToArray($value, $documentType, $arErrorsTmp);
					if (count($arErrorsTmp) > 0)
						$arErrors = array_merge($arErrors, $arErrorsTmp);
				}
			}
			elseif (array_key_exists($field, $arCurrentValues) || array_key_exists($field."_text", $arCurrentValues))
			{
				$arValue = array();
				if (array_key_exists($field, $arCurrentValues))
				{
					$arValue = $arCurrentValues[$field];
					if (!is_array($arValue) || is_array($arValue) && CBPHelper::IsAssociativeArray($arValue))
						$arValue = array($arValue);
				}
				if (array_key_exists($field."_text", $arCurrentValues))
					$arValue[] = $arCurrentValues[$field."_text"];

				foreach ($arValue as $value)
				{
					$value = trim($value);
					if (!preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", $value) && (substr($value, 0, 1) !== "="))
					{
						if ($field == "PRIORITY")
						{
							if (strlen($value) <= 0)
								$value = null;

							if ($value != null && !array_key_exists($value, $arTaskPriority))
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => "Priority is empty",
									"parameter" => $field,
								);
							}
						}
						elseif ($field == "GROUP_ID")
						{
							if (strlen($value) <= 0)
								$value = null;
							if ($value != null && !array_key_exists($value, $arGroups))
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => "Group is empty",
									"parameter" => $field,
								);
							}
						}
						elseif (in_array($field, array("ALLOW_CHANGE_DEADLINE", "TASK_CONTROL", "ADD_IN_REPORT")))
						{
							if (strtoupper($value) == "Y" || $value === true || $value."!" == "1!")
								$value = "Y";
							elseif (strtoupper($value) == "N" || $value === false || $value."!" == "0!")
								$value = "N";
							else
								$value = null;
						}
						else
						{
							if (!is_array($value) && strlen($value) <= 0)
								$value = null;
						}
					}

					if ($value != null)
						$r[] = $value;
				}
			}

			$r_orig = $r;

			if (!in_array($field, array("ACCOMPLICES", "AUDITORS")))
			{
				if (count($r) > 0)
					$r = $r[0];
				else
					$r = null;
			}

			if (in_array($field, array("TITLE", "CREATED_BY", "RESPONSIBLE_ID")) && ($r == null || is_array($r) && count($r) <= 0))
			{
				$arErrors[] = array(
					"code" => "emptyRequiredField",
					"message" => str_replace("#FIELD#", $arDF[$field]["Name"], GetMessage("BPCDA_FIELD_REQUIED")),
				);
			}

			$arProperties["Fields"][$field] = $r;

			if (array_key_exists($field."_text", $arCurrentValues) && isset($r_orig[1]))
				$arProperties["Fields"][$field . '_text'] = $r_orig[1];
		}

		$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("TASKS_TASK", 0, LANGUAGE_ID);
		foreach ($arUserFields as $field)
		{
			$r = $arCurrentValues[$field["FIELD_NAME"]];

			if (($field["MULTIPLE"] == "Y") && (!$r || is_array($r) && count($r) <= 0))
			{
				$arErrors[] = array(
					"code" => "emptyRequiredField",
					"message" => str_replace("#FIELD#", $field["EDIT_FORM_LABEL"], GetMessage("BPCDA_FIELD_REQUIED")),
				);
			}

			$arProperties["Fields"][$field["FIELD_NAME"]] = $r;
		}

		$arProperties["HoldToClose"] = ((strtoupper($arCurrentValues["HOLD_TO_CLOSE"]) == "Y") ? true : false);
		$arProperties["AUTO_LINK_TO_CRM_ENTITY"] = ((strtoupper($arCurrentValues["AUTO_LINK_TO_CRM_ENTITY"]) == "Y") ? true : false);

		if (count($arErrors) > 0)
			return false;

		$arCurrentActivity = &CBPWorkflowTemplateLoader::FindActivityByName($arWorkflowTemplate, $activityName);
		$arCurrentActivity["Properties"] = $arProperties;

		return true;
	}


	private static function __GetFields()
	{
		$arTaskPriority = array(0, 1, 2);
		foreach ($arTaskPriority as $k => $v)
			$arTaskPriority[$v] = GetMessage("TASK_PRIORITY_".$v);

		$arGroups = array(GetMessage("TASK_EMPTY_GROUP"));
		if (CModule::IncludeModule("socialnetwork"))
		{
			$db = CSocNetGroup::GetList(array("NAME" => "ASC"), array("ACTIVE" => "Y"), false, false, array("ID", "NAME"));
			while ($ar = $db->GetNext())
				$arGroups[$ar["ID"]] = "[".$ar["ID"]."]".$ar["NAME"];
		}

		$arFields = array(
			"TITLE" => array(
				"Name" => GetMessage("BPTA1A_TASKNAME"),
				"Type" => "S",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
				"Multiple" => false,
				"BaseType" => "string"
			),
			"CREATED_BY" => array(
				"Name" => GetMessage("BPTA1A_TASKCREATEDBY"),
				"Type" => "S:UserID",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
				"Multiple" => false,
				"BaseType" => "user"
			),
			"RESPONSIBLE_ID" => array(
				"Name" => GetMessage("BPTA1A_TASKASSIGNEDTO"),
				"Type" => "S:UserID",
				"Filterable" => true,
				"Editable" => true,
				"Required" => true,
				"Multiple" => false,
				"BaseType" => "user"
			),
			"ACCOMPLICES" => array(
				"Name" => GetMessage("BPTA1A_TASKACCOMPLICES"),
				"Type" => "S:UserID",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => true,
				"BaseType" => "user"
			),
			"START_DATE_PLAN" => array(
				"Name" => GetMessage("BPTA1A_TASKACTIVEFROM"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "datetime"
			),
			"END_DATE_PLAN" => array(
				"Name" => GetMessage("BPTA1A_TASKACTIVETO"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "datetime"
			),
			"DEADLINE" => array(
				"Name" => GetMessage("BPTA1A_TASKDEADLINE"),
				"Type" => "S:DateTime",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "datetime"
			),
			"DESCRIPTION" => array(
				"Name" => GetMessage("BPTA1A_TASKDETAILTEXT"),
				"Type" => "T",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "text"
			),
			"PRIORITY" => array(
				"Name" => GetMessage("BPTA1A_TASKPRIORITY"),
				"Type" => "L",
				"Options" => $arTaskPriority,
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "select"
			),
			"GROUP_ID" => array(
				"Name" => GetMessage("BPTA1A_TASKGROUPID"),
				"Type" => "L",
				"Options" => $arGroups,
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "select"
			),
			"ALLOW_CHANGE_DEADLINE" => array(
				"Name" => GetMessage("BPTA1A_CHANGE_DEADLINE"),
				"Type" => "B",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "bool"
			),
			"TASK_CONTROL" => array(
				"Name" => GetMessage("BPTA1A_CHECK_RESULT"),
				"Type" => "B",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "bool"
			),
			"ADD_IN_REPORT" => array(
				"Name" => GetMessage("BPTA1A_ADD_TO_REPORT"),
				"Type" => "B",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => false,
				"BaseType" => "bool"
			),
			"AUDITORS" => array(
				"Name" => GetMessage("BPTA1A_TASKTRACKERS"),
				"Type" => "S:UserID",
				"Filterable" => true,
				"Editable" => true,
				"Required" => false,
				"Multiple" => true,
				"BaseType" => "user"
			),
		);

		$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("TASKS_TASK", 0, LANGUAGE_ID);

		foreach($arUserFields as $field)
		{
			$arFields[$field["FIELD_NAME"]] = array(
				"Name" => $field["EDIT_FORM_LABEL"],
				"Type" => $field["USER_TYPE_ID"],
				"Filterable" => true,
				"Editable" => true,
				"Required" => ($field["MANDATORY"] == "Y"),
				"Multiple" => ($field["MULTIPLE"] == "Y"),
				"BaseType" => $field["USER_TYPE_ID"],
				"UserField" => $field
			);
		}

		return $arFields;
	}
}
