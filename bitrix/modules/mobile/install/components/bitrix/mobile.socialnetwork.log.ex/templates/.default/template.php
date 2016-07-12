<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL("/bitrix/components/bitrix/mobile.socialnetwork.log.ex/templates/.default/script_attached.js").'"></script>', true);
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/components/bitrix/rating.vote/mobile_like/script_attached.js").'"></script>', true);
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/components/bitrix/rating.vote/mobile_comment_like/script_attached.js").'"></script>', true);

if (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	$event_cnt = 0;

	if (
		$arParams["LOG_ID"] <= 0
		&& !$arResult["AJAX_CALL"]
	)
	{
		?><script type="text/javascript">

		var arLogTs = {};
		var arLikeRandomID = {};

		<?
		if (!$arResult["AJAX_CALL"])
		{
			?>
			var LiveFeedID = parseInt(Math.random() * 100000);
			<?
		}

		if ($arParams["GROUP_ID"] > 0)
		{
			?>
			if (app.enableInVersion(3))
			{
				app.menuCreate({
					items: [
						{
							name: "<?=GetMessageJS("MOBILE_LOG_ADD_POST")?>",
							action: function(){
								if (BMAjaxWrapper.offline === true)
									BMAjaxWrapper.OfflineAlert();
								else
									app.showModalDialog({
										url: "/mobile/log/new_post.php?feed_id=" + LiveFeedID + "&group_id=<?=$arParams["GROUP_ID"]?>"
									});
							},
							arrowFlag: false,
							icon: "add"
						},
						{
							name: '<?php echo GetMessageJS('MB_TASKS_AT_SOCNET_LOG_CPT_MENU_ITEM_LIST'); ?>',
							icon: 'checkbox',
							arrowFlag: true,
							action: function() {
								var path = '<?php echo CUtil::JSEscape($arParams['PATH_TO_TASKS_SNM_ROUTER']); ?>';
								path = path
									.replace('#ROUTE_PAGE#', 'list')
									.replace('#USER_ID#',
										<?php echo (int) $GLOBALS['USER']->GetID(); ?>);

								app.openNewPage(path);
							}
						},
						{
							name: "<?=GetMessageJS("MOBILE_LOG_GROUP_FILES")?>",
							action: function(){
								app.openBXTable({
									url: "/mobile/webdav/group/<?=intval($arParams["GROUP_ID"])?>/",
									TABLE_SETTINGS : {
										cache : "NO",
										type : "files",
										useTagsInSearch : "NO"
									}
								});
							},
							arrowFlag: true,
							icon: "file"
						}
					]
				});

				app.addButtons({
					menuButton:{
						type: "context-menu",
						style: "custom",
						callback: function() { app.menuShow(); }
					}
				});
			}
			else
			{
				app.addButtons({
					addPostButton: {
						type: "plus",
						style: "custom",
						callback: function(){
							if (BMAjaxWrapper.offline === true)
								BMAjaxWrapper.OfflineAlert();
							else
								app.showModalDialog({
									url: "/mobile/log/new_post.php?feed_id=" + LiveFeedID + "&group_id=<?=$arParams["GROUP_ID"]?>"
								});
						}
					}
				});
			}
			<?
		}
		else
		{
			?>
			app.addButtons({
				addPostButton:{
					type: "plus",
					style:"custom",
					callback:function(){
						if (BMAjaxWrapper.offline === true)
							BMAjaxWrapper.OfflineAlert();
						else
							app.showModalDialog({
								url: "/mobile/log/new_post.php?feed_id=" + LiveFeedID
							});
					}
				}
			});
			<?
		}
		?>

		BX.addCustomEvent("onMPFSent", function(post_data) {

			if (post_data.LiveFeedID != LiveFeedID)
				return;

			window.scrollTo(0,0);

			__MSLPullDownInit(false);
			__MSLScrollInit(false);

			if (BX('blog-post-new-waiter'))
				BX('blog-post-new-waiter').style.display = 'block';

			BMAjaxWrapper.Wrap({
				'type': 'html',
				'method': 'POST',
				'url': '<?=$APPLICATION->GetCurPageParam("", array("LAST_LOG_TS", "AJAX_CALL"))?>',
				'data': post_data.data,
				'callback': function(post_response_data)
				{
					if (post_response_data != "*")
					{
//						app.onCustomEvent('onMPFSDeleteUnPosted');
//						app.onCustomEvent('onMPFSetUnPosted', post_data.data);
							
						if (BX('blog-post-new-waiter'))
							BX('blog-post-new-waiter').style.display = 'none';

						var new_post_id = 'new_post_ajax_' + Math.random();
						var new_post = BX.create('DIV', { props: { id: new_post_id }, html: post_response_data});
						BX('blog-post-first-after').parentNode.insertBefore(new_post, BX('blog-post-first-after').nextSibling);

						var ob = BX(new_post_id);
						var obNew = BX.processHTML(ob.innerHTML, true);
						var scripts = obNew.SCRIPT;
						BX.ajax.processScripts(scripts, true);
					}
					else
					{
						if (BX('blog-post-new-error'))
						{
//							app.onCustomEvent('onMPFSetUnPosted', post_data.data);
							BX('blog-post-new-error').style.display = 'block';
							BX.bind(BX('blog-post-new-error'), 'click', __MSLOnErrorClick);
						}
					}
					if (BX('blog-post-new-waiter'))
						BX('blog-post-new-waiter').style.display = 'none';
					__MSLPullDownInit(true);
					__MSLScrollInit(true);
				},
				'callback_failure': function() {
//					app.onCustomEvent('onMPFSetUnPosted', post_data.data);				
					if (BX('blog-post-new-waiter'))
						BX('blog-post-new-waiter').style.display = 'none';
					__MSLPullDownInit(true);
					__MSLScrollInit(true);
				}
			});
		});

		BX.addCustomEvent("onStreamRefresh", function(data) {
			document.location.reload();
		});

		BX.addCustomEvent("onLogEntryRead", function(data) {
			__MSLLogEntryRead(data.log_id, data.ts, (data.bPull === true || data.bPull === 'YES' ? true : false));
		});

		BX.addCustomEvent("onLogEntryCommentAdd", function(data) {
			__MSLLogEntryCommentAdd(data.log_id);
		});

		BX.addCustomEvent("onLogEntryRatingLike", function(data) {
			__MSLLogEntryRatingLike(data.rating_id, data.voteAction);
		});

		BX.addCustomEvent("onLogEntryFollow", function(data) {
			__MSLLogEntryFollow(data.log_id);
		});

		BX.message({
			MSLNextPostMoreTitle: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_NEXT_POST_MORE"))?>',
			MSLPullDownText1: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_NEW_PULL"))?>',
			MSLPullDownText2: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_NEW_PULL_RELEASE"))?>',
			MSLPullDownText3: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_NEW_PULL_LOADING"))?>',
			MSLLogCounter1: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_COUNTER_1"))?>',
			MSLLogCounter2: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_COUNTER_2"))?>',
			MSLLogCounter3: '<?=CUtil::JSEscape(GetMessage("MOBILE_LOG_COUNTER_3"))?>'
			<?
			if ($arParams["USE_FOLLOW"] == "Y"):
				?>
				, MSLFollowY: '<?=GetMessageJS("MOBILE_LOG_FOLLOW_Y")?>'
				, MSLFollowN: '<?=GetMessageJS("MOBILE_LOG_FOLLOW_N")?>'
				<?
			endif;
			?>
		});
		</script>
		<div class="lenta-notifier" id="lenta_notifier" onclick="if (!BMAjaxWrapper.offline) { app.BasicAuth({'success': function() { document.location.reload(); }, 'failture': function() { } }); return false; }"><span class="lenta-notifier-arrow"></span><span class="lenta-notifier-text"><span id="lenta_notifier_cnt"></span>&nbsp;<span id="lenta_notifier_cnt_title"></span></span></div><?
	}
	elseif ($arParams["LOG_ID"] > 0)
	{
		?><div style="display: none;" id="comment_send_button_waiter" class="send-message-button-waiter"></div>
		<script type="text/javascript">
			app.onCustomEvent('onLogEntryRead', { log_id: <?=$arParams["LOG_ID"]?>, ts: <?=time()?>, bPull: false });

			if (
				window.platform != "android"
				&& !app.enableInVersion(4)
			)
				app.enableScroll(false);

			BX.message({
				MSLSessid: '<?=bitrix_sessid()?>',
				MSLSiteId: '<?=CUtil::JSEscape(SITE_ID)?>',
				MSLLangId: '<?=CUtil::JSEscape(LANGUAGE_ID)?>',
				MSLLogId: <?=intval($arParams["LOG_ID"])?>,
				MSLPathToUser: '<?=CUtil::JSEscape($arParams["PATH_TO_USER"])?>',
				MSLPathToGroup: '<?=CUtil::JSEscape($arParams["PATH_TO_GROUP"])?>',
				MSLDestinationLimit: '<?=intval($arParams["DESTINATION_LIMIT_SHOW"])?>',
				MSLNameTemplate: '<?=CUtil::JSEscape($arParams["NAME_TEMPLATE"])?>',
				MSLShowLogin: '<?=CUtil::JSEscape($arParams["SHOW_LOGIN"])?>',
				MSLDestinationHidden1: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_1")?>',
				MSLDestinationHidden2: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_2")?>',
				MSLDestinationHidden3: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_3")?>',
				MSLDestinationHidden4: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_4")?>',
				MSLDestinationHidden5: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_5")?>',
				MSLDestinationHidden6: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_6")?>',
				MSLDestinationHidden7: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_7")?>',
				MSLDestinationHidden8: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_8")?>',
				MSLDestinationHidden9: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_9")?>',
				MSLDestinationHidden0: '<?=GetMessageJS("MOBILE_LOG_DESTINATION_HIDDEN_0")?>'
				<?
				if ($arParams["USE_FOLLOW"] == "Y"):
					?>
					, MSLFollowY: '<?=GetMessageJS("MOBILE_LOG_FOLLOW_Y")?>'
					, MSLFollowN: '<?=GetMessageJS("MOBILE_LOG_FOLLOW_N")?>'
					<?
				endif;
				?>
			});
		</script><?
	}

	if (!$arResult["AJAX_CALL"])
	{
		?><script type="text/javascript">
			var arBlockToCheck = [];
		</script><?
	}

	?><div class="lenta-wrapper" id="lenta_wrapper"><?
		?><div class="lenta-item post-without-informers new-post-message" id="blog-post-new-waiter" style="display: none;"><?
			?><div class="post-item-top-wrap"><?
				?><div class="new-post-waiter"></div><?
			?></div><?
		?></div><?
		?><div class="lenta-item post-without-informers new-post-message" id="blog-post-new-error" style="display: none;"><?
			?><div class="post-item-top-wrap"><div class="post-item-post-block"><div class="post-item-text" style="text-align: center;"><?=GetMessage("MOBILE_LOG_NEW_ERROR")?></div></div></div><?
		?></div><?
		?><span id="blog-post-first-after"></span><?

	if(strlen($arResult["ErrorMessage"])>0)
	{
		?><span class='errortext'><?=$arResult["ErrorMessage"]?></span><br /><br /><?
	}

	if($arResult["AJAX_CALL"])
	{
		$GLOBALS["APPLICATION"]->RestartBuffer();

		?><script type="text/javascript">
			arBlockToCheck = []; // empty array to check height
		</script><?
	}

	if (
		$arResult["Events"]
		&& is_array($arResult["Events"])
		&& count($arResult["Events"]) > 0
	)
	{
		?><script type="text/javascript">
			if (BX("lenta_block_empty", true))
				BX("lenta_block_empty", true).style.display = "none";
		</script><?

		foreach ($arResult["Events"] as $arEvent)
		{
			$event_cnt++;
			$ind = RandString(8);

			$bUnread = (
				$arParams["SET_LOG_COUNTER"] == "Y"
				&& $arResult["COUNTER_TYPE"] == "**"
				&& $arEvent["USER_ID"] != $GLOBALS["USER"]->GetID()
				&& intval($arResult["LAST_LOG_TS"]) > 0
				&& (MakeTimeStamp($arEvent["LOG_DATE"]) - intval($arResult["TZ_OFFSET"])) > $arResult["LAST_LOG_TS"]
			);

			if(in_array($arEvent["EVENT_ID"], array("blog_post", "blog_post_important", "blog_post_micro", "blog_comment", "blog_comment_micro")))
			{
				$arComponentParams = array(
					"PATH_TO_BLOG" => $arParams["PATH_TO_USER_BLOG"],
					"PATH_TO_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
					"PATH_TO_BLOG_CATEGORY" => $arParams["PATH_TO_USER_BLOG_CATEGORY"],
					"PATH_TO_POST_EDIT" => $arParams["PATH_TO_USER_BLOG_POST_EDIT"],
					"PATH_TO_USER" => $arParams["PATH_TO_USER"],
					"PATH_TO_GROUP" => $arParams["PATH_TO_GROUP"],
					"PATH_TO_SMILE" => $arParams["PATH_TO_BLOG_SMILE"],
					"PATH_TO_MESSAGES_CHAT" => $arResult["PATH_TO_MESSAGES_CHAT"],
					"PATH_TO_LOG_ENTRY" => $arParams["PATH_TO_LOG_ENTRY"],
					"SET_NAV_CHAIN" => "N",
					"SET_TITLE" => "N",
					"POST_PROPERTY" => $arParams["POST_PROPERTY"],
					"DATE_TIME_FORMAT" => $arParams["DATE_TIME_FORMAT"],
					"DATE_TIME_FORMAT_FROM_LOG" => $arParams["DATE_TIME_FORMAT"],
					"LOG_ID" => $arEvent["ID"],
					"USER_ID" => $arEvent["USER_ID"],
					"ENTITY_TYPE" => $arEvent["ENTITY_TYPE"],
					"ENTITY_ID" => $arEvent["ENTITY_ID"],
					"EVENT_ID" => $arEvent["EVENT_ID"],
					"EVENT_ID_FULLSET" => $arEvent["EVENT_ID_FULLSET"],
					"IND" => $ind,
					"SONET_GROUP_ID" => $arParams["GROUP_ID"],
					"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"],
					"SHOW_LOGIN" => $arParams["SHOW_LOGIN"],
					"SHOW_YEAR" => $arParams["SHOW_YEAR"],
					"PATH_TO_CONPANY_DEPARTMENT" => $arParams["PATH_TO_CONPANY_DEPARTMENT"],
					"PATH_TO_VIDEO_CALL" => $arParams["PATH_TO_VIDEO_CALL"],
					"USE_SHARE" => $arParams["USE_SHARE"],
					"SHARE_HIDE" => $arParams["SHARE_HIDE"],
					"SHARE_TEMPLATE" => $arParams["SHARE_TEMPLATE"],
					"SHARE_HANDLERS" => $arParams["SHARE_HANDLERS"],
					"SHARE_SHORTEN_URL_LOGIN" => $arParams["SHARE_SHORTEN_URL_LOGIN"],
					"SHARE_SHORTEN_URL_KEY" => $arParams["SHARE_SHORTEN_URL_KEY"],
					"SHOW_RATING" => $arParams["SHOW_RATING"],
					"RATING_TYPE" => $arParams["RATING_TYPE"],
					"IMAGE_MAX_WIDTH" => $arParams["IMAGE_MAX_WIDTH"],
					"IMAGE_MAX_HEIGHT" => $arParams["IMAGE_MAX_HEIGHT"],
					"ALLOW_POST_CODE" => $arParams["ALLOW_POST_CODE"],
					"ID" => $arEvent["SOURCE_ID"],
					"FROM_LOG" => "Y",
					"ADIT_MENU" => $arAditMenu,
					"IS_LIST" => (intval($arParams["LOG_ID"]) <= 0),
					"IS_UNREAD" => $bUnread,
					"IS_HIDDEN" => false,
					"LAST_LOG_TS" => ($arResult["LAST_LOG_TS"] + $arResult["TZ_OFFSET"]),
					"CACHE_TIME" => $arParams["CACHE_TIME"],
					"CACHE_TYPE" => $arParams["CACHE_TYPE"],
					"ALLOW_VIDEO"  => $arParams["BLOG_COMMENT_ALLOW_VIDEO"],
					"ALLOW_IMAGE_UPLOAD" => $arParams["BLOG_COMMENT_ALLOW_IMAGE_UPLOAD"],
					"USE_CUT" => $arParams["BLOG_USE_CUT"],
					"MOBILE" => "Y",
					"ATTACHED_IMAGE_MAX_WIDTH_FULL" => 640,
					"ATTACHED_IMAGE_MAX_HEIGHT_FULL" => 832,
					"RETURN_DATA" => ($arParams["LOG_ID"] > 0 ? "Y" : "N"),
					"AVATAR_SIZE_COMMENT" => $arParams["AVATAR_SIZE_COMMENT"],
					"CHECK_PERMISSIONS_DEST" => $arParams["CHECK_PERMISSIONS_DEST"],
					"COMMENTS_COUNT" => $arEvent["COMMENTS_COUNT"],
				);

				if ($arParams["USE_FOLLOW"] == "Y")
				{
					$arComponentParams["FOLLOW"] = $arEvent["FOLLOW"];
					$arComponentParams["FOLLOW_DEFAULT"] = $arResult["FOLLOW_DEFAULT"];
				}

				if (
					strlen($arEvent["RATING_TYPE_ID"])>0
					&& $arEvent["RATING_ENTITY_ID"] > 0
					&& $arParams["SHOW_RATING"] == "Y"
				)
					$arComponentParams["RATING_ENTITY_ID"] = $arEvent["RATING_ENTITY_ID"];

				$APPLICATION->IncludeComponent(
					"bitrix:socialnetwork.blog.post",
					"mobile",
					$arComponentParams,
					$component,
					Array("HIDE_ICONS" => "Y")
				);
			}
			else
			{
				$arComponentParams = array_merge($arParams, array(
						"LOG_ID" => $arEvent["ID"],
						"IS_LIST" => (intval($arParams["LOG_ID"]) <= 0),
						"LAST_LOG_TS" => $arResult["LAST_LOG_TS"],
						"COUNTER_TYPE" => $arResult["COUNTER_TYPE"],
						"AJAX_CALL" => $arResult["AJAX_CALL"],
						"bReload" => $arResult["bReload"],
						"IND" => $ind,
						"CURRENT_PAGE_DATE" => $arResult["CURRENT_PAGE_DATE"],
						"EVENT" => array(
							"IS_UNREAD" => $bUnread,
							"LOG_DATE" => $arEvent["LOG_DATE"],
							"COMMENTS_COUNT" => $arEvent["COMMENTS_COUNT"],
						)
					)
				);

				if ($GLOBALS["USER"]->IsAuthorized())
				{
					if ($arParams["USE_FOLLOW"] == "Y")
					{
						$arComponentParams["EVENT"]["FOLLOW"] = $arEvent["FOLLOW"];
						$arComponentParams["EVENT"]["DATE_FOLLOW"] = $arEvent["DATE_FOLLOW"];
						$arComponentParams["EVENT"]["FOLLOW_DEFAULT"] = $arResult["FOLLOW_DEFAULT"];
					}

					$arComponentParams["EVENT"]["FAVORITES"] = (
						array_key_exists("FAVORITES_USER_ID", $arEvent) 
						&& intval($arEvent["FAVORITES_USER_ID"]) > 0 
							? "Y" 
							: "N"
					);
				}

				if (
					strlen($arEvent["RATING_TYPE_ID"])>0
					&& $arEvent["RATING_ENTITY_ID"] > 0
					&& $arParams["SHOW_RATING"] == "Y"
				)
				{
					$arComponentParams["RATING_TYPE"] = $arParams["RATING_TYPE"];
					$arComponentParams["EVENT"]["RATING_TYPE_ID"] = $arEvent["RATING_TYPE_ID"];
					$arComponentParams["EVENT"]["RATING_ENTITY_ID"] = $arEvent["RATING_ENTITY_ID"];
				}

				$APPLICATION->IncludeComponent(
					"bitrix:mobile.socialnetwork.log.entry",
					"",
					$arComponentParams,
					$component,
					Array("HIDE_ICONS" => "Y")
				);
			}

			
		} // foreach ($arResult["Events"] as $arEvent)
	} // if ($arResult["Events"] && is_array($arResult["Events"]) && count($arResult["Events"]) > 0)
	elseif
	(
		$arParams["LOG_ID"] <= 0
		&& !$arResult["AJAX_CALL"]
	)
	{
		?><div class="lenta-block-empty" id="lenta_block_empty"><?=GetMessage("MOBILE_LOG_MESSAGE_EMPTY");?></div><?
	}

	if($arResult["AJAX_CALL"])
	{
		$strParams = "LAST_LOG_TS=".$arResult["LAST_LOG_TS"]."&AJAX_CALL=Y&PAGEN_".$arResult["PAGE_NAVNUM"]."=".($arResult["PAGE_NUMBER"] + 1);

		?><script type="text/javascript">
			<?
			if (
				$event_cnt > 0
				&& $event_cnt >= $arParams["PAGE_SIZE"]
			)
			{
				?>
				url_next = '<?=$APPLICATION->GetCurPageParam($strParams, array("LAST_LOG_TS", "AJAX_CALL", "PAGEN_".$arResult["PAGE_NAVNUM"]));?>';
				<?
			}
			else
			{
				?>
				__MSLScrollInit(false, true);
				<?
				if ($arParams["NEW_LOG_ID"] > 0)
				{
					?>
					setTimeout(function() { __MSLCheckNodesHeight(); }, 1000);
					<?
				}
			}
			?>
		</script><?
		die();
	}

	if ($arParams["LOG_ID"] <= 0)
	{
		if ($event_cnt >= $arParams["PAGE_SIZE"])
		{
			?><div id="next_post_more" class="next-post-more"></div><?
		}
		?></div><? // lenta-wrapper
	}

	$strParams = "LAST_LOG_TS=".$arResult["LAST_LOG_TS"]."&AJAX_CALL=Y&PAGEN_".$arResult["PAGE_NAVNUM"]."=".($arResult["PAGE_NUMBER"] + 1);
	if (
		is_array($arResult["arLogTmpID"]) 
		&& count($arResult["arLogTmpID"]) > 0
	)
		$strParams .= "&pplogid=".implode("|", $arResult["arLogTmpID"]);

	// sonet_log_content
	?><script type="text/javascript">
		var maxScroll = 0;
		var isPullDownEnabled = false;
		var url_next = '<?=$APPLICATION->GetCurPageParam($strParams, array("LAST_LOG_TS", "AJAX_CALL", "PAGEN_".$arResult["PAGE_NAVNUM"], "pplogid"));?>';
		BX.ready(function() {
			app.pullDownLoadingStop();

			<?if (!$arResult["AJAX_CALL"]):?>
				<?if($arParams["LOG_ID"] <= 0):?>
					window.addEventListener("scroll", __MSLShowLentaImages, false);
				<?else:?>
					var postCardWrap = (!app.enableInVersion(4) && window.platform != "android" ? BX("post-card-wrap", true) : window);
					if (postCardWrap)
					{
						postCardWrap.addEventListener("scroll", function() { __MSLShowPostImages(<?=$arParams["LOG_ID"]?>); }, false);
						postCardWrap.addEventListener("scroll", __MSLShowCommentsImages, false);
					}
				<?endif?>
			<?endif?>

			<?if($arParams["LOG_ID"] <= 0):?>
				__MSLShowLentaImages();
				<?else:?>
				__MSLShowPostImages(<?=$arParams["LOG_ID"]?>);
				__MSLShowCommentsImages();
			<?endif?>

			<?
			if (
				$arParams["LOG_ID"] <= 0
				&& !$arResult["AJAX_CALL"]
			)
			{
				?>
				var windowSize = BX.GetWindowSize();
				maxScroll = windowSize.scrollHeight - windowSize.innerHeight - 190;
				__MSLScrollInit(true);

				BX.bind(document, "offline", function(){
					app.pullDownLoadingStop();
					__MSLScrollInit(false, true);
				});

				BX.bind(document, "online", function(){
					__MSLPullDownInit(true);
					__MSLScrollInit(true, true);
				});

				BX.addCustomEvent("UIApplicationDidBecomeActiveNotification", function(params) {
					var networkState = navigator.network.connection.type;

					if (networkState == Connection.UNKNOWN || networkState == Connection.NONE)
					{
						app.pullDownLoadingStop();
						__MSLScrollInit(false, true);
					}
					else
					{
						__MSLPullDownInit(true);
						__MSLScrollInit(true, true);
					}
				});

				BX.addCustomEvent("onUpdateSocnetCounters", function(params) {
					if (parseInt(params["<?=$arResult["COUNTER_TYPE"]?>"]) > 0)
						__MSLShowNotifier(params["<?=$arResult["COUNTER_TYPE"]?>"]);
					else
						__MSLHideNotifier();
				});
				<?
			}
			?>
			setTimeout(function() { __MSLCheckNodesHeight(); }, 1000);
			<?
			if ($arParams["LOG_ID"] <= 0)
			{
				?>
				__MSLPullDownInit(true);
				<?
			}
			?>
		});
		<?
		if (
			$arParams["LOG_ID"] > 0
			&& $_REQUEST["BOTTOM"] == "Y"
		)
		{
			?>
			__MSLDetailMoveBottom();
			<?
		}
	?>
	</script>
	<?
}
?>