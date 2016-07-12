<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/agent.php");

class CAgent extends CAllAgent
{
	function err_mess()
	{
		return "<br>Class: CAgent<br>File: ".__FILE__;
	}

	function CheckAgents()
	{
		global $DB, $CACHE_MANAGER;

		if(defined("NO_AGENT_CHECK") && NO_AGENT_CHECK===true)
			return null;

		$err_mess = (CAgent::err_mess())."<br>Function: CheckAgents<br>Line: ";

		$agents_use_crontab	= COption::GetOptionString("main", "agents_use_crontab", "N");
		$str_crontab = "";
		if($agents_use_crontab=="Y" || (defined("BX_CRONTAB_SUPPORT") && BX_CRONTAB_SUPPORT===true))
		{
			$str_crontab = (defined("BX_CRONTAB") && BX_CRONTAB==true) ? " AND IS_PERIOD='N' " : " AND IS_PERIOD='Y' ";
		}

		$cache_id = "agents".$str_crontab;
		if(CACHED_b_agent !== false && $CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents"))
		{
			$saved_time = $CACHE_MANAGER->Get($cache_id);
			if(time() < $saved_time)
				return "";
		}

		$DB->StartTransaction();
		$DB->Query("SET LOCK_TIMEOUT 0", true, $err_mess.__LINE__);
		$strSql = "
			SELECT
				ID,
				NAME,
				AGENT_INTERVAL,
				IS_PERIOD,
				MODULE_ID
			FROM
				b_agent
			WITH (TABLOCKX)
			WHERE
					ACTIVE = 'Y'
				and NEXT_EXEC <= getdate()
				$str_crontab
			ORDER BY
				SORT desc
			";
		$rs = $DB->Query($strSql, true, $err_mess.__LINE__);
		$DB->Query("SET LOCK_TIMEOUT -1", true, $err_mess.__LINE__);
		$ignore_abort = false;
		$i=0;
		while (is_object($rs) && $arAgent = $rs->Fetch())
		{
			$i++;
			if (!$ignore_abort)
			{
				@set_time_limit(0);
				ignore_user_abort(true);
				$ignore_abort = true;
			}

			if(strlen($arAgent["MODULE_ID"])>0 && $arAgent["MODULE_ID"]!="main")
			{
				if(!CModule::IncludeModule($arAgent["MODULE_ID"]))
					continue;
			}

			//these vars can be assigned within agent code
			$pPERIOD = $arAgent["AGENT_INTERVAL"];

			CTimeZone::Disable();

			global $USER;
			unset($USER);
			$eval_result = "";
			eval("\$eval_result=".$arAgent["NAME"]);
			unset($USER);

			CTimeZone::Enable();

			if (strlen($eval_result)<=0)
			{
				$strSql = "
					DELETE FROM b_agent WHERE ID = ".$arAgent["ID"];
			}
			else
			{
				if ($arAgent["IS_PERIOD"]=="Y")
				{
					$strSql = "
						UPDATE b_agent SET
							NAME = '".$DB->ForSql($eval_result, 2000)."',
							LAST_EXEC = getdate(),
							NEXT_EXEC = dateadd(second, ".intval($pPERIOD).", NEXT_EXEC)
						WHERE
							ID = ".$arAgent["ID"];
				}
				else
				{
					$strSql = "
						UPDATE b_agent SET
							NAME = '".$DB->ForSql($eval_result, 2000)."',
							LAST_EXEC = getdate(),
							NEXT_EXEC = dateadd(second, ".intval($pPERIOD).", getdate())
						WHERE
							ID = ".$arAgent["ID"];
				}
			}
			$DB->Query($strSql, true, $err_mess.__LINE__);
		}
		$DB->Commit();
		if($i===0 && CACHED_b_agent!==false)
		{
			$rs = $DB->Query("SELECT DATEDIFF(s, GETDATE(), MIN(NEXT_EXEC)) DATE_DIFF FROM b_agent WHERE ACTIVE='Y'");
			$ar = $rs->Fetch();
			if(!$ar || $ar["DATE_DIFF"] < 0)
				$date_diff = 0;
			elseif($ar["DATE_DIFF"] > CACHED_b_agent)
				$date_diff = CACHED_b_agent;
			else
				$date_diff = $ar["DATE_DIFF"];

			if(isset($saved_time))
			{
				$CACHE_MANAGER->Clean($cache_id, "agents");
				$CACHE_MANAGER->Read(CACHED_b_agent, $cache_id, "agents");
			}
			$CACHE_MANAGER->Set($cache_id, intval(time()+$date_diff));
		}
		return null;
	}
}
