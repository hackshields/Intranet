<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/usertype.php");

class CUserTypeEntity extends CAllUserTypeEntity
{

	function CreatePropertyTables($entity_id)
	{
		global $DB, $APPLICATION;
		if(!$DB->TableExists("B_UTM_".$entity_id))
		{
			$rs = $DB->Query("
				create table B_UTM_".$entity_id." (
					ID number(18) not null,
					VALUE_ID number(18) not null,
					FIELD_ID number(18) not null,
					VALUE varchar2(2000),
					VALUE_INT number(18),
					VALUE_DOUBLE number,
					VALUE_DATE date
				)
			", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("ALTER TABLE B_UTM_".$entity_id." ADD CONSTRAINT PK_UTM_".$entity_id." PRIMARY KEY(ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE INDEX IX_UTM_".$entity_id."_1 ON B_UTM_".$entity_id." (FIELD_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE INDEX IX_UTM_".$entity_id."_2 ON B_UTM_".$entity_id." (VALUE_ID)", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query("CREATE SEQUENCE SQ_B_UTM_".$entity_id." INCREMENT BY 1 START WITH 1", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__);
			if($rs)
				$rs = $DB->Query(
"CREATE OR REPLACE TRIGGER TR_UTM_".$entity_id."_I
BEFORE INSERT
ON B_UTM_".$entity_id."
FOR EACH ROW
BEGIN
	IF :NEW.ID IS NULL THEN
		SELECT SQ_B_UTM_".$entity_id.".NEXTVAL INTO :NEW.ID FROM dual;
	END IF;
END;", false, "FILE: ".__FILE__."<br>LINE: ".__LINE__
				);

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
				create table B_UTS_".$entity_id." (
					VALUE_ID number(18) not null
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
		return array("ALTER TABLE ".$strTable." DROP (".implode(",", $arColumns).")");
	}
}

class CUserTypeManager extends CAllUserTypeManager
{

	function DateTimeToChar($FIELD_NAME)
	{
		global $DB;
		return "DECODE(TRUNC(".$FIELD_NAME."), ".$FIELD_NAME.", ".$DB->DateToCharFunction($FIELD_NAME, "SHORT").", ".$DB->DateToCharFunction($FIELD_NAME, "FULL").")";
	}
}

class CSQLWhere extends CAllSQLWhere
{
	function _Upper($field)
	{
		return "UPPER(".$field.")";
	}

	function _Empty($field)
	{
		return "(".$field." IS NULL)";
	}

	function _NotEmpty($field)
	{
		return "(".$field." IS NOT NULL)";
	}

	function _StringEQ($field, $sql_value)
	{
		return $field." = '".$sql_value."'";
	}

	function _StringNotEQ($field, $sql_value)
	{
		return "(".$field." IS NULL OR ".$field." <> '".$sql_value."')";
	}

	function _StringIN($field, $sql_values)
	{
		return $field." in ('".implode("', '", $sql_values)."')";
	}

	function _StringNotIN($field, $sql_values)
	{
		return "(".$field." IS NULL OR ".$field." not in ('".implode("', '", $sql_values)."'))";
	}

	function _ExprEQ($field, CSQLWhereExpression $val)
	{
		return $field." = ".$val->compile();
	}

	function _ExprNotEQ($field, CSQLWhereExpression $val)
	{
		return "(".$field." IS NULL OR ".$field." <> ".$val->compile().")";
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