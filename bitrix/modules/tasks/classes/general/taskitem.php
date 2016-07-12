<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */


interface CTaskItemInterface
{
	public function getTaskData($returnEscapedData = true);
	public function getAllowedTaskActions();
	public function startExecution();
	public function defer();
	public function complete();
	public function delete();
	public function update($arNewTaskData);
	public function accept();
	public function delegate($newResponsibleId);
	public function decline($reason = '');
	public function renew();
	public function approve();
	public function disapprove();

	/**
	 * Remove file attached to task
	 * 
	 * @param integer $fileId
	 * @throws TasksException
	 * @throws CTaskAssertException
	 */
	public function removeAttachedFile($fileId);

	/**
	 * @param integer $format one of constants: 
	 * CTaskItem::DESCR_FORMAT_RAW - give description of task "as is" (HTML or BB-code, depends on task)
	 * CTaskItem::DESCR_FORMAT_HTML - always return HTML (even if task in BB-code)
	 * CTaskItem::DESCR_FORMAT_PLAIN_TEXT - always return plain text (all HTML/BBCODE tags are stripped)
	 * can be omitted. Value by default is CTaskItem::DESCR_FORMAT_HTML.
	 * 
	 * @throws CTaskAssertException if invalid format value given
	 * 
	 * @return string description of the task (HTML will be sanitized accord to task module settings)
	 */
	public function getDescription($format = CTaskItem::DESCR_FORMAT_HTML);

	public function addElapsedTime($arFields);
}


final class CTaskItem implements CTaskItemInterface
{
	// Actions
	const ACTION_ACCEPT     = 0x01;
	const ACTION_DECLINE    = 0x02;
	const ACTION_COMPLETE   = 0x03;
	const ACTION_APPROVE    = 0x04;		// closes task
	const ACTION_DISAPPROVE = 0x05;		// perform ACTION_RENEW
	const ACTION_START      = 0x06;
	const ACTION_DELEGATE   = 0x07;
	const ACTION_REMOVE     = 0x08;
	const ACTION_EDIT       = 0x09;
	const ACTION_DEFER      = 0x0A;
	const ACTION_RENEW      = 0x0B;		// switch tasks to new or accepted state (depends on subordination)
	const ACTION_CREATE     = 0x0C;
	const ACTION_CHANGE_DEADLINE = 0x0D;

	const ROLE_NOT_A_MEMBER = 0x01;		// not a member of the task
	const ROLE_DIRECTOR     = 0x02;
	const ROLE_RESPONSIBLE  = 0x04;
	const ROLE_ACCOMPLICE   = 0x08;
	const ROLE_AUDITOR      = 0x10;

	const DESCR_FORMAT_RAW        = 0x01;		// give description of task "as is" (HTML or BB-code, depends on task)
	const DESCR_FORMAT_HTML       = 0x02;		// always return HTML (even if task in BB-code)
	const DESCR_FORMAT_PLAIN_TEXT = 0x03;		// always return plain text (all HTML/BBCODE tags are stripped)

	static $bSocialNetworkModuleIncluded = null;

	private $taskId = false;
	private $executiveUserId = false;	// User id under which rights will be checked

	// Lazy init:
	private $arTaskData = null;		// Task data

	// Very lazy init (not inited on arTaskData init, inited on demand):
	private $arTaskAllowedActions = null;		// Allowed actions on task
	private $arTaskDataEscaped = null;


	public function __construct($taskId, $executiveUserId)
	{
		CTaskAssert::assertLaxIntegers($taskId, $executiveUserId);
		CTaskAssert::assert( ($taskId > 0) && ($executiveUserId > 0) );

		$this->taskId = (int) $taskId;
		$this->executiveUserId = (int) $executiveUserId;
	}


	/**
	 * Create new task and return instance for it
	 *
	 * @param array $arNewTaskData new task fields
	 * @param integer $executiveUserId, put 1 (admin) to skip rights check
	 * @throws TasksException - on access denied, task not exists
	 * @throws CTaskAssertException
	 * @throws Exception - on unexpected error
	 *
	 * @return object of class CTaskItem
	 */
	public static function add($arNewTaskData, $executiveUserId)
	{
		CTaskAssert::assertLaxIntegers($executiveUserId);
		CTaskAssert::assert($executiveUserId > 0);

		// Use of BB code by default, HTML is deprecated, 
		// but supported for backward compatibility when tasks created
		// from template or as copy of old task with HTML-description.
		if (
			isset($arNewTaskData['DESCRIPTION_IN_BBCODE'])
			&& ($arNewTaskData['DESCRIPTION_IN_BBCODE'] === 'N')	// HTML mode requested
			&& isset($arNewTaskData['DESCRIPTION'])
			&& ($arNewTaskData['DESCRIPTION'] !== '')		// allow HTML mode if there is description
			&& (strpos($arNewTaskData['DESCRIPTION'], '<') !== false)	// with HTML tags
		)
		{
			$arNewTaskData['DESCRIPTION_IN_BBCODE'] = 'N';			// Set HTML mode
		}
		else
			$arNewTaskData['DESCRIPTION_IN_BBCODE'] = 'Y';

		if ( ! isset($arNewTaskData['CREATED_BY']) )
			$arNewTaskData['CREATED_BY'] = $executiveUserId;

		if (
			($arNewTaskData['RESPONSIBLE_ID'] != $executiveUserId)
			&& ($arNewTaskData['CREATED_BY'] != $executiveUserId)
			&& ( ! CTasksTools::IsAdmin($executiveUserId) )
			&& ( ! CTasksTools::IsPortalB24Admin($executiveUserId) )
		)
		{
			throw new TasksException(
				'',
				TasksException::TE_ACCESS_DENIED
			);
		}

		if ( ! array_key_exists('GUID', $arNewTaskData) )
			$arNewTaskData['GUID'] = CTasksTools::genUuid();

		$arParams = array(
			'USER_ID'			   => $executiveUserId,
			'CHECK_RIGHTS_ON_FILES' => true
		);

		$o = new CTasks();
		$rc = $o->Add($arNewTaskData, $arParams);
		if ( ! ($rc > 0) )
		{
			throw new TasksException(
				serialize($o->GetErrors()),
				TasksException::TE_ACTION_FAILED_TO_BE_PROCESSED
					| TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE
			);
		}

		return (new CTaskItem( (int) $rc, $executiveUserId));
	}


	public function __wakeup()
	{
		$this->markCacheAsDirty();
	}


	public function __sleep()
	{
		$this->markCacheAsDirty();
		return (array('taskId', 'executiveUserId', 'arTaskData', 'arTaskAllowedActions'));
	}


	// prevent clone of object
	private function __clone(){}


	public function getId()
	{
		return ($this->taskId);
	}


	/**
	 * Get task data (read from DB on demand)
	 */
	public function getTaskData($returnEscapedData = true)
	{
		// Preload data, if it isn't in cache
		if ($this->arTaskData === null)
		{
			$this->markCacheAsDirty();

			// Load task data
			$bCheckPermissions = true;
			$arParams = array('USER_ID' => $this->executiveUserId);

			$rs = CTasks::GetByID($this->taskId, $bCheckPermissions, $arParams);
			if ( ! ($arTask = $rs->Fetch()) )
				throw new TasksException('', TasksException::TE_TASK_NOT_FOUND_OR_NOT_ACCESSIBLE);

			$this->arTaskData = $arTask;
		}

		if ($returnEscapedData)
		{
			// Prepare escaped data on-demand
			if ($this->arTaskDataEscaped === null)
			{
				foreach ($this->arTaskData as $field => $value)
				{
					$this->arTaskDataEscaped['~' . $field] = $value;

					if ($field === 'DESCRIPTION')
						$this->arTaskDataEscaped[$field] = $this->getDescription();
					else
						$this->arTaskDataEscaped[$field] = htmlspecialcharsex($value);
				}
			}

			return ($this->arTaskDataEscaped);
		}
		else
			return ($this->arTaskData);
	}


	public function getDescription($format = self::DESCR_FORMAT_HTML)
	{
		$rc = null;

		CTaskAssert::assert(in_array(
			$format,
			array(self::DESCR_FORMAT_RAW, self::DESCR_FORMAT_HTML, self::DESCR_FORMAT_PLAIN_TEXT),
			true
		));

		$arTask = $this->getTaskData($bSpecialChars = false);

		$description = $arTask['DESCRIPTION'];

		if ($format === self::DESCR_FORMAT_RAW)
			return ($description);

		// Now, convert description to HTML
		if ($arTask['DESCRIPTION_IN_BBCODE'] === 'Y')
		{
			$parser = new CTextParser();
			$description = str_replace(
				"\t",
				' &nbsp; &nbsp;',
				$parser->convertText($description)
			);
		}
		else
			$description = CTasksTools::SanitizeHtmlDescriptionIfNeed($description);

		if ($format === self::DESCR_FORMAT_HTML)
			$rc = $description;
		elseif ($format === self::DESCR_FORMAT_PLAIN_TEXT)
		{
			$rc = strip_tags(
				str_replace(
					array('<br>', '<br/>', '<br />'),
					"\n",
					$description
				)
			);
		}
		else
		{
			CTaskAssert::log(
				'CTaskItem->getTaskDescription(): unexpected format: ' . $format,
				CTaskAssert::ELL_ERROR
			);

			CTaskAssert::assert(false);
		}

		return ($rc);
	}


	/**
	 * Get task data (read from DB on demand)
	 */
	public function getAllowedTaskActions()
	{
		// Lazy load and cache allowed actions list
		if ($this->arTaskAllowedActions === null)
		{
			$arTaskData = $this->getTaskData($bSpecialChars = false);
			$bmUserRoles = self::getUserRoles($arTaskData, $this->executiveUserId);
			$arBaseAllowedActions = self::getBaseAllowedActions();
			$arActualBaseAllowedActions = $arBaseAllowedActions[$arTaskData['REAL_STATUS']];

			$arAllowedActions = array();

			$mergesCount = 0;
			foreach ($arActualBaseAllowedActions as $userRole => $arActions)
			{
				if ($userRole & $bmUserRoles)
				{
					$arAllowedActions = array_merge($arAllowedActions, $arActions);
					++$mergesCount;
				}
			}

			if ($mergesCount > 1)
				$arAllowedActions = array_unique($arAllowedActions);

			$isAdmin = CTasksTools::IsAdmin($this->executiveUserId)
				|| CTasksTools::IsPortalB24Admin($this->executiveUserId);

			if (self::$bSocialNetworkModuleIncluded === null)
				self::$bSocialNetworkModuleIncluded = CModule::IncludeModule('socialnetwork');

			// Admin always can edit and remove, also implement rights from task group
			if ( ! in_array(self::ACTION_REMOVE, $arAllowedActions, true) )
			{
				if (
					$isAdmin
					|| (
						($arTaskData['GROUP_ID'] > 0)
						&& self::$bSocialNetworkModuleIncluded
						&& CSocNetFeaturesPerms::CanPerformOperation(
							$this->executiveUserId, SONET_ENTITY_GROUP, 
							$arTaskData['GROUP_ID'], 'tasks', 'delete_tasks'
						)
					)
				)
				{
					$arAllowedActions[] = self::ACTION_REMOVE;
				}
			}

			if ( ! in_array(self::ACTION_EDIT, $arAllowedActions, true) )
			{
				if (
					$isAdmin
					|| (
						($arTaskData['GROUP_ID'] > 0)
						&& self::$bSocialNetworkModuleIncluded
						&& CSocNetFeaturesPerms::CanPerformOperation(
							$this->executiveUserId, SONET_ENTITY_GROUP, 
							$arTaskData['GROUP_ID'], 'tasks', 'edit_tasks'
						)
					)
				)
				{
					$arAllowedActions[] = self::ACTION_EDIT;
				}
			}

			// User can change deadline, if ...
			if (
				// he can edit task
				in_array(self::ACTION_EDIT, $arAllowedActions, true)
				|| (
					// or this options is set to Y and ...
					($arTaskData['ALLOW_CHANGE_DEADLINE'] === 'Y')
					&& (
						// user is responsible
						($arTaskData['RESPONSIBLE_ID'] == $this->executiveUserId)
						// or user is a manager of responsible
						|| in_array(
							(int) $arTaskData['RESPONSIBLE_ID'],
							self::getSubUsers($this->executiveUserId),
							true
						)
					)
				)
			)
			{
				$arAllowedActions[] = self::ACTION_CHANGE_DEADLINE;
			}

			$this->arTaskAllowedActions = $arAllowedActions;		
		}

		return ($this->arTaskAllowedActions);
	}


	public function getAllowedTaskActionsAsStrings()
	{
		static $arStringsMap = array(
			self::ACTION_ACCEPT     => 'ACTION_ACCEPT',
			self::ACTION_DECLINE    => 'ACTION_DECLINE',
			self::ACTION_COMPLETE   => 'ACTION_COMPLETE',
			self::ACTION_APPROVE    => 'ACTION_APPROVE',
			self::ACTION_DISAPPROVE => 'ACTION_DISAPPROVE',
			self::ACTION_START      => 'ACTION_START',
			self::ACTION_DELEGATE   => 'ACTION_DELEGATE',
			self::ACTION_REMOVE     => 'ACTION_REMOVE',
			self::ACTION_EDIT       => 'ACTION_EDIT',
			self::ACTION_DEFER      => 'ACTION_DEFER',
			self::ACTION_RENEW      => 'ACTION_RENEW',
			self::ACTION_CREATE     => 'ACTION_CREATE',
		);

		$arAllowedActions = $this->getAllowedTaskActions();

		$arResult = array();

		foreach ($arStringsMap as $actionCode => $actionString)
		{
			if (in_array($actionCode, $arAllowedActions, true))
				$arResult[$actionString] = true;	// action is allowed
			else
				$arResult[$actionString] = false;	// not allowed
		}

		return ($arResult);
	}


	public function isActionAllowed($actionId)
	{
		$bActionAllowed = false;

		if (in_array($actionId, $this->getAllowedTaskActions(), true))
			$bActionAllowed = true;

		return ($bActionAllowed);
	}

	/**
	 * Remove task
	 */
	public function delete()
	{
		$this->proceedAction(self::ACTION_REMOVE);
	}


	/**
	 * Delegate task to some responsible person (only subordinate users allowed)
	 * 
	 * @param integer $newResponsibleId user id of new responsible person
	 * @throws TasksException, including codes TE_TRYED_DELEGATE_TO_WRONG_PERSON,
	 * TE_ACTION_NOT_ALLOWED, TE_ACTION_FAILED_TO_BE_PROCESSED, 
	 * TE_TASK_NOT_FOUND_OR_NOT_ACCESSIBLE
	 */
	public function delegate($newResponsibleId)
	{
		$this->proceedAction(
			self::ACTION_DELEGATE,
			array('RESPONSIBLE_ID' => $newResponsibleId)
		);
	}


	/**
	 * Decline task
	 * 
	 * @param string $reason reason by which task declined
	 * @throws TasksException, including codes TE_ACTION_NOT_ALLOWED,
	 * TE_ACTION_FAILED_TO_BE_PROCESSED, 
	 * TE_TASK_NOT_FOUND_OR_NOT_ACCESSIBLE
	 */
	public function decline($reason = '')
	{
		$this->proceedAction(
			self::ACTION_DECLINE,
			array('DECLINE_REASON' => $reason)
		);
	}


	public function startExecution()
	{
		$this->proceedAction(self::ACTION_START);
	}


	public function defer()
	{
		$this->proceedAction(self::ACTION_DEFER);
	}



	public function complete()
	{
		$this->proceedAction(self::ACTION_COMPLETE);
	}


	public function update($arNewTaskData)
	{
		$this->proceedAction(
			self::ACTION_EDIT,
			array('FIELDS' => $arNewTaskData)
		);
	}


	public function accept()
	{
		$this->proceedAction(self::ACTION_ACCEPT);
	}


	public function renew()
	{
		$this->proceedAction(self::ACTION_RENEW);
	}


	public function approve()
	{
		$this->proceedAction(self::ACTION_APPROVE);
	}


	public function disapprove()
	{
		$this->proceedAction(self::ACTION_DISAPPROVE);
	}


	/**
	 * @param integer $fileId
	 * @throws TasksException
	 * @throws CTaskAssertException
	 */
	public function removeAttachedFile($fileId)
	{
		CTaskAssert::assertLaxIntegers($fileId);
		CTaskAssert::assert($fileId > 0);

		if ( ! $this->isActionAllowed(self::ACTION_EDIT) )
		{
			CTaskAssert::log(
				'access denied while trying to remove file: fileId=' . $fileId 
				. ', taskId=' . $this->taskId . ', userId=' . $this->executiveUserId,
				CTaskAssert::ELL_WARNING
			);

			throw new TasksException('', TasksException::TE_ACTION_NOT_ALLOWED);
		}

		if ( ! CTaskFiles::Delete($this->taskId, $fileId) )
		{
			throw new TasksException(
				'File #' . $fileId . ' not attached to task #' . $this->taskId,
				TasksException::TE_FILE_NOT_ATTACHED_TO_TASK
			);
		}
	}


	/**
	 * @param  array $arFields with elements MINUTES, COMMENT_TEXT
	 * @throws TasksException
	 * @return integer id of added log item
	 */
	public function addElapsedTime($arFields)
	{
		CTaskAssert::assert(
			is_array($arFields)
			&& (count($arFields) == 2)
			&& isset($arFields['MINUTES'], $arFields['COMMENT_TEXT'])
			&& CTaskAssert::isLaxIntegers($arFields['MINUTES'])
			&& is_string($arFields['COMMENT_TEXT'])
		);

		if ( ! $this->isActionAllowed(self::ACTION_EDIT) )
			throw new TasksException('', TasksException::TE_ACTION_NOT_ALLOWED);

		$arFields['USER_ID'] = $this->executiveUserId;
		$arFields['TASK_ID'] = $this->taskId;

		$obElapsed = new CTaskElapsedTime();
		$logId = $obElapsed->Add($arFields);

		if ($logId === false)
			throw new TasksException('', TasksException::TE_ACTION_FAILED_TO_BE_PROCESSED);

		return ($logId);
	}


	private function markCacheAsDirty()
	{
		$this->arTaskData           = null;
		$this->arTaskAllowedActions = null;
		$this->arTaskDataEscaped    = null;
	}


	private function proceedAction($actionId, $arActionArguments = null)
	{
		$actionId = (int) $actionId;

		if ( ! $this->isActionAllowed($actionId) )
			throw new TasksException('', TasksException::TE_ACTION_NOT_ALLOWED);

		$arTaskData = $this->getTaskData($bSpecialChars = false);
		$arNewFields = null;
		$this->markCacheAsDirty();

		if ($actionId == self::ACTION_REMOVE)
		{
			if (CTasks::Delete($this->taskId) !== true)
			{
				throw new TasksException(
					'', 
					TasksException::TE_ACTION_FAILED_TO_BE_PROCESSED
				);
			}

			return;
		}
		elseif ($actionId == self::ACTION_EDIT)
		{
			$arFields = $arActionArguments['FIELDS'];
			$arParams = array(
				'USER_ID'               => $this->executiveUserId,
				'CHECK_RIGHTS_ON_FILES' => true
			);

			$o = new CTasks();
			if ($o->Update($this->taskId, $arFields, $arParams) !== true)
			{
				throw new TasksException(
					serialize($o->GetErrors()),
					TasksException::TE_ACTION_FAILED_TO_BE_PROCESSED
					| TasksException::TE_FLAG_SERIALIZED_ERRORS_IN_MESSAGE
				);
			}

			return;
		}

		switch ($actionId)
		{
			case self::ACTION_ACCEPT:
				$arNewFields['STATUS'] = CTasks::STATE_PENDING;
			break;

			case self::ACTION_DECLINE:
				$arNewFields['STATUS'] = CTasks::STATE_DECLINED;

				if (isset($arActionArguments['DECLINE_REASON']))
					$arNewFields['DECLINE_REASON'] = $arActionArguments['DECLINE_REASON'];
				else
					$arNewFields['DECLINE_REASON'] = '';
			break;

			case self::ACTION_COMPLETE:
				if (
					($arTaskData['TASK_CONTROL'] === 'N')
					|| ($arTaskData['CREATED_BY'] == $this->executiveUserId)
				)
				{
					$arNewFields['STATUS'] = CTasks::STATE_COMPLETED;
				}
				else
					$arNewFields['STATUS'] = CTasks::STATE_SUPPOSEDLY_COMPLETED;
			break;

			case self::ACTION_APPROVE:
				$arNewFields['STATUS'] = CTasks::STATE_COMPLETED;
			break;

			case self::ACTION_START:
				$arNewFields['STATUS'] = CTasks::STATE_IN_PROGRESS;
			break;

			case self::ACTION_DELEGATE:
				$arNewFields['STATUS'] = CTasks::STATE_PENDING;

				$delegateToPerson = (int) $arActionArguments['RESPONSIBLE_ID'];

				if ( ! isset($arActionArguments['RESPONSIBLE_ID']) )
					throw new Exception('Expected $arActionArguments[\'RESPONSIBLE_ID\']');

				if (
					($delegateToPerson == $this->executiveUserId)
					|| ( ! CTasks::IsSubordinate($delegateToPerson, $this->executiveUserId) )
				)
				{
					throw new TasksException('', TasksException::TE_TRYED_DELEGATE_TO_WRONG_PERSON);
				}

				$arNewFields['RESPONSIBLE_ID'] = $arActionArguments['RESPONSIBLE_ID'];
				if (isset($arTaskData['AUDITORS']) && count($arTaskData['AUDITORS']))
				{
					if ( ! in_array($this->executiveUserId, $arTaskData['AUDITORS']) )
					{
						$arNewFields['AUDITORS'] = $arTaskData['AUDITORS'];
						$arNewFields['AUDITORS'][] = $this->executiveUserId;
					}
				}
				else
					$arNewFields['AUDITORS'] = array($this->executiveUserId);
			break;

			case self::ACTION_DEFER:
				$arNewFields['STATUS'] = CTasks::STATE_DEFERRED;
			break;

			case self::ACTION_DISAPPROVE:
			case self::ACTION_RENEW:
				// We can't use $arTaskData['SUBORDINATE'] here, because it doesn't
				// show that director is manager of responsinble person.
				if (
					($arTaskData['CREATED_BY'] == $arTaskData['RESPONSIBLE_ID'])
					|| CTasks::IsSubordinate($arTaskData['RESPONSIBLE_ID'], $arTaskData['CREATED_BY'])
				)
				{
					$arNewFields['STATUS'] = CTasks::STATE_PENDING;
				}
				else
					$arNewFields['STATUS'] = CTasks::STATE_NEW;
			break;

			default:
			break;
		}

		if ($arNewFields === null)
			throw new Exception();

		$arParams = array('USER_ID' => $this->executiveUserId);
		$o = new CTasks();
		if ($o->Update($this->taskId, $arNewFields, $arParams) !== true)
		{
			throw new TasksException(
				'', 
				TasksException::TE_ACTION_FAILED_TO_BE_PROCESSED
			);
		}
	}


	private static function getSubUsers($userId)
	{
		static $arSubUsersIdsCache = array();

		if ( ! isset($arSubUsersIdsCache[$userId]) )
		{
			$arSubUsersIds = array();
			$rsSubUsers = CIntranetUtils::GetSubordinateEmployees($userId, $bRecursive = true);
			while ($ar = $rsSubUsers->fetch())
				$arSubUsersIds[] = (int) $ar['ID'];

			$arSubUsersIdsCache[$userId] = $arSubUsersIds;
		}

		return ($arSubUsersIdsCache[$userId]);
	}


	private static function getUserRoles($arTask, $userId)
	{
		CTaskAssert::isLaxIntegers($userId);

		$userRole = 0;

		if ($arTask['CREATED_BY'] == $userId)
			$userRole |= self::ROLE_DIRECTOR;
		
		if ($arTask['RESPONSIBLE_ID'] == $userId)
			$userRole |= self::ROLE_RESPONSIBLE;

		if (in_array($userId, $arTask['ACCOMPLICES']))
			$userRole |= self::ROLE_ACCOMPLICE;

		if (in_array($userId, $arTask['AUDITORS']))
			$userRole |= self::ROLE_AUDITOR;

		// Now, process subordinated users
		$allRoles = self::ROLE_DIRECTOR | self::ROLE_RESPONSIBLE | self::ROLE_ACCOMPLICE | self::ROLE_AUDITOR;
		if ($userRole !== $allRoles)
		{
			$arSubUsersIds = self::getSubUsers($userId);

			if ( ! empty($arSubUsersIds) )
			{
				// Check only roles, that user doesn't have already
				if ( ! ($userRole & self::ROLE_DIRECTOR) )
				{
					if (in_array((int)$arTask['CREATED_BY'], $arSubUsersIds, true))
						$userRole |= self::ROLE_DIRECTOR;
				}

				if ( ! ($userRole & self::ROLE_RESPONSIBLE) )
				{
					if (in_array((int)$arTask['RESPONSIBLE_ID'], $arSubUsersIds, true))
						$userRole |= self::ROLE_RESPONSIBLE;
				}

				if ( ! ($userRole & self::ROLE_ACCOMPLICE) )
				{
					foreach ($arTask['ACCOMPLICES'] as $accompliceId)
					{
						if (in_array((int)$accompliceId, $arSubUsersIds, true))
						{
							$userRole |= self::ROLE_ACCOMPLICE;
							break;
						}
					}
				}

				if ( ! ($userRole & self::ROLE_AUDITOR) )
				{
					foreach ($arTask['ACCOMPLICES'] as $auditorId)
					{
						if (in_array((int)$auditorId, $arSubUsersIds, true))
						{
							$userRole |= self::ROLE_AUDITOR;
							break;
						}
					}
				}
			}
		}

		// No role in task?
		if ($userRole === 0)
			$userRole = self::ROLE_NOT_A_MEMBER;

		return ($userRole);
	}


	private static function getBaseAllowedActions()
	{
		static $arBaseActionsMap = null;

		// Init just once per hit
		if ($arBaseActionsMap === null)
		{
			$arBaseActionsMap = array(
				CTasks::STATE_NEW => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE
					),
					self::ROLE_RESPONSIBLE => array(
						self::ACTION_ACCEPT,
						self::ACTION_DECLINE,
						self::ACTION_START,
						self::ACTION_DELEGATE
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				),
				CTasks::STATE_PENDING => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE,
						self::ACTION_COMPLETE
					),
					self::ROLE_RESPONSIBLE => array(
						self::ACTION_START,
						self::ACTION_DELEGATE,
						self::ACTION_DEFER,
						self::ACTION_COMPLETE
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				),
				CTasks::STATE_IN_PROGRESS => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE,
						self::ACTION_COMPLETE
					),
					self::ROLE_RESPONSIBLE => array(
						self::ACTION_DELEGATE,
						self::ACTION_DEFER,
						self::ACTION_COMPLETE
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				),
				CTasks::STATE_SUPPOSEDLY_COMPLETED => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE,
						self::ACTION_APPROVE,
						self::ACTION_DISAPPROVE
					),
					self::ROLE_RESPONSIBLE => array(
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				),
				CTasks::STATE_COMPLETED => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE,
						self::ACTION_RENEW
					),
					self::ROLE_RESPONSIBLE => array(
						self::ACTION_START,
						self::ACTION_DELEGATE
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				),
				CTasks::STATE_DEFERRED => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE,
						self::ACTION_COMPLETE
					),
					self::ROLE_RESPONSIBLE => array(
						self::ACTION_START,
						self::ACTION_DELEGATE,
						self::ACTION_COMPLETE
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				),
				CTasks::STATE_DECLINED => array(
					self::ROLE_DIRECTOR => array(
						self::ACTION_EDIT,
						self::ACTION_REMOVE,
						self::ACTION_COMPLETE,
						self::ACTION_RENEW
					),
					self::ROLE_RESPONSIBLE => array(
						self::ACTION_DELEGATE
					),
					self::ROLE_ACCOMPLICE => array(
					),
					self::ROLE_AUDITOR => array(
					)
				)
			);

			foreach(GetModuleEvents('tasks', 'OnBaseAllowedActionsMapInit', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array(&$arBaseActionsMap));
		}

		return ($arBaseActionsMap);
	}
}
