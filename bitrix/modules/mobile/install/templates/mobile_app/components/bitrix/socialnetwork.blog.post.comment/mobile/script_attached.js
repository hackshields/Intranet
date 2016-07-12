function showMoreComments(id, source)
{
	var moreButton = BX('blog-comment-more');
	var lastComment = BX("comcntshow").value;
	var urlToMore = BX.message('SBPCurlToMore');
	var url = urlToMore.replace(/#comment_id#/, lastComment);
	url = url.replace(/#post_id#/, id);

	if (moreButton)
		BX.addClass(moreButton, 'post-comments-button-waiter');

	BMAjaxWrapper.Wrap({
		'type': 'html',
		'method': 'GET',
		'url': url,
		'data': '',
		'callback': function(data) {
			if (moreButton)
				BX.removeClass(moreButton, 'post-comments-button-waiter');
			var obNew = BX.processHTML(data, true);
			scripts = obNew.SCRIPT;
			BX.ajax.processScripts(scripts, true);

			BX('blog-comment-hidden').innerHTML = data + BX('blog-comment-hidden').innerHTML;
			BX('blog-comment-hidden').style.display = "block"; 
		},
		'callback_failure': function(data) {
			if (moreButton)
				BX.removeClass(moreButton, 'post-comments-button-waiter');
		}
	});
}

function showNewComment(response, bClearForm)
{
	bClearForm = !!bClearForm;
	BX('blog-comment-last-after').parentNode.insertBefore(BX.create('DIV', { html: response} ), BX('blog-comment-last-after'));
	if (bClearForm)
		BX('comment_send_form_comment').value = '';

	// increment comment counters both in post card and LiveFeed

	var log_id = BX.message('SBPClogID');

	if (
		BX('informer_comments_' + log_id)
		&& !BX('informer_comments_new_' + log_id)
	)
	{
		var old_value = (BX('informer_comments_' + log_id).innerHTML.length > 0 ? parseInt(BX('informer_comments_' + log_id).innerHTML) : 0);
		var val = old_value + 1;
		BX('informer_comments_' + log_id).innerHTML = val;
	}

	app.onCustomEvent('onLogEntryCommentAdd', { log_id: BX.message('SBPClogID') });	
}

function showNewPullComment(id, postId)
{
	if(!BX('blg-comment-' + id))
	{
		var url = BX.message('SBPCurlToNew').replace(/#comment_id#/, id);
		url = url.replace(/#post_id#/, postId);

		bCommentAjaxEnd = false;

		BMAjaxWrapper.Wrap({
			'type': 'html',
			'method': 'GET',
			'url': url,
			'data': '',
			'callback': function(data) {
				var obNew = BX.processHTML(data, true);
				scripts = obNew.SCRIPT;
				BX.ajax.processScripts(scripts, true);

				if (
					app.enableInVersion(4)
					|| window.platform == "android"
				)
					var postCard  = document.body;
				else
				var postCard  = BX('post-card-wrap', true);

				var maxScrollTop = postCard.scrollHeight - postCard.offsetHeight;

				showNewComment(data, false);

				setTimeout(function() {
 					if (
						postCard.scrollTop >= (maxScrollTop - 120) 
						&& postCard
					)
					{
						BitrixAnimation.animate({
							duration : 1000,
							start : { scroll : postCard.scrollTop },
							finish : { scroll : postCard.scrollTop + 140 },
							transition : BitrixAnimation.makeEaseOut(BitrixAnimation.transitions.quart),
							step : function(state)
							{
								postCard.scrollTop = state.scroll;
							},
							complete : function(){}
						});
					}
					BX.addClass(BX('blg-comment-' + id), "post-comment-new-transition"); 

				}, 0);

				bCommentAjaxEnd = true;
			},
			'callback_failure': function(data) { bCommentAjaxEnd = true; }
		});
	}
}