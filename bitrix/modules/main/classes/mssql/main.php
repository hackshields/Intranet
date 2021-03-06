<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002-2007 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/

require_once(substr(__FILE__, 0, strlen(__FILE__) - strlen("/classes/mysql/main.php"))."/bx_root.php");

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/main.php");

class CMain extends CAllMain
{
	function err_mess()
	{
		return "<br>Class: CMain<br>File: ".__FILE__;
	}

	function __GetConditionFName()
	{
		return "CONDITION";
	}

	function FileAction()
	{
		global $DB;
		$io = CBXVirtualIo::GetInstance();
		$err_mess = (CMain::err_mess())."<br>Function: FileAction<br>Line: ";

		$DB->StartTransaction();
		$DB->Query("SET LOCK_TIMEOUT 0", false, $err_mess.__LINE__);
		if (!($rs = $DB->Query("SELECT * FROM b_file_action WITH (TABLOCKX)", true, $err_mess.__LINE__)))
		{
			$DB->Commit();
			return;
		}

		$upload_dir = COption::GetOptionString("main", "upload_dir", "upload");
		$delete_size = 0;
		while ($ar = $rs->Fetch())
		{
			if ($ar["FILE_ACTION"] == "DELETE")
			{
				$dir = $_SERVER["DOCUMENT_ROOT"]."/".$upload_dir."/".$ar["SUBDIR"]."/";
				$file = $io->GetFile($dir.$ar["FILE_NAME"]);
				if ($file->isExists())
				{
					$size = $file->getFileSize();
					if ($file->unlink())
						$delete_size+= $size;

					$directory = $io->GetDirectory($dir);
					if ($directory->isExists() && $directory->isEmpty())
						$directory->rmdir();
				}
				$delete_size+= CFile::ResizeImageDelete($ar);
			}
			$DB->Query("DELETE FROM b_file_action WHERE ID='".$ar["ID"]."'", false, $err_mess.__LINE__);
		}
		$DB->Query("SET LOCK_TIMEOUT -1", false, $err_mess.__LINE__);
		/****************************** QUOTA ******************************/
		if ($delete_size > 0 && COption::GetOptionInt("main", "disk_space") > 0)
			CDiskQuota::updateDiskQuota("file", $delete_size, "delete");
		/****************************** QUOTA ******************************/
		$DB->Commit();
	}

	function GetLang($cur_dir=false, $cur_host=false)
	{
		$err_mess = (CMain::err_mess())."<br>Function: GetLang<br>Line: ";
		global $DB, $lang, $DOCUMENT_ROOT, $MAIN_LANGS_CACHE, $MAIN_LANGS_ADMIN_CACHE;

		$arDef = array(
				"LID"				=> "en",
				"ID"				=> "en",
				"SITE_ID"			=> "en",
				"FORMAT_DATE"		=> "MM/DD/YYYY",
				"FORMAT_DATETIME"	=> "MM/DD/YYYY HH24:MI:SS"
			);

		if ($cur_dir===false)
			$cur_dir = $this->GetCurDir();

		if ($cur_host===false)
			$cur_host = $_SERVER["HTTP_HOST"];

		if (substr($cur_dir, 0, strlen(BX_ROOT."/admin/")) == BX_ROOT."/admin/"
			|| substr($cur_dir, 0, strlen(BX_ROOT."/updates/")) == BX_ROOT."/updates/"
			|| (defined("ADMIN_SECTION") &&  ADMIN_SECTION==true)
			|| (defined("BX_PUBLIC_TOOLS") && BX_PUBLIC_TOOLS===true)
		) //���� ������ �����������������
		{
			//���� �� ���������
			if (strlen($lang)<=0)
				$lang = COption::GetOptionString("main", "admin_lid", "ru");

			$rs = CLanguage::GetList($o, $b, Array("LID"=>$lang, "ACTIVE"=>$ACTIVE));
			if ($ar = $rs->Fetch())
			{
				$MAIN_LANGS_ADMIN_CACHE[$ar["LID"]] = $ar;
				return $ar;
			}

			//���� ���������� �� ������ - ����� ���� �� ���������
			$strSql = "
				SELECT
					*
				FROM
					b_language
				WHERE
					ACTIVE='Y'
				ORDER BY
					DEF DESC, SORT
				";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			if ($ar = $rs->Fetch())
			{
				$MAIN_LANGS_ADMIN_CACHE[$ar["LID"]] = $ar;
				return $ar;
			}
			//�� ���� ������ ������ �� ������ - ������ ������
			return $arDef;
		}
		else //��� ��������� �����
		{
			$arURL = parse_url("http://".$cur_host);
			if($arURL["scheme"]=="" && strlen($arURL["host"])>0)
				$CURR_DOMAIN = $arURL["host"];
			else
				$CURR_DOMAIN = $cur_host;

			if(strpos($CURR_DOMAIN, ':')>0)
				$CURR_DOMAIN = substr($CURR_DOMAIN, 0, strpos($CURR_DOMAIN, ':'));

			$CURR_DOMAIN = trim($CURR_DOMAIN, "\t\r\n\0 .");

			//������� ���� ���������� �� ����
			if(CACHED_b_lang!==false && CACHED_b_lang_domain!==false)
			{
				global $CACHE_MANAGER;
				$strSql =
					"SELECT L.*, L.LID as ID, L.LID as SITE_ID ".
					"FROM b_lang L ".
					"WHERE L.ACTIVE='Y' ".
					"ORDER BY ".
					"	LEN(L.DIR) DESC, ".
					"	L.DOMAIN_LIMITED DESC, ".
					"	SORT ";
				if($CACHE_MANAGER->Read(CACHED_b_lang, "b_lang".md5($strSql), "b_lang"))
				{
					$arLang = $CACHE_MANAGER->Get("b_lang".md5($strSql));
				}
				else
				{
					$arLang = array();
					$R = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($row = $R->Fetch())
						$arLang[]=$row;
					$CACHE_MANAGER->Set("b_lang".md5($strSql), $arLang);
				}

				$strSql =
					"SELECT  ".
					"LD.LID as LD_LID,LD.DOMAIN as LD_DOMAIN ".
					"FROM  ".
					"	 b_lang_domain LD  ".
					"ORDER BY ".
					"	LEN(LD.DOMAIN) DESC ";
				if($CACHE_MANAGER->Read(CACHED_b_lang_domain, "b_lang_domain2", "b_lang_domain"))
				{
					$arLangDomain = $CACHE_MANAGER->Get("b_lang_domain2");
				}
				else
				{
					$arLangDomain = array();
					$R = $DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
					while($row = $R->Fetch())
						$arLangDomain[$row["LD_LID"]][]=$row;
					$CACHE_MANAGER->Set("b_lang_domain2", $arLangDomain);
				}

				$arJoin = array();
				foreach($arLang as $row)
				{
					//LEFT JOIN
					$bLeft = true;
					//LEFT JOIN b_lang_domain LD ON L.LID=LD.LID
					if(array_key_exists($row["LID"], $arLangDomain))
						foreach($arLangDomain[$row["LID"]] as $dom)
							//AND '".$DB->ForSql($CURR_DOMAIN, 255)."' LIKE CONCAT('%', LD.DOMAIN)
							if(strcasecmp(substr($CURR_DOMAIN, -strlen($dom["LD_DOMAIN"])), $dom["LD_DOMAIN"]) == 0)
							{
								$arJoin[] = $row+$dom;
								$bLeft = false;
							}
					if($bLeft)
						$arJoin[] = $row+array("LD_LID"=>"","LD_DOMAIN"=>"");
				}
				$A = array();
				foreach($arJoin as $row)
				{
					//WHERE ('".$DB->ForSql($cur_dir)."' LIKE CONCAT(L.DIR, '%') OR LD.LID IS NOT NULL)
					if($row["LD_LID"]!="" || strcasecmp(substr($cur_dir, 0, strlen($row["DIR"])), $row["DIR"]) == 0)
						$A[]=$row;
				}

				$res=false;
				if($res===false)
					foreach($A as $row)
						if(
							(strcasecmp(substr($cur_dir, 0, strlen($row["DIR"])), $row["DIR"]) == 0)
							&& (($row["DOMAIN_LIMITED"]=="Y" && $row["LD_LID"]!="")||$row["DOMAIN_LIMITED"]!="Y")
						)
						{
							$res=$row;
							break;
						}
				if($res===false)
					foreach($A as $row)
						if(
							strncasecmp($cur_dir, $row["DIR"], strlen($cur_dir))==0
						)
						{
							$res=$row;
							break;
						}
				if($res===false)
					foreach($A as $row)
						if(
							(($row["DOMAIN_LIMITED"]=="Y" && $row["LD_LID"]!="")||$row["DOMAIN_LIMITED"]!="Y")
						)
						{
							$res=$row;
							break;
						}
				if($res===false && count($A)>0)
					$res=$A[0];
			}
			else
			{
				$strSql = "
					SELECT
						L.*,
						L.LID as ID,
						L.LID as SITE_ID
					FROM
						b_lang L
					LEFT JOIN b_lang_domain LD ON (L.LID = LD.LID and '".$DB->ForSql($CURR_DOMAIN, 255)."' like '%' + LD.DOMAIN)
					WHERE
						(
							'".$DB->ForSql($cur_dir)."' like L.DIR + '%' or
							LD.LID is not null
						)
					and L.ACTIVE='Y'
					ORDER BY
						CASE
							when (L.DOMAIN_LIMITED='Y' and LD.LID is not null) or L.DOMAIN_LIMITED<>'Y' THEN
								CASE
									WHEN '".$DB->ForSql($cur_dir)."' like L.DIR + '%' THEN 3
									ELSE 1
								END
							ELSE
								CASE
									WHEN '".$DB->ForSql($cur_dir)."' like L.DIR + '%' THEN 2
									ELSE 0
								END
						END DESC,
						LEN(L.DIR) DESC,
						L.DOMAIN_LIMITED DESC,
						SORT,
						LEN(LD.DOMAIN) DESC
					";
				$R = $DB->Query($strSql, false, "File: ".__FILE__." Line:".__LINE__);
				$res = $R->Fetch();
			}

			if ($res)
			{
				$MAIN_LANGS_CACHE[$res["LID"]] = $res;
				return $res;
			}

			//���� ���������� �� ������ - ����� ���� �� ���������
			$strSql = "
				SELECT
					L.*,
					L.LID as ID,
					L.LID as SITE_ID
				FROM
					b_lang L
				WHERE
					ACTIVE='Y'
				ORDER BY
					DEF DESC,
					SORT
				";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($ar = $rs->Fetch())
			{
				$MAIN_LANGS_CACHE[$ar["LID"]] = $ar;
				return $ar;
			}
		}
		return $arDef;
	}
}

class CSite extends CAllSite
{
	function err_mess()
	{
		return "<br>Class: CSite<br>File: ".__FILE__;
	}

	function GetCurTemplate()
	{
		$err_mess = (CSite::err_mess())."<br>Function: GetCurTemplate<br>Line: ";
		global $DB, $APPLICATION, $USER, $CACHE_MANAGER;
		if(CACHED_b_site_template===false)
		{
			$strSql = "
				SELECT
					".CMain::__GetConditionFName().",
					TEMPLATE
				FROM
					b_site_template
				WHERE
					SITE_ID='".SITE_ID."'
				ORDER BY
					CASE
						when len(isnull(".CMain::__GetConditionFName().",''))=0 then 2
						else 1
					END,
					SORT
				";
			$dbr = $DB->Query($strSql, false, $err_mess.__LINE__);
			while($ar = $dbr->Fetch())
			{
				$strCondition = trim($ar["CONDITION"]);
				if(strlen($strCondition)>0 && (!@eval("return ".$strCondition.";")))
					continue;
				if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]) && is_dir($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]))
					return $ar["TEMPLATE"];
			}
		}
		else
		{
			if($CACHE_MANAGER->Read(CACHED_b_site_template, "b_site_template"))
			{
				$arSiteTemplateBySite = $CACHE_MANAGER->Get("b_site_template");
			}
			else
			{
				$dbr = $DB->Query("
					SELECT
						".CMain::__GetConditionFName().",
						TEMPLATE,
						SITE_ID
					FROM
						b_site_template
					ORDER BY
						SITE_ID, CASE WHEN LEN(ISNULL(".CMain::__GetConditionFName().",''))=0 THEN 2 ELSE 1 END, SORT
				");
				while($ar = $dbr->Fetch())
					$arSiteTemplateBySite[$ar['SITE_ID']][]=$ar;
				$CACHE_MANAGER->Set("b_site_template", $arSiteTemplateBySite);
			}
			if(is_array($arSiteTemplateBySite[SITE_ID]))
			{
				foreach($arSiteTemplateBySite[SITE_ID] as $ar)
				{
					$strCondition = trim($ar["CONDITION"]);
					if(strlen($strCondition)>0 && (!@eval("return ".$strCondition.";")))
						continue;
					if(file_exists($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]) && is_dir($_SERVER["DOCUMENT_ROOT"].BX_PERSONAL_ROOT."/templates/".$ar["TEMPLATE"]))
						return $ar["TEMPLATE"];
				}
			}
		}

		return ".default";
	}
}

class CFilterQuery extends CAllFilterQuery
{
	function BuildWhereClause($word)
	{
		$this->cnt++;
		//if($this->cnt>10) return "1=1";

		global $DB;
		if (isset($this->m_kav[$word]))
			$word = $this->m_kav[$word];

		$this->m_words[] = $word;

		$word = $DB->ForSql($word, 2000);

		$n = count($this->m_fields);
		$ret = "";
		if ($n>1) $ret = "(";
		for ($i=0; $i<$n; $i++)
		{
			$field = $this->m_fields[$i];
			if ($this->procent=="Y")
			{
				$ret.= "
					(upper(convert(varchar(8000), $field)) like upper('%$word%') and $field is not null)
					";
			}
			elseif (strpos($word, "%")!==false || strpos($word, "_")!==false)
			{
				$ret.= "
					(upper(convert(varchar(8000), $field)) like upper('$word') and $field is not null)
					";
			}
			else
			{
				$ret.= "
					(upper(convert(varchar(8000), $field)) = upper(convert(varchar(8000), '$word')) and $field is not null)
					";
			}
			if ($i<>$n-1) $ret.= " OR ";
		}
		if ($n>1) $ret.= ")";
		return $ret;
	}
}

class CLang extends CSite
{
}
?>