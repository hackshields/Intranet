<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH."/im_mobile.js").'"></script>');
?>
<?if(empty($arResult['notify'])):?>
	<div class="notif-block-empty"><?=GetMessage('NM_EMPTY');?></div>
<?else:?>
	<div class="notif-block-wrap">
	<?foreach ($arResult['notify'] as $data):?>
	<?
		$arFormat = Array(
			"tommorow" => "tommorow, ".GetMessage('NM_FORMAT_TIME'),
			"today" => "today, ".GetMessage('NM_FORMAT_TIME'),
			"yesterday" => "yesterday, ".GetMessage('NM_FORMAT_TIME'),
			"" => GetMessage('NM_FORMAT_DATE')
		);
		$data['date'] = FormatDate($arFormat, $data['date']);
		$data['text'] = strip_tags($data['text']);
		$data['link'] = createLink($data['original_tag']);
	?>
		<div class="notif-block" <?=($data['link']? ' onclick="app.openNewPage(\''.$data['link'].'\')"':'')?>>
			<?=($data['link']? '<div class="notif-counter">'.GetMessage('NM_MORE').'</div>': '')?>
			<div class="avatar"><div class="im-avatar" style="background-image:url('<?=$data['userAvatar']?>'); background-size:cover;"></div></div>
			<div class="notif-cont">
				<div class="notif-title"><?=$data['userName']?></div>
				<div class="notif-text"><?=$data['text']?></div>
				<div class="notif-time"><?=$data['date']?></div>
			</div>
		</div>
	<?endforeach;?>
	</div>
	<script type="text/javascript">
		app.onCustomEvent('onNotificationsLastId', <?=intval($arResult['maxNotify'])?>);
	</script>
<?
endif;

function createLink($tag)
{
	$link = '/mobile/log/?ACTION=CONVERT';
	$result = false;

	if (substr($tag, 0, 10) == 'BLOG|POST|'
	|| substr($tag, 0, 13) == 'BLOG|COMMENT|'
	|| substr($tag, 0, 18) == 'BLOG|POST_MENTION|'
	|| substr($tag, 0, 21) == 'BLOG|COMMENT_MENTION|')
	{
		$params = explode("|", $tag);
		$result = $link."&ENTITY_TYPE_ID=BLOG_POST&ENTITY_ID=".$params[2];
	}
	else if (substr($tag, 0, 10) == 'RATING|DL|')
	{
		$params = explode("|", $tag);
		$result = $link."&ENTITY_TYPE_ID=".$params[2]."&ENTITY_ID=".$params[3];
	}
	else if (substr($tag, 0, 7) == 'RATING|')
	{
		$params = explode("|", $tag);
		$result = $link."&ENTITY_TYPE_ID=".$params[1]."&ENTITY_ID=".$params[2];
	}
	else if (substr($tag, 0, 15) == 'CALENDAR|INVITE')
	{
		$params = explode("|", $tag);
		if (count($params) >= 5 && $params[4] == 'cancel')
			$result = false;
		else
			$result = '/mobile/calendar/view_event.php?event_id='.$params[2];
	}

	return $result;
}
?>
