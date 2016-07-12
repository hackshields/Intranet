<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("E-Mail Templates");
$APPLICATION->IncludeComponent(
	'bitrix:crm.mail_template', 
	'', 
	array(
		"SEF_MODE" => "Y",
		"SEF_FOLDER" => "#SITE_DIR#crm/configs/mailtemplate/",
	),
	false
); 
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");
?>