<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/socialnetwork/classes/general/log_favorites.php");

class CSocNetLogFavorites extends CAllSocNetLogFavorites
{

	function Add($user_id, $log_id)
	{
		global $DB;

		if (intval($user_id) <= 0 || intval($log_id) <= 0)
			return false;

		$strSQL = "SELECT * FROM b_sonet_log_favorites WHERE USER_ID = ".$user_id." AND LOG_ID = ".$log_id;
		$dbRes = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		
		if (!$arRes = $dbRes->Fetch())
		{
			$strSQL = "INSERT INTO b_sonet_log_favorites (USER_ID, LOG_ID) VALUES(".$user_id.", ".$log_id.")";
			if ($DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__))
				return true;
			else
				return false;
		}
		else
			return false;
	}

}
?>