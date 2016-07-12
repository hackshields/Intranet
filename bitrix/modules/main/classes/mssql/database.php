<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/database.php");

/********************************************************************
*	MSSQL database classes
********************************************************************/
class CDatabase extends CAllDatabase
{
	var $DBName;
	var $DBHost;
	var $DBLogin;
	var $DBPassword;
	var $bConnected;
	var $version;
	var $cntQuery;
	var $timeQuery;
	var $open_transaction = false;
	var $XE;

	public
		$escL = '[',
		$escR = ']';

	public
		$alias_length = 30;

	function GetVersion()
	{
		if($this->version)
			return $this->version;

		$rs = $this->Query("SELECT @@VERSION as R", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		if ($ar = $rs->Fetch())
		{
			$version = trim($ar["R"]);
			$this->XE = (strpos($version, "Express Edition")>0);
			preg_match("#[0-9]+\\.[0-9]+\\.[0-9]+#", $version, $arr);
			$version = $arr[0];
			$this->version = $version;
			return $version;
		}
		else
		{
			return false;
		}
	}

	function StartTransaction()
	{
		$this->DoConnect();
		odbc_autocommit($this->db_Conn, false);
	}

	function Commit()
	{
		$this->DoConnect();
		odbc_commit($this->db_Conn);
		odbc_autocommit($this->db_Conn, true);
	}

	function Rollback()
	{
		$this->DoConnect();
		odbc_rollback($this->db_Conn);
		odbc_autocommit($this->db_Conn, true);
	}

	//Connect to database
	function Connect($DBHost, $DBName, $DBLogin, $DBPassword)
	{
		$this->type="MSSQL";
		$this->DBHost = $DBHost;
		$this->DBName = $DBName;
		$this->DBLogin = $DBLogin;
		$this->DBPassword = $DBPassword;
		$this->bConnected = false;

		if(!defined("DBPersistent"))
			define("DBPersistent", true);

		if(defined("DELAY_DB_CONNECT") && DELAY_DB_CONNECT===true)
			return true;
		else
			return $this->DoConnect();
	}

	function DoConnect()
	{
		if($this->bConnected)
			return true;
		$this->bConnected = true;

		//$DSN = "DRIVER={SQL Server}; SERVER={".$DBHost."};UID={".$DBLogin."};PWD={".$DBPassword."}; DATABASE={".$DBName."}";
		$DSN = $this->DBHost;

		if(DBPersistent)
			$this->db_Conn = odbc_pconnect($DSN, $this->DBLogin, $this->DBPassword);
		else
			$this->db_Conn = odbc_connect($DSN, $this->DBLogin, $this->DBPassword);

		if(!$this->db_Conn)
		{
			$s = (DBPersistent? "odbc_pconnect":"odbc_connect");
			if($this->debug || (@session_start() && $_SESSION["SESS_AUTH"]["ADMIN"]))
				echo "<br><font color=#ff0000>Error! ".$s."('-', '-', '-')</font><br>#".odbc_error()." ".odbc_errormsg()."<br>";

			SendError("Error! ".$s."('-', '-', '-')\n#".odbc_error()." ".odbc_errormsg()."\n");
			return false;
		}
		odbc_autocommit($this->db_Conn, true);

		if($this->DBName <> "")
		{
			$res = $this->Query('USE "'.$this->DBName.'"', true);
			if(!$res)
			{
				if($this->debug || (@session_start() && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo "<br><font color=#ff0000>Error! ('-', '-', '-')</font><br>#".$this->GetErrorMessage()."<br>";

				SendError("Error! ('-', '-', '-')\n#".$this->GetErrorMessage()."\n");
				return false;
			}
		}

		$this->cntQuery = 0;
		$this->timeQuery = 0;
		$this->arQueryDebug = array();

		global /** @noinspection PhpUnusedLocalVariableInspection */
		$DB, $USER, $APPLICATION;
		if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/after_connect.php"))
			include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/after_connect.php");
		return true;
	}

	//This function executes query against database
	function Query($strSql, $bIgnoreErrors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		$this->DoConnect();
		$this->db_Error="";

		if($this->DebugToFile || $DB->ShowSqlStat)
			$start_time = microtime(true);

		$result = @odbc_exec($this->db_Conn, $strSql);

		if($this->DebugToFile || $DB->ShowSqlStat)
		{
			/** @noinspection PhpUndefinedVariableInspection */
			$exec_time = round(microtime(true) - $start_time, 10);

			if($DB->ShowSqlStat)
				$DB->addDebugQuery($strSql, $exec_time);

			if($this->DebugToFile)
			{
				$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/mssql_debug.sql","ab+");
				$str = "TIME: ".$exec_time." SESSION: ".session_id()." CONN: ".$this->db_Conn."\n";
				$str .= $strSql."\n\n";
				$str .= "----------------------------------------------------\n\n";
				fputs($fp, $str);
				@fclose($fp);
			}
		}

		if(!$result)
		{
			$this->db_Error = "#".odbc_error()." ".odbc_errormsg();
			$this->db_ErrorSQL = $strSql;
			if(!$bIgnoreErrors)
			{
				AddMessage2Log($error_position." MSSQL Query Error: ".$strSql." [".$this->db_Error."]", "main");
				if ($this->DebugToFile)
				{
					$fp = fopen($_SERVER["DOCUMENT_ROOT"]."/mssql_debug.sql","ab+");
					fputs($fp,"SESSION: ".session_id()." ERROR: ".$this->db_Error."\n\n----------------------------------------------------\n\n");
					@fclose($fp);
				}

				if($this->debug || (@session_start() && $_SESSION["SESS_AUTH"]["ADMIN"]))
					echo $error_position."<br>MSSQL Query Error:<br><font color=#ff0000>".htmlspecialcharsbx($strSql)."</font><br>".htmlspecialcharsbx($this->db_Error)."<br>";

				$error_position = preg_replace("/<br>/i", "\n", $error_position);
				SendError($error_position."\nMSSQL Query Error:\n".$strSql." \n [".$this->db_Error."]\n---------------\n\n");

				if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/dbquery_error.php");
				elseif(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php"))
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/dbquery_error.php");
				else
					die("MSSQL Query Error!");

				die();
			}
			return false;
		}
		if(defined("BX_MSSQL_BINMODE") && BX_MSSQL_BINMODE == true)
		{
			@odbc_binmode($result, ODBC_BINMODE_PASSTHRU);
			@odbc_longreadlen($result, 9999999);
		}
		$res = new CDBResult($result);
		$res->DB = $this;
		$res->num_rows_affected = intval(odbc_num_rows($result));
		if($DB->ShowSqlStat)
			$res->SqlTraceIndex = count($DB->arQueryDebug) - 1;
		return $res;
	}

	function QueryLong($strSql, $bIgnoreErrors = false)
	{
		return $this->Query($strSql, $bIgnoreErrors);
	}

	function CurrentTimeFunction()
	{
		return "getdate()";
	}

	function CurrentDateFunction()
	{
		return "
			convert(
				datetime,
				cast(year(getdate()) as varchar(4)) + '-' +
				cast(month(getdate()) as varchar(2)) + '-' +
				cast(day(getdate()) as varchar(2)),
				120
				)
			";
	}

	function DateFormatToDB($format, $field)
	{
		static $search  = array("YYYY", "MMMM", "MM", "MI", "M", "DD", "HH", "H", "GG", "G", "SS", "TT", "T");
		static $replace = array("~YYYY", "~MMMM", "~MM", "~MI", "~M", "~DD", "~HH", "~H", "~GG", "~G", "~SS", "~TT", "~T");

		$format = preg_replace("#([^(YYYY|MMMM|MM|MI|M|DD|HH|H|GG|G|SS|TT|T)])#", "+'\\1'+", $format);
		$format = str_replace($search, $replace, $format);

		$format = str_replace("++", "+", $format);
		$format = str_replace("~~", "~", $format);
		$format = str_replace("~M~M~M~M", "MMMM", $format);
		$format = str_replace("~M~M", "~MM", $format);
		$format = str_replace("~T~T", "~TT", $format);
		$format = str_replace("~H~H", "~HH", $format);
		$format = str_replace("~G~G", "~GG", $format);

		$format = str_replace("~YYYY", "\n\tCONVERT(varchar(4),DATEPART(yyyy, $field))", $format);
		$format = str_replace("~MMMM", "\n\tdatename(mm, $field)", $format);
		$format = str_replace("~MM", "\n\tREPLICATE('0',2-LEN(DATEPART(mm, $field)))+CONVERT(varchar(2),DATEPART(mm, $field))", $format);
		$format = str_replace("~MI", "\n\tREPLICATE('0',2-LEN(DATEPART(mi, $field)))+CONVERT(varchar(2),DATEPART(mi, $field))", $format);
		$format = str_replace("~M", "\n\tCONVERT(varchar(3), $field,7)", $format);

		$format = str_replace("~DD", "\n\tREPLICATE('0',2-LEN(DATEPART(dd, $field)))+CONVERT(varchar(2),DATEPART(dd, $field))", $format);
		if (strpos($format, '~T') !== false)
		{
			$format = str_replace("~H", "\n\tCASE WHEN DATEPART(HH, $field) < 13 THEN RIGHT(REPLICATE('0',2) + CAST(datepart(HH, $field) AS VARCHAR(2)),2) ELSE RIGHT(REPLICATE('0',2) + CAST(datepart(HH, dateadd(HH, -12, $field)) AS VARCHAR(2)), 2) END", $format);
			$format = str_replace("~G", "\n\tCASE WHEN DATEPART(HH, $field) < 13 THEN RIGHT(REPLICATE('0',2) + CAST(datepart(HH, $field) AS VARCHAR(2)),2) ELSE RIGHT(REPLICATE('0',2) + CAST(datepart(HH, dateadd(HH, -12, $field)) AS VARCHAR(2)), 2) END", $format);
			$format = str_replace("~TT", "\n\tCASE WHEN DATEPART(HH, $field) < 13 THEN 'AM' ELSE 'PM' END", $format);
			$format = str_replace("~T", "\n\tCASE WHEN DATEPART(HH, $field) < 13 THEN 'AM' ELSE 'PM' END", $format);
		}
		else
		{
			$format = str_replace("~HH", "\n\tREPLICATE('0',2-LEN(DATEPART(hh, $field)))+CONVERT(varchar(2),DATEPART(hh, $field))", $format);
			$format = str_replace("~GG", "\n\tREPLICATE('0',2-LEN(DATEPART(hh, $field)))+CONVERT(varchar(2),DATEPART(hh, $field))", $format);
		}

		$format = str_replace("~SS", "\n\tREPLICATE('0',2-LEN(DATEPART(ss, $field)))+CONVERT(varchar(2),DATEPART(ss, $field))", $format);
		$format = "case when isdate($field)=0 then null else $format \nend";
		return $format;
	}

	function DateToCharFunction($field, $format_type="FULL", $lang=false)
	{
		$format = CLang::GetDateFormat($format_type, $lang);
		$sFieldExpr = $field;

		//time zone
		if($format_type == "FULL" && CTimeZone::Enabled())
		{
			static $diff = false;
			if($diff === false)
				$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$sFieldExpr = "DATEADD(second, ".$diff.", ".$field.")";
		}

		return $this->DateFormatToDB($format, $sFieldExpr);
	}

	function CharToDateFunction($value, $format_type="FULL", $lang=false)
	{
		$value = trim($value);
		if($value == '')
			return "NULL";

		$value = CDatabase::FormatDate($value, CLang::GetDateFormat($format_type, $lang), ($format_type=="SHORT"? "YYYY-MM-DD":"YYYY-MM-DD HH:MI:SS"));
		$sFieldExpr = "convert(datetime, '".$this->ForSql($value)."', 120)";

		//time zone
		if($format_type == "FULL" && CTimeZone::Enabled())
		{
			static $diff = false;
			if($diff === false)
				$diff = CTimeZone::GetOffset();

			if($diff <> 0)
				$sFieldExpr = "DATEADD(second, -(".$diff."), ".$sFieldExpr.")";
		}

		return $sFieldExpr;
	}

	function DatetimeToDateFunction($strValue)
	{
		return 'DATEADD(dd, DATEDIFF(dd, 0, '.$strValue.'), 0)';
	}

	//  1 if date1 > date2
	//  0 if date1 = date2
	// -1 if date1 < date2
	function CompareDates($date1, $date2)
	{
		$s_date1 = $this->CharToDateFunction($date1);
		$s_date2 = $this->CharToDateFunction($date2);
		$strSql = "
			SELECT
				CASE
					when $s_date1 > $s_date2 then 1
					when $s_date1 = $s_date2 then 0
					when $s_date1 < $s_date2 then -1
					else 'x'
				END as RES
			";
		$z = $this->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$zr = $z->Fetch();
		return $zr["RES"];
	}

	function LastID()
	{
		$rs = $this->Query("SELECT @@IDENTITY as ID", false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		$ar = $rs->Fetch();
		return $ar["ID"];
	}

	//Closes database connection
	function Disconnect()
	{
		if(!DBPersistent && $this->bConnected)
		{
			$this->bConnected = false;
			odbc_close($this->db_Conn);
		}

		foreach(self::$arNodes as $arNode)
		{
			if(is_array($arNode) && array_key_exists("DB", $arNode))
			{
				odbc_close($arNode["DB"]->db_Conn);
				unset($arNode["DB"]);
			}
		}
	}

	function PrepareFields($strTableName, $strPrefix = "str_", $strSuffix = "")
	{
		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $arColumn)
		{
			$column = $arColumn["NAME"];
			$type = $arColumn["TYPE"];
			global $$column;
			$var = $strPrefix.$column.$strSuffix;
			global $$var;
			switch ($type)
			{
				case "int":
				case "tinyint":
				case "smallint":
				case "bigint":
					$$var = intval($$column);
					break;
				case "real":
				case "float":
					$$var = doubleval($$column);
					break;
				default:
					$$var = $this->ForSql($$column);
			}
		}
	}

	function PrepareInsert($strTableName, $arFields, $strFileDir="", $lang=false)
	{
		$strInsert1 = "";
		$strInsert2 = "";

		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if(isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];
				if($value === false)
				{
					$strInsert1 .= ", ".$strColumnName;
					$strInsert2 .= ",  NULL ";
				}
				else
				{
					$strInsert1 .= ", ".$strColumnName;
					switch ($type)
					{
						case "datetime":
						case "timestamp":
							if(strlen($value)<=0)
								$strInsert2 .= ", NULL ";
							else
								$strInsert2 .= ", ".$this->CharToDateFunction($value);
							break;
						case "int":
						case "tinyint":
						case "smallint":
						case "bigint":
							$strInsert2 .= ", '".intval($value)."'";
							break;
						case "decimal":
						case "numeric":
							$strInsert2 .= ", '".round(doubleval($value), intval($arColumnInfo["NUMERIC_SCALE"]))."'";
							break;
						case "real":
						case "float":
							$strInsert2 .= ", '".doubleval($value)."'";
							break;
						case "image":
							$strInsert2 .= ", ".$value;
							break;
						default:
							$strInsert2 .= ", '".$this->ForSql($value, $arColumnInfo['CHARACTER_MAXIMUM_LENGTH'])."'";
					}
				}
			}
			elseif(array_key_exists("~".$strColumnName, $arFields))
			{
				$strInsert1 .= ", ".$strColumnName;
				$strInsert2 .= ", ".$arFields["~".$strColumnName];
			}
		}

		if($strInsert1!="")
		{
			$strInsert1 = substr($strInsert1, 2);
			$strInsert2 = substr($strInsert2, 2);
		}
		return array($strInsert1, $strInsert2);
	}

	function PrepareUpdate($strTableName, $arFields, $strFileDir="", $lang = false, $strTableAlias = "")
	{
		$arBinds = array();
		return $this->PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, $arBinds, $strTableAlias);
	}

	function PrepareUpdateBind($strTableName, $arFields, $strFileDir, $lang, &$arBinds, $strTableAlias = "")
	{
		$arBinds = array();
		if ($strTableAlias != "")
			$strTableAlias .= ".";
		$strUpdate = "";
		$arColumns = $this->GetTableFields($strTableName);
		foreach($arColumns as $strColumnName => $arColumnInfo)
		{
			$type = $arColumnInfo["TYPE"];
			if(isset($arFields[$strColumnName]))
			{
				$value = $arFields[$strColumnName];
				if($value === false)
				{
					$strUpdate .= ", $strTableAlias".$strColumnName." = NULL";
				}
				else
				{
					switch ($type)
					{
						case "int":
						case "tinyint":
						case "smallint":
						case "bigint":
							$value = intval($value);
							break;
						case "decimal":
						case "numeric":
							$value = round(doubleval($value), intval($arColumnInfo["NUMERIC_SCALE"]));
							break;
						case "real":
							$value = doubleval($value);
							break;
						case "datetime":
						case "timestamp":
							$value = (strlen(trim($value))<=0) ? "NULL" : $this->CharToDateFunction($value);
							break;
						case "image":
							break;
						default:
							$value = "'".$this->ForSql($value, $arColumnInfo['CHARACTER_MAXIMUM_LENGTH'])."'";
					}
					$strUpdate .= ", $strTableAlias".$strColumnName." = ".$value;
				}
			}
			elseif(is_set($arFields, "~".$strColumnName))
			{
				$strUpdate .= ", $strTableAlias".$strColumnName." = ".$arFields["~".$strColumnName];
			}
		}

		if($strUpdate!="")
			$strUpdate = substr($strUpdate, 2);

		return $strUpdate;
	}

	function Insert($table, $arFields, $error_position="", $DEBUG=false, $EXIST_ID="", $ignore_errors=false)
	{
		if (!is_array($arFields))
			return false;

		$str1 = "";
		$str2 = "";
		foreach($arFields as $field => $value)
		{
			$str1 .= ($str1 <> ""? ", ":"").$field;
			$str2 .= ($str2 <> ""? ", ":"").(strlen($value)<=0? "''"  : $value);
		}

		if (strlen($EXIST_ID) > 0)
		{
			$this->Query("SET IDENTITY_INSERT ".$table." ON", $ignore_errors, $error_position);
			$strSql = "INSERT INTO ".$table."(ID,".$str1.") VALUES ('".$this->ForSql($EXIST_ID)."',".$str2.")";
		}
		else
		{
			$strSql = "INSERT INTO ".$table."(".$str1.") VALUES (".$str2.")";
		}

		if ($DEBUG)
			echo "<br>".htmlspecialcharsEx($strSql)."<br>";

		$res = $this->Query($strSql, $ignore_errors, $error_position);

		if($res === false)
		{
			$this->Query("SET IDENTITY_INSERT ".$table." OFF", $ignore_errors, $error_position);
			return false;
		}

		if (strlen($EXIST_ID) > 0)
		{
			$this->Query("SET IDENTITY_INSERT ".$table." OFF", $ignore_errors, $error_position);
			return $EXIST_ID;
		}
		else
		{
			return $this->LastID();
		}
	}

	function Update($table, $arFields, $WHERE="", $error_position="", $DEBUG=false, $ignore_errors=false)
	{
		$rows = 0;
		if (is_array($arFields))
		{
			$ar = array();
			foreach ($arFields as $field => $value)
			{
				if (strlen($value) <= 0)
					$ar[] = $field." = ''";
				else
					$ar[] = $field." = ".$value;
			}
			$strSql = "UPDATE ".$table." SET ".implode(", ", $ar)." ".$WHERE;
			if ($DEBUG)
				echo "<br>".$strSql."<br>";
			$w = $this->Query($strSql, $ignore_errors, $error_position);
			if (is_object($w))
				$rows = $w->AffectedRowsCount();
			if ($DEBUG)
				echo "affected_rows = ".$rows."<br>";
		}
		return $rows;
	}

	function Add($tablename, $arFields, $arCLOBFields = Array(), $strFileDir="", $ignore_errors=false, $error_position="", $arOptions=array())
	{
		global $DB;

		if(!is_object($this) || !isset($this->type))
		{
			return $DB->Add($tablename, $arFields, $arCLOBFields, $strFileDir, $ignore_errors, $error_position, $arOptions);
		}
		else
		{
			$arInsert = $this->PrepareInsert($tablename, $arFields, $strFileDir, false);
			$strSql = "INSERT INTO ".$tablename."(".$arInsert[0].") VALUES(".$arInsert[1].")";
			$this->Query($strSql, $ignore_errors, $error_position, $arOptions);
			return $this->LastID();
		}
	}

	function TopSql($strSql, $nTopCount)
	{
		$nTopCount = intval($nTopCount);
		if($nTopCount>0)
			return preg_replace("#^\\s*SELECT(\\s+DISTINCT|)\\s+#is", "SELECT \\1 TOP ".$nTopCount." ", $strSql);
		else
			return $strSql;
	}

	function ForSql($value, $max=0)
	{
		return str_replace("\x00", "", ($max>0) ? str_replace("'","''", substr($value, 0, $max)) : str_replace("'","''", $value));
	}

	function ForSqlLike($value, $max=0)
	{
		if ($max>0)
			$value = substr($value, 0, $max);

		return str_replace("\x00", "", str_replace("'", "''", str_replace("\\", "\\\\\\\\", $value)));
	}

	function InitTableVarsForEdit($table, $prefix_from = "str_", $prefix_to="str_", $suffix_from="", $safe_for_html=false)
	{
		$arColumns = $this->GetTableFields($table);
		if (is_array($arColumns) && count($arColumns)>0)
		{
			foreach($arColumns as $arColumn)
			{
				$column = $arColumn["NAME"];
				$var_from = $prefix_from.$column.$suffix_from;
				$var_to = $prefix_to.$column;
				global ${$var_from}, ${$var_to};
				if ((isset(${$var_from}) || $safe_for_html))
				{
					if (is_array(${$var_from}))
					{
						${$var_to} = array();
						foreach(${$var_from} as $k => $v)
							${$var_to}[$k] = htmlspecialcharsbx($v);
					}
					else
						${$var_to} = htmlspecialcharsbx(${$var_from});
				}
			}
		}
	}

	function GetTableFieldsList($table)
	{
		return array_keys($this->GetTableFields($table));
	}

	function GetTableFields($table)
	{
		if(!array_key_exists($table, $this->column_cache))
		{
			$this->column_cache[$table] = array();
			$strSql = "
				SELECT
					*,
					COLUMN_NAME as NAME,
					DATA_TYPE as TYPE
				FROM
					INFORMATION_SCHEMA.COLUMNS
				WHERE
					TABLE_NAME = '$table'
			";
			$rs = $this->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			while($ar = $rs->Fetch())
				$this->column_cache[$table][$ar["NAME"]] = $ar;
		}
		return $this->column_cache[$table];
	}

	function Concat()
	{
		$str = "";
		$ar = func_get_args();
		if (is_array($ar)) $str .= implode(" + ", $ar);
		return $str;
	}

	function Substr($str, $from, $length = null)
	{
		$sql = 'SUBSTRING('.$str.', '.$from;

		if (!is_null($length))
		{
			$sql .= ', '.$length;
		}
		else
		{
			$sql .= ', LEN('.$str.') + 1 - '.$from;
		}

		return $sql.')';
	}

	function IsNull($expression, $result)
	{
		return "ISNULL(".$expression.", ".$result.")";
	}

	function Length($field)
	{
		return "len($field)";
	}

	function TableExists($tableName)
	{
		$tableName = preg_replace("/[^A-Za-z0-9%_]+/i", "", $tableName);
		$tableName = Trim($tableName);

		if (strlen($tableName) <= 0)
			return False;

		$dbResult = $this->Query(
				"SELECT TABLE_NAME ".
				"FROM INFORMATION_SCHEMA.TABLES ".
				"WHERE TABLE_NAME LIKE '".$this->ForSql($tableName)."'"
			);
		if ($arResult = $dbResult->Fetch())
			return True;
		else
			return False;
	}

	function IndexExists($tableName, $arColumns)
	{
		return $this->GetIndexName($tableName, $arColumns) !== "";
	}

	function GetIndexName($tableName, $arColumns, $bStrict = false)
	{
		if(!is_array($arColumns) || count($arColumns) <= 0)
			return "";

		//2005
		//$rs = $this->Query("SELECT index_id, COL_NAME(object_id, column_id) AS column_name, key_ordinal FROM SYS.INDEX_COLUMNS WHERE object_id=OBJECT_ID('".$this->ForSql($tableName)."')", true);

		//2000
		$rs = $this->Query("
			select
				s.indid as index_id
				,s.keyno as key_ordinal
				,c.name column_name
				,si.name index_name
			from sysindexkeys s
			inner join syscolumns c on s.id = c.id and s.colid = c.colid
			inner join sysobjects o on s.id = o.Id and o.xtype='U'
			left join sysindexes si on si.indid = s.indid and si.id=s.id
			where o.name=upper('".$this->ForSql($tableName)."')
		", true);

		if(!$rs)
			return "";

		$arIndexes = array();
		while($ar = $rs->Fetch())
			$arIndexes[$ar["index_name"]][$ar["key_ordinal"]-1] = $ar["column_name"];

		$strColumns = implode(",", $arColumns);
		foreach($arIndexes as $Key_name => $arKeyColumns)
		{
			ksort($arKeyColumns);
			$strKeyColumns = implode(",", $arKeyColumns);
			if($bStrict)
			{
				if($strKeyColumns === $strColumns)
					return $Key_name;
			}
			else
			{
				if(substr($strKeyColumns, 0, strlen($strColumns)) === $strColumns)
					return $Key_name;
			}
		}

		return "";
	}
}

class CDBResult extends CAllDBResult
{
	var $num_rows_affected=-1;
	var $bFetched = false;

	function CDBResult($res=NULL)
	{
		parent::CAllDBResult($res);
	}

	function __fetch_array($rownumber=false, $result=false)
	{
		if ($result===false)
			$result = $this->result;

		if ($rownumber!==false)
		{
			if(!odbc_fetch_row($result, $rownumber))
				return false;
		}
		else
		{
			if(!odbc_fetch_row($result))
				return false;
		}

		$row = array();
		$numfields = odbc_num_fields($result);
		for ($i=1; $i<=$numfields; ++$i)
		{
			$row[odbc_field_name($result, $i)] = odbc_result($result, $i);
		}
		if($this->arUserMultyFields)
		{
			foreach($this->arUserMultyFields as $FIELD_NAME=>$flag)
				if($row[$FIELD_NAME])
					$row[$FIELD_NAME] = unserialize($row[$FIELD_NAME]);
		}

		if ($row && $this->arReplacedAliases)
		{
			foreach($this->arReplacedAliases as $tech => $human)
			{
				$row[$human] = $row[$tech];
				unset($row[$tech]);
			}
		}

		return $row;
	}

	//После запроса делает выборку значений полей в массив
	function Fetch()
	{
		if($this->bNavStart || $this->bFromArray)
		{
			if (!is_array($this->arResult)) return false;
			if ($tmp=current($this->arResult)) next($this->arResult);
			return $tmp;
		}
		else
		{
			return $this->__fetch_array();
		}
	}

	function SelectedRowsCount()
	{
		if($this->nSelectedCount !== false)
			return $this->nSelectedCount;

		if($this->NavRecordCount !== false)
			return $this->NavRecordCount;

		return odbc_num_rows($this->result);
	}

	function AffectedRowsCount($DEBUG=false)
	{
		return $this->num_rows_affected;
	}

	function AffectedRowsCountEx()
	{
		if (intval($this->SelectedRowsCount())>0)
			return 0;
		else
			return $this->AffectedRowsCount();
	}

	function FieldsCount()
	{
		return odbc_num_fields($this->result);
	}

	function FieldName($iCol)
	{
		return odbc_field_name($this->result, $iCol+1);
	}

	function DBNavStart()
	{
		if($this->bFetched===true)
			return;
		$this->bFetched = true;
		$this->NavPageNomer = ($this->PAGEN < 1?($_SESSION[$this->SESS_PAGEN] < 1?1:$_SESSION[$this->SESS_PAGEN]):$this->PAGEN);

		if($this->NavShowAll)
		{
			$NavFirstRecordShow = 0;
			$NavLastRecordShow = 100000;
		}
		else
		{
			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;
		}

		$temp_arrray=array();
		$num_rows=0;
		$rsEnd=false;

		$cache_arrray=array();

		while($num_rows<$NavFirstRecordShow && !$rsEnd)
		{
			if($db_result_array = $this->__fetch_array())
			{
				$num_rows++;

				if(count($cache_arrray) == $this->NavPageSize)
					$cache_arrray=array();
				$cache_arrray[] = $db_result_array;
			}
			else
			{
				$rsEnd = true;
			}
		}

		if($rsEnd && count($cache_arrray)>0)
		{
			$this->NavPageNomer = floor($num_rows / $this->NavPageSize);
			if($num_rows % $this->NavPageSize > 0)
				$this->NavPageNomer++;

			$temp_arrray=$cache_arrray;
		}

		$bFirst=true;
		while($num_rows<$NavLastRecordShow && !$rsEnd)
		{
			if($db_result_array = $this->__fetch_array())
			{
				$num_rows++;
				$temp_arrray[]=$db_result_array;
			}
			else
			{
				$rsEnd=true;
				if($bFirst && count($cache_arrray)>0)
				{
					$this->NavPageNomer = floor($num_rows / $this->NavPageSize);
					if($num_rows % $this->NavPageSize > 0)
						$this->NavPageNomer++;

					$temp_arrray=$cache_arrray;
				}
			}
			$bFirst=false;
		}

		if(!$rsEnd)
			while($this->__fetch_array())
			{
				$num_rows++;
			}

		$this->arResult=$temp_arrray;

		$this->NavRecordCount = $num_rows;
		if($this->NavShowAll)
		{
			$this->NavPageSize = $this->NavRecordCount;
			$this->NavPageNomer = 1;
		}

		if($this->NavPageSize > 0)
			$this->NavPageCount = floor($this->NavRecordCount / $this->NavPageSize);
		else
			$this->NavPageCount = 0;

		if($this->NavPageSize <> 0 && $this->NavRecordCount % $this->NavPageSize > 0)
			$this->NavPageCount++;
	}

	function NavQuery($strSql, $cnt, $arNavStartParams, $bIgnoreErrors = false)
	{
		global $DB;

		if(isset($arNavStartParams["SubstitutionFunction"]))
		{
			$arNavStartParams["SubstitutionFunction"]($this, $strSql, $cnt, $arNavStartParams);
			return null;
		}

		if(isset($arNavStartParams["bDescPageNumbering"]))
			$bDescPageNumbering = $arNavStartParams["bDescPageNumbering"];
		else
			$bDescPageNumbering = false;

		$this->InitNavStartVars($arNavStartParams);
		$this->NavRecordCount = $cnt;

		if($this->NavShowAll)
			$this->NavPageSize = $this->NavRecordCount;

		//Определяем число страниц при указанном размере страниц. Счет начиная с 1
		$this->NavPageCount = ($this->NavPageSize>0 ? floor($this->NavRecordCount/$this->NavPageSize) : 0);
		if($bDescPageNumbering)
		{
			$makeweight = ($this->NavRecordCount % $this->NavPageSize);
			if($this->NavPageCount == 0 && $makeweight > 0)
				$this->NavPageCount = 1;

			//Номер страницы для отображения.
			$this->NavPageNomer =
			(
				$this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount
				?
					($_SESSION[$this->SESS_PAGEN] < 1 || $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount
					?
						$this->NavPageCount
					:
						$_SESSION[$this->SESS_PAGEN]
					)
				:
					$this->PAGEN
			);

			//Смещение от начала RecordSet
			$NavFirstRecordShow = 0;
			if($this->NavPageNomer != $this->NavPageCount)
				$NavFirstRecordShow += $makeweight;

			$NavFirstRecordShow += ($this->NavPageCount - $this->NavPageNomer) * $this->NavPageSize;
			$NavLastRecordShow = $makeweight + ($this->NavPageCount - $this->NavPageNomer + 1) * $this->NavPageSize;
		}
		else
		{
			if($this->NavPageSize && ($this->NavRecordCount % $this->NavPageSize > 0))
				$this->NavPageCount++;

			//Номер страницы для отображения. Отсчет начинается с 1
			$this->NavPageNomer = ($this->PAGEN < 1 || $this->PAGEN > $this->NavPageCount? ($_SESSION[$this->SESS_PAGEN] < 1 || $_SESSION[$this->SESS_PAGEN] > $this->NavPageCount? 1:$_SESSION[$this->SESS_PAGEN]):$this->PAGEN);

			//Смещение от начала RecordSet
			$NavFirstRecordShow = $this->NavPageSize*($this->NavPageNomer-1);
			$NavLastRecordShow = $this->NavPageSize*$this->NavPageNomer;
		}

		$NavAdditionalRecords = 0;
		if(is_set($arNavStartParams, "iNavAddRecords"))
			$NavAdditionalRecords = $arNavStartParams["iNavAddRecords"];

		if(preg_match("#^\\s*SELECT(\\s+DISTINCT|)\\s+#is", $strSql, $match) && !preg_match("#^\\s*SELECT(\\s+DISTINCT|)\\s+TOP#is", $strSql))
			$strSql = $match[0]."TOP ".($NavLastRecordShow+$NavAdditionalRecords)." ".substr($strSql, strlen($match[0]));

		if(is_object($this->DB))
			$res_tmp = $this->DB->Query($strSql, $bIgnoreErrors, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		else
			$res_tmp = $DB->Query($strSql, $bIgnoreErrors, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		// Return false on sql errors (if $bIgnoreErrors == true)
		if ($bIgnoreErrors && ($res_tmp === false))
			return false;

		$res_tmp->arUserMultyFields = $this->arUserMultyFields;
		$res_tmp->arReplacedAliases = $this->arReplacedAliases;

		$temp_arrray_add = array();
		$temp_array = array();
		$tmp_cnt = 0;

		if(!$this->NavShowAll)
		{
			for($i=$NavFirstRecordShow+1; $i<$NavLastRecordShow+$NavAdditionalRecords+1; $i++)
			{
				$tmp_cnt++;
				if (intval($NavLastRecordShow - $NavFirstRecordShow) > 0 && $tmp_cnt > ($NavLastRecordShow - $NavFirstRecordShow))
					$temp_arrray_add[] = $res_tmp->__fetch_array($i);
				else
					$temp_array[] = $res_tmp->__fetch_array($i);
			}
		}
		else
		{
			while($ar = $res_tmp->__fetch_array())
				$temp_array[] = $ar;
		}

		$this->arResult = $temp_array;
		$this->arResultAdd = (count($temp_arrray_add)? $temp_arrray_add : false);
		$this->nSelectedCount = $cnt;
		$this->bDescPageNumbering = $bDescPageNumbering;
		$this->bFromLimited = true;
		$this->DB = $res_tmp->DB;

		return null;
	}
}
