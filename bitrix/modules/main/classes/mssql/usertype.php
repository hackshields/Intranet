<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/usertype.php");

class CUserTypeEntity extends CAllUserTypeEntity
{
	//TODO
	function CreatePropertyTables($entity_id)
	{
		global $DB, $APPLICATION;
		if(!$DB->TableExists("B_UTM_".$entity_id))
		{
			$rs = $DB->Query("
				CREATE TABLE B_UTM_".$entity_id." (
					ID INT NOT NULL IDENTITY (1, 1),
					VALUE_ID INT NOT NULL,
					FIELD_ID INT NOT NULL,
					VALUE TEXT,
					VALUE_INT INT,
					VALUE_DOUBLE FLOAT,
					VALUE_DATE DATETIME
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("ALTER TABLE B_UTM_".$entity_id." ADD CONSTRAINT PK_UTM_".$entity_id." PRIMARY KEY(ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE INDEX IX_UTM_".$entity_id."_1 ON B_UTM_".$entity_id." (FIELD_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE INDEX IX_UTM_".$entity_id."_2 ON B_UTM_".$entity_id." (VALUE_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if(!$rs)
			{
				$APPLICATION->ThrowException(GetMessage("USER_TYPE_TABLE_CREATION_ERROR",array(
					"#ENTITY_ID#"=>htmlspecialcharsbx($entity_id),
				)));
				return false;
			}
		}
		if(!$DB->TableExists("B_UTS_".$entity_id))
		{
			$rs = $DB->Query("
				CREATE TABLE B_UTS_".$entity_id." (
					VALUE_ID int not null
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("ALTER TABLE B_UTS_".$entity_id." ADD CONSTRAINT PK_UTS_".$entity_id." PRIMARY KEY(VALUE_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if(!$rs)
			{
				$APPLICATION->ThrowException(GetMessage("USER_TYPE_TABLE_CREATION_ERROR",array(
					"#ENTITY_ID#"=>htmlspecialcharsbx($entity_id),
				)));
				return false;
			}
		}
		return true;
	}

	function DropColumnSQL($strTable, $arColumns)
	{
		$res = array();
		foreach($arColumns as $strColumn)
			$res[]="ALTER TABLE ".$strTable." DROP COLUMN ".$strColumn;
		return $res;
	}
}

class CUserTypeManager extends CAllUserTypeManager
{

	function DateTimeToChar($FIELD_NAME)
	{
		global $DB;
		return "case when (
				datepart(second, ".$FIELD_NAME.") = 0
				and datepart(minute, ".$FIELD_NAME.") = 0
				and datepart(hour, ".$FIELD_NAME.") = 0
			) then ".$DB->DateToCharFunction($FIELD_NAME, "SHORT")."
			else ".$DB->DateToCharFunction($FIELD_NAME, "FULL")."
			end";
	}
}

class CSQLWhere extends CAllSQLWhere
{
	function _Upper($field)
	{
		return "UPPER(CONVERT(VARCHAR(8000), ".$field."))";
	}

	function _Empty($field)
	{
		return "(".$field." IS NULL OR DATALENGTH(".$field.") = 0)";
	}

	function _NotEmpty($field)
	{
		return "(".$field." IS NOT NULL AND DATALENGTH(".$field.") > 0)";
	}

	function _StringEQ($field, $sql_value)
	{
		return "CONVERT(VARCHAR(8000), ".$field.") = '".$sql_value."'";
	}

	function _StringNotEQ($field, $sql_value)
	{
		return "(".$field." IS NULL OR CONVERT(VARCHAR(8000), ".$field.") <> '".$sql_value."')";
	}

	function _StringIN($field, $sql_values)
	{
		return "CONVERT(VARCHAR(8000), ".$field.") in ('".implode("', '", $sql_values)."')";
	}

	function _StringNotIN($field, $sql_values)
	{
		return "(".$field." IS NULL OR CONVERT(VARCHAR(8000), ".$field.") not in ('".implode("', '", $sql_values)."'))";
	}

	function _ExprEQ($field, CSQLWhereExpression $val)
	{
		return "CONVERT(VARCHAR(8000), ".$field.") = ".$val->compile();
	}

	function _ExprNotEQ($field, CSQLWhereExpression $val)
	{
		return "(".$field." IS NULL OR CONVERT(VARCHAR(8000), ".$field.") <> ".$val->compile().")";
	}
}

/**
 * ��� ���������� �������� ��������� ������ ����� API ��������
 * � ���������� ������ � ����������������� ����������.
 * @global CUserTypeManager $GLOBALS['USER_FIELD_MANAGER']
 * @name $USER_FIELD_MANAGER
 */
$GLOBALS['USER_FIELD_MANAGER'] = new CUserTypeManager;
?>