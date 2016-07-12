<?if(!defined("B_PROLOG_INCLUDED")||B_PROLOG_INCLUDED!==true)die();
$APPLICATION->AddHeadString('<script src="'.CUtil::GetAdditionalFileURL("/bitrix/components/bitrix/mobile.file.upload/templates/.default/script_attached.js").'"></script>', true);

$controlName = $arParams['INPUT_NAME'];
$controlNameFull = $controlName . (($arParams['MULTIPLE'] == 'Y') ? '[]' : '');
?>
<script>
	BX.message({
		MFUControlNameFull: '<?=CUtil::JSEscape($controlNameFull)?>',
		MFULoadingTitle1: '<?=CUtil::JSEscape(GetMessage("MFU_LOADING_TITLE_1"))?>',
		MFULoadingTitle2: '<?=CUtil::JSEscape(GetMessage("MFU_LOADING_TITLE_2"))?>'
	});
</script>
<span id="mfu_file_container">
<?
	if (is_array($_SESSION["MFU_UPLOADED_FILES"]) && count($_SESSION["MFU_UPLOADED_FILES"]))
	{
		foreach($_SESSION["MFU_UPLOADED_FILES"] as $file_id)
		{
			?><input type="hidden" id="mfu_file_id_<?=$file_id?>" name="<?=$controlNameFull?>" value="<?=$file_id?>" /><?
		}
	}
?>
</span>

