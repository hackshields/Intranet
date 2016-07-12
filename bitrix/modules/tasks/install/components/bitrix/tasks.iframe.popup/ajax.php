<?php
define('STOP_STATISTICS',    true);
define('NO_AGENT_CHECK',     true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

CUtil::JSPostUnescape();
CModule::IncludeModule('tasks');
CModule::IncludeModule('socialnetwork');

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;
$nameTemplate = null;
$batch = null;

if (isset($_POST['nameTemplate']))
{
	preg_match_all("/(#NAME#)|(#NOBR#)|(#\/NOBR#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/", urldecode($_POST['nameTemplate']), $matches);
	$nameTemplate = implode("", $matches[0]);
}
else
	$nameTemplate = CSite::GetNameFormat(false);

$batchId = 'unknown';

if (isset($_POST['batch']))
	$batch = $_POST['batch'];

if (isset($_POST['batchId']))
	$batchId = $_POST['batchId'];

if ( ! is_array($batch) )
{
	CTaskAssert::log(
		'Batch not given. File: ' . __FILE__, 
		CTaskAssert::ELL_ERROR
	);
	exit();
}

if ( ! check_bitrix_sessid() )
	exit();


function BXTasksResolveDynaParamValue($request, $arData)
{
	// Is task id the result of previous operation in batch?
	if (BXTasksIsDynaParamValue($request))
		$request = BXTasksParseAndGetDynaParamValue($arData, $request);

	return ($request);
}


/**
 * determine if request starts from "#RC#"
 */
function BXTasksIsDynaParamValue($request)
{
	if ( ! is_string($request) )
		return (false);

	return(substr($request, 0, 4) === '#RC#');
}


/**
 * 
 * @param array $arData with element "arDataName"
 * @param string $strRequest, for example: #RC#arDataName#-2#field1#field2#...#fieldN
 */
function BXTasksParseAndGetDynaParamValue($arData, $strRequest)
{
	CTaskAssert::assert(
		is_array($arData)
		&& is_string($strRequest) 
		&& (substr($strRequest, 0, 4) === '#RC#')
	);

	$dataCount = count($arData);

	$strToParse   = substr($strRequest, 4);
	$arrayToParse = explode('#', $strToParse);

	CTaskAssert::assert(
		is_array($arrayToParse)
		&& (count($arrayToParse) >= 3)
		&& isset($arData[$arrayToParse[0]])		// in 0th element - arDataName
		&& CTaskAssert::isLaxIntegers($arrayToParse[1])	// there is relative index
		&& ($arrayToParse[1] < 0)	// relative index must be < 0
	);

	$arRequestedData = $arData[$arrayToParse[0]];

	$curDataIndex   = count($arRequestedData) - 1;
	$deltaIndex     = (int) $arrayToParse[1];
	$requestedIndex = $curDataIndex + $deltaIndex + 1;	// +1 because last data item mustn't be in data array yet

	if ( ! isset($arRequestedData[$requestedIndex]) )
		return (null);

	// Now, iterate throws given fields
	$maxIndex = count($arrayToParse) - 1;

	$arIteratedData = $arRequestedData[$requestedIndex];

	for ($i = 2; $i <= $maxIndex; $i++)
	{
		$requestedNthFieldName = $arrayToParse[$i];
		if ( ! isset($arIteratedData[$requestedNthFieldName]) )
			return (null);

		$arIteratedData = $arIteratedData[$requestedNthFieldName];
	}

	return ($arIteratedData);
}


$status = 'unknown';
$breakExecution = false;
try
{
	$loggedInUserId = $GLOBALS['USER']->GetID();
	CTaskAssert::assert($loggedInUserId >= 1);
	$loggedInUserId = (int) $loggedInUserId;

	$arTasksObjectsPool = array();

	$operationIndex = 0;
	$arOperationsResults = array();
	foreach ($batch as $arAction)
	{
		$APPLICATION->RestartBuffer();
		CTaskAssert::assert(isset($arAction['operation']));

		$arCurOperationResult = array();
		switch ($arAction['operation'])
		{
			case 'CTaskItem::add()':
				// convert multiple UF_ fields to arrays, if they not are
				$arUserFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields('TASKS_TASK');

				CTaskAssert::assert(isset($arAction['taskData']) && is_array($arAction['taskData']));

				// Don't allow fields started not from the letter, because they will not be filtered during DB query
				$testForLetters = '';
				foreach (array_keys($arAction['taskData']) as $fieldName)
					$testForLetters .= substr($fieldName, 0, 1);

				CTaskAssert::assert((bool)preg_match('/^[A-Za-z]*$/', $testForLetters));

				foreach($arUserFields as $arUserField)
				{
					if ($arUserField['EDIT_IN_LIST'] !== 'Y')
						continue;

					if ( ! array_key_exists($arUserField['FIELD_NAME'], $arAction['taskData']) )
						continue;

					$value = $arAction['taskData'][$arUserField['FIELD_NAME']];

					if ( ($arUserField['MULTIPLE'] === 'Y') && ( ! is_array($value) ) )
						$arAction['taskData'][$arUserField['FIELD_NAME']] = array($value);
				}

				$arErrors = array();
				$justCreatedTaskId = false;
				try
				{		
					$oTask = CTaskItem::add($arAction['taskData'], $loggedInUserId);
					$justCreatedTaskId = $oTask->getId();
				}
				catch (Exception $e)
				{
					if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
						$arErrors = unserialize($e->GetMessage());
					else
					{
						$arErrors[] = array(
							'text' => 'UNKNOWN ERROR OCCURED',
							'id'   => 'ERROR_TASKS_UNKNOWN'
						);
					}
				}

				// cache tasks objects, so then can be reused in other batch operations
				$arTasksObjectsPool[$justCreatedTaskId] = $oTask;
				$arCurOperationResult = array(
					'returnValue'       => null,	// because CTaskItem::add() returns an PHP object
					'justCreatedTaskId' => $justCreatedTaskId,
					'errors'            => $arErrors
				);

				if ($justCreatedTaskId === false)
					$breakExecution = true;
			break;


			case 'CTaskItem::getTaskData()':
			case 'CTaskItem::getAllowedTaskActions()':
			case 'CTaskItem::getAllowedTaskActionsAsStrings()':
			case 'CTaskItem::update()':
				CTaskAssert::assert(
					isset($arAction['taskData'], $arAction['taskData']['ID'])
				);

				// Don't allow fields started not from the letter, because they will not be filtered during DB query
				$testForLetters = '';
				foreach (array_keys($arAction['taskData']) as $fieldName)
					$testForLetters .= substr($fieldName, 0, 1);

				CTaskAssert::assert((bool)preg_match('/^[A-Za-z]*$/', $testForLetters));

				// Resolve task id if it is the result of previous operation in batch
				$taskId = BXTasksResolveDynaParamValue(
					$arAction['taskData']['ID'],
					array('$arOperationsResults' => $arOperationsResults)
				);

				CTaskAssert::assertLaxIntegers($taskId);

				// Cache task object in pool
				if ( ! isset($arTasksObjectsPool[$taskId]) )
					$arTasksObjectsPool[$taskId] = new CTaskItem($taskId, $loggedInUserId);

				$oTask = $arTasksObjectsPool[$taskId];

				$returnValue = null;
				switch ($arAction['operation'])
				{
					case 'CTaskItem::getTaskData()':
						$returnValue = $oTask->getTaskData($bSpecialChars = false);
					break;

					case 'CTaskItem::getAllowedTaskActions()':
						$returnValue = $oTask->getAllowedTaskActions();
					break;

					case 'CTaskItem::getAllowedTaskActionsAsStrings()':
						$returnValue = $oTask->getAllowedTaskActionsAsStrings();
					break;

					case 'CTaskItem::update()':
						$returnValue = $oTask->update($arAction['taskData']);
					break;

					default:
						throw new Exception('Unknown operation: ' . $arAction['operation']);
					break;
				}

				$arCurOperationResult = array(
					'returnValue'     => $returnValue,
					'requestedTaskId' => $taskId
				);
			break;


			case 'XCHG EAX, EAX':
			case 'NOP':
			case 'NOOP':
				$arCurOperationResult = array('returnValue' => null);
			break;


			case 'CUser::FormatName()':
				CTaskAssert::assert(
					isset($arAction['userData'], $arAction['userData']['ID'])
				);

				// Resolve user id if it is the result of previous operation in batch
				$userId = BXTasksResolveDynaParamValue(
					$arAction['userData']['ID'],
					array('$arOperationsResults' => $arOperationsResults)
				);

				CTaskAssert::assertLaxIntegers($userId);

				$nt = $nameTemplate;
				if (isset($arAction['params'], $arAction['params']['nameTemplate']))
				{
					preg_match_all(
						"/(#NAME#)|(#NOBR#)|(#\/NOBR#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/",
						$arAction['params']['nameTemplate'],
						$matches
					);

					$nt = implode('', $matches[0]);
				}

				$rsUser = CUser::GetList(
					$by = 'ID', $order = 'ASC', 
					array('ID' => $userId), 
					array('FIELDS' => array('NAME', 'LAST_NAME', 'SECOND_NAME', 'LOGIN'))
				);

				$returnValue = null;

				if ($arUser = $rsUser->Fetch())
				{
					$returnValue = CUser::FormatName(
						$nt, 
						array(
							'NAME'        => $arUser['NAME'],
							'LAST_NAME'   => $arUser['LAST_NAME'],
							'SECOND_NAME' => $arUser['SECOND_NAME'],
							'LOGIN'       => $arUser['LOGIN']
						),
						$bUseLogin = true,
						$bHtmlSpecialChars = false
					);
				}

				$arCurOperationResult = array(
					'returnValue'     => $returnValue,
					'requestedUserId' => $userId
				);
			break;


			case 'tasksRenderJSON() && tasksRenderListItem()':
				CTaskAssert::assert(
					isset($arAction['taskData'], $arAction['taskData']['ID'])
				);

				// Is task id the result of previous operation in batch?
				$taskId = BXTasksResolveDynaParamValue(
					$arAction['taskData']['ID'],
					array('$arOperationsResults' => $arOperationsResults)
				);

				CTaskAssert::assertLaxIntegers($taskId);

				// Cache task object in pool
				if ( ! isset($arTasksObjectsPool[$taskId]) )
					$arTasksObjectsPool[$taskId] = new CTaskItem($taskId, $loggedInUserId);

				$oTask = $arTasksObjectsPool[$taskId];

				$returnValue = null;

				$arTask = $oTask->getTaskData($bSpecialChars = false);

				if ($arTask['GROUP_ID'])
				{
					$arTask['GROUP_NAME'] = '';
					$arGroup = CSocNetGroup::GetByID($arTask['GROUP_ID']);

					if ($arGroup)
						$arTask['GROUP_NAME'] = $arGroup['NAME'];
				}

				$childrenCount = 0;
				$rsChildrenCount = CTasks::GetChildrenCount($arFilter = array(), array($taskId));
				if ($rsChildrenCount)
				{
					if ($arChildrens = $rsChildrenCount->Fetch())
						$childrenCount = $arChildrens['CNT'];
				}

				$arPathes = array(
					'PATH_TO_TASKS_TASK' => str_replace(
						"#user_id#",
						$loggedInUserId,
						COption::GetOptionString('tasks', 'paths_task_user_action', null, $SITE_ID)
					)
				);

				$arTaskEscaped = $arTask;
				array_walk_recursive(
					$arTaskEscaped,
					function(&$item, $key)
					{
						$item = htmlspecialcharsbx($item);
					}
				);

				ob_start();
				tasksRenderListItem($arTaskEscaped, 0, $arPathes, $depth = 0, false, true, $SITE_ID, 0, true, true, 'bitrix:tasks.list.item', '.default', $nameTemplate);
				$html = ob_get_clean();

				ob_start();
				$arAdditionalFields = array();
				tasksRenderJSON($arTaskEscaped, $childrenCount, $arPathes, $bParent = true, $bGant = false, false, $nameTemplate, $arAdditionalFields);
				$json = ob_get_clean();

				$returnValue = array(
					'tasksRenderListItem' => $html,
					'tasksRenderJSON'     => $json
				);

				$arCurOperationResult = array(
					'returnValue'     => $returnValue,
					'requestedTaskId' => $taskId
				);
			break;


			case 'CSocNetGroup::GetByID()':
				CTaskAssert::assert(
					isset($arAction['groupData'], $arAction['groupData']['ID'])
				);
				$groupId = $arAction['groupData']['ID'];
				CTaskAssert::assertLaxIntegers($groupId);

				$arGroupData = array(
					'ID'             => (int) $groupId,
					'~ID'            => (int) $groupId,
					'NAME'           => '',
					'~NAME'          => '',
					'SUBJECT_NAME'   => '',
					'~SUBJECT_NAME'  => '',
					'NAME_FORMATTED' => ''
				);

				$arGroup = CSocNetGroup::GetByID($groupId, $bCheckPermissions = false);

				if (
					is_array($arGroup)
					&& ( ! empty($arGroup) )
				)
				{
					$arGroupData = $arGroup;
				}

				$arCurOperationResult = array(
					'returnValue'      => $arGroupData,
					'requestedGroupId' => $groupId
				);
			break;


			case 'CTaskItem::addElapsedTime()':
				CTaskAssert::assert(
					isset(
						$arAction['elapsedTimeData'],
						$arAction['elapsedTimeData']['TASK_ID'],
						$arAction['elapsedTimeData']['MINUTES'],
						$arAction['elapsedTimeData']['COMMENT_TEXT']
					)
					&& (count($arAction['elapsedTimeData']) === 3)
					&& is_string($arAction['elapsedTimeData']['COMMENT_TEXT'])
				);

				// Is task id the result of previous operation in batch?
				$taskId = BXTasksResolveDynaParamValue(
					$arAction['elapsedTimeData']['TASK_ID'],
					array('$arOperationsResults' => $arOperationsResults)
				);

				CTaskAssert::assertLaxIntegers($taskId, $arAction['elapsedTimeData']['MINUTES']);

				$justCreatedLogId = false;
				$arErrors = array();
				try
				{
					// Cache task object in pool
					if ( ! isset($arTasksObjectsPool[$taskId]) )
						$arTasksObjectsPool[$taskId] = new CTaskItem($taskId, $loggedInUserId);

					// Take cached task object
					$oTask = $arTasksObjectsPool[$taskId];

					$arFields = array(
						'MINUTES'      => $arAction['elapsedTimeData']['MINUTES'],
						'COMMENT_TEXT' => $arAction['elapsedTimeData']['COMMENT_TEXT']
					);

					$justCreatedLogId = $oTask->addElapsedTime($arFields);
				}
				catch (Exception $e)
				{
					if ($e->GetCode() & TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE)
						$arErrors = unserialize($e->GetMessage());
					else
					{
						$arErrors[] = array(
							'text' => 'UNKNOWN ERROR OCCURED',
							'id'   => 'ERROR_TASKS_UNKNOWN'
						);
					}
				}

				$arCurOperationResult = array(
					'returnValue'      => $justCreatedLogId,
					'justCreatedLogId' => $justCreatedLogId,
					'requestedData'    => $arAction['elapsedTimeData'],
					'errors'           => $arErrors
				);

				if ($justCreatedLogId === false)
					$breakExecution = true;
			break;


			default:
				throw new Exception(
					'Unknown operation requested. File: ' . __FILE__ 
						. '; action: ' . $arAction['operation']
				);
			break;
		}

		$arCurOperationResult['requestedOperationName'] = $arAction['operation'];
		$arOperationsResults[$operationIndex] = $arCurOperationResult;
		$operationIndex++;

		if ($breakExecution)
			break;
	}

	if ( ! $breakExecution )
		$status = 'success';
	else
		$status = 'error occured';
}
catch (Exception $e)
{
	CTaskAssert::log(
		'Exception. Current file: ' . __FILE__ 
			. '; exception file: ' . $e->GetFile()
			. '; line: ' . $e->GetLine()
			. '; message: ' . $e->GetMessage(), 
		CTaskAssert::ELL_ERROR
	);

	$status = 'error occured';
}

$APPLICATION->RestartBuffer();
header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
echo CUtil::PhpToJsObject(
	array(
		'status'        => $status,
		'repliesCount'  => count($arOperationsResults),
		'data'          => $arOperationsResults,
		'batchId'       => $batchId
	)
);
