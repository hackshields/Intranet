<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
include_once($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/components/bitrix/socialnetwork.blog.post/mobile/functions.php");

if(!empty($arResult["Post"]) > 0)
{
	if (!$arParams["IS_LIST"] && CMobile::getApiVersion() < 4 && CMobile::getPlatform() != "android")
	{
		?><div class="post-card-wrap" id="post-card-wrap" onclick=""><?
	}

	$item_class = (!$arParams["IS_LIST"] ? "post-wrap" : "lenta-item".($arParams["IS_UNREAD"] ? " lenta-item-new" : ""));

	if ($arParams["IS_LIST"])
	{
		?><script type="text/javascript">
			arLogTs.entry_<?=intval($arParams["LOG_ID"])?> = <?=intval($arParams["LAST_LOG_TS"] -  CTimeZone::GetOffset())?>;
		</script><?
	}

	?><div class="<?=($item_class)?>" id="lenta_item_<?=intval($arParams["LOG_ID"])?>"><?

		?><div class="post-item-top-wrap"><?

			?><div class="post-item-top"><?

				?><div class="avatar"<?=(strlen($arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]) > 0 ? " style=\"background:url('".$arResult["arUser"]["PERSONAL_PHOTO_resized"]["src"]."') 0 0 no-repeat; background-size: 29px 29px;\"" : "")?>></div>
				<div class="post-item-top-cont"><?
					$anchor_id = $arResult["Post"]["ID"];
					$arTmpUser = array(
							"NAME" => $arResult["arUser"]["~NAME"],
							"LAST_NAME" => $arResult["arUser"]["~LAST_NAME"],
							"SECOND_NAME" => $arResult["arUser"]["~SECOND_NAME"],
							"LOGIN" => $arResult["arUser"]["~LOGIN"],
							"NAME_LIST_FORMATTED" => "",
						);
					?><a class="post-item-top-title" href="<?=$arResult["arUser"]["url"]?>"><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] != "N" ? true : false))?></a><?

					$timestamp = MakeTimeStamp($arResult["Post"]["DATE_PUBLISH"]);
					$timeFormated = FormatDate(GetMessage("BLOG_MOBILE_FORMAT_TIME"), $timestamp);

					if (strlen($arParams["DATE_TIME_FORMAT_FROM_LOG"]) <= 0)
						$dateTimeFormated = __SMLFormatDate($timestamp);
					else
						$dateTimeFormated = FormatDate(
							(
								$arParams["DATE_TIME_FORMAT_FROM_LOG"] == "FULL" 
									? $GLOBALS["DB"]->DateFormatToPHP(str_replace(":SS", "", FORMAT_DATETIME)) 
									: $arParams["DATE_TIME_FORMAT_FROM_LOG"]
							),
							$timestamp
						);

					if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
						$dateTimeFormated = ToLower($dateTimeFormated);

					// strip current year
					if (
						!empty($arParams["DATE_TIME_FORMAT_FROM_LOG"]) 
						&& (
							$arParams["DATE_TIME_FORMAT_FROM_LOG"] == "j F Y G:i" 
							|| $arParams["DATE_TIME_FORMAT_FROM_LOG"] == "j F Y g:i a"
						)
					)
					{
						$dateTimeFormated = ltrim($dateTimeFormated, "0");
						$curYear = date("Y");
						$dateTimeFormated = str_replace(array("-".$curYear, "/".$curYear, " ".$curYear, ".".$curYear), "", $dateTimeFormated);
					}

					if ($arParams["IS_LIST"]) // list
					{
						if (
							(ConvertTimeStamp($timestamp, "SHORT") == ConvertTimeStamp())
							|| (intval((time() - $timestamp) / 60 / 60) < 24) // last 24h
						)
							$datetime = $timeFormated;
						else
							$datetime = $dateTimeFormated;

					}
					else // detail
					{
						$arFormat = Array(
							"tommorow" => "tommorow, ".GetMessage("BLOG_MOBILE_FORMAT_TIME"),
							"today" => "today, ".GetMessage("BLOG_MOBILE_FORMAT_TIME"),
							"yesterday" => "yesterday, ".GetMessage("BLOG_MOBILE_FORMAT_TIME"),
							"" => (date("Y", $timestamp) == date("Y") ? GetMessage("BLOG_MOBILE_FORMAT_DATE") : GetMessage("BLOG_MOBILE_FORMAT_DATE_YEAR"))
						);
						$datetime = FormatDate($arFormat, $timestamp);
					}

					if (!$arParams["IS_LIST"])
					{
						?><div class="post-date"><?=$datetime?></div><?
					}

					$strTopic = "";

					if(!empty($arResult["Post"]["SPERM"]))
					{
						$strTopic .= '<div class="post-item-top-text post-item-top-arrow">'.GetMessage("BLOG_MOBILE_TITLE_24").'</div>';

						$cnt = count($arResult["Post"]["SPERM"]["U"]) + count($arResult["Post"]["SPERM"]["SG"]) + count($arResult["Post"]["SPERM"]["DR"]);
						$i = 0;

						if(!empty($arResult["Post"]["SPERM"]["U"]))
						{
							foreach($arResult["Post"]["SPERM"]["U"] as $id => $val)
							{
								$i++;
								if ($i == 4)
								{
									$more_cnt = $cnt - 3;
									if (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
									)
										$suffix = 5;
									else
										$suffix = $more_cnt % 10;

									$moreClick = " onclick=\"showHiddenDestination('".$arResult["Post"]["ID"]."', this)\"";
									$strTopic .= "&nbsp;<span class=\"post-destination-more\"".$moreClick." ontouchstart=\"BX.toggleClass(this, 'post-destination-more-touch');\" ontouchend=\"BX.toggleClass(this, 'post-destination-more-touch');\">".GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))."</span><span id=\"blog-destination-hidden-".$arResult["Post"]["ID"]."\" style=\"display:none;\">";
								}

								if($i != 1)
									$strTopic .= ", ";

								if($val["NAME"] != "All")
									$strTopic .= '<a href="'.$val["URL"].'" class="post-item-destination post-item-dest-users">'.$val["NAME"].'</a>';
								else
									$strTopic .= '<span class="post-item-destination post-item-dest-all-users">'.GetMessage("BLOG_DESTINATION_ALL").'</span>';
							}
						}

						if(!empty($arResult["Post"]["SPERM"]["SG"]))
						{
							foreach($arResult["Post"]["SPERM"]["SG"] as $id => $val)
							{
								$i++;
								if ($i == 4)
								{
									$more_cnt = $cnt - 3;
									if (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
									)
										$suffix = 5;
									else
										$suffix = $more_cnt % 10;

									$moreClick = " onclick=\"showHiddenDestination('".$arResult["Post"]["ID"]."', this)\"";
									$strTopic .= "<span class=\"post-destination-more\"".$moreClick." ontouchstart=\"BX.toggleClass(this, 'post-destination-more-touch');\" ontouchend=\"BX.toggleClass(this, 'post-destination-more-touch');\">".GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))."</span><span id=\"blog-destination-hidden-".$arResult["Post"]["ID"]."\" style=\"display:none;\">";
								}
								
								if($i != 1)
									$strTopic .= ", ";

								$strTopic .= '<a href="'.$val["URL"].'" class="post-item-destination post-item-dest-sonetgroups">'.$val["NAME"].'</a>';
							}
						}

						if(!empty($arResult["Post"]["SPERM"]["DR"]))
						{
							foreach($arResult["Post"]["SPERM"]["DR"] as $id => $val)
							{
								$i++;
								if($i == 4)
								{
									$more_cnt = $cnt - 3;
									if (
										($more_cnt % 100) > 10
										&& ($more_cnt % 100) < 20
									)
										$suffix = 5;
									else
										$suffix = $more_cnt % 10;

									$moreClick = " onclick=\"showHiddenDestination('".$arResult["Post"]["ID"]."', this)\"";
									$strTopic .= "<span class=\"post-destination-more\"".$moreClick." ontouchstart=\"BX.toggleClass(this, 'post-destination-more-touch');\" ontouchend=\"BX.toggleClass(this, 'post-destination-more-touch');\">".GetMessage("BLOG_DESTINATION_MORE_".$suffix, Array("#NUM#" => $more_cnt))."</span><span id=\"blog-destination-hidden-".$arResult["Post"]["ID"]."\" style=\"display:none;\">";
								}

								if($i != 1)
									$strTopic .= ", ";

								$strTopic .= '<span class="post-item-destination post-item-dest-department">'.$val["NAME"].'</span>';
							}
						}

						if (
							isset($arResult["Post"]["SPERM_HIDDEN"])
							&& intval($arResult["Post"]["SPERM_HIDDEN"]) > 0
						)
						{
							if (
								($arResult["Post"]["SPERM_HIDDEN"] % 100) > 10
								&& ($arResult["Post"]["SPERM_HIDDEN"] % 100) < 20
							)
								$suffix = 5;
							else
								$suffix = $arResult["Post"]["SPERM_HIDDEN"] % 10;

							$strTopic .= "&nbsp;".GetMessage("BLOG_DESTINATION_HIDDEN_".$suffix, Array("#NUM#" => intval($arResult["Post"]["SPERM_HIDDEN"])));
						}
					}
					else
						$strTopic .= '<div class="post-item-top-text">'.GetMessage("BLOG_MOBILE_TITLE_24").'</div>';

					?><div class="post-item-top-topic"><?=$strTopic ?></div><?
				?></div><?
				if ($arParams["IS_LIST"])
				{
					?><div class="lenta-item-time"><?=$datetime?></div><?
				}
			?></div><?

			$post_item_style = (!$arParams["IS_LIST"] && $_REQUEST["show_full"] == "Y" ? "post-item-post-block-full" : "post-item-post-block");
			$strOnClick = ($arParams["IS_LIST"] ? " onclick=\"__MSLOpenLogEntry(".intval($arParams["LOG_ID"]).", '".str_replace("#log_id#", $arParams["LOG_ID"], $arParams["PATH_TO_LOG_ENTRY"])."', false, event);\"" : "");
				
			?><div class="<?=$post_item_style?>"<?=$strOnClick?> id="post_block_check_cont_<?=$arParams["LOG_ID"]?>"><?
									
				if($arResult["Post"]["MICRO"] != "Y")
				{
					?><div class="post-text-title" id="post_text_title_<?=intval($arParams["LOG_ID"])?>"><?=$arResult["Post"]["TITLE"]?></div><?
				}

				?><div class="post-item-text" id="post_block_check_<?=intval($arParams["LOG_ID"])?>"><?=$arResult["Post"]["textFormated"]?></div><?
				if(!empty($arResult["images"]))
				{
					?><div class="post-item-attached-img-wrap"><?
						foreach($arResult["images"] as $val)
						{
							?><div class="post-item-attached-img-block" onclick="app.openNewPage('<?=$val["full"]?>'); event.stopPropagation();"><img class="post-item-attached-img" src="<?=$val["small"]?>" alt="" border="0"></div><?
						}
					?></div><?
				}

				if($arResult["POST_PROPERTIES"]["SHOW"] == "Y")
				{
					?><div class="post-item-attached-file-wrap"><?
						$eventHandlerID = false;
						$eventHandlerID = AddEventHandler('main', 'system.field.view.file', '__blogUFfileShowMobile');
						foreach ($arResult["POST_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
						{
							if(!empty($arPostField["VALUE"]))
							{
								?><?$APPLICATION->IncludeComponent(
									"bitrix:system.field.view",
									$arPostField["USER_TYPE"]["USER_TYPE_ID"],
									array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y")
								);?><?
							}
						}
						if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
							RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
					?></div><?
				}

				if (!empty($arResult["GRATITUDE"]))
				{
					?><div class="lenta-info-block lenta-block-grat"><?
						?><div class="lenta-block-grat-medal<?=(strlen($arResult["GRATITUDE"]["TYPE"]["XML_ID"]) > 0 ? " lenta-block-grat-medal-".$arResult["GRATITUDE"]["TYPE"]["XML_ID"] : "")?>"></div><?
						?><div class="lenta-block-grat-users"><?				
							foreach($arResult["GRATITUDE"]["USERS_FULL"] as $arGratUser)
							{
								?><div class="lenta-block-grat-user">
									<div class="lenta-new-grat-avatar">
										<div class="avatar"<?if($arGratUser["AVATAR_SRC"]):?> style="background:url('<?=$arGratUser["AVATAR_SRC"]?>') 0 0 no-repeat; background-size: 29px 29px;"<?endif?>></div>
									</div>
									<div class="lenta-info-block-content">
										<div class="lenta-important-block-title"><a href="<?=$arGratUser["URL"]?>"><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arGratUser)?></a></div>
										<div class="lenta-important-block-text"><?=htmlspecialcharsbx($arGratUser["WORK_POSITION"])?></div>
									</div>
								</div><?
							}
						?></div><?
					?></div><?
				}
				
				$post_more_block = ($_REQUEST["show_full"] != "Y" ? '<div class="post-more-block" id="post_more_block_'.$arParams["LOG_ID"].'"></div>' : '');	
				echo $post_more_block;
			
			?></div><? // post-item-post-block, post_block_check_cont_..

			$post_more_corner = ($_REQUEST["show_full"] != "Y" ? '<div class="post-item-corner" id="post_more_corner_'.$arParams["LOG_ID"].'"></div>' : '');
			echo $post_more_corner;
			
		?></div><? // post-item-top-wrap

		if ($arResult["is_ajax_post"] != "Y")
			ob_start();

		if ($arResult["Post"]["ENABLE_COMMENTS"] == "Y")
		{
			$bHasComments = true;

			if ($arResult["is_ajax_post"] != "Y")
				ob_start(); // inner buffer

			$arCommentsResult = $APPLICATION->IncludeComponent(
				"bitrix:socialnetwork.blog.post.comment",
				"mobile",
				Array(
					"PATH_TO_BLOG" => $arParams["PATH_TO_BLOG"],
					"PATH_TO_POST" => "/company/personal/user/#user_id#/blog/#post_id#/",
					"PATH_TO_POST_MOBILE" => $APPLICATION->GetCurPageParam("", array("LAST_LOG_TS")),
					"PATH_TO_USER" => $arParams["PATH_TO_USER"],
					"PATH_TO_SMILE" => $arParams["PATH_TO_SMILE"],
					"PATH_TO_MESSAGES_CHAT" => $arParams["PATH_TO_MESSAGES_CHAT"],
					"ID" => $arResult["Post"]["ID"],
					"LOG_ID" => $arParams["LOG_ID"],
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"COMMENTS_COUNT" => "5",
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"USER_ID" => $GLOBALS["USER"]->GetID(),
					"SONET_GROUP_ID" => $arParams["SONET_GROUP_ID"],
					"NOT_USE_COMMENT_TITLE" => "Y",
					"USE_SOCNET" => "Y",
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
					"SHOW_YEAR" => $arParams["SHOW_YEAR"],
					"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
					"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
					"SHOW_RATING" => $arParams["SHOW_RATING"],
					"RATING_TYPE" => $arParams["RATING_TYPE"],
					"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
					"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
					"ALLOW_VIDEO"  => $arParams["ALLOW_VIDEO"],
					"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"],
					"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
					"AJAX_POST" => "Y",
					"POST_DATA" => $arResult["PostSrc"],
					"BLOG_DATA" => $arResult["Blog"],
					"FROM_LOG" => ($arParams["IS_LIST"] ? "Y" : false),
					"bFromList" => ($arParams["IS_LIST"] ? true: false),
					"LAST_LOG_TS" => $arParams["LAST_LOG_TS"],
	//				"MARK_NEW_COMMENTS" => "Y",
					"AVATAR_SIZE" => $arParams["AVATAR_SIZE_COMMENT"],
					"MOBILE" => "Y",
					"ATTACHED_IMAGE_MAX_WIDTH_FULL" => 640,
					"ATTACHED_IMAGE_MAX_HEIGHT_FULL" => 832,
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);

			$strCommentsBlock = (!$arParams["IS_LIST"] ? ob_get_contents() : "");
			ob_end_clean(); // inner buffer

			if ($arParams["IS_LIST"])
				$strPath = str_replace("#log_id#", $arParams["LOG_ID"], $arParams["PATH_TO_LOG_ENTRY"]);
			$strOnClickComments = ($arParams["IS_LIST"] ? " onclick=\"__MSLOpenLogEntry(".intval($arParams["LOG_ID"]).", '".$strPath."', true);\"" : " onclick=\"__MSLDetailMoveBottom();\"");
						
			?><div class="post-item-informers post-item-inform-comments"<?=$strOnClickComments?>><?
				?><div class="post-item-inform-left"></div><?
				?><div class="post-item-inform-right" id="informer_comments_<?=$arParams["LOG_ID"]?>"><?

				$num_comments = (isset($arParams["COMMENTS_COUNT"]) ? $arParams["COMMENTS_COUNT"] : intval($arResult["Post"]["NUM_COMMENTS"]));				

				if (
					($arParams["USE_FOLLOW"] != "Y" || $arParams["FOLLOW"] == "Y")
					&& intval($arCommentsResult["newCountWOMark"]) > 0
				)
				{
					?><span id="informer_comments_all_<?=$arParams["LOG_ID"]?>"><?
						$old_comments = intval(abs($num_comments - intval($arCommentsResult["newCountWOMark"])));
						echo ($old_comments > 0 ? $old_comments : '');
					?></span><?
					?><span id="informer_comments_new_<?=$arParams["LOG_ID"]?>">+<?=intval($arCommentsResult["newCountWOMark"])?></span><?
				}
				else
				{
					?><?=$num_comments?><?
				}
				?></div><?
			?></div><?
		}
		else
			$bHasComments = false;
		
		if ($arParams["SHOW_RATING"] == "Y")
		{
			$arResultVote = $APPLICATION->IncludeComponent(
				"bitrix:rating.vote", "mobile_like",
				Array(
					"ENTITY_TYPE_ID" => "BLOG_POST",
					"ENTITY_ID" => $arResult["Post"]["ID"],
					"OWNER_ID" => $arResult["Post"]["AUTHOR_ID"],
					"USER_VOTE" => $arResult["RATING"][$arResult["Post"]["ID"]]["USER_VOTE"],
					"USER_HAS_VOTED" => $arResult["RATING"][$arResult["Post"]["ID"]]["USER_HAS_VOTED"],
					"TOTAL_VOTES" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VOTES"],
					"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_POSITIVE_VOTES"],
					"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_NEGATIVE_VOTES"],
					"TOTAL_VALUE" => $arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VALUE"],
					"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
					"EXTENDED" => (!$arParams["IS_LIST"] ? "Y" : "N"),
					"VOTE_RAND" => (!$arParams["IS_LIST"] && intval($_REQUEST["LIKE_RANDOM_ID"]) > 0 ? intval($_REQUEST["LIKE_RANDOM_ID"]) : false)
				),
				$component,
				array("HIDE_ICONS" => "Y")
			);

			$bRatingExtended = (
				!$arParams["IS_LIST"]
				&& intval($GLOBALS["APPLICATION"]->GetPageProperty("api_version")) >= 2
			);

			$bRatingExtendedOpen = (
				$bRatingExtended
				&& intval($arResult["RATING"][$arResult["Post"]["ID"]]["TOTAL_VOTES"]) > 0
			);

			if (
				$arParams["IS_LIST"] == "Y"
				&& intval($arResultVote["VOTE_RAND"]) > 0
			)
			{
				?><script type="text/javascript">
					arLikeRandomID.entry_<?=intval($arParams["LOG_ID"])?> = <?=intval($arResultVote["VOTE_RAND"])?>;
				</script><?
			}
		}

		if (
			$bHasComments
			&& array_key_exists("FOLLOW", $arParams)
		)
		{
			$follow_type_default = " post-item-follow-default".($arParams["FOLLOW_DEFAULT"] == "Y" ? "-active" : "");
			$follow_type = " post-item-follow".($arParams["FOLLOW"] == "Y" ? "-active" : "");
			?><div id="log_entry_follow_<?=intval($arParams["LOG_ID"])?>" data-follow="<?=($arParams["FOLLOW"] == "Y" ? "Y" : "N")?>" class="post-item-informers<?=$follow_type_default?><?=$follow_type?>" onclick="__MSLSetFollow(<?=$arParams["LOG_ID"]?>)">
				<div class="post-item-inform-left"></div>
			</div><?
		}

		if ($_REQUEST["show_full"] != "Y")
		{
			if ($arParams["IS_LIST"])
				$strOnClickMore = "onclick=\"__MSLOpenLogEntry(".intval($arParams["LOG_ID"]).", '".str_replace("#log_id#", $arParams["LOG_ID"], $arParams["PATH_TO_LOG_ENTRY"])."&show_full=Y');\"";
			else
				$strOnClickMore = "onclick=\"__MSLExpandText(".intval($arParams["LOG_ID"]).");\"";

			?><div <?=$strOnClickMore?> class="post-item-more" ontouchstart="BX.toggleClass(this, 'post-item-more-pressed');" ontouchend="BX.toggleClass(this, 'post-item-more-pressed');" style="display: none;" id="post_block_check_more_<?=$arParams["LOG_ID"]?>"><?=GetMessage("BLOG_LOG_MORE")?></div><?
		}

		if ($bRatingExtended)
		{
			?><div class="post-item-inform-footer" id="rating-footer"></div><?
		}

		$strBottomBlock = ob_get_contents();
		ob_end_clean();
				

		if (strlen($strBottomBlock) > 0)
		{
			?><div <?=($bRatingExtended ? 'id="post_item_inform_wrap"' : '')?> id="post_inform_wrap_<?=$arEvent["EVENT"]["ID"]?>" class="post-item-inform-wrap<?=($bRatingExtendedOpen ? " post-item-inform-action" : "")?>"><?=$strBottomBlock;?></div><?
		}

	?></div><? // post-wrap / lenta-item

	?><script type="text/javascript">
	arBlockToCheck[arBlockToCheck.length] = {
		lenta_item_id: 'lenta_item_<?=$arParams["LOG_ID"]?>',
		text_block_id: 'post_block_check_cont_<?=$arParams["LOG_ID"]?>',
		title_block_id: 'post_block_check_title_<?=$arParams["LOG_ID"]?>',
		more_block_id: 'post_block_check_more_<?=$arParams["LOG_ID"]?>',
		more_overlay_id: 'post_more_block_<?=$arParams["LOG_ID"]?>',
		more_corner_id: 'post_more_corner_<?=$arParams["LOG_ID"]?>',
		post_inform_wrap_id: 'post_inform_wrap_<?=$arParams["LOG_ID"]?>'
	};
	</script><?

	if (!$arParams["IS_LIST"])
	{
		if (
			$arResult["Post"]["ENABLE_COMMENTS"] == "Y"
			&& strlen($strCommentsBlock) > 0
		)
			echo $strCommentsBlock;

		if (CMobile::getApiVersion() < 4 && CMobile::getPlatform() != "android")
		{
			?></div><? // post-card-wrap
		}

		if(
			IntVal($_REQUEST["comment_post_id"]) <= 0
			&& $arCommentsResult["CanUserComment"]
		)
		{
			if (CMobile::getApiVersion() >= 4)
			{
				?>
				<script>
				function blogCommentsNativeInputCallback(text)
				{
					function disableSubmitButton(status)
					{
						app.showInputLoading(status);
					}

					if (text.length == 0)
						return;

					app.showInputLoading(true);

					var data = {
						'sessid': '<?=bitrix_sessid()?>',
						'comment_post_id': <?=intval($arResult["Post"]["ID"])?>,
						'act': 'add',
						'post': 'Y',
						'comment': text,
						'decode': 'Y'
						<?
						if (
							$_REQUEST["ACTION"] == "CONVERT"
							&& strlen($_REQUEST["ENTITY_TYPE_ID"]) > 0
							&& intval($_REQUEST["ENTITY_ID"]) > 0
						)
						{
							?>
							,'ACTION': 'CONVERT'
							,'ENTITY_TYPE_ID': '<?=CUtil::JSEscape($_REQUEST["ENTITY_TYPE_ID"])?>'
							,'ENTITY_ID': <?=intval($_REQUEST["ENTITY_ID"])?>
							<?
						}
						?>
					};

					BMAjaxWrapper.Wrap({
						'type': 'html',
						'method': 'POST',
						'url': '<?=$GLOBALS["APPLICATION"]->GetCurPageParam("", array("sessid", "comment_post_id", "act", "post", "comment", "decode", "ACTION", "ENTITY_TYPE_ID", "ENTITY_ID"))?>',
						'data': data,
						'callback': function(response)
						{
							disableSubmitButton(false);
							showNewComment(response);
							app.clearInput();
							__MSLDetailMoveBottom();

							var followBlock = BX("log_entry_follow_<?=intval($arParams["LOG_ID"])?>", true);

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

				app.showInput({
					placeholder: "<?=GetMessageJS("BLOG_C_ADD_TITLE")?>",
					button_name: "<?=GetMessageJS("BLOG_C_BUTTON_SEND")?>",
					action:function(text)
					{
						blogCommentsNativeInputCallback(text);
					}
				});
				</script>
				<?
			}
			else
			{
				?><form class="send-message-block" id="comment_send_form" action="<?=POST_FORM_ACTION_URI?>" method="POST">
					<?=bitrix_sessid_post()?>
					<input type="hidden" name="comment_post_id" value="<?=intval($arResult["Post"]["ID"])?>">
					<input type="hidden" name="act" value="add">
					<input type="hidden" name="post" value="Y">
					<textarea id="comment_send_form_comment" class="send-message-input" placeholder="<?=GetMessage("BLOG_C_ADD_TITLE")?>"></textarea>
					<input type="button" id="comment_send_button" class="send-message-button" value="<?=GetMessage("BLOG_C_BUTTON_SEND")?>" ontouchstart="BX.toggleClass(this, 'send-message-button-press');" ontouchend="BX.toggleClass(this, 'send-message-button-press');">
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

						var data = {
							'sessid': '<?=bitrix_sessid()?>',
							'comment_post_id': <?=intval($arResult["Post"]["ID"])?>,
							'act': 'add',
							'post': 'Y',
							'comment': BX('comment_send_form_comment').value,
							'decode': 'Y'
							<?
							if (
								$_REQUEST["ACTION"] == "CONVERT"
								&& strlen($_REQUEST["ENTITY_TYPE_ID"]) > 0
								&& intval($_REQUEST["ENTITY_ID"]) > 0
							)
							{
								?>
								,'ACTION': 'CONVERT'
								,'ENTITY_TYPE_ID': '<?=CUtil::JSEscape($_REQUEST["ENTITY_TYPE_ID"])?>'
								,'ENTITY_ID': <?=intval($_REQUEST["ENTITY_ID"])?>
								<?
							}
							?>
						};

						BMAjaxWrapper.Wrap({
							'type': 'html',
							'method': 'POST',
							'url': '<?=$GLOBALS["APPLICATION"]->GetCurPageParam("", array("sessid", "comment_post_id", "act", "post", "comment", "decode", "ACTION", "ENTITY_TYPE_ID", "ENTITY_ID"))?>',
							'data': data,
							'callback': function(response)
							{
								showNewComment(response, true);
								BitrixMobile.Utils.resetAutoResize(BX("comment_send_form_comment"), BX("post-card-wrap"));
								__MSLDetailMoveBottom();
								__MSLDisableSubmitButton(false);

								var followBlock = BX("log_entry_follow_<?=intval($arParams["LOG_ID"])?>", true);

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
				});
				</script><?
			}
		}
	}
}
elseif(!$arResult["bFromList"])
	echo GetMessage("BLOG_BLOG_BLOG_NO_AVAIBLE_MES");
?>
