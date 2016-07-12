if(typeof(BX.CrmMobileContext) === "undefined")
{
	BX.CrmMobileContext = function()
	{
		this._reloadOnPullDown = false
	};

	BX.CrmMobileContext.prototype =
	{
		initialize: function()
		{
		},
		isOffLine: function()
		{
			return BMAjaxWrapper && BMAjaxWrapper.offline
		},
		createMenu: function(menuItems)
		{
			if(app)
			{
				app.menuCreate({ items: menuItems });
			}
		},
		showMenu: function()
		{
			if(app)
			{
				app.menuShow();
			}
		},
		beginRequest: function(params)
		{
			if(!BMAjaxWrapper)
			{
				return false;
			}

			BMAjaxWrapper.Wrap(params);
			return true;
		},
		redirect: function(params)
		{
			if(app)
			{
				app.loadPageBlank(params);
			}
		},
		reload: function()
		{
			if(app)
			{
				app.reload();
			}
		},
		enableReloadOnPullDown: function(params)
		{
			if(!params)
			{
				params = {};
			}

			this._reloadOnPullDown = true;

			if(app)
			{
				app.pullDown(
					{
						enable: true,
						pulltext: BX.type.isNotEmptyString(params["pullText"]) ? params["pullText"] : "",
						downtext: BX.type.isNotEmptyString(params["downText"]) ? params["downText"] : "",
						loadtext: BX.type.isNotEmptyString(params["loadText"]) ? params["loadText"] : "",
						callback: BX.delegate(this._onPagePullDown, this)
					}
				);
			}
		},
		showWait: function()
		{
			if(app)
			{
				app.showLoadingScreen();
			}
		},
		hideWait: function()
		{
			if(app)
			{
				app.hideLoadingScreen();
			}
		},
		_onPagePullDown: function(e)
		{
			if(app && this._reloadOnPullDown)
			{
				app.reload();
			}
		}
	};

	BX.CrmMobileContext.current = null;
	BX.CrmMobileContext.getCurrent = function()
	{
		if(!this.current)
		{
			this.current = new BX.CrmMobileContext();
			this.current.initialize();
		}

		return this.current;
	};
	BX.CrmMobileContext.redirect = function(params)
	{
		this.getCurrent().redirect(params);
	};
}

if(typeof(BX.CrmEntityDispatcher) === "undefined")
{
	BX.CrmEntityDispatcher = function()
	{
		this._id = "";
		this._settings = {};
		this._models = {};
	};

	BX.CrmEntityDispatcher.prototype =
	{
		initialize: function(id, settings)
		{
			//alert('CrmEntityDispatcher: init');
			this._id = id;
			this._settings = settings ? settings : {};

			var typeName = this.getTypeName();

			// Initialize models
			var data = this.getSetting("data", []);
			for(var i = 0; i < data.length; i++)
			{
				this.createEntityModel(data[i], typeName, true);
			}

			// Start to listen "push&pull" events for model synchronization
			var pullTag = this.getSetting("pullTag", "");
			if(app && BX.type.isNotEmptyString(pullTag))
			{
				//alert('CrmEntityDispatcher: init, attach on onPullExtendWatch');
				app.onCustomEvent("onPullExtendWatch", { id: pullTag });
			}

			//alert('CrmEntityDispatcher: init, attach onPull');
			BX.addCustomEvent("onPull", BX.delegate(this, this._onPull));
			//BX.addCustomEvent("onPull", function(data){ alert('cmd:' + data['command']); });
		},
		getId: function()
		{
			return this._id;
		},
		getSettings: function()
		{
			return this._settings;
		},
		getSetting: function(name, defaultVal)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
		},
		getTypeName: function()
		{
			return this.getSetting("typeName", "");
		},
		createEntityModel: function(data, typeName, register)
		{
			if(!BX.type.isNotEmptyString(typeName))
			{
				typeName = data && BX.type.isNotEmptyString(data["__TYPE_NAME"]) ? data["__TYPE_NAME"] : "";
				if(typeName === "")
				{
					typeName = this.getTypeName();
				}
			}

			var model = null;
			if(typeName === "DEAL")
			{
				model = BX.CrmDealModel.create(data);
			}
			else if(typeName === "CONTACT")
			{
				model = BX.CrmContactModel.create(data);
			}
			else if(typeName === "COMPANY")
			{
				model = BX.CrmCompanyModel.create(data);
			}
			else if(typeName === "LEAD")
			{
				model = BX.CrmLeadModel.create(data);
			}
			else if(typeName === "ACTIVITY")
			{
				model = BX.CrmActivityModel.create(data);
			}
			else if(typeName === "EVENT")
			{
				model = BX.CrmEventModel.create(data);
			}
			else
			{
				model = BX.CrmEntityModel.create(data);
			}

			if(register === true)
			{
				this.registerEntityModel(model);
			}

			return model;
		},
		registerEntityModel: function(model)
		{
			this._models[model.getKey()] = model;
		},
		getModelById: function(id)
		{
			var key = this._getModelKey(id, this.getTypeName());
			return this._models.hasOwnProperty(key) ? this._models[key] : null;
		},
		_getModelKey: function(id, typeName)
		{
			return typeName.toUpperCase() + "_" + id.toString()
		},
		_onPull: function(data)
		{
			if(!(data && data["module_id"] === "crm"))
			{
				return;
			}

			var updateEventName = this.getSetting("updateEventName", "");
			var deleteEventName = this.getSetting("deleteEventName", "");

			var cmd = BX.type.isNotEmptyString(data["command"]) ? data["command"] : "";
			if(cmd === "")
			{
				return;
			}

			var entityId = data && data["params"] && data.params["ID"] ? parseInt(data.params.ID) : 0;
			if(isNaN(entityId) || entityId <= 0)
			{
				return;
			}

			var key = this._getModelKey(id, this.getTypeName());
			var model = this._models.hasOwnProperty(key) ? this._models[key] : null;

			if(!model)
			{
				return;
			}

			if(cmd === updateEventName)
			{
				alert("CrmEntityDispatcher: update of " + key);

				this.loadEntity(id);
			}
			else if(cmd === deleteEventName)
			{
				alert("CrmEntityDispatcher: delete of " + key);

				model.notifyDeleted();
				delete this._models[key];
			}
		},
		loadEntity: function(entityId, callback)
		{
			var typeName = this.getTypeName();
			var self = this;
			BX.ajax(
				{
					url: this.getSetting("serviceUrl", ""),
					method: "POST",
					dataType: "json",
					data:
					{
						"ACTION" : "GET_ENTITY",
						"ENTITY_TYPE_NAME": typeName,
						"ENTITY_ID": entityId,
						"FORMAT_PARAMS": this.getSetting("formatParams", {})
					},
					onsuccess: function(data)
					{
						var entityData = data && data["DATA"] && data["DATA"]["ENTITY"] ? data["DATA"]["ENTITY"] : null;

						if(entityData)
						{
							var model = self.getModelById(entityId);
							if(model)
							{
								model.setData(entityData);
								model.notifyUpdated();
							}
							else
							{
								model = self.createEntityModel(entityData, typeName);
								entityId = model.getId();
								if(entityId > 0)
								{
									self._models[self._getModelKey(entityId, typeName)] = model;
								}
							}
						}

						if(typeof(callback) === "function")
						{
							callback(entityData);
						}
					},
					onfailure: function(data)
					{
					}
				}
			);
		}
	};

	BX.CrmEntityDispatcher.items = {};
	BX.CrmEntityDispatcher.create = function(id, settings)
	{
		var self = new BX.CrmEntityDispatcher();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}

if(typeof(BX.CrmEntityModel) === "undefined")
{
	BX.CrmEntityModel = function()
	{
	};

	BX.CrmEntityModel.prototype =
	{
		initialize: function(data)
		{
			this._data = data ? data : {};
			this._views = [];
		},
		getData: function()
		{
			return this._data;
		},
		setData: function(data)
		{
			this._data = data ? data : {};
		},
		getDataParam: function(name, defaultVal)
		{
			return this._data.hasOwnProperty(name) ? this._data[name] : defaultVal;
		},
		getStringParam: function(name, defaultVal)
		{
			if(typeof(defaultVal) === "undefined")
			{
				defaultVal = "";
			}

			return this._data.hasOwnProperty(name) ? this._data[name] : defaultVal;
		},
		getFloatParam: function(name, defaultVal)
		{
			if(typeof(defaultVal) === "undefined")
			{
				defaultVal = 0.0;
			}

			return this._data.hasOwnProperty(name) ? parseFloat(this._data[name]) : defaultVal;
		},
		getId: function()
		{
			return parseInt(this.getDataParam("ID", 0));
		},
		getTypeName: function()
		{
			return this.getDataParam("__TYPE_NAME", "");
		},
		getKey: function()
		{
			var typeName = this.getTypeName();
			if(typeName === "")
			{
				typeName = "ENTITY";
			}
			return typeName + '_' + this.getId().toString();
		},
		addView: function(view)
		{
			this._views.push(view);
		},
		removeView: function(view)
		{
			for(var i = 0; i < this._views.length; i++)
			{
				if(this._views[i] === view)
				{
					this._views.splice(i, 1);
				}
			}
		},
		notifyUpdated: function()
		{
			alert("CrmEntityModel: notifyUpdated of " + this.getId());

			for(var i = 0; i < this._views.length; i++)
			{
				try
				{
					this._views[i].handleModelUpdate(this);
				}
				catch(e)
				{
				}
			}
		},
		notifyDeleted: function()
		{
			alert("CrmEntityModel: notifyDeleted of " + this.getId());

			for(var i = 0; i < this._views.length; i++)
			{
				try
				{
					this._views[i].handleModelDelete(this);
				}
				catch(e)
				{
				}
			}
		}
	};

	BX.CrmEntityModel.create = function(data)
	{
		var self = new BX.CrmEntityModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmDealModel) === "undefined")
{
	BX.CrmDealModel = function()
	{
	};

	BX.extend(BX.CrmDealModel, BX.CrmEntityModel);

	BX.CrmDealModel.prototype.getTypeName = function()
	{
		return "DEAL";
	};

	BX.CrmDealModel.prototype.getKey = function()
	{
		return 'DEAL_' + this.getId().toString();
	};

	BX.CrmDealModel.create = function(data)
	{
		var self = new BX.CrmDealModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmContactModel) === "undefined")
{
	BX.CrmContactModel = function()
	{
	};
	BX.extend(BX.CrmContactModel, BX.CrmEntityModel);
	BX.CrmContactModel.prototype.getTypeName = function()
	{
		return "CONTACT";
	};
	BX.CrmContactModel.prototype.getKey = function()
	{
		return 'CONTACT_' + this.getId().toString();
	};
	BX.CrmContactModel.create = function(data)
	{
		var self = new BX.CrmContactModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmCompanyModel) === "undefined")
{
	BX.CrmCompanyModel = function()
	{
	};
	BX.extend(BX.CrmCompanyModel, BX.CrmEntityModel);

	BX.CrmCompanyModel.prototype.getTypeName = function()
	{
		return "COMPANY";
	};
	BX.CrmCompanyModel.prototype.getKey = function()
	{
		return 'COMPANY_' + this.getId().toString();
	};
	BX.CrmCompanyModel.create = function(data)
	{
		var self = new BX.CrmCompanyModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmLeadModel) === "undefined")
{
	BX.CrmLeadModel = function()
	{
	};
	BX.extend(BX.CrmLeadModel, BX.CrmEntityModel);

	BX.CrmLeadModel.prototype.getTypeName = function()
	{
		return "LEAD";
	};
	BX.CrmLeadModel.prototype.getKey = function()
	{
		return 'LEAD_' + this.getId().toString();
	};
	BX.CrmLeadModel.create = function(data)
	{
		var self = new BX.CrmLeadModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmActivityModel) === "undefined")
{
	BX.CrmActivityModel = function()
	{
	};
	BX.extend(BX.CrmActivityModel, BX.CrmEntityModel);

	BX.CrmActivityModel.prototype.getTypeName = function()
	{
		return "ACTIVITY";
	};
	BX.CrmActivityModel.create = function(data)
	{
		var self = new BX.CrmActivityModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmEventModel) === "undefined")
{
	BX.CrmEventModel = function()
	{
	};
	BX.extend(BX.CrmEventModel, BX.CrmEntityModel);

	BX.CrmEventModel.prototype.getTypeName = function()
	{
		return "EVENT";
	};
	BX.CrmEventModel.create = function(data)
	{
		var self = new BX.CrmEventModel();
		self.initialize(data);
		return self;
	};
}

if(typeof(BX.CrmEntityView) === "undefined")
{
	BX.CrmEntityView = function()
	{
	};

	BX.CrmEntityView.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};
			this.doInitialize();
		},
		getSettings: function()
		{
			return this._settings;
		},
		getSetting: function(name, defaultVal)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
		},
		doInitialize: function()
		{
		},
		layout: function()
		{
		},
		clearLayout: function()
		{
		},
		getContainer: function()
		{
			return null;
		},
		getModel: function()
		{
			return null;
		},
		getModelKey: function()
		{
			return "";
		},
		handleModelUpdate: function(model)
		{
		},
		handleModelDelete: function(model)
		{
		}
	};
	BX.CrmEntityView.create = function(settings)
	{
		var self = new BX.CrmEntityView();
		self.initialize(settings);
		return self;
	};
}

if(typeof(BX.CrmEntityFilterPreset) === "undefined")
{
	BX.CrmEntityFilterPreset = function()
	{
		this._settings = {};
		this._owner = null;
	};

	BX.CrmEntityFilterPreset.prototype =
	{
		initialize: function(settings, owner)
		{
			this._settings = settings ? settings : {};
			this._owner = owner;
		},
		getSettings: function()
		{
			return this._settings;
		},
		getSetting: function(name, defaultVal)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
		},
		getId: function()
		{
			return this.getSetting("id", "");
		},
		getName: function()
		{
			return this.getSetting("name", "");
		},
		getFields: function()
		{
			return this.getSetting("fields", {});
		},
		apply: function()
		{
			if(this._owner && typeof(this._owner["applyFilterPreset"]) === "function")
			{
				this._owner.applyFilterPreset(this);
			}
		},
		createApplyDelagate: function()
		{
			return BX.delegate(this.apply, this);
		}
	};

	BX.CrmEntityFilterPreset.create = function(settings, owner)
	{
		var self = new BX.CrmEntityFilterPreset();
		self.initialize(settings, owner);
		return self;
	}
}

if(typeof(BX.CrmEntityFilterPresetButton) === "undefined")
{
	BX.CrmEntityFilterPresetButton = function()
	{
		this._settings = {};
		this._container = this._button = this._preset = null;
		this._isActive = false;
	}

	BX.CrmEntityFilterPresetButton.prototype =
	{
		initialize: function(settings)
		{
			this._settings = settings ? settings : {};

			this._preset = this.getSetting("preset");
			this._container = this.getSetting("container");
			this._button = this.getSetting("button");
			if(this._button && this._preset)
			{
				BX.bind(this._button, "click", BX.delegate(this._onButtonClick, this));
			}

			this._isActive = BX.hasClass(this._container, "current");
		},
		getSettings: function()
		{
			return this._settings;
		},
		getSetting: function(name, defaultVal)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
		},
		getPresetId: function()
		{
			return this._preset ? this._preset.getId() : "";
		},
		isActive: function()
		{
			return this._isActive;
		},
		setActive: function(active)
		{
			active = !!active;
			this._isActive = active;

			if(!this._container)
			{
				return;
			}

			if(active)
			{
				BX.addClass(this._container, "current");
			}
			else
			{
				BX.removeClass(this._container, "current");
			}
		},
		_onButtonClick: function(e)
		{
			if(this._preset)
			{
				this._preset.apply();
			}

			return BX.PreventDefault(e);
		}
	};

	BX.CrmEntityFilterPresetButton.create = function(settings)
	{
		var self = new BX.CrmEntityFilterPresetButton();
		self.initialize(settings);
		return self;
	}
}

if(typeof(BX.CrmEntityListView) === "undefined")
{
	BX.CrmEntityListView = function()
	{
	};
	BX.CrmEntityListView.prototype =
	{
		initialize: function(id, settings)
		{
			this._items = {};
			this._waiter = null;
			this._maxWindowScroll = -1;
			this._scrollHandler = BX.delegate(this._onWindowScroll, this);
			this._isRequestStarted = false;
			this._isSearchRequestStarted = false;
			this._filterPresets = [];
			this._filterPresetButtons = [];

			this._context = BX.CrmMobileContext.getCurrent();
			this._id = id;
			this._settings = settings ? settings : {};
			this._dispatcher = this.getSetting("dispatcher", null);
			this._wrapper = BX(this.getSetting("wrapperId", ""));
			this._container = this.getContainer();
			this._searchContainer = BX(this.getSetting("searchContainerId", ""));
			this._filterContainer = BX(this.getSetting("filterContainerId", ""));
			this._searchInput = BX.findChild(this._searchContainer, { className: "crm_search_input" }, true, false);
			this._searchButton = BX.findChild(this._searchContainer, { className: "crm_button" }, true, false);
			this._clearSearchButton = BX.findChild(this._searchContainer, { className: "crm_clear" }, true, false);

			this._isFiltered = this.getSetting("isFiltered", false);

			var waiterClassName = this.getWaiterClassName();
			var itemContainers = this.getItemContainers();
			if(BX.type.isArray(itemContainers))
			{
				for(var i = 0; i < itemContainers.length; i++)
				{
					var itemContainer = itemContainers[i];
					if(waiterClassName !== "" && BX.hasClass(itemContainer, waiterClassName))
					{
						this._waiter = itemContainer;
						continue;
					}

					var item = this.createItemView(
						{
							container: itemContainer,
							rootContainer: this._container,
							dispatcher: this._dispatcher,
							list: this
						}
					);

					this._items[item.getModelKey()] = item;
					/*if(i === 0)
					{
						item.scrollInToView();
					}*/
				}
			}

			var nextPageUrl = this.getNextPageUrl();
			if(nextPageUrl !== "")
			{
				this._synchronizeMaxScroll();
				BX.bind(window, "scroll", this._scrollHandler);
			}

			if(this._waiter)
			{
				this._waiter.style.display = nextPageUrl !== "" ? "" : "none";
			}

			if(this._searchInput)
			{
				BX.bind(this._searchInput, "focus", BX.delegate(this._onSearchFocus, this));
				BX.bind(this._searchInput, "blur", BX.delegate(this._onSearchBlur, this));
				BX.bind(this._searchInput, "keypress", BX.delegate(this._onSearchKey, this));
			}

			if(this._searchButton)
			{
				BX.bind(this._searchButton, "click", BX.delegate(this._onSearchClick, this));
			}

			if(this._clearSearchButton)
			{
				BX.bind(this._clearSearchButton, "click", BX.delegate(this._onClearSearchClick, this));
			}

			var filterPresetData = this.getSetting("filterPresets", []);
			if(BX.type.isArray(filterPresetData))
			{
				for(var j = 0; j < filterPresetData.length; j++)
				{
					this._filterPresets.push(
						BX.CrmEntityFilterPreset.create(filterPresetData[j], this)
					);
				}
			}

			if(this._filterContainer)
			{
				var menuItems = [];

				for(var k = 0; k < this._filterPresets.length; k++)
				{
					var curPreset = this._filterPresets[k];
					menuItems.push(
						{
							name: curPreset.getName(),
							arrowFlag: false,
							action: curPreset.createApplyDelagate()
						}
					);
				}

				if(menuItems.length > 0)
				{
					this._context.createMenu(menuItems);
					BX.bind(this._filterContainer, "click", BX.delegate(this._onFilterClick, this));
				}
			}

			if(this.getSetting("enablePresetButtons", false))
			{
				var filterPresetContainers = this.getFilterPresetContainers();
				if(filterPresetContainers)
				{
					for(var m = 0; m < filterPresetContainers.length; m++)
					{
						var presetContainer = filterPresetContainers[m];
						var presetData = BX.findChild(presetContainer, { className: "crm-filter-preset-data" }, true, false);
						var preset = presetData ? this.findFilterPreset(presetData.value) : null;

						if(!preset)
						{
							continue;
						}

						this._filterPresetButtons.push(
							BX.CrmEntityFilterPresetButton.create(
								{
									preset: preset,
									container: presetContainer,
									button: BX.findChild(presetContainer, { className: "crm-filter-preset-button" }, true, false)
								}
							)
						);
					}
				}
			}

			this.doInitialize();
		},
		doInitialize: function()
		{
		},
		getContainer: function()
		{
			return null;
		},
		getItemContainers: function()
		{
			return [];
		},
		getWaiterClassName: function()
		{
			return "";
		},
		getMessage: function(name, defaultVal)
		{
			return "";
		},
		createItemView: function(settings)
		{
			return null;
		},
		createSearchParams: function(val)
		{
			return null;
		},
		isFiltered: function()
		{
			return this._isFiltered;
		},
		addItemView: function(itemView)
		{
			var container = this.getContainer();
			var view = itemView.getContainer();
			if(container && view)
			{
				container.appendChild(view);
			}
		},
		removeItemView: function(itemView)
		{
			var view = itemView.getContainer();
			if(view)
			{
				BX.remove(view);
			}
		},
		getItemCount: function()
		{
			return this._items ? this._items.length : 0;
		},
		getFilterPresetContainers: function()
		{
			return BX.findChildren(this._wrapper, { className: "crm-filter-preset-button-container" }, true);
		},
		findFilterPreset: function(id)
		{
			for(var i = 0; i < this._filterPresets.length; i++)
			{
				var preset = this._filterPresets[i];
				if(preset.getId() === id)
				{
					return preset;
				}
			}
			return null;
		},
		getId: function()
		{
			return this._id;
		},
		getSettings: function()
		{
			return this._settings;
		},
		getSetting: function(name, defaultVal)
		{
			return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
		},
		setSetting: function(name, val)
		{
			this._settings[name] = val;
		},
		getDispatcher: function()
		{
			return this._dispatcher;
		},
		applyFilterPreset: function(preset)
		{
			if(!preset)
			{
				return;
			}

			var presetId = preset.getId();

			var params = {};
			if(presetId === "clear_filter")
			{
				params["clear_filter"] = "Y";
			}
			else
			{
				params["grid_filter_id"] = presetId;
				var fields = preset.getFields();
				for(var key in fields)
				{
					if(fields.hasOwnProperty(key))
					{
						params[key] = fields[key];
					}
				}
			}

			if(this._searchInput)
			{
				this._searchInput.value = "";
			}

			this._beginSearchRequest(params);
		},
		handleItemUpdate: function(item)
		{
			// Nothing to do...
		},
		handleItemDelete: function(item)
		{
			var key = item.getModelKey();
			if(this._items[key])
			{
				delete this._items[key];
			}
		},
		getNextPageUrl: function()
		{
			return this.getSetting("nextPageUrl", "");
		},
		setNextPageUrl: function(url)
		{
			this.setSetting("nextPageUrl", url);
			if(this._waiter)
			{
				this._waiter.style.display = url !== "" ? "" : "none";
			}
		},
		getSearchPageUrl: function()
		{
			return this.getSetting("searchPageUrl", "");
		},
		_processClearSearchClick: function()
		{
			return false;
		},
		_clearItems: function()
		{
			for(var key in this._items)
			{
				if(this._items.hasOwnProperty(key))
				{
					this._items[key].clearLayout();
				}
			}

			this._items = {};
		},
		_synchronizeItemData: function(data)
		{
			if(!BX.type.isArray(data))
			{
				return;
			}

			if(this._container && this._waiter)
			{
				this._container.removeChild(this._waiter);
			}

			for(var i = 0; i < data.length; i++)
			{
				var model = this.createModel(data[i], true);
				var item = this.createItemView(
					{
						container: null,
						rootContainer: this._container,
						dispatcher: this._dispatcher,
						model: model,
						list: this
					}
				);

				var key = item.getModelKey();
				if(this._items[key])
				{
					this._items[key].clearLayout();
					delete this._items[key];
				}

				this._items[key] = item;
				item.layout();
				/*if(i === 0)
				{
					item.scrollInToView();
				}*/
			}

			if(this._container && this._waiter)
			{
				this._container.appendChild(this._waiter);
			}
		},
		_synchronizeMaxScroll: function()
		{
			var windowSize = BX.GetWindowSize();
			this._maxWindowScroll = windowSize.scrollHeight - windowSize.innerHeight - 100;
		},
		_onWindowScroll: function()
		{
			if(this._maxWindowScroll <= 0)
			{
				return;
			}

			var windowScroll = BX.GetWindowScrollPos();
			if (windowScroll.scrollTop >= this._maxWindowScroll )
			{
				this._lastScrollTop = windowScroll.scrollTop;
				this._beginPagingRequest();
			}
		},
		_beginPagingRequest: function()
		{
			var nextPageUrl = this.getNextPageUrl();
			if(nextPageUrl === "" || this._isRequestStarted || this._context.isOffLine())
			{
				return;
			}

			BX.unbind(window, "scroll", this._scrollHandler);

			this._isRequestStarted = this._context.beginRequest(
				{
					url: nextPageUrl,
					method: "GET",
					type: "json", //BMAjaxWrapper expects 'type' in lower case only!
					processData: true,
					callback: BX.delegate(this._onPagingRequestSuccess, this),
					callback_failure: BX.delegate(this._onRequestFailure, this)
				}
			);
		},
		_beginSearchRequest: function(queryParams)
		{
			var searchPageUrl = this.getSearchPageUrl();
			if(searchPageUrl === "" || this._isSearchRequestStarted || this._context.isOffLine())
			{
				return;
			}

			BX.unbind(window, "scroll", this._scrollHandler);

			var url = searchPageUrl;
			var query = [];
			for(var key in queryParams)
			{
				if(!queryParams.hasOwnProperty(key))
				{
					continue;
				}

				var param = queryParams[key];
				if(!BX.type.isArray(param))
				{
					query.push(key + "=" + encodeURIComponent(queryParams[key]));
				}
				else
				{
					for(var i = 0; i < param.length; i++)
					{
						query.push(key + "[]=" + encodeURIComponent(param[i]));
					}
				}

			}
			if(query.length > 0)
			{
				url += (url.indexOf("?") >= 0 ? "&" : "?") + query.join("&");
			}

			this._context.showWait();
			this._isSearchRequestStarted = this._context.beginRequest(
				{
					url: url,
					method: "GET",
					type: "json",
					processData: true,
					callback: BX.delegate(this._onSearchRequestSuccess, this),
					callback_failure: BX.delegate(this._onRequestFailure, this)
				}
			);

			if(!this._isSearchRequestStarted)
			{
				this._context.hideWait();
			}
		},
		_onPagingRequestSuccess: function(data)
		{
			this._isRequestStarted = false;

			var resultData = data["DATA"] ? data["DATA"] : {};
			this._synchronizeItemData(resultData["MODELS"]);

			this.setNextPageUrl(
				BX.type.isNotEmptyString(resultData["NEXT_PAGE_URL"]) ? resultData["NEXT_PAGE_URL"] : ""
			);

			if(this.getNextPageUrl() !== "")
			{
				this._synchronizeMaxScroll();
				BX.bind(window, "scroll", this._scrollHandler);
			}
		},
		_onSearchRequestSuccess: function(data)
		{
			this._context.hideWait();
			this._isSearchRequestStarted = false;

			var resultData = data["DATA"] ? data["DATA"] : {};
			this._isFiltered = typeof(resultData["IS_FILTERED"]) !== "undefined" ? resultData["IS_FILTERED"] : true;

			this._clearItems();
			this._synchronizeItemData(resultData["MODELS"]);

			this.setNextPageUrl(
				BX.type.isNotEmptyString(resultData["NEXT_PAGE_URL"]) ? resultData["NEXT_PAGE_URL"] : ""
			);

			if(this.getNextPageUrl() !== "")
			{
				this._synchronizeMaxScroll();
				BX.bind(window, "scroll", this._scrollHandler);
			}

			if(this._filterContainer)
			{
				BX.cleanNode(this._filterContainer, false);
				this._filterContainer.appendChild(
					BX.create("SPAN", { attrs: { className: "crm_filter_icon" } })
				);

				var filterName = "";
				if(!this._isFiltered)
				{
					filterName = this.getMessage("notFiltered")
				}
				else
				{
					filterName = BX.type.isNotEmptyString(resultData["GRID_FILTER_NAME"])
						? resultData["GRID_FILTER_NAME"] : this.getMessage("customFilter");
				}

				this._filterContainer.appendChild(
					document.createTextNode(filterName)
				);

				this._filterContainer.appendChild(
					BX.create("SPAN", { attrs: { className: "crm_arrow_bottom" } })
				);
			}

			if(this._filterPresetButtons)
			{
				var gridFilterId = BX.type.isNotEmptyString(resultData["GRID_FILTER_ID"])
					? resultData["GRID_FILTER_ID"] : "";

				var i;
				var curPresetButton = null;
				if(gridFilterId !== "")
				{
					for(i = 0; i < this._filterPresetButtons.length; i++)
					{
						curPresetButton = this._filterPresetButtons[i];
						if(gridFilterId !== "" && curPresetButton.getPresetId() === gridFilterId)
						{
							curPresetButton.setActive(true);
						}
						else if(curPresetButton.isActive())
						{
							curPresetButton.setActive(false);
						}
					}
				}
				else
				{
					for(i = 0; i < this._filterPresetButtons.length; i++)
					{
						curPresetButton = this._filterPresetButtons[i];
						if(curPresetButton.getPresetId() === "clear_filter")
						{
							curPresetButton.setActive(true);
						}
						else if(curPresetButton.isActive())
						{
							curPresetButton.setActive(false);
						}
					}
				}
			}
		},
		_onRequestFailure: function()
		{
			this._context.hideWait();
			this._isSearchRequestStarted = false;
			BX.bind(window, "scroll", this._scrollHandler);
		},
		_onSearchFocus: function()
		{
			if(this._searchContainer)
			{
				BX.removeClass(this._searchContainer, "crm_search");
				BX.addClass(this._searchContainer, "crm_search active");
			}
		},
		_onSearchClick: function()
		{
			var searchParams = this.createSearchParams(
				this._searchInput ? this._searchInput.value : ""
			);

			if(searchParams)
			{
				this._beginSearchRequest(searchParams);
			}
		},
		_onClearSearchClick: function()
		{
			if(this._searchInput)
			{
				this._searchInput.value = "";
			}

			if(this._processClearSearchClick())
			{
				return;
			}

			if(this._isFiltered)
			{
				BX.CrmMobileContext.getCurrent().reload();
			}
		},
		_onSearchBlur: function()
		{
			var val = this._searchInput ? this._searchInput.value : "";
			if(val === "" && this._searchContainer)
			{
				BX.removeClass(this._searchContainer, "crm_search active");
				BX.addClass(this._searchContainer, "crm_search");
			}
		},
		_onSearchKey: function(e)
		{
			if(!e)
			{
				e = window.event;
			}

			if(e.keyCode != 13)
			{
				return;
			}

			var searchParams = this.createSearchParams(
				this._searchInput ? this._searchInput.value : ""
			);

			if(searchParams)
			{
				this._beginSearchRequest(searchParams);
			}
		},
		_onFilterClick: function()
		{
			this._context.showMenu();
		}
	};
}

if(typeof(BX.CrmDealStageManager) === "undefined")
{
	BX.CrmDealStageManager = function() {};

	BX.CrmDealStageManager.prototype =
	{
		getInfos: function() { return BX.CrmDealStageManager.infos; },
		getMessage: function(name)
		{
			var msgs = BX.CrmDealStageManager.messages;
			return BX.type.isNotEmptyString(msgs[name]) ? msgs[name] : "";
		}
	};

	BX.CrmDealStageManager.current = new BX.CrmDealStageManager();
	BX.CrmDealStageManager.infos =
	[
		{ "id": "NEW", "name": "In Progress", "sort": 10, "semantics": "process" },
		{ "id": "WON", "name": "Is Won", "sort": 20, "semantics": "success" },
		{ "id": "LOSE", "name": "Is Lost", "sort": 30, "semantics": "failure" }
	];

	BX.CrmDealStageManager.messages = {}
}

if(typeof(BX.CrmLeadStatusManager) === "undefined")
{
	BX.CrmLeadStatusManager = function() {};

	BX.CrmLeadStatusManager.prototype =
	{
		getInfos: function() { return BX.CrmLeadStatusManager.infos; },
		getMessage: function(name)
		{
			var msgs = BX.CrmLeadStatusManager.messages;
			return BX.type.isNotEmptyString(msgs[name]) ? msgs[name] : "";
		}
	};

	BX.CrmLeadStatusManager.current = new BX.CrmLeadStatusManager();
	BX.CrmLeadStatusManager.infos =
	[
		{ "id": "NEW", "name": "Not Processed", "sort": 10, "semantics": "process" },
		{ "id": "CONVERTED", "name": "Converted", "sort": 20, "semantics": "success" },
		{ "id": "JUNK", "name": "Junk", "sort": 30, "semantics": "failure" }
	];

	BX.CrmLeadStatusManager.messages = {}
}

if(typeof(BX.CrmProgressBar) === "undefined")
{
	BX.CrmProgressBar = function()
	{
		this._id = "";
		this._settings = null;
		this._rootContainer = null;
		this._container = null;
		this._entityId = 0;
		this._entityType = null;
		this._currentStepId = "";
		this._manager = null;
		this._stepInfos = null;
	};

	BX.CrmProgressBar.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = id;
			this._settings = settings ? settings : {};

			this._rootContainer = this.getSetting("rootContainer");
			this._entityId = parseInt(this.getSetting("entityId", 0));
			this._entityType = this.getSetting("entityType");
			this._currentStepId = this.getSetting("currentStepId");

			if(this._entityType === "DEAL")
			{
				this._manager = BX.CrmDealStageManager.current;
			}
			else if(this._entityType === "LEAD")
			{
				this._manager = BX.CrmLeadStatusManager.current;
			}

			this._stepInfos = this._manager.getInfos();
		},
		getSetting: function(name, defaultval)
		{
			return typeof(this._settings[name]) !== "undefined" ? this._settings[name] : defaultval;
		},
		layout: function(rootContainer)
		{
			if(!BX.type.isDomNode(rootContainer))
			{
				rootContainer = this._rootContainer;
			}
			var stepIndex = this._findStepInfoIndex(this._currentStepId);
			var stepInfo = stepIndex >= 0 ? this._stepInfos[stepIndex] : null;

			var semantics = stepInfo && BX.type.isNotEmptyString(stepInfo["semantics"]) ? stepInfo["semantics"] : "";
			var sort = stepInfo && typeof(stepInfo["sort"]) !== "undefined" ? parseInt(stepInfo["sort"]) : 0;

			var className = "crm-list-stage-bar-small";
			if(semantics === "success")
			{
				className += " crm-list-stage-end-good";
			}
			else if(semantics === "failure" || semantics === "apology")
			{
				className += " crm-list-stage-end-bad";
			}

			this._container = BX.create(
				"DIV",
				{
					attrs: { className: className }
				}
			);

			var table = BX.create(
				"TABLE",
				{
					attrs: { className: "crm-list-stage-bar-table-small" }
				}
			);
			this._container.appendChild(table);
			rootContainer.appendChild(this._container);

			var row = table.insertRow(-1);

			var infos = this._stepInfos;
			for(var i = 0; i < infos.length; i++)
			{
				var curInfo = infos[i];

				var curSemantics = BX.type.isNotEmptyString(curInfo["semantics"]) ? curInfo["semantics"] : "";
				if(curSemantics === "failure" || curSemantics === "apology")
				{
					break;
				}

				var curSort = typeof(curInfo["sort"]) !== "undefined" ? parseInt(curInfo["sort"]) : 0;

				var cell = row.insertCell(-1);
				cell.className = "crm-list-stage-bar-part";
				if(curSort <= sort)
				{
					cell.className += " crm-list-stage-passed";
				}

				cell.appendChild(
					BX.create("DIV",
						{
							attrs: { className: "crm-list-stage-bar-block" },
							children:
							[
								BX.create("DIV",
									{
										attrs: { className: "crm-list-stage-bar-btn" }
									}
								)
							]
						}
					)
				);
			}
		},
		_findStepInfoIndex: function(id)
		{
			var infos = this._stepInfos;
			for(var i = 0; i < infos.length; i++)
			{
				if(infos[i]["id"] === id)
				{
					return i;
				}
			}

			return -1;
		}
	};

	BX.CrmProgressBar.create = function(id, settings)
	{
		var self = new BX.CrmProgressBar();
		self.initialize(id, settings);
		return self;
	};

	BX.CrmProgressBar.layout = function(settings)
	{
		var self = this.create("", settings);
		self.layout();
	}
}
