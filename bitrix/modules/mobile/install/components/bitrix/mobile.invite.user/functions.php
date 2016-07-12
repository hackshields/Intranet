<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

function RegisterNewUser($SITE_ID, $arFields)
{
	global $USER;

	if (strlen($arFields["EMAIL"]) > 0)
	{
		$arEmailOriginal = preg_split("/[\n\r\t\,;\ ]+/", trim($arFields["EMAIL"]));

		foreach($arEmailOriginal as $addr)
		{
			if(strlen($addr) > 0 && check_email($addr))
			{
				$arEmail[] = $addr;
			}
		}
		if (count($arEmailOriginal) > count($arEmail))
			return array(GetMessage("BX24_INVITE_DIALOG_EMAIL_ERROR"));

		if (count($arEmail) > 0):
			$arEmailToRegister = array();
			$arEmailToReinvite = array();
			$arEmailExist = array();
			foreach($arEmail as $email)
			{
				$arFilter = array(
					//"ACTIVE"=>"Y",
					"=EMAIL"=>$email
				);

				$rsUser = CUser::GetList(($by="id"), ($order="asc"), $arFilter);
				$bFound = false;
				while ($arUser = $rsUser->GetNext())
				{
					$bFound = true;

					if ($arUser["LAST_LOGIN"] == "")
					{
						$arEmailToReinvite[] = array("EMAIL" => $email, "REINVITE" => true, "ID" => $arUser["ID"]);
					}
					else
					{
						$arEmailExist[] = $email;
					}
				}

				if (!$bFound )
					$arEmailToRegister[] = array("EMAIL" => $email, "REINVITE" => false);
			}
		endif;
	}

	$messageText = (isset($arFields["MESSAGE_TEXT"])) ? htmlspecialcharsbx($arFields["MESSAGE_TEXT"]) : GetMessage("BX24_INVITE_DIALOG_INVITE_MESSAGE_TEXT");
	if (isset($arFields["MESSAGE_TEXT"]))
		CUserOptions::SetOption("bitrix24", "invite_message_text", $arFields["MESSAGE_TEXT"]);
//reinvite users
	if (count($arEmailToReinvite) > 0)
	{
		foreach ($arEmailToReinvite as $key=>$userData)
		{
			$arUser = array(
				"CHECKWORD" => md5(CMain::GetServerUniqID().uniqid()),
			);

			$User = new CUser;
			$res = $User->Update($userData["ID"], $arUser);
			if(!$res)
			{
				$arErrors = preg_split("/<br>/", $User->LAST_ERROR);
				return $arErrors;
			}
			$ID = $userData["ID"];
			$event = new CEvent;
			$event->SendImmediate("BITRIX24_USER_INVITATION", $SITE_ID, array(
				"EMAIL_FROM" => $USER->GetEmail(),
				"EMAIL_TO" => $userData["EMAIL"],
				"LINK" => CHTTP::URN2URI("/bitrix/tools/invite_dialog.php?user_id=".$ID."&checkword=".urlencode($arUser["CHECKWORD"])),
				"USER_TEXT" => $messageText,
			));
		}
	}
//register users
	if (count($arEmailToRegister) > 0)
	{
		if (CModule::IncludeModule("bitrix24"))
		{
			$UserMaxCount = intval(COption::GetOptionString("main", "PARAM_MAX_USERS"));
			$currentUserCount = CBitrix24::ActiveUserCount();
			if ($UserMaxCount > 0 && count($arEmailToRegister) > $UserMaxCount - $currentUserCount)
				return array(GetMessage("BX24_INVITE_DIALOG_MAX_COUNT_ERROR"));
		}

		$arGroups = array();
		$rsGroups = CGroup::GetList($o, $b, array(
			"STRING_ID" => "EMPLOYEES_".$SITE_ID,
		));
		while($arGroup = $rsGroups->Fetch())
			$arGroups[] = $arGroup["ID"];

		$rsIBlock = CIBlock::GetList(array(), array("CODE" => "departments"));
		$arIBlock = $rsIBlock->Fetch();
		$iblockID = $arIBlock["ID"];

		if (!(isset($arFields["UF_DEPARTMENT"]) && intval($arFields["UF_DEPARTMENT"]) > 0))
		{
			$db_up_department = CIBlockSection::GetList(Array(), Array("SECTION_ID"=>0, "IBLOCK_ID"=>$iblockID));
			if ($ar_up_department = $db_up_department->Fetch())
			{
				$arFields["UF_DEPARTMENT"] = $ar_up_department['ID'];
			}
		}
		foreach ($arEmailToRegister as $key=>$userData)
		{
			$arUser = array(
				"LOGIN" => $userData["EMAIL"],
				"EMAIL" => $userData["EMAIL"],
				"UF_DEPARTMENT" => array($arFields["UF_DEPARTMENT"]),
				"PASSWORD" => randString(12, $password_chars = array(
					"abcdefghijklnmopqrstuvwxyz",
					"ABCDEFGHIJKLNMOPQRSTUVWXYZ",
					"0123456789",
					"(*)",
				)),
				"CHECKWORD" => md5(CMain::GetServerUniqID().uniqid()),
				"GROUP_ID" => $arGroups,
			);

			$User = new CUser;
			$ID = $User->Add($arUser);

			if(!$ID)
			{
				$arErrors = preg_split("/<br>/", $User->LAST_ERROR);
				return $arErrors;
			}
			else
			{
				$event = new CEvent;
				$event->SendImmediate("BITRIX24_USER_INVITATION", $SITE_ID, array(
					"EMAIL_FROM" => $USER->GetEmail(),
					"EMAIL_TO" => $userData["EMAIL"],
					"LINK" => CHTTP::URN2URI("/bitrix/tools/invite_dialog.php?user_id=".$ID."&checkword=".urlencode($arUser["CHECKWORD"])),
					"USER_TEXT" => $messageText,
				));
			}
		}
		return true;
	}
	if (count($arEmailExist) > 0)
		return array(GetMessage("BX24_INVITE_DIALOG_USER_EXIST_ERROR"));
	else
		return true;
}

function ReinviteUser($SITE_ID, $USER_ID)
{
	global $DB, $USER;
	$USER_ID = intval($USER_ID);

	$rsUser = CUser::GetList(
		$o="ID",
		$b="DESC",
		array("ID_EQUAL_EXACT" => $USER_ID)
	);
	if($arUser = $rsUser->Fetch())
	{
		$arNewUser = array(
			"CHECKWORD" => md5(CMain::GetServerUniqID().uniqid()),
		);
		$User = new CUser;
		$res = $User->Update($USER_ID, $arNewUser);

		$messageText = ($userMessageText = CUserOptions::GetOption("bitrix24", "invite_message_text")) ? htmlspecialcharsbx($userMessageText) : GetMessage("BX24_INVITE_DIALOG_INVITE_MESSAGE_TEXT");

		if($res)
		{
			$event = new CEvent;
			$event->SendImmediate("BITRIX24_USER_INVITATION", $SITE_ID, array(
				"EMAIL_FROM" => $USER->GetEmail(),
				"EMAIL_TO" => $arUser["EMAIL"],
				"LINK" => CHTTP::URN2URI("/bitrix/tools/invite_dialog.php?user_id=".$USER_ID."&checkword=".urlencode($arNewUser["CHECKWORD"])),
				"USER_TEXT" => $messageText,
			));
			return true;
		}
	}
	return false;
}
?>