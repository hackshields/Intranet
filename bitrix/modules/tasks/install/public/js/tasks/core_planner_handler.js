;(function(){

if(!!window.BX.CTasksPlannerHandler)
	return;

var
	BX = window.BX,
	TASK_SUFFIXES = {"-1": "overdue", "-2": "new", 1: "new", 2: "accepted", 3: "in-progress", 4: "waiting", 5: "completed", 6: "delayed", 7: "declined"},
	PLANNER_HANDLER = null;

BX.addTaskToPlanner = function(taskId)
{
	PLANNER_HANDLER.addTask({id:taskId});
}

BX.CTasksPlannerHandler = function()
{
	this.TASKS = null;
	this.TASKS_LIST = null;

	this.TASK_CHANGES = {add: [], remove: []};
	this.TASK_CHANGES_TIMEOUT = null;

	this.TASKS_WND = null;

	this.DATA_TASKS = null;

	this.PLANNER = null;

	BX.addCustomEvent('onPlannerDataRecieved', BX.proxy(this.draw, this));
};

BX.CTasksPlannerHandler.prototype.draw = function(obPlanner, DATA)
{
	if(!DATA.TASKS_ENABLED)
		return;

	this.PLANNER = obPlanner;

	if (null == this.TASKS)
	{
		this.TASKS = BX.create('DIV');

		this.TASKS.appendChild(BX.create('DIV', {
			props: {className: 'tm-popup-section tm-popup-section-tasks'},
			children: [
				BX.create('SPAN', {
					props: {className: 'tm-popup-section-text'},
					text: BX.message('JS_CORE_PL_TASKS')
				}),
				BX.create('span', {
					props: {className: 'tm-popup-section-right-link'},
					events: {click: BX.proxy(this.showTasks, this)},
					text: BX.message('JS_CORE_PL_TASKS_CHOOSE')
				})
			]
		}));

		this.TASKS.appendChild(BX.create('DIV', {
			props: {className: 'tm-popup-tasks'},
			children: [
			(this.TASKS_LIST = BX.create('OL', {
				props: {
					className: 'tm-popup-task-list'
				}
			})),
			this.drawTasksForm(BX.proxy(this.addTask, this))
		]}));
	}
	else
	{
		BX.cleanNode(this.TASKS_LIST);
	}

	if (DATA.TASKS && DATA.TASKS.length > 0)
	{
		var LAST_TASK = null;

		BX.removeClass(this.TASKS, 'tm-popup-tasks-empty');

		for (var i=0,l=DATA.TASKS.length; i<l; i++)
		{
			var q = this.TASKS_LIST.appendChild(BX.create('LI', {
				props: {
					className: 'tm-popup-task tm-popup-task-status-' + TASK_SUFFIXES[DATA.TASKS[i].STATUS],
					bx_task_id: DATA.TASKS[i].ID
				},
				children:
				[
					BX.create('SPAN', {props: {className: 'tm-popup-task-icon'}}),
					BX.create('SPAN', {
						props: {
							className: 'tm-popup-task-name',
							BXPOPUPBIND: this.TASKS.firstChild
						},
						text: DATA.TASKS[i].TITLE,
						events: {click: BX.proxy(this.showTask, this)}
					}),
					BX.create('SPAN', {
						props: {className: 'tm-popup-task-delete'},
						events: {click: BX.proxy(this.removeTask, this)}
					})
				]
			}));

			if (DATA.TASK_LAST_ID && DATA.TASKS[i].ID == DATA.TASK_LAST_ID)
			{
				LAST_TASK = q;
			}
		}

		if (LAST_TASK)
		{
			setTimeout(BX.delegate(function()
			{
				if (LAST_TASK.offsetTop < this.TASKS_LIST.scrollTop || LAST_TASK.offsetTop + LAST_TASK.offsetHeight > this.TASKS_LIST.scrollTop + this.TASKS_LIST.offsetHeight)
				{
					this.TASKS_LIST.scrollTop = LAST_TASK.offsetTop - parseInt(this.TASKS_LIST.offsetHeight/2);
				}
			}, this), 10);
		}
	}
	else
	{
		BX.addClass(this.TASKS, 'tm-popup-tasks-empty');
	}

	this.DATA_TASKS = BX.clone(DATA.TASKS);

	obPlanner.addBlock(this.TASKS, 200);
};

BX.CTasksPlannerHandler.prototype.addTask = function(task_data)
{
	if(!!this.TASKS_LIST)
	{
		this.TASKS_LIST.appendChild(BX.create('LI', {
			props: {className: 'tm-popup-task'},
			text: task_data.name
		}));

		BX.removeClass(this.TASKS, 'tm-popup-tasks-empty');
	}

	var data = {action: 'add'};

	if(typeof task_data.id != 'undefined')
		data.id = task_data.id;
	if(typeof task_data.name != 'undefined')
		data.name = task_data.name;

	this.query(data);
};

BX.CTasksPlannerHandler.prototype.removeTask = function(e)
{
	this.query({action: 'remove', id: BX.proxy_context.parentNode.bx_task_id});
	BX.cleanNode(BX.proxy_context.parentNode, true);

	if(!this.TASKS_LIST.firstChild)
	{
		BX.addClass(this.TASKS, 'tm-popup-tasks-empty');
	}

	return BX.PreventDefault(e);
};

BX.CTasksPlannerHandler.prototype.showTasks = function()
{
	if (!this.TASKS_WND)
	{
		this.TASKS_WND = new BX.CTasksPlannerSelector({
			node: BX.proxy_context,
			onselect: BX.proxy(this.addTask, this)
		});
	}
	else
	{
		this.TASKS_WND.setNode(BX.proxy_context);
	}

	this.TASKS_WND.Show();
};

BX.CTasksPlannerHandler.prototype.showTask = function(e)
{
	var task_id = BX.proxy_context.parentNode.bx_task_id,
		tasks = this.DATA_TASKS,
		arTasks = [];

	if (tasks.length > 0)
	{
		for(var i=0; i<tasks.length; i++)
		{
			arTasks.push(tasks[i].ID);
		}

		taskIFramePopup.tasksList = arTasks;
		taskIFramePopup.view(task_id);
	}

	return false;
};

BX.CTasksPlannerHandler.prototype.drawTasksForm = function(cb)
{
	var handler = BX.delegate(function(e, bEnterPressed) {
		inp_Task.value = BX.util.trim(inp_Task.value);
		if (inp_Task.value && inp_Task.value!=BX.message('JS_CORE_PL_TASKS_ADD'))
		{
			cb({
				name: inp_Task.value
			});

			if (!bEnterPressed)
			{
				BX.addClass(inp_Task.parentNode, 'tm-popup-task-form-disabled')
				inp_Task.value = BX.message('JS_CORE_PL_TASKS_ADD');
			}
			else
			{
				inp_Task.value = '';
			}
		}

		return BX.PreventDefault(e);
	}, this);

	var inp_Task = BX.create('INPUT', {
		props: {type: 'text', className: 'tm-popup-task-form-textbox', value: BX.message('JS_CORE_PL_TASKS_ADD')},
		events: {
			keypress: function(e) {
				return (e.keyCode == 13) ? handler(e, true) : true;
			},
			blur: function() {
				if (this.value == '')
				{
					BX.addClass(this.parentNode, 'tm-popup-task-form-disabled');
					this.value = BX.message('JS_CORE_PL_TASKS_ADD');
				}
			},
			focus: function() {
				BX.removeClass(this.parentNode, 'tm-popup-task-form-disabled');
				if (this.value == BX.message('JS_CORE_PL_TASKS_ADD'))
					this.value = '';
			}
		}
	});

	BX.focusEvents(inp_Task);

	return BX.create('DIV', {
		props: {
			className: 'tm-popup-task-form tm-popup-task-form-disabled'
		},
		children: [
			inp_Task,
			BX.create('SPAN', {
				props: {className: 'tm-popup-task-form-submit'},
				events: {click: handler}
			})
		]
	});
};

BX.CTasksPlannerHandler.prototype.query = function(entry, callback)
{
	if (this.TASK_CHANGES_TIMEOUT)
	{
		clearTimeout(this.TASK_CHANGES_TIMEOUT);
	}

	if (typeof entry == 'object')
	{
		if(!!entry.id)
		{
			this.TASK_CHANGES[entry.action].push(entry.id);
		}

		if (entry.action == 'add')
		{
			if(!entry.id)
			{
				this.TASK_CHANGES.name = entry.name;
			}

			this.query();
		}
		else
		{
			this.TASK_CHANGES_TIMEOUT = setTimeout(
				BX.proxy(this.query, this), 1000
			);
		}
	}
	else
	{
		if(!!this.PLANNER)
		{
			this.DATA_TASKS = [];
			this.PLANNER.query('task', this.TASK_CHANGES);
		}
		else
		{
			BX.CPlanner.query('task', this.TASK_CHANGES);
		}
		this.TASK_CHANGES = {add: [], remove: []};
	}
};

BX.CTasksPlannerSelector = function(params)
{
	this.params = params;

	this.isReady = false;
	this.WND = BX.PopupWindowManager.create(
		'planner_tasks_selector_' + parseInt(Math.random() * 10000), this.params.node,
		{
			autoHide: true,
			closeByEsc: true,
			content: (this.content = BX.create('DIV')),
			buttons: [
				new BX.PopupWindowButtonLink({
					text : BX.message('JS_CORE_WINDOW_CLOSE'),
					className : "popup-window-button-link-cancel",
					events : {click : function(e) {this.popupWindow.close();return BX.PreventDefault(e);}}
				})
			]
		}
	);
};

BX.CTasksPlannerSelector.prototype.Show = function()
{
	if (!this.isReady)
	{
		var suffix = parseInt(Math.random() * 10000);
		window['PLANNER_ADD_TASK_' + suffix] = BX.proxy(this.setValue, this);

		return BX.ajax.get('/bitrix/tools/tasks_planner.php', {action:'list', suffix: suffix, sessid: BX.bitrix_sessid(), site_id: BX.message('SITE_ID')}, BX.proxy(this.Ready, this));
	}

	return this.WND.show();
};

BX.CTasksPlannerSelector.prototype.Hide = function()
{
	this.WND.close();
};

BX.CTasksPlannerSelector.prototype.Ready = function(data)
{
	this.content.innerHTML = data;

	this.isReady = true;
	this.Show();
};

BX.CTasksPlannerSelector.prototype.setValue = function(task)
{
	this.params.onselect(task)
	this.WND.close();
};

BX.CTasksPlannerSelector.prototype.setNode = function(node)
{
	this.WND.setBindElement(node);
};

PLANNER_HANDLER = new BX.CTasksPlannerHandler();
})();