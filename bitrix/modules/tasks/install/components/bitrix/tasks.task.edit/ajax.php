<?php

define("STOP_STATISTICS", true);
define("BX_SECURITY_SHOW_MESSAGE", true);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

CUtil::JSPostUnescape();
CModule::IncludeModule('tasks');

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

function tasksTaskEditAjaxHandler()
{
	if ( ! check_bitrix_sessid() )
		throw new Exception();

	switch ($_POST['action'])
	{
		case 'tasks_isUserMemberOfGroup':
			if (!CModule::IncludeModule('socialnetwork'))
			{
				throw new Exception($_POST['action'] 
					. ': socialnetwork module failed to load.');
			}

			if (
				( ! isset($_POST['groupId']) )
				|| ( ! isset($_POST['userId']) )
				|| ($_POST['groupId'] < 0)
				|| ($_POST['userId'] < 0)
			)
			{
				throw new Exception($_POST['action'] 
					. ': invalid userId or groupId');
			}

			$rc = CSocNetUserToGroup::GetUserRole(
				(int) $_POST['userId'],
				(int) $_POST['groupId']
			);

			if (($rc === false) || ($rc == SONET_ROLES_REQUEST))
				echo 'N';
			else
				echo 'Y';
		break;

		default:
			throw new Exception('Requested action is unknown!');
		break;
	}
}

try
{
	ob_start();
	tasksTaskEditAjaxHandler();
	ob_end_flush();
}
catch (Exception $e)
{
	ob_end_clean();
	$strErrorMessage = $e->GetMessage();

	if (!strlen($strErrorMessage))
		$strErrorMessage = 'Request cannot be processed!';

	echo 'FATAL ERROR: ' . htmlspecialcharsbx($strErrorMessage);
}
