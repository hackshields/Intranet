<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if(is_array($arResult['DATA'])&&count($arResult['DATA'])>0)
{

	if(IsAmPmMode())
	{
		$t = FormatDate('g#i#A', time() + CTimeZone::GetOffset());
	}
	else
	{
		$t = FormatDate('H#i',time() + CTimeZone::GetOffset());
	}

	$at = explode('#', $t);
	$t = '<span class="time-hours">'.$at[0].'</span><span class="time-semicolon">:</span><span class="time-minutes">'.$at[1].'</span>';

	if($at[2])
	{
		$t .= '<span class="time-am-pm">'.$at[2].'</span>';
	}

?>

<div class="timeman-wrap planner-wrap">
	<span id="timeman-block" class="timeman-block">
		<span class="time" id="timeman-timer"><?=$t?></span>
		<span class="timeman-right-side">
			<span class="timeman-info" id="timeman-info"></span>
		</span>
		<span class="timeman-background"></span>
	</span>
</div>

<script type="text/javascript">
(function(){
	window.plannerFormatCurrentTime = function(hours, minutes, seconds)
	{
		var mt = "";
		if (BX.isAmPmMode())
		{
			mt = "AM";
			if (hours > 12)
			{
				hours = hours - 12;
				mt = "PM";
			}
			else if (hours == 0)
			{
				hours = 12;
				mt = "AM";
			}
			else if (hours == 12)
			{
				mt = "PM";
			}

			mt = '<span class="time-am-pm">' + mt + '</span>';
		}
		else
			hours = BX.util.str_pad(hours, 2, "0", "left");

		return '<span class="time-hours">' + hours + '</span>' +
				'<span class="time-semicolon">:</span>' +
				'<span class="time-minutes">' + BX.util.str_pad(minutes, 2, "0", "left") + '</span>' +
				mt;
	}

	window.plannerUnFormatTime = function(time)
	{
		var q = time.split(/[\s:]+/);
		if (q.length == 3)
		{
			var mt = q[2];
			if (mt == 'pm' && q[0] < 12)
				q[0] = parseInt(q[0], 10) + 12;

			if (mt == 'am' && q[0] == 12)
				q[0] = 0;

		}
		return parseInt(q[0], 10) * 3600 + parseInt(q[1], 10) * 60;
	}

	var BXPLANNER = new BX.CPlanner(<?=CUtil::PhpToJsObject($arResult['DATA']);?>),
		BXPLANNERWND = null,
		NODE_TASKS = null,
		NODE_EVENTS = null,

		timer = null;

	BX.addCustomEvent(
		BXPLANNER, 'onPlannerDataRecieved', function(ob, DATA)
		{
			if(!!DATA.CALENDAR_ENABLED)
			{
				var d = DATA.EVENT_TIME;
				if(d != '')
				{
					var t = plannerUnFormatTime(DATA.EVENT_TIME),
						dt = new Date();
					if(t < dt.getHours()*3600 + dt.getMinutes() * 60)
					{
						d = '';
					}
				}

				if(d != '')
				{
					if(!NODE_EVENTS)
					{
						NODE_EVENTS = BX.create('SPAN', {props: {
							className: 'timeman-event',
							id: 'timeman-event'
						}});
					}

					NODE_EVENTS.innerHTML = d;
					NODE_EVENTS.style.display = 'inline-block';
				}
				else if (!!NODE_EVENTS)
				{
					NODE_EVENTS.style.display = 'none';
				}
			}

			if(!!DATA.TASKS_ENABLED)
			{
				if(!NODE_TASKS)
				{
					NODE_TASKS = BX.create('SPAN', {
						style: {
							display: 'block' // !!!!!!
						},
						props: {
							className: 'timeman-tasks',
							id: 'timeman-tasks'
						}
					})
				}

				NODE_TASKS.innerHTML = parseInt(DATA.TASKS_COUNT)||'0';
			}


			BX.adjust(BX('timeman-info', true), {children: [NODE_EVENTS, NODE_TASKS]});

			if (!timer)
			{
				timer = BX.timer({container: BX('timeman-timer'), display : "bitrix24_time"});
			}
		}
	);

	BX.ready(function(){
		BX.bind(BX('timeman-block', true), 'click', function()
		{
			if(!BXPLANNERWND)
			{
				BXPLANNERWND = new BX.PopupWindow('planner_main', this, {
					autoHide: true,
					offsetTop : 10,
					offsetLeft : -60,
					zIndex : -1,
					bindOptions: {
						forceBindPosition: true,
						forceTop: true
					},
					angle: {
						position: "top",
						offset: 130
					},
					events: {
						onPopupClose: function() {
							BX.removeClass(BX('timeman-block', true), "timeman-block-active");
						}
					}
				});
			}

			if(!BXPLANNERWND.isShown())
			{
				BXPLANNER.update();
				BXPLANNERWND.setContent(BX.create('DIV', {
					props: {className: 'tm-tabs-content tm-tab-content'},
					children: [BXPLANNER.draw()]
				}));
			}

			BX.addClass(this, "timeman-block-active");
			BXPLANNERWND.show();
		});
	});

	BX.timer.registerFormat("bitrix24_time",BX.proxy(this.plannerFormatCurrentTime, this));
	BXPLANNER.draw();
})();
</script>
<?
}
?>

