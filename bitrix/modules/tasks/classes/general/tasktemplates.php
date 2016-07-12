<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 * 
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @global CUser $USER
 */
global $APPLICATION, $DB, $USER;

class CTaskTemplates
{
	private $_errors = array();


	function GetErrors()
	{
		return $this->_errors;
	}


	function CheckFields(&$arFields, $ID = false, $arParams = array())
	{
		global $APPLICATION;

		if ((is_set($arFields, "TITLE") || $ID === false) && strlen($arFields["TITLE"]) <= 0)
		{
			$this->_errors[] = array("text" => GetMessage("TASKS_BAD_TITLE"), "id" => "ERROR_BAD_TASKS_TITLE");
		}

		if ($ID === false && !is_set($arFields, "RESPONSIBLE_ID"))
		{
			$this->_errors[] = array("text" => GetMessage("TASKS_BAD_RESPONSIBLE_ID"), "id" => "ERROR_TASKS_BAD_RESPONSIBLE_ID");
		}

		if (is_set($arFields, "RESPONSIBLE_ID"))
		{
			$r = CUser::GetByID($arFields["RESPONSIBLE_ID"]);
			if (!$r->Fetch())
			{
				$this->_errors[] = array("text" => GetMessage("TASKS_BAD_RESPONSIBLE_ID_EX"), "id" => "ERROR_TASKS_BAD_RESPONSIBLE_ID_EX");
			}
		}

		if (is_set($arFields, "PARENT_ID") && intval($arFields["PARENT_ID"]) > 0)
		{
			$r = CTasks::GetList(array(), array("ID" => $arFields["PARENT_ID"]));
			if (!$r->Fetch())
			{
				$this->_errors[] = array("text" => GetMessage("TASKS_BAD_PARENT_ID"), "id" => "ERROR_TASKS_BAD_PARENT_ID");
			}
		}

		if (
			isset($arFields['FILES'])
			&& isset($arParams['CHECK_RIGHTS_ON_FILES'])
			&& (
				($arParams['CHECK_RIGHTS_ON_FILES'] === true)
				|| ($arParams['CHECK_RIGHTS_ON_FILES'] === 'Y')
			)
		)
		{
			CTaskAssert::assert(
				isset($arParams['USER_ID'])
				&& CTaskAssert::isLaxIntegers($arParams['USER_ID'])
				&& ($arParams['USER_ID'] > 0)
			);

			$arFilesIds = unserialize($arFields['FILES']);

			$ar = CTaskFiles::checkFilesAccessibilityByUser($arFilesIds, (int) $arParams['USER_ID']);

			// If we have one file, that is not accessible, than emit error
			foreach ($arFilesIds as $fileId)
			{
				if (
					( ! isset($ar['f' . $fileId]) )
					|| ($ar['f' . $fileId] === false)
				)
				{
					$this->_errors[] = array('text' => GetMessage('TASKS_BAD_FILE_ID_EX'), 'id' => 'ERROR_TASKS_BAD_FILE_ID_EX');
				}
			}
		}

		if (!empty($this->_errors))
		{
			$e = new CAdminException($this->_errors);
			$APPLICATION->ThrowException($e);
			return false;
		}

		//Defaults
		if (is_set($arFields, "PRIORITY") && !in_array($arFields["PRIORITY"], Array(0, 1, 2)))
			$arFields["PRIORITY"] = 1;

		return true;
	}


	function Add($arFields, $arParams = array())
	{
		global $DB;

		$bCheckFilesPermissions = false;
		if (
			isset($arParams['CHECK_RIGHTS_ON_FILES'])
			&& (
				($arParams['CHECK_RIGHTS_ON_FILES'] === true)
				|| ($arParams['CHECK_RIGHTS_ON_FILES'] === 'Y')
			)
		)
		{
			CTaskAssert::assert(
				isset($arParams['USER_ID'])
				&& CTaskAssert::isLaxIntegers($arParams['USER_ID'])
				&& ($arParams['USER_ID'] > 0)
			);

			$bCheckFilesPermissions = true;
		}

		$arParamsForCheckFields = array(
			'CHECK_RIGHTS_ON_FILES' => $bCheckFilesPermissions
		);

		if (isset($arParams['USER_ID']))
			$arParamsForCheckFields['USER_ID'] = $arParams['USER_ID'];

		if ($this->CheckFields($arFields, false, $arParamsForCheckFields))
		{
			$arBinds = Array(
				"DESCRIPTION",
				"REPLICATE_PARAMS",
				"ACCOMPLICES",
				"AUDITORS",
				"FILES",
				"TAGS",
				"DEPENDS_ON",
				"RESPONSIBLES"
			);

			$ID = $DB->Add("b_tasks_template", $arFields, $arBinds, "tasks");
			if (isset($arFields['FILES']))
				CTaskFiles::removeTemporaryStatusForFiles(unserialize($arFields['FILES']), $arParams['USER_ID']);

			// periodic tasks
			if ($arFields["REPLICATE"] == "Y")
			{
				// Firstly, remove all agents for this template
				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ');', 'tasks');

				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ', 0);', 'tasks');

				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ', 1);', 'tasks');

				// Set up new agent
				if ($arFields['REPLICATE'] === 'Y')
				{
					$nextTime = CTasks::GetNextTime(unserialize($arFields['REPLICATE_PARAMS']));
					if ($nextTime)
					{
						CTimeZone::Disable();
						$result = CAgent::AddAgent(
							'CTasks::RepeatTaskByTemplateId(' . $ID . ', 0);', 
							'tasks', 
							'N', 		// is periodic?
							86400, 		// interval
							$nextTime, 	// datecheck
							'Y', 		// is active?
							$nextTime	// next_exec
						);
						CTimeZone::Enable();
					}
				}
			}

			return $ID;
		}

		return false;
	}


	function Update($ID, $arFields, $arParams = array())
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1)
			return false;

		$bCheckFilesPermissions = false;
		if (
			isset($arParams['CHECK_RIGHTS_ON_FILES'])
			&& (
				($arParams['CHECK_RIGHTS_ON_FILES'] === true)
				|| ($arParams['CHECK_RIGHTS_ON_FILES'] === 'Y')
			)
		)
		{
			CTaskAssert::assert(
				isset($arParams['USER_ID'])
				&& CTaskAssert::isLaxIntegers($arParams['USER_ID'])
				&& ($arParams['USER_ID'] > 0)
			);

			$bCheckFilesPermissions = true;
		}

		$arParamsForCheckFields = array(
			'CHECK_RIGHTS_ON_FILES' => $bCheckFilesPermissions
		);

		if (isset($arParams['USER_ID']))
			$arParamsForCheckFields['USER_ID'] = $arParams['USER_ID'];

		// We need understand, does REPLICATE_PARAMS changed
		$rsCurData = self::GetByID($ID);
		$arCurData = $rsCurData->Fetch();
		$isReplicateParamsChanged = 
			($arCurData['REPLICATE'] !== $arFields['REPLICATE'])
			|| ($arCurData['REPLICATE_PARAMS'] !== $arFields['REPLICATE_PARAMS']);

		if ($this->CheckFields($arFields, $ID, $arParamsForCheckFields))
		{
			unset($arFields['ID']);

			$arBinds = Array(
				'DESCRIPTION'      => $arFields['DESCRIPTION'],
				'REPLICATE_PARAMS' => $arFields['REPLICATE_PARAMS'],
				'ACCOMPLICES'      => $arFields['ACCOMPLICES'],
				'AUDITORS'         => $arFields['AUDITORS'],
				'FILES'            => $arFields['FILES'],
				'TAGS'             => $arFields['TAGS'],
				'DEPENDS_ON'       => $arFields['DEPENDS_ON']
			);

			$strUpdate = $DB->PrepareUpdate('b_tasks_template', $arFields, 'tasks');
			$strSql = "UPDATE b_tasks_template SET " . $strUpdate . " WHERE ID=" . $ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			if (isset($arFields['FILES']))
				CTaskFiles::removeTemporaryStatusForFiles(unserialize($arFields['FILES']), $arParams['USER_ID']);

			if ($isReplicateParamsChanged)
			{
				// Firstly, remove all agents for this template
				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ');', 'tasks');

				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ', 0);', 'tasks');

				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ', 1);', 'tasks');

				// Set up new agent
				if ($arFields['REPLICATE'] === 'Y')
				{
					$nextTime = CTasks::GetNextTime(unserialize($arFields['REPLICATE_PARAMS']));
					if ($nextTime)
					{
						CTimeZone::Disable();
						$result = CAgent::AddAgent(
							'CTasks::RepeatTaskByTemplateId(' . $ID . ', 0);', 
							'tasks', 
							'N', 		// is periodic?
							86400, 		// interval
							$nextTime, 	// datecheck
							'Y', 		// is active?
							$nextTime	// next_exec
						);
						CTimeZone::Enable();
					}
				}
			}

			return true;
		}

		return false;
	}


	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);

		if ($ID > 0)
		{
			$rsTemplate = CTaskTemplates::GetByID($ID);

			if ($arTemplate = $rsTemplate->Fetch())
			{
				// Remove all agents for this template
				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ');', 'tasks');

				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ', 0);', 'tasks');

				CAgent::RemoveAgent('CTasks::RepeatTaskByTemplateId(' 
					. $ID . ', 1);', 'tasks');

				if ($arTemplate["FILES"])
				{
					$arFiles = unserialize($arTemplate["FILES"]);
					if (is_array($arFiles))
					{
						$arFilesToDelete = array();
						foreach($arFiles as $file)
						{
							$rsFile = CTaskFiles::GetList(array(), array("FILE_ID" => $file));
							if (!$arFile = $rsFile->Fetch())
							{
								$arFilesToDelete[] = $file;
							}
						}
						foreach ($arFilesToDelete as $file)
						{
							CFile::Delete($file);
						}

					}
				}
				$strSql = "DELETE FROM b_tasks_template WHERE ID = ".$ID;

				if ($DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				{
					return true;
				}
			}
		}

		return false;
	}


	/**
	 * @param $arOrder
	 * @param $arFilter
	 * @return bool|CDBResult
	 *
	 * @var CDatabase $DB
	 * @var CUser $USER
	 */
	public static function GetList($arOrder, $arFilter)
	{
		global $DB, $USER;

		$arSqlSearch = CTaskTemplates::GetFilter($arFilter);

		$strSql = "
			SELECT
				TT.*,
				CU.NAME AS CREATED_BY_NAME,
				CU.LAST_NAME AS CREATED_BY_LAST_NAME,
				CU.SECOND_NAME AS CREATED_BY_SECOND_NAME,
				CU.LOGIN AS CREATED_BY_LOGIN,
				RU.NAME AS RESPONSIBLE_NAME,
				RU.LAST_NAME AS RESPONSIBLE_LAST_NAME,
				RU.SECOND_NAME AS RESPONSIBLE_SECOND_NAME,
				RU.LOGIN AS RESPONSIBLE_LOGIN
			FROM
				b_tasks_template TT
			INNER JOIN
				b_user CU ON CU.ID = TT.CREATED_BY
			INNER JOIN
				b_user RU ON RU.ID = TT.RESPONSIBLE_ID
			".(sizeof($arSqlSearch) ? "WHERE ".implode(" AND ", $arSqlSearch) : "")."
		";

		if (!is_array($arOrder))
			$arOrder = Array();

		foreach ($arOrder as $by => $order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order != "asc")
				$order = "desc";

			if ($by == "task")
				$arSqlOrder[] = " TT ".$order." ";
			elseif ($by == "depends_on")
				$arSqlOrder[] = " TT.DEPENDS_ON ".$order." ";
			elseif ($by == "rand")
				$arSqlOrder[] = CTasksTools::getRandFunction();
			else
			{
				$arSqlOrder[] = " TT.ID ".$order." ";
				$by = "id";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$arSqlOrderCnt = count($arSqlOrder);
		for ($i = 0; $i < $arSqlOrderCnt; $i++)
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


	function GetCount()
	{
		global $DB, $USER;

		if (intval($USER->GetID()))
		{
			$strSql = "SELECT COUNT(*) AS CNT FROM b_tasks_template WHERE CREATED_BY = ".intval($USER->GetID());
			if ($dbRes = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			{
				if ($arRes = $dbRes->Fetch())
				{
					return $arRes["CNT"];
				}
			}
		}

		return 0;
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
				case "CREATED_BY":
				case "TASK_ID":
				case "ID":
					$arSqlSearch[] = CTasks::FilterCreate("TT.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "RESPONSIBLE":
					$arSqlSearch[] = CTasks::FilterCreate("TT.RESPONSIBLE_ID", $val, "number", $bFullJoin, $cOperationType);
					break;

				case "TITLE":
					$arSqlSearch[] = CTasks::FilterCreate("TT.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "PRIORITY":
					$arSqlSearch[] = CTasks::FilterCreate("TT.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}
		}

		return $arSqlSearch;
	}


	function GetByID($ID)
	{
		return CTaskTemplates::GetList(Array(), Array("ID" => $ID));
	}
}