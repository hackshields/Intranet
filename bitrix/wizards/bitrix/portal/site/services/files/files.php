<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(file_exists(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/urlrewrite.php"))
{
	DeleteDirFilesEx("/bitrix/wizards/bitrix/portal/site/public/".LANGUAGE_ID."/urlrewrite.php");
}

if(file_exists(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp"))
{
	CopyDirFiles(
		WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp",
		WIZARD_SITE_PATH,
		$rewrite = false,
		$recursive = true,
		$delete_after_copy = true,
		$exclude = "bitrix"
	);

	unlink(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/saas_tmp");
}
if(!(WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011 && !WIZARD_FIRST_INSTAL) || WIZARD_B24_TO_CP)
{
	if (WIZARD_INSTALL_DEMO_DATA || !WIZARD_FIRST_INSTAL || WIZARD_B24_TO_CP)
	{
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/",
			WIZARD_SITE_PATH,
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false,
			$exclude = "bitrix"
		);
		if (WIZARD_B24_TO_CP)
		{
			if (file_exists(WIZARD_SITE_PATH."settings/"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."settings/");
			if (file_exists(WIZARD_SITE_PATH."bitrix/templates/login"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."bitrix/templates/login");
			if (file_exists(WIZARD_SITE_PATH."company/meeting"))
				DeleteDirFilesEx(WIZARD_SITE_DIR."company/meeting");	
		}
	}
}

/*if(WIZARD_INSTALL_MOBILE)
{
	if(!file_exists($_SERVER["DOCUMENT_ROOT"].WIZARD_SITE_PATH."/m/"))
	{
		CopyDirFiles(
			WIZARD_ABSOLUTE_PATH."/site/public_m/".LANGUAGE_ID."/",
			WIZARD_SITE_PATH."m/",
			$rewrite = true,
			$recursive = true,
			$delete_after_copy = false
		);

		if(!WIZARD_INSTALL_DEMO_DATA)
		{
			$arIblockRepl = Array(
				"OFFICIAL_NEWS_IBLOCK_ID" => Array(
							"xml_id" => "official_news_".WIZARD_SITE_ID,
							"type" => "news",
						),
				"OUR_LIFE_IBLOCK_ID" => Array(
							"code" => "our_life_".WIZARD_SITE_ID,
							"type" => "news",
						),
				"FILES_USER_IBLOCK_ID" => Array(
							"code" => "user_files",
							"site" => WIZARD_SITE_ID,
						),
				"FILES_GROUP_IBLOCK_ID" => Array(
							"code" => "group_files_".WIZARD_SITE_ID,
							"site" => WIZARD_SITE_ID,
						),
				"SHARED_FILES_IBLOCK_ID" => Array(
							"code" => "shared_files_".WIZARD_SITE_ID,
							"type" => "library",
						),
				"SALES_FILES_IBLOCK_ID" => Array(
							"code" => "sales_files_".WIZARD_SITE_ID,
							"type" => "library",
						),
				"DIRECTORS_FILES_IBLOCK_ID" => Array(
							"code" => "directors_files_".WIZARD_SITE_ID,
							"type" => "library",
						),

			);
			if(CModule::IncludeModule("iblock"))
			{
				$arReplace = array();
				foreach($arIblockRepl as $k => $v)
				{
					$arFilter = Array();
					if(strlen($v["code"]) > 0)
						$arFilter["CODE"] = $v["code"];
					if(strlen($v["type"]) > 0)
						$arFilter["TYPE"] = $v["type"];
					if(strlen($v["site"]) > 0)
						$arFilter["LID"] = $v["site"];

					$dbRes = CIBlock::GetList(array(), $arFilter);
					if ($arRes = $dbRes->Fetch())
						$arReplace[$k] = $arRes["ID"];
				}

				if(!empty($arReplace))
					CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH."/m/", $arReplace);
			}
			if(CModule::IncludeModule("forum"))
			{
				$forumCode = "intranet_tasks";
				$dbRes = CForumNew::GetListEx(array(), array("SITE_ID" => WIZARD_SITE_ID, "XML_ID" => $forumCode));
				if ($arRes = $dbRes->Fetch())
				{
					CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH."/m/tasks/", Array("TASKS_FORUM_ID" => $arRes["ID"]));
				}
			}
		}
	}
} */

if(!WIZARD_IS_RERUN){
	CopyDirFiles(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/bitrix/urlrewrite.php", WIZARD_SITE_PATH."/urlrewrite.php", false);
}

if(WIZARD_SITE_ID == 's1' && !WIZARD_NEW_2011){
	CopyDirFiles(WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/.department.menu_ext.php", WIZARD_SITE_PATH."/.department.menu_ext.php", false);
}

CWizardUtil::ReplaceMacrosRecursive(WIZARD_SITE_PATH, Array("SITE_DIR" => WIZARD_SITE_DIR));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/desktop.php", Array("SITE_ID" => WIZARD_SITE_ID));
CWizardUtil::ReplaceMacros(WIZARD_SITE_PATH."/_index.php", Array("SITE_ID" => WIZARD_SITE_ID));

if (WIZARD_INSTALL_DEMO_DATA || WIZARD_B24_TO_CP)
{
	$arUrlRewrite = array();
	if (file_exists(WIZARD_SITE_ROOT_PATH."/urlrewrite.php"))
	{
		include(WIZARD_SITE_ROOT_PATH."/urlrewrite.php");
	}

	$arNewUrlRewrite = array(
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/gallery/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:photogallery_user",
			"PATH"	=>	WIZARD_SITE_DIR."company/gallery/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."company/personal/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:socialnetwork_user",
			"PATH"	=>	WIZARD_SITE_DIR."company/personal.php",
		),
		/*array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."community/forum/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:forum",
			"PATH"	=>	WIZARD_SITE_DIR."community/forum.php",
		),*/
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."about/gallery/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:photogallery",
			"PATH"	=>	WIZARD_SITE_DIR."about/gallery/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."docs/manage/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:webdav",
			"PATH"	=>	WIZARD_SITE_DIR."docs/manage/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."workgroups/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:socialnetwork_group",
			"PATH"	=>	WIZARD_SITE_DIR."workgroups/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."docs/shared/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:webdav",
			"PATH"	=>	WIZARD_SITE_DIR."docs/shared/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."docs/folder/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:webdav",
			"PATH"	=>	WIZARD_SITE_DIR."docs/folder/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."docs/sale/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:webdav",
			"PATH"	=>	WIZARD_SITE_DIR."docs/sale/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/lists/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:lists",
			"PATH"	=>	WIZARD_SITE_DIR."services/lists/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/faq/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:support.faq",
			"PATH"	=>	WIZARD_SITE_DIR."services/faq/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."services/bp/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:bizproc.wizards",
			"PATH"	=>	WIZARD_SITE_DIR."services/bp/index.php",
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."docs/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:webdav.aggregator",
			"PATH"	=>	WIZARD_SITE_DIR."docs/index.php",
		),
		array(
			"CONDITION" => "#^".WIZARD_SITE_DIR."services/idea/#",
			"RULE" => "",
			"ID" => "bitrix:idea",
			"PATH" => WIZARD_SITE_DIR."services/idea/index.php"
		),
		array(
			"CONDITION"	=>	"#^".WIZARD_SITE_DIR."m/docs/#",
			"RULE"	=>	"",
			"ID"	=>	"bitrix:mobile.webdav.aggregator",
			"PATH"	=>	WIZARD_SITE_DIR."m/docs/index.php",
		),
	);
	foreach ($arNewUrlRewrite as $arUrl)
	{
		if (!in_array($arUrl, $arUrlRewrite))
		{
			CUrlRewriter::Add($arUrl);
		}
	}
}
?>