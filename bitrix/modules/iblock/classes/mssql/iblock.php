<?
class CIBlock extends CAllIBlock
{
	///////////////////////////////////////////////////////////////////
	// List of blocks
	///////////////////////////////////////////////////////////////////
	function GetList($arOrder=Array("SORT"=>"ASC"), $arFilter=Array(), $bIncCnt = false)
	{
		global $DB, $USER;

		$strSqlSearch = "";
		$bAddSites = false;
		foreach($arFilter as $key => $val)
		{
			$res = CIBlock::MkOperationFilter($key);
			$key = strtoupper($res["FIELD"]);
			$cOperationType = $res["OPERATION"];

			switch($key)
			{
			case "ACTIVE":
				$sql = CIBlock::FilterCreate("B.ACTIVE", $val, "string_equal", $cOperationType);
				break;
			case "LID":
			case "SITE_ID":
				$sql = CIBlock::FilterCreate("BS.SITE_ID", $val, "string_equal", $cOperationType);
				if(strlen($sql))
					$bAddSites = true;
				break;
			case "NAME":
			case "CODE":
			case "XML_ID":
				$sql = CIBlock::FilterCreate("B.".$key, $val, "string", $cOperationType);
				break;
			case "EXTERNAL_ID":
				$sql = CIBlock::FilterCreate("B.XML_ID", $val, "string", $cOperationType);
				break;
			case "TYPE":
				$sql = CIBlock::FilterCreate("B.IBLOCK_TYPE_ID", $val, "string", $cOperationType);
				break;
			case "ID":
			case "VERSION":
			case "SOCNET_GROUP_ID":
				$sql = CIBlock::FilterCreate("B.".$key, $val, "number", $cOperationType);
				break;
			default:
				$sql = "";
				break;
			}

			if(strlen($sql))
				$strSqlSearch .= " AND  (".$sql.") ";
		}

		$bCheckPermissions = !array_key_exists("CHECK_PERMISSIONS", $arFilter) || $arFilter["CHECK_PERMISSIONS"]!=="N";
		$bIsAdmin = is_object($USER) && $USER->IsAdmin();
		if($bCheckPermissions && !$bIsAdmin)
		{
			$min_permission = (strlen($arFilter["MIN_PERMISSION"])==1) ? $arFilter["MIN_PERMISSION"] : "R";
			if(is_object($USER))
			{
				$iUserID = intval($USER->GetID());
				$strGroups = $USER->GetGroups();
				$bAuthorized = $USER->IsAuthorized();
			}
			else
			{
				$iUserID = 0;
				$strGroups = "2";
				$bAuthorized = false;
			}

			$stdPermissions = "
				SELECT IBLOCK_ID
				FROM b_iblock_group IBG
				WHERE IBG.GROUP_ID IN (".$strGroups.")
				AND IBG.PERMISSION >= '".$min_permission."'
			";
			if(!defined("ADMIN_SECTION"))
				$stdPermissions .= "
					AND (IBG.PERMISSION='X' OR B.ACTIVE='Y')
				";

			if($min_permission >= "X")
				$operation = "'iblock_edit'";
			elseif($min_permission >= "U")
				$operation = "'iblock_admin_display'";
			else
				$operation = "'section_read', 'element_read', 'section_element_bind', 'section_section_bind'";

			if($operation)
			{
				$acc = new CAccess;
				$acc->UpdateCodes();

				$extPermissions = "
					SELECT IBLOCK_ID
					FROM b_iblock_right IBR
					INNER JOIN b_task_operation T ON T.TASK_ID = IBR.TASK_ID
					INNER JOIN b_operation O ON O.ID = T.OPERATION_ID
					".($iUserID > 0? "LEFT": "INNER")." JOIN b_user_access UA ON UA.ACCESS_CODE = IBR.GROUP_CODE AND UA.USER_ID = ".$iUserID."
					WHERE IBR.ENTITY_TYPE = 'iblock'
					AND O.NAME in (".$operation.")
					".($bAuthorized? "AND (UA.USER_ID IS NOT NULL OR IBR.GROUP_CODE = 'AU')": "")."
				";
				$sqlPermissions = "AND (
					B.ID IN ($stdPermissions)
					OR (B.RIGHTS_MODE = 'E' AND B.ID IN ($extPermissions))
				)";
			}
			else
			{
				$sqlPermissions = "AND (
					B.ID IN ($stdPermissions)
				)";
			}
		}
		else
		{
			$sqlPermissions = "";
		}

		if ($bAddSites)
			$sqlJoinSites = "LEFT JOIN b_iblock_site BS ON B.ID=BS.IBLOCK_ID
					LEFT JOIN b_lang L ON L.LID=BS.SITE_ID";
		else
			$sqlJoinSites = "INNER JOIN b_lang L ON L.LID=B.LID";

		if(!$bIncCnt)
		{
			$strSql = "
				SELECT
					B.*
					,B.XML_ID as EXTERNAL_ID
					,".$DB->DateToCharFunction("B.TIMESTAMP_X")." as TIMESTAMP_X
					,L.DIR as LANG_DIR
					,L.SERVER_NAME
				FROM
					b_iblock B
					".$sqlJoinSites."
				WHERE 1 = 1
					".$sqlPermissions."
					".$strSqlSearch."
			";
		}
		else
		{
			$strSql = "
				SELECT
					B.*
					,B.XML_ID as EXTERNAL_ID
					,".$DB->DateToCharFunction("B.TIMESTAMP_X")." as TIMESTAMP_X
					,L.DIR as LANG_DIR
					,L.SERVER_NAME
					,T.CNT as ELEMENT_CNT
				FROM
					b_iblock B
					".$sqlJoinSites."
					LEFT JOIN (
						SELECT COUNT(DISTINCT BE.ID) as CNT, BE.IBLOCK_ID
						FROM b_iblock_element BE
						WHERE (
							(BE.WF_STATUS_ID=1 AND BE.WF_PARENT_ELEMENT_ID IS NULL )
							".($arFilter["CNT_ALL"]=="Y"?" OR BE.WF_NEW = 'Y' ":"")."
						)
						".($arFilter["CNT_ACTIVE"]=="Y"? "
							AND BE.ACTIVE='Y'
							AND (BE.ACTIVE_TO >= ".$DB->CurrentDateFunction()." OR BE.ACTIVE_TO IS NULL)
							AND (BE.ACTIVE_FROM <= ".$DB->CurrentDateFunction()." OR BE.ACTIVE_FROM IS NULL)
						": "")."
						GROUP BY BE.IBLOCK_ID
					) T ON T.IBLOCK_ID = B.ID
				WHERE 1=1
					".$sqlPermissions."
					".$strSqlSearch."
			";
		}

		$arSqlOrder = Array();
		if(is_array($arOrder))
		{
			foreach($arOrder as $by=>$order)
			{
				$by = strtolower($by);
				$order = strtolower($order);
				if ($order!="asc")
					$order = "desc";

				if ($by == "id") $arSqlOrder[$by] = " B.ID ".$order." ";
				elseif ($by == "lid") $arSqlOrder[$by] = " B.LID ".$order." ";
				elseif ($by == "iblock_type") $arSqlOrder[$by] = " B.IBLOCK_TYPE_ID ".$order." ";
				elseif ($by == "name") $arSqlOrder[$by] = " B.NAME ".$order." ";
				elseif ($by == "active") $arSqlOrder[$by] = " B.ACTIVE ".$order." ";
				elseif ($by == "sort") $arSqlOrder[$by] = " B.SORT ".$order." ";
				elseif ($by == "code") $arSqlOrder[$by] = " B.CODE ".$order." ";
				elseif ($bIncCnt && $by == "element_cnt") $arSqlOrder[$by] = " ELEMENT_CNT ".$order." ";
				else
				{
					$by = "timestamp_x";
					$arSqlOrder[$by] = " B.TIMESTAMP_X ".$order." ";
				}
			}
		}

		if(count($arSqlOrder) > 0)
			$strSqlOrder = " ORDER BY ".implode(",", $arSqlOrder);
		else
			$strSqlOrder = "";

		$res = $DB->Query($strSql.$strSqlOrder, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		return $res;
	}

	function _Upper($str)
	{
		return "UPPER(".$str.")";
	}

	function _Add($ID)
	{
		global $DB;
		$err_mess = "FILE: ".__FILE__."<br>LINE: ";
		$ID=intval($ID);
		$strSql = "
			CREATE TABLE B_IBLOCK_ELEMENT_PROP_S".$ID." (
				IBLOCK_ELEMENT_ID 	int not null,
				CONSTRAINT PK_B_IBLOCK_ELEMENT_PROP_S".$ID." PRIMARY KEY (IBLOCK_ELEMENT_ID),
				CONSTRAINT FK_B_IBLOCK_ELEMENT_PROP_S".$ID." FOREIGN KEY (IBLOCK_ELEMENT_ID) REFERENCES B_IBLOCK_ELEMENT(ID)
			)
		";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$strSql = "
			CREATE TABLE B_IBLOCK_ELEMENT_PROP_M".$ID."
			(
				ID int NOT NULL IDENTITY (1, 1),
				IBLOCK_PROPERTY_ID int NOT NULL,
				IBLOCK_ELEMENT_ID int NOT NULL,
				VALUE varchar(2000) NOT NULL,
				VALUE_ENUM int NULL,
				VALUE_NUM numeric(18,4) NULL,
				DESCRIPTION varchar(255) NULL
				CONSTRAINT PK_B_IBLOCK_ELEM_PROP_M".$ID." PRIMARY KEY (ID),
				CONSTRAINT FK_B_IBLOCK_ELEM_PROP_M".$ID."_1 FOREIGN KEY (IBLOCK_PROPERTY_ID) REFERENCES B_IBLOCK_PROPERTY(ID),
				CONSTRAINT FK_B_IBLOCK_ELEM_PROP_M".$ID."_2 FOREIGN KEY (IBLOCK_ELEMENT_ID) REFERENCES B_IBLOCK_ELEMENT(ID)
			)
		";
		if($rs)
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$strSql = "
			CREATE INDEX IX_IBLOCK_ELEM_PROP_M".$ID."_1 on B_IBLOCK_ELEMENT_PROP_M".$ID." (IBLOCK_ELEMENT_ID,IBLOCK_PROPERTY_ID)
		";
		if($rs)
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$strSql = "
			CREATE INDEX IX_IBLOCK_ELEM_PROP_M".$ID."_2 on B_IBLOCK_ELEMENT_PROP_M".$ID." (IBLOCK_PROPERTY_ID)
		";
		if($rs)
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		$strSql = "
			CREATE INDEX IX_IBLOCK_ELEM_PROP_M".$ID."_3 on B_IBLOCK_ELEMENT_PROP_M".$ID." (VALUE_ENUM,IBLOCK_PROPERTY_ID)
		";
		if($rs)
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $rs;
	}

	function _Order($by, $order, $default_order, $nullable = true)
	{
		static $temp_by = 0;
		$o = parent::_Order($by, $order, $default_order, $nullable);
		//$o[0] - bNullsFirst
		//$o[1] - asc|desc
		if($o[0])
		{
			if($o[1] == "asc")
				return $by." asc";//
			else
				return array(
					"case when len(".$by.") > 0 then 1 else 0 end",
					"_IS_NULL_".$temp_by,
					"_IS_NULL_".($temp_by++)." asc, ".$by." desc",
				);
		}
		else
		{
			if($o[1] == "asc")
				return array(
					"case when len(".$by.") > 0 then 1 else 0 end",
					"_IS_NULL_".$temp_by,
					"_IS_NULL_".($temp_by++)." desc, ".$by." asc",
				);
			else
				return $by." desc";//
		}
	}

	function _NotEmpty($column)
	{
		return "case when ".$column." is null then 0 else 1 end";
	}
}
?>
