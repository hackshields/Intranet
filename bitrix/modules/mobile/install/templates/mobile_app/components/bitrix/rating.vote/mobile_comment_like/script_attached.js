if (!BXRL)
{
	var BXRL = {};
	var BXRLW = null;
}

RatingLikeComments = function(likeId, entityTypeId, entityId, available)
{	
	this.enabled = true;
	this.likeId = likeId;
	this.entityTypeId = entityTypeId;
	this.entityId = entityId;
	this.available = available == 'Y'? true: false;

	this.box = BX('bx-ilike-button-'+likeId);
	this.countText = BX('bx-ilike-count-'+likeId);

	if (this.box === null)
	{
		this.enabled = false;
		return false;
	}

	this.likeTimeout = false;	
	this.lastVote = BX.hasClass(this.box, 'post-comment-state-active') ? 'plus' : 'cancel';
}

RatingLikeComments.Set = function(likeId, entityTypeId, entityId, available)
{
	BXRL[likeId] = new RatingLikeComments(likeId, entityTypeId, entityId, available);
	if (BXRL[likeId].enabled)
		RatingLikeComments.Init(likeId);	
};

RatingLikeComments.Init = function(likeId)
{
	// like/unlike button
	if (BXRL[likeId].available)
	{
		BX.bind(BXRL[likeId].box, 'click', function(e) {
			clearTimeout(BXRL[likeId].likeTimeout);
			if (BX.hasClass(BXRL[likeId].box, 'post-comment-state-active'))
			{
				BXRL[likeId].countText.innerHTML = parseInt(BXRL[likeId].countText.innerHTML) - 1;
				if (parseInt(BXRL[likeId].countText.innerHTML) <= 0)
					BXRL[likeId].countText.style.display = "none";

				BX.removeClass(BXRL[likeId].box, 'post-comment-state-active');
				BX.addClass(BXRL[likeId].box, 'post-comment-state');

				BXRL[likeId].box.innerHTML = BX.message('RVCTextY');

				BXRL[likeId].likeTimeout = setTimeout(function(){
					if (BXRL[likeId].lastVote != 'cancel')
						RatingLikeComments.Vote(likeId, 'cancel');
				}, 1000);
			}
			else
			{
				BXRL[likeId].countText.innerHTML =  parseInt(BXRL[likeId].countText.innerHTML) + 1;
				BXRL[likeId].countText.style.display = "inline-block";

				BX.removeClass(BXRL[likeId].box, 'post-comment-state');
				BX.addClass(BXRL[likeId].box, 'post-comment-state-active');

				BXRL[likeId].box.innerHTML = BX.message('RVCTextN');

				BXRL[likeId].likeTimeout = setTimeout(function(){
					if (BXRL[likeId].lastVote != 'plus')
						RatingLikeComments.Vote(likeId, 'plus');
				}, 1000);
			}
			BX.PreventDefault(e);
		});
		
	}
}

RatingLikeComments.Vote = function(likeId, voteAction)
{
	BMAjaxWrapper.Wrap({
		'type': 'json',
		'method': 'POST',
		'url': '/bitrix/components/bitrix/rating.vote/vote.ajax.php',
		'data': {
			'RATING_VOTE': 'Y', 
			'RATING_VOTE_TYPE_ID': BXRL[likeId].entityTypeId, 
			'RATING_VOTE_ENTITY_ID': BXRL[likeId].entityId, 
			'RATING_VOTE_ACTION': voteAction,
			'sessid': BX.message('RVCSessID')
		},
		'callback': function(data) {
			BXRL[likeId].lastVote = data.action;
			BXRL[likeId].countText.innerHTML = data.items_all;
		},
		'callback_failure': function(data) { }
	});

	return false;
}

RatingLikeComments.List = function(likeId)
{
	if (app.enableInVersion(2))
	{
		app.openTable({
			callback: function() {},
			url: '/mobile/index.php?mobile_action=get_likes&RATING_VOTE_TYPE_ID=' + BXRL[likeId].entityTypeId + '&RATING_VOTE_ENTITY_ID=' + BXRL[likeId].entityId + '&URL=' + BX.message('RVCPathToUserProfile'),
			markmode: false,
			showtitle: false,
			modal: false,
			cache: false,
			outsection: false,
			cancelname: BX.message('RVCListBack')
		});
	}

	return false;
}