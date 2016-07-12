<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

CopyDirFiles(
	WIZARD_ABSOLUTE_PATH."/site/public/".LANGUAGE_ID."/",
	WIZARD_SITE_PATH,
	$rewrite = (WIZARD_B24_TO_CP) ? true : false, 
	$recursive = true,
	$delete_after_copy = false,
	$exclude = "bitrix"
);

$APPLICATION->SetFileAccessPermission(
	WIZARD_SITE_DIR."confirm/", 
	array("2" => "R")
);
?>