var responsiblePopup, accomplicesPopup, auditorsPopup, quickResponsiblePopup, delegatePopup, delegateUser;
var arAccomplices = [];
var arAuditors = [];

BX.ready(function() {

	BX.bind(BX("task-comments-switcher", true), "click", ToggleSwitcher);
	BX.bind(BX("task-log-switcher", true), "click", ToggleSwitcher);
	BX.bind(BX("task-time-switcher", true), "click", ToggleSwitcher);

	if (BX("task-add-elapsed-time", true))
	{
		BX.bind(BX("task-add-elapsed-time", true), "click", AddElaplsedTime);
		BX.bind(BX("task-send-elapsed-time", true), "click", SendElaplsedTime);
		BX.bind(BX("task-cancel-elapsed-time", true), "click", CancelElaplsedTime);
	}

	if (BX("task-group-change", true))
	{
		BX.bind(BX("task-group-change", true), "click", ChangeGroup);
	}

	if (BX("task-detail-responsible-change"))
	{
		BX.bind(BX("task-detail-responsible-change").parentNode, "click", function(e) {

			if(!e) e = window.event;

			responsiblePopup = BX.PopupWindowManager.create("responsible-employee-popup", this, {
				offsetTop : 1,
				autoHide : true,
				closeByEsc : true,
				content : BX("RESPONSIBLE_selector_content")
			});

			responsiblePopup.show();

			this.value = "";
			BX.focus(this);

			BX.PreventDefault(e);
		});
	}

	if (BX("task-detail-info-assistants-add"))
	{
		function _accomplicesAddChange(e) {

			if(!e) e = window.event;

			arAccomplices = O_ACCOMPLICES.arSelected;

			accomplicesPopup = BX.PopupWindowManager.create("assistants-employee-popup", this, {
				autoHide : true,
				closeByEsc : true,
				content : BX("ACCOMPLICES_selector_content"),
				buttons : [
				new BX.PopupWindowButton({
					text : BX.message("TASKS_SELECT"),
					className : "popup-window-button-accept",
					events : {
						click : function(e) {
							if(!e) e = window.event;

							var empIDs = [];
							div = BX("task-detail-assistants");

							BX.cleanNode(div);
							for(i = 0; i < arAccomplices.length; i++)
							{
								if (arAccomplices[i])
								{
									div.appendChild(RenderUser(arAccomplices[i]));
									empIDs.push(arAccomplices[i].id);
								}
							}
							if (empIDs.length > 0)
							{
								BX.removeClass(BX("task-detail-info-assistants"), "task-detail-info-users-empty");
								BX("task-detail-info-assistants-add").parentNode.style.display = "none";
								this.popupWindow.setBindElement(BX("task-detail-info-assistants-change"));
							}
							else
							{
								BX.addClass(BX("task-detail-info-assistants"), "task-detail-info-users-empty");
								BX("task-detail-info-assistants-add").parentNode.style.display = "block";
								this.popupWindow.setBindElement(BX("task-detail-info-assistants-add"));
							}
							var data = {
								mode : "accomplices",
								sessid : BX.message("bitrix_sessid"),
								id : detailTaksID,
								path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
								path_to_task: BX.message("TASKS_PATH_TO_TASK"),
								accomplices : empIDs
							};
							BX.ajax.post(ajaxUrl, data);


							this.popupWindow.close();
						}
					}
				}),

			new BX.PopupWindowButtonLink({
				text : BX.message("TASKS_CANCEL"),
				className : "popup-window-button-link-cancel",
				events : {
					click : function(e) {
						if(!e) e = window.event;

						this.popupWindow.close();

						BX.PreventDefault(e);
					}
				}
			})
			]
		});

		accomplicesPopup.show();

		this.value = "";
		BX.focus(this);

		BX.PreventDefault(e);
	}

	BX.bind(BX("task-detail-info-assistants-change"), "click", _accomplicesAddChange);
		BX.bind(BX("task-detail-info-assistants-add"), "click", _accomplicesAddChange);
	}

	if (BX("task-detail-info-auditors-add"))
	{
		function _auditorsAddChange(e) {

			if(!e) e = window.event;

			arAuditors = O_AUDITORS.arSelected;

			auditorsPopup = BX.PopupWindowManager.create("auditors-employee-popup", this, {
				autoHide : true,
				closeByEsc : true,
				content : BX("AUDITORS_selector_content"),
				buttons : [
				new BX.PopupWindowButton({
					text : BX.message("TASKS_SELECT"),
					className : "popup-window-button-accept",
					events : {
						click : function(e) {
							if(!e) e = window.event;

							var empIDs = [];
							div = BX("task-detail-auditors");

							BX.cleanNode(div);
							for(i = 0; i < arAuditors.length; i++)
							{
								if (arAuditors[i])
								{
									div.appendChild(RenderUser(arAuditors[i]));
									empIDs.push(arAuditors[i].id);
								}
							}
							if (empIDs.length > 0)
							{
								BX.removeClass(BX("task-detail-info-auditors"), "task-detail-info-users-empty");
								BX("task-detail-info-auditors-add").parentNode.style.display = "none";
								this.popupWindow.setBindElement(BX("task-detail-info-auditors-change"));
							}
							else
							{
								BX.addClass(BX("task-detail-info-auditors"), "task-detail-info-users-empty");
								BX("task-detail-info-auditors-add").parentNode.style.display = "block";
								this.popupWindow.setBindElement(BX("task-detail-info-auditors-add"));
							}

							var data = {
								mode : "auditors",
								sessid : BX.message("bitrix_sessid"),
								id : detailTaksID,
								path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
								path_to_task: BX.message("TASKS_PATH_TO_TASK"),
								path_to_user_tasks_task: BX.message("PATH_TO_USER_TASKS_TASK"),
								auditors : empIDs
							};
							BX.ajax.post(ajaxUrl, data);


							this.popupWindow.close();
						}
					}
				}),

			new BX.PopupWindowButtonLink({
				text : BX.message("TASKS_CANCEL"),
				className : "popup-window-button-link-cancel",
				events : {
					click : function(e) {
						if(!e) e = window.event;

						this.popupWindow.close();

						BX.PreventDefault(e);
					}
				}
			})
			]
		});

		auditorsPopup.show();

			this.value = "";
			BX.focus(this);

			BX.PreventDefault(e);
		}

		BX.bind(BX("task-detail-info-auditors-change"), "click", _auditorsAddChange);
		BX.bind(BX("task-detail-info-auditors-add"), "click", _auditorsAddChange);
	}

	if (BX("task-elapsed-time-form"))
	{
		var elapsedInputs = BX("task-elapsed-time-form").getElementsByTagName("input");
		for (var i = 0; i < elapsedInputs.length; i++) {
			BX.bind(elapsedInputs[i], "keypress", function(e) {
				if(!e) e = window.event;
				if (e.keyCode == 13) {
					BX.submit(this.form);
				}
			});
		}
	}
});

var tasksDetailsNS = {
	deleteFile : function (fileId, taskId, linkId, oRemoveBtn)
	{
		if ( ! confirm(BX.message("TASKS_DELETE_FILE_CONFIRM")) )
			return (false);

		BX.ajax.post(
			"/bitrix/components/bitrix/tasks.task.detail/ajax.php?action=remove_file",
			{
				fileId : fileId,
				taskId : taskId,
				sessid : BX.message("bitrix_sessid")
			},
			(function(linkId, oRemoveBtn){
				return function(datum){
					try
					{
						if (datum === 'Success')
						{
							BX(linkId).style.textDecoration = 'line-through';
							BX.remove(oRemoveBtn);
						}
					}
					catch (e)
					{
						// do nothing
					}
				} 
			})(linkId, oRemoveBtn)
		);
	}
}


function ToggleSwitcher()
{
	if (BX.hasClass(this, "task-switcher-selected"))
		return false;

	var tabs = ["task-log", "task-time", "task-comments"];
	for (var i = 0; i < tabs.length; i++)
	{
		var block = BX(tabs[i] + "-block", true);
		var switcher = BX(tabs[i] + "-switcher", true);

		if (switcher === this)
		{
			BX.addClass(switcher, "task-switcher-selected");
			BX.addClass(block, tabs[i] + "-block-selected");
		}
		else
		{
			BX.removeClass(switcher, "task-switcher-selected");
			if (block)
			{
				BX.removeClass(block, tabs[i] + "-block-selected");
			}
		}
	}

	return false;
}

function GetNumericCase(number, nominative, genitiveCase, prepositional)
{
	number = parseInt(number, 10);
	if (isNaN(number))
		return prepositional;

	if (number < 0)
		number = 0 - number;

	number %= 100;
	if (number >= 5 && number <= 20)
		return prepositional;

	number %= 10;
	if (number == 1)
		return nominative;

	if (number >= 2 && number <= 4)
		return genitiveCase;

	return prepositional;
}

function ChangeTaskUsers(event)
{
	var id = this.id.replace(/-change/, "");
	BX.addClass(this.parentNode.parentNode, "task-detail-info-users-empty");
	BX(id + "-add", true).parentNode.style.display = "block";

	BX.PreventDefault(event);
}


function AddTaskUsers(event)
{
	var id = this.id.replace(/-add/, "");
	BX.removeClass(BX(id + "-change", true).parentNode.parentNode, "task-detail-info-users-empty");
	this.parentNode.style.display = "none";


	BX.PreventDefault(event);
}


function ShowDeclinePopup(bindElement, taskId)
{
	BX.TaskDeclinePopup.show(bindElement, {
		offsetTop : -180,
		events : {
			onPopupChange: (function (){
				return function() {
					var form = BX.create("form", {
						props : {
							method : "POST",
							action : postFormAction
						},
						style : {
							display : "none"
						},
						children : [
						BX.create("input",{
							props : {
								name : "ACTION",
								value : "decline"
							}
						}),
						BX.create("input",{
							props : {
								name : "sessid",
								value : BX.message("bitrix_sessid")
							}
						}),
						BX.create("input",{
							props : {
								name : "ID",
								value : taskId
							}
						}),
						BX.create("input",{
							props : {
								name : "REASON",
								value : this.textarea.value
							}
						})
						]
					});
					document.body.appendChild(form);
					BX.submit(form);
				}

			})()
		}
	});

	return false;
}

/*=====================Templates Popup==========================*/

function ShowTemplatesPopup(bindElement)
{
	var popup = BX("task-popup-templates-popup-content", true);

	BX.PopupWindowManager.create("task-templates-popup" , bindElement, {
		autoHide : true,
		offsetTop : 1,
		//lightShadow : true,
		events : {
			onPopupClose : __onTemplatesPopupClose
		},
		content : popup
	}).show();

	BX.addClass(bindElement, "task-title-button-templates-selected");

	return false;
}

function __onTemplatesPopupClose()
{
	BX.removeClass(this.bindElement, "task-title-button-templates-selected");
}

/*=====================Grade Popup==============================*/
function ShowGradePopupDetail(taskId, bindElement, currentValues)
{
	BX.TaskGradePopup.show(
		taskId,
		bindElement,
		currentValues,
		{
			events : {
				onPopupChange : __onGradePopupChangeDetail
			}
		}
		);

	return false;
}


function __onGradePopupChangeDetail()
{
	this.bindElement.className = "task-detail-grade task-detail-grade-" + this.listItem.className;
	this.bindElement.childNodes[1].innerHTML = this.listItem.name;
	var data = {
		mode : "mark",
		sessid : BX.message("bitrix_sessid"),
		id : this.id,
		mark : this.listValue
	};
	BX.ajax.post(ajaxUrl, data);

}

/*=====================Priority Popup============================*/
function ShowPriorityPopupDetail(taskId, bindElement, currentPriority)
{
	BX.TaskPriorityPopup.show(
		taskId,
		bindElement,
		currentPriority,
		{
			events : {
				onPopupChange : __onPriorityChangeDetail
			}
		}

		);

	return false;
}

function __onPriorityChangeDetail()
{
	this.bindElement.className = "task-detail-priority task-detail-priority-" + this.listValue;
	this.bindElement.childNodes[1].innerHTML = this.listItem.name;
	var data = {
		mode : "priority",
		sessid : BX.message("bitrix_sessid"),
		id : this.id,
		path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
		path_to_task: BX.message("TASKS_PATH_TO_TASK"),
		path_to_user_tasks_task: BX.message("PATH_TO_USER_TASKS_TASK"),
		priority : this.listValue
	};
	BX.ajax.post(ajaxUrl, data);

	if (taskData.priority != this.listValue && window.top.BX.TasksIFrameInst)
	{
		taskData.priority = this.listValue;
		window.top.BX.TasksIFrameInst.onTaskChanged(taskData);
	}
}

function SetReport(id, flag)
{
	if ((flag && BX.hasClass(BX("task-detail-report-no"), 'selected')) || (!flag && BX.hasClass(BX("task-detail-report-yes"), 'selected')))
	{
		BX.toggleClass(BX("task-detail-report-yes"), 'selected');
		BX.toggleClass(BX("task-detail-report-no"), 'selected');

		var data = {
			mode : "report",
			sessid : BX.message("bitrix_sessid"),
			id : id,
			path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
			path_to_task: BX.message("TASKS_PATH_TO_TASK"),
			path_to_user_tasks_task: BX.message("PATH_TO_USER_TASKS_TASK"),
			report : flag
		};
		BX.ajax.post(ajaxUrl, data);
	}
}

function SaveTags(tags)
{
	var tagsString = "";
	for (var i = 0, length = tags.length; i < length; i++)
	{
		if (i > 0)
			tagsString += ", ";
		tagsString += tags[i].name
	};

	var data = {
		mode : "tags",
		sessid : BX.message("bitrix_sessid"),
		id : detailTaksID,
		path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
		path_to_task: BX.message("TASKS_PATH_TO_TASK"),
		path_to_user_tasks_task: BX.message("PATH_TO_USER_TASKS_TASK"),
		tags : tagsString
	};
	BX.ajax.post(ajaxUrl, data);
}

function onResponsibleSelect(arUser)
{
	var div = BX.findNextSibling(responsiblePopup.bindElement, {
		tag : "div"
	});
	BX.cleanNode(div);

	div.appendChild(RenderUser(arUser, true));

	var data = {
		mode : "responsible",
		sessid : BX.message("bitrix_sessid"),
		id : detailTaksID,
		path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
		path_to_task: BX.message("TASKS_PATH_TO_TASK"),
		path_to_user_tasks_task: BX.message("PATH_TO_USER_TASKS_TASK"),
		responsible : arUser.id
	};
	BX.ajax.post(ajaxUrl, data);

	if (taskData.responsibleId != arUser.id && window.top.BX.TasksIFrameInst)
	{
		taskData.responsibleId = arUser.id;
		taskData.responsible = arUser.name;
		window.top.BX.TasksIFrameInst.onTaskChanged(taskData);
	}

	responsiblePopup.close();
}

function onAccomplicesChange(arUsers)
{
	arAccomplices = arUsers;
}

function onAuditorsChange(arUsers)
{
	arAuditors = arUsers;
}

function RenderUser(arUser, bAvatar)
{
	var arChildren = [];
	if (bAvatar)
	{
		arChildren.push(BX.create("a", {
			props : {
				href : BX.message("TASKS_PATH_TO_USER_PROFILE").replace("#user_id#", arUser.id),
				className : "task-detail-info-user-avatar"
			},
			style : {
				background : arUser.photo ? "url('" + arUser.photo + "') no-repeat center center" : ""
			}
		}));
	}
	arChildren.push(BX.create("div", {
		props : {
			className : "task-detail-info-user-name"
		},
		children : [
		BX.create("a", {
			props : {
				href : BX.message("TASKS_PATH_TO_USER_PROFILE").replace("#user_id#", arUser.id)
			},
			text : arUser.name
		})
		]
	}));
	arChildren.push(BX.create("div", {
		props : {
			className : "task-detail-info-user-position"
		},
		text : arUser.position
	}));
	return BX.create("div", {
		props : {
			className : "task-detail-info-user"
		},
		children : arChildren
	});
}

function ShowDelegatePopup(bindElement, taskId)
{
	delegatePopup = BX.PopupWindowManager.create("delegate-employee-popup", bindElement, {
		offsetTop : 1,
		autoHide : false,
		closeByEsc : true,
		content : BX("DELEGATE_selector_content"),
		buttons : [
		new BX.PopupWindowButton({
			text : BX.message("TASKS_SELECT"),
			className : "popup-window-button-accept",
			events : {
				click : (function (e) {
					if(!e) e = window.event;

					return function(e) {
						if (delegateUser != currentUser)
						{
							var form = BX.create("form", {
								props : {
									method : "POST"
								},
								style : {
									display : "none"
								},
								children : [
								BX.create("input",{
									props : {
										name : "ACTION",
										value : "delegate"
									}
								}),
								BX.create("input",{
									props : {
										name : "sessid",
										value : BX.message("bitrix_sessid")
									}
								}),
								BX.create("input",{
									props : {
										name : "ID",
										value : taskId
									}
								}),
								BX.create("input",{
									props : {
										name : "USER_ID",
										value : delegateUser
									}
								})
								]
							});
							document.body.appendChild(form);
							BX.submit(form);
						}

						this.popupWindow.close();
					}

				})()
				}
		}),

		new BX.PopupWindowButtonLink({
			text : BX.message("TASKS_CANCEL"),
			className : "popup-window-button-link-cancel",
			events : {
				click : function(e) {
					if(!e) e = window.event;

					this.popupWindow.close();

					BX.PreventDefault(e);
				}
			}
		})
	]
	});

	delegatePopup.show();
}

function onDelegateChange(arUsers)
{
	if (arUsers)
	{
		var tmp = arUsers.pop();
		if (tmp)
			delegateUser = tmp.id;
	}
}

function GoToComment(link, toggle)
{
	if (toggle)
	{
		(BX.proxy(ToggleSwitcher, BX("task-comments-switcher", true)))();
	}
	setTimeout('window.location = "' + link + '"', 10);
}

var lastRow;

function MoveForm(toRow)
{
	var nextRow = BX.findNextSibling(toRow, {tag: "tr"});
	if (nextRow)
	{
		toRow.parentNode.insertBefore(BX("task-elapsed-time-form-row"), nextRow);
	}
	else
	{
		toRow.parentNode.appendChild(BX("task-elapsed-time-form-row"));
	}

	if (lastRow)
	{
		lastRow.style.display = "";
	}
	lastRow = toRow;
	lastRow.style.display = "none";
}

function ShowElapsedForm(id, action, hours, minutes, comment)
{
	BX("task-elapsed-time-form").elements["ELAPSED_ID"].value = id;
	BX("task-elapsed-time-form").elements["ACTION"].value = action;
	BX("task-elapsed-time-form").elements["HOURS"].value = hours;
	BX("task-elapsed-time-form").elements["MINUTES"].value = minutes;
	BX("task-elapsed-time-form").elements["COMMENT_TEXT"].value = comment;

	BX("task-elapsed-time-form-row").style.display = "";
	BX("task-time-comment-column").style.display = ""; // IE7 hack
	BX("task-elapsed-time-form").elements["HOURS"].focus();
	BX("task-elapsed-time-form").elements["HOURS"].select();
}

function AddElaplsedTime()
{
	MoveForm(BX("task-elapsed-time-button-row"));

	ShowElapsedForm("", "elapsed_add", "1", "00", "");

}

function EditElapsedTime(id, hours, minutes, comment)
{
	MoveForm(BX("elapsed-time-" + id));

	ShowElapsedForm(id, "elapsed_update", hours, minutes, comment)
}

function CancelElaplsedTime()
{
	if (lastRow)
	{
		lastRow.style.display = "";
	}
	BX("task-elapsed-time-form-row").style.display = "none";
	BX("task-time-comment-column").style.display = "none"; // IE7 hack
	BX("task-elapsed-time-button-row").style.display = "";
}

function SendElaplsedTime()
{
	BX.submit(BX("task-elapsed-time-form"));
	BX.unbind(BX("task-send-elapsed-time", true), "click", SendElaplsedTime);
}

function ShowActionMenu(button, id, menu)
{
	BX.TaskMenuPopup.show(
		id,
		button,
		menu,
		{ /*bindOptions : {forceBindPosition : true } */}
	);

	return false;
}

function ShowNewTaskMenu(button, id, menu)
{
	BX.TaskMenuPopup.show(
		-1,
		button,
		menu,
		{offsetTop : -2, offsetLeft : -10}
	);

	return false;
}

function onDeleteClick(e, taskId)
{
	if (!e) e = window.event;

	if (confirm(BX.message("TASKS_DELETE_TASK_CONFIRM")))
	{
		if (window.top.BX.TasksIFrameInst && window.top.BX.TasksIFrameInst.isOpened())
		{
			var data = {
				mode : "delete",
				sessid : BX.message("bitrix_sessid"),
				id : taskId
			};
			BX.ajax.post(ajaxUrl, data, function(datum) { onDeleteClick_onSuccess(e, taskId, datum); } );

			BX.PreventDefault(e);
		}
	}
	else
	{
		BX.PreventDefault(e);
	}
}

function onDeleteClick_onSuccess(e, taskId, data)
{
	if (data && data.length > 0)
	{
		// there is an error occured
		alert(data);
	}
	else
	{
		window.top.BX.TasksIFrameInst.close();
		window.top.BX.TasksIFrameInst.onTaskDeleted(taskId);
	}
}


function ChangeGroup(e)
{
	if (!e) e = window.event;

	taskGroupPopup.show();

	BX.PreventDefault(e);
}

function ClearGroup(groupId, deleteIcon)
{
	BX.adjust(BX("task-group-change"), {text: BX.message("TASKS_GROUP_ADD")});
	BX.cleanNode(deleteIcon, true);
	groupsPopup.deselect(groupId);

	SaveGroup(0);
}

function onTaskGroupSelect(groups, params)
{
	try
	{
		if (
			(typeof(params) === 'object')
			&& (typeof(params.onInit) !== 'undefined')
			&& (params.onInit === true)
		)
		{
			return;
		}
	}
	catch (e)
	{
	}

	if (groups[0])
	{
		BX.adjust(BX("task-group-change"), {text: groups[0].title});
		var deleteIcon = BX.findChild(BX("task-group-change").parentNode, {tag: "span", className: "task-group-delete"});
		if (!deleteIcon)
		{
			deleteIcon = BX.create("span", {props: {className: "task-group-delete"}});
			BX("task-group-change").parentNode.appendChild(deleteIcon);
		}

		BX.adjust(deleteIcon, {
			events: {
				click: function(e) {
					if (!e) e = window.event;
					ClearGroup(groups[0].id, this)
					BX.PreventDefault(e);
				}
			}
		});

		SaveGroup(groups[0].id);
	}
}

function SaveGroup(groupId)
{
	var data = {
		mode : "group",
		sessid : BX.message("bitrix_sessid"),
		id : detailTaksID,
		path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
		path_to_task: BX.message("TASKS_PATH_TO_TASK"),
		path_to_user_tasks_task: BX.message("PATH_TO_USER_TASKS_TASK"),
		groupId : groupId
	};
	BX.ajax.post(ajaxUrl, data);
}

function ClearDeadline(taskId, deleteIcon)
{
	deleteIcon.style.display = "none";
	var field = BX("task-deadline-hidden");
	field.value = "";

	BX.cleanNode (field.previousSibling);
	var newsubcont = document.createElement("span");
	newsubcont.innerHTML = BX.message("TASKS_SIDEBAR_DEADLINE_NO");
	field.previousSibling.appendChild(newsubcont);

	field.previousSibling.className = "webform-field-action-link";
	var data = {
		mode : "deadline",
		sessid : BX.message("bitrix_sessid"),
		id : taskId,
		deadline : ""
	};
	BX.ajax.post(ajaxUrl, data);
}