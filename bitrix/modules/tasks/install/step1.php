<?if(!check_bitrix_sessid()) return;?>
<?
global $errors;

if(!is_array($errors) && strlen($errors)<=0 || is_array($errors) && count($errors) <= 0)
{
	echo CAdminMessage::ShowNote(GetMessage("MOD_INST_OK"));
	if (COption::GetOptionString("intranet", "use_tasks_2_0", "N") != "Y")
	{
		echo "<form action=\"/bitrix/admin/tasks_convert_admin.php\"><input type=\"hidden\" name=\"lang\" value=\"".LANG."\" /><input type=\"submit\" value=\"".GetMessage("TASKS_CONVERT_AND_USE_NEW")."\" />";
	}
}
else
{
	for($i=0; $i<count($errors); $i++)
	{
		$alErrors .= $errors[$i]."<br>";
	}
	echo CAdminMessage::ShowMessage(Array("TYPE"=>"ERROR", "MESSAGE" =>GetMessage("MOD_INST_ERR"), "DETAILS"=>$alErrors, "HTML"=>true));
}
if ($ex = $APPLICATION->GetException())
{
	echo CAdminMessage::ShowMessage(Array("TYPE" => "ERROR", "MESSAGE" => GetMessage("MOD_INST_ERR"), "HTML" => true, "DETAILS" => $ex->GetString()));
}
?>