<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule('tasks'))
{
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
}

$this->IncludeComponentTemplate();

?>
