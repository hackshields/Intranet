<?
/*
##############################################
# Bitrix Site Manager                        #
# Copyright (c) 2002 - 2007 Bitrix           #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
*/
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/mail/classes/general/mail.php");
class CMailbox extends CAllMailBox
{
	function CleanUp()
	{
		global $DB;
		$days = COption::GetOptionInt("mail", "time_keep_log", B_MAIL_KEEP_LOG);

		$strSql = "DELETE FROM b_mail_log WHERE DATE_INSERT < SYSDATE-".intval($days);
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$strSql = "DELETE FROM b_mail_spam_weight WHERE TIMESTAMP_X < SYSDATE-30 AND TOTAL_CNT<100";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$strSql = "DELETE FROM b_mail_spam_weight WHERE TIMESTAMP_X < SYSDATE-120";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$mt = GetMicroTime();
		$dbr = $DB->Query("SELECT MS.ID FROM b_mail_message MS, b_mail_mailbox MB WHERE MS.MAILBOX_ID=MB.ID AND MB.MAX_KEEP_DAYS>0 AND MS.DATE_INSERT < SYSDATE - MB.MAX_KEEP_DAYS");
		while($ar = $dbr->Fetch())
		{
			CMailMessage::Delete($ar["ID"]);
			if(GetMicroTime() - $mt > 10 * 1000)
				break;
		}


		return "CMailbox::CleanUp();";
	}
}

class CMailUtil extends CAllMailUtil
{
	function IsSizeAllowed($size)
	{
		return true;
	}
}

class CMailMessage extends CAllMailMessage
{
	function AddAttachment($arFields)
	{
		global $DB;

		$strSql = "SELECT ATTACHMENTS FROM b_mail_message WHERE ID=".IntVal($arFields["MESSAGE_ID"]);
		$dbr = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if(!($dbr_arr = $dbr->Fetch()))
			return false;

		$n = IntVal($dbr_arr["ATTACHMENTS"])+1;
		if(strlen($arFields["FILE_NAME"])<=0)
		{
			$arFields["FILE_NAME"] = $n.".";
			if(strpos($arFields["CONTENT_TYPE"], "message/")===0)
				$arFields["FILE_NAME"] .= "msg";
			else
				$arFields["FILE_NAME"] .= "tmp";
		}

		if(is_set($arFields, "CONTENT_TYPE"))
			$arFields["CONTENT_TYPE"] = strtolower($arFields["CONTENT_TYPE"]);

		if(strpos($arFields["CONTENT_TYPE"], "image/")===0 && (!is_set($arFields, "IMAGE_WIDTH") || !is_set($arFields, "IMAGE_HEIGHT")) && is_set($arFields, "FILE_DATA"))
		{
			$filename = CTempFile::GetFileName(md5(uniqid("")).'.tmp');
			CheckDirPath($filename);
			if(file_put_contents($filename, $arFields["FILE_DATA"]) !== false)
			{
				$img_arr = CFile::GetImageSize($filename);
				$arFields["IMAGE_WIDTH"] = $img_arr? $img_arr[0]: 0;
				$arFields["IMAGE_HEIGHT"] = $img_arr? $img_arr[1]: 0;
			}
		}

		if(is_set($arFields, "FILE_DATA") && !is_set($arFields, "FILE_SIZE"))
			$arFields["FILE_SIZE"] = CUtil::BinStrlen($arFields["FILE_DATA"]);

		if(!CMailUtil::IsSizeAllowed($arFields["FILE_SIZE"]))
			return false;

		$ID = $DB->Add("b_mail_msg_attachment", $arFields, array("FILE_DATA"));

		if($ID>0)
		{
			$strSql = "UPDATE b_mail_message SET ATTACHMENTS=".$n." WHERE ID=".IntVal($arFields["MESSAGE_ID"]);
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		return $ID;

		/*
		$arFile = Array(
				"name"=>$filename,
				"size"=>strlen($part["BODY"]),
				"type"=>$part["CONTENT-TYPE"],
				"content"=>$part["BODY"],
				"MODULE_ID"=>"mail"
			);
		$file_id = CFile::SaveFile($arFile, "mail");
		*/
	}
}
?>