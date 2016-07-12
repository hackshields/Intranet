<?
IncludeModuleLangFile(__FILE__);

define("TM_SHORT_FORMAT","DD.MM.YYYY");
define("TM_FULL_FORMAT","DD.MM.YYYY HH:MI:SS");

class CTimeManReportFull
{

	public static function GetByID($ID)
	{
		return CTimeManReportFull::GetList(Array("ID"=>"desc"),Array("ID"=>intval($ID)));
	}

	public static function GetList($arOrder = array(), $arFilter = array(),$arSelect = array(),$arNavStartParams = Array())
	{
		global $DB;

		$arFields = array(
			"ID" => array("FIELD_NAME" => "R.ID", "FIELD_TYPE" => "int"),
			"TIMESTAMP_X" => array("FIELD_NAME" => "R.TIMESTAMP_X", "FIELD_TYPE" => "datetime"),
			"ACTIVE" => array("FIELD_NAME" => "R.ACTIVE", "FIELD_TYPE" => "string"),
			"USER_ID" => array("FIELD_NAME" => "R.USER_ID", "FIELD_TYPE" => "int"),
			"REPORT_DATE" => array("FIELD_NAME" => "R.REPORT_DATE", "FIELD_TYPE" => "datetime"),
			"DATE_TO" => array("FIELD_NAME" => "R.DATE_TO", "FIELD_TYPE" => "date"),
			"DATE_FROM" => array("FIELD_NAME" => "R.DATE_FROM", "FIELD_TYPE" => "date"),
			"TASKS" => array("FIELD_NAME" => "R.TASKS", "FIELD_TYPE" => "string"),
			"EVENTS" => array("FIELD_NAME" => "R.EVENTS", "FIELD_TYPE" => "string"),
			"REPORT" => array("FIELD_NAME" => "R.REPORT", "TYPE" => "string"),
			"PLANS" => array("FIELD_NAME" => "R.PLANS", "TYPE" => "string"),
			"MARK" => array("FIELD_NAME" => "R.MARK", "FIELD_TYPE" => "string"),
			"APPROVE" => array("FIELD_NAME" => "R.APPROVE", "FIELD_TYPE" => "string"),
			"APPROVE_DATE" => array("FIELD_NAME" => "R.APPROVE_DATE", "FIELD_TYPE" => "datetime"),
			"APPROVER" => array("FIELD_NAME" => "R.APPROVER", "FIELD_TYPE" => "int"),
			"FORUM_TOPIC_ID" => array("FIELD_NAME" => "R.FORUM_TOPIC_ID", "FIELD_TYPE" => "int"),
			"FILES" => array("FIELD_NAME" => "R.FILES", "FIELD_TYPE" => "string"),
		);
		$arSqls = self::PrepareSql($arFields, $arOrder, $arFilter,$arSelect,$arNavStartParams);

		$strSql = "SELECT ".$arSqls["SELECT"]."
		FROM b_timeman_report_full R ".
		(strlen($arSqls["WHERE"])<=0 ? "" : "WHERE ".$arSqls["WHERE"]).
		(strlen($arSqls["ORDERBY"])<=0 ? "" : " ORDER BY ".$arSqls["ORDERBY"]).
		(strlen($arSqls["LIMIT"])>0?" ".$arSqls["LIMIT"]:"");

		$res = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		return $res;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$e = GetModuleEvents('timeman', 'OnBeforeFullReportUpdate');
		while ($a = $e->Fetch())
		{
				if (false === ExecuteModuleEventEx($a, array(&$arFields)))
					return false;
		}

		if (!self::CheckFields('UPDATE', $arFields))
				return false;

		CTimeZone::Disable();
		$strUpdate = $DB->PrepareUpdate('b_timeman_report_full', $arFields);
		CTimeZone::Enable();

		$query = 'UPDATE b_timeman_report_full SET '.$strUpdate.' WHERE ID=\''.intval($ID).'\'';

		if (($dbRes = $DB->Query($query)) && ($dbRes->AffectedRowsCount() > 0))
		{
			$arBinds = array();

			if (isset($arFields['REPORT']))
				$arBinds['REPORT'] = $arFields['REPORT'];
			if (isset($arFields['PLANS']))
				$arBinds['PLANS'] = $arFields['PLANS'];
			if (isset($arFields['TASKS']))
				$arBinds['TASKS'] = $arFields['TASKS'];
			if (isset($arFields['EVENTS']))
				$arBinds['EVENTS'] = $arFields['EVENTS'];
			if (isset($arFields['FILES']))
				$arBinds['FILES'] = $arFields['FILES'];

			if (count($arBinds) > 0)
			{
				$DB->QueryBind($query, $arBinds);
			}

			$e = GetModuleEvents('timeman', 'OnAfterFullReportUpdate');
			while ($a = $e->Fetch())
				ExecuteModuleEventEx($a, array($ID, $arFields));

			return $ID;
		}

		return false;
	}


	protected static function PrepareSql(&$arFields, $arOrder, &$arFilter, $arSelectFields = false, $arNavStartParams = false)
	{
		global $DB;

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";
		$strSqlOrderBy = "";
		$strSqlLimit = "";
		$arGroupByFunct = Array();
		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && strlen($arSelectFields)>0 && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

		if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| count($arSelectFields)<=0
				|| in_array("*", $arSelectFields))
		{
			foreach($arFields as $key => $arField)
				{
					if (isset($arField["WHERE_ONLY"])
							&& $arField["WHERE_ONLY"] == "Y")
					{
							continue;
					}

					if (strlen($strSqlSelect) > 0)
							$strSqlSelect .= ", ";

					if ($arField["FIELD_TYPE"] == "datetime")
					{
							if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($key, $arOrder)))
								$strSqlSelect .= $arField["FIELD_NAME"]." as ".$key."_X1, ";

							$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD_NAME"], "FULL")." as ".$key;
					}
					elseif ($arField["FIELD_TYPE"] == "date")
					{
							if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($key, $arOrder)))
								$strSqlSelect .= $arField["FIELD_NAME"]." as ".$key."_X1, ";

							$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD_NAME"], "SHORT")." as ".$key;
					}
					else
							$strSqlSelect .= $arField["FIELD_NAME"]." as ".$key;

					if (isset($arField["FROM"])
							&& strlen($arField["FROM"]) > 0
							&& !in_array($arField["FROM"], $arAlreadyJoined))
					{
							if (strlen($strSqlFrom) > 0)
								$strSqlFrom .= " ";
							$strSqlFrom .= $arField["FROM"];
							$arAlreadyJoined[] = $arField["FROM"];
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
								$strSqlSelect .= $key."(".$arFields[$val]["FIELD_NAME"].") as ".$val;
							}
							else
							{
								if ($arFields[$val]["FIELD_TYPE"] == "datetime")
								{
										if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($val, $arOrder)))
											$strSqlSelect .= $arFields[$val]["FIELD_NAME"]." as ".$val."_X1, ";

										$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "FULL")." as ".$val;
								}
								elseif ($arFields[$val]["FIELD_TYPE"] == "date")
								{
										if ((strtoupper($DB->type)=="ORACLE" || strtoupper($DB->type)=="MSSQL") && (array_key_exists($val, $arOrder)))
											$strSqlSelect .= $arFields[$val]["FIELD_NAME"]." as ".$val."_X1, ";

										$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD_NAME"], "SHORT")." as ".$val;
								}
								else
										$strSqlSelect .= $arFields[$val]["FIELD_NAME"]." as ".$val;
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

		// <-- SELECT

		// WHERE -->
		$obWhere = new CSQLWhere;
		$obWhere->SetFields($arFields);
		$strSqlWhere = $obWhere->GetQuery($arFilter);

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
					$arSqlOrder[] = " ".$arFields[$by]["FIELD_NAME"]." ".$order." ";

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
		$cnt = count($arSqlOrder);
		for ($i=0; $i<$cnt; $i++)
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

		if (is_array($arNavStartParams) && IntVal($arNavStartParams["nTopCount"]) > 0)
		{
			$dbType = strtoupper($DB->type);
			switch ($dbType) {
				case "MSSQL":
					$strSqlSelect = "TOP ".$arNavStartParams["nTopCount"]." ".$strSqlSelect;
					break;
				case "ORACLE":
					if(strlen($strSqlWhere)>0)
						$strSqlWhere.=" AND ";
					$strSqlWhere.= "ROWNUM<=".$arNavStartParams["nTopCount"];
					break;
				case "MYSQL":
					$strSqlLimit.= "LIMIT ".$arNavStartParams["nTopCount"];
			}
		}

		return array(
				"SELECT" => $strSqlSelect,
				"FROM" => $strSqlFrom,
				"WHERE" => $strSqlWhere,
				"GROUPBY" => $strSqlGroupBy,
				"ORDERBY" => $strSqlOrderBy,
				"LIMIT"=> $strSqlLimit
		);
	}

	function CheckFields($action, &$arFields)
	{
		global $DB, $USER;

		if ($action == 'ADD')
		{
			if (!$arFields['USER_ID'])
					$arFields['USER_ID'] = $USER->GetID();
				if (!$arFields["DATE_FROM"])
					$arFields["DATE_FROM"] = mktime();
				if (!$arFields["DATE_TO"])
					$arFields["DATE_TO"] = $arFields["DATE_FROM"];
		}

		$arFields["REPORT_DATE"] = ConvertTimeStampForReport(mktime(),"FULL");

		if (isset($arFields['REPORT']))
				$arFields['REPORT'] = trim($arFields['REPORT']);

		if (isset($arFields['ACTIVE']))
				$arFields['ACTIVE'] = $arFields['ACTIVE'] == 'N' ? 'N' : 'Y';

		if (is_array($arFields['TASKS']))
				$arFields['TASKS'] = serialize($arFields['TASKS']);
		if (is_array($arFields['EVENTS']))
				$arFields['EVENTS'] = serialize($arFields['EVENTS']);
		if (is_array($arFields['FILES']))
				$arFields['FILES'] = serialize($arFields['FILES']);

		if ($action == 'UPDATE')
				$arFields['~TIMESTAMP_X'] = $DB->GetNowFunction();

		unset($arFields['TIMESTAMP_X']);

		return true;
	}

	public static function Add($arFields)
	{
		global $DB;

		$tm_user = new CUserReportFull($arFields["USER_ID"]);
		$arReportDate = $tm_user->GetReportInfo();

		if ($arReportDate["IS_REPORT_DAY"]!="Y")
			return false;

		$arFields["DATE_TO"] = ConvertTimeStamp(MakeTimeStamp($arReportDate["DATE_TO"], TM_SHORT_FORMAT),"SHORT");
		$arFields["DATE_FROM"] = ConvertTimeStamp(MakeTimeStamp($arReportDate["DATE_FROM"], TM_SHORT_FORMAT),"SHORT");
		$arFields["REPORT_DATE"] = ConvertTimeStamp(MakeTimeStamp($arFields["REPORT_DATE"], TM_FULL_FORMAT),"FULL");

		$Events = GetModuleEvents('timeman', 'OnBeforeFullReportAdd');
		while ($event = $Events->Fetch())
		{
				if (false === ExecuteModuleEventEx($event, array(&$arFields)))
					return false;
		}

		if (!self::CheckFields('ADD', $arFields))
				return false;


		//we try to find report with DATE_TO>DATE_FROM-of-current-report
		$dbreport = CTimeManReportFull::GetList(
					Array("DATE_FROM"=>"DESC"),
					Array(
						">=DATE_TO"=>$arFields["DATE_FROM"],
						"USER_ID"=>$arFields["USER_ID"],
						"ACTIVE"=>"Y"
					),
					Array("ID","DATE_TO"),
					Array("nTopCount"=>1)
			);
		if ($last_report = $dbreport->Fetch())//if we found it
		//fix date from
			$arFields["DATE_FROM"] = ConvertTimeStamp(strtotime("next day", MakeTimeStamp($last_report["DATE_TO"])),"SHORT");
		if (MakeTimeStamp($arFields["DATE_FROM"])>MakeTimeStamp($arFields["DATE_TO"]))
		//fix date to
			$arFields["DATE_TO"] = $arFields["DATE_FROM"];

		CTimeZone::Disable();
		$ID = $DB->Add('b_timeman_report_full', $arFields, array('REPORT', 'TASKS', 'EVENTS','FILES'));
		CTimeZone::Enable();
		if ($ID > 0)
		{

				$last_date = ConvertTimeStampForReport(MakeTimeStamp($arFields["DATE_TO"]),"SHORT");

				$arFields['ID'] = $ID;
				$e = GetModuleEvents('timeman', 'OnAfterFullReportAdd');
				while ($a = $e->Fetch())
					ExecuteModuleEventEx($a, array($arFields));
				if ($arFields["ACTIVE"] != "N")
					$tm_user->SetLastDate($arFields["USER_ID"],$last_date);

		}

		return $ID;
	}

	public static function SetPeriodSection($arFields)
	{
		global $USER;
		$dep = new CIBlockSection;
		if ($ib = COption::GetOptionInt('intranet', 'iblock_structure', false))
			$entity_id = 'IBLOCK_'.$ib.'_SECTION';
		$period = CUserReportFull::GetEntityID($arFields["UF_REPORT_PERIOD"],$entity_id);
		$arFields["UF_REPORT_PERIOD"] = $period["ID"];
		$arFields["UF_SETTING_DATE"] = ConvertTimeStampForReport(time(),"FULL");
		$ID = $arFields["ID"];
		unset($arFields["ID"]);

		if ($ID>0)
		{
			$dep->Update($ID,$arFields);
			return CReportSettings::GetSectionSettings($ID, true);
		}
		return false;
	}

	public static function __getReportJSDraw($arFields)
	{
		$arResult = Array(
				"CELL_FROM"=>0,
				"CELL_COUNT"=>0
			);
		$cellCount = (($arFields["REPORT_DATE_TO"] - $arFields["REPORT_DATE_FROM"])/86400)+1;
		$dayCount = date('t', $arFields["PERIOD_DATE_FROM"]);
		$curMonth = date('n', $arFields["PERIOD_DATE_FROM"]);
		$dateFrom = getdate($arFields["REPORT_DATE_FROM"]);
		$dateTo = getdate($arFields["REPORT_DATE_TO"]);
		$rowCell = $dateFrom["mday"]-1;

		if ($dateTo["mday"]<$dateFrom["mday"]
			&& ($dateFrom["mon"]<$dateTo["mon"] || $dateFrom["year"]<$dateTo["year"])
		)
		{

			if ($curMonth == $dateFrom["mon"])
			{
				$cellCount = $dayCount - $dateFrom["mday"]+1;
				$rowCell = $dateFrom["mday"]-1;
			}
			elseif($curMonth == $dateTo["mon"])
			{
				$cellCount = $dateTo["mday"];
				$rowCell = 0;
			}
		}

		$arResult = Array(
				"CELL_FROM"=>$rowCell,
				"CELL_COUNT"=>$cellCount,
			);

		return $arResult;

	}

	function Delete($ID = false)
	{
		//todo
	}
}

class CUserReportFull
{
	function __construct($USER_ID = 0)
	{
		global $USER;
		if($USER_ID == false)
			$USER_ID = $USER->GetID();
		$this->USER_ID = $USER_ID;
		$this->SETTINGS = CReportSettings::GetUserSettings($USER_ID);
		$this->TimeFull = CSite::GetDateFormat("FULL",SITE_ID);
		$this->TimeShort = CSite::GetDateFormat("SHORT",SITE_ID);
		$this->days = Array('mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun');
		$this->month = Array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
	}

	public function GetSettings($maketimestamp = false)
	{
		$settings = $this->SETTINGS;
		if ($maketimestamp == true)
			$settings["UF_TM_TIME"] = CTimeman::MakeShortTS($settings["UF_TM_TIME"]);
		return $settings;
	}

	public function SetLastDate($USER_ID=false,$arLastDate=false)
	{
		global $DB,$USER;

		if($arLastDate == false)
			return false;
			if($USER_ID == false)
				$USER_ID = $this->USER_ID;

		$arFields = Array("UF_LAST_REPORT_DATE"=>$arLastDate);

		if ($USER->Update($USER_ID,$arFields))
		{
			$this->SETTINGS["UF_LAST_REPORT_DATE"] = $arLastDate;
			$this->SETTINGS["UF_DELAY_TIME"] = "";
			$_SESSION['TIMEMAN_REPORT_DELAY_TIME_'.$USER_ID] = "";

			return true;
		}
		return false;

	}

	public function SetPeriod($arFields)
	{
		global $USER;
		$period = $this->GetEntityID($arFields["UF_REPORT_PERIOD"]);
		$arFields["UF_REPORT_PERIOD"] = $period["ID"];
		$arFields["UF_LAST_REPORT_DATE"] = "";

		if($USER->Update($this->USER_ID,$arFields))
		{
			$arNewTM = new CUserReportFull($this->USER_ID);

			return $arNewTM->GetSettings(true);
		}

		return false;
	}


	public function Recalc()
	{
		$arParams = $this->GetSettings();
		$last_date = $this->GetLastDate();
		$time = CTimeman::MakeShortTS($arParams["UF_TM_TIME"]);

		$arFields = Array(
			"DATE_FROM"=>"",
			"DATE_TO"=>"",
			"DATE_SUBMIT"=>""
		);

		if ($arParams["UF_REPORT_PERIOD"])
		{
			switch ($arParams["UF_REPORT_PERIOD"])
			{
				case "WEEK":
					$arFields["DATE_FROM"] = ConvertTimeStampForReport($last_date+3600*24,"SHORT");
					if ($arParams["UF_TM_DAY"]<=4)//mon,tue,wen,thu
					{

						if ($last_date>strtotime("last sun -1 week") && $last_date<strtotime("last sun") || $last_date>=strtotime("last sun"))
							$arFields["DATE_FROM"] = $last_date+3600*24;
						else
							$arFields["DATE_FROM"] = strtotime("next mon",$last_date);
						$arFields["DATE_TO"] = strtotime("next sun", $arFields["DATE_FROM"]);
						$arFields["DATE_SUBMIT"] = strtotime("next ".$this->days[$arParams["UF_TM_DAY"]-1], $arFields["DATE_TO"])+$time;

					}
					else//fri,sat,sun
					{
						if ($last_date>strtotime("last sun") && $last_date<strtotime("next sun") || $last_date>=strtotime("next sun"))
							$arFields["DATE_FROM"] = $last_date+3600*24;
						else
							$arFields["DATE_FROM"] = strtotime("mon this week",$last_date);
						$arFields["DATE_TO"] = strtotime("next sun", $arFields["DATE_FROM"]);
						$arFields["DATE_SUBMIT"] = strtotime("last ".$this->days[$arParams["UF_TM_DAY"]-1], $arFields["DATE_TO"])+$time;
					}

				break;
				case "MONTH":
					$arFields["DATE_FROM"] = ConvertTimeStampForReport($last_date+3600*24,"SHORT");
					if ($arParams["UF_TM_REPORT_DATE"]<=20)
					{
						if ($last_date>strtotime("last day of last month -1 month") && $last_date<strtotime("last day of last month") || $last_date>=strtotime("last day of last month"))
							$arFields["DATE_FROM"] = $last_date+3600*24;
						else
							$arFields["DATE_FROM"] = strtotime("first day of next month",$last_date);
						$arFields["DATE_TO"] = strtotime("last day of this month", $arFields["DATE_FROM"]);
						$arFields["DATE_SUBMIT"] = strtotime("last day of this month", CTimeMan::RemoveHoursTS($arFields["DATE_TO"]))+$arParams["UF_TM_REPORT_DATE"]*3600*24+$time;

						if ($arFields["DATE_SUBMIT"]<$arFields["DATE_FROM"])
						{
							$arFields["DATE_FROM"] = strtotime("first day of next month",$arFields["DATE_FROM"]);
							$arFields["DATE_TO"] = strtotime("last day of this month", $arFields["DATE_FROM"]);
							$arFields["DATE_SUBMIT"] = strtotime("last day of last month", CTimeMan::RemoveHoursTS($arFields["DATE_TO"]))+$arParams["UF_TM_REPORT_DATE"]*3600*24+$time;
						}
					}
					else
					{
						if ($last_date && $last_date>strtotime("first day of this month") && $last_date<strtotime("last day of this month") || $last_date>=strtotime("last day of this month"))
							$arFields["DATE_FROM"] = $last_date+3600*24;
						else
							$arFields["DATE_FROM"] = strtotime("first day of this month");

						$arFields["DATE_TO"] = strtotime("last day of this month", $arFields["DATE_FROM"]);
						$arFields["DATE_SUBMIT"] = strtotime("last day of last month", CTimeMan::RemoveHoursTS($arFields["DATE_TO"]))+$arParams["UF_TM_REPORT_DATE"]*3600*24+$time;

						if ($arFields["DATE_SUBMIT"]<$arFields["DATE_FROM"])
						{
							$arFields["DATE_FROM"] = strtotime("first day of next month",$arFields["DATE_FROM"]);
							$arFields["DATE_TO"] = strtotime("last day of this month", $arFields["DATE_FROM"]);
							$arFields["DATE_SUBMIT"] = strtotime("last day of last month", CTimeMan::RemoveHoursTS($arFields["DATE_TO"]))+$arParams["UF_TM_REPORT_DATE"]*3600*24+$time;
						}
					}
				break;
				case "DAY":
					$arFields["DATE_FROM"] = $last_date+3600*24;
					$arFields["DATE_TO"] = $last_date+3600*24;
					$arFields["DATE_SUBMIT"] = CTimeMan::RemoveHoursTS($arFields["DATE_FROM"])+$time;
				break;

			}

			$arFields["DATE_FROM"] = ConvertTimeStampForReport($arFields["DATE_FROM"],"SHORT");
			$arFields["DATE_TO"] = ConvertTimeStampForReport($arFields["DATE_TO"] ,"SHORT");
			$arFields["DATE_SUBMIT"] = ConvertTimeStampForReport($arFields["DATE_SUBMIT"] ,"FULL");
		}

		return $arFields;
	}

	public function GetLastDate()
	{
		global $DB,$USER;
		$arSettings=$this->GetSettings();

		if (!$arSettings["UF_LAST_REPORT_DATE"])
		{
			$dbres = CTimeManReportFull::GetList(
				Array("DATE_TO"=>"desc"),
				Array("USER_ID"=>$this->USER_ID,"ACTIVE"=>"Y"),
				Array("DATE_TO"),
				Array("nTopCount"=>1)
			);
			$last_report = $dbres->Fetch();
			$last_date_report = MakeTimeStamp($last_report["DATE_TO"],$this->TimeShort);
		}
		else
			$last_date_report = MakeTimeStamp($arSettings["UF_LAST_REPORT_DATE"],TM_SHORT_FORMAT);

		switch ($arSettings["UF_REPORT_PERIOD"])
		{
			case "WEEK":

					if($arSettings["UF_TM_DAY"]<=4)
						$arLastDate = strtotime("last sun -1 week");
					else
						$arLastDate = strtotime("last sun");

					if ($last_date_report)
						$arLastDate = $last_date_report;
			break;
			case "DAY":
				$arLastDate = strtotime("-1 day");
				if ($last_date_report)
						$arLastDate = $last_date_report;
			break;

			case "MONTH";

				if($arSettings["UF_TM_REPORT_DATE"]<=20)
					$arLastDate = strtotime("last day of last month -1 month");
				else
					$arLastDate = strtotime("last day of last month");
				if ($last_date_report && $last_date_report>=$arLastDate)
					$arLastDate = $last_date_report;
			break;
		}

		return $arLastDate;
	}

	private function FixDateByHoliday($DATE_FROM = false, $DATE_TO = false)
	{
		$arResult = Array(
			"NEED_TO_RECALC"=>false,
			"DO_NOT_SHOW_THE_FORM"=>false
		);
		//$DATE_TO_INC = ConvertTimeStampForReport(strtotime('+1 day', MakeTimeStamp($DATE_TO,$this->TimeShort)),"SHORT");
		// $DATE_TO_INC = ConvertTimeStampForReport(strtotime('+1 day', MakeTimeStamp($DATE_TO,TM_SHORT_FORMAT)),"SHORT");
		// $DATE_FROM = ConvertTimeStampForReport(MakeTimeStamp($DATE_FROM, TM_SHORT_FORMAT),"SHORT");

		$DATE_TO_INC = ConvertTimeStamp(strtotime('+1 day', MakeTimeStamp($DATE_TO,TM_SHORT_FORMAT)),"SHORT");
		$DATE_FROM = ConvertTimeStamp(MakeTimeStamp($DATE_FROM, TM_SHORT_FORMAT),"SHORT");

		//was the work day is open between $DATE_FROM and $DATE_TO?

		$dbRes = CTimeManEntry::GetList(
			array(),
			array(
				'USER_ID' => $this->USER_ID,
				'>=DATE_START'=>$DATE_FROM,
				'<DATE_START'=>$DATE_TO_INC,
				false,false,Array("ID")
			)
		);

		if (!$dbRes->Fetch())//it's all right, we found work between $DATE_FROM and $DATE_TO
		{
			//the work day was NOT open between $DATE_FROM and $DATE_TO...hmm, holidays?
			//let's try to find first open work day after holidays
			$dbRes = CTimeManEntry::GetList(
				array(),
				array(
					'USER_ID' => $this->USER_ID,
					'>DATE_START'=>$DATE_FROM,
					false,false,Array("ID")
				)
			);
			if ($res = $dbRes->Fetch())
			{

			//we found first open work day after holidays, now rewrite last report date
				$FWorkDayDateTS = MakeTimeStamp($res['DATE_START'],$this->TimeFull);
				$LastDate = ConvertTimeStampForReport(strtotime('-1 day',$FWorkDayDateTS),"SHORT");
				$this->SetLastDate($this->USER_ID,$LastDate);
				$arResult["NEED_TO_RECALC"] = true;
			}
			else
			//we not found the first open workday after holidays, it's mean that the holidays are not over yet and we can't to show the report form
				$arResult["DO_NOT_SHOW_THE_FORM"] = true;
		}

		return $arResult;
	}

	public function GetReportInfo()
	{
		global $USER, $CACHE_MANAGER;

		$obCache = new CPHPCache();

		$cache_dir = '/timeman/report_info';
		$cache_id = 'timeman|report_info|'.$USER->GetID().'|'.FORMAT_DATETIME.'|'.FORMAT_DATE;

		$arReportInfo = null;
		$today = ConvertTimeStamp();

		if ($obCache->InitCache(86400, $cache_id, $cache_dir))
		{
			$arReportInfo = $obCache->GetVars();

			if($today != $arReportInfo['TODAY'])
			{
				$arReportInfo = null;
				$obCache->Clean($cache_id, $cache_dir);
			}
		}

		$bCache = false;
		// cache expired or there's no cache
		if($obCache->StartDataCache() || $arReportInfo == null)
		{
			$bCache = true;

			$CACHE_MANAGER->StartTagCache($cache_dir);
			$CACHE_MANAGER->RegisterTag('timeman_report_full');
			$CACHE_MANAGER->RegisterTag('timeman_report_full_'.$USER->GetID());

			$arReportInfo = $this->_GetReportInfo();
			$arReportInfo['TODAY'] = $today;

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache($arReportInfo);
		}

		if(is_array($arReportInfo['_DATA']))
		{
			$arData = $arReportInfo['_DATA'];
			$fix = $arReportInfo['_FIX'];

			//is time to show a report form?
			if (
				doubleval(MakeTimeStamp($arData["DATE_SUBMIT"],TM_FULL_FORMAT)) <= doubleval(time())
				&& $arReportInfo["MODE"]
			)
			{
				$arReportInfo["SHOW_REPORT_FORM"] = "Y";
			}

			if(
				$arData["DATE_SUBMIT"]
				&&(CTimeMan::RemoveHoursTS(MakeTimeStamp($arData["DATE_SUBMIT"],TM_FULL_FORMAT)) <= CTimeMan::RemoveHoursTS(time()))
			)
			{
				$arReportInfo["IS_REPORT_DAY"] = "Y";

				if ($fix["NEED_TO_RECALC"] && $bCache)
				{
					$obCache->Clean($cache_id, $cache_dir);
					return $this->_GetReportInfo();
				}
				elseif($fix["DO_NOT_SHOW_THE_FORM"])
				{
					$arReportInfo["IS_REPORT_DAY"] = "N";
					$arReportInfo["SHOW_REPORT_FORM"] = "N";
				}
			}
		}
//echo '<pre>'; print_r($arReportInfo); echo '</pre>';
		unset($arReportInfo['TODAY']);
		unset($arReportInfo['_DATA']);
		unset($arReportInfo['_FIX']);

		return $arReportInfo;
	}

	protected function _GetReportInfo()
	{

		global $DB,$USER;
		$arSettings = $this->GetSettings();

		$arReport = Array(
			"MODE"=>$arSettings["UF_REPORT_PERIOD"],
			"DATE_FROM"=>"",
			"DATE_TO"=>"",
			"DATE_SUBMIT"=>"",
			"SHOW_REPORT_FORM"=>"N",
			"LAST_REPORT"=>"",
			"DELAY_TIME"=>"",
			"IS_DELAY"=>"N",
			"IS_REPORT_DAY"=>"N"
		);

		//where is no last date report?
		if (!$arSettings["UF_LAST_REPORT_DATE"] && $arSettings["UF_REPORT_PERIOD"] && $arSettings["UF_REPORT_PERIOD"] != "NONE")
		{
			//calc last date report
			if($arLastDate = $this->GetLastDate())//return ts
			{
				$this->SetLastDate($this->USER_ID,ConvertTimeStampForReport($arLastDate,"SHORT"));
						$USER->Update($this->USER_ID,Array("UF_SETTING_DATE"=>$arSettings["UF_SETTING_DATE"]));
				$arSettings["UF_LAST_REPORT_DATE"] = ConvertTimeStampForReport($arLastDate,"SHORT");
			}
		}

		if($arSettings["UF_REPORT_PERIOD"] != "NONE")
		{
			$arData = $this->Recalc();//calc date_from, date_to and date_submit

			$arReport = Array(
				"MODE"=>$arSettings["UF_REPORT_PERIOD"],
				"DATE_FROM"=>$arData["DATE_FROM"],
				"DATE_TO"=>$arData["DATE_TO"],
				"DATE_SUBMIT"=>$arData["DATE_SUBMIT"],
				"SHOW_REPORT_FORM"=>"N",
				"LAST_REPORT"=>$arSettings["UF_LAST_REPORT_DATE"],
				"DELAY_TIME"=>$_SESSION['TIMEMAN_REPORT_DELAY_TIME_'.$this->USER_ID],
				"IS_DELAY"=>"N",
				"IS_REPORT_DAY"=>"N"
			);

			//report is delayed?
			$datefomat = $this->TimeShort;
			if (strlen($arReport["DELAY_TIME"])>0)
			{
				if (MakeTimeStamp($arReport["DELAY_TIME"],TM_FULL_FORMAT)>doubleval(time()))
					$arReport["IS_DELAY"] = "Y";
			}
			//is time to show a report form?
			if (doubleval(MakeTimeStamp($arData["DATE_SUBMIT"],TM_FULL_FORMAT))<=doubleval(time()) && $arReport["MODE"])
				$arReport["SHOW_REPORT_FORM"] = "Y";
			//this is a report day?
			if($arData["DATE_SUBMIT"]
			&&(CTimeMan::RemoveHoursTS(MakeTimeStamp($arData["DATE_SUBMIT"],TM_FULL_FORMAT))<= CTimeMan::RemoveHoursTS(time()))
			)
			{
				$arReport["IS_REPORT_DAY"] = "Y";
				$fix = $this->FixDateByHoliday($arData["DATE_FROM"],$arData["DATE_TO"]);

				$arReport['_FIX'] = $fix;

				if ($fix["NEED_TO_RECALC"] == true)
				{
					return $this->_GetReportInfo();
				}
				elseif($fix["DO_NOT_SHOW_THE_FORM"] == true)
				{
					$arReport["IS_REPORT_DAY"] = "N";
					$arReport["SHOW_REPORT_FORM"] = "N";
				}
			}

			$arReport['_DATA'] = $arData;
		}
		return $arReport;
	}

	public function GetReportData($force = false)
	{

		$arResult = Array(
			"REPORT_INFO"=>Array(),
			"REPORT_DATA"=>Array(),
		);
		$date = $arResult["REPORT_INFO"] = $this->GetReportInfo();

//echo '<pre>'; print_r($date); echo '</pre>';

		if ($date["IS_REPORT_DAY"] == "N")
			return $arResult;
		elseif($date["IS_REPORT_DAY"] == "Y")
		{
			if (($date["IS_DELAY"]=="Y" || $date["SHOW_REPORT_FORM"]=="N") && !$force)
				return $arResult;
		}

		$datefomat = CSite::GetDateFormat("SHORT",SITE_ID);
		$USER_ID = $this->USER_ID;
		$arManagers = CTimeMan::GetUserManagers($USER_ID);
		$arManagers[] = $USER_ID;
		$user_url = COption::GetOptionString('intranet', 'path_user', '/company/personal/user/#USER_ID#/', SITE_ID);

		$dbManagers = CUser::GetList($by='ID', $order='ASC', array('ID' => implode('|', $arManagers)));
		$arCurrentUserManagers = array();
		$arCurrentUser = Array();
		while ($manager = $dbManagers->GetNext())
		{
			$manager['PHOTO'] =
				$manager['PERSONAL_PHOTO'] > 0
				? CIntranetUtils::InitImage($manager['PERSONAL_PHOTO'], 32, 0, BX_RESIZE_IMAGE_EXACT)
				: array();
			$userData = array(
				'ID' => $manager['ID'],
				'LOGIN' => $manager['LOGIN'],
				'NAME' => CUser::FormatName(CSite::GetNameFormat(false), $manager, true, false),
				'URL' => str_replace(array('#ID#', '#USER_ID#'), $manager['ID'], $user_url),
				'WORK_POSITION' => $manager['WORK_POSITION'],
				'PHOTO' => $manager['PHOTO']['CACHE']['src'],
			);
			if ($userData["ID"] == $this->USER_ID)
				$arCurrentUser = $userData;
			else
				$arCurrentUserManagers[] = $userData;
		}
		if(count($arCurrentUserManagers) == 0)
			$arCurrentUserManagers[] = $arCurrentUser;
		$arInfo = CTimeMan::GetRuntimeInfo(true);
		$dbReports = CTimeManReport::GetList(array('ID' => 'ASC'), array('ENTRY_ID' => $arInfo["ID"], 'REPORT_TYPE' => 'REPORT'));
		if ($Report = $dbReports->Fetch())
			$RTReport = $Report["REPORT"];

		$dbReport = CTimeManReportFull::GetList(Array("DATE_FROM"=>"DESC"),Array("USER_ID"=>$USER_ID,"ACTIVE"=>"N"),Array(),Array("nTopCount"=>1));
		if($arReport = $dbReport->Fetch())
		{
			$arInfo["REPORT_DATE_FROM"] = MakeTimeStamp($arReport["DATE_FROM"], $datefomat);
			$arInfo["REPORT_DATE_TO"] = MakeTimeStamp($arReport["DATE_TO"], $datefomat);
			//$arInfo["TASKS"] = unserialize($arReport["TASKS"]);
			$arInfo["REPORT"] = $arReport["REPORT"];
			$arInfo["PLANS"] = $arReport["PLANS"];
			if ($arReport["FILES"])
				$arInfo["FILES"] = unserialize($arReport["FILES"]);
			$arInfo["REPORT_ID"] = $arReport["ID"];
			if ($arInfo["REPORT_DATE_FROM"]!=$arInfo["REPORT_DATE_TO"])
				$arInfo['DATE_TEXT'] = FormatDate('j F', $arInfo["REPORT_DATE_FROM"])." - ".FormatDate('j F', $arInfo["REPORT_DATE_TO"]);
			else
				$arInfo['DATE_TEXT'] = FormatDate('j F', $arInfo["REPORT_DATE_TO"]);
		}
		else
		{

			$arInfo["REPORT_DATE_FROM"] = MakeTimeStamp($date["DATE_FROM"], TM_SHORT_FORMAT);
			$arInfo["REPORT_DATE_TO"] = MakeTimeStamp($date["DATE_TO"], TM_SHORT_FORMAT);
		}
		$date_to = (($date["DATE_TO"])?$date["DATE_TO"]:$arReport["DATE_TO"]);
		$date_to = MakeTimeStamp($date_to,CSite::GetDateFormat("SHORT", SITE_ID));
		$today = CTimeMan::RemoveHoursTS(time());
		if ($date_to<$today)
		{
			$arInfo["TASKS"] = Array();
			$arInfo["EVENTS"] = Array();
		}

		$arLastTasks = Array();
		$arFilter = Array(
			">=REPORT_DATE" => (($date["DATE_FROM"])?$date["DATE_FROM"]:$arReport["DATE_FROM"]),
			"<=REPORT_DATE" => (($date["DATE_TO"])?$date["DATE_TO"]:$arReport["DATE_TO"]),
			"USER_ID"=>$USER_ID
		);
		$arTaskIDs = Array();
		$arEventIDs = Array();

		if(is_array($arInfo['PLANNER']))
		{
			$arInfo = array_merge($arInfo, $arInfo['PLANNER']['DATA']);
			unset($arInfo['PLANNER']);
		}

		if(is_array($arInfo["TASKS"]))
			foreach($arInfo["TASKS"] as $task)
				$arTaskIDs[] = $task["ID"];
		if(is_array($arInfo["EVENTS"]))
			foreach($arInfo["EVENTS"] as $event)
				$arEventIDs[] = $event["ID"];

		$res = CTimeManReportDaily::GetList(array(),$arFilter);
		while($day = $res->Fetch())
		{
			$arDayTasks = unserialize($day["TASKS"]);
			$arDayEvents = unserialize($day["EVENTS"]);
			if (is_array($arDayTasks))
				foreach($arDayTasks as $task)
				{
					if (!in_array($task["ID"],$arTaskIDs))
					{
						$arInfo["TASKS"][] = $task;
						$arTaskIDs[] = $task["ID"];
					}
					else
					{
						foreach($arInfo["TASKS"] as $key=>$cur_task)
						{
							if ($cur_task["ID"] == $task["ID"])
							{
								$arInfo["TASKS"][$key]["TIME"]+= $task["TIME"];
							}
						}
					}
				}
			if (is_array($arDayEvents))
				foreach($arDayEvents as $event)
					if (!in_array($event["ID"],$arEventIDs))
					{
						$arInfo["EVENTS"][] = $event;
						$arEventIDs[] = $event["ID"];
					}

			if(strlen($day["REPORT"])>0 && !$arInfo["REPORT_ID"])
			{
				$day["REPORT"] = nl2br($day["REPORT"]);
				$arInfo["REPORT"].="<b>".$day["REPORT_DATE"]."</b><br>".$day["REPORT"]."<br>";
			}
		}

		if($RTReport && !$arInfo["REPORT_ID"])
			$arInfo["REPORT"].="<b>".ConvertTimeStamp(time(),"SHORT")."</b><br>".nl2br($RTReport);

		if (is_array($arInfo['EVENTS']))
		{
			foreach ($arInfo['EVENTS'] as $key => $arEvent)
			{
				if ($arEvent['STATUS'] && $arEvent['STATUS'] != 'Y')
					unset($arInfo['EVENTS'][$key]);
			}
			$arInfo['EVENTS'] = array_values($arInfo['EVENTS']);
		}

		if(!$arInfo["REPORT_ID"])
		{
			if ($arInfo["REPORT_DATE_FROM"]!=$arInfo["REPORT_DATE_TO"])
				$arInfo['DATE_TEXT'] = FormatDate('j F', $arInfo["REPORT_DATE_FROM"])." - ".FormatDate('j F', $arInfo["REPORT_DATE_TO"]);
			else
				$arInfo['DATE_TEXT'] = FormatDate('j F', $arInfo["REPORT_DATE_TO"]);
		}

		$arResult["REPORT_DATA"] = array(
			'FROM' => $arCurrentUser,
			'TO' => array_values($arCurrentUserManagers),
			'INFO' => $arInfo,
			'REPORT' => $arInfo["REPORT"],
			'PLANS' => $arInfo["PLANS"],
			'REPORT_ID'=>($arInfo["REPORT_ID"]?$arInfo["REPORT_ID"]:"")
		);
//echo '<pre>'; print_r($arResult); echo '</pre>';
		return $arResult;
	}

	static function GetEntityID($XML_ID = false,$entity_id = false)
	{
		if ($XML_ID == false)
			return false;
		if ($entity_id == false)
			$entity_id = "USER";

		$entities = CUserTypeEntity::GetList(Array(),Array("ENTITY_ID"=>$entity_id,"FIELD_NAME"=>"UF_REPORT_PERIOD"));

		if($arEntity = $entities ->Fetch())
		{
			$oStatus = CUserFieldEnum::GetList(array(), array("USER_FIELD_ID" =>$arEntity["ID"]));
			while($result = $oStatus->Fetch())
			{
				if ($result["XML_ID"] == $XML_ID)
					return $result;
			}

		}
		return false;
	}

	function Delay($time = 3600)
	{
		global $USER;
		$_SESSION['TIMEMAN_REPORT_DELAY_TIME_'.$USER->GetID()] = ConvertTimeStampForReport(time()+$time,"FULL");

		return true;
	}
}

class CReportSettings
{
	private static $SECTIONS_SETTINGS_CACHE = null;

	function GetUserSettings($USER_ID = false)
	{
		global $CACHE_MANAGER;

		$USER_ID = intval($USER_ID);

		if($USER_ID == false)
			$USER_ID = $GLOBALS['USER']->GetID();

		$arSettings = array();

		$cache_dir = '/timeman/report_settings/u'.$this->USER_ID;
		$cache_id = 'timeman|report_settings|u'.$this->USER_ID;

		$obCache = new CPHPCache();

		if ($obCache->InitCache(30*86400, $cache_id, $cache_dir))
		{
			$arSettings = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();
			$CACHE_MANAGER->StartTagCache($cache_dir);

			$CACHE_MANAGER->RegisterTag("USER_CARD_".intval($USER_ID / 100));

			$dbSettings = CUser::GetByID($USER_ID);
			$arUser = $dbSettings->Fetch();
			//getting user settings
			if($arUser)
			{
				$arSettings = array(
					'UF_REPORT_PERIOD' => CReportSettings::GetPeriodByID($arUser['UF_REPORT_PERIOD']),
					'UF_LAST_REPORT_DATE' => $arUser['UF_LAST_REPORT_DATE'],
					'UF_TM_REPORT_DATE' => $arUser['UF_TM_REPORT_DATE'],
					'UF_SETTING_DATE' => $arUser['UF_SETTING_DATE'],
					'UF_TM_TIME' => $arUser['UF_TM_TIME'],
					'UF_TM_DAY' => $arUser['UF_TM_DAY'],
				);

				//exhibited a period of individual settings - if not, check section settings
				if (!$arSettings["UF_REPORT_PERIOD"] && is_array($arUser['UF_DEPARTMENT']) && count($arUser['UF_DEPARTMENT']) > 0)
				{
					foreach ($arUser['UF_DEPARTMENT'] as $dep)
					{

						$res = CReportSettings::GetSectionSettings($dep);
						//have a period setting in a section?
						$user_setting_date = $arSettings["UF_SETTING_DATE"];
						$arSettings = array(
							'UF_REPORT_PERIOD' => $res['UF_REPORT_PERIOD'],
							'UF_LAST_REPORT_DATE' => $arUser['UF_LAST_REPORT_DATE'],
							'UF_TM_REPORT_DATE' => $res['UF_TM_REPORT_DATE'],
							'UF_TM_TIME' => $res['UF_TM_TIME'],
							'UF_TM_DAY' => $res['UF_TM_DAY'],
						);
						//section settings were updated?
						if (($res["UF_SETTING_DATE"]) && (!$user_setting_date || MakeTimeStamp($user_setting_date,CSite::GetDateFormat("FULL",SITE_ID))<MakeTimeStamp($res["UF_SETTING_DATE"],CSite::GetDateFormat("FULL",SITE_ID))))
						{
							//nulling last date report and update settings date
							$arSettings["UF_SETTING_DATE"] = $res["UF_SETTING_DATE"];
							$arSettings["UF_LAST_REPORT_DATE"] = "";
						}
						$arSettings["PARENT"] = ($res["PARENT"])?$res["PARENT"]:$res["ID"];
						$arSettings["PARENT_NAME"] = ($res["PARENT_NAME"])?$res["PARENT_NAME"]:$res["NAME"];
					}
				}
			}

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache($arSettings);
		}

		$arSettings['UF_DELAY_TIME'] = $_SESSION['TIMEMAN_REPORT_DELAY_TIME_'.$USER_ID];

		return $arSettings;
	}

	public static function GetSectionSettings($section_id, $maketimestamp = false)
	{
		if (null == self::$SECTIONS_SETTINGS_CACHE)
			self::_GetTreeSettings();

		$settings = self::$SECTIONS_SETTINGS_CACHE[$section_id];
		if ($maketimestamp == true)
			$settings["UF_TM_TIME"] = CTimeman::MakeShortTS($settings["UF_TM_TIME"]);
		return $settings;

	}

	private static function _GetTreeSettings()
	{
		global $USER_FIELD_MANAGER, $CACHE_MANAGER;

		self::$SECTIONS_SETTINGS_CACHE = array();

		$ibDept = COption::GetOptionInt('intranet', 'iblock_structure', false);
		$CACHE_MANAGER->RegisterTag("iblock_id_".$ibDept);

		$cache_dir = '/timeman/report_settings';
		$cache_id = 'timeman|report_full_settings|'.$ibDept;

		$obCache = new CPHPCache();

		if ($obCache->InitCache(30*86400, $cache_id, $cache_dir))
		{
			self::$SECTIONS_SETTINGS_CACHE = $obCache->GetVars();
		}
		else
		{
			$obCache->StartDataCache();

			$CACHE_MANAGER->StartTagCache($cache_dir);
			$CACHE_MANAGER->RegisterTag("iblock_id_report_".$ibDept);

			$arAllFields = $USER_FIELD_MANAGER->GetUserFields('IBLOCK_'.$ibDept.'_SECTION');
			$arSettings = array('UF_TM_REPORT_DATE','UF_TM_DAY','UF_REPORT_PERIOD','UF_TM_TIME','UF_SETTING_DATE');
			$dbRes = CIBlockSection::GetList(
				array("LEFT_MARGIN"=>"ASC"),
				array('IBLOCK_ID' => $ibDept, 'ACTIVE' => 'Y'),
				false,
				array('ID','NAME','IBLOCK_SECTION_ID','UF_TIMEMAN','UF_REPORT_PERIOD','UF_TM_REPORT_DATE','UF_TM_DAY','UF_TM_TIME','UF_SETTING_DATE')
			);

			while ($arRes = $dbRes->Fetch())
			{
				$arSectionSettings = array();
				$arRes["UF_REPORT_PERIOD"] = CReportSettings::GetPeriodByID($arRes['UF_REPORT_PERIOD'], 'IBLOCK_'.$ibDept.'_SECTION' );
				$arSectionSettings = $arRes;

				if (!$arRes["UF_REPORT_PERIOD"] && $arRes['IBLOCK_SECTION_ID']>0)
				{
					$parent = self::$SECTIONS_SETTINGS_CACHE[$arRes['IBLOCK_SECTION_ID']];
					$parent["PARENT"] = ($parent["PARENT"])?$parent["PARENT"]:$arRes['IBLOCK_SECTION_ID'];
					$parent["ID"] = $arRes["ID"];
					$parent["PARENT_NAME"] = ($parent["PARENT_NAME"])?$parent["PARENT_NAME"]:$parent["NAME"];
					$parent["NAME"] = $arRes["NAME"];
					$arSectionSettings = $parent;
				}

				if (!$arSectionSettings['UF_TIMEMAN'])
					$arSectionSettings['UF_TIMEMAN'] = 'Y';

				self::$SECTIONS_SETTINGS_CACHE[$arRes['ID']] = $arSectionSettings;
			}

			$CACHE_MANAGER->EndTagCache();
			$obCache->EndDataCache(self::$SECTIONS_SETTINGS_CACHE);
		}
	}

	function GetPeriodByID($ID,$ENTITY = "USER")
	{

		$entities = CUserTypeEntity::GetList(Array(),Array("ENTITY_ID"=>$ENTITY,"FIELD_NAME"=>"UF_REPORT_PERIOD"));

			if($arEntity = $entities ->Fetch())
			{
				$oStatus = CUserFieldEnum::GetList(array(), array("USER_FIELD_ID" =>$arEntity["ID"]));
				while($result = $oStatus->Fetch())
				{
					if ($ID == $result["ID"])
						return $result["XML_ID"];
				}

			}
		return false;
	}
}

class CReportNotifications
{
	public static function SendMessage($REPORT_ID,$bSendEvent = true)
	{
		global $DB;
		$REPORT_ID = intval($REPORT_ID);
		if ($REPORT_ID<=0)
			return false;
		$dbReport = CTimeManReportFull::GetByID($REPORT_ID);
		if (CModule::IncludeModule("socialnetwork") && $arReport = $dbReport->Fetch())
		{
			$date_from = FormatDate("j F", MakeTimeStamp($arReport["DATE_FROM"],CSite::GetDateFormat("FULL", SITE_ID)));
			$date_to = FormatDate("j F", MakeTimeStamp($arReport["DATE_TO"],CSite::GetDateFormat("FULL", SITE_ID)));
			if ($date_from == $date_to)
				$date_text = $date_to;
			else
				$date_text = $date_from." - ".$date_to;
			$message = GetMessage('REPORT_DONE');
			$arSoFields = Array(
				"EVENT_ID" => "report",
				"=LOG_DATE" =>$DB->CurrentTimeFunction(),
				"TITLE_TEMPLATE" => "#TITLE#",
				"TITLE" => GetMessage("REPORT_PERIOD").$date_text,
				"MESSAGE" => $message,
				"TEXT_MESSAGE" => $message,
				"CALLBACK_FUNC" => false,
				"SOURCE_ID" => $REPORT_ID,
				"SITE_ID"=>SITE_ID,
				"ENABLE_COMMENTS" => "Y",
				"PARAMS"=>serialize(Array("FORUM_ID"=>COption::GetOptionInt("timeman","report_forum_id","")))
			);
			$arSoFields["ENTITY_TYPE"] = SONET_WORK_REPORT_ENTITY;
			$arSoFields["ENTITY_ID"] = $arReport["USER_ID"];
			$arSoFields["USER_ID"] = $arReport["USER_ID"];
			CReportNotifications::Subscribe($arReport["USER_ID"]);
			$logID = CSocNetLog::Add($arSoFields, false);

			if (intval($logID) > 0)
			{
				CSocNetLog::Update($logID, array("TMP_ID" => $logID));
				$arRights = CReportNotifications::GetRights($arReport["USER_ID"]);

				CSocNetLogRights::Add($logID, $arRights);
				if ($bSendEvent)
					CSocNetLog::SendEvent($logID, "SONET_NEW_EVENT", $logID);
			}

			return $logID;
		}

		return false;

	}

	public static function Subscribe($USER_ID)
	{
		CModule::IncludeModule("socialnetwork");
		$arManagers = CTimeMan::GetUserManagers($USER_ID);
		$arManagers[] = $USER_ID;
		$arManagers = array_unique($arManagers);
		if (is_array($arManagers) && count($arManagers) > 0)
		foreach($arManagers as $mID)
		{
			$dbEvents = CSocNetLogEvents::GetList(Array(),Array("USER_ID"=>$mID,"EVENT_ID"=>"report","ENTITY_ID"=>$USER_ID));
			if(!$event = $dbEvents->Fetch())
			{
				$arFields = Array(
				"USER_ID"=>$mID,
				"ENTITY_TYPE"=>"R",
				"ENTITY_ID"=>$USER_ID,
				"EVENT_ID"=>"report",
				"TRANSPORT"=>"X",
				"VISIBLE"=>"I"

				);

				CSocNetLogEvents::Add($arFields);
				$arFields["EVENT_ID"] = "report_comment";
				CSocNetLogEvents::Add($arFields);

			}
		}
	}

	public static function MessageUpdate($REPORT_ID)
	{
		global $DB,$USER;
		$curUser = $USER->GetID();
		if(CModule::IncludeModule("socialnetwork"))
		{
			$dbLog = CSocNetLog::GetList(Array(),Array("SOURCE_ID"=>$REPORT_ID,"EVENT_ID"=>"report"));
			if (!$arLog = $dbLog->Fetch())
			{
				$LOG_ID = CReportNotifications::SendMessage($REPORT_ID,false);
			}
			else
			{
				$LOG_ID = $arLog["ID"];
				CSocNetLog::Update($LOG_ID,Array("USER_ID"=>$curUser, "=LOG_UPDATE"=>$DB->CurrentTimeFunction()));
			}

			return $LOG_ID;
		}


		$dbLogRights = CSocNetLogRights::GetList(Array(),Array("LOG_ID"=>$LOG_ID));
		while($arRight = $dbLogRights->Fetch())
		$arRights[] = $arRight["GROUP_CODE"];
		if(!in_array("U".$curUser,$arRights))
		CSocNetLogRights::Add($LOG_ID,"U".$curUser);

		return false;
	}

	public static function GetRights($USER_ID)
	{
		$arRights = Array("U".$USER_ID);
		$arManagers = CTimeMan::GetUserManagers($USER_ID);
		if (is_array($arManagers) && count($arManagers) > 0)
		foreach($arManagers as $mID)
			$arRights[] = "U".$mID;

		return array_unique($arRights);
	}

	public static function GetByID($ID)
	{
		$ID = IntVal($ID);
		$dbUser = CUser::GetByID($ID);
		if ($arUser = $dbUser->GetNext())
		{
			$arUser["NAME_FORMATTED"] = CUser::FormatName(CSite::GetNameFormat(false), $arUser, true);
			$arUser["~NAME_FORMATTED"] =GetMessage("REPORT_TITLE2").htmlspecialcharsback($arUser["NAME_FORMATTED"]);
			return $arUser;
		}
		else
			return false;
	}

	public static function GetForShow($arDesc)
	{
		return GetMessage("REPORT_TITLE2").htmlspecialcharsback($arDesc["NAME_FORMATTED"]);
	}

	public static function OnFillSocNetAllowedSubscribeEntityTypes(&$arSocNetAllowedSubscribeEntityTypes)
	{
		define("SONET_WORK_REPORT_ENTITY", "R");
		$arSocNetAllowedSubscribeEntityTypes[] = SONET_WORK_REPORT_ENTITY;
		global $arSocNetAllowedSubscribeEntityTypesDesc;
		$arSocNetAllowedSubscribeEntityTypesDesc[SONET_WORK_REPORT_ENTITY] = array(

			"TITLE_LIST" => GetMessage("REPORT_TITLE"),
			"TITLE_ENTITY" =>GetMessage("REPORT_TITLE"),
			"CLASS_DESC_GET" => "CReportNotifications",
			"METHOD_DESC_GET" => "GetByID",
			"CLASS_DESC_SHOW" => "CReportNotifications",
			"METHOD_DESC_SHOW" => "GetForShow",
			"USE_CB_FILTER" => "Y",
			"HAS_CB" => "Y"
		);
	}


	public static function AddEvent(&$arEvent)
	{
		$arEvent["report"]= array(
			'ENTITIES' => array(
				SONET_WORK_REPORT_ENTITY => array(
					'TITLE' =>GetMessage("REPORT_TITLE"),
					"TITLE_SETTINGS_1" => "#TITLE#",
					"TITLE_SETTINGS_2" => "#TITLE#",
					"TITLE_SETTINGS_ALL" => GetMessage("REPORT_TITLE"),
					"TITLE_SETTINGS_ALL_1" => GetMessage("REPORT_TITLE"),
					"TITLE_SETTINGS_ALL_2" => GetMessage("REPORT_TITLE")
				)
			),
			'CLASS_FORMAT' => 'CReportNotifications',
			'METHOD_FORMAT' => 'FormatEvent',
			"FULL_SET" => array("report", "report_comment"),
			"COMMENT_EVENT" => array(
				"EVENT_ID" => "report_comment",
				"CLASS_FORMAT" => "CReportNotifications",
				"METHOD_FORMAT" => "FormatComment_Report",
				"ADD_CALLBACK" => array("CReportNotifications", "AddComment_Report")
			)
		);
	}

	public static function FormatEvent($arFields, $arParams, $bMail = false)
	{
		$user_url = (strlen($arParams["PATH_TO_USER"]) > 0 ? $arParams["PATH_TO_USER"] : COption::GetOptionString('intranet', 'path_user', '/company/personal/user/#USER_ID#/', $arFields["SITE_ID"]));
		$dbReport = CTimeManReportFull::GetByID($arFields["SOURCE_ID"]);
		$arReport = $dbReport->Fetch();
		$arManagers = CTimeMan::GetUserManagers($arReport["USER_ID"]);
		$arManagers[] = $arReport["USER_ID"];
		$arManagers = array_unique($arManagers);

		$dbManagers = CUser::GetList($by='ID', $order='ASC', array('ID' => implode('|', $arManagers)));
		$arCurrentUserManagers = array();
		while ($manager = $dbManagers->GetNext())
		{
			$tmpUser = array(
					'ID' => $manager['ID'],
					'LOGIN' => $manager['LOGIN'],
					'NAME' => CUser::FormatName(CSite::GetNameFormat(false), $manager, true, false),
					'URL' => str_replace(array('#ID#', '#USER_ID#', '#id#', '#user_id#'), $manager['ID'], $user_url),
					'WORK_POSITION' => $manager['WORK_POSITION'],
					'PERSONAL_PHOTO' => $manager['PERSONAL_PHOTO'],
					'SEX'=>$manager["PERSONAL_GENDER"]
				);
			if ($manager['ID'] == $arReport["USER_ID"])
				$arUser = $tmpUser;
			if (($manager['ID'] != $arReport["USER_ID"]) || count($arManagers) == 1)
				$arCurrentUserManagers[] = $tmpUser;
		}
		$arResult["EVENT"] = $arFields;
		if(!$bMail)
		{
			if(defined("BX_COMP_MANAGED_CACHE"))
			{
				$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_NAME_".intval($arUser["ID"]));
				$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_NAME_".intval($arCurrentUserManagers[0]["ID"]));
			}

			ob_start();
			$GLOBALS['APPLICATION']->IncludeComponent('bitrix:timeman.livefeed.workreport', ($arParams["MOBILE"] == "Y" ? 'mobile' : ''), array(
					"USER" => $arUser,
					"MANAGER" => $arCurrentUserManagers[0],
					"MARK" => $arReport["MARK"],
					"REPORT_ID" => $arReport["ID"],
					"PARAMS" => $arParams
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			$html_message = ob_get_contents();
			ob_end_clean();
			$arResult["EVENT"] = $arFields;

			if ($arParams["MOBILE"] == "Y")
				$arResult["EVENT_FORMATTED"] = Array(
					"TITLE_24" => ($arFields["USER_ID"] == $arFields["ENTITY_ID"]
									? GetMessage("REPORT_ADD_24".($arUser["SEX"] == "M" ? "_M" : ($arUser["SEX"] == "F" ? "_F" : "")))
									: GetMessage("REPORT_CHANGE_24".($arCurrentUserManagers[0]["SEX"] == "M" ? "_M" : ($arCurrentUserManagers[0]["SEX"] == "F" ? "_F" : "")))
								)." ".$arFields["TITLE"],
					"MESSAGE" => $html_message,
					"IS_IMPORTANT" => false,
					"DESCRIPTION" => (in_array($arReport["MARK"], array("G", "B")) ? array(GetMessage("REPORT_FULL_COMMENT_CONFIRM_MOBILE"), GetMessage("REPORT_FULL_COMMENT_CONFIRM_MOBILE_VALUE_".$arReport["MARK"])) : ($arReport["MARK"] == "N" ? GetMessage("REPORT_FULL_COMMENT_CONFIRM_WO_MARK") : "")),
					"DESCRIPTION_STYLE" => (in_array($arReport["MARK"], array("G", "B")) ? ($arReport["MARK"] == "G" ? "green" : "red") : false)
				);
			else
			{
				$arResult["EVENT_FORMATTED"] = Array(
					"TITLE" => ($arFields["USER_ID"] == $arFields["ENTITY_ID"]
									? (($arUser["SEX"] == "F") ? GetMessage("REPORT_ADD_W") : GetMessage("REPORT_ADD"))
									: (($arCurrentUserManagers[0]["SEX"] == "F") ? GetMessage("REPORT_CHANGE_W") : GetMessage("REPORT_CHANGE"))
								)." <a href='javascript:BX.StartSlider(".$arReport["USER_ID"].",".$arFields["SOURCE_ID"].");'>".$arFields["TITLE"]."</a>",
					"TITLE_24" => ($arFields["USER_ID"] == $arFields["ENTITY_ID"]
									? GetMessage("REPORT_ADD_24".($arUser["SEX"] == "M" ? "_M" : ($arUser["SEX"] == "F" ? "_F" : "")))
									: GetMessage("REPORT_CHANGE_24".($arCurrentUserManagers[0]["SEX"] == "M" ? "_M" : ($arCurrentUserManagers[0]["SEX"] == "F" ? "_F" : "")))
								)." <a href='javascript:BX.StartSlider(".$arReport["USER_ID"].",".$arFields["SOURCE_ID"].");'>".$arFields["TITLE"]."</a>",
					"URL" => "javascript:BX.StartSlider(".$arFields["USER_ID"].",".$arFields["SOURCE_ID"].");",
					"MESSAGE"=>$html_message,
					"SHORT_MESSAGE"=>$html_message,
					"IS_IMPORTANT" => false,
					"STYLE" => ($arReport["MARK"] == "G" ? "workday-confirm" : ($arReport["MARK"] == "B" ? "workday-rejected" : "workday-edit"))
				);

				if ($arParams["NEW_TEMPLATE"] != "Y")
					$arResult["EVENT_FORMATTED"]["IS_MESSAGE_SHORT"] = CSocNetLog::FormatEvent_IsMessageShort($arFields['MESSAGE']);

				$arResult['ENTITY']['FORMATTED']["NAME"] = GetMessage('REPORT_TITLE');
				$arResult['ENTITY']['FORMATTED']["URL"] = COption::GetOptionString("timeman","WORK_REPORT_PATH","/company/work_report.php");
			}
			$arResult['AVATAR_SRC'] = CSocNetLog::FormatEvent_CreateAvatar($arFields, $arParams, 'CREATED_BY');
			$arFieldsTooltip = array(
				'ID' => $arFields['USER_ID'],
				'NAME' => $arFields['~CREATED_BY_NAME'],
				'LAST_NAME' => $arFields['~CREATED_BY_LAST_NAME'],
				'SECOND_NAME' => $arFields['~CREATED_BY_SECOND_NAME'],
				'LOGIN' => $arFields['~CREATED_BY_LOGIN'],
			);
			$arResult['CREATED_BY']['TOOLTIP_FIELDS'] = CSocNetLog::FormatEvent_FillTooltip($arFieldsTooltip, $arParams);
		}
		else
		{
			$reportURL = COption::GetOptionString("timeman","WORK_REPORT_PATH","/company/work_report.php");
			if (strlen($reportURL) == 0)
				$reportURL = "/company/work_report.php";
			$reportURL = CSocNetLogTools::FormatEvent_GetURL(Array("URL"=>$reportURL,"SITE_ID"=>$arFields["SITE_ID"]));
			$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("REPORT_TITLE_FOR_MAIL");
			$arResult['EVENT_FORMATTED'] = Array(
				"TITLE"=>$arUser["NAME"]." ".(($arUser["SEX"] == "F")?GetMessage("REPORT_ADD_W"):GetMessage("REPORT_ADD"))." ".GetMessage("REPORT_WORK_REPORT"),
				"URL"=>$reportURL."#user_id=".$arReport["USER_ID"]."&report=".$arFields["SOURCE_ID"],
				"MESSAGE"=> $arFields["TITLE"],
				"IS_IMPORTANT"=> false
			);
		}
		return $arResult;
	}

	public static function FormatComment_Report($arFields, $arParams, $bMail = false, $arLog = array())
	{

		$arResult = array(
			"EVENT_FORMATTED" => array(),
		);

		if (!CModule::IncludeModule("socialnetwork"))
			return $arResult;

		if(!$bMail && $arParams["USE_COMMENT"] != "Y")
		{
			$arLog["ENTITY_ID"] = $arFields["ENTITY_ID"];
			$arLog["ENTITY_TYPE"] = $arFields["ENTITY_TYPE"];
		}

		$news_tmp = $arLog["TITLE"];
		$title_tmp = GetMessage("REPORT_NEW_COMMENT").'"'.$arLog["TITLE"].'"'."\n";
		$title_tmp.= GetMessage("COMMENT_AUTHOR").CUser::FormatName(CSite::GetNameFormat(false),
			array("NAME" => $arFields["CREATED_BY_NAME"], "LAST_NAME" => $arFields["CREATED_BY_LAST_NAME"], "SECOND_NAME" => $arFields["CREATED_BY_SECOND_NAME"], "LOGIN" => $arFields["CREATED_BY_LOGIN"]), true)."\n";
		$title_tmp.= GetMessage("COMMENT_TEXT");

		$title = str_replace(
			array("#TITLE#", "#ENTITY#"),
			array($news_tmp, ($bMail ? $arResult["ENTITY"]["FORMATTED"] : $arResult["ENTITY"]["FORMATTED"]["NAME"])),
			$title_tmp
		);

		$arResult["EVENT_FORMATTED"] = array(
			"TITLE" => ($bMail || $arParams["USE_COMMENT"] != "Y" ? $title : ""),
			"MESSAGE" => ($bMail ? $arFields["TEXT_MESSAGE"] : $arFields["MESSAGE"])
		);

		$arResult["ENTITY"]["TYPE_MAIL"] = GetMessage("REPORT_TITLE_FOR_MAIL");
		if ($bMail)
		{

			$reportURL = COption::GetOptionString("timeman","WORK_REPORT_PATH","/company/work_report.php");
			if (strlen($reportURL) == 0)
				$reportURL = "/company/work_report.php";
			$reportURL = CSocNetLogTools::FormatEvent_GetURL(Array("URL"=>$reportURL,"SITE_ID"=>$arFields["LOG_SITE_ID"]));
			if (strlen($reportURL) > 0)
				$arResult["EVENT_FORMATTED"]["URL"] = $reportURL."#user_id=".$arLog["ENTITY_ID"]."&report=".$arLog["SOURCE_ID"];
		}
		else
		{
			$parserLog = new logTextParser(false, $arParams["PATH_TO_SMILE"]);
			$arAllow = array(
				"HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y",
				"IMG" => "N", "LOG_IMG" => "N",
				"QUOTE" => "Y", "LOG_QUOTE" => "N",
				"CODE" => "Y", "LOG_CODE" => "N",
				"FONT" => "Y", "LOG_FONT" => "N",
				"LIST" => "Y",
				"SMILES" => "N",
				"NL2BR" => "Y",
				"MULTIPLE_BR" => "N",
				"VIDEO" => "Y", "LOG_VIDEO" => "N"
			);

			$arAllow = array("HTML" => "N", "ANCHOR" => "Y", "BIU" => "Y", "IMG" => "Y", "QUOTE" => "Y", "CODE" => "Y", "FONT" => "Y", "LIST" => "Y", "SMILES" => "Y", "NL2BR" => "N", "VIDEO" => "N", "LOG_VIDEO" => "N");
			$arResult["EVENT_FORMATTED"]["MESSAGE"] = htmlspecialcharsbx($parserLog->convert(htmlspecialcharsback($arResult["EVENT_FORMATTED"]["MESSAGE"]), array(), $arAllow));

			if (
				$arParams["MOBILE"] != "Y"
				&& $arParams["NEW_TEMPLATE"] != "Y"
			)
			{
				$arResult["EVENT_FORMATTED"]["SHORT_MESSAGE"] = $parserLog->html_cut(
					$parserLog->convert(htmlspecialcharsback($arResult["EVENT_FORMATTED"]["MESSAGE"]), array(), $arAllow),
					500
				);

				$arResult["EVENT_FORMATTED"]["IS_MESSAGE_SHORT"] = CSocNetLogTools::FormatEvent_IsMessageShort($arResult["EVENT_FORMATTED"]["MESSAGE"], $arResult["EVENT_FORMATTED"]["SHORT_MESSAGE"]);
			}
		}

		return $arResult;
	}

	public static function AddCommentToLog($arFields)
	{
		global $DB,$USER;
		CModule::IncludeModule("socialnetwork");

		$result = false;
		$dbLog = CSocNetLog::GetList(Array(),Array("SOURCE_ID"=>$arFields["REPORT_ID"],"EVENT_ID"=>"report"));
		if (!$arLog = $dbLog->Fetch())
		{
			$LOG_ID = CReportNotifications::SendMessage($arFields["REPORT_ID"],false);
		}
		else
			$LOG_ID = $arLog["ID"];
		$arMessFields = Array(
			"EVENT_ID"=>"report_comment",
			"ENTITY_ID"=>$arFields["REPORT_OWNER"],
			"TEXT_MESSAGE"=>$arFields["COMMENT_TEXT"],
			"MESSAGE"=>$arFields["COMMENT_TEXT"],
			"USER_ID"=>$arFields["USER_ID"],
			"ENTITY_TYPE"=>"R",
			"LOG_ID"=>$LOG_ID,
			"=LOG_DATE"=>$DB->CurrentTimeFunction()
			);

		$result = CSocNetLogComments::Add($arMessFields,true);
		$curUser = $USER->GetID();
		$dbLogRights = CSocNetLogRights::GetList(Array(),Array("LOG_ID"=>$LOG_ID));
		while($arRight = $dbLogRights->Fetch())
		$arRights[] = $arRight["GROUP_CODE"];
		if(!in_array("U".$curUser,$arRights))
		CSocNetLogRights::Add($LOG_ID,"U".$curUser);

		return $result;
	}

	public static function AddComment_Report($arFields)
	{
		$dbResult = CSocNetLog::GetList(
				array("ID" => "DESC"),
				array("TMP_ID" => $arFields["LOG_ID"]),
				false,
				false,
				array("ID", "SOURCE_ID", "PARAMS","SITE_ID")
			);

		$FORUM_ID = 0;
		if ($arLog = $dbResult->Fetch())
		{
			if ($arLog["SOURCE_ID"]>0)
			{
				$FORUM_ID = CTimeManNotify::GetForum($arLog);
			}
		}

		if ($FORUM_ID > 0)
			$arReturn = CReportNotifications::AddComment_Report_Forum($arFields, $FORUM_ID, $arLog);
		else
			$arReturn = array(
				"SOURCE_ID" => false,
				"ERROR" => GetMessage("SONET_ADD_COMMENT_SOURCE_ERROR"),
				"NOTES" => ""
			);

		return $arReturn;
	}

	public static function AddComment_Report_Forum($arFields,$FORUM_ID,$arLog)
	{
		global $USER,$DB;
		$mess_id = false;
		$dbReport = CTimeManReportFull::GetByID($arLog["SOURCE_ID"]);
		$arReport = $dbReport->Fetch();

		if(CModule::IncludeModule("forum") && $arReport)
		{
			if(!$userName = trim($USER->GetFormattedName(false)))
					$userName = $USER->GetLogin();
			if (!$arReport["FORUM_TOPIC_ID"])
			{
				$arTopicFields = Array(
					"TITLE"=>$arReport["DATE_FROM"]."-".$arReport["DATE_TO"],
					"USER_START_ID"=>$arFields["USER_ID"],
					"STATE"=>"Y",
					"FORUM_ID"=>$FORUM_ID,
					"USER_START_NAME"=>$userName,
					"START_DATE"=>ConvertTimeStamp(time(),"FULL"),
					"POSTS"=>0,
					"VIEWS"=>0,
					"APPROVED"=>"Y",
					"LAST_POSTER_NAME"=>$userName,
					"LAST_POST_DATE"=>ConvertTimeStamp(time(),"FULL"),
					"LAST_MESSAGE_ID"=>0

				);
				$TOPIC_ID = CForumTopic::Add($arTopicFields);
				if($TOPIC_ID)
					CTimeManReportFull::Update($arLog["SOURCE_ID"],Array("FORUM_TOPIC_ID"=>$TOPIC_ID));
			}
			else
				$TOPIC_ID = $arReport["FORUM_TOPIC_ID"];

			if ($TOPIC_ID)
			{
				$arFieldsP = array(
					"AUTHOR_ID" => $arFields["USER_ID"],
					"AUTHOR_NAME" => $userName,
					"POST_MESSAGE" => $arFields["TEXT_MESSAGE"],
					"FORUM_ID" => $FORUM_ID,
					"TOPIC_ID" =>$TOPIC_ID,
					"APPROVED" => "Y",
				);

				$mess_id = CForumMessage::Add($arFieldsP);
			}
		}

		return Array("SOURCE_ID" =>$mess_id);

	}
}

function ConvertTimeStampForReport($timestamp, $format = "FULL")
{
	$datatime = ConvertTimeStamp($timestamp, $format);
	$tm_format = TM_FULL_FORMAT;
	if ($format == "SHORT")
		$tm_format = TM_SHORT_FORMAT;
	return ConvertDateTime($datatime, $tm_format);
}
?>