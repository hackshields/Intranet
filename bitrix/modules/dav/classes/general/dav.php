<?
define("GW_MAXIMUM_PRIVILEGES", "65535");
define("GW_ADDRESSBOOK_MAXIMUM_PRIVILEGES", "1023");

class CDav
{
	public static function ProcessRequest()
	{
		$request = new CDavRequest($_SERVER);
		$groupdav = new CDavGroupDav($request);
		$groupdav->ProcessRequest();
	}

	public static function GetCharset($siteId = null)
	{
		if (is_null($siteId))
			$siteId = SITE_ID;
		if (is_null($siteId) || empty($siteId))
			$siteId = CDav::GetIntranetSite();

		static $cs = null;
		if (is_null($cs))
		{
			$dbSite = CSite::GetByID($siteId);
			if ($arSite = $dbSite->Fetch())
				$cs = strtolower($arSite["CHARSET"]);
		}

		return $cs;
	}

	public static function GetTimezoneId($siteId = null)
	{
		if (is_null($siteId))
			$siteId = SITE_ID;

		static $tz = null;
		if (is_null($tz))
			$tz = COption::GetOptionString('dav', "timezone", "Europe/Moscow", $siteId);
		return $tz;
	}

	public static function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (substr($key, 0, 1)=="+")
		{
			$key = substr($key, 1);
			$strOrNull = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="~")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}

	public static function PrepareSql(&$arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields)
	{
		global $DB;

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";
		$strSqlOrderBy = "";

		$arGroupByFunct = array("COUNT", "AVG", "MIN", "MAX", "SUM");

		$arAlreadyJoined = array();

		// GROUP BY -->
		if (is_array($arGroupBy) && count($arGroupBy)>0)
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = strtoupper($val);
				$key = strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if (strlen($strSqlGroupBy) > 0)
						$strSqlGroupBy .= ", ";
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (isset($arFields[$val]["FROM"])
						&& strlen($arFields[$val]["FROM"]) > 0
						&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$val]["FROM"];
						$arAlreadyJoined[] = $arFields[$val]["FROM"];
					}
				}
			}
		}
		// <-- GROUP BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSqlSelect = "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT ";
		}
		else
		{
			if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && strlen($arSelectFields)>0 && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

			if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| count($arSelectFields)<=0
				|| in_array("*", $arSelectFields))
			{
				for ($i = 0; $i < count($arFieldsKeys); $i++)
				{
					if (isset($arFields[$arFieldsKeys[$i]]["WHERE_ONLY"])
						&& $arFields[$arFieldsKeys[$i]]["WHERE_ONLY"] == "Y")
					{
						continue;
					}

					if (strlen($strSqlSelect) > 0)
						$strSqlSelect .= ", ";

					if ($arFields[$arFieldsKeys[$i]]["TYPE"] == "datetime")
					{
						if (array_key_exists($arFieldsKeys[$i], $arOrder))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "FULL")." as ".$arFieldsKeys[$i];
					}
					elseif ($arFields[$arFieldsKeys[$i]]["TYPE"] == "date")
					{
						if (array_key_exists($arFieldsKeys[$i], $arOrder))
							$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i]."_X1, ";

						$strSqlSelect .= $DB->DateToCharFunction($arFields[$arFieldsKeys[$i]]["FIELD"], "SHORT")." as ".$arFieldsKeys[$i];
					}
					else
						$strSqlSelect .= $arFields[$arFieldsKeys[$i]]["FIELD"]." as ".$arFieldsKeys[$i];

					if (isset($arFields[$arFieldsKeys[$i]]["FROM"])
						&& strlen($arFields[$arFieldsKeys[$i]]["FROM"]) > 0
						&& !in_array($arFields[$arFieldsKeys[$i]]["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$arFieldsKeys[$i]]["FROM"];
						$arAlreadyJoined[] = $arFields[$arFieldsKeys[$i]]["FROM"];
					}
				}
			}
			else
			{
				foreach ($arSelectFields as $key => $val)
				{
					$val = strtoupper($val);
					$key = strtoupper($key);
					if (array_key_exists($val, $arFields))
					{
						if (strlen($strSqlSelect) > 0)
							$strSqlSelect .= ", ";

						if (in_array($key, $arGroupByFunct))
						{
							$strSqlSelect .= $key."(".$arFields[$val]["FIELD"].") as ".$val;
						}
						else
						{
							if ($arFields[$val]["TYPE"] == "datetime")
							{
								if (array_key_exists($val, $arOrder))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "FULL")." as ".$val;
							}
							elseif ($arFields[$val]["TYPE"] == "date")
							{
								if (array_key_exists($val, $arOrder))
									$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val."_X1, ";

								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "SHORT")." as ".$val;
							}
							else
								$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val;
						}

						if (isset($arFields[$val]["FROM"])
							&& strlen($arFields[$val]["FROM"]) > 0
							&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
						{
							if (strlen($strSqlFrom) > 0)
								$strSqlFrom .= " ";
							$strSqlFrom .= $arFields[$val]["FROM"];
							$arAlreadyJoined[] = $arFields[$val]["FROM"];
						}
					}
				}
			}

			if (strlen($strSqlGroupBy) > 0)
			{
				if (strlen($strSqlSelect) > 0)
					$strSqlSelect .= ", ";
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT";
			}
			else
				$strSqlSelect = "%%_DISTINCT_%% ".$strSqlSelect;
		}
		// <-- SELECT

		// WHERE -->
		$arSqlSearch = Array();

		if (!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		for ($i = 0; $i < count($filter_keys); $i++)
		{
			$vals = $arFilter[$filter_keys[$i]];
			if (!is_array($vals))
				$vals = array($vals);

			$key = $filter_keys[$i];
			$key_res = CDav::GetFilterOperation($key);
			$key = $key_res["FIELD"];
			$strNegative = $key_res["NEGATIVE"];
			$strOperation = $key_res["OPERATION"];
			$strOrNull = $key_res["OR_NULL"];

			if (array_key_exists($key, $arFields))
			{
				$arSqlSearch_tmp = array();
				for ($j = 0; $j < count($vals); $j++)
				{
					$val = $vals[$j];

					if (isset($arFields[$key]["WHERE"]))
					{
						$arSqlSearch_tmp1 = call_user_func_array(
								$arFields[$key]["WHERE"],
								array($val, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], $arFields, $arFilter)
							);
						if ($arSqlSearch_tmp1 !== false)
							$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
					}
					else
					{
						if ($arFields[$key]["TYPE"] == "int")
						{
							if ((IntVal($val) == 0) && (strpos($strOperation, "=") !== False))
								$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
							else
								$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".IntVal($val)." )";
						}
						elseif ($arFields[$key]["TYPE"] == "double")
						{
							$val = str_replace(",", ".", $val);

							if ((DoubleVal($val) == 0) && (strpos($strOperation, "=") !== False))
								$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND" : "OR")." ".(($strNegative == "Y") ? "NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." 0)";
							else
								$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".DoubleVal($val)." )";
						}
						elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
						{
							if ($strOperation == "QUERY")
							{
								$arSqlSearch_tmp[] = GetFilterQuery($arFields[$key]["FIELD"], $val, "Y");
							}
							else
							{
								if ((strlen($val) == 0) && (strpos($strOperation, "=") !== False))
									$arSqlSearch_tmp[] = "(".$arFields[$key]["FIELD"]." IS ".(($strNegative == "Y") ? "NOT " : "")."NULL) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$DB->Length($arFields[$key]["FIELD"])." <= 0) ".(($strNegative == "Y") ? "AND NOT" : "OR")." (".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
								else
									$arSqlSearch_tmp[] = (($strNegative == "Y") ? " ".$arFields[$key]["FIELD"]." IS NULL OR NOT " : "")."(".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
							}
						}
						elseif ($arFields[$key]["TYPE"] == "datetime")
						{
							if (strlen($val) <= 0)
								$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
							else
								$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
						}
						elseif ($arFields[$key]["TYPE"] == "date")
						{
							if (strlen($val) <= 0)
								$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
							else
								$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
						}
					}
				}

				if (isset($arFields[$key]["FROM"])
					&& strlen($arFields[$key]["FROM"]) > 0
					&& !in_array($arFields[$key]["FROM"], $arAlreadyJoined))
				{
					if (strlen($strSqlFrom) > 0)
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$key]["FROM"];
					$arAlreadyJoined[] = $arFields[$key]["FROM"];
				}

				$strSqlSearch_tmp = "";
				for ($j = 0; $j < count($arSqlSearch_tmp); $j++)
				{
					if ($j > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arSqlSearch_tmp[$j].")";
				}
				if ($strOrNull == "Y")
				{
					if (strlen($strSqlSearch_tmp) > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." IS ".($strNegative=="Y" ? "NOT " : "")."NULL)";

					if (strlen($strSqlSearch_tmp) > 0)
						$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
					if ($arFields[$key]["TYPE"] == "int" || $arFields[$key]["TYPE"] == "double")
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." 0)";
					elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." ".($strNegative=="Y" ? "<>" : "=")." '')";
				}

				if ($strSqlSearch_tmp != "")
					$arSqlSearch[] = "(".$strSqlSearch_tmp.")";
			}
		}

		for ($i = 0; $i < count($arSqlSearch); $i++)
		{
			if (strlen($strSqlWhere) > 0)
				$strSqlWhere .= " AND ";
			$strSqlWhere .= "(".$arSqlSearch[$i].")";
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = Array();
		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);

			if ($order != "ASC")
				$order = "DESC";
			else
				$order = "ASC";

			if (array_key_exists($by, $arFields))
			{
				if ($arFields[$by]["TYPE"] == "datetime" || $arFields[$by]["TYPE"] == "date")
					$arSqlOrder[] = " ".$by."_X1 ".$order." ";
				else
					$arSqlOrder[] = " ".$arFields[$by]["FIELD"]." ".$order." ";

				if (isset($arFields[$by]["FROM"])
					&& strlen($arFields[$by]["FROM"]) > 0
					&& !in_array($arFields[$by]["FROM"], $arAlreadyJoined))
				{
					if (strlen($strSqlFrom) > 0)
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$by]["FROM"];
					$arAlreadyJoined[] = $arFields[$by]["FROM"];
				}
			}
		}

		$strSqlOrderBy = "";
		DelDuplicateSort($arSqlOrder);
		for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if (strlen($strSqlOrderBy) > 0)
				$strSqlOrderBy .= ", ";

			if(strtoupper($DB->type)=="ORACLE")
			{
				if(substr($arSqlOrder[$i], -3)=="ASC")
					$strSqlOrderBy .= $arSqlOrder[$i]." NULLS FIRST";
				else
					$strSqlOrderBy .= $arSqlOrder[$i]." NULLS LAST";
			}
			else
				$strSqlOrderBy .= $arSqlOrder[$i];
		}
		// <-- ORDER BY

		return array(
			"SELECT" => $strSqlSelect,
			"FROM" => $strSqlFrom,
			"WHERE" => $strSqlWhere,
			"GROUPBY" => $strSqlGroupBy,
			"ORDERBY" => $strSqlOrderBy
		);
	}

	public static function CheckIfRightSlashAdded($str)
	{
		if (substr($str, -1) != '/')
			return $str."/";

		return $str;
	}

	public static function FormatDateTime($date, $format = null)
	{
		// 20110118T131500
		if (strlen($date) < 15)
			return 0;

		$year = intval(substr($date, 0, 4));
		$month = intval(substr($date, 4, 2));
		$day = intval(substr($date, 6, 2));
		$hour = intval(substr($date, 9, 2));
		$minute = intval(substr($date, 11, 2));
		$second = intval(substr($date, 13, 2));

		$t = mktime($hour, $minute, $second, $month, $day, $year);

		if (!is_string($format) || empty($format))
			$format = FORMAT_DATETIME;

		return date($GLOBALS["DB"]->DateFormatToPHP($format), $t);
	}

	public static function PackPrivileges($arPrivileges)
	{
		static $arPrivilegesMap = array('read' => 1, 'write-properties' => 2, 'write-content' => 4, 'unlock' => 8, 'read-acl' => 16,
			'read-current-user-privilege-set' => 32, 'bind' => 64, 'unbind' => 128, 'write-acl' => 256, 'read-free-busy' => 512,
			'schedule-deliver-invite' => 1024, 'schedule-deliver-reply' => 2048, 'schedule-query-freebusy' => 4096,
			'schedule-send-invite' =>  8192, 'schedule-send-reply' => 16384, 'schedule-send-freebusy' => 32768,
			'write' => 198 /* 2 + 4 + 64 + 128 */, 'schedule-deliver' => 7168 /* 1024 + 2048 + 4096 */,
			'schedule-send' => 57344 /* 8192 + 16384 + 32768 */, 'all' => GW_MAXIMUM_PRIVILEGES);

		if (!is_array($arPrivileges))
			$arPrivileges = array($arPrivileges);

		$result = 0;
		foreach ($arPrivileges as $privilege)
		{
			$privilege = trim(strtolower(preg_replace('/^.*:/', '', $privilege)));

			if (array_key_exists($privilege, $arPrivilegesMap))
				$result |= $arPrivilegesMap[$privilege];
		}

		if (($result & GW_MAXIMUM_PRIVILEGES) >= GW_MAXIMUM_PRIVILEGES)
			$result = pow(2, 24) - 1;

		return $result;
	}

	public static function ToString($var)
	{
		switch (($type = gettype($var)))
		{
			case 'boolean':
				return $var ? 'true' : 'false';
			case 'string':
				return "'$var'";
			case 'integer':
			case 'double':
			case 'resource':
				return $var;
			case 'NULL':
				return 'null';
			case 'object':
			case 'array':
				return str_replace(array("\n", '    '), '', print_r($var, true));
		}
		return 'unknown';
	}

	public static function Report($place, $varName, $varValue = "UNDEFINED", $mark = false)
	{
		if (defined("GW_DEBUG") && GW_DEBUG === true)
		{
			$f = fopen($_SERVER["DOCUMENT_ROOT"]."/++++++++.+++", "a");
			if ($mark)
				fwrite($f, "--------------------------------------------------------------\n");
			fwrite($f, date("H:i:s")." ".$place.": ".$varName);
			if ($varValue !== "UNDEFINED")
			{
				if (is_array($varValue))
					fwrite($f, "\n\t".preg_replace("#\r?\n#i", "\n\t", print_r($varValue, true))."\n");
				else
					fwrite($f, " = ".$varValue."\n");
			}
			else
			{
				fwrite($f, "\n");
			}
			if ($mark)
				fwrite($f, "--------------------------------------------------------------\n");
			fclose($f);
		}
	}

	public static function WriteToLog($text, $code = "")
	{
		$filename = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/dav.log";

		if ($f = fopen($filename, "a"))
		{
			fwrite($f, date("Y-m-d H:i:s")." ".str_pad($code, 7)." ".$text."\n");
			fclose($f);
		}
	}

	public static function UseProxy()
	{
		$useProxy = COption::GetOptionString("dav", "use_proxy", "N");
		$proxyHost = COption::GetOptionString("dav", "proxy_host", "");
		return (($useProxy == "Y") && (strlen($proxyHost) > 0));
	}

	public static function GetProxySettings()
	{
		return array(
			"PROXY_SCHEME" => COption::GetOptionString("dav", "proxy_scheme", ""),
			"PROXY_HOST" => COption::GetOptionString("dav", "proxy_host", ""),
			"PROXY_PORT" => COption::GetOptionString("dav", "proxy_port", ""),
			"PROXY_USERNAME" => COption::GetOptionString("dav", "proxy_username", ""),
			"PROXY_PASSWORD" => COption::GetOptionString("dav", "proxy_password", "")
		);
	}

	public static function GetIntranetSite()
	{
		static $intranetSite = null;

		if (is_null($intranetSite))
		{
			$arSkipSites = array();
			if (IsModuleInstalled("extranet"))
				$arSkipSites[] = COption::GetOptionString("extranet", "extranet_site", "ex");

			$arSites = array();

			$dbSite = CSite::GetList($o = "SORT", $b = "ASC", array("ACTIVE" => "Y"));
			while ($arSite = $dbSite->Fetch())
			{
				if (!in_array($arSite["ID"], $arSkipSites))
					$arSites[] = $arSite["ID"];
			}

			if (count($arSites) > 0)
				$intranetSite = $arSites[0];
		}

		return $intranetSite;
	}
}
?>