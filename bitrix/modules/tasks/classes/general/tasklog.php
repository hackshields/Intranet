<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */

class CTaskLog
{
	static $arComparedFields = array(
		"TITLE" => "string",
		"DESCRIPTION" => "text",
		"RESPONSIBLE_ID" => "integer",
		"DEADLINE" => "date",
		"START_DATE_PLAN" => "date",
		"END_DATE_PLAN" => "date",
		"ACCOMPLICES" => "array",
		"AUDITORS" => "array",
		"FILES" => "array",
		"TAGS" => "array",
		"PRIORITY" => "integer",
		"GROUP_ID" => "integer",
		"DURATION_PLAN" => "integer",
		"DURATION_PLAN_SECONDS" => "integer",
		"DURATION_FACT" => "integer",
		"PARENT_ID" => "integer",
		"DEPENDS_ON" => "array",
		"STATUS" => "integer",
		"MARK" => "string",
		"ADD_IN_REPORT" => "bool"
	);


	public static function CheckFields(&$arFields, $ID = false)
	{
		return true;
	}


	public function Add($arFields)
	{
		global $DB;
		if ($this->CheckFields($arFields))
		{
			$arFields["ID"] = 1;
			$ID = $DB->Add("b_tasks_log", $arFields, Array("FROM_VALUE", "TO_VALUE"), "tasks");

			return $ID;
		}

		return false;
	}


	public static function GetFilter($arFilter)
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
					$arSqlSearch[] = CTasks::FilterCreate("TL.".$key, $DB->CharToDateFunction($val), "date", $bFullJoin, $cOperationType);
					break;

				case "USER_ID":
				case "TASK_ID":
					$arSqlSearch[] = CTasks::FilterCreate("TL.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "FIELD":
					$arSqlSearch[] = CTasks::FilterCreate("TL.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}
		}

		return $arSqlSearch;
	}


	public static function GetList($arOrder, $arFilter)
	{
		global $DB;

		$arSqlSearch = CTaskLog::GetFilter($arFilter);

		$strSql = "
			SELECT
				TL.*,
				".$DB->DateToCharFunction("TL.CREATED_DATE", "FULL")." AS CREATED_DATE,
				U.NAME AS USER_NAME,
				U.LAST_NAME AS USER_LAST_NAME,
				U.SECOND_NAME AS USER_SECOND_NAME,
				U.LOGIN AS USER_LOGIN
			FROM
				b_tasks_log TL
			INNER JOIN
				b_user U
			ON
				U.ID = TL.USER_ID
			".(sizeof($arSqlSearch) ? "WHERE ".implode(" AND ", $arSqlSearch) : "")."
		";

		if (!is_array($arOrder) || sizeof($arOrder) == 0)
			$arOrder = Array("CREATED_DATE" => "ASC");

		foreach ($arOrder as $by => $order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order != "asc")
				$order = "desc";

			if ($by == "user")
				$arSqlOrder[] = " TL.USER_ID ".$order." ";
			elseif ($by == "field")
				$arSqlOrder[] = " TL.FIELD ".$order." ";
			elseif ($by == "rand")
				$arSqlOrder[] = CTasksTools::getRandFunction();
			else
				$arSqlOrder[] = " TL.CREATED_DATE ".$order." ";
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


	public static function GetChanges($currentFields, $newFields)
	{
		$arChanges = array();

		array_walk($currentFields, array("CTaskLog", "UnifyFields"));
		array_walk($newFields, array("CTaskLog", "UnifyFields"));
		
		$currentFields["STATUS"] = $currentFields["REAL_STATUS"];

		foreach ($newFields as $key => $value)
		{
			if (array_key_exists($key, self::$arComparedFields) && $currentFields[$key] != $newFields[$key])
			{
				if ($key == "FILES")
				{
					$arDeleted = array_diff($currentFields[$key], $newFields[$key]);
					if (sizeof($arDeleted) > 0)
					{
						$rsFiles = CFile::GetList(array(), array("@ID" => implode(",", $arDeleted)));
						$arFilesNames = array();
						while ($arFile = $rsFiles->Fetch())
						{
							$arFilesNames[] = $arFile["ORIGINAL_NAME"];
						}
						if (sizeof($arFilesNames))
						{
							$arChanges["DELETED_FILES"] = array("FROM_VALUE" => implode(", ", $arFilesNames), "TO_VALUE" => false);
						}
					}

					$arNew = array_diff($newFields[$key], $currentFields[$key]);
					if (sizeof($arNew) > 0)
					{
						$rsFiles = CFile::GetList(array(), array("@ID" => implode(",", $arNew)));
						$arFilesNames = array();
						while ($arFile = $rsFiles->Fetch())
						{
							$arFilesNames[] = $arFile["ORIGINAL_NAME"];
						}
						if (sizeof($arFilesNames))
						{
							$arChanges["NEW_FILES"] = array("FROM_VALUE" => false, "TO_VALUE" => implode(", ", $arFilesNames));
						}
					}
				}
				else
				{
					if (self::$arComparedFields[$key] == "text")
					{
						$currentFields[$key] = false;
						$newFields[$key] = false;
					}
					elseif (self::$arComparedFields[$key] == "array")
					{
						$currentFields[$key] = implode(",", $currentFields[$key]);
						$newFields[$key] = implode(",", $newFields[$key]);
					}

					$arChanges[$key] = array(
						"FROM_VALUE" => $currentFields[$key] || $key == "PRIORITY" ? $currentFields[$key] : false, 
						"TO_VALUE" => $newFields[$key] || $key == "PRIORITY" ? $newFields[$key] : false
						);
				}
			}
		}

		return $arChanges;
	}


	public static function UnifyFields(&$value, $key)
	{
		if (array_key_exists($key, self::$arComparedFields))
		{
			switch (self::$arComparedFields[$key])
			{
				case "integer":
					$value = intval($value);
					break;
				case "string":
					$value = trim($value);
					break;
				case "array":
					if (!is_array($value))
					{
						$value = explode(",", $value);
					}
					$value = array_unique(array_filter(array_map("trim", $value)));
					sort($value);
					break;
				case "date":
					if (!($value = MakeTimeStamp($value)))
					{
						$value = strtotime($value);
					}
					break;
				case "bool":
					if ($value != "Y")
					{
						$value = "N";
					}
					break;
			}
		}
	}


	/**
	 * Remove all log data for given task_id
	 * @param int $in_taskId
	 * 
	 * @throws Exception on any error
	 */
	public static function DeleteByTaskId ($in_taskId)
	{
		global $DB;

		$taskId = (int) $in_taskId;

		if ((!is_numeric($in_taskId)) || ($taskId < 1))
			throw new Exception('EA_PARAMS');

		$rc = $DB->Query("DELETE FROM b_tasks_log WHERE TASK_ID = $taskId", true);
		if ($rc === false)
			throw new Exception('EA_SQLERROR');
	}
}