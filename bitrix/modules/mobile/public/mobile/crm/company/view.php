<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.company.view',
	'',
	array(
		'UID' => 'mobile_crm_company_view',
		'ACTIVITY_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/activity/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'COMMUNICATION_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/comm/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#&type_id=#type_id#',		
		'EVENT_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/event/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'CONTACT_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/contact/list.php?company_id=#company_id#',
		'DEAL_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/deal/list.php?company_id=#company_id#',
		'USER_PROFILE_URL_TEMPLATE' => '#SITE_DIR#mobile/users/?user_id=#user_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
