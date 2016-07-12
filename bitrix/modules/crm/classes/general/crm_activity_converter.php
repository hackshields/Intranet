<?php
class CCrmActivityConverter
{
	public static function IsCalEventConvertigRequired()
	{
		if(!(IsModuleInstalled('calendar') && CModule::IncludeModule('calendar')))
		{
			return false;
		}

		$arEvents = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					'!UF_CRM_CAL_EVENT' => null,
					'DELETED' => 'N'
				),
				'getUserfields' => true
			)
		);

		foreach($arEvents as $arEvent)
		{
			$eventID = $arEvent['ID'];
			$dbEntities = CCrmActivity::GetList(
				array(),
				array(
					'@TYPE_ID' =>  array(CCrmActivityType::Call, CCrmActivityType::Meeting),
					'=ASSOCIATED_ENTITY_ID' => $eventID
				)
			);

			if($dbEntities->SelectedRowsCount() === 0)
			{
				return true;
			}
		}
		return false;
	}
	public static function ConvertCalEvents($checkPerms = true, $regEvent = true)
	{
		if(!(IsModuleInstalled('calendar') && CModule::IncludeModule('calendar')))
		{
			return 0;
		}

		$arEvents = CCalendarEvent::GetList(
			array(
				'arFilter' => array(
					'!UF_CRM_CAL_EVENT' => null,
					'DELETED' => 'N'
				),
				'getUserfields' => true
			)
		);

		$count = 0;
		foreach($arEvents as $arEvent)
		{
			$eventID = $arEvent['ID'];
			$dbEntities = CCrmActivity::GetList(
				array(),
				array(
					'@TYPE_ID' =>  array(CCrmActivityType::Call, CCrmActivityType::Meeting),
					'=ASSOCIATED_ENTITY_ID' => $eventID
				)
			);

			if($dbEntities->SelectedRowsCount() === 0
				&& CCrmActivity::CreateFromCalendarEvent($eventID, $arEvent, $checkPerms, $regEvent) > 0)
			{
				$count++;
			}
		}
		return $count;
	}
	public static function IsTaskConvertigRequired()
	{
		if(!(IsModuleInstalled('tasks') && CModule::IncludeModule('tasks')))
		{
			return false;
		}

		$taskEntity = new CTasks();
		$dbRes = $taskEntity->GetList(
			array(),
			array('!UF_CRM_TASK' => null),
			array('ID'),
			false
		);

		while($arTask = $dbRes->GetNext())
		{
			$taskID = intval($arTask['ID']);
			$dbEntities = CCrmActivity::GetList(
				array(),
				array(
					'=TYPE_ID' =>  CCrmActivityType::Task,
					'=ASSOCIATED_ENTITY_ID' => $taskID
				)
			);

			if($dbEntities->SelectedRowsCount() === 0)
			{
				return true;
			}
		}
		return false;
	}
	public static function ConvertTasks($checkPerms = true, $regEvent = true)
	{
		if(!(IsModuleInstalled('tasks') && CModule::IncludeModule('tasks')))
		{
			return 0;
		}

		$taskEntity = new CTasks();
		$dbRes = $taskEntity->GetList(
			array(),
			array('!UF_CRM_TASK' => null),
			array(
				'ID',
				'TITLE',
				'DESCRIPTION',
				'RESPONSIBLE_ID',
				'PRIORITY',
				'STATUS',
				'CREATED_DATE',
				'DATE_START',
				'CLOSED_DATE',
				'START_DATE_PLAN',
				'END_DATE_PLAN',
				'DEADLINE',
				'UF_CRM_TASK'
			),
			false
		);

		$count = 0;
		while($arTask = $dbRes->GetNext())
		{
			$taskID = intval($arTask['ID']);
			$dbEntities = CCrmActivity::GetList(
				array(),
				array(
					'=TYPE_ID' =>  CCrmActivityType::Task,
					'=ASSOCIATED_ENTITY_ID' => $taskID
				)
			);

			if($dbEntities->SelectedRowsCount() === 0
				&& CCrmActivity::CreateFromTask($taskID, $arTask, $checkPerms, $regEvent) > 0)
			{
				$count++;
			}
		}
		return $count;
	}
}