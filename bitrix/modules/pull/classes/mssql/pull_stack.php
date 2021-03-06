<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/pull/classes/general/pull_stack.php");

class CPullStack extends CAllPullStack
{
	// check messages that are older than 24hours, remove them.
	// only works in PULL mode
	public static function CheckExpireAgent()
	{
		global $DB;
		if (!CPullOptions::ModuleEnable())
			return false;

		CAgent::RemoveAgent("CPullStack::CheckExpireAgent();", "pull");

		$strSql = "SELECT count(ID) CNT FROM b_pull_stack WHERE DATE_CREATE < dateadd(DAY, -1, getdate())";
		$dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arRes = $dbRes->Fetch())
		{
			$strSql = "DELETE TOP (1000) FROM b_pull_stack WHERE DATE_CREATE < dateadd(DAY, -1, getdate())";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if ($arRes['CNT'] > 1000)
			{
				CAgent::AddAgent("CPullStack::CheckExpireAgent();", "pull", "N", 600, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+600, "FULL"));
				return false;
			}
		}

		CAgent::AddAgent("CPullStack::CheckExpireAgent();", "pull", "N", 86400, "", "Y", ConvertTimeStamp(time()+CTimeZone::GetOffset()+86400, "FULL"));
		return false;
	}
}
?>