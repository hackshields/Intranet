<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/components/bitrix/rating.vote/mobile_like/script_attached.js").'"></script>', true);

?><script>
BX.message({
	RVSessID: '<?=CUtil::JSEscape(bitrix_sessid())?>',
	RVPathToUserProfile: '<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['PATH_TO_USER_PROFILE']))?>',
	RVListBack: '<?=CUtil::JSEscape(GetMessage("RV_T_LIST_BACK"))?>',
	RVRunEvent: '<?=(intval($arParams["VOTE_RAND"]) > 0 ? "Y" : "N")?>',
});
</script><?
?><div class="post-item-informers post-item-inform-likes<?=($arResult['USER_HAS_VOTED'] == "N" ? "": "-active")?>" id="bx-ilike-button-<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>"><?
	?><div class="post-item-inform-left"></div><?
	?><div class="post-item-inform-right"><span class="post-item-inform-right-text"><?=htmlspecialcharsEx($arResult["TOTAL_VOTES"])?></span></div><?
?></div><?
if ($arParams["EXTENDED"] == "Y")
{
	$me = '<span class="post-strong">'.GetMessage("RV_T_LIKE_ME").'</span>';

	?><script>
	BX.ready(function() { setTimeout(function() {
		if (
			app.enableInVersion(2)
			&& BX('rating-footer')
		)
		{
			<?
			if (intval($arResult["TOTAL_VOTES"]) > 0)
			{
				if (
					intval($arResult["TOTAL_VOTES"]) == 1
					&& $arResult["USER_HAS_VOTED"] == "Y"
				)
				{
					?>
					BX('rating-footer').appendChild(BX.create('DIV', 
					{
						'html': '<?=str_replace("#YOU#", $me, GetMessage("RV_T_LIKE_YOU"))?>'
					})); // only you
					<?
				}
				else
				{
					$count = ($arResult["USER_HAS_VOTED"] == "Y" ? intval($arResult["TOTAL_VOTES"]) - 1 : intval($arResult["TOTAL_VOTES"]));
					$reminder = $count % 10;
					$users_title = GetMessage("RV_T_LIKE_USERS_".($reminder == 1 ? "1" : "2"));

					$count_users = '<span class="post-strong">'.str_replace(array("#COUNT#", "#USERS#"), array($count, $users_title), GetMessage("RV_T_LIKE_COUNT_USERS")).'</span>';
					?>
					BX('rating-footer').appendChild(BX.create('DIV', 
					{
						'attrs': {
							'id': 'bx-ilike-list-youothers'
						},
						'style': {
							'display': '<?=($arResult["USER_HAS_VOTED"] == "Y" ? "block" : "none")?>'
						},
						'html': '<?=str_replace(array("#YOU#", "#COUNT_USERS#"), array($me, $count_users), GetMessage("RV_T_LIKE_YOU_OTHERS"))?>',
						'events': {
							'click': function() { RatingLike.List('<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>'); }
						}
					})); // // you and others

					BX('rating-footer').appendChild(BX.create('DIV', 
					{
						'attrs': {
							'id': 'bx-ilike-list-others'
						},
						'style': {
							'display': '<?=($arResult["USER_HAS_VOTED"] == "Y" ? "none" : "block")?>'
						},
						'html': '<?=str_replace("#COUNT_USERS#", $count_users, GetMessage("RV_T_LIKE_OTHERS"))?>',
						'events': {
							'click': function() { RatingLike.List('<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>'); }
						}
					})); // // others
					<?
				}
			}
			else
			{
				?>
				BX('rating-footer').appendChild(BX.create('DIV', 
				{
					'html': '<?=str_replace("#YOU#", $me, GetMessage("RV_T_LIKE_YOU"))?>'
				})); // only you
				<?
			}
			?>
			
		}
	}, 100); });
	</script><?

}
?><script type="text/javascript">
BX.ready(function() {
	if (!window.RatingLike && top.RatingLike)
		RatingLike = top.RatingLike;
	RatingLike.Set(
		'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_ID']))?>', 
		'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['ENTITY_TYPE_ID']))?>', 
		'<?=IntVal($arResult['ENTITY_ID'])?>', 
		'<?=CUtil::JSEscape(htmlspecialcharsbx($arResult['VOTE_AVAILABLE']))?>'
	);
});
</script>