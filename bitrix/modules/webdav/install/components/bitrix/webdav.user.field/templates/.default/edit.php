<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$addClass = ((strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS') !== false) ? 'wduf-filemacos' : '');

$sValues = '[]';
$arValue = $arParams['PARAMS']['arUserField']['VALUE'];
if (is_array($arValue) && sizeof($arValue) > 0)
	$sValues = 'BX.findChildren(BX("wduf-selectdialog-'.$arResult['UID'].'"), {"className" : "wd-inline-file"}, true)';
?>
		<div id="wduf-selectdialog-<?=$arResult['UID']?>" class="wduf-selectdialog">
			<div class="wduf-files-block"<?if(!empty($arResult['ELEMENTS'])){?> style="display:block;"<?}?>>
				<div class="wduf-label">
					<?=GetMessage("WDUF_ATTACHMENTS")?>
					<span class="wduf-label-icon"></span>
				</div>
				<div class="wduf-placeholder">
					<table cellspacing="0" class="files-list">
						<tbody class="wduf-placeholder-tbody">
<?
	if (
		isset($arResult['ELEMENTS'])
		&& !empty($arResult['ELEMENTS'])
	)
	{
		foreach ($arResult['ELEMENTS'] as $arElement) {
?>
							<tr class="wd-inline-file" id="wd-doc<?=intval($arElement['ID'])?>">
								<td class="files-name">
									<span class="files-text">
										<span class="f-wrap"><?=htmlspecialcharsEx($arElement['NAME'])?></span>
<?		if ($arElement['URL_PREVIEW'] != '') { ?>
										<span class="wd-files-icon files-preview-wrap">
											<span class="files-preview-border">
												<span class="files-preview-alignment">
													<img class="files-preview" src="<?=$arElement['URL_PREVIEW']?>" <?
														?> data-bx-full-width="<?=$arElement['FILE']['WIDTH']?>"<?
														?> data-bx-full-height="<?=$arElement['FILE']['HEIGHT']?>"<?
													?> />
												</span>
											</span>
										</span>
<?		} else { ?>
										<span class="wd-files-icon feed-file-icon-<?=GetFileExtension($arElement['NAME'])?>"></span>
<?		}?>
<?		if ($arElement['URL_EDIT'] !== '') { ?>
										<a class="file-edit" href="<?=$arElement['URL_EDIT']?>">edit</a>
<?		} ?>
									</span>
								</td>
								<td class="files-size"><?=$arElement["FILE_SIZE"]?></td>
								<td class="files-storage">
									<div class="files-storage-block">
<?		if ($arElement['DROPPED']) { ?>
										<span class="files-storage-text">
											<?=GetMessage("WD_SAVED_PATH")?>:
										</span>
										<a class="files-path" href="javascript:void(0);"><?=htmlspecialcharsEx($arElement['TITLE'])?></a>
										<span class="edit-stor"></span>
<?		} else { ?>
										<span class="files-placement"><?=htmlspecialcharsEx($arElement['TITLE'])?></span>
<?		} ?>
										<input id="wduf-doc<?=$arElement['ID']?>" type="hidden" name="<?=htmlspecialcharsbx($arResult['controlName'])?>" value="<?=$arElement['ID']?>" />
									</div>
								</td>
							</tr>
<?
		}  // foreach
	} // if
?>
						</tbody>
					</table>
				</div>
			</div>
				<div class="wduf-extended">
			<input type="hidden" name="<?=htmlspecialcharsbx($arResult['controlName'])?>" value="" />
				<table class="wduf-selector-table" cellspacing="0">
					<tr>
						<td class="wduf-selector" onmouseover="BX.addClass(this, 'wduf-selector-hover')"<?
							?> onmouseout="BX.removeClass(this, 'wduf-selector-hover')">
							<div class="wduf-uploader">
								<span class="wduf-uploader-text">
									<span class="wduf-uploader-title">
										<span class="wduf-uploader-title-text"><?=GetMessage("WDUF_SELECT_ATTACHMENTS")?></span>
									</span>
									<span class="wduf-uploader-descript"><?=GetMessage("WDUF_DROP_ATTACHMENTS")?></span>
								</span>
								<input class="wduf-fileUploader <?=$addClass?>" type="file" multiple='multiple' size='1' />
							</div>
						</td>
						<td class="wduf-selector-empty"><span>&nbsp;</span></td>
						<td class="wduf-selector-left">
							<div class="wduf-select-in-portal">
								<span class="wduf-uploader-text">
									<span class="wduf-uploader-title">
										<a href="javascript:void(0);" class='wduf-selector-link'><?=GetMessage('WD_SELECT_FILE_LINK');?></a>
									</span>
									<span class="wduf-uploader-descript"><?=GetMessage("WD_SELECT_FILE_LINK_ALT")?></span>
								</span>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<div class="wduf-simple">
				<table class="wduf-selector-table" cellspacing="0">
					<tr>
						<td class="wduf-selector" onmouseover="BX.addClass(this, 'wduf-selector-hover')"<?
							?> onmouseout="BX.removeClass(this, 'wduf-selector-hover')">
							<div class="wduf-uploader">
								<span class="wduf-uploader-text">
									<span class="wduf-uploader-title">
										<span class="wduf-uploader-title-text"><?=GetMessage("WDUF_SELECT_ATTACHMENTS")?></span>
									</span>
									<span class="wduf-uploader-descript"><?=GetMessage("WDUF_PICKUP_ATTACHMENTS")?></span>
								</span>
								<input class="wduf-fileUploader <?=$addClass?>" type="file" multiple='multiple' size='1' />
							</div>
						</td>
						<td class="wduf-selector-empty"><span>&nbsp;</span></td>
						<td class="wduf-selector-left">
							<div class="wduf-select-in-portal">
								<span class="wduf-uploader-text">
									<span class="wduf-uploader-title">
										<a href="javascript:void(0);" class='wduf-selector-link'><?=GetMessage('WD_SELECT_FILE_LINK');?></a>
									</span>
									<span class="wduf-uploader-descript"><?=GetMessage("WD_SELECT_FILE_LINK_ALT")?></span>
								</span>
							</div>
						</td>
					</tr>
				</table>
			</div>
			<script>
<?			foreach ($arResult['JSON'] as $json) { ?>
				BX.ajax.loadJSON('<?=CUtil::JSEscape($json)?>', BX.DoNothing);
<?			}
				$styleSrc = CUtil::GetAdditionalFileURL('/bitrix/components/bitrix/webdav/templates/.default/style.css');?>
				BX.loadCSS('<?=$styleSrc?>');
				BX.addCustomEvent('WDSelectFileDialogLoaded', function(wdFD) {
					wdFD.LoadDialogs('DropInterface');
				});
<?
				$obDavEventHandler = CWebDavSocNetEvent::GetRuntime();
				$selectUrl = (isset($obDavEventHandler->arPath['PATH_TO_FILES']) ? $obDavEventHandler->arPath['PATH_TO_FILES'] : '');
				$uploadUrl = (isset($obDavEventHandler->arPath['ELEMENT_UPLOAD_URL']) ? $obDavEventHandler->arPath['ELEMENT_UPLOAD_URL'] : "");
				$showUrl = (isset($obDavEventHandler->arPath['ELEMENT_SHOW_INLINE_URL']) ? $obDavEventHandler->arPath['ELEMENT_SHOW_INLINE_URL'] : "");
				$getUrl = (isset($obDavEventHandler->arPath["ELEMENT_HISTORYGET_URL"]) ? $obDavEventHandler->arPath["ELEMENT_HISTORYGET_URL"] : "");

				$scriptSrc = CUtil::GetAdditionalFileURL('/bitrix/js/webdav/selectfiledialog.js');
?>
				var wdFD = null;
				var wdDisp<?=$arResult['UID']?> = null;
				var WDUFController<?=$arResult['UID']?> = BX('wduf-selectdialog-<?=$arResult['UID']?>');
				var bEmpty<?=$arResult['UID']?> = <?=( (strlen($sValues) < 3) ? "true" : "false" )?>;

				var arScripts = [
					'<?=$scriptSrc?>', 
					'/bitrix/js/main/core/core_ajax.js',
					'/bitrix/js/main/core/core_dd.js'
				];

				if (bEmpty<?=$arResult['UID']?>)
				{
					if (!BX.browser.IsIE())
					{
						BX.loadScript(arScripts, function() {
							wdDisp<?=$arResult['UID']?> = new WDFileDialogDispatcher(WDUFController<?=$arResult['UID']?>, '<?= CUtil::JSEscape($arResult['controlName'])?>');
						});

						function WDUnbindDispatcher<?=$arResult['UID']?>()
						{
							BX.onCustomEvent(WDUFController<?=$arResult['UID']?>.parentNode.parentNode, 'UnbindDndDispatcher');
						}
					}

					BX.addCustomEvent(WDUFController<?=$arResult['UID']?>.parentNode, "WDLoadFormController", OnWDLoadFormController);
				}

				function OnWDLoadFormController()
				{
					if (! WDUFController<?=$arResult['UID']?>.loaded)
					{
						BX.loadScript(arScripts, function() {
							WDUFController<?=$arResult['UID']?>.loaded = true;

							var dropbox = new BX.DD.dropFiles();
							var variant = 'simple';
							if (dropbox && dropbox.supported() && BX.ajax.FormData.isSupported())
							{
								variant = 'extended';
							}

							wdFD = new WDFileDialog({
								'urlSelect' : "<?=CUtil::JSEscape($selectUrl)?>",
								'urlUpload' : "<?=CUtil::JSEscape($uploadUrl)?>",
								'urlShow'	: "<?=CUtil::JSEscape($showUrl)?>",
								'urlGet'	: "<?=CUtil::JSEscape($getUrl)?>",
								'controller':  WDUFController<?=$arResult['UID']?>,
								'inputName' : "<?=CUtil::JSEscape($arResult['controlName'])?>",
								'fileInputName' : "SourceFile_1",
								'mode' 		: variant,
								'values'	:  <?=$sValues?>,
								'msg' : {
									'loading' : "<?=CUtil::JSEscape(GetMessage('WD_FILE_LOADING'))?>",
									'file_exists':"<?=CUtil::JSEscape(GetMessage('WD_FILE_EXISTS'))?>",
									'access_denied':"<p style='margin-top:0;'><?=CUtil::JSEscape(GetMessage('WD_ACCESS_DENIED'))?></p>"
								}
							});
							if (bEmpty<?=$arResult['UID']?>)
							{
								BX.fx.show(WDUFController<?=$arResult['UID']?>, 'fade', {time:0.2});

								if (! BX.browser.IsIE())
									WDUnbindDispatcher<?=$arResult['UID']?>();
							}
							else
							{
								BX.show(WDUFController<?=$arResult['UID']?>);
							}
							BX.onCustomEvent('WDSelectFileDialogLoaded', [wdFD]);
						});

					}
					else
					{

						if (WDUFController<?=$arResult['UID']?>.style.display == 'block')
						{
							BX.fx.hide(WDUFController<?=$arResult['UID']?>, 'fade', {time:0.2});
						}
						else
						{
							BX.fx.show(WDUFController<?=$arResult['UID']?>, 'fade', {time:0.2});
						}

					}
				}

				if (! bEmpty<?=$arResult['UID']?>)
					OnWDLoadFormController();
			</script>
			<?  CJSCore::Init(array('wdfiledialog')); ?>
		</div>
