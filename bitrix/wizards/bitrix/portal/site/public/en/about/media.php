<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Video Archive");
?>

<?$APPLICATION->IncludeComponent("bitrix:iblock.tv", "round", Array(
	"IBLOCK_TYPE"	=>	"services",
	"IBLOCK_ID"	=>	"#VIDEO_IBLOCK_ID#",
	"PATH_TO_FILE"	=>	"#VIDEO_PATH_TO_FILE_ID#",
	"DURATION"	=>	"#VIDEO_DURATION_ID#",
	"SECTION_ID"	=>	"#VIDEO_SECTION_ID#",
	"ELEMENT_ID"	=>	"#VIDEO_ELEMENT_ID#",
	"WIDTH"	=>	"400",
	"HEIGHT"	=>	"300",
	"CACHE_TYPE"	=>	"A",
	"CACHE_TIME"	=>	"36000000"
	)
);?>

<p>Sample video content courtesy of Sun Microsystems, Inc. (<a href="http://channelsun.sun.com/video/about-sun/about+sun/1631259665/welcome+to+sun/1699225661">Original source</a>)</p> 

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>