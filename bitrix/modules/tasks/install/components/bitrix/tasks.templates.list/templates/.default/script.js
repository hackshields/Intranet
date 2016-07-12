var tasksMenuPopup = {};

function DeleteTemplate(templateId)
{
	BX.remove(BX("template-" + templateId));
	var data = {
		mode : "delete",
		sessid : BX.message("bitrix_sessid"),
		id : templateId
	};
	BX.ajax.post(ajaxUrl, data);
}

/*=====================Menu Popup===============================*/

function ShowMenuPopup(taskId, bindElement)
{
	if (tasksMenuPopup[taskId])
		BX.TaskMenuPopup.show(taskId, bindElement, tasksMenuPopup[taskId], { events : { onPopupClose: __onMenuPopupClose} });

	BX.addClass(bindElement, "task-menu-button-selected");

	return false;
}

function __onMenuPopupClose()
{
	BX.removeClass(this.bindElement, "task-menu-button-selected");
}

function SwitchTaskFilter(link)
{
	if (BX.hasClass(link, "task-filter-mode-selected"))
		return false;
	
	BX.toggleClass(link.parentNode.parentNode.parentNode, "task-filter-advanced-mode");

	var links = link.parentNode.getElementsByTagName("a");
	for (var i = 0; i < links.length; i++)
		BX.toggleClass(links[i], "task-filter-mode-selected");

	return false;
}

function SortTable(url, e)
{
	if(!e) e = window.event;
	window.location = url;
	BX.PreventDefault(e);
}