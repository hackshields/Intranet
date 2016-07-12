<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

//cache data
$cache = new CPHPCache();
$cache_time = 3600*24*365;
$detailurl = $_REQUEST["detail_url"];
$cache_path = '/mobile_cache/'.$action;

if (in_array($action, array("get_user_list", "get_usergroup_list")))
{
	$withTags = ($_REQUEST["tags"] == "N" ? "N" : "Y");
	$cache_id = "mobileAction|get_users|".$GLOBALS["USER"]->GetID()."|".$detailurl."|".$withTags;
	if ($cache->InitCache($cache_time, $cache_id, $cache_path))
	{
		$cachedData = $cache->GetVars();
		$data = $cachedData["DATA"];
		$tableType = $cachedData["TYPE"];
	}
	else
	{
		$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_user2group_U".$GLOBALS["USER"]->GetID());
		$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_CARD");

		$tmpData = array(
			"NAME" => GetMessage("MD_EMPLOYEES_ALL"),
			"ID" => 0,
			"OUTSECTION" => true
		);
		if (SITE_CHARSET != "utf-8")
			$tmpData = $APPLICATION->ConvertCharsetArray($tmpData, SITE_CHARSET, "utf-8");

		$data = Array(
			$tmpData
		);
		$filter = array(
			"ACTIVE" => "Y"
		);

		/*
		if (IsModuleInstalled('bitrix24'))
			$filter["!LAST_ACTIVITY"] = false;
		*/

//		if (CModule::IncludeModule("extranet"))
		if (IsModuleInstalled("extranet"))
		{
			$filter["!UF_DEPARTMENT"] = false;
/*
			$arUsersInMyGroupsID = CExtranet::GetMyGroupsUsers(SITE_ID);

			if (CExtranet::IsIntranetUser())
			{
				$arIntranetUsersID = CExtranet::GetIntranetUsers();
				$arUsersToFilter = array_merge($arUsersInMyGroupsID, $arIntranetUsersID);
			}
			else
			{
				$arPublicUsersID = CExtranet::GetPublicUsers();
				$arUsersToFilter = array_merge($arUsersInMyGroupsID, $arPublicUsersID);
			}
			$arUsersToFilter = array_unique($arUsersToFilter);
			$filter["ID"] = implode("|", $arUsersToFilter);
*/
		}

		$arParams = Array("FIELDS" => Array("NAME", "ID", "PERSONAL_PHOTO", "LAST_NAME", "WORK_POSITION"));
		if($withTags == "Y")
		{
			$iblockId = COption::GetOptionInt('intranet', 'iblock_structure', 0);
			$arDepartaments = Array();
			$arSectionFilter = array(
				'IBLOCK_ID' => $iblockId,
			);

			$dbRes = CIBlockSection::GetList(
				array('LEFT_MARGIN' => 'DESC'),
				$arSectionFilter,
				false,
				array('ID', 'NAME')
			);

			while ($arRes = $dbRes->Fetch())
				$arDepartaments[$arRes["ID"]] = trim($arRes["NAME"]);
			$arParams["SELECT"] = Array("UF_DEPARTMENT");
		}

		$dbUsers = CUser::GetList(
			($by = array("last_name"=>"asc", "name"=>"asc")),
			($order = false),
			$filter,
			$arParams
		);
		while($userData = $dbUsers->Fetch())
		{
			if (intval($userData["PERSONAL_PHOTO"]) > 0)
			{
				$arImage = CFile::ResizeImageGet(
					$userData["PERSONAL_PHOTO"],
					array("width" => 64, "height" => 64),
					BX_RESIZE_IMAGE_EXACT,
					false
				);
				$img_src = $arImage["src"];
			}
			else
				$img_src = false;

			$tmpData = Array(
//				"NAME" => CUser::FormatName(CSite::GetNameFormat(false), $userData, true),
				"NAME" => CUser::FormatName("#LAST_NAME# #NAME#", $userData, true),
				"ID" => $userData["ID"],
				"IMAGE" => $img_src,
				"URL" => $detailurl.$userData["ID"],
				"TAGS"=> "",
				'WORK_POSITION'    => $userData['WORK_POSITION'],
				'WORK_DEPARTMENTS' => array()
			);

			if($withTags == "Y")
			{
				$arUserDepartments = array();
				foreach ($userData['UF_DEPARTMENT'] as $departmentId)
					$arUserDepartments[] = $arDepartaments[$departmentId];

				$tmpTags = array_merge(
					array(trim($userData['WORK_POSITION'])),
					$arUserDepartments
				);

				$tmpData["TAGS"] = implode(",", $tmpTags);
				$tmpData['WORK_DEPARTMENTS'] = $arUserDepartments;
			}

			if (SITE_CHARSET != "utf-8")
				$tmpData = $APPLICATION->ConvertCharsetArray($tmpData, SITE_CHARSET, "utf-8");
			$data[] = $tmpData;
		}

		$GLOBALS["CACHE_MANAGER"]->EndTagCache();

		$tableType = "a_users";

		if ($cache->StartDataCache())
			$cache->EndDataCache(
				array(
					"DATA" => $data,
					"TYPE" => $tableType
				)
			);
	}
	$tableTitle = GetMessage("MD_EMPLOYEES_TITLE");
	$tableData = AddTableData($tableData, $data, $tableTitle, $tableType);
}

if (in_array($action, array("get_group_list", "get_usergroup_list")))
{
	$cache_id = "mobileAction|get_groups|".$GLOBALS["USER"]->GetID()."|".$detailurl;
	if ($cache->InitCache($cache_time, $cache_id, $cache_path))
	{
		$cachedData = $cache->GetVars();
		$data = $cachedData["DATA"];
		$tableType = $cachedData["TYPE"];
	}
	else
	{
		if(CModule::IncludeModule("socialnetwork"))
		{
			$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_user2group_U".$GLOBALS["USER"]->GetID());
			$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_group");

			$data = Array();

			$arSonetGroups = CSocNetLogDestination::GetSocnetGroup(
				array(
					"features" => array("blog", array("premoderate_post", "moderate_post", "write_post", "full_post")),
					"THUMBNAIL_SIZE_WIDTH" => 64,
					"THUMBNAIL_SIZE_HEIGHT" => 64
				)
			);

			foreach($arSonetGroups as $arSocnetGroup)
			{
				$tmpData = Array(
					"NAME" => htmlspecialcharsback($arSocnetGroup["name"]),
					"ID" => $arSocnetGroup["entityId"],
					"IMAGE" => $arSocnetGroup["avatar"],
					//"URL" => $userData["ID"]
				);
				if(ToUpper(SITE_CHARSET)!="UTF-8")
					$tmpData = $APPLICATION->ConvertCharsetArray($tmpData, SITE_CHARSET, "utf-8");
				$data[] = $tmpData;
			}

			$GLOBALS["CACHE_MANAGER"]->EndTagCache();

			$tableType = "b_groups";

			if ($cache->StartDataCache())
				$cache->EndDataCache(
					array(
						"DATA" => $data,
						"TYPE" => $tableType
					)
				);
		}
	}
	$tableTitle = GetMessage("MD_GROUPS_TITLE");

	if (count($data) > 0)
		$tableData = AddTableData($tableData, $data, $tableTitle, $tableType);
}

$data = $tableData;
?>