
var app = false;
BitrixMobile = function () {
	this.callbacks = {};
	this.callbackIndex = 0;
	this.dataBrigePath = "/mobile/";
	this.contacts = new BMContacts;
	this.available = false;
	this.platform = null;
};

//#############################
//#####--api version 5--#######
//#############################

BitrixMobile.prototype.removeTableCache = function(tableId)
{
    /**
     * use it to clear cache by tableid
     * in next time the table appear it will be reloaded
     */
	return this.exec("removeTableCache", {"table_id": tableId});
};

BitrixMobile.prototype.showDatePicker = function(params)
{
    /** use it to show native datetime picker
     * params description
     * format - date's format
     * type - "datetime"|"time"|"date"
     * callback - handler on date select event
     */
    return this.exec("showDatePicker", params);
};

BitrixMobile.prototype.hideDatePicker = function()
{
    /**
     * use it to hide native datetime picker
     */
    return this.exec("hideDatePicker");
};


//#############################
//#####--api version 4--#######
//#############################

BitrixMobile.prototype.showInput = function(params)
{
	//@params description
	/*
			placeholder - just a placeholder-text
			button_name - button's title
			action - javascript callback function

			Example:

			app.showInput({
				placeholder:"New message...",
				button_name:"Send",
				action:function(text)
				{
					app.clearInput();
					alert(text);
				},
			});
	*/
	return this.exec("showInput", params);
}

BitrixMobile.prototype.showInputLoading = function(loading_status)
{
	//use it to disable with activity indicator or enable button
	if(loading_status&&loading_status !== true)
		loading_status = false;
	return this.exec("showInputLoading", {"status":loading_status});

}

BitrixMobile.prototype.clearInput = function()
{
	//use it to clear text input
	return this.exec("clearInput");
}

BitrixMobile.prototype.hideInput = function()
{
	//use it to hide text input
	return this.exec("hideInput");
};

/*android picker
BitrixMobile.prototype.showDatePicker = function(options, cb) {

	//for Android only
	//options.date =
	if (this.platform.toUpperCase()	 != "ANDROID")
		return false;

	if (options.date) {
		options.date = (options.date.getMonth() + 1) + "/" + (options.date.getDate()) + "/" + (options.date.getFullYear()) + "/"
				+ (options.date.getHours()) + "/" + (options.date.getMinutes());
	}
	var defaults = {
		mode : '',
		date : '',
		allowOldDates : true
	};

	for ( var key in defaults) {
		if (typeof options[key] !== "undefined")
			defaults[key] = options[key];
	}

	this._callback = cb;

	return Cordova.exec(cb, failureCallback, 'BitrixMobile', 'showDatePicker', new Array(defaults));
};
*/
//#############################
//#####--api version 3--#######
//#############################
BitrixMobile.prototype.reload = function(params)
{
	var params = params || {url: document.location.href};

	if (window.platform == 'android')
		this.exec('reload', params);
	else
	{
		document.location.href = params.url;
	}
};


BitrixMobile.prototype.flipScreen = function()
{
	return this.exec("flipScreen");
};


BitrixMobile.prototype.removeButtons = function(params)
{
	/*
	var params = {
		position: 'left'	// allowed: left, right
	}
	*/

	return this.exec("removeButtons", params);
};


BitrixMobile.prototype.openBXTable = function(params)
{
    /**
     * open new list
     * params = {
          url:"/mobile/json-data.php"
          isroot: true|false
           TABLE_SETTINGS: {
                //table params
           }
     };
     * @table params description
         callback: handler on ok button tap action, it work only when 'markmode' is true
         markmode: set it true to turn on mark mode,false - by default
         modal: true - your table will be open in modal dialog, false - by default
         multiple: it works if 'markmode' is true, set it false to turn off multiple selection
         okname - name of ok button
         cancelname - name of cancel button
         showtitle: true - to make title visible, false - by default
         alphabet_index: if true - table will be divided on alphabetical sections
         selected: this is a start selected data in a table, for example {users:[1,2,3,4],groups:[1,2,3]}
         button:{
            name: "name",
            type: "plus",
            callback:function(){
                //your code
            }
         }
     */
	return this.exec("openBXTable", params);
};

BitrixMobile.prototype.openDocument = function(params)
{
	//{"url":"/upload/123.doc"}
	return this.exec("openDocument", params);
}

BitrixMobile.prototype.showPopupLoader = function(params)
{
	// show small loader on center of screen
	// loader will be automatically hided when "back page" button pressed
	/*
	var params = {
		text: text
	};
	*/
	return this.exec("showPopupLoader", params);
};

BitrixMobile.prototype.hidePopupLoader = function(params)
{
	// hide small loader on center of screen
	return this.exec("hidePopupLoader", params);
};

BitrixMobile.prototype.changeCurPageParams = function(params)
{
	// change params for current page, that can be getted by getPageParams()
	/*
	var params = {
		data: data,
		callback: callback
	};
	*/
	return this.exec("changeCurPageParams", params);
};

BitrixMobile.prototype.getPageParams = function(params) {
    /** use it get page params
     * callback - callback function
     */

	if ( ! this.enableInVersion(3) )
		return false;

	return this.exec("getPageParams", params);
};

BitrixMobile.prototype.menuCreate = function (params) {
	//use it to create popup menu for your page
	/*for example
	params = {
			items:[
				{
					name:"Post message",
					action:function() { postMessage();},
					image: "/upload/post_message_icon.phg"
				},
				{
					name:"To Bitrix!",
					url:"http://bitrix.ru",
					icon: 'settings'
				}
			]
		}

	Available icons:
		@"www/images/check-menu-icon.png", @"check",
		@"www/images/edit-menu-icon.png", @"edit",
		@"www/images/adduser-menu-icon.png", @"adduser",
		@"www/images/settings-menu-icon.png", @"settings",
		@"www/images/delete-menu-icon.png", @"delete",
		@"www/images/filter-menu-icon.png", @"filter",
		@"www/images/photo-menu-icon.png", @"photo",
		@"www/images/file-menu-icon.png", @"file",
		@"www/images/add-menu-icon.png", @"add",
		@"www/images/user-menu-icon.png", @"user",
		@"www/images/default-menu-icon.png", @"default",
		@"www/images/play-menu-icon.png", @"play",
		@"www/images/pause-menu-icon.png", @"pause",
		@"www/images/cancel-menu-icon.png", @"cancel"
		@"www/images/finish-menu-icon.png", @"finish"
		@"www/images/checkbox-menu-icon.png", @"checkbox"
	*/

	return this.exec("menuCreate", params);
};

BitrixMobile.prototype.menuShow = function () {
	//use it to show popup menu on your page
	return this.exec("menuShow");
};

BitrixMobile.prototype.menuHide = function () {
	//use it to show popup menu on your page
	return this.exec("menuHide");
};

//#############################
//#####--api version 2--#######
//#############################

BitrixMobile.prototype.enableInVersion = function(ver, strict){
	//check api version
	strict = strict == true? true: false;

	var api_version = 1;
	try{
		api_version = appVersion;
	}catch(e)
	{
		//do nothing
	}

	return strict? (parseInt(api_version)==parseInt(ver)?true:false): (parseInt(api_version)>=parseInt(ver)?true:false);
};

BitrixMobile.prototype.checkOpenStatus = function (params) {
	//check visibility status
	//params.callback
	return this.exec("checkOpenStatus", params);
};

BitrixMobile.prototype.asyncRequest = function (params) {
	//native asyncRequest
	//params.url
	return this.exec("asyncRequest", params);
};

//#############################
//#####--api version 1--#######
//#############################

BitrixMobile.prototype.openUrl = function (url) {
	//open url in external browser
	return this.exec("openUrl", url);
};

BitrixMobile.prototype.RegisterCallBack = function (func) {
	//register callback
	if (typeof(func) == "function") {
		this.callbackIndex++;

		this.callbacks["callback"+this.callbackIndex] = func;

		return this.callbackIndex;
	}

};

BitrixMobile.prototype.CallBackExecute = function(index, result) {
	//execute callback by register index
	//alert(this.callbacks["callback"+this.callbackIndex]);
	if (this.callbacks["callback"+index] && (typeof this.callbacks["callback"+index]) === "function") {
		this.callbacks["callback"+index](result);
	}
};

BitrixMobile.prototype.onCustomEvent = function(eventName, params, where){

	if(!this.available)
	{
		document.addEventListener("deviceready", BX.delegate(function() {
				this.onCustomEvent(eventName, params, where);
			}, this), false);

		return;
	}

	params = this.prepareParams(params);
	if(typeof(params) == "object")
		params = JSON.stringify(params);

	if(device.platform.toUpperCase() == "ANDROID")
	{
		var params_pre = {
			"eventName":eventName,
			"params":params
		};
		return Cordova.exec(null, null, "BitrixMobile", "onCustomEvent", [params_pre]);
	}
	else
		return Cordova.exec("BitrixMobile.onCustomEvent", eventName, params, where);
};

BitrixMobile.prototype.getVar = function(params) {
	//get variable from current and left
	//params desc
	//params.callback
	//params.var - variable's name
	//params.from - "left"|"current" ("center" is deprecated)

	return this.exec("getVar",params);
};

BitrixMobile.prototype.passVar = function(variable, key) {

	try
	{
		evalVar = window[variable];
		if(!evalVar)
			evalVar = "empty"
	}
	catch(e)
	{
		evalVar = "empty"
	}

	if (evalVar)
	{

		if (typeof(evalVar) == "object")
			evalVar = JSON.stringify(evalVar);

		if(platform.toUpperCase() == "ANDROID")
		{

			key = key||false;
			if(key)
				Bitrix24Android.receiveStringValue(JSON.stringify({variable: evalVar, key:key}));
			else
				Bitrix24Android.receiveStringValue(evalVar);
		} else {
			return evalVar;
		}
	}
};

BitrixMobile.prototype.prepareParams = function(params) {
	//prepare params
	if(params && typeof(params) == "object")
	{
		for(var key in params)
		{
			if(typeof(params[key]) == "object")
				params[key] = this.prepareParams(params[key]);
			if(typeof(params[key]) == "function")
				params[key] = this.RegisterCallBack(params[key]);
			else if(params[key] === true)
				params[key] = "YES";
			else if(params[key] === false)
				params[key] = "NO";
		}
	}
	else
	{
		if(typeof(params) == "function")
			params = this.RegisterCallBack(params[key]);
		else if(params === true)
			params = "YES";
		else if(params === false)
			params = "NO";
	}

	return params;
};

BitrixMobile.prototype.exec = function(funcName, params) {

	if (!this.available)
	{
		document.addEventListener("deviceready", BX.proxy(function(){
			this.exec(funcName, params);
		},this), false);
		return false;
	}

	if (typeof(params) != "undefined")
	{
		params = this.prepareParams(params);

		if(typeof(params) == "object")
			params = JSON.stringify(params);
	}
	else
		params = "empty";

	if(device.platform.toUpperCase() == "ANDROID")
		return Cordova.exec(null, null, "BitrixMobile", funcName, [params]);
	else
		return Cordova.exec("BitrixMobile."+funcName, params);

	return false;
};

BitrixMobile.prototype.takePhoto = function(options) {

	//open picture dialog or camera
	//options.source 0 - albums
	//options.source 1 - camera
	//options.callback - callback handler on event selecting of photo . Photo will be pass to the callback in base64 as a first param.
	navigator.camera.getPicture(
	options.callback, onFail, {
		quality: (options.quality || (this.enableInVersion(2) ? 40 : 10)),
		correctOrientation: (options.correctOrientation || false),
		targetWidth: (options.targetWidth || false),
		targetHeight: (options.targetHeight || false),
		destinationType: Camera.DestinationType.FILE_URI,
		sourceType: (options.source || 0)
	});

	function onFail(data) {
		//error
	}
};

BitrixMobile.prototype.openMenu = function(types, success, fail) {

	//sliding to the left
	return this.exec("openMenu");
};

BitrixMobile.prototype.showModalDialog = function(options) {

	//open modal dialog with options.url
	return this.exec("showModalDialog", options);
};

BitrixMobile.prototype.closeModalDialog = function(options) {

	//close current modal dialog
	return this.exec("closeModalDialog", options);
};

BitrixMobile.prototype.closeController = function(params) {

	//close current controller
	/*
	var params = {
		drop: false		// if true - than controller will be dropped at Android
	}
	*/
	return this.exec("closeController", params);
};

BitrixMobile.prototype.addButtons = function(buttons) {

	//buttons object description
	//callback - a function that will be execute on tap action,must have one incomming parameter
	//type - plus|back|refresh|right_text|back_text|users;
	//style - "custom" if use "type" parameter
	//name - title of button, use it if type=right_text|back_text or style!="custom"
	//bar_type - set "toolbar" to create button on a bottom panel

	return this.exec("addButtons", buttons);
};

BitrixMobile.prototype.openContent = function(types, success, fail) {
	//open main app window(move slider to the left)
	return this.exec("openContent");
};

BitrixMobile.prototype.closeMenu = function(types, success, fail) {
	//just close left slider-menu
	return this.exec("closeMenu");
};

BitrixMobile.prototype.loadPage = function(url, title) {
	//open page from menu
	if (this.enableInVersion(2) && title)
	{
		params = {
			url:url,
			page_id: title
		};
		return this.exec("loadPage", params);
	}
	this.openContent();
	return this.exec("loadPage", url);
};

BitrixMobile.prototype.setPageID = function(pageID) {
	//set page id for current page
	//
	return this.exec("setPageID", pageID);
};

BitrixMobile.prototype.openNewPage = function(url, data) {
	//open new page with slider effect

	if (this.enableInVersion(3))
	{
		var params = {
			url: url,
			data: data
		};

		return this.exec("openNewPage", params);
	}
	else
		return this.exec("openNewPage", url);
};

BitrixMobile.prototype.loadMenu = function(url) {
	//load menu page from url
	return this.exec("loadMenu", url);
};

BitrixMobile.prototype.openTable = function(options) {
	//@open table controller
	//options description
	//callback: handler on ok button tap action, it work only when 'markmode' is true
	//url: a source for the table, response must be in json format
	//markmode: set it true to turn on mark mode,false - by default
	//modal: true - your table will be open in modal dialog, false - by default
	//multiple: it works if 'markmode' is true, set it false to turn off multiple selection
    //okname - name of ok button
    //cancelname - name of cancel button
    //showtitle: true - to make title visible, false - by default
    //alphabet_index: if true - table will be divided on alphabetical sections
    //selected: this is a start selected data in a table, for example {users:[1,2,3,4],groups:[1,2,3]}
    //return_full_mode:false|true


    return this.exec("openTable", options);
};

BitrixMobile.prototype.openUserList = function(options) {
	//open table controller
	//options description
	//url: a source for the table, response must be in json format
	return this.exec("openUserList", options);
};

BitrixMobile.prototype.addUserListButton = function(options) {
	//open table controller
	//options.url
	return this.exec("addUserListButton", options);
};

BitrixMobile.prototype.pullDown = function(params) {
	//on|off pull down action on the current page
	//params.pulltext, params.downtext, params.loadtext
	//params.callback - action on pull-down-refresh
	//params.enable - true|false
	return this.exec("pullDown",params);
};

BitrixMobile.prototype.pullDownLoadingStop = function() {

	return this.exec("pullDownLoadingStop");
};

BitrixMobile.prototype.enableScroll = function(enable_status) {
	//enable|disable scroll on the current page
	var enable_status = enable_status||false;
	return this.exec("enableScroll", enable_status);
};

BitrixMobile.prototype.enableCaptureKeyboard = function(enable_status) {
	//enable|disable capture keyboard event on the current page
	var enable_status = enable_status||false;
	return this.exec("enableCaptureKeyboard", enable_status);
};
BitrixMobile.prototype.enableLoadingScreen = function(enable_status) {
	//enable|disable autoloading screen on the current page
	var enable_status = enable_status||false;
	return this.exec("enableLoadingScreen", enable_status);
};

BitrixMobile.prototype.showLoadingScreen = function() {
	//show loading screen
	return this.exec("showLoadingScreen");
};

BitrixMobile.prototype.hideLoadingScreen = function() {
	//hide loading screen
	return this.exec("hideLoadingScreen");
};

BitrixMobile.prototype.visibleNavigationBar = function(visible) {
	//visibility status of the native navigation bar
	var visible = visible||false;
	return this.exec("visibleNavigationBar", visible);
};

BitrixMobile.prototype.visibleToolBar = function(visible) {
	//visibility status of toolbar at the bottom
	var visible = visible||false;
	return this.exec("visibleToolBar", visible);
};

BitrixMobile.prototype.enableSliderMenu = function(enable) {
	//lock|unlock slider menu
	var enable = enable||false;
		return this.exec("enableSliderMenu", enable);
};

BitrixMobile.prototype.setCounters = function(counters) {
	//set counters values on the navigation bar
	//counters.messages,counters.notifications
	return this.exec("setCounters", counters);
};

BitrixMobile.prototype.setBadge = function(number) {
	//application's badge number on the dashboard
	return this.exec("setBadge", number);
};

BitrixMobile.prototype.refreshPanelPage = function(pagename) {
	//set counters values on the navigation bar
	//counters.messages,counters.notifications

	if(!pagename)
		pagename = "";
	var options = {
		page: pagename
	};
	return this.exec("refreshPanelPage", options);
};

BitrixMobile.prototype.setPanelPages = function(pages) {
	//pages for notify panel
	//pages.messages_page, pages.notifications_page,
	//pages.messages_open_empty, pages.notifications_open_empty
	return this.exec("setPanelPages", pages);
};

BitrixMobile.prototype.getToken = function() {
	//get device token
	var dt = "APPLE";
	if (platform != "ios")
		dt = "GOOGLE";
	params = {
		callback: function(token) {
			BX.proxy(
			BX.ajax.post(
			this.dataBrigePath,
			{
				mobile_action: "save_device_token",
				device_name: device.name,
				uuid: device.uuid,
				device_token: token,
				device_type:dt
			},
			 function(data) {
			}),this);
		}
	};

	return this.exec("getToken", params);
};

BitrixMobile.prototype.BasicAuth = function(params) {

	//basic autorization
	//params.success, params.check_url
	params = params||{};
	if(params.failture && typeof(params.failture) == "function")
		failture = params.failture;
	params.failture = function(data)
	{
		if(data.status == "failed")
			this.showAuthForm();
		else
			failture();
	}
	return this.exec("BasicAuth", params);
};

BitrixMobile.prototype.logOut = function() {
	//logout
	//request to mobile.data with mobile_action=logout
	if( this.enableInVersion(2))
	{
		this.asyncRequest({ url:this.dataBrigePath+"?mobile_action=logout&uuid="+device.uuid});
		return this.exec("showAuthForm");
	}

	var xhr = new XMLHttpRequest();
	xhr.open("GET", this.dataBrigePath+"?mobile_action=logout&uuid="+device.uuid,true);
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4 && xhr.status == "200")
		{
			//console.log(xhr.responseText);
			return app.exec("showAuthForm");
		}

	}
	xhr.send(null);
};

BitrixMobile.prototype.getCurrentLocation = function(options) {

	//get geolocation data
	var geolocationSuccess;
	var geolocationError;
	if (options) {
		geolocationSuccess = options.onsuccess;
		geolocationError = options.onerror;
	}
	navigator.geolocation.getCurrentPosition(
	geolocationSuccess, geolocationError);
};

BitrixMobile.prototype.setVibrate = function(ms) {
	// vibrate (ms)
	ms = ms || 500;
	navigator.notification.vibrate(parseInt(ms));
};

//<--end of BitrixMobile plugin


BitrixMobile.Utils = {

	autoResizeForm : function(textarea, pageContainer, maxHeight)
	{
		if (!textarea || !pageContainer)
			return;

		var formContainer = textarea.parentNode;
		maxHeight = maxHeight || 126;

		var origTextareaHeight = (textarea.ownerDocument || document).defaultView.getComputedStyle(textarea, null).getPropertyValue("height");
		var origFormContainerHeight = (formContainer.ownerDocument || document).defaultView.getComputedStyle(formContainer, null).getPropertyValue("height");

		origTextareaHeight = parseInt(origTextareaHeight); //23
		origFormContainerHeight = parseInt(origFormContainerHeight); //51
		textarea.setAttribute("data-orig-height", origTextareaHeight);
		formContainer.setAttribute("data-orig-height", origFormContainerHeight);

		var currentTextareaHeight = origTextareaHeight;
		var hiddenTextarea = document.createElement("textarea");
		hiddenTextarea.className = "send-message-input";
		hiddenTextarea.style.height = currentTextareaHeight + "px";
		hiddenTextarea.style.visibility = "hidden";
		hiddenTextarea.style.position = "absolute";
		hiddenTextarea.style.left = "-300px";

		document.body.appendChild(hiddenTextarea);

		textarea.addEventListener("change", resize, false);
		textarea.addEventListener("cut", resizeDelay, false);
		textarea.addEventListener("paste", resizeDelay, false);
		textarea.addEventListener("drop", resizeDelay, false);
		textarea.addEventListener("keyup", resize, false);

		if (window.platform == "android")
			textarea.addEventListener("keydown", resizeDelay, false);

		function resize()
		{
			hiddenTextarea.value = textarea.value;
			var scrollHeight = hiddenTextarea.scrollHeight;
			if (scrollHeight > maxHeight)
				scrollHeight = maxHeight;

			if (currentTextareaHeight != scrollHeight)
			{
				currentTextareaHeight = scrollHeight;
				textarea.style.height = scrollHeight + "px";
				formContainer.style.height = origFormContainerHeight + (scrollHeight - origTextareaHeight) + "px";
				pageContainer.style.bottom = origFormContainerHeight + (scrollHeight - origTextareaHeight) + "px";

				if (window.platform == "android")
					window.scrollTo(0, document.documentElement.scrollHeight);
			}
		}

		function resizeDelay()
		{
			setTimeout(resize, 0);
		}

	},

	resetAutoResize : function(textarea, pageContainer) {

		if (!textarea || !pageContainer)
			return;

		var formContainer = textarea.parentNode;

		var origTextareaHeight = textarea.getAttribute("data-orig-height");
		var origFormContainerHeight = formContainer.getAttribute("data-orig-height");

		textarea.style.height = origTextareaHeight + "px";
		formContainer.style.height = origFormContainerHeight + "px";
		pageContainer.style.bottom = origFormContainerHeight + "px";
	},

	showHiddenImages : function()
	{
		var images = document.getElementsByTagName("img");
		for (var i = 0; i < images.length; i++)
		{
			var image = images[i];
			var realImage = image.getAttribute("data-src");
			if (!realImage)
				continue;

			if (BitrixMobile.Utils.isElementVisibleOnScreen(image))
			{
				image.src = realImage;
				image.setAttribute("data-src", "");
			}
		}
	},

	isElementVisibleOnScreen : function(element)
	{
		var coords = BitrixMobile.Utils.getElementCoords(element);

		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + document.documentElement.clientHeight;

		coords.bottom = coords.top + element.offsetHeight;

		var topVisible = coords.top > windowTop && coords.top < windowBottom;
		var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

		return topVisible || bottomVisible;
	},

	isElementVisibleOn2Screens : function(element)
	{
		var coords = BitrixMobile.Utils.getElementCoords(element);

		var windowHeight = document.documentElement.clientHeight;
		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + windowHeight;

		coords.bottom = coords.top + element.offsetHeight;

		windowTop -= windowHeight;
		windowBottom += windowHeight;

		var topVisible = coords.top > windowTop && coords.top < windowBottom;
		var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

		return topVisible || bottomVisible;

	},

	getElementCoords : function(element)
	{
		var box = element.getBoundingClientRect();

		return {
			originTop : box.top,
			originLeft : box.left,
			top: box.top + window.pageYOffset,
			left: box.left + window.pageXOffset
		};
	}
};

BMContacts = function() {
	if (!navigator.contacts)
		return false;
};

BMContacts.prototype.AddContact = function(fields, callback) {

	//add contact to device address book
	var contact = navigator.contacts.create();
	var phoneNumbers = [];
	phoneNumbers[0] = new ContactField('work', fields.phone_number.work, false);
	phoneNumbers[1] = new ContactField('mobile', fields.phone_number.mobile, true); // preferred number
	phoneNumbers[2] = new ContactField('home', fields.phone_number.home, false);
	//email
	var emails = [];
	emails[0] = new ContactField('work', fields.email, true);

	var photos = [];
	photos[0] = new ContactField("url", fields.photo, true);
	//contact name
	var name = new ContactName();
	name.givenName = fields.firstname;
	name.familyName = fields.secondname;
	contact.name = name;

	contact.name = name;
	contact.photos = photos;
	contact.phoneNumbers = phoneNumbers;
	contact.emails = emails;
	callback = callback ||
	function() {
		alert("User is added to contact list!")
	};
	contact.save(
	callback, function() { //error
	});
};

BMContacts.prototype.FindContact = function(filter, callback) {

	//find contact in device address book
	var options = new ContactFindOptions();
	options.filter = filter;

	var fields = ["displayName", "name"];
	navigator.contacts.find(fields, callback, function() {}, options);
};

function ReadyDevice(func) {
	document.addEventListener("deviceready", func, false);
};

var BitrixAnimation = {

	animate : function(options)
	{
		if (!options || !options.start || !options.finish ||
			typeof(options.start) != "object" || typeof(options.finish) != "object"
			)
			return null;

		for (var propName in options.start)
		{
			if (!options.finish[propName])
			{
				delete options.start[propName];
			}
		}

		options.progress = function(progress) {
			var state = {};
			for (var propName in this.start)
				state[propName] = Math.round(this.start[propName] + (this.finish[propName] - this.start[propName]) * progress);

			if (this.step)
				this.step(state);
		};

		return BitrixAnimation.animateProgress(options);
	},

	animateProgress : function(options)
	{
		var start = new Date();
		var delta = options.transition || BitrixAnimation.transitions.linear;
		var duration = options.duration || 1000;

		var timer = setInterval(function() {

			var progress = (new Date() - start) / duration;
			if (progress > 1)
				progress = 1;

			options.progress(delta(progress));

			if (progress == 1)
			{
				clearInterval(timer);
				options.complete && options.complete();
			}

		}, options.delay || 13);

		return timer;
	},

	makeEaseInOut : function(delta)
	{
		return function(progress) {
			if (progress < 0.5)
				return delta(2*progress) / 2;
			else
				return (2 - delta(2*(1-progress))) / 2;
		}
	},

	makeEaseOut : function(delta)
	{
		return function(progress) {
			return 1 - delta(1 - progress);
		};
	},

	transitions : {

		linear : function(progress)
		{
			return progress;
		},

		elastic: function(progress)
		{
			return Math.pow(2, 10 * (progress-1)) * Math.cos(20 * Math.PI * 1.5/3 * progress);
		},

		quad : function(progress)
		{
			return Math.pow(progress, 2);
		},

		cubic : function(progress) {
			return Math.pow(progress, 3);
		},

		quart : function(progress)
		{
			return Math.pow(progress, 4);
		},

		quint : function(progress)
		{
			return Math.pow(progress, 5);
		},

		circ : function(progress)
		{
			return 1 - Math.sin(Math.acos(progress));
		},

		back : function(progress)
		{
			return Math.pow(progress, 2) * ((1.5 + 1) * progress - 1.5);
		},

		bounce : function(progress)
		{
			for(var a = 0, b = 1; 1; a += b, b /= 2) {
				if (progress >= (7 - 4 * a) / 11) {
					return -Math.pow((11 - 6 * a - 11 * progress) / 4, 2) + Math.pow(b, 2);
				}
			}
		}
	}
};
document.addEventListener("deviceready", function(){
	app.available = true;

}, false);
app = new BitrixMobile;
window.app = app;

MobileAjaxWrapper = function () {
	this.type = null;
	this.method = null;
	this.url = null;
	this.callback = null;
	this.failure_callback = null;
	this.progress_callback = null;
	this.offline = null;
	this.processData = null;
	this.xhr = null;
};

MobileAjaxWrapper.prototype.Init = function(params)
{
	if (params.type != 'json')
		params.type = 'html';

	if (params.method != 'POST')
		params.method = 'GET';

	if (params.processData == 'undefined')
		params.processData = true;

	this.type = params.type;
	this.method = params.method;
	this.url = params.url;
	this.data = params.data;
	this.processData = params.processData;
	this.callback = params.callback;

	if (params.callback_failure != 'undefined')
		this.failure_callback = params.callback_failure;
	if (params.callback_progress != 'undefined')
		this.progress_callback = params.callback_progress;
	if (params.callback_loadstart != 'undefined')
		this.loadstart_callback = params.callback_loadstart;
	if (params.callback_loadend != 'undefined')
		this.loadend_callback = params.callback_loadend;
}

MobileAjaxWrapper.prototype.Wrap = function(params)
{
	this.Init(params);

	if (this.offline === true)
	{
		this.failure_callback();
		this.OfflineAlert();
		return;
	}

	this.xhr = BX.ajax({
		'timeout': 30,
		'method': this.method,
		'dataType': this.type,
		'url': this.url,
		'data': this.data,
		'processData': this.processData,
		'onsuccess': BX.delegate(
			function(response)
			{
				if (this.type == 'json')
					var bFailed = (response.status == 'failed');
				else if (this.type == 'html')
					var bFailed = (response == '{"status":"failed"}');

				if (bFailed)
				{
					app.BasicAuth({
						'success': BX.delegate(
							function(auth_data)
							{
								this.data.sessid = auth_data.sessid_md5;
								this.xhr = BX.ajax({
									'timeout': 30,
									'method': this.method,
									'dataType': this.type,
									'url': this.url,
									'data': this.data,
									'onsuccess': BX.delegate( function(response_ii) { this.callback(response_ii); }, this),
									'onfailure': BX.delegate( function() { this.failure_callback(); }, this)
								});
							},
							this
						),
						'failture': BX.delegate( function() { this.failure_callback(); }, this)
					});
				}
				else
					this.callback(response);
			},
			this
		),
		'onfailure': BX.delegate( function() { this.failure_callback(); }, this)
	});

	if (this.progress_callback != null)
		BX.bind(this.xhr, "progress", this.progress_callback);

	if (this.load_callback != null)
		BX.bind(this.xhr, "load", this.load_callback);

	if (this.loadstart_callback != null)
		BX.bind(this.xhr, "loadstart", this.loadstart_callback);

	if (this.loadend_callback != null)
		BX.bind(this.xhr, "loadend", this.loadend_callback);

	if (this.error_callback != null)
		BX.bind(this.xhr, "error", this.error_callback);

	if (this.abort_callback != null)
		BX.bind(this.xhr, "abort", this.abort_callback);
}

MobileAjaxWrapper.prototype.OfflineAlert = function(callback)
{
	navigator.notification.alert(BX.message('MobileAppOfflineMessage'), (callback || BX.DoNothing), BX.message('MobileAppOfflineTitle'));
}

BMAjaxWrapper = new MobileAjaxWrapper;

document.addEventListener("offline", function(){ BMAjaxWrapper.offline = true; }, false);
document.addEventListener("online", function(){ BMAjaxWrapper.offline = false; }, false);

document.addEventListener('DOMContentLoaded', function() {
	BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function(params) {
		var networkState = navigator.network.connection.type;
		BMAjaxWrapper.offline = (networkState == Connection.UNKNOWN || networkState == Connection.NONE);
	});
}, false);

(function() {

  function addListener(el, type, listener, useCapture) {
    if (el.addEventListener) {
      el.addEventListener(type, listener, useCapture);
      return {
        destroy: function() { el.removeEventListener(type, listener, useCapture); }
      };
    } else {
      var handler = function(e) { listener.handleEvent(window.event, listener); }
      el.attachEvent('on' + type, handler);

      return {
        destroy: function() { el.detachEvent('on' + type, handler); }
      };
    }
  }

  var isTouch = true;

  /* Construct the FastButton with a reference to the element and click handler. */
  this.FastButton = function(element, handler, useCapture) {
    // collect functions to call to cleanup events
    this.events = [];
    this.touchEvents = [];
    this.element = element;
    this.handler = handler;
    this.useCapture = useCapture;
    if (isTouch)
      this.events.push(addListener(element, 'touchstart', this, this.useCapture));
    this.events.push(addListener(element, 'click', this, this.useCapture));
  };

  /* Remove event handling when no longer needed for this button */
  this.FastButton.prototype.destroy = function() {
    for (i = this.events.length - 1; i >= 0; i -= 1)
      this.events[i].destroy();
    this.events = this.touchEvents = this.element = this.handler = this.fastButton = null;
  };

  /* acts as an event dispatcher */
  this.FastButton.prototype.handleEvent = function(event) {
    switch (event.type) {
      case 'touchstart': this.onTouchStart(event); break;
      case 'touchmove': this.onTouchMove(event); break;
      case 'touchend': this.onClick(event); break;
      case 'click': this.onClick(event); break;
    }
  };


  this.FastButton.prototype.onTouchStart = function(event) {
    event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
    this.touchEvents.push(addListener(this.element, 'touchend', this, this.useCapture));
    this.touchEvents.push(addListener(document.body, 'touchmove', this, this.useCapture));
    this.startX = event.touches[0].clientX;
    this.startY = event.touches[0].clientY;
  };


  this.FastButton.prototype.onTouchMove = function(event) {
    if (Math.abs(event.touches[0].clientX - this.startX) > 10 || Math.abs(event.touches[0].clientY - this.startY) > 10) {
      this.reset(); //if he did, then cancel the touch event
    }
  };


  this.FastButton.prototype.onClick = function(event) {
    event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
    this.reset();

    var result = this.handler.call(this.element, event);
    if (event.type == 'touchend')
      clickbuster.preventGhostClick(this.startX, this.startY);
    return result;
  };

  this.FastButton.prototype.reset = function() {
    for (i = this.touchEvents.length - 1; i >= 0; i -= 1)
      this.touchEvents[i].destroy();
    this.touchEvents = [];
  };

  this.clickbuster = function() {}

  this.clickbuster.preventGhostClick = function(x, y) {
    clickbuster.coordinates.push(x, y);
    window.setTimeout(clickbuster.pop, 2500);
  };

  this.clickbuster.pop = function() {
    clickbuster.coordinates.splice(0, 2);
  };


  this.clickbuster.onClick = function(event) {
    for (var i = 0; i < clickbuster.coordinates.length; i += 2) {
      var x = clickbuster.coordinates[i];
      var y = clickbuster.coordinates[i + 1];
      if (Math.abs(event.clientX - x) < 25 && Math.abs(event.clientY - y) < 25) {
        event.stopPropagation ? event.stopPropagation() : (event.cancelBubble=true);
        event.preventDefault ? event.preventDefault() : (event.returnValue=false);
      }
    }
  };

  if (isTouch) {
    document.addEventListener('click', clickbuster.onClick, true);
    clickbuster.coordinates = [];
  }
})(this);