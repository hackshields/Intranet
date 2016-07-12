<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/classes/general/workflowtemplateloader.php");

class CBPWorkflowTemplateLoader
	extends CAllBPWorkflowTemplateLoader
{
	private static $instance;

	private function __construct()
	{
		$this->useGZipCompression = (function_exists("gzcompress") && !defined('BX_UTF'));
	}

	/**
	* Static method returns loader object. Singleton pattern.
	*
	* @return CBPWorkflowTemplateLoader
	*/
	public static function GetLoader()
	{
		if (!isset(self::$instance))
		{
			$c = __CLASS__;
			self::$instance = new $c;
		}

		return self::$instance;
	}

	public function GetTemplatesList($arOrder = array("ID" => "DESC"), $arFilter = array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
 		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "MODULE_ID", "ENTITY", "DOCUMENT_TYPE", "AUTO_EXECUTE", "NAME", "DESCRIPTION", "TEMPLATE", "PARAMETERS", "VARIABLES", "MODIFIED", "USER_ID", "ACTIVE");

		if (count(array_intersect($arSelectFields, array("MODULE_ID", "ENTITY", "DOCUMENT_TYPE"))) > 0)
		{
			if (!in_array("MODULE_ID", $arSelectFields))
				$arSelectFields[] = "MODULE_ID";
			if (!in_array("ENTITY", $arSelectFields))
				$arSelectFields[] = "ENTITY";
			if (!in_array("DOCUMENT_TYPE", $arSelectFields))
				$arSelectFields[] = "DOCUMENT_TYPE";
		}

		if (array_key_exists("DOCUMENT_TYPE", $arFilter))
		{
			$d = CBPHelper::ParseDocumentId($arFilter["DOCUMENT_TYPE"]);
			$arFilter["MODULE_ID"] = $d[0];
			$arFilter["ENTITY"] = $d[1];
			$arFilter["DOCUMENT_TYPE"] = $d[2];
		}

		if (array_key_exists("AUTO_EXECUTE", $arFilter))
		{
			$arFilter["AUTO_EXECUTE"] = intval($arFilter["AUTO_EXECUTE"]);

			if ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::None)
				$arFilter["AUTO_EXECUTE"] = 0;
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Create)
				$arFilter["AUTO_EXECUTE"] = array(1, 3, 5, 7);
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Edit)
				$arFilter["AUTO_EXECUTE"] = array(2, 3, 6, 7);
			elseif ($arFilter["AUTO_EXECUTE"] == CBPDocumentEventType::Delete)
				$arFilter["AUTO_EXECUTE"] = array(4, 5, 6, 7);
			else
				$arFilter["AUTO_EXECUTE"] = array(-1);
		}

		static $arFields = array(
			"ID" => Array("FIELD" => "T.ID", "TYPE" => "int"),
			"MODULE_ID" => Array("FIELD" => "T.MODULE_ID", "TYPE" => "string"),
			"ENTITY" => Array("FIELD" => "T.ENTITY", "TYPE" => "string"),
			"DOCUMENT_TYPE" => Array("FIELD" => "T.DOCUMENT_TYPE", "TYPE" => "string"),
			"AUTO_EXECUTE" => Array("FIELD" => "T.AUTO_EXECUTE", "TYPE" => "int"),
			"NAME" => Array("FIELD" => "T.NAME", "TYPE" => "string"),
			"DESCRIPTION" => Array("FIELD" => "T.DESCRIPTION", "TYPE" => "string"),
			"TEMPLATE" => Array("FIELD" => "T.TEMPLATE", "TYPE" => "string"),
			"PARAMETERS" => Array("FIELD" => "T.PARAMETERS", "TYPE" => "string"),
			"VARIABLES" => Array("FIELD" => "T.VARIABLES", "TYPE" => "string"),
			"MODIFIED" => Array("FIELD" => "T.MODIFIED", "TYPE" => "datetime"),
 			"USER_ID" => Array("FIELD" => "T.USER_ID", "TYPE" => "int"),
			"SYSTEM_CODE" => Array("FIELD" => "T.SYSTEM_CODE", "TYPE" => "string"),
			"ACTIVE" => Array("FIELD" => "T.ACTIVE", "TYPE" => "string"),

			"USER_NAME" => Array("FIELD" => "U.NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
			"USER_LAST_NAME" => Array("FIELD" => "U.LAST_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
			"USER_SECOND_NAME" => Array("FIELD" => "U.SECOND_NAME", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
			"USER_LOGIN" => Array("FIELD" => "U.LOGIN", "TYPE" => "string", "FROM" => "LEFT JOIN b_user U ON (T.USER_ID = U.ID)"),
		);

		$arSqls = CBPHelper::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_bp_workflow_template T ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!1!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_bp_workflow_template T ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) <= 0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_bp_workflow_template T ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			//echo "!2.1!=".htmlspecialcharsbx($strSql_tmp)."<br>";

			$dbRes = $DB->Query($strSql_tmp, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$cnt = 0;
			if ($arRes = $dbRes->Fetch())
				$cnt = $arRes["CNT"];

			$dbRes = new CDBResult();

			//echo "!2.3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
				$strSql = "SELECT * FROM (".$strSql.") WHERE ROWNUM<=".IntVal($arNavStartParams["nTopCount"]);

			//echo "!3!=".htmlspecialcharsbx($strSql)."<br>";

			$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$dbRes = new CBPWorkflowTemplateResult($dbRes, $this->useGZipCompression);
		return $dbRes;
	}

	public function AddTemplate($arFields)
	{
		global $DB;

		self::ParseFields($arFields, 0);

		$arInsert = $DB->PrepareInsert("b_bp_workflow_template", $arFields);

		$id = intval($DB->NextID("SQ_B_BP_WORKFLOW_TEMPLATE"));

		$strSql =
			"INSERT INTO b_bp_workflow_template (ID, ".$arInsert[0].", MODIFIED) ".
			"VALUES(".$id.", ".$arInsert[1].", ".$DB->CurrentTimeFunction().")";

		$arBinds = Array();
		if (is_set($arFields, "DESCRIPTION"))
			$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];
		if (is_set($arFields, "TEMPLATE"))
			$arBinds["TEMPLATE"] = $arFields["TEMPLATE"];
		if (is_set($arFields, "PARAMETERS"))
			$arBinds["PARAMETERS"] = $arFields["PARAMETERS"];
		if (is_set($arFields, "VARIABLES"))
			$arBinds["VARIABLES"] = $arFields["VARIABLES"];

		$DB->QueryBind($strSql, $arBinds);

		return $id;
	}

	public function UpdateTemplate($id, $arFields)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
			throw new CBPArgumentNullException("id");

		self::ParseFields($arFields, $id);

		$strUpdate = $DB->PrepareUpdate("b_bp_workflow_template", $arFields);

		$strSql =
			"UPDATE b_bp_workflow_template SET ".
			"	".$strUpdate.", ".
			"	MODIFIED = ".$DB->CurrentTimeFunction()." ".
			"WHERE ID = ".intval($id)." ";

		$arBinds = Array();
		if (is_set($arFields, "DESCRIPTION"))
			$arBinds["DESCRIPTION"] = $arFields["DESCRIPTION"];
		if (is_set($arFields, "TEMPLATE"))
			$arBinds["TEMPLATE"] = $arFields["TEMPLATE"];
		if (is_set($arFields, "PARAMETERS"))
			$arBinds["PARAMETERS"] = $arFields["PARAMETERS"];
		if (is_set($arFields, "VARIABLES"))
			$arBinds["VARIABLES"] = $arFields["VARIABLES"];

		$DB->QueryBind($strSql, $arBinds);

		return $id;
	}
}
?>
