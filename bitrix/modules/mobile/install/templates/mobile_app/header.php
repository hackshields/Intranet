<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$platform = "android";
if(CModule::IncludeModule("mobileapp"))
{
    CMobile::Init();
    $platform = CMobile::$platform;
}

$APPLICATION->IncludeComponent("bitrix:mobile.data","",Array(
	"START_PAGE"=>"/mobile/index.php",
	"MENU_PAGE"=>"/mobile/left.php"
),false, Array("HIDE_ICONS" => "Y"));
?><!DOCTYPE html>
<html<?=$APPLICATION->ShowProperty("Manifest");?> class="<?=$platform;?>">
<head>
	<meta http-equiv="Content-Type" content="text/html;charset=<?=SITE_CHARSET?>"/>
	<meta name="format-detection" content="telephone=no">
	<link href="<?=CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/template_styles.css")?>" type="text/css" rel="stylesheet" />
	<?$APPLICATION->ShowHeadStrings(true);?>
	<?$APPLICATION->ShowHeadStrings();?>
	<?$APPLICATION->ShowHeadScripts();?>
	<?CJSCore::Init('ajax');?>
	<title><?$APPLICATION->ShowTitle()?></title>
</head>
<body class="<?=$APPLICATION->ShowProperty("BodyClass");?>">

