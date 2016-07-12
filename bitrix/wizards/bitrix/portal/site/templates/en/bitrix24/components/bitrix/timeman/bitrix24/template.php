<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<script type="text/javascript">

BX.timeman('bx_tm', <?=CUtil::PhpToJsObject($arResult["START_INFO"]);?>, '<?=SITE_ID?>');

BX.message({
	"TM_STATUS_OPENED" : "<?=GetMessageJS("TM_STATUS_WORK")?>",
	"TM_STATUS_CLOSED" : "<?=GetMessageJS("TM_STATUS_START")?>",
	"TM_STATUS_PAUSED" : "<?=GetMessageJS("TM_STATUS_PAUSED")?>",
	"TM_STATUS_COMPLETED" : "<?=GetMessageJS("TM_STATUS_COMPLETED")?>",
	"TM_STATUS_EXPIRED" : "<?=GetMessageJS("TM_STATUS_EXPIRED")?>"
});

B24Timemanager.init();
BX.ready(function(){
	BXTIMEMAN.ShowFormWeekly(<?=CUtil::PhpToJsObject($arResult["WORK_REPORT"]);?>);
});
</script>

<?

$statusName = "";
$statusClass = "";
if ($arResult["START_INFO"]["STATE"] == "OPENED")
{
	$statusName = GetMessage("TM_STATUS_WORK");
	$statusClass = "";
}
elseif ($arResult["START_INFO"]["STATE"] == "CLOSED")
{
	if ($arResult["START_INFO"]["CAN_OPEN"] == "REOPEN" || !$arResult["START_INFO"]["CAN_OPEN"])
	{
		$statusName = GetMessage("TM_STATUS_COMPLETED");
		$statusClass = "timeman-completed";
	}
	else
	{
		$statusName = GetMessage("TM_STATUS_START");
		$statusClass = "timeman-start";
	}
}
elseif ($arResult["START_INFO"]["STATE"] == "PAUSED")
{
	$statusName = GetMessage("TM_STATUS_PAUSED");
	$statusClass = "timeman-paused";
}
elseif ($arResult["START_INFO"]["STATE"] == "EXPIRED")
{
	$statusName = "";
	$statusClass = "timeman-expired";
}
?>
<div class="timeman-wrap">
	<span id="timeman-block" class="timeman-block <?=$statusClass?>">
		<span class="time" id="timeman-timer"><script type="text/javascript">document.write(B24Timemanager.formatCurrentTime(new Date().getHours(), new Date().getMinutes()))</script></span>
		<span class="timeman-right-side">
			<span class="timeman-info" id="timeman-info"<?if($arResult["START_INFO"]['PLANNER']["EVENT_TIME"] == '' && $arResult["START_INFO"]['PLANNER']["TASKS_COUNT"] <= 0):?> style="display:none"<?endif?>>
				<span class="timeman-event" id="timeman-event"<?if($arResult["START_INFO"]['PLANNER']["EVENT_TIME"] == ''):?> style="display:none"<?endif?>><?=$arResult["START_INFO"]['PLANNER']["EVENT_TIME"]?></span>
				<span class="timeman-tasks" id="timeman-tasks"<?if($arResult["START_INFO"]['PLANNER']["TASKS_COUNT"] <= 0):?> style="display:none"<?endif?>><?=$arResult["START_INFO"]['PLANNER']["TASKS_COUNT"]?></span>
			</span>
			<span class="timeman-beginning-but"><i></i><span id="timeman-status"><?=$statusName?></span></span>
		</span>
		<span class="timeman-not-closed-block">
			<span class="timeman-not-cl-icon"></span>
			<span class="timeman-not-cl-text"><?=GetMessage("TM_STATUS_EXPIRED")?></span>
		</span>
		<span class="timeman-background"></span>
	</span>
</div>