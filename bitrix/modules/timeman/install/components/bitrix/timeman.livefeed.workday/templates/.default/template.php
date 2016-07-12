<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
CJSCore::Init(array('timeman'));
?><div class="feed-workday-table"><?
	?><span class="feed-workday-left-side"><span class="feed-workday-table-text"><?=GetMessage("TIMEMAN_ENTRY_FROM")?>:</span> <span class="feed-workday-avatar"<?=(strlen($arParams["USER"]["PHOTO"]) > 0 ? " style=\"background:url('".$arParams["USER"]["PHOTO"]."') no-repeat center center #FFFFFF\"" : "")?>></span><span class="feed-user-name-wrap"><a href="<?=$arParams['USER']["URL"]?>" class="feed-workday-user-name"><?=$arParams['USER']["NAME"]?></a><span class="feed-workday-user-position"><?=$arParams['USER']["WORK_POSITION"]?></span></span></span><?
	?><span class="feed-workday-right-side"><span class="feed-workday-table-text"><?=GetMessage("TIMEMAN_ENTRY_TO")?>:</span> <span class="feed-workday-avatar"<?=(strlen($arParams["MANAGER"]["PHOTO"]) > 0 ? " style=\"background:url('".$arParams["MANAGER"]["PHOTO"]."') no-repeat center center #FFFFFF\"" : "")?>></span><span class="feed-user-name-wrap"><a href="<?=$arParams['MANAGER']["URL"]?>" class="feed-workday-user-name"><?=$arParams['MANAGER']["NAME"]?></a><span class="feed-workday-user-position"><?=$arParams['MANAGER']["WORK_POSITION"]
?></div><?

/*
if (comments and mark)
{
	?><div class="feed-workday-comments"><span class="feed-workday-com-icon"></span><?=str_replace("#VALUE#", '<span class="feed-post-color-green">'.GetMessage("TIMEMAN_COMMENT_CONFIRM_VALUE").'</span>', GetMessage("TIMEMAN_COMMENT_CONFIRM"))?></div><?
}
*/
?>