<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arWizardDescription = Array(
	"NAME" => GetMessage("EXTRANET_WIZARD_NAME"), 
	"DESCRIPTION" => GetMessage("EXTRANET_WIZARD_DESC"), 
	"VERSION" => "1.0.0",
	"START_TYPE" => "WINDOW",
	"TEMPLATES" => Array(
		Array("SCRIPT" => "scripts/template.php", "CLASS" => "WizardTemplate")
	),
/*
	"TEMPLATES" => Array(
		Array("SCRIPT" => "wizard_sol")
	),
*/
	"STEPS" => Array("WelcomeStep", "SelectTemplateStep", "SelectThemeStep", "SiteSettingsStep", "DataInstallStep" ,"FinishStep"),
);

?>