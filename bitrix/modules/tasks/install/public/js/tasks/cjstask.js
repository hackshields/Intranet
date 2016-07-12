(function() {

if (BX.CJSTask)
	return;

BX.CJSTask = {
	ajaxUrl    : '/bitrix/components/bitrix/tasks.iframe.popup/ajax.php',
	sequenceId : 0
};


BX.CJSTask.createItem = function(newTaskData, params)
{
	var params = params || null;

	var postData = {
		sessid : BX.message('bitrix_sessid'),
		batch  : [
			{
				operation : 'CTaskItem::add()',
				taskData  :  newTaskData
			},
			{
				operation : 'CTaskItem::getTaskData()',
				taskData  : {
					ID : '#RC#$arOperationsResults#-1#justCreatedTaskId'
				}
			},
			{
				operation : 'CTaskItem::getAllowedTaskActions()',
				taskData  : {
					ID : '#RC#$arOperationsResults#-1#returnValue#ID'
				}
			},
			{
				operation : 'NOOP'
			},
			{
				operation : 'CTaskItem::getAllowedTaskActionsAsStrings()',
				taskData  : {
					ID : '#RC#$arOperationsResults#-3#returnValue#ID'
				}
			},
			{
				operation : 'tasksRenderJSON() && tasksRenderListItem()',
				taskData  : {
					ID : '#RC#$arOperationsResults#-4#returnValue#ID'
				}
			}
		]
	};

	BX.ajax({
		method      : 'POST',
		dataType    : 'json',
		url         :  BX.CJSTask.ajaxUrl,
		data        :  postData,
		processData :  true,
		onsuccess   : (function(params) {
			var callbackOnSuccess = false;
			var callbackOnFailure = false;

			if (params)
			{
				if (params.callback)
					callbackOnSuccess = params.callback;

				if (params.callbackOnFailure)
					callbackOnFailure = params.callbackOnFailure;
			}

			return function(reply) {
				if ((reply.status === 'success') && (!!callbackOnSuccess))
				{
					var precachedData = {
						taskData                    : reply['data'][1]['returnValue'],
						allowedTaskActions          : reply['data'][2]['returnValue'],
						allowedTaskActionsAsStrings : reply['data'][4]['returnValue']
					}

					var oTask = new BX.CJSTask.Item(
						reply['data'][1]['returnValue']['ID'],
						precachedData
					);

					var legacyDataFormat = BX.parseJSON(reply['data'][5]['returnValue']['tasksRenderJSON']);
					var legacyHtmlTaskItem = reply['data'][5]['returnValue']['tasksRenderListItem'];

					callbackOnSuccess(oTask, precachedData, legacyDataFormat, legacyHtmlTaskItem);
				}
				else if ((reply.status !== 'success') && (!!callbackOnFailure))
				{
					var errMessages = [];
					var errorsCount = 0;

					if (
						(reply.repliesCount > 0)
						&& reply.data[reply.repliesCount - 1].hasOwnProperty('errors')
					)
					{
						errorsCount = reply.data[reply.repliesCount - 1].errors.length;

						for (var i = 0; i < errorsCount; i++)
							errMessages.push(reply.data[reply.repliesCount - 1].errors[i]['text']);
					}

					callbackOnFailure({
						rawReply    : reply,
						status      : reply.status,
						errMessages : errMessages
					});
				}
			}
		})(params)
	});
}


BX.CJSTask.Item = function(taskId, precachedData)
{
	if ( ! taskId )
		throw ('taskId must be set');

	if ( ! (taskId >= 1) )
		throw ('taskId must be >= 1');

	this.taskId = taskId;
	this.cachedData = {
		taskData                    : false,
		allowedTaskActions          : false,
		allowedTaskActionsAsStrings : false
	};

	if (precachedData)
	{
		if (precachedData.taskData)
			this.cachedData.taskData = precachedData.taskData;

		if (precachedData.allowedTaskActions)
			this.cachedData.allowedTaskActions = precachedData.allowedTaskActions;

		if (precachedData.allowedTaskActionsAsStrings)
			this.cachedData.allowedTaskActionsAsStrings = precachedData.allowedTaskActionsAsStrings;
	}


	this.getCachedData = function()
	{
		return (this.cachedData);
	};


	this.refreshCache = function(params)
	{
		var params = params || null;

		var postData = {
			sessid : BX.message('bitrix_sessid'),
			batch  : [
				{
					operation : 'CTaskItem::getTaskData()',
					taskData  : {
						ID : this.taskId
					}
				},
				{
					operation : 'CTaskItem::getAllowedTaskActions()',
					taskData  : {
						ID : this.taskId
					}
				},
				{
					operation : 'CTaskItem::getAllowedTaskActionsAsStrings()',
					taskData  : {
						ID : this.taskId
					}
				}
			]
		};

		BX.ajax({
			method      : 'POST',
			dataType    : 'json',
			url         :  BX.CJSTask.ajaxUrl,
			data        :  postData,
			processData :  true,
			onsuccess   : (function(params, objTask) {
				var callback = false;

				if (params && params.callback)
					callback = params.callback;

				return function(reply) {
					objTask.cachedData = {
						taskData                    : reply['data'][0]['returnValue'],
						allowedTaskActions          : reply['data'][1]['returnValue'],
						allowedTaskActionsAsStrings : reply['data'][2]['returnValue']
					}

					if (!!callback)
						callback(objTask.cachedData);
				}
			})(params, this)
		});
	};


	/**
	 * data is array with elements MINUTES, COMMENT_TEXT
	 */
	this.addElapsedTime = function(data, callbacks)
	{
		var elapsedTimeData = {
			TASK_ID      : this.taskId,
			MINUTES      : data.MINUTES,
			COMMENT_TEXT : data.COMMENT_TEXT
		};

		var batchId = BX.CJSTask.batchOperations(
			[
				{
					operation       : 'CTaskItem::addElapsedTime()',
					elapsedTimeData :  elapsedTimeData
				}
			],
			callbacks
		);

		return (batchId);
	}
}


BX.CJSTask.formatUsersNames = function(arUsersIds, params)
{
	var params = params || null;

	var userId = null;
	var batch  = [];

	for (var key in arUsersIds)
	{
		userId = arUsersIds[key];

		batch.push({
			operation : 'CUser::FormatName()',
			userData  :  { ID : userId }
		});
	}

	var postData = {
		sessid : BX.message('bitrix_sessid'),
		batch  : batch
	};

	BX.ajax({
		method      : 'POST',
		dataType    : 'json',
		url         :  BX.CJSTask.ajaxUrl,
		data        :  postData,
		processData :  true,
		onsuccess   : (function(params) {
			var callback = false;

			if (params && params.callback)
				callback = params.callback;

			return function(reply) {
				if (!!callback)
				{
					var replyItem = null;
					var result = {};
					var repliesCount = reply['repliesCount'];

					for (var i = 0; i < repliesCount; i++)
					{
						replyItem = reply['data'][i];
						result['u' + replyItem['requestedUserId']] = replyItem['returnValue'];
					}

					callback(result);
				}
			}
		})(params)
	});
}


BX.CJSTask.getGroupsData = function(arGroupsIds, params)
{
	var params = params || null;

	var groupId = null;
	var batch   = [];

	for (var key in arGroupsIds)
	{
		groupId = arGroupsIds[key];

		batch.push({
			operation : 'CSocNetGroup::GetByID()',
			groupData  :  { ID : groupId }
		});
	}

	var postData = {
		sessid : BX.message('bitrix_sessid'),
		batch  : batch
	};

	BX.ajax({
		method      : 'POST',
		dataType    : 'json',
		url         :  BX.CJSTask.ajaxUrl,
		data        :  postData,
		processData :  true,
		onsuccess   : (function(params) {
			var callback = false;

			if (params && params.callback)
				callback = params.callback;

			return function(reply) {
				if (!!callback)
				{
					var replyItem = null;
					var result = {};
					var repliesCount = reply['repliesCount'];

					for (var i = 0; i < repliesCount; i++)
					{
						replyItem = reply['data'][i];
						result[replyItem['requestedGroupId']] = replyItem['returnValue'];
					}

					callback(result);
				}
			}
		})(params)
	});
}


BX.CJSTask.batchOperations = function(batch, callbacks)
{
	var callbacks = callbacks || null;
	var batchId   = 'batch_sequence_No_' + (++BX.CJSTask.sequenceId);

	var postData = {
		sessid  : BX.message('bitrix_sessid'),
		batch   : batch,
		batchId : batchId
	};

	BX.ajax({
		method      : 'POST',
		dataType    : 'json',
		url         :  BX.CJSTask.ajaxUrl,
		data        :  postData,
		processData :  true,
		onsuccess   : (function(callbacks) {
			var callbackOnSuccess = false;
			var callbackOnFailure = false;

			if (callbacks)
			{
				if (callbacks.callbackOnSuccess)
					callbackOnSuccess = callbacks.callbackOnSuccess;

				if (callbacks.callbackOnFailure)
					callbackOnFailure = callbacks.callbackOnFailure;
			}

			return function(reply) {
				if ((reply.status === 'success') && (!!callbackOnSuccess))
				{
					callbackOnSuccess({
						rawReply : reply,
						status   : reply.status
					});
				}
				else if ((reply.status !== 'success') && (!!callbackOnFailure))
				{
					var errMessages = [];
					var errorsCount = 0;

					if (
						(reply.repliesCount > 0)
						&& reply.data[reply.repliesCount - 1].hasOwnProperty('errors')
					)
					{
						errorsCount = reply.data[reply.repliesCount - 1].errors.length;

						for (var i = 0; i < errorsCount; i++)
							errMessages.push(reply.data[reply.repliesCount - 1].errors[i]['text']);
					}

					callbackOnFailure({
						rawReply    : reply,
						status      : reply.status,
						errMessages : errMessages
					});
				}
			}
		})(callbacks)
	});

	return (batchId);
}

})();
