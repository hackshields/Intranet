<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2013 Bitrix
 */

class CTaskComments
{
	public static function Remove($taskId, $commentId, $userId, $arParams)
	{
		global $DB;

		if (self::CanRemoveComment($taskId, $commentId, $userId, $arParams) !== true)
			throw new TasksException('', TasksException::TE_ACCESS_DENIED);

		$strErrorMessage = $strOKMessage = '';
		ForumDeleteMessage($commentId, $strErrorMessage, $strOKMessage, array('PERMISSION' => 'Y'));

		$dbRes = CSocNetLogComments::GetList(
			array(),
			array(
				'EVENT_ID'	=> array('tasks_comment'),
				'SOURCE_ID' => $commentId
			),
			false,
			false,
			array('ID')
		);
		
		if ($arRes = $dbRes->Fetch())
		{
			CSocNetLogComments::Delete($arRes['ID']);

			// Tasks log
			$arLogFields = array(
				'TASK_ID'       =>  $taskId,
				'USER_ID'       =>  $userId,
				'~CREATED_DATE' =>  $DB->CurrentTimeFunction(),
				'FIELD'         => 'COMMENT_REMOVE'
			);

			$log = new CTaskLog();
			$log->Add($arLogFields);
		}
	}


	private static function CanRemoveComment($taskId, $commentId, $userId, $arParams)
	{
		$bCommentsCanBeRemoved = COption::GetOptionString('tasks', 'task_comment_allow_remove');

		if ( ! $bCommentsCanBeRemoved )
			return (false);

		$res = CForumMessage::GetListEx(
			array('ID' => 'ASC'),
			array(
				'FORUM_ID' => $arParams['FORUM_ID'],
				'TOPIC_ID' => $arParams['FORUM_TOPIC_ID'],
				'APPROVED' => $arParams['APPROVED']
			),
			false,
			0,
			array('bShowAll' => true)
		);

		// Take last message
		$comment = false;
		while ($ar = $res->fetch())
			$comment = $ar;

		if ( ! is_array($comment) )
			return (false);

		if (
			CTasksTools::isAdmin($userId)
			|| CTasksTools::IsPortalB24Admin($userId)
		)
		{
			return (true);
		}
		elseif ($userId == $comment['AUTHOR_ID'])
		{
			if ($commentId != $comment['ID'])	// it's not the last comment
				return (false);
			else
				return (true);
		}
		else
			return (false);
	}


	/**
	 * WARNING! This method is transitional and can be changed without 
	 * any notifications! Don't use it.
	 * 
	 * @deprecated
	 */
	public static function __deprecated_Add(
		$commentText,
		$forumTopicId,
		$forumId,
		$nameTemplate,
		$arTask,
		$permissions,
		$commentId,
		$givenUserId,
		$imageWidth,
		$imageHeight,
		$arSmiles,
		$arForum,
		$messagesPerPage,
		$arUserGroupArray,
		$backPage,
		$strMsgAddComment,
		$strMsgEditComment,
		$strMsgNewTask,
		$componentName,
		&$outForumTopicId,
		&$arErrorCodes,
		&$outStrUrl
	)
	{
		global $DB;

		if (is_array($arTask))
		{
			if ( ! array_key_exists('~TITLE', $arTask) )
			{
				$arTmpTask = $arTask;

				foreach ($arTmpTask as $key => $value)
				{
					if (substr($key, 0, 1) !== '~')
						$arTask['~' . $key] = $arTmpTask[$key];
				}
			}
		}

		$MID = 0;
		$TID = 0;

		if (($forumTopicId > 0) && (CForumTopic::GetByID($forumTopicId) === false))
			$forumTopicId = false;

		if ($forumTopicId <= 0)
		{
			$arUserStart = array(
				"ID" => intVal($arTask["CREATED_BY"]),
				"NAME" => $GLOBALS["FORUM_STATUS_NAME"]["guest"]
			);

			if ($arUserStart["ID"] > 0)
			{
				$res = array();
				$db_res = CForumUser::GetListEx(
					array(),
					array("USER_ID" => $arTask["CREATED_BY"])
				);

				if ($db_res && $res = $db_res->Fetch())
				{
					$res["FORUM_USER_ID"] = intVal($res["ID"]);
					$res["ID"] = $res["USER_ID"];
				}
				else
				{
					$db_res = CUser::GetByID($arTask["CREATED_BY"]);
					if ($db_res && $res = $db_res->Fetch())
					{
						$res["SHOW_NAME"] = COption::GetOptionString("forum", "USER_SHOW_NAME", "Y");
						$res["USER_PROFILE"] = "N";
					}
				}

				if (!empty($res))
				{
					$arUserStart = $res;
					$sName = ($res["SHOW_NAME"] == "Y" ? trim(CUser::FormatName($nameTemplate, $res)) : "");
					$arUserStart["NAME"] = (empty($sName) ? trim($res["LOGIN"]) : $sName);
				}
			}

			$arUserStart["NAME"] = (empty($arUserStart["NAME"]) ? $GLOBALS["FORUM_STATUS_NAME"]["guest"] : $arUserStart["NAME"]);
			$DB->StartTransaction();

			$arFields = Array(
				"TITLE" => $arTask["~TITLE"],
				"FORUM_ID" => $forumId,
				"USER_START_ID" => $arUserStart["ID"],
				"USER_START_NAME" => $arUserStart["NAME"],
				"LAST_POSTER_NAME" => $arUserStart["NAME"],
				"APPROVED" => "Y",
				"PERMISSION_EXTERNAL" => $permissions,
				"PERMISSION" => $permissions,
				"NAME_TEMPLATE" => $nameTemplate
			);

			$TID = CForumTopic::Add($arFields);

			if (intVal($TID) <= 0)
				$arErrorCodes[] = array('code' => 'topic is not created');
			else
			{
				$arFields = array(
					"FORUM_TOPIC_ID" => $TID
				);

				$task = new CTasks();
				$task->Update($arTask["ID"], $arFields);
			}

			if (!empty($arErrorCodes))
			{
				$DB->Rollback();
				return false;
			}
			else
			{
				$DB->Commit();
			}
		}

		$arFieldsG = array(
			"POST_MESSAGE" => $commentText,
			"AUTHOR_NAME"  => '',
			"AUTHOR_EMAIL" => NULL,
			"USE_SMILES" => NULL,
			"PARAM2" => 0,
			"TITLE"               => $arTask["~TITLE"],
			"PERMISSION_EXTERNAL" => $permissions,
			"PERMISSION"          => $permissions,
		);

		if (!empty($_FILES["REVIEW_ATTACH_IMG"]))
		{
			$arFieldsG["ATTACH_IMG"] = $_FILES["REVIEW_ATTACH_IMG"];
		}
		else
		{
			$arFiles = array();
			if (!empty($_REQUEST["FILES"]))
			{
				foreach ($_REQUEST["FILES"] as $key)
				{
					$arFiles[$key] = array("FILE_ID" => $key);
					if (!in_array($key, $_REQUEST["FILES_TO_UPLOAD"]))
					{
						$arFiles[$key]["del"] = "Y";
					}
				}
			}
			if (!empty($_FILES))
			{
				$res = array();
				foreach ($_FILES as $key => $val)
				{
					if (substr($key, 0, strLen("FILE_NEW")) == "FILE_NEW" && !empty($val["name"]))
					{
						$arFiles[] = $_FILES[$key];
					}
				}
			}
			if (!empty($arFiles))
			{
				$arFieldsG["FILES"] = $arFiles;
			}
		}
		$TOPIC_ID = ($forumTopicId > 0 ? $forumTopicId : $TID);

		$MESSAGE_ID = 0;
		$MESSAGE_TYPE = $TOPIC_ID > 0 ? "REPLY" : "NEW";
		if (COption::GetOptionString("tasks", "task_comment_allow_edit") && $MESSAGE_ID = intval($commentId))
		{
			$MESSAGE_TYPE = "EDIT";
		}

		$strErrorMessage = '';
		$strOKMessage = '';
		$MID = ForumAddMessage($MESSAGE_TYPE, $forumId, $TOPIC_ID, $MESSAGE_ID, 
			$arFieldsG, $strErrorMessage, $strOKMessage, false, 
			$_POST["captcha_word"], 0, $_POST["captcha_code"], $nameTemplate);

		if ($MID <= 0 || !empty($strErrorMessage))
		{
			$arErrorCodes[] = array(
				'code'  => 'message is not added 2',
				'title' => (empty($strErrorMessage) ? NULL : $strErrorMessage)
			);
		}
		else
		{
			$arMessage = CForumMessage::GetByID($MID);

			if ($forumTopicId <= 0)
			{
				$forumTopicId = $TID = intVal($arMessage["TOPIC_ID"]);
			}

			$outForumTopicId = intVal($forumTopicId);
			ForumClearComponentCache($componentName);

			// NOTIFICATION
			$arTask["ACCOMPLICES"] = $arTask["AUDITORS"] = array();
			$rsMembers = CTaskMembers::GetList(array(), array("TASK_ID" => $arTask["ID"]));
			while ($arMember = $rsMembers->Fetch())
			{
				if ($arMember["TYPE"] == "A")
				{
					$arTask["ACCOMPLICES"][] = $arMember["USER_ID"];
				}
				elseif ($arMember["TYPE"] == "U")
				{
					$arTask["AUDITORS"][] = $arMember["USER_ID"];
				}
			}
			$arEmailUserIDs = array($arTask["RESPONSIBLE_ID"], $arTask["CREATED_BY"]);
			$arEmailUserIDs = array_unique(array_merge($arEmailUserIDs, $arTask["ACCOMPLICES"], $arTask["AUDITORS"]));
			$currentUserPos = array_search($givenUserId, $arEmailUserIDs);
			if ($currentUserPos !== false)
			{
				unset($arEmailUserIDs[$currentUserPos]);
			}

			$parser = new CTextParser();
			$parser->imageWidth = $imageWidth;
			$parser->imageHeight = $imageHeight;
			$parser->smiles = $arSmiles;
			$parser->allow = array(
				"HTML" => $arForum["ALLOW_HTML"],
				"ANCHOR" => $arForum["ALLOW_ANCHOR"],
				"BIU" => $arForum["ALLOW_BIU"],
				"IMG" => "N",
				"VIDEO" => "N",
				"LIST" => $arForum["ALLOW_LIST"],
				"QUOTE" => $arForum["ALLOW_QUOTE"],
				"CODE" => $arForum["ALLOW_CODE"],
				"FONT" => $arForum["ALLOW_FONT"],
				"SMILES" => "N",
				"UPLOAD" => $arForum["ALLOW_UPLOAD"],
				"NL2BR" => $arForum["ALLOW_NL2BR"],
				"TABLE" => "Y"
			);

			$arAllow = NULL;
			$MESSAGE = HTMLToTxt($parser->convertText($commentText, $arAllow));

			// remove [ url] for socialnetwork log
			$MESSAGE = preg_replace("/(\s\[\s(http:\/\/|https:\/\/|ftp:\/\/))(.*?)(\s\])/is", "", $MESSAGE);

			$parser->allow = array("HTML" => 'Y',"ANCHOR" => 'Y',"BIU" => 'Y',"IMG" => "Y","VIDEO" => "Y","LIST" => 'N',"QUOTE" => 'Y',"CODE" => 'Y',"FONT" => 'Y',"SMILES" => "N","UPLOAD" => 'N',"NL2BR" => 'N',"TABLE" => "Y");
			$message_notify = $parser->convertText($commentText);

			$arRecipientsIDs = CTaskNotifications::GetRecipientsIDs($arTask);

			// Instant Messages
			if (IsModuleInstalled("im") && CModule::IncludeModule("im") && sizeof($arRecipientsIDs))
			{
				$pageNumber = CForumMessage::GetMessagePage(
					$MID, 
					$messagesPerPage, 
					$arUserGroupArray
				);

				// There are different links for extranet users
				$isExtranetEnabled = false;
				if (CModule::IncludeModule("extranet"))
					$isExtranetEnabled = true;

				if ($isExtranetEnabled)
				{
					$arSites = array();
					$dbSite = CSite::GetList($by="sort", $order="desc", array("ACTIVE" => "Y"));

					while($arSite = $dbSite->Fetch())
					{
						if (strlen(trim($arSite["DIR"])) > 0)
							$arSites[$arSite['ID']]['DIR'] = $arSite['DIR'];
						else
							$arSites[$arSite['ID']]['DIR'] = '/';

						if (strlen(trim($arSite["SERVER_NAME"])) > 0)
							$arSites[$arSite['ID']]['SERVER_NAME'] = $arSite["SERVER_NAME"];
						else
							$arSites[$arSite['ID']]['SERVER_NAME'] = COption::GetOptionString("main", "server_name", $_SERVER["HTTP_HOST"]);

						$arSites[$arSite['ID']]['urlPrefix'] = $arSites[$arSite['ID']]['SERVER_NAME'] . $arSites[$arSite['ID']]['DIR'];

						// remove last '/'
						if (
							(strlen($arSites[$arSite['ID']]['urlPrefix']) > 0)
							&& (substr($arSites[$arSite['ID']]['urlPrefix'], -1) === '/')
						)
						{
							$arSites[$arSite['ID']]['urlPrefix'] = substr($arSites[$arSite['ID']]['urlPrefix'], 0, -1);
						}
					}

					$extranet_site_id = CExtranet::GetExtranetSiteID();
					$intranet_site_id = CSite::GetDefSite();

					$arIntranetUsers = CExtranet::GetIntranetUsers();
				}
				else
				{
					if ($arTask["GROUP_ID"])
						$pathTemplateWoExtranet = str_replace("#group_id#", $arTask["GROUP_ID"], COption::GetOptionString("tasks", "paths_task_group_entry", "/workgroups/group/#group_id#/tasks/task/view/#task_id#/", $arFields["SITE_ID"]));
					else
						$pathTemplateWoExtranet = COption::GetOptionString("tasks", "paths_task_user_entry", "/company/personal/user/#user_id#/tasks/task/view/#task_id#/", $arFields["SITE_ID"]);
				}

				foreach ($arRecipientsIDs as $userID)
				{
					$urlPrefixForUser = tasksServerName();

					if ($isExtranetEnabled)
					{
						if ( ! in_array($userID, $arIntranetUsers) 
							&& $extranet_site_id
						)
						{
							$userSiteId = $extranet_site_id;
						}
						else
							$userSiteId = $intranet_site_id;

						if (isset($arSites[$userSiteId]['SERVER_NAME']))
						{
							$urlPrefixForUser = tasksServerName(
								$arSites[$userSiteId]['SERVER_NAME']
							);
						}

						if ($arTask["GROUP_ID"])
						{
							$pathTemplate = str_replace(
								'#group_id#', 
								$arTask['GROUP_ID'], 
								CTasksTools::GetOptionPathTaskGroupEntry($userSiteId, '')
								);
						}
						else
							$pathTemplate = CTasksTools::GetOptionPathTaskUserEntry($userSiteId, '');
					}
					else
						$pathTemplate = $pathTemplateWoExtranet;

					$NOTIFY_MESSAGE_TITLE_TEMPLATE = '';
					$messageUrl = '';
					if (strlen($pathTemplate) > 0)
					{
						$groupId = 0;
						
						if (isset($arTask['GROUP_ID']))
							$groupId = (int) $arTask['GROUP_ID'];

						$messageUrl = $urlPrefixForUser 
							. CComponentEngine::MakePathFromTemplate(
								$pathTemplate, 
								array(
									"user_id"  => $userID, 
									"task_id"  => $arTask["ID"], 
									"action"   => "view",
									"USER_ID"  => $userID, 
									"TASK_ID"  => $arTask["ID"], 
									"ACTION"   => "view",
									'GROUP_ID' => $groupId,
									'group_id' => $groupId
									)
								);

						if ($pageNumber > 1)
							$messageUrl .= ( strpos($messageUrl, "?") === false ? "?" : "&")."MID=".$MID;

						$NOTIFY_MESSAGE_TITLE_TEMPLATE = '[URL=' . $messageUrl . "#message" . $MID.']' 
							. $arTask["~TITLE"] . '[/URL]';
					}
					else
						$NOTIFY_MESSAGE_TITLE_TEMPLATE = $arTask["~TITLE"];

					$MESSAGE_SITE = trim(
						htmlspecialcharsbx(
							strip_tags(
								str_replace(
									array("\r\n","\n","\r"), 
									' ', 
									htmlspecialcharsback($message_notify)
								)
							)
						)
					);

					$MESSAGE_EMAIL = $MESSAGE_SITE;	// full message to email

					$dot = strlen($MESSAGE_SITE)>=100? '...': '';
					$MESSAGE_SITE = substr($MESSAGE_SITE, 0, 99).$dot;

					$arMessageFields = array(
						"TO_USER_ID" => $userID,
						"FROM_USER_ID" => $givenUserId, 
						"NOTIFY_TYPE" => IM_NOTIFY_FROM, 
						"NOTIFY_MODULE" => "tasks", 
						"NOTIFY_EVENT" => "comment", 
						"NOTIFY_MESSAGE" => str_replace(
							array("#TASK_TITLE#", "#TASK_COMMENT_TEXT#"), 
							array($NOTIFY_MESSAGE_TITLE_TEMPLATE, '[COLOR=#000000]'.$MESSAGE_SITE.'[/COLOR]'), 
							($MESSAGE_TYPE != "EDIT" ? $strMsgAddComment : $strMsgEditComment)
						),
						"NOTIFY_MESSAGE_OUT" => str_replace(
							array("#TASK_TITLE#", "#TASK_COMMENT_TEXT#"), 
							array($arTask["~TITLE"], $MESSAGE_EMAIL.' #BR# '.$messageUrl."#message".$MID.' '), 
							($MESSAGE_TYPE != "EDIT" ? $strMsgAddComment : $strMsgEditComment)
						),
					);

					CIMNotify::Add($arMessageFields);
				}
			}

			$strURL = (!empty($backPage) ? $backPage : $GLOBALS['APPLICATION']->GetCurPageParam("", array("IFRAME", "MID", "SEF_APPLICATION_CUR_PAGE_URL", BX_AJAX_PARAM_ID, "result")));
			$strURL = ForumAddPageParams(
				$strURL,
				array(
					"MID" => $MID, 
					"result" => ($arForum["MODERATION"] != "Y" 
						|| CForumNew::CanUserModerateForum($forumId, $arUserGroupArray) ? "reply" : "not_approved"
					)
				), 
				false, 
				false
			);
			$outStrUrl = $strURL;

			// sonet log
			if (CModule::IncludeModule("socialnetwork"))
			{
				$dbRes = CSocNetLog::GetList(
					array("ID" => "DESC"),
					array(
						"EVENT_ID" => "tasks",
						"SOURCE_ID" => $arTask["ID"]
					),
					false,
					false,
					array("ID", "ENTITY_TYPE", "ENTITY_ID", "TMP_ID")
				);
				if ($arRes = $dbRes->Fetch())
				{
					$log_id = $arRes["TMP_ID"];
					$entity_type = $arRes["ENTITY_TYPE"];
					$entity_id = $arRes["ENTITY_ID"];
				}
				else
				{
					$entity_type = ($arTask["GROUP_ID"] ? SONET_ENTITY_GROUP : SONET_ENTITY_USER);
					$entity_id = ($arTask["GROUP_ID"] ? $arTask["GROUP_ID"] : $arTask["CREATED_BY"]);

					$rsUser = CUser::GetByID($arTask["CREATED_BY"]);
					if ($arUser = $rsUser->Fetch())
					{
						$arSoFields = Array(
							"ENTITY_TYPE" => $entity_type,
							"ENTITY_ID" => $entity_id,
							"EVENT_ID" => "tasks",
							"LOG_DATE" => $arTask["CREATED_DATE"],
							"TITLE_TEMPLATE" => "#TITLE#",
							"TITLE" => htmlspecialcharsBack($arTask["~TITLE"]),
							"MESSAGE" => "",
							"TEXT_MESSAGE" => $strMsgNewTask,
							"MODULE_ID" => "tasks",
							"CALLBACK_FUNC" => false,
							"SOURCE_ID" => $arTask["ID"],
							"ENABLE_COMMENTS" => "Y",
							"USER_ID" => $arTask["CREATED_BY"],
							"URL" => CTaskNotifications::GetNotificationPath($arUser, $arTask["ID"]),
							"PARAMS" => serialize(array("TYPE" => "create"))
						);
						$log_id = CSocNetLog::Add($arSoFields, false);
						if (intval($log_id) > 0)
						{
							CSocNetLog::Update($log_id, array("TMP_ID" => $log_id));
							$arRights = CTaskNotifications::__UserIDs2Rights(CTaskNotifications::GetRecipientsIDs($arTask, false));
							if($arTask["GROUP_ID"])
								$arRights[] = "S".SONET_ENTITY_GROUP.$arTask["GROUP_ID"];
							CSocNetLogRights::Add($log_id, $arRights);
						}
					}
				}

				if (intval($log_id) > 0)
				{
					$sText = (COption::GetOptionString("forum", "FILTER", "Y") == "Y" ? $arMessage["POST_MESSAGE_FILTER"] : $arMessage["POST_MESSAGE"]);

					$arFieldsForSocnet = array(
						"ENTITY_TYPE" => $entity_type,
						"ENTITY_ID" => $entity_id,
						"EVENT_ID" => "tasks_comment",
						"MESSAGE" => $sText,
						"TEXT_MESSAGE" => $parser->convert4mail($sText),
						"URL" => str_replace("?IFRAME=Y", "", str_replace("&IFRAME=Y", "", str_replace("IFRAME=Y&", "", $strURL))),
						"MODULE_ID" => "tasks",
						"SOURCE_ID" => $MID,
						"LOG_ID" => $log_id,
						"RATING_TYPE_ID" => "FORUM_POST",
						"RATING_ENTITY_ID" => $MID
					);

					if ($MESSAGE_TYPE == "EDIT")
					{
						$dbRes = CSocNetLogComments::GetList(
							array("ID" => "DESC"),
							array(
								"EVENT_ID"	=> array("tasks_comment"),
								"SOURCE_ID" => $MID
							),
							false,
							false,
							array("ID")
						);
						while ($arRes = $dbRes->Fetch())
						{
							CSocNetLogComments::Update($arRes["ID"], $arFieldsForSocnet);
						}
					}
					else
					{
						$arFieldsForSocnet['USER_ID']   = $givenUserId;
						$arFieldsForSocnet['=LOG_DATE'] = $GLOBALS['DB']->CurrentTimeFunction();

						CSocNetLogComments::Add($arFieldsForSocnet);
					}
				}
			}

			// Tasks log
			$arLogFields = array(
				"TASK_ID" => $arTask["ID"],
				"USER_ID" => $arMessage["AUTHOR_ID"],
				"CREATED_DATE" => ($arMessage["EDIT_DATE"] ? ConvertTimeStamp(MakeTimeStamp($arMessage["EDIT_DATE"], CSite::GetDateFormat()), "FULL") : $arMessage["POST_DATE"]),
				"FIELD" => "COMMENT",
				"TO_VALUE" => $MID
			);

			$log = new CTaskLog();
			$log->Add($arLogFields);
		}

		return ($MID);	// Message id
	}
}
