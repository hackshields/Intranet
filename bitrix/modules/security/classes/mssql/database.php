<?
global $SECURITY_SESSION_DBH;
$SECURITY_SESSION_DBH = false;
class CSecurityDB
{
	function Init()
	{
		global $SECURITY_SESSION_DBH, $DB;

		if(is_resource($SECURITY_SESSION_DBH))
			return true;

		if(!is_object($DB))
			return false;

		//Unfortunatly there is no way to get really new connection
		//so we try to hack with space append
		if(strpos($DB->DBHost, ";")!==false)
			$DBHost = $DB->DBHost.";";
		else
			$DBHost = $DB->DBHost." ";

		$SECURITY_SESSION_DBH = @odbc_connect($DBHost, $DB->DBLogin, $DB->DBPassword);

		//In case of error just skip it over
		if(!is_resource($SECURITY_SESSION_DBH))
			return false;

		if($DB->DBName <> "")
			CSecurityDB::Query('USE "'.$DB->DBName.'"', '');

		odbc_autocommit($SECURITY_SESSION_DBH, true);

		return true;
	}

	function Disconnect()
	{
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			odbc_close($SECURITY_SESSION_DBH);
			$SECURITY_SESSION_DBH = false;
		}
	}

	function CurrentTimeFunction()
	{
		return "getdate()";
	}

	function SecondsAgo($sec)
	{
		return "DATEADD(SECOND, -".intval($sec).", GETDATE())";
	}

	function Query($strSql, $error_position)
	{
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			$result = @odbc_exec($SECURITY_SESSION_DBH, $strSql);
			if($result)
				return $result;
		}
		return false;
	}

	function QueryBind($strSql, $arBinds, $error_position)
	{
		foreach($arBinds as $key => $value)
			$strSql = str_replace(":".$key, "'".$value."'", $strSql);
		return CSecurityDB::Query($strSql, $error_position);
	}

	function Fetch($result)
	{
		if($result)
		{
			$row = @odbc_fetch_row($result);
			if($row)
			{
				$arRow = array();
				$numfields = odbc_num_fields($result);
				for($i = 1; $i <= $numfields; $i++)
					$arRow[odbc_field_name($result, $i)] = odbc_result($result, $i);
				return $arRow;
			}
		}
		return false;
	}

	function Lock($id, $timeout = 60)
	{
		global $SECURITY_SESSION_DBH;
		static $lock_id = "";

		if($id === false)
		{
			if(is_resource($SECURITY_SESSION_DBH) && $lock_id)
			{
				CSecurityDB::Query("SET LOCK_TIMEOUT -1", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
				odbc_commit($SECURITY_SESSION_DBH);
				odbc_autocommit($SECURITY_SESSION_DBH, true);
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			$lock_id = $id;
			odbc_autocommit($SECURITY_SESSION_DBH, false);
			while(true)
			{
				CSecurityDB::Query("SET LOCK_TIMEOUT ".($timeout*1000), "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
				$rsLock = CSecurityDB::Query("
					update b_sec_session
					set TIMESTAMP_X = getdate()
					where SESSION_ID = '".$lock_id."'
				", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);

				if($rsLock)
				{
					$rsLock = CSecurityDB::Query("
						select *
						from b_sec_session
						where SESSION_ID = '".$lock_id."'
					", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
					if(CSecurityDB::Fetch($rsLock))
					{
						return true;
					}
					else
					{
						$rsLock = CSecurityDB::Query("
							insert into b_sec_session values
							('".$lock_id."', getdate(), null)
						", "Module: security; Class: CSecurityDB; Function: Lock; File: ".__FILE__."; Line: ".__LINE__);
						if($rsLock)
							return true;
						else
							return false;
					}
				}
				else
				{
					return false;
				}
			}
		}
	}

	function LockTable($table_name, $lock_id)
	{
		global $SECURITY_SESSION_DBH;
		if(is_resource($SECURITY_SESSION_DBH))
		{
			odbc_autocommit($SECURITY_SESSION_DBH, false);
			CSecurityDB::Query("SET LOCK_TIMEOUT 0", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
			$rsLock = CSecurityDB::Query("SELECT * FROM $table_name WITH (TABLOCKX)", "Module: security; Class: CSecurityDB; Function: LockTable; File: ".__FILE__."; Line: ".__LINE__);
			if($rsLock)
			{
				return true;
			}
			else
			{
				odbc_commit($SECURITY_SESSION_DBH);
				odbc_autocommit($SECURITY_SESSION_DBH, true);
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	function UnlockTable($table_lock)
	{
		global $DB, $SECURITY_SESSION_DBH;
		if($table_lock)
		{
			CSecurityDB::Query("SET LOCK_TIMEOUT -1", "Module: security; Class: CSecurityDB; Function: UnlockTable; File: ".__FILE__."; Line: ".__LINE__);
			odbc_commit($SECURITY_SESSION_DBH);
			odbc_autocommit($SECURITY_SESSION_DBH, true);
		}
	}
}
?>