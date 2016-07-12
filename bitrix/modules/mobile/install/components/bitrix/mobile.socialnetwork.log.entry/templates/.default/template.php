<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if (
		$arResult["Event"] 
		&& is_array($arResult["Event"])
		&& !empty($arResult["Event"])
	)
	{
		$arEvent = $arResult["Event"];
		if ($arParams["IS_LIST"] != "Y" && CMobile::getApiVersion() < 4 && CMobile::getPlatform() != "android")
		{
			?><div class="post-card-wrap" id="post-card-wrap" onclick=""><?
		}

		$bUnread = $arParams["EVENT"]["IS_UNREAD"];

		$strTopic = "";
		if (
			isset($arEvent["EVENT_FORMATTED"]["DESTINATION"])
			&& is_array($arEvent["EVENT_FORMATTED"]["DESTINATION"])
			&& count($arEvent["EVENT_FORMATTED"]["DESTINATION"]) > 0
		)
		{
			if (
				array_key_exists("TITLE_24", $arEvent["EVENT_FORMATTED"])
				&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24"]) > 0
			)
				$strTopic .= '<div class="post-item-top-text post-item-top-arrow'.(strlen($arEvent["EVENT_FORMATTED"]["STYLE"]) > 0 ? ' post-item-'.$arEvent["EVENT_FORMATTED"]["STYLE"] : '').'">'.$arEvent["EVENT_FORMATTED"]["TITLE_24"].'</div>';

			if (in_array($arEvent["EVENT"]["EVENT_ID"], array("system", "system_groups", "system_friends")))
			{
				foreach($arEvent["EVENT_FORMATTED"]["DESTINATION"] as $arDestination)
				{
					if (strlen($arDestination["URL"]) > 0)
						$strTopic .= '<a href="'.$arDestination["URL"].'" class="post-item-topic-description"><span'.(strlen($arDestination["STYLE"]) > 0 ? ' class="post-item-top-text-'.$arDestination["STYLE"].'"' : '').'>'.$arDestination["TITLE"].'</span></a>';
					else
						$strTopic .= '<span class="post-item-topic-description"><span'.(strlen($arDestination["STYLE"]) > 0 ? ' class="post-item-top-text-'.$arDestination["STYLE"].'"' : '').'>'.$arDestination["TITLE"].'</span></span>';
				}
			}
			else
			{
				$i = 0;
				foreach($arEvent["EVENT_FORMATTED"]["DESTINATION"] as $arDestination)
				{
					if ($i > 0)
						$strTopic .= ', ';

					if (strlen($arDestination["URL"]) > 0)
						$strTopic .= '<a href="'.$arDestination["URL"].'" class="post-item-destination'.(strlen($arDestination["STYLE"]) > 0 ? ' post-item-dest-'.$arDestination["STYLE"] : '').'">'.$arDestination["TITLE"].'</a>';
					else
						$strTopic .= '<span class="post-item-destination'.(strlen($arDestination["STYLE"]) > 0 ? ' post-item-dest-'.$arDestination["STYLE"] : '').'">'.$arDestination["TITLE"].'</span>';

					$i++;
				}
				if (intval($arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"]) > 0)
				{
					$moreClick = ($arParams["IS_LIST"] != "Y" ? " onclick=\"__MSLGetHiddenDestinations(".$arEvent["EVENT"]["ID"].", ".$arEvent["EVENT"]["USER_ID"].", this);\"" : "");
					$strTopic .= "<span class=\"post-destination-more\"".$moreClick." ontouchstart=\"BX.toggleClass(this, 'post-destination-more-touch');\" ontouchend=\"BX.toggleClass(this, 'post-destination-more-touch');\">".str_replace("#COUNT#", $arEvent["EVENT_FORMATTED"]["DESTINATION_MORE"], GetMessage("MOBILE_LOG_DESTINATION_MORE"))."</span>";
				}
			}
		}
		elseif (
			array_key_exists("TITLE_24", $arEvent["EVENT_FORMATTED"])
			&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24"]) > 0
		)
			$strTopic .= '<div class="post-item-top-text'.(strlen($arEvent["EVENT_FORMATTED"]["STYLE"]) > 0 ? ' post-item-'.$arEvent["EVENT_FORMATTED"]["STYLE"] : '').'">'.$arEvent["EVENT_FORMATTED"]["TITLE_24"].'</div>';
		else
			$strTopic .= '<div class="post-item-top-text'.(strlen($arEvent["EVENT_FORMATTED"]["STYLE"]) > 0 ? ' post-item-'.$arEvent["EVENT_FORMATTED"]["STYLE"] : '').'">'.$arEvent["EVENT_FORMATTED"]["TITLE"].'</div>';

		$strCreatedBy = "";
		if (
			array_key_exists("CREATED_BY", $arEvent)
			&& is_array($arEvent["CREATED_BY"])
		)
		{
			if (
				array_key_exists("TOOLTIP_FIELDS", $arEvent["CREATED_BY"])
				&& is_array($arEvent["CREATED_BY"]["TOOLTIP_FIELDS"])
			)
				$strCreatedBy .= '<a class="post-item-top-title" href="'.str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]).'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</a>';
			elseif (
				array_key_exists("FORMATTED", $arEvent["CREATED_BY"])
				&& strlen($arEvent["CREATED_BY"]["FORMATTED"]) > 0
			)
				$strCreatedBy .= '<div class="post-item-top-title">'.$arEvent["CREATED_BY"]["FORMATTED"].'</div>';
		}
		elseif (
			in_array($arEvent["EVENT"]["EVENT_ID"], array("data", "news", "system"))
			&& array_key_exists("ENTITY", $arEvent)
		)
		{
			if (
				array_key_exists("TOOLTIP_FIELDS", $arEvent["ENTITY"])
				&& is_array($arEvent["ENTITY"]["TOOLTIP_FIELDS"])
			)
				$strCreatedBy .= '<a class="post-item-top-title" href="'.str_replace(array("#user_id#", "#USER_ID#", "#id#", "#ID#"), $arEvent["ENTITY"]["TOOLTIP_FIELDS"]["ID"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]).'">'.CUser::FormatName($arParams["NAME_TEMPLATE"], $arEvent["ENTITY"]["TOOLTIP_FIELDS"], ($arParams["SHOW_LOGIN"] != "N" ? true : false)).'</a>';
			elseif (
				array_key_exists("FORMATTED", $arEvent["ENTITY"])
				&& array_key_exists("NAME", $arEvent["ENTITY"]["FORMATTED"])
			)
				$strCreatedBy .= '<div class="post-item-top-title">'.$arEvent["ENTITY"]["FORMATTED"]["NAME"].'</div>';
		}

		$strDescription = "";
		if (
			array_key_exists("DESCRIPTION", $arEvent["EVENT_FORMATTED"])
			&& (
				(!is_array($arEvent["EVENT_FORMATTED"]["DESCRIPTION"]) && strlen($arEvent["EVENT_FORMATTED"]["DESCRIPTION"]) > 0)
				|| (is_array($arEvent["EVENT_FORMATTED"]["DESCRIPTION"]) && count($arEvent["EVENT_FORMATTED"]["DESCRIPTION"]) > 0)
			)
		)
			$strDescription = '<div class="post-item-description'.(strlen($arEvent["EVENT_FORMATTED"]["DESCRIPTION_STYLE"]) > 0 ? ' post-item-description-'.$arEvent["EVENT_FORMATTED"]["DESCRIPTION_STYLE"].'"' : '').'">'.(is_array($arEvent["EVENT_FORMATTED"]["DESCRIPTION"]) ? '<span>'.implode('</span> <span>', $arEvent["EVENT_FORMATTED"]["DESCRIPTION"]).'</span>' : $arEvent["EVENT_FORMATTED"]["DESCRIPTION"]).'</div>';

		if ($arParams["IS_LIST"] == "Y")
		{
			?><script type="text/javascript">
				arLogTs.entry_<?=intval($arEvent["EVENT"]["ID"])?> = <?=intval($arResult["LAST_LOG_TS"])?>;
			</script><?
		}

		if ($arParams["IS_LIST"] == "Y")
		{
			if (
				isset($arEvent["EVENT"])
				&& isset($arEvent["EVENT"]["MODULE_ID"])
				&& ($arEvent["EVENT"]["MODULE_ID"] === "tasks")
				&& isset($arEvent["EVENT"]["EVENT_ID"])
				&& ($arEvent["EVENT"]["EVENT_ID"] === "tasks")
				&& isset($arEvent["EVENT"]["SOURCE_ID"])
				&& ($arEvent["EVENT"]["SOURCE_ID"] > 0)
				&& (intval($GLOBALS["APPLICATION"]->GetPageProperty("api_version")) >= 2)
			)
			{
				$strPath = str_replace(
					array("#ROUTE_PAGE#", "#USER_ID#"),
					array("view", (int) $GLOBALS["USER"]->GetID()),
					$arParams["PATH_TO_TASKS_SNM_ROUTER"] 
					."&TASK_ID=".(int)$arEvent["EVENT"]["SOURCE_ID"]
				);
			}
			else
				$strPath = str_replace("#log_id#", $arEvent["EVENT"]["ID"], $arParams["PATH_TO_LOG_ENTRY"]);
		}

		$strOnClick = ($arParams["IS_LIST"] == "Y" ? " onclick=\"__MSLOpenLogEntry(".intval($arEvent["EVENT"]["ID"]).", '".$strPath."', false, event);\"" : "");

		if (
			array_key_exists("EVENT_FORMATTED", $arEvent)
			&& array_key_exists("DATETIME_FORMATTED", $arEvent["EVENT_FORMATTED"])
			&& strlen($arEvent["EVENT_FORMATTED"]["DATETIME_FORMATTED"]) > 0
		)
			$datetime = $arEvent["EVENT_FORMATTED"]["DATETIME_FORMATTED"];
		elseif (
			array_key_exists("DATETIME_FORMATTED", $arEvent)
			&& strlen($arEvent["DATETIME_FORMATTED"]) > 0
		)
			$datetime = $arEvent["DATETIME_FORMATTED"];
		elseif ($arEvent["LOG_DATE_DAY"] == ConvertTimeStamp())
			$datetime = $arEvent["LOG_TIME_FORMAT"];
		else
			$datetime = $arEvent["LOG_DATE_DAY"]." ".$arEvent["LOG_TIME_FORMAT"];

		$bHasNoCommentsOrLikes = (
			(
				!array_key_exists("HAS_COMMENTS", $arEvent) 
				|| $arEvent["HAS_COMMENTS"] != "Y"
			)
			&& (
				$arParams["SHOW_RATING"] != "Y" 
				|| strlen($arEvent["RATING_TYPE_ID"]) <= 0 
				|| intval($arEvent["RATING_ENTITY_ID"]) <= 0
			)
		);

		$item_class = ($arParams["IS_LIST"] != "Y" ? "post-wrap" : "lenta-item".($bUnread ? " lenta-item-new" : "")).($bHasNoCommentsOrLikes ? " post-without-informers" : "");

		?><div class="<?=($item_class)?>" id="lenta_item_<?=$arEvent["EVENT"]["ID"]?>">
			<div class="post-item-top-wrap">
				<div class="post-item-top">
					<div class="avatar<?=(strlen($arEvent["EVENT_FORMATTED"]["AVATAR_STYLE"]) > 0 ? " ".$arEvent["EVENT_FORMATTED"]["AVATAR_STYLE"] : "")?>"<?=(strlen($arEvent["AVATAR_SRC"]) > 0 ? " style=\"background:url('".$arEvent["AVATAR_SRC"]."') 0 0 no-repeat; background-size: 29px 29px;\"" : "")?>></div>
					<div class="post-item-top-cont">
						<?=$strCreatedBy?><?
						if ($arParams["IS_LIST"] != "Y")
						{
							?><div class="post-date"><?=$datetime?></div><?
						}
						?><div class="post-item-top-topic"><?=$strTopic ?></div><?
						if (strlen($strDescription) > 0)
							echo $strDescription;
					?></div><?
					if ($arParams["IS_LIST"] == "Y")
					{
						?><div class="lenta-item-time"><?=$datetime?></div><?
					}
				?></div><?

				ob_start();

				if (
					array_key_exists("HAS_COMMENTS", $arEvent)
					&& $arEvent["HAS_COMMENTS"] == "Y"
				)
				{
					$bHasComments = true;
					$strOnClickComments = ($arParams["IS_LIST"] == "Y" ? " onclick=\"__MSLOpenLogEntry(".intval($arEvent["EVENT"]["ID"]).", '".$strPath."', true);\"" : " onclick=\"__MSLDetailMoveBottom();\"");
					?><div class="post-item-informers post-item-inform-comments"<?=$strOnClickComments?>><div class="post-item-inform-left"></div><div class="post-item-inform-right" id="informer_comments_<?=$arEvent["EVENT"]["ID"]?>"><?
					if (
						($arParams["USE_FOLLOW"] != "Y" || $arEvent["EVENT"]["FOLLOW"] == "Y")
						&& intval($arResult["NEW_COMMENTS"]) > 0
					)
					{
						?><span id="informer_comments_all_<?=$arEvent["EVENT"]["ID"]?>"><?
							$old_comments = intval(abs(intval($arParams["EVENT"]["COMMENTS_COUNT"]) - intval($arResult["NEW_COMMENTS"])));
							echo ($old_comments > 0 ? $old_comments : '');
						?></span><?
						?><span id="informer_comments_new_<?=$arEvent["EVENT"]["ID"]?>">+<?=intval($arResult["NEW_COMMENTS"])?></span><?
					}
					else
					{
						?><?=intval($arParams["EVENT"]["COMMENTS_COUNT"])?><?
					}
					?></div></div><?
				}
				else
					$bHasComments = false;

				if (
					strlen($arEvent["EVENT"]["RATING_TYPE_ID"]) > 0
					&& $arEvent["EVENT"]["RATING_ENTITY_ID"] > 0
					&& $arParams["SHOW_RATING"] == "Y"
				)
				{
					$arResultVote = $APPLICATION->IncludeComponent(
						"bitrix:rating.vote", "mobile_like",
						Array(
							"ENTITY_TYPE_ID" => $arEvent["EVENT"]["RATING_TYPE_ID"],
							"ENTITY_ID" => $arEvent["EVENT"]["RATING_ENTITY_ID"],
							"OWNER_ID" => $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
							"USER_VOTE" => $arEvent["RATING"]["USER_VOTE"],
							"USER_HAS_VOTED" => $arEvent["RATING"]["USER_HAS_VOTED"],
							"TOTAL_VOTES" => $arEvent["RATING"]["TOTAL_VOTES"],
							"TOTAL_POSITIVE_VOTES" => $arEvent["RATING"]["TOTAL_POSITIVE_VOTES"],
							"TOTAL_NEGATIVE_VOTES" => $arEvent["RATING"]["TOTAL_NEGATIVE_VOTES"],
							"TOTAL_VALUE" => $arEvent["RATING"]["TOTAL_VALUE"],
							"PATH_TO_USER_PROFILE" => $arEvent["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"],
							"EXTENDED" => ($arParams["IS_LIST"] != "Y" ? "Y" : "N"),
							"VOTE_RAND" => ($arParams["IS_LIST"] != "Y" && intval($_REQUEST["LIKE_RANDOM_ID"]) > 0 ? intval($_REQUEST["LIKE_RANDOM_ID"]) : false)
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);

					$bRatingExtended = (
						$arParams["IS_LIST"] != "Y"
						&& intval($GLOBALS["APPLICATION"]->GetPageProperty("api_version")) >= 2
					);

					$bRatingExtendedOpen = (
						$bRatingExtended
						&& intval($arResultVote["TOTAL_VOTES"]) > 0
					);

					if (
						$arParams["IS_LIST"] == "Y"
						&& intval($arResultVote["VOTE_RAND"]) > 0
					)
					{
						?><script type="text/javascript">
							arLikeRandomID.entry_<?=intval($arEvent["EVENT"]["ID"])?> = <?=intval($arResultVote["VOTE_RAND"])?>;
						</script><?
					}
				}

				if (
					$bHasComments
					&& array_key_exists("FOLLOW", $arEvent["EVENT"])
				)
				{
					$follow_type_default = " post-item-follow-default".($arResult["FOLLOW_DEFAULT"] == "Y" ? "-active" : "");
					$follow_type = " post-item-follow".($arEvent["EVENT"]["FOLLOW"] == "Y" ? "-active" : "");
					?><div id="log_entry_follow_<?=intval($arEvent["EVENT"]["ID"])?>" data-follow="<?=($arEvent["EVENT"]["FOLLOW"] == "Y" ? "Y" : "N")?>" class="post-item-informers<?=$follow_type_default?><?=$follow_type?>" onclick="__MSLSetFollow(<?=$arEvent["EVENT"]["ID"]?>)">
						<div class="post-item-inform-left"></div>
					</div><?
				}

				if ($_REQUEST["show_full"] != "Y")
				{
					if ($arParams["IS_LIST"] == "Y")
						$strOnClickMore = "onclick=\"__MSLOpenLogEntry(".intval($arEvent["EVENT"]["ID"]).", '".str_replace("#log_id#", $arEvent["EVENT"]["ID"], $arParams["PATH_TO_LOG_ENTRY"])."&show_full=Y');\"";
					else
						$strOnClickMore = "onclick=\"__MSLExpandText(".intval($arEvent["EVENT"]["ID"]).");\"";

					?><div <?=$strOnClickMore?> class="post-item-more" ontouchstart="BX.toggleClass(this, 'post-item-more-pressed');" ontouchend="BX.toggleClass(this, 'post-item-more-pressed');" style="display: none;" id="post_block_check_more_<?=$arEvent["EVENT"]["ID"]?>"><?=GetMessage("MOBILE_LOG_MORE")?></div><?
				}

				if ($bRatingExtended)
				{
					?><div class="post-item-inform-footer" id="rating-footer"></div><?
				}

				$strBottomBlock = ob_get_contents();
				ob_end_clean();

				$post_more_block = ($_REQUEST["show_full"] != "Y" ? '<div class="post-more-block" id="post_more_block_'.$arEvent["EVENT"]["ID"].'"></div>' : '');
				$post_more_corner = ($_REQUEST["show_full"] != "Y" ? '<div class="post-item-corner" id="post_more_corner_'.$arEvent["EVENT"]["ID"].'"></div>' : '');

				$post_item_style = ($arParams["IS_LIST"] != "Y" && $_REQUEST["show_full"] == "Y" ? "post-item-post-block-full" : "post-item-post-block");

				if (in_array($arEvent["EVENT"]["EVENT_ID"], array("photo", "photo_photo")))
					include($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/mobile.socialnetwork.log.entry/templates/.default/photo.php");
				elseif (strlen($arEvent["EVENT_FORMATTED"]["MESSAGE"]) > 0)
				{
					// body

					if (
						array_key_exists("EVENT_FORMATTED", $arEvent)
						&& array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
						&& $arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
					)
					{
						$news_item_style = (
							$arParams["IS_LIST"] != "Y" 
							&& $_REQUEST["show_full"] == "Y" 
								? "lenta-info-block-wrapp-full" 
								: "lenta-info-block-wrapp"
						);

						?><div class="<?=$news_item_style?>"<?=$strOnClick?> id="post_block_check_cont_<?=$arEvent["EVENT"]["ID"]?>"><?
							?><div class="lenta-info-block <?=(in_array($arEvent["EVENT"]["EVENT_ID"], array("intranet_new_user", "bitrix24_new_user")) ? "lenta-block-new-employee" : "info-block-important")?>"><?
								if (in_array($arEvent["EVENT"]["EVENT_ID"], array("intranet_new_user", "bitrix24_new_user")))
								{
									echo CSocNetTextParser::closetags($arEvent["EVENT_FORMATTED"]["MESSAGE"]);
								}
								else
								{
									if (
										array_key_exists("IS_IMPORTANT", $arEvent["EVENT_FORMATTED"])
										&& $arEvent["EVENT_FORMATTED"]["IS_IMPORTANT"]
										&& array_key_exists("TITLE_24_2", $arEvent["EVENT_FORMATTED"])
										&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24_2"]) > 0
									)
									{
											?><div class="lenta-important-block-title"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?
									}

									?><div class="lenta-important-block-text"><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?><i></i></div><?
								}
							?></div><?

							echo $post_more_block;

						?></div><?
					}
					elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("files", "commondocs")))
					{
						?><div class="post-item-post-block-full"<?=$strOnClick?>>
							<div class="post-item-attached-file-wrap">
								<div class="post-item-attached-file"><span><?=$arEvent["EVENT"]["TITLE"]?></span></div>
							</div><?
						?></div><?
					}
					elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("tasks")))
					{
						?><div class="lenta-info-block-wrapp"<?=$strOnClick?>><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?></div><?
					}
					elseif (in_array($arEvent["EVENT"]["EVENT_ID"], array("timeman_entry", "report")))
					{
						?><div class="lenta-info-block-wrapp"<?=$strOnClick?>><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?></div><?
					}
					elseif (!in_array($arEvent["EVENT"]["EVENT_ID"], array("system", "system_groups", "system_friends")) && strlen($arEvent["EVENT_FORMATTED"]["MESSAGE"]) > 0) // all other events
					{
						?><div class="<?=$post_item_style?>"<?=$strOnClick?> id="post_block_check_cont_<?=$arEvent["EVENT"]["ID"]?>"><?
							if (
								array_key_exists("TITLE_24_2", $arEvent["EVENT_FORMATTED"])
								&& strlen($arEvent["EVENT_FORMATTED"]["TITLE_24_2"]) > 0
							)
							{
								?><div class="post-text-title" id="post_text_title_<?=$arEvent["EVENT"]["ID"]?>"><?=$arEvent["EVENT_FORMATTED"]["TITLE_24_2"]?></div><?
							}
							?><div class="post-item-text" id="post_block_check_<?=$arEvent["EVENT"]["ID"]?>"><?=CSocNetTextParser::closetags(htmlspecialcharsback($arEvent["EVENT_FORMATTED"]["MESSAGE"]))?></div><?

							echo $post_more_block;

						?></div><?
					}
				}

				echo $post_more_corner;

			?></div><? // post-item-top-wrap

			if (
				strlen($strBottomBlock) > 0
				&& !in_array($arEvent["EVENT"]["EVENT_ID"], array("system", "system_group", "system_friends", "photo"))
			)
			{
				?><div <?=($bRatingExtended ? 'id="post_item_inform_wrap"' : '')?> id="post_inform_wrap_<?=$arEvent["EVENT"]["ID"]?>" class="post-item-inform-wrap<?=($bRatingExtendedOpen ? " post-item-inform-action" : "")?>"><?=$strBottomBlock;?></div><?
			}

		?></div><? // post-wrap / lenta-item

		?><script type="text/javascript">
		arBlockToCheck[arBlockToCheck.length] = {
			lenta_item_id: 'lenta_item_<?=$arEvent["EVENT"]["ID"]?>',
			text_block_id: 'post_block_check_cont_<?=$arEvent["EVENT"]["ID"]?>',
			title_block_id: 'post_block_check_title_<?=$arEvent["EVENT"]["ID"]?>',
			more_block_id: 'post_block_check_more_<?=$arEvent["EVENT"]["ID"]?>',
			more_overlay_id: 'post_more_block_<?=$arEvent["EVENT"]["ID"]?>',
			more_corner_id: 'post_more_corner_<?=$arEvent["EVENT"]["ID"]?>',
			post_inform_wrap_id: 'post_inform_wrap_<?=$arEvent["EVENT"]["ID"]?>'
		};
		</script><?

		if (
			$arParams["IS_LIST"] != "Y"
			&& array_key_exists("HAS_COMMENTS", $arEvent)
			&& $arEvent["HAS_COMMENTS"] == "Y"
		)
		{
			?><div class="post-comments-wrap" id="post-comments-wrap"><?
				if (is_array($arEvent["COMMENTS"]))
				{
					foreach($arEvent["COMMENTS"] as $arComment)
					{
						if (
							!$bMoreShown
							&& count($arEvent["COMMENTS"]) > 0
							&& intval($arParams["EVENT"]["COMMENTS_COUNT"]) > count($arEvent["COMMENTS"])
						)
						{
							$bMoreShown = true;
							?><div id="post-comment-more" class="post-comments-button" ontouchstart="BX.toggleClass(this, 'post-comments-button-press');" ontouchend="BX.toggleClass(this, 'post-comments-button-press');"><?=str_replace("#COMMENTS#", $arParams["EVENT"]["COMMENTS_COUNT"], GetMessage("MOBILE_LOG_COMMENT_BUTTON_MORE"))?></div>
							<script>
							BX.bind(BX('post-comment-more'), 'click', function(e)
							{
								var moreButton = BX('post-comment-more');
								if (moreButton)
									BX.addClass(moreButton, 'post-comments-button-waiter');

								var get_data = {
									'sessid': '<?=bitrix_sessid()?>',
									'site': '<?=CUtil::JSEscape(SITE_ID)?>',
									'lang': '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
									'logid': <?=$arParams["LOG_ID"]?>,
									'last_comment_id': <?=intval($arComment["EVENT"]["ID"])?>,
									'as': <?=intval($arParams["AVATAR_SIZE_COMMENT"])?>,
									'nt': '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
									'sl': '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',
									'dtf': '<?=CUtil::JSEscape($arParams["DATE_TIME_FORMAT"])?>',
									'p_user': '<?=CUtil::JSEscape($arParams["PATH_TO_USER"])?>',
									'action': 'get_comments'
								};

								BMAjaxWrapper.Wrap({
									'type': 'json',
									'method': 'POST',
									'url': '/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php',
									'data': get_data,
									'callback': function(get_response_data)
									{
										if (moreButton)
											BX.removeClass(moreButton, 'post-comments-button-waiter');
										if (get_response_data["arComments"] != 'undefined')
										{
											__MSLShowComments(get_response_data["arComments"]);
										}
									},
									'callback_failure': function() {
										if (moreButton)
											BX.removeClass(moreButton, 'post-comments-button-waiter');
									}
								});
							});
							</script>
							<div id="post-comment-hidden" style="display:none; overflow:hidden;"></div><?
						}

						$strCreatedBy = "";
						if (
							array_key_exists("CREATED_BY", $arComment)
							&& is_array($arComment["CREATED_BY"])
							&& array_key_exists("FORMATTED", $arComment["CREATED_BY"])
							&& strlen($arComment["CREATED_BY"]["FORMATTED"]) > 0
						)
							$strCreatedBy = $arComment["CREATED_BY"]["FORMATTED"];

						$bUnread = (
							($arResult["COUNTER_TYPE"] == "**")
							&& $arComment["EVENT"]["USER_ID"] != $GLOBALS["USER"]->GetID()
							&& intval($arResult["LAST_LOG_TS"]) > 0
							&& (MakeTimeStamp($arComment["EVENT"]["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])) > $arResult["LAST_LOG_TS"]
						);
						?><div class="post-comment-block<?=($bUnread ? " post-comment-new" : "")?>">
							<div class="avatar"<?=(strlen($arComment["AVATAR_SRC"]) > 0 ? " style=\"background:url('".$arComment["AVATAR_SRC"]."') no-repeat; background-size: 29px 29px;\"" : "")?>></div>
							<div class="post-comment-cont"><?
								if (strlen($arComment["CREATED_BY"]["URL"]) > 0)
								{
									?><a href="<?=$arComment["CREATED_BY"]["URL"]?>" class="post-comment-author"><?=$strCreatedBy?></a><?
								}
								else
								{
									?><div class="post-comment-author"><?=$strCreatedBy?></div><?
								}
								?><div class="post-comment-text"><?
									$message = (array_key_exists("EVENT_FORMATTED", $arComment) && array_key_exists("MESSAGE", $arComment["EVENT_FORMATTED"]) ? $arComment["EVENT_FORMATTED"]["MESSAGE"] : $arComment["EVENT"]["MESSAGE"]);
									if (strlen($message) > 0)
										echo CSocNetTextParser::closetags(htmlspecialcharsback($message));
								?></div>
								<div class="post-comment-time"><?
									echo (
										array_key_exists("EVENT_FORMATTED", $arComment)
										&& array_key_exists("DATETIME", $arComment["EVENT_FORMATTED"])
										&& strlen($arComment["EVENT_FORMATTED"]["DATETIME"]) > 0
											? $arComment["EVENT_FORMATTED"]["DATETIME"]
											: ($arComment["LOG_DATE_DAY"] == ConvertTimeStamp() ? $arComment["LOG_TIME_FORMAT"] : $arComment["LOG_DATE_DAY"]." ".$arComment["LOG_TIME_FORMAT"])
									);
								?></div><?
								$strBottomBlockComments = "";

								ob_start();

								if (
									strlen($arComment["EVENT"]["RATING_TYPE_ID"]) > 0
									&& $arComment["EVENT"]["RATING_ENTITY_ID"] > 0
									&& $arParams["SHOW_RATING"] == "Y"
								)
								{
									$APPLICATION->IncludeComponent(
										"bitrix:rating.vote", "mobile_comment_like",
										Array(
										
											"ENTITY_TYPE_ID" => $arComment["EVENT"]["RATING_TYPE_ID"],
											"ENTITY_ID" => $arComment["EVENT"]["RATING_ENTITY_ID"],
											"OWNER_ID" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["ID"],
											"USER_VOTE" => array_key_exists($arComment["EVENT"]["RATING_ENTITY_ID"], $arResult["RATING_COMMENTS"]) ? $arResult["RATING_COMMENTS"][$arComment["EVENT"]["RATING_ENTITY_ID"]]["USER_VOTE"] : false,
											"USER_HAS_VOTED" => array_key_exists($arComment["EVENT"]["RATING_ENTITY_ID"], $arResult["RATING_COMMENTS"]) ? $arResult["RATING_COMMENTS"][$arComment["EVENT"]["RATING_ENTITY_ID"]]["USER_HAS_VOTED"] : false,
											"TOTAL_VOTES" => array_key_exists($arComment["EVENT"]["RATING_ENTITY_ID"], $arResult["RATING_COMMENTS"]) ? $arResult["RATING_COMMENTS"][$arComment["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_VOTES"] : false,
											"TOTAL_POSITIVE_VOTES" => array_key_exists($arComment["EVENT"]["RATING_ENTITY_ID"], $arResult["RATING_COMMENTS"]) ? $arResult["RATING_COMMENTS"][$arComment["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_POSITIVE_VOTES"] : false,
											"TOTAL_NEGATIVE_VOTES" => array_key_exists($arComment["EVENT"]["RATING_ENTITY_ID"], $arResult["RATING_COMMENTS"]) ? $arResult["RATING_COMMENTS"][$arComment["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_NEGATIVE_VOTES"] : false,
											"TOTAL_VALUE" => array_key_exists($arComment["EVENT"]["RATING_ENTITY_ID"], $arResult["RATING_COMMENTS"]) ? $arResult["RATING_COMMENTS"][$arComment["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_VALUE"] : false,
											"PATH_TO_USER_PROFILE" => $arComment["CREATED_BY"]["TOOLTIP_FIELDS"]["PATH_TO_SONET_USER_PROFILE"]
										),
										$component,
										array("HIDE_ICONS" => "Y")
									);
								}

								$strBottomBlockComments = ob_get_contents();
								ob_end_clean();

								if (strlen($strBottomBlockComments) > 0)
								{
									?><?=$strBottomBlockComments;?><? // comments rating
								}

							?></div>
						</div><?
					}
				}
				?><span id="post-comment-last-after"></span>
			</div><? // post-comments-wrap
		}

		if ($arParams["IS_LIST"] != "Y" && CMobile::getApiVersion() < 4 && CMobile::getPlatform() != "android")
		{
			?></div><? // post-card-wrap
		}

		if (
			$arParams["IS_LIST"] != "Y"
			&& isset($arEvent["HAS_COMMENTS"])
			&& $arEvent["HAS_COMMENTS"] == "Y"
			&& isset($arEvent["CAN_ADD_COMMENTS"])
			&& $arEvent["CAN_ADD_COMMENTS"] == "Y"
		)
		{
			if (CMobile::getApiVersion() >= 4)
			{
				?>
				<script>
				function commentsNativeInputCallback(text)
				{
					function disableSubmitButton(status)
					{
						app.showInputLoading(status);
					}

					if (text.length == 0)
						return;

					app.showInputLoading(true);

					var post_data = {
						'sessid': '<?=bitrix_sessid()?>',
						'site': '<?=CUtil::JSEscape(SITE_ID)?>',
						'lang': '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
						'log_id': <?=intval($arParams["LOG_ID"])?>,
						'message': text,
						'action': 'add_comment'
					};

					BMAjaxWrapper.Wrap({
						'type': 'json',
						'method': 'POST',
						'url': '/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php',
						'data': post_data,
						'callback': function(post_response_data)
						{
							if (post_response_data["commentID"] != 'undefined' && parseInt(post_response_data["commentID"]) > 0)
							{
								var commentID = post_response_data["commentID"];
								get_data = {
									'sessid': '<?=bitrix_sessid()?>',
									'site': '<?=CUtil::JSEscape(SITE_ID)?>',
									'lang': '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
									'cid': commentID,
									'as': <?=intval($arParams["AVATAR_SIZE_COMMENT"])?>,
									'nt': '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
									'sl': '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',
									'dtf': '<?=CUtil::JSEscape($arParams["DATE_TIME_FORMAT"])?>',
									'p_user': '<?=CUtil::JSEscape($arParams["PATH_TO_USER"])?>',
									'action': 'get_comment'
								};

								BMAjaxWrapper.Wrap({
									'type': 'json',
									'method': 'POST',
									'url': '/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php',
									'data': get_data,
									'callback': function(get_response_data)
									{
										disableSubmitButton(false);
										if (get_response_data["arCommentFormatted"] != 'undefined')
											__MSLShowNewComment(get_response_data["arCommentFormatted"]);
										app.clearInput();
										__MSLDetailMoveBottom();

										var followBlock = BX('log_entry_follow_' + post_data.log_id, true);
										if (followBlock)
										{
											var strFollowOld = (followBlock.getAttribute("data-follow") == "Y" ? "Y" : "N");
											if (strFollowOld == "N")
											{
												BX.removeClass(followBlock, 'post-item-follow');
												BX.addClass(followBlock, 'post-item-follow-active');
												followBlock.setAttribute("data-follow", "Y");
											}
										}
									},
									'callback_failure': function() { disableSubmitButton(false); }
								});
							}
							else { disableSubmitButton(false); }
						},
						'callback_failure': function() { disableSubmitButton(false); }
					});
				}

				app.showInput({
					placeholder: "<?=GetMessageJS("MOBILE_LOG_COMMENT_ADD_TITLE")?>",
					button_name: "<?=GetMessageJS("MOBILE_LOG_COMMENT_ADD_BUTTON_SEND")?>",
					action:function(text)
					{
						commentsNativeInputCallback(text);
					}
				});
				</script>
				<?
			}
			else
			{
				?><form class="send-message-block" id="comment_send_form">
					<input type="hidden" id="comment_send_form_logid" name="sonet_log_comment_logid" value="<?=$arParams["LOG_ID"]?>">
					<textarea id="comment_send_form_comment" class="send-message-input" placeholder="<?=GetMessage("MOBILE_LOG_COMMENT_ADD_TITLE")?>"></textarea>
					<input type="button" id="comment_send_button" class="send-message-button" value="<?=GetMessage("MOBILE_LOG_COMMENT_ADD_BUTTON_SEND")?>" ontouchstart="BX.toggleClass(this, 'send-message-button-press');" ontouchend="BX.toggleClass(this, 'send-message-button-press');">
				</form>
				<script>

				document.addEventListener("DOMContentLoaded", function() {
					BitrixMobile.Utils.autoResizeForm(
							document.getElementById("comment_send_form_comment"),
							document.getElementById("post-card-wrap")
					);
				}, false);

				BX.bind(BX('comment_send_button'), 'click', function(e)
				{
					if (BX('comment_send_form_comment').value.length > 0)
					{
						__MSLDisableSubmitButton(true);

						var post_data = {
							'sessid': '<?=bitrix_sessid()?>',
							'site': '<?=CUtil::JSEscape(SITE_ID)?>',
							'lang': '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
							'log_id': BX('comment_send_form_logid').value,
							'message': BX('comment_send_form_comment').value,
							'action': 'add_comment'
						};

						BMAjaxWrapper.Wrap({
							'type': 'json',
							'method': 'POST',
							'url': '/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php',
							'data': post_data,
							'callback': function(post_response_data)
							{
								if (post_response_data["commentID"] != 'undefined' && parseInt(post_response_data["commentID"]) > 0)
								{
									var commentID = post_response_data["commentID"];
									get_data = {
										'sessid': '<?=bitrix_sessid()?>',
										'site': '<?=CUtil::JSEscape(SITE_ID)?>',
										'lang': '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
										'cid': commentID,
										'as': <?=intval($arParams["AVATAR_SIZE_COMMENT"])?>,
										'nt': '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
										'sl': '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',
										'dtf': '<?=CUtil::JSEscape($arParams["DATE_TIME_FORMAT"])?>',
										'p_user': '<?=CUtil::JSEscape($arParams["PATH_TO_USER"])?>',
										'action': 'get_comment'
									};

									BMAjaxWrapper.Wrap({
										'type': 'json',
										'method': 'POST',
										'url': '/bitrix/components/bitrix/mobile.socialnetwork.log.entry/ajax.php',
										'data': get_data,
										'callback': function(get_response_data)
										{
											__MSLDisableSubmitButton(false);
											if (get_response_data["arCommentFormatted"] != 'undefined')
												__MSLShowNewComment(get_response_data["arCommentFormatted"]);
											BitrixMobile.Utils.resetAutoResize(BX("comment_send_form_comment"), BX("post-card-wrap"));

											var followBlock = BX('log_entry_follow_' + post_data.log_id, true);
											if (followBlock)
											{
												var strFollowOld = (followBlock.getAttribute("data-follow") == "Y" ? "Y" : "N");
												if (strFollowOld == "N")
												{
													BX.removeClass(followBlock, 'post-item-follow');
													BX.addClass(followBlock, 'post-item-follow-active');
													followBlock.setAttribute("data-follow", "Y");
												}
											}
										},
										'callback_failure': function() { __MSLDisableSubmitButton(false); }
									});
								}
								else { __MSLDisableSubmitButton(false); }
							},
							'callback_failure': function() { __MSLDisableSubmitButton(false); }
						});
					}
				});
				</script><?
			}
		}
	}
}
?>