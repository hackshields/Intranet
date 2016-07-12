;(function(window){
if (top.BSBBW)
	return true;

top.BSBBW = function(params) {
	this.CID = params["CID"];
	this.controller = params["controller"];

	this.nodes = params["nodes"];
	this.tMessage = this.nodes['template'].innerHTML;

	this.url = params["url"];

	this.options = params["options"];
	this.post_info = params["post_info"];
	this.post_info['AJAX_POST'] = "Y";
	this.post_info['sessid'] = BX.bitrix_sessid();

	this.timeInterval = 180000;
	this.sended = false;
	this.active = false;
	this.inited = false;

	this.inited = this.init(params);
	this.show();

	BX.addCustomEvent(this.controller, "onDataAppeared", BX.delegate(this.onDataAppeared, this));
	BX.addCustomEvent(this.controller, "onDataRanOut", BX.delegate(this.onDataRanOut, this));
	BX.addCustomEvent(this.controller, "onReachedLimit", BX.delegate(this.onReachedLimit, this));
	BX.addCustomEvent(this.controller, "onRequestSend", BX.delegate(this.showWait, this));
	BX.addCustomEvent(this.controller, "onResponseCame", BX.delegate(this.hideWait, this));
	BX.addCustomEvent(this.controller, "onResponseFailed", BX.delegate(this.hideWait, this));
}

top.BSBBW.prototype = {
	init : function(params) {
		this.page_settings = params["page_settings"];
		this.page_settings["NavRecordCount"] = parseInt(this.page_settings["NavRecordCount"]);

		this.limit = (this.page_settings["NavPageCount"] > 1 ? 3 : 0);
		this.current = 0;

		if (this.active)
			clearTimeout(this.active);
		this.active = false;

		this.data_id = {};
		this.data = params["data"];
		for (var ii in this.data)
			this.data_id['id' + this.data[ii]["id"]] = 'normal';

		if (this.data.length <= 0)
			BX.onCustomEvent(this.controller, "onDataRanOut");
		else
			BX.onCustomEvent(this.controller, "onDataAppeared");

		if (!this.inited)
		{
			BX.bind(this.nodes["right"], "click", BX.delegate(function(){this.onShiftPage("right")}, this));
			BX.bind(this.nodes["left"], "click", BX.delegate(function(){this.onShiftPage("left")}, this));
			BX.adjust(this.nodes["btn"], {attrs : {url : this.url}, events: {click : BX.delegate(this.onClickToRead, this)}});
			this.startCheck();
		}
		return true;
	},
	show : function() {
		var
			message = this.tMessage,
			data = this.data[this.current];
		if (!data)
			return;
		for (var ii in data)
			message = message.replace("__" + ii + "__", data[ii]);
		this.nodes["text"].innerHTML = message;
		this.nodes["counter"].innerHTML = (this.current + 1);
		this.nodes["total"].innerHTML = this.page_settings["NavRecordCount"];
		var btn = BX.findChild(this.nodes["text"], {"className" : "sidebar-imp-mess-text"}, true),
			avatar = BX.findChild(this.nodes["text"], {attribute : {"data-bx-author-avatar" : true}}, true);
		if (!!btn)
			BX.adjust(btn, {attrs : {url : this.url}, events: {click : BX.delegate(this.onClickToRead, this)}});
		if (data["author_avatar_style"] !== "" && !!avatar){
			avatar.style.background = data["author_avatar_style"];
		}
	},
	showWait : function() { /* showWait */ },
	hideWait : function() { /* hideWait */ },
	startCheck : function(send)
	{
		if (send === true)
		{
			var request = this.post_info;
			request['page_settings'] = this.page_settings;
			request['page_settings']['iNumPage'] = null;
			BX.ajax({
				'method': 'POST',
				'processData': false,
				'url': this.url,
				'data': request,
				'onsuccess': BX.delegate(this.stepCheck, this),
				'onfailure': BX.delegate(this.onResponseFailed, this)
			});
		} else {
			this.timeOut = setTimeout(BX.delegate(function(){this.startCheck(true);}, this), this.timeInterval);
		}
	},
	stepCheck : function(data)
	{
		this.parseResponse(data, true);
		this.startCheck();
	},
	parseResponse : function(response, fromCheck)
	{
		var data = false, result = false;
		try{eval("result="+ response + ";");} catch(e) {}
		if (!result || !result.data || result.data.length <= 0)
			data = false;
		else if (fromCheck === true)
		{
			var dataNew = [], data = result.data;
			for (var ii in data )
			{
				if (typeof data[ii] == "object" && !this.data_id['id' + data[ii]["id"]])
				{
					dataNew.push(data[ii]);
				}
			}
			result.page_settings["NavRecordCount"] = parseInt(result.page_settings["NavRecordCount"]);
			this.page_settings["NavRecordCount"] = parseInt(this.page_settings["NavRecordCount"]);
			if (this.data.length > 0 &&
				dataNew.length == (result.page_settings["NavRecordCount"] - this.page_settings["NavRecordCount"]))
			{
				var d = dataNew.pop();
				while(!!d)
				{
					this.data_id['id' + d["id"]] = 'normal';
					this.data.unshift(d);
					this.current++;
					d = dataNew.pop();
				}
				this.page_settings["NavPageCount"] = result.page_settings["NavPageCount"];
				this.page_settings["NavRecordCount"] = result.page_settings["NavRecordCount"];
				this.show();
			}
			else
			{
				var current = 0, res = this.data[this.current];
				if (this.data.length > 0 && !!res)
				{
					for (var ii = 0; ii < data.length; ii++)
					{
						if (typeof data[ii] == "object" && data[ii]["id"] == res["id"])
						{
							current = ii;
							break;
						}
					}
				}
				this.init(result);
				this.current = current;
				this.show();
			}
		}
		else
		{
			this.page_settings["NavPageNomer"] = result.page_settings["NavPageNomer"];
			data = result.data;
			for (var ii in data )
			{
				if (typeof data[ii] == "object" && !this.data_id['id' + data[ii]["id"]])
				{
					this.data_id['id' + data[ii]["id"]] = 'normal';
					this.data.push(data[ii]);
				}
			}
			if (this.data.length > 0)
				BX.onCustomEvent(this.controller, "onDataAppeared");
		}
		return true;
	},
	onClickToRead : function()
	{
		var
			data = this.data[this.current], options = [], ii;
		for (ii in this.options)
			options.push({post_id : data["id"], name : this.options[ii]['name'], value:this.options[ii]['value']});
		var
			request = this.post_info;
		request['options'] = options;
		request['page_settings'] = this.page_settings;

		BX.ajax({
			'method': 'POST',
			'processData': false,
			'url': this.url,
			'data': request,
			'onsuccess': BX.delegate(this.onAfterClickToRead, this),
			'onfailure': function(data){}
		});
		this.onShiftPage('drop');
	},
	onAfterClickToRead : function ()
	{
	},
	onShiftPage : function(status)
	{
		if (this.active)
			clearTimeout(this.active);
		this.active = setTimeout(BX.delegate(function(){this.active=false;}, this), 120000);

		if (status == 'drop')
		{
			this.page_settings["NavRecordCount"]--;
			this.data_id['id' + this.data[this.current]["id"]] = 'readed';
			this.data = BX.util.deleteFromArray(this.data, this.current);
			if (!!this.data && this.data.length > 0)
			{
				this.current = this.current - 1;
				status = 'left';
			}
			else
			{
				BX.onCustomEvent(this.controller, "onDataRanOut");
				return;
			}
		}

		if (status == 'right')
		{
			if (this.current <= 0)
			{
				this.page_settings["NavRecordCount"] = parseInt(this.page_settings["NavRecordCount"]);
				if (this.data.length < this.page_settings["NavRecordCount"])
					this.current = 1;
				else
					this.current = this.data.length;
			}
			this.current = this.current - 1;
		}
		else
		{
			if (this.current >= (this.data.length - 1))
				this.current = 0;
			else
				this.current = this.current + 1;
		}
		if (this.limit > 0 && this.current >= (this.data.length - 1 - this.limit))
			BX.onCustomEvent(this.controller, "onReachedLimit");

		this.show();
	},
	onDataRanOut: function()
	{
		if ((!this.data || this.data.length <= 0) && this.controller.style.display != "none")
		{
			this.bodyAnimationheight = this.controller.offsetHeight;
			(this.bodyAnimation = new BX.easing({
				duration : 200,
				start : { height : this.controller.offsetHeight, opacity : 100},
				finish : { height : 0, opacity : 0},
				transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
				step : BX.delegate(function(state){
					BX.adjust(this.controller, {style:{height : state.height + 'px', opacity : (state.opacity/100)}});
				}, this),
				complete : BX.delegate(function(){
					this.controller.style.display = "none";
				}, this)
			})).animate();
		}
	},
	onDataAppeared: function()
	{
		if (!!this.data && this.data.length > 0 && this.controller.style.display == "none")
		{
			var height = (!!this.bodyAnimationheight ? this.bodyAnimationheight : 200);
			this.controller.style.display = "block";
			(this.bodyAnimation = new BX.easing({
				duration : 200,
				start : { height : 0, opacity : 0},
				finish : { height : height, opacity : 100},
				transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),
				step : BX.delegate(function(state){
					BX.adjust(this.controller, {style:{height : state.height + 'px', opacity : (state.opacity/100)}});
				}, this),
				complete : BX.delegate(function(){
					BX.adjust(this.controller, {style:{display : "block", height : "auto", opacity : "auto"}});
				}, this)
			})).animate();
		}
	},
	onReachedLimit : function()
	{
		if (this.sended === true)
			return;

		var
			request = this.post_info,
			needToUnbind = false;

		this.page_settings["NavPageNomer"] = parseInt(this.page_settings["NavPageNomer"]);
		this.page_settings["NavPageCount"] = parseInt(this.page_settings["NavPageCount"]);

		if (this.page_settings["NavPageCount"] <= 1)
			needToUnbind = true;
		else if (this.page_settings["bDescPageNumbering"] == true)
		{
			if (this.page_settings["NavPageNomer"] > 1)
				this.page_settings["iNumPage"] = parseInt(this.page_settings["NavPageNomer"]) - 1;
			else
				needToUnbind = true;
		}
		else if (this.page_settings["NavPageNomer"] < this.page_settings["NavPageCount"])
			this.page_settings["iNumPage"] = parseInt(this.page_settings["NavPageNomer"]) + 1;
		else
			needToUnbind = true;
		if (needToUnbind === true)
		{
			BX.removeCustomEvent(this.controller, "onReachedLimit", BX.delegate(this.onReachedLimit, this));
			return true;
		}
		BX.onCustomEvent(this.controller, "onRequestSend");
		this.sended = true;
		request['page_settings'] = this.page_settings;
		BX.ajax({
			'method': 'POST',
			'processData': false,
			'url': this.url,
			'data': request,
			'onsuccess': BX.delegate(this.onResponseCame, this),
			'onfailure': BX.delegate(this.onResponseFailed, this)
		});
	},
	onResponseCame : function(data)
	{
		this.sended = false;
		BX.onCustomEvent(this.controller, "onResponseCame");
		this.parseResponse(data);
	},
	onResponseFailed : function(data)
	{
		this.sended = false;
		BX.onCustomEvent(this.controller, "onResponseFailed");
	}
}
})(window);