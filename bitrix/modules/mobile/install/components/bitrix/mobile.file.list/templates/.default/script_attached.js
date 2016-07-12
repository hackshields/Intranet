BX.ready(
	function() {

		var wrap = document.getElementById('fl-wrapper');
		if (wrap)
			wrap.addEventListener(/*'touchstart'*/'click', function(event) {
				event.preventDefault();
				if(event.target.getAttribute('data-removable-icon') == 'true') 
				{
					var childrenList = event.target.parentNode.parentNode.childNodes;
					BX.toggleClass(event.target, "fl-delete-column");
					for(var i=0; i<childrenList.length; i++) 
					{
						if(childrenList[i].nodeType != 3)
						{
							if(childrenList[i].getAttribute('data-removable-btn')) 
							{
								BX.toggleClass(childrenList[i], "fl-delete-btn-open");
								var button = childrenList[i];
								button.addEventListener('click', function() {
									if (
										button.parentNode.id.length > 0
										&& button.parentNode.id.indexOf('mfl_item_') === 0
									)
									{
										 file_id = parseInt(button.parentNode.id.substr(9));
										 if (file_id > 0)
											__MFLDeleteFile(file_id);
									}
								});
							}
						}
					}
				}

			}, false);
	}
);

__MFLDeleteFile = function(file_id) {

	if (parseInt(file_id) > 0)
	{
		BMAjaxWrapper.Wrap({
			'type': 'json',
			'method': 'POST',
			'url': '/bitrix/components/bitrix/mobile.file.list/ajax.php',
			'data': {
				'file_id': file_id,
				'action': 'delete',
				'sessid' : BX.message('MFUSessID')
			},
			'callback': function(data) {
				if (BX('mfl_item_' + file_id))
					BX.toggleClass(BX('mfl_item_' + file_id), 'fl-block-close');

				if (
					data["SUCCESS"] != "undefined"
					&& data["SUCCESS"] == "Y"
					&& parseInt(data["FILE_ID"]) > 0
				)
					app.onCustomEvent('onAfterMFLDeleteFile', data["FILE_ID"]);
			},
			'callback_failure': function() { }
		});
	}
}