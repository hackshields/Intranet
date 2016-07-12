function __onKeyTags(event)
{
	if (!event)
		event = window.event;
	var key = (event.keyCode ? event.keyCode : (event.which ? event.which : null));
    if (key == 13)
        addTag();
}

function __MPFonAfterMFLDeleteFile(file_id)
{

	if (BX('mfu_file_id_' + file_id))
	{
		BX.remove(BX('mfu_file_id_' + file_id));

		if (BX('newpost_photo_counter'))
		{
			BX('newpost_photo_counter').value = parseInt(BX('newpost_photo_counter').value) - 1;

			if (BX('newpost_photo_counter_title') && BX('newpost_photo_counter_title').firstChild)
			{
				BX.adjust(BX('newpost_photo_counter_title').firstChild, {
					html : BX('newpost_photo_counter').value
				});
				if (parseInt(BX('newpost_photo_counter').value) <= 0)
					BX('newpost_photo_counter_title').style.display = 'none';
			}
		}
	}

}
function __MPFonAfterSelectMentions(data)
{
	data.users = data.a_users;
	if (data.users != 'undefined' && data.users.length > 0)
		BX('POST_MESSAGE').value += ' [USER=' + data.users[0].ID + ']' + data.users[0].NAME + '[/USER] ';
}

function __MPFonAfterSelectDestinations(data)
{
	var prefix = '';
	data.users = data.a_users;
	data.groups = data.b_groups;

	if (
		(data.users != 'undefined' && data.users.length > 0)
		|| (data.groups != 'undefined' && data.groups.length > 0)
	)
	{
		var elements = BX.findChildren(BX('feed-add-post-destination-container'), {tagName: 'span', className: 'newpost-button-destination-item'}, true);
		if (elements != null)
		{
			for (var j = 0; j < elements.length; j++)
				BX.remove(elements[j]);
		}

		if (data.users != 'undefined' && data.users.length > 0)
		{
			for (var j = 0; j < data.users.length; j++)
			{
				prefix = (data.users[j].ID == 0 ? 'UA' : 'U');

				BX('feed-add-post-destination-container').appendChild(
					BX.create("span", { props: { className: 'newpost-button-destination-item' }, attrs : { 'data-id' : 'U' + (data.users[j].ID == 0 ? 'A' : data.users[j].ID) }, children: [
						BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'SPERM[' + prefix + '][]', 'value' : 'U' + (data.users[j].ID == 0 ? 'A' : data.users[j].ID) }}),
						BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'SPERM_NAME[' + prefix + '][]', 'value' : (data.users[j].ID == 0 ? 'UA' : data.users[j].NAME) }}),
						BX.create("span", { html : data.users[j].NAME})
					]})
				);
			}
		}

		if (data.groups != 'undefined' && data.groups.length > 0)
		{
			prefix = 'SG';
			for (var j = 0; j < data.groups.length; j++)
			{
				BX('feed-add-post-destination-container').appendChild(
					BX.create("span", { props: { className: 'newpost-button-destination-item' }, attrs : { 'data-id' : 'SG' + data.groups[j].ID }, children: [
						BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'SPERM[' + prefix +'][]', 'value' : 'SG' + data.groups[j].ID }}),
						BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'SPERM_NAME[' + prefix + '][]', 'value' : BX.util.htmlspecialchars(data.groups[j].NAME) }}),
						BX.create("span", { html : BX.util.htmlspecialchars(data.groups[j].NAME)})
					]})
				);
			}
		}

	}

}

function __MPFDestinationInit(item, type)
{
	prefix = 'S';
	if (type == 'sonetgroups')
		prefix = 'SG';
	else if (type == 'groups')
		prefix = 'UA';
	else if (type == 'users')
		prefix = 'U';
	else if (type == 'department')
		prefix = 'DR';

	BX.cleanNode('feed-add-post-destination-container', false);

	BX('feed-add-post-destination-container').appendChild(
		BX.create("span", { props: { className: 'newpost-button-destination-item' }, attrs : { 'data-id' : item.id }, children: [
			BX.create("input", { attrs : { 'type' : 'hidden', 'name' : 'SPERM[' + prefix + '][]', 'value' : item.id }}),
			BX.create("span", { html : item.name})
		]})
	);
}

function __MPFDestinationInitEx(arItems)
{
	BX.cleanNode('feed-add-post-destination-container', false);

	for (var j = 0; j < arItems.length; j++)
	{
		prefix = 'S';
		if (arItems[j].type == 'sonetgroups')
			prefix = 'SG';
		else if (arItems[j].type == 'groups')
			prefix = 'UA';
		else if (arItems[j].type == 'users')
			prefix = 'U';
		else if (arItems[j].type == 'department')
			prefix = 'DR';

		BX('feed-add-post-destination-container').appendChild(
			BX.create("span", { 
				props: { className: 'newpost-button-destination-item' }, 
				attrs : { 'data-id' : arItems[j].item.id }, 
				children: 
				[
					BX.create("input", { 
						attrs : { 'type' : 'hidden', 'name' : 'SPERM[' + prefix + '][]', 'value' : arItems[j].item.id }
					}),
					BX.create("span", { html : arItems[j].item.name})
				]
			})
		);
	}
}

// remove block
function BXfpdUnSelectCallback(item, type, search)
{
	var elements = BX.findChildren(BX('feed-add-post-destination-item'), {attribute: {'data-id': ''+item.id+''}}, true);
	if (elements != null)
	{
		for (var j = 0; j < elements.length; j++)
			BX.remove(elements[j]);
	}
	BX('feed-add-post-destination-input').value = '';
}
function BXfpdOpenDialogCallback()
{
	BX.style(BX('feed-add-post-destination-input-box'), 'display', 'inline-block');
	BX.style(BX('bx-destination-tag'), 'display', 'none');
	BX.focus(BX('feed-add-post-destination-input'));
}

function BXfpdCloseDialogCallback()
{
	if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-add-post-destination-input').value.length <= 0)
	{
		BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
		BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
		BXfpdDisableBackspace();
	}
}

function BXfpdCloseSearchCallback()
{
	if (!BX.SocNetLogDestination.isOpenSearch() && BX('feed-add-post-destination-input').value.length > 0)
	{
		BX.style(BX('feed-add-post-destination-input-box'), 'display', 'none');
		BX.style(BX('bx-destination-tag'), 'display', 'inline-block');
		BX('feed-add-post-destination-input').value = '';
		BXfpdDisableBackspace();
	}

}
function BXfpdDisableBackspace(event)
{
	if (BX.SocNetLogDestination.backspaceDisable || BX.SocNetLogDestination.backspaceDisable != null)
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);

	BX.bind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable = function(event){
		if (event.keyCode == 8)
		{
			BX.PreventDefault(event);
			return false;
		}
	});
	setTimeout(function(){
		BX.unbind(window, 'keydown', BX.SocNetLogDestination.backspaceDisable);
		BX.SocNetLogDestination.backspaceDisable = null;
	}, 5000);
}

function CustomizeLightEditorForBlog(editorId)
{
	BX.addCustomEvent(window, 'LHE_OnInit', function(pLEditor){
		if (pLEditor.id == editorId)
		{
			BX.bind(pLEditor.pEditorDocument, 'keyup', function(){pLEditor.SaveSelectionRange(); return true;});
			BX.bind(pLEditor.pEditorDocument, 'touchend', function(){pLEditor.SaveSelectionRange(); return true;});
		}
	});

	BX.addCustomEvent(window, 'LHE_OnBeforeParsersInit', function(pLEditor){
		if (pLEditor.id == editorId)
			pLEditor.AddParser({
				name: 'bloguser',
				obj: {
					Parse: function(sName, sContent, pLEditor)
					{
						sContent = sContent.replace(/\[USER\s*=\s*(\d+)\]((?:\s|\S)*?)\[\/USER\]/ig, function(str, id, name)
						{
							var
								id = parseInt(id),
								name = BX.util.trim(name);

							return '<span id="' + pLEditor.SetBxTag(false, {tag: "bloguser", params: {value : id}}) + '" style="color: #2067B0; border-bottom: 1px dashed #2067B0;">' + name + '</span>';
						});
						return sContent;
					},
					UnParse: function(bxTag, pNode, pLEditor)
					{
						if (bxTag.tag == 'bloguser')
						{
							var name = '';
							for (var i = 0; i < pNode.arNodes.length; i++)
								name += pLEditor._RecursiveGetHTML(pNode.arNodes[i]);
							name = BX.util.trim(name);
							return "[USER=" + bxTag.params.value + "]" + name +"[/USER]";
						}
						return "";
					}
				}
			});
	});
}
