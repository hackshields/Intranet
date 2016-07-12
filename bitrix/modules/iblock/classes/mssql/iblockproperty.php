<?
class CIBlockProperty extends CAllIBlockProperty
{
	function _Update($ID, $arFields)
	{
		global $DB;
		$ID=intval($ID);
		$rsProperty = $this->GetByID($ID);
		$arProperty = $rsProperty->Fetch();
		if(!$arProperty)
		{
			$this->LAST_ERROR = $this->FormatNotFoundError($ID);
			return false;
		}
		if($arProperty["VERSION"]!=2)
		{
			return true;
		}
		if(is_set($arFields, "MULTIPLE") && $arFields["MULTIPLE"]!=$arProperty["MULTIPLE"])
		{//MULTIPLE changed
			if($arFields["MULTIPLE"]=="Y")
			{//MULTIPLE=Y
				$strSql = "
					INSERT INTO b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
					(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM, VALUE_NUM, DESCRIPTION)
					SELECT
						IBLOCK_ELEMENT_ID
						,".$arProperty["ID"]."
						,PROPERTY_".$arProperty["ID"]."
						,".($arProperty["PROPERTY_TYPE"]=="S" || $arProperty["PROPERTY_TYPE"]=="s"?
							"0":
							"PROPERTY_".$arProperty["ID"])."
						,".($arProperty["PROPERTY_TYPE"]=="S" || $arProperty["PROPERTY_TYPE"]=="s"?
							"0":
							"PROPERTY_".$arProperty["ID"])."
						,DESCRIPTION_".$arProperty["ID"]."
					FROM
						b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					WHERE
						PROPERTY_".$arProperty["ID"]." is not null
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS01");
					return false;
				}
				$strSql = "
					ALTER TABLE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					DROP COLUMN PROPERTY_".$arProperty["ID"]."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS02");
					return false;
				}
				$strSql = "
					ALTER TABLE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					ADD PROPERTY_".$arProperty["ID"]." text
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS03");
					return false;
				}
			}
			else
			{//MULTIPLE=N
				switch($arFields["PROPERTY_TYPE"])
				{
					case "S":
						$strType = "varchar(2000)";
						break;
					case "N":
						$strType = "numeric(18,4)";
						break;
					case "L":
					case "F":
					case "G":
					case "E":
						$strType = "int";
						break;
					default://s - small string
						$strType = "varchar(255)";
				}
				$strSql = "
					ALTER TABLE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					DROP COLUMN PROPERTY_".$arProperty["ID"]."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS04");
					return false;
				}
				$strSql = "
					ALTER TABLE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					ADD PROPERTY_".$arProperty["ID"]." ".$strType."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS05");
					return false;
				}
				switch($arFields["PROPERTY_TYPE"])
				{
					case "N":
						$strTrans = "EN.VALUE_NUM";
						break;
					case "L":
					case "F":
					case "G":
					case "E":
						$strTrans = "EN.VALUE_ENUM";
						break;
					case "s":
						$strTrans = "SUBSTR(EN.VALUE, 0 ,255)";
						break;
					default:
						$strTrans = "EN.VALUE";
				}
				$strSql = "
					UPDATE
						b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					SET
						PROPERTY_".$ID."=".$strTrans."
						,DESCRIPTION_".$ID." = EN.DESCRIPTION
					FROM
						b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]." EL
						,b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]." EN
					WHERE
						EN.IBLOCK_ELEMENT_ID = EL.IBLOCK_ELEMENT_ID
						AND EN.IBLOCK_PROPERTY_ID = ".$ID."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS06");
					return false;
				}
				$strSql = "
					DELETE FROM
						b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
					WHERE
						IBLOCK_PROPERTY_ID = ".$ID."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS07");
					return false;
				}
			}
		}
		else
		{//MULTIPLE not changed
			if(is_set($arFields ,"PROPERTY_TYPE")
			&& $arFields["PROPERTY_TYPE"]!=$arProperty["PROPERTY_TYPE"]
			&& $arProperty["MULTIPLE"]=="N")
			{
				switch($arFields["PROPERTY_TYPE"])
				{
					case "S":
						$strType = "varchar(2000)";
						break;
					case "N":
						$strType = "numeric(18,4)";
						break;
					case "L":
					case "F":
					case "G":
					case "E":
						$strType = "int";
						break;
					default://s - small string
						$strType = "varchar(255)";
				}
				$strSql = "
					INSERT INTO b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
					(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM, VALUE_NUM, DESCRIPTION)
					SELECT
						IBLOCK_ELEMENT_ID
						,".$arProperty["ID"]."
						,PROPERTY_".$arProperty["ID"]."
						,".($arProperty["PROPERTY_TYPE"]=="S" || $arProperty["PROPERTY_TYPE"]=="s"?
							"0":
							"PROPERTY_".$arProperty["ID"])."
						,".($arProperty["PROPERTY_TYPE"]=="S" || $arProperty["PROPERTY_TYPE"]=="s"?
							"0":
							"PROPERTY_".$arProperty["ID"])."
						,null
					FROM
						b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					WHERE
						PROPERTY_".$arProperty["ID"]." is not null
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS08");
					return false;
				}
				$strSql = "
					UPDATE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					SET PROPERTY_".$arProperty["ID"]."=null
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS09");
					return false;
				}
				$strSql = "
					ALTER TABLE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					DROP COLUMN PROPERTY_".$arProperty["ID"]."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS10");
					return false;
				}
				$strSql = "
					ALTER TABLE b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					ADD PROPERTY_".$arProperty["ID"]." ".$strType."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS11");
					return false;
				}
				switch($arFields["PROPERTY_TYPE"])
				{
					case "N":
						$strTrans = "EN.VALUE_NUM";
						break;
					case "L":
					case "F":
					case "G":
					case "E":
						$strTrans = "EN.VALUE_ENUM";
						break;
					case "s":
						$strTrans = "SUBSTR(EN.VALUE, 0 ,255)";
						break;
					default:
						$strTrans = "EN.VALUE";
				}
				$strSql = "
					UPDATE
						b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]."
					SET
						PROPERTY_".$ID."=".$strTrans."
						,DESCRIPTION_".$ID." = EN.DESCRIPTION
					FROM
						b_iblock_element_prop_s".$arProperty["IBLOCK_ID"]." EL
						,b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]." EN
					WHERE
						EN.IBLOCK_ELEMENT_ID = EL.IBLOCK_ELEMENT_ID
						AND EN.IBLOCK_PROPERTY_ID = ".$ID."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS12");
					return false;
				}
				$strSql = "
					DELETE FROM
						b_iblock_element_prop_m".$arProperty["IBLOCK_ID"]."
					WHERE
						IBLOCK_PROPERTY_ID = ".$ID."
				";
				if(!$DB->Query($strSql))
				{
					$this->LAST_ERROR = $this->FormatUpdateError($ID, "MS13");
					return false;
				}
			}
		}
		return true;
	}

	function DropColumnSQL($strTable, $arColumns)
	{
		$res = array();
		foreach($arColumns as $strColumn)
			$res[]="ALTER TABLE ".$strTable." DROP COLUMN ".$strColumn;
		return $res;
	}

	function _Add($ID, $arFields)
	{
		global $DB;
		$ID = IntVal($ID);

		if($arFields["MULTIPLE"]=="Y")
			$strType = "text";
		else
		{
			switch($arFields["PROPERTY_TYPE"])
			{
				case "S":
					$strType = "varchar(2000)";
					break;
				case "N":
					$strType = "numeric(18,4)";
					break;
				case "L":
				case "F":
				case "G":
				case "E":
					$strType = "int";
					break;
				default://s - small string
					$strType = "varchar(255)";
			}
		}
		$strSql = "
			ALTER TABLE b_iblock_element_prop_s".$arFields["IBLOCK_ID"]."
			ADD PROPERTY_".$ID." ".$strType.", DESCRIPTION_".$ID." varchar(255)
		";
		$rs = $DB->Query($strSql, true);
		return $rs;
	}
}
?>
