if(typeof(BX.CrmControlPanel) === "undefined")
{
	BX.CrmControlPanel = function()
	{
		this._id = "";
		this._settings = null;
		this._container = null;
		this._items = [];
		this._activeItem = null;
	};

	BX.CrmControlPanel.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : "";
			this._settings = settings ? settings : BX.CrmParamBag.create(null);
			this._container = BX(this.getSetting("containerId"));

			var itemInfos = this.getSetting("itemInfos", []);
			for(var i = 0; i < itemInfos.length; i++)
			{
				var itemInfo = itemInfos[i];
				var itemSettings = BX.CrmParamBag.create(BX.clone(itemInfo));
				itemSettings.setParam("panel", this);
				var item = BX.CrmControlPanelItem.create(itemSettings.getParam("id"), itemSettings);
				if(item.isActive())
				{
					this._activeItem = item;
				}
				this._items.push(item);
			}

			var searcButton = BX.findChild(this._container, { "tag": "SPAN", "class": "crm-search-btn" }, true, false);
			if(searcButton)
			{
				BX.bind(
					searcButton,
					"click",
					BX.delegate(this._onSearchButtonClick, this)
				);
			}
		},
		getSetting: function(name, defaultval)
		{
			return this._settings.getParam(name, defaultval);
		},
		getId: function()
		{
			return this._id;
		},
		getItemContainerId: function(id)
		{
			return this.getSetting("itemContainerPrefix", "crm_ctrl_panel_item_") + id;
		},
		requireItemActivityChange: function(item)
		{
			return true;
		},
		handleItemActivityChange: function(item)
		{
			if(!item.isActive())
			{
				return;
			}

			if(this._activeItem !== null && this._activeItem !== item)
			{
				this._activeItem.setActive(false, true, true);
			}

			this._activeItem = item;
		},
		_onSearchButtonClick: function(e)
		{
			var searcForm = BX.findChild(this._container, { "tag": "FORM", "class": "crm-search" }, true, false);
			if(searcForm)
			{
				searcForm.submit();
			}
		}
	};

	BX.CrmControlPanel.items = {};
	BX.CrmControlPanel.create = function(id, settings)
	{
		var self = new BX.CrmControlPanel();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}

if(typeof(BX.CrmControlPanelItem) === "undefined")
{
	BX.CrmControlPanelItem = function()
	{
		this._id = "";
		this._settings = null;
		this._panel = null;
		this._container = null;
		this._actions = null;
		this._childItems = null;
	};

	BX.CrmControlPanelItem.prototype =
	{
		initialize: function(id, settings)
		{
			this._id = BX.type.isNotEmptyString(id) ? id : "";
			this._menuId = 'crm_menu_popup_' + this._id.toLowerCase();
			this._settings = settings ? settings : BX.CrmParamBag.create(null);
			this._panel = this.getSetting("panel");
			this._container = BX(this._panel.getItemContainerId(this._id));
			this._isActive = this.getSetting("isActive", false);
			this._actions = this.getSetting("actions", []);
			this._childItems = this.getSetting("childItems", []);

			BX.bind(
				this._getLink(),
				"click",
				BX.delegate(this._onClick, this)
			);

			if(this._findAction("CREATE"))
			{
				this._container.appendChild(
					BX.create(
						"DIV",
						{
							"attrs": { "class": "crm-menu-plus-btn" },
							"events": { "click": BX.delegate(this._onCreateButtonClick, this) }
						}
					)
				);
			}
		},
		getSetting: function(name, defaultval)
		{
			return this._settings.getParam(name, defaultval);
		},
		getId: function()
		{
			return this._id;
		},
		isActive: function()
		{
			return this._isActive;
		},
		setActive: function(active, force, silent)
		{
			active = !!active;
			if(this._isActive === active)
			{
				return;
			}

			if(!force && !this._panel.requireItemActivityChange(this))
			{
				return;
			}

			this._isActive = active;
			if(active)
			{
				BX.addClass(this._getLink(), "crm-menu-item-active");
			}
			else
			{
				BX.removeClass(this._getLink(), "crm-menu-item-active");
			}

			if(!silent)
			{
				this._panel.handleItemActivityChange(this);
			}
		},
		hasUrl: function()
		{
			var url = this.getSetting("url");
			return url !== "" && url !== "#";
		},
		hasChildItems: function()
		{
			return this._childItems.length > 0;
		},
		showSubMenu: function()
		{
			var menuItems = [];
			for(var i = 0; i < this._childItems.length; i++)
			{
				var childItem = this._childItems[i];
				var name = childItem["name"];
				if(!BX.type.isNotEmptyString(name))
				{
					continue;
				}

				var className = BX.type.isNotEmptyString(childItem["icon"]) ? "crm-menu-more-" + childItem["icon"] : "";
				menuItems.push(
					{ "text": name, "className": className , "href" : BX.type.isNotEmptyString(childItem["url"]) ? childItem["url"] : "" }
				);
			}

			if(menuItems.length === 0)
			{
				return;
			}

			if(typeof(BX.PopupMenu.Data[this._menuId]) !== "undefined")
			{
				BX.PopupMenu.Data[this._menuId].popupWindow.destroy();
				delete BX.PopupMenu.Data[this._menuId];
			}

			var anchor = this._getLink();
			var anchorPos = BX.pos(anchor);

			BX.PopupMenu.show(
				this._menuId,
				anchor,
				menuItems,
				{
					"autoHide": true,
					"offsetLeft": (anchorPos["width"] / 2) - 18,
					"offsetTop": 4,
					"angle":
					{
						"position": "top",
						"offset": 20
					},
					"events":
					{
						"onPopupClose" : BX.delegate(this._onSubMenuClose, this)
					}
				}
		   );
		},
		_onSubMenuClose: function()
		{
			this.setActive(false, false, true);
		},
		_findAction: function(id)
		{
			for(var i = 0; i < this._actions.length; i++)
			{
				var action = this._actions[i];
				if(action["id"] === id)
				{
					return action;
				}
			}

			return null;
		},
		_processAction: function(action)
		{
			if(!action)
			{
				return;
			}

			if(BX.type.isNotEmptyString(action["script"]))
			{
				try{ eval(action["script"]); }
				catch(ex){}
			}

			if(BX.type.isNotEmptyString(action["url"]))
			{
				window.location = action["url"];
			}
		},
		_getLink: function()
		{
			return BX.findChild(this._container, { "tag": "A", "class": "crm-menu-item" }, true, false);
		},
		_onClick: function(e)
		{
			if(this.hasChildItems())
			{
				if(!this.isActive())
				{
					this.setActive(true, false, true);
					this.showSubMenu();
				}
				return BX.PreventDefault(e);
			}

			if(!this.isActive())
			{
				this.setActive(true);
			}

			return this.hasUrl() ? true : BX.PreventDefault(e);
		},
		_onCreateButtonClick: function(e)
		{
			this._processAction(this._findAction("CREATE"));
		}
	};

	BX.CrmControlPanelItem.items = {};
	BX.CrmControlPanelItem.create = function(id, settings)
	{
		var self = new BX.CrmControlPanelItem();
		self.initialize(id, settings);
		this.items[self.getId()] = self;
		return self;
	};
}