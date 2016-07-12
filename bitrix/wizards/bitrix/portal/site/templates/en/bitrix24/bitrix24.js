/*Global Settings */
(function() {

	BX.addCustomEvent(window, "onTopPanelCollapse", function(isCollapsed) {
		var header = BX("header", true);
		if (header)
			header.style.top = isCollapsed ? "39px" : "147px";
	});

	BX.addCustomEvent("onPopupWindowInit", function(uniquePopupId, bindElement, params) {
		//if (BX.util.in_array(uniquePopupId, ["task-legend-popup"]))
		//	params.lightShadow = true;

		if (uniquePopupId == "bx_log_filter_popup")
		{
			params.lightShadow = true;
			params.className = "";
		}
		else if (uniquePopupId == "task-legend-popup")
		{
			params.lightShadow = true;
			params.offsetTop = -15;
			params.offsetLeft = -670;
			params.angle = {offset : 740};
		}
		else if ((uniquePopupId == "task-gantt-filter") || (uniquePopupId == "task-list-filter"))
		{
			params.lightShadow = true;
			params.className = "";
		}
		else if (uniquePopupId.indexOf("sonet_iframe_popup_") > -1)
		{
			params.lightShadow = true;
		}
	});


	BX.addCustomEvent("onJCClockInit", function(config) {

		JCClock.setOptions({
			"centerXInline" : 83,
			"centerX" : 83,
			"centerYInline" : 67,
			"centerY" : 79,
			"minuteLength" : 31,
			"hourLength" : 26,
			"popupHeight" : 229,
			"inaccuracy" : 15,
			"cancelCheckClick" : true
		});
	});

	BX.PopupWindow.setOptions({
		"angleMinTop" : 35,
		"angleMinRight" : 10,
		"angleMinBottom" : 35,
		"angleMinLeft" : 10,
		"angleTopOffset" : 5,
		"angleLeftOffset" : 45,
		"offsetLeft" : 0, //-15,
		"offsetTop" : 2,
		"positionTopXOffset" : -11 //20
	});

})();

var B24Utils = {

	formateDate : function(time){
		return BX.util.str_pad(time.getHours(), 2, '0', 'left') + ':' + BX.util.str_pad(time.getMinutes(), 2, '0', 'left');
	}
};

var B24HelpPopup = function(steps, bindELement, settings)
{
	this.currentStep = null;
	this.layout = {
		paging : null,
		previousButton : null,
		nextButton : null
	};

	this.selectedClass = BX.type.isNotEmptyString(settings.selectedClass) ? settings.selectedClass : "b24-popup-selected";
	this.steps = [];

	if (settings && settings.video)
	{
		this.createVideoLayout(steps);
	}
	else
	{
		this.createHelpLayout(steps);
	}

	this.showStepByNumber(0);
};

B24HelpPopup.prototype.createHelpLayout = function(steps)
{
	var content = [];
	var paging = [];
	if (BX.type.isArray(steps))
	{
		for (var i = 0; i < steps.length; i++)
		{
			var step = steps[i];
			if (!BX.type.isNotEmptyString(step.title) || !BX.type.isNotEmptyString(step.content))
				continue;

			var stepContent = BX.create("div", { props : { className : "b24-help-popup-step" },  children : [
				BX.create("div", { props:{ className: "b24-help-popup-title" }, html : step.title }),
				BX.create("div", { props:{ className: "b24-help-popup-content" }, html : step.content })
			]});
			var stepPage = BX.create("span", {
				props : { className : "b24-help-popup-page"},
				html : i+1,
				events : { click : BX.proxy(this.onPageClick, this)}
			});
			this.steps.push({ content : stepContent, page : stepPage });

			content.push(stepContent);
			paging.push(stepPage);
		}
	}

	this.popup = BX.PopupWindowManager.create("b24-help-popup", null, {
		closeIcon : { top : "10px", right : "15px"},
		offsetTop : 1,
		overlay : { opacity : 20 },
		lightShadow : true,
		draggable : { restrict : true},
		closeByEsc : true,
		titleBar: {content: BX.create("span", {html: BX.message('B24_HELP_TITLE')})},
		content : BX.create("div", { props : { className : BX.message("B24_HELP_CLASSNAME") }, children : [
			BX.create("div", { props : { className : "b24-help-popup-contents" }, children : content }),
			BX.create("div", { props : { className : "b24-help-popup-navigation" }, children : [
				(this.layout.paging = BX.create("div", { props:{ className: "b24-help-popup-paging" }, children: paging })),
				BX.create("div", { props : { className : "b24-help-popup-buttons" }, children : [
					(this.layout.previousButton = BX.create("span", {
						props:{ className: "popup-window-button" },
						events : { click : BX.proxy(this.showPrevStep, this) },
						children:[
							BX.create("span", { props:{ className: "popup-window-button-left" }}),
							BX.create("span", { props:{ className: "popup-window-button-text" }, html: BX.message("B24_HELP_PREV") }),
							BX.create("span", { props:{ className: "popup-window-button-right" }})
						]
					})),
					(this.layout.nextButton = BX.create("span", {
						props:{ className:"popup-window-button" },
						events : { click : BX.proxy(this.showNextStep, this) },
						children:[
							BX.create("span", { props:{ className: "popup-window-button-left" }}),
							BX.create("span", { props:{ className: "popup-window-button-text" }, html: BX.message("B24_HELP_NEXT") }),
							BX.create("span", { props:{ className: "popup-window-button-right" }})
						]
					}))
				]})
			]})
		]})
	});
};

B24HelpPopup.prototype.createVideoLayout = function(steps)
{
	BX.addCustomEvent(this, "onShowStep", BX.proxy(this.onShowVideoStep, this));
	var content = [];
	var paging = [];
	if (BX.type.isArray(steps))
	{
		for (var i = 0; i < steps.length; i++)
		{
			var step = steps[i];
			if (!BX.type.isNotEmptyString(step.title) || !BX.type.isNotEmptyString(step.content))
				continue;

			var stepContent = null;
			if (step.content.indexOf("iframe") !== -1)
			{
				stepContent = BX.create("div", { props : { className : "b24-video-popup-step" }, children : [
					BX.create("div", { props : { className : "b24-video-popup-player" }, html : step.content })
				]});

				var iframe = stepContent.getElementsByTagName("iframe");
				if (iframe.length > 0)
				{
					var src = iframe[0].getAttribute("src");
					iframe[0].setAttribute("data-src", src);
					iframe[0].setAttribute("src", "");
				}
			}
			else
			{
				stepContent = BX.create("div", { props : { className : "b24-video-popup-step" }, html : step.content });
			}

			var stepPage = BX.create("div", {
				props : { className : "b24-video-popup-menu-item"},
				events : { click : BX.proxy(this.onPageClick, this)},
				children : [
					BX.create("div", { props : { className : "b24-video-popup-menu-index" }, html : (i+1) + "." }),
					BX.create("div", { props : { className : "b24-video-popup-menu-title" }, html : step.title })
				]
			});
			this.steps.push({ content : stepContent, page : stepPage });

			content.push(stepContent);
			paging.push(stepPage);
		}
	}

	this.popup = BX.PopupWindowManager.create("b24-video-popup", null, {
		closeIcon : { top : "20px", right : "20px"},
		offsetTop : 1,
		overlay : { opacity : 20 },
		lightShadow : true,
		draggable : { restrict : true},
		closeByEsc : true,
		events : {
			onPopupClose : BX.proxy(function(popupWindow)
			{
				this.unsetFrameSrc(this.currentStep);

				var transformProperty = BX.browser.isPropertySupported("transform");
				var helpBlock = BX("help-block");
				if (!transformProperty || !helpBlock)
					return;

				BX.addClass(popupWindow.popupContainer, "b24-help-popup-animation");

				var minScale = 5;
				var start = { height : popupWindow.popupContainer.offsetHeight, scale : 100 };
				var finish  = { height : 0, scale : minScale };

				var helpPos = BX.pos(helpBlock);
				var popupPos = BX.pos(popupWindow.popupContainer);
				start.left = popupPos.left;
				start.top = popupPos.top;
				finish.left = helpPos.left - ((popupPos.width - popupPos.width * (minScale / 100)) / 2);
				finish.top = helpPos.top - ((popupPos.height - popupPos.height * (minScale / 100)) / 2);

				(new BX.easing({
					duration : 500,
					start : start,
					finish : finish,
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quad),
					step : BX.proxy(function(state){
						//popupWindow.popupContainer.style.opacity = state.opacity/100;
						popupWindow.popupContainer.style[transformProperty] = "scale(" + state.scale / 100 +")";
						popupWindow.popupContainer.style.left = state.left + "px";
						popupWindow.popupContainer.style.top = state.top + "px";
					}, popupWindow),
					complete : BX.proxy(function() {
						//popupWindow.popupContainer.style.opacity = 100;
						popupWindow.popupContainer.style[transformProperty] = "none";
						BX.removeClass(popupWindow.popupContainer, "b24-help-popup-animation");
						popupWindow.adjustPosition();
					}, popupWindow)
				})).animate();
			}, this),
			onPopupShow: BX.proxy(function(popupWindow) {
				this.setFrameSrc(this.currentStep);
			}, this)
		},
		content : BX.create("div", { props : { className : "b24-video-popup" }, children : [
			BX.create("div", { props : { className : "b24-video-popup-title" }, html : BX.message("B24_HELP_POPUP_TITLE")}),
			BX.create("div", { props : { className : "b24-video-popup-contents" }, children : [
				BX.create("div", { props : { className : "b24-video-popup-menu" }, children : paging}),
				BX.create("div", { props : { className : "b24-video-popup-steps " }, children : content })
			]})
		]})
	});
};


B24HelpPopup.prototype.onShowVideoStep = function(prevStep, newStep)
{
	this.setFrameSrc(newStep);
	this.unsetFrameSrc(prevStep);
};

B24HelpPopup.prototype.setFrameSrc = function(step)
{
	if (!step)
		return;
	var iframe = step.content.getElementsByTagName("iframe");
	if (iframe.length > 0)
		iframe[0].setAttribute("src", iframe[0].getAttribute("data-src"));
};

B24HelpPopup.prototype.unsetFrameSrc = function(step)
{
	if (!step)
		return;
	var iframe = step.content.getElementsByTagName("iframe");
	if (iframe.length > 0)
		iframe[0].setAttribute("src", "");
};

B24HelpPopup.prototype.showStepByNumber = function(number)
{
	if (!this.steps[number] || this.currentStep == this.steps[number])
		return;

	if (this.currentStep != null)
	{
		this.currentStep.content.style.display = "none";
		BX.removeClass(this.currentStep.page, this.selectedClass);
	}

	this.steps[number].content.style.display = "block";
	BX.addClass(this.steps[number].page, this.selectedClass);

	BX.onCustomEvent(this, "onShowStep", [this.currentStep, this.steps[number]]);

	this.currentStep = this.steps[number];


};

B24HelpPopup.prototype.onPageClick = function(event)
{
	for (var i = 0; i < this.steps.length; i++)
	{
		if (this.steps[i].page == BX.proxy_context)
		{
			this.showStepByNumber(i);
			break;
		}
	}
};

B24HelpPopup.prototype.showNextStep = function()
{
	var currentPosition = this.getStepPosition(this.currentStep);

	if (currentPosition + 1 > this.steps.length - 1)
		this.showStepByNumber(0);
	else
		this.showStepByNumber(currentPosition + 1);
};

B24HelpPopup.prototype.showPrevStep = function()
{
	var currentPosition = this.getStepPosition(this.currentStep);
	if (currentPosition > 0)
		this.showStepByNumber(currentPosition - 1);
	else
		this.showStepByNumber(this.steps.length - 1);
};

B24HelpPopup.prototype.getStepPosition = function(step)
{
	for (var i = 0; i < this.steps.length; i++)
	{
		if (this.steps[i] == step)
			return i;
	}

	return -1;
};

BX.B24HelpPopup = {
	legend : null,
	show :  function(steps, bindElement, settings)
	{
		if (this.popup == null)
			this.legend = new B24HelpPopup(steps, bindElement, {
				"selectedClass" : "b24-help-popup-page-selected"
			});

		this.legend.popup.show();
	}
};

BX.B24VideoPopup = {
	legend : null,
	show :  function(steps, bindElement)
	{
		if (this.popup == null)
			this.legend = new B24HelpPopup(steps, bindElement, {
				"video" : true,
				"selectedClass" : "b24-video-popup-menu-item-selected"
			});

		this.legend.popup.show();
	}
};

function JCTitleSearchModified(arParams)
{
	var _this = this;

	this.arParams = {
		'AJAX_PAGE': arParams.AJAX_PAGE,
		'CONTAINER_ID': arParams.CONTAINER_ID,
		'INPUT_ID': arParams.INPUT_ID,
		'MIN_QUERY_LEN': parseInt(arParams.MIN_QUERY_LEN)
	};
	if(arParams.WAIT_IMAGE)
		this.arParams.WAIT_IMAGE = arParams.WAIT_IMAGE;
	if(arParams.MIN_QUERY_LEN <= 0)
		arParams.MIN_QUERY_LEN = 1;

	this.cache = [];
	this.cache_key = null;

	this.startText = '';
	this.currentRow = -1;
	this.RESULT = null;
	this.CONTAINER = null;
	this.INPUT = null;
	this.WAIT = null;

	this.ShowResult = function(result)
	{
		/* modified */
		var ieTop = 0;
		var ieLeft = 0;
		var ieWidth = 0;
		if(BX.browser.IsIE())
		{
			ieTop = 0;
			ieLeft = 1;
			ieWidth = -1;

			if(/MSIE 7/i.test(navigator.userAgent))
			{
				ieTop = -1;
				ieLeft = -1;
				ieWidth = -2;
			}
		}

		var pos = BX.pos(_this.CONTAINER);
		pos.width = pos.right - pos.left;
		_this.RESULT.style.position = 'absolute';
		_this.RESULT.style.top = pos.bottom + ieTop - 1 + 'px';/* modified */
		_this.RESULT.style.left = pos.left + ieLeft + 'px';/* modified */
		_this.RESULT.style.width = (pos.width + ieWidth - 2) + 'px';/* modified */
		if(result != null)
			_this.RESULT.innerHTML = result;

		if(_this.RESULT.innerHTML.length > 0)
			_this.RESULT.style.display = 'block';
		else
			_this.RESULT.style.display = 'none';

		//ajust left column to be an outline
		var th;
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'title-search-result'}, true);
		if(tbl) th = BX.findChild(tbl, {'tag':'th'}, true);

		/* ------%<----- */ /* modified */
	}

	this.onKeyPress = function(keyCode)
	{
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'title-search-result'}, true);
		if(!tbl)
			return false;

		var cnt = tbl.rows.length;

		switch (keyCode)
		{
			case 27: // escape key - close search div
				_this.RESULT.style.display = 'none';
				_this.currentRow = -1;
				_this.UnSelectAll();
				return true;

			case 40: // down key - navigate down on search results
				if(_this.RESULT.style.display == 'none')
					_this.RESULT.style.display = 'block';

				var first = -1;
				for(var i = 0; i < cnt; i++)
				{
					if(!BX.findChild(tbl.rows[i], {'class':'title-search-separator'}, true))
					{
						if(first == -1)
							first = i;

						if(_this.currentRow < i)
						{
							_this.currentRow = i;
							break;
						}
						else if(tbl.rows[i].className == 'title-search-selected')
						{
							tbl.rows[i].className = '';
						}
					}
				}

				if(i == cnt && _this.currentRow != i)
					_this.currentRow = first;

				tbl.rows[_this.currentRow].className = 'title-search-selected';
				return true;

			case 38: // up key - navigate up on search results
				if(_this.RESULT.style.display == 'none')
					_this.RESULT.style.display = 'block';

				var last = -1;
				for(var i = cnt-1; i >= 0; i--)
				{
					if(!BX.findChild(tbl.rows[i], {'class':'title-search-separator'}, true))
					{
						if(last == -1)
							last = i;

						if(_this.currentRow > i)
						{
							_this.currentRow = i;
							break;
						}
						else if(tbl.rows[i].className == 'title-search-selected')
						{
							tbl.rows[i].className = '';
						}
					}
				}

				if(i < 0 && _this.currentRow != i)
					_this.currentRow = last;

				tbl.rows[_this.currentRow].className = 'title-search-selected';
				return true;

			case 13: // enter key - choose current search result
				if(_this.RESULT.style.display == 'block')
				{
					for(var i = 0; i < cnt; i++)
					{
						if(_this.currentRow == i)
						{
							if(!BX.findChild(tbl.rows[i], {'class':'title-search-separator'}, true))
							{
								var a = BX.findChild(tbl.rows[i], {'tag':'a'}, true);
								if(a)
								{
									window.location = a.href;
									return true;
								}
							}
						}
					}
				}
				return false;
		}

		return false;
	}

	this.onTimeout = function()
	{
		if(_this.INPUT.value != _this.oldValue && _this.INPUT.value != _this.startText)
		{
			if(_this.INPUT.value.length >= _this.arParams.MIN_QUERY_LEN)
			{
				_this.oldValue = _this.INPUT.value;
				_this.cache_key = _this.arParams.INPUT_ID + '|' + _this.INPUT.value;
				if(_this.cache[_this.cache_key] == null)
				{
					if(_this.WAIT)
					{
						var pos = BX.pos(_this.INPUT);
						var height = (pos.bottom - pos.top)-2;
						_this.WAIT.style.top = (pos.top+1) + 'px';
						_this.WAIT.style.height = height + 'px';
						_this.WAIT.style.width = height + 'px';
						_this.WAIT.style.left = (pos.right - height + 2) + 'px';
						_this.WAIT.style.display = 'block';
					}

					BX.ajax.post(
						_this.arParams.AJAX_PAGE,
						{
							'ajax_call':'y',
							'INPUT_ID':_this.arParams.INPUT_ID,
							'q':_this.INPUT.value
						},
						function(result)
						{
							_this.cache[_this.cache_key] = result;
							_this.ShowResult(result);
							_this.currentRow = -1;
							_this.EnableMouseEvents();
							if(_this.WAIT)
								_this.WAIT.style.display = 'none';
							setTimeout(_this.onTimeout, 500);
						}
					);
				}
				else
				{
					_this.ShowResult(_this.cache[_this.cache_key]);
					_this.currentRow = -1;
					_this.EnableMouseEvents();
					setTimeout(_this.onTimeout, 500);
				}
			}
			else
			{
				_this.RESULT.style.display = 'none';
				_this.currentRow = -1;
				_this.UnSelectAll();
				setTimeout(_this.onTimeout, 500);
			}
		}
		else
		{
			setTimeout(_this.onTimeout, 500);
		}
	}

	this.UnSelectAll = function()
	{
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'title-search-result'}, true);
		if(tbl)
		{
			var cnt = tbl.rows.length;
			for(var i = 0; i < cnt; i++)
				tbl.rows[i].className = '';
		}
	}

	this.EnableMouseEvents = function()
	{
		var tbl = BX.findChild(_this.RESULT, {'tag':'table','class':'title-search-result'}, true);
		if(tbl)
		{
			var cnt = tbl.rows.length;
			for(var i = 0; i < cnt; i++)
				if(!BX.findChild(tbl.rows[i], {'class':'title-search-separator'}, true))
				{
					tbl.rows[i].id = 'row_' + i;
					tbl.rows[i].onmouseover = function (e) {
						if(_this.currentRow != this.id.substr(4))
						{
							_this.UnSelectAll();
							this.className = 'title-search-selected';
							_this.currentRow = this.id.substr(4);
						}
					};
					tbl.rows[i].onmouseout = function (e) {
						this.className = '';
						_this.currentRow = -1;
					};
				}
		}
	}

	this.onFocusLost = function(hide)
	{
		setTimeout(function(){_this.RESULT.style.display = 'none';}, 250);
	}

	this.onFocusGain = function()
	{
		if(_this.RESULT.innerHTML.length)
			_this.ShowResult();
	}

	this.onKeyDown = function(e)
	{
		if(!e)
			e = window.event;

		if (_this.RESULT.style.display == 'block')
		{
			if(_this.onKeyPress(e.keyCode))
				return BX.PreventDefault(e);
		}
	}

	this.Init = function()
	{
		this.CONTAINER = document.getElementById(this.arParams.CONTAINER_ID);
		this.RESULT = document.body.appendChild(document.createElement("DIV"));
		this.RESULT.className = 'title-search-result title-search-result-header';
		this.INPUT = document.getElementById(this.arParams.INPUT_ID);
		this.startText = this.oldValue = this.INPUT.value;
		BX.bind(this.INPUT, 'focus', function() {_this.onFocusGain()});
		BX.bind(window, 'resize', function() {_this.onFocusGain()});/* modified */
		BX.bind(this.INPUT, 'blur', function() {_this.onFocusLost()});

		if(BX.browser.IsSafari() || BX.browser.IsIE())
			this.INPUT.onkeydown = this.onKeyDown;
		else
			this.INPUT.onkeypress = this.onKeyDown;

		if(this.arParams.WAIT_IMAGE)
		{
			this.WAIT = document.body.appendChild(document.createElement("DIV"));
			this.WAIT.style.backgroundImage = "url('" + this.arParams.WAIT_IMAGE + "')";
			if(!BX.browser.IsIE())
				this.WAIT.style.backgroundRepeat = 'none';
			this.WAIT.style.display = 'none';
			this.WAIT.style.position = 'absolute';
			this.WAIT.style.zIndex = '1100';
		}

		setTimeout(this.onTimeout, 500);
	}

	BX.ready(function (){_this.Init(arParams)});
}

function showPassForm(arParams)
{
	BX = window.BX;
	BX.Bitrix24PassForm =
	{
		bInit: false,
		popup: null,
		arParams: {}
	}
	BX.Bitrix24PassForm.arParams = arParams;
	BX.message(arParams['MESS']);
	BX.Bitrix24PassForm.popup = BX.PopupWindowManager.create("BXPass", null, {
			autoHide: false,
			zIndex: 0,
			offsetLeft: 0,
			offsetTop: 0,
			overlay : true,
			draggable: {restrict:true},
			closeByEsc: true,
			titleBar: {content: BX.create("span", {html: BX.message('BX24_SITE_TITLE')})},
			closeIcon: { right : "12px", top : "10px"},
			buttons: [
				new BX.PopupWindowButton({
					text : BX.message('BX24_SITE_BUTTON'),
					className : "popup-window-button-accept",
					events : { click : function()
					{
						var form = BX('SITE_PASSWORD_FORM');
						if(form)
						{
							BX.submit(form);
							this.popupWindow.close();
						}
					}}
				}),

				new BX.PopupWindowButtonLink({
					text: BX.message('BX24_CLOSE_BUTTON'),
					className: "popup-window-button-link-cancel",
					events: { click : function()
					{
						this.popupWindow.close();
					}}
				})
			],
			content: '<div style="width:450px;height:230px"></div>',
			events: {
				onAfterPopupShow: function()
				{
					this.setContent('<div style="width:450px;height:230px">'+BX.message('BX24_LOADING')+'</div>');
					BX.ajax.post(
						'/bitrix/tools/b24_site_pass.php',
						{
							lang: BX.message('LANGUAGE_ID'),
							site_id: BX.message('SITE_ID') || '',
							arParams: BX.Bitrix24PassForm.arParams
						},
						BX.delegate(function(result)
						{
							this.setContent(result);
						},
						this)
					);
				}
			}
		});
	
	BX.Bitrix24PassForm.popup.show();
}

function showPartnerForm(arParams)
{
	BX = window.BX;
	BX.Bitrix24PartnerForm =
	{
		bInit: false,
		popup: null,
		arParams: {}
	}
	BX.Bitrix24PartnerForm.arParams = arParams;
	BX.message(arParams['MESS']);
	BX.Bitrix24PartnerForm.popup = BX.PopupWindowManager.create("BXPartner", null, {
		autoHide: false,
		zIndex: 0,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		draggable: {restrict:true},
		closeByEsc: true,
		titleBar: {content: BX.create("span", {html: BX.message('BX24_PARTNER_TITLE')})},
		closeIcon: { right : "12px", top : "10px"},
		buttons: [
			new BX.PopupWindowButtonLink({
				text: BX.message('BX24_CLOSE_BUTTON'),
				className: "popup-window-button-link-cancel",
				events: { click : function()
				{
					this.popupWindow.close();
				}}
			})
		],
		content: '<div style="width:450px;height:230px"></div>',
		events: {
			onAfterPopupShow: function()
			{
				this.setContent('<div style="width:450px;height:230px">'+BX.message('BX24_LOADING')+'</div>');
				BX.ajax.post(
					'/bitrix/tools/b24_site_partner.php',
					{
						lang: BX.message('LANGUAGE_ID'),
						site_id: BX.message('SITE_ID') || '',
						arParams: BX.Bitrix24PartnerForm.arParams
					},
					BX.delegate(function(result)
						{
							this.setContent(result);
						},
						this)
				);
			}
		}
	});

	BX.Bitrix24PartnerForm.popup.show();
}

function showExtranet2IntranetForm(arParams)
{
	BX = window.BX;
	window.Bitrix24Extranet2IntranetForm =
	{
		bInit: false,
		popup: null,
		arParams: {}
	}
	Bitrix24Extranet2IntranetForm.arParams = arParams;
	BX.message(arParams['MESS']);
	Bitrix24Extranet2IntranetForm.popup = BX.PopupWindowManager.create("BXExtranet2Intranet", null, {
		autoHide: false,
		zIndex: 0,
		offsetLeft: 0,
		offsetTop: 0,
		overlay : true,
		draggable: {restrict:true},
		closeByEsc: true,
		titleBar: {content: BX.create("span", {html: BX.message('BX24_TITLE')})},
		closeIcon: { right : "12px", top : "10px"},
		buttons: [
			new BX.PopupWindowButton({
				text : BX.message('BX24_BUTTON'),
				className : "popup-window-button-accept",
				events : { click : function()
				{
					var form = BX('EXTRANET2INTRANET_FORM');

					if(form)
						BX.ajax.submit(form, BX.delegate(function(result) {
							Bitrix24Extranet2IntranetForm.popup.setContent(result);
						}));
				}}
			}),

			new BX.PopupWindowButtonLink({
				text: BX.message('BX24_CLOSE_BUTTON'),
				className: "popup-window-button-link-cancel",
				events: { click : function()
				{
					this.popupWindow.close();
				}}
			})
		],
		content: '<div style="width:450px;height:230px"></div>',
		events: {
			onAfterPopupShow: function()
			{
				this.setContent('<div style="width:450px;height:230px">'+BX.message('BX24_LOADING')+'</div>');
				BX.ajax.post(
					'/bitrix/tools/b24_extranet2intranet.php',
					{
						lang: BX.message('LANGUAGE_ID'),
						site_id: BX.message('SITE_ID') || '',
						arParams: Bitrix24Extranet2IntranetForm.arParams
					},
					BX.delegate(function(result)
						{
							this.setContent(result);
						},
						this)
				);
			}
		}
	});

	Bitrix24Extranet2IntranetForm.popup.show();
}

var B24Timemanager = {

	inited : false,

	layout : {
		block : null,
		timer : null,
		info : null,
		event : null,
		tasks : null,
		status : null
	},

	data : null,
	timer : null,
	clock : null,

	formatWorkTime : function(h, m, s)
	{
		return '<span class="tm-popup-notice-time-hours"><span class="tm-popup-notice-time-number">' + h + '</span></span><span class="tm-popup-notice-time-minutes"><span class="tm-popup-notice-time-number">' + BX.util.str_pad(m, 2, '0', 'left') + '</span></span><span class="tm-popup-notice-time-seconds"><span class="tm-popup-notice-time-number">' + BX.util.str_pad(s, 2, '0', 'left') + '</span></span>';
	},

	formatCurrentTime : function(hours, minutes, seconds)
	{
		var mt = "";
		if (BX.isAmPmMode())
		{
			mt = "AM";
			if (hours > 12)
			{
				hours = hours - 12;
				mt = "PM";
			}
			else if (hours == 0)
			{
				hours = 12;
				mt = "AM";
			}
			else if (hours == 12)
			{
				mt = "PM";
			}

			mt = '<span class="time-am-pm">' + mt + '</span>';
		}
		else
			hours = BX.util.str_pad(hours, 2, "0", "left");

		return '<span class="time-hours">' + hours + '</span>' +
			'<span class="time-semicolon">:</span>' +
			'<span class="time-minutes">' + BX.util.str_pad(minutes, 2, "0", "left") + '</span>' +
			mt;
	},

	init : function()
	{
		BX.addCustomEvent("onTimeManDataRecieved", BX.proxy(this.onDataRecieved, this));
		BX.addCustomEvent("onPlannerDataRecieved", BX.proxy(this.onPlannerDataRecieved, this));
		BX.addCustomEvent("onPlannerQueryResult", BX.proxy(this.onPlannerQueryResult, this));

		BX.timer.registerFormat("worktime_notice_timeman",BX.proxy(this.formatWorkTime, this));
		BX.timer.registerFormat("bitrix24_time",BX.proxy(this.formatCurrentTime, this));

		BX.ready(BX.proxy(function() {

			this.inited = true;

			this.layout.block = BX("timeman-block");
			this.layout.timer = BX("timeman-timer");
			this.layout.info = BX("timeman-info");
			this.layout.event = BX("timeman-event");
			this.layout.tasks = BX("timeman-tasks");
			this.layout.status = BX("timeman-status");

			BX.bind(this.layout.block, "click", BX.proxy(this.onTimemanClick, this));

			BXTIMEMAN.setBindOptions({
				node: this.layout.block,
				mode: "popup",
				popupOptions: {
					angle : { position : "top", offset : 130},
					offsetTop : 10,
					autoHide : true,
					offsetLeft : -60,
					zIndex : -1,
					events : {
						onPopupClose : BX.proxy(function() {
							BX.removeClass(this.layout.block, "timeman-block-active");
						}, this)
					}
				}
			});

			this.redraw();

		}, this));
	},

	onTimemanClick : function()
	{
		BX.addClass(this.layout.block, "timeman-block-active");
		BXTIMEMAN.Open();
	},

	setTimer : function()
	{
		if (this.timer)
		{
			this.timer.setFrom(new Date(this.data.INFO.DATE_START * 1000));
			this.timer.dt = -this.data.INFO.TIME_LEAKS * 1000;
		}
		else
		{
			this.timer = BX.timer(this.layout.timer, {
				from: new Date(this.data.INFO.DATE_START*1000),
				dt: -this.data.INFO.TIME_LEAKS * 1000,
				display: "simple"
			});
		}
	},

	stopTimer : function()
	{
		if (this.timer != null)
		{
			BX.timer.stop(this.timer);
			this.timer = null;
		}
	},

	redraw_planner: function(data)
	{
		if(!!data.TASKS_ENABLED)
		{
			data.TASKS_COUNT = !data.TASKS_COUNT ? 0 : data.TASKS_COUNT;
			this.layout.tasks.innerHTML = data.TASKS_COUNT;
			this.layout.tasks.style.display = data.TASKS_COUNT == 0 ? "none" : "inline-block";
		}

		if(!!data.CALENDAR_ENABLED)
		{
			this.layout.event.innerHTML = data.EVENT_TIME;
			this.layout.event.style.display = data.EVENT_TIME == '' ? 'none' : 'inline-block';
		}

		this.layout.info.style.display =
			(BX.style(this.layout.tasks, "display") == 'none' && BX.style(this.layout.event, "display") == 'none')
				? 'none'
				: 'block';
	},

	redraw : function()
	{
		this.redraw_planner(this.data.PLANNER);

		if (this.data.STATE == "CLOSED" && (this.data.CAN_OPEN == "REOPEN" || !this.data.CAN_OPEN))
			this.layout.status.innerHTML = this.getStatusName("COMPLETED");
		else
			this.layout.status.innerHTML = this.getStatusName(this.data.STATE);

		// if (this.data.STATE == "OPENED")
		// 	this.setTimer();
		// else
		// {
		// 	this.stopTimer();
		// 	var workedTime = (this.data.INFO.DATE_FINISH - this.data.INFO.DATE_START - this.data.INFO.TIME_LEAKS);
		// 	this.layout.timer.innerHTML = BX.timeman.formatTime(workedTime);
		// }
		if (!this.timer)
			this.timer = BX.timer({container: this.layout.timer, display : "bitrix24_time"}); //BX.timer.clock(this.layout.timer);

		var statusClass = "";
		if (this.data.STATE == "CLOSED")
		{
			if (this.data.CAN_OPEN == "REOPEN" || !this.data.CAN_OPEN)
				statusClass = "timeman-completed";
			else
				statusClass = "timeman-start";
		}
		else if (this.data.STATE == "PAUSED")
			statusClass = "timeman-paused";
		else if (this.data.STATE == "EXPIRED")
			statusClass = "timeman-expired";

		BX.removeClass(this.layout.block, "timeman-completed timeman-start timeman-paused timeman-expired");
		BX.addClass(this.layout.block, statusClass);
		//this.layout.block.className = "timeman-block" + " " + statusClass;
	},

	getStatusName : function(id)
	{
		return BX.message("TM_STATUS_" + id);
	},

	onDataRecieved : function(data)
	{
		data.OPEN_NOW = false;

		this.data = data;

		if (this.inited)
			this.redraw();
	},

	onPlannerQueryResult : function(data, action)
	{
		if (this.inited)
			this.redraw_planner(data);
	},

	onPlannerDataRecieved : function(ob, data)
	{
		if (this.inited)
			this.redraw_planner(data);
	}
};