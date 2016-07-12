function __MB_TASKS_TASK_TOPIC_REVIEWS_RenderComment(arComment, callbackInserter)
{
	var commentNode = null;
	var ratingNode = null;
	var anchor_id = null;	
	var you_like_class = null;
	var you_like_text = null;
	var nodeStyle = null;

	if ( ! arComment['META:FORMATTED_DATA'] )
		return null;

	anchor_id = Math.floor(Math.random()*100000) + 1;

	if (
		! (
			arComment['POST_MESSAGE_TEXT']
			&& (arComment['POST_MESSAGE_TEXT'].length > 0)
			&& (arComment['ID'] > 0)
		)
	)
	{
		return null;
	}

	var ratingTypeId = 'FORUM_POST';
	var entityTypeId = ratingTypeId;
	var eventEntityId = arComment['ID'];
	var ownerId = arComment['AUTHOR_ID'];

	if (arComment['META:ALLOW_VOTE_RATING']['RESULT'])
		var allowRatingVote = 'Y';
	else
		var allowRatingVote = 'N';

	if (typeof(arComment['RATING']['TOTAL_POSITIVE_VOTES']) === 'undefined')
		arComment['RATING']['TOTAL_POSITIVE_VOTES'] = 0;

	if (parseInt(arComment['RATING']['TOTAL_POSITIVE_VOTES']) <= 0)
		nodeStyle = { 'display': 'none' };
	else
		nodeStyle = {};

	if (arComment['RATING']['USER_HAS_VOTED'] === 'Y')
	{
		you_like_class =  'post-comment-state-active';
		you_like_text = BX.message('RVCTextN');
	}
	else
	{
		you_like_class = 'post-comment-state';
		you_like_text = BX.message('RVCTextY');
	}

	var vote_id = ratingTypeId
		+ '-' + eventEntityId
		+ '-' + anchor_id;

	ratingNode = BX.create('div', {
		props: {
			'className': 'post-comment-likes'
		},
		children: [
			BX.create('div', {
				props: {
					'id': 'bx-ilike-button-' + vote_id,
					'className': 'post-comment-likes-text ' + you_like_class
				},
				html: you_like_text
			}),
			BX.create('div', {
				props: {
					'id': 'bx-ilike-count-' + vote_id,
					'className': 'post-comment-likes-counter'
				},
				style: nodeStyle,
				html: arComment['RATING']['TOTAL_POSITIVE_VOTES']
			})
		]
	});

	if (arComment['AUTHOR_PHOTO'] 
		&& (arComment['AUTHOR_PHOTO'] != 'undefined')
	)
	{
		var avatar = BX.create(
			'div', 
			{
				props: { 'className': 'avatar' }, 
				style: { 
					backgroundImage: "url('" + arComment['AUTHOR_PHOTO'] + "')",
					backgroundRepeat: "no-repeat",
					backgroundSize: "29px 29px"
				}
			}
		);
	}
	else
	{
		var avatar = BX.create(
			'div', {
				props: { 'className': 'avatar' } 
			}
		);
	}

	if (arComment['META:FORMATTED_DATA']['DATETIME_SEXY'] != 'undefined')
		comment_datetime = arComment['META:FORMATTED_DATA']['DATETIME_SEXY'];
	else
		comment_datetime = '';

	class_name_unread = '';

	commentNode = BX.create('div', {
		props: { 'className': 'post-comment-block' },
		children: [
			avatar,
			BX.create('div', {
				props: { 'className': 'post-comment-cont' },
				children: [
					BX.create('a', {
						props: { 'className': 'post-comment-author' },
						attrs: { 'href': arComment['META:FORMATTED_DATA']['AUTHOR_URL'] },
						html: arComment['META:FORMATTED_DATA']['AUTHOR_NAME']
					}),
					BX.create('div', {
						props: { 'className': 'post-comment-text' },
						html: arComment['POST_MESSAGE_TEXT']
					}),
					BX.create('div', {
						props: { 'className': 'post-comment-time' },
						html: comment_datetime
					}),
					ratingNode
				]
			})
		]
	});

	callbackInserter(commentNode, ratingNode, vote_id, ratingTypeId, eventEntityId, allowRatingVote, arComment['ID']);

	return (commentNode);
}


function __MB_TASKS_TASK_TOPIC_REVIEWS_ShowComments(arComments)
{
	var commentNode = null;
	var arComment = null;

	for (var indx in arComments)
	{
		if ( ! arComments.hasOwnProperty(indx) )
			continue;

		arComment = arComments[indx];

		commentNode = __MB_TASKS_TASK_TOPIC_REVIEWS_RenderComment(
			arComment,
			function(comNode, ratingNode, vote_id, ratingTypeId, eventEntityId, allowRatingVote, commentId)
			{
				if (comNode)
					BX('post-comment-hidden').appendChild(comNode);

				if (ratingNode)
				{
					if (!window.RatingLikeComments && top.RatingLikeComments)
						RatingLikeComments = top.RatingLikeComments;

					RatingLikeComments.Set(
						vote_id,
						ratingTypeId,
						eventEntityId,
						allowRatingVote
					);
				}
			}
		);
	}

	BX('post-comment-hidden').style.display = "block";
	BX('post-comment-more').style.display = "none";

	if (__MB_TASKS_TASK_DETAIL_scrollPageBottom)
		__MB_TASKS_TASK_DETAIL_scrollPageBottom();
}
