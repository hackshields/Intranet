<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

if(!CModule::IncludeModule("lists"))
	return;

CLists::SetPermission('lists', array(1, WIZARD_PORTAL_ADMINISTRATION_GROUP));

COption::SetOptionString("lists", "socnet_iblock_type_id", "lists_socnet");
CLists::EnableSocnet(true);
?>