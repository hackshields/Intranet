<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/support/classes/general/sla.php");

class CTicketSLA extends CAllTicketSLA
{
	function err_mess()
	{
		$module_id = "support";
		@include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/install/version.php");
		return "<br>Module: ".$module_id." (".$arModuleVersion["VERSION"].")<br>Class: CTicketSLA<br>File: ".__FILE__;
	}

	// get SLA list
	function GetList(&$arSort, $arFilter=Array(), &$isFiltered)
	{
		$err_mess = (CTicketSLA::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB, $USER, $APPLICATION;
		$isFiltered = false;

		// filter params correct
		if (CTicket::CheckFilter($arFilter)):

			$arSqlSearch = Array();

			if (is_array($arFilter) && count($arFilter)>0):

				$filter_keys = array_keys($arFilter);
				for ($i=0; $i<count($filter_keys); $i++):

					$key = $filter_keys[$i];
					$val = $arFilter[$filter_keys[$i]];
					if ((is_array($val) && count($val)<=0) || (!is_array($val) && (strlen($val)<=0 || $val==='NOT_REF')))
						continue;
					$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
					$key = strtoupper($key);
					if (is_array($val)) $val = implode(" | ",$val);
					switch($key) :

						case "ID":
						case "SLA_ID":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("S.".$key,$val,$match);
							break;
						case "NAME":
						case "DESCRIPTION":
							$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
							$arSqlSearch[] = GetFilterQuery("S.".$key, $val, $match);
							break;
						case "SITE":
							$val .= " | ALL";
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch_1[] = GetFilterQuery("SS.SITE_ID", $val, $match);
							$strSqlSearch_1 = GetFilterSqlSearch($arSqlSearch_1);
							$where = " and exists (SELECT 'x' FROM b_ticket_sla_2_site SS WHERE $strSqlSearch_1 and S.ID = SS.SLA_ID) ";
							break;

					endswitch;
				endfor;
			endif;
		endif;

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		$arSort = is_array($arSort) ? $arSort : array();
		if (count($arSort)>0)
		{
			$ar1 = array_merge($DB->GetTableFieldsList("b_ticket_sla"), array());
			$ar2 = array_keys($arSort);
			$arDiff = array_diff($ar2, $ar1);
			if (is_array($arDiff) && count($arDiff)>0) foreach($arDiff as $value) unset($arSort[$value]);
		}
		if (count($arSort)<=0) $arSort = array("PRIORITY" => "DESC");
		while(list($by, $order) = each($arSort))
		{
			if( strtoupper( $order ) != "DESC" ) $order="ASC";
			if ($by=="RESPONSE_TIME")
			{
				$arSqlOrder[] = "case RESPONSE_TIME_UNIT when 'day' then 3 when 'hour' then 2 when 'minute' then 1 end $order";
				$arSqlOrder[] = $by." ".$order;
			}
			else
			{
				$arSqlOrder[] = $by." ".$order;
			}
		}
		if (is_array($arSqlOrder) && count($arSqlOrder)>0) $strSqlOrder = " ORDER BY ".implode(",", $arSqlOrder);

		$strSql = "
			SELECT
				S.*,
				case S.RESPONSE_TIME_UNIT
					when 'day' then S.RESPONSE_TIME*1440
					when 'hour' then S.RESPONSE_TIME*60
					when 'minute' then S.RESPONSE_TIME
				end												M_RESPONSE_TIME,
				case S.NOTICE_TIME_UNIT
					when 'day' then S.NOTICE_TIME*1440
					when 'hour' then S.NOTICE_TIME*60
					when 'minute' then S.NOTICE_TIME
				end												M_NOTICE_TIME,
				S.ID											REFERENCE_ID,
				S.NAME											REFERENCE,
				".$DB->DateToCharFunction("S.DATE_MODIFY")."	DATE_MODIFY_F,
				".$DB->DateToCharFunction("S.DATE_CREATE")."	DATE_CREATE_F
			FROM
				b_ticket_sla S
			WHERE
			$strSqlSearch
			$where
			$strSqlOrder
			";

		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$isFiltered = (IsFiltered($strSqlSearch));
		return $rs;
	}
}

?>