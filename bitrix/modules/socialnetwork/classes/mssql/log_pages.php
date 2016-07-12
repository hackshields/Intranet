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

		$rs = $DB->Query("
			SELECT USER_ID FROM b_sonet_log_page
			WHERE USER_ID = ".$user_id."
			AND SITE_ID = '".$DB->ForSQL($site_id)."'
			AND PAGE_SIZE = ".$page_size."
			AND PAGE_NUM = ".$page_num."
		");
		if ($rs->Fetch())
			$DB->Query("
				UPDATE b_sonet_log_page SET
				PAGE_LAST_DATE = ".$page_last_date."
				WHERE USER_ID = ".$user_id."
				AND SITE_ID = '".$DB->ForSQL($site_id)."'
				AND PAGE_SIZE = ".$page_size."
				AND PAGE_NUM = ".$page_num."
			");
		else
			$DB->Query("
				INSERT INTO b_sonet_log_page
				(USER_ID, SITE_ID, PAGE_SIZE, PAGE_NUM, PAGE_LAST_DATE)
				VALUES
				(".$user_id.", '".$DB->ForSQL($site_id)."', ".$page_size.", ".$page_num.", ".$page_last_date.")
			", true);
	}
}
?>