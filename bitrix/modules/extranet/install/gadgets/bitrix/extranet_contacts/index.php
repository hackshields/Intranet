<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("extranet"))
{
	return;
}

if (!function_exists('_FormatUser')) 
{

function _FormatUser(&$arUser, $arPath)
{

	global $USER, $CACHE_ABSENCE;

	if ($arUser['PERSONAL_PHOTO'])
	{
		$arImage = CIntranetUtils::InitImage($arUser['PERSONAL_PHOTO'], 50);
		$arUser['PERSONAL_PHOTO'] = $arImage['IMG'];
	}

	if ($arPath['DETAIL_URL'])
		$arUser['DETAIL_URL'] = str_replace('#ID#', $arUser['ID'], $arPath['DETAIL_URL']);

	$arUser["canViewProfile"] = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUser['ID'], "viewprofile", CSocNetUser::IsCurrentUserModuleAdmin());
	$arUser["canMessage"] = CSocNetUserPerms::CanPerformOperation($USER->GetID(), $arUser['ID'], "message", CSocNetUser::IsCurrentUserModuleAdmin());


	if ($arPath['MESSAGES_CHAT_URL'])
		$arUser['MESSAGES_CHAT_URL'] = str_replace('#ID#', $arUser['ID'], $arPath['MESSAGES_CHAT_URL']);

	$arUser['IS_ABSENT'] = CIntranetUtils::IsUserAbsent($arUser['ID']);
	$arUser['IS_ONLINE'] = CSocNetUser::IsOnLine($arUser['ID']);

	if ($arUser['IS_ABSENT'])
	{
		$maxAbsence = 0;
		foreach ($CACHE_ABSENCE[$arUser["ID"]] as $arAbsence)
		{
			if (MakeTimeStamp($arAbsence["DATE_TO"]) > $maxAbsence)
				$arUser['ABSENT_TILL'] = $arAbsence["DATE_TO"];
		}
	}

	$arFilter = array(
		"TO_USER_ID" => $arUser['ID'],
		"MESSAGE_TYPE" => SONET_MESSAGE_PRIVATE,
		"FROM_USER_ID" =>  $USER->GetID(),
	);

	$dbMessages = CSocNetMessages::GetList(
		array("DATE_CREATE" => "DESC"),
		$arFilter,
		false,
		array("nTopCount" => 1),
		array("ID", "DATE_CREATE")
	);

	if ($arMessages = $dbMessages->GetNext())
		$arUser['LAST_CHAT'] = $arMessages["DATE_CREATE"];
	
	return true;

}

}


if (!function_exists('_SortByLastMessage')) 
{

function _SortByLastMessage($a, $b)
{

	if (MakeTimeStamp($a["LAST_CHAT"]) == MakeTimeStamp($b["LAST_CHAT"]))
		return 0;

	return (MakeTimeStamp($a["LAST_CHAT"]) > MakeTimeStamp($b["LAST_CHAT"])) ? -1 : 1;

}

}

if (!function_exists('_ShowUserString')) 
{

function _ShowUserString($arUser, $is_public = false, $arParams, $arGadgetParams)
{

	?>
	<tr>
		<td colspan="2"><div class="gd-contacts-vertical-spacer"></div></td>
	</tr>
	<tr>
		<td colspan="2" width="100%" class="gd-contacts-main">
		<?
		$GLOBALS["APPLICATION"]->IncludeComponent("bitrix:main.user.link",
			'',
			array(
				"ID" => $arUser["ID"],
				"HTML_ID" => "extranet_contacts_".$arUser["ID"],
				"NAME" => $arUser["NAME"],
				"LAST_NAME" => $arUser["LAST_NAME"],
				"SECOND_NAME" => $arUser["SECOND_NAME"],
				"LOGIN" => $arUser["LOGIN"],
				"USE_THUMBNAIL_LIST" => "Y",
				"THUMBNAIL_LIST_SIZE" => 30,
				"PROFILE_URL" => $arUser["DETAIL_URL"],
				"PATH_TO_SONET_MESSAGES_CHAT" => $arGadgetParams["MESSAGES_CHAT_URL"],
				"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
				"SHOW_YEAR" => $arParams["SHOW_YEAR"],
				"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
				"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
				"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
				"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
			),
			false,
			array("HIDE_ICONS" => "Y")
		);
		?>
		</td>
	</tr>
	<tr>
		<td colspan="2" class="gd-contacts-delimiter"></td>
	</tr>
	<?
	return true;

}

}

if (!$arGadgetParams['DETAIL_URL'])
	$arGadgetParams['DETAIL_URL'] = SITE_DIR."personal/user/#ID#/";

if (!$arGadgetParams['MESSAGES_CHAT_URL'])
	$arGadgetParams['MESSAGES_CHAT_URL'] = SITE_DIR."personal/messages/chat/#ID#/";

if (!$arGadgetParams['FULLLIST_URL'])
	$arGadgetParams['FULLLIST_URL'] = SITE_DIR."contacts/";

if (!$arGadgetParams['EMPLOYEES_FULLLIST_URL'])
	$arGadgetParams['EMPLOYEES_FULLLIST_URL'] = SITE_DIR."contacts/employees.php";


$APPLICATION->SetAdditionalCSS('/bitrix/gadgets/bitrix/extranet_contacts/styles.css');

$arUsersInMyGroups = CExtranet::GetMyGroupsUsersFull(SITE_ID, true, true);

$arPublicUsers = CExtranet::GetPublicUsers(true);

$arUsersInMyGroupsFmt = array();
$arPublicUsersFmt = array();

?>
<table width="100%" cellspacing="0" cellpadding="0" border="0">
<?
echo "<tr><td colspan='2'><b>".GetMessage('GD_CONTACTS_MYGROUPS_USERS')."</b></td></tr>";

$arUsersInListID = array();

foreach ($arUsersInMyGroups as $arUser)
{
	$arUsersInListID[] = $arUser["ID"];
	_FormatUser($arUser, $arGadgetParams);
	$arUsersInMyGroupsFmt[] = $arUser;
	
}

uasort($arUsersInMyGroupsFmt, '_SortByLastMessage');

$nCount = 1;
foreach ($arUsersInMyGroupsFmt as $arUser)
{
	_ShowUserString($arUser, false, $arParams, $arGadgetParams);
	$nCount++;

	if ($nCount > $arGadgetParams['MY_WORKGROUPS_USERS_COUNT'])
		break;
}

foreach ($arPublicUsers as $arUser)
{
	if (in_array($arUser["ID"], $arUsersInListID))
		continue;

	_FormatUser($arUser, $arGadgetParams);
	$arPublicUsersFmt[] = $arUser;
	
}

uasort($arPublicUsersFmt, '_SortByLastMessage');

if (count($arPublicUsersFmt) > 0)
{
	echo "<tr><td colspan='2'><div class='gd-contacts-vertical-spacer'></div></td></tr>";
	echo "<tr><td colspan='2'><b>".GetMessage('GD_CONTACTS_PUBLIC_USERS')."</b></td></tr>";
}

$nCount = 1;
foreach ($arPublicUsersFmt as $arUser)
{
	_ShowUserString($arUser, true, $arParams, $arGadgetParams);
	$nCount++;

	if ($nCount > $arGadgetParams['PUBLIC_USERS_COUNT'])
		break;

}
?>
</table>
<div class="gd-contacts-vertical-spacer"></div>
<?
echo "<div class='fullist-links'><a href='".$arGadgetParams['FULLLIST_URL']."'>".GetMessage('GD_CONTACTS_FULLLIST')."</a></div>";
echo "<div class='fullist-links'><a href='".$arGadgetParams['EMPLOYEES_FULLLIST_URL']."'>".GetMessage('GD_CONTACTS_EMPLOYEES_FULLLIST')."</a></div>";
?>