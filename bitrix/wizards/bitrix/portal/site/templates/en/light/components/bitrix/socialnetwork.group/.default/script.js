function ToggleDescription()
{
	if (BX('bx_group_description'))
	{
		BX.toggleClass(BX('bx_group_description'), 'bx-group-description-hide-table');

		var val = 'Y';

		if (BX('bx_group_pagetitle_link_open') && BX('bx_group_pagetitle_link_closed'))
		{
			if (BX('bx_group_pagetitle_link_open').style.display == 'inline-block')
			{
				BX('bx_group_pagetitle_link_open').style.display = 'none';
				BX('bx_group_pagetitle_link_closed').style.display = 'inline-block';
				val = 'N';
			}
			else
			{
				BX('bx_group_pagetitle_link_closed').style.display = 'none';
				BX('bx_group_pagetitle_link_open').style.display = 'inline-block';
				val = 'Y';
			}
		}

		BX.userOptions.save('socialnetwork', 'sonet_group_description', 'state', val, false);
	}

	return false;
		
}

function InvitePopupGroup(groupId, groupName, e)
{
	if(!e) e = window.event;
	
	if (isLeftClick(e))
	{
		sonetGroupIFramePopup.Invite(groupId, groupName);
		return BX.PreventDefault(e);
	}
}

function isLeftClick(event)
{
	if (!event.which && event.button !== undefined)
	{
		if (event.button & 1)
			event.which = 1;
		else if (event.button & 4)
			event.which = 2;
		else if (event.button & 2)
			event.which = 3;
		else
			event.which = 0;
	}

	return event.which == 1 || (event.which == 0 && BX.browser.IsIE());
};