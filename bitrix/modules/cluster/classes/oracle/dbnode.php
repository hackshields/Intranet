<?
IncludeModuleLangFile(__FILE__);

class CClusterDBNode extends CAllClusterDBNode
{
	function CheckFields(&$arFields, $ID)
	{
		global $DB, $APPLICATION;

		$this->LAST_ERROR = "";
		$aMsg = array();

		if(array_key_exists("NAME", $arFields))
			$arFields["NAME"] = trim($arFields["NAME"]);

		if(array_key_exists("ACTIVE", $arFields))
			$arFields["ACTIVE"] = $arFields["ACTIVE"] === "Y"? "Y": "N";

		if(array_key_exists("SELECTABLE", $arFields))
			$arFields["SELECTABLE"] = $arFields["SELECTABLE"] == "N"? "N": "Y";

		if(array_key_exists("WEIGHT", $arFields))
		{
			$weight = intval($arFields["WEIGHT"]);
			if($weight < 0)
				$weight = 0;
			elseif($weight > 100)
				$weight = 100;
			$arFields["WEIGHT"] = $weight;
		}

		if($arFields["ACTIVE"] == "Y" && $arFields["ROLE_ID"] != "SLAVE")
		{
			$obCheck = new CClusterDBNodeCheck;
			$nodeDB = $obCheck->SlaveNodeConnection(
				$arFields["DB_HOST"],
				$arFields["DB_NAME"],
				$arFields["DB_LOGIN"],
				$arFields["DB_PASSWORD"]
			);
			if(!is_object($nodeDB))
			{
				if(!array_key_exists("STATUS", $arFields))
					$arFields["STATUS"] = "OFFLINE";
				$aMsg[] = array("id" => "", "text" => $nodeDB);
			}
		}

		if(!empty($aMsg))
		{
			$e = new CAdminException($aMsg);
			$APPLICATION->ThrowException($e);
			return false;
		}
		return true;
	}

	function GetUpTime($node_id)
	{
		if($node_id > 1)
		{
			ob_start();
			$DB = CDatabase::GetDBNodeConnection($node_id, true, false);
			ob_end_clean();
		}
		else
		{
			$DB = $GLOBALS["DB"];
		}

		if(is_object($DB))
		{
			$rs = $DB->Query("
				select
				(sysdate-startup_time)*24*60*60 \"Uptime\"
				from v\$instance
			", true, '', array('fixed_connection'=>true));
			if(!$rs)
				return GetMessage("CLU_DBNODE_NO_PRIVS", array("#sql#" => "grant select any dictionary to ".$DB->DBLogin));
			if($ar = $rs->Fetch())
				return $ar["Uptime"];
		}

		return false;
	}
}
?>