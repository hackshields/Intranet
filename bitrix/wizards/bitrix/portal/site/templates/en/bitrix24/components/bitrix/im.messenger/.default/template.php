<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$GLOBALS["LEFT_MENU_COUNTERS"] = is_array($arResult["COUNTERS"]) ? $arResult["COUNTERS"] : Array();

?><span class="header-informers-wrap"><span id="im-informer-messages" title="<?=GetMessage("IM_MESSENGER_OPEN_MESSENGER");?>" class="header-informers header-informer-messages<?if($arResult["MESSAGE_COUNTER"] > 0):?> header-informer-act<?endif?>" onclick="showMessagePopup(this)"><?=($arResult["MESSAGE_COUNTER"] > 0 ? $arResult["MESSAGE_COUNTER"] : "")?></span><span onclick="showNotifyPopup(this)" title="<?=GetMessage("IM_MESSENGER_OPEN_NOTIFY");?>" id="im-informer-events" class="header-informers header-informer-events<?if($arResult["NOTIFY_COUNTER"] > 0):?> header-informer-act<?endif?>"><?=($arResult["NOTIFY_COUNTER"] > 0 ? $arResult["NOTIFY_COUNTER"] : "")?></span></span>

<?$this->SetViewTarget("im")?>
<div id="bx-notifier-panel" class="bx-notifier-panel">
	<span class="bx-notifier-panel-left"></span><span class="bx-notifier-panel-center"><span class="bx-notifier-drag">
	</span><span class="bx-notifier-indicators"><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-message" title="<?=GetMessage('IM_MESSENGER_OPEN_MESSENGER');?>"><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count">0</span>
		</a><a href="javascript:void(0)" class="bx-notifier-indicator bx-notifier-notify" title="<?=GetMessage('IM_MESSENGER_OPEN_NOTIFY');?>"><span class="bx-notifier-indicator-text"></span><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count">0</span>
		</a><a class="bx-notifier-indicator bx-notifier-mail" href="#mail" title="<?=GetMessage('IM_MESSENGER_OPEN_EMAIL');?>"><span class="bx-notifier-indicator-icon"></span><span class="bx-notifier-indicator-count">0</span>
		</a></span>
	</span><span class="bx-notifier-panel-right"></span>
</div>
<?$this->EndViewTarget()?>

<script type="text/javascript">
<?=CIMMessenger::GetTemplateJS(Array(), $arResult)?>

function showMessagePopup(button)
{
	if (BXIM.isOpenMessenger())
	{
		BXIM.closeMessenger();
		BXIM.closeContactList();
	}
	else
	{
		BXIM.openMessenger();
		BXIM.openContactList() 
	}
}

function showNotifyPopup(button)
{
	if (BX.hasClass(button, "header-informer-press"))
	{
		BX.removeClass(button, "header-informer-press");
		BXIM.closeNotify();
	}
	else
	{
		//BX.addClass(button, "header-informer-press");

		BXIM.openNotify({
			bindElement : button, 
			offsetLeft : 17,
			offsetTop : 5
		});
	}
}

BX.ready(function() {

	function updateInformer(informer, counter)
	{
		if (counter > 0)
		{
			informer.innerHTML = counter;
			BX.addClass(informer, "header-informer-act");
		}
		else
		{
			informer.innerHTML = "";
			BX.removeClass(informer, "header-informer-act");
		}
	}

	BX.addCustomEvent("onImUpdateCounterNotify", function(counter) { 
		updateInformer(BX("im-informer-events", true), counter);
	});

	BX.addCustomEvent("onImUpdateCounterMessage", function(counter) { 
		updateInformer(BX("im-informer-messages", true), counter);
	});
});
</script>