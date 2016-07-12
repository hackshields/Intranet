<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/currency/general/currency_rate.php");

class CCurrencyRates extends CAllCurrencyRates
{
	function Add($arFields)
	{
		global $DB;
		global $CACHE_MANAGER;
		global $APPLICATION;
		global $stackCacheManager;

		$arMsg = array();

		if (!CCurrencyRates::CheckFields("ADD", $arFields))
			return false;

		$db_result = $DB->Query("SELECT 'x' ".
			"FROM B_CATALOG_CURRENCY_RATE ".
			"WHERE CURRENCY = '".$DB->ForSql($arFields["CURRENCY"])."' ".
			"	AND DATE_RATE = ".$DB->CharToDateFunction($DB->ForSql($arFields["DATE_RATE"]), "SHORT"));
		if ($db_result->Fetch())
		{
			$arMsg[] = array("id"=>"DATE_RATE", "text"=> GetMessage("ERROR_ADD_REC2"));
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		else
		{
			$stackCacheManager->Clear("currency_rate");
			CTimeZone::Disable();
			$ID = $DB->Add("b_catalog_currency_rate", $arFields);
			CTimeZone::Enable();

			if (defined("BX_COMP_MANAGED_CACHE"))
				$CACHE_MANAGER->ClearByTag("currency_id_".$arFields["CURRENCY"]);

			return $ID;
		}
	}

	function Update($ID, $arFields)
	{
		global $DB;
		global $CACHE_MANAGER;
		global $APPLICATION;
		global $stackCacheManager;

		$ID = intval($ID);
		$arMsg = array();

		if (!CCurrencyRates::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_result = $DB->Query("SELECT 'x' ".
			"FROM b_catalog_currency_rate ".
			"WHERE CURRENCY = '".$DB->ForSql($arFields["CURRENCY"])."' ".
			"	AND DATE_RATE = ".$DB->CharToDateFunction($DB->ForSql($arFields["DATE_RATE"]), "SHORT")." ".
			"	AND ID<>".$ID." ");
		if ($db_result->Fetch())
		{
			$arMsg[] = array("id"=>"DATE_RATE", "text"=> GetMessage("ERROR_ADD_REC2"));
			$e = new CAdminException($arMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		else
		{
			CTimeZone::Disable();
			$strUpdate = $DB->PrepareUpdate("b_catalog_currency_rate", $arFields);
			CTimeZone::Enable();
			if (!empty($strUpdate))
			{
				$strSql = "UPDATE b_catalog_currency_rate SET ".$strUpdate." WHERE ID = ".$ID;
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				$stackCacheManager->Clear("currency_rate");

				if (defined("BX_COMP_MANAGED_CACHE"))
					$CACHE_MANAGER->ClearByTag("currency_id_".$arFields["CURRENCY"]);
			}
		}
		return true;
	}

	function ConvertCurrency($valSum, $curFrom, $curTo, $valDate = "")
	{
		return doubleval(doubleval($valSum) * CCurrencyRates::GetConvertFactor($curFrom, $curTo, $valDate));
	}

	function GetConvertFactor($curFrom, $curTo, $valDate = "")
	{
		$obRates = new CCurrencyRates;
		return $obRates->GetConvertFactorEx($curFrom, $curTo, $valDate);
	}

	function _get_last_rates($valDate, $cur)
	{
		global $DB;

		$strSql = $DB->TopSql("
			SELECT C.AMOUNT, C.AMOUNT_CNT, CR.RATE, CR.RATE_CNT
			FROM
				b_catalog_currency C
				LEFT JOIN b_catalog_currency_rate CR ON (C.CURRENCY = CR.CURRENCY AND CR.DATE_RATE < convert(datetime, '".$DB->ForSql($valDate)."', 120))
			WHERE
				C.CURRENCY = '".$DB->ForSql($cur)."'
			ORDER BY
				DATE_RATE DESC
		", 1);
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res->Fetch();
	}
}
?>