<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?IncludeTemplateLangFile(__FILE__);?>

			</div>
		</div>
		<div class="spaceForFooter"></div>
		<div id="footer">
			<div class="footer-content-lowerEnd-wrap"><div class="footer-content-lowerEnd"></div></div>
			<span id="copyright">
				<?if (IsModuleInstalled("bitrix24")):?>
				<span class="bitrix24-copyright"><?=GetMessage("BITRIX24_COPYRIGHT1")?></span>
				<?endif?>
				<a id="bitrix24-logo" href="<?=GetMessage("BITRIX24_URL")?>"></a>
				<span class="bitrix24-copyright"><?=GetMessage("BITRIX24_COPYRIGHT2", array("#CURRENT_YEAR#" => date("Y")))?></span>
			</span>
			<?if (IsModuleInstalled("bitrix24")):
				if ($partnerID = COption::GetOptionString("bitrix24", "partner_id", "")):
					$arParamsPartner["MESS"] = array(
						"BX24_PARTNER_TITLE" => GetMessage("BX24_SITE_PARTNER"),
						"BX24_CLOSE_BUTTON" => GetMessage("BX24_CLOSE_BUTTON"),
						"BX24_LOADING" => GetMessage("BX24_LOADING"),
					);?>
					<a href="javascript:void(0)" onclick="showPartnerForm(<?echo CUtil::PhpToJSObject($arParamsPartner)?>); return false;" class="footer-discuss-link"><?=GetMessage("BITRIX24_PARTNER_CONNECT")?></a><?
				else:
					$arParamsPass["MESS"] = array(
						"BX24_SITE_TITLE" => GetMessage("BX24_SITE_TITLE"),
						"BX24_SITE_BUTTON" => GetMessage("BX24_SITE_BUTTON"),
						"BX24_CLOSE_BUTTON" => GetMessage("BX24_CLOSE_BUTTON"),
						"BX24_LOADING" => GetMessage("BX24_LOADING"),
						"BX24_SITE_PASS_INPUT" => GetMessage("BX24_SITE_PASS_INPUT"),
						"BX24_SITE_URL" => GetMessage("BITRIX24_SSL_URL"),
					);?>
					<a href="javascript:void(0)" onclick="showPassForm(<?echo CUtil::PhpToJSObject($arParamsPass)?>); return false;" class="footer-discuss-link"><?=GetMessage("BITRIX24_REVIEW")?></a><?
				endif?>
			<?elseif (file_exists($_SERVER["DOCUMENT_ROOT"].SITE_DIR."services/help/")):?>
				<a href="<?=SITE_DIR?>services/help/" class="footer-discuss-link"><?=GetMessage("BITRIX24_MENU_HELP")?></a>
			<?endif;?>
		</div>
		<div class="clear"></div>
	</div>
</div>
</body>
</html>