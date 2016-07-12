<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/im_mobile.js").'"></script>');
?>
<?if(empty($arResult['ELEMENTS'])):?>
	<div class="ml-block-empty" id="ml-block-empty"><?=GetMessage('IM_RESENT_CHAT_EMPTY')?></div>
<?else:?>
	<?foreach ($arResult['ELEMENTS'] as $data):
		echo ___IMRMGetSeparator($data['MESSAGE']['date']);
		if ($data['TYPE'] == 'P'):
		$arResult['COUNTERS'][$data['USER']['id']] = intval($data['COUNTER']);
		?><div id="dialog<?=$data['USER']['id']?>" class="ml-block" onclick="_openNewPage(<?=$data['USER']['id']?>, this)">
			<!-- ml-avatar ml-avatar-<?=$data['USER']['status']?>" -->
			<div class="avatar ml-avatar"><div class="ml-avatar-sub" style="background-image:url('<?=$data['USER']['avatar']?>'); background-size:cover;"></div></div>
			<div class="ml-title"><?=(isset($data['USER']['nameList'])? $data['USER']['nameList']: $data['USER']['name'])?></div>
			<div class="ml-text"><?=(strlen($data['MESSAGE']['text'])>0?$data['MESSAGE']['text']:GetMessage('IM_RESENT_MESSAGE_EMPTY'))?></div>
			<div class="ml-counter <?=(isset($data['COUNTER'])? 'ml-counter-active':'')?>" id="dialogCounter<?=$data['USER']['id']?>"><?=intval($data['COUNTER'])?></div>
		</div><?
		else:
		$arResult['COUNTERS']['chat'.$data['CHAT']['id']] = intval($data['COUNTER']);
		?><div id="dialogchat<?=$data['CHAT']['id']?>" class="ml-block" onclick="_openNewPage('chat<?=$data['CHAT']['id']?>', this)">
			<div class="avatar avatar-chat ml-avatar"></div>
			<div class="ml-title"><?=($data['CHAT']['name'])?></div>
			<div class="ml-text"><?=(strlen($data['MESSAGE']['text'])>0?$data['MESSAGE']['text']:GetMessage('IM_RESENT_MESSAGE_EMPTY'))?></div>
			<div class="ml-counter <?=(isset($data['COUNTER'])? 'ml-counter-active':'')?>" id="dialogCounterchat<?=$data['CHAT']['id']?>"><?=intval($data['COUNTER'])?></div>
		</div><?
		endif;
	endforeach;?>
<?endif?>
<script type="text/javascript">
	COUNTERS = <?=(empty($arResult['COUNTERS'])? '{}': CUtil::PhpToJSObject($arResult['COUNTERS']))?>;
	function _openNewPage(id, button)
	{
		BX.addClass(button, "ml-block-active");
		app.openNewPage('/mobile/im/dialog.php?id='+id);
		setTimeout(function(){
			BX.removeClass(button, "ml-block-active");
		}, 1000);
	}
	MESSAGE_DRAW_TIMEOUT = null;
	BX.addCustomEvent("onUpdateUserCounters", function(dialogCounters) {
		var dialogId = 0;
		var dialogIsChat = false;
		var data = [];
		for(var i in dialogCounters)
			data.push(dialogCounters[i]);

		for (var i = data.length - 1; i >= 0; i--)
		{
			if (data[i].USER)
			{
				dialogId = data[i].USER.id;
			}
			else if (data[i].CHAT)
			{
				dialogId = 'chat'+data[i].CHAT.id;
				dialogIsChat = true;
			}
			else
				continue;

			var dialog = BX('dialog'+dialogId);
			var dialogMove = false;
			if (dialog != null)
			{
				var mlText = BX.findChild(dialog, {className : "ml-text"}, true);
				mlText.innerHTML = data[i].MESSAGE.text_mobile;
				var mlCounter = BX.findChild(dialog, {className : "ml-counter"}, true);
				if (parseInt(data[i].MESSAGE.counter)>0)
				{
					if (parseInt(mlCounter.innerHTML) != data[i].MESSAGE.counter)
						dialogMove = true;

					mlCounter.innerHTML = data[i].MESSAGE.counter;
					if (!BX.hasClass(mlCounter, 'ml-counter-active'))
						BX.addClass(mlCounter, 'ml-counter-active');
				}
				else
				{
					BX.removeClass(mlCounter, 'ml-counter-active');
				}
			}
			else
			{
				dialogMove = true;
				var dialog = BX.create('div', {
					attrs : {
						id: 'dialog'+dialogId
					},
					events : {
						click: function(){app.openNewPage('/mobile/im/dialog.php?id='+dialogId)},
						touchend: function(){ BX.toggleClass(this, "ml-block-active"); },
						touchstart: function(){ BX.toggleClass(this, "ml-block-active");}
					},
					props : { className : "ml-block" },
					html: //  ml-avatar ml-avatar-'+dataParams.USERS[dialogId].status+'
						'<div class="avatar ml-avatar"><div class="ml-avatar-sub" '+(dialogIsChat? '': 'style="background-image:url(\''+data[i].USER.avatar+'\'); background-size:cover;"')+'></div></div>\
						<div class="ml-title">'+(dialogIsChat? data[i].CHAT.name: (data[i].USER.nameList? data[i].USER.nameList: data[i].USER.name))+'</div>\
						<div class="ml-text">'+data[i].MESSAGE.text_mobile+'</div>\
						<div class="ml-counter" id="dialogCounter'+dialogId+'"></div>'
				});
			}
			if (dialogMove)
			{
				if (dialog.previousElementSibling && dialog.previousElementSibling.className == 'ml-separator')
				{
					if (dialog.previousElementSibling.id != "recentNow" && dialog.nextElementSibling.className != 'ml-block')
					{
						BX.remove(dialog.previousElementSibling);
					}
				}
				var recentNow = BX('recentNow');
				if (recentNow != null)
				{
					recentNow.parentNode.insertBefore(dialog, recentNow.nextSibling);
				}
				else
				{
					var separator = BX.create('div', {props : { className : "ml-separator" }, attrs : {id: 'recentNow'}, html: '<?=GetMessage('IM_RESENT_TODAY')?>'});
					document.body.insertBefore(separator, document.body.firstChild);
					separator.parentNode.insertBefore(dialog, separator.nextSibling);
				}
			}
		}
	});
	BX.addCustomEvent("onPull", function(data) {
		if (data.module_id == "im")
		{
			if (data.command == 'readMessage')
			{
				COUNTERS[data.params.userId] = 0;
			}
			if (data.command == 'readMessageChat')
			{
				COUNTERS['chat'+data.params.userId] = 0;
			}
			else if (data.command == 'message' || data.command == 'fakeMessage' || data.command == 'messageChat')
			{
				if (data.params.MESSAGE.senderId == BX.message('USER_ID'))
				{
					COUNTERS[data.params.MESSAGE.recipientId] = 0;
				}
				else if (data.command == 'messageChat')
				{
					if (!COUNTERS[data.params.MESSAGE.recipientId])
						COUNTERS[data.params.MESSAGE.recipientId]=1;
					else
						COUNTERS[data.params.MESSAGE.recipientId]++;
				}
				else
				{
					if (!COUNTERS[data.params.MESSAGE.senderId])
						COUNTERS[data.params.MESSAGE.senderId]=1;
					else
						COUNTERS[data.params.MESSAGE.senderId]++;
				}

				var emptyBlock = BX('ml-block-empty');
				if (emptyBlock != null)
				{
					BX.remove(emptyBlock);
				}
				var dataParams =  data.params;
				var dialogId = data.command == 'messageChat'? dataParams.MESSAGE.recipientId: (dataParams.MESSAGE.senderId == BX.message('USER_ID')? dataParams.MESSAGE.recipientId: dataParams.MESSAGE.senderId);
				var dialog = BX('dialog'+dialogId);

				dataParams.MESSAGE.text_mobile = dataParams.MESSAGE.text_mobile.replace(/------------------------------------------------------(.*?)------------------------------------------------------/gmi, "[<?=GetMessage('IM_QUOTE')?>]");

				if (dialog != null)
				{
					var mlText = BX.findChild(dialog, {className : "ml-text"}, true);
					mlText.innerHTML = dataParams.MESSAGE.text_mobile;
				}
				else
				{
					var dialogHtml = '';
					if (data.command == 'messageChat')
					{
						dialogHtml = '<div class="avatar avatar-chat ml-avatar"></div>\
							<div class="ml-title">'+(dataParams.CHAT[dialogId.toString().substr(4)].name) +'</div>\
							<div class="ml-text">'+dataParams.MESSAGE.text_mobile+'</div>\
							<div class="ml-counter" id="dialogCounter'+dialogId+'"></div>';
					}
					else
					{
						dialogHtml = '<div class="avatar ml-avatar"><div class="ml-avatar-sub" style="background-image:url(\''+dataParams.USERS[dialogId].avatar+'\'); background-size:cover;"></div></div>\
							<div class="ml-title">'+(dataParams.USERS[dialogId].nameList? dataParams.USERS[dialogId].nameList: dataParams.USERS[dialogId].name) +'</div>\
							<div class="ml-text">'+dataParams.MESSAGE.text_mobile+'</div>\
							<div class="ml-counter" id="dialogCounter'+dialogId+'"></div>';
					}
					var dialog = BX.create('div', {
						attrs : {
							id: 'dialog'+dialogId
						},
						events : {
							click: function(){app.openNewPage('/mobile/im/dialog.php?id='+dialogId)},
							touchend: function(){ BX.toggleClass(this, "ml-block-active"); },
							touchstart: function(){ BX.toggleClass(this, "ml-block-active"); }
						},
						props : { className : "ml-block" },
						html: dialogHtml
					});
				}

				if (dialog.previousElementSibling && dialog.previousElementSibling.className == 'ml-separator')
				{
					if (dialog.previousElementSibling.id != "recentNow" && dialog.nextElementSibling.className != 'ml-block')
					{
						BX.remove(dialog.previousElementSibling);
					}
				}
				var recentNow = BX('recentNow');
				if (recentNow != null)
				{
					recentNow.parentNode.insertBefore(dialog, recentNow.nextSibling);
				}
				else
				{
					var separator = BX.create('div', {props : { className : "ml-separator" }, attrs : {id: 'recentNow'}, html: '<?=GetMessage('IM_RESENT_TODAY')?>'});
					document.body.insertBefore(separator, document.body.firstChild);
					separator.parentNode.insertBefore(dialog, separator.nextSibling);
				}
			}
			else if (data.command == 'chatRename')
			{
				var dialog = BX('dialogchat'+data.params.chatId);
				if (dialog)
				{
					var mlText = BX.findChild(dialog, {className : "ml-title"}, true);
					mlText.innerHTML = data.params.chatTitle;
				}
			}
			else if (data.command == 'chatUserLeave')
			{
				if (data.params.userId == BX.message('USER_ID'))
				{
					var dialog = BX('dialogchat'+data.params.chatId);
					if (dialog)
					{
						if (dialog.previousElementSibling && dialog.previousElementSibling.className == 'ml-separator')
						{
							if (dialog.nextElementSibling.className != 'ml-block')
							{
								BX.remove(dialog.previousElementSibling);
							}
						}
						BX.remove(dialog);
						COUNTERS['chat'+data.params.chatId] = 0;
					}
				}
			}
			clearTimeout(MESSAGE_DRAW_TIMEOUT);
			MESSAGE_DRAW_TIMEOUT = setTimeout(drawMessage, 500);
		}
	});
	function drawMessage()
	{
		for (var userId in COUNTERS)
		{
			var mlCounter = BX('dialogCounter'+userId);
			if (COUNTERS[userId]>0)
			{
				if (mlCounter.innerHTML != COUNTERS[userId])
					mlCounter.innerHTML = COUNTERS[userId];

				if (!BX.hasClass(mlCounter, 'ml-counter-active'))
					BX.addClass(mlCounter, 'ml-counter-active');
			}
			else
			{
				BX.removeClass(mlCounter, 'ml-counter-active');
			}
		}
		app.getVar({'var' : 'DIALOG_ID','from' : 'current','callback' : function(DIALOG_ID){
			if (DIALOG_ID != 'empty')
			{
				COUNTERS[DIALOG_ID] = 0;
				BX.removeClass(BX('dialogCounter'+DIALOG_ID), 'ml-counter-active');
			}
		}});
	}

	BX.addCustomEvent("onMessageAdd", function(params) {

		var dialog = BX('dialog'+params.userId);
		var mlText = BX.findChild(dialog, {className : "ml-text"}, true);
		mlText.innerHTML = params.text;

		var recentNow = BX('recentNow');
		recentNow.parentNode.insertBefore(dialog, recentNow.nextSibling);
	});
	BX.addCustomEvent("onDialogOpen", function(params) {
		if (typeof(params.id) == 'undefined')
			return false;

		var dialog = BX('dialog'+params.id);
		if (dialog == null)
		{
			if (typeof(params.name) != 'undefined')
				return false;

			var node = BX.create('div', {
				attrs : {
					id: 'dialog'+params.id
				},
				events : {
					click: function(){app.openNewPage('/mobile/im/dialog.php?id='+params.id)},
					touchend: function(){BX.toggleClass(this, "ml-block-active");},
					touchstart: function(){BX.toggleClass(this, "ml-block-active");}
				},
				props : { className : "ml-block" },
				html: //ml-avatar ml-avatar-'+params.status+'
					'<div class="avatar ml-avatar"><div class="ml-avatar-sub" '+(params.avatar? 'style="background:url(\''+params.avatar+'\'); background-size:cover;"': '')+'></div></div>\
					<div class="ml-title">'+(params.nameList? params.nameList: params.name)+'</div>\
					<div class="ml-text">'+(params.lastMessage == ""? '<?=GetMessage('IM_RESENT_MESSAGE_EMPTY')?>': params.lastMessage)+'</div>'
			});

			var recentNow = BX('recentNow');
			if (recentNow != null)
			{
				recentNow.parentNode.insertBefore(node, recentNow.nextSibling);
			}
			else
			{
				var separator = BX.create('div', {props : { className : "ml-separator" }, attrs : {id: 'recentNow'}, html: '<?=GetMessage('IM_RESENT_TODAY')?>'});
				document.body.insertBefore(separator, document.body.firstChild);
				separator.parentNode.insertBefore(node, separator.nextSibling);
			}
		}
		else
		{
			BX.removeClass(BX('dialogCounter'+params.id), 'ml-counter-active');
		}
	});
	<?if (!isset($arParams['TEMPLATE_POPUP']) || $arParams['TEMPLATE_POPUP'] == 'N'):?>
		app.addUserListButton({
			url:"/mobile/?mobile_action=get_user_list&detail_url=/mobile/im/dialog.php?id="
		});
		app.pullDown({
			'enable': true,
			'pulltext': '<?=GetMessage('IM_RESENT_PULLTEXT')?>',
			'downtext': '<?=GetMessage('IM_RESENT_DOWNTEXT')?>',
			'loadtext': '<?=GetMessage('IM_RESENT_LOADTEXT')?>',
			'callback': function(){
				app.BasicAuth({
					success: function() {
						app.onCustomEvent('onImError', {error: 'RECENT_RELOAD'});
						document.location.reload();
					},
					failture: function() {
						app.pullDownLoadingStop();
					}
				});
			}
		});
	<?endif;?>

</script>
<?
$arSeparator = Array();
$lastSeparator = "";
function ___IMRMGetSeparator($date)
{
	global $lastSeparator;
	if ($date > 0)
	{
		$id = '';
		$arNow = localtime();
		$today_1 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3], $arNow[5]+1900);
		$today_2 = mktime(0, 0, 0, $arNow[4]+1, $arNow[3]+1, $arNow[5]+1900);
		if($date >= $today_1 && $date < $today_2)
		{
			$id = 'id="recentNow"';
		}

		$arFormat = Array(
			"tommorow" => "tommorow",
			"today" => "today",
			"yesterday" => "yesterday",
			"" => GetMessage('IM_RESENT_FORMAT_DATE')
		);
		$date = FormatDate($arFormat, $date);
		if ($lastSeparator != md5($date))
		{
			$lastSeparator = md5($date);
			return '<div class="ml-separator" '.$id.'>'.$date.'</div>';
		}
	}
	else
	{
		if ($lastSeparator != md5($date))
		{
			$lastSeparator = md5($date);
			return '<div class="ml-separator">'.GetMessage('IM_RESENT_NEVER').'</div>';
		}
	}
}
?>
