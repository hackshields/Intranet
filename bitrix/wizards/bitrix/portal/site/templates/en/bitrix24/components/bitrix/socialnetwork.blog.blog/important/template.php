<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
$uid = "sbbw".rand(100, 100000);
$controller = "BX('blog-".$uid."')";
$arParams["OPTIONS"] = (is_array($arParams["OPTIONS"]) ? $arParams["OPTIONS"] : array());
$arRes = array("data" => array(),
		"page_settings" => array(
			"NavPageCount" => $arResult["NAV_RESULT"]->NavPageCount,
			"NavPageNomer" => $arResult["NAV_RESULT"]->NavPageNomer,
			"NavPageSize" => $arResult["NAV_RESULT"]->NavPageSize,
			"NavRecordCount" => $arResult["NAV_RESULT"]->NavRecordCount,
			"bDescPageNumbering" => $arResult["NAV_RESULT"]->bDescPageNumbering,
			"nPageSize" => $arResult["NAV_RESULT"]->NavPageSize)
	);
foreach($arResult["POST"] as $id => $res)
{
	$res = array(
		"id" => $res["ID"],
		"post_text" => (!empty($res["CLEAR_TEXT"]) ? $res["CLEAR_TEXT"] : substr($res["TITLE"], 0, 60)),
		"post_url" => $res["urlToPost"],
		"author_name" => $res["AUTHOR_NAME"],
		"author_avatar_style" => (!empty($res["AUTHOR_AVATAR"]["src"]) ? "url('".$res["AUTHOR_AVATAR"]["src"]."') no-repeat center;" : ""),
		"author_avatar" => (!empty($res["AUTHOR_AVATAR"]["src"]) ? "style=\"background:url('".$res["AUTHOR_AVATAR"]["src"]."') no-repeat center;\"" : ""),
		"author_url" => $res["urlToAuthor"]
	);

	$arRes["data"][] = $res;
}
if ($_REQUEST["AJAX_POST"] == "Y")
{
	$APPLICATION->RestartBuffer();
	echo CUtil::PhpToJSObject($arRes);
	die();
}
$arUser = (is_array($arResult["USER"]) ? $arResult["USER"] : array());
$btnTitle = GetMessage("SBB_READ_".$arUser["PERSONAL_GENDER"]);
$btnTitle = (!empty($btnTitle) ? $btnTitle : GetMessage("SBB_READ_"));
$res = reset($arRes["data"]);
$this->SetViewTarget("sidebar", 80);
?>
<div class="sidebar-widget sidebar-imp-messages" id="blog-<?=$uid?>"<?if(empty($arRes["data"])){?> style="display:none;"<?}?>>
	<div class="sidebar-imp-mess-top"><?=GetMessage("SBB_IMPORTANT")?></div>
	<div class="sidebar-imp-mess-tmp-wrap">
		<div class="sidebar-imp-mess-tmp">
			<div class="sidebar-imp-mess">
				<div class="sidebar-imp-mess-templates" style="display:none;">
					<div class="user-avatar sidebar-user-avatar" data-bx-author-avatar="true" __author_avatar__></div>
					<a href="__author_url__" class="sidebar-imp-mess-title">__author_name__</a>
					<a href="__post_url__" class="sidebar-imp-mess-text">__post_text__</a>
				</div>
				<div class="sidebar-imp-mess-wrap">
					<div class="user-avatar sidebar-user-avatar"<?if($res["author_avatar"]!==""){?> <?=$res["author_avatar"]?><?}?>></div>
					<a href="<?=$res["author_url"]?>" class="sidebar-imp-mess-title"><?=$res["author_name"]?></a>
					<a href="<?=$res["post_url"]?>" class="sidebar-imp-mess-text"><?=$res["post_text"]?></a>
				</div>
				<div class="sidebar-imp-mess-bottom">
					<span class="sidebar-imp-mess-btn"><?=$btnTitle?></span>
					<div class="sidebar-imp-mess-nav-block">
						<span class="sidebar-imp-mess-nav-arrow-l" id="blog-<?=$uid?>-right"></span>
						<span class="sidebar-imp-mess-nav-arrow-r" id="blog-<?=$uid?>-left"></span>
						<span class="sidebar-imp-mess-nav-current-page">1</span><?
							?><span class="sidebar-imp-mess-nav-separator">/</span><?
						?><span class="sidebar-imp-mess-nav-total-page"><?=$arResult["NAV_RESULT"]->NavRecordCount?></span>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
<script>
if (!!<?=$controller?> && ! <?=$controller?>.loaded)
{
	BX.loadScript(
		[
			'<?=CUtil::GetAdditionalFileURL('/bitrix/templates/bitrix24/components/bitrix/socialnetwork.blog.blog/important/script.js')?>',
			'/bitrix/js/main/core/core_ajax.js',
			'/bitrix/js/main/core/core_dd.js',
			'/bitrix/js/main/core/core_popup.js'
		], function() {
			if (!!<?=$controller?>)
			{
				<?=$controller?>.loaded = true;
				new BSBBW({
					'CID' : '<?=$uid?>',
					'controller': <?=$controller?>,
					'options' : <?=CUtil::PhpToJSObject($arParams["OPTIONS"])?>,
					'post_info' : {'template' : '<?=$this->__name?>', 'filter' : <?=CUtil::PhpToJSObject($arParams["FILTER"])?>},
					'page_settings' : <?=CUtil::PhpToJSObject($arRes["page_settings"])?>,
					'nodes' : {
						'btn' : BX.findChild(<?=$controller?>, {'className' : 'sidebar-imp-mess-btn'}, true),
						'left' : BX("blog-<?=$uid?>-left"),
						'right' : BX("blog-<?=$uid?>-right"),
						'total' : BX.findChild(<?=$controller?>, {'className' : 'sidebar-imp-mess-nav-total-page'}, true),
						'counter' : BX.findChild(<?=$controller?>, {'className' : 'sidebar-imp-mess-nav-current-page'}, true),
						'text' : BX.findChild(<?=$controller?>, {'className' : 'sidebar-imp-mess-wrap'}, true),
						'template' : BX.findChild(<?=$controller?>, {'className' : 'sidebar-imp-mess-templates'}, true)
					},
					'data' : <?=CUtil::PhpToJSObject($arRes["data"])?>,
					'url' : '<?=CUtil::JSEscape($arResult["urlToPosts"])?>'
				});
			}
		}
	);
}
</script>
<?
$this->EndViewTarget();
?>