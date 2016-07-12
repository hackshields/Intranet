<?
class CPerfomanceTableList extends CDBResult
{
	function GetList()
	{
		global $DB;
		$rsTables = $DB->Query("SELECT TABLE_SCHEMA, TABLE_NAME FROM INFORMATION_SCHEMA.TABLES ORDER BY TABLE_NAME");
		return new CPerfomanceTableList($rsTables);
	}

	function Fetch()
	{
		global $DB;
		$ar = parent::Fetch();
		if($ar)
		{
			$rsTableStat = $DB->Query("EXEC sp_spaceused N'".$ar["TABLE_SCHEMA"].".".$ar["TABLE_NAME"]."'");
			if($arTableStat = $rsTableStat->Fetch())
			{
				$ar = array(
					"TABLE_NAME" => $arTableStat["name"],
					"ENGINE_TYPE" => "",
					"NUM_ROWS" => $arTableStat["rows"],
					"BYTES" => preg_replace("/ KB$/", "", $arTableStat["data"])*1024,
				);
			}
			else
			{
				$ar = array(
					"TABLE_NAME" => $ar["TABLE_NAME"],
				);
			}
		}
		return $ar;
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
			FROM INFORMATION_SCHEMA.TABLES
			WHERE TABLE_NAME='".$DB->ForSQL($TABLE_NAME)."'
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
			$rs = $DB->Query("
				select
					s.indid as index_id
					,s.keyno as key_ordinal
					,c.name column_name
					,si.name index_name
				from sysindexkeys s
				inner join syscolumns c on s.id = c.id and s.colid = c.colid
				inner join sysobjects o on s.id = o.Id and o.xtype='U'
				left join sysindexes si on si.indid = s.indid and si.id=s.id
				where o.name=upper('".$DB->ForSql($TABLE_NAME)."')
				order by s.indid, s.keyno
			", true);

			$arResult = array();
			if($rs)
			{
				while($arInd = $rs->Fetch())
					$arResult[$arInd["index_name"]][$arInd["key_ordinal"]-1] = $arInd["column_name"];
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
			$rs = $DB->Query("
				select
					s.indid as index_id
					,s.keyno as key_ordinal
					,c.name column_name
					,si.name index_name
				from sysindexkeys s
				inner join syscolumns c on s.id = c.id and s.colid = c.colid
				inner join sysobjects o on s.id = o.Id and o.xtype='U'
				left join sysindexes si on si.indid = s.indid and si.id=s.id
				where o.name=upper('".$DB->ForSql($TABLE_NAME)."')
				AND 1 = IndexProperty(si.id, si.name, 'IsUnique')
				order by s.indid, s.keyno
			", true);

			$arResult = array();
			if($rs)
			{
				while($arInd = $rs->Fetch())
					$arResult[$arInd["index_name"]][$arInd["key_ordinal"]-1] = $arInd["column_name"];
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
				SELECT
					*
				FROM
					INFORMATION_SCHEMA.COLUMNS
				WHERE
					TABLE_NAME = '".$DB->ForSQL($TABLE_NAME)."'
				ORDER BY
					ORDINAL_POSITION
			";
			$rs = $DB->Query($strSql);
			$arResult = array();
			$arResultExt = array();
			while($ar = $rs->Fetch())
			{
				switch($ar["DATA_TYPE"])
				{
					case "char":
					case "nvarchar":
					case "text":
					case "varchar":
						$ar["DATA_TYPE"] = "string";
						$ar["DATA_LENGTH"] = $ar["CHARACTER_MAXIMUM_LENGTH"];
						break;
					case "datetime":
						$ar["DATA_TYPE"] = "datetime";
						break;
					case "bigint":
					case "decimal":
					case "float":
					case "numeric":
						$ar["DATA_TYPE"] = "double";
						break;
					case "int":
					case "tinyint":
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
					"nullable" => $ar["IS_NULLABLE"] !== "NO",
					"default" => trim($ar["COLUMN_DEFAULT"], "(')"),
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
				SELECT TOP ".IntVal($arNavParams["nTopCount"])."
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
			return $DB->Query($strSql);
		}
	}
}
?>