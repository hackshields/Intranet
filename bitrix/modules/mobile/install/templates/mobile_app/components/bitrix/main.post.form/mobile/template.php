<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->SetPageProperty("BodyClass", "newpost-page");
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/components/bitrix/main.post.form/mobile/script_attached.js").'"></script>', true);

$bFilesUploaded = (is_array($_SESSION["MFU_UPLOADED_FILES"]) && count($_SESSION["MFU_UPLOADED_FILES"]) > 0);
?>
	<form action="<?=$arParams["FORM_ACTION_URL"]?>" id="<?=$arParams["FORM_ID"]?>" name="<?=$arParams["FORM_ID"]?>" method="POST" enctype="multipart/form-data"<?if(strlen($arParams["FORM_TARGET"]) > 0) echo " target=\"".$arParams["FORM_TARGET"]."\""?>>
		<input type="hidden" id="<?=$arParams["FORM_ID"]?>_is_sent" name="is_sent" value="Y" />
		<?
		if(!empty($arParams["HIDDENS"]))
		{
			foreach($arParams["HIDDENS"] as $val)
			{
				?><input type="hidden" name="<?=$val["NAME"]?>" id="<?=$val["ID"]?>" value="<?=$val["VALUE"]?>" /><?
			}
		}
		?>
		<?=bitrix_sessid_post();?>
	<textarea name="POST_MESSAGE" class="newpost-textarea" id="POST_MESSAGE" cols="30" rows="10" placeholder="<?=GetMessage("MFP_MENTION_TEXTAREA_TITLE")?>"></textarea>
	<input type="hidden" name="newpost_photo_counter" id="newpost_photo_counter" value="<?
		echo ($bFilesUploaded ? count($_SESSION["MFU_UPLOADED_FILES"]) : 0);
	?>" />
	<div class="newpost-controls"><?
	?><div class="newpost-button newpost-button-destination" ontouchstart="BX.toggleClass(this, 'newpost-button-press');" ontouchend="BX.toggleClass(this, 'newpost-button-press');" id="feed-add-post-destination-container"></div>
	<div class="newpost-button newpost-button-mention" ontouchstart="BX.toggleClass(this, 'newpost-button-press');" ontouchend="BX.toggleClass(this, 'newpost-button-press');" id="feed-add-post-mention"><?=GetMessage("MFP_MENTION_BUTTON_TITLE")?></div>
	<div class="newpost-button newpost-button-file">
		<div id="feed-add-post-image-camera" class="newpost-but-file newpost-but-file-image" ontouchstart="BX.toggleClass(this, 'newpost-but-file-press-image');" ontouchend="BX.toggleClass(this, 'newpost-but-file-press-image');"></div><div id="feed-add-post-image-gallery" class="newpost-but-file newpost-but-file-file" ontouchstart="BX.toggleClass(this, 'newpost-but-file-press-file');" ontouchend="BX.toggleClass(this, 'newpost-but-file-press-file');"></div>
	</div>
	<script type="text/javascript">
		BX.bind(BX('feed-add-post-destination-container'), 'click', function(e)
		{
			var destinations = BX.findChildren(BX('feed-add-post-destination-container'), {'tag': 'input', 'attr': {'type': 'hidden'} }, true);
			var destination_name = false;
			var destination_value = false;
			var arSelectedDestinations = { a_users: [], b_groups: [] };

			if (destinations != null)
			{
				for (var j = 0; j < destinations.length; j++)
				{
					destination_name = destinations[j].name;
					destination_value = destinations[j].value;

					if (destination_value == 'UA')
						arSelectedDestinations.a_users[arSelectedDestinations.a_users.length] = 0;
					else if (destination_name.substr(6,1) == 'U')
						arSelectedDestinations.a_users[arSelectedDestinations.a_users.length] = parseInt(destination_value.substr(1));
					else if (destination_name.substr(6,2) == 'SG')
						arSelectedDestinations.b_groups[arSelectedDestinations.b_groups.length] = parseInt(destination_value.substr(2));
				}
			}

			app.openTable({
				callback: __MPFonAfterSelectDestinations,
				url: '/mobile/index.php?mobile_action=get_usergroup_list',
				markmode: true,
				multiple: true,
				return_full_mode: true,
				user_all: true,
				showtitle: true,
				modal: true,
				selected: arSelectedDestinations,
				alphabet_index: true,
				okname: '<?=CUtil::JSEscape(GetMessage("MPF_TABLE_OK"))?>',
				cancelname: '<?=CUtil::JSEscape(GetMessage("MPF_TABLE_CANCEL"))?>'
			});

			BX.PreventDefault(e)
		});
		BX.bind(BX('feed-add-post-mention'), 'click', function(e)
		{
			app.openTable({
				callback: __MPFonAfterSelectMentions,
				url: '/mobile/index.php?mobile_action=get_user_list',
				markmode: true,
				multiple: false,
				return_full_mode: true,
				modal: true,
				alphabet_index: true,
				outsection: false,
				okname: '<?=CUtil::JSEscape(GetMessage("MPF_TABLE_OK"))?>',
				cancelname: '<?=CUtil::JSEscape(GetMessage("MPF_TABLE_CANCEL"))?>'
			});
			BX.PreventDefault(e)
		});
		BX.bind(BX('feed-add-post-image-camera'), 'click', function()
		{
			app.takePhoto({
				source:1,
				correctOrientation: true,
				targetWidth: 1000,
				targetHeight: 1000,
				callback: function(fileURI)
				{
					var loading_id = __MFUProgressBarShow();

					function win(r)
					{
						if (decodeURIComponent(r.response) == '{"status":"failed"}')
							fail_1try();
						else
						{
							__MFUProgressBarHide(loading_id); 
							__MFUCallback({ 'fileID': r.response }, loading_id);
						}
					}
					
					function fail_1try(error)
					{
						app.BasicAuth({
							'success': function(auth_data) 
							{

								uri = '<?=CUtil::JSEscape((CMain::IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam("", array("bxajaxid", "logout")))?>';
								uri += ((uri.indexOf("?") > 0) ? "&" : "?") + 'sessid=' + auth_data.sessid_md5;

								ft.upload(fileURI, uri, win, fail_2try, options);
							},
							'failture': function() { __MFUProgressBarHide(loading_id); }
						});
					}					

					function fail_2try(error) { __MFUProgressBarHide(loading_id); }

					var options = new FileUploadOptions();
					options.fileKey = "file";
					options.fileName = fileURI.substr(fileURI.lastIndexOf('/') + 1);
					options.mimeType = "image/jpeg";
					var params = {};
					options.params = params;
					options.chunkedMode = false;

					var ft = new FileTransfer();
					var uri = '<?=CUtil::JSEscape((CMain::IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam(bitrix_sessid_get(), array("bxajaxid", "logout")))?>';

					ft.upload(fileURI, uri, win, fail_1try, options);
				}
			});
		});

		BX.bind(BX('feed-add-post-image-gallery'), 'click', function()
		{
			app.takePhoto({
				targetWidth: 1000,
				targetHeight: 1000,
				callback: function(fileURI)
				{
					var loading_id = __MFUProgressBarShow();

					function win(r)
					{
						if (decodeURIComponent(r.response) == '{"status":"failed"}')
							fail_1try();
						else
						{
							__MFUProgressBarHide(loading_id); 
							__MFUCallback({ 'fileID': r.response }, loading_id);
						}
					}
					
					function fail_1try(error)
					{
						app.BasicAuth({
							'success': function(auth_data) 
							{

								uri = '<?=CUtil::JSEscape((CMain::IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam("", array("bxajaxid", "logout")))?>';
								uri += ((uri.indexOf("?") > 0) ? "&" : "?") + 'sessid=' + auth_data.sessid_md5;

								ft.upload(fileURI, uri, win, fail_2try, options);
							},
							'failture': function() { __MFUProgressBarHide(loading_id); }
						});
					}					

					function fail_2try(error) { __MFUProgressBarHide(loading_id); }

					var options = new FileUploadOptions();
					options.fileKey = "file";

					options.fileName = fileURI.substr(fileURI.lastIndexOf('/') + 1);
					if (options.fileName.indexOf('?') > 0)
						options.fileName = options.fileName.substr(0, options.fileName.indexOf('?'));

					options.mimeType = "image/jpeg";
					var params = {};
					options.params = params;
					options.chunkedMode = false;

					var ft = new FileTransfer();
					var uri = '<?=CUtil::JSEscape((CMain::IsHTTPS() ? "https" : "http")."://".$_SERVER["HTTP_HOST"].$APPLICATION->GetCurPageParam(bitrix_sessid_get(), array("bxajaxid", "logout")))?>';

					ft.upload(fileURI, uri, win, fail_1try, options);
				}
			});
		});

		BX.addCustomEvent('onAfterMFLDeleteFile', __MPFonAfterMFLDeleteFile);
	</script><?
	?></div><?
	?><div class="newpost-panel" id="newpost-panel"><div class="newpost-keyboard newpost-grey-button" id="newpost-keyboard"></div><?
	?><div id="newpost_progressbar_cont" class="newpost-progress" style="display: none;"><?
		?><div id="newpost_progressbar_label" class="newpost-progress-label"></div><?
		?><div id="newpost_progressbar_ind" class="newpost-progress-indicator"></div><?
	?></div><?
	?><div onclick="app.openNewPage('/mobile/log/new_post_images.php');" style="display: <?=($bFilesUploaded ? "block" : "none")?>;" class="newpost-info newpost-grey-button" id="newpost_photo_counter_title" ontouchstart="BX.toggleClass(this, 'newpost-info-pressed');" ontouchend="BX.toggleClass(this, 'newpost-info-pressed');"><span><?=($bFilesUploaded ? count($_SESSION["MFU_UPLOADED_FILES"]) : 0)?></span><span>&nbsp;<?=GetMessage("MPF_PHOTO")?></span></div><?

	if($arParams["USER_FIELDS"]["SHOW"] == "Y")
	{
		$eventHandlerID = false;
		include_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/templates/mobile_app/components/bitrix/main.post.form/mobile/result_modifier.php");
		$eventHandlerID = AddEventHandler('main', 'system.field.edit.file', '__blogUFfileEditMobile');

		foreach($arParams["USER_FIELDS"]["VALUE"] as $FIELD_NAME => $arPostField)
		{
			if ($arPostField["USER_TYPE"]["USER_TYPE_ID"] != "file")
				continue;

			$APPLICATION->IncludeComponent(
					"bitrix:system.field.edit",
					$arPostField["USER_TYPE"]["USER_TYPE_ID"],
					array("arUserField" => $arPostField), null, array("HIDE_ICONS"=>"Y"));
		}

		if ($eventHandlerID !== false && ( intval($eventHandlerID) > 0 ))
			RemoveEventHandler('main', 'system.field.edit.file', $eventHandlerID);
	}

	?>
	</div>
	</form>
	<script type="text/javascript">

	var LiveFeedID = null;
	var LoadingFilesStack = [];
	var progressbar_id = null;
	var progressbar_state = 0;

	BX.ready(

		function()
		{
			LiveFeedID = '<?=CUtil::JSEscape($_REQUEST["feed_id"])?>';

/*
			var obPostUnsent = {
					result: null
				};

			app.onCustomEvent('onMPFGetUnPosted', obPostUnsent);
			
			if (obPostUnsent.result != null)
			{
				var arItemsSelected = [];

				if (obPostUnsent.result.SPERM[UA] != null && obPostUnsent.result.SPERM[UA] != undefined)
				{
					arItemsSelected[arItemsSelected.length] = {
						type: 'groups',
						item: {
							id: 'UA',
							name: '<?=GetMessageJS("MFP_DEST_UA")?>'
						}
					};
				}

				if (obPostUnsent.result.SPERM[U] != null && obPostUnsent.result.SPERM[U] != undefined)
				{
					for (var i = 0; i < obPostUnsent.result.SPERM[U].length; i++)				
					{
						arItemsSelected[arItemsSelected.length] = {
							type: 'users',
							item: {
								id: obPostUnsent.result.SPERM[U][i],
								name: (obPostUnsent.result.SPERM_NAME[U][i] != null && obPostUnsent.result.SPERM_NAME[U][i] != undefined 
									? obPostUnsent.result.SPERM_NAME[U][i]
									: ''
								)
							}
						};
					}
				}

				if (obPostUnsent.result.SPERM[SG] != null && obPostUnsent.result.SPERM[SG] != undefined)
				{
					for (var i = 0; i < obPostUnsent.result.SPERM[SG].length; i++)
					{
						arItemsSelected[arItemsSelected.length] = {
							type: 'sonetgroups',
							item: {
								id: obPostUnsent.result.SPERM[U][i],
								name: (obPostUnsent.result.SPERM_NAME[SG][i] != null && obPostUnsent.result.SPERM_NAME[SG][i] != undefined 
									? obPostUnsent.result.SPERM_NAME[SG][i]
									: ''
								)
							}
						};
					}
				}

				__MPFDestinationInitEx(arItemsSelected);
				
				if (obPostUnsent.result.POST_MESSAGE != null && obPostUnsent.result.POST_MESSAGE != undefined)
					BX('POST_MESSAGE').value = obPostUnsent.result.POST_MESSAGE;
				
			}
*/
			<?
			if (intval($arParams["SOCNET_GROUP_ID"]) > 0)
			{
				?>
//				if (arItemsSelected === undefined || arItemsSelected.length <= 0)
					__MPFDestinationInit({ id: 'SG<?=$arParams["SOCNET_GROUP_ID"]?>', name: '<?=CUtil::JSEscape($arResult["SONET_GROUP_NAME"])?>' }, 'sonetgroups');
				<?
			}
			else
			{
				?>
//				if (arItemsSelected === undefined || arItemsSelected.length <= 0)
					__MPFDestinationInit({ id: 'UA', name: '<?=CUtil::JSEscape(GetMessage("MFP_DEST_UA"))?>' }, 'groups');<?
			}
			?>
			app.addButtons({
				sendButton: {
					type: "right_text",
					name: '<?=CUtil::JSEscape(GetMessage("MPF_SEND"))?>',
					position:"right",
					style: "custom",
					callback: function(){

						var data = {
							'ACTION': 'ADD_POST',
							'AJAX_CALL': 'Y',
							'PUBLISH_STATUS': 'P',
							'is_sent': 'Y',
							'apply': 'Y',
							'sessid': '<?=bitrix_sessid()?>',
							'POST_MESSAGE': BX('POST_MESSAGE').value,
							'newpost_photo_counter': BX('newpost_photo_counter').value,
							'decode': 'Y'
						};

						var varName = '';

						var arSPermInput = BX.findChildren(BX('feed-add-post-destination-container'), {'tag': 'input', 'attr': {'type': 'hidden'} }, true);
						if (arSPermInput != null)
						{
							for (var i = 0; i < arSPermInput.length; i++)
							{
								varName = arSPermInput[i].name.replace(/[\[\]]{2}$/g,"");
								if (data[varName] == 'undefined' || data[varName] == null)
									data[varName] = [];
								data[varName][data[varName].length] = arSPermInput[i].value;
							}
						}

						var arAttachedFile = BX.findChildren(BX('mfu_file_container'), {'tag': 'input', 'attr': {'type': 'hidden'} }, true);
						if (arAttachedFile != null)
						{
							for (var j = 0; j < arAttachedFile.length; j++)
							{
								varName = arAttachedFile[j].name.replace(/[\[\]]{2}$/g,"");
								if (data[varName] == 'undefined' || data[varName] == null)
									data[varName] = [];
								data[varName][data[varName].length] = arAttachedFile[j].value;
							}
						}

						if (BX('POST_MESSAGE').value.length > 0)
						{
							if (BMAjaxWrapper.offline === true)
								BMAjaxWrapper.OfflineAlert();
							else
							{
								app.onCustomEvent('onMPFSent', { 'data': data, 'LiveFeedID': LiveFeedID } );
								app.closeModalDialog({});
							}
						}
					}
				},
				cancelButton: {
					type: "right_text",
					name: '<?=CUtil::JSEscape(GetMessage("MPF_CANCEL"))?>',
					position:"left",
					style: "custom",
					action: "DISMISS"
				}
			});

			var keyboard = BX("newpost-keyboard");
			var textarea = BX("POST_MESSAGE");
			var postPanel = BX("newpost-panel");

			BX.bind(keyboard, 'click', function(e)
				{
					if (!BX.hasClass(this, "newpost-keyboard-pressed"))
						textarea.focus();
				}
			);
			
			BX.bind(keyboard, 'touchstart', function(e)
				{
				}
			);

			var form = BX("<?=CUtil::JSEscape($arParams["FORM_ID"])?>");
			if (window.platform == "android")
			{
				form.style.height = BX.GetWindowInnerSize().innerHeight + "px";
				form.style.position = "relative";
			}

			BX.addCustomEvent("onKeyboardDidShow", function() {
				if (window.platform == "android")
				{
					var keyboardHeight = parseInt(BX.style(form, "height")) - BX.GetWindowInnerSize().innerHeight;
					postPanel.style.bottom = keyboardHeight + "px";
					textarea.style.bottom = keyboardHeight + postPanel.offsetHeight + "px";
					setTimeout(function() {BX.addClass(keyboard, "newpost-keyboard-pressed");}, 0);
				}
				else
				{
					BX.addClass(keyboard, "newpost-keyboard-pressed");
				}
			});

			BX.addCustomEvent("onKeyboardDidHide", function() {
				if (window.platform == "android")
				{
					postPanel.style.cssText = "";
					textarea.style.cssText = "";
					setTimeout(function() {BX.removeClass(keyboard, "newpost-keyboard-pressed");}, 0);
				}
				else
				{
					BX.removeClass(keyboard, "newpost-keyboard-pressed");
				}

			});

			ReadyDevice(function() 
			{
				app.enableCaptureKeyboard(true);

				if (window.platform != "android")
					app.enableScroll(false);
			});
		}
	);
	</script>
