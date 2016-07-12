if(typeof(BX.CrmDealListView) === "undefined")
{
	BX.CrmDealListView = function()
	{
	};
	BX.extend(BX.CrmDealListView, BX.CrmEntityListView);
	BX.CrmDealListView.prototype.doInitialize = function()
	{
	};
	BX.CrmDealListView.prototype.getContainer = function()
	{
		return this._container ? this._container : BX.findChild(this._wrapper, { className: "crm_dealings_list" }, true, false);
	};
	BX.CrmDealListView.prototype.getItemContainers = function()
	{
		return BX.findChild(this.getContainer(), { className: "crm_dealings_list_item" }, true, true);
	};
	BX.CrmDealListView.prototype.getWaiterClassName = function()
	{
		return "crm_dealings_list_item_wait";
	};
	BX.CrmDealListView.prototype.createModel = function(data, register)
	{
		var d = this.getDispatcher();
		return d ? d.createEntityModel(data, "DEAL", register) : null;
	};
	BX.CrmDealListView.prototype.createItemView = function(settings)
	{
		return BX.CrmDealListItemView.create(settings);
	};
	BX.CrmDealListView.prototype.createSearchParams = function(val)
	{
		return { TITLE: val };
	};
	BX.CrmDealListView.prototype.getMessage = function(name, defaultVal)
	{
		var m = BX.CrmDealListView.messages;
		return m.hasOwnProperty(name) ? m[name] : defaultVal;
	};
	BX.CrmDealListView.create = function(id, settings)
	{
		var self = new BX.CrmDealListView();
		self.initialize(id, settings);
		return self;
	};
	if(typeof(BX.CrmDealListView.messages) === "undefined")
	{
		BX.CrmDealListView.messages =
		{
		};
	}
}

if(typeof(BX.CrmDealListItemView) === "undefined")
{
	BX.CrmDealListItemView = function()
	{
		this._list = this._dispatcher = this._model = this._container = this._progressBar = null;
	};
	BX.extend(BX.CrmDealListItemView, BX.CrmEntityView);
	BX.CrmDealListItemView.prototype.doInitialize = function()
	{
		this._list = this.getSetting("list", null);
		this._dispatcher = this.getSetting("dispatcher", null);
		this._model = this.getSetting("model", null);
		this._container = this.getSetting("container", null);

		if(!this._model && this._container)
		{
			var info = BX.findChild(this._container, { className: "crm_entity_info" }, true, false);
			this._model = info ? this._dispatcher.getModelById(info.value) : null;
		}

		if(this._model)
		{
			this._model.addView(this);
		}
	};
	BX.CrmDealListItemView.prototype.layout = function()
	{
		if(this._container)
		{
			BX.cleanNode(this._container);
		}
		else
		{
			var rootContainer = this.getSetting("rootContainer", null);
			this._container = BX.create("LI",
				{
					attrs: { "class": "crm_dealings_list_item" },
					events: { "click": BX.delegate(this._onContainerClick, this) }
				}
			);

			rootContainer.appendChild(this._container);
		}


		var m = this._model;
		if(!m)
		{
			return;
		}
		var c = this._container;

		var titleContainer = BX.create("DIV", { attrs: { className: "crm_dealings_title" } });
		titleContainer.appendChild(document.createTextNode(m.getStringParam("TITLE")));

		if(m.getFloatParam("OPPORTUNITY") > 0.0)
		{
			titleContainer.appendChild(
				BX.create("SPAN",
					{ text: " - " + m.getStringParam("FORMATTED_OPPORTUNITY") }
				)
			);
		}

		c.appendChild(titleContainer);

		var infoWrapper = BX.create("DIV",
			{
				attrs: { className: "crm_dealings_company" }
			}
		);

		c.appendChild(infoWrapper);

		infoWrapper.appendChild(
			BX.create("SPAN", { text: m.getStringParam("CLIENT_TITLE") })
		);

		var entityId =  m.getId();
		var bar = this._progressBar = BX.CrmProgressBar.create(
			"DEAL_" + entityId,
			{
				entityType: "DEAL",
				entityId: entityId,
				currentStepId: m.getStringParam("STAGE_ID"),
				rootContainer: infoWrapper
			}
		);
		bar.layout();

		c.appendChild(
			BX.create("DIV", { attrs: { className: "clb" } })
		);
	};
	BX.CrmDealListItemView.prototype.clearLayout = function()
	{
		if(this._container)
		{
			BX.remove(this._container);
			this._container = null;
		}
	};
	BX.CrmDealListItemView.prototype.scrollInToView = function()
	{
		if(this._container)
		{
			BX.scrollToNode(this._container);
		}
	};
	BX.CrmDealListItemView.prototype.getModelKey = function()
	{
		return this._model ? this._model.getKey() : "";
	};

	BX.CrmDealListItemView.prototype._onContainerClick = function(e)
	{
		var m = this._model;
		if(!m)
		{
			return;
		}

		var showUrl = m.getDataParam("SHOW_URL", "");
		if(showUrl !== "")
		{
			BX.CrmMobileContext.redirect({ url: showUrl });
		}
	};
	BX.CrmDealListItemView.prototype.handleModelUpdate = function(model)
	{
		if(this._model !== model)
		{
			return;
		}

		this.layout();
		if(this._list)
		{
			this._list.handleItemUpdate(this);
		}
	};
	BX.CrmDealListItemView.prototype.handleModelDelete = function(model)
	{
		if(this._model !== model)
		{
			return;
		}

		this.clearLayout();
		if(this._list)
		{
			this._list.handleItemDelete(this);
		}
	};
	BX.CrmDealListItemView.create = function(settings)
	{
		var self = new BX.CrmDealListItemView();
		self.initialize(settings);
		return self;
	};
}
