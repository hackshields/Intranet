<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
global $APPLICATION;
$APPLICATION->AddHeadString('<script type="text/javascript" src="'.CUtil::GetAdditionalFileURL(SITE_TEMPLATE_PATH.'/crm_mobile.js').'"></script>');
$APPLICATION->SetPageProperty('BodyClass', 'crm-page');

$UID = $arResult['UID'];

?><div id="<?=htmlspecialcharsbx($UID)?>" class="crm_wrapper">
	<div class="crm_head_title tal m0" style="padding: 10px 5px 0;">
		<?=htmlspecialcharsbx($arResult['TITLE'])?>
		<span style="font-size: 13px;color: #87949b;"> <?=htmlspecialcharsbx(GetMessage('M_CRM_PRODUCT_ROW_LIST_LEGEND'))?></span>
	</div>
	<hr style="border-top: 1px solid #a2acb0;" />
	<div class="crm_head_title tal m0" style="padding: 0 5px 10px;">
		<span class="fwn"><?=htmlspecialcharsbx(GetMessage('M_CRM_PRODUCT_ROW_LIST_SUM_TOTAL'))?>:</span>
		<?=htmlspecialcharsbx($arResult['FORMATTED_OPPORTUNITY'])?>
	</div>
	<ul class="crm_company_list">
		<?foreach($arResult['ITEMS'] as &$item):?>
			<li class="crm_company_list_item" style="padding: 7px 7px 13px 13px;">
<!--				<a class="crm_company_img" style="box-shadow:none;">-->
<!--					<span>-->
<!--						<img src="images/edit.png" />-->
<!--					</span>-->
<!--				</a>-->
				<a class="crm_company_title"><?=htmlspecialcharsbx($item['PRODUCT_NAME'])?></a>
				<div class="crm_company_company">
					<?=htmlspecialcharsbx(GetMessage('M_CRM_PRODUCT_ROW_LIST_PRICE'))?>:&nbsp;
					<span class="fwb"><?=htmlspecialcharsbx($item['FORMATTED_PRICE'])?></span>
					<br/>
					<?=htmlspecialcharsbx(GetMessage('M_CRM_PRODUCT_ROW_LIST_QTY'))?>:&nbsp;
					<span class="fwb"><?=$item['QUANTITY']?></span>
				</div>
				<div class="clb"></div>
				<!--<div class="crm_item_del"></div>-->
			</li>
		<?endforeach;?>
		<?unset($item);?>
	</ul>
</div>
<script type="text/javascript">
	BX.ready(
		function()
		{
			BX.CrmMobileContext.getCurrent().enableReloadOnPullDown(
				{
					pullText: '<?= GetMessage('M_CRM_PRODUCT_ROW_LIST_PULL_TEXT')?>',
					downText: '<?= GetMessage('M_CRM_PRODUCT_ROW_LIST_DOWN_TEXT')?>',
					loadText: '<?= GetMessage('M_CRM_PRODUCT_ROW_LIST_LOAD_TEXT')?>'
				}
			);
		}
	);
</script>
