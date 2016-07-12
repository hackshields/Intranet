function onFilterGroupSelect(arGroups)
{
	if (arGroups[0])
	{
		document.forms["log_filter"]["flt_group_id"].value = arGroups[0].id;
		BX.removeClass(BX("filter-field-group").parentNode.parentNode, "webform-field-textbox-empty");
	}
}

function onFilterCreatedBySelect(arUser)
{
	if (arUser.id)
	{
		document.forms["log_filter"]["flt_created_by_id"].value = arUser.id;
		document.forms["log_filter"]["filter-field-created-by"].value = arUser.name;
		BX.removeClass(BX("filter-field-created-by").parentNode.parentNode, "webform-field-textbox-empty");
		if (BX("flt_comments_cont"))
			BX("flt_comments_cont").style.visibility = "visible";
	}
	else if (BX("flt_comments_cont"))
		BX("flt_comments_cont").style.visibility = "hidden";

	filterCreatedByPopup.close();
}

var filterPopup = false;

function ShowFilterPopup(bindElement)
{
	if (!filterPopup)
	{
		filterPopup = new BX.PopupWindow(
			'bx_log_filter_popup', 
			bindElement,
			{
				closeIcon : false,
				offsetTop: 10,
				autoHide: true,
				offsetLeft: -200,
				zIndex : -100,
				className : 'sonet-log-filter-popup-window'
			}
		);

		var filter_block = BX("sonet-log-filter");
		filterPopup.setContent(filter_block);
	}

	filterPopup.show();

	BX.bind(BX("filter-field-created-by"), "click", function(e) {
		if(!e) e = window.event;

		filterCreatedByPopup = BX.PopupWindowManager.create("filter-created-by-popup", this.parentNode, {
			offsetTop : 1,
			autoHide : true,
			content : BX("FILTER_CREATEDBY_selector_content"),
			zIndex : 1200
		});

		if (filterCreatedByPopup.popupContainer.style.display != "block")
			filterCreatedByPopup.show();

		return BX.PreventDefault(e);
	});

	BX.bind(BX.findNextSibling(BX("filter-field-created-by"), {tagName : "a"}), "click", function(e){
		if(!e) e = window.event;

		BX("filter-field-created-by").value = "";
		BX("filter_field_createdby_hidden").value = "0";
		BX.addClass(BX("filter-field-created-by").parentNode.parentNode, "webform-field-textbox-empty");
		if (BX("flt_comments_cont"))
			BX("flt_comments_cont").style.visibility = "hidden";
		return BX.PreventDefault(e);
	});
	
	if (BX("filter-field-group"))
	{
		BX.bind(BX("filter-field-group"), "click", function(e) {
			if(!e) e = window.event;
			filterGroupsPopup.show();
			return BX.PreventDefault(e);
		});

		BX.bind(BX.findNextSibling(BX("filter-field-group"), {tagName : "a"}), "click", function(e){
			if(!e) e = window.event;

			filterGroupsPopup.deselect(BX("filter_field_group_hidden").value.value);
			BX("filter_field_group_hidden").value = "0";
			BX.addClass(BX("filter-field-group").parentNode.parentNode, "webform-field-textbox-empty");
			return BX.PreventDefault(e);
		});
	}
}

__logOnDateChange = function(sel)
{
	var bShowFrom=false, bShowTo=false, bShowHellip=false, bShowDays=false, bShowBr=false;

	if(sel.value == 'interval')
		bShowBr = bShowFrom = bShowTo = bShowHellip = true;
	else if(sel.value == 'before')
		bShowTo = true;
	else if(sel.value == 'after' || sel.value == 'exact')
		bShowFrom = true;
	else if(sel.value == 'days')
		bShowDays = true;
	
	BX('flt_date_from_span').style.display = (bShowFrom? '':'none');
	BX('flt_date_to_span').style.display = (bShowTo? '':'none');
	BX('flt_date_hellip_span').style.display = (bShowHellip? '':'none');
	BX('flt_date_day_span').style.display = (bShowDays? '':'none');
}

function __logOnReload(log_counter)
{
	if (BX("preset_filters"))
	{
		var arTabs = BX.findChildren(BX("preset_filters"), { className: 'sonet-log-pagetitle-button' }, false);
		for (var i = 0; i < arTabs.length; i++)
		{
			if (arTabs[i].id == 'preset_filter_all')
				BX.addClass(arTabs[i], 'sonet-log-pagetitle-button-active');
			else
				BX.removeClass(arTabs[i], 'sonet-log-pagetitle-button-active');
		}
	}

	if (BX("sonet_log_counter_preset"))
	{
		if (parseInt(log_counter) > 0)
		{
			BX("sonet_log_counter_preset").style.display = "inline-block";
			BX("sonet_log_counter_preset").innerHTML = log_counter;
		}
		else
		{
			BX("sonet_log_counter_preset").innerHTML = '';
			BX("sonet_log_counter_preset").style.display = "none";
		}
	}
}