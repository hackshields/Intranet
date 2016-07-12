<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

CUtil::InitJSCore(array('popup', 'tooltip'));

// commented out probably wrong script $GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/tasks.task.edit/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/tasks.list/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/tasks.list/templates/.default/table-view.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/tasks/task-popups.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/tasks/task-reminders.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/tasks/task-iframe-popup.js");

$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/intranet/intranet-common.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/main/core/css/core_popup.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/tasks/css/tasks.css");

$GLOBALS["APPLICATION"]->IncludeComponent(
	'bitrix:main.calendar',
	'',
	array(
		'SILENT' => 'Y',
	),
	null,
	array('HIDE_ICONS' => 'Y')
);

$arPaths = array(
	"PATH_TO_TASKS_TASK" => $arParams["PATH_TO_TASKS_TASK"],
	"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"]
);

$createUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit"));
$createSubtaskUrl = $createUrl.(strpos($createUrl, "?") === false ? "?" : "&")."PARENT_ID=".$arResult["TASK"]["ID"];

$APPLICATION->IncludeComponent(
	"bitrix:tasks.iframe.popup",
	".default",
	array(
		"ON_TASK_ADDED" => "onPopupTaskAdded",
		"ON_TASK_CHANGED" => "onPopupTaskChanged",
		"ON_TASK_DELETED" => "onPopupTaskDeleted"
	),
	null,
	array("HIDE_ICONS" => "Y")
);

if (!defined('TASKS_MUL_INCLUDED')):
	$APPLICATION->IncludeComponent("bitrix:main.user.link",
		'',
		array(
			"AJAX_ONLY" => "Y",
			"PATH_TO_SONET_USER_PROFILE" => $arParams["~PATH_TO_USER_PROFILE"],
			"PATH_TO_SONET_MESSAGES_CHAT" => $arParams["~PATH_TO_MESSAGES_CHAT"],
			"DATE_TIME_FORMAT" => $arParams["~DATE_TIME_FORMAT"],
			"SHOW_YEAR" => $arParams["SHOW_YEAR"],
			"NAME_TEMPLATE" => $arParams["~NAME_TEMPLATE"],
			"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
			"PATH_TO_CONPANY_DEPARTMENT" => $arParams["~PATH_TO_CONPANY_DEPARTMENT"],
			"PATH_TO_VIDEO_CALL" => $arParams["~PATH_TO_VIDEO_CALL"],
		),
		false,
		array("HIDE_ICONS" => "Y")
	);
	define('TASKS_MUL_INCLUDED', 1);
endif;

?>
<script type="text/javascript">
BX.message({
	TASKS_PRIORITY : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY")); ?>',
	TASKS_APPLY : '<?php echo CUtil::JSEscape(GetMessage("TASKS_APPLY")); ?>',
	TASKS_ADD_IN_REPORT : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_IN_REPORT")); ?>',
	TASKS_MARK : '<?php echo CUtil::JSEscape(GetMessage("TASKS_MARK")); ?>',
	TASKS_PRIORITY_LOW : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY_0")); ?>',
	TASKS_PRIORITY_MIDDLE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY_1")); ?>',
	TASKS_PRIORITY_HIGH : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY_2")); ?>',
	TASKS_MARK_NONE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_MARK_NONE")); ?>',
	TASKS_MARK_POSITIVE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_MARK_P")); ?>',
	TASKS_MARK_NEGATIVE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_MARK_N")); ?>',
	TASKS_DURATION : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DURATION")); ?>',
	TASKS_SELECT : '<?php echo CUtil::JSEscape(GetMessage("TASKS_SELECT")); ?>',
	TASKS_OK : '<?php echo CUtil::JSEscape(GetMessage("TASKS_OK")); ?>',
	TASKS_CANCEL : '<?php echo CUtil::JSEscape(GetMessage("TASKS_CANCEL")); ?>',
	TASKS_DECLINE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DECLINE_TASK")); ?>',
	TASKS_DECLINE_REASON : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DECLINE_REASON")); ?>',
	TASKS_NO_TITLE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_NO_TITLE")); ?>',
	TASKS_NO_RESPONSIBLE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_NO_RESPONSIBLE")); ?>',
	TASKS_PATH_TO_USER_PROFILE : '<?php echo CUtil::JSEscape($arParams["PATH_TO_USER_PROFILE"]); ?>',
	TASKS_PATH_TO_TASK : '<?php echo CUtil::JSEscape($arParams["PATH_TO_TASKS_TASK"]); ?>',
	PATH_TO_GROUP_TASKS : '<?php echo CUtil::JSEscape($arParams["PATH_TO_GROUP_TASKS"]); ?>',
	TASKS_HOURS_N : '<?php echo CUtil::JSEscape(GetMessage("TASKS_HOURS_N")); ?>',
	TASKS_HOURS_G : '<?php echo CUtil::JSEscape(GetMessage("TASKS_HOURS_G")); ?>',
	TASKS_HOURS_P : '<?php echo CUtil::JSEscape(GetMessage("TASKS_HOURS_P")); ?>',
	TASKS_REMINDER_TITLE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMINDER_TITLE")); ?>',
	TASKS_ABOUT_DEADLINE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ABOUT_DEADLINE")); ?>',
	TASKS_BY_DATE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_BY_DATE")); ?>',
	TASKS_REMIND_BEFORE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMIND_BEFORE")); ?>',
	TASKS_REMIND_VIA_JABBER : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMIND_VIA_JABBER")); ?>',
	TASKS_REMIND_VIA_EMAIL : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMIND_VIA_EMAIL")); ?>',
	TASKS_REMIND_VIA_JABBER_EX : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMIND_VIA_JABBER_EX")); ?>',
	TASKS_REMIND_VIA_EMAIL_EX : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMIND_VIA_EMAIL_EX")); ?>',
	TASKS_REMINDER_OK : '<?php echo CUtil::JSEscape(GetMessage("TASKS_REMINDER_OK")); ?>',
	TASKS_DOUBLE_CLICK : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DOUBLE_CLICK")); ?>',
	TASKS_MENU : '<?php echo CUtil::JSEscape(GetMessage("TASKS_MENU")); ?>',
	TASKS_FINISH : '<?php echo CUtil::JSEscape(GetMessage("TASKS_FINISH")); ?>',
	TASKS_FINISHED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_FINISHED")); ?>',
	TASKS_QUICK_IN_GROUP : '<?php echo CUtil::JSEscape(GetMessage("TASKS_QUICK_IN_GROUP")); ?>',
	TASKS_TASK_TITLE_LABEL : '<?php echo CUtil::JSEscape(GetMessage("TASKS_TASK_TITLE_LABEL")); ?>',
	TASKS_RESPONSIBLE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_RESPONSIBLE")); ?>',
	TASKS_DIRECTOR : '<?php echo CUtil::JSEscape(GetMessage("TASKS_CREATOR")); ?>',
	TASKS_DATE_CREATED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DATE_CREATED")); ?>',
	TASKS_DATE_DEADLINE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_QUICK_DEADLINE")); ?>',
	TASKS_DATE_START : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DATE_START")); ?>',
	TASKS_DATE_END : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DATE_END")); ?>',
	TASKS_DATE_STARTED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DATE_STARTED")); ?>',
	TASKS_DATE_COMPLETED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DATE_COMPLETED")); ?>',
	TASKS_STATUS : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS")); ?>',
	TASKS_STATUS_IN_PROGRESS : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_IN_PROGRESS")); ?>',
	TASKS_STATUS_ACCEPTED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_ACCEPTED")); ?>',
	TASKS_STATUS_COMPLETED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_COMPLETED")); ?>',
	TASKS_STATUS_DELAYED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_DELAYED")); ?>',
	TASKS_STATUS_NEW : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_NEW")); ?>',
	TASKS_STATUS_OVERDUE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_OVERDUE")); ?>',
	TASKS_STATUS_WAITING : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_WAITING")); ?>',
	TASKS_STATUS_DECLINED : '<?php echo CUtil::JSEscape(GetMessage("TASKS_STATUS_DECLINED")); ?>',
	TASKS_PRIORITY_0 : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY_0")); ?>',
	TASKS_PRIORITY_1 : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY_1")); ?>',
	TASKS_PRIORITY_2 : '<?php echo CUtil::JSEscape(GetMessage("TASKS_PRIORITY_2")); ?>',
	TASKS_QUICK_INFO_DETAILS : '<?php echo CUtil::JSEscape(GetMessage("TASKS_QUICK_INFO_DETAILS")); ?>',
	TASKS_QUICK_INFO_EMPTY_DATE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_QUICK_INFO_EMPTY_DATE")); ?>',
	TASKS_ADD_TASK : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK")); ?>',
	TASKS_DELETE_CONFIRM : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DELETE_CONFIRM")); ?>',
	TASKS_DELETE_TASK_CONFIRM : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DELETE_TASK_CONFIRM")); ?>',
	TASKS_DELETE_FILE_CONFIRM : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DELETE_FILE_CONFIRM")); ?>',
	TASKS_FILES: '<?php echo CUtil::JSEscape(GetMessage("TASKS_TASK_FILES")); ?>',
	TASKS_GROUP_ADD: '<?php echo CUtil::JSEscape(GetMessage("TASKS_GROUP_ADD")); ?>',
	TASKS_SIDEBAR_DEADLINE_NO: '<?php echo CUtil::JSEscape(GetMessage("TASKS_SIDEBAR_DEADLINE_NO")); ?>',
	TASKS_DATE_MUST_BE_IN_FUTURE : '<?php echo CUtil::JSEscape(GetMessage("TASKS_DATE_MUST_BE_IN_FUTURE")); ?>'
});

// This needs for __onBeforeUnload preventer
var iframePopup = window.top.BX.TasksIFrameInst;
if (iframePopup)
{
	window.top.BX.TasksIFrameInst.isEditMode = false;
}

var taskData = <?php
	$bSkipJsMenu = false;
	$bIsIe = false;
	$userAgent = strtolower($_SERVER["HTTP_USER_AGENT"]);
	if (strpos($userAgent, "opera") === false && strpos($userAgent, "msie") !== false)
		$bIsIe = true;

	if (isset($arResult["IS_IFRAME"]) && ($arResult["IS_IFRAME"] === true) && $bIsIe)
		$bSkipJsMenu = true;

	tasksRenderJSON($arResult["TASK"], sizeof($arResult["SUBTASKS"]), $arPaths, true, true, true, $arParams["NAME_TEMPLATE"], $arAdditionalFields = array(), $bSkipJsMenu);
?>;

if (!window.top.BX("gantt-container"))
{
	for(var i = taskData.menuItems.length - 1; i >= 0; i--)
	{
		if (taskData.menuItems[i].className == "task-menu-popup-item-add-deadline" || taskData.menuItems[i].className == "task-menu-popup-item-remove-deadline")
		{
			taskData.menuItems.splice (i, 1);
		}
	}
}

<?php if ($arResult["IS_IFRAME"] && ($arResult["CALLBACK"] == "CHANGED" || $arResult["CALLBACK"] == "ADDED")):?>
(function() {
	var iframePopup = window.top.BX.TasksIFrameInst;
	if (iframePopup)
	{
		<?php
		if ($arResult["CALLBACK"] == "CHANGED")
		{
			?>
			iframePopup.onTaskChanged(taskData);
			<?php
		}
		else
		{
			if (is_array($arResult["SUBTASKS"]) && count($arResult["SUBTASKS"]))
			{
				?>
				iframePopup.onTaskAdded(
					taskData,
					null,
					{
						multipleTasksAdded : true,
						firstTask          : true,
						callbackOnAfterAdd : function () {
							var subTaskData = null;
							<?php

							foreach ($arResult["SUBTASKS"] as $subTaskData)
							{
								?>
								subTaskData = <?php tasksRenderJSON($subTaskData, 0, $arPaths, true, true, true, $arParams["NAME_TEMPLATE"]); ?>
								iframePopup.onTaskAdded(
									subTaskData,
									null,
									{
										multipleTasksAdded : true,
										firstTask          : false
									}
								);
								<?php
							}
							?>
						}
					}
				);
				<?php
			}
			else
			{
				?>
				iframePopup.onTaskAdded(taskData);
				<?php
			}
		}
		?>

		if (iframePopup.lastAction != "view")
		{
			iframePopup.close();
		}
	}
})();
<?php endif?>

var arFilter = {};
var arOrder = {};
var ajaxUrl = "/bitrix/components/bitrix/tasks.list/ajax.php?SITE_ID=<?php echo SITE_ID?><?php echo $arResult["TASK_TYPE"] == "group" ? "&GROUP_ID=".$arParams["GROUP_ID"] : ""?>";
var postFormAction = "<?php echo CUtil::JSEscape(POST_FORM_ACTION_URI)?>";
var detailTaksID = <?php echo $arResult["TASK"]["ID"]?>;

var currentUser = <?php echo $USER->GetID()?>;
var defaultQuickParent = <?php echo $arResult["TASK"]["ID"]?>;

var reminders = <?php echo $arResult["REMINDERS"] ? CUtil::PhpToJsObject($arResult["REMINDERS"]) : "[]" ?>;

<?php
// Prevent loading page without header and footer when not in iframe (it's may happens on "open in new window")
if ($arResult["IS_IFRAME"])
{
	?>
	if (window == window.top)
	{
		// not in iframe, so reload page as not in IFRAME
		window.location = '<?php echo CUtil::JSEscape($APPLICATION->GetCurPageParam('', array('IFRAME'))); ?>';
	}
	<?php
}
?>

var tasksRemindersPopUp;
BX.ready(function() {
	if (BX('pagetitle'))
		BX('pagetitle').style.paddingRight = '200px';

	tasksRemindersPopUp = new BX.TaskReminders.create("tasks-reminder-popup", BX("task-reminder-link"), reminders, <?php echo $arResult["TASK"]["DEADLINE"] ? "\"".CUtil::JSEscape($arResult["TASK"]["DEADLINE"])."\"" : "false" ?>, {
		events: {
			onRemindersSave: function (reminders)
			{
				for (var i = 0; i < this.reminders.length; i++)
				{
					reminders[i].r_date = BX.date.format(
						BX.date.convertBitrixFormat(
							BX.message('FORMAT_DATE')
						),
						reminders[i].date
					);
					//reminders[i].r_date = this.calendar.FormatDate(reminders[i].date);
				}
				var data = {
					mode : "reminders",
					sessid : BX.message("bitrix_sessid"),
					id : <?php echo $arResult["TASK"]["ID"]?>,
					reminders : reminders
				};
				BX.ajax.post(ajaxUrl, data);
			},
			onRemindersChange: function (reminders) {
				if (reminders.length) {
					BX.addClass(BX("task-reminder-link").parentNode, "task-reminder-selected");
				} else {
					BX.removeClass(BX("task-reminder-link").parentNode, "task-reminder-selected");
				}
			}
		}
	});

	BX.bind(BX("task-reminder-link"), "click", function (e) {
		if(!e) e = window.event;

		tasksRemindersPopUp.show();

		BX.PreventDefault(e);
	});
});

var tasks_funcOnChangeOfSomeDateFields = function (field)
{
	value = field.value;

	if (field.id == "task-new-item-deadline" || field.id == "task-deadline-hidden")
		BX.removeClass(field.parentNode.parentNode, "webform-field-textbox-empty");

	if (field.id == "task-deadline-hidden")
	{
		var dateSpan = field.previousSibling;
		dateSpan.innerHTML = value;
		dateSpan.className = "task-detail-deadline webform-field-action-link";
		field.nextSibling.style.display = "";
		field.value = value;
		tasksRemindersPopUp.setDeadline(field.value)
		var data = {
			mode : "deadline",
			sessid : BX.message("bitrix_sessid"),
			id : <?php echo $arResult["TASK"]["ID"]?>,
			deadline : value
		};

		BX.ajax({
			'url' : ajaxUrl,
			'dataType': 'html',
			'method' : 'POST',
			'data' : data,
			'processData' : true,
			'onsuccess': function() {
				var data = {
					'PATH_TO_USER_PROFILE' : '<?php echo CUtil::JSEscape($arParams['PATH_TO_USER_PROFILE']); ?>',
					'sessid' : BX.message('bitrix_sessid'),
					'task_id' : <?php echo (int) $arResult['TASK']['ID']; ?>
				};

				// name format
				var urlRequest = '<?php
					echo CUtil::JSEscape($this->__component->GetPath() 
						. '/ajax.php?lang=' . urlencode(LANGUAGE_ID) 
						. '&action=render_task_log_last_row_with_date_change'
						. '&SITE_ID=' . urlencode($arParams['SITE_ID'])
						. '&nt=' . urlencode($arParams['NAME_TEMPLATE']));
					?>';

				BX.ajax({
					'method': 'POST',
					'dataType': 'json',
					'url': urlRequest,
					'data':  data,
					'processData' : true,
					'onsuccess': function(datum) {

						var count = parseInt(BX('task-switcher-text-log-count').innerHTML, 10) + 1;
						BX('task-switcher-text-log-count').innerHTML = count.toString();

						var row = BX.create("tr", {  children : [
							BX.create(
								"td",
								{
									props : { className: "task-log-date-column" },
									html : datum.td1
								}
							),
							BX.create(
								"td",
								{
									props : { className: "task-log-author-column" },
									html : datum.td2
								}
							),
							BX.create(
								"td",
								{
									props : { className: "task-log-where-column" },
									html : datum.td3
								}
							),
							BX.create(
								"td",
								{
									props : { className: "task-log-what-column" },
									html : datum.td4
								}
							),
						]});

						BX('task-log-table').appendChild(row);
						return;
					}
				});
				return;
			}
		});

		if (!taskData.dateDeadline || taskData.dateDeadline.getTime() != value)
		{
			taskData.dateDeadline = new Date(BX.parseDate(value));
			var form = document.float_calendar_time;
			if (form)
			{
				taskData.dateDeadline.setHours(parseInt(form.hours.value, 10));
				taskData.dateDeadline.setMinutes(parseInt(form.minutes.value, 10));
				taskData.dateDeadline.setSeconds(parseInt(form.seconds.value, 10));
			}
			window.top.BX.TasksIFrameInst.onTaskChanged(taskData);
		}
	}
};

var createMenu = [
	{
		text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK")); ?>',
		title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK")); ?>',
		className : "task-menu-popup-item-create",
		href: '<?php echo CUtil::JSEscape($createUrl); ?>',
		onclick : function(event) {
			AddQuickPopupTask(event);
			this.popupWindow.close();
		}
	},
	{
		text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_SUBTASK_2")); ?>',
		title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_SUBTASK_2")); ?>',
		className : "task-menu-popup-item-create",
		href: '<?php echo CUtil::JSEscape($createSubtaskUrl)?>',
		onclick : function(event) {
			AddQuickPopupTask(event, {PARENT_ID: <?php echo (int) $arResult['TASK']['ID']; ?>});
			this.popupWindow.close();
		}
	}
]
</script>
<div class="webform task-detail">
	<div class="webform-round-corners webform-main-fields">
		<div class="webform-corners-top">
			<div class="webform-left-corner"></div>
			<div class="webform-right-corner"></div>
		</div>
		<div class="webform-content task-detail-title-label"><?php echo GetMessage("TASKS_TASK_TITLE")?>
			<div class="task-reminder<?php if ($arResult["REMINDERS"]):?> task-reminder-selected<?php endif?>"><a href="" class="webform-field-action-link task-reminder-link" id="task-reminder-link"><?php echo GetMessage("TASKS_REMIND")?></a></div>
		</div>
	</div>

	<div class="webform-round-corners webform-main-block webform-main-block-topless webform-main-block-bottomless">
		<div class="webform-content">
			<div class="task-detail-title"><?php echo $arResult["TASK"]["TITLE"]?></div>
			<div class="task-detail-description"><?php
				echo $arResult['TASK']['~DESCRIPTION'];
			?></div>
		</div>
	</div>

	<div class="webform-round-corners webform-additional-block webform-additional-block-topless">
		<div class="webform-content">
			<table cellspacing="0" class="task-detail-additional-layout">
				<tr>
					<td class="task-detail-additional-layout-tags">
						<label><?php echo GetMessage("TASKS_TASK_TAGS")?>:</label><span class="task-detail-tags"><?php
							if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID())
							{
								$name = $APPLICATION->IncludeComponent(
									"bitrix:tasks.tags.selector",
									".default",
									array(
										"NAME" => "TAGS",
										"VALUE" => $arResult["TASK"]["~TAGS"],
										"ON_SELECT" => "SaveTags"
									),
									null,
									array('HIDE_ICONS' => 'Y')
								);
							}
							elseif ($arResult["TASK"]["TAGS"])
							{
								if (is_array($arResult["TASK"]["TAGS"]))
								{
									echo implode(", ", $arResult["TASK"]["TAGS"]);
								}
								else
								{
									echo $arResult["TASK"]["TAGS"];
								}
							}
							else
							{
								echo GetMessage("TASKS_TASK_NO_TAGS");
							}
						?></span>
					</td>
					<td class="task-detail-additional-layout-files">
						<div class="task-detail-files">
							<?php if ($arResult["TASK"]["FILES"] || $arResult["TASK"]["FORUM_FILES"]):?>
								<label class="task-detail-files-title"><?php echo GetMessage("TASKS_TASK_FILES")?>:</label>
								<div class="task-detail-files-list">
									<?php

									$bCanRemoveFiles = false;
									if ($arResult["TASK"]['META:ALLOWED_ACTIONS']['ACTION_EDIT'] === true)
										$bCanRemoveFiles = true;

									$i = 0;
									foreach($arResult["TASK"]["FILES"] as $key=>$file)
									{
										$linkId = 'task-detail-file-href-' . (int) $file['ID'];


										?>
										<?php $i++?>
										<div class="task-detail-file webform-field-upload-list"
											><span class="task-detail-file-number"><?php echo $i; 
											?>.</span><span class="task-detail-file-info"
											><a id="<?php echo $linkId; ?>" 
												href="/bitrix/components/bitrix/tasks.task.detail/show_file.php?fid=<?php echo $file["ID"]?>" 
												target="_blank" class="task-detail-file-link"
											><?php
												echo $file["ORIGINAL_NAME"];
											?></a><span class="task-detail-file-size">(<?php
												echo CFile::FormatSize($file["FILE_SIZE"]);
											?>)</span><?php

											if ($bCanRemoveFiles)
											{
												?><a href="#" class="delete-file"
													onclick="
														BX.PreventDefault(event);
														return tasksDetailsNS.deleteFile(
															<?php echo (int) $file['ID']; ?>, 
															<?php echo (int) $arResult["TASK"]['ID']; ?>, 
															'<?php echo $linkId; ?>',
															this
														);"
												></a><?php
											}
										?></span></div>
										<?php
									}

									foreach($arResult["TASK"]["FORUM_FILES"] as $file):?>
										<?php $i++?>
										<div class="task-detail-file"><span class="task-detail-file-number"><?php echo $i?>.</span><span class="task-detail-file-info"><a href="#message<?php echo $file["MESSAGE_ID"]?>" class="task-detail-file-comment"/><a class="task-detail-file-link" target="_blank" href="/bitrix/components/bitrix/forum.interface/show_file.php?fid=<?php echo $file["ID"]?>"><?php echo $file["ORIGINAL_NAME"]?></a><span class="task-detail-file-size">(<?php echo CFile::FormatSize($file["FILE_SIZE"])?>)</span></span></div>
									<?php endforeach?>
								</div>
							<?php else:?>
							&nbsp;
							<?php endif?>
						</div>
					</td>
				</tr>
			</table>
			<?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID()):?>
				<div class="task-detail-group"><label><?php echo GetMessage("TASKS_TASK_GROUP")?>:</label><span class="task-detail-group-name task-detail-group-name-inline"><a href="<?php echo $arResult["TASK"]["GROUP_ID"] ? CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["TASK"]["GROUP_ID"])) : "javascript: void(0);"?>" class="webform-field-action-link" id="task-group-change"><?php if ($arResult["TASK"]["GROUP_ID"]):?><?php echo $arResult["TASK"]["GROUP_NAME"]?></a><span class="task-group-delete" onclick="ClearGroup(<?php echo $arResult["TASK"]["GROUP_ID"]?>, this)"></span><?php else:?><?php echo GetMessage("TASKS_GROUP_ADD")?></a><?php endif?></span></div>
				<?php
					$APPLICATION->IncludeComponent(
						"bitrix:socialnetwork.group.selector", ".default", array(
							"JS_OBJECT_NAME" => "taskGroupPopup",
							"BIND_ELEMENT" => "task-group-change",
							"SELECTED" => ($arResult["TASK"]["GROUP_ID"] ? $arResult["TASK"]["GROUP_ID"] : 0),
							"ON_SELECT" => "onTaskGroupSelect",
							"FEATURES_PERMS" => array("tasks", "create_tasks")
						), null, array("HIDE_ICONS" => "Y")
					);
				?>
			<?php elseif ($arResult["TASK"]["GROUP_ID"] && CSocNetGroup::CanUserViewGroup($USER->GetID(), $arResult["TASK"]["GROUP_ID"])):?>
				<div class="task-detail-group"><span class="task-detail-group-label"><?php echo GetMessage("TASKS_TASK_GROUP")?>:</span><span class="task-detail-group-name"><a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arResult["TASK"]["GROUP_ID"]))?>" class="task-detail-group-link" target="_top"><?php echo $arResult["TASK"]["GROUP_NAME"]?></a></span></div>
			<?php endif?>
			<?php
				if ($arResult["TASK"]["PARENT_ID"]):
					$rsTask = CTasks::GetList(array(), array("ID" => $arResult["TASK"]["PARENT_ID"]), array("ID", "TITLE"));
					if ($parent = $rsTask->GetNext()):
			?>
					<div class="task-detail-supertask">
						<span class="task-detail-supertask-label"><?php echo GetMessage("TASKS_PARENT_TASK")?>:</span>
						<span class="task-detail-group-name"><a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $parent["ID"], "action" => "view"))?>" class="task-detail-group-link"<?php if ($arResult["IS_IFRAME"]):?> onclick="taskIFramePopup.view(<?php echo $parent["ID"]?>);return false;"<?php endif?>><?php echo $parent["TITLE"]?></a></span>
					</div>
				<?php endif?>
			<?php endif?>
			<?php if($arResult["SHOW_USER_FIELDS"]):?>
				<div class="task-detail-properties">
					<table cellspacing="0" class="task-properties-layout">
						<?php
						foreach($arResult["USER_FIELDS"] as $arUserField)
						{
							if ($arUserField["VALUE"] === null)
								continue;

							?>
							<tr>
								<td class="task-property-name"><?php echo htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"])?>:</td>
								<td class="task-property-value"><span class="fields"><?php
								if ($arUserField['USER_TYPE']['USER_TYPE_ID'] === 'file')
								{
									if ( ! is_array($arUserField['VALUE']) )
										$arUserField['VALUE'] = array($arUserField['VALUE']);

									$first = true;
									foreach ($arUserField['VALUE'] as $fileId)
									{
										$isImage = false;
										$arFile = CFile::GetFileArray($fileId);

										if ( ! $arFile )
											continue;

										if (
											(substr($arFile["CONTENT_TYPE"], 0, 6) == "image/")
											//&& (CFile::CheckImageFile($arFile) === null)
										)
										{
											$isImage = true;
										}


										if ( ! $first )
											echo '<span class="bx-br-separator"><br /></span>';
										else
											$first = false;

										echo '<span class="fields files">';

										if ($isImage)
										{
											$arFile['SRC'] = "/bitrix/components/bitrix/tasks.task.detail/show_file.php?fid=" . $arFile['ID'] . "&amp;TASK_ID=" . (int) $arResult['TASK']['ID'];

											echo CFile::ShowImage(
												$arFile, 
												$arParams["FILE_MAX_WIDTH"], 
												$arParams["FILE_MAX_HEIGHT"], 
												"", 
												"", 
												($arParams["FILE_SHOW_POPUP"]=="Y")
											);
										}
										else
										{
											?>
											<span class="task-detail-file-info"><a 
												href="/bitrix/components/bitrix/tasks.task.detail/show_file.php?fid=<?php echo $arFile['ID']; ?>&amp;TASK_ID=<?php echo (int) $arResult['TASK']['ID']; ?>"
												target="_blank" class="task-detail-file-link"><?php
													echo htmlspecialcharsbx($arFile['ORIGINAL_NAME']);
												?></a><span class="task-detail-file-size">(<?php
													echo CFile::FormatSize($arFile['FILE_SIZE']);
											?>)</span></span>
											<?php
										}

										echo '</span>';
									}
								}
								else
								{
									$APPLICATION->IncludeComponent(
										"bitrix:system.field.view",
										$arUserField["USER_TYPE"]["USER_TYPE_ID"],
										array("arUserField" => $arUserField),
										null,
										array("HIDE_ICONS"=>"Y")
									);
								}
								?></td>
							</tr>
							<?php
						}
						?>
					</table>
				</div>
			<?php endif?>
		</div>
		<div class="webform-corners-bottom">
			<div class="webform-left-corner"></div>
			<div class="webform-right-corner"></div>
		</div>
	</div>

	<?php if ($arResult["TASK"]["REAL_STATUS"] == 7):?>
	<div class="webform-round-corners webform-warning-block">
		<div class="webform-corners-top">
			<div class="webform-left-corner"></div>
			<div class="webform-right-corner"></div>
		</div>
		<div class="webform-content">
			<div class="webform-warning-content">
				<div class="webform-warning-title"><?php echo GetMessage("TASKS_TASK_DECLINE_REASON")?>:</div>
				<div class="webform-warning-text"><?php echo $arResult["TASK"]["DECLINE_REASON"]?></div>
			</div>
		</div>
		<div class="webform-corners-bottom">
			<div class="webform-left-corner"></div>
			<div class="webform-right-corner"></div>
		</div>
	</div>
	<?php endif?>

	<div class="webform-buttons task-buttons">
		<?php
			if($arResult["TASK"]["REAL_STATUS"] == 1 && $arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=accept&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button webform-small-button-accept"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php echo GetMessage("TASKS_ACCEPT_TASK")?></span><span class="webform-small-button-right"></span></a><?php
			}

			if($arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID() && in_array($arResult["TASK"]["REAL_STATUS"], array(2, 3, 6)))
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=close&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button webform-small-button-accept"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php echo GetMessage("TASKS_CLOSE_TASK")?></span><span class="webform-small-button-right"></span></a><?php
			}

			if(in_array($arResult["TASK"]["REAL_STATUS"], array(1, 2, 6)) && $arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=start&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button webform-small-button-accept"><span class="webform-small-button-left"></span><span class="webform-small-button-icon task-button-icon-play"></span><span class="webform-small-button-text"><?php echo GetMessage("TASKS_START_TASK")?></span><span class="webform-small-button-right"></span></a><?php
			}

			if($arResult["TASK"]["REAL_STATUS"] == 1 && $arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=decline&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button webform-small-button-decline" onclick="return ShowDeclinePopup(this, <?php echo $arResult["TASK"]["ID"]?>);"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php echo GetMessage("TASKS_DECLINE_TASK")?></span><span class="webform-small-button-right"></span></a><?php
			}

			if($arResult["TASK"]["REAL_STATUS"] == 4 && $arResult["TASK"]["CREATED_BY"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=close&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button webform-small-button-accept"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php echo GetMessage("TASKS_APPROVE_TASK")?></span><span class="webform-small-button-right"></span></a><?php
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=".($arResult["TASK"]["SUBORDINATE"] == "Y" ? "accept" : "renew")."&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button webform-small-button-decline"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?php echo GetMessage("TASKS_REDO_TASK")?></span><span class="webform-small-button-right"></span></a><?php
			}

			$copyUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit"));

			?><script type="text/javascript">
				var taskMenu = [
					{ text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_COPY_TASK")); ?>', title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_COPY_TASK_EX")); ?>', className : "task-menu-popup-item-copy", href : "<?php echo $copyUrl.(strpos($copyUrl, "?") === false ? "?" : "&")."COPY=".$arResult["TASK"]["ID"].($arResult["IS_IFRAME"] ? "&IFRAME=Y" : "")?>" }
					<?php if($arResult["IS_IFRAME"]):?>
						,
						{
							text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK")); ?>',
							title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK")); ?>',
							className : "task-menu-popup-item-create",
							href: "<?php echo CUtil::JSEscape($createUrl)?>",
							onclick : function()
							{
								AddQuickPopupTask(event);
							}
						},
						{ text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_SUBTASK_2")); ?>', title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_SUBTASK_2")); ?>', className : "task-menu-popup-item-create", href: "<?php echo CUtil::JSEscape($createSubtaskUrl)?>", onclick : function(event, item) {AddPopupSubtask(<?php echo $arResult["TASK"]["ID"]?>, event);} },
						{ text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_QUICK_SUBTASK")); ?>', title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_QUICK_SUBTASK")); ?>', className : "task-menu-popup-item-create-quick", onclick : function(event) { ShowQuickTask(BX('subtask-list-table')<?php if ($arResult["TASK"]["GROUP_ID"]):?>, {group: {id: <?php echo $arResult["TASK"]["GROUP_ID"]?>, title: '<?php echo CUtil::JSEscape($arResult["TASK"]["GROUP_NAME"])?>'}}<?php endif?>); this.popupWindow.close(); } }
					<?php endif?>
					<?php
					if ($arResult['TASK']['META:CAN_ADD_TO_DAY_PLAN'] === 'Y')
					{
						?>,{ text : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK_TO_TIMEMAN")); ?>', title : '<?php echo CUtil::JSEscape(GetMessage("TASKS_ADD_TASK_TO_TIMEMAN_EX")); ?>', className : "task-menu-popup-item-add-to-tm", onclick : function() { var func = false; if (window.top.Add2Timeman) func = window.top.Add2Timeman; else if (window.Add2Timeman) func = window.Add2Timeman; if (func !== false) func (this, <?php echo $arResult["TASK"]["ID"]?>); } }<?php
					}
				?>];
			</script><a href="" class="webform-small-button task-small-button-menu" id="task-small-button-menu" onclick="return ShowActionMenu(this, <?php echo $arResult["TASK"]["ID"]?>, taskMenu);"><span class="webform-small-button-left"></span><span class="webform-small-button-icon"></span><span class="webform-small-button-right"></span></a><?php

			$arSubDeps = CTasks::GetSubordinateDeps();


			if (
				($arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID()) 
				&& ($arResult["TASK"]["CREATED_BY"] != $USER->GetID()) 
				&& sizeof($arSubDeps)
			)
			{
				?><a 
					href="javascript: void(0);" 
					class="webform-small-button-link task-button-delegate-link" 
					onclick="
						ShowDelegatePopup(
							this, 
							<?php echo $arResult['TASK']['ID']; ?>
							)"
					><?php
						echo GetMessage('TASKS_DELEGATE_TASK');
				?></a><?php

				$groupIdForSite = false;

				if (isset($_GET["GROUP_ID"])
					&& (intval($_GET["GROUP_ID"]) > 0)
				)
				{
					$groupIdForSite = (int) $_GET["GROUP_ID"];
				}
				elseif (isset($arParams["GROUP_ID"])
					&& (intval($arParams["GROUP_ID"]) > 0)
				)
				{
					$groupIdForSite = (int) $arParams["GROUP_ID"];
				}

				$name = $APPLICATION->IncludeComponent(
					"bitrix:intranet.user.selector.new",
					".default",
					array(
						"MULTIPLE" => "N",
						"NAME" => "DELEGATE",
						"POPUP" => "Y",
						"ON_CHANGE" => "onDelegateChange",
						"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
						"SITE_ID" => SITE_ID,
						"SUBORDINATE_ONLY" => "Y",
						'SHOW_EXTRANET_USERS' => 'NONE',
						"GROUP_ID_FOR_SITE" => $groupIdForSite,
						'DISPLAY_TAB_GROUP' => 'Y',
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			}

			if(CTasks::CanCurrentUserEdit($arResult["TASK"]))
			{
				$editURL  = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $arResult["TASK"]["ID"], "action" => "edit"));
				if ($arResult["IS_IFRAME"])
				{
					$editURL .= ((strpos($editURL, "?") === false ? "?" : "&") ? "?" : "&")."IFRAME=Y";
				}
				?><a href="<?php echo $editURL?>" class="webform-small-button-link task-button-edit-link"><?php echo GetMessage("TASKS_EDIT_TASK")?></a><?php
			}

			if($arResult["TASK"]["RESPONSIBLE_ID"] != $USER->GetID() && $arResult["TASK"]["CREATED_BY"] == $USER->GetID() && in_array($arResult["TASK"]["REAL_STATUS"], array(2, 3, 6, 7)))
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=close&".bitrix_sessid_get(), array("sessid", "ACTION"))?>" class="webform-small-button-link task-button-approve-link"><?php echo GetMessage("TASKS_APPROVE_TASK")?></a><?php
			}

			if(($arResult["TASK"]["REAL_STATUS"] == 2 || $arResult["TASK"]["REAL_STATUS"] == 3) && $arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=defer&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button-link task-button-hold-link"><?php echo GetMessage("TASKS_PAUSE_TASK")?></a><?php
			}

			if(($arResult["TASK"]["REAL_STATUS"] == 4 || $arResult["TASK"]["REAL_STATUS"] == 5) && $arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=start&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button-link task-button-hold-link"><?php echo GetMessage("TASKS_RENEW_TASK")?></a><?php
			}
			elseif(($arResult["TASK"]["REAL_STATUS"] == 5 || $arResult["TASK"]["REAL_STATUS"] == 7) && $arResult["TASK"]["CREATED_BY"] == $USER->GetID())
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=".($arResult["TASK"]["SUBORDINATE"] == "Y" ? "accept" : "renew")."&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button-link task-button-hold-link"><?php echo GetMessage("TASKS_RENEW_TASK")?></a><?php
			}

			if(CTasks::CanCurrentUserDelete($arResult["TASK"]))
			{
				?><a href="<?php echo $APPLICATION->GetCurPageParam("ACTION=delete&".bitrix_sessid_get(), array("sessid", "ACTION"));?>" class="webform-small-button-link task-button-delete-link" target="_top" onclick="onDeleteClick(event, <?php echo $arResult["TASK"]["ID"]?>);"><?php echo GetMessage("TASKS_DELETE_TASK")?></a><?php
			}
			?>
	</div>

	<div class="task-detail-subtasks" id="task-detail-subtasks-block"<?php if (!sizeof($arResult["SUBTASKS"])):?> style="display: none;"<?php endif?>>
		<div class="task-list">
			<div class="task-list-left-corner"></div>
			<div class="task-list-right-corner"></div>
			<table class="task-list-table task-list-table-unsortable" cellspacing="0" id="subtask-list-table">

				<colgroup>
					<col class="task-title-column" />
					<col class="task-menu-column" />
					<col class="task-flag-column" />
					<col class="task-priority-column" />
					<col class="task-deadline-column" />
					<col class="task-responsible-column" />
					<col class="task-director-column" />
					<col class="task-grade-column" />
					<col class="task-complete-column" />
				</colgroup>

				<thead>
					<tr>
						<th class="task-title-column"  colspan="4">
							<div class="task-head-cell">
								<span class="task-head-cell-sort-order"></span>
								<span class="task-head-cell-title"><?php echo GetMessage("TASKS_TASK_SUBTASKS")?></span>
								<span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div>
						</th>
						<th class="task-deadline-column">
							<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_DEADLINE")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>
						<th class="task-responsible-column">
							<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_RESPONSIBLE")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>
						<th  class="task-director-column" >
							<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_CREATOR")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>

						<th class="task-grade-column">&nbsp;</th>
						<th class="task-complete-column">&nbsp;</th>

					</tr>
				</thead>
				<tbody>
					<tr class="task-list-item task-list-item-hidden" id="task-new-item-row">
						<td class="task-new-item-column" colspan="9">
							<form onSubmit="return AddTask()">
								<table class="task-new-item-table" cellspacing="0">
									<tr>
										<td class="task-new-item-title"><label for="task-new-item-name"><?php 
											echo GetMessage("TASKS_QUICK_TITLE"); 
											?></label><input 
												type="text" id="task-new-item-name" class="task-new-item-textbox" 
											/><div class="task-new-item-description"><span class="task-new-item-link" 
												id="task-new-item-description-link"><?php echo GetMessage("TASKS_QUICK_DESCRIPTION");
											?></span><div class="task-description-textarea" 
												id="task-quick-description-textarea"><textarea 
												id="task-new-item-description"></textarea></div></div><div class="task-new-item-buttons"><span class="task-new-item-buttons-wrap"><input type="submit" id="task-new-item-submit" value="<?php echo GetMessage("TASKS_QUICK_SAVE")?>" />&nbsp;<input type="button" id="task-new-item-cancel" value="<?php echo GetMessage("TASKS_QUICK_CANCEL")?>" onClick="HideQuickTask()" /></span><span class="task-new-item-link" id="task-new-item-link-group"><?php echo GetMessage("TASKS_QUICK_IN_GROUP")?></span></div></td>
										<td class="task-new-item-priority"><a href="javascript: void(0)" class="task-priority-box" onclick="return ShowPriorityPopup(0, this, 0);" title="<?php echo GetMessage("TASKS_PRIORITY")?>: <?php echo GetMessage("TASKS_PRIORITY_0")?>"><i id="task-new-item-priority" class="task-priority-icon task-priority-0"></i></a></td>
										<td class="task-new-item-deadline">
											<label for="task-new-item-deadline"><?php echo GetMessage("TASKS_QUICK_DEADLINE")?></label>
											<input type="text" id="task-new-item-deadline" name="DEADLINE" class="task-new-item-textbox" 
											onClick="
											<?php /* jsCal endar. Show(this, this.name, this.name, '', true, Math.round((new Date()) / 1000) - (new Date()).getTimezoneOffset()*60, '', false); */ ?>
											BX.calendar({
												node: this, 
												field: BX('task-new-item-deadline'), 
												form: '', 
												bTime: true, 
												currentTime: Math.round((new Date()) / 1000) - (new Date()).getTimezoneOffset()*60, 
												bHideTimebar: false,
												callback_after: function(value) {
													tasks_funcOnChangeOfSomeDateFields(BX('task-new-item-deadline'));
												}
											});
											" />
										</td>
										<td class="task-new-item-responsible"><label for="task-new-item-responsible"><?php echo GetMessage("TASKS_RESPONSIBLE")?></label><input type="text" id="task-new-item-responsible" class="task-new-item-textbox" value="<?php 
											echo tasksFormatName(
												$arResult["USER"] ? $arResult["USER"]["NAME"] : htmlspecialcharsbx($USER->GetFirstName()), 
												$arResult["USER"] ? $arResult["USER"]["LAST_NAME"] : htmlspecialcharsbx($USER->GetLastName()), 
												$arResult["USER"] ? $arResult["USER"]["LOGIN"] : htmlspecialcharsbx($USER->GetLogin()), 
												$arResult["USER"] ? $arResult["USER"]["SECOND_NAME"] : htmlspecialcharsbx($USER->GetSecondName()), 
												$arParams["NAME_TEMPLATE"],
												false
												);
											?>" /><input type="hidden" name="task-new-item-responsible-hidden" id="task-new-item-responsible-hidden" value="<?php echo $arParams["USER_ID"]?>" /></td>
									</tr>
								</table>
								<?php
									$APPLICATION->IncludeComponent(
										"bitrix:intranet.user.selector.new",
										".default",
										array(
											"MULTIPLE" => "N",
											"NAME" => "QUICK_RESPONSIBLE",
											"INPUT_NAME" => "task-new-item-responsible",
											"VALUE" => $arParams["USER_ID"],
											"POPUP" => "Y",
											"ON_SELECT" => "onQuickResponsibleSelect",
											"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
											"SITE_ID" => SITE_ID,
											"GROUP_ID_FOR_SITE" => (isset($_GET["GROUP_ID"]) && intval($_GET["GROUP_ID"]) > 0 ? $_GET["GROUP_ID"] : (isset($arParams["GROUP_ID"]) && intval($arParams["GROUP_ID"]) > 0 ? $arParams["GROUP_ID"] : false)),
											'SHOW_EXTRANET_USERS' => 'FROM_MY_GROUPS',
											'DISPLAY_TAB_GROUP' => 'Y',
											"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
										),
										null,
										array("HIDE_ICONS" => "Y")
									);
									$APPLICATION->IncludeComponent(
										"bitrix:socialnetwork.group.selector", ".default", array(
											"BIND_ELEMENT" => "task-new-item-link-group",
											"ON_SELECT" => "onGroupSelect",
											"FEATURES_PERMS" => array("tasks", "create_tasks")
										), null, array("HIDE_ICONS" => "Y")
									);
								?>
							</form>
						</td>
					</tr>
					<?php if (sizeof($arResult["SUBTASKS"])):?>
						<?php $currentProject = false?>
						<?php foreach($arResult["SUBTASKS"] as $task):?>
							<?php if ($arResult["TASK_TYPE"] != "group" && $task["GROUP_ID"] && $task["GROUP_ID"] != $currentProject):?>
									<?php
										$currentProject = $task["GROUP_ID"];
										$task["GROUP_NAME"] = $arResult["GROUPS"][$task["GROUP_ID"]]["NAME"];
									?>
									<tr class="task-list-item" id="task-project-<?php echo $task["GROUP_ID"]?>">
										<td class="task-project-column" colspan="9">
											<div class="task-project-column-inner">
												<div class="task-project-name"><span class="task-project-folding<?php if (!$arResult["GROUPS"][$task["GROUP_ID"]]["EXPANDED"]):?> task-project-folding-closed<?php endif?>" onclick="ToggleProjectTasks(<?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]?>, event);"></span><a class="task-project-name-link" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TASKS"], array("group_id" => $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]))?>" onclick="ToggleProjectTasks(<?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]?>, event);"><?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["NAME"]?></a></div>
												<?php if (is_object($USER) && $USER->IsAuthorized()):?>
													<div class="task-project-actions"><a class="task-project-action-link" href="<?php $path = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit")); echo $path.(strstr($path, "?") ? "&" : "?")."GROUP_ID=".$arResult["GROUPS"][$task["GROUP_ID"]]["ID"].($arResult["IS_IFRAME"] ? "&IFRAME=Y" : "");?>"><i class="task-project-action-icon"></i><span class="task-project-action-text"><?php echo GetMessage("TASKS_ADD_TASK")?></span></a><span class="task-project-lightning" onclick="ShowQuickTask(null, {group: {id: <?php echo $task["GROUP_ID"]?>, title: '<?php echo CUtil::JSEscape($arResult["GROUPS"][$task["GROUP_ID"]]["NAME"])?>'}});"></span></div>
												<?php endif?>
											</div>
										</td>
									</tr>
								<?php endif?>
							<?php tasksRenderListItem($task, $arResult["CHILDREN_COUNT"]["PARENT_".$task["ID"]], $arPaths, 0, false, false, SITE_ID, 0, true, false, "bitrix:tasks.list.item", ".default", $arParams["NAME_TEMPLATE"])?>
						<?php endforeach?>
					<?php else:?>
						<tr class="task-list-item" id="task-list-no-tasks"><td class="task-new-item-column" colspan="9" style="text-align: center"><?php echo GetMessage("TASKS_NO_SUBTASKS")?></td></tr>
					<?php endif?>
				</tbody>
			</table>
		</div>
	</div>

	<?php if (sizeof($arResult["PREV_TASKS"])):?>
	<div class="task-detail-previous-tasks">
		<div class="task-list">
			<div class="task-list-left-corner"></div>
			<div class="task-list-right-corner"></div>
			<table class="task-list-table task-list-table-unsortable" cellspacing="0">

				<colgroup>
					<col class="task-title-column" />
					<col class="task-menu-column" />
					<col class="task-flag-column" />
					<col class="task-priority-column" />
					<col class="task-deadline-column" />
					<col class="task-responsible-column" />
					<col class="task-director-column" />
					<col class="task-grade-column" />
					<col class="task-complete-column" />
				</colgroup>

				<thead>
				<tr>
					<th class="task-title-column"  colspan="4">
						<div class="task-head-cell">
							<span class="task-head-cell-sort-order"></span>
							<span class="task-head-cell-title"><?php echo GetMessage("TASKS_TASK_PREVIOUS_TASKS")?></span>
							<span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div>
					</th>
					<th class="task-deadline-column">
						<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_DEADLINE")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>
					<th class="task-responsible-column">
						<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_RESPONSIBLE")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>
					<th  class="task-director-column" >
						<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_CREATOR")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="#"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>

					<th class="task-grade-column">&nbsp;</th>
					<th class="task-complete-column">&nbsp;</th>

				</tr>
				</thead>
				<tbody>
					<?php if (sizeof($arResult["PREV_TASKS"])):?>
						<?php $currentProject = false?>
						<?php foreach($arResult["PREV_TASKS"] as $task):?>
							<?php if ($arResult["TASK_TYPE"] != "group" && $task["GROUP_ID"] && $task["GROUP_ID"] != $currentProject):?>
								<?php
									$currentProject = $task["GROUP_ID"];
									$task["GROUP_NAME"] = $arResult["GROUPS"][$task["GROUP_ID"]]["NAME"];
								?>
								<tr class="task-list-item" id="task-project-<?php echo $task["GROUP_ID"]?>">
									<td class="task-project-column" colspan="9">
										<div class="task-project-column-inner">
											<div class="task-project-name"><span class="task-project-folding<?php if (!$arResult["GROUPS"][$task["GROUP_ID"]]["EXPANDED"]):?> task-project-folding-closed<?php endif?>" onclick="ToggleProjectTasks(<?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]?>, event);"></span><a class="task-project-name-link" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TASKS"], array("group_id" => $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]))?>" onclick="ToggleProjectTasks(<?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]?>, event);"><?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["NAME"]?></a></div>
											<?php if (is_object($USER) && $USER->IsAuthorized()):?>
												<div class="task-project-actions"><a class="task-project-action-link" href="<?php $path = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit")); echo $path.(strstr($path, "?") ? "&" : "?")."GROUP_ID=".$arResult["GROUPS"][$task["GROUP_ID"]]["ID"].($arResult["IS_IFRAME"] ? "&IFRAME=Y" : "");?>"><i class="task-project-action-icon"></i><span class="task-project-action-text"><?php echo GetMessage("TASKS_ADD_TASK")?></span></a><span class="task-project-lightning" onclick="ShowQuickTask(null, {group: {id: <?php echo $task["GROUP_ID"]?>, title: '<?php echo CUtil::JSEscape($arResult["GROUPS"][$task["GROUP_ID"]]["NAME"])?>'}});"></span></div>
											<?php endif?>
										</div>
									</td>
								</tr>
							<?php endif?>
							<?php tasksRenderListItem($task, $arResult["CHILDREN_COUNT"]["PARENT_".$task["ID"]], $arPaths, 0, false, false, SITE_ID, 0, true, false, "bitrix:tasks.list.item", ".default", $arParams["NAME_TEMPLATE"])?>
						<?php endforeach?>
					<?php endif?>
				</tbody>
			</table>
		</div>
	</div>
	<?php endif?>

	<div class="task-comments-and-log">

		<div class="task-comments-log-switcher">
			<span class="task-switcher task-switcher-selected" id="task-comments-switcher"><span class="task-switcher-left"></span><span class="task-switcher-text"><span class="task-switcher-text-inner"><?php echo GetMessage("TASKS_TASK_COMMENTS")?> (<?php echo intval($arResult["TASK"]["COMMENTS_COUNT"])?>)</span></span><span class="task-switcher-right"></span></span>
			<span class="task-switcher" id="task-log-switcher"><span class="task-switcher-left"></span><span class="task-switcher-text"><span class="task-switcher-text-inner"><?php echo GetMessage("TASKS_TASK_LOG")?> (<span id="task-switcher-text-log-count"><?php echo sizeof($arResult["LOG"])?></span>)</span></span><span class="task-switcher-right"></span></span>
			<span class="task-switcher" id="task-time-switcher"><span class="task-switcher-left"></span><span class="task-switcher-text"><span class="task-switcher-text-inner"><?php echo GetMessage("TASKS_ELAPSED_TIME")?> (<?php echo floor($arResult["FULL_ELAPSED_TIME"] / 60)?><?php echo GetMessage("TASKS_ELAPSED_H")?> <?php echo $arResult["FULL_ELAPSED_TIME"] % 60?><?php echo GetMessage("TASKS_ELAPSED_M")?>)</span></span><span class="task-switcher-right"></span></span>
		</div>

		<div class="task-comments-block task-comments-block-selected" id="task-comments-block">
			<a name="comments"></a>
			<?php
				$APPLICATION->IncludeComponent(
					'bitrix:tasks.topic.reviews',
					'',
					array(
						'TASK'                       => $arResult['TASK'],
						'CACHE_TYPE'                 => $arParams['CACHE_TYPE'],
						'CACHE_TIME'                 => $arParams['CACHE_TIME'],
						'MESSAGES_PER_PAGE'          => $arParams['ITEM_DETAIL_COUNT'],
						'USE_CAPTCHA'                => 'N',
						'PREORDER'                   => 'ACCORD_FORUM_SETTINGS',
						'PATH_TO_SMILE'              => $arParams['PATH_TO_FORUM_SMILE'],
						'FORUM_ID'                   => $arParams['FORUM_ID'],
						'TASK_ID'                    => $arResult['TASK']['ID'],
						'SHOW_RATING'                => $arParams['SHOW_RATING'],
						'RATING_TYPE'                => $arParams['RATING_TYPE'],
						'FILES_COUNT'                => $arResult['MAX_UPLOAD_FILES_IN_COMMENTS'],
						'SHOW_LINK_TO_FORUM'         => 'N',
						'URL_TEMPLATES_PROFILE_VIEW' => $arParams['PATH_TO_USER_PROFILE'],
						'PAGE_NAVIGATION_TEMPLATE'   => 'arrows',
						"NAME_TEMPLATE"              => $arParams["NAME_TEMPLATE"]
					),
					$component,
					array('HIDE_ICONS' => 'Y')
				);
			?>
		</div>

		<div class="task-log-block" id="task-log-block">
			<a name="updates"></a>
			<?php
				if (sizeof($arResult["LOG"]) > 0):
			?>

			<table id="task-log-table" class="task-log-table" cellspacing="0">
				<col class="task-log-date-column" />
				<col class="task-log-author-column" />
				<col class="task-log-where-column" />
				<col class="task-log-what-column" />

				<tr>
					<th class="task-log-date-column"><?php echo GetMessage("TASKS_LOG_WHEN")?></th>
					<th class="task-log-author-column"><?php echo GetMessage("TASKS_LOG_WHO")?></th>
					<th class="task-log-where-column"><?php echo GetMessage("TASKS_LOG_WHERE")?></th>
					<th class="task-log-what-column"><?php echo GetMessage("TASKS_LOG_WHAT")?></th>
				</tr>
				<?php

				$commentsCurrPage = (intval($_GET["PAGEN_2"]) > 1 ? intval($_GET["PAGEN_2"]) : 1);

				$funcFormatForHuman = function($seconds)
				{
					if ($seconds === NULL)
						return '';

					$hours = (int) ($seconds / 3600);

					if ($hours < 24)
						$duration = $hours . ' ' . GetMessage('TASKS_TASK_DURATION_HOURS');
					elseif ($hours % 24)
					{
						$duration = (int) ($hours / 24) . ' ' . GetMessage('TASKS_TASK_DURATION_DAYS') . ' ' 
							. ($hours % 24) . ' ' . GetMessage('TASKS_TASK_DURATION_HOURS');
					}
					else
						$duration = (int) ($hours / 24) . ' ' . GetMessage('TASKS_TASK_DURATION_DAYS');

					return ($duration);
				};

				$funcFormatForHumanMinutes = function($minutes)
				{
					if ($minutes === NULL)
						return '';

					$hours = (int) ($minutes / 60);

					if ($minutes < 60)
						$duration = $minutes . ' ' . GetMessage('TASKS_TASK_DURATION_MINUTES');
					elseif ($minutes % 60)
					{
						$duration = $hours . ' ' . GetMessage('TASKS_TASK_DURATION_HOURS') . ' ' 
							. (int) ($minutes % 60) . ' ' . GetMessage('TASKS_TASK_DURATION_MINUTES');
					}
					else
						$duration = $hours . ' ' . GetMessage('TASKS_TASK_DURATION_HOURS');

					return ($duration);
				};

				foreach($arResult["LOG"] as $record):?>
					<?php $anchor_id = RandString(8);?>
					<tr>
						<td class="task-log-date-column"><span class="task-log-date"><?php echo FormatDateFromDB($record["CREATED_DATE"]);?></span></td>
						<td class="task-log-author-column"><script type="text/javascript">BX.tooltip(<?php echo $record["USER_ID"]?>, "anchor_log_<?php echo $anchor_id?>", "");</script><a id="anchor_log_<?php echo $anchor_id?>" class="task-log-author" target="_top" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $record["USER_ID"]))?>"><?php 
							echo tasksFormatNameShort(
								$record["USER_NAME"], 
								$record["USER_LAST_NAME"], 
								$record["USER_LOGIN"], 
								$record["USER_SECOND_NAME"], 
								$arParams["NAME_TEMPLATE"],
								false
								)?></a></td>
						<td class="task-log-where-column"><span class="task-log-where"><?php echo GetMessage("TASKS_LOG_".$record["FIELD"])?><?php
							if ($record["FIELD"] == "DELETED_FILES")
							{
								?>: <?php echo $record["FROM_VALUE"]?><?php
							}
							elseif ($record["FIELD"] == "NEW_FILES")
							{
								?>: <?php echo $record["TO_VALUE"]?><?php
							}
							elseif ($record["FIELD"] == "COMMENT" || $record["FIELD"] == "COMMENT_EDIT")
							{
								$link = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $arResult["TASK"]["ID"], "action" => "view"));
								if ($pageNumber != 1)
								{
									$link .= (strpos($link, "?") === false ? "?" : "&")."MID=".intval($record["TO_VALUE"]);
								}
								if ($arResult["IS_IFRAME"])
								{
									$link .= (strpos($link, "?") === false ? "?" : "&")."IFRAME=Y";
								}
								$link .= "#message".$record["TO_VALUE"];
								?> <a href="javascript: void(0)" onClick="GoToComment('<?php echo $link?>', <?php echo $pageNumber == $commentsCurrPage ? "true" : "false"?>)">#<?php echo $record["TO_VALUE"]?></a><?php
							}
						?></span></td>
						<td class="task-log-what-column"><span class="task-log-what"><?php
							switch($record["FIELD"])
							{
								case "DURATION_PLAN_SECONDS":
									echo $funcFormatForHuman($record['FROM_VALUE']);;
									?><span class="task-log-arrow">&rarr;</span><?php
									echo $funcFormatForHuman($record['TO_VALUE']);;
								break;

								case "TITLE":
								case "DURATION_PLAN":
									echo $record["FROM_VALUE"];
										?><span class="task-log-arrow">&rarr;</span><?php
									echo $record["TO_VALUE"];
								break;

								case "DURATION_FACT":
									echo $funcFormatForHumanMinutes($record["FROM_VALUE"]);
										?><span class="task-log-arrow">&rarr;</span><?php
									echo $funcFormatForHumanMinutes($record["TO_VALUE"]);
								break;								

								case "RESPONSIBLE_ID":
									$rsUserFrom = CUser::GetByID($record["FROM_VALUE"]);
									if ($arUserFrom = $rsUserFrom->GetNext())
									{
										$anchor_id = RandString(8);
										$sUserFrom = '<script type="text/javascript">BX.tooltip('.$arUserFrom["ID"].', "anchor_log_'.$anchor_id.'", "");</script><a id="anchor_log_'.$anchor_id.'" class="task-log-author" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $arUserFrom["ID"])).'">'
											. tasksFormatNameShort(
												$arUserFrom["NAME"], 
												$arUserFrom["LAST_NAME"], 
												$arUserFrom["LOGIN"], 
												$arUserFrom["SECOND_NAME"], 
												$arParams["NAME_TEMPLATE"],
												false
												).'</a>';
									}
									$rsUserTo = CUser::GetByID($record["TO_VALUE"]);
									if ($arUserTo = $rsUserTo->GetNext())
									{
										$anchor_id = RandString(8);
										$sUserTo = '<script type="text/javascript">BX.tooltip('.$arUserTo["ID"].', "anchor_log_'.$anchor_id.'", "");</script><a id="anchor_log_'.$anchor_id.'" class="task-log-author" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $arUserTo["ID"])).'">'
										. tasksFormatNameShort(
											$arUserTo["NAME"], 
											$arUserTo["LAST_NAME"], 
											$arUserTo["LOGIN"], 
											$arUserTo["SECOND_NAME"], 
											$arParams["NAME_TEMPLATE"],
											false
											).'</a>';
									}
									?>
									<?php echo $sUserFrom?><span class="task-log-arrow">&rarr;</span><?php echo $sUserTo?>
									<?php
									break;
								case "DEADLINE":
								case "START_DATE_PLAN":
								case "END_DATE_PLAN":
									if ($record['FROM_VALUE'] > 0)
									{
										// Don't format time, if it's 00:00
										if (date("H:i", $record["FROM_VALUE"]) == "00:00")
											echo FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATE), $record['FROM_VALUE']);
										else
											echo FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATETIME), $record['FROM_VALUE']);
									}

									?><span class="task-log-arrow">&rarr;</span><?php

									if ($record['TO_VALUE'] > 0)
									{
										// Don't format time, if it's 00:00
										if (date("H:i", $record["TO_VALUE"]) == "00:00")
											echo FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATE), $record['TO_VALUE']);
										else
											echo FormatDate(CDatabase::DateFormatToPHP(FORMAT_DATETIME), $record['TO_VALUE']);
									}
									break;
								case "ACCOMPLICES":
								case "AUDITORS":
									$arUsersFromStr = array();
									if ($record["FROM_VALUE"])
									{
										$rsUsersFrom = CUser::GetList(($by="id"), ($order="asc"), array("ID" => str_replace(",", "|", $record["FROM_VALUE"])));
										while ($arUserFrom = $rsUsersFrom->GetNext())
										{
											$anchor_id = RandString(8);
											$arUsersFromStr[] = '<script type="text/javascript">BX.tooltip('.$arUserFrom["ID"].', "anchor_log_'.$anchor_id.'", "");</script><a id="anchor_log_'.$anchor_id.'" class="task-log-link" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $arUserFrom["ID"])).'">'
											. tasksFormatNameShort(
												$arUserFrom["NAME"], 
												$arUserFrom["LAST_NAME"], 
												$arUserFrom["LOGIN"], 
												$arUserFrom["SECOND_NAME"], 
												$arParams["NAME_TEMPLATE"],
												false
												).'</a>';
										}
									}

									$arUsersToStr = array();
									if ($record["TO_VALUE"])
									{
										$rsUsersTo = CUser::GetList(($by="id"), ($order="asc"), array("ID" => str_replace(",", "|", $record["TO_VALUE"])));
										while ($arUserTo = $rsUsersTo->GetNext())
										{
											$anchor_id = RandString(8);
											$arUsersToStr[] = '<script type="text/javascript">BX.tooltip('.$arUserTo["ID"].', "anchor_log_'.$anchor_id.'", "");</script><a id="anchor_log_'.$anchor_id.'" class="task-log-link" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $arUserTo["ID"])).'">'
											. tasksFormatNameShort(
												$arUserTo["NAME"], 
												$arUserTo["LAST_NAME"], 
												$arUserTo["LOGIN"], 
												$arUserTo["SECOND_NAME"], 
												$arParams["NAME_TEMPLATE"],
												false).'</a>';
										}
									}
									?>
									<?php echo implode(", ", $arUsersFromStr)?><span class="task-log-arrow">&rarr;</span><?php echo implode(", ", $arUsersToStr)?>
									<?php
									break;
								case "TAGS":
									?>
									<?php echo str_replace(",", ", ", $record["FROM_VALUE"])?><span class="task-log-arrow">&rarr;</span><?php echo str_replace(",", ", ", $record["TO_VALUE"])?>
									<?php
									break;
								case "PRIORITY":
									?>
									<?php echo GetMessage("TASKS_PRIORITY_".$record["FROM_VALUE"])?><span class="task-log-arrow">&rarr;</span><?php echo GetMessage("TASKS_PRIORITY_".$record["TO_VALUE"])?>
									<?php
									break;
								case "GROUP_ID":
									if ($record["FROM_VALUE"] && CSocNetGroup::CanUserViewGroup($USER->GetID(), $record["FROM_VALUE"]))
									{
										$arGroupFrom = CSocNetGroup::GetByID($record["FROM_VALUE"]);
										{
											if ($arGroupFrom)
											{
												?><a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroupFrom["ID"]))?>"><?php echo $arGroupFrom["NAME"]?></a><?php
											}
										}
									}
									?><span class="task-log-arrow">&rarr;</span><?php
									if ($record["TO_VALUE"] && CSocNetGroup::CanUserViewGroup($USER->GetID(), $record["TO_VALUE"]))
									{
										$arGroupTo = CSocNetGroup::GetByID($record["TO_VALUE"]);
										{
											if ($arGroupTo)
											{
												?><a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP"], array("group_id" => $arGroupTo["ID"]))?>"><?php echo $arGroupTo["NAME"]?></a><?php
											}
										}
									}
									break;
								case "PARENT_ID":
									if ($record["FROM_VALUE"])
									{
										$rsTaskFrom = CTasks::GetList(array(), array("ID" => $record["FROM_VALUE"]), array("ID", "TITLE"));
										{
											if ($arTaskFrom = $rsTaskFrom->GetNext())
											{
												?><a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $arTaskFrom["ID"], "action" => "view"))?>"><?php echo $arTaskFrom["TITLE"]?></a><?php
											}
										}
									}
									?><span class="task-log-arrow">&rarr;</span><?php
									if ($record["TO_VALUE"])
									{
										$rsTaskTo = CTasks::GetList(array(), array("ID" => $record["TO_VALUE"]), array("ID", "TITLE"));
										{
											if ($arTaskTo = $rsTaskTo->GetNext())
											{
												?><a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $arTaskTo["ID"], "action" => "view"))?>"><?php echo $arTaskTo["TITLE"]?></a><?php
											}
										}
									}
									break;
								case "DEPENDS_ON":
									$arTasksFromStr = array();
									if ($record["FROM_VALUE"])
									{
										$rsTasksFrom = CTasks::GetList(array(), array("ID" => explode(",", $record["FROM_VALUE"])), array("ID", "TITLE"));
										while ($arTaskFrom = $rsTasksFrom->GetNext())
										{
											$arTasksFromStr[] = '<a class="task-log-link" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $arTaskFrom["ID"], "action" => "view")).'">'.$arTaskFrom["TITLE"].'</a>';
										}
									}

									$arTasksToStr = array();
									if ($record["TO_VALUE"])
									{
										$rsTasksTo = CTasks::GetList(array(), array("ID" => explode(",", $record["TO_VALUE"])), array("ID", "TITLE"));
										while ($arTaskTo = $rsTasksTo->GetNext())
										{
											$arTasksToStr[] = '<a class="task-log-link" href="'.CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => $arTaskTo["ID"], "action" => "view")).'">'.$arTaskTo["TITLE"].'</a>';
										}
									}
									?>
									<?php echo implode(", ", $arTasksFromStr)?><span class="task-log-arrow">&rarr;</span><?php echo implode(", ", $arTasksToStr)?>
									<?php
									break;
								case "STATUS":
									?>
									<?php echo GetMessage("TASKS_STATUS_".$record["FROM_VALUE"])?><span class="task-log-arrow">&rarr;</span><?php echo GetMessage("TASKS_STATUS_".$record["TO_VALUE"])?>
									<?php
									break;
								case "MARK":
									?>
									<?php echo !$record["FROM_VALUE"] ? GetMessage("TASKS_MARK_NONE") : GetMessage("TASKS_MARK_".$record["FROM_VALUE"])?><span class="task-log-arrow">&rarr;</span><?php echo !$record["TO_VALUE"] ? GetMessage("TASKS_MARK_NONE") : GetMessage("TASKS_MARK_".$record["TO_VALUE"])?>
									<?php
									break;
								case "ADD_IN_REPORT":
									?>
									<?php echo $record["FROM_VALUE"] == "Y" ? GetMessage("TASKS_SIDEBAR_IN_REPORT_YES") : GetMessage("TASKS_SIDEBAR_IN_REPORT_NO")?><span class="task-log-arrow">&rarr;</span><?php echo $record["TO_VALUE"] == "Y" ? GetMessage("TASKS_SIDEBAR_IN_REPORT_YES") : GetMessage("TASKS_SIDEBAR_IN_REPORT_NO")?>
									<?php
									break;
								default:
									echo "&nbsp;";
									break;
							}
						?></span></td>
					</tr>
				<?php endforeach;?>
			</table>
			<?php endif?>
		</div>
		<script type="text/javascript">
			if (document.location.hash == "#updates")
			{
				(BX.proxy(ToggleSwitcher, BX("task-log-switcher", true)))();
			}
		</script>
		<div id="task-time-block" class="task-time-block">
			<form method="post" action="<?php echo POST_FORM_ACTION_URI?>" name="task-elapsed-time-form" id="task-elapsed-time-form">
				<?php echo bitrix_sessid_post()?>
				<input type="hidden" name="ELAPSED_ID" value="" />
				<input type="hidden" name="ACTION" value="elapsed_add" />
				<table id="task-time-table" class="task-time-table" cellspacing="0" cellpadding="0">
					<col class="task-time-date-column" />
					<col class="task-time-author-column" />
					<col class="task-time-spent-column" />
					<col class="task-time-comments-column" />
					<tr>
						<th class="task-time-date-column"><?php echo GetMessage("TASKS_ELAPSED_DATE")?></th>
						<th class="task-time-author-column"><?php echo GetMessage("TASKS_ELAPSED_AUTHOR")?></th>
						<th class="task-time-spent-column"><?php echo GetMessage("TASKS_ELAPSED_TIME_SHORT")?></th>
						<th class="task-time-comment-column"><?php echo GetMessage("TASKS_ELAPSED_COMMENT")?></th>
					</tr>
					<?php foreach($arResult["ELAPSED_TIME"] as $time):?>
						<tr id="elapsed-time-<?php echo $time["ID"]?>">
							<td class="task-time-date-column"><span class="task-time-date"><?php echo FormatDateFromDB($time["CREATED_DATE"]);?></span></td>
							<td class="task-time-author-column"><script type="text/javascript">BX.tooltip(<?php echo $time["USER_ID"]?>, "anchor_elapsed_<?php echo $anchor_id?>", "");</script><a id="anchor_elapsed_<?php echo $anchor_id?>" class="task-log-author" target="_top" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $time["USER_ID"]))?>"><?php 
								echo tasksFormatNameShort(
									$time["USER_NAME"], 
									$time["USER_LAST_NAME"], 
									$time["USER_LOGIN"], 
									$time["USER_SECOND_NAME"], 
									$arParams["NAME_TEMPLATE"],
									false)?></a></td>
							<td class="task-time-spent-column"><?php echo floor($time["MINUTES"] / 60)?><span><?php echo GetMessage("TASKS_ELAPSED_H")?></span><?php echo $time["MINUTES"] % 60?><span><?php echo GetMessage("TASKS_ELAPSED_M")?></span></td>
							<td class="task-time-comment-column">
								<div class="wrap-edit-nav">
									<span class="task-time-comment"><?php echo $time["COMMENT_TEXT"] ? $time["COMMENT_TEXT"] : "&nbsp;"?></span>
									<?php if ($time["USER_ID"] == $USER->GetID()):?>
										<span class="task-edit-nav">
											<a class="task-table-edit" onclick="EditElapsedTime(<?php echo $time["ID"]?>, <?php echo floor($time["MINUTES"] / 60)?>, <?php echo $time["MINUTES"] % 60?>, '<?php echo CUtil::JSEscape($time["COMMENT_TEXT"])?>')"></a>
											<a class="task-table-remove" href="<?php echo $APPLICATION->GetCurPageParam("ACTION=elapsed_delete&ELAPSED_ID=".$time["ID"]."&".bitrix_sessid_get(), array("sessid", "ACTION"));?>"></a>
										</span>
									<?php endif?>
								</div>
							</td>
						</tr>
					<?php endforeach?>
					<?php if (CTaskElapsedTime::CanCurrentUserAdd($arResult["TASK"])):?>
						<tr id="task-elapsed-time-button-row">
							<td class="task-time-date-column"><a class="task-add-new" id="task-add-elapsed-time"><span></span><?php echo GetMessage("TASKS_ELAPSED_ADD")?></a></td>
							<td class="task-time-author-column">&nbsp;</td>
							<td class="task-time-spent-column">&nbsp;</td>
							<td class="task-time-comment-column">
								<div class="wrap-edit-nav">&nbsp;</div>
							</td>
						</tr>
					<?php endif?>
						<tr id="task-elapsed-time-form-row" style="display: none;">
							<td class="task-time-date-column">&nbsp;</td>
							<td class="task-time-author-column">&nbsp;</td>
							<td class="task-time-spent-column"><input type="text" name="HOURS" value="1" /><span><?php echo GetMessage("TASKS_ELAPSED_H")?></span><input type="text" name="MINUTES" value="00" /><span><?php echo GetMessage("TASKS_ELAPSED_M")?></span></td>
							<td class="task-time-comment-column" id="task-time-comment-column"><div class="wrap-edit-nav"><input type="text" name="COMMENT_TEXT" value="" /><span class="task-edit-nav"><a class="task-table-edit-ok" id="task-send-elapsed-time"></a><a class="task-table-edit-remove" id="task-cancel-elapsed-time"></a></span></div></td>
						</tr>
				</table>
			</form>
		</div>
	</div>
	<script type="text/javascript">
		if (window.location.hash == "#elapsed")
		{
			(BX.proxy(ToggleSwitcher, BX("task-time-switcher")))();
		}
	</script>

</div>

<?php $this->SetViewTarget("sidebar_tools_1", 100);?>
<div class="sidebar-block task-detail-info">
	<b class="r2"></b><b class="r1"></b><b class="r0"></b>
	<div class="sidebar-block-inner">

		<div class="task-detail-info-users task-detail-info-responsible">
			<div class="task-detail-info-users-border"></div>
			<div class="task-detail-info-users-inner">
				<div class="task-detail-info-users-title"><span><?php echo GetMessage("TASKS_RESPONSIBLE")?></span><?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID()):?><a class="webform-field-action-link" id="task-detail-responsible-change" href=""><?php echo GetMessage("TASKS_SIDEBAR_CHANGE")?></a><?php endif?></div>
				<div class="task-detail-info-users-list">
					<div class="task-detail-info-user">
						<a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $arResult["TASK"]["RESPONSIBLE_ID"]))?>" class="task-detail-info-user-avatar"<?php if ($arResult["TASK"]["RESPONSIBLE_PHOTO"]):?> style="background: url('<?php echo $arResult["TASK"]["RESPONSIBLE_PHOTO"]?>') no-repeat center center; "<?php endif?>></a>
						<div class="task-detail-info-user-info">
							<div class="task-detail-info-user-name"><a href="<?php 
								echo CComponentEngine::MakePathFromTemplate(
									$arParams["PATH_TO_USER_PROFILE"], 
									array("user_id" => $arResult["TASK"]["RESPONSIBLE_ID"]))
									?>" target="_top"><?php 
								echo tasksFormatName(
									$arResult["TASK"]["RESPONSIBLE_NAME"], 
									$arResult["TASK"]["RESPONSIBLE_LAST_NAME"], 
									$arResult["TASK"]["RESPONSIBLE_LOGIN"], 
									$arResult["TASK"]["RESPONSIBLE_SECOND_NAME"], 
									$arParams["NAME_TEMPLATE"],
									false);
								?></a></div>
							<?php if ($arResult["TASK"]["RESPONSIBLE_WORK_POSITION"]):?><div class="task-detail-info-user-position"><?php 
							echo $arResult["TASK"]["RESPONSIBLE_WORK_POSITION"]?><?php else:?><div class="task-detail-info-user-position-empty"><?php endif?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="task-detail-info-users-border"></div>
		</div>
		<?php
			if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID())
			{
				$name = $APPLICATION->IncludeComponent(
					"bitrix:intranet.user.selector.new",
					".default",
					array(
						"MULTIPLE" => "N",
						"NAME" => "RESPONSIBLE",
						"VALUE" => $arResult["TASK"]["RESPONSIBLE_ID"],
						"POPUP" => "Y",
						"ON_SELECT" => "onResponsibleSelect",
						"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
						"SITE_ID" => SITE_ID,
						"GROUP_ID_FOR_SITE" => (isset($_GET["GROUP_ID"]) && intval($_GET["GROUP_ID"]) > 0 ? $_GET["GROUP_ID"] : (isset($arParams["GROUP_ID"]) && intval($arParams["GROUP_ID"]) > 0 ? $arParams["GROUP_ID"] : false)),
						'SHOW_EXTRANET_USERS' => 'FROM_MY_GROUPS',
						'DISPLAY_TAB_GROUP' => 'Y',
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			}
		?>

		<div class="task-detail-info-users task-detail-info-director">
			<div class="task-detail-info-users-border"></div>
			<div class="task-detail-info-users-inner">
				<div class="task-detail-info-users-title"><span><?php echo GetMessage("TASKS_CREATOR")?></span></div>
				<div class="task-detail-info-users-list">
					<div class="task-detail-info-user">
						<a href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER_PROFILE"], array("user_id" => $arResult["TASK"]["CREATED_BY"]))?>" class="task-detail-info-user-avatar"<?php if ($arResult["TASK"]["CREATED_BY_PHOTO"]):?> style="background: url('<?php echo $arResult["TASK"]["CREATED_BY_PHOTO"]?>') no-repeat center center; "<?php endif?>></a>
						<div class="task-detail-info-user-info">
							<div class="task-detail-info-user-name"><a href="<?php 
							echo CComponentEngine::MakePathFromTemplate(
								$arParams["PATH_TO_USER_PROFILE"], 
								array("user_id" => $arResult["TASK"]["CREATED_BY"]))?>" target="_top"><?php 
							echo tasksFormatName(
								$arResult["TASK"]["CREATED_BY_NAME"], 
								$arResult["TASK"]["CREATED_BY_LAST_NAME"], 
								$arResult["TASK"]["CREATED_BY_LOGIN"], 
								$arResult["TASK"]["CREATED_BY_SECOND_NAME"], 
								$arParams["NAME_TEMPLATE"],
								false);
								?></a></div>
							<?php if ($arResult["TASK"]["CREATED_BY_WORK_POSITION"]):?><div class="task-detail-info-user-position"><?php echo $arResult["TASK"]["CREATED_BY_WORK_POSITION"]?><?php else:?><div class="task-detail-info-user-position-empty"><?php endif?></div>
						</div>
					</div>
				</div>
			</div>
			<div class="task-detail-info-users-border"></div>
		</div>

		<table class="task-detail-info-layout" cellspacing="0">
			<tr>
				<td class="task-detail-info-layout-name"><?php echo GetMessage("TASKS_SIDEBAR_STATUS")?>:</td>
				<td class="task-detail-info-layout-value">
					<span class="task-detail-info-status task-detail-info-status-in-progress"><span class="task-detail-info-status-text"><?php echo GetMessage("TASKS_STATUS_".$arResult["TASK"]["REAL_STATUS"])?></span>
						<span class="task-detail-info-status-date">
							<?php if ($arResult["TASK"]["REAL_STATUS"] != 4 && $arResult["TASK"]["REAL_STATUS"] != 5):?>
								<?php echo GetMessage("TASKS_SIDEBAR_START_DATE")?>
							<?php endif?>
							<?php echo tasksFormatDate($arResult["TASK"]["STATUS_CHANGED_DATE"])?>
							<?php if(date("H:i", strtotime($arResult["TASK"]["STATUS_CHANGED_DATE"])) != "00:00"):?>
								<?php echo FormatDateFromDB($arResult["TASK"]["STATUS_CHANGED_DATE"], CSite::getTimeFormat())?>
							<?php endif?>
						</span>
					</span>
				</td>
			</tr>
			<tr>
				<td class="task-detail-info-layout-name"><?php echo GetMessage("TASKS_PRIORITY")?>:</td>
				<td class="task-detail-info-layout-value">
					<span class="task-detail-priority task-detail-priority-<?php echo $arResult["TASK"]["PRIORITY"]?><?php if($arResult["TASK"]["CREATED_BY"] != $USER->GetID()):?> task-detail-priority-readonly<?php endif?>" id="task-detail-priority"<?php if($arResult["TASK"]["CREATED_BY"] == $USER->GetID()):?> onclick="return ShowPriorityPopupDetail(<?php echo $arResult["TASK"]["ID"]?>, this, <?php echo $arResult["TASK"]["PRIORITY"]?>);"<?php endif?>><span class="task-detail-priority-icon"></span><span class="task-detail-priority-text"><?php echo GetMessage("TASKS_PRIORITY_".$arResult["TASK"]["PRIORITY"])?></span></span>
				</td>
			</tr>
			<?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID() || ($arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID() && $arResult["TASK"]["ALLOW_CHANGE_DEADLINE"] == "Y") || $arResult["TASK"]["DEADLINE"]):?>
			<tr>
				<td class="task-detail-info-layout-name" style=""><?php echo GetMessage("TASKS_QUICK_DEADLINE")?>:</td>
				<td class="task-detail-info-layout-value">
					<span class="<?php if ($arResult["TASK"]["DEADLINE"]):?>task-detail-deadline<?php endif?> <?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID() || ($arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID() && $arResult["TASK"]["ALLOW_CHANGE_DEADLINE"] == "Y")):?>webform-field-action-link" 
						onclick="
						BX.calendar({
							node: this, 
							field: 'task-deadline-hidden', 
							form: '', 
							bTime: true, 
							currentTime: Math.round((new Date()) / 1000) - (new Date()).getTimezoneOffset()*60, 
							bHideTimebar: false,
							callback_after: function(value) {
								tasks_funcOnChangeOfSomeDateFields(BX('task-deadline-hidden'));
							}
						});
						"<?php
						else:
							echo '"';
						endif;
						?> 
						id="task-detail-deadline" style="display:inline; line-height:19px;"><?php
					if ($arResult["TASK"]["DEADLINE"])
					{
						echo tasksFormatDate($arResult["TASK"]["DEADLINE"]);
						if(convertTimeToMilitary($arResult["TASK"]["DEADLINE"], CSite::GetDateFormat(), "HH:MI") != "00:00")
						{
							echo " ".convertTimeToMilitary($arResult["TASK"]["DEADLINE"], CSite::GetDateFormat(), CSite::GetTimeFormat());
						}
					}
					else
					{
						echo GetMessage("TASKS_SIDEBAR_DEADLINE_NO");
					}
					?></span><?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID() || ($arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID() && $arResult["TASK"]["ALLOW_CHANGE_DEADLINE"] == "Y")):?><input type="text" style="display:none;" id="task-deadline-hidden" value="<?php echo $arResult["TASK"]["DEADLINE"]?>" /><span class="task-deadline-delete"<?php if (!$arResult["TASK"]["DEADLINE"]):?>style="display: none;"<?php endif?> onclick="ClearDeadline(<?php echo $arResult["TASK"]["ID"]?>, this)"><?php endif?></td>
			</tr>
			<?php
			endif;

			$amPmFormatSymbol = 'a';
			if (strpos(FORMAT_DATETIME, 'T') !== false)
				$amPmFormatSymbol = 'A';

			if ($arResult["TASK"]["START_DATE_PLAN"]):?>
			<tr>
				<td class="task-detail-info-layout-name"><?php echo GetMessage("TASKS_SIDEBAR_START")?>:</td>
				<td class="task-detail-info-layout-value">
					<span class="task-detail-start-date">
						<?php
						echo tasksFormatDate($arResult["TASK"]["START_DATE_PLAN"]);
						if (IsAmPmMode()) :?>
							<?php if(date("g:i a", strtotime($arResult["TASK"]["START_DATE_PLAN"])) != "12:00 am"):?>
								<?php echo date("g:i " . $amPmFormatSymbol, strtotime($arResult["TASK"]["START_DATE_PLAN"]))?>
							<?php endif?>
						<?php else :?>
							<?php if(date("H:i", strtotime($arResult["TASK"]["START_DATE_PLAN"])) != "00:00"):?>
								<?php echo date("H:i", strtotime($arResult["TASK"]["START_DATE_PLAN"]))?>
							<?php endif?>
						<?php endif?>
					</span>
				</td>
			</tr>
			<?php endif?>
			<?php if ($arResult["TASK"]["END_DATE_PLAN"]):?>
			<tr>
				<td class="task-detail-info-layout-name"><?php echo GetMessage("TASKS_SIDEBAR_FINISH")?>:</td>
				<td class="task-detail-info-layout-value">
					<span class="task-detail-end-date">
						<?php echo tasksFormatDate($arResult["TASK"]["END_DATE_PLAN"])?>
						<?php if (IsAmPmMode()) :?>
							<?php if(date("g:i a", strtotime($arResult["TASK"]["END_DATE_PLAN"])) != "12:00 am"):?>
								<?php echo date("g:i " . $amPmFormatSymbol, strtotime($arResult["TASK"]["END_DATE_PLAN"]))?>
							<?php endif?>
						<?php else :?>
							<?php if(date("H:i", strtotime($arResult["TASK"]["END_DATE_PLAN"])) != "00:00"):?>
								<?php echo date("H:i", strtotime($arResult["TASK"]["END_DATE_PLAN"]))?>
							<?php endif?>
						<?php endif?>
					</span>
				</td>
			</tr>
			<?php endif?>
			<tr>
				<td class="task-detail-info-layout-name"><?php echo GetMessage("TASKS_MARK")?>:</td>
				<td class="task-detail-info-layout-value task-detail-grade-value"><span
					class="task-detail-grade<?php
						if($arResult["TASK"]["MARK"] == "P")
						{
							?> task-detail-grade-plus<?php
						}
						elseif($arResult["TASK"]["MARK"] == "N")
						{
							?> task-detail-grade-minus<?php
						}
						else
						{
							?> task-detail-grade-none<?php
						}

						if (
							(
								$arResult["TASK"]["CREATED_BY"] != $USER->GetID()
								&& $arResult["TASK"]["SUBORDINATE"] != "Y"
							)
							|| ($arResult["TASK"]["RESPONSIBLE_ID"] == $USER->GetID())
						)
						{
							?> task-detail-grade-readonly<?php
						}
						?>"
					id="task-detail-grade"
					<?php

					if (
						(
							($arResult["TASK"]["CREATED_BY"] == $USER->GetID())
							|| ($arResult["TASK"]["SUBORDINATE"] == "Y")
						) 
						&& ($arResult["TASK"]["RESPONSIBLE_ID"] != $USER->GetID())
					)
					{
						?> onclick="return ShowGradePopupDetail(
							<?php echo $arResult["TASK"]["ID"]?>,
							this,
							{
								listValue : '<?php
									if ($arResult["TASK"]["MARK"] == "N" || $arResult["TASK"]["MARK"] == "P")
										echo $arResult["TASK"]["MARK"];
									else
										echo "NULL";
								?>'
							}
						);"<?php
					}
					?>
					><span class="task-detail-grade-icon"></span
						><span class="task-detail-grade-text"><?php
							if ($arResult["TASK"]["MARK"])
								echo GetMessage("TASKS_MARK_".$arResult["TASK"]["MARK"]);
							else
								echo GetMessage("TASKS_MARK_NONE");
						?></span
					></span
				></td>
			</tr>
			<tr>
				<td class="task-detail-info-layout-name"><?php echo GetMessage("TASKS_SIDEBAR_IN_REPORT")?>:</td>
				<td class="task-detail-info-layout-value"><span class="task-detail-report"><?php
				if ($arResult["TASK"]["SUBORDINATE"] == "Y" && $arResult["TASK"]["RESPONSIBLE_ID"] != $USER->GetID()):?><a class="webform-field-action-link<?php if($arResult["TASK"]["ADD_IN_REPORT"] == "Y"):?> selected<?php endif?> task-detail-report-yes" id="task-detail-report-yes" onclick="SetReport(<?php echo $arResult["TASK"]["ID"]?>, true)" href="javascript: void(0);"><?php echo GetMessage("TASKS_SIDEBAR_IN_REPORT_YES")?></a><a class="webform-field-action-link<?php if($arResult["TASK"]["ADD_IN_REPORT"] != "Y"):?> selected<?php endif?> task-detail-report-no" id="task-detail-report-no" onclick="SetReport(<?php echo $arResult["TASK"]["ID"]?>, false)" href="javascript: void(0);"><?php echo GetMessage("TASKS_SIDEBAR_IN_REPORT_NO")?></a></span><?php
				else:?><?php if($arResult["TASK"]["ADD_IN_REPORT"] == "Y"):?><?php echo GetMessage("TASKS_SIDEBAR_IN_REPORT_YES")?><?php else:?><?php echo GetMessage("TASKS_SIDEBAR_IN_REPORT_NO")?><?php endif?><?php
				endif?></td>
			</tr>
			<?php

			$arTemplate = false;

			if (isset($arResult['TASK']['FORKED_BY_TEMPLATE']))
				$arTemplate = $arResult['TASK']['FORKED_BY_TEMPLATE'];
			elseif (isset($arResult["TASK"]["TEMPLATE"]))
				$arTemplate = $arResult['TASK']['TEMPLATE'];

			if ($arTemplate || isset($arResult['TASK']['FORKED_BY_TEMPLATE_ID']))
			{
				?>
				<tr>
					<td class="task-detail-info-layout-name"><?php
						echo GetMessage("TASKS_SIDEBAR_REPEAT") . ':';
					?></td>
					<td class="task-detail-info-layout-value"><span class="task-detail-periodicity"><?php
						if ($arTemplate)
						{
							echo tasksPeriodToStr($arTemplate["REPLICATE_PARAMS"]);
							?> (<a href="<?php
								echo CComponentEngine::MakePathFromTemplate(
									$arParams["PATH_TO_TEMPLATES_TEMPLATE"],
									array("template_id" => $arTemplate["ID"], "action" => "edit")
								);
							?>" class="task-detail-periodicity-link" target="_top"><?php
								echo GetMessage("TASKS_SIDEBAR_TEMPLATE");
							?></a>)<?php
						}
						else
							echo GetMessage('TASKS_SIDEBAR_TEMPLATE_NOT_EXISTS');
					?></span></td>
				</tr>
				<?php
			}
			?>
		</table>

		<div class="task-detail-info-users<?php if (!sizeof($arResult["TASK"]["ACCOMPLICES"])):?> task-detail-info-users-empty<?php endif?> task-detail-info-assistants" id="task-detail-info-assistants">
			<div class="task-detail-info-users-border"></div>
			<div class="task-detail-info-users-inner">
				<div class="task-detail-info-users-title"><span><?php echo GetMessage("TASKS_SIDEBAR_ACCOMPLICES")?></span><?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID()):?><a class="webform-field-action-link" id="task-detail-info-assistants-change" href=""><?php echo GetMessage("TASKS_SIDEBAR_CHANGE")?></a><?php endif?></div>
				<div class="task-detail-info-users-list" id="task-detail-assistants">
					<?php
						if ($arResult["TASK"]["ACCOMPLICES"]):
							$rsAccomplices = CUser::GetList(($b = "LOGIN"), ($o = "ASC"), array("ID" => implode("|", $arResult["TASK"]["ACCOMPLICES"])));
							while($arAccomplice = $rsAccomplices->GetNext()):
					?>
					<div class="task-detail-info-user">
						<div class="task-detail-info-user-name"><a href="<?php 
							echo CComponentEngine::MakePathFromTemplate(
								$arParams["PATH_TO_USER_PROFILE"], 
								array("user_id" => $arAccomplice["ID"]))?>"><?php 
							echo tasksFormatName(
								$arAccomplice["NAME"], 
								$arAccomplice["LAST_NAME"], 
								$arAccomplice["LOGIN"], 
								$arAccomplice["SECOND_NAME"], 
								$arParams["NAME_TEMPLATE"],
								false
								)?></a></div>
						<?php if ($arAccomplice["WORK_POSITION"]):?><div class="task-detail-info-user-position"><?php echo $arAccomplice["WORK_POSITION"]?><?php else:?><div class="task-detail-info-user-position-empty"><?php endif?></div>
					</div>
					<?php endwhile?>
					<?php endif?>
				</div>
			</div>
			<div class="task-detail-info-users-border"></div>
		</div>
		<?php
			if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID())
			{
				$name = $APPLICATION->IncludeComponent(
					"bitrix:intranet.user.selector.new",
					".default",
					array(
						"MULTIPLE" => "Y",
						"NAME" => "ACCOMPLICES",
						"VALUE" => $arResult["TASK"]["ACCOMPLICES"],
						"POPUP" => "Y",
						"ON_CHANGE" => "onAccomplicesChange",
						"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
						"SITE_ID" => SITE_ID,
						"GROUP_ID_FOR_SITE" => (isset($_GET["GROUP_ID"]) && intval($_GET["GROUP_ID"]) > 0 ? $_GET["GROUP_ID"] : (isset($arParams["GROUP_ID"]) && intval($arParams["GROUP_ID"]) > 0 ? $arParams["GROUP_ID"] : false)),
						'SHOW_EXTRANET_USERS' => 'FROM_MY_GROUPS',
						'DISPLAY_TAB_GROUP' => 'Y',
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			}
		?>

		<div class="task-detail-info-users<?php if (!sizeof($arResult["TASK"]["AUDITORS"])):?> task-detail-info-users-empty<?php endif?> task-detail-info-auditors" id="task-detail-info-auditors">
			<div class="task-detail-info-users-border"></div>
			<div class="task-detail-info-users-inner">
				<div class="task-detail-info-users-title"><span><?php echo GetMessage("TASKS_SIDEBAR_AUDITORS")?></span><?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID()):?><a class="webform-field-action-link" id="task-detail-info-auditors-change" href=""><?php echo GetMessage("TASKS_SIDEBAR_CHANGE")?></a><?php endif?></div>
				<div class="task-detail-info-users-list" id="task-detail-auditors">
					<?php
						if ($arResult["TASK"]["AUDITORS"]):
							$rsAuditors = CUser::GetList(($b = "LOGIN"), ($o = "ASC"), array("ID" => implode("|", $arResult["TASK"]["AUDITORS"])));
							while($arAuditor = $rsAuditors->GetNext()):
					?>
					<div class="task-detail-info-user">
						<div class="task-detail-info-user-name"><a href="<?php 
							echo CComponentEngine::MakePathFromTemplate(
								$arParams["PATH_TO_USER_PROFILE"], 
								array("user_id" => $arAuditor["ID"]))?>"><?php 
							echo tasksFormatName(
								$arAuditor["NAME"], 
								$arAuditor["LAST_NAME"], 
								$arAuditor["LOGIN"], 
								$arAuditor["SECOND_NAME"], 
								$arParams["NAME_TEMPLATE"],
								false
								)?></a></div>
						<?php if ($arAuditor["WORK_POSITION"]):?><div class="task-detail-info-user-position"><?php echo $arAuditor["WORK_POSITION"]?><?php else:?><div class="task-detail-info-user-position-empty"><?php endif?></div>
					</div>
					<?php endwhile?>
					<?php endif?>
				</div>
			</div>
		</div>
		<?php
			if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID())
			{
				$name = $APPLICATION->IncludeComponent(
					"bitrix:intranet.user.selector.new",
					".default",
					array(
						"MULTIPLE" => "Y",
						"NAME" => "AUDITORS",
						"VALUE" => $arResult["TASK"]["AUDITORS"],
						"POPUP" => "Y",
						"ON_CHANGE" => "onAuditorsChange",
						"PATH_TO_USER_PROFILE" => $arParams["PATH_TO_USER_PROFILE"],
						"SITE_ID" => SITE_ID,
						"GROUP_ID_FOR_SITE" => (isset($_GET["GROUP_ID"]) && intval($_GET["GROUP_ID"]) > 0 ? $_GET["GROUP_ID"] : (isset($arParams["GROUP_ID"]) && intval($arParams["GROUP_ID"]) > 0 ? $arParams["GROUP_ID"] : false)),
						'SHOW_EXTRANET_USERS' => 'FROM_MY_GROUPS',
						'DISPLAY_TAB_GROUP' => 'Y',
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
					),
					null,
					array("HIDE_ICONS" => "Y")
				);
			}
		?>

		<?php if ($arResult["TASK"]["CREATED_BY"] == $USER->GetID()):?>
			<div class="task-detail-info-users-links">
				<div class="task-detail-info-users-link"<?php if (sizeof($arResult["TASK"]["ACCOMPLICES"])):?> style="display:none;"<?php endif?>><a class="webform-field-action-link" id="task-detail-info-assistants-add" href=""><?php echo GetMessage("TASKS_SIDEBAR_ADD_ACCOMPLICES")?></a></div>
				<div class="task-detail-info-users-link"<?php if (sizeof($arResult["TASK"]["AUDITORS"])):?> style="display:none;"<?php endif?>><a class="webform-field-action-link" id="task-detail-info-auditors-add" href=""><?php echo GetMessage("TASKS_SIDEBAR_ADD_AUDITORS")?></a></div>
			</div>
		<?php endif?>

	</div>
	<i class="r0"></i><i class="r1"></i><i class="r2"></i>
</div>
<?php $this->EndViewTarget();

$quickBtnOnclick = 'ToggleQuickTask(BX(\'subtask-list-table\')';

if ($arResult["TASK"]["GROUP_ID"])
{
	$quickBtnOnclick .= ', {group: {id: ' . (int) $arResult["TASK"]["GROUP_ID"] 
		. ', title: \'' . CUtil::JSEscape($arResult["TASK"]["GROUP_NAME"]) . '\'}}';
}

$quickBtnOnclick .= ')';

ob_start();
?>
<div class="task-popup-templates" id="task-popup-templates-popup-content" style="display:none;">
	<div class="task-popup-templates-title"><?php echo GetMessage("TASKS_ADD_TEMPLATE_SUBTASK")?></div>
	<div class="popup-window-hr"><i></i></div>
	<?php if (sizeof($arResult["TEMPLATES"]) > 0):?>
		<ol class="task-popup-templates-items">
			<?php $commonUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit"))?>
			<?php foreach($arResult["TEMPLATES"] as $template):?>
			<?php $createUrl = $commonUrl.(strpos($commonUrl, "?") === false ? "?" : "&")."TEMPLATE=".$template["ID"]."&PARENT_ID=".$arResult["TASK"]["ID"];?>
			<li class="task-popup-templates-item"><a class="task-popup-templates-item-link" href="<?php echo $createUrl?>" onclick="AddPopupTemplateSubtask(<?php echo $template["ID"]?>, <?php echo $arResult["TASK"]["ID"]?>, event)"><?php echo $template["TITLE"]?></a></li>
			<?php endforeach?>
		</ol>
	<?php else:?>
		<div class="task-popup-templates-empty"><?php echo GetMessage("TASKS_NO_TEMPLATES")?></div>
	<?php endif?>
	<div class="popup-window-hr"><i></i></div>
	<a class="task-popup-templates-item task-popup-templates-item-all" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TEMPLATES"], array())?>"><?php echo GetMessage("TASKS_TEMPLATES_LIST")?></a>
</div>
<?php
$templatesPopupHtml = ob_get_clean();

$arComponentParams = array(
	'SHOW_TASK_LIST_MODES'   => 'N',
	'SHOW_HELP_ICON'         => 'N',
	'SHOW_SEARCH_FIELD'      => 'N',
	'SHOW_TEMPLATES_TOOLBAR' => 'N',
	'SHOW_QUICK_TASK_ADD'    => 'N',
	'SHOW_ADD_TASK_BUTTON'   => 'N',
	'SHOW_FILTER_BUTTON'     => 'N',
	'CUSTOM_ELEMENTS' => array(
		'ADD_BUTTON' => array(
			'name'            =>  GetMessage('TASKS_ADD_TASK'),
			'onclick'         => 'ShowNewTaskMenu(this, ' . (int) $arResult["TASK"]["ID"] . ', createMenu)',
			'url'             =>  null,
			'separator_after' => 'Y'
		),
		'QUICK_BUTTON' => array(
			'id'              => 'task-new-item-icon',
			'title'           =>  GetMessage('TASKS_ADD_QUICK_SUBTASK'),
			'onclick'         =>  $quickBtnOnclick,
			'url'             => 'javascript:void(0);'
		),
		'TEMPLATES_TOOLBAR' => array(
			'title'           =>  GetMessage('TASKS_ADD_TEMPLATE_SUBTASK'),
			'onclick'         => 'return ShowTemplatesPopup(this)',
			'url'             => '',
			'html_after'      => $templatesPopupHtml,
			'separator_after' => 'Y'
		),
		'BACK_BUTTON' => array(
			'name'    =>  GetMessage('TASKS_ADD_BACK_TO_TASKS_LIST'),
			'onclick' =>  null,
			'url'     =>  CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS"], array())
		)
	)
);

$APPLICATION->IncludeComponent(
	'bitrix:tasks.list.controls',
	((defined('SITE_TEMPLATE_ID') && (SITE_TEMPLATE_ID === 'bitrix24')) ? 'default_b24' : '.default'),
	$arComponentParams,
	null,
	array('HIDE_ICONS' => 'Y')
);
