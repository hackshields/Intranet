/* IM Mobile */
(function() {

if (BX.IM)
	return;

BX.MessengerMobile = function () {
	this.notifyLastId = 0;
	this.counterUserMessages = {};
	this.counterMessages = 0;
	this.counterNotifications = 0;
	this.openNotificationFlag = false;
	this.updateCountersTimeout = null;

	this.timeoutRefreshMessage = null;
	this.timeoutRefreshNotifications = null;
	this.timeoutReadMessage = null;
	this.timeoutSendInit = null;
	this.timeoutAnimation = null;

	this.messageTmpIndex = 0;

	this.sendAjaxTry = 0;
	this.sendMessageFlag = false;

	this.historyMessage = {};
	this.historyMessageCount = 20;
	this.historyPage = 1;

	this.audio = {};
	this.audio.newMessage = null;
	this.audio.send = null;
	this.audio.reminder = null;
	this.audio.ready = false;

	this.writing = null;
	this.writingList = {};
	this.writingListTimeout = {};
	this.writingSendList = {};
	this.writingSendListTimeout = {};
}

BX.MessengerMobile.prototype.init = function(params)
{
	BX.addCustomEvent("onPullError", BX.delegate(function(error) {
		if (error == 'AUTHORIZE_ERROR')
		{
			app.BasicAuth({success: BX.delegate(function(){
				setTimeout(BX.delegate(this.sendInit, this), 1000);
			}, this)});
		}
	}, this));

	BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", BX.delegate(function(params) {
		app.BasicAuth({success: BX.delegate(function(){
			setTimeout(BX.delegate(this.sendInit, this), 1000);
		}, this)});
	}, this));

	// temp function authorize
	BX.addCustomEvent("onImError", BX.delegate(function(params) {
		if (params.error == 'AUTHORIZE_ERROR')
		{
			app.BasicAuth();
		}
		else if (params.error == 'RECENT_RELOAD')
		{
			app.BasicAuth({success: BX.delegate(function(){
				setTimeout(BX.delegate(this.sendInit, this), 1000);
			}, this)});
		}
	}, this));

	BX.addCustomEvent("onPullEvent", BX.delegate(function(module_id,command,params) {
		if (module_id == "im")
		{
			if (command == 'readMessage')
			{
				this.counterUserMessages[params.userId] = 0;
				this.updateCounters();
			}
			else if (command == 'readMessageChat')
			{
				this.counterUserMessages['chat'+params.chatId] = 0;
				this.updateCounters();
			}
			else if (command == 'chatUserLeave')
			{
				if (params.userId == BX.message('USER_ID'))
				{
					this.counterUserMessages['chat'+params.chatId] = 0;
					this.updateCounters();
				}
			}
			else if (command == 'readNotify')
			{
				this.notifyLastId = parseInt(params.lastId);
				this.counterNotifications = 0;
				this.updateCounters();
			}
			else if (command == 'message' || command == 'messageChat')
			{
				var userId = params.MESSAGE.senderId;
				if (userId == BX.message('USER_ID'))
				{
					this.counterUserMessages[params.MESSAGE.recipientId] = 0;
					this.updateCounters();
					return ;
				}
				if (command == 'messageChat')
					userId = params.MESSAGE.recipientId;

				if (typeof(this.counterUserMessages[userId]) != 'undefined')
					this.counterUserMessages[userId]++;
				else
					this.counterUserMessages[userId]=1;

				app.getVar({'var' : 'PAGE_ID','from' : 'current','callback' : BX.delegate(function(PAGE_ID)
				{
					if (PAGE_ID == 'DIALOG'+userId)
						this.counterUserMessages[userId] = 0;
					else
						app.setVibrate();

					this.updateCounters();
				}, this)});
			}
			else if (command == 'notify')
			{
				clearTimeout(this.timeoutRefreshNotifications);
				this.timeoutRefreshNotifications = setTimeout(BX.delegate(function(){
					app.refreshPanelPage('notifications');
					//try{
					//	this.audio.reminder.play();
					//} catch(e) {};
					app.setVibrate();
				}, this), 500);

				lastId = parseInt(params.id);
				if (this.notifyLastId < lastId)
					this.notifyLastId = lastId;

				this.counterNotifications++;
				this.updateCounters();
			}
		}
	}, this));

	BX.addCustomEvent("onNotificationsLastId", BX.delegate(function(lastId) {
		lastId = parseInt(lastId);
		if (this.notifyLastId < lastId)
			this.notifyLastId = lastId;
	}, this));

	BX.addCustomEvent("onDialogOpen", BX.delegate(function(params) {
		this.counterUserMessages[params.id] = 0;
		this.updateCounters();
	}, this));


	BX.addCustomEvent("onOpenPush", function(push) {
		if (!(app.enableInVersion(2) && typeof(push) == 'object' && typeof(push.params) == 'string'))
			return false;

		if (push.params.substr(0,8) == 'IM_MESS_')
		{
			var userId = parseInt(push.params.substr(8));
			if (userId > 0)
			{
				app.getVar({'var' : 'PAGE_ID','from' : 'current','callback' : function(PAGE_ID){
					setTimeout(function(){
						app.closeMenu();
						if (PAGE_ID != 'DIALOG'+userId)
							app.openNewPage('/mobile/im/dialog.php?id='+userId);
					}, 500);
				}});
			}
		}
	});

	BX.addCustomEvent("onMessagesOpen", function() {
	});

	BX.addCustomEvent("onNotificationsOpen", BX.delegate(function() {
		if (this.openNotificationFlag)
			this.notifyViewed();
		else
		{
			this.openNotificationFlag = true;
			setTimeout(BX.delegate(function(){
				this.notifyViewed();
			}, this), 1000);
		}

		this.counterNotifications = 0;
		this.updateCounters();
	}, this));

	app.setPanelPages({
		'messages_page': "/mobile/im/newmessage.php",
		'messages_open_empty': true,
		'notifications_page': "/mobile/im/notify.php",
		'notifications_open_empty': true
	});
	app.refreshPanelPage('messages');

	this.sendInit();

	this.initAudio();
}

BX.MessengerMobile.prototype.initAudio = function()
{
	return;

	if (this.audio.ready)
		return;

	this.audio.ready = true;

	BX.ready(BX.delegate(function(){
		var divAudio = BX.create("div", { attrs : {style : "display: none"}, children: [
			this.audio.reminder = BX.create("audio", { props : {style : { display : "none" }}, children : [
				BX.create("source", { attrs : { src : "/bitrix/js/im/audio/reminder.ogg", type : "audio/ogg; codecs=vorbis" }}),
				BX.create("source", { attrs : { src : "/bitrix/js/im/audio/reminder.mp3", type : "audio/mpeg" }})
			]}),
			this.audio.newMessage = BX.create("audio", { props : {style : { display : "none" }}, children : [
				BX.create("source", { attrs : { src : "/bitrix/js/im/audio/new-message-2.ogg", type : "audio/ogg; codecs=vorbis" }}),
				BX.create("source", { attrs : { src : "/bitrix/js/im/audio/new-message-2.mp3", type : "audio/mpeg" }})
			]}),
			this.audio.send = BX.create("audio", { props : {style : { display : "none" }}, children : [
				BX.create("source", { attrs : { src : "/bitrix/js/im/audio/send.ogg", type : "audio/ogg; codecs=vorbis" }}),
				BX.create("source", { attrs : { src : "/bitrix/js/im/audio/send.mp3", type : "audio/mpeg" }})
			]})
		]});
		document.body.insertBefore(divAudio, document.body.firstChild);
	}, this));
}


BX.MessengerMobile.prototype.updateCounters = function()
{
	clearTimeout(this.updateCountersTimeout);
	this.updateCountersTimeout = setTimeout(BX.delegate(function(){
		this.counterMessages = 0;
		for(var i in this.counterUserMessages)
			this.counterMessages += parseInt(this.counterUserMessages[i]);

		app.setBadge(parseInt(this.counterMessages+this.counterNotifications));
		app.setCounters({
			'messages':this.counterMessages,
			'notifications':this.counterNotifications
		});
	}, this), 500);
}
BX.MessengerMobile.prototype.sendMessage = function(recipientId, messageText)
{
	var userIsChat = false;
	if (recipientId.toString().substr(0,4) == 'chat')
	{
		userIsChat = true;
		var chatId = recipientId.toString().substr(4);
		if (parseInt(chatId) <= 0)
			return false;
	}
	else
	{
		if (parseInt(recipientId) <= 0)
			return false;
	}

	if (typeof(messageText) != 'undefined')
	{
		messageText = BX.util.trim(messageText+'');
	}
	else
	{
		var messengerTextarea = BX('send-message-input');
		messageText = BX.util.trim(messengerTextarea.value);
		messengerTextarea.value = '';
		messengerTextarea.blur();
	}

	if (messageText.length == 0)
		return false;

	var messageTmpIndex = this.messageTmpIndex;
	this.drawMessage({
		'id' : 'temp'+messageTmpIndex,
		'senderId' : BX.message('USER_ID'),
		'recipientId' : recipientId,
		'date' : BX.message('IM_MESSENGER_DELIVERED'),
		'text' : BX.MessengerMobile.prepareText(messageText, true, true)
	});
	if (!userIsChat)
		this.endSendWriting(recipientId);
	this.messageTmpIndex++;

	this.sendMessageAjax(messageTmpIndex, recipientId, messageText, userIsChat);

	return false;
}

BX.MessengerMobile.prototype.sendMessageAjax = function(messageTmpIndex, recipientId, messageText, sendMessageToChat)
{
	BX.addClass(BX('messagetemp'+messageTmpIndex, true), 'im-block-load');
	BX('messagetemp'+messageTmpIndex, true).appendChild(BX.create('div', {props : { className : "im-message-loading" }}));

	this.sendMessageFlag = true;
	BX.ajax({
		url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {'IM_SEND_MESSAGE' : 'Y', 'CHAT': sendMessageToChat? 'Y': 'N', 'ID' : 'temp'+messageTmpIndex, 'RECIPIENT_ID' : recipientId, 'MESSAGE' : messageText, 'TAB' : recipientId, 'MOBILE': 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			if (typeof(data) == 'undefined')
				data = {'ERROR': BX.message('IM_MESSENGER_NOT_DELIVERED')};

			this.sendMessageFlag = false;

			BX.removeClass(BX('messagetemp'+messageTmpIndex, true), 'im-block-load');
			if (data.ERROR.length == 0)
			{
				this.sendAjaxTry = 0;
				app.onCustomEvent('onMessageAdd', {'userId': recipientId, 'text': data.SEND_MESSAGE});

				var textElement = BX.findChild(BX('messagetemp'+messageTmpIndex), {className : "im-block-text"}, true);
				if (textElement)
					textElement.innerHTML =  data.SEND_MESSAGE;

				var lastMessageElementDate = BX.findChild(BX('messagetemp'+messageTmpIndex), {className : "im-block-info"}, true);
				if (lastMessageElementDate)
				{
					lastMessageElementDate.innerHTML = "";
					lastMessageElementDate.innerHTML = data.SEND_DATE_FORMAT;
				}

				BX('messagetemp'+messageTmpIndex).setAttribute('id', 'message'+data.ID)
			}
			else if (data.ERROR == 'AUTHORIZE_ERROR' && this.sendAjaxTry <= 5)
			{
				this.sendAjaxTry++;
				app.BasicAuth({
					success: BX.delegate(function(){
						this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
					}, this),
					failture: BX.delegate(function(){
						setTimeout(BX.delegate(function(){
							this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
						}, this), 3000);
					}, this)
				});
			}
			else if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 3)
			{
				this.sendAjaxTry++;
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				setTimeout(BX.delegate(function(){
					this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
				}, this), 1000);
			}
			else
			{
				BX.addClass(BX('messagetemp'+messageTmpIndex, true), 'im-undelivered');
				BX('messagetemp'+messageTmpIndex, true).appendChild(BX.create('div', {props : { className : "im-undelivered-message-icon" }}));

				var reason = data.ERROR;
				if (data.ERROR == 'SESSION_ERROR' || data.ERROR == 'AUTHORIZE_ERROR' || data.ERROR == 'UNKNOWN_ERROR' || data.ERROR == 'IM_MODULE_NOT_INSTALLED')
					reason = BX.message('IM_MESSENGER_NOT_DELIVERED');

				var node = BX.create('div', {props : { className : "im-undelivered-btn-block" }, children: [
					BX.create('input', {
						attrs : {type: 'button', value : BX.message('IM_MESSENGER_ND_RETRY')},
						props : { className : "im-undelivered-btn" },
						events : {
							touchend: BX.delegate(function(){
								BX.proxy_context.classList.remove('im-undelivered-btn-press');
								BX.removeClass(BX('messagetemp'+messageTmpIndex, true), 'im-undelivered');
								BX.remove(BX.proxy_context.parentNode);
								var undeliveredMessageIcon = BX.findChild(BX('messagetemp'+messageTmpIndex, true), {className : "im-undelivered-message-icon"}, true);
								if (undeliveredMessageIcon)
								{
									BX.remove(undeliveredMessageIcon);
								}
								this.sendAjaxTry = 0;
								this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
							}, this),
							touchstart: function(){ this.classList.add('im-undelivered-btn-press');}
						}
					}),
					BX.create('span', {props : { className : "im-undelivered-text" }, html: reason})
				]});
				BX("im-blocks", true).insertBefore(node, BX('messagetemp'+messageTmpIndex, true).nextSibling);

				this.sendAjaxTry = 0;
				var lastMessageElementDate = BX.findChild(BX('messagetemp'+messageTmpIndex, true), {className : "im-block-info"}, true);
				if (lastMessageElementDate)
				{
					lastMessageElementDate.innerHTML = "";
					lastMessageElementDate.innerHTML = BX.MessengerMobile.formatDate(BX.MessengerMobile.getNowDate());
				}
			}
		}, this),
		onfailure: BX.delegate(function()
		{
			BX.addClass(BX('messagetemp'+messageTmpIndex, true), 'im-undelivered');
			BX('messagetemp'+messageTmpIndex, true).appendChild(BX.create('div', {props : { className : "im-undelivered-message-icon" }}));

			var reason = BX.message('IM_MESSENGER_NOT_DELIVERED');

			var node = BX.create('div', {props : { className : "im-undelivered-btn-block" }, children: [
				BX.create('input', {
					attrs : {type: 'button', value : BX.message('IM_MESSENGER_ND_RETRY')},
					props : { className : "im-undelivered-btn" },
					events : {
						touchend: BX.delegate(function(){
							BX.proxy_context.classList.remove('im-undelivered-btn-press');
							BX.removeClass(BX('messagetemp'+messageTmpIndex, true), 'im-undelivered');
							BX.remove(BX.proxy_context.parentNode);
							var undeliveredMessageIcon = BX.findChild(BX('messagetemp'+messageTmpIndex, true), {className : "im-undelivered-message-icon"}, true);
							if (undeliveredMessageIcon)
							{
								BX.remove(undeliveredMessageIcon);
							}
							this.sendAjaxTry = 0;
							this.sendMessageAjax(messageTmpIndex, recipientId, messageText, sendMessageToChat);
						}, this),
						touchstart: function(){ this.classList.add('im-undelivered-btn-press');}
					}
				}),
				BX.create('span', {props : { className : "im-undelivered-text" }, html: reason})
			]});
			BX("im-blocks", true).insertBefore(node, BX('messagetemp'+messageTmpIndex, true).nextSibling);

			this.sendAjaxTry = 0;
			var lastMessageElementDate = BX.findChild(BX('messagetemp'+messageTmpIndex, true), {className : "im-block-info"}, true);
			if (lastMessageElementDate)
			{
				lastMessageElementDate.innerHTML = "";
				lastMessageElementDate.innerHTML = BX.MessengerMobile.formatDate(BX.MessengerMobile.getNowDate());
			}
		}, this)
	});
}


BX.MessengerMobile.prototype.drawMessage = function(message, appendTop)
{
	appendTop = appendTop == true? true: false;
	if (typeof(USER) == undefined)
	{
		alert('ERROR: Array USER undefined!');
		return false;
	}
	if (message.senderId == 0)
	{
		var node = BX.create('div', {
			attrs : {
				id: 'message'+message.id
			},
			props : { className : "im-block im-block-system" },
			html:
				'<div class="im-block-cont">\
					<div class="im-block-title"></div>\
					<div class="im-block-text">'+message.text+'</div>\
					<div class="im-block-info">'+(parseInt(message.date) > 0 ? BX.MessengerMobile.formatDate(message.date): message.date)+'</div>\
				</div>'
		});
	}
	else
	{
		var node = BX.create('div', {
			attrs : {
				id: 'message'+message.id
			},
			props : { className : "im-block im-block-"+(message.senderId == BX.message('USER_ID')? 'out': 'in') },
			html:
				'<div class="avatar"><div class="im-avatar" style="background-image:url(\''+(USERS[message.senderId]['avatar'])+'\'); background-size:cover;"></div></div>\
				<div class="im-block-cont">\
					<div class="im-block-title">'+USERS[message.senderId]['name']+'</div>\
					<div class="im-block-text">'+message.text+'</div>\
					<div class="im-block-info">'+(parseInt(message.date) > 0 ? BX.MessengerMobile.formatDate(message.date): message.date)+'</div>\
				</div>'
		});
	}
	var emptyBlock = BX('im-block-empty');
	if (emptyBlock != null)
	{
		BX.remove(emptyBlock);
	}
	var container = BX("im-blocks", true);
	var scrollContainer = null;
	if(window.platform == "android" && app.enableInVersion(4))
		scrollContainer = document.body;
	else
		scrollContainer = container;

	if (appendTop)
	{
		if (container.firstChild)
			container.insertBefore(node, container.firstChild);
		else
			container.insertBefore(node, this.writing);
	}
	else
	{
		container.insertBefore(node, this.writing);
		this.timeoutAnimation = setTimeout(function(){
			BitrixAnimation.animate({
				duration : 1000,
				start : { scroll : scrollContainer.scrollTop },
				finish : { scroll : scrollContainer.scrollHeight - scrollContainer.offsetHeight },
				transition : BitrixAnimation.makeEaseOut(BitrixAnimation.transitions.quart),
				step : function(state)
				{
					scrollContainer.scrollTop = state.scroll;
				},
				complete : function(){}
			});
		}, 10);
	}
	return node.offsetHeight+20;
}
BX.MessengerMobile.prototype.notifyViewed = function()
{
	if (parseInt(this.notifyLastId) <= 0)
		return false;

	BX.ajax({
		url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {'IM_NOTIFY_VIEWED' : 'Y', 'MAX_ID' : parseInt(this.notifyLastId), 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			if (data.ERROR.length == 0)
			{
				this.sendAjaxTry = 0;
				this.notifyLastId = 0;
			}
			else if (data.ERROR == 'AUTHORIZE_ERROR' && this.sendAjaxTry <= 3)
			{
				this.sendAjaxTry++;
				app.onCustomEvent('onImError', {error: data.ERROR});

				setTimeout(BX.delegate(function(){
					this.notifyViewed();
				}, this), 2000);
			}
			else if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 3)
			{
				this.sendAjaxTry++;
				BX.message({'bitrix_sessid': data.BITRIX_SESSID});
				setTimeout(BX.delegate(function(){
					this.notifyViewed();
				}, this), 1000);
			}
			else
			{
				this.sendAjaxTry = 0;
			}
		}, this),
		onfailure: BX.delegate(function(data){
			this.sendAjaxTry = 0;
		}, this)
	});

	return true;
}

BX.MessengerMobile.prototype.readMessage = function(userId, lastId)
{
	lastId = parseInt(lastId)>0? parseInt(lastId): 'N';

	clearTimeout(this.timeoutReadMessage);
	this.timeoutReadMessage = setTimeout(function(){
		BX.ajax({
			url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {'IM_READ_MESSAGE' : 'Y', 'USER_ID' : userId, 'LAST_ID' : lastId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR.length == 0)
				{
					this.sendAjaxTry = 0;
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR' && this.sendAjaxTry <= 3)
				{
					this.sendAjaxTry++;
					app.onCustomEvent('onImError', {error: data.ERROR});

					setTimeout(BX.delegate(function(){
						this.readMessage(userId, lastId);
					}, this), 2000);
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 3)
				{
					this.sendAjaxTry++;
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});
					setTimeout(BX.delegate(function(){
						this.readMessage(userId, lastId);
					}, this), 1000);
				}
				else
				{
					this.sendAjaxTry = 0;
				}
			}, this),
			onfailure: BX.delegate(function(data){
				this.sendAjaxTry = 0;
			}, this)
		});
	}, 500);
}

BX.MessengerMobile.prototype.sendInit = function()
{
	clearTimeout(this.timeoutSendInit);
	this.timeoutSendInit = setTimeout(BX.delegate(function()
	{
		BX.ajax({
			url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
			method: 'POST',
			dataType: 'json',
			timeout: 20,
			data: {'IM_UPDATE_STATE_LIGHT' : 'Y', 'SITE_ID' : BX.message('SITE_ID'), 'NOTIFY':'Y', 'MESSAGE':'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(data)
			{
				if (data.ERROR.length == 0)
				{
					if (BX.PULL && data.PULL_CONFIG)
						BX.PULL.updateChannelID(data.PULL_CONFIG.METHOD, data.PULL_CONFIG.CHANNEL_ID, data.PULL_CONFIG.PATH, data.PULL_CONFIG.LAST_ID, data.PULL_CONFIG.PATH_WS);

					if (data.COUNTER_MESSAGES)
						this.counterMessages = parseInt(data.COUNTER_MESSAGES);
					if (data.COUNTER_NOTIFICATIONS)
						this.counterNotifications = parseInt(data.COUNTER_NOTIFICATIONS);
					if (data.NOTIFY_LAST_ID)
						this.notifyLastId = parseInt(data.NOTIFY_LAST_ID);

					if (this.counterMessages > 0 && data.COUNTER_UNREAD_MESSAGES && typeof(data.COUNTER_UNREAD_MESSAGES) == 'object')
					{
						this.counterMessages = 0;
						this.counterUserMessages = {};
						for (var i in data.COUNTER_UNREAD_MESSAGES)
						{
							this.counterMessages += data.COUNTER_UNREAD_MESSAGES[i].MESSAGE.counter;
							this.counterUserMessages[i] = data.COUNTER_UNREAD_MESSAGES[i].MESSAGE.counter;
						}
						app.onCustomEvent('onUpdateUserCounters', data.COUNTER_UNREAD_MESSAGES);
					}
					else
					{
						this.counterUserMessages = {};
						app.onCustomEvent('onUpdateUserCounters', data.COUNTER_UNREAD_MESSAGES);
					}
					this.updateCounters();

					if (data.COUNTERS && typeof(data.COUNTERS) == 'object')
						app.onCustomEvent('onUpdateSocnetCounters', data.COUNTERS);

					app.refreshPanelPage('notifications');
					this.sendAjaxTry = 0;

					if (BX.PULL)
					{
						if (!BX.PULL.tryConnect())
						{
							BX.PULL.updateState(true);
						}
					}

					clearTimeout(this.timeoutSendInit);
					this.timeoutSendInit = setTimeout(BX.delegate(function(){
						this.sendInit();
					}, this), 80000);
				}
				else if (data.ERROR == 'AUTHORIZE_ERROR' && this.sendAjaxTry <= 3)
				{
					this.sendAjaxTry++;
					app.onCustomEvent('onImError', {error: data.ERROR});

					clearTimeout(this.timeoutSendInit);
					this.timeoutSendInit = setTimeout(BX.delegate(function(){
						this.sendInit();
					}, this), 2000);
				}
				else if (data.ERROR == 'SESSION_ERROR' && this.sendAjaxTry <= 3)
				{
					this.sendAjaxTry++;
					BX.message({'bitrix_sessid': data.BITRIX_SESSID});

					clearTimeout(this.timeoutSendInit);
					this.timeoutSendInit = setTimeout(BX.delegate(function(){
						this.sendInit();
					}, this), 1000);
				}
				else
				{
					this.sendAjaxTry = 0;
				}
			}, this),
			onfailure: BX.delegate(function(data){
				this.sendAjaxTry = 0;
			}, this)
		});
	}, this), 300);
}

BX.MessengerMobile.prototype.getHistory = function(userId)
{
	this.historyPage = Math.floor(this.historyMessageCount/20)+1;
	BX.ajax({
		url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
		method: 'POST',
		dataType: 'json',
		data: {'IM_HISTORY_LOAD_MORE' : 'Y', 'USER_ID' : userId, 'PAGE_ID' : this.historyPage, 'MOBILE': 'Y', 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: BX.delegate(function(data)
		{
			var count = 0;
			var height = 0;
			var heightFirst = 0;
			for (var i in data.MESSAGE)
			{
				if (this.historyMessage[data.MESSAGE[i].id])
					continue;
				data.MESSAGE[i].date = parseInt(data.MESSAGE[i].date)+parseInt(BX.message('USER_TZ_OFFSET'));
				data.MESSAGE[i].text = BX.MessengerMobile.prepareText(data.MESSAGE[i].text, true, true);

				height += this.drawMessage(data.MESSAGE[i], true);

				if (heightFirst==0)
					heightFirst = height;

				this.historyMessage[data.MESSAGE[i].id] = true;
				this.historyMessageCount++;
				count++;
			}
			app.pullDownLoadingStop();
			var container = BX("im-blocks", true);
			container.scrollTop = height;
			BitrixAnimation.animate({
				duration : 1500,
				start : { scroll : container.scrollTop },
				finish : { scroll : height-heightFirst},
				transition : BitrixAnimation.makeEaseOut(BitrixAnimation.transitions.quart),
				step : function(state)
				{
					container.scrollTop = state.scroll;
				},
				complete : function(){ }
			});

			if (count < 20)
			{
				app.pullDown({'enable': false});
				return;
			}
		}, this),
		onfailure: function(data){
			app.pullDownLoadingStop();
		}
	});
}
BX.MessengerMobile.getNowDate = function()
{
	return Math.round((+(new Date)/1000)+(new Date).getTimezoneOffset()*60)+parseInt(BX.message("SERVER_TZ_OFFSET"))+parseInt(BX.message("USER_TZ_OFFSET"));
}

BX.MessengerMobile.formatDate = function(timestamp)
{
	var format = [
		["tommorow", BX.message("IM_FORMAT_DATETIME_TOMMOROW")],
		["today", BX.message("IM_FORMAT_DATETIME_TODAY")],
		["yesterday", BX.message("IM_FORMAT_DATETIME_YESTERDAY")],
		["", BX.date.convertBitrixFormat(BX.message("IM_FORMAT_DATETIME"))]
	];
	return BX.date.format(format, parseInt(timestamp)+parseInt(BX.message("SERVER_TZ_OFFSET")), BX.MessengerMobile.getNowDate(), true);
}

BX.MessengerMobile.prepareText = function(text, prepare, quote)
{
	prepare = prepare == true? true: false;
	quote = quote == true? true: false;

	text = BX.util.trim(text);
	if (prepare)
		text = BX.util.htmlspecialchars(text);

	if (quote)
	{
		text = text.replace(/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/g, "<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\"><div class=\"bx-messenger-content-quote-name\">$1 <span class=\"bx-messenger-content-quote-time\">$2</span></div>$3</div></div>");
		text = text.replace(/------------------------------------------------------<br \/>(.*?)<br \/>------------------------------------------------------(<br \/>)?/g, "<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">$1</div></div>");
	}
	if (prepare)
		text = text.replace(/\n/gi, '<br />');

	text = text.replace(/\t/gi, '&nbsp;&nbsp;&nbsp;&nbsp;');

	return text;
}

BX.MessengerMobile.prototype.startWriting = function(userId)
{
	this.writingList[userId] = true;
	this.drawWriting(userId);
	clearTimeout(this.writingListTimeout[userId]);
	this.writingListTimeout[userId] = setTimeout(BX.delegate(function(){
		this.endWriting(userId);
	}, this), 30000);
}
BX.MessengerMobile.prototype.drawWriting = function(userId)
{
	if (this.writingList[userId] && DIALOG_ID == userId)
	{
		BX.addClass(this.writing, 'im-block-writing-write');
		this.writing.innerHTML = BX.message('IM_MESSENGER_WRITING').replace('#USER_NAME#', '<b>'+USERS[userId].name+'</b>');
	}
	else if (!this.writingList[userId] && DIALOG_ID == userId)
	{
		BX.removeClass(this.writing, 'im-block-writing-write');
	}
}
BX.MessengerMobile.prototype.endWriting = function(userId, fast)
{
	fast = fast == true? true: false;

	clearTimeout(this.writingListTimeout[userId]);
	this.writingList[userId] = false;
	this.drawWriting(userId);

	if (fast)
		this.writing.innerHTML = '';
}
BX.MessengerMobile.prototype.sendWriting = function(userId)
{
	if (parseInt(userId) > 0 && !this.writingSendList[userId])
	{
		clearTimeout(this.writingSendListTimeout[userId]);
		this.writingSendList[userId] = true;
		BX.ajax({
			url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
			method: 'POST',
			dataType: 'json',
			data: {'IM_START_WRITING' : 'Y', 'RECIPIENT_ID' : DIALOG_ID, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()}
		});
		this.writingSendListTimeout[userId] = setTimeout(BX.delegate(function(){
			this.endSendWriting(userId);
		}, this), 30000);
	}
}
BX.MessengerMobile.prototype.endSendWriting = function(userId)
{
	if (parseInt(userId) <= 0)
		return false;

	clearTimeout(this.writingSendListTimeout[userId]);
	this.writingSendList[userId] = false;
}

BX.MessengerMobile.prototype.leaveFromChat = function(chatId)
{
	BX.ajax({
		url: '/bitrix/components/bitrix/im.messenger/im.ajax.php',
		method: 'POST',
		dataType: 'json',
		timeout: 60,
		data: {'IM_CHAT_LEAVE' : 'Y', 'CHAT_ID' : chatId, 'IM_AJAX_CALL' : 'Y', 'sessid': BX.bitrix_sessid()},
		onsuccess: function(data){
			if (data.ERROR == '')
			{
				app.closeController();
			}
		}
	});
}

BX.IM = new BX.MessengerMobile;
window.BX.IM = BX.IM;
})();