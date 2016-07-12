<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH.'/crm_mobile.js').'"></script>');
$APPLICATION->SetPageProperty('BodyClass', 'crm-page');

if(!function_exists('__CrmMobileCompanyViewRenderMultiFields'))
{
	function __CrmMobileCompanyViewRenderMultiFields($type, &$fields, &$typeInfos)
	{
		$data = isset($fields[$type]) ? $fields[$type] : array();
		if(empty($data))
		{
			return '';
		}

		$result = '';

		$typeInfo = isset($typeInfos[$type]) ? $typeInfos[$type] : array();
		foreach($data as $datum)
		{
			$value = isset($datum['VALUE']) ? $datum['VALUE'] : '';
			if($value === '')
			{
				continue;
			}

			$type = isset($datum['VALUE_TYPE']) ? $datum['VALUE_TYPE'] : '';
			$legend = '';
			if(isset($typeInfo[$type]))
			{
				$legend = isset($typeInfo[$type]['ABBR']) ? $typeInfo[$type]['ABBR'] : '';
				if($legend === '' && isset($typeInfo[$type]['SHORT']))
				{
					$legend = $typeInfo[$type]['SHORT'];
				}
			}

			if($result !== '')
			{
				$result .= '<br/>';
			}

			$result .= htmlspecialcharsbx($value).' '.htmlspecialcharsbx($legend);
		}

		return $result;
	}
}

$UID = $arResult['UID'];
$entity = $arResult['ENTITY'];

$dataItem = CCrmMobileHelper::PrepareCompanyData($entity);
$typeInfos = CCrmFieldMulti::GetEntityTypes();

//ADDRESS
$address = '';
$addressCut = '';
$hasAddress = CCrmMobileHelper::PrepareCut(
	isset($entity['~ADDRESS']) ? $entity['~ADDRESS'] : '',
	$address,
	$addressCut
);

//ADDRESS_LEGAL
$addressLegal = '';
$addressLegalCut = '';
$hasAddressLegal = CCrmMobileHelper::PrepareCut(
	isset($entity['~ADDRESS_LEGAL']) ? $entity['~ADDRESS_LEGAL'] : '',
	$addressLegal,
	$addressLegalCut
);

//BANKING_DETAILS
$bankDetails = '';
$bankDetailsCut = '';
$hasBankDetails = CCrmMobileHelper::PrepareCut(
	isset($entity['~BANKING_DETAILS']) ? $entity['~BANKING_DETAILS'] : '',
	$bankDetails,
	$bankDetailsCut
);

//COMMENTS already encoded by LHE
$comment = isset($entity['~COMMENTS']) ? $entity['~COMMENTS'] : '';
/*$comment = '';
$commentCut = '';
$hasComment = CCrmMobileHelper::PrepareCut(
	isset($entity['~COMMENTS']) ? htmlspecialcharsback($entity['~COMMENTS']) : '',
	$comment,
	$commentCut
);*/

$titleHtml = isset($entity['TITLE']) ? $entity['TITLE'] : '';
?><div id="<?=htmlspecialcharsbx($UID)?>" class="crm_wrapper">
	<div class="crm_block_container">
		<div class="crm_card">
			<div class="crm_card_image">
				<img src="<?=htmlspecialcharsbx($dataItem['VIEW_IMAGE_URL'])?>"/>
			</div>
			<div class="crm_card_name"><?=$titleHtml?></div>
			<div class="clb"></div>
		</div>
		<div class="crm_tac lisb"><?
				$callto = isset($arResult['CALLTO']) ? $arResult['CALLTO'] : null;
				$enableCallto = $callto && ($callto['URL'] !== '' || $callto['SCRIPT'] !== '');
				$mailto = isset($arResult['MAILTO']) ? $arResult['MAILTO'] : null;
				$enableMailto = $mailto && ($mailto['URL'] !== '' || $mailto['SCRIPT'] !== '');
				?><a class="crm accept-button<?=!$enableCallto ? ' disable' : ''?>" href="<?=$callto['URL'] !== '' ? htmlspecialcharsbx($callto['URL']) : '#'?>"<?=$callto['SCRIPT'] !== '' ? ' onclick="'.htmlspecialcharsbx($callto['SCRIPT']).'"' : (!$enableCallto ? ' onclick="return false;"' : '')?>><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_ACTION_CALL_TO'))?></a><?
				?><a class="crm_buttons email<?=!$enableMailto ? ' disabled' : ''?>" href="<?=$mailto['URL'] !== '' ?  htmlspecialcharsbx($mailto['URL']) : '#'?>"<?=$mailto['SCRIPT'] !== '' ? ' onclick="'.htmlspecialcharsbx($mailto['SCRIPT']).'"' : (!$enableMailto ? ' onclick="return false;"' : '')?>><span></span></a><?
		?></div>
		<div class="clb"></div>
	</div>
	<div class="crm_block_container work" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['ACTIVITY_LIST_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_ACTIVITY_LIST'))?></div>
			<div class="crm_dealings_count"><?=$entity['ACTITITY_QUANTITY']?></div>
			<div class="clb"></div>
		</div>
	</div>
	<div class="crm_block_container dealings" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['DEAL_LIST_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_DEAL_LIST'))?></div>
			<div class="crm_dealings_count"><?=$entity['DEAL_QUANTITY']?></div>
			<div class="clb"></div>
		</div>
	</div>
	<div class="crm_block_container dealings" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['CONTACT_LIST_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_CONTACT_LIST'))?></div>
			<div class="crm_dealings_count"><?=$entity['CONTACT_QUANTITY']?></div>
			<div class="clb"></div>
		</div>
	</div>
	<div class="crm_block_container comments" onclick="BX.CrmMobileContext.redirect({ url: '<?=CUtil::JSEscape($entity['EVENT_LIST_URL'])?>' });">
		<div class="crm_block-aqua-container">
			<div class="crm_block_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_EVENT_LIST'))?></div>
			<!--<div class="crm_dealings_count"></div>-->
			<div class="clb"></div>
		</div>
	</div>
	<?$phoneHtml = __CrmMobileCompanyViewRenderMultiFields('PHONE', $entity['FM'], $typeInfos);
	$emailHtml = __CrmMobileCompanyViewRenderMultiFields('EMAIL', $entity['FM'], $typeInfos);
	$webHtml = __CrmMobileCompanyViewRenderMultiFields('WEB', $entity['FM'], $typeInfos);
	$imHtml = __CrmMobileCompanyViewRenderMultiFields('IM', $entity['FM'], $typeInfos);
	if($hasAddress || $hasAddressLegal || $hasBankDetails || $phoneHtml !== '' || $emailHtml !== '' || $webHtml !== '' || $imHtml !== ''):?>
	<div class="crm_block_container">
		<div class="crm_contact_info">
			<table><tbody>
				<?if($phoneHtml !== ''):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_PHONE'))?>:</td>
						<td><?=$phoneHtml?></td>
					</tr>
				<?endif;?>
				<?if($emailHtml !== ''):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_EMAIL'))?>:</td>
						<td><?=$emailHtml?></td>
					</tr>
				<?endif;?>
				<?if($webHtml !== ''):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_WEB'))?>:</td>
						<td><?=$webHtml?></td>
					</tr>
				<?endif;?>
				<?if($imHtml !== ''):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_IM'))?>:</td>
						<td><?=$imHtml?></td>
					</tr>
				<?endif;?>
				<?if($hasAddress):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_ADDRESS'))?>:</td>
						<td>
							<span><?=htmlspecialcharsbx($address)?></span><?if($addressCut !== ''):?><a class="tdn" href="#" onclick="this.style.display = 'none'; BX.findNextSibling(this, { tagName: 'SPAN' }).style.display = ''; return false;"> <?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_COMMENT_CUT'))?></a><span style="display:none;"><?=htmlspecialcharsbx($addressCut)?></span><?endif;?>
						</td>
					</tr>
				<?endif;?>
				<?if($hasAddressLegal):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_ADDRESS_LEGAL'))?>:</td>
						<td>
							<span><?=htmlspecialcharsbx($addressLegal)?></span><?if($addressLegalCut !== ''):?><a class="tdn" href="#" onclick="this.style.display = 'none'; BX.findNextSibling(this, { tagName: 'SPAN' }).style.display = ''; return false;"> <?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_COMMENT_CUT'))?></a><span style="display:none;"><?=htmlspecialcharsbx($addressLegalCut)?></span><?endif;?>
						</td>
					</tr>
				<?endif;?>
				<?if($hasBankDetails):?>
					<tr>
						<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_BANKING_DETAILS'))?>:</td>
						<td>
							<span><?=htmlspecialcharsbx($bankDetails)?></span><?if($bankDetailsCut !== ''):?><a class="tdn" href="#" onclick="this.style.display = 'none'; BX.findNextSibling(this, { tagName: 'SPAN' }).style.display = ''; return false;"> <?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_COMMENT_CUT'))?></a><span style="display:none;"><?=htmlspecialcharsbx($bankDetailsCut)?></span><?endif;?>
						</td>
					</tr>
				<?endif;?>
			</tbody></table>
		</div>
	</div>
	<?endif;?>
	<div class="crm_block_container">
		<div class="crm_contact_info">
			<?$hasType = isset($entity['COMPANY_TYPE_NAME']) && $entity['COMPANY_TYPE_NAME'] !== '';
			$hasIndustry = isset($entity['INDUSTRY_NAME']) && $entity['INDUSTRY_NAME'] !== '';
			$hasRevenue = isset($entity['FORMATTED_REVENUE']) && $entity['FORMATTED_REVENUE'] !== '';
			$hasEmploees = isset($entity['EMPLOYEES_NAME']) && $entity['EMPLOYEES_NAME'] !== '';?>
			<?if($hasType || $hasIndustry || $hasRevenue || $hasEmploees):?>
			<table><tbody>
				<?if($hasType):?>
				<tr>
					<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_TYPE'))?>:</td>
					<td><?=$entity['COMPANY_TYPE_NAME']?></td>
				</tr>
				<?endif;?>
				<?if($hasIndustry):?>
				<tr>
					<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_INDUSTRY'))?>:</td>
					<td><?=$entity['INDUSTRY_NAME']?></td>
				</tr>
				<?endif;?>
				<?if($hasRevenue):?>
				<tr>
					<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_REVENUE'))?>:</td>
					<td><?=$entity['FORMATTED_REVENUE']?></td>
				</tr>
				<?endif;?>
				<?if($hasEmploees):?>
				<tr>
					<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_EMPLOYEES'))?>:</td>
					<td><?=$entity['EMPLOYEES_NAME']?></td>
				</tr>
				<?endif;?>
			</tbody></table>
			<hr/>
			<?endif;?>
			<table><tbody>
				<tr>
					<td class="crm_vat"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_RESPONSIBLE'))?>:</td>
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
			</tbody></table>
			<?if($comment !== ''):?>
			<hr/>
			<div class="crm_block_content">
				<div class="crm_block_content_title"><?=htmlspecialcharsbx(GetMessage('M_CRM_COMPANY_VIEW_COMMENT'))?>:</div>
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
					pullText: '<?=GetMessageJS('M_CRM_COMPANY_VIEW_PULL_TEXT')?>',
					downText: '<?=GetMessageJS('M_CRM_COMPANY_VIEW_DOWN_TEXT')?>',
					loadText: '<?=GetMessageJS('M_CRM_COMPANY_VIEW_LOAD_TEXT')?>'
				}
			);
		}
	);
</script>
