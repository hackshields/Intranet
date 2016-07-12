<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Settings");
$APPLICATION->IncludeComponent(
	'bitrix:crm.control_panel',
	'',
	array(
		'ID' => 'CONFIG',
		'ACTIVE_ITEM_ID' => ''
	),
	$component
);
$CrmPerms = CCrmPerms::GetCurrentUserPermissions();
?>
<ul class="config-CRM">
<?if(!$CrmPerms->HavePerm('CONFIG', BX_CRM_PERM_NONE)):?>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/status/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Selection Lists</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/currency/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Currencies</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/perms/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Access Permissions</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/bp/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Business processes</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/fields/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Custom Fields</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/config/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Email Settings</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/sendsave/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">Send&Save Integration</a></li>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/external_sale/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">e-Stores</a></li>
<?endif?>
<?if($CrmPerms->IsAccessEnabled()):?>
	<li style="color: #3F729C;height: 22px;list-style: none outside none;margin-bottom: 26px;overflow: hidden;">- <a href="<?=SITE_DIR?>crm/configs/mailtemplate/" style="color: #3F729C;font-family: Arial,sans-serif;font-size: 18px;">E-Mail Templates</a></li>
<?endif;?>
</ul>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>