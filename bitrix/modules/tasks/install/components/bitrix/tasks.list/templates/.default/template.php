<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

CUtil::InitJSCore(array('popup', 'tooltip'));

$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/tasks.list/templates/.default/script.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/components/bitrix/tasks.list/templates/.default/table-view.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/tasks/task-popups.js");
$GLOBALS['APPLICATION']->AddHeadScript("/bitrix/js/tasks/task-iframe-popup.js");

$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/intranet/intranet-common.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/main/core/css/core_popup.css");
$GLOBALS['APPLICATION']->SetAdditionalCSS("/bitrix/js/tasks/css/tasks.css");

$GLOBALS['APPLICATION']->SetPageProperty("BodyClass", "page-one-column");

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

$APPLICATION->IncludeComponent(
	"bitrix:tasks.iframe.popup",
	".default",
	array(
		"ON_TASK_ADDED" => "onPopupTaskAdded",
		'ON_TASK_ADDED_MULTIPLE' => 'onPopupTaskAdded',
		"ON_TASK_CHANGED" => "onPopupTaskChanged",
		"ON_TASK_DELETED" => "onPopupTaskDeleted",
		'PATH_TO_USER_TASKS_TASK'  => $arParams['PATH_TO_USER_TASKS_TASK']
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

if ($arResult["USER"])
{
	$userName 		= $arResult["USER"]["NAME"];
	$userLastName 	= $arResult["USER"]["LAST_NAME"];
	$userSecondName = $arResult["USER"]["SECOND_NAME"];
	$userLogin 		= $arResult["USER"]["LOGIN"];
}
else
{
	$userName 		= $USER->GetFirstName();
	$userLastName	= $USER->GetLastName();
	$userSecondName	= $USER->GetSecondName();
	$userLogin		= $USER->GetLogin();
}

?>
<script type="text/javascript">
BX.message({
	TASKS_PRIORITY : '<?php echo GetMessageJS('TASKS_PRIORITY')?>',
	TASKS_APPLY : '<?php echo GetMessageJS('TASKS_APPLY')?>',
	TASKS_ADD_IN_REPORT : '<?php echo GetMessageJS('TASKS_ADD_IN_REPORT')?>',
	TASKS_MARK : '<?php echo GetMessageJS('TASKS_MARK')?>',
	TASKS_PRIORITY_LOW : '<?php echo GetMessageJS('TASKS_PRIORITY_0')?>',
	TASKS_PRIORITY_MIDDLE : '<?php echo GetMessageJS('TASKS_PRIORITY_1')?>',
	TASKS_PRIORITY_HIGH : '<?php echo GetMessageJS('TASKS_PRIORITY_2')?>',
	TASKS_MARK_NONE : '<?php echo GetMessageJS('TASKS_MARK_NONE')?>',
	TASKS_MARK_POSITIVE : '<?php echo GetMessageJS('TASKS_MARK_P')?>',
	TASKS_MARK_NEGATIVE : '<?php echo GetMessageJS('TASKS_MARK_N')?>',
	TASKS_DURATION : '<?php echo GetMessageJS('TASKS_DURATION')?>',
	TASKS_OK : '<?php echo GetMessageJS('TASKS_OK')?>',
	TASKS_CANCEL : '<?php echo GetMessageJS('TASKS_CANCEL')?>',
	TASKS_DECLINE : '<?php echo GetMessageJS('TASKS_DECLINE_TASK')?>',
	TASKS_DECLINE_REASON : '<?php echo GetMessageJS('TASKS_DECLINE_REASON')?>',
	TASKS_NO_TITLE : '<?php echo GetMessageJS('TASKS_NO_TITLE')?>',
	TASKS_NO_RESPONSIBLE : '<?php echo GetMessageJS('TASKS_NO_RESPONSIBLE')?>',
	TASKS_PATH_TO_USER_PROFILE : '<?php echo CUtil::JSEscape($arParams['PATH_TO_USER_PROFILE'])?>',
	TASKS_PATH_TO_TASK : '<?php echo CUtil::JSEscape($arParams['PATH_TO_TASKS_TASK'])?>',
	PATH_TO_GROUP_TASKS : '<?php echo CUtil::JSEscape($arParams['PATH_TO_GROUP_TASKS'])?>',
	TASKS_DOUBLE_CLICK : '<?php echo GetMessageJS('TASKS_DOUBLE_CLICK')?>',
	TASKS_MENU : '<?php echo GetMessageJS('TASKS_MENU')?>',
	TASKS_FINISH : '<?php echo GetMessageJS('TASKS_FINISH')?>',
	TASKS_FINISHED : '<?php echo GetMessageJS('TASKS_FINISHED')?>',
	TASKS_QUICK_IN_GROUP : '<?php echo GetMessageJS('TASKS_QUICK_IN_GROUP')?>',
	TASKS_TASK_TITLE_LABEL : '<?php echo GetMessageJS('TASKS_TASK_TITLE_LABEL')?>',
	TASKS_RESPONSIBLE : '<?php echo GetMessageJS('TASKS_RESPONSIBLE')?>',
	TASKS_DIRECTOR : '<?php echo GetMessageJS('TASKS_CREATOR')?>',
	TASKS_DATE_CREATED : '<?php echo GetMessageJS('TASKS_FILTER_CREAT_DATE')?>',
	TASKS_DATE_DEADLINE : '<?php echo GetMessageJS('TASKS_QUICK_DEADLINE')?>',
	TASKS_DATE_START : '<?php echo GetMessageJS('TASKS_DATE_START')?>',
	TASKS_DATE_END : '<?php echo GetMessageJS('TASKS_DATE_END')?>',
	TASKS_DATE_STARTED : '<?php echo GetMessageJS('TASKS_DATE_STARTED')?>',
	TASKS_DATE_COMPLETED : '<?php echo GetMessageJS('TASKS_DATE_COMPLETED')?>',
	TASKS_STATUS : '<?php echo GetMessageJS('TASKS_STATUS')?>',
	TASKS_STATUS_IN_PROGRESS : '<?php echo GetMessageJS('TASKS_STATUS_IN_PROGRESS')?>',
	TASKS_STATUS_ACCEPTED : '<?php echo GetMessageJS('TASKS_STATUS_ACCEPTED')?>',
	TASKS_STATUS_COMPLETED : '<?php echo GetMessageJS('TASKS_STATUS_COMPLETED')?>',
	TASKS_STATUS_DELAYED : '<?php echo GetMessageJS('TASKS_STATUS_DELAYED')?>',
	TASKS_STATUS_NEW : '<?php echo GetMessageJS('TASKS_STATUS_NEW')?>',
	TASKS_STATUS_OVERDUE : '<?php echo GetMessageJS('TASKS_STATUS_OVERDUE')?>',
	TASKS_STATUS_WAITING : '<?php echo GetMessageJS('TASKS_STATUS_WAITING')?>',
	TASKS_STATUS_DECLINED : '<?php echo GetMessageJS('TASKS_STATUS_DECLINED')?>',
	TASKS_PRIORITY_0 : '<?php echo GetMessageJS('TASKS_PRIORITY_0')?>',
	TASKS_PRIORITY_1 : '<?php echo GetMessageJS('TASKS_PRIORITY_1')?>',
	TASKS_PRIORITY_2 : '<?php echo GetMessageJS('TASKS_PRIORITY_2')?>',
	TASKS_QUICK_INFO_DETAILS : '<?php echo GetMessageJS('TASKS_QUICK_INFO_DETAILS')?>',
	TASKS_QUICK_INFO_EMPTY_DATE : '<?php echo GetMessageJS('TASKS_QUICK_INFO_EMPTY_DATE')?>',
	TASKS_ADD_TASK : '<?php echo GetMessageJS('TASKS_ADD_TASK')?>',
	TASKS_FILES: '<?php echo GetMessageJS('TASKS_TASK_FILES')?>',
	TASKS_LEGEND_TITLE_1: '<?php echo GetMessageJS('TASKS_LEGEND_TITLE_1')?>',
	TASKS_LEGEND_CONTENT_1: '<?php echo GetMessageJS('TASKS_LEGEND_CONTENT_1')?>',
	TASKS_LEGEND_TITLE_2: '<?php echo GetMessageJS('TASKS_LEGEND_TITLE_2')?>',
	TASKS_LEGEND_CONTENT_2: '<?php echo GetMessageJS('TASKS_LEGEND_CONTENT_2')?>',
	TASKS_LEGEND_TITLE_3: '<?php echo GetMessageJS('TASKS_LEGEND_TITLE_3')?>',
	TASKS_LEGEND_CONTENT_3: '<?php echo GetMessageJS('TASKS_LEGEND_CONTENT_3')?>',
	TASKS_LEGEND_TITLE_4: '<?php echo GetMessageJS('TASKS_LEGEND_TITLE_4')?>',
	TASKS_LEGEND_CONTENT_4: '<?php echo GetMessageJS('TASKS_LEGEND_CONTENT_4')?>',
	TASKS_LEGEND_TITLE_5: '<?php echo GetMessageJS('TASKS_LEGEND_TITLE_5')?>',
	TASKS_LEGEND_CONTENT_5: '<?php echo GetMessageJS('TASKS_LEGEND_CONTENT_5')?>',
	TASKS_LEGEND_PREV: '<?php echo GetMessageJS('TASKS_LEGEND_PREV')?>',
	TASKS_LEGEND_NEXT: '<?php echo GetMessageJS('TASKS_LEGEND_NEXT')?>',
	TASKS_LEGEND_CLASSNAME: '<?php echo GetMessageJS('TASKS_LEGEND_CLASSNAME')?>',
	TASKS_START: '<?php echo GetMessageJS('TASKS_START')?>',
	TASKS_WAINTING_CONFIRM: '<?php echo GetMessageJS('TASKS_WAINTING_CONFIRM')?>',
	TASKS_MULTITASK: '<?php echo GetMessageJS('TASKS_WAINTING_CONFIRM')?>'
});


var arFilter = <?php echo CUtil::PhpToJSObject($arResult["FILTER"])?>;
var arOrder = <?php echo CUtil::PhpToJSObject($arResult["ORDER"])?>;
var ajaxUrl = "/bitrix/components/bitrix/tasks.list/ajax.php?SITE_ID=<?php echo SITE_ID?><?php echo $arResult["TASK_TYPE"] == "group" ? "&GROUP_ID=".$arParams["GROUP_ID"] : ""?>&nt=<?php echo urlencode($arParams['NAME_TEMPLATE']); ?>";

var currentUser = <?php echo $USER->GetID()?>;
var tasksIFrameList = <?php echo CUtil::PhpToJSObject(array_keys($arResult["TASKS"]))?>;

<?php /*
BX.ready(function() {
	<?php if (!CUserOptions::GetOption("tasks", "legend_shown", false) && $arParams["HIDE_VIEWS"] != "Y"):?>
		<?php CUserOptions::SetOption("tasks", "legend_shown", true)?>
		ShowLegendPopup(BX("task-title-button-legend"));
	<?php endif?>
});
 */ ?>
</script>

<div id="task-list-container" class="task-list">
	<?php
		if ($arParams['GROUP_ID'])
		{
			?>
			<input type="hidden"
				id="task-current-project"
				value="<?php echo (int) $arParams['GROUP_ID']; ?>"
				>
			<?php
		}
	?>
	
	<div class="task-list-left-corner"></div>
	<div class="task-list-right-corner"></div>
	<table class="task-list-table" cellspacing="0" id="task-list-table">

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
				<th class="task-title-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "TITLE"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>"  colspan="4" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=TITLE&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "TITLE" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell">
						<span class="task-head-cell-sort-order"></span>
						<span class="task-head-cell-title"><?php echo GetMessage("TASKS_TITLE")?></span>
						<span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="javascript: void(0)" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("", array("SORTF", "SORTD"));?>', event)" title="<?php echo GetMessage("TASKS_DEFAULT_SORT")?>"><i class="task-head-cell-sort-clear-icon"></i></a></span></div>
				</th>
				<th class="task-deadline-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "DEADLINE"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=DEADLINE&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "DEADLINE" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_DEADLINE")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="javascript: void(0)" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("", array("SORTF", "SORTD"));?>', event)" title="<?php echo GetMessage("TASKS_DEFAULT_SORT")?>"><i class="task-head-cell-sort-clear-icon"></i></a></span></span></div></th>
				<th class="task-responsible-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "RESPONSIBLE_ID"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=RESPONSIBLE_ID&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "RESPONSIBLE_ID" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_RESPONSIBLE")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="javascript: void(0)" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("", array("SORTF", "SORTD"));?>', event)" title="<?php echo GetMessage("TASKS_DEFAULT_SORT")?>"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>
				<th  class="task-director-column<?php if(is_array($arResult["ORDER"]) && key($arResult["ORDER"]) == "CREATED_BY"):?> task-column-selected task-column-order-by-<?php echo (current($arResult["ORDER"]) == "ASC" ? "asc" : "desc")?><?php endif?>"  onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("SORTF=CREATED_BY&SORTD=".(current($arResult["ORDER"]) == "ASC" && key($arResult["ORDER"]) == "CREATED_BY" ? "DESC" : "ASC"), array("SORTF", "SORTD"));?>', event)">
					<div class="task-head-cell"><span class="task-head-cell-sort-order"></span><span class="task-head-cell-title"><?php echo GetMessage("TASKS_CREATOR")?></span><span class="task-head-cell-clear-underlay"><a class="task-head-cell-sort-clear" href="javascript: void(0)" onclick="SortTable('<?php echo $APPLICATION->GetCurPageParam("", array("SORTF", "SORTD"));?>', event)" title="<?php echo GetMessage("TASKS_DEFAULT_SORT")?>"><i class="task-head-cell-sort-clear-icon"></i></a></span></div></th>

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
								<td class="task-new-item-title">
									<label for="task-new-item-name"><?php echo GetMessage("TASKS_QUICK_TITLE")?></label>
									<input type="text" id="task-new-item-name" class="task-new-item-textbox" />
									<div class="task-new-item-description">
										<span class="task-new-item-link" id="task-new-item-description-link">
											<?php echo GetMessage("TASKS_QUICK_DESCRIPTION")?>
										</span>
										<div class="task-description-textarea" id="task-quick-description-textarea">
											<!-- <div class="webform-field-textarea-inner"> -->
												<textarea id="task-new-item-description"></textarea>
											<!-- </div> -->
										</div>
									</div>
									<div class="task-new-item-buttons">
										<span class="task-new-item-buttons-wrap">
											<input type="submit" id="task-new-item-submit" value="<?php echo GetMessage("TASKS_QUICK_SAVE")?>" />&nbsp;
											<input type="button" id="task-new-item-cancel" value="<?php echo GetMessage("TASKS_QUICK_CANCEL")?>" onClick="HideQuickTask()" />
										</span>
										<span class="task-new-item-link" id="task-new-item-link-group">
											<?php echo GetMessage("TASKS_QUICK_IN_GROUP")?>
										</span>
									</div>
								</td>
								<td class="task-new-item-priority"><a href="javascript: void(0)" class="task-priority-box" onclick="return ShowPriorityPopup(0, this, 0);" title="<?php echo GetMessage("TASKS_PRIORITY")?>: <?php echo GetMessage("TASKS_PRIORITY_0")?>"><i id="task-new-item-priority" class="task-priority-icon task-priority-0"></i></a></td>
								<td class="task-new-item-deadline">
									<label for="task-new-item-deadline"><?php echo GetMessage("TASKS_QUICK_DEADLINE")?></label>
									<input type="text" id="task-new-item-deadline" name="DEADLINE" class="task-new-item-textbox" 
										onClick="
										<?php /* jsCal endar. Show(this, this.name, this.name, '', true, Math.round((new Date()) / 1000) - (new Date()).getTimezoneOffset()*60, '', false); */ ?>
										var curDate = new Date();

										curDayEveningTime = new Date(
											curDate.getFullYear(),
											curDate.getMonth(),
											curDate.getDate(),
											19, 0, 0
										);

										BX.calendar({
											node: this, 
											field: this.name, 
											form: '', 
											bTime: true, 
											value: curDayEveningTime,
											bHideTimebar: false,
											callback: function () {
												var fId = BX('task-new-item-deadline');
												BX.removeClass(fId.parentNode.parentNode, 'webform-field-textbox-empty');
											}
										});
										" />
								</td>
								<td class="task-new-item-responsible"><label 
									for="task-new-item-responsible"><?php 
										echo GetMessage("TASKS_RESPONSIBLE");
									?></label><input 
									type="text" 
									id="task-new-item-responsible" 
									class="task-new-item-textbox" 
									value="<?php 
										echo tasksFormatName(
											$userName,
											$userLastName,
											$userLogin,
											$userSecondName,
											$arParams["NAME_TEMPLATE"]
										);
									?>" 
									/><input type="hidden" name="task-new-item-responsible-hidden" 
										id="task-new-item-responsible-hidden" 
										value="<?php echo $arParams["USER_ID"]?>" 
								/></td>
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
									"GROUP_ID_FOR_SITE" => (intval($_GET["GROUP_ID"]) > 0 ? $_GET["GROUP_ID"] : (intval($arParams["GROUP_ID"]) > 0 ? $arParams["GROUP_ID"] : false)),
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
			<?php if (sizeof($arResult["TASKS"]) > 0):?>
				<?php $currentProject = false?>
				<?php foreach($arResult["TASKS"] as $key=>$task):?>
					<?php if ($arResult["TASK_TYPE"] != "group" && $task["GROUP_ID"] && $task["GROUP_ID"] != $currentProject):?>
						<?php
							$currentProject = $task["GROUP_ID"];
							$task["GROUP_NAME"] = $arResult["GROUPS"][$task["GROUP_ID"]]["NAME"];
						?>
						<tr class="task-list-item" id="task-project-<?php echo $task["GROUP_ID"]?>">
							<td class="task-project-column" colspan="9">
								<div class="task-project-column-inner">
									<div class="task-project-name"><span class="task-project-folding<?php 
									if ( ! $arResult['GROUPS'][$task['GROUP_ID']]['EXPANDED'] )
										echo ' task-project-folding-closed';
										?>" onclick="ToggleProjectTasks(<?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]?>, event);"></span><a class="task-project-name-link" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_GROUP_TASKS"], array("group_id" => $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]))?>" onclick="ToggleProjectTasks(<?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["ID"]?>, event);"><?php echo $arResult["GROUPS"][$task["GROUP_ID"]]["NAME"]?></a></div>
									<?php if (is_object($USER) && $USER->IsAuthorized() && $arParams["HIDE_VIEWS"] != "Y"):?>
										<div class="task-project-actions"><a 
											onclick="
											<?php
											if ($currentProject > 0)
											{
												?>AddQuickPopupTask(event, {GROUP_ID: <?php echo (int) $currentProject; ?>});<?php
											}
											else
											{
												?>AddQuickPopupTask(event);<?php
											}
											?>
											" class="task-project-action-link" href="<?php $path = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASKS_TASK"], array("task_id" => 0, "action" => "edit")); echo $path.(strstr($path, "?") ? "&" : "?")."GROUP_ID=".$arResult["GROUPS"][$task["GROUP_ID"]]["ID"].($arResult["IS_IFRAME"] ? "&IFRAME=Y" : "");?>"><i class="task-project-action-icon"></i><span class="task-project-action-text"><?php echo GetMessage("TASKS_ADD_TASK")?></span></a><span class="task-project-lightning" onclick="ShowQuickTask(null, {group: {id: <?php echo $task["GROUP_ID"]?>, title: '<?php echo CUtil::JSEscape($arResult["GROUPS"][$task["GROUP_ID"]]["NAME"])?>'}});"></span></div>
									<?php endif?>
								</div>
							</td>
						</tr>
					<?php endif?>
					<?php
					$projectExpanded = true;
					if (isset($arResult["GROUPS"][$task["GROUP_ID"]])
						&& isset($arResult["GROUPS"][$task["GROUP_ID"]]["EXPANDED"])
						&& ( ! $arResult["GROUPS"][$task["GROUP_ID"]]["EXPANDED"] )
						)
					{
						$projectExpanded = false;
					}

					tasksRenderListItem(
						$task, 		// $task
						isset($arResult["CHILDREN_COUNT"]["PARENT_".$task["ID"]]) ? $arResult["CHILDREN_COUNT"]["PARENT_".$task["ID"]] : 0, 	// $childrenCount
						$arPaths, 	// $arPaths
						0, 			// $depth
						($arResult["VIEW_TYPE"] == "list"), 	// $plain
						false, 		// $defer
						SITE_ID, 	// $site_id
						isset($arResult["UPDATES_COUNT"][$task["ID"]]) ? $arResult["UPDATES_COUNT"][$task["ID"]] : 0, 	// $updatesCount
						$projectExpanded,	// $projectExpanded,
						false,
						"bitrix:tasks.list.item",
						".default",
						$arParams["NAME_TEMPLATE"]
						);
					?>
				<?php endforeach?>
				<tr class="task-list-item" id="task-list-no-tasks" style="display:none;"><td class="task-new-item-column" colspan="9" style="text-align: center"><?php echo GetMessage("TASKS_NO_TASKS")?></td></tr>
			<?php else:?>
				<tr class="task-list-item" id="task-list-no-tasks"><td class="task-new-item-column" colspan="9" style="text-align: center"><?php echo GetMessage("TASKS_NO_TASKS")?></td></tr>
			<?php endif?>
		</tbody>
	</table>
</div>
<br />
<?php echo $arResult["NAV_STRING"]?>

<?php if (!isset($arParams["HIDE_VIEWS"]) || $arParams["HIDE_VIEWS"] != "Y"):?>
		<div id="task-list-filter" class="task-gantt-filter">
			<div class="task-filter<?php if (isset($arResult["ADV_FILTER"]["F_ADVANCED"]) && $arResult["ADV_FILTER"]["F_ADVANCED"] == "Y"):?> task-filter-advanced-mode<?php endif?>">

				<?php
					$name = $APPLICATION->IncludeComponent(
						"bitrix:tasks.filter.v2",
						".default",
						array(
							"ADV_FILTER" => isset($arResult["ADV_FILTER"]) ? $arResult["ADV_FILTER"] : null,
							"VIEW_TYPE" => $arResult["VIEW_TYPE"],
							"COMMON_FILTER" => $arResult["COMMON_FILTER"],
							"USER_ID" => $arParams["USER_ID"],
							"HIGHLIGHT_CURRENT" => isset($arResult["ADV_FILTER"]["F_ADVANCED"]) && $arResult["ADV_FILTER"]["F_ADVANCED"] == "Y" ? "N" : "Y",
							"ROLE_FILTER_SUFFIX" => $arResult["ROLE_FILTER_SUFFIX"],
							"PATH_TO_TASKS" => $arParams["PATH_TO_TASKS"],
							"GROUP_ID" => $arParams["GROUP_ID"],
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
						),
						null,
						array("HIDE_ICONS" => "Y")
					);
				?>

				<?php if ($arParams["USER_ID"] == $USER->GetID()):?>
					<div class="task-filter-extra-pages">
						<ul class="task-filter-items">
							<li class="task-filter-item">
								<a class="task-filter-item-link" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TEMPLATES"], array());?>"><span class="task-filter-item-left"></span><span class="task-filter-item-text"><?php echo GetMessage("TASKS_TEMPLATES")?></span><span class="task-filter-item-number"><?php echo CTaskTemplates::GetCount()?></span></a>
							</li>
							<li class="task-filter-item">
								<a class="task-filter-item-link" href="<?php echo CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_REPORTS"], array());?>"><span class="task-filter-item-left"></span><span class="task-filter-item-text"><?php echo GetMessage("TASKS_REPORTS")?></span></a>
							</li>
						</ul>
					</div>
				<?php endif?>

				<ul class="task-filter-extra-links">
					<li><i class="task-list-to-excel"></i><a href="<?php echo $APPLICATION->GetCurPageParam("EXCEL=Y", array("PAGEN_".$arResult["NAV_PARAMS"]["PAGEN"], "SHOWALL_".$arResult["NAV_PARAMS"]["PAGEN"], "VIEW"));?>"><?php echo GetMessage("TASKS_EXPORT_EXCEL")?></a></li>
					<li><i class="task-list-to-outlook"></i><a href="javascript:<?echo CIntranetUtils::GetStsSyncURL(array('LINK_URL' => '/'.$USER->GetID().'/'), 'tasks')?>"><?php echo GetMessage("TASKS_EXPORT_OUTLOOK")?></a></li>
				</ul>
			</div>
		</div>

<?php
	$arComponentParams = $arParams;
	$arComponentParams['VIEW_TYPE'] = $arResult['VIEW_TYPE'];
	$arComponentParams['GROUP'] = $arResult['GROUP'];
	$arComponentParams['TEMPLATES'] = $arResult['TEMPLATES'];
	$arComponentParams['SHOW_QUICK_TASK_ADD'] = 'Y';

	$filterName = '';
	if (strlen($arResult['SELECTED_PRESET_NAME']))
		$filterName .= ': ' . htmlspecialcharsbx($arResult['SELECTED_PRESET_NAME']);

	$arComponentParams['SELECTED_PRESET_NAME'] = $arResult['SELECTED_PRESET_NAME'];

	$arComponentParams['ADDITIONAL_HTML'] = '<span class="task-title-button-filter" '
		. ' onclick="TaskListFilterPopup.show(this);">'
		. '<span class="task-title-button-filter-left"></span><span class="task-title-button-filter-text">'
		. GetMessage("TASKS_FILTER") . $filterName
		. '</span><span class="task-title-button-filter-right"></span></span>';
	$APPLICATION->IncludeComponent(
		'bitrix:tasks.list.controls',
		((defined('SITE_TEMPLATE_ID') && (SITE_TEMPLATE_ID === 'bitrix24')) ? 'default_b24' : '.default'),
		$arComponentParams,
		null,
		array('HIDE_ICONS' => 'Y')
	);
endif;
