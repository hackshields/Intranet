<?
include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/runtimeservice.php");

class CBPDocumentService
	extends CBPRuntimeService
{
	private $arDocumentsCache = array();

	public function GetDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			return $this->arDocumentsCache[$k];

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$this->arDocumentsCache[$k] = call_user_func_array(array($entity, "GetDocument"), array($documentId));
			return $this->arDocumentsCache[$k];
		}

		return null;
	}

	public function UpdateDocument($parameterDocumentId, $arFields)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "UpdateDocument"), array($documentId, $arFields));

		return false;
	}

	public function CreateDocument($parameterDocumentId, $arFields)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "CreateDocument"), array($documentId, $arFields));

		return false;
	}

	public function PublishDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "PublishDocument"), array($documentId));

		return false;
	}

	public function UnpublishDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "UnpublishDocument"), array($documentId));

		return false;
	}

	public function LockDocument($parameterDocumentId, $workflowId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "LockDocument"), array($documentId, $workflowId));

		return false;
	}

	public function UnlockDocument($parameterDocumentId, $workflowId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "UnlockDocument"), array($documentId, $workflowId));

		return false;
	}

	public function DeleteDocument($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		$k = $moduleId."@".$entity."@".$documentId;
		if (array_key_exists($k, $this->arDocumentsCache))
			unset($this->arDocumentsCache[$k]);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "DeleteDocument"), array($documentId));

		return false;
	}

	public function IsDocumentLocked($parameterDocumentId, $workflowId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "IsDocumentLocked"), array($documentId, $workflowId));

		return false;
	}

	public function SubscribeOnUnlockDocument($parameterDocumentId, $workflowId,  $eventName)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);
		RegisterModuleDependences($moduleId, $entity."_OnUnlockDocument", "bizproc", "CBPDocumentService", "OnUnlockDocument", 100, "", array($workflowId,  $eventName));
	}

	public function UnsubscribeOnUnlockDocument($parameterDocumentId, $workflowId, $eventName)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);
		UnRegisterModuleDependences($moduleId, $entity."_OnUnlockDocument", "bizproc", "CBPDocumentService", "OnUnlockDocument", "", array($workflowId,  $eventName));
	}

	public static function OnUnlockDocument($workflowId, $eventName, $documentId = array())
	{
		CBPRuntime::SendExternalEvent($workflowId, $eventName, array());
	}

	public function GetDocumentType($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "GetDocumentType"))
			return array($moduleId, $entity, call_user_func_array(array($entity, "GetDocumentType"), array($documentId)));

		return null;
	}

	public function GetDocumentFields($parameterDocumentType)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$ar = call_user_func_array(array($entity, "GetDocumentFields"), array($documentType));
			if (is_array($ar))
			{
				$arKeys = array_keys($ar);
				if (!array_key_exists("BaseType", $ar[$arKeys[0]]) || strlen($ar[$arKeys[0]]["BaseType"]) <= 0)
				{
					foreach ($arKeys as $key)
					{
						if (in_array($ar[$key]["Type"], array("int", "datetime", "user", "string", "bool", "file", "text", "select")))
							$ar[$key]["BaseType"] = $ar[$key]["Type"];
						else
							$ar[$key]["BaseType"] = "string";
					}
				}
			}

			return $ar;
		}

		return null;
	}

	public function GetDocumentFieldTypes($parameterDocumentType)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "GetDocumentFieldTypes"))
			return call_user_func_array(array($entity, "GetDocumentFieldTypes"), array($documentType));

		return CBPHelper::GetDocumentFieldTypes();
	}

	public function AddDocumentField($parameterDocumentType, $arFields)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "AddDocumentField"), array($documentType, $arFields));

		return false;
	}

	public function GetJSFunctionsForFields($parameterDocumentType, $objectName, $arDocumentFields = array(), $arDocumentFieldTypes = array())
	{
		if (!is_array($arDocumentFields) || count($arDocumentFields) <= 0)
			$arDocumentFields = self::GetDocumentFields($parameterDocumentType);
		if (!is_array($arDocumentFieldTypes) || count($arDocumentFieldTypes) <= 0)
			$arDocumentFieldTypes = self::GetDocumentFieldTypes($parameterDocumentType);

		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		$documentFieldsString = "";
		foreach ($arDocumentFields as $fieldKey => $arFieldValue)
		{
			if (strlen($documentFieldsString) > 0)
				$documentFieldsString .= ",";

			$documentFieldsString .= "'".Cutil::JSEscape($fieldKey)."':{";

			$documentFieldsString .= "'Name':'".CUtil::JSEscape($arFieldValue["Name"])."',";
			$documentFieldsString .= "'Type':'".CUtil::JSEscape($arFieldValue["Type"])."',";
			$documentFieldsString .= "'Multiple':'".CUtil::JSEscape($arFieldValue["Multiple"] ? "Y" : "N")."',";
			$documentFieldsString .= "'Complex':'".CUtil::JSEscape($arFieldValue["Complex"] ? "Y" : "N")."',";

			$documentFieldsString .= "'Options':";
			if (array_key_exists("Options", $arFieldValue))
			{
				if (is_array($arFieldValue["Options"]))
				{
					$documentFieldsString .= "{";
					$flTmp = false;
					foreach ($arFieldValue["Options"] as $k => $v)
					{
						if ($flTmp)
							$documentFieldsString .= ",";
						$documentFieldsString .= "'".CUtil::JSEscape($k)."':'".CUtil::JSEscape($v)."'";
						$flTmp = true;
					}
					$documentFieldsString .= "}";
				}
				else
				{
					$documentFieldsString .= "'".CUtil::JSEscape($arFieldValue["Options"])."'";
				}
			}
			else
			{
				$documentFieldsString .= "''";
			}

			$documentFieldsString .= "}";
		}

		$fieldTypesString = "";
		$ind = -1;
		foreach ($arDocumentFieldTypes as $typeKey => $arTypeValue)
		{
			$ind++;
			if (strlen($fieldTypesString) > 0)
				$fieldTypesString .= ",";

			$fieldTypesString .= "'".CUtil::JSEscape($typeKey)."':{";

			$fieldTypesString .= "'Name':'".CUtil::JSEscape($arTypeValue["Name"])."',";
			$fieldTypesString .= "'BaseType':'".CUtil::JSEscape($arTypeValue["BaseType"])."',";
			$fieldTypesString .= "'Complex':'".CUtil::JSEscape($arTypeValue["Complex"] ? "Y" : "N")."',";
			$fieldTypesString .= "'Index':".$ind."";

			$fieldTypesString .= "}";
		}

		$documentTypeString = CUtil::PhpToJSObject($parameterDocumentType);
		$bitrixSessId = bitrix_sessid();

$result = <<<EOS
<script type="text/javascript">
var $objectName = {};

$objectName.arDocumentFields = { $documentFieldsString };
$objectName.arFieldTypes = { $fieldTypesString };

$objectName.AddField = function(fldCode, fldName, fldType, fldMultiple, fldOptions)
{
	this.arDocumentFields[fldCode] = {};
	this.arDocumentFields[fldCode]["Name"] = fldName;
	this.arDocumentFields[fldCode]["Type"] = fldType;
	this.arDocumentFields[fldCode]["Multiple"] = fldMultiple;
	this.arDocumentFields[fldCode]["Options"] = fldOptions;
}

$objectName._PrepareResponse = function(v)
{
	v = v.replace(/^\s+|\s+$/g, '');
	while (v.length > 0 && v.charCodeAt(0) == 65279)
		v = v.substring(1);

	if (v.length <= 0)
		return undefined;

	eval("v = " + v);

	return v;
}

$objectName.GetFieldInputControl4Type = function(type, value, name, subtypeFunctionName, func)
{
	this.GetFieldInputControlInternal(
		type,
		value,
		name,
		function(v)
		{
			var p = v.indexOf('<!--__defaultOptionsValue:');
			if (p >= 0)
			{
				p = p + '<!--__defaultOptionsValue:'.length;
				var p1 = v.indexOf('-->', p);
				type['Options'] = v.substring(p, p1);
			}

			var newPromt = "";

			p = v.indexOf('<!--__modifyOptionsPromt:');
			if (p >= 0)
			{
				p = p + '<!--__modifyOptionsPromt:'.length;
				p1 = v.indexOf('-->', p);
				newPromt = v.substring(p, p1);
			}

			func(v, newPromt);
		},
		false,
		subtypeFunctionName,
		'Type'
	);
}

$objectName.GetFieldInputControl4Subtype = function(type, value, name, func)
{
	$objectName.GetFieldInputControlInternal(type, value, name, func, false, '', '');
}

$objectName.GetFieldInputControl = function(type, value, name, func, als)
{
	$objectName.GetFieldInputControlInternal(type, value, name, func, als, '', '');
}

$objectName.GetFieldInputControlInternal = function(type, value, name, func, als, subtypeFunctionName, mode)
{
	if (typeof name == "undefined" || name.length <= 0)
		name = "BPVDDefaultValue";

	if (typeof type != "object")
		type = {'Type' : type, 'Multiple' : 0, 'Required' : 0, 'Options' : null};

	if (typeof name != "object")
		name = {'Field' : name, 'Form' : null};

	BX.ajax.post(
		'/bitrix/tools/bizproc_get_field.php',
		{
			'DocumentType' : $documentTypeString,
			'Field' : name,
			'Value' : value,
			'Type' : type,
			'Als' : als ? 1 : 0,
			'rnd' : Math.random(),
			'Mode' : mode,
			'Func' : subtypeFunctionName,
			'sessid' : '$bitrixSessId'
		},
		func
	);
}

$objectName.GetFieldValueByTagName = function(tag, name, form)
{
	var fieldValues = {};

	var ar;
	if (form && (form.length > 0))
	{
		var obj = document.getElementById(form);
		if (!obj)
		{
			for (var i in document.forms)
			{
				if (document.forms[i].name == form)
				{
					obj = document.forms[i];
					break;
				}
			}
		}

		if (!obj)
			return;

		ar = obj.getElementsByTagName(tag);
	}
	else
	{
		ar = document.getElementsByTagName(tag);
	}

	for (var i in ar)
	{
		if (ar[i] && ar[i].name && (ar[i].name.length >= name.length) && (ar[i].name.substr(0, name.length) == name))
		{
			if (ar[i].type.substr(0, "select".length) == "select")
			{
				if (ar[i].multiple)
				{
					var newName = ar[i].name.replace(/\[\]/g, "");
					for (var j = 0; j < ar[i].options.length; j++)
					{
						if (ar[i].options[j].selected)
						{
							if ((typeof(fieldValues[newName]) != 'object') || !(fieldValues[newName] instanceof Array))
							{
								if (fieldValues[newName])
									fieldValues[newName] = [fieldValues[newName]];
								else
									fieldValues[newName] = [];
							}
							fieldValues[newName][fieldValues[newName].length] = ar[i].options[j].value;
						}
					}
				}
				else
				{
					if (ar[i].selectedIndex >= 0)
						fieldValues[ar[i].name] = ar[i].options[ar[i].selectedIndex].value;
				}
			}
			else
			{
				if (ar[i].name.indexOf("[]", 0) >= 0)
				{
					var newName = ar[i].name.replace(/\[\]/g, "");

					if ((typeof(fieldValues[newName]) != 'object') || !(fieldValues[newName] instanceof Array))
					{
						if (fieldValues[newName])
							fieldValues[newName] = [fieldValues[newName]];
						else
							fieldValues[newName] = [];
					}

					fieldValues[newName][fieldValues[newName].length] = ar[i].value;
				}
				else
				{
					fieldValues[ar[i].name] = ar[i].value;
				}
			}
		}
	}

	return fieldValues;
}

$objectName.GetFieldInputValue = function(type, name, func)
{
	if (typeof name == "undefined" || name.length <= 0)
		name = "BPVDDefaultValue";

	if (typeof type != "object")
		type = {'Type' : type, 'Multiple' : 0, 'Required' : 0, 'Options' : null};

	if (typeof name != "object")
		name = {'Field' : name, 'Form' : null};

	var s = {
		'DocumentType' : $documentTypeString,
		'Field' : name,
		'Type' : type,
		'rnd' : Math.random(),
		'sessid' : '$bitrixSessId'
	};

	if (type != null && type['Type'] != "F")
	{
		var ar = this.GetFieldValueByTagName('input', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
		ar = this.GetFieldValueByTagName('select', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
		ar = this.GetFieldValueByTagName('textarea', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
		ar = this.GetFieldValueByTagName('hidden', name['Field'], name['Form']);
		for (var v in ar)
			s[v] = ar[v];
	}

	BX.ajax.post('/bitrix/tools/bizproc_set_field.php', s, function(v){v = $objectName._PrepareResponse(v); func(v);});
}

$objectName.HtmlSpecialChars = function(string, quote)
{
	string = string.toString();
	string = string.replace(/&/g, '&amp;');
	string = string.replace(/</g, '&lt;');
	string = string.replace(/>/g, '&gt;');
	string = string.replace(/"/g, '&quot;');

	if (quote)
		string = string.replace(/'/g, '&#039;');

	return string;
}

$objectName.GetGUITypeEdit = function(type)
{
	return "";
}

$objectName.SetGUITypeEdit = function(type)
{
	return "";
}

function __dump_bx(arr, limitLevel, txt)
{
	if (limitLevel == undefined)
		limitLevel = 3;
	if (txt == undefined)
		txt = "";
	else
		txt += ":\\n";
	alert(txt+__dumpInternal_bx(arr, 0, limitLevel));
}
function __dumpInternal_bx(arr, level, limitLevel) {
	var dumped_text = "";
	if(!level) level = 0;
	if (level > limitLevel)
		return "";
	var level_padding = "";
	for(var j=0;j<level+1;j++) level_padding += "    ";
	if(typeof(arr) == 'object') {
		for(var item in arr) {
			var value = arr[item];
			if(typeof(value) == 'object') {
				dumped_text += level_padding + "'" + item + "' ...\\n";
				dumped_text += __dumpInternal_bx(value, level+1, limitLevel);
			} else {
				dumped_text += level_padding + "'" + item + "' => '" + value + "'\\n";
			}
		}
	} else {
		dumped_text = "===>"+arr+"<===("+typeof(arr)+")";
	}

	return dumped_text;
}

</script>
EOS;

		if (class_exists($entity) && method_exists($entity, "GetJSFunctionsForFields"))
		{
			$result .= call_user_func_array(array($entity, "GetJSFunctionsForFields"), array($documentType, $objectName, $arDocumentFields, $arDocumentFieldTypes));
		}
		else
		{
			if (!is_array($arDocumentFields) || count($arDocumentFields) <= 0)
				$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			if (!is_array($arDocumentFieldTypes) || count($arDocumentFieldTypes) <= 0)
				$arDocumentFieldTypes = $this->GetDocumentFieldTypes($parameterDocumentType);

			$result .= CBPHelper::GetJSFunctionsForFields($objectName, $arDocumentFields, $arDocumentFieldTypes);
		}

		return $result;
	}

	public function GetFieldInputControlOptions($parameterDocumentType, &$fieldType, $jsFunctionName, &$value)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (is_array($fieldType))
		{
			$arFieldType = array("Type" => null, "Multiple" => false, "Required" => false, "Options" => null);
			foreach ($fieldType as $key => $val)
			{
				switch (strtoupper($key))
				{
					case "TYPE":
					case "0":
						$arFieldType["Type"] = strval($val);
						break;
					case "MULTIPLE":
					case "1":
						$arFieldType["Multiple"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "REQUIRED":
					case "2":
						$arFieldType["Required"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "OPTIONS":
					case "3":
						$arFieldType["Options"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldType = array("Type" => strval($fieldType), "Multiple" => false, "Required" => false, "Options" => null);
		}
		if ((string) $arFieldType["Type"] == "")
			return "";

		$fieldType = $arFieldType;

		if (class_exists($entity) && method_exists($entity, "GetFieldInputControlOptions"))
			return call_user_func_array(array($entity, "GetFieldInputControlOptions"), array($documentType, &$fieldType, $jsFunctionName, &$value));

		return "";
	}

	public function GetFieldInputControl($parameterDocumentType, $fieldType, $fieldName, $fieldValue, $bAllowSelection = false, $publicMode = false)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (is_array($fieldType))
		{
			$arFieldType = array("Type" => null, "Multiple" => false, "Required" => false, "Options" => null);
			foreach ($fieldType as $key => $val)
			{
				switch (strtoupper($key))
				{
					case "TYPE":
					case "0":
						$arFieldType["Type"] = strval($val);
						break;
					case "MULTIPLE":
					case "1":
						$arFieldType["Multiple"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "REQUIRED":
					case "2":
						$arFieldType["Required"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "OPTIONS":
					case "3":
						$arFieldType["Options"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldType = array("Type" => strval($fieldType), "Multiple" => false, "Required" => false, "Options" => null);
		}
		if ((string) $arFieldType["Type"] == "")
			return "";

		if (is_array($fieldName))
		{
			$arFieldName = array("Form" => null, "Field" => null);
			foreach ($fieldName as $key => $val)
			{
				switch (strtoupper($key))
				{
					case "FORM":
					case "0":
						$arFieldName["Form"] = $val;
						break;
					case "FIELD":
					case "1":
						$arFieldName["Field"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldName = array("Form" => null, "Field" => $fieldName);
		}
		if ((string) $arFieldName["Field"] == "" || preg_match("#[^a-z0-9_]#i", $arFieldName["Field"]))
			return "";
		if ((string) $arFieldName["Form"] != "" && preg_match("#[^a-z0-9_]#i", $arFieldName["Form"]))
			return "";

		if ($publicMode && !array_key_exists("BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType, $GLOBALS))
		{
			$GLOBALS["BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType] = 1;
			CBPDocument::AddShowParameterInit($moduleId, "only_users", $documentType, $entity);
		}

		if (class_exists($entity))
		{
			if (method_exists($entity, "GetFieldInputControl"))
				return call_user_func_array(array($entity, "GetFieldInputControl"), array($documentType, $arFieldType, $arFieldName, $fieldValue, $bAllowSelection, $publicMode));

			if (method_exists($entity, "GetGUIFieldEdit"))
				return call_user_func_array(array($entity, "GetGUIFieldEdit"), array($documentType, $arFieldName["Form"], $arFieldName["Field"], $fieldValue, $arFieldType, $bAllowSelection));
		}

		return CBPHelper::GetFieldInputControl($parameterDocumentType, $arFieldType, $arFieldName, $fieldValue, $bAllowSelection);
	}

	public function GetFieldInputValue($parameterDocumentType, $fieldType, $fieldName, $arRequest, &$arErrors)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (is_array($fieldType))
		{
			$arFieldType = array("Type" => null, "Multiple" => false, "Required" => false, "Options" => null);
			foreach ($fieldType as $key => $val)
			{
				switch (strtoupper($key))
				{
					case "TYPE":
					case "0":
						$arFieldType["Type"] = strval($val);
						break;
					case "MULTIPLE":
					case "1":
						$arFieldType["Multiple"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "REQUIRED":
					case "2":
						$arFieldType["Required"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "OPTIONS":
					case "3":
						if (is_array($val))
							$arFieldType["Options"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldType = array("Type" => strval($fieldType), "Multiple" => false, "Required" => false, "Options" => null);
		}
		if ((string) $arFieldType["Type"] == "")
			return "";

		if (is_array($fieldName))
		{
			$arFieldName = array("Form" => null, "Field" => null);
			foreach ($fieldName as $key => $val)
			{
				switch (strtoupper($key))
				{
					case "FORM":
					case "0":
						$arFieldName["Form"] = $val;
						break;
					case "FIELD":
					case "1":
						$arFieldName["Field"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldName = array("Form" => null, "Field" => $fieldName);
		}
		if ((string) $arFieldName["Field"] == "" || preg_match("#[^a-z0-9_]#i", $arFieldName["Field"]))
			return "";
		if ((string) $arFieldName["Form"] != "" && preg_match("#[^a-z0-9_]#i", $arFieldName["Form"]))
			return "";

		if (class_exists($entity))
		{
			if (method_exists($entity, "GetFieldInputValue"))
				return call_user_func_array(array($entity, "GetFieldInputValue"), array($documentType, $arFieldType, $arFieldName, $arRequest, &$arErrors));

			if (method_exists($entity, "SetGUIFieldEdit"))
				return call_user_func_array(array($entity, "SetGUIFieldEdit"), array($documentType, $arFieldName["Field"], $arRequest, &$arErrors, $arFieldType));
		}

		return CBPHelper::GetFieldInputValue($parameterDocumentType, $arFieldType, $arFieldName, $arRequest, $arErrors);
	}

	public function GetFieldInputValuePrintable($parameterDocumentType, $fieldType, $fieldValue)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (is_array($fieldType))
		{
			$arFieldType = array("Type" => null, "Multiple" => false, "Required" => false, "Options" => null);
			foreach ($fieldType as $key => $val)
			{
				switch (strtoupper($key))
				{
					case "TYPE":
					case "0":
						$arFieldType["Type"] = strval($val);
						break;
					case "MULTIPLE":
					case "1":
						$arFieldType["Multiple"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "REQUIRED":
					case "2":
						$arFieldType["Required"] = ((!$val || is_int($val) && ($val == 0) || (strtoupper($val) == "N")) ? false : true);
						break;
					case "OPTIONS":
					case "3":
						if (is_array($val))
							$arFieldType["Options"] = $val;
						break;
				}
			}
		}
		else
		{
			$arFieldType = array("Type" => strval($fieldType), "Multiple" => false, "Required" => false, "Options" => null);
		}
		if ((string) $arFieldType["Type"] == "")
			return "";

		if (class_exists($entity))
		{
			if (method_exists($entity, "GetFieldInputValuePrintable"))
				return call_user_func_array(array($entity, "GetFieldInputValuePrintable"), array($documentType, $arFieldType, $fieldValue));

			if (method_exists($entity, "GetFieldValuePrintable"))
				return call_user_func_array(array($entity, "GetFieldValuePrintable"), array(null, "", $arFieldType["Type"], $fieldValue, $arFieldType));
		}

		return CBPHelper::GetFieldInputValuePrintable($parameterDocumentType, $arFieldType, $fieldValue);
	}

	public function GetFieldValuePrintable($parameterDocumentId, $fieldName, $fieldType, $fieldValue, $arFieldType = null)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "GetFieldValuePrintable"))
			return call_user_func_array(array($entity, "GetFieldValuePrintable"), array($documentId, $fieldName, $fieldType, $fieldValue, $arFieldType));

		return CBPHelper::GetFieldValuePrintable($fieldName, $fieldType, $fieldValue, $arFieldType);
	}

	// Deprecated
	public function GetGUIFieldEdit($parameterDocumentType, $formName, $fieldName, $fieldValue, $arDocumentField = array(), $bAllowSelection = false)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (!array_key_exists("BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType, $GLOBALS))
		{
			$GLOBALS["BP_AddShowParameterInit_".$moduleId."_".$entity."_".$documentType] = 1;
			CBPDocument::AddShowParameterInit($moduleId, "only_users", $documentType, $entity);
		}

		if (class_exists($entity) && method_exists($entity, "GetGUIFieldEdit"))
			return call_user_func_array(array($entity, "GetGUIFieldEdit"), array($documentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection));

		return CBPHelper::GetGUIFieldEdit($parameterDocumentType, $formName, $fieldName, $fieldValue, $arDocumentField, $bAllowSelection);
	}

	// Deprecated
	public function SetGUIFieldEdit($parameterDocumentType, $fieldName, $arRequest, &$arErrors, $arDocumentField = array())
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (!is_array($arDocumentField) || count($arDocumentField) <= 0)
		{
			$arDocumentFields = $this->GetDocumentFields($parameterDocumentType);
			$arDocumentField = $arDocumentFields[$fieldName];
		}

		if (class_exists($entity) && method_exists($entity, "SetGUIFieldEdit"))
			return call_user_func_array(array($entity, "SetGUIFieldEdit"), array($documentType, $fieldName, $arRequest, &$arErrors, $arDocumentField));

		return CBPHelper::SetGUIFieldEdit($parameterDocumentType, $fieldName, $arRequest, $arErrors, $arDocumentField);
	}

	public function GetDocumentAdminPage($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentAdminPage"), array($documentId));

		return "";
	}

	public function GetDocumentForHistory($parameterDocumentId, $historyIndex)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetDocumentForHistory"), array($documentId, $historyIndex));

		return null;
	}

	public function RecoverDocumentFromHistory($parameterDocumentId, $arDocument)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "RecoverDocumentFromHistory"), array($documentId, $arDocument));

		return false;
	}

	public function GetUsersFromUserGroup($group, $parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetUsersFromUserGroup"), array($group, $documentId));

		return array();
	}

	public function GetAllowableUserGroups($parameterDocumentId)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
		{
			$result = call_user_func_array(array($entity, "GetAllowableUserGroups"), array($documentId));
			$result1 = array();
			foreach ($result as $key => $value)
				$result1[strtolower($key)] = $value;
			return $result1;
		}

		return array();
	}

	public function GetAllowableOperations($parameterDocumentType)
	{
		list($moduleId, $entity, $documentType) = CBPHelper::ParseDocumentId($parameterDocumentType);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity))
			return call_user_func_array(array($entity, "GetAllowableOperations"), array($documentType));

		return array();
	}

	public function SetPermissions($parameterDocumentId, $workflowId, $arPermissions, $bRewrite = true)
	{
		list($moduleId, $entity, $documentId) = CBPHelper::ParseDocumentId($parameterDocumentId);

		if (strlen($moduleId) > 0)
			CModule::IncludeModule($moduleId);

		if (class_exists($entity) && method_exists($entity, "SetPermissions"))
			return call_user_func_array(array($entity, "SetPermissions"), array($documentId, $workflowId, $arPermissions, $bRewrite));

		return false;
	}
}
?>