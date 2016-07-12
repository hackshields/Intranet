<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/components/bitrix/socialnetwork.blog.post.comment/mobile/script_attached.js").'"></script>', true);
include_once($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/components/bitrix/socialnetwork.blog.post/mobile/functions.php");

if ($arResult["is_ajax_post"] == "Y")
{
	$APPLICATION->RestartBuffer();
}
else
{
	?><script>
	app.setPageID('BLOG_POST_<?=$arParams["ID"]?>');
	BX.message({
		SBPCurlToMore: '<?=CUtil::JSEscape($GLOBALS["APPLICATION"]->GetCurPageParam("last_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y", array("last_comment_id", "comment_post_id", "IFRAME")))?>',
		SBPCurlToNew: '<?=CUtil::JSEscape($arResult["urlToNew"])?>',
		SBPClogID: <?=intval($arParams["LOG_ID"])?>
	});

	<?
	if(
		CModule::IncludeModule("pull") 
		&& IntVal($arResult["userID"]) > 0
	)
	{
		?>
		var arCommentsToShow = [];
		var arCommentID = [];

		var showNewPullCommentTimeout = null;
		var bCommentAjaxEnd = true;

		function drawPullComment(j)
		{
			if(!bCommentAjaxEnd)
			{
				setTimeout(function() { drawPullComment(j); }, 100);
			}
			else
			{
				showNewPullComment(arCommentsToShow[j].commentID, arCommentsToShow[j].postID);
				app.onCustomEvent('onLogEntryRead', { log_id: <?=$arParams["LOG_ID"]?>, ts: arCommentsToShow[j].ts, bPull: true }); // just for TS
				if (j == (arCommentsToShow.length - 1))
				{
					arCommentsToShow = [];
					arCommentID = [];
				}
			}
		}

		app.onCustomEvent('onPullExtendWatch', {'id': 'BLOG_POST_<?=$arParams["ID"]?>'});
		BX.addCustomEvent("onPull", function(data) {
			if (
				data.module_id == "blog" 
				&& data.command == 'comment'
				&& data.params["POST_ID"] == <?=intval($arResult["Post"]["ID"])?>
			)
			{
				if(
					!BX('blg-comment-' + data.params["ID"])
					&& !BX.util.in_array(data.params["ID"], arCommentID)
				)
				{
					arCommentsToShow[arCommentsToShow.length] = { commentID: data.params["ID"], postID: data.params["POST_ID"], ts: data.params["TS"] };
					arCommentID[arCommentID.length] = data.params["ID"];

						clearTimeout(showNewPullCommentTimeout);
					showNewPullCommentTimeout = setTimeout(function()
					{
						for (i = 0; i < arCommentsToShow.length; i++)
							drawPullComment(i);
					}, 500);
				}
			}
		});
		<?
	}
	?>
	</script><?
}

if(strlen($arResult["MESSAGE"])>0)
{
	?>
	<?
}
if(strlen($arResult["ERROR_MESSAGE"])>0)
{
	?>
	<?
}
if(strlen($arResult["FATAL_MESSAGE"])>0)
{
	?>
	<?
}
else
{
	if(!empty($arResult["CommentsResult"]) || $arResult["CanUserComment"])
	{
		$commentsCnt = count($arResult["CommentsResult"]);

		if(!empty($arResult["CommentsResult"]))
		{
			$i = 0;
			$moreCommentId = IntVal($_REQUEST["last_comment_id"]);

			if (
				$commentsCnt > $arResult["newCount"] 
				&& $arResult["newCount"] > 0 
				&& $moreCommentId <= 0
			)
				array_splice($arResult["CommentsResult"], 0, ($commentsCnt - $arResult["newCount"]));

			if($moreCommentId > 0)
			{
				array_splice($arResult["CommentsResult"], -$moreCommentId);
				$prev = IntVal(count($arResult["CommentsResult"]) - $arParams["PAGE_SIZE"]);
				if($prev <= 0)
					$prev = 0;

				?><script>
					BX("comcntshow").value = <?=IntVal($commentsCnt - $prev)?>;
				<?
				if($prev > 0)
				{
					?>
					BX("comcntleave-all").innerHTML = "<?=$prev?>";
					BX("comcntleave-old").innerHTML = "<?=$prev?>";
					<?
				}
				else
				{
					?>
					BX("blog-comment-more").style.display = "none";
					BX("comshowend").value = "Y";
					BX("comcntleave-old").innerHTML = "<?=$commentsCnt?>";
					BX("comcntleave-all").innerHTML = "<?=$commentsCnt?>";
					BX("comcntshow").value = 0;
					BX("blog-comment-more-old").style.display = "none";
					BX("blog-comment-more-all").style.display = "inline-block";
					<?
				}
				?>
				</script>
				<?
				if($prev > 0)
					array_splice($arResult["CommentsResult"], 0, $prev);
			}

			if ($arResult["is_ajax_post"] != "Y")
			{
				?><div class="post-comments-wrap" id="post-comments-wrap"><?
			}

			foreach($arResult["CommentsResult"] as $comment)
			{
				$i++;
				
				if(
					$moreCommentId <= 0
					&& $i == 1 
					&& $commentsCnt > $arResult["newCount"]
				)
				{
					$adit1 = " style=\"display:none;\"";
					$adit2 = "";
					if($commentsCnt > ($arResult["newCount"] + $arParams["PAGE_SIZE"]))
					{
						$adit1 = "";
						$adit2 = " style=\"display:none;\"";
					}
					?><div id="blog-comment-more" class="post-comments-button" onclick="showMoreComments('<?=intval($arResult["Post"]["ID"])?>', '<?=$comment["ID"]?>', this)" ontouchstart="BX.toggleClass(this, 'post-comments-button-press');" ontouchend="BX.toggleClass(this, 'post-comments-button-press');"><?
						?><span id="blog-comment-more-old"<?=$adit1?>><?=str_replace("#COMMENTS#", '<span id="comcntleave-old">'.$commentsCnt.'</span>', GetMessage("BLOG_C_BUTTON_OLD"))?></span><?
						?><span id="blog-comment-more-all"<?=$adit2?>><?=str_replace("#COMMENTS#", '<span id="comcntleave-all">'.$commentsCnt.'</span>', GetMessage("BLOG_C_BUTTON_ALL"))?></span><?
					?></div>
					<div id="blog-comment-hidden" style="display:none; overflow:hidden;"></div><?
					?><input type="hidden" name="comcntshow" id="comcntshow" value="<?=$arResult["newCount"]?>"><?
					?><input type="hidden" name="comshowend" id="comshowend" value="N"><?
				}

				if($comment["PUBLISH_STATUS"] != BLOG_PUBLISH_STATUS_PUBLISH)
					continue;

				$bUnread = (
					$comment["AUTHOR_ID"] != $GLOBALS["USER"]->GetID() 
					&& $comment["NEW"] == "Y"
				);

				?><div class="post-comment-block<?=($bUnread ? " post-comment-new" : "")?><?=(IntVal($_REQUEST["new_comment_id"]) > 0 ? " post-comment-new-transition-init" : "")?>" id="blg-comment-<?=$comment["ID"]?>"<?=($arResult["ajax_comment"] == $comment["ID"] ? ' data-send="Y"' : '')?>>
					<div class="avatar"<?=(array_key_exists("PERSONAL_PHOTO_RESIZED", $arResult["userCache"][$comment["AUTHOR_ID"]]) && strlen($arResult["userCache"][$comment["AUTHOR_ID"]]["PERSONAL_PHOTO_RESIZED"]["SRC"]) > 0 ? " style=\"background:url('".$arResult["userCache"][$comment["AUTHOR_ID"]]["PERSONAL_PHOTO_RESIZED"]["SRC"]."') no-repeat; background-size: 29px 29px;\"" : "")?>></div>
					<div class="post-comment-cont"><?
						$arTmpUser = array(
							"NAME" => $arResult["userCache"][$comment["AUTHOR_ID"]]["~NAME"],
							"LAST_NAME" => $arResult["userCache"][$comment["AUTHOR_ID"]]["~LAST_NAME"],
							"SECOND_NAME" => $arResult["userCache"][$comment["AUTHOR_ID"]]["~SECOND_NAME"],
							"LOGIN" => $arResult["userCache"][$comment["AUTHOR_ID"]]["~LOGIN"]
						);
						?><a href="<?=$arResult["userCache"][$comment["AUTHOR_ID"]]["url"]?>" class="post-comment-author"><?=CUser::FormatName($arParams["NAME_TEMPLATE"], $arTmpUser, ($arParams["SHOW_LOGIN"] != "N" ? true : false))?></a>
						<div class="post-comment-text"><?=$comment["TextFormated"]?></div><?

						if(!empty($arResult["arImages"][$comment["ID"]]))
						{
							?><div class="post-item-attached-img-wrap"><?
									foreach($arResult["arImages"][$comment["ID"]] as $val)
									{
										?><div class="post-item-attached-img-block" onclick="app.openNewPage('<?=$val["full"]?>'); event.stopPropagation();"><img class="post-item-attached-img" src="<?=$val["small"]?>" alt="" border="0"></div><?
									}
							?></div><?
						}

						if($comment["COMMENT_PROPERTIES"]["SHOW"] == "Y")
						{
							?><div class="post-item-attached-file-wrap"><?
							$eventHandlerID = false;
							$eventHandlerID = AddEventHandler('main', 'system.field.view.file', '__blogUFfileShowMobile');
							foreach ($comment["COMMENT_PROPERTIES"]["DATA"] as $FIELD_NAME => $arPostField)
							{
								if(!empty($arPostField["VALUE"]))
								{
									$APPLICATION->IncludeComponent(
										"bitrix:system.field.view", 
										$arPostField["USER_TYPE"]["USER_TYPE_ID"], 
										array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));
								}
							}
							if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
								RemoveEventHandler('main', 'system.field.view.file', $eventHandlerID);
							?></div><?
						}
						?><div class="post-comment-time"><?
							if (ConvertTimeStamp(MakeTimeStamp($comment["DATE_CREATE"]), "SHORT") == ConvertTimeStamp())
								echo ToLower($comment["DATE_CREATE_TIME"]);
							else
								echo ToLower($comment["DateFormated"]);
						?></div><?

						$strBottomBlock = "";
						ob_start();

						if (
							$arParams["SHOW_RATING"] == "Y"
						)
						{
							?><?$GLOBALS["APPLICATION"]->IncludeComponent(
								"bitrix:rating.vote", 
								"mobile_comment_".$arParams["RATING_TYPE"],
								Array(
									"ENTITY_TYPE_ID" => "BLOG_COMMENT",
									"ENTITY_ID" => $comment["ID"],
									"OWNER_ID" => $comment["AUTHOR_ID"],
									"USER_VOTE" => $arResult["RATING"][$comment["ID"]]["USER_VOTE"],
									"USER_HAS_VOTED" => $arResult["RATING"][$comment["ID"]]["USER_HAS_VOTED"],
									"TOTAL_VOTES" => $arResult["RATING"][$comment["ID"]]["TOTAL_VOTES"],
									"TOTAL_POSITIVE_VOTES" => $arResult["RATING"][$comment["ID"]]["TOTAL_POSITIVE_VOTES"],
									"TOTAL_NEGATIVE_VOTES" => $arResult["RATING"][$comment["ID"]]["TOTAL_NEGATIVE_VOTES"],
									"TOTAL_VALUE" => $arResult["RATING"][$comment["ID"]]["TOTAL_VALUE"],
									"PATH_TO_USER_PROFILE" => $arParams["~PATH_TO_USER"],
								),
								$arParams["component"],
								array("HIDE_ICONS" => "Y")
							);?><?
						}

						$strBottomBlock = ob_get_contents();
						ob_end_clean();

						if (strlen($strBottomBlock) > 0)
						{
							?><?=$strBottomBlock;?><?
						}

					?></div>
				</div><? // post-comment-block
			}

			if ($arResult["is_ajax_post"] != "Y")
			{
					?><span id="blog-comment-last-after"></span><?
				?></div><? // post-comments-wrap
			}
		}
		else
		{
			?><div class="post-comments-wrap" id="post-comments-wrap"><?
				?><span id="blog-comment-last-after"></span>
			</div><?
		}

	}
}

if ($arResult["is_ajax_post"] == "Y")
	die();
?>