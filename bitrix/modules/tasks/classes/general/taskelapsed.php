<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */

class CTaskElapsedTime
{
	function CheckFields(&$arFields, $ID = false)
	{
		return true;
	}


	function Add($arFields, $arParams = array())
	{
		global $DB, $USER;

		$userId = null;
		if (isset($arParams['USER_ID']))
			$userId = (int) $arParams['USER_ID'];
		elseif (is_object($USER) && method_exists($USER, 'getId'))
			$userId = (int) $USER->getId();

		if ($this->CheckFields($arFields))
		{
			$curDuration = 0;
			$rsTask = CTasks::getById($arFields['TASK_ID']);
			if ($rsTask && ($arTask = $rsTask->fetch()))
				$curDuration = (int) $arTask['DURATION_FACT'];

			$ID = $DB->Add("b_tasks_elapsed_time", $arFields, array("COMMENT_TEXT"), "tasks");

			$oLog = new CTaskLog();
			$oLog->Add(array(
				'TASK_ID'       =>  $arFields['TASK_ID'],
				'USER_ID'       => ($userId ? $userId : 1),
				'~CREATED_DATE' =>  $DB->currentTimeFunction(),
				'FIELD'         => 'DURATION_FACT',
				'FROM_VALUE'    =>  $curDuration,
				'TO_VALUE'      =>  $curDuration + (int) $arFields['MINUTES']
			));

			foreach(GetModuleEvents('tasks', 'OnTaskElapsedTimeAdd', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			return $ID;
		}

		return false;
	}


	function Update($ID, $arFields, $arParams = array())
	{
		global $DB, $USER;

		$ID = intval($ID);
		if ($ID < 1)
			return false;

		$userId = null;
		if (isset($arParams['USER_ID']))
			$userId = (int) $arParams['USER_ID'];
		elseif (is_object($USER) && method_exists($USER, 'getId'))
			$userId = (int) $USER->getId();

		if ($this->CheckFields($arFields, $ID))
		{
			$rsUpdatingLogItem = self::getByID($ID);
			if ( ! ($rsUpdatingLogItem && ($arUpdatingLogItem = $rsUpdatingLogItem->fetch())) )
				return (false);

			$taskId = $arUpdatingLogItem['TASK_ID'];

			$curDuration = 0;
			$rsTask = CTasks::getById($taskId);
			if ($rsTask && ($arTask = $rsTask->fetch()))
				$curDuration = (int) $arTask['DURATION_FACT'];

			unset($arFields["ID"]);

			$arBinds = array(
				"COMMENT_TEXT" => $arFields["COMMENT_TEXT"]
			);

			$strUpdate = $DB->PrepareUpdate("b_tasks_elapsed_time", $arFields);
			$strSql = "UPDATE b_tasks_elapsed_time SET ".$strUpdate." WHERE ID=".$ID;
			$rc = $DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$oLog = new CTaskLog();
			$oLog->Add(array(
				'TASK_ID'       =>  $taskId,
				'USER_ID'       => ($userId ? $userId : 1),
				'~CREATED_DATE' =>  $DB->currentTimeFunction(),
				'FIELD'         => 'DURATION_FACT',
				'FROM_VALUE'    =>  $curDuration,
				'TO_VALUE'      =>  $curDuration - (int) $arUpdatingLogItem['MINUTES'] + (int) $arFields['MINUTES']
			));

			foreach(GetModuleEvents('tasks', 'OnTaskElapsedTimeUpdate', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			return ($rc);
		}

		return false;
	}


	function Delete($ID, $arParams = array())
	{
		global $DB, $USER;

		$ID = intval($ID);
		if ($ID < 1)
			return false;

		$userId = null;
		if (isset($arParams['USER_ID']))
			$userId = (int) $arParams['USER_ID'];
		elseif (is_object($USER) && method_exists($USER, 'getId'))
			$userId = (int) $USER->getId();

		$rsRemovingLogItem = self::getByID($ID);
		if ( ! ($rsRemovingLogItem && ($arRemovingLogItem = $rsRemovingLogItem->fetch())) )
			return (false);

		$taskId = $arRemovingLogItem['TASK_ID'];

		$curDuration = 0;
		$rsTask = CTasks::getById($taskId);
		if ($rsTask && ($arTask = $rsTask->fetch()))
			$curDuration = (int) $arTask['DURATION_FACT'];

		$strSql = "DELETE FROM b_tasks_elapsed_time WHERE ID = ".$ID;

		$rc = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$oLog = new CTaskLog();
		$oLog->Add(array(
			'TASK_ID'       =>  $taskId,
			'USER_ID'       => ($userId ? $userId : 1),
			'~CREATED_DATE' =>  $DB->currentTimeFunction(),
			'FIELD'         => 'DURATION_FACT',
			'FROM_VALUE'    =>  $curDuration,
			'TO_VALUE'      =>  $curDuration - (int) $arRemovingLogItem['MINUTES']
		));

		foreach(GetModuleEvents('tasks', 'OnTaskElapsedTimeDelete', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID));

		return ($rc);
	}


	function GetFilter($arFilter)
	{
		global $DB;

		if (!is_array($arFilter))
			$arFilter = Array();

		$arSqlSearch = Array();

		foreach ($arFilter as $key => $val)
		{
			$res = CTasks::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = strtoupper($key);

			switch ($key)
			{
				case "CREATED_DATE":
					$arSqlSearch[] = CTasks::FilterCreate("TE.".$key, $DB->CharToDateFunction($val), "date", $bFullJoin, $cOperationType);
					break;

				case "ID":
				case "USER_ID":
				case "TASK_ID":
					$arSqlSearch[] = CTasks::FilterCreate("TE.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "FIELD":
					$arSqlSearch[] = CTasks::FilterCreate("TE.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}
		}

		return $arSqlSearch;
	}


	function GetList($arOrder, $arFilter, $arParams = array())
	{
		global $DB;

		$userPseudoId = 'undefined';
		if (isset($arParams['USER_ID']))
		{
			CTaskAssert::assertLaxIntegers($arParams['USER_ID']);
			CTaskAssert::assert($arParams['USER_ID'] > 0);
			$userPseudoId = (int) $arParams['USER_ID'];

			if (
				CTasksTools::IsAdmin($userPseudoId)
				|| CTasksTools::IsPortalB24Admin($userPseudoId)
			)
			{
				$userPseudoId = 'admin';
			}
		}

		$arSqlSearch = CTaskElapsedTime::GetFilter($arFilter);

		$strSql = "
			SELECT
				TE.*,
				".$DB->DateToCharFunction("TE.CREATED_DATE", "FULL")." AS CREATED_DATE,
				U.NAME AS USER_NAME,
				U.LAST_NAME AS USER_LAST_NAME,
				U.SECOND_NAME AS USER_SECOND_NAME,
				U.LOGIN AS USER_LOGIN,
				CASE
					WHEN
						'undefined' = '" . $userPseudoId . "'
					THEN
						'undefined'
					WHEN
						(
							TE.USER_ID = '" . $userPseudoId . "'
							OR 'admin' = '" . $userPseudoId . "'
						)
					THEN
						'Y'
					ELSE
						'N'
				END AS CAN_EDIT_OR_REMOVE
			FROM
				b_tasks_elapsed_time TE
			INNER JOIN
				b_user U
			ON
				U.ID = TE.USER_ID
			".(sizeof($arSqlSearch) ? "WHERE ".implode(" AND ", $arSqlSearch) : "")."
		";

		if (!is_array($arOrder))
			$arOrder = Array("CREATED_DATE" => "ASC");

		foreach ($arOrder as $by => $order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order != "asc")
				$order = "desc";

			if ($by == "id")
				$arSqlOrder[] = " TE.ID ".$order." ";
			elseif ($by == "user")
				$arSqlOrder[] = " TE.USER_ID ".$order." ";
			elseif ($by == "field")
				$arSqlOrder[] = " TE.FIELD ".$order." ";
			elseif ($by == "rand")
				$arSqlOrder[] = CTasksTools::getRandFunction();
			else
				$arSqlOrder[] = " TE.CREATED_DATE ".$order." ";
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		for ($i = 0, $arSqlOrderCnt = count($arSqlOrder); $i < $arSqlOrderCnt; $i++)
		{
			if ($i == 0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}


	function GetByID($ID)
	{
		return CTaskElapsedTime::GetList(Array(), Array("ID" => $ID));
	}


	function CanCurrentUserAdd($task)
	{
		global $USER;

		if (!$userID = $USER->GetID())
		{
			return false;
		}
		elseif (
			$USER->IsAdmin() 
			|| CTasksTools::IsPortalB24Admin()
			|| ($userID == $task["RESPONSIBLE_ID"]) 
			|| (is_array($task["ACCOMPLICES"]) && in_array($USER->GetID(), $task["ACCOMPLICES"]))
		)
		{
			return true;
		}

		return false;
	}
}