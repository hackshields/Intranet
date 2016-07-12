<?php
define('STOP_STATISTICS',    true);
define('NO_AGENT_CHECK',     true);
define('DisableEventsCheck', true);

define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CUtil::JSPostUnescape();

CModule::IncludeModule('tasks');

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;

if (isset($_GET["nt"]))
{
	preg_match_all("/(#NAME#)|(#NOBR#)|(#\/NOBR#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/", urldecode($_GET["nt"]), $matches);
	$nameTemplate = implode("", $matches[0]);
}
else
	$nameTemplate = CSite::GetNameFormat(false);

$arParams = array();
$arParams["NAME_TEMPLATE"] = $nameTemplate;

$loggedInUserId = 0;
if (isset($GLOBALS['USER']) && is_object($GLOBALS['USER']) && $GLOBALS['USER']->isAuthorized())
	$loggedInUserId = (int) $GLOBALS['USER']->GetID();

if (check_bitrix_sessid() && ($loggedInUserId > 0))
{
	$action = '';
	if (isset($_GET['action']))
		$action = $_GET['action'];

	if ($action === 'render_task_log_last_row_with_date_change')
	{
		$arParams["PATH_TO_USER_PROFILE"] = (string) $_POST["PATH_TO_USER_PROFILE"];

		$authorUserId = (int) $loggedInUserId;
		$taskId = (int) $_POST['task_id'];

		$rsLog = CTaskLog::GetList(
			array('CREATED_DATE' => 'DESC'), 
			array("TASK_ID" => $taskId)
		);

		$arData = false;
		while ($arLog = $rsLog->GetNext())
		{
			// wait for DEADLINE field
			if ($arLog['FIELD'] !== 'DEADLINE')
				continue;

			// Yeah, we found it!
			$arData = $arLog;
			break;
		}

		// If row found
		if ($arData !== false)
		{
			$rsCurUserData = $USER->GetByID($authorUserId);
			$arCurUserData = $rsCurUserData->Fetch();

			$strDateFrom = $strDateTo = '';

			if ($arData['FROM_VALUE'])
			{
				// Don't format time, if it's 00:00
				if (date('H:i', $arData['FROM_VALUE']) == '00:00')
				{
					$strDateFrom = FormatDate(
						CDatabase::DateFormatToPHP(FORMAT_DATE), 
						$arData['FROM_VALUE']
					);
				}
				else
				{
					$strDateFrom = FormatDate(
						CDatabase::DateFormatToPHP(FORMAT_DATETIME), 
						$arData['FROM_VALUE']
					);
				}
			}

			if ($arData['TO_VALUE'])
			{
				// Don't format time, if it's 00:00
				if (date('H:i', $arData['TO_VALUE']) == '00:00')
				{
					$strDateTo = FormatDate(
						CDatabase::DateFormatToPHP(FORMAT_DATE), 
						$arData['TO_VALUE']
					);
				}
				else
				{
					$strDateTo = FormatDate(
						CDatabase::DateFormatToPHP(FORMAT_DATETIME), 
						$arData['TO_VALUE']
					);
				}
			}

			$arResult = array(
				'td1' => '<span class="task-log-date">' . FormatDateFromDB($arData['CREATED_DATE']) . '</span>',
				'td2' => '<a class="task-log-author" target="_top" href="' 
					. CComponentEngine::MakePathFromTemplate(
						$arParams['PATH_TO_USER_PROFILE'], 
						array('user_id' => $authorUserId)
						) 
					. '">' 
					. htmlspecialcharsbx(tasksFormatNameShort(
						$arCurUserData["NAME"], 
						$arCurUserData["LAST_NAME"], 
						$arCurUserData["LOGIN"], 
						$arCurUserData["SECOND_NAME"], 
						$arParams["NAME_TEMPLATE"]))
					. '</a>',
				'td3' => '<span class="task-log-where">' . GetMessage("TASKS_LOG_DEADLINE")  . '</span>',
				'td4' => '<span class="task-log-what">'
					. $strDateFrom
					. '<span class="task-log-arrow">&rarr;</span>'
					. $strDateTo
					. '</span>'
				);

			header('Content-Type: application/x-javascript; charset=' . LANG_CHARSET);
			echo CUtil::PhpToJsObject($arResult);
		}
	}
	elseif ($action === 'remove_file')
	{
		try
		{
			CTaskAssert::log(
				'remove_file: fileId=' . $_POST['fileId'] . ', taskId=' . $_POST['taskId']
				. ', userId=' . $loggedInUserId,
				CTaskAssert::ELL_INFO
			);
			CTaskAssert::assert(isset($_POST['fileId'], $_POST['taskId']));
			$oTaskItem = new CTaskItem($_POST['taskId'], $loggedInUserId);
			$oTaskItem->removeAttachedFile($_POST['fileId']);
			echo 'Success';
		}
		catch (Exception $e)
		{
			echo 'Error occured';
			CTaskAssert::log(
				'Unable to remove_file: fileId=' . $_POST['fileId'] 
				. ', taskId=' . $_POST['taskId'] . ', userId=' . $loggedInUserId,
				CTaskAssert::ELL_WARNING
			);
		}
	}
}
