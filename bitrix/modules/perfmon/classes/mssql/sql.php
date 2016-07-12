<?
class CPerfomanceSQL extends CAllPerfomanceSQL
{
	function _console_explain($strSQL)
	{
	}

	function Clear()
	{
		global $DB;
		$res = $DB->Query("TRUNCATE TABLE b_perf_sql_backtrace");
		if($res)
			$res = $DB->Query("TRUNCATE TABLE b_perf_sql");
		return $res;
	}
}
?>