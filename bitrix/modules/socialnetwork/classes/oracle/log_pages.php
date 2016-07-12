<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_pages.php");

class CSocNetLogPages extends CAllSocNetLogPages
{
	function Set($user_id, $page_last_date, $page_size, $page_num = 1, $site_id = SITE_ID)
	{
		global $DB;

		$user_id = intval($user_id);
		$page_size = intval($page_size);
		$page_num = intval($page_num);

		if (
			$user_id <= 0
			|| $page_size <= 0
			|| strlen($page_last_date) <= 0
		)
			return false;

		$page_last_date = $DB->CharToDateFunction($page_last_date);

		$strSQL = "
			MERGE INTO b_sonet_log_page USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, ".$page_size." PAGE_SIZE, ".$page_num." PAGE_NUM FROM dual)
			source ON
			(
				source.USER_ID = b_sonet_log_page.USER_ID
				AND source.SITE_ID = b_sonet_log_page.SITE_ID
				AND source.PAGE_SIZE = b_sonet_log_page.PAGE_SIZE
				AND source.PAGE_NUM = b_sonet_log_page.PAGE_NUM
			)
			WHEN MATCHED THEN
				UPDATE SET b_sonet_log_page.PAGE_LAST_DATE = ".$page_last_date." 
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, PAGE_SIZE, PAGE_NUM, PAGE_LAST_DATE)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', ".$page_size.", ".$page_num.", ".$page_last_date.")
		";

		$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
	}
}
?>