<?
class CFile extends CAllFile
{
	function err_mess()
	{
		return "<br>Class: CFile<br>File: ".__FILE__;
	}

	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if($ID <= 0)
			return;

		$res = CFile::GetByID($ID);
		if($res = $res->Fetch())
		{
			foreach(GetModuleEvents("main", "OnFileDelete", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($res));
		}

		$err_mess = (CFile::err_mess())."<br>Function: Delete<br>Line: ";
		$DB->Query("exec DELFILE ".$ID.", null", false, $err_mess.__LINE__);
	}

	function DoDelete($ID)
	{
		//nothing to do: images delete in triggers
	}
}
?>