<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!CModule::IncludeModule("intranet"))
	return "";

ob_start();
$this->IncludeComponentTemplate();
$result = ob_get_contents();
ob_end_clean();

return $result;
?>