<?php

define('STOP_STATISTICS',    true);
define('NO_AGENT_CHECK',     true);
define('DisableEventsCheck', true);
define('BX_SECURITY_SHOW_MESSAGE', true);

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

$APPLICATION->ShowAjaxHead();

CUtil::JSPostUnescape();
CModule::IncludeModule('tasks');
CModule::IncludeModule('intranet');
CModule::IncludeModule('socialnetwork');

__IncludeLang(dirname(__FILE__) . '/lang/' . LANGUAGE_ID . '/' . basename(__FILE__));

$SITE_ID = isset($_GET["SITE_ID"]) ? $_GET["SITE_ID"] : SITE_ID;

if ( ! check_bitrix_sessid() )
	exit();

$nameTemplateForSite = CSite::GetNameFormat(false);

try
{
	CTaskAssert::assert(isset($_POST['requestsCount']));

	for ($i = 0; $i < $_POST['requestsCount']; $i++)
	{
		$inData = $_POST['data_' . $i];
		CTaskAssert::assert(isset($inData['requestedObject']));
		$nameTemplate = $nameTemplateForSite;

		if (isset($inData['nameTemplate']))
		{
			preg_match_all("/(#NAME#)|(#NOBR#)|(#\/NOBR#)|(#LAST_NAME#)|(#SECOND_NAME#)|(#NAME_SHORT#)|(#SECOND_NAME_SHORT#)|\s|\,/", urldecode($inData['nameTemplate']), $matches);
			$nameTemplate = implode("", $matches[0]);
		}

		switch ($inData['requestedObject'])
		{
			case 'intranet.user.selector.new':
				if ( ! isset($inData['namespace']) )
					exit();

				$namespace = $inData['namespace'];
				$inputId   = null;

				if (isset($inData['inputId']))
					$inputId = $inData['inputId'];

				$multiple = 'N';
				if (isset($inData['multiple']) && ($inData['multiple'] === 'Y'))
					$multiple = 'Y';

				$onSelectFunctionName = null;
				if (isset($inData['onSelectFunctionName']) && strlen($inData['onSelectFunctionName']))
					$onSelectFunctionName = $inData['onSelectFunctionName'];

				$selectedUsersIds = array();
				if (isset($inData['selectedUsersIds']))
				{
					if (is_array($inData['selectedUsersIds']))
						$selectedUsersIds = array_map('intval', $inData['selectedUsersIds']);
					else
					$selectedUsersIds = (int) $inData['selectedUsersIds'];
				}

				$GROUP_ID_FOR_SITE = false;
				if (isset($inData['GROUP_ID_FOR_SITE']) && ($inData['GROUP_ID_FOR_SITE'] > 0))
					$GROUP_ID_FOR_SITE = $inData['GROUP_ID_FOR_SITE'];

				$APPLICATION->IncludeComponent(
					'bitrix:intranet.user.selector.new',
					'.default',
					array(
						'MULTIPLE'          =>  $multiple,
						'NAME'              =>  $namespace,
						'INPUT_NAME'        =>  $inputId,
						'VALUE'             =>  $selectedUsersIds,
						'POPUP'             => 'Y',
						'ON_SELECT'         =>  $onSelectFunctionName,
						//'PATH_TO_USER_PROFILE' => 'sdfgtdy',
						'SITE_ID'           =>  $SITE_ID,
						'GROUP_ID_FOR_SITE' =>  $GROUP_ID_FOR_SITE,
						'GROUP_SITE_ID'     =>  $SITE_ID,
						'SHOW_EXTRANET_USERS' => 'FROM_MY_GROUPS',
						'DISPLAY_TAB_GROUP' => 'Y',
						'NAME_TEMPLATE'     =>  $nameTemplate
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);
			break;

			case 'socialnetwork.group.selector':
				CTaskAssert::assert(isset($inData['bindElement'], $inData['jsObjectName']));
				$onSelectFuncName = null;
				if (isset($inData['onSelectFuncName']) && strlen($inData['onSelectFuncName']))
					$onSelectFuncName = $inData['onSelectFuncName'];

				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.group.selector",
					".default",
					array(
						'BIND_ELEMENT'   => $inData['bindElement'],
						'ON_SELECT'      => $onSelectFuncName,
						'JS_OBJECT_NAME' => $inData['jsObjectName'],
						'FEATURES_PERMS' => array('tasks', 'create_tasks'),
						'SELECTED'       => 0
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			break;

			case 'LHEditor':
				CTaskAssert::assert(isset($inData['jsObjectName'],
					$inData['elementId'], $inData['inputId']));

				$arToolbarConfig = array(
					'Bold', 'Italic', 'Underline', 'Strike',
					'ForeColor', 'FontList', 'FontSizeList',
					'RemoveFormat', 'Quote', 'Code',
					'CreateLink', 'DeleteLink', 'Image',
					'Table',
					'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyFull',
					'InsertOrderedList', 'InsertUnorderedList',
					'Source'
				);

				$ar = array(
					'id'              =>  $inData['elementId'],
					'height'          => '200px',
					'inputName'       => 'DESCRIPTION',
					'inputId'         =>  $inData['inputId'],
					'content'         =>  '',
					'bUseFileDialogs' =>  false,
					'BBCode'          =>  true,
					'toolbarConfig'   =>  $arToolbarConfig,
					'jsObjName'       =>  $inData['jsObjectName'],
					'bResizable'      =>  false,
					'bAutoResize'     =>  false
				);
				CModule::IncludeModule("fileman");
				$LHE = new CLightHTMLEditor;
				$LHE->Show($ar);
			break;

			case 'system.field.edit::CRM':
				CTaskAssert::assert(
					isset(
						$inData['taskId'], $inData['userFieldName'], 
						$inData['nameContainerId'], $inData['dataContainerId']
					)
					&& CTaskAssert::isLaxIntegers($inData['taskId'])
					&& is_string($inData['userFieldName'])
					&& ($inData['userFieldName'] !== '')
					&& is_string($inData['nameContainerId'])
					&& ($inData['nameContainerId'] !== '')
					&& is_string($inData['dataContainerId'])
					&& ($inData['dataContainerId'] !== '')
				);

				global $USER_FIELD_MANAGER;
				$arAvailableUserFieldsMeta = $USER_FIELD_MANAGER->GetUserFields(
					'TASKS_TASK', $inData['taskId'], LANGUAGE_ID
				);

				// We need only $inData['userFieldName']
				if ( ! isset($arAvailableUserFieldsMeta[$inData['userFieldName']]) )
					break;

				$arUserField = $arAvailableUserFieldsMeta[$inData['userFieldName']];

				if ($arUserField['EDIT_IN_LIST'] !== 'Y')
					break;

				echo '<div id="' . $inData['nameContainerId'] . '">'
					. htmlspecialcharsbx($arUserField['EDIT_FORM_LABEL'])
					. '</div>';
				echo '<div id="' . $inData['dataContainerId'] . '">';
				$APPLICATION->IncludeComponent(
					'bitrix:system.field.edit',
					$arUserField['USER_TYPE']['USER_TYPE_ID'],
					array(
						'bVarsFromForm'     =>  true,
						'arUserField'       =>  $arUserField,
						'form_name'         => 'quick-task-edit-form',
						'SHOW_FILE_PATH'    =>  false,
						'FILE_URL_TEMPLATE' => '/bitrix/components/bitrix/tasks.task.detail/show_file.php?fid=#file_id#'
					),
					null,
					array('HIDE_ICONS' => 'Y')
				);
				echo '</div>';
			break;

			default:
				throw new Exception('Unknown requestedObject: ' . $inData['requestedObject']);
			break;
		}
	}
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
	exit();
}
