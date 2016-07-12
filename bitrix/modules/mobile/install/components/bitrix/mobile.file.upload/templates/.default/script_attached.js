__MFUCallback = function(data, loading_id)
{
	if (data.fileID && BX('mfu_file_container'))
	{
		var hidden = BX.create('INPUT', {
			props: {
				'id': 'mfu_file_id_' + data.fileID,
				'type': 'hidden',
				'name': BX.message('MFUControlNameFull'),
				'value': data.fileID
			}
		});
		BX('mfu_file_container').appendChild(hidden);
		
		if (BX('newpost_photo_counter'))
		{
			if (BX('newpost_photo_counter').value == '')
				BX('newpost_photo_counter').value = 0;
			BX('newpost_photo_counter').value = parseInt(BX('newpost_photo_counter').value) + 1;
			
			if (BX('newpost_photo_counter_title') && BX('newpost_photo_counter_title').firstChild)
			{
				BX.adjust(BX('newpost_photo_counter_title').firstChild, {
					html : BX('newpost_photo_counter').value
				});
				BX('newpost_photo_counter_title').style.display = 'block';
			}
		}
	}
	
	if (data.fileID == 'undefined')
		__MFUProgressBarHide(loading_id);
}

__MFUProgressBarShow = function()
{
	if (BX('newpost_progressbar_cont'))
	{
		BX('newpost_progressbar_cont').style.display = 'block';

		var loading_id = Math.floor(Math.random() * 100000) + 1;
		LoadingFilesStack[LoadingFilesStack.length] = loading_id;
		__MFUProgressBarText();

		clearInterval(progressbar_id);

		progressbar_id = BitrixAnimation.animate({
			duration : LoadingFilesStack.length * 5000,
			start : { width : parseInt(progressbar_state / LoadingFilesStack.length) + 10 },
			finish : { width : 90 },
			transition : BitrixAnimation.makeEaseOut(BitrixAnimation.transitions.linear),
			step : function(state)
			{
				BX('newpost_progressbar_ind').style.width = state.width + '%';
				progressbar_state = state.width;
			},
			complete : function(){ progressbar_state = 0; }
		});

	}

	return loading_id;
}

__MFUProgressBarHide = function(loading_id)
{
	var newLoadingFilesStack = [];

	for (var i = 0; i < LoadingFilesStack.length; i++)
	{
		if (LoadingFilesStack[i] != loading_id)
			newLoadingFilesStack[newLoadingFilesStack.length] = LoadingFilesStack[i];
	}

	LoadingFilesStack = newLoadingFilesStack;

	if (LoadingFilesStack.length == 0)
	{
		clearInterval(progressbar_id);
		progressbar_state = 0;

		BX('newpost_progressbar_ind').style.width = '100%';
		setTimeout(function() { __MFUProgressBarText(); BX('newpost_progressbar_cont').style.display = 'none'; }, 2000);
	}
	else
		__MFUProgressBarText();
}

__MFUProgressBarText = function()
{
	if (BX('newpost_progressbar_label'))
		BX('newpost_progressbar_label').innerHTML = (LoadingFilesStack.length <= 0 
			? '' 
			: BX.message('MFULoadingTitle' + (LoadingFilesStack.length == 1 
				? '1' 
				: (LoadingFilesStack.length + ''))
			).replace("#COUNT#", LoadingFilesStack.length)
		);
}