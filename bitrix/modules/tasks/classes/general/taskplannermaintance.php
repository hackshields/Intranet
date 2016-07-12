<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */


class CTaskPlannerMaintance
{
	const PLANNER_COUNTER_CODE = 'planner_tasks';
	const PLANNER_OPTION_CURRENT_TASKS = 'current_tasks_list';

	private static $arTaskStatusOpened = array(4,5,7);

	private static $SITE_ID = SITE_ID;
	private static $USER_ID = null;

	public static function OnPlannerInit($params)
	{
		global $APPLICATION, $USER, $CACHE_MANAGER;

		self::$SITE_ID = $params['SITE_ID'];
		self::$USER_ID = $USER->GetID();

		$CACHE_MANAGER->RegisterTag('tasks_user_'.self::$USER_ID);

		$arTasks = array();
		$arTaskIDs = self::getCurrentTasksList();
		$tasksCount = self::getTasksCount($arTaskIDs);

		if($params['FULL'])
		{
			if (is_array($arTaskIDs) && count($arTaskIDs) > 0)
			{
				$arTasks = self::getTasks($arTaskIDs);
	/*
				foreach ($info['TASKS'] as &$arTask)
				{
					$arTask['TIME'] = self::GetTaskTime(array(
						'TASK_ID' => $arTask['ID'],
						'USER_ID' => $USER->GetID(),
						'DATE_START' => $info['INFO']['DATE_START'],
						'DATE_FINISH' => $info['INFO']['DATE_FINISH'],
						'EXPIRED_DATE' => $info['EXPIRED_DATE'],
						'TASK_STATUS' => $arTask['STATUS']
					));
				}
	*/
			}
		}
		else
		{
			$APPLICATION->IncludeComponent(
				"bitrix:tasks.iframe.popup",
				".default",
				array(
					"ON_TASK_ADDED" => "BX.DoNothing",
					"ON_TASK_CHANGED" => "BX.DoNothing",
					"ON_TASK_DELETED" => "BX.DoNothing",
				),
				null,
				array("HIDE_ICONS" => "Y")
			);
		}

		CJSCore::RegisterExt('tasks_planner_handler', array(
			'js' => '/bitrix/js/tasks/core_planner_handler.js',
			'css' => '/bitrix/js/tasks/css/core_planner_handler.css',
			'lang' => BX_ROOT.'/modules/tasks/lang/'.LANGUAGE_ID.'/core_planner_handler.php',
			'rel' => array('popup', 'tooltip')
		));

		return array(
			'DATA' => array(
				'TASKS_ENABLED' => true,
				'TASKS' => $arTasks,
				'TASKS_COUNT' => $tasksCount,
			),
			'STYLES' => array('/bitrix/js/tasks/css/tasks.css'),
			'SCRIPTS' => array('CJSTask', 'taskQuickPopups', 'tasks_planner_handler', '/bitrix/js/tasks/task-iframe-popup.js')
		);
	}

	public static function OnPlannerAction($action, $params)
	{
		$res = null;
		$lastTaskId = 0;
		switch($action)
		{
			case 'task':

				$lastTaskId = self::plannerActions(array(
					'name' => $_REQUEST['name'],
					'add' => $_REQUEST['add'],
					'remove' => $_REQUEST['remove'],
				), $params['SITE_ID']);

			break;
		}

		if($lastTaskId > 0)
		{
			$res['TASK_LAST_ID'] = $lastTaskId;
		}

		return $res;
	}

	public static function plannerActions($arActions, $site_id = SITE_ID)
	{
		global $USER, $CACHE_MANAGER;

		self::$SITE_ID = $site_id;
		self::$USER_ID = $USER->GetID();

		$lastTaskId = 0;

		$arTasks = self::getCurrentTasksList();

		if (!is_array($arTasks))
			$arTasks = array();

		if (strlen($arActions['name']) > 0)
		{
			$obt = new CTasks();
			$ID = $obt->Add(array(
				'RESPONSIBLE_ID' => self::$USER_ID,
				'TITLE' => $arActions['name'],
				'TAGS' => array(),
				'STATUS' => 2,
				'SITE_ID' => self::$SITE_ID
			));

			if ($ID)
			{
				if (!is_array($arActions['add']))
				{
					$arActions['add'] = array($ID);
				}
				else
				{
					$arActions['add'][] = $ID;
				}
			}
		}

		if (is_array($arActions['add']))
		{
			$task_id = $lastTaskId;

			foreach ($arActions['add'] as $task_id)
			{
				$arTasks[] = intval($task_id);
			}

			$lastTaskId = $task_id;
		}

		$arTasks = array_unique($arTasks);

		if (is_array($arActions['remove']))
		{
			$arActions['remove'] = array_unique($arActions['remove']);

			foreach ($arActions['remove'] as $task_id)
			{
				$task_id = intval($task_id);

				if (($key = array_search($task_id, $arTasks)) !== false)
				{
					unset($arTasks[$key]);
				}
			}
		}

		$CACHE_MANAGER->ClearByTag('tasks_user_'.self::$USER_ID);

		self::setCurrentTasksList($arTasks);

		return $lastTaskId;
	}

	private static function getTasks($arIDs = array(), $bOpened = false)
	{
		$res = null;

		if  (!is_array($arIDs) && strlen($arIDs) > 0)
			$arIDs = unserialize($arIDs);

		$arIDs = array_values($arIDs);

		$USER_ID = self::$USER_ID;

		$res = array();
		if (count($arIDs) > 0)
		{
			$arFilter = array('ID' => $arIDs);

			if ($bOpened)
			{
				$arFilter['!STATUS'] = self::$arTaskStatusOpened;
			}

			$dbRes = CTasks::GetList(array(), $arFilter);
			while ($arRes = $dbRes->Fetch())
			{
				$arRes['ACCOMPLICES'] = $arRes['AUDITORS'] = array();
				$rsMembers = CTaskMembers::GetList(
					array(),
					array('TASK_ID' => $arRes['ID'])
				);

				while ($arMember = $rsMembers->Fetch())
				{
					if ($arMember['TYPE'] == 'A')
						$arRes['ACCOMPLICES'][] = $arMember['USER_ID'];
					elseif ($arMember['TYPE'] == 'U')
						$arRes['AUDITORS'][] = $arMember['USER_ID'];
				}

				// Permit only for responsible user, accomplices or auditors
				$isPermited = ( ($arRes['RESPONSIBLE_ID'] == $USER_ID)
					|| in_array($USER_ID, $arRes['ACCOMPLICES'])
					|| in_array($USER_ID, $arRes['AUDITORS'])
				);

				if (!$isPermited)
					continue;

				$res[] = array(
					'ID' => $arRes['ID'],
					'PRIORITY' => $arRes['PRIORITY'],
					'STATUS' => $arRes['STATUS'],
					'TITLE' => $arRes['TITLE'],
					'TASK_CONTROL' => $arRes['TASK_CONTROL'],
					'URL' => str_replace(
						array('#USER_ID#', '#TASK_ID#'),
						array($USER_ID, $arRes['ID']),
						COption::GetOptionString('intranet', 'path_task_user_entry', '/company/personal/user/#USER_ID#/tasks/task/view/#TASK_ID#/')
					)
				);
			}
		}

		return $res;
	}

	private static function getTasksCount($arTasks)
	{
		$cnt = 0;
		if (is_array($arTasks) && count($arTasks) > 0)
		{
			$dbRes = CTasks::GetCount(array(
				'ID' => $arTasks,
				'RESPONSIBLE_ID' => self::$USER_ID,
				'!STATUS' => self::$arTaskStatusOpened
			));
			if ($arRes = $dbRes->Fetch())
			{
				$cnt = $arRes['CNT'];
			}
		}

		return $cnt;
	}

	public static function getCurrentTasksList()
	{
		$list = CUserOptions::GetOption('tasks', self::PLANNER_OPTION_CURRENT_TASKS, null);
		// current user hasn't already used tasks list or has list in timeman
		if($list === null)
		{
			if(CModule::IncludeModule('timeman'))
			{
				$TMUSER = CTimeManUser::instance();
				$arInfo = $TMUSER->GetCurrentInfo();
				if(is_array($arInfo['TASKS']))
				{
					$list = $arInfo['TASKS'];
				}
			}
			else
			{
				$list = array();
			}

			if ($list !== null)
				self::setCurrentTasksList($list);
		}

		if(!is_array($list))
		{
			$list = array();
		}

		return $list;
	}

	private static function setCurrentTasksList($list)
	{
		CUserOptions::SetOption('tasks', self::PLANNER_OPTION_CURRENT_TASKS, $list);
	}
}
