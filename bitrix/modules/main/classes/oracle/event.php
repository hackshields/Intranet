<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/event.php");

class CEvent extends CAllEvent
{
	function CheckEvents()
	{
		if((defined("DisableEventsCheck") && DisableEventsCheck===true) || (defined("BX_CRONTAB_SUPPORT") && BX_CRONTAB_SUPPORT===true && BX_CRONTAB!==true))
			return;

		$err_mess = "<br>Class: CEvent<br>File: ".__FILE__."<br>Function: CheckEvents<br>Line: ";
		global $DB, $CACHE_MANAGER;

		if(CACHED_b_event !== false && $CACHE_MANAGER->Read(CACHED_b_event, $cache_id = "events"))
			return "";

		$bulk = intval(COption::GetOptionString("main", "mail_event_bulk", 5));
		if($bulk <= 0)
			$bulk = 5;

		$DB->StartTransaction();

		$strSql = "
			SELECT /*+RULE*/ E.ID, E.C_FIELDS, E.EVENT_NAME, E.MESSAGE_ID, E.LID,
				TO_CHAR(E.DATE_INSERT, 'DD.MM.YYYY HH24:MI:SS') as DATE_INSERT, DUPLICATE
			FROM b_event E
			WHERE E.SUCCESS_EXEC='N'
			ORDER BY E.ID
			FOR UPDATE NOWAIT
			";

		if(!($rsMails = $DB->Query($strSql, true)))
		{
			$DB->Commit();
			return;
		}

		$cnt = 0;
		while($arMail = $rsMails->Fetch())
		{
			$flag = CEvent::HandleEvent($arMail);
			/*
			'0' - нет шаблонов (не нужно было ничего отправлять)
			'Y' - все отправлены
			'F' - все не смогли быть отправлены
			'P' - частично отправлены
			*/
			$strSql = "
				UPDATE b_event SET
					DATE_EXEC = SYSDATE,
					SUCCESS_EXEC = '$flag'
				WHERE
					ID = ".$arMail["ID"];
			$DB->Query($strSql, false, $err_mess.__LINE__);
			$cnt++;
			if($cnt >= $bulk)
				break;
		}

		$DB->Commit();
		if($cnt===0 && CACHED_b_event!==false)
			$CACHE_MANAGER->Set($cache_id, true);
	}

	function CleanUpAgent()
	{
		global $DB;
		$period = abs(intval(COption::GetOptionString("main", "mail_event_period", 14)));
		$strSql = "DELETE FROM b_event WHERE DATE_EXEC <= SYSDATE-".$period;
		$DB->Query($strSql, true);
		return "CEvent::CleanUpAgent();";
	}
}

///////////////////////////////////////////////////////////////////
// Класс почтовых шаблонов
///////////////////////////////////////////////////////////////////

class CEventMessage extends CAllEventMessage
{
	function GetList(&$by, &$order, $arFilter=Array())
	{
		$err_mess = "<br>Class: CEventMessage<br>File: ".__FILE__."<br>Function: GetList<br>Line: ";
		global $DB, $USER;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		$bIsLang = false;
		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				$key = strtoupper($key);
				switch($key)
				{
				case "ID":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
					$arSqlSearch[] = GetFilterQuery("M.ID", $val, $match);
					break;
				case "TYPE":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
					$arSqlSearch[] = GetFilterQuery("M.EVENT_NAME, T.NAME", $val, $match);
					break;
				case "EVENT_NAME":
				case "TYPE_ID":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
					$arSqlSearch[] = GetFilterQuery("M.EVENT_NAME", $val, $match);
					break;
				case "TIMESTAMP_1":
					$arSqlSearch[] = "M.TIMESTAMP_X>=TO_DATE('".FmtDate($val, "D.M.Y")." 00:00:00','dd.mm.yyyy hh24:mi:ss')";
					break;
				case "TIMESTAMP_2":
					$arSqlSearch[] = "M.TIMESTAMP_X<=TO_DATE('".FmtDate($val, "D.M.Y")." 23:59:59','dd.mm.yyyy hh24:mi:ss')";
					break;
				case "LID":
				case "LANG":
				case "SITE_ID":
					if (is_array($val)) $val = implode(" | ",$val);
					$arSqlSearch[] = GetFilterQuery("MS.SITE_ID",$val,"N");
					$bIsLang = true;
					break;
				case "ACTIVE":
					$arSqlSearch[] = ($val=="Y") ? "M.ACTIVE = 'Y'" : "M.ACTIVE = 'N'";
					break;
				case "FROM":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
					$arSqlSearch[] = GetFilterQuery("M.EMAIL_FROM", $val, $match);
					break;
				case "TO":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
					$arSqlSearch[] = GetFilterQuery("M.EMAIL_TO", $val, $match);
					break;
				case "BCC":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
					$arSqlSearch[] = GetFilterQuery("M.BCC", $val, $match);
					break;
				case "SUBJECT":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
					$arSqlSearch[] = GetFilterQuery("M.SUBJECT", $val, $match);
					break;
				case "BODY_TYPE":
					$arSqlSearch[] = ($val=="text") ? "M.BODY_TYPE = 'text'" : "M.BODY_TYPE = 'html'";
					break;
				case "BODY":
					$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
					$arSqlSearch[] = GetFilterQuery("M.MESSAGE", $val, $match);
					break;
				}
			}
		}

		if ($by == "id") $strSqlOrder = " ORDER BY M.ID ";
		elseif ($by == "active") $strSqlOrder = " ORDER BY M.ACTIVE ";
		elseif ($by == "event_name") $strSqlOrder = " ORDER BY M.EVENT_NAME ";
		elseif ($by == "from") $strSqlOrder = " ORDER BY M.EMAIL_FROM ";
		elseif ($by == "to") $strSqlOrder = " ORDER BY M.EMAIL_TO ";
		elseif ($by == "bcc") $strSqlOrder = " ORDER BY M.BCC ";
		elseif ($by == "body_type") $strSqlOrder = " ORDER BY M.BODY_TYPE ";
		elseif ($by == "subject") $strSqlOrder = " ORDER BY M.SUBJECT ";
		else
		{
			$strSqlOrder = " ORDER BY M.ID ";
			$by = "id";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order = "desc";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql =
			"SELECT M.ID, M.EVENT_NAME, M.LID, ".($bIsLang? "MS.SITE_ID":"M.LID AS SITE_ID").", M.ACTIVE, M.EMAIL_FROM, M.EMAIL_TO, M.SUBJECT, M.MESSAGE, M.BODY_TYPE, M.BCC,
				M.REPLY_TO,
				M.CC,
				M.IN_REPLY_TO,
				M.PRIORITY,
				M.FIELD1_NAME,
				M.FIELD1_VALUE,
				M.FIELD2_NAME,
				M.FIELD2_VALUE,
			".
			"	".$DB->DateToCharFunction("M.TIMESTAMP_X")." TIMESTAMP_X, ".
			"	decode(nvl(T.ID,0), ".
			"		0, M.EVENT_NAME,  ".
			"		'[ '||T.EVENT_NAME||' ] '||nvl(T.NAME,'') ".
			"	)	EVENT_TYPE ".
			"FROM b_event_message M ".
				($bIsLang?" LEFT JOIN b_event_message_site MS ON (M.ID = MS.EVENT_MESSAGE_ID)":"")." ".
			"       LEFT JOIN b_event_type T ON (T.EVENT_NAME = M.EVENT_NAME and T.LID = '".LANG_ADMIN."') ".
			"WHERE ".
			$strSqlSearch.
			$strSqlOrder;

		//echo $strSql;
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$res->is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}
}
?>