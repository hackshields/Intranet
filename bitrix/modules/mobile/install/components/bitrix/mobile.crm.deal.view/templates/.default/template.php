<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH.'/crm_mobile.js').'"></script>');
$APPLICATION->SetPageProperty('BodyClass', 'crm-page');

$UID = $arResult['UID'];
$entity = $arResult['ENTITY'];

$clientTitle = '';
$clientLegend = '';
$clientImageInfo = null;
$clientCallTo = null;
$clientMailTo = null;
$showCompany = true;

if($entity['~CONTACT_ID'] > 0)
{
	$clientTitle = $entity['~CONTACT_FORMATTED_NAME'];
	$clientLegend = $entity['~COMPANY_TITLE'];
	if($entity['~CONTACT_POST'] !== '')
	{
		if($clientLegend !== '')
		{
			$clientLegend .= ', ';
			$clientLegend .= $entity['~CONTACT_POST'];
		}
	}

	$clientImageID = $entity['~CONTACT_PHOTO'];
	$clientImageInfo = $clientImageID > 0
		? CFile::ResizeImageGet($clientImageID, array('width' => 55, 'height' => 55), BX_RESIZE_IMAGE_EXACT)
		: array('src' => CCrmMobileHelper::GetContactViewImageStub());

	if(isset($arResult['CONTACT_CALLTO']))
	{
		$clientCallTo = $arResult['CONTACT_CALLTO'];
	}

	if(isset($arResult['CONTACT_MAILTO']))
	{
		$clientMailTo = $arResult['CONTACT_MAILTO'];
	}
}
else
{
	$clientTitle = $entity['~COMPANY_TITLE'];
	$clientImageID = $entity['~COMPANY_LOGO'];
	$clientImageInfo = $clientImageID > 0
		? CFile::ResizeImageGet($clientImageID, array('width' => 55, 'height' => 55), BX_RESIZE_IMAGE_EXACT)
		: array('src' => CCrmMobileHelper::GetCompanyViewImageStub());

	$showCompany = false;

	if(isset($arResult['COMPANY_CALLTO']))
	{
		$clientCallTo = $arResult['COMPANY_CALLTO'];
	}

	if(isset($arResult['COMPANY_MAILTO']))
	{
		$clientMailTo = $arResult['COMPANY_MAILTO'];
	}
}

$dataItem = CCrmMobileHelper::PrepareDealData($entity);
$stageClassName = $dataItem['IS_FINISHED']
	? ($dataItem['IS_SUCCESSED'] ? 'green' : 'red') : 'blue';

//COMMENTS already encoded by LHE
$comment = isset($entity['~COMMENTS']) ? $entity['~COMMENTS'] : '';
/*$comment = '';
$commentCut = '';
$hasComment = CCrmMobileHelper::PrepareCut(
	isset($entity['~COMMENTS']) ? $entity['~COMMENTS'] : '',
	$comment,
	$commentCut
);*/

$probability = isset($entity['PROBABILITY']) ? intval($entity['PROBABILITY']) : 0;

echo CCrmViewHelper::RenderDealStageSettings();

?><div id="<?=htmlspecialcharsbx($UID)?>" class="crm_wrapper">
	<span class="crm_head_title_number"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_ID', array('#ID#' => $entity['~ID'])))?></span>
	<div class="crm_head_title"><?=$entity['TITLE']?> - <?=$entity['FORMATTED_OPPORTUNITY']?></div>
	<div class="clb"></div>
	<div class="crm_block_container">
		<div class="crm_tac lisb order">
			<div style="padding: 10px;">
				<div class="crm_order_status">
					<span class="fll" style="color:#687178"><?=$entity['STAGE_TEXT']?></span>
					<div class="clb"></div>
					<?CCrmMobileHelper::RenderProgressBar(
						array(
							'LAYOUT' => 'big',
							'ENTITY_TYPE_ID' => CCrmOwnerType::Deal,
							'ENTITY_ID' => $entity['~ID'],
							//'PREFIX' => strtolower($UID).'_',
							'CURRENT_ID' => $entity['~STAGE_ID']
						)
					);?>
				</div>
				<hr/>
				<div class="crm_order_status">
					<span class="fll fwn"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_PROBABILITY'))?>:</span>
					<span class="fll fwb"><?=$probability?>%</span>
					<div class="clb"></div>
				</div>
			</div>
<!--			<a href="#" class="crm_buttons sms"><span></span></a>-->
<!--			<a href="#" class="crm_buttons phone"><span></span></a>-->
<!--			<a href="#" class="crm_buttons phone"><span></span></a>-->
<!--			<a href="#" class="crm_buttons ceck"><span></span></a>-->
<!--			<a href="#" class="crm_buttons cont"><span></span></a>-->
		</div>
		<div class="clb"></div>
	</div>
	<?if($clientTitle !== ''):?>
		<div class="crm_block_container">
			<div class="crm_card">
				<div class="crm_card_image">
					<img src="<?=$clientImageInfo && isset($clientImageInfo['src']) ? htmlspecialcharsbx($clientImageInfo['src']) : ''?>"/>
				</div>
				<div class="crm_card_name">
					<?=htmlspecialcharsbx($clientTitle)?>
				</div>
				<div class="crm_card_description">
					<?=htmlspecialcharsbx($clientLegend)?>
				</div>
				<div class="clb"></div>
			</div>
			<div class="crm_tac lisb"><?
					$enableCallto = $clientCallTo && ($clientCallTo['URL'] !== '' || $clientCallTo['SCRIPT'] !== '');
					$enableMailto = $clientMailTo && ($clientMailTo['URL'] !== '' || $clientMailTo['SCRIPT'] !== '');
					?><a class="crm accept-button<?=!$enableCallto ? ' disable' : ''?>" href="<?=$clientCallTo['URL'] !== '' ? htmlspecialcharsbx($clientCallTo['URL']) : '#'?>"<?=$clientCallTo['SCRIPT'] !== '' ? ' onclick="'.htmlspecialcharsbx($clientCallTo['SCRIPT']).'"' : (!$enableCallto ? ' onclick="return false;"' : '')?>><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_ACTION_CALL_TO_CLIENT'))?></a><?
					?><a class="crm_buttons email<?=!$enableMailto ? ' disabled' : ''?>" href="<?=$clientMailTo['URL'] !== '' ?  htmlspecialcharsbx($clientMailTo['URL']) : '#'?>"<?=$clientMailTo['SCRIPT'] !== '' ? ' onclick="'.htmlspecialcharsbx($clientMailTo['SCRIPT']).'"' : (!$enableMailto ? ' onclick="return false;"' : '')?>><span></span></a><?
			?></div>
		</div>
	<?endif;?>
	<?if($showCompany && $entity['COMPANY_SHOW_URL'] !== ''):?>
	<div class="crm_block_container company" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['COMPANY_SHOW_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title fln crm_arrow"><?=htmlspecialcharsbx($entity['~COMPANY_TITLE'] != '' ? $entity['~COMPANY_TITLE'] : GetMessage('M_CRM_DEAL_VIEW_NO_TITLE'))?></div>
			<div class="clb"></div>
		</div>
	</div>
	<?endif;?>
	<div class="crm_block_container dealings" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['ACTIVITY_LIST_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_ACTIVITY_LIST'))?></div>
			<div class="crm_dealings_count"><?=$entity['ACTITITY_QUANTITY']?></div>
			<div class="clb"></div>
		</div>
	</div>
	<div class="crm_block_container work" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['PRODUCT_ROWS_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_PRODUCT_ROWS'))?></div>
			<div class="crm_work_count"><?=$entity['PRODUCT_ROWS_QUANTITY']?></div>
			<div class="clb"></div>
		</div>
	</div>
	<div class="crm_block_container comments" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['EVENT_LIST_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_EVENT_LIST'))?></div>
			<!--<div class="crm_dealings_count"></div>-->
			<div class="clb"></div>
		</div>
	</div>
	<div class="crm_block_container">
		<div class="crm_contact_info">
			<?$hasType = isset($entity['TYPE_NAME']) && $entity['TYPE_NAME'] !== '';
			$hasBeginDate = isset($entity['BEGINDATE']) && $entity['BEGINDATE'] !== '';
			$hasCloseDate = isset($entity['CLOSEDATE']) && $entity['CLOSEDATE'] !== '';?>
			<?if($hasBeginDate || $hasCloseDate):?>
			<table><tbody>
				<?if($hasType):?>
				<tr>
					<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_TYPE'))?>:</td>
					<td><?=$entity['TYPE_NAME']?></td>
				</tr>
				<?endif;?>
				<?if($hasBeginDate):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_BEGINDATE'))?>:</td>
						<td><?=$entity['BEGINDATE']?></td>
					</tr>
				<?endif;?>
				<?if($hasCloseDate):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_CLOSEDATE'))?>:</td>
						<td><?=$entity['CLOSEDATE']?></td>
					</tr>
				<?endif;?>
			</tbody></table>
			<hr/>
			<?endif;?>
			<table>
				<tbody>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_RESPONSIBLE'))?>:</td>
						<?if($entity['ASSIGNED_BY_SHOW_URL'] !== ''):?>
							<td class="crm_arrow" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['ASSIGNED_BY_SHOW_URL'])?>' });">
								<span class="crm_user_link"><?=$entity['ASSIGNED_BY_FORMATTED_NAME']?></span>
							</td>
						<?else:?>
							<td>
								<span class="crm_user_link"><?=$entity['ASSIGNED_BY_FORMATTED_NAME']?></span>
							</td>
						<?endif;?>
					</tr>
				</tbody>
			</table>
			<?if($comment !== ''):?>
			<hr/>
			<div class="crm_block_content">
				<div class="crm_block_content_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_DEAL_VIEW_COMMENT'))?>:</div>
				<div class="crm_block_comment"><?=$comment?></div>
			</div>
			<?endif;?>
		</div>
	</div>
</div>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmMobileContext.getCurrent().enableReloadOnPullDown(
				{
					pullText: '<?=GetMessageJS('M_CRM_DEAL_VIEW_PULL_TEXT')?>',
					downText: '<?=GetMessageJS('M_CRM_DEAL_VIEW_DOWN_TEXT')?>',
					loadText: '<?=GetMessageJS('M_CRM_DEAL_VIEW_LOAD_TEXT')?>'
				}
			);
		}
	);
</script>

