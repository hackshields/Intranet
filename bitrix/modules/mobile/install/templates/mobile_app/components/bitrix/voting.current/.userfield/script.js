;(function(window){
if (window.BVotedUser)
	return true;

	window.FormToArray = function(form, data)
	{
		data = (!!data ? data : new Array());
		if(!!form){
			var
				i,
				_data = new Array(),
				n = form.elements.length;

			for(i=0; i<n; i++)
			{
				var el = form.elements[i];
				if (el.disabled)
					continue;
				switch(el.type.toLowerCase())
				{
					case 'text':
					case 'textarea':
					case 'password':
					case 'hidden':
					case 'select-one':
						_data.push({name: el.name, value: el.value});
						break;
					case 'radio':
					case 'checkbox':
						if(el.checked)
							_data.push({name: el.name, value: el.value});
						break;
					case 'select-multiple':
						for (var j = 0; j < el.options.length; j++) {
							if (el.options[j].selected)
								_data.push({name : el.name, value : el.options[j].value});
						}
						break;
					default:
						break;
				}
			}

			var current = data,
				i = 0;

			while(i < _data.length)
			{
				var p = _data[i].name.indexOf('[');
				if (p == -1) {
					current[_data[i].name] = _data[i].value;
					current = data;
					i++;
				}
				else
				{
					var name = _data[i].name.substring(0, p);
					var rest = _data[i].name.substring(p+1);
					if(!current[name])
						current[name] = new Array;

					var pp = rest.indexOf(']');
					if(pp == -1)
					{
						current = data;
						i++;
					}
					else if(pp == 0)
					{
						//No index specified - so take the next integer
						current = current[name];
						_data[i].name = '' + current.length;
					}
					else
					{
						//Now index name becomes and name and we go deeper into the array
						current = current[name];
						_data[i].name = rest.substring(0, pp) + rest.substring(pp+1);
					}
				}
			}
		}
		return data;
	};



	window.voteGetID = function() {
		return 'vote' + new Date().getTime();
	}

	window.VCLinkCloseWait = function(el)
	{
		if (el && !BX.type.isElementNode(el))
			el = null;
		el = el || this;

		if (BX.type.isElementNode(el))
		{
			el.setAttribute("href", el.getAttribute("rel"));
			BX.removeClass(el, 'bx-vote-loading');
		}
	}

	window.VCLinkShowWait = function(el)
	{
		if (el && !BX.type.isElementNode(el))
			el = null;
		el = el || this;

		if (BX.type.isElementNode(el))
		{
			BX.defer(function(){el.setAttribute("rel", el.getAttribute("href")), el.setAttribute("href", "javascript:void(0);");})();
			BX.addClass(el, 'bx-vote-loading');
		}
	}

	window.voteSendForm = function(link, form, CID){
		if (!!form) {
			window.voteAJAX(
				link,
				CID,
				form.action,
				window.FormToArray(form, {'AJAX_POST' : 'Y'}));
		}
	};


	window.voteGetForm = function (link, VOTE_ID, CID) {
		var
			url = link.getAttribute('href').
				replace(/.AJAX_RESULT=Y/g,'').
				replace(/.AJAX_POST=Y/g,'').
				replace(/.sessid=[^&]*/g, '').
				replace(/.VOTE_ID=([\d]+)/,'').
				replace(/.view_form=Y/g, '').
				replace(/.view_result=Y/g, ''),
			data = {
				'view_form' : 'Y',
				'VOTE_ID' : VOTE_ID,
				'AJAX_POST' : 'Y',
				'sessid': BX.bitrix_sessid()};

		window.voteAJAX(link, CID, url, data);

		return false;
	}

	window.voteAJAX = function(link, CID, url, data)
	{
		VCLinkShowWait(link);
		BMAjaxWrapper.Wrap({
			'type': 'html',
			'method': 'POST',
			'url': url,
			'data': data,
			'callback': function(result){
				VCLinkCloseWait(link);
				var
					ob = BX.processHTML(result, false),
					res = BX.findParent(link, {"className" : "bx-vote-block"});

				res.innerHTML = ob.HTML;

				BX.removeClass(res, "bx-vote-block-result");
				BX.removeClass(res, "bx-vote-block-result-view");

				if (ob.HTML.indexOf('<form') < 0) {
					BX.addClass(res, "bx-vote-block-result");
				};
				BX.ajax.processScripts(ob.SCRIPT);
				window['BVote' + CID] = null;
			},
			'callback_failure': function(data){}
		});
	}

	window.voteGetResult = function (controller, uid, link) {
		VCLinkShowWait(link);

		BX.addCustomEvent(
			controller,
			'OnBeforeChangeData',
			function(){
				var res = BX.findParent(controller, {"className" : "bx-vote-block"});
				BX.addClass(res, "bx-vote-block-result");
				BX.addClass(res, "bx-vote-block-result-view");
				VCLinkCloseWait(link);
			}
		);

		BX.addCustomEvent(
			controller,
			'OnAfterChangeData',
			function(){
				BX.hide(link);
			}
		);

		window['BVote' + uid].checker.lastVote = 0;
		window['BVote' + uid].checker.check(true);

		return false;
	}

	window.BVoteChecker = function(params)
	{
		if (!params["url"] || !params["CID"] || !params["controller"])
			return false;

		this.CID = params["CID"];
		this.voteId = params["voteId"];
		this.url = params["url"];
		this.controller = params["controller"];
		this.period = [2, 5, 10, 30];
		this.lastVote = (!!params["startCheck"] ? parseInt(params["startCheck"]/60) : false);
		if (!!this.lastVote)
			this.check();

		return this;
	}

	window.BVoteChecker.prototype.check = function(now) {
		var time = (now === true ? 0 : false);
		if (now !== true) {
			for (var i in this.period) {
				if (this.lastVote <= this.period[i]) {
					time = this.period[i];
					break;
				}
			}
		}

		if (time !== false)
			setTimeout(BX.proxy(function(){this.send()}, this), time * 60 * 1000);
	}

	window.BVoteChecker.prototype.send = function(){
		BX.ajax({
			url: this.url.replace(/.AJAX_RESULT=Y/g,'').
				replace(/.AJAX_POST=Y/g,'').
				replace(/.sessid=[^&]*/g, '').
				replace(/.VOTE_ID=([\d]+)/,'').
				replace(/.view_form=Y/g, '').
				replace(/.view_result=Y/g, ''),
			method: 'POST',
			dataType: 'json',
			data: {
				'VOTE_ID' : this.voteId,
				'AJAX_RESULT' : 'Y',
				'view_result' : 'Y',
				'sessid': BX.bitrix_sessid()
			},
			onsuccess: BX.proxy(function(data){this.start(data);}, this),
			onfailure: function(data){}
		});
	}
	window.BVoteChecker.prototype.start = function(data){
		this.lastVote = parseInt(data["LAST_VOTE"]/60);
		this.changeData(data);
		this.check();
	}
	window.BVoteChecker.prototype.changeData = function(data){
		data = data["QUESTIONS"];
		BX.onCustomEvent(this.controller, 'OnBeforeChangeData');
		for (var q in data)
		{
			var question = BX.findChild(this.controller, {"attr" : {"id" : "question" + q}}, true);
			if (!!question) {
				for (var i in data[q]) {
					var answer = BX.findChild(question, {"attr" : {"id" : ("answer" + i)}}, true);
					if (!!answer) {
						BX.adjust(BX.findChild(answer, {"tagName" : "DIV", "className" : "bx-vote-data-percent"}, true),
							{"html" : '<span>' + parseInt(data[q][i]["PERCENT"]) + '</span><span class="post-vote-color">%</span>'});
						BX.adjust(BX.findChild(answer, {"tagName" : "DIV", "className" : "bx-vote-answer-bar"}, true),
							{"style" : {"width" : parseInt(data[q][i]["PERCENT"]) + '%'}});
					}
				}
			}
		}
		BX.adjust(BX.findChild(this.controller, {"tagName" : "SPAN", "className" : "bx-vote-events-count"}, true),
			{"html" : '<span>' + parseInt(data["COUNTER"]) + '</span><span class="post-vote-color">%</span>'});

		BX.onCustomEvent(this.controller, 'OnAfterChangeData');
	}

	window.BVotedUser = function(params)
	{
		this.checker = new BVoteChecker(params);
	}
})(window);