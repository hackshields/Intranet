<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Staff Changes");
?>
<?$APPLICATION->IncludeComponent("bitrix:intranet.structure.events", ".default", Array(
	"PM_URL"	=>	"#SITE_DIR#company/personal/messages/chat/#USER_ID#/",
	"STRUCTURE_PAGE"	=>	"#SITE_DIR#company/structure.php",
	"PATH_TO_CONPANY_DEPARTMENT" => "#SITE_DIR#company/structure.php?set_filter_structure=Y&structure_UF_DEPARTMENT=#ID#",
	"PATH_TO_VIDEO_CALL" => "#SITE_DIR#company/personal/video/#USER_ID#/",
	"STRUCTURE_FILTER"	=>	"structure",
	"NUM_USERS"	=>	"25",
	"NAME_TEMPLATE" => "",
	"NAV_TITLE"	=>	"Employees",
	"SHOW_NAV_TOP"	=>	"N",
	"SHOW_NAV_BOTTOM"	=>	"Y",
	"USER_PROPERTY"	=>	array(
		0	=>	"PERSONAL_PHONE",
		1	=>	"UF_DEPARTMENT",		
		2	=>	"UF_PHONE_INNER",
		3	=>	"UF_SKYPE",
	),
	"SHOW_FILTER"	=>	"Y"
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>