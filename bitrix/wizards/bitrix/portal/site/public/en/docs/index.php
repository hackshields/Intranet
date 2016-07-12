<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("All Documents");
?><?$APPLICATION->IncludeComponent(
	"bitrix:webdav.aggregator",
	"",
	Array(
		"SEF_MODE" => "Y",
		"IBLOCK_TYPE" => "library",
		"IBLOCK_OTHER_IDS" => array("#FILES_GROUP_IBLOCK_ID#", "#FILES_USER_IBLOCK_ID#", "#SHARED_FILES_IBLOCK_ID#", "#SALES_FILES_IBLOCK_ID#", "#DIRECTORS_FILES_IBLOCK_ID#"),
		"IBLOCK_GROUP_ID" => "#FILES_GROUP_IBLOCK_ID#",
		"IBLOCK_USER_ID" => "#FILES_USER_IBLOCK_ID#",
		"CACHE_TYPE" => "A",
		"CACHE_TIME" => "3600",
		"SEF_FOLDER" => "#SITE_DIR#docs/",
		"SEF_URL_TEMPLATES" => Array(
			"USER_FILE_PATH" => "#SITE_DIR#company/personal/user/#USER_ID#/files/lib/#PATH#",
			"GROUP_FILE_PATH" => "#SITE_DIR#workgroups/group/#GROUP_ID#/files/#PATH#"
		),
		"VARIABLE_ALIASES" => Array(
			"USER_FILE_PATH" => Array(),
			"GROUP_FILE_PATH" => Array(),
		)
	),
false
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
