<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.lead.list',
	'',
	array(
		'UID' => 'mobile_crm_lead_list',
		'LEAD_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/lead/view.php?lead_id=#lead_id#',
		'USER_PROFILE_URL_TEMPLATE' => '#SITE_DIR#mobile/users/?user_id=#user_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
