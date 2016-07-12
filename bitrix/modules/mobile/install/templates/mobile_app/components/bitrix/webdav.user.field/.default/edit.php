<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$addClass = ((strpos($_SERVER['HTTP_USER_AGENT'], 'Mac OS') !== false) ? 'wduf-filemacos' : '');

$sValues = '[]';
$arValue = $arParams['PARAMS']['arUserField']['VALUE'];
if (is_array($arValue) && sizeof($arValue) > 0)
	$sValues = 'BX.findChildren(BX("wduf-selectdialog-'.$arResult['UID'].'"), {"className" : "wd-inline-file"}, true)';
?>

		<div id="wduf-selectdialog-<?=$arResult['UID']?>" class="wduf-selectdialog">
			<div class="wduf-extended">
				<span class="wduf-label"><?=GetMessage('WDUF_FILES')?></span>
				<div class="wduf-placeholder">
					<table class="files-list" cellspacing="0">
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
			<input type="hidden" name="<?=htmlspecialcharsbx($arResult['controlName'])?>" value="" />
				<div class="wduf-selector">
					<?=GetMessage('WDUF_DROPHERE');?><br />
					<span class="wduf-uploader">
						<span class="wduf-but-text"><?=GetMessage('WDUF_SELECT_EXIST');?></span>
						<input class="wduf-fileUploader <?=$addClass?>" type="file" multiple='multiple' size='1' />
					</span>
					<div class="wduf-load-img"></div>
				</div>
				<div class="wduf-label2">
					<a href="javascript:void(0);" class='wduf-selector-link'><?=GetMessage('WD_SELECT_FILE_LINK');?></a>
				</div>
			</div>
			<div class="wduf-simple">
				<span class="wduf-label"><?=GetMessage('WDUF_FILES')?></span>
				<div class="wduf-placeholder">
					<table class="files-list" cellspacing="0">
						<tbody class="wduf-placeholder-tbody">
<?
foreach ($arResult['ELEMENTS'] as $arElement)
{
?>
							<tr class="wd-inline-file" id="wd-doc<?=intval($arElement['ID'])?>">
								<td class="files-name">
									<span class="files-text">
										<span class="f-wrap"><?=htmlspecialcharsEx($arElement['NAME'])?></span>
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
}
?>
						</tbody>
					</table>
				</div>
				<div class="wduf-selector">
					<span class="wduf-uploader">
						<span class="wduf-uploader-left"></span><span class="wduf-but-text"><?=GetMessage('WDUF_SELECT_LOCAL');?></span><span class="wduf-uploader-right"></span>
						<input class="wduf-fileUploader <?=$addClass?>" type="file" multiple='multiple' size='1' />
					</span>
				</div>
				<div class="wduf-label2">
					<a href="javascript:void(0);" class='wduf-selector-link'><?=GetMessage('WD_SELECT_FILE_LINK');?></a>
				</div>
			</div>
			<script>
<?			foreach ($arResult['JSON'] as $json) { ?>
				BX.ajax.loadJSON('<?=CUtil::JSEscape($json)?>', BX.DoNothing);
<?			} ?>
				BX.loadCSS('/bitrix/components/bitrix/webdav/templates/.default/style.css');
				BX.addCustomEvent('WDSelectFileDialogLoaded', function(wdFD) {
					wdFD.LoadDialogs('DropInterface');
				});
<?
				$obDavEventHandler = CWebDavSocNetEvent::GetRuntime();
				$selectUrl = '';
				$uploadUrl = '';
				$showUrl = '';
				if (isset($obDavEventHandler->arPath['PATH_TO_FILES']))
					$selectUrl = $obDavEventHandler->arPath['PATH_TO_FILES'];
				if (isset($obDavEventHandler->arPath['ELEMENT_UPLOAD_URL']))
					$uploadUrl = $obDavEventHandler->arPath['ELEMENT_UPLOAD_URL'];
				if (isset($obDavEventHandler->arPath['ELEMENT_SHOW_INLINE_URL']))
					$showUrl = $obDavEventHandler->arPath['ELEMENT_SHOW_INLINE_URL'];

				$scriptSrc = CUtil::GetAdditionalFileURL('/bitrix/js/webdav/selectfiledialog.js');
?>
				var wdFD = null;
				var wdDisp<?=$arResult['UID']?> = null;
				var WDUFController<?=$arResult['UID']?> = BX('wduf-selectdialog-<?=$arResult['UID']?>');
				var bEmpty = <?=( (strlen($sValues) < 3) ? "true" : "false" )?>;

				var arScripts = [
					'<?=$scriptSrc?>', 
					'/bitrix/js/main/core/core_ajax.js',
					'/bitrix/js/main/core/core_dd.js'
				];

				if (bEmpty)
				{
					if (! BX.browser.IsIE())
					{
						BX.loadScript(arScripts, function() {
							wdDisp<?=$arResult['UID']?> = new WDFileDialogDispatcher(WDUFController<?=$arResult['UID']?>);
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

							if (bEmpty)
							{
								setTimeout(function() {
									BX.fx.show(WDUFController<?=$arResult['UID']?>, 'fade', {time:0.3});
								}, 300);

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
							BX.fx.hide(WDUFController<?=$arResult['UID']?>, 'fade', {time:0.3});
						}
						else
						{
							BX.fx.show(WDUFController<?=$arResult['UID']?>, 'fade', {time:0.3});
						}

					}
				}

				if (! bEmpty)
					OnWDLoadFormController();

			</script>
			<?  CJSCore::Init(array('wdfiledialog')); ?>
		</div>
