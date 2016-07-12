BX.ready(function() {

	if (BX("task-new-item-responsible"))
	{
		BX.bind(BX("task-new-item-responsible"), "click", function(e) {

			if(!e) e = window.event;

			quickResponsiblePopup = BX.PopupWindowManager.create("quick-responsible-employee-popup", this, {
				offsetTop : 1,
				autoHide : true,
				closeByEsc : true,
				content : BX("QUICK_RESPONSIBLE_selector_content")
			});

			quickResponsiblePopup.show();
			
			BX.addCustomEvent(quickResponsiblePopup, "onPopupClose", onQuickResponsibleClose);
			
			this.value = "";
			BX.focus(this);

			// this broke closing of overlapped calendar (in create quick task mode)
			// so, commented
			// BX.PreventDefault(e);
		});
		
		BX.bind(BX("task-new-item-link-group"), "click", function(e) {
			if(!e) e = window.event;

			groupsPopup.show();

			BX.PreventDefault(e);
		});
		
		BX.bind(BX("task-new-item-description-link"), "click", function(e) {
			if(!e) e = window.event;

			BX("task-quick-description-textarea").style.display = "block";
			this.style.display = "none";
		})
	}
})

function CloseTask(taskId)
{
	var row = BX("task-" + taskId);
	if (row)
	{
		SetCSSStatus(row, "completed", "task-status-");
		var cells = row.getElementsByTagName("TD");
		var title = BX.findChild(cells[0], {tagName : "a"}, true);

		BX.style(title, "text-decoration", "line-through");

		cells[2].innerHTML = "&nbsp;";
		var link = BX.findChild(cells[8], {tagName : "a"});
		link.onclick = null;
		link.title = BX.message("TASKS_FINISHED");
	}
	SetServerStatus(taskId, "close");
}

function StartTask(taskId)
{
	var row = BX("task-" + taskId);
	if (row)
	{
		SetCSSStatus(row, "in-progress", "task-status-");
		var cells = row.getElementsByTagName("TD");
		cells[2].innerHTML = "<span class=\"task-flag-in-progress\"></span>";
	}
	SetServerStatus(taskId, "start");
}

function AcceptTask(taskId)
{
	var row = BX("task-" + taskId);
	if (row)
	{
		SetCSSStatus(row, "accepted", "task-status-");
		var cells = row.getElementsByTagName("TD");
		cells[2].innerHTML = "&nbsp;";
	}
	SetServerStatus(taskId, "accept");
}

function RenewTask(taskId)
{
	var row = BX("task-" + taskId);
	if (row)
	{
		SetCSSStatus(row, "new", "task-status-");
		var cells = row.getElementsByTagName("TD");
		cells[2].innerHTML = "<span class=\"task-flag-waiting-confirm\"></span>";
	}
	SetServerStatus(taskId, "renew");
}

function DeferTask(taskId)
{
	var row = BX("task-" + taskId);
	if (row)
	{
		SetCSSStatus(row, "delayed", "task-status-");
		var cells = row.getElementsByTagName("TD");
		cells[2].innerHTML = "&nbsp;";
	}
	SetServerStatus(taskId, "defer");
}

function DeclineTask(taskId)
{
	var row = BX("task-" + taskId);
	if (row)
	{	
		var cells = row.getElementsByTagName("TD");
		var link = BX.findChild(cells[1], {tagName : "a"});
	}
	BX.TaskDeclinePopup.show(link, {
		offsetLeft : -100,
		taskId : this.id,
		events : {
			onPopupChange: function()
			{
				if (row)
				{
					SetCSSStatus(row, "declined", "task-status-");
					cells[2].innerHTML = "&nbsp;"
				}
				SetServerStatus(taskId, "decline", {reason : this.textarea.value});
			}
		}
	})
}

function AddTask()
{
	var title = BX("task-new-item-name").value;
	if (BX.util.trim(title).length == 0)
	{
		alert(BX.message("TASKS_NO_TITLE"));
		return false;
	}
	var deadline = BX("task-new-item-deadline").value;
	var responsible =BX("task-new-item-responsible-hidden").value;
	if (!parseInt(responsible))
	{
		alert(BX.message("TASKS_NO_RESPONSIBLE"));
		return false;
	}
	var priority = 0;
	if (BX.hasClass(BX("task-new-item-priority"), "task-priority-1"))
	{
		priority = 1;
	}
	else if (BX.hasClass(BX("task-new-item-priority"), "task-priority-2"))
	{
		priority = 2;
	}
	var description = BX('task-new-item-description').value.replace(/\r\n|\r|\n/g, "<br />");
	
	// disable form elements
	BX("task-new-item-description").disabled = true;
	BX("task-new-item-name").disabled = true;
	BX("task-new-item-deadline").disabled = true;
	BX("task-new-item-responsible").disabled = true;
	BX("task-new-item-submit").disabled = true;
	BX("task-new-item-cancel").disabled = true;
	
	BX.addClass(BX("task-new-item-submit").parentNode, "loading");
	
	taskParent = window.defaultQuickParent ? window.defaultQuickParent : 0;
	if (newTaskParent > 0)
	{
		taskParent = newTaskParent;
	}
	
	var data = {
		mode : "add",
    	sessid : BX.message("bitrix_sessid"),
		title : title,
		description : description,
		deadline : deadline,
		priority : priority,
		responsible : responsible,
		depth : newTaskDepth,
		parent : taskParent,
		group : newTaskGroup,
    	path_to_user: BX.message("TASKS_PATH_TO_USER_PROFILE"),
    	path_to_task: BX.message("TASKS_PATH_TO_TASK"),
    	forum_id: BX.message("FORUM_ID")
	};
	
	BX.ajax({
		"method": "POST",
		"dataType": "html",
		"url": ajaxUrl,
		"data":  data,
		"processData" : false,
		"onsuccess": (function() {
			var parentId = newTaskParent;
			var groupObj = newTaskGroupObj;
			return function(data) {
				
				// converting html to dom nodes
				var tempDiv = document.createElement("div");
				tempDiv.innerHTML = "<table>" + data + "</table>";
				var arRows = tempDiv.firstChild.getElementsByTagName("TR");
	    		var arScripts = tempDiv.firstChild.getElementsByTagName("SCRIPT");

	    		if (BX.browser.IsIE())
	    		{
	    			var script = arScripts[0];
	    		}
	    		else
	    		{
	    			var script = BX.create(
						"script", {
							props : {type : "text/javascript"},
							html: arScripts[0].innerHTML
						}
					)
	    		}

				var table = BX("task-new-item-row").parentNode;
				beforeRow = __FindBeforeRow(table, parentId, groupObj);

				if (beforeRow)
				{
					table.insertBefore(arRows[0], beforeRow);
					table.insertBefore(script, beforeRow);
				}
				else
				{
					table.appendChild(arRows[0]);
					table.appendChild(script);
				}
				
				if (BX("task-list-no-tasks"))
				{
					BX("task-list-no-tasks").style.display = "none";
				}

				// enable form elements
				BX("task-new-item-name").disabled = false;
				BX("task-new-item-description").disabled = false;
				BX("task-new-item-deadline").disabled = false;
				BX("task-new-item-responsible").disabled = false;
				BX("task-new-item-submit").disabled = false;
				BX("task-new-item-cancel").disabled = false;
				
				// restore defaults
				BX.removeClass(BX("task-new-item-submit").parentNode, "loading");
				BX("task-new-item-name").value = "";
				BX("task-new-item-description").value = "";
				
				BX("task-new-item-name").blur(); //if it has focus already
				BX("task-new-item-name").focus();
			}
		})()
	});
	
	return false;
}

function __CreateProjectRow(groupObj)
{
	var addUrl = BX.message("TASKS_PATH_TO_TASK").replace("#action#", "edit").replace("#task_id#", 0);
	var groupUrl = BX.message("PATH_TO_GROUP_TASKS").replace("#group_id#", groupObj.id);
	return BX.create("tr", {
		props: {
			className: "task-list-item",
			id: "task-project-" + groupObj.id
		},
		children: [
			BX.create("td", {
				props: {
					className: "task-project-column",
					colSpan: 9
				},
				html: '\
					<div class="task-project-column-inner">\
						<div class="task-project-name">\
							<span class="task-project-folding" onclick="ToggleProjectTasks(' + groupObj.id + ', event);"></span>\
							<a class="task-project-name-link" href="' + groupUrl + '" onclick="ToggleProjectTasks(' + groupObj.id + ', event);">' + BX.util.htmlspecialchars(groupObj.title) + '</a>\
						</div>\
						<div class="task-project-actions">\
							<a onclick="AddQuickPopupTask(event, {GROUP_ID: ' + groupObj.id + '});" class="task-project-action-link" href="' + addUrl + (addUrl.indexOf("?") == -1 ? "?" : "&") + 'GROUP_ID=' + groupObj.id + '">\
								<i class="task-project-action-icon"></i>\
								<span class="task-project-action-text">' + BX.message("TASKS_ADD_TASK") + '</span>\
							</a>\
							<span class="task-project-lightning" onclick="ShowQuickTask(null, {group: {id: ' + groupObj.id + ', title: \'' + BX.util.htmlspecialchars(groupObj.title) + '\'}});"></span>\
						</div>\
					</div>\
				'
			})
		]
	});
}

function __FindBeforeRow(table, parentId, groupObj)
{
	var newRow = BX("task-new-item-row");
	var beforeRow, parentRow;
	if (parentId > 0 && (parentRow = BX("task-" + parentId)))
	{
		beforeRow = BX.findNextSibling(parentRow, {tagName : "tr"});

		if (BX.findChild(parentRow, {tagName: "div", className : "task-title-folding"}, true))
		{
			var span = BX.findChild(parentRow, {tagName : "span"}, true);
			span.innerHTML = parseInt(span.innerHTML) + 1;
		}
		else
		{
			var folding = "<div class=\"task-title-folding\" onclick=\"ToggleSubtasks(this.parentNode.parentNode.parentNode, " + Depth(parentRow) + ", " + parentId + ")\"><span>1</span></div>";
			var titleHolder = BX.findChild(parentRow, {tagName : "div", className : "task-title-container"}, true);
			titleHolder.innerHTML = folding + titleHolder.innerHTML;
			BX.addClass(parentRow, "task-list-item-opened");
			loadedTasks[parentId] = true;
		}
	}
	else if (groupObj && groupObj.id > 0)
	{
		if (BX("task-project-" + groupObj.id))
		{
			beforeRow = BX.findNextSibling(BX("task-project-" + groupObj.id), {tagName : "tr"});
		}
		else
		{
			beforeRow = null;

			for(var i = 0, count = table.rows.length; i < count; i++) {
				if(table.rows[i].id.substr(0, 13) == "task-project-")
				{
					beforeRow = table.rows[i];
					break;
				}
			}

			projectRow = __CreateProjectRow(groupObj);
			if (beforeRow)
			{
				table.insertBefore(projectRow, beforeRow);
			}
			else
			{
				table.appendChild(projectRow);
			}
		}
	}
	else
	{
		if (table.firstChild.id == "task-new-item-row")
		{
			beforeRow = newRow.nextSibling;
		}
		else
		{
			beforeRow = table.firstChild;
		}
	}
	
	return beforeRow;
}


function DeleteTask(taskId)
{
	var data = {
		mode : "delete",
		sessid : BX.message("bitrix_sessid"),
		id : taskId
	};
	
	BX.ajax.post(ajaxUrl, data, function(datum) { TASKS_table_view_onDeleteClick_onSuccess(taskId, datum); } );
}


function TASKS_table_view_onDeleteClick_onSuccess(taskId, data)
{
	if (data && data.length > 0)
	{
		// there is an error occured
		alert(data);
	}
	else
	{
		__DeleteTaskRow(taskId);
		BX.onCustomEvent('onTaskListTaskDelete', [taskId]);
	}
}


function __DeleteTaskRow(taskId)
{
	var row = BX("task-" + taskId);
	var depth = Depth(row);
	
	var nextDepth = 0;
	var directChild = 0;
	
	var prevRow = BX.findPreviousSibling(row, {tagName : "tr"});
	var nextRow = BX.findNextSibling(row, {tagName : "tr"});

	while (
		nextRow 
		&& (nextRow.id !== 'task-list-no-tasks') 
		&& (nextDepth = Depth(nextRow)) > depth
	)
	{
		if (nextDepth == depth + 1)
		{
			directChild++;
		}
		BX.removeClass(nextRow, "task-depth-" + nextDepth);
		BX.addClass(nextRow, "task-depth-" + (nextDepth - 1));
		nextRow = BX.findNextSibling(nextRow, {tagName : "tr"});
	}
	
	if (depth > 0)
	{
		var parentRow = BX.findPreviousSibling(row, {tagName : "tr", className : "task-depth-" + (depth - 1)});
		if (parentRow)
		{
			var span = BX.findChild(parentRow, {tagName : "span"}, true);
			span.innerHTML = parseInt(span.innerHTML) - 1 + directChild;
			if (parseInt(span.innerHTML) ==  0)
			{
				BX.remove(span.parentNode);
				BX.removeClass(parentRow, "task-list-item-opened");
			}
		}
	}

	// let's count, how many rows with tasks will be after removing
	var taskRowsCount = 0;

	prevRow = BX.findPreviousSibling(row, {tagName : "tr"});
	while ( (taskRowsCount == 0) 
		&& (prevRow)
	)
	{
		if ( (prevRow.id !== 'task-list-no-tasks')
			&& (prevRow.id.substr(0, 13) !== 'task-project-' )
			&& (prevRow.id !== 'task-new-item-row')
		)
		{
			taskRowsCount = taskRowsCount + 1;
		}

		prevRow = BX.findPreviousSibling(prevRow, {tagName : "tr"})
	}

	nextRow = BX.findNextSibling(row, {tagName : "tr"});
	while ( (taskRowsCount == 0) 
		&& (nextRow)
	)
	{
		if ( (nextRow.id !== 'task-list-no-tasks')
			&& (nextRow.id.substr(0, 13) !== 'task-project-' )
			&& (nextRow.id !== 'task-new-item-row')
		)
		{
			taskRowsCount = taskRowsCount + 1;
		}

		nextRow = BX.findNextSibling(nextRow, {tagName : "tr"});
	}

	// if no more tasks in list => show phrase "there is no tasks"
	// and hide 'task-project-' rows, if exists
	if (taskRowsCount == 0)
	{
		BX('task-list-no-tasks').style.display = "";

		var bMustBeRemoved = false;
		var rowToBeRemoved = null;

		prevRow = BX.findPreviousSibling(row, {tagName : "tr"});
		while ( (taskRowsCount == 0) 
			&& (prevRow)
		)
		{
			if (prevRow.id.substr(0, 13) === 'task-project-')
			{
				bMustBeRemoved = true;
				rowToBeRemoved = prevRow;
			}
			
			prevRow = BX.findPreviousSibling(prevRow, {tagName : "tr"})

			if (bMustBeRemoved)
			{
				BX.remove (rowToBeRemoved);
				bMustBeRemoved = false;
			}
		}

		nextRow = BX.findNextSibling(row, {tagName : "tr"});
		while ( (taskRowsCount == 0) 
			&& (nextRow)
		)
		{
			if (nextRow.id.substr(0, 13) === 'task-project-')
			{
				bMustBeRemoved = true;
				rowToBeRemoved = nextRow;
			}

			nextRow = BX.findNextSibling(nextRow, {tagName : "tr"});

			if (bMustBeRemoved)
			{
				BX.remove (rowToBeRemoved);
				bMustBeRemoved = false;
			}
		}
	}

	BX.remove(row);
}

function SortTable(url, e)
{
	if(!e) e = window.event;
	window.location = url;
	BX.PreventDefault(e);
}

function ToggleProjectTasks(projectID, e)
{
	if(!e) e = window.event;
	
	var row = BX.findNextSibling(BX("task-project-" + projectID, true), {tagName : "tr"});
	
	var span = BX.findChild(BX("task-project-" + projectID, true), {tag: "span", className: "task-project-folding"}, true);
	var bFoldingClosed = BX.hasClass(span, 'task-project-folding-closed');

	if (bFoldingClosed)
		BX.userOptions.save('tasks', 'opened_projects', projectID, true);
	else
		BX.userOptions.save('tasks', 'opened_projects', projectID, false);
	
	while(row && !BX.hasClass(row.cells[0], "task-project-column") && !BX.hasClass(row.cells[0], "task-new-item-column"))
	{
		if (bFoldingClosed)
			row.style.display = "";
		else
			row.style.display = "none";

		row =  BX.findNextSibling(row, {tagName : "tr"});
	}
	BX.toggleClass(span, 'task-project-folding-closed');
	
	BX.PreventDefault(e);
}

function onGroupSelect(groups)
{
	if (groups[0])
	{
		if (groups[0].title.length > 40)
		{
			groups[0].title = groups[0].title.substr(0, 40) + "...";
		}
		BX.adjust(BX("task-new-item-link-group"), {
			text: groups[0].title
		});

		var deleteIcon = BX.findChild(BX("task-new-item-link-group").parentNode, {tag: "span", className: "task-group-delete"});
		if (!deleteIcon)
		{
			deleteIcon = BX.create("span", {props: {className: "task-group-delete"}});
			BX("task-new-item-link-group").parentNode.appendChild(deleteIcon);
		}

		BX.adjust(deleteIcon, {
			events: {
				click: function(e) {
					if (!e) e = window.event;
					BX.cleanNode(this, true);
					BX.adjust(BX("task-new-item-link-group"), {
						text: BX.message("TASKS_QUICK_IN_GROUP")
					});
					groupsPopup.deselect(groups[0].id);
					newTaskGroup = 0;
					newTaskGroupObj = null;
				}
			}
		});

		newTaskGroup = groups[0].id;
		newTaskGroupObj = groups[0];
	}
}

function onPopupTaskChanged(task) {
	tasksMenuPopup[task.id] = task.menuItems;
	quickInfoData[task.id] = task;

	var row = BX("task-" + task.id, true);
	if (row)
	{
		var a = BX.findChild(row,  {tagName: "a", className: "task-title-link"}, true);
		var cells = row.getElementsByTagName("TD");
		
		BX.adjust(a, {text: BX.util.htmlspecialcharsback(task.name)});

		cells[3].innerHTML = __renderPriority(task);
		cells[4].innerHTML = __renderDeadline(task);
	
		// Special format in tasks list for russian version
		var directorName    = task.director;
		var responsibleName = task.responsible;
		if (BX.message('LANGUAGE_ID') === 'ru')
		{
			if (
				task.hasOwnProperty('director_last_name')
				&& task.hasOwnProperty('director_name')
				&& (task.director_last_name.length + task.director_name.length)
			)
			{
				directorName = task.director_last_name + ' ' + task.director_name.substr(0, 1) + '.';
			}

			if (
				task.hasOwnProperty('responsible_last_name')
				&& task.hasOwnProperty('responsible_name')
				&& (task.responsible_last_name.length + task.responsible_name.length)
			)
			{
				responsibleName = task.responsible_last_name + ' ' + task.responsible_name.substr(0, 1) + '.';
			}
		}

		BX.cleanNode(cells[5]);
		BX.adjust(cells[5], {
			children: [
				BX.create("a", {
					props: {
						className: "task-responsible-link",
						href: BX.message("TASKS_PATH_TO_USER_PROFILE").replace("#user_id#", task.responsibleId),
						target: "_top",
						id: "anchor_responsible_" + task.id
					},
					text: BX.util.htmlspecialcharsback(responsibleName)
				})
			]
		});

		BX.cleanNode(cells[6]);
		BX.adjust(cells[6], {
			children: [
				BX.create("a", {
					props: {
						className: "task-director-link",
						href: BX.message("TASKS_PATH_TO_USER_PROFILE").replace("#user_id#", task.directorId),
						target: "_top",
						id: "anchor_created_" + task.id
					},
					text: BX.util.htmlspecialcharsback(directorName)
				})
			]
		});

		cells[7].innerHTML = __renderMark(task);

		switch(task.status){
			case "overdue":
				SetCSSStatus(row, "overdued", "task-status-");;
				break;
			case "new":
				SetCSSStatus(row, "new", "task-status-");
				cells[2].innerHTML = "<span class=\"task-flag-waiting-confirm\"></span>";
				break;
			case "in-progress":
				SetCSSStatus(row, "in-progress", "task-status-");
				cells[2].innerHTML = "<span class=\"task-flag-in-progress\"></span>";
				break;
				break;
			case "completed":
				SetCSSStatus(row, "completed", "task-status-");
				var cells = row.getElementsByTagName("TD");
				var title = BX.findChild(cells[0], {tagName : "a"}, true);

				BX.style(title, "text-decoration", "line-through");

				cells[2].innerHTML = "&nbsp;";
				var link = BX.findChild(cells[8], {tagName : "a"});
				link.onclick = null;
				link.title = BX.message("TASKS_FINISHED");
				break;
			case "accepted":
				cells[2].innerHTML = '<a href="javascript: void(0)" class="task-flag-begin-perform" onclick="StartTask(' + task.id + ')">';
				break;
			case "delayed":
			case "declined":
				SetCSSStatus(row, task.status, "task-status-");
				cells[2].innerHTML = "&nbsp;"
				break;
		}
		
		var currentParentId, currentParent, currentProjectId;
		var newDepth = 0;
		var currentDepth = Depth(row);
		var prevRow = row;

		if (currentDepth > 0 && (currentParent = BX.findPreviousSibling(row, {className: "task-depth-" + (currentDepth - 1)})))
		{
			currentParentId = currentParent.id.replace("task-", "");
		}
		
		if (currentParentId != task.parentTaskId)
		{
			if (currentParent)
			{
				var span = BX.findChild(currentParent, {tagName : "span"}, true);
				span.innerHTML = parseInt(span.innerHTML) - 1;
				if (parseInt(span.innerHTML) ==  0)
				{
					BX.remove(span.parentNode);
					BX.removeClass(currentParent, "task-list-item-opened");
				}
			}
			
			if (task.parentTaskId && BX("task-" + task.parentTaskId, true))
			{
				newDepth = Depth(BX("task-" + task.parentTaskId, true)) + 1;
			}
			else
			{
				newDepth = 0;
			}
			if (newDepth != currentDepth)
			{
				BX.removeClass(row, "task-depth-" + currentDepth);
				BX.addClass(row, "task-depth-" + newDepth);
			}
		}

		if (BX('task-current-project') && (BX('task-current-project').value > 0))
			currentProjectId = BX('task-current-project').value;
		else
		{
			do {
				if (prevRow.id.substr(0, 13) == "task-project-")
				{
					currentProjectId = prevRow.id.replace("task-project-", "");
				}
				prevRow = BX.findPreviousSibling(prevRow, {tagName: "tr"});
			} while (prevRow && !currentProjectId);
		}

		if (currentParentId != task.parentTaskId || (newDepth == 0 && currentProjectId != task.projectId))
		{
			var table = row.parentNode;
			beforeRow = __FindBeforeRow(table, task.parentTaskId, {id: task.projectId, title: task.projectName});

			if (beforeRow)
			{
				table.insertBefore(row, beforeRow);
			}
			else
			{
				table.appendChild(row);
			}
		}
	
		BX.tooltip(task.directorId, "anchor_created_" + task.id, "");
		BX.tooltip(task.responsibleId, "anchor_responsible_" + task.id, "");
	}
}


function onPopupTaskAdded(task, action, params)
{
	if (typeof(detailTaksID) == "undefined" || detailTaksID == task.parentTaskId)
	{
		tasksMenuPopup[task.id] = task.menuItems;
		quickInfoData[task.id] = task;
		BX.onCustomEvent("onTaskListTaskAdd", [task]);

		var multipleHtml = '';

		if (task.hasChildren)
			multipleHtml = '<span class="task-title-multiple" title="' + BX.message('TASKS_MULTITASK') + '"></span>';

		// Special format in tasks list for russian version
		var directorName    = task.director;
		var responsibleName = task.responsible;
		if (BX.message('LANGUAGE_ID') === 'ru')
		{
			if (
				task.hasOwnProperty('director_last_name')
				&& task.hasOwnProperty('director_name')
				&& (task.director_last_name.length + task.director_name.length)
			)
			{
				directorName = task.director_last_name + ' ' + task.director_name.substr(0, 1) + '.';
			}

			if (
				task.hasOwnProperty('responsible_last_name')
				&& task.hasOwnProperty('responsible_name')
				&& (task.responsible_last_name.length + task.responsible_name.length)
			)
			{
				responsibleName = task.responsible_last_name + ' ' + task.responsible_name.substr(0, 1) + '.';
			}
		}

		var row = BX.create("tr", {
			props: {
				className: "task-list-item task-depth-" + (task.parentTaskId && BX("task-" + task.parentTaskId) ? Depth(BX("task-" + task.parentTaskId)) + 1 : 0) + " task-status-" + task.status,
				ondblclick: "ShowPopupTask('" + task.id + "', event);",
				oncontextmenu: "return ShowMenuPopupContext(" + task.id + ", event);",
				title: BX.message("TASKS_DOUBLE_CLICK")
			},
			attrs: {
				id: "task-" + task.id
			},
			children: [
				BX.create("td", {
					props: {className: "task-title-column"},
					html: '\
						<div class="task-title-container">\
							<div class="task-title-info">\
								' + multipleHtml + '<a href="' + BX.message("TASKS_PATH_TO_TASK").replace("#action#", "view").replace("#task_id#", task.id) + '" class="task-title-link" onmouseover="ShowTaskQuickInfo(' + task.id + ', event);" onmouseout="HideTaskQuickInfo(' + task.id + ', event);" onclick="ShowPopupTask(' + task.id + ', event);">' + task.name + '</a>\
							</div>\
						</div>\
					'
				}),
				BX.create("td", {
					props: {className: "task-menu-column"},
					html: '\
						<a href="javascript: void(0)" class="task-menu-button" onclick="return ShowMenuPopup(' + task.id + ', this);" title="' + BX.message("TASKS_MENU") + '">\
							<i class="task-menu-button-icon"></i>\
						</a>\
					'
				}),
				BX.create("td", {
					props: {className: "task-flag-column"},
					html: __renderFlag(task)
				}),
				BX.create("td", {
					props: {className: "task-priority-column"},
					html: __renderPriority(task)
				}),
				BX.create("td", {
					props: {className: "task-deadline-column"},
					html: __renderDeadline(task)
				}),
				BX.create("td", {
					props: {className: "task-responsible-column"},
					html: '<a class="task-responsible-link" target="_top" href="'
						+ BX.message("TASKS_PATH_TO_USER_PROFILE").replace("#user_id#", task.responsibleId) 
						+ '" id="anchor_responsible_' 
						+ task.id + '">' 
						+ responsibleName
						+ '</a>'
				}),
				BX.create("td", {
					props: {className: "task-director-column"},
					html: '<a class="task-director-link" target="_top" href="' 
						+ BX.message("TASKS_PATH_TO_USER_PROFILE").replace("#user_id#", task.directorId) 
						+ '" id="anchor_created_' 
						+ task.id 
						+ '">' 
						+ directorName
						+ '</a>'
				}),
				BX.create("td", {
					props: {className: "task-grade-column"},
					html: __renderMark(task)
				}),
				BX.create("td", {
					props: {className: "task-complete-column"},
					html: '<a class="task-complete-action" href="javascript: void(0)" onclick="CloseTask(' + task.id + ')" title="' + BX.message("TASKS_FINISH") + '"></a>'
				})
			]
		});

		var table = BX("task-new-item-row").parentNode;
		beforeRow = __FindBeforeRow(table, task.parentTaskId, {id: task.projectId, title: task.projectName});

		if (beforeRow)
		{
			table.insertBefore(row, beforeRow);
		}
		else
		{
			table.appendChild(row);
		}

		if (BX("task-detail-subtasks-block") && BX("task-detail-subtasks-block").style.display == "none")
		{
			BX("task-detail-subtasks-block").style.display = "";
			BX("task-list-no-tasks").style.display = "none";
		}

		BX.tooltip(task.directorId, "anchor_created_" + task.id, "");
		BX.tooltip(task.responsibleId, "anchor_responsible_" + task.id, "");

		if ((typeof(params) == 'object') && (params !== null))
		{
			if (typeof(params.callbackOnAfterAdd) == 'function')
				params.callbackOnAfterAdd();
		}
	}
	if (BX("task-list-no-tasks"))
	{
		BX("task-list-no-tasks").style.display = "none";
	}
}

function onPopupTaskDeleted(taskId) {
	__DeleteTaskRow(taskId);
}

function __renderMark(task)
{
	if ((task.directorId == currentUser || task.isSubordinate) && task.responsibleId != currentUser)
	{
		return '<a href="javascript: void(0)" class="task-grade-and-report' + (task.mark ? ' task-grade-' + (task.mark == "N" ? "minus" : "plus") : "") + (task.isInReport ? " task-in-report" : "") + '" onclick="return ShowGradePopup(' + task.id + ', this, {listValue : \'' + (task.mark ? task.mark : "NULL") + '\'' + (task.isSubordinate ? ", report : " + (task.isInReport ? "true" : "false") : "") + '});" title="' + BX.message("TASKS_MARK") + ': ' + BX.message("TASKS_MARK_" + (task.mark ? task.mark : "NONE")) + '"><span class="task-grade-and-report-inner"><i class="task-grade-and-report-icon"></i></span></a>';
	}
	else
	{
		return '&nbsp;';
	}
}

function __renderPriority(task)
{
	if (currentUser == task.directorId)
	{
		return '<a href="javascript: void(0)" class="task-priority-box" onclick="return ShowPriorityPopup(' + task.id + ', this, ' + task.priority + ');" title="' + BX.message("TASKS_PRIORITY") + ': ' + BX.message("TASKS_PRIORITY_" + task.priority) + '"><i class="task-priority-icon task-priority-' + (task.priority == 2 ? "high" : (task.priority == 0 ? "low" : "medium")) + '"></i></a>';
	}
	else
	{
		return '<i class="task-priority-icon task-priority-' + (task.priority == 2 ? "high" : (task.priority == 0 ? "low" : "medium") + '" title="' + BX.message("TASKS_PRIORITY") + ': ' + BX.message("TASKS_PRIORITY_" + task.priority)) + '"></i>';
	}
}

function __renderDeadline(task)
{
	if (task.dateDeadline)
	{
		//jsCal endar. bTime = true;
		//var deadline = jsCal endar. ValueToString(task.dateDeadline);
		var deadline = BX.calendar.ValueToString(task.dateDeadline, true);
		if (BX.isAmPmMode())
		{
			deadline = deadline.substr(11, 11) == "12:00:00 am" ? deadline.substr(0, 10) : deadline.substr(0, 22);
		}
		else
		{
			deadline = deadline.substr(11, 8) == "00:00:00" ? deadline.substr(0, 10) : deadline.substr(0, 16);
		}
		deadline = deadline.split(' ');
		if (deadline.length > 1)
		{
			var date = deadline[0];
			delete(deadline[0]);
			var time = deadline.join(' ');
			deadline = '<span class="task-deadline-datetime"><span class="task-deadline-date">' + date + '</span></span><span class="task-deadline-time">' + time + '</span>';
		}
		else
			deadline = '<span class="task-deadline-date">' + deadline[0] + '</span>';
	}
	else
	{
		var deadline = "&nbsp;"
	}
	return deadline;
}

function __renderFlag(task)
{
	if (task.responsibleId == currentUser)
	{
		return '<a href="javascript: void(0)" class="task-flag-begin-perform" onclick="StartTask(' + task.id + ')"  title="' + BX.message("TASKS_START") + '">';
	}
	else if (task.status == "new")
	{
		return '<span class="task-flag-waiting-confirm"  title="' + BX.message("TASKS_WAINTING_CONFIRM") + '" />';
	}
	else
	{
		return '&nbsp;';
	}
}