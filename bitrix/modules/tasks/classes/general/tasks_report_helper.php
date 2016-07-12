<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */


if (!CModule::IncludeModule('report'))
	return;

class CTasksReportHelper extends CReportHelper
{
	public static function getEntityName()
	{
		return 'Bitrix\Tasks\Task';
	}

	public static function getOwnerId()
	{
		return 'TASKS';
	}

	public static function getColumnList()
	{
		IncludeModuleLangFile(__FILE__);

		return array(
			'ID',
			'TITLE',
			'PRIORITY',
			'STATUS',
			'STATUS_PSEUDO',
			'STATUS_SUB' => array(
				'IS_NEW',
				'IS_OPEN',
				'IS_RUNNING',
				'IS_FINISHED',
				'IS_OVERDUE',
				'IS_MARKED',
				'IS_EFFECTIVE_PRCNT'
			),
			'ADD_IN_REPORT',
			'CREATED_DATE',
			'START_DATE_PLAN',
			'END_DATE_PLAN',
			'DURATION_PLAN_HOURS',
			'DATE_START',
			'CHANGED_DATE',
			'CLOSED_DATE',
			'DEADLINE',
			'DURATION',
			'DURATION_FOR_PERIOD',
			'MARK',
			'Tag:TASK.NAME',
			'GROUP' => array(
				'ID',
				'NAME'
			),
			'CREATED_BY_USER' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'RESPONSIBLE' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'Member:TASK_COWORKED.USER' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'CHANGED_BY_USER' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'STATUS_CHANGED_BY_USER' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			),
			'CLOSED_BY_USER' => array(
				'ID',
				'SHORT_NAME',
				'NAME',
				'LAST_NAME',
				'WORK_POSITION'
			)
		);
	}

	public static function getDefaultColumns()
	{
		return array(
			array('name' => 'TITLE'),
			array('name' => 'PRIORITY'),
			array('name' => 'RESPONSIBLE.SHORT_NAME'),
			array('name' => 'STATUS_PSEUDO')
		);
	}

	public static function getCalcVariations()
	{
		return array_merge(parent::getCalcVariations(), array(
			'IS_OVERDUE_PRCNT' => array(),
			'IS_MARKED_PRCNT' => array(),
			'IS_EFFECTIVE_PRCNT' => array(),
			'Tag:TASK.NAME' => array(
				'COUNT_DISTINCT',
				'GROUP_CONCAT'
			),
			'Member:TASK_COWORKED.USER.SHORT_NAME' => array(
				'COUNT_DISTINCT',
				'GROUP_CONCAT'
			)
		));
	}

	public static function getCompareVariations()
	{
		return array_merge(parent::getCompareVariations(), array(
			'STATUS' => array(
				'EQUAL',
				'NOT_EQUAL'
			),
			'STATUS_PSEUDO' => array(
				'EQUAL',
				'NOT_EQUAL'
			),
			'PRIORITY' => array(
				'EQUAL',
				'NOT_EQUAL'
			),
			'MARK' => array(
				'EQUAL',
				'NOT_EQUAL'
			)
		));
	}

	public static function buildHTMLSelectTreePopup($tree, $withReferencesChoose = false, $level = 0)
	{
		//return parent::buildHTMLSelectTreePopup($tree, $withReferencesChoose, $level);

		/* remove it when PHP 5.3 available */
		$indent = str_repeat('&nbsp;', ($level+1)*2);

		$html = '';

		$i = 0;

		foreach($tree as $treeElem)
		{
			$isLastElem = (++$i == count($tree));

			//list($fieldDefinition, $field, $branch) = $treeElem;
			$fieldDefinition = $treeElem['fieldName'];
			$branch = $treeElem['branch'];

			$fieldType = $treeElem['field'] ? $treeElem['field']->GetDataType() : null;

			if (empty($branch))
			{
				// single field
				$htmlElem = self::buildSelectTreePopupElelemnt($treeElem['humanTitle'], $treeElem['fullHumanTitle'], $fieldDefinition, $fieldType);

				if ($isLastElem && $level > 0)
				{
					$htmlElem = str_replace(
						'<div class="reports-add-popup-item">',
						'<div class="reports-add-popup-item reports-add-popup-item-last">',
						$htmlElem
					);

				}

				$html .= $htmlElem;
			}
			else
			{
				// add branch

				$scalarTypes = array('integer', 'string', 'boolean', 'datetime');
				if ($withReferencesChoose &&
					(in_array($fieldType, $scalarTypes) || empty($fieldType))
				)
				{
					// ignore virtual branches (without references)
					continue;
				}

				$html .= sprintf('<div class="reports-add-popup-item reports-add-popup-it-node">
					<span class="reports-add-popup-arrow"></span><span
						class="reports-add-popup-it-text">%s</span>
				</div>', $treeElem['humanTitle']);

				$html .= '<div class="reports-add-popup-it-children">';

				// add self
				if ($withReferencesChoose)
				{
					// replace by static:: when php 5.3 available
					$html .= self::buildSelectTreePopupElelemnt(GetMessage('REPORT_CHOOSE').'...', $treeElem['humanTitle'], $fieldDefinition, $fieldType);
				}

				// replace by static:: when php 5.3 available
				$html .= self::buildHTMLSelectTreePopup($branch, $withReferencesChoose, $level+1);

				$html .= '</div>';
			}
		}

		return $html;
		/* \remove it */
	}

	/* remove it when PHP 5.3 available */
	public static function buildSelectTreePopupElelemnt($humanTitle, $fullHumanTitle, $fieldDefinition, $fieldType)
	{
		// replace by static:: when php 5.3 available
		$grcFields = self::getGrcColumns();

		$htmlCheckbox = sprintf(
			'<input type="checkbox" name="%s" title="%s" fieldType="%s" isGrc="%s" class="reports-add-popup-checkbox" />',
			htmlspecialcharsbx($fieldDefinition), htmlspecialcharsbx($fullHumanTitle), htmlspecialcharsbx($fieldType),
			(int) in_array($fieldDefinition, $grcFields)
		);

		$htmlElem = sprintf('<div class="reports-add-popup-item">
			<span class="reports-add-pop-left-bord"></span><span
			class="reports-add-popup-checkbox-block">
				%s
			</span><span class="reports-add-popup-it-text">%s</span>
		</div>', $htmlCheckbox, $humanTitle);

		return $htmlElem;
	}
	/* \remove it */

	public static function beforeViewDataQuery(&$select, &$filter, &$group, &$order, &$limit, &$options, &$runtime)
	{
		parent::beforeViewDataQuery($select, $filter, $group, $order, $limit, $options, $runtime);

		global $USER, $DB;

		$permFilter = array(
			'LOGIC' => 'OR'
		);

		// owner permission
		if (isset($_GET['select_my_tasks']) ||
			(!isset($_GET['select_my_tasks']) && !isset($_GET['select_depts_tasks']) && !isset($_GET['select_group_tasks']))
		)
		{
			$runtime['IS_TASK_COWORKER'] = array(
				'data_type' => 'integer',
				'expression' => array("(CASE WHEN EXISTS("
					."SELECT 'x' FROM b_tasks_member TM "
					."WHERE TM.TASK_ID = ".$DB->escL."tasks_task".$DB->escR.".ID AND TM.USER_ID = ".$USER->GetID()." AND TM.TYPE = 'A'"
				.") THEN 1 ELSE 0 END)")
			);

			$permFilter[] = array(
				'LOGIC' => 'OR',
				'=RESPONSIBLE_ID' => $USER->GetID(),
				'=IS_TASK_COWORKER' => 1
			);
		}

		// own departments permission
		if (isset($_GET['select_depts_tasks']))
		{
			$permFilterDepts = array(
				'LOGIC' => 'OR',
				'=CREATED_BY' => $USER->GetID()
			);

			$deptsPermSql = CTasks::GetSubordinateSql('__ULTRAUNIQUEPREFIX__');

			if (strlen($deptsPermSql))
			{
				$deptsPermSql = "EXISTS(".$deptsPermSql.")";
				$deptsPermSql = str_replace('__ULTRAUNIQUEPREFIX__T.', $DB->escL.'tasks_task'.$DB->escR.'.', $deptsPermSql);
				$deptsPermSql = str_replace('__ULTRAUNIQUEPREFIX__', '', $deptsPermSql);

				$runtime['IS_SUBORDINATED_TASK'] = array(
					'data_type' => 'integer',
					'expression' => array("(CASE WHEN ".$deptsPermSql." THEN 1 ELSE 0 END)")
				);

				$permFilterDepts[] = array(
					'!RESPONSIBLE_ID' => $USER->GetID(),
					'=IS_SUBORDINATED_TASK' => 1
				);
			}

			$permFilter[] = $permFilterDepts;
		}

		// group permission
		if (isset($_GET['select_group_tasks']))
		{
			$allowedGroups = CTasks::GetAllowedGroups();
			$permFilter[] = array('=GROUP_ID' => $allowedGroups);
		}

		// concat permissions with common filter
		$filter[] = $permFilter;
	}

	/* remove it when PHP 5.3 available */
	public static function formatResults(&$rows, &$columnInfo, $total)
	{
		foreach ($rows as &$row)
		{
			foreach ($row as $k => &$v)
			{
				if (!array_key_exists($k, $columnInfo))
				{
					continue;
				}

				$cInfo = $columnInfo[$k];

				if (is_array($v))
				{
					foreach ($v as &$subv)
					{
						self::formatResultValue($k, $subv, $row, $cInfo, $total);
					}
				}
				else
				{
					self::formatResultValue($k, $v, $row, $cInfo, $total);
				}
			}
		}

		unset($row, $v, $subv);
	}
	/* \remove it */

	public static function formatResultValue($k, &$v, &$row, &$cInfo, $total)
	{
		$field = $cInfo['field'];

		if ($k == 'STATUS' || $k == 'STATUS_PSEUDO' || $k == 'PRIORITY')
		{
			if (empty($cInfo['aggr']) || $cInfo['aggr'] !== 'COUNT_DISTINCT')
			{
				$v = htmlspecialcharsbx(GetMessage($field->getLangCode().'_VALUE_'.$v));
			}
		}
		elseif (strpos($k, 'DURATION_PLAN_HOURS') !== false && !strlen($cInfo['prcnt']))
		{
			if (!empty($v))
			{
				$days = floor($v/24);
				$hours = $v - $days*24;
				$v = '';
				if (!empty($days)) $v .= $days.GetMessage('TASKS_REPORT_DURATION_DAYS');
				if (!empty($hours))
				{
					if (!empty($days)) $v .= ' ';
					$v .= $hours.GetMessage('TASKS_REPORT_DURATION_HOURS');
				}
			}
		}
		elseif (strpos($k, 'DURATION') !== false && !strlen($cInfo['prcnt']))
		{
			//$row[$k] = FormatDate('Hdiff', time()-$row[$k]*60);
			$hours = floor($v/60);
			$minutes = date('i', ($v % 60)*60);
			$v = $hours.':'.$minutes;
		}
		elseif ($k == 'MARK' && empty($cInfo['aggr']))
		{
			if (HasMessage($field->getLangCode().'_VALUE_'.$v))
			{
				$v = GetMessage($field->getLangCode().'_VALUE_'.$v);
			}
			else
			{
				$v = GetMessage($field->getLangCode().'_VALUE_NONE');
			}
		}
		else
		{
			parent::formatResultValue($k, $v, $row, $cInfo, $total);
		}
	}

	public static function formatResultsTotal(&$total, &$columnInfo)
	{
		parent::formatResultsTotal($total, $columnInfo);

		foreach ($total as $k => $v)
		{
			// remove prefix TOTAL_
			$original_k = substr($k, 6);

			$cInfo = $columnInfo[$original_k];

			if (strpos($k, 'DURATION_PLAN_HOURS') !== false && !strlen($cInfo['prcnt']))
			{
				unset($total[$k]);
			}
			elseif (strpos($k, 'DURATION') !== false && !strlen($cInfo['prcnt']))
			{
				$hours = floor($v/60);
				$minutes = date('i', ($v % 60)*60);
				$total[$k] = $hours.':'.$minutes;
			}
		}
	}

	public static function getPeriodFilter($date_from, $date_to)
	{
		$filter = array();

		if (!is_null($date_from) && !is_null($date_to))
		{
			$filter = array(
				'LOGIC' => 'OR',
				array(
					'LOGIC' => 'AND',
					'>=CREATED_DATE' => $date_from,
					'<=CREATED_DATE' => $date_to
				),
				array(
					'LOGIC' => 'AND',
					'>=CLOSED_DATE' => $date_from,
					'<=CLOSED_DATE' => $date_to
				),
				array(
					'LOGIC' => 'AND',
					'<CREATED_DATE' => $date_from,
					array(
						'LOGIC' => 'OR',
						'>CLOSED_DATE' => $date_to,
						'=CLOSED_DATE' => ''
					)
				)
			);
		}
		else if (!is_null($date_from))
		{
			$filter = array(
				'LOGIC' => 'OR',
				'>=CREATED_DATE' => $date_from,
				'>=CLOSED_DATE' => $date_from,
				'=CLOSED_DATE' => ''
			);
		}
		else if (!is_null($date_to))
		{
			$filter = array(
				'LOGIC' => 'OR',
				'<=CREATED_DATE' => $date_to,
				'<=CLOSED_DATE' => $date_to
			);
		}

		return $filter;
	}

	public static function getDefaultElemHref($elem, $fList)
	{
		$href = null;

		if (empty($elem['aggr']) || $elem['aggr'] == 'GROUP_CONCAT')
		{
			$field = $fList[$elem['name']];

			if ($field->getEntity()->getName() == 'Task' && $elem['name'] == 'TITLE')
			{
				$href = array('pattern' => '/company/personal/user/#RESPONSIBLE_ID#/tasks/task/view/#ID#/');
			}
			elseif ($field->getEntity()->getName() == 'User')
			{
				if ($elem['name'] == 'CREATED_BY_USER.SHORT_NAME')
				{
					$href = array('pattern' => '/company/personal/user/#CREATED_BY#/');
				}
				elseif ($elem['name'] == 'RESPONSIBLE.SHORT_NAME')
				{
					$href = array('pattern' => '/company/personal/user/#RESPONSIBLE_ID#/');
				}
				elseif ($elem['name'] == 'Member:TASK_COWORKED.USER.SHORT_NAME')
				{
					$href = array('pattern' => '/company/personal/user/#Member:TASK_COWORKED.USER.ID#/');
				}
				elseif ($elem['name'] == 'CHANGED_BY_USER.SHORT_NAME')
				{
					$href = array('pattern' => '/company/personal/user/#CHANGED_BY#/');
				}
				elseif ($elem['name'] == 'STATUS_CHANGED_BY_USER.SHORT_NAME')
				{
					$href = array('pattern' => '/company/personal/user/#STATUS_CHANGED_BY#/');
				}
				elseif ($elem['name'] == 'CLOSED_BY_USER.SHORT_NAME')
				{
					$href = array('pattern' => '/company/personal/user/#CLOSED_BY#/');
				}
			}
			elseif ($field->getEntity()->getName() == 'Group' && $elem['name'] == 'GROUP.NAME')
			{
				$href = array('pattern' => '/workgroups/group/#GROUP_ID#/');
			}
		}

		return $href;
	}

	public static function getDefaultReports()
	{
		IncludeModuleLangFile(__FILE__);

		$reports = array(
			'11.0.1' => array(
				array(
					'title' => GetMessage('TASKS_REPORT_DEFAULT_2'),
					'mark_default' => 2,
					'settings' => unserialize('a:6:{s:6:"entity";s:5:"Tasks";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:4:{i:0;a:1:{s:4:"name";s:22:"RESPONSIBLE.SHORT_NAME";}i:1;a:1:{s:4:"name";s:10:"GROUP.NAME";}i:2;a:2:{s:4:"name";s:8:"DURATION";s:4:"aggr";s:3:"SUM";}i:3;a:2:{s:4:"name";s:10:"IS_RUNNING";s:4:"aggr";s:3:"SUM";}}s:6:"filter";a:1:{i:0;a:2:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:5:"GROUP";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:0;s:5:"limit";N;}')
				),
				array(
					'title' => GetMessage('TASKS_REPORT_DEFAULT_3'),
					'mark_default' => 3,
					'settings' => unserialize('a:6:{s:6:"entity";s:5:"Tasks";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:8:{i:0;a:1:{s:4:"name";s:22:"RESPONSIBLE.SHORT_NAME";}i:1;a:1:{s:4:"name";s:5:"TITLE";}i:2;a:1:{s:4:"name";s:13:"STATUS_PSEUDO";}i:3;a:1:{s:4:"name";s:8:"PRIORITY";}i:4;a:1:{s:4:"name";s:12:"CREATED_DATE";}i:5;a:1:{s:4:"name";s:10:"DATE_START";}i:6;a:1:{s:4:"name";s:11:"CLOSED_DATE";}i:7;a:1:{s:4:"name";s:8:"DEADLINE";}}s:6:"filter";a:1:{i:0;a:3:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"RESPONSIBLE";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:5:"GROUP";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:0;s:5:"limit";N;}')
				)
			),
			'11.0.3' => array(
				array(
					'title' => GetMessage('TASKS_REPORT_DEFAULT_4'),
					'mark_default' => 4,
					'settings' => unserialize('a:6:{s:6:"entity";s:5:"Tasks";s:6:"period";a:2:{s:4:"type";s:5:"month";s:5:"value";N;}s:6:"select";a:7:{i:0;a:2:{s:4:"name";s:22:"RESPONSIBLE.SHORT_NAME";s:5:"alias";s:9:"SSSSSSSSS";}i:1;a:3:{s:4:"name";s:6:"IS_NEW";s:5:"alias";s:5:"SSSSS";s:4:"aggr";s:3:"SUM";}i:2;a:3:{s:4:"name";s:10:"IS_RUNNING";s:5:"alias";s:8:"SSSSSSSS";s:4:"aggr";s:3:"SUM";}i:3;a:3:{s:4:"name";s:11:"IS_FINISHED";s:5:"alias";s:9:"SSSSSSSSS";s:4:"aggr";s:3:"SUM";}i:4;a:4:{s:4:"name";s:10:"IS_OVERDUE";s:5:"alias";s:10:"SSSSSSSSSS";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"2";}i:5;a:4:{s:4:"name";s:9:"IS_MARKED";s:5:"alias";s:7:"SSSSSSS";s:4:"aggr";s:3:"SUM";s:5:"prcnt";s:1:"2";}i:6;a:2:{s:4:"name";s:18:"IS_EFFECTIVE_PRCNT";s:5:"alias";s:13:"SSSSSSSSSSSSS";}}s:6:"filter";a:1:{i:0;a:4:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"RESPONSIBLE";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:5:"GROUP";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:13:"ADD_IN_REPORT";s:7:"compare";s:5:"EQUAL";s:5:"value";s:4:"true";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:0;s:5:"limit";N;}')
				)
			),
			'11.0.8' => array(
				array(
					'title' => GetMessage('TASKS_REPORT_DEFAULT_5'),
					'mark_default' => 5,
					'settings' => unserialize('a:6:{s:6:"entity";s:5:"Tasks";s:6:"period";a:2:{s:4:"type";s:9:"month_ago";s:5:"value";N;}s:6:"select";a:6:{i:0;a:1:{s:4:"name";s:5:"TITLE";}i:2;a:1:{s:4:"name";s:22:"RESPONSIBLE.SHORT_NAME";}i:7;a:1:{s:4:"name";s:8:"PRIORITY";}i:3;a:1:{s:4:"name";s:13:"STATUS_PSEUDO";}i:5;a:1:{s:4:"name";s:8:"DURATION";}i:6;a:1:{s:4:"name";s:4:"MARK";}}s:6:"filter";a:1:{i:0;a:5:{i:0;a:5:{s:4:"type";s:5:"field";s:4:"name";s:11:"RESPONSIBLE";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}i:1;a:5:{s:4:"type";s:5:"field";s:4:"name";s:8:"PRIORITY";s:7:"compare";s:5:"EQUAL";s:5:"value";s:1:"1";s:10:"changeable";s:1:"1";}i:2;a:5:{s:4:"type";s:5:"field";s:4:"name";s:13:"STATUS_PSEUDO";s:7:"compare";s:5:"EQUAL";s:5:"value";s:1:"5";s:10:"changeable";s:1:"1";}i:3;a:5:{s:4:"type";s:5:"field";s:4:"name";s:5:"GROUP";s:7:"compare";s:5:"EQUAL";s:5:"value";s:0:"";s:10:"changeable";s:1:"1";}s:5:"LOGIC";s:3:"AND";}}s:4:"sort";i:0;s:5:"limit";N;}')
				)
			)
		);

		foreach ($reports as $version => &$vreports)
		{
			foreach ($vreports as $num => &$report)
			{
				if ($version === '11.0.3' && $num === 0)
				{
					$report['settings']['select'][0]['alias'] = GetMessage('TASKS_REPORT_EFF_EMPLOYEE');
					$report['settings']['select'][1]['alias'] = GetMessage('TASKS_REPORT_EFF_NEW');
					$report['settings']['select'][2]['alias'] = GetMessage('TASKS_REPORT_EFF_OPEN');
					$report['settings']['select'][3]['alias'] = GetMessage('TASKS_REPORT_EFF_CLOSED');
					$report['settings']['select'][4]['alias'] = GetMessage('TASKS_REPORT_EFF_OVERDUE');
					$report['settings']['select'][5]['alias'] = GetMessage('TASKS_REPORT_EFF_MARKED');
					$report['settings']['select'][6]['alias'] = GetMessage('TASKS_REPORT_EFF_EFFICIENCY');
				}

				// remove reports, which not work in MSSQL
				global $DBType;
				if (ToUpper($DBType) === 'MSSQL')
				{
					if ($version === '11.0.1' && $report['mark_default'] === 2)
					{
						unset($vreports[$num]);
					}
				}
			}
		}

		return $reports;
	}

	public static function getFirstVersion()
	{
		return '11.0.1';
	}

	public static function getCurrentVersion()
	{
		include($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/tasks/install/version.php");
		return $arModuleVersion['VERSION'];
	}
}

