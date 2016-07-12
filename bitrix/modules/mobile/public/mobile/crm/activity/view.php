<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.activity.view',
	'',
	array(
		'UID' => 'mobile_crm_activity_view',
		'LEAD_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/lead/view.php?lead_id=#lead_id#',
		'DEAL_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/deal/view.php?deal_id=#deal_id#',
		'CONTACT_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/contact/view.php?contact_id=#contact_id#',
		'COMPANY_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/company/view.php?company_id=#company_id#',
		'USER_PROFILE_URL_TEMPLATE' => '#SITE_DIR#mobile/users/?user_id=#user_id#',
		'COMMUNICATION_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/comm/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#&type_id=#type_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
