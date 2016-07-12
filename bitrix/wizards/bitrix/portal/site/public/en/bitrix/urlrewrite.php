<?
$arUrlRewrite = array(
	array(
		"CONDITION"	=>	"#^/company/personal/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:socialnetwork_user",
		"PATH"	=>	"/company/personal.php",
	),
	array(
		"CONDITION"	=>	"#^/about/gallery/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:photogallery",
		"PATH"	=>	"/about/gallery/index.php",
	),
	array(
		"CONDITION"	=>	"#^/docs/manage/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:webdav",
		"PATH"	=>	"/docs/manage/index.php",
	),
	array(
		"CONDITION"	=>	"#^/workgroups/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:socialnetwork_group",
		"PATH"	=>	"/workgroups/index.php",
	),
	array(
		"CONDITION"	=>	"#^/docs/shared/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:webdav",
		"PATH"	=>	"/docs/shared/index.php",
	),
	array(
		"CONDITION"	=>	"#^/docs/folder/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:webdav",
		"PATH"	=>	"/docs/folder/index.php",
	),
	array(
		"CONDITION"	=>	"#^/docs/sale/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:webdav",
		"PATH"	=>	"/docs/sale/index.php",
	),
	array(
		"CONDITION"	=>	"#^/services/lists/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:lists",
		"PATH"	=>	"/services/lists/index.php",
	),
	array(
		"CONDITION"	=>	"#^/services/faq/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:support.faq",
		"PATH"	=>	"/services/faq/index.php",
	),
	array(
		"CONDITION"	=>	"#^/services/bp/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:bizproc.wizards",
		"PATH"	=>	"/services/bp/index.php",
	),
	array(
		"CONDITION"	=>	"#^/docs/#",
		"RULE"	=>	"",
		"ID"	=>	"bitrix:webdav.aggregator",
		"PATH"	=>	"/docs/index.php",
	),
);

?>
