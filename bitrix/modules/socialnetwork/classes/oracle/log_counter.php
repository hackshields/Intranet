<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_counter.php");

class CSocNetLogCounter extends CAllSocNetLogCounter
{

	function Increment($log_id, $entity_type = false, $entity_id = false, $event_id = false, $created_by_id = false, $arOfEntities = false, $arAdmin = false, $transport = false, $visible = "Y", $type = "L")
	{
		global $DB;

		if (intval($log_id) <= 0)
			return false;

		$counter = new CSocNetLogCounter;

		$subSelect = $counter->GetSubSelect($log_id, $entity_type, $entity_id, $event_id, $created_by_id, $arOfEntities, $arAdmin, $transport, $visible, $type);
		if (strlen($subSelect) > 0)
		{
			$strSQL = "MERGE INTO b_sonet_log_counter USING (".$subSelect.") source ON (source.ID = b_sonet_log_counter.USER_ID AND source.SITE_ID = b_sonet_log_counter.SITE_ID AND source.CODE = b_sonet_log_counter.CODE) WHEN MATCHED THEN UPDATE SET b_sonet_log_counter.CNT = b_sonet_log_counter.CNT + 1 WHEN NOT MATCHED THEN INSERT (USER_ID, CNT, SITE_ID, CODE) VALUES (source.ID, 1, source.SITE_ID, source.CODE)";
			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}

		$subSelect = $counter->GetSubSelect($log_id, $entity_type, $entity_id, $event_id, $created_by_id, $arOfEntities, $arAdmin, $transport, $visible, "group");
		if (strlen($subSelect) > 0)
		{
			$strSQL = "MERGE INTO b_sonet_log_counter USING (".$subSelect.") source ON (source.ID = b_sonet_log_counter.USER_ID AND source.SITE_ID = b_sonet_log_counter.SITE_ID AND source.CODE = b_sonet_log_counter.CODE) WHEN MATCHED THEN UPDATE SET b_sonet_log_counter.CNT = b_sonet_log_counter.CNT + 1 WHEN NOT MATCHED THEN INSERT (USER_ID, CNT, SITE_ID, CODE) VALUES (source.ID, 1, source.SITE_ID, source.CODE)";
			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}
	}

	function ClearByUser($user_id, $site_id = SITE_ID, $code = "**", $page_size = 0, $page_last_date_1 = "")
	{
		global $DB;

		$user_id = intval($user_id);
		if ($user_id <= 0)
			return false;

		$strSQL = "
			MERGE INTO b_sonet_log_counter USING (SELECT $user_id USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_sonet_log_counter.USER_ID
				AND source.SITE_ID = b_sonet_log_counter.SITE_ID
				AND source.CODE = b_sonet_log_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_sonet_log_counter.CNT = 0, b_sonet_log_counter.LAST_DATE = ".$DB->CurrentTimeFunction().(intval($page_size) > 0 ? ", PAGE_SIZE = ".$page_size : "").(strlen($page_last_date_1) > 0 ? ", PAGE_LAST_DATE_1 = ".$DB->CharToDateFunction($page_last_date_1) : "")." 
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE, PAGE_SIZE, PAGE_LAST_DATE_1)
				VALUES ($user_id, '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', 0, ".$DB->CurrentTimeFunction().", ".(intval($page_size) > 0 ? $page_size : "NULL").", ".(strlen($page_last_date_1) > 0 ? $DB->CharToDateFunction($page_last_date_1) : "NULL").")
		";
		$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$strSQL = "DELETE FROM b_sonet_log_counter WHERE USER_ID = ".$user_id." AND CODE = '".$code."' AND SITE_ID = '**'";
		$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}

	function dbIF($condition, $yes, $no)
	{
		return "case when ".$condition." then ".$yes." else ".$no." end ";
	}

}
?>