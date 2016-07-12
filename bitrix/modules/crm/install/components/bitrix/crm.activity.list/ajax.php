<?
define('NO_KEEP_STATISTIC', 'Y');
define('NO_AGENT_STATISTIC','Y');
define('NO_AGENT_CHECK', true);
define('DisableEventsCheck', true);

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_before.php');

if (!CModule::IncludeModule('crm'))
{
	return;
}

IncludeModuleLangFile(__FILE__);

/*
 * ONLY 'POST' METHOD SUPPORTED
 * SUPPORTED ACTIONS:
 * 'SAVE_ACTIVITY' - save activity (CALL, MEETING)
 * 'SAVE_EMAIL'
 * 'SET_NOTIFY' - change notification settings
 * 'SET_PRIORITY'
 * 'COMPLETE' - mark activity as completed
 * 'DELETE' - delete activity
 * 'GET_ENTITY_COMMUNICATIONS' - get entity communications
 * 'GET_TASK'
 * 'SEARCH_COMMUNICATIONS'
 * 'GET_ACTIVITIES'
 */

global $DB, $USER, $APPLICATION;

if (!$USER->IsAuthorized() || !check_bitrix_sessid() || $_SERVER['REQUEST_METHOD'] != 'POST')
{
	return;
}

__IncludeLang(dirname(__FILE__).'/lang/'.LANGUAGE_ID.'/'.basename(__FILE__));
CUtil::JSPostUnescape();
$GLOBALS['APPLICATION']->RestartBuffer();
Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
$action = isset($_POST['ACTION']) ? $_POST['ACTION'] : '';
if(strlen($action) == 0)
{
	echo CUtil::PhpToJSObject(
		array('ERROR' => 'Invalid data!')
	);
	die();
}

if($action == 'DELETE')
{
	$ID = isset($_POST['ITEM_ID']) ? intval($_POST['ITEM_ID']) : 0;

	if($ID <= 0)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid parameters!')
		);
		die();
	}

	$arActivity = CCrmActivity::GetByID($ID);
	if(!$arActivity)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Activity not found!')
		);
		die();
	}

	$ownerTypeName = isset($_POST['OWNER_TYPE']) ? strtoupper(strval($_POST['OWNER_TYPE'])) : '';
	if(!isset($ownerTypeName[0]))
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	$ownerID = isset($_POST['OWNER_ID']) ? intval($_POST['OWNER_ID']) : 0;
	if($ownerID <= 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	if(!CCrmActivity::CheckUpdatePermission(CCrmOwnerType::ResolveID($ownerTypeName), $ownerID))
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}

	if(CCrmActivity::Delete($ID))
	{
		echo CUtil::PhpToJsObject(
			array('DELETED_ITEM_ID'=> $ID)
		);
	}
	else
	{
		echo CUtil::PhpToJsObject(
			array('ERROR'=> "Could not delete activity ('$ID')!")
		);
	}
}
elseif($action == 'COMPLETE')
{
	$ID = isset($_POST['ITEM_ID']) ? intval($_POST['ITEM_ID']) : 0;

	if($ID <= 0)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data!')
		);
		die();
	}

	$arActivity = CCrmActivity::GetByID($ID);
	if(!$arActivity)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Activity not found!')
		);
		die();
	}

	$ownerTypeName = isset($_POST['OWNER_TYPE']) ? strtoupper(strval($_POST['OWNER_TYPE'])) : '';
	if(!isset($ownerTypeName[0]))
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	$ownerID = isset($_POST['OWNER_ID']) ? intval($_POST['OWNER_ID']) : 0;
	if($ownerID <= 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	if(!CCrmActivity::CheckUpdatePermission(CCrmOwnerType::ResolveID($ownerTypeName), $ownerID))
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}


	$completed = (isset($_POST['COMPLETED']) ? intval($_POST['COMPLETED']) : 0) > 0;

	if(CCrmActivity::Complete($ID, $completed))
	{
		echo CUtil::PhpToJsObject(
			array(
				'ITEM_ID'=> $ID,
				'COMPLETED'=> $completed
			)
		);
	}
	else
	{
		$errorMsg = CCrmActivity::GetLastErroMessage();
		if(!isset($errorMsg[0]))
		{
			$errorMsg = "Could not complete activity ('$ID')!";
		}
		echo CUtil::PhpToJsObject(
			array(
				'ERROR' => $errorMsg
			)
		);
	}
}
elseif($action == 'SET_PRIORITY')
{
	$ID = isset($_POST['ITEM_ID']) ? intval($_POST['ITEM_ID']) : 0;

	if($ID <= 0)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data!')
		);
		die();
	}

	$arActivity = CCrmActivity::GetByID($ID);
	if(!$arActivity)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Activity not found!')
		);
		die();
	}

	$ownerTypeName = isset($_POST['OWNER_TYPE']) ? strtoupper(strval($_POST['OWNER_TYPE'])) : '';
	if(!isset($ownerTypeName[0]))
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	$ownerID = isset($_POST['OWNER_ID']) ? intval($_POST['OWNER_ID']) : 0;
	if($ownerID <= 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER ID IS NOT DEFINED!'));
		die();
	}

	if(!CCrmActivity::CheckUpdatePermission(CCrmOwnerType::ResolveID($ownerTypeName), $ownerID))
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}

	$priority = isset($_POST['PRIORITY']) ? intval($_POST['PRIORITY']) : CCrmActivityPriority::Medium;

	if(CCrmActivity::SetPriority($ID, $priority))
	{
		echo CUtil::PhpToJsObject(
			array(
				'ITEM_ID'=> $ID,
				'PRIORITY'=> $priority
			)
		);
	}
	else
	{
		$errorMsg = CCrmActivity::GetLastErroMessage();
		if(!isset($errorMsg[0]))
		{
			$errorMsg = "Could not change priority!";
		}
		echo CUtil::PhpToJsObject(
			array(
				'ERROR' => $errorMsg
			)
		);
	}
}
elseif($action == 'SAVE_ACTIVITY')
{
	$siteID = !empty($_REQUEST['siteID']) ? $_REQUEST['siteID'] : SITE_ID;

	if (!CModule::IncludeModule('calendar'))
	{
		echo CUtil::PhpToJSObject(array('ERROR' => 'Could not load module "calendar"!'));
		die();
	}

	$data = isset($_POST['DATA']) && is_array($_POST['DATA']) ? $_POST['DATA'] : array();
	if(count($data) == 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'SOURCE DATA ARE NOT FOUND!'));
		die();
	}

	$ID = isset($data['ID']) ? intval($data['ID']) : 0;
	$typeID = isset($data['type']) ? intval($data['type']) : CCrmActivityType::Activity;

	$arActivity = null;
	if($ID > 0)
	{
		$arActivity = CCrmActivity::GetByID($ID);
		if(!$arActivity)
		{
			echo CUtil::PhpToJSObject(array('ERROR'=>'IS NOT EXISTS!'));
			die();
		}
	}

	$ownerTypeName = isset($data['ownerType']) ? strtoupper(strval($data['ownerType'])) : '';
	if(!isset($ownerTypeName[0]))
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	$ownerTypeID = CCrmOwnerType::ResolveID($ownerTypeName);

	$ownerID = isset($data['ownerID']) ? intval($data['ownerID']) : 0;
	if($ownerID <= 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER ID IS NOT DEFINED!'));
		die();
	}

	if(!CCrmActivity::CheckUpdatePermission($ownerTypeID, $ownerID))
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}

	$userID = $USER->GetID();
	if($userID <= 0)
	{
		$arFilter = array('ID' => $ownerID);
		switch ($ownerTypeName)
		{
			case 'CONTACT':
				$obRes = CCrmContact::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'ASSIGNED_BY_ID'));
				break;
			case 'DEAL':
				$obRes = CCrmDeal::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'ASSIGNED_BY_ID'));
				break;
			case 'COMPANY':
				$obRes = CCrmCompany::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'CREATED_BY_ID'));
				break;
			case 'LEAD':
				$obRes = CCrmLead::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'ASSIGNED_BY_ID'));
				break;
			default:
				{
					echo CUtil::PhpToJSObject(array('ERROR'=>"OWNER TYPE '$ownerTypeName' IS NOT SUPPORTED!"));
					die();
				}
		}

		if($arRow = $obRes->Fetch())
		{
			$userID = intval(isset($arRow['ASSIGNED_BY_ID']) ? $arRow['ASSIGNED_BY_ID'] : $arRow['CREATED_BY_ID']);
		}
	}

	if($userID <= 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>GetMessage('CRM_ACTIVITY_RESPONSIBLE_NOT_FOUND')));
		die();
	}

	$now = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL', $siteID);

	$subject = isset($data['subject']) ? strval($data['subject']) : '';
	if($subject === '')
	{
		$msgID = 'CRM_ACTION_DEFAULT_SUBJECT';
		if($typeID === CCrmActivityType::Call)
		{
			$msgID = 'CRM_CALL_ACTION_DEFAULT_SUBJECT';
		}
		elseif($typeID === CCrmActivityType::Meeting)
		{
			$msgID = 'CRM_MEETING_ACTION_DEFAULT_SUBJECT';
		}

		$subject = GetMessage($msgID, array('#DATE#'=> $now));
	}

	$start = isset($data['start']) ? strval($data['start']) : '';

	if($start === '' || !$DB->IsDate($start, false, $siteID, 'FULL'))
	{
		$start =  $now;
	}

	$end = $start; //by default
	$descr = isset($data['description']) ? strval($data['description']) : '';
	$priority = isset($data['priority']) ? intval($data['priority']) : CCrmActivityPriority::Medium;
	$location = isset($data['location']) ? strval($data['location']) : '';

	$arCalEvantFields = array(
		'CAL_TYPE' => 'user',
		'OWNER_ID' => $userID,
		'NAME' => $subject,
		'DT_FROM' => $start,
		'DT_TO' => $end,
		'IMPORTANCE' => CCrmActivityPriority::ToCalendarEventImportance($priority),
		'DESCRIPTION' => $descr,
		'LOCATION' => $location
	);

	if($arActivity && isset($arActivity['ASSOCIATED_ENTITY_ID']))
	{
		$arCalEvantFields['ID'] = intval($arActivity['ASSOCIATED_ENTITY_ID']);
	}

	$notify = isset($data['notify']) ? $data['notify'] : false;
	if(is_array($notify))
	{
		$arCalEvantFields['REMIND'] = array(
				array(
					'type' => CCrmActivityNotifyType::ToCalendarEventRemind(isset($notify['type']) ? $notify['type'] : CCrmActivityNotifyType::Min),
					'count' => isset($notify['value']) ? intval($notify['value']) : 15
				)
		);
	}

	$eventID = intval(CCalendar::SaveEvent(
		array(
			'arFields' => $arCalEvantFields,
			'userId' => $userID,
			'autoDetectSection' => true,
			'autoCreateSection' => true
		)
	));

	if ($eventID > 0)
	{
		CCalendarEvent::UpdateUserFields(
			$eventID,
			array(
				'UF_CRM_CAL_EVENT' => array(CUserTypeCrm::GetShortEntityType($ownerTypeName).'_'.$ownerID)
			)
		);
	}
	else
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'COULD NOT SAVE CALENDAR EVENT'));
		die();
	}

	$arFields = array(
		//'OWNER_ID' => $ownerID,
		//'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($ownerTypeName),
		'TYPE_ID' =>  $typeID,
		'ASSOCIATED_ENTITY_ID' => $eventID,
		'SUBJECT' => $subject,
		'START_TIME' => $start,
		'END_TIME' => $end,
		'COMPLETED' => isset($data['completed']) ? (intval($data['completed']) > 0 ? 'Y' : 'N') : 'N',
		//'RESPONSIBLE_ID' => $userID,
		'PRIORITY' => $priority,
		'DESCRIPTION' => $descr,
		'LOCATION' => $location,
		'NOTIFY_TYPE' => CCrmActivityNotifyType::None,
		'SETTINGS' => array()
	);

	$arFields['DIRECTION'] =
		$typeID === CCrmActivityType::Call
			? (isset($data['direction']) ? intval($data['direction']) : CCrmActivityDirection::Outgoing)
			: CCrmActivityDirection::Undefined;

	$arBindings = array(
		"{$ownerTypeName}_{$ownerID}" => array(
			'OWNER_TYPE_ID' => $ownerTypeID,
			'OWNER_ID' => $ownerID
		)
	);

	if(is_array($notify))
	{
		$arFields['NOTIFY_TYPE'] = isset($notify['type']) ? intval($notify['type']) : CCrmActivityNotifyType::Min;
		$arFields['NOTIFY_VALUE'] = isset($notify['value']) ? intval($notify['value']) : 15;
	}

	// Communications
	$commData = isset($data['communication']) ? $data['communication'] : array();

	$commID = isset($commData['id']) ? intval($commData['id']) : 0;
	$commEntityType = isset($commData['entityType']) ? strtoupper(strval($commData['entityType'])) : '';
	$commEntityID = isset($commData['entityId']) ? intval($commData['entityId']) : 0;

	$commType = isset($commData['type']) ? strtoupper(strval($commData['type'])) : '';
	$commValue = isset($commData['value']) ? strval($commData['value']) : '';

	$arComms = array();

	if($commEntityID <= 0 && $commType === 'PHONE' && $ownerTypeName !== 'DEAL')
	{
		// Communication entity ID is 0 (processing of new communications)
		// Communication type must present it determines TYPE_ID (is only 'PHONE' in current context)
		// Deal does not have multi fields.

		$fieldMulti = new CCrmFieldMulti();
		$arFieldMulti = array(
			'ENTITY_ID' => $ownerTypeName,
			'ELEMENT_ID' => $ownerID,
			'TYPE_ID' => 'PHONE',
			'VALUE_TYPE' => 'WORK',
			'VALUE' => $commValue
		);

		$fieldMultiID = $fieldMulti->Add($arFieldMulti);
		if($fieldMultiID > 0)
		{
			$commEntityType = $ownerTypeName;
			$commEntityID = $ownerID;
		}
	}

	if($commEntityType !== '')
	{
		$arComms[] = array(
			'ID' => $commID,
			'TYPE' => $commType,
			'VALUE' => $commValue,
			'ENTITY_ID' => $commEntityID,
			'ENTITY_TYPE_ID' => CCrmOwnerType::ResolveID($commEntityType)
		);

		$bindingKey = $commEntityID > 0 ? "{$commEntityType}_{$commEntityID}" : uniqid("{$commEntityType}_");
		if(!isset($arBindings[$bindingKey]))
		{
			$arBindings[$bindingKey] = array(
				'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($commEntityType),
				'OWNER_ID' => $commEntityID
			);
		}
	}

	$isNew = $ID <= 0;
	if($isNew)
	{
		$arFields['OWNER_ID'] = $ownerID;
		$arFields['OWNER_TYPE_ID'] = $ownerTypeID;
		$arFields['RESPONSIBLE_ID'] = $userID;
		$arFields['BINDINGS'] = array_values($arBindings);

		if(!($ID = CCrmActivity::Add($arFields)))
		{
			echo CUtil::PhpToJSObject(array('ERROR' => CCrmActivity::GetLastErroMessage()));
			die();
		}
	}
	else
	{
		$dbRes = CCrmActivity::GetList(array(), array('ID'=>$ID), false, false, array('OWNER_ID', 'OWNER_TYPE_ID'));
		$arRes = $dbRes->Fetch();
		if(!$arRes)
		{
			echo CUtil::PhpToJSObject(array('ERROR' => 'COULD NOT FIND ACTIVITY'));
			die();
		}

		$primaryOwnerTypeID = intval($arRes['OWNER_TYPE_ID']);
		$primaryOwnerID = intval($arRes['OWNER_ID']);

		if($primaryOwnerTypeID !== $ownerTypeID || $primaryOwnerID !== $ownerID)
		{
			$primaryOwnerTypeName = CCrmOwnerType::ResolveName($primaryOwnerTypeID);
			$arBindings["{$primaryOwnerTypeName}_{$ownerID}"] = array(
				'OWNER_TYPE_ID' => $primaryOwnerTypeID,
				'OWNER_ID' => $primaryOwnerID
			);
		}

		$arFields['BINDINGS'] = array_values($arBindings);

		if(!CCrmActivity::Update($ID, $arFields))
		{
			echo CUtil::PhpToJSObject(array('ERROR' => CCrmActivity::GetLastErroMessage()));
			die();
		}
	}

	CCrmActivity::SaveCommunications($ID, $arComms, $arFields, !$isNew, false);

	$arPermittedFiles = array();
	$arUserFiles = isset($data['files']) && is_array($data['files']) ? $data['files'] : array();
	if(!empty($arUserFiles))
	{
		$arPreviousFiles = array();
		if(!$isNew)
		{
			$arPreviousFiles = CCrmActivity::GetFileIDs($ID);
			if(is_array($arPreviousFiles) && !empty($arPreviousFiles))
			{
				$arPermittedFiles = array_intersect($arUserFiles, $arPreviousFiles);
			}
		}

		$uploadControlCID = isset($data['uploadControlCID']) ? strval($data['uploadControlCID']) : '';
		if($uploadControlCID !== '' && isset($_SESSION["MFI_UPLOADED_FILES_{$uploadControlCID}"]))
		{
			$uploadedFiles = $_SESSION["MFI_UPLOADED_FILES_{$uploadControlCID}"];
			if(!empty($uploadedFiles))
			{
				$arPermittedFiles = array_merge(
					array_intersect($arUserFiles, $uploadedFiles),
					$arPermittedFiles
				);
			}
		}
	}

	if(!CCrmActivity::SetFileIDs($ID, $arPermittedFiles, $arFields, !$isNew, false))
	{
		echo CUtil::PhpToJSObject(array('ERROR' => CCrmActivity::GetLastErroMessage()));
		die();
	}

	$arFiles = array();
	foreach($arPermittedFiles as $fileID)
	{
		$arData = CFile::GetFileArray($fileID);
		if(is_array($arData))
		{
			$arFiles[] = array(
				'fileID' => $arData['ID'],
				'fileName' => $arData['FILE_NAME'],
				'fileURL' => $arData['SRC'],
				'fileSize' => $arData['FILE_SIZE']
			);
		}
	}

	$commData = array();
	$communications = CCrmActivity::GetCommunications($ID);
	foreach($communications as &$arComm)
	{
		CCrmActivity::PrepareCommunicationInfo($arComm);
		$commData[] = array(
			'type' => $arComm['TYPE'],
			'value' => $arComm['VALUE'],
			'entityId' => $arComm['ENTITY_ID'],
			'entityType' => CCrmOwnerType::ResolveName($arComm['ENTITY_TYPE_ID']),
			'entityTitle' => $arComm['TITLE'],
		);
	}
	unset($arComm);

	$userName = '';
	if($userID > 0)
	{
		$dbResUser = CUser::GetByID($userID);
		$userName = is_array(($user = $dbResUser->Fetch()))
			? CUser::FormatName(CSite::GetNameFormat(false), $user, true, false) : '';
	}

	$jsonFields = array(
		'ID' => $ID,
		'typeID' => $typeID,
		'subject' => $subject,
		'direction' => $arFields['DIRECTION'],
		'description' => $descr,
		'location' => $location,
		'start' => ConvertTimeStamp(MakeTimeStamp($start), 'FULL', $siteID),
		'end' => ConvertTimeStamp(MakeTimeStamp($end), 'FULL', $siteID),
		'completed' => isset($arFields['COMPLETED']) && $arFields['COMPLETED'] == 'Y',
		'notifyType' => isset($arFields['NOTIFY_TYPE']) ? intval($arFields['NOTIFY_TYPE']) : CCrmActivityNotifyType::None,
		'notifyValue' => isset($arFields['NOTIFY_VALUE']) ? intval($arFields['NOTIFY_VALUE']) : 0,
		'priority' => $priority,
		'responsibleName' => $userName,
		'files' => $arFiles,
		'communications' => $commData
	);

	echo CUtil::PhpToJSObject(
		array(
			'ACTIVITY' => $jsonFields
		)
	);
}
elseif($action == 'SAVE_EMAIL')
{
	if (!CModule::IncludeModule('subscribe'))
	{
		echo CUtil::PhpToJSObject(array('ERROR' => 'Could not load module "subscribe"!'));
		die();
	}

	$siteID = !empty($_REQUEST['siteID']) ? $_REQUEST['siteID'] : SITE_ID;

	$data = isset($_POST['DATA']) && is_array($_POST['DATA']) ? $_POST['DATA'] : array();
	if(count($data) == 0)
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'SOURCE DATA ARE NOT FOUND!'));
		die();
	}

	$ID = isset($data['ID']) ? intval($data['ID']) : 0;

	$ownerTypeName = isset($data['ownerType']) ? strtoupper(strval($data['ownerType'])) : '';
	if(!isset($ownerTypeName[0]))
	{
		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER TYPE IS NOT DEFINED!'));
		die();
	}

	$ownerID = isset($data['ownerID']) ? intval($data['ownerID']) : 0;
//	if($ownerID <= 0)
//	{
//		echo CUtil::PhpToJSObject(array('ERROR'=>'OWNER ID IS NOT DEFINED!'));
//		die();
//	}

	if(!CCrmActivity::CheckUpdatePermission(CCrmOwnerType::ResolveID($ownerTypeName), $ownerID))
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}

	$userID = $USER->GetID();
	if($userID <= 0)
	{
		$arFilter = array('ID' => $ownerID);
		switch ($ownerTypeName)
		{
			case 'CONTACT':
				$obRes = CCrmContact::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'ASSIGNED_BY_ID'));
				break;
			case 'DEAL':
				$obRes = CCrmDeal::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'ASSIGNED_BY_ID'));
				break;
			case 'COMPANY':
				$obRes = CCrmCompany::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'CREATED_BY_ID'));
				break;
			case 'LEAD':
				$obRes = CCrmLead::GetList(array('ID' => 'ASC'), $arFilter, array('ID', 'ASSIGNED_BY_ID'));
				break;
			default:
				{
				echo CUtil::PhpToJSObject(array('ERROR'=>"OWNER TYPE '$ownerTypeName' IS NOT SUPPORTED!"));
				die();
				}
		}

		if($arRow = $obRes->Fetch())
		{
			$userID = intval(isset($arRow['ASSIGNED_BY_ID']) ? $arRow['ASSIGNED_BY_ID'] : $arRow['CREATED_BY_ID']);
		}
	}

	$arErrors = array();

	if($userID <= 0)
	{
		$arErrors[] = GetMessage('CRM_ACTIVITY_RESPONSIBLE_NOT_FOUND');
	}

	$from = isset($data['from']) ? strval($data['from']) : '';
	if($from === '')
	{
		$arErrors[] = GetMessage('CRM_ACTIVITY_EMAIL_EMPTY_FROM_FIELD');
	}
	elseif(!check_email($from))
	{
		$arErrors[] = GetMessage('CRM_ACTIVITY_INVALID_EMAIL', array('#VALUE#' => $from));
	}

	$subject = isset($data['subject']) ? strval($data['subject']) : '';
	$message = isset($data['message']) ? strval($data['message']) : '';

	// Creating Email -->
	$to = array();
	$commData = isset($data['communications']) ? $data['communications'] : array();
	foreach($commData as &$commDatum)
	{
		$commType = isset($commDatum['type']) ? strtoupper(strval($commDatum['type'])) : '';
		$commValue = isset($commDatum['value']) ? strval($commDatum['value']) : '';

		if($commType !== 'EMAIL' || $commValue === '')
		{
			continue;
		}

		if(!check_email($commValue))
		{
			$arErrors[] = GetMessage('CRM_ACTIVITY_INVALID_EMAIL', array('#VALUE#' => $commValue));
			continue;
		}

		$to[] = $commValue;
	}
	unset($commDatum);

	if(count($to) == 0)
	{
		$arErrors[] = GetMessage('CRM_ACTIVITY_EMAIL_EMPTY_TO_FIELD');
	}

	if(!empty($arErrors))
	{
		echo CUtil::PhpToJSObject(array('ERROR' => $arErrors));
		die();
	}

	$posting = new CPosting();
	$postingID = $posting->Add(
		array(
			'STATUS' => 'D',
			'FROM_FIELD' => $from,
			'TO_FIELD' => $from,
			'BCC_FIELD' => implode(',', $to),
			'SUBJECT' => $subject,
			'BODY_TYPE' => 'html',
			'BODY' => $message,
			'DIRECT_SEND' => 'Y',
			'SUBSCR_FORMAT' => 'html',
			'CHARSET' => array_shift(explode(',', COption::GetOptionString('subscribe', 'posting_charset')))
		)
	);
	// <-- Creating Email

	$now = ConvertTimeStamp(time() + CTimeZone::GetOffset(), 'FULL', $siteID);
	if($subject === '')
	{
		$subject = GetMessage(
			'CRM_EMAIL_ACTION_DEFAULT_SUBJECT',
			array('#DATE#'=> $now)
		);
	}

	$description = preg_replace('/<(?:\/)?[a-z0-9]+[^>]*>/i', '', $message);
	$arFields = array(
		'OWNER_ID' => $ownerID,
		'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($ownerTypeName),
		'TYPE_ID' =>  CCrmActivityType::Email,
		'ASSOCIATED_ENTITY_ID' => $postingID,
		'SUBJECT' => $subject,
		'START_TIME' => $now,
		'END_TIME' => $now,
		'COMPLETED' => 'Y',
		'RESPONSIBLE_ID' => $userID,
		'PRIORITY' => CCrmActivityPriority::Medium,
		'DESCRIPTION' => $description,
		'DIRECTION' => CCrmActivityDirection::Outgoing,
		'LOCATION' => '',
		'NOTIFY_TYPE' => CCrmActivityNotifyType::None
	);

	$arBindings = array(
		"{$ownerTypeName}_{$ownerID}" => array(
			'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($ownerTypeName),
			'OWNER_ID' => $ownerID
		)
	);

	//Communications
	$arComms = array();
	foreach($commData as &$commDatum)
	{
		$commID = isset($commData['id']) ? intval($commData['id']) : 0;
		$commEntityType = isset($commDatum['entityType']) ? strtoupper(strval($commDatum['entityType'])) : '';
		$commEntityID = isset($commDatum['entityId']) ? intval($commDatum['entityId']) : 0;

		$commType = isset($commDatum['type']) ? strtoupper(strval($commDatum['type'])) : '';
		if($commType === '')
		{
			$commType = 'EMAIL';
		}

		$commValue = isset($commDatum['value']) ? strval($commDatum['value']) : '';

		$arComms[] = array(
			'ID' => $commID,
			'TYPE' => $commType,
			'VALUE' => $commValue,
			'ENTITY_ID' => $commEntityID,
			'ENTITY_TYPE_ID' => CCrmOwnerType::ResolveID($commEntityType)
		);

		if($commEntityID <= 0 && $commType === 'EMAIL'&& $ownerTypeName !== 'DEAL')
		{
			// Communication entity ID is 0 (processing of new communications)
			// Communication type must present it determines TYPE_ID (is only 'EMAIL' in current context)
			// Deal does not have multi fields.

			$fieldMulti = new CCrmFieldMulti();
			$arFieldMulti = array(
				'ENTITY_ID' => $ownerTypeName,
				'ELEMENT_ID' => $ownerID,
				'TYPE_ID' => 'EMAIL',
				'VALUE_TYPE' => 'WORK',
				'VALUE' => $commValue
			);

			$fieldMultiID = $fieldMulti->Add($arFieldMulti);
			if($fieldMultiID > 0)
			{
				$commEntityType = $ownerTypeName;
				$commEntityID = $ownerID;
			}
		}

		if($commEntityType !== '')
		{
			$bindingKey = $commEntityID > 0 ? "{$commEntityType}_{$commEntityID}" : uniqid("{$commEntityType}_");
			if(!isset($arBindings[$bindingKey]))
			{
				$arBindings[$bindingKey] = array(
					'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($commEntityType),
					'OWNER_ID' => $commEntityID
				);
			}
		}
	}
	unset($commDatum);

	$arFields['BINDINGS'] = array_values($arBindings);

	if(count($arFields['BINDINGS']) === 1)
	{
		// In single bindind mode override owner data
		$arBinding = $arFields['BINDINGS'][0];
		$arFields['OWNER_TYPE_ID'] = $arBinding['OWNER_TYPE_ID'];
		$arFields['OWNER_ID'] = $arBinding['OWNER_ID'];
	}

	$isNew = $ID <= 0;
	if($isNew)
	{
		if(!($ID = CCrmActivity::Add($arFields)))
		{
			echo CUtil::PhpToJSObject(array('ERROR' => CCrmActivity::GetLastErroMessage()));
			die();
		}
	}
	else
	{
		if(!CCrmActivity::Update($ID, $arFields))
		{
			echo CUtil::PhpToJSObject(array('ERROR' => CCrmActivity::GetLastErroMessage()));
			die();
		}
	}

	CCrmActivity::SaveCommunications($ID, $arComms, $arFields, !$isNew, false);

	$arPermittedFiles = array();
	$arUserFiles = isset($data['files']) && is_array($data['files']) ? $data['files'] : array();
	if(!empty($arUserFiles))
	{
		$uploadControlCID = isset($data['uploadControlCID']) ? strval($data['uploadControlCID']) : '';
		if($uploadControlCID !== '' && isset($_SESSION["MFI_UPLOADED_FILES_{$uploadControlCID}"]))
		{
			$uploadedFiles = $_SESSION["MFI_UPLOADED_FILES_{$uploadControlCID}"];
			if(!empty($uploadedFiles))
			{
				$arPermittedFiles = array_intersect($arUserFiles, $uploadedFiles);
			}
		}
	}

	if(!CCrmActivity::SetFileIDs($ID, $arPermittedFiles, $arFields, !$isNew, false))
	{
		echo CUtil::PhpToJSObject(array('ERROR' => CCrmActivity::GetLastErroMessage()));
		die();
	}

	// Prepare files for email and event -->
	$arRawFiles = array();
	foreach($arPermittedFiles as $fileID)
	{
		$arRawFile = CFile::MakeFileArray($fileID);
		if(is_array($arRawFile))
		{
			$arRawFiles[] = $arRawFile;
		}
	}
	// <-- Prepare files for email and event

	// Attaching files -->
	foreach($arRawFiles as &$arRawFile)
	{
		$posting->SaveFile($postingID, $arRawFile);
	}
	unset($arRawFile);
	// <-- Attaching files

	// Sending Email -->
	$posting->ChangeStatus($postingID, 'P');
	if(($e = $APPLICATION->GetException()) == false)
	{
		$rsAgents = CAgent::GetList(
			array('ID'=>'DESC'),
			array(
				'MODULE_ID' => 'subscribe',
				'NAME' => 'CPosting::AutoSend('.$postingID.',%',
			)
		);

		if(!$rsAgents->Fetch())
		{
			CAgent::AddAgent('CPosting::AutoSend('.$postingID.',true);', 'subscribe', 'N', 0);
		}
	}

	// Try add event to entity
	$CCrmEvent = new CCrmEvent();

	$eventText  = '';
	$eventText .= GetMessage('CRM_TITLE_EMAIL_SUBJECT').': '.$subject."\n\r";
	$eventText .= GetMessage('CRM_TITLE_EMAIL_FROM').': '.$from."\n\r";
	$eventText .= GetMessage('CRM_TITLE_EMAIL_TO').': '.implode(',', $to)."\n\r\n\r";
	$eventText .= $message;

	$CCrmEvent->Add(
		array(
			'ENTITY' => array(
				$ownerID => array(
					'ENTITY_TYPE' => $ownerTypeName,
					'ENTITY_ID' => $ownerID
				)
			),
			'EVENT_ID' => 'MESSAGE',
			'EVENT_TEXT_1' => $eventText,
			'FILES' => $arRawFiles
		)
	);

	// <-- Sending Email

	$commData = array();
	$communications = CCrmActivity::GetCommunications($ID);
	foreach($communications as &$arComm)
	{
		CCrmActivity::PrepareCommunicationInfo($arComm);
		$commData[] = array(
			'type' => $arComm['TYPE'],
			'value' => $arComm['VALUE'],
			'entityId' => $arComm['ENTITY_ID'],
			'entityType' => CCrmOwnerType::ResolveName($arComm['ENTITY_TYPE_ID']),
			'entityTitle' => $arComm['TITLE'],
		);
	}
	unset($arComm);

	$arFiles = array();
	foreach($arPermittedFiles as $fileID)
	{
		$arData = CFile::GetFileArray($fileID);
		if(is_array($arData))
		{
			$arFiles[] = array(
				'fileID' => $arData['ID'],
				'fileName' => $arData['FILE_NAME'],
				'fileURL' => $arData['SRC'],
				'fileSize' => $arData['FILE_SIZE']
			);
		}
	}

	$userName = '';
	if($userID > 0)
	{
		$dbResUser = CUser::GetByID($userID);
		$userName = is_array(($user = $dbResUser->Fetch()))
			? CUser::FormatName(CSite::GetNameFormat(false), $user, true, false) : '';
	}

	echo CUtil::PhpToJSObject(
		array(
			'ACTIVITY' => array(
				'ID' => $ID,
				'typeID' => CCrmActivityType::Email,
				'subject' => $subject,
				'description' => $description,
				'location' => '',
				'start' => ConvertTimeStamp(MakeTimeStamp($now), 'FULL', $siteID),
				'end' => ConvertTimeStamp(MakeTimeStamp($now), 'FULL', $siteID),
				'completed' => true,
				'notifyType' => CCrmActivityNotifyType::None,
				'notifyValue' => 0,
				'priority' => CCrmActivityPriority::Medium,
				'responsibleName' => $userName,
				'files' => $arFiles,
				'communications' => $commData
			)
		)
	);
}
elseif($action == 'GET_ENTITY_COMMUNICATIONS')
{
	$fullNameFormat = CSite::GetNameFormat(false);

	$entityType = isset($_POST['ENTITY_TYPE']) ? strtoupper(strval($_POST['ENTITY_TYPE'])) : '';
	$entityID = isset($_POST['ENTITY_ID']) ? intval($_POST['ENTITY_ID']) : 0;

	if($entityType === '' || $entityID <= 0)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data')
		);
		die();
	}

	$communicationType = isset($_POST['COMMUNICATION_TYPE']) ? strval($_POST['COMMUNICATION_TYPE']) : '';

	if($entityType === 'LEAD')
	{
		$data = array(
			'ownerEntityType' => 'LEAD',
			'ownerEntityId' => $entityID,
			'entityType' => 'LEAD',
			'entityId' => $entityID,
			'entityTitle' => "{$entityType}_{$entityID}",
			'entityDescription' => '',
			'tabId' => 'main',
			'communications' => array()
		);

		$entity = CCrmLead::GetByID($entityID);
		if(!$entity)
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Invalid data')
			);
			die();
		}

		// Prepare title
		$data['entityTitle'] = CUser::FormatName($fullNameFormat,
			array(
				'LOGIN' => '',
				'NAME' => $entity['NAME'],
				'LAST_NAME' => $entity['LAST_NAME'],
				'SECOND_NAME' => $entity['SECOND_NAME']
			),
			false,
			false
		);

		$data['entityDescription'] = $entity['TITLE'];

		// Try to load entity communications
		if(!CCrmActivity::CheckReadPermission(CCrmOwnerType::ResolveID($entityType), $entityID))
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Permission denied!')
			);
			die();
		}

		if($communicationType !== '')
		{
			$dbResFields = CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array('ENTITY_ID' => $entityType, 'ELEMENT_ID' => $entityID, 'TYPE_ID' =>  $communicationType)
			);

			while($arField = $dbResFields->Fetch())
			{
				if(empty($arField['VALUE']))
				{
					continue;
				}

				$comm = array(
					'type' => $communicationType,
					'value' => $arField['VALUE']
				);

				$data['communications'][] = $comm;
			}
		}

		echo CUtil::PhpToJSObject(
			array(
				'DATA' => array(
					'TABS' => array(
						array(
							'id' => 'lead',
							'title' => GetMessage('CRM_COMMUNICATION_TAB_LEAD'),
							'active' => true,
							'items' => array($data)
						)
					)
				)
			)
		);

		die();
	}
	elseif($entityType === 'DEAL')
	{
		$data = array();

		$entity = CCrmDeal::GetByID($entityID);
		if(!$entity)
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Invalid data')
			);
			die();
		}

		$dealData = array();

		// Prepare company data

		$entityCompanyData = null;

		$entityCompanyID =  isset($entity['COMPANY_ID']) ? intval($entity['COMPANY_ID']) : 0;
		$entityCompany = $entityCompanyID > 0 ? CCrmCompany::GetByID($entityCompanyID) : null;

		if($entityCompany > 0)
		{
			$entityCompanyData = array(
				'ownerEntityType' => 'DEAL',
				'ownerEntityId' => $entityID,
				'entityType' => 'COMPANY',
				'entityId' => $entityCompanyID,
				'entityTitle' => isset($entityCompany['TITLE']) ? $entityCompany['TITLE'] : '',
				'entityDescription' => '',
				'communications' => array()
			);

			if($communicationType !== '')
			{
				$entityCompanyComms = CCrmActivity::PrepareCommunications('COMPANY', $entityCompanyID, $communicationType);

				foreach($entityCompanyComms as &$entityCompanyComm)
				{
					$comm = array(
						'type' => $entityCompanyComm['TYPE'],
						'value' => $entityCompanyComm['VALUE']
					);

					$entityCompanyData['communications'][] = $comm;
				}
				unset($entityCompanyComm);
			}
		}

		// Try to get contact of deal
		$entityContactID =  isset($entity['CONTACT_ID']) ? intval($entity['CONTACT_ID']) : 0;
		if($entityContactID > 0)
		{
			$entityContact = CCrmContact::GetByID($entityContactID);
			if($entityContact)
			{
				$item = array(
					'ownerEntityType' => 'DEAL',
					'ownerEntityId' => $entityID,
					'entityType' => 'CONTACT',
					'entityId' => $entityContactID,
					'entityTitle' => CUser::FormatName($fullNameFormat,
						array(
							'LOGIN' => '',
							'NAME' => $entityContact['NAME'],
							'LAST_NAME' => $entityContact['LAST_NAME'],
							'SECOND_NAME' => $entityContact['SECOND_NAME']
						),
						false,
						false
					),
					'tabId' => 'deal',
					'communications' => array()
				);

				$entityCompany = isset($entityContact['COMPANY_ID']) ? CCrmCompany::GetByID($entityContact['COMPANY_ID']) : null;
				if($entityCompany && isset($entityCompany['TITLE']))
				{
					$item['entityDescription'] = $entityCompany['TITLE'];
				}

				if($communicationType !== '')
				{
					$entityContactComms = CCrmActivity::PrepareCommunications('CONTACT', $entityContactID, $communicationType);
					foreach($entityContactComms as &$entityContactComm)
					{
						$comm = array(
							'type' => $entityContactComm['TYPE'],
							'value' => $entityContactComm['VALUE']
						);

						$item['communications'][] = $comm;
					}
					unset($entityContactComm);
				}

				if($communicationType === '' || !empty($item['communications']))
				{
					$dealData["CONTACT_{$entityContactID}"] = $item;
				}
			}
		}

		if($entityCompanyData && !empty($entityCompanyData['communications']))
		{
			$dealData['COMPANY_'.$entityCompanyID] = $entityCompanyData;
			$dealData['COMPANY_'.$entityCompanyID]['tabId'] = 'deal';
		}

		// Try to get previous communications
		$entityComms = CCrmActivity::GetCommunicationsByOwner('DEAL', $entityID, $communicationType);
		foreach($entityComms as &$entityComm)
		{
			CCrmActivity::PrepareCommunicationInfo($entityComm);
			$key = "{$entityComm['ENTITY_TYPE']}_{$entityComm['ENTITY_ID']}";
			if(!isset($dealData[$key]))
			{
				$dealData[$key] = array(
					'ownerEntityType' => 'DEAL',
					'ownerEntityId' => $entityID,
					'entityType' => CCrmOwnerType::ResolveName($entityComm['ENTITY_TYPE_ID']),
					'entityId' => $entityComm['ENTITY_ID'],
					'entityTitle' => isset($entityComm['TITLE']) ? $entityComm['TITLE'] : '',
					'entityDescription' => isset($entityComm['DESCRIPTION']) ? $entityComm['DESCRIPTION'] : '',
					'tabId' => 'deal',
					'communications' => array()
				);
			}

			if($communicationType !== '')
			{
				$commFound = false;
				foreach($dealData[$key]['communications'] as &$comm)
				{
					if($comm['value'] === $entityComm['VALUE'])
					{
						$commFound = true;
					}
				}
				unset($comm);

				if($commFound)
				{
					continue;
				}

				$comm = array(
					'type' => $entityComm['TYPE'],
					'value' => $entityComm['VALUE']
				);

				$dealData[$key]['communications'][] = $comm;
			}
		}
		unset($entityComm);

		$companyData = array();
		// Try to get contacts of company
		if($entityCompany > 0)
		{
			$entityComms = CCrmActivity::GetCompanyCommunications($entityCompanyID, $communicationType);
			foreach($entityComms as &$entityComm)
			{
				CCrmActivity::PrepareCommunicationInfo($entityComm);
				$key = "{$entityComm['ENTITY_TYPE']}_{$entityComm['ENTITY_ID']}";
				if(!isset($companyData[$key]))
				{
					$companyData[$key] = array(
						'ownerEntityType' => 'DEAL',
						'ownerEntityId' => $entityID,
						'entityType' => CCrmOwnerType::ResolveName($entityComm['ENTITY_TYPE_ID']),
						'entityId' => $entityComm['ENTITY_ID'],
						'entityTitle' => isset($entityComm['TITLE']) ? $entityComm['TITLE'] : '',
						'entityDescription' => isset($entityComm['DESCRIPTION']) ? $entityComm['DESCRIPTION'] : '',
						'tabId' => 'company',
						'communications' => array()
					);
				}

				if($communicationType !== '')
				{
					$comm = array(
						'type' => $entityComm['TYPE'],
						'value' => $entityComm['VALUE']
					);

					$companyData[$key]['communications'][] = $comm;
				}
			}
			unset($entityComm);
		}

		if($entityCompanyData && !empty($entityCompanyData['communications']))
		{
			$companyData['COMPANY_'.$entityCompanyID] = $entityCompanyData;
			$companyData['COMPANY_'.$entityCompanyID]['tabId'] = 'company';
		}

		echo CUtil::PhpToJSObject(
			array(
				'DATA' => array(
					'TABS' => array(
						array(
							'id' => 'deal',
							'title' => GetMessage('CRM_COMMUNICATION_TAB_DEAL'),
							'active' => true,
							'items' => array_values($dealData)
						),
						array(
							'id' => 'company',
							'title' => GetMessage('CRM_COMMUNICATION_TAB_COMPANY'),
							'items' => array_values($companyData)
						)
					)
				)
			)
		);

		die();
	}
	elseif($entityType === 'COMPANY')
	{
		$companyData = array();

		$entity = CCrmCompany::GetByID($entityID);
		if(!$entity)
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Invalid data')
			);
			die();
		}

		$companyItem = array(
			'ownerEntityType' => 'COMPANY',
			'ownerEntityId' => $entityID,
			'entityType' => 'COMPANY',
			'entityId' => $entityID,
			'entityTitle' => isset($entity['TITLE']) ? $entity['TITLE'] : "{$entityType}_{$entityID}",
			'entityDescription' => '',
			'tabId' => 'company',
			'communications' => array()
		);

		// Try to load entity communications
		if(!CCrmActivity::CheckReadPermission(CCrmOwnerType::ResolveID($entityType), $entityID))
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Permission denied!')
			);
			die();
		}

		$dbResFields = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array('ENTITY_ID' => $entityType, 'ELEMENT_ID' => $entityID, 'TYPE_ID' =>  $communicationType)
		);

		while($arField = $dbResFields->Fetch())
		{
			if(empty($arField['VALUE']))
			{
				continue;
			}

			$comm = array(
				'type' => $communicationType,
				'value' => $arField['VALUE']
			);

			$companyItem['communications'][] = $comm;
		}

		$companyData["{$entityType}_{$entityID}"] = $companyItem;

		$entityComms = CCrmActivity::GetCompanyCommunications($entityID, $communicationType, 50);
		foreach($entityComms as &$entityComm)
		{
			CCrmActivity::PrepareCommunicationInfo($entityComm);
			$key = "{$entityComm['ENTITY_TYPE']}_{$entityComm['ENTITY_ID']}";
			if(!isset($companyData[$key]))
			{
				$companyData[$key] = array(
					'ownerEntityType' => 'COMPANY',
					'ownerEntityId' => $entityID,
					'entityType' => $entityComm['ENTITY_TYPE'],
					'entityId' => $entityComm['ENTITY_ID'],
					'entityTitle' => isset($entityComm['TITLE']) ? $entityComm['TITLE'] : '',
					'entityDescription' => isset($entityComm['DESCRIPTION']) ? $entityComm['DESCRIPTION'] : '',
					'tabId' => 'company',
					'communications' => array()
				);
			}

			$comm = array(
				'type' => $entityComm['TYPE'],
				'value' => $entityComm['VALUE']
			);

			$companyData[$key]['communications'][] = $comm;
		}
		unset($entityComm);

		echo CUtil::PhpToJSObject(
			array(
				'DATA' => array(
					'TABS' => array(
						array(
							'id' => 'company',
							'title' => GetMessage('CRM_COMMUNICATION_TAB_COMPANY'),
							'active' => true,
							'items' => array_values($companyData)
						)
					)
				)
			)
		);

		die();
	}
	elseif($entityType === 'CONTACT')
	{
		$contactData = array();

		$entity = CCrmContact::GetByID($entityID);
		if(!$entity)
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Invalid data')
			);
			die();
		}

		$entityCompany = isset($entity['COMPANY_ID']) ? CCrmCompany::GetByID($entity['COMPANY_ID']) : null;

		$contactItem = array(
			'ownerEntityType' => 'CONTACT',
			'ownerEntityId' => $entityID,
			'entityType' => 'CONTACT',
			'entityId' => $entityID,
			'entityTitle' => CUser::FormatName($fullNameFormat,
				array(
					'LOGIN' => '',
					'NAME' => $entity['NAME'],
					'LAST_NAME' => $entity['LAST_NAME'],
					'SECOND_NAME' => $entity['SECOND_NAME']
				),
				false,
				false
			),
			'entityDescription' => ($entityCompany && isset($entityCompany['TITLE'])) ? $entityCompany['TITLE'] : '',
			'tabId' => 'contact',
			'communications' => array()
		);

		// Try to load entity communications
		if(!CCrmActivity::CheckReadPermission(CCrmOwnerType::ResolveID($entityType), $entityID))
		{
			echo CUtil::PhpToJSObject(
				array('ERROR' => 'Permission denied!')
			);
			die();
		}

		$dbResFields = CCrmFieldMulti::GetList(
			array('ID' => 'asc'),
			array('ENTITY_ID' => $entityType, 'ELEMENT_ID' => $entityID, 'TYPE_ID' =>  $communicationType)
		);

		while($arField = $dbResFields->Fetch())
		{
			if(empty($arField['VALUE']))
			{
				continue;
			}

			$comm = array(
				'type' => $communicationType,
				'value' => $arField['VALUE']
			);

			$contactItem['communications'][] = $comm;
		}

		$contactData["{$entityType}_{$entityID}"] = $contactItem;

		echo CUtil::PhpToJSObject(
			array(
				'DATA' => array(
					'TABS' => array(
						array(
							'id' => 'contact',
							'title' => GetMessage('CRM_COMMUNICATION_TAB_CONTACT'),
							'active' => true,
							'items' => array_values($contactData)
						)
					)
				)
			)
		);

		die();
	}
}
elseif($action == 'SEARCH_COMMUNICATIONS')
{
	$entityType = isset($_POST['ENTITY_TYPE']) ? strtoupper(strval($_POST['ENTITY_TYPE'])) : '';
	$entityID = isset($_POST['ENTITY_ID']) ? intval($_POST['ENTITY_ID']) : 0;
	$communicationType = isset($_POST['COMMUNICATION_TYPE']) ? strval($_POST['COMMUNICATION_TYPE']) : '';
	$needle = isset($_POST['NEEDLE']) ? strval($_POST['NEEDLE']) : '';
	$results = CCrmActivity::FindContactCommunications($needle, $communicationType);

	$data = array();
	foreach($results as &$result)
	{
		$key = "{$result['ENTITY_TYPE']}_{$result['ENTITY_ID']}";
		if(!isset($data[$key]))
		{
			$data[$key] = array(
				'ownerEntityType' => $entityType,
				'ownerEntityId' => $entityID,
				'entityType' => CCrmOwnerType::ResolveName($result['ENTITY_TYPE_ID']),
				'entityId' => $result['ENTITY_ID'],
				'entityTitle' => $result['TITLE'],
				'entityDescription' => $result['DESCRIPTION'],
				'tabId' => 'search',
				'communications' => array()
			);
		}

		if($result['TYPE'] !== '' && $result['VALUE'] !== '')
		{
			$comm = array(
				'type' => $result['TYPE'],
				'value' => $result['VALUE']
			);

			$data[$key]['communications'][] = $comm;
		}
	}
	unset($result);

	echo CUtil::PhpToJSObject(
		array(
			'DATA' => array(
				'ITEMS' => array_values($data)
			)
		)
	);

	die();
}
elseif($action == 'GET_TASK')
{
	$ownerTypeName = isset($_POST['OWNER_TYPE']) ? strtoupper(strval($_POST['OWNER_TYPE'])) : '';
	$ownerID = isset($_POST['OWNER_ID']) ? intval($_POST['OWNER_ID']) : 0;
	$taskID = isset($_POST['TASK_ID']) ? intval($_POST['TASK_ID']) : 0;

	if($ownerTypeName === '' || $ownerID <= 0 || $taskID <= 0)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data')
		);
		die();
	}

	$arFilter = array(
		'=OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($ownerTypeName),
		'=OWNER_ID' => $ownerID,
		'=TYPE_ID' => CCrmActivityType::Task,
		'=ASSOCIATED_ENTITY_ID' => $taskID
	);

	$dbActivities = CCrmActivity::GetList(
		array(),
		$arFilter
	);

	$arActivity = $dbActivities->Fetch();
	if(!$arActivity)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Not found')
		);
		die();
	}

	$userName = '';
	if($arActivity['RESPONSIBLE_ID'] > 0)
	{
		$dbResUser = CUser::GetByID($arActivity['RESPONSIBLE_ID']);
		$userName = is_array(($user = $dbResUser->Fetch()))
			? CUser::FormatName(CSite::GetNameFormat(false), $user, true, false) : '';
	}

	echo CUtil::PhpToJSObject(
		array(
			'ACTIVITY' => array(
				'ID' => $arActivity['ID'],
				'typeID' => CCrmActivityType::Task,
				'associatedEntityID' => $taskID,
				'subject' => $arActivity['SUBJECT'],
				'description' => $arActivity['DESCRIPTION'],
				'start' => !empty($arActivity['START_TIME']) ? $arActivity['START_TIME'] : '',
				'end' => !empty($arActivity['END_TIME']) ? $arActivity['END_TIME'] : '',
				'completed' => $arActivity['COMPLETED'] === 'Y',
				'notifyType' => CCrmActivityNotifyType::None,
				'notifyValue' => 0,
				'priority' => $arActivity['PRIORITY'],
				'responsibleName' => $userName
			)
		)
	);
}
elseif($action == 'GET_ACTIVITIES')
{
	$ownerTypeName = isset($_POST['OWNER_TYPE']) ? strtoupper(strval($_POST['OWNER_TYPE'])) : '';
	$ownerID = isset($_POST['OWNER_ID']) ? intval($_POST['OWNER_ID']) : 0;

	if($ownerTypeName === '' || $ownerID <= 0)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data')
		);
		die();
	}

	$completed = isset($_POST['COMPLETED']) ? intval($_POST['COMPLETED']) : 0;

	if(!CCrmActivity::CheckReadPermission(CCrmOwnerType::ResolveID($ownerTypeName), $ownerID))
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}

	$dbRes = CCrmActivity::GetList(
		array('end_time' => 'asc'),
		array(
			'OWNER_ID' => $ownerID,
			'OWNER_TYPE_ID' => CCrmOwnerType::ResolveID($ownerTypeName),
			'COMPLETED' => $completed > 0 ? 'Y' : 'N'
		)
	);

	$arItems = array();
	while($arRes = $dbRes->GetNext())
	{
		$responsibleID = isset($arRes['~RESPONSIBLE_ID'])
			? intval($arRes['~RESPONSIBLE_ID']) : 0;
		if($responsibleID > 0)
		{
			$dbResUser = CUser::GetByID($responsibleID);
			$arRes['RESPONSIBLE'] = $dbResUser->Fetch();
			$arRes['RESPONSIBLE_FULL_NAME'] = is_array($arRes['RESPONSIBLE'])
				? CUser::FormatName(CSite::GetNameFormat(false), $arRes['RESPONSIBLE'], true, false) : '';
		}
		else
		{
			$arRes['RESPONSIBLE'] = false;
			$arRes['RESPONSIBLE_FULL_NAME'] = '';
			$arRes['PATH_TO_RESPONSIBLE'] = '';
		}

		$arRes['FILES'] = array();
		if(is_array($arFileID = CCrmActivity::GetFileIDs($arRes['~ID'])))
		{
			$fileCount = count($arFileID);
			for($i = 0; $i < $fileCount; $i++)
			{
				if(is_array($arData = CFile::GetFileArray($arFileID[$i])))
				{
					$arRes['FILES'][] = array(
						'fileID' => $arData['ID'],
						'fileName' => $arData['FILE_NAME'],
						'fileURL' => $arData['SRC'],
						'fileSize' => $arData['FILE_SIZE']
					);
				}
			}
		}

		$arRes['SETTINGS'] = isset($arRes['~SETTINGS']) ? unserialize($arRes['~SETTINGS']) : array();
		$arRes['COMMUNICATIONS'] = CCrmActivity::GetCommunications($arRes['~ID']);

		$commData = array();
		if(is_array($arRes['COMMUNICATIONS']))
		{
			foreach($arRes['COMMUNICATIONS'] as &$arComm)
			{
				CCrmActivity::PrepareCommunicationInfo($arComm);
				$commData[] = array(
					'id' => $arComm['ID'],
					'type' => $arComm['TYPE'],
					'value' => $arComm['VALUE'],
					'entityId' => $arComm['ENTITY_ID'],
					'entityType' => CCrmOwnerType::ResolveName($arComm['ENTITY_TYPE_ID']),
					'entityTitle' => $arComm['TITLE'],
				);
			}
			unset($arComm);
		}

		$item = array(
			'ID' => $arRes['~ID'],
			'typeID' => $arRes['~TYPE_ID'],
			'subject' => strval($arRes['~SUBJECT']),
			'description' => strval($arRes['~DESCRIPTION']),
			'direction' => intval($arRes['~DIRECTION']),
			'location' => strval($arRes['~LOCATION']),
			'start' => isset($arRes['~START_TIME']) ? ConvertTimeStamp(MakeTimeStamp($arRes['~START_TIME']), 'FULL', SITE_ID) : '',
			'end' => isset($arRes['~START_TIME']) ? ConvertTimeStamp(MakeTimeStamp($arRes['~END_TIME']), 'FULL', SITE_ID) : '',
			'completed' => strval($arRes['~COMPLETED']) == 'Y',
			'notifyType' => intval($arRes['~NOTIFY_TYPE']),
			'notifyValue' => intval($arRes['~NOTIFY_VALUE']),
			'priority' => intval($arRes['~PRIORITY']),
			'responsibleName' => isset($arRes['RESPONSIBLE_FULL_NAME'][0]) ? $arRes['RESPONSIBLE_FULL_NAME'] : GetMessage('CRM_UNDEFINED_VALUE'),
			'files' => $arRes['FILES'],
			'associatedEntityID' => isset($arRes['~ASSOCIATED_ENTITY_ID']) ? intval($arRes['~ASSOCIATED_ENTITY_ID']) : 0,
			'communications' => $commData
		);

		$arItems[] = $item;
	}

	echo CUtil::PhpToJSObject(
		array(
			'DATA' => array(
				'ITEMS' => $arItems
			)
		)
	);
}
elseif($action == 'GET_ENTITIES_DEFAULT_COMMUNICATIONS')
{
	$fullNameFormat = CSite::GetNameFormat(false);

	$entityType = isset($_POST['ENTITY_TYPE']) ? strtoupper(strval($_POST['ENTITY_TYPE'])) : '';
	$entityIDs = array();
	$arEntityID = isset($_POST['ENTITY_IDS']) ? $_POST['ENTITY_IDS'] : array();
	$communicationType = isset($_POST['COMMUNICATION_TYPE']) ? strval($_POST['COMMUNICATION_TYPE']) : '';

	if($entityType === '' || $communicationType === '')
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Invalid data')
		);
		die();
	}

	// PERMISSIONS CHECK -->
	$isPermitted = true;
	$userPermissions = CCrmPerms::GetCurrentUserPermissions();
	if(empty($arEntityID))
	{
		$isPermitted = CCrmActivity::CheckReadPermission(CCrmOwnerType::ResolveID($entityType), 0, $userPermissions);
	}
	else
	{
		foreach($arEntityID as $entityID)
		{
			$isPermitted = CCrmActivity::CheckReadPermission(CCrmOwnerType::ResolveID($entityType), $entityID, $userPermissions);
			if(!$isPermitted)
			{
				break;
			}
		}
	}

	if(!$isPermitted)
	{
		echo CUtil::PhpToJSObject(
			array('ERROR' => 'Permission denied!')
		);
		die();
	}
	// <--PERMISSIONS CHECK

	$data = array();
	$arFilter = array(
		'ENTITY_ID' => $entityType,
		'TYPE_ID' =>  $communicationType,
		'VALUE_TYPE' => 'WORK'
	);

	if(!empty($arEntityID))
	{
		$arFilter['@ELEMENT_ID'] = $arEntityID;
	}

	$dbResFields = CCrmFieldMulti::GetList(
		array('ID' => 'asc'),
		$arFilter
	);

	while($arField = $dbResFields->Fetch())
	{
		if(empty($arField['VALUE']))
		{
			continue;
		}

		$entityID = intval($arField['ELEMENT_ID']);
		if($entityID <= 0)
		{
			continue;
		}

		if(!isset($data[$entityID]))
		{
			$data[$entityID] = array(
				//'entityType' => $entityType,
				//'entityTitle' => '',
				'entityId' => $entityID
			);
		}

		$data[$entityID]['value'] = $arField['VALUE'];
	}

	echo CUtil::PhpToJSObject(
		array(
			'DATA' => array(
				'ENTITY_TYPE' => $entityType,
				'ITEMS' => array_values($data)
			)
		)
	);
	die();
}
die();
?>