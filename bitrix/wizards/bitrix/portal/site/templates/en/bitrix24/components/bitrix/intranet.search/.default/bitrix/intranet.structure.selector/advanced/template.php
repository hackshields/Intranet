<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
$formName = 'FILTER_'.$arParams['FILTER_NAME'].'_adv';
?>
<form method="POST" name="<?=$formName?>" action="<?=$arParams['LIST_URL_SEARCH']?>">
	<input type="hidden" name="current_filter" value="adv" />
<?if ($arResult['FILTER_VALUES'][$arParams['FILTER_NAME'].'_LAST_NAME']):?>
	<input type="hidden" name="<?=$arParams['FILTER_NAME']?>_LAST_NAME" value="<?=htmlspecialcharsbx($arResult['FILTER_VALUES'][$arParams['FILTER_NAME'].'_LAST_NAME'])?>" />
<?endif;?>
	<input class="employee-input" type="text" id="user-fio" name="<?=$arParams['FILTER_NAME']?>_FIO" value="<?=$arResult['FILTER_VALUES'][$arParams['FILTER_NAME'].'_FIO']?>" />
	<input type="hidden" name="set_filter_<?=$arParams['FILTER_NAME']?>" value="Y" /> 
<?if (strlen($GLOBALS[$arParams['FILTER_NAME'].'_FIO']) > 0):?>
	<i class="employee-search-wrap-cancel" onclick="document.location.href = document.location.href;"></i>
<?else:?>
	<i class="employee-search-wrap-loupe" onclick="var form = BX(<?='FILTER_'.$arParams['FILTER_NAME'].'_adv'?>); BX.submit(form);"></i>
<?endif;?>
</form>
