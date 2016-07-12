<?php
require($_SERVER['DOCUMENT_ROOT'] . '/mobile/headers.php');
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/header.php');

$GLOBALS['APPLICATION']->IncludeComponent(
	'bitrix:mobile.crm.deal.view',
	'',
	array(
		'UID' => 'mobile_crm_deal_view',
		'ACTIVITY_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/activity/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'COMMUNICATION_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/comm/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#&type_id=#type_id#',		
		'EVENT_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/event/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'PRODUCT_ROW_LIST_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/product_row/list.php?entity_type_id=#entity_type_id#&entity_id=#entity_id#',
		'COMPANY_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/company/view.php?company_id=#company_id#',
		'CONTACT_SHOW_URL_TEMPLATE' => '#SITE_DIR#mobile/crm/contact/view.php?contact_id=#contact_id#',	
		'USER_PROFILE_URL_TEMPLATE' => '#SITE_DIR#mobile/users/?user_id=#user_id#'
	)
);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/footer.php');
