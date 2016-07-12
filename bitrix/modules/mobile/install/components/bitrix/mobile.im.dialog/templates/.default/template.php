<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/im_mobile.js").'"></script>');

$lastMessage = "";
$bSkip = false;
if ($arResult['ID'] == $USER->GetID())
{?>
	<script type="text/javascript">
		setTimeout(function(){
			app.closeController();
		}, 300);
		BX.addCustomEvent("onOpenPageAfter", function(){
			app.closeController();
		});
	</script>
<?
	$bSkip = true;
}
if (!$bSkip):
?>
<div class="im-undelivered" style="position: absolute; width: 0; height: 0; overflow:hidden">
	<div class="im-undelivered-btn"></div>
	<div class="im-undelivered-btn-press"></div>
	<div class="im-undelivered-message-icon"></div>
</div>
<div class="im-blocks" id="im-blocks" onclick="">
<?if(empty($arResult['MESSAGES'])):?>
	<div class="im-block-empty" id="im-block-empty"><?=GetMessage('IM_MESSENGER_MESSAGE_EMPTY');?></div>
<?else:?>
	<?foreach (array_reverse($arResult['MESSAGES']) as $data):?><?
		$arFormat = Array(
			"tommorow" => "tommorow, ".GetMessage('IM_MESSENGER_FORMAT_TIME'),
			"today" => "today, ".GetMessage('IM_MESSENGER_FORMAT_TIME'),
			"yesterday" => "yesterday, ".GetMessage('IM_MESSENGER_FORMAT_TIME'),
			"" => GetMessage('IM_MESSENGER_FORMAT_DATE')
		);
		$data['date'] = FormatDate($arFormat, $data['date']);
		$data['text'] = ImFormatText($data['text']);
	if ($data['senderId'] == 0):?>
		<div class="im-block im-block-system" id="message<?=$data['id']?>">
			<div class="im-block-cont">
				<div class="im-block-title"></div>
				<div class="im-block-text"><?=$data['text']?></div>
				<div class="im-block-info"><?=$data['date']?></div>
			</div>
		</div>
	<?elseif ($data['senderId'] != $USER->GetID()):?>
		<div class="im-block im-block-in" id="message<?=$data['id']?>">
			<div class="avatar"><div class="im-avatar" style="background-image:url('<?=$arResult['USERS'][$data['senderId']]['avatar']?>'); background-size:cover;"></div></div>
			<div class="im-block-cont">
				<div class="im-block-title"><?=$arResult['USERS'][$data['senderId']]['name']?></div>
				<div class="im-block-text"><?=$data['text']?></div>
				<div class="im-block-info"><?=$data['date']?></div>
			</div>
		</div>
	<?else:?>
		<div class="im-block im-block-out" id="message<?=$data['id']?>">
			<div class="avatar"><div class="im-avatar" style="background-image:url('<?=$arResult['USERS'][$data['senderId']]['avatar']?>'); background-size:cover;"></div></div>
			<div class="im-block-cont">
				<div class="im-block-title"><?=$arResult['USERS'][$data['senderId']]['name']?></div>
				<div class="im-block-text"><?=$data['text']?></div>
				<div class="im-block-info"><?=$data['date']?></div>
			</div>
		</div>
		<script type="text/javascript">BX.IM.historyMessage[<?=$data['id']?>] = true;</script>
		<?endif;?>
		<?$lastMessage = $data['text'];?>
	<?endforeach;?>
<?endif;?>
	<div id="im-block-writing" class="im-block-writing"></div>
</div>
<script type="text/javascript">
	var PAGE_ID = 'DIALOG<?=$arResult['ID']?>';
	var DIALOG_ID = '<?=$arResult['ID']?>';
	var PUSH_LAST_MESSAGE_ID = 0;
	<?
	$arResult['DIALOG']['lastMessage'] = $lastMessage;
	?>
	BX.addCustomEvent("onUpdateUserCounters", function(data) {
		if (data[DIALOG_ID])
		{
			PUSH_LAST_MESSAGE_ID = PUSH_LAST_MESSAGE_ID < data[DIALOG_ID].MESSAGE.id? data[DIALOG_ID].MESSAGE.id: PUSH_LAST_MESSAGE_ID;
		}
	});
	BX.addCustomEvent("onOpenPageAfter", function(data){
		if (PUSH_LAST_MESSAGE_ID > 0)
			BX.IM.readMessage(DIALOG_ID, PUSH_LAST_MESSAGE_ID);
		app.onCustomEvent('onDialogOpen', <?=CUtil::PhpToJSObject($arResult['DIALOG'])?>);
	});
	BX.message({
		'IM_FORMAT_DATETIME_TOMMOROW' : 'tommorow, <?=GetMessage("IM_MESSENGER_FORMAT_TIME")?>',
		'IM_FORMAT_DATETIME_TODAY' : 'today, <?=GetMessage("IM_MESSENGER_FORMAT_TIME")?>',
		'IM_FORMAT_DATETIME_YESTERDAY' : 'yesterday, <?=GetMessage("IM_MESSENGER_FORMAT_TIME")?>',
		'IM_FORMAT_DATETIME' : '<?=GetMessage("IM_MESSENGER_FORMAT_DATE")?>',
		'IM_MESSENGER_WRITING' : '<?=GetMessage("IM_MESSENGER_WRITING")?>'
	});

	BX.IM.historyMessageCount = <?=count($arResult['MESSAGES'])?>;
	USERS = <?=CUtil::PhpToJSObject($arResult['USERS'])?>;
	closeDialog = false;
	BX.addCustomEvent("onPull", function(data)
	{
		if (data.module_id == "im")
		{
			if (data.command == 'startWriting')
			{
				BX.IM.startWriting(data.params.senderId);
			}
			else if (data.command == 'message' || data.command == 'messageChat')
			{
				var message = data.params.MESSAGE;
				message.date = parseInt(message.date)+parseInt(BX.message('USER_TZ_OFFSET'));

				if (message.recipientId == DIALOG_ID)
					closeDialog = false;

				if (data.command == 'message' && message.senderId == DIALOG_ID
				|| data.command == 'messageChat' && message.recipientId == DIALOG_ID && message.senderId != BX.message('USER_ID'))
				{
					BX.IM.drawMessage({
						'id' : message.id,
						'senderId' : message.senderId,
						'recipientId' : message.recipientId,
						'date' : message.date,
						'text' : BX.MessengerMobile.prepareText(message.text, false, true)
					});

					PUSH_LAST_MESSAGE_ID = PUSH_LAST_MESSAGE_ID < message.id? message.id: PUSH_LAST_MESSAGE_ID;
					app.checkOpenStatus({
						'callback' : function(data)
						{
							if (data && data.status == 'visible')
							{
								BX.IM.readMessage(DIALOG_ID, message.id);
							}
						}
					});

					BX.IM.endWriting(message.senderId, true);
				}
				else if (message.recipientId == DIALOG_ID && !BX.IM.sendMessageFlag && BX('message'+message.id) == null)
				{
					BX.IM.drawMessage({
						'id' : message.id,
						'senderId' : message.senderId,
						'recipientId' : message.recipientId,
						'date' : message.date,
						'text' : BX.MessengerMobile.prepareText(message.text, false, true)
					});
				}
			}
			else if (data.command == 'chatUserLeave')
			{
				if (data.params.userId == BX.message('USER_ID'))
				{
					app.checkOpenStatus({
						'callback' : function(data)
						{
							if (data && data.status == 'visible')
							{
								app.closeController();
							}
							else
							{
								closeDialog = true;
							}
						}
					});
				}
			}
		}
	});
	BX.addCustomEvent("onOpenPageAfter", function(){
		if (closeDialog)
		{
			closeDialog = false;
			app.closeController({'drop': true});
		}
	});
	BX.message({'IM_MESSENGER_DELIVERED':'<?=GetMessage('IM_MESSENGER_DELIVERED')?>','IM_MESSENGER_NOT_DELIVERED':'<?=GetMessage('IM_MESSENGER_NOT_DELIVERED')?>','IM_MESSENGER_ND_RETRY':'<?=GetMessage('IM_MESSENGER_ND_RETRY')?>'});
	if (BX.IM.historyMessageCount == 20)
	{
		app.pullDown({
			'enable': true,
			'pulltext': '<?=GetMessage('IM_MESSENGER_MESSAGE_PULLTEXT')?>',
			'downtext': '<?=GetMessage('IM_MESSENGER_MESSAGE_DOWNTEXT')?>',
			'loadtext': '<?=GetMessage('IM_MESSENGER_MESSAGE_LOADTEXT')?>',
			'callback': function(){
				BX.IM.getHistory(DIALOG_ID);
			}
		});
	}

	BX.IM.initAudio();
	BX.ready(function(){
		BitrixMobile.Utils.autoResizeForm(BX("send-message-input"), BX("im-blocks", true));
		var container = BX("im-blocks", true);

		if (window.platform == "android")
			window.scrollTo(0, document.documentElement.scrollHeight);
		else
			container.scrollTop = container.scrollHeight - container.offsetHeight;
		BX.IM.writing = BX('im-block-writing');
		BX.bind(BX('send-message-input'), 'keydown', function(e) {
			if (BX.util.trim(this.value).length > 2)
				BX.IM.sendWriting(DIALOG_ID);
		});
	});
	<?if (isset($_REQUEST['FROM_PROFILE'])): ?>
		app.addButtons({
			addRefreshButton:{
				type: "user",
				style:"custom",
				callback:function(){
					app.closeController();
				}
			}
		});
	<?else:?>
		<?if ($arResult['IS_CHAT']):?>
			app.menuCreate({items:[
				{ icon: 'user', name: '<?=GetMessage('IM_MESSENGER_MENU_USERS')?>', action:function() { app.openNewPage('/mobile/im/chat.php?chat_id='+DIALOG_ID); }},
				{ icon: 'delete', name: '<?=GetMessage('IM_MESSENGER_MENU_LEAVE')?>', action:function() { BX.IM.leaveFromChat(DIALOG_ID.toString().substr(4)); }}
			]});
			app.addButtons({
				addRefreshButton:{
					type: 'context-menu',
					style: 'custom',
					callback:function(){
						app.menuShow();
					}
				}
			});
		<?else:?>
			app.addButtons({
				addRefreshButton:{
					type: "user",
					style:"custom",
					callback:function(){
						app.openNewPage('/mobile/users/?user_id='+DIALOG_ID+'&FROM_DIALOG=Y');
					}
				}
			});
		<?endif;?>
	<?endif;?>
</script>
<form id="send-message-form" class="send-message-block"  style="display:none" onsubmit="BX.IM.sendMessage(<?=$arResult['ID']?>); BitrixMobile.Utils.resetAutoResize(BX('send-message-input'), BX('im-blocks', true)); return false;">
	<textarea id="send-message-input" class="send-message-input" placeholder="<?=GetMessage('IM_MESSENGER_MESSAGE_NEW')?>"></textarea><input type="submit" class="send-message-button" value="<?=GetMessage('IM_MESSENGER_MESSAGE_SEND')?>" ontouchstart="BX.toggleClass(this, 'send-message-button-press')" ontouchend="BX.toggleClass(this, 'send-message-button-press');">
</form>
<script type="text/javascript">
	if(app.enableInVersion(4))
	{
		app.showInput({
			placeholder:"<?=GetMessage('IM_MESSENGER_MESSAGE_NEW')?>",
			button_name:"<?=GetMessage('IM_MESSENGER_MESSAGE_SEND')?>",
			action:function(text)
			{
				BX.IM.sendMessage("<?=$arResult['ID']?>", text);
				app.clearInput();
				BitrixMobile.Utils.resetAutoResize(BX('send-message-input'), BX('im-blocks', true));
			}
		});
	}
	else
	{
		BX("send-message-form").style.display = "block";
	}
</script>
<?
endif;
function ImFormatText($text)
{
	$text = preg_replace("/------------------------------------------------------<br \/>(.*?)\[(.*?)\]<br \/>(.*?)------------------------------------------------------(<br \/>)?/m", "<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\"><div class=\"bx-messenger-content-quote-name\">$1 <span class=\"bx-messenger-content-quote-time\">$2</span></div>$3</div></div>", $text);
	$text = preg_replace("/------------------------------------------------------<br \/>(.*?)<br \/>------------------------------------------------------(<br \/>)?/m", "<div class=\"bx-messenger-content-quote\"><span class=\"bx-messenger-content-quote-icon\"></span><div class=\"bx-messenger-content-quote-wrap\">$1</div></div>", $text);
	$text = preg_replace("/\n/im", '<br />', $text);
	$text = preg_replace("/\t/im", '&nbsp;&nbsp;&nbsp;&nbsp;', $text);

	return $text;
}
?>