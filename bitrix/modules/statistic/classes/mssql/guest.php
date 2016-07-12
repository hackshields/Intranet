<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/statistic/classes/general/guest.php");
class CGuest extends CAllGuest
{
	function GetLastByID($ID)
	{
		$DB = CDatabase::GetModuleConnection('statistic');
		$ID = intval($ID);
		if($ID>0)
		{
			$strSql = "
				SELECT
					G.ID,
					G.FAVORITES,
					G.LAST_USER_ID,
					A.ID as LAST_ADV_ID,
					case when datediff(day, G.LAST_DATE, getdate())=0 then 'Y' else 'N' end LAST
				FROM b_stat_guest G
				LEFT JOIN b_stat_adv A ON A.ID = G.LAST_ADV_ID
				WHERE G.ID='$ID'
				";
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		else
		{
			$res = new CDBResult;
			$res->InitFromArray(array());
		}
		return $res;
	}
}
?>
