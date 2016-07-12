<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.contact.list',
	'',
	array(
		'UID' => 'mobile_crm_contact_list',
		'CONTACT_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/contact/view.php?contact_id=#contact_id#',
		'USER_PROFILE_URL_TEMPLATE' => '#SITE_DIR#mobile/users/?user_id=#user_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
