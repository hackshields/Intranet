<?
IncludeModuleLangFile(__FILE__);

class CClusterDBNodeCheck extends CAllClusterDBNodeCheck
{
	const OK = 1;
	const WARNING = 0;
	const ERROR = -1;

	function MainNodeCommon()
	{
		global $DB;

		$result = array();

		$is_ok = !file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/php_interface/after_connect.php");
		$result["after_connect"] = array(
			"IS_OK" => $is_ok? CClusterDBNodeCheck::OK: CClusterDBNodeCheck::ERROR,
			"MESSAGE" => GetMessage("CLU_AFTER_CONNECT_MSG"),
			"WIZ_REC" => GetMessage("CLU_AFTER_CONNECT_WIZREC"),
		);

		return $result;
	}

	function MainNodeForReplication()
	{
		$result = array();
		return $result;
	}

	function SlaveNodeConnection($db_host, $db_name, $db_login, $db_password, $master_host=false, $master_port=false)
	{
		global $DB;

		$node_id = "v99";
		CClusterDBNode::GetByID($node_id, array(
			"ACTIVE" => "Y",
			"STATUS" => "ONLINE",
			"DB_HOST" => $db_host,
			"DB_NAME" => $db_name,
			"DB_LOGIN" => $db_login,
			"DB_PASSWORD" => $db_password,
		));

		ob_start();
		$nodeDB = CDatabase::GetDBNodeConnection($node_id, true);
		$error = ob_get_contents();
		ob_end_clean();

		if(is_object($nodeDB))
		{
			//Test if this connection is not the same as master
			//1. Check if b_cluster_dbnode exists on node
			if($nodeDB->TableExists("b_cluster_dbnode"))
			{
				//2.1 Generate uniq id
				$uniqid = md5(mt_rand());
				$DB->Query("UPDATE b_cluster_dbnode SET UNIQID='".$uniqid."' WHERE ID=1", false, '', array("fixed_connection"=>true));
				$rs = $nodeDB->Query("SELECT UNIQID FROM b_cluster_dbnode WHERE ID=1", true);
				if($rs)
				{
					if($ar = $rs->Fetch())
					{
						if($ar["UNIQID"] == $uniqid)
							return GetMessage("CLU_SAME_DATABASE");
					}
				}
			}

			return $nodeDB;
		}
		else
		{
			return $error;
		}
	}

	function SlaveNodeCommon($nodeDB)
	{
		$result = array();
		return $result;
	}

	function SlaveNodeForReplication($nodeDB)
	{
		$result = array();
		return $result;
	}

	function GetServerVariables($DB, $arVariables, $db_mask)
	{
		return $arVariables;
	}

	function GetServerVariable($DB, $var_name)
	{
		return '';
	}

}
?>