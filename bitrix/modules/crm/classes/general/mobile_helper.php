<?php
class CCrmMobileHelper
{
	private static $LEAD_STATUSES = null;
	private static $DEAL_STAGES = null;

	public static function PrepareDealItem(&$item, &$arParams)
	{
		$itemID = intval($item['~ID']);

		$item['SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
			$arParams['DEAL_SHOW_URL_TEMPLATE'],
			array('deal_id' => $itemID)
		);

		if(!isset($item['~TITLE']))
		{
			$item['~TITLE'] = $item['TITLE'] =  $itemID;
		}

		if(!isset($item['~OPPORTUNITY']))
		{
			$item['~OPPORTUNITY'] = $item['OPPORTUNITY'] = 0;
		}

		if(!isset($item['~CURRENCY_ID']))
		{
			$item['~CURRENCY_ID'] =  CCrmCurrency::GetBaseCurrencyID();
			$item['CURRENCY_ID'] = htmlspecialcharsbx($item['~CURRENCY_ID']);
		}

		$item['~FORMATTED_OPPORTUNITY'] = CCrmCurrency::MoneyToString($item['~OPPORTUNITY'], $item['~CURRENCY_ID']);
		$item['FORMATTED_OPPORTUNITY'] = htmlspecialcharsbx($item['~FORMATTED_OPPORTUNITY']);

		$contactID = isset($item['~CONTACT_ID']) ? intval($item['~CONTACT_ID']) : 0;
		$item['~CONTACT_ID'] = $item['CONTACT_ID'] = $contactID;
		$item['CONTACT_SHOW_URL'] = $contactID > 0
			? CComponentEngine::MakePathFromTemplate(
				$arParams['CONTACT_SHOW_URL_TEMPLATE'], array('contact_id' => $contactID)
			) : '';

		$item['~CONTACT_FORMATTED_NAME'] = $contactID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => '',
					'NAME' => isset($item['~CONTACT_NAME']) ? $item['~CONTACT_NAME'] : '',
					'LAST_NAME' => isset($item['~CONTACT_LAST_NAME']) ? $item['~CONTACT_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['~CONTACT_SECOND_NAME']) ? $item['~CONTACT_SECOND_NAME'] : ''
				),
				false, false
			) : '';
		$item['CONTACT_FORMATTED_NAME'] = htmlspecialcharsbx($item['~CONTACT_FORMATTED_NAME']);

		$companyID = isset($item['~COMPANY_ID']) ? intval($item['~COMPANY_ID']) : 0;
		$item['~COMPANY_ID'] = $item['COMPANY_ID'] = $companyID;

		if(!isset($item['~COMPANY_TITLE']))
		{
			$item['~COMPANY_TITLE'] = $item['COMPANY_TITLE'] = '';
		}

		$item['COMPANY_SHOW_URL'] = $companyID > 0
			? CComponentEngine::MakePathFromTemplate(
				$arParams['COMPANY_SHOW_URL_TEMPLATE'], array('company_id' => $companyID)
			) : '';

		$clientTitle = '';
		if($item['~CONTACT_ID'] > 0)
			$clientTitle = $item['~CONTACT_FORMATTED_NAME'];
		if($item['~COMPANY_ID'] > 0 && $item['COMPANY_TITLE'] !== '')
		{
			if($clientTitle !== '')
				$clientTitle .= ', ';
			$clientTitle .= $item['~COMPANY_TITLE'];
		}

		$item['~CLIENT_TITLE'] = $clientTitle;
		$item['CLIENT_TITLE'] = htmlspecialcharsbx($item['~CLIENT_TITLE']);

		$assignedByID = isset($item['~ASSIGNED_BY_ID']) ? intval($item['~ASSIGNED_BY_ID']) : 0;
		$item['~ASSIGNED_BY_ID'] = $item['ASSIGNED_BY_ID'] = $assignedByID;
		$item['ASSIGNED_BY_SHOW_URL'] = $assignedByID > 0  ?
			CComponentEngine::MakePathFromTemplate(
				$arParams['USER_PROFILE_URL_TEMPLATE'],
					array('user_id' => $assignedByID)
			) : '';

		$item['~ASSIGNED_BY_FORMATTED_NAME'] = $assignedByID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => isset($item['~ASSIGNED_BY_LOGIN']) ? $item['~ASSIGNED_BY_LOGIN'] : '',
					'NAME' => isset($item['~ASSIGNED_BY_NAME']) ? $item['~ASSIGNED_BY_NAME'] : '',
					'LAST_NAME' => isset($item['~ASSIGNED_BY_LAST_NAME']) ? $item['~ASSIGNED_BY_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['~ASSIGNED_BY_SECOND_NAME']) ? $item['~ASSIGNED_BY_SECOND_NAME'] : ''
				),
				true, false
			) : '';
		$item['ASSIGNED_BY_FORMATTED_NAME'] = htmlspecialcharsbx($item['~ASSIGNED_BY_FORMATTED_NAME']);

		if(!isset($item['~STAGE_ID']))
		{
			$item['~STAGE_ID'] = $item['STAGE_ID'] = '';
		}

		$stageID = $item['~STAGE_ID'];
		$item['~STAGE_NAME'] = $stageID !== ''
			? (isset($arResult['STAGE_LIST'][$stageID]) ? $arResult['STAGE_LIST'][$stageID] : $stageID)
			: '';

		$item['STAGE_NAME'] = htmlspecialcharsbx($item['~STAGE_NAME']);

		if(!isset($item['~COMMENTS']))
		{
			$item['~COMMENTS'] = $item['COMMENTS'] = '';
		}
	}
	public static function PrepareDealData(&$arFields)
	{
		$clientImageID = 0;
		$clientTitle = '';
		//$clientLegend = '';
		if($arFields['~CONTACT_ID'] > 0)
		{
			$clientImageID = $arFields['~CONTACT_PHOTO'];
			$clientTitle = $arFields['~CONTACT_FORMATTED_NAME'];
			//$clientLegend = $arFields['~CONTACT_POST'];
		}
		if($arFields['~COMPANY_ID'] > 0)
		{
			if($clientImageID === 0)
			{
				$clientImageID = $arFields['~COMPANY_LOGO'];
			}
			if($clientTitle !== '')
			{
				$clientTitle .= ', ';
			}
			$clientTitle .= $arFields['~COMPANY_TITLE'];
		}

		$stageID = $arFields['~STAGE_ID'];
		$stageSort = CCrmDeal::GetStageSort($stageID);
		$finalStageSort = CCrmDeal::GetFinalStageSort();

		return array(
			'ID' => $arFields['~ID'],
			'TITLE' => $arFields['~TITLE'],
			'STAGE_ID' => $arFields['~STAGE_ID'],
			'PROBABILITY' => $arFields['~PROBABILITY'],
			'OPPORTUNITY' => $arFields['~OPPORTUNITY'],
			'FORMATTED_OPPORTUNITY' => $arFields['~FORMATTED_OPPORTUNITY'],
			'CURRENCY_ID' => $arFields['~CURRENCY_ID'],
			'ASSIGNED_BY_ID' => $arFields['~ASSIGNED_BY_ID'],
			'ASSIGNED_BY_FORMATTED_NAME' => $arFields['~ASSIGNED_BY_FORMATTED_NAME'],
			'CONTACT_ID' => $arFields['~CONTACT_ID'],
			'CONTACT_FORMATTED_NAME' => $arFields['~CONTACT_FORMATTED_NAME'],
			'COMPANY_ID' => $arFields['~COMPANY_ID'],
			'COMPANY_TITLE' => $arFields['~COMPANY_TITLE'],
			'COMMENTS' => $arFields['~COMMENTS'],
			'DATE_CREATE' => $arFields['~DATE_CREATE'],
			'DATE_MODIFY' => $arFields['~DATE_MODIFY'],
			'SHOW_URL' => $arFields['SHOW_URL'],
			'CONTACT_SHOW_URL' => $arFields['CONTACT_SHOW_URL'],
			'COMPANY_SHOW_URL' => $arFields['COMPANY_SHOW_URL'],
			'ASSIGNED_BY_SHOW_URL' => $arFields['ASSIGNED_BY_SHOW_URL'],
			'CLIENT_TITLE' => $clientTitle,
			'CLIENT_IMAGE_ID' => $clientImageID,
			'IS_FINISHED' => $stageSort >= $finalStageSort,
			'IS_SUCCESSED' => $stageSort === $finalStageSort
		);
	}
	public static function PrepareContactItem(&$item, &$arParams)
	{
		$itemID = intval($item['~ID']);

		$item['SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
			$arParams['CONTACT_SHOW_URL_TEMPLATE'],
			array('contact_id' => $itemID)
		);

		if(!isset($item['~NAME']))
		{
			$item['~NAME'] = $item['NAME'] = '';
		}

		if(!isset($item['~LAST_NAME']))
		{
			$item['~LAST_NAME'] = $item['LAST_NAME'] = '';
		}

		if(!isset($item['~SECOND_NAME']))
		{
			$item['~SECOND_NAME'] = $item['SECOND_NAME'] = '';
		}

		$item['~FORMATTED_NAME'] = CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => '',
					'NAME' => $item['~NAME'],
					'LAST_NAME' => $item['~LAST_NAME'],
					'SECOND_NAME' => $item['~SECOND_NAME']
				),
				false, false
			);
		$item['FORMATTED_NAME'] = htmlspecialcharsbx($item['~FORMATTED_NAME']);

		$lastName = $item['~LAST_NAME'];
		$item['CLASSIFIER'] = $lastName !== '' ? strtoupper(substr($lastName, 0, 1)) : '';

		if(!isset($item['~POST']))
		{
			$item['~POST'] = $item['POST'] = '';
		}

		$companyID = isset($item['~COMPANY_ID']) ? intval($item['~COMPANY_ID']) : 0;
		$item['~COMPANY_ID'] = $item['COMPANY_ID'] = $companyID;

		if(!isset($item['~COMPANY_TITLE']))
		{
			$item['~COMPANY_TITLE'] = $item['COMPANY_TITLE'] = '';
		}

		$item['COMPANY_SHOW_URL'] = $companyID > 0
			? CComponentEngine::MakePathFromTemplate(
				$arParams['COMPANY_SHOW_URL_TEMPLATE'], array('company_id' => $companyID)
			) : '';

		$assignedByID = isset($item['~ASSIGNED_BY_ID']) ? intval($item['~ASSIGNED_BY_ID']) : 0;
		$item['~ASSIGNED_BY_ID'] = $item['ASSIGNED_BY_ID'] = $assignedByID;
		$item['ASSIGNED_BY_SHOW_URL'] = $assignedByID > 0  ?
			CComponentEngine::MakePathFromTemplate(
				$arParams['USER_PROFILE_URL_TEMPLATE'],
					array('user_id' => $assignedByID)
			) : '';

		$item['~ASSIGNED_BY_FORMATTED_NAME'] = $assignedByID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => isset($item['~ASSIGNED_BY_LOGIN']) ? $item['~ASSIGNED_BY_LOGIN'] : '',
					'NAME' => isset($item['~ASSIGNED_BY_NAME']) ? $item['~ASSIGNED_BY_NAME'] : '',
					'LAST_NAME' => isset($item['~ASSIGNED_BY_LAST_NAME']) ? $item['~ASSIGNED_BY_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['~ASSIGNED_BY_SECOND_NAME']) ? $item['~ASSIGNED_BY_SECOND_NAME'] : ''
				),
				true, false
			) : '';
		$item['ASSIGNED_BY_FORMATTED_NAME'] = htmlspecialcharsbx($item['~ASSIGNED_BY_FORMATTED_NAME']);

		if(!isset($item['~COMMENTS']))
		{
			$item['~COMMENTS'] = $item['COMMENTS'] = '';
		}
	}
	public static function PrepareContactData(&$fields)
	{
		$legend = '';
		$companyTitle = isset($fields['~COMPANY_TITLE']) ? $fields['~COMPANY_TITLE'] : '';
		$post = isset($fields['~POST']) ? $fields['~POST'] : '';

		if($companyTitle !== '' && $post !== '')
		{
			$legend = "{$companyTitle}, {$post}";
		}
		elseif($companyTitle !== '')
		{
			$legend = $companyTitle;
		}
		elseif($post !== '')
		{
			$legend = $post;
		}

		$listImageInfo = null;
		$viewImageInfo = null;
		$photoID = isset($fields['PHOTO']) ? intval($fields['PHOTO']) : 0;
		if($photoID > 0)
		{
			$listImageInfo = CFile::ResizeImageGet(
				$photoID, array('width' => 40, 'height' => 40), BX_RESIZE_IMAGE_EXACT);
			$viewImageInfo = CFile::ResizeImageGet(
				$photoID, array('width' => 55, 'height' => 55), BX_RESIZE_IMAGE_EXACT);
		}
		else
		{
			$listImageInfo = array('src' => SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_contact_small.png?ver=1');
			$viewImageInfo = array('src' => SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_contact_big.png?ver=1');
		}
		return array(
			'ID' => $fields['~ID'],
			'NAME' => isset($fields['~NAME']) ? $fields['~NAME'] : '',
			'LAST_NAME' => isset($fields['~LAST_NAME']) ? $fields['~LAST_NAME'] : '',
			'SECOND_NAME' => isset($fields['~SECOND_NAME']) ? $fields['~SECOND_NAME'] : '',
			'FORMATTED_NAME' => isset($fields['~FORMATTED_NAME']) ? $fields['~FORMATTED_NAME'] : '',
			'COMPANY_ID' => isset($fields['~COMPANY_ID']) ? $fields['~COMPANY_ID'] : '',
			'COMPANY_TITLE' => $companyTitle,
			'POST' => $post,
			'ASSIGNED_BY_ID' => isset($fields['~ASSIGNED_BY_ID']) ? $fields['~ASSIGNED_BY_ID'] : '',
			'ASSIGNED_BY_FORMATTED_NAME' => isset($fields['~ASSIGNED_BY_FORMATTED_NAME']) ? $fields['~ASSIGNED_BY_FORMATTED_NAME'] : '',
			'COMMENTS' => isset($fields['~COMMENTS']) ? $fields['~COMMENTS'] : '',
			'DATE_CREATE' => isset($fields['~DATE_CREATE']) ? $fields['~DATE_CREATE'] : '',
			'DATE_MODIFY' => isset($fields['~DATE_MODIFY']) ? $fields['~DATE_MODIFY'] : '',
			'LEGEND' => $legend,
			'CLASSIFIER' => isset($fields['CLASSIFIER']) ? $fields['CLASSIFIER'] : '',
			'COMPANY_SHOW_URL' => isset($fields['COMPANY_SHOW_URL']) ? $fields['COMPANY_SHOW_URL'] : '',
			'ASSIGNED_BY_SHOW_URL' => isset($fields['ASSIGNED_BY_SHOW_URL']) ? $fields['ASSIGNED_BY_SHOW_URL'] : '',
			'SHOW_URL' => isset($fields['SHOW_URL']) ? $fields['SHOW_URL'] : '',
			'LIST_IMAGE_URL' => $listImageInfo && isset($listImageInfo['src']) ? $listImageInfo['src'] : '',
			'VIEW_IMAGE_URL' => $viewImageInfo && isset($viewImageInfo['src']) ? $viewImageInfo['src'] : ''
		);
	}
	public static function PrepareCompanyItem(&$item, &$arParams, $arEnums = array())
	{
		$itemID = intval($item['~ID']);

		$item['SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
			$arParams['COMPANY_SHOW_URL_TEMPLATE'],
			array('company_id' => $itemID)
		);

		$typeList = $arEnums && isset($arEnums['COMPANY_TYPE'])
			? $arEnums['COMPANY_TYPE'] : CCrmStatus::GetStatusListEx('COMPANY_TYPE');

		$type = isset($item['~COMPANY_TYPE']) ? $item['~COMPANY_TYPE'] : '';
		if($type === '' || !isset($typeList[$type]))
		{
			$item['~COMPANY_TYPE_NAME'] = $item['COMPANY_TYPE_NAME'] = '';
		}
		else
		{
			$item['~COMPANY_TYPE_NAME'] = $typeList[$type];
			$item['COMPANY_TYPE_NAME'] = htmlspecialcharsbx($item['~COMPANY_TYPE_NAME']);
		}

		$industryList = $arEnums && isset($arEnums['INDUSTRY'])
			? $arEnums['INDUSTRY'] : CCrmStatus::GetStatusListEx('INDUSTRY');

		$industry = isset($item['~INDUSTRY']) ? $item['~INDUSTRY'] : '';
		if($industry === '' || !isset($industryList[$industry]))
		{
			$item['~INDUSTRY_NAME'] = $item['INDUSTRY_NAME'] = '';
		}
		else
		{
			$item['~INDUSTRY_NAME'] = $industryList[$industry];
			$item['INDUSTRY_NAME'] = htmlspecialcharsbx($item['~INDUSTRY_NAME']);
		}

		$employeesList = $arEnums && isset($arEnums['EMPLOYEES_LIST'])
			? $arEnums['EMPLOYEES_LIST'] : CCrmStatus::GetStatusListEx('EMPLOYEES');

		$employees = isset($item['~EMPLOYEES']) ? $item['~EMPLOYEES'] : '';
		if($employees === '' || !isset($employeesList[$employees]))
		{
			$item['~EMPLOYEES_NAME'] = $item['EMPLOYEES_NAME'] = '';
		}
		else
		{
			$item['~EMPLOYEES_NAME'] = $employeesList[$employees];
			$item['EMPLOYEES_NAME'] = htmlspecialcharsbx($item['~EMPLOYEES_NAME']);
		}

		$item['FORMATTED_REVENUE'] = CCrmCurrency::MoneyToString(
			isset($item['~REVENUE']) ? $item['~REVENUE'] : '',
			isset($item['~CURRENCY_ID']) ? $item['~CURRENCY_ID'] : CCrmCurrency::GetBaseCurrencyID()
		);

		$assignedByID = isset($item['~ASSIGNED_BY_ID']) ? intval($item['~ASSIGNED_BY_ID']) : 0;
		$item['~ASSIGNED_BY_ID'] = $item['ASSIGNED_BY_ID'] = $assignedByID;
		$item['ASSIGNED_BY_SHOW_URL'] = $assignedByID > 0  ?
			CComponentEngine::MakePathFromTemplate(
				$arParams['USER_PROFILE_URL_TEMPLATE'],
					array('user_id' => $assignedByID)
			) : '';

		$item['~ASSIGNED_BY_FORMATTED_NAME'] = $assignedByID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => isset($item['~ASSIGNED_BY_LOGIN']) ? $item['~ASSIGNED_BY_LOGIN'] : '',
					'NAME' => isset($item['~ASSIGNED_BY_NAME']) ? $item['~ASSIGNED_BY_NAME'] : '',
					'LAST_NAME' => isset($item['~ASSIGNED_BY_LAST_NAME']) ? $item['~ASSIGNED_BY_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['~ASSIGNED_BY_SECOND_NAME']) ? $item['~ASSIGNED_BY_SECOND_NAME'] : ''
				),
				true, false
			) : '';
		$item['ASSIGNED_BY_FORMATTED_NAME'] = htmlspecialcharsbx($item['~ASSIGNED_BY_FORMATTED_NAME']);
	}
	public static function PrepareCompanyData(&$fields)
	{
		$listImageInfo = null;
		$viewImageInfo = null;
		$logoID = isset($fields['LOGO']) ? intval($fields['LOGO']) : 0;
		if($logoID > 0)
		{
			$listImageInfo = CFile::ResizeImageGet(
				$logoID, array('width' => 32, 'height' => 32), BX_RESIZE_IMAGE_EXACT);
			$viewImageInfo = CFile::ResizeImageGet(
				$logoID, array('width' => 55, 'height' => 55), BX_RESIZE_IMAGE_EXACT);
		}
		else
		{
			$viewImageInfo = array('src' => SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_company_big.png?ver=1');
			$listImageInfo = array('src' => SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_company_small.png?ver=1');
		}

		return array(
			'ID' => $fields['~ID'],
			'TITLE' => isset($fields['~TITLE']) ? $fields['~TITLE'] : '',
			'COMPANY_TYPE' => isset($fields['~COMPANY_TYPE']) ? $fields['~COMPANY_TYPE'] : '',
			'COMPANY_TYPE_NAME' => isset($fields['~COMPANY_TYPE_NAME']) ? $fields['~COMPANY_TYPE_NAME'] : '',
			'INDUSTRY' => isset($fields['~INDUSTRY']) ? $fields['~INDUSTRY'] : '',
			'INDUSTRY_NAME' => isset($fields['~INDUSTRY_NAME']) ? $fields['~INDUSTRY_NAME'] : '',
			'EMPLOYEES' => isset($fields['~EMPLOYEES']) ? $fields['~EMPLOYEES'] : '',
			'EMPLOYEES_NAME' => isset($fields['~EMPLOYEES_NAME']) ? $fields['~EMPLOYEES_NAME'] : '',
			'ASSIGNED_BY_ID' => isset($fields['~ASSIGNED_BY_ID']) ? $fields['~ASSIGNED_BY_ID'] : '',
			'ASSIGNED_BY_FORMATTED_NAME' => isset($fields['~ASSIGNED_BY_FORMATTED_NAME']) ? $fields['~ASSIGNED_BY_FORMATTED_NAME'] : '',
			'ADDRESS' => isset($fields['~ADDRESS']) ? $fields['~ADDRESS'] : '',
			'ADDRESS_LEGAL' => isset($fields['~ADDRESS_LEGAL']) ? $fields['~ADDRESS_LEGAL'] : '',
			'BANKING_DETAILS' => isset($fields['~BANKING_DETAILS']) ? $fields['~BANKING_DETAILS'] : '',
			'COMMENTS' => isset($fields['~COMMENTS']) ? $fields['~COMMENTS'] : '',
			'DATE_CREATE' => isset($fields['~DATE_CREATE']) ? $fields['~DATE_CREATE'] : '',
			'DATE_MODIFY' => isset($fields['~DATE_MODIFY']) ? $fields['~DATE_MODIFY'] : '',
			'ASSIGNED_BY_SHOW_URL' => isset($fields['ASSIGNED_BY_SHOW_URL']) ? $fields['ASSIGNED_BY_SHOW_URL'] : '',
			'SHOW_URL' => isset($fields['SHOW_URL']) ? $fields['SHOW_URL'] : '',
			'LIST_IMAGE_URL' => $listImageInfo && isset($listImageInfo['src']) ? $listImageInfo['src'] : '',
			'VIEW_IMAGE_URL' => $viewImageInfo && isset($viewImageInfo['src']) ? $viewImageInfo['src'] : ''
		);
	}
	public static function PrepareLeadItem(&$item, &$arParams, $arEnums = array())
	{
		$itemID = intval($item['~ID']);

		$item['SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
			$arParams['LEAD_SHOW_URL_TEMPLATE'],
			array('lead_id' => $itemID)
		);

		$statusList = $arEnums && isset($arEnums['STATUS_LIST'])
			? $arEnums['STATUS_LIST'] : CCrmStatus::GetStatusListEx('STATUS');

		$statusID = isset($item['~STATUS_ID']) ? $item['~STATUS_ID'] : '';
		if($statusID === '' || !isset($statusList[$statusID]))
		{
			$item['~STATUS_NAME'] = $item['STATUS_NAME'] = '';
		}
		else
		{
			$item['~STATUS_NAME'] = $statusList[$statusID];
			$item['STATUS_NAME'] = htmlspecialcharsbx($item['~STATUS_NAME']);
		}

		$sourceList = $arEnums && isset($arEnums['SOURCE_LIST'])
			? $arEnums['SOURCE_LIST'] : CCrmStatus::GetStatusListEx('SOURCE');

		$sourceID = isset($item['~SOURCE_ID']) ? $item['~SOURCE_ID'] : '';
		if($sourceID === '' || !isset($sourceList[$sourceID]))
		{
			$item['~SOURCE_NAME'] = $item['SOURCE_NAME'] = '';
		}
		else
		{
			$item['~SOURCE_NAME'] = $sourceList[$sourceID];
			$item['SOURCE_NAME'] = htmlspecialcharsbx($item['~SOURCE_NAME']);
		}

		$item['FORMATTED_OPPORTUNITY'] = CCrmCurrency::MoneyToString(
			isset($item['~OPPORTUNITY']) ? $item['~OPPORTUNITY'] : '',
			isset($item['~CURRENCY_ID']) ? $item['~CURRENCY_ID'] : CCrmCurrency::GetBaseCurrencyID()
		);

		$item['~FORMATTED_NAME'] = CUser::FormatName(
			$arParams['NAME_TEMPLATE'],
			array(
				'LOGIN' => '',
				'NAME' => isset($item['~NAME']) ? $item['~NAME'] : '',
				'LAST_NAME' => isset($item['~LAST_NAME']) ? $item['~LAST_NAME'] : '',
				'SECOND_NAME' => isset($item['~SECOND_NAME']) ? $item['~SECOND_NAME'] : ''
			),
			false, false
		);
		$item['FORMATTED_NAME'] = htmlspecialcharsbx($item['~FORMATTED_NAME']);

		$assignedByID = isset($item['~ASSIGNED_BY_ID']) ? intval($item['~ASSIGNED_BY_ID']) : 0;
		$item['~ASSIGNED_BY_ID'] = $item['ASSIGNED_BY_ID'] = $assignedByID;
		$item['ASSIGNED_BY_SHOW_URL'] = $assignedByID > 0  ?
			CComponentEngine::MakePathFromTemplate(
				$arParams['USER_PROFILE_URL_TEMPLATE'],
					array('user_id' => $assignedByID)
			) : '';

		$item['~ASSIGNED_BY_FORMATTED_NAME'] = $assignedByID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => isset($item['~ASSIGNED_BY_LOGIN']) ? $item['~ASSIGNED_BY_LOGIN'] : '',
					'NAME' => isset($item['~ASSIGNED_BY_NAME']) ? $item['~ASSIGNED_BY_NAME'] : '',
					'LAST_NAME' => isset($item['~ASSIGNED_BY_LAST_NAME']) ? $item['~ASSIGNED_BY_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['~ASSIGNED_BY_SECOND_NAME']) ? $item['~ASSIGNED_BY_SECOND_NAME'] : ''
				),
				true, false
			) : '';
		$item['ASSIGNED_BY_FORMATTED_NAME'] = htmlspecialcharsbx($item['~ASSIGNED_BY_FORMATTED_NAME']);
	}
	public static function PrepareLeadData(&$fields)
	{
		return array(
			'ID' => $fields['~ID'],
			'TITLE' => isset($fields['~TITLE']) ? $fields['~TITLE'] : '',
			'STATUS_ID' => isset($fields['~STATUS_ID']) ? $fields['~STATUS_ID'] : '',
			'STATUS_NAME' => isset($fields['~STATUS_NAME']) ? $fields['~STATUS_NAME'] : '',
			'SOURCE_ID' => isset($fields['~SOURCE_ID']) ? $fields['~SOURCE_ID'] : '',
			'SOURCE_NAME' => isset($fields['~SOURCE_NAME']) ? $fields['~SOURCE_NAME'] : '',
			'FORMATTED_NAME' => isset($fields['~FORMATTED_NAME']) ? $fields['~FORMATTED_NAME'] : '',
			'COMPANY_TITLE' => isset($fields['~COMPANY_TITLE']) ? $fields['~COMPANY_TITLE'] : '',
			'POST' => isset($fields['~POST']) ? $fields['~POST'] : '',
			'OPPORTUNITY' => isset($fields['~OPPORTUNITY']) ? $fields['~OPPORTUNITY'] : '',
			'FORMATTED_OPPORTUNITY' => isset($fields['FORMATTED_OPPORTUNITY']) ? $fields['FORMATTED_OPPORTUNITY'] : '',
			'ASSIGNED_BY_ID' => isset($fields['~ASSIGNED_BY_ID']) ? $fields['~ASSIGNED_BY_ID'] : '',
			'ASSIGNED_BY_FORMATTED_NAME' => isset($fields['~ASSIGNED_BY_FORMATTED_NAME']) ? $fields['~ASSIGNED_BY_FORMATTED_NAME'] : '',
			'COMMENTS' => isset($fields['~COMMENTS']) ? $fields['~COMMENTS'] : '',
			'DATE_CREATE' => isset($fields['~DATE_CREATE']) ? $fields['~DATE_CREATE'] : '',
			'DATE_MODIFY' => isset($fields['~DATE_MODIFY']) ? $fields['~DATE_MODIFY'] : '',
			'ASSIGNED_BY_SHOW_URL' => isset($fields['ASSIGNED_BY_SHOW_URL']) ? $fields['ASSIGNED_BY_SHOW_URL'] : '',
			'SHOW_URL' => isset($fields['SHOW_URL']) ? $fields['SHOW_URL'] : '',
			//'LIST_IMAGE_URL' => SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_lead_small.png?ver=1',
			'LIST_IMAGE_URL' => '',
			//'VIEW_IMAGE_URL' => SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_lead_big.png?ver=1'
			'VIEW_IMAGE_URL' => ''
		);
	}
	public static function PrepareActivityItem(&$item, &$arParams)
	{
		$itemID = intval($item['ID']);

		$typeID = isset($item['TYPE_ID']) ? intval($item['TYPE_ID']) : CCrmActivityType::Undefined;
		$item['TYPE_ID'] = $typeID;

		$direction = isset($item['DIRECTION']) ? intval($item['DIRECTION']) : CCrmActivityDirection::Undefined;
		$item['DIRECTION'] = $direction;

		$priority = isset($item['PRIORITY']) ? intval($item['PRIORITY']) : CCrmActivityPriority::None;
		$item['PRIORITY'] = $priority;
		$item['IS_IMPORTANT'] = $priority === CCrmActivityPriority::High;

		$completed = isset($item['COMPLETED']) ? $item['COMPLETED'] === 'Y' : false;
		$item['COMPLETED'] = $completed ? 'Y' : 'N';

		if($typeID === CCrmActivityType::Task)
		{
			$taskID = isset($item['ASSOCIATED_ENTITY_ID']) ? intval($item['ASSOCIATED_ENTITY_ID']) : 0;
			$item['SHOW_URL'] = $taskID > 0
				? CComponentEngine::MakePathFromTemplate(
					$arParams['TASK_SHOW_URL_TEMPLATE'],
					array(
						'user_id' => isset($arParams['USER_ID']) ? $arParams['USER_ID'] : CCrmSecurityHelper::GetCurrentUserID(),
						'task_id' => $taskID
					)
				) : '';
			$item['DEAD_LINE'] = isset($item['END_TIME']) ? $item['END_TIME'] : '';
		}
		else
		{
			$item['SHOW_URL'] = CComponentEngine::MakePathFromTemplate(
				$arParams['ACTIVITY_SHOW_URL_TEMPLATE'],
				array('activity_id' => $itemID)
			);
			$item['DEAD_LINE'] = isset($item['START_TIME']) ? $item['START_TIME'] : '';
		}

		//OWNER_TITLE
		$ownerTitle = '';
		$ownerID = isset($item['OWNER_ID']) ? intval($item['OWNER_ID']) : 0;
		$item['OWNER_ID'] = $ownerID;

		$ownerTypeID = isset($item['OWNER_TYPE_ID']) ? intval($item['OWNER_TYPE_ID']) : 0;
		$item['OWNER_TYPE_ID'] = $ownerTypeID;

		if($ownerID > 0 && $ownerTypeID > 0)
		{
			$ownerTitle = CCrmOwnerType::GetCaption($ownerTypeID, $ownerID);
		}

		$item['OWNER_TITLE'] = $ownerTitle;

		//OWNER_SHOW_URL
		$ownerShowUrl = '';
		if($ownerID > 0)
		{
			if($ownerTypeID === CCrmOwnerType::Lead)
			{
				$ownerShowUrl = CComponentEngine::MakePathFromTemplate(
					$arParams['LEAD_SHOW_URL_TEMPLATE'],
					array('lead_id' => $ownerID)
				);
			}
			elseif($ownerTypeID === CCrmOwnerType::Contact)
			{
				$ownerShowUrl = CComponentEngine::MakePathFromTemplate(
					$arParams['CONTACT_SHOW_URL_TEMPLATE'],
					array('contact_id' => $ownerID)
				);
			}
			elseif($ownerTypeID === CCrmOwnerType::Company)
			{
				$ownerShowUrl = CComponentEngine::MakePathFromTemplate(
					$arParams['COMPANY_SHOW_URL_TEMPLATE'],
					array('company_id' => $ownerID)
				);
			}
			elseif($ownerTypeID === CCrmOwnerType::Deal)
			{
				$ownerShowUrl = CComponentEngine::MakePathFromTemplate(
					$arParams['DEAL_SHOW_URL_TEMPLATE'],
					array('deal_id' => $ownerID)
				);
			}
		}
		$item['OWNER_SHOW_URL'] = $ownerShowUrl;

		//IS_EXPIRED
		if($item['COMPLETED'] === 'Y')
		{
			$item['IS_EXPIRED'] = false;
		}
		else
		{
			$time = isset($item['START_TIME']) ? MakeTimeStamp($item['START_TIME']) : 0;
			$item['IS_EXPIRED'] = $time !== 0 && $time <= (time() + CTimeZone::GetOffset());
		}

		$responsibleID = isset($item['RESPONSIBLE_ID']) ? intval($item['RESPONSIBLE_ID']) : 0;
		$item['RESPONSIBLE_ID'] = $responsibleID;
		$item['RESPONSIBLE_SHOW_URL'] = $responsibleID > 0  ?
			CComponentEngine::MakePathFromTemplate(
				$arParams['USER_PROFILE_URL_TEMPLATE'],
					array('user_id' => $responsibleID)
			) : '';

		$item['RESPONSIBLE_FORMATTED_NAME'] = $responsibleID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => isset($item['RESPONSIBLE_LOGIN']) ? $item['RESPONSIBLE_LOGIN'] : '',
					'NAME' => isset($item['RESPONSIBLE_NAME']) ? $item['RESPONSIBLE_NAME'] : '',
					'LAST_NAME' => isset($item['RESPONSIBLE_LAST_NAME']) ? $item['RESPONSIBLE_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['RESPONSIBLE_SECOND_NAME']) ? $item['RESPONSIBLE_SECOND_NAME'] : ''
				),
				true, false
			) : '';

		//COMMUNICATIONS
		$item['COMMUNICATIONS'] = CCrmActivity::GetCommunications($itemID);

		$storageTypeID = isset($item['STORAGE_TYPE_ID']) ? intval($item['STORAGE_TYPE_ID']) : CCrmActivityStorageType::Undefined;
		if($storageTypeID === CCrmActivityStorageType::Undefined || !CCrmActivityStorageType::IsDefined($storageTypeID))
		{
			$storageTypeID = CCrmActivity::GetDefaultStorageTypeID();
		}
		$item['STORAGE_TYPE_ID'] = $storageTypeID;
	}
	public static function PrepareActivityData(&$fields)
	{
		$typeID = isset($fields['TYPE_ID']) ? intval($fields['TYPE_ID']) : CCrmActivityType::Undefined;
		$direction = isset($fields['DIRECTION']) ? intval($fields['DIRECTION']) : CCrmActivityDirection::Undefined;
		$isCompleted = $fields['COMPLETED'] === 'Y';

		$imageFileName = '';
		if($typeID === CCrmActivityType::Call)
		{
			$imageFileName = $direction === CCrmActivityDirection::Incoming ? 'call_in' : 'call_out';
		}
		elseif($typeID === CCrmActivityType::Email)
		{
			$imageFileName = $direction === CCrmActivityDirection::Incoming ? 'email_in' : 'email_out';
		}
		elseif($typeID === CCrmActivityType::Meeting)
		{
			$imageFileName = 'cont';
		}
		elseif($typeID === CCrmActivityType::Task)
		{
			$imageFileName = 'check';
		}

		if($imageFileName !== '' && $isCompleted)
		{
			$imageFileName .= '_disabled';
		}

		$imageUrl = $imageFileName !== ''
			? SITE_DIR.'bitrix/templates/mobile_app/images/crm/'.$imageFileName.'.png?ver=1'
			: '';

		return array(
			'ID' => $fields['ID'],
			'SUBJECT' => isset($fields['SUBJECT']) ? $fields['SUBJECT'] : '',
			'START_TIME' => isset($fields['START_TIME']) ? CCrmComponentHelper::RemoveSeconds(ConvertTimeStamp(MakeTimeStamp($fields['START_TIME']), 'FULL', SITE_ID)) : '',
			'END_TIME' => isset($fields['END_TIME']) ? CCrmComponentHelper::RemoveSeconds(ConvertTimeStamp(MakeTimeStamp($fields['END_TIME']), 'FULL', SITE_ID)) : '',
			'DEAD_LINE' => isset($fields['DEAD_LINE']) ? CCrmComponentHelper::RemoveSeconds(ConvertTimeStamp(MakeTimeStamp($fields['DEAD_LINE']), 'FULL', SITE_ID)) : '',
			'COMPLETED' => isset($fields['COMPLETED']) ? $fields['COMPLETED'] === 'Y' : false,
			'PRIORITY' => isset($fields['PRIORITY']) ? intval($fields['PRIORITY']) : CCrmActivityPriority::None,
			'IS_IMPORTANT' => isset($fields['IS_IMPORTANT']) ? $fields['IS_IMPORTANT'] : false,
			'IS_EXPIRED' => isset($fields['IS_EXPIRED']) ? $fields['IS_EXPIRED'] : false,
			'OWNER_TITLE' => isset($fields['OWNER_TITLE']) ? $fields['OWNER_TITLE'] : '',
			'SHOW_URL' => isset($fields['SHOW_URL']) ? $fields['SHOW_URL'] : '',
			'LIST_IMAGE_URL' => $imageUrl,
			'VIEW_IMAGE_URL' => $imageUrl
		);
	}
	public static function PrepareEventItem(&$item, &$arParams)
	{
		if(isset($item['EVENT_TEXT_1']))
		{
			$item['EVENT_TEXT_1'] = strip_tags($item['EVENT_TEXT_1'], '<br>');
		}

		if(isset($item['EVENT_TEXT_2']))
		{
			$item['EVENT_TEXT_2'] = strip_tags($item['EVENT_TEXT_2'], '<br>');
		}

		$authorID = isset($item['CREATED_BY_ID']) ? intval($item['CREATED_BY_ID']) : 0;
		$item['CREATED_BY_ID'] = $authorID;
		$item['CREATED_BY_SHOW_URL'] = $authorID > 0  ?
			CComponentEngine::MakePathFromTemplate(
				$arParams['USER_PROFILE_URL_TEMPLATE'],
					array('user_id' => $authorID)
			) : '';

		$item['CREATED_BY_FORMATTED_NAME'] = $authorID > 0
			? CUser::FormatName(
				$arParams['NAME_TEMPLATE'],
				array(
					'LOGIN' => isset($item['CREATED_BY_LOGIN']) ? $item['CREATED_BY_LOGIN'] : '',
					'NAME' => isset($item['CREATED_BY_NAME']) ? $item['CREATED_BY_NAME'] : '',
					'LAST_NAME' => isset($item['CREATED_BY_LAST_NAME']) ? $item['CREATED_BY_LAST_NAME'] : '',
					'SECOND_NAME' => isset($item['CREATED_BY_SECOND_NAME']) ? $item['CREATED_BY_SECOND_NAME'] : ''
				),
				true, false
			) : '';
	}
	public static function PrepareEventData(&$fields)
	{
		return array(
			'ID' => $fields['ID'],
			'EVENT_NAME' => isset($fields['EVENT_NAME']) ? $fields['EVENT_NAME'] : '',
			'EVENT_TEXT_1' => isset($fields['EVENT_TEXT_1']) ? $fields['EVENT_TEXT_1'] : '',
			'EVENT_TEXT_2' => isset($fields['EVENT_TEXT_2']) ? $fields['EVENT_TEXT_2'] : '',
			'CREATED_BY_ID' => isset($fields['CREATED_BY_ID']) ? $fields['CREATED_BY_ID'] : '',
			'CREATED_BY_FORMATTED_NAME' => isset($fields['CREATED_BY_FORMATTED_NAME']) ? $fields['CREATED_BY_FORMATTED_NAME'] : '',
			'DATE_CREATE' => isset($fields['DATE_CREATE']) ? ConvertTimeStamp(MakeTimeStamp($fields['DATE_CREATE']), 'SHORT', SITE_ID) : ''
		);
	}
	public static function RenderProgressBar($params)
	{
		$entityTypeID = isset($params['ENTITY_TYPE_ID']) ? intval($params['ENTITY_TYPE_ID']) : 0;
		//$entityTypeName = CCrmOwnerType::ResolveName($entityTypeID);

		$infos = isset($arParams['INFOS']) ? $arParams['INFOS'] : null;
		if(!is_array($infos) || empty($infos))
		{
			if($entityTypeID === CCrmOwnerType::Lead)
			{
				if(!self::$LEAD_STATUSES)
				{
					self::$LEAD_STATUSES = CCrmStatus::GetStatus('STATUS');
				}
				$infos = self::$LEAD_STATUSES;
			}
			elseif($entityTypeID === CCrmOwnerType::Deal)
			{
				if(!self::$DEAL_STAGES)
				{
					self::$DEAL_STAGES = CCrmStatus::GetStatus('DEAL_STAGE');
				}
				$infos = self::$DEAL_STAGES;
			}
		}

		if(!is_array($infos) || empty($infos))
		{
			return;
		}

		$currentInfo = null;
		$currentID = isset($params['CURRENT_ID']) ? $params['CURRENT_ID'] : '';
		if($currentID !== '' && isset($infos[$currentID]))
		{
			$currentInfo = $infos[$currentID];
		}
		$currentSort = is_array($currentInfo) && isset($currentInfo['SORT']) ? intval($currentInfo['SORT']) : -1;

		$finalID = isset($params['FINAL_ID']) ? $params['FINAL_ID'] : '';
		if($finalID === '')
		{
			if($entityTypeID === CCrmOwnerType::Lead)
			{
				$finalID = 'CONVERTED';
			}
			elseif($entityTypeID === CCrmOwnerType::Deal)
			{
				$finalID = 'WON';
			}
		}

		$finalInfo = null;
		if($finalID !== '' && isset($infos[$finalID]))
		{
			$finalInfo = $infos[$finalID];
		}
		$finalSort = is_array($finalInfo) && isset($finalInfo['SORT']) ? intval($finalInfo['SORT']) : -1;

		$layout = isset($params['LAYOUT']) ? strtolower($params['LAYOUT']) : 'small';

		$wrapperClass = "crm-list-stage-bar-{$layout}";
		if($currentSort === $finalSort)
		{
			$wrapperClass .= ' crm-list-stage-end-good';
		}
		elseif($currentSort > $finalSort)
		{
			$wrapperClass .= ' crm-list-stage-end-bad';
		}

		//$prefix = isset($params['PREFIX']) ? $params['PREFIX'] : '';
		//$entityID = isset($params['ENTITY_ID']) ? intval($params['ENTITY_ID']) : 0;

		//$controlID = $entityTypeName !== '' && $entityID > 0
		//	? "{$prefix}{$entityTypeName}_{$entityID}" : uniqid($prefix);

		$tableClass = "crm-list-stage-bar-table-{$layout}";
		echo '<div class="', $wrapperClass,'">',
			'<table class="', $tableClass, '"><tbody><tr>';

		foreach($infos as &$info)
		{
			//$ID = isset($info['STATUS_ID']) ? $info['STATUS_ID'] : '';
			$sort = isset($info['SORT']) ? intval($info['SORT']) : 0;
			if($sort > $finalSort)
			{
				break;
			}

			echo '<td class="crm-list-stage-bar-part',
				($sort <= $currentSort ? ' crm-list-stage-passed' : ''), '">',
				'<div class="crm-list-stage-bar-block"><div class="crm-list-stage-bar-btn"></div></div>',
			'</td>';
		}
		unset($info);

		echo '</tr></tbody></table></div>';
	}
	public static function PrepareCalltoUrl($value)
	{
		return 'tel:'.$value;
	}
	public static function PrepareMailtoUrl($value)
	{
		return 'mailto:'.$value;
	}
	public static function PrepareCalltoParams($params)
	{
		$result = array(
			'URL' => '',
			'SCRIPT' => ''
		);

		$multiFields = isset($params['FM']) ? $params['FM'] : array();
		$c = count($multiFields['PHONE']);
		if($c === 0)
		{
			return $result;
		}


		$commListUrlTemplate = isset($params['COMMUNICATION_LIST_URL_TEMPLATE']) ? $params['COMMUNICATION_LIST_URL_TEMPLATE'] : '';
		$entityTypeID = isset($params['ENTITY_TYPE_ID']) ? intval($params['ENTITY_TYPE_ID']) : 0;
		$entityID = isset($params['ENTITY_ID']) ? intval($params['ENTITY_ID']) : 0;

		if($c === 1)
		{
			$result['URL'] = self::PrepareCalltoUrl($multiFields['PHONE'][0]['VALUE']);
		}
		elseif($commListUrlTemplate !== '' && $entityTypeID > 0 && $entityID > 0)
		{
			$url = CComponentEngine::MakePathFromTemplate(
				$commListUrlTemplate,
				array(
					'entity_type_id' => $entityTypeID,
					'entity_id' => $entityID,
					'type_id' => 'PHONE'
				)
			);

			$result['SCRIPT'] = 'BX.CrmMobileContext.redirect({ url: \''.CUtil::JSEscape($url).'\', pageid:\'crm_phone_list_'.$entityTypeID.'_'.$entityID.'\' }); return false;';
		}

		return $result;
	}
	public static function PrepareMailtoParams($params)
	{
		$result = array(
			'URL' => '',
			'SCRIPT' => ''
		);

		$multiFields = isset($params['FM']) ? $params['FM'] : array();
		$c = count($multiFields['EMAIL']);
		if($c === 0)
		{
			return $result;
		}


		$commListUrlTemplate = isset($params['COMMUNICATION_LIST_URL_TEMPLATE']) ? $params['COMMUNICATION_LIST_URL_TEMPLATE'] : '';
		$entityTypeID = isset($params['ENTITY_TYPE_ID']) ? intval($params['ENTITY_TYPE_ID']) : 0;
		$entityID = isset($params['ENTITY_ID']) ? intval($params['ENTITY_ID']) : 0;

		if($c === 1)
		{
			$result['URL'] = self::PrepareMailtoUrl($multiFields['EMAIL'][0]['VALUE']);
		}
		elseif($commListUrlTemplate !== '' && $entityTypeID > 0 && $entityID > 0)
		{
			$url = CComponentEngine::MakePathFromTemplate(
				$commListUrlTemplate,
				array(
					'entity_type_id' => $entityTypeID,
					'entity_id' => $entityID,
					'type_id' => 'EMAIL'
				)
			);

			$result['SCRIPT'] = 'BX.CrmMobileContext.redirect({ url: \''.CUtil::JSEscape($url).'\' }); return false;';
		}

		return $result;
	}
	public static function PrepareCut($src, &$text, &$cut)
	{
		$text = '';
		$cut = '';
		if($src === '' || preg_match('/^\s*(\s*<br[^>]*>\s*)+\s*$/i', $src) === 1)
		{
			return false;
		}

		$text = $src;
		if(strlen($text) > 128)
		{
			$cut = substr($text, 128);
			$text = substr($text, 0, 128);
		}

		return true;
	}
	public static function GetContactViewImageStub()
	{
		return SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_contact_big.png?ver=1';
	}
	public static function GetCompanyViewImageStub()
	{
		return SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_company_big.png?ver=1';
	}
	public static function GetLeadViewImageStub()
	{
		return SITE_DIR.'bitrix/templates/mobile_app/images/crm/no_lead_big.png?ver=1';
	}
}
