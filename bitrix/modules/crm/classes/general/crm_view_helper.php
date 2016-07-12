<?php
IncludeModuleLangFile(__FILE__);

class CCrmViewHelper
{
	private static $MULTI_VIEW_TEMPLATES = array(
		'LINK' => '<span class="crm-fld-block">
			<span class="crm-fld crm-fld-#FIELD_TYPE#">
				<span class="crm-fld-container">
					<a class="crm-fld-text" href="#VIEW_VALUE#" onclick="#ON_CLICK#">#VALUE#</a>
				</span>
				<span class="crm-fld-value">
					<input class="crm-fld-element-input" type="text" value="#VALUE#" />
					<input class="crm-fld-element-name" type="hidden" value="#NAME#"/>
				</span>
			</span>
			<span class="crm-fld-icon crm-fld-icon-#FIELD_TYPE#"></span>
		</span>',
		'INPUT' => '<span class="crm-fld-block">
			<span class="crm-fld crm-fld-input">
				<span class="crm-fld-text">#VALUE#</span>
				<span class="crm-fld-value">
					<input class="crm-fld-element-input" type="text" value="#VALUE#" />
					<input class="crm-fld-element-name" type="hidden" value="#NAME#"/>
				</span>
			</span>
			<span class="crm-fld-icon crm-fld-icon-input"></span>
		</span>'
	);
	private static $DEAL_STAGES = null;
	private static $LEAD_STATUSES = null;

	public static function PrepareClientBaloonHtml($arParams)
	{
		return self::PrepareEntityBaloonHtml($arParams);
	}
	public static function PrepareEntityBaloonHtml($arParams)
	{
		if(!is_array($arParams))
		{
			return '';
		}

		$entityTypeID = isset($arParams['ENTITY_TYPE_ID']) ? intval($arParams['ENTITY_TYPE_ID']) : 0;
		$entityID = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
		$prefix = isset($arParams['PREFIX']) ? $arParams['PREFIX'] : '';
		$className = isset($arParams['CLASS_NAME']) ? $arParams['CLASS_NAME'] : '';

		if($entityTypeID <= 0 || $entityID <= 0)
		{
			return '';
		}

		$showPath = isset($arParams['SHOW_URL']) ? $arParams['SHOW_URL'] : '';

		if($entityTypeID === CCrmOwnerType::Company)
		{
			if($showPath === '')
			{
				$showPath = CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_company_show'),
					array('company_id' => $entityID)
				);
			}

			$title = isset($arParams['TITLE']) ? $arParams['TITLE'] : '';
			if($title === '')
			{
				$title = $entityID;
			}

			$baloonID = $prefix !== '' ? "BALLOON_{$prefix}_CO_{$entityID}" : "BALLOON_CO_{$entityID}";
			return '<a href="'.htmlspecialcharsbx($showPath).'" id="'.$baloonID.'"'.($className !== '' ? ' class="'.htmlspecialcharsbx($className).'"' : '').'>'.htmlspecialcharsbx($title).'</a>'.
				'<script type="text/javascript">BX.tooltip("COMPANY_'.$entityID.'", "'.$baloonID.'", "/bitrix/components/bitrix/crm.company.show/card.ajax.php", "crm_balloon_company", true);</script>';
		}
		elseif($entityTypeID === CCrmOwnerType::Contact)
		{
			if($showPath === '')
			{
				$showPath = CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_contact_show'),
					array('contact_id' => $entityID)
				);
			}

			$title = isset($arParams['TITLE']) ? $arParams['TITLE'] : '';
			if($title === '')
			{
				$title = CUser::FormatName(CSite::GetNameFormat(false),
					array(
						'LOGIN' => '',
						'NAME' => isset($arParams['NAME']) ? $arParams['NAME'] : '',
						'LAST_NAME' => isset($arParams['LAST_NAME']) ? $arParams['LAST_NAME'] : '',
						'SECOND_NAME' => isset($arParams['SECOND_NAME']) ? $arParams['SECOND_NAME'] : ''
					),
					false, false
				);
			}

			$baloonID = $prefix !== '' ? "BALLOON_{$prefix}_C_{$entityID}" : "BALLOON_C_{$entityID}";
			return '<a href="'.htmlspecialcharsbx($showPath).'" id="'.$baloonID.'"'.($className !== '' ? ' class="'.htmlspecialcharsbx($className).'"' : '').'>'.htmlspecialcharsbx($title).'</a>'.
				'<script type="text/javascript">BX.tooltip("CONTACT_'.$entityID.'", "'.$baloonID.'", "/bitrix/components/bitrix/crm.contact.show/card.ajax.php", "crm_balloon_contact", true);</script>';
		}
		elseif($entityTypeID === CCrmOwnerType::Lead)
		{
			if($showPath === '')
			{
				$showPath = CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_lead_show'),
					array('lead_id' => $entityID)
				);
			}

			$title = isset($arParams['TITLE']) ? $arParams['TITLE'] : '';
			if($title === '')
			{
				$title = CUser::FormatName(CSite::GetNameFormat(false),
					array(
						'LOGIN' => '',
						'NAME' => isset($arParams['NAME']) ? $arParams['NAME'] : '',
						'LAST_NAME' => isset($arParams['LAST_NAME']) ? $arParams['LAST_NAME'] : '',
						'SECOND_NAME' => isset($arParams['SECOND_NAME']) ? $arParams['SECOND_NAME'] : ''
					),
					false, false
				);
			}

			$baloonID = $prefix !== '' ? "BALLOON_{$prefix}_L_{$entityID}" : "BALLOON_L_{$entityID}";
			return '<a href="'.htmlspecialcharsbx($showPath).'" id="'.$baloonID.'"'.($className !== '' ? ' class="'.htmlspecialcharsbx($className).'"' : '').'>'.htmlspecialcharsbx($title).'</a>'.
				'<script type="text/javascript">BX.tooltip("LEAD_'.$entityID.'", "'.$baloonID.'", "/bitrix/components/bitrix/crm.lead.show/card.ajax.php", "crm_balloon_lead", true);</script>';
		}
		elseif($entityTypeID === CCrmOwnerType::Deal)
		{
			if($showPath === '')
			{
				$showPath = CComponentEngine::MakePathFromTemplate(
					COption::GetOptionString('crm', 'path_to_deal_show'),
					array('deal_id' => $entityID)
				);
			}

			$title = isset($arParams['TITLE']) ? $arParams['TITLE'] : '';

			$baloonID = $prefix !== '' ? "BALLOON_{$prefix}_D_{$entityID}" : "BALLOON_D_{$entityID}";
			return '<a href="'.htmlspecialcharsbx($showPath).'" id="'.$baloonID.'"'.($className !== '' ? ' class="'.htmlspecialcharsbx($className).'"' : '').'>'.htmlspecialcharsbx($title).'</a>'.
				'<script type="text/javascript">BX.tooltip("DEAL_'.$entityID.'", "'.$baloonID.'", "/bitrix/components/bitrix/crm.deal.show/card.ajax.php", "crm_balloon_deal", true);</script>';
		}
		return '';
	}
	public static function GetFormattedUserName($userID, $format = '')
	{
		$userID = intval($userID);
		if($userID <= 0)
		{
			return '';
		}

		$format = strval($format);
		if($format === '')
		{
			$format = CSite::GetNameFormat(false);
		}

		$dbUser = CUser::GetList(
			($by = 'id'),
			($order = 'asc'),
			array('ID'=> $userID),
			array(
				'FIELDS'=> array(
					'ID',
					'LOGIN',
					'EMAIL',
					'NAME',
					'LAST_NAME',
					'SECOND_NAME'
				)
			)
		);

		$user = $dbUser ? $dbUser->Fetch() : null;
		return is_array($user) ? CUser::FormatName($format, $user, true, false) : '';
	}
	public static function RenderInfo($url, $titleHtml, $descriptionHtml, $target = '_blank', $onclick = '')
	{
		$url = strval($url);
		$titleHtml = strval($titleHtml);
		$descriptionHtml = strval($descriptionHtml);
		$target = strval($target);
		$onclick = strval($onclick);

		$result = '';
		if($url !== '' || $titleHtml !== '')
		{
			$result .= '<div class="crm-info-title-wrapper">';
			if($url !== '')
			{
				$result .= '<a target="'.htmlspecialcharsbx($target).'" href="'.$url.'"';
				if($onclick !== '')
				{
					$result .= ' onclick="'.CUtil::JSEscape($onclick).'"';
				}

				$result .= '>'.($titleHtml !== '' ? $titleHtml : $url).'</a>';
			}
			elseif($titleHtml !== '')
			{
				$result .= $titleHtml;
			}
			$result .= '</div>';
		}
		if($descriptionHtml !== '')
		{
			$result .= '<div class="crm-info-description-wrapper">'.$descriptionHtml.'</div>';
		}

		return '<div class="crm-info-wrapper">'.$result.'</div>';
	}
	public static function PrepareClientInfo($arParams)
	{
		$result = '<div class="crm-info-title-wrapper">';
		$result .= self::PrepareClientBaloonHtml($arParams);
		$result .= '</div>';

		$description = isset($arParams['DESCRIPTION']) ? $arParams['DESCRIPTION'] : '';
		if($description !== '')
		{
			$result .= '<div class="crm-info-description-wrapper">'.htmlspecialcharsbx($description).'</div>';
		}

		return '<div class="crm-info-wrapper">'.$result.'</div>';
	}
	public static function PrepareClientInfoV2($arParams)
	{
		$showUrl = isset($arParams['SHOW_URL']) ? $arParams['SHOW_URL'] : '';
		if($showUrl === '')
		{
			$entityTypeID = isset($arParams['ENTITY_TYPE_ID']) ? intval($arParams['ENTITY_TYPE_ID']) : 0;
			$entityID = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;
			if($entityTypeID > 0 && $entityID > 0)
			{
				$showUrl = CCrmOwnerType::GetShowUrl($entityTypeID, $entityID);
			}
		}

		$photoID = isset($arParams['PHOTO_ID']) ? intval($arParams['PHOTO_ID']) : 0;
		$photoUrl = $photoID > 0
			? CFile::ResizeImageGet($photoID, array('width' => 30, 'height' => 30), BX_RESIZE_IMAGE_EXACT)
			: '';

		$name = isset($arParams['NAME']) ? $arParams['NAME'] : '';
		$description = isset($arParams['DESCRIPTION']) ? $arParams['DESCRIPTION'] : '';
		$html = isset($arParams['ADDITIONAL_HTML']) ? $arParams['ADDITIONAL_HTML'] : '';

		if($showUrl !== '')
		{
			return '<a class="crm-item-client-block" href="'
				.htmlspecialcharsbx($showUrl).'"><div class="crm-item-client-img">'
				.(isset($photoUrl['src']) ? '<img alt="" src="'.htmlspecialcharsbx($photoUrl['src']).'"/>' : '')
				.'</div>'
				.'<span class="crm-item-client-alignment"></span>'
				.'<span class="crm-item-client-alignment-block">'
				.'<div class="crm-item-client-name">'
				.htmlspecialcharsbx($name).'</div><div class="crm-item-client-description">'
				.htmlspecialcharsbx($description).$html.'</div></span></a>';
		}

		return '<span class="crm-item-client-block"><div class="crm-item-client-img">'
			.(isset($photoUrl['src']) ? '<img alt="" src="'.htmlspecialcharsbx($photoUrl['src']).'"/>' : '')
			.'</div>'
			.'<span class="crm-item-client-alignment"></span>'
			.'<span class="crm-item-client-alignment-block">'
			.'<div class="crm-item-client-name">'
			.htmlspecialcharsbx($name).'</div><div class="crm-item-client-description">'
			.htmlspecialcharsbx($description).$html.'</div></span></span>';
	}
	public static function RenderClientSummary($url, $titleHtml, $descriptionHtml, $photoHtml = '', $target = '_self')
	{
		$url = strval($url);
		$titleHtml = strval($titleHtml);
		$descriptionHtml = strval($descriptionHtml);
		$photoHtml = strval($photoHtml);

		$result = '<div class="crm-client-photo-wrapper">'.($photoHtml !== ''
			? $photoHtml
			: '<img src="/bitrix/js/tasks/css/images/avatar.png" alt=""/>').'</div>';

		$result .= '<div class="crm-client-info-wrapper">';
		if($url !== '' || $titleHtml !== '')
		{
			$result .= '<div class="crm-client-title-wrapper">';
			if($url !== '')
			{
				$result .= '<a target="'.htmlspecialcharsbx($target).'" href="'.$url.'">'
					.($titleHtml !== '' ? $titleHtml : htmlspecialcharsbx($url)).'</a>';
			}
			elseif($titleHtml !== '')
			{
				$result .= $titleHtml;
			}
			$result .= '</div>';
		}
		if($descriptionHtml !== '')
		{
			$result .= '<div class="crm-client-description-wrapper">'.$descriptionHtml.'</div>';
		}
		$result .= '</div>';

		return '<div class="crm-client-summary-wrapper">'.$result.'<div style="clear:both;"></div></div>';
	}
	public static function RenderNearestActivity($arParams)
	{
		$gridManagerID = isset($arParams['GRID_MANAGER_ID']) ? $arParams['GRID_MANAGER_ID'] : '';
		$mgrID = strtolower($gridManagerID);

		$entityTypeName = isset($arParams['ENTITY_TYPE_NAME']) ? strtolower($arParams['ENTITY_TYPE_NAME']) : '';
		$entityID = isset($arParams['ENTITY_ID']) ? $arParams['ENTITY_ID'] : '';

		$allowEdit = isset($arParams['ALLOW_EDIT']) ? $arParams['ALLOW_EDIT'] : false;
		$menuItems = isset($arParams['MENU_ITEMS']) ? $arParams['MENU_ITEMS'] : array();
		$menuID = CUtil::JSEscape("bx_{$mgrID}_{$entityTypeName}_{$entityID}_activity_add");

		$ID = isset($arParams['ACTIVITY_ID']) ? intval($arParams['ACTIVITY_ID']) : 0;
		if($ID > 0)
		{
			$subject = isset($arParams['ACTIVITY_SUBJECT']) ? $arParams['ACTIVITY_SUBJECT'] : '';


			$time = isset($arParams['ACTIVITY_TIME']) ? MakeTimeStamp($arParams['ACTIVITY_TIME']) : 0;
			$timeFormatted = $time > 0 ? CCrmComponentHelper::TrimDateTimeString(FormatDate('FULL', $time)) : '';
			$isExpired = isset($arParams['ACTIVITY_EXPIRED']) ? $arParams['ACTIVITY_EXPIRED'] : ($time <= (time() + CTimeZone::GetOffset()));

			$result = '<div class="crm-nearest-activity-wrapper"><div class="crm-list-deal-date crm-nearest-activity-time'.($isExpired ? '-expiried' : '').'"><a class="crm-link" target = "_self" href = "#"
				onclick="BX.CrmInterfaceGridManager.viewActivity(\''.CUtil::JSEscape($gridManagerID).'\', '.$ID.', { enableEditButton:'.($allowEdit ? 'true' : 'false').' }); return false;">'
				.htmlspecialcharsbx($timeFormatted).'</a></div><div class="crm-nearest-activity-subject">'.htmlspecialcharsbx($subject).'</div>';

			if($allowEdit && !empty($menuItems))
			{
				$result .= '<div class="crm-nearest-activity-plus" onclick="BX.CrmInterfaceGridManager.showMenu(\''.$menuID.'\', this);"></div></div>
					<script type="text/javascript">BX.CrmInterfaceGridManager.createMenu("'.$menuID.'", '.CUtil::PhpToJSObject($menuItems).');</script>';
			}


			$responsibleID = isset($arParams['ACTIVITY_RESPONSIBLE_ID']) ? intval($arParams['ACTIVITY_RESPONSIBLE_ID']) : 0;
			if($responsibleID > 0)
			{
				$nameTemplate = isset($arParams['NAME_TEMPLATE']) ? $arParams['NAME_TEMPLATE'] : '';
				if($nameTemplate === '')
				{
					$nameTemplate = CSite::GetNameFormat(false);
				}

				$responsibleFullName = CUser::FormatName(
					$nameTemplate,
					array(
						'LOGIN' => isset($arParams['ACTIVITY_RESPONSIBLE_LOGIN']) ? $arParams['ACTIVITY_RESPONSIBLE_LOGIN'] : '',
						'NAME' => isset($arParams['ACTIVITY_RESPONSIBLE_NAME']) ? $arParams['ACTIVITY_RESPONSIBLE_NAME'] : '',
						'LAST_NAME' => isset($arParams['ACTIVITY_RESPONSIBLE_LAST_NAME']) ? $arParams['ACTIVITY_RESPONSIBLE_LAST_NAME'] : '',
						'SECOND_NAME' => isset($arParams['ACTIVITY_RESPONSIBLE_SECOND_NAME']) ? $arParams['ACTIVITY_RESPONSIBLE_SECOND_NAME'] : ''
					),
					true, false
				);

				$responsibleShowUrl = '';
				$pathToUserProfile = isset($arParams['PATH_TO_USER_PROFILE']) ? $arParams['PATH_TO_USER_PROFILE'] : '';
				if($pathToUserProfile !== '')
				{
					$responsibleShowUrl = CComponentEngine::MakePathFromTemplate(
						$pathToUserProfile,
						array('user_id' => $responsibleID)
					);
				}
				$result .= '<div class="crm-list-deal-responsible"><span class="crm-list-deal-responsible-grey">'.htmlspecialcharsbx(GetMessage('CRM_ENTITY_ACTIVITY_FOR_RESPONSIBLE')).'</span><a class="crm-list-deal-responsible-name" target="_blank" href="'.htmlspecialcharsbx($responsibleShowUrl).'">'.htmlspecialcharsbx($responsibleFullName).'</a></div>';
			}
			return $result;
		}
		elseif($allowEdit && !empty($menuItems))
		{
			return '<span class="crm-activity-add-hint">'.htmlspecialcharsbx(GetMessage('CRM_ENTITY_ADD_ACTIVITY_HINT')).'</span>
				<a class="crm-activity-add" onclick="BX.CrmInterfaceGridManager.showMenu(\''.$menuID.'\', this); return false;">'.htmlspecialcharsbx(GetMessage('CRM_ENTITY_ADD_ACTIVITY')).'</a>
				<script type="text/javascript">BX.CrmInterfaceGridManager.createMenu("'.$menuID.'", '.CUtil::PhpToJSObject($menuItems).');</script>';
		}

		return '';
	}
	public static function RenderMultiFields(&$arFields, $prefix = '')
	{
		$result = array();

		$arEntityTypes = CCrmFieldMulti::GetEntityTypes();

		$arInfos = CCrmFieldMulti::GetEntityTypeInfos();
		foreach($arInfos as $typeID => &$arInfo)
		{
			$result[$typeID] = self::RenderMultiField($arFields, $typeID, $prefix, $arEntityTypes);
		}
		unset($arInfo);
		return $result;
	}
	public static function RenderMultiField(&$arFields, $typeName, $prefix = '', $arEntityTypes = null)
	{
		$typeName = strtoupper(strval($typeName));
		$prefix = strval($prefix);

		if(!is_array($arEntityTypes))
		{
			$arEntityTypes = CCrmFieldMulti::GetEntityTypes();
		}

		$result = '';

		$arValueTypes = isset($arEntityTypes[$typeName]) ? $arEntityTypes[$typeName] : array();
		if(!empty($arValueTypes))
		{
			$values = self::PrepareMultiFieldValues($arFields, $typeName, $arValueTypes);
			$result .= '<div class="bx-crm-multi-field-wrapper">'
				.self::RenderMultiFieldValues("{$prefix}{$typeName}", $values, $typeName, $arValueTypes)
				.'</div>';
		}

		return $result;
	}
	public static function PrepareMultiFieldCalltoLink($phone)
	{
		$linkAttrs = CCrmCallToUrl::PrepareLinkAttributes($phone);
		return '<a class="crm-fld-text" href="'
			.htmlspecialcharsbx($linkAttrs['HREF'])
			.'" onclick="'.htmlspecialcharsbx($linkAttrs['ONCLICK']).'">'
			.htmlspecialcharsbx($phone).'</a>';
	}
	public static function PrepareMultiFieldHtml($typeName, $arParams)
	{
		if($typeName === 'PHONE')
		{
			$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';
			$linkAttrs = CCrmCallToUrl::PrepareLinkAttributes($value);
			$className = $arParams['CLASS_NAME'] ? $arParams['CLASS_NAME'] : '';
			return '<a'.($className !== '' ? ' class="'.htmlspecialcharsbx($className).'"' : '')
				.' href="'.htmlspecialcharsbx($linkAttrs['HREF']).'"'
				.' onclick="'.htmlspecialcharsbx($linkAttrs['ONCLICK']).'">'
				.htmlspecialcharsbx($value).'</a>';
		}
		elseif($typeName === 'EMAIL')
		{
			$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';
			$valueUrl = $value;
			$crmEmail = strtolower(trim(COption::GetOptionString('crm', 'mail', '')));
			if($crmEmail !== '')
			{
				$valueUrl = $valueUrl.'?cc='.urlencode($crmEmail);
			}

			$className = $arParams['CLASS_NAME'] ? $arParams['CLASS_NAME'] : '';
			return '<a'.($className !== '' ? ' class="'.htmlspecialcharsbx($className).'"' : '')
				.' href="mailto:'.htmlspecialcharsbx($valueUrl).'">'
				.htmlspecialcharsbx($value).'</a>';
		}

		$valueTypeID = isset($arParams['VALUE_TYPE_ID']) ? $arParams['VALUE_TYPE_ID'] : '';
		$valueType = isset($arParams['VALUE_TYPE']) ? $arParams['VALUE_TYPE'] : null;
		if(!$valueType && $valueTypeID !== '')
		{
			$arEntityTypes = CCrmFieldMulti::GetEntityTypes();
			$arValueTypes = isset($arEntityTypes[$typeName]) ? $arEntityTypes[$typeName] : array();
			$valueType = isset($arValueTypes[$valueTypeID]) ? $arValueTypes[$valueTypeID] : null;
		}

		$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';

		if(!($valueType && !empty($valueType['TEMPLATE'])))
		{
			return htmlspecialcharsbx($value);
		}

		return str_replace(
			array(
				'#VALUE#',
				'#VALUE_URL#',
				'#VALUE_HTML#'
			),
			array(
				$value,
				htmlspecialcharsbx($value),
				htmlspecialcharsbx($value)
			),
			$valueType['TEMPLATE']
		);
	}
	public static function PrepareMultiFieldValues(&$arFields, $typeName, &$arValueTypes)
	{
		$typeName = strtoupper(strval($typeName));

		$result = array();
		foreach($arValueTypes as $valueTypeID => &$arValueType)
		{
			$key = "~{$typeName}_{$valueTypeID}";
			if(isset($arFields[$key]))
			{
				$result[$valueTypeID] = $arFields[$key];
			}
		}
		unset($arValueType);

		return $result;
	}
	private static function RenderMultiFieldValues($ID, &$arValues, $typeName, &$arValueTypes)
	{
		CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');

		$ID = strval($ID);
		if($ID === '')
		{
			$ID = uniqid('CRM_MULTI_FIELD_');
		}

		$typeName = strtoupper(strval($typeName));
		$queryString = '';

		if($typeName === 'EMAIL')
		{
			$crmEmail = strtolower(trim(COption::GetOptionString('crm', 'mail', '')));
			$queryString = $crmEmail !== '' ? '?cc='.urlencode($crmEmail) : '';
		}

		$result = '';
		$arValueData = array();
		foreach($arValueTypes as $valueTypeID => &$arValueType)
		{
			if(!isset($arValues[$valueTypeID]) || empty($arValues[$valueTypeID]))
			{
				continue;
			}

			foreach($arValues[$valueTypeID] as $value)
			{
				$arValueData[] = array(
					'VALUE_TYPE_ID' => $valueTypeID,
					'VALUE' => $value,
					'VALUE_URL' => $queryString !== '' ? $value.$queryString : $value
				);
			}
		}
		unset($arValueType);

		$qty = count($arValueData);
		if($qty === 0)
		{
			return '';
		}

		$first = $arValueData[0];
		$firstValueType = isset($arValueTypes[$first['VALUE_TYPE_ID']]) ? $arValueTypes[$first['VALUE_TYPE_ID']] : null;
		if($firstValueType)
		{
			$result .= '<div class="crm-multi-field-value-wrapper">'
				.self::PrepareMultiFieldHtml($typeName, array('VALUE_TYPE' => $firstValueType, 'VALUE' => $first['VALUE']))
				.'</div>';
		}

		if($qty > 1)
		{
			$arPopupItems = array();
			for($i = 1; $i < $qty; $i++)
			{
				$current = $arValueData[$i];
				$valueType = isset($arValueTypes[$current['VALUE_TYPE_ID']]) ? $arValueTypes[$current['VALUE_TYPE_ID']] : null;
				if(!$valueType)
				{
					continue;
				}

				$arPopupItems[] = array(
					'value' => htmlspecialcharsbx(
						self::PrepareMultiFieldHtml($typeName, array('TYPE' => $valueType, 'VALUE' => $current['VALUE']))
					),
					'type' => htmlspecialcharsbx(
						isset($valueType['SHORT']) ? strtolower($valueType['SHORT']) : ''
					)
				);
			}

			$buttonID = $ID.'_BTN';
			$result .= '<div class="crm-multi-field-popup-wrapper">';
			$result .= '<span id="'.htmlspecialcharsbx($buttonID)
				.'" class="crm-multi-field-popup-button" onclick="BX.CrmMultiFieldViewer.ensureCreated(\''
				.CUtil::JSEscape($ID).'\', { \'anchorId\':\''.CUtil::JSEscape($buttonID).'\', \'items\':'.CUtil::PhpToJSObject($arPopupItems).' }).show();">'.htmlspecialcharsbx(GetMessage('CRM_ENTITY_MULTI_FIELDS_MORE')).' '.($qty - 1).'</span>';
			$result .= '</div>';
		}

		return $result;
	}
	public static function PrepareFirstMultiFieldHtml($typeName, $arValues, $arValueTypes, $arParams = array())
	{
		foreach($arValues as $valueTypeID => $values)
		{
			$valueType = isset($arValueTypes[$valueTypeID]) ? $arValueTypes[$valueTypeID] : null;

			foreach($values as $value)
			{
				if($value !== '')
				{
					if(!is_array($arParams))
					{
						$arParams = array();
					}
					$arParams['VALUE_TYPE'] = $valueType;
					$arParams['VALUE'] = $value;
					return self::PrepareMultiFieldHtml($typeName, $arParams);
				}
			}
		}
		return '';
	}
	public static function PrepareMultiFieldValuesPopup($popupID, $achorID, $typeName, $arValues, $arValueTypes)
	{
		CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');

		$arPopupItems = array();
		foreach($arValues as $valueTypeID => $values)
		{
			$valueType = isset($arValueTypes[$valueTypeID]) ? $arValueTypes[$valueTypeID] : null;

			foreach($values as $value)
			{
				$arPopupItems[] = array(
					'value' => htmlspecialcharsbx(
						self::PrepareMultiFieldHtml($typeName, array('VALUE_TYPE' => $valueType, 'VALUE' => $value))
					),
					'type' => htmlspecialcharsbx(
						isset($valueType['SHORT']) ? strtolower($valueType['SHORT']) : ''
					)
				);
			}
		}

		return 'BX.CrmMultiFieldViewer.ensureCreated(\''
			.CUtil::JSEscape($popupID).'\', { \'anchorId\':\''
			.CUtil::JSEscape($achorID).'\', \'items\':'
			.CUtil::PhpToJSObject($arPopupItems).' }).show();';
	}
	public static function RenderResponsiblePanel($arParams)
	{
		$prefix = isset($arParams['PREFIX']) ? $arParams['PREFIX'] : '';
		$editable = isset($arParams['EDITABLE']) ? $arParams['EDITABLE'] : false;
		$userProfileUrlTemplate = isset($arParams['USER_PROFILE_URL_TEMPLATE']) ? $arParams['USER_PROFILE_URL_TEMPLATE'] : '';
		$userID = isset($arParams['USER_ID']) ? $arParams['USER_ID'] : '';
		$showUrl = $userID > 0 && $userProfileUrlTemplate !== '' ? str_replace('#user_id#', $userID, $userProfileUrlTemplate) : '#';

		echo '<div class="crm-detail-info-resp-block">';
		echo '<div class="crm-detail-info-resp-header">';
		echo '<span class="crm-detail-info-resp-text">',
			htmlspecialcharsbx(GetMessage('CRM_ENTITY_INFO_RESPONSIBLE')),':</span>';

		if($editable)
		{
			$editButtonID =  $prefix !== '' ? "{$prefix}_responsible_edit" : 'responsible_edit';
			echo '<span class="crm-detail-info-resp-edit" id="',
				htmlspecialcharsbx($editButtonID), '">',
				htmlspecialcharsbx(GetMessage('CRM_ENTITY_INFO_RESPONSIBLE_CHANGE')),'</span>';
		}

		echo '</div>';
		$containerID =  $prefix !== '' ? "{$prefix}_responsible_container" : 'responsible_container';
		echo '<a class="crm-detail-info-resp" id="',
			htmlspecialcharsbx($containerID),
			'" target="_blank" href="', htmlspecialcharsbx($showUrl),'">';

		echo '<div class="crm-detail-info-resp-img">';
		$photoID = isset($arParams['PHOTO']) ? intval($arParams['PHOTO']) : 0;
		if($photoID > 0)
		{
			$photoUrl = CFile::ResizeImageGet(
				$photoID,
				array('width' => 30, 'height' => 30),
				BX_RESIZE_IMAGE_EXACT
			);
			echo '<img alt="" src="', htmlspecialcharsbx($photoUrl['src']),'"/>';
		}
		echo '</div>';

		echo '<span class="crm-detail-resp-alignment"></span>',
			'<span class="crm-detail-info-resp-name">',
			(isset($arParams['NAME']) ? htmlspecialcharsbx($arParams['NAME']) : ''),
			'</span>';

		echo '<span class="crm-detail-info-resp-descr">',
			(isset($arParams['WORK_POSITION']) ? htmlspecialcharsbx($arParams['WORK_POSITION']) : ''),
			'</span>';
		echo '</a>';

		if($editable)
		{
			$userSelectorName = $prefix !== '' ? "{$prefix}_responsible_selector" : 'responsible_selector';
			$GLOBALS['APPLICATION']->IncludeComponent(
				'bitrix:intranet.user.selector.new', '.default',
				array(
					'MULTIPLE' => 'N',
					'NAME' => $userSelectorName,
					'POPUP' => 'Y',
					'SITE_ID' => SITE_ID
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);

			echo '<script type="text/javascript">';
			echo 'BX.ready(function(){';
			echo 'BX.CrmSidebarUserSelector.create(',
				'"', $userSelectorName, '", ',
				'BX("', CUtil::JSEscape($editButtonID), '"), ',
				'BX("', CUtil::JSEscape($containerID), '"), ',
				'"', CUtil::JSEscape($userSelectorName), '", ',
				'"', isset($arParams['FIELD_ID']) ? CUtil::JSEscape($arParams['FIELD_ID']) : 'ASSIGNED_BY_ID', '", ',
				'"', isset($arParams['SERVICE_URL']) ? CUtil::JSEscape($arParams['SERVICE_URL']) : '', '", ',
				'{ "GET_USER_INFO_GENERAL_ERROR": "', GetMessageJS('CRM_GET_USER_INFO_GENERAL_ERROR'), '" }, ',
				'{ "userProfileUrlTemplate":"', CUtil::JSEscape($userProfileUrlTemplate) ,'"  }',
				');';
			echo '});';
			echo '</script>';
		}

		echo '</div>';

	}
	public static function RenderInstantEditorField($arParams)
	{
		$fieldID = isset($arParams['FIELD_ID']) ? $arParams['FIELD_ID'] : '';
		$type = isset($arParams['TYPE']) ? $arParams['TYPE'] : '';

		if($type === 'TEXT')
		{
			$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';
			$suffixHtml = isset($arParams['SUFFIX_HTML']) ? $arParams['SUFFIX_HTML'] : '';
			if($suffixHtml === '')
			{
				$suffix = isset($arParams['SUFFIX']) ? $arParams['SUFFIX'] : '';
				if($suffix !== '')
				{
					$suffixHtml = htmlspecialcharsbx($suffix);
				}
			}
			$inputWidth = isset($arParams['INPUT_WIDTH']) ? intval($arParams['INPUT_WIDTH']) : 0;

			echo '<span class="crm-instant-editor-fld crm-instant-editor-fld-input">',
				'<span class="crm-instant-editor-fld-text">', htmlspecialcharsbx($value), '</span>';

			echo '<input class="crm-instant-editor-data-input" type="text" value="', htmlspecialcharsbx($value),
				'" style="display:none;', ($inputWidth > 0 ? "width:{$inputWidth}px;" : ''), '" />',
				'<input class="crm-instant-editor-data-name" type="hidden" value="', htmlspecialcharsbx($fieldID), '" />';

			if($suffixHtml !== '')
			{
				echo '<span class="crm-instant-editor-fld-suffix">', $suffixHtml, '</span>';
			}

			echo '</span><span class="crm-instant-editor-fld-btn crm-instant-editor-fld-btn-input"></span>';
		}
		elseif($type === 'LHE')
		{
			$editorID = isset($arParams['EDITOR_ID']) ? $arParams['EDITOR_ID'] : '';
			if($editorID ==='')
			{
				$editorID = uniqid('LHE_');
			}

			$editorJsName = isset($arParams['EDITOR_JS_NAME']) ? $arParams['EDITOR_JS_NAME'] : '';
			if($editorJsName ==='')
			{
				$editorJsName = $editorID;
			}


			$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';

			/*if($value === '<br />')
			{
				$value = '';
			}*/

			echo '<span class="crm-instant-editor-fld-text">';
			echo $value;
			echo '</span>';
			echo '<div class="crm-instant-editor-fld-btn crm-instant-editor-fld-btn-lhe"></div>';
			echo '<input class="crm-instant-editor-data-name" type="hidden" value="', htmlspecialcharsbx($fieldID), '" />';
			echo '<input class="crm-instant-editor-data-value" type="hidden" value="', htmlspecialcharsbx($value), '" />';

			$wrapperID = isset($arParams['WRAPPER_ID']) ? $arParams['WRAPPER_ID'] : '';
			if($wrapperID ==='')
			{
				$wrapperID = $editorID.'_WRAPPER';
			}

			echo '<input class="crm-instant-editor-lhe-data" type="hidden" value="',
			htmlspecialcharsbx('{ "id":"'.CUtil::JSEscape($editorID).'", "wrapperId":"'.CUtil::JSEscape($wrapperID).'", "jsName":"'.CUtil::JSEscape($editorJsName).'" }'),
			'" />';

			echo '<div id="', htmlspecialcharsbx($wrapperID),'" style="display:none;">';

			CModule::IncludeModule('fileman');
			$editor = new CLightHTMLEditor;
			$editor->Show(
				array(
					'id' => $editorID,
					'width' => '600',
					'height' => '200',
					'bUseFileDialogs' => false,
					'bFloatingToolbar' => false,
					'bArisingToolbar' => false,
					'bResizable' => false,
					'jsObjName' => $editorJsName,
					'bInitByJS' => false, // TODO: Lazy initialization
					'bSaveOnBlur' => true,
					'bHandleOnPaste'=> false,
					'toolbarConfig' => array(
						'Bold', 'Italic', 'Underline', 'Strike',
						'BackColor', 'ForeColor',
						'CreateLink', 'DeleteLink',
						'InsertOrderedList', 'InsertUnorderedList', 'Outdent', 'Indent'
					)
				)
			);
			echo '</div>';
		}
	}
	public static function RenderSelector($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';
		//Items must be html encoded
		$items = isset($arParams['ITEMS']) ? $arParams['ITEMS'] : array();
		$encodeItems = isset($arParams['ENCODE_ITEMS']) ? (bool)$arParams['ENCODE_ITEMS'] : true;
		$resultItems = array();
		foreach($items as $id => $caption)
		{
			$resultItems[] = array(
				'id' => $id,
				'caption' => !$encodeItems ? $caption : htmlspecialcharsbx($caption)
			);
		}

		$text =  $value !== '' && isset($items[$value]) ? $items[$value] : '';

		if($text === '')
		{
			$text = isset($arParams['UNDEFINED']) ? htmlspecialcharsbx($arParams['UNDEFINED']) : '';
		}

		$editable = isset($arParams['EDITABLE']) ? $arParams['EDITABLE'] : false;
		if($editable)
		{
			$selectorName = isset($arParams['SELECTOR_ID']) ? $arParams['SELECTOR_ID'] : 'selector';
			$fieldID = isset($arParams['FIELD_ID']) ? $arParams['FIELD_ID'] : '';
			//$containerID = isset($arParams['CONTAINER_ID']) ? $arParams['CONTAINER_ID'] : 'sidebar';

			$containerClassName = isset($arParams['CONTAINER_CLASS']) ? $arParams['CONTAINER_CLASS'] : '';
			echo '<span',
				($containerClassName !== '' ? ' class="'.htmlspecialcharsbx($containerClassName).'"' : ''),
				'>';

			$uniqueID = uniqid();

			$itemID = "{$selectorName}_{$uniqueID}";
			$textClassName = isset($arParams['TEXT_CLASS']) ? $arParams['TEXT_CLASS'] : '';
			echo '<span id="', htmlspecialcharsbx($itemID), '"';
			if($textClassName !== '')
			{
				echo ' class="', htmlspecialcharsbx($textClassName), '"';
			}

			echo '>', $text, '</span>';

			$buttonID = '';
			$arrowClassName = isset($arParams['ARROW_CLASS']) ? $arParams['ARROW_CLASS'] : '';
			if($arrowClassName !== '')
			{
				$buttonID = "{$selectorName}_btn_{$uniqueID}";
				echo '<span id="', htmlspecialcharsbx($buttonID),'" class="', htmlspecialcharsbx($arrowClassName), '"></span>';
			}

			echo '<script type="text/javascript">';
			echo 'BX.ready(function(){',
				'BX.CmrSidebarFieldSelector.create(',
				'"', CUtil::JSEscape($selectorName), '",',
				'"', CUtil::JSEscape($fieldID), '",',
				'BX("', CUtil::JSEscape($itemID) ,'"),',
				'{
					"options": ', CUtil::PhpToJSObject($resultItems), ',
					"buttonId":', CUtil::JSEscape($buttonID) ,'
				});});';
			echo '</script>';

			echo '</span>';
		}
		else
		{
			echo htmlspecialcharsbx($text);
		}
	}
	private static function RenderFormResponsible($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		$prefix = isset($arParams['PREFIX']) ? $arParams['PREFIX'] : '';
		$editable = isset($arParams['EDITABLE']) ? $arParams['EDITABLE'] : false;

		echo '<div class="crm-entity-info-field-person">';

		echo '<div class="crm-entity-info-field-person-header">';

		echo '<span class="crm-entity-info-field-person-title">',
			htmlspecialcharsbx(GetMessage('CRM_ENTITY_INFO_RESPONSIBLE')),
			'</span>';

		$editButtonID =  $prefix !== '' ? "{$prefix}_responsible_edit" : 'responsible_edit';
		if($editable)
		{
			echo '<span id="',
				htmlspecialcharsbx($editButtonID),
				'" class="crm-entity-info-field-person-edit">',
				htmlspecialcharsbx(GetMessage('CRM_ENTITY_INFO_RESPONSIBLE_CHANGE')),
				'</span>';
		}
		echo '</div>'; //crm-entity-info-field-person-header

		$containerID =  $prefix !== '' ? "{$prefix}_responsible_container" : 'responsible_container';
		echo '<div id="', htmlspecialcharsbx($containerID), '" class="crm-entity-info-field-person-content">';

		$photoID = isset($arParams['PHOTO']) ? intval($arParams['PHOTO']) : 0;
		if($photoID <= 0)
		{
			echo '<span class="crm-entity-info-field-person-photo"></span>';
		}
		else
		{
			$photoUrl = CFile::ResizeImageGet(
				$photoID,
				array('width'=>32, 'height'=>32),
				BX_RESIZE_IMAGE_EXACT
			);

			echo '<span class="crm-entity-info-field-person-photo" style="background-image: url(\'', $photoUrl['src'], '\');"></span>';
		}

		echo '<div class="crm-entity-info-field-person-full-name">', isset($arParams['NAME']) ? htmlspecialcharsbx($arParams['NAME']) : '', '</div>';
		echo '<div class="crm-entity-info-field-person-post">', isset($arParams['WORK_POSITION']) ? htmlspecialcharsbx($arParams['WORK_POSITION']) : '', '</div>';

		if($editable)
		{
			$userSelectorName = $prefix !== '' ? "{$prefix}_responsible_selector" : 'responsible_selector';
			$GLOBALS['APPLICATION']->IncludeComponent(
				'bitrix:intranet.user.selector.new', '.default',
				array(
					'MULTIPLE' => 'N',
					'NAME' => $userSelectorName,
					'POPUP' => 'Y',
					'SITE_ID' => SITE_ID
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);

			echo '<script type="text/javascript">';
			echo 'BX.ready(function(){';
			echo 'BX.CrmSidebarUserSelector.create(',
				'"', $userSelectorName, '", ',
				'BX("', CUtil::JSEscape($editButtonID), '"), ',
				'BX("', CUtil::JSEscape($containerID), '"), ',
				'"', CUtil::JSEscape($userSelectorName), '", ',
				'"', isset($arParams['FIELD_ID']) ? CUtil::JSEscape($arParams['FIELD_ID']) : 'ASSIGNED_BY_ID', '", ',
				'"', isset($arParams['SERVICE_URL']) ? CUtil::JSEscape($arParams['SERVICE_URL']) : '', '", ',
				'{ "GET_USER_INFO_GENERAL_ERROR": "', GetMessageJS('CRM_GET_USER_INFO_GENERAL_ERROR'), '" }',
				');';
			echo '});';
			echo '</script>';
		}

		echo '</div>'; //crm-entity-info-field-person-content
		echo '</div>'; //crm-entity-info-field-person

	}
	private static function RenderFormMultiFields($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		$typeID = isset($arParams['TYPE_ID']) ? $arParams['TYPE_ID'] : '';
		$entityTypes = isset($arParams['ENTITY_TYPES']) ? $arParams['ENTITY_TYPES'] : null;
		if(!is_array($entityTypes))
		{
			$entityTypes = CCrmFieldMulti::GetEntityTypes();
		}

		$typeInfo = isset($entityTypes[$typeID]) ? $entityTypes[$typeID] : array();

		$readonly = isset($arParams['READONLY']) ? (bool)$arParams['READONLY'] : true;
		$items = isset($arParams['ITEMS']) ? $arParams['ITEMS'] : array();

		$qty = 0;
		foreach($items as $ID => &$item)
		{
			if($qty > 0)
			{
				echo '<br/>';
			}

			$value = isset($item['VALUE']) ? $item['VALUE'] : '';
			$valueType = isset($item['VALUE_TYPE']) ? $item['VALUE_TYPE'] : '';

			$valueTypeInfo = isset($typeInfo[$valueType]) ? $typeInfo[$valueType] : null;
			$caption = $valueTypeInfo && isset($valueTypeInfo['FULL']) ? $valueTypeInfo['FULL'] : '';
			if($caption !== '')
			{
				echo htmlspecialcharsbx($caption), ': ';
			}

			if($readonly)
			{
				echo CCrmFieldMulti::GetTemplate($typeID, $valueType, $value);
			}
			else
			{
				$templateType = 'INPUT';
				$editorFieldType = strtolower($typeID);

				if($typeID === 'PHONE' || $typeID === 'EMAIL' || $typeID === 'WEB')
				{
					$templateType = 'LINK';
				}

				if($typeID === 'WEB' && $valueType !== 'WORK' && $valueType !== 'HOME' && $valueType !== 'OTHER')
				{
					$editorFieldType .= '-'.strtolower($valueType);
				}
				elseif($typeID === 'IM')
				{
					$templateType = $valueType === 'SKYPE' || $valueType === 'ICQ' || $valueType === 'MSN' ? 'LINK' : 'INPUT';
					$editorFieldType .= '-'.strtolower($valueType);
				}

				$template = isset(self::$MULTI_VIEW_TEMPLATES[$templateType]) ? self::$MULTI_VIEW_TEMPLATES[$templateType] : '';

				if($template === '')
				{
					echo CCrmFieldMulti::GetTemplate($typeID, $valueType, $value);
				}
				else
				{
					$viewValue = $value;
					$onClick = '';
					if($typeID === 'PHONE')
					{
						$linkAttrs = CCrmCallToUrl::PrepareLinkAttributes($value);
						$viewValue = $linkAttrs['HREF'];
						$onClick = $linkAttrs['ONCLICK'];
					}
					elseif($typeID === 'EMAIL')
					{
						$viewValue = "mailto:{$value}";
						$crmEmail = strtolower(trim(COption::GetOptionString('crm', 'mail', '')));
						if($crmEmail !== '')
						{
							$viewValue .= '?cc='.urlencode($crmEmail);
						}
					}
					elseif($typeID === 'WEB')
					{
						if($valueType === 'OTHER' || $valueType === 'WORK' || $valueType === 'HOME')
						{
							$hasProto = preg_match('/^http(?:s)?:\/\/(.+)/', $value, $urlMatches) > 0;
							if($hasProto)
							{
								$value = $urlMatches[1];
							}
							else
							{
								$viewValue = "http://{$value}";
							}
						}
						elseif($valueType === 'FACEBOOK')
						{
							$viewValue = "http://www.facebook.com/{$value}/";
						}
						elseif($valueType === 'TWITTER')
						{
							$viewValue = "http://twitter.com/{$value}/";
						}
						elseif($valueType === 'LIVEJOURNAL')
						{
							$viewValue = "http://{$value}.livejournal.com/";
						}
					}
					elseif($typeID === 'IM')
					{
						if($valueType === 'SKYPE')
						{
							$viewValue = "skype:{$value}?chat";
						}
						elseif($valueType === 'ICQ')
						{
							$viewValue = "http://www.icq.com/people/{$value}/";
						}
						elseif($valueType === 'MSN')
						{
							$viewValue = "msn:{$value}";
						}
					}

					echo str_replace(
						array('#NAME#', '#FIELD_TYPE#', '#VALUE#', '#VIEW_VALUE#', '#ON_CLICK#'),
						array("FM.{$typeID}.{$valueType}.{$ID}", htmlspecialcharsbx($editorFieldType), htmlspecialcharsbx($value), htmlspecialcharsbx($viewValue), htmlspecialcharsbx($onClick)),
						$template
					);
				}
			}
			$qty++;
		}
		unset($item);
	}
	private static function RenderFormLink($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		echo '<span><a target=',
			'"', isset($arParams['TARGET']) ? htmlspecialcharsbx($arParams['TARGET']) : '_blank', '"',
			' href=',
			'"', isset($arParams['HREF']) ? htmlspecialcharsbx($arParams['HREF']) : '', '">',
			isset($arParams['TEXT']) ? htmlspecialcharsbx($arParams['TEXT']) : '',
			'</a></span>';

	}
	private static function RenderFormSelect($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';
		//Items must be html encoded
		$items = isset($arParams['ITEMS']) ? $arParams['ITEMS'] : array();
		$encodeItems = isset($arParams['ENCODE_ITEMS']) ? (bool)$arParams['ENCODE_ITEMS'] : true;

		$resultItems = array();
		foreach($items as $id => $caption)
		{
			$resultItems[] = array(
				'id' => $id,
				'caption' => !$encodeItems ? $caption : htmlspecialcharsbx($caption)
			);
		}

		$text =  $value !== '' && isset($items[$value]) ? $items[$value] : '';

		if($text === '')
		{
			$text = isset($arParams['UNDEFINED']) ? htmlspecialcharsbx($arParams['UNDEFINED']) : '';
		}

		$editable = isset($arParams['EDITABLE']) ? $arParams['EDITABLE'] : false;
		if($editable)
		{
			echo '<span class="crm-entity-info-field';
			$className = isset($arParams['CLASS']) ? $arParams['CLASS'] : '';
			if($className !== '')
			{
				echo ' ', $className;
			}

			echo '">', $text, '</span>';

			$selectorName = isset($arParams['SELECTOR_ID']) ? $arParams['SELECTOR_ID'] : 'selector';
			$fieldID = isset($arParams['FIELD_ID']) ? $arParams['FIELD_ID'] : '';
			$containerID = isset($arParams['CONTAINER_ID']) ? $arParams['CONTAINER_ID'] : 'sidebar';

			echo '<script type="text/javascript">';
			echo 'BX.ready(function(){',
				'BX.CmrSidebarFieldSelector.create(',
				'"', CUtil::JSEscape($selectorName), '",',
				'"', CUtil::JSEscape($fieldID), '",',
				'BX.findChild(BX("', CUtil::JSEscape($containerID) ,'"), { "tagName":"span", "className":',
				'"', CUtil::JSEscape($className), '" }, true, false),',
				'{ "options": ', CUtil::PhpToJSObject($resultItems), ' });});';
			echo '</script>';
		}
		else
		{
			echo '<span class="crm-entity-info-field crm-entity-info-field-text">',
				htmlspecialcharsbx($text),
				'</span>';
		}
	}
	private static function RenderFormMoney($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';

		$editable = isset($arParams['EDITABLE']) ? $arParams['EDITABLE'] : false;
		if($editable)
		{
			$fieldID = isset($arParams['FIELD_ID']) ? $arParams['FIELD_ID'] : '';
			$currencyID = isset($arParams['CURRENCY_ID']) ? $arParams['CURRENCY_ID'] : '';

			echo '<span class="crm-fld-block">',
				'<span class="crm-fld crm-fld-input">',
				'<span class="crm-fld-text">', htmlspecialcharsbx($value), '</span>',
				'<span class="crm-fld-value">',
				'<input type="text" value="', htmlspecialcharsbx($value), '" class="crm-fld-element-input" style="display:none;" />',
				'<input type="hidden" value="', htmlspecialcharsbx($fieldID), '" class="crm-fld-element-name" /></span></span>',
				'&nbsp;<span class="crm-entity-info-field-currency-id">', htmlspecialcharsbx($currencyID) ,'</span>',
				'<span class="crm-fld-icon crm-fld-icon-input"></span></span>';
		}
		else
		{
			echo '<span class="crm-entity-info-field crm-entity-info-field-text">', htmlspecialcharsbx($value),'</span>';
		}
	}
	private static function RenderFormPercents($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		$value = isset($arParams['VALUE']) ? $arParams['VALUE'] : '';

		$editable = isset($arParams['EDITABLE']) ? $arParams['EDITABLE'] : false;
		if($editable)
		{
			$fieldID = isset($arParams['FIELD_ID']) ? $arParams['FIELD_ID'] : '';

			echo '<span class="crm-fld-block">',
			'<span class="crm-fld crm-fld-input">',
			'<span class="crm-fld-text">', htmlspecialcharsbx($value), '</span>',
			'<span class="crm-fld-value">',
			'<input type="text" value="', htmlspecialcharsbx($value), '" class="crm-fld-element-input" style="display:none;" />',
			'<input type="hidden" value="', htmlspecialcharsbx($fieldID), '" class="crm-fld-element-name" /></span></span>',
			'&nbsp;<span class="crm-entity-info-field-probability-suffix">%</span>',
			'<span class="crm-fld-icon crm-fld-icon-input"></span></span>';
		}
		else
		{
			echo '<span class="crm-entity-info-field crm-entity-info-field-text">', htmlspecialcharsbx($value),'&nbsp;%</span>';
		}
	}
	private static function PrepareImageHtml($fileID, $width = 50, $height = 50, $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL)
	{
		$fileID = intval($fileID);
		if($fileID <= 0)
		{
			return '';
		}

		$width = intval($width);
		if($width <= 0)
		{
			$width = 50;
		}

		$height = intval($height);
		if($height <= 0)
		{
			$height = 50;
		}

		$ary = CFile::ResizeImageGet(
			$fileID,
			array('width' => $width, 'height' => $height),
			$resizeType,
			false
		);
		return CFile::ShowImage($ary['src'], $width, $height, 'border=0');
	}
	private static function RenderFormClientInfo($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		echo '<div class="crm-client-summary-wrapper"><div class="crm-client-photo-wrapper">';
		$photoHtml = self::PrepareImageHtml(isset($arParams['PHOTO_ID']) ? intval($arParams['PHOTO_ID']) : 0);
		if($photoHtml !== '')
		{
			echo $photoHtml;
		}
		else
		{
			echo '<img src="/bitrix/js/tasks/css/images/avatar.png"/>';
		}
		echo '</div>';

		echo '<div class="crm-client-info-wrapper">';
		$url = isset($arParams['URL']) ? $arParams['URL'] : '';
		$name = isset($arParams['NAME']) ? $arParams['NAME'] : '';
		if($url !== '' || $name !== '')
		{
			echo '<div class="crm-client-title-wrapper">';
			if($url !== '')
			{
				$typeName = isset($arParams['TYPE_NAME']) ? $arParams['TYPE_NAME'] : 'CONTACT';
				$ID = isset($arParams['ID']) ? intval($arParams['ID']) : 0;
				$tooltipID = (isset($arParams['ENABLE_TOOLTIP']) ? (bool)$arParams['ENABLE_TOOLTIP'] : false) ? uniqid(strtolower($typeName).'_tooltip_'.$ID) : '';
				$target = isset($arParams['TARGET']) ? $arParams['TARGET'] : '_blank';
				echo '<a',($tooltipID !== '' ? ' id="'.$tooltipID.'"' : ''), ' target="', htmlspecialcharsbx($target), '" href="', htmlspecialcharsbx($url), '">',
					htmlspecialcharsbx($name !== '' ? $name: $url), '</a>';

				if($tooltipID !== '')
				{
					echo '<script type="text/javascript">',
						'BX.tooltip("', $typeName, '_', $ID, '", "', $tooltipID, '", "/bitrix/components/bitrix/crm.', strtolower($typeName),'.show/card.ajax.php", "crm_balloon_', strtolower($typeName),'", true);',
						'</script>';
				}

			}
			elseif($name !== '')
			{
				echo htmlspecialcharsbx($name);
			}
			echo '</div>';
		}

		$description = isset($arParams['DESCRIPTION']) ? $arParams['DESCRIPTION'] : '';
		if($description !== '')
		{
			echo '<div class="crm-client-description-wrapper">', htmlspecialcharsbx($description), '</div>';
		}

		$html = isset($arParams['ADDITIONAL_HTML']) ? $arParams['ADDITIONAL_HTML'] : '';
		if($html !== '')
		{
			echo '<div class="crm-client-description-wrapper">', $html, '</div>';
		}

		echo '</div><div style="clear:both;"></div></div>';
	}
	public static function PrepareHtml(&$arData)
	{
		if(!is_array($arData))
		{
			return '';
		}

		if(isset($arData['HTML']))
		{
			return $arData['HTML'];
		}
		elseif(isset($arData['TEXT']))
		{
			return htmlspecialcharsbx($arData['TEXT']);
		}

		return '';
	}
	public static function RenderEntityInfoSidebar($ID, $title, $detailTitle, &$arSectionData, $detailsContainerID = '')
	{
		$ID = strval($ID);
		$title = strval($title);
		$detailTitle = strval($detailTitle);
		if(!is_array($arSectionData))
		{
			$arSectionData = array();
		}

		echo '<div class="sidebar-block"><div id="'.htmlspecialcharsbx($ID).'" class="crm-entity-info">',
			'<div class="crm-entity-info-header">',
			'<h2>', htmlspecialcharsbx($title), '</h2>';

		if($detailsContainerID !== '')
		{
			echo '<span class="crm-entity-info-details-button" onclick="BX.CrmEntityDetailViewDialog.ensureCreated(\'',
			CUtil::JSEscape("{$ID}_details_dialog"),
			'\', { \'containerId\':\'', CUtil::JSEscape($detailsContainerID), '\', \'title\': \'',
			GetMessageJS('CRM_ENTITY_INFO_DETAILS_TITLE'),
			'\' }).toggle(BX.findChild(BX(\'sidebar\'), { \'tag\': \'H2\' }, true, false));">',
			htmlspecialcharsbx($detailTitle !== '' ? $detailTitle : GetMessage('CRM_ENTITY_INFO_DETAILS_BUTTON')),
			'</span>';
		}

		echo '</div>';

		foreach($arSectionData as &$arSection)
		{
			$sectionType = isset($arSection['TYPE']) ? $arSection['TYPE'] : '';

			if($sectionType === 'EXPANDABLE')
			{
				echo '<div class="crm-entity-info-section crm-entity-info-section-hidden">';

				echo '<div class="crm-entity-info-section-header">';
				echo '<span class="crm-entity-info-section-header-text">',
					isset($arSection['TITLE']) ? htmlspecialcharsbx($arSection['TITLE']) : '',
					'</span>';
				echo '<span class="crm-entity-info-section-header-cursor"></span>';
				echo '</div>'; //crm-entity-info-section-header
			}
			else
			{
				echo '<div class="crm-entity-info-section">';
			}

			echo '<div class="crm-entity-info-section-content">',
			'<ul class="crm-entity-info-list">';

			$itemData = isset($arSection['ITEMS']) && is_array($arSection['ITEMS']) ? $arSection['ITEMS'] : null;
			if($itemData)
			{
				foreach($itemData as &$item)
				{
					echo '<li>';

					$itemType = isset($item['TYPE']) ? $item['TYPE'] : '';
					$itemParams = isset($item['PARAMS']) ? $item['PARAMS'] : null;
					$titleData = isset($item['TITLE']) && is_array($item['TITLE']) ? $item['TITLE'] : null;
					if(is_array($itemParams))
					{
						if($itemType === 'LINK')
						{
							if($titleData)
							{
								echo self::PrepareHtml($titleData), ': ';
							}
							self::RenderFormLink($itemParams);
						}
						elseif($itemType === 'MONEY')
						{
							if($titleData)
							{
								echo self::PrepareHtml($titleData), ': ';
							}
							self::RenderFormMoney($itemParams);
						}
						elseif($itemType === 'PERCENTS')
						{
							if($titleData)
							{
								echo self::PrepareHtml($titleData), ': ';
							}
							self::RenderFormPercents($itemParams);
						}
						elseif($itemType === 'CLIENT_INFO')
						{
							self::RenderFormClientInfo($itemParams);
						}
						elseif($itemType === 'SELECT')
						{
							if($titleData)
							{
								echo self::PrepareHtml($titleData), ': ';
							}
							self::RenderFormSelect($itemParams);
						}
						elseif($itemType === 'RESPONSIBLE')
						{
							self::RenderFormResponsible($itemParams);
						}
						elseif($itemType === 'MULTI_FIELD')
						{
							self::RenderFormMultiFields($itemParams);
						}
						elseif($itemType === 'LHE' || $itemType === 'TEXT_AREA')
						{
							if($titleData)
							{
								echo '<span class="crm-fld-block">', self::PrepareHtml($titleData), ': </span>';
							}

							if($itemType === 'LHE')
							{
								CCrmInstantEditorHelper::RenderHtmlEditor($itemParams);
							}
							else
							{
								CCrmInstantEditorHelper::RenderTextArea($itemParams);
							}
						}
					}
					else
					{
						$titleData = isset($item['TITLE']) && is_array($item['TITLE']) ? $item['TITLE'] : null;
						if($titleData)
						{
							if(isset($titleData['HTML']))
							{
								echo $titleData['HTML'], ': ';
							}
							elseif(isset($titleData['TEXT']))
							{
								echo htmlspecialcharsbx($titleData['TEXT']), ': ';
							}
						}

						$contentData = isset($item['CONTENT']) && is_array($item['CONTENT']) ? $item['CONTENT'] : null;
						if($contentData)
						{
							if(isset($contentData['HTML']))
							{
								echo $contentData['HTML'];
							}
							elseif(isset($contentData['TEXT']))
							{
								echo htmlspecialcharsbx($contentData['TEXT']);
							}
						}

						$scriptData = isset($item['SCRIPT'])  ? $item['SCRIPT'] : null;
						if($scriptData !== '')
						{
							echo '<script type="text/javascript">';
							echo $scriptData;
							echo '</script>';
						}
					}

					echo '</li>';
				}
				unset($item);
			}

			echo '</ul></div></div>';
		}
		unset($arSection);

		echo '</div></div>';
	}
	public static function RenderEntityInfoDetails(&$arFields, $containerID = '')
	{
		if(!is_array($arFields))
		{
			return;
		}

		$containerID = strval($containerID);

		echo '<div';
		if($containerID !== '')
		{
			echo ' id="', htmlspecialcharsbx($containerID), '"';
		}

		echo ' class="crm-entity-info-details-container" style="display:none;">',
			'<table cellspacing="0" cellpadding="0" border="0" class="bx-crm-view-fieldset-content-table"><tbody>';
		foreach($arFields as &$arField)
		{
			echo '<tr>';

			echo '<td class="bx-field-name bx-padding">';
			echo $arField['name'], ': ';
			echo '</td>';

			echo '<td class="bx-field-value">';
			echo $arField['value'];
			echo '</td>';

			echo '</tr>';
		}
		unset($arField);
		echo '</tbody></table></div>';
	}

	public static function RenderEntityInfoItemTitle(&$item)
	{
		$titleData = isset($item['TITLE']) && is_array($item['TITLE']) ? $item['TITLE'] : null;
		if($titleData)
		{
			echo self::PrepareHtml($titleData), ': ';
		}
	}
	public static function RenderEntityInfoItemContent(&$item)
	{
		$itemType = isset($item['TYPE']) ? $item['TYPE'] : '';
		$itemParams = isset($item['PARAMS']) ? $item['PARAMS'] : null;
		if(is_array($itemParams))
		{
			if($itemType === 'LINK')
			{
				self::RenderFormLink($itemParams);
			}
			elseif($itemType === 'MONEY')
			{
				self::RenderFormMoney($itemParams);
			}
			elseif($itemType === 'PERCENTS')
			{
				self::RenderFormPercents($itemParams);
			}
			elseif($itemType === 'CLIENT_INFO')
			{
				self::RenderFormClientInfo($itemParams);
			}
			elseif($itemType === 'SELECT')
			{
				self::RenderFormSelect($itemParams);
			}
			elseif($itemType === 'RESPONSIBLE')
			{
				self::RenderFormResponsible($itemParams);
			}
			elseif($itemType === 'MULTI_FIELD')
			{
				self::RenderFormMultiFields($itemParams);
			}
			elseif($itemType === 'LHE' || $itemType === 'TEXT_AREA')
			{
				if($itemType === 'LHE')
				{
					CCrmInstantEditorHelper::RenderHtmlEditor($itemParams);
				}
				else
				{
					CCrmInstantEditorHelper::RenderTextArea($itemParams);
				}
			}
		}
		else
		{
			$contentData = isset($item['CONTENT']) && is_array($item['CONTENT']) ? $item['CONTENT'] : null;
			if($contentData)
			{
				if(isset($contentData['HTML']))
				{
					echo $contentData['HTML'];
				}
				elseif(isset($contentData['TEXT']))
				{
					echo htmlspecialcharsbx($contentData['TEXT']);
				}
			}

			$scriptData = isset($item['SCRIPT'])  ? $item['SCRIPT'] : null;
			if($scriptData !== '')
			{
				echo '<script type="text/javascript">';
				echo $scriptData;
				echo '</script>';
			}
		}
	}
	public static function RenderUserCustomSearch($arParams)
	{
		if(!is_array($arParams))
		{
			return;
		}

		CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');

		$ID = isset($arParams['ID']) ? strval($arParams['ID']) : '';
		$searchInputID = isset($arParams['SEARCH_INPUT_ID']) ? strval($arParams['SEARCH_INPUT_ID']) : '';
		$searchInputName = isset($arParams['SEARCH_INPUT_NAME']) ? strval($arParams['SEARCH_INPUT_NAME']) : '';
		if($searchInputName === '')
		{
			$searchInputName = $searchInputID;
		}

		$dataInputID = isset($arParams['DATA_INPUT_ID']) ? strval($arParams['DATA_INPUT_ID']) : '';
		$dataInputName = isset($arParams['DATA_INPUT_NAME']) ? strval($arParams['DATA_INPUT_NAME']) : '';
		if($dataInputName === '')
		{
			$dataInputName = $dataInputID;
		}

		$componentName = isset($arParams['COMPONENT_NAME']) ? strval($arParams['COMPONENT_NAME']) : '';

		$siteID = isset($arParams['SITE_ID']) ? strval($arParams['SITE_ID']) : '';
		if($siteID === '')
		{
			$siteID = SITE_ID;
		}

		$nameFormat = isset($arParams['NAME_FORMAT']) ? strval($arParams['NAME_FORMAT']) : '';
		if($nameFormat === '')
		{
			$nameFormat = CSite::GetNameFormat(false);
		}

		$user = isset($arParams['USER']) && is_array($arParams['USER']) ? $arParams['USER'] : array();
		$zIndex = isset($arParams['ZINDEX']) ? intval($arParams['ZINDEX']) : 0;

		/*
		//new style with user clear support
		echo '<span class="webform-field webform-field-textbox webform-field-textbox-empty webform-field-textbox-clearable">',
			'<span class="webform-field-textbox-inner">',
			'<input type="text" class="webform-field-textbox" id="', htmlspecialcharsbx($searchInputID) ,'" name="', htmlspecialcharsbx($searchInputName), '">',
			'<a class="webform-field-textbox-clear" href="#"></a>',
			'</span></span>',
			'<input type="hidden" id="', htmlspecialcharsbx($dataInputID),'" name="', htmlspecialcharsbx($dataInputName), '" value="">';
		*/
		echo '<input type="text" id="', htmlspecialcharsbx($searchInputID) ,'" name="', htmlspecialcharsbx($searchInputName), '" style="width:200px;" >',
		'<input type="hidden" id="', htmlspecialcharsbx($dataInputID),'" name="', htmlspecialcharsbx($dataInputName), '" value="">';

		$delay = isset($arParams['DELAY']) ? intval($arParams['DELAY']) : 0;

		echo '<script type="text/javascript">',
		'BX.ready(function(){',
		'BX.CrmUserSearchPopup.deletePopup("', $ID, '");',
		'BX.CrmUserSearchPopup.create("', $ID, '", { searchInput: BX("', CUtil::JSEscape($searchInputID), '"), dataInput: BX("', CUtil::JSEscape($dataInputID),'"), componentName: "', CUtil::JSEscape($componentName),'", user: ', CUtil::PhpToJSObject(array_change_key_case($user, CASE_LOWER)) ,', zIndex: ', $zIndex,' }, ', $delay,');',
		'}); </script>';

		$GLOBALS['APPLICATION']->IncludeComponent(
			'bitrix:intranet.user.selector.new',
			'',
			array(
				'MULTIPLE' => 'N',
				'NAME' => $componentName,
				'INPUT_NAME' => $searchInputID,
				'SHOW_EXTRANET_USERS' => 'NONE',
				'POPUP' => 'Y',
				'SITE_ID' => $siteID,
				'NAME_TEMPLATE' => $nameFormat
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}
	public static function RenderUserSearch($ID, $searchInputID, $dataInputID, $componentName, $siteID = '', $nameFormat = '', $delay = 0)
	{
		CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/common.js');

		$ID = strval($ID);
		$searchInputID = strval($searchInputID);
		$dataInputID = strval($dataInputID);
		$componentName = strval($componentName);

		$siteID = strval($siteID);
		if($siteID === '')
		{
			$siteID = SITE_ID;
		}

		$nameFormat = strval($nameFormat);
		if($nameFormat === '')
		{
			$nameFormat = CSite::GetNameFormat(false);
		}

		$delay = intval($delay);
		if($delay < 0)
		{
			$delay = 0;
		}

		echo '<input type="text" id="', htmlspecialcharsbx($searchInputID) ,'" style="width:200px;"   >',
		'<input type="hidden" id="', htmlspecialcharsbx($dataInputID),'" name="', htmlspecialcharsbx($dataInputID),'" value="">';

		echo '<script type="text/javascript">',
			'BX.ready(function(){',
			'BX.CrmUserSearchPopup.deletePopup("', $ID, '");',
			'BX.CrmUserSearchPopup.create("', $ID, '", { searchInput: BX("', CUtil::JSEscape($searchInputID), '"), dataInput: BX("', CUtil::JSEscape($dataInputID),'"), componentName: "', CUtil::JSEscape($componentName),'", user: {} }, ', $delay,');',
			'});</script>';

		$GLOBALS['APPLICATION']->IncludeComponent(
			'bitrix:intranet.user.selector.new',
			'',
			array(
				'MULTIPLE' => 'N',
				'NAME' => $componentName,
				'INPUT_NAME' => $searchInputID,
				'SHOW_EXTRANET_USERS' => 'NONE',
				'POPUP' => 'Y',
				'SITE_ID' => $siteID,
				'NAME_TEMPLATE' => $nameFormat
			),
			null,
			array('HIDE_ICONS' => 'Y')
		);
	}
	public static function RenderFiles($fileIDs, $fileUrlTemplate = '', $fileMaxWidth = 0, $fileMaxHeight = 0)
	{
		if(!is_array($fileIDs))
		{
			return 0;
		}
		$fileUrlTemplate = strval($fileUrlTemplate);
		$fileMaxWidth = intval($fileMaxWidth);
		if($fileMaxWidth <= 0)
		{
			$fileMaxWidth = 350;
		}
		$fileMaxHeight = intval($fileMaxHeight);
		if($fileMaxHeight <= 350)
		{
			$fileMaxHeight = 350;
		}

		$processed = 0;
		foreach($fileIDs as $fileID)
		{
			$fileInfo = CFile::GetFileArray($fileID);
			if (!is_array($fileInfo))
			{
				continue;
			}

			if($processed > 0)
			{
				echo '<span class="bx-br-separator"><br/></span>';
			}

			echo '<span class="fields files">';

			$fileInfo['name'] = $fileInfo['ORIGINAL_NAME'];

			if (CFile::IsImage($fileInfo['ORIGINAL_NAME'], $fileInfo['CONTENT_TYPE'])
				&& isset($fileInfo['WIDTH']) && intval($fileInfo['WIDTH']) <= $fileMaxWidth
				&& isset($fileInfo['HEIGHT']) && intval($fileInfo['HEIGHT']) <= $fileMaxHeight)
			{
				echo CFile::ShowImage($fileInfo, $fileMaxWidth, $fileMaxHeight, '', '', true, false, 0, 0, $fileUrlTemplate);
			}
			else
			{
				echo '<span class="crm-entity-file-info"><a target="_blank" class="crm-entity-file-link" href="',
					htmlspecialcharsbx(
						CComponentEngine::MakePathFromTemplate(
							$fileUrlTemplate,
							array('file_id' => $fileInfo['ID'])
						)
					), '">',
					htmlspecialcharsbx($fileInfo['ORIGINAL_NAME']).'</a><span class="crm-entity-file-size">',
					CFile::FormatSize($fileInfo['FILE_SIZE']).'</span></span>';
			}

			echo '</span>';
			$processed++;
		}

		return $processed;
	}
	public static function RenderDealStageSettings()
	{
		if(!self::$DEAL_STAGES)
		{
			self::$DEAL_STAGES = CCrmStatus::GetStatus('DEAL_STAGE');
		}

		$result = array();
		$isTresholdPassed = false;
		foreach(self::$DEAL_STAGES as &$stage)
		{
			$info = array(
				'id' => $stage['STATUS_ID'],
				'name' => $stage['NAME'],
				'sort' => intval($stage['SORT'])
			);

			if($stage['STATUS_ID'] === 'WON')
			{
				$isTresholdPassed = true;
				$info['semantics'] = 'success';
				$info['hint'] = GetMessage('CRM_DEAL_STAGE_MANAGER_WON_STEP_HINT');
			}
			elseif($stage['STATUS_ID'] === 'LOSE')
			{
				$info['semantics'] = 'failure';
			}
			elseif(!$isTresholdPassed)
			{
				$info['semantics'] = 'process';
			}
			else
			{
				$info['semantics'] = 'apology';
			}
			$result[] = $info;
		}
		unset($stage);

		$messages = array(
			'dialogTitle' => GetMessage('CRM_DEAL_STAGE_MANAGER_DLG_TTL'),
			'apologyTitle' => GetMessage('CRM_DEAL_STAGE_MANAGER_APOLOGY_TTL')
		);

		return '<script type="text/javascript">'
		.'BX.ready(function(){ BX.CrmDealStageManager.infos = '.CUtil::PhpToJSObject($result).'; BX.CrmDealStageManager.messages = '.CUtil::PhpToJSObject($messages).'; });'
		.'</script>';
	}
	public static function RenderLeadStatusSettings()
	{
		if(!self::$LEAD_STATUSES)
		{
			self::$LEAD_STATUSES = CCrmStatus::GetStatus('STATUS');
		}

		$result = array();
		$isTresholdPassed = false;
		foreach(self::$LEAD_STATUSES as &$status)
		{
			$info = array(
				'id' => $status['STATUS_ID'],
				'name' => $status['NAME'],
				'sort' => intval($status['SORT'])
			);

			if($status['STATUS_ID'] === 'CONVERTED')
			{
				$isTresholdPassed = true;
				$info['semantics'] = 'success';
				$info['name'] = GetMessage('CRM_LEAD_STATUS_MANAGER_CONVERTED_STEP_NAME');
				$info['hint'] = GetMessage('CRM_LEAD_STATUS_MANAGER_CONVERTED_STEP_HINT');
				$info['isFrozen'] = true;
			}
			elseif($status['STATUS_ID'] === 'JUNK')
			{
				$info['semantics'] = 'failure';
			}
			elseif(!$isTresholdPassed)
			{
				$info['semantics'] = 'process';
			}
			else
			{
				$info['semantics'] = 'apology';
			}
			$result[] = $info;
		}
		unset($status);

		$messages = array(
			'dialogTitle' => GetMessage('CRM_LEAD_STATUS_MANAGER_DLG_TTL'),
			'apologyTitle' => GetMessage('CRM_LEAD_STATUS_MANAGER_APOLOGY_TTL')
		);

		return '<script type="text/javascript">'
		.'BX.ready(function(){ BX.CrmLeadStatusManager.infos = '.CUtil::PhpToJSObject($result).'; BX.CrmLeadStatusManager.messages = '.CUtil::PhpToJSObject($messages).'; });'
		.'</script>';
	}
	public static function RenderDealStageControl($arParams)
	{
		if(!is_array($arParams))
		{
			$arParams = array();
		}

		if(!self::$DEAL_STAGES)
		{
			self::$DEAL_STAGES = CCrmStatus::GetStatus('DEAL_STAGE');
		}
		$arParams['INFOS'] = self::$DEAL_STAGES;
		$arParams['FINAL_ID'] = 'WON';
		$arParams['ENTITY_TYPE_NAME'] = CCrmOwnerType::ResolveName(CCrmOwnerType::Deal);
		return self::RenderProgressControl($arParams);
	}
	public static function RenderLeadStatusControl($arParams)
	{
		if(!is_array($arParams))
		{
			$arParams = array();
		}

		if(!self::$LEAD_STATUSES)
		{
			self::$LEAD_STATUSES = CCrmStatus::GetStatus('STATUS');
		}
		$arParams['INFOS'] = self::$LEAD_STATUSES;
		$arParams['FINAL_ID'] = 'CONVERTED';
		$arParams['FINAL_URL'] = isset($arParams['LEAD_CONVERT_URL']) ? $arParams['LEAD_CONVERT_URL'] : '';
		$arParams['ENTITY_TYPE_NAME'] = CCrmOwnerType::ResolveName(CCrmOwnerType::Lead);
		return self::RenderProgressControl($arParams);
	}
	public static function RenderProgressControl($arParams)
	{
		if(!is_array($arParams))
		{
			return '';
		}

		CCrmComponentHelper::RegisterScriptLink('/bitrix/js/crm/progress_control.js');

		$entityTypeName = isset($arParams['ENTITY_TYPE_NAME']) ? $arParams['ENTITY_TYPE_NAME'] : '';
		$leadTypeName = CCrmOwnerType::ResolveName(CCrmOwnerType::Lead);
		$dealTypeName = CCrmOwnerType::ResolveName(CCrmOwnerType::Deal);

		$infos = isset($arParams['INFOS']) ? $arParams['INFOS'] : null;
		if(!is_array($infos) || empty($infos))
		{
			if($entityTypeName === $leadTypeName)
			{
				if(!self::$LEAD_STATUSES)
				{
					self::$LEAD_STATUSES = CCrmStatus::GetStatus('STATUS');
				}
				$infos = self::$LEAD_STATUSES;
			}
			elseif($entityTypeName === $dealTypeName)
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
			return '';
		}

		$registerSettings = isset($arParams['REGISTER_SETTINGS']) && is_bool($arParams['REGISTER_SETTINGS'])
			? $arParams['REGISTER_SETTINGS'] : false;

		$registrationScript = '';
		if($registerSettings)
		{
			if($entityTypeName === $leadTypeName)
			{
				$registrationScript = self::RenderLeadStatusSettings();
			}
			elseif($entityTypeName === $dealTypeName)
			{
				$registrationScript = self::RenderDealStageSettings();
			}
		}

		$finalID = isset($arParams['FINAL_ID']) ? $arParams['FINAL_ID'] : '';
		if($finalID === '')
		{
			if($entityTypeName === $leadTypeName)
			{
				$finalID = 'CONVERTED';
			}
			elseif($entityTypeName === $dealTypeName)
			{
				$finalID = 'WON';
			}
		}

		$finalUrl = isset($arParams['FINAL_URL']) ? $arParams['FINAL_URL'] : '';
		if($finalUrl === '' && $entityTypeName === $leadTypeName)
		{
			$finalUrl = isset($arParams['LEAD_CONVERT_URL']) ? $arParams['LEAD_CONVERT_URL'] : '';
		}

		$currentInfo = null;
		$currentID = isset($arParams['CURRENT_ID']) ? $arParams['CURRENT_ID'] : '';
		if($currentID !== '' && isset($infos[$currentID]))
		{
			$currentInfo = $infos[$currentID];
		}
		$currentSort = is_array($currentInfo) && isset($currentInfo['SORT']) ? intval($currentInfo['SORT']) : -1;

		$finalInfo = null;
		if($finalID !== '' && isset($infos[$finalID]))
		{
			$finalInfo = $infos[$finalID];
		}
		$finalSort = is_array($finalInfo) && isset($finalInfo['SORT']) ? intval($finalInfo['SORT']) : -1;

		$isSuccessful = $currentSort === $finalSort;
		$isFailed = $currentSort > $finalSort;

		$stepHtml = '';
		foreach($infos as &$info)
		{
			$ID = isset($info['STATUS_ID']) ? $info['STATUS_ID'] : '';

			$sort = isset($info['SORT']) ? intval($info['SORT']) : 0;
			if($sort > $finalSort)
			{
				break;
			}

			$stepHtml .= '<td class="crm-list-stage-bar-part';
			if($sort <= $currentSort)
			{
				$stepHtml .= ' crm-list-stage-passed';
			}
			$stepHtml .= '"><div class="crm-list-stage-bar-block  crm-stage-'.htmlspecialcharsbx(strtolower($ID)).'"><div class="crm-list-stage-bar-btn"></div></div></td>';
		}
		unset($info);

		$wrapperClass = '';
		if($isSuccessful)
		{
			$wrapperClass = ' crm-list-stage-end-good';
		}
		elseif($isFailed)
		{
			$wrapperClass =' crm-list-stage-end-bad';
		}

		$prefix = isset($arParams['PREFIX']) ? $arParams['PREFIX'] : '';
		$entityID = isset($arParams['ENTITY_ID']) ? intval($arParams['ENTITY_ID']) : 0;

		$controlID = $entityTypeName !== '' && $entityID > 0
			? "{$prefix}{$entityTypeName}_{$entityID}" : uniqid($prefix);

		return $registrationScript.'<div class="crm-list-stage-bar'.$wrapperClass.'" id="'.htmlspecialcharsbx($controlID).'"><table class="crm-list-stage-bar-table"><tr>'
			.$stepHtml
			.'</tr></table>'
			.'<script type="text/javascript">BX.ready(function(){ BX.CrmProgressControl.create("'
			.CUtil::JSEscape($controlID).'"'
			.', BX.CrmParamBag.create({"containerId": "'.CUtil::JSEscape($controlID).'"'
			.', "entityType":"'.CUtil::JSEscape($entityTypeName).'"'
			.', "entityId":"'.CUtil::JSEscape($entityID).'"'
			.', "serviceUrl":"'.(isset($arParams['SERVICE_URL']) ? CUtil::JSEscape($arParams['SERVICE_URL']) : '').'"'
			.', "finalUrl":"'.(isset($arParams['FINAL_URL']) ? CUtil::JSEscape($arParams['FINAL_URL']) : '').'"'
			.', "currentStepId":"'.CUtil::JSEscape($currentID).'"'
			.' }));});</script>'
			.'</div>';
	}
}
