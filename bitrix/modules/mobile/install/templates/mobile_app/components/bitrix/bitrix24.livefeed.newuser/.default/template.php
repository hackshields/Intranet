<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

?><div class="lenta-new-employee-icon">
	<div class="avatar"<?if($arParams['AVATAR_SRC']):?> style="background:url('<?=$arParams['AVATAR_SRC']?>') 0 0 no-repeat; background-size: 29px 29px;"<?endif?>></div>
</div>
<div class="lenta-info-block-content">
	<div class="lenta-important-block-title"><a href="<?=$arParams['USER_URL']?>"><?=CUser::FormatName($arParams['PARAMS']['NAME_TEMPLATE'], $arParams['USER'])?></a></div>
	<div class="lenta-important-block-text"><?=htmlspecialcharsbx($arParams['USER']['WORK_POSITION'])?></div>
</div>