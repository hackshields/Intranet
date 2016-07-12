<?
class CPerfomanceTableList extends CDBResult
{
	function GetList()
	{
		global $DB;
		$rsTables = $DB->Query("
			SELECT
				TABLE_NAME
				,NUM_ROWS
				,S.BYTES
			FROM
				USER_TABLES T
				,(select segment_name,sum(bytes) BYTES from user_segments group by segment_name) S
			WHERE
				T.TABLE_NAME = S.SEGMENT_NAME
				AND TABLE_NAME NOT LIKE 'BIN$%'
			ORDER BY
				TABLE_NAME
		");
		return new CPerfomanceTableList($rsTables);
	}
}

class CPerfomanceTable extends CAllPerfomanceTable
{
	var $TABLE_NAME;

	function Init($TABLE_NAME)
	{
		$this->TABLE_NAME = $TABLE_NAME;
	}

	function IsExists($TABLE_NAME = false)
	{
		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;
		global $DB;
		$strSql = "
			SELECT TABLE_NAME
			FROM USER_TABLES
			WHERE TABLE_NAME = '".$DB->ForSQL($TABLE_NAME)."' OR TABLE_NAME = UPPER('".$DB->ForSQL($TABLE_NAME)."')
		";
		$rs = $DB->Query($strSql);
		if($rs->Fetch())
			return true;
		else
			return false;
	}

	function GetIndexes($TABLE_NAME = false)
	{
		static $cache = array();

		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;

		if(!array_key_exists($TABLE_NAME, $cache))
		{
			global $DB;

			$strSql = "
				SELECT INDEX_NAME
				FROM USER_INDEXES
				WHERE (TABLE_NAME='".$DB->ForSQL($TABLE_NAME)."' OR TABLE_NAME = UPPER('".$DB->ForSQL($TABLE_NAME)."'))
				AND INDEX_TYPE = 'NORMAL'
				ORDER BY INDEX_NAME
			";
			$rsInd = $DB->Query($strSql);
			$arResult = array();
			while($arInd = $rsInd->Fetch())
			{
				$strSql = "
					SELECT COLUMN_NAME
					FROM USER_IND_COLUMNS
					WHERE (TABLE_NAME='".$DB->ForSQL($TABLE_NAME)."' OR TABLE_NAME = UPPER('".$DB->ForSQL($TABLE_NAME)."'))
					AND INDEX_NAME = '".$DB->ForSQL($arInd["INDEX_NAME"])."'
					ORDER BY COLUMN_POSITION
				";
				$rsCol = $DB->Query($strSql);
				while($arCol = $rsCol->Fetch())
				{
					$arResult[$arInd["INDEX_NAME"]][] = $arCol["COLUMN_NAME"];
				}
			}
			$cache[$TABLE_NAME] = $arResult;
		}

		return $cache[$TABLE_NAME];
	}

	function GetUniqueIndexes($TABLE_NAME = false)
	{
		static $cache = array();

		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;

		if(!array_key_exists($TABLE_NAME, $cache))
		{
			global $DB;

			$strSql = "
				SELECT INDEX_NAME
				FROM USER_INDEXES
				WHERE (TABLE_NAME='".$DB->ForSQL($TABLE_NAME)."' OR TABLE_NAME = UPPER('".$DB->ForSQL($TABLE_NAME)."'))
				AND INDEX_TYPE = 'NORMAL'
				AND UNIQUENESS = 'UNIQUE'
				ORDER BY INDEX_NAME
			";
			$rsInd = $DB->Query($strSql);
			$arResult = array();
			while($arInd = $rsInd->Fetch())
			{
				$strSql = "
					SELECT COLUMN_NAME
					FROM USER_IND_COLUMNS
					WHERE (TABLE_NAME='".$DB->ForSQL($TABLE_NAME)."' OR TABLE_NAME = UPPER('".$DB->ForSQL($TABLE_NAME)."'))
					AND INDEX_NAME = '".$DB->ForSQL($arInd["INDEX_NAME"])."'
					ORDER BY COLUMN_POSITION
				";
				$rsCol = $DB->Query($strSql);
				while($arCol = $rsCol->Fetch())
				{
					$arResult[$arInd["INDEX_NAME"]][] = $arCol["COLUMN_NAME"];
				}
			}
			$cache[$TABLE_NAME] = $arResult;
		}

		return $cache[$TABLE_NAME];
	}

	function GetTableFields($TABLE_NAME = false, $bExtended = false)
	{
		static $cache = array();

		if($TABLE_NAME===false)
			$TABLE_NAME = $this->TABLE_NAME;

		if(!array_key_exists($TABLE_NAME, $cache))
		{
			global $DB;

			$strSql = "
				SELECT COLUMN_NAME, DATA_TYPE DATA_TYPE_ORIG, DATA_PRECISION, CHAR_LENGTH, DATA_DEFAULT, NULLABLE
				FROM USER_TAB_COLUMNS
				WHERE (TABLE_NAME='".$DB->ForSQL($TABLE_NAME)."' OR TABLE_NAME = UPPER('".$DB->ForSQL($TABLE_NAME)."'))
				ORDER BY COLUMN_ID
			";
			$rs = $DB->Query($strSql);
			$arResult = array();
			$arResultExt = array();
			while($ar = $rs->Fetch())
			{
				switch($ar["DATA_TYPE_ORIG"])
				{
					case "CHAR":
					case "VARCHAR2":
						$ar["DATA_TYPE"] = "string";
						$ar["DATA_LENGTH"] = $ar["CHAR_LENGTH"];
						break;
					case "DATE":
						$ar["DATA_TYPE"] = "datetime";
						break;
					case "NUMBER":
					case "FLOAT":
						if(strlen($ar["DATA_PRECISION"]) < 1)
							$ar["DATA_TYPE"] = "double";
						else
							$ar["DATA_TYPE"] = "int";
						break;
					default:
						$ar["DATA_TYPE"] = "unknown";
						break;
				}
				$arResult[$ar["COLUMN_NAME"]] = $ar["DATA_TYPE"];
				$arResultExt[$ar["COLUMN_NAME"]] = array(
					"type" => $ar["DATA_TYPE"],
					"length" => $ar["DATA_LENGTH"],
					"nullable" => $ar["NULLABLE"] === "Y",
					"default" => trim($ar["DATA_DEFAULT"], "' \n\r\t"),
					//"info" => $ar,
				);
			}
			$cache[$TABLE_NAME] = array($arResult, $arResultExt);
		}

		if($bExtended)
			return $cache[$TABLE_NAME][1];
		else
			return $cache[$TABLE_NAME][0];
	}

	function NavQuery($arNavParams, $arQuerySelect, $strTableName, $strQueryWhere, $arQueryOrder)
	{
		global $DB;
		if(IntVal($arNavParams["nTopCount"]) <= 0)
		{
			$strSql = "
				SELECT
					count(*) C
				FROM
					".$strTableName." t
			";
			if($strQueryWhere)
			{
				$strSql .= "
					WHERE
					".$strQueryWhere."
				";
			}
			$res_cnt = $DB->Query($strSql);
			$res_cnt = $res_cnt->Fetch();
			$cnt = $res_cnt["C"];

			$strSql = "
				SELECT
				".implode(", ", $arQuerySelect)."
				FROM
					".$strTableName." t
			";
			if($strQueryWhere)
			{
				$strSql .= "
					WHERE
					".$strQueryWhere."
				";
			}
			if(count($arQueryOrder) > 0)
			{
				$strSql .= "
					ORDER BY
					".implode(", ", $arQueryOrder)."
				";
			}

			$res = new CDBResult();
			$res->NavQuery($strSql, $cnt, $arNavParams);

			return $res;
		}
		else
		{
			$strSql = "
				SELECT
				".implode(", ", $arQuerySelect)."
				FROM
					".$strTableName." t
			";
			if($strQueryWhere)
			{
				$strSql .= "
					WHERE
					".$strQueryWhere."
				";
			}
			if(count($arQueryOrder) > 0)
			{
				$strSql .= "
					ORDER BY
					".implode(", ", $arQueryOrder)."
				";
			}
			$strSql = "SELECT * FROM (".$strSql.") WHERE ROWNUM <= ".IntVal($arNavParams["nTopCount"]);
			return $DB->Query($strSql);
		}
	}
}
?>