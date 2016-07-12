<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!$this->__component->__parent || $this->__component->__parent->__name != "bitrix:webdav"):
	$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/components/bitrix/webdav/templates/.default/style.css');
	$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/components/bitrix/webdav/templates/.default/script.js"></script>', true);
endif;
CAjax::Init(); 
CUtil::InitJSCore(array(/*'ajax', */'window')); 
$GLOBALS['APPLICATION']->AddHeadString('<script src="/bitrix/js/main/utils.js"></script>', true);
/********************************************************************
				Input params
********************************************************************/
$arParams["USE_SEARCH"] = ($arParams["USE_SEARCH"] == "Y" && IsModuleInstalled("search") ? "Y" : "N");
$arParams["SHOW_WEBDAV"] = ($arParams["SHOW_WEBDAV"] == "N" ? "N" : "Y");
$res = strtolower($_SERVER["HTTP_USER_AGENT"]); 
$bIsIE = (strpos($res, "opera") === false && strpos($res, "msie") !== false); 
$bIsFF = (strpos($res, "firefox") !== false); 
$ob = $arParams['OBJECT'];
$bInTrash = ($ob->meta_state == 'TRASH');
/********************************************************************
				/Input params
********************************************************************/
$bBitrix24Tpl = function_exists('BX24ShowPanel');
$arButtons = array(); 
$arSubButtons = array();
if (strpos($arParams["PAGE_NAME"], "WEBDAV_BIZPROC_WORKFLOW") !== false)
{
	if ($arParams["USE_BIZPROC"] == "Y" && $ob->CheckRight($arParams["PERMISSION"], 'element_edit') >= "W" && IsModuleInstalled("bizprocdesigner"))
	{
		$arButtons[] = array(
			"TEXT" => GetMessage("BPATT_HELP1"),
			"TITLE" => GetMessage("BPATT_HELP1_TEXT"),
			"LINK" => $arResult["URL"]["WEBDAV_BIZPROC_WORKFLOW_EDIT"].(strpos($arResult["URL"]["WEBDAV_BIZPROC_WORKFLOW_EDIT"], "?") === false ? "?" : "&").
				"init=statemachine",
			"ICON" => "btn-list"); 
		$arButtons[] = array(
			"TEXT" => GetMessage("BPATT_HELP2"),
			"TITLE" => GetMessage("BPATT_HELP2_TEXT"),
			"LINK" => $arResult["URL"]["WEBDAV_BIZPROC_WORKFLOW_EDIT"].(strpos($arResult["URL"]["WEBDAV_BIZPROC_WORKFLOW_EDIT"], "?") === false ? "?" : ""),
			"ICON" => "btn-list"); 
	}
}
elseif ($arParams["PAGE_NAME"] == "SECTIONS")
{
	if (
		$ob->CheckRight($arParams["PERMISSION"], 'section_element_bind') >= "U"
		&& !(
			$arParams["OBJECT"]->workflow == 'workflow'
			&& !$arParams["OBJECT"]->permission_wf_edit)
		)
	{
		if (!$bInTrash)
		{
			if ($arParams["SHOW_CREATE_ELEMENT"] != "N")
			{
				if ($arParams["SHOW_WEBDAV"] == "Y" && $bIsIE)
				{
					$arButtons[] = array(
						"TEXT" => GetMessage("WD_ELEMENT_ADD"),
						"TITLE" => GetMessage("WD_ELEMENT_ADD_ALT"),
						"LINK" => "javascript:WDAddElement('".CUtil::JSEscape($arResult["URL"]["ELEMENT"]["ADD"])."');",
						"ICON" => "btn-new element-add"); 
				}

				$urlParams = array("use_light_view" => "Y");
				if ($arResult['BP_PARAM_REQUIRED'] == 'Y')
					$urlParams['bp_param_required'] = 'Y';

				$arButtons[] = array(
					"TEXT" => GetMessage("WD_UPLOAD"),
					"TITLE" => ($arParams["SECTION_ID"] > 0 ? GetMessage("WD_UPLOAD_ALT") : GetMessage("WD_UPLOAD_ROOT_ALT")),
					"LINK" => "javascript:".$APPLICATION->GetPopupLink(
						Array(
							"URL"=> WDAddPageParams(
								$arResult["URL"]["ELEMENT"]["UPLOAD"],
								$urlParams,
								false),
							"PARAMS" => Array("width" => 600, "height" => 200)
						)
					),
					"ICON" => "btn-new element-upload"
				);
			}

			if ($ob->CheckRight($arParams["PERMISSION"],"section_section_bind") >= "W" && $arParams["CHECK_CREATOR"] != "Y")
			{
				$arButtons[] = array(
					"TEXT" => GetMessage("WD_SECTION_ADD"),
					"TITLE" => GetMessage("WD_SECTION_ADD_ALT"),
					"LINK" => "javascript:".$APPLICATION->GetPopupLink(
						Array(
							"URL"=> WDAddPageParams(
								WDAddPageParams($arResult["URL"]["SECTION"]["~POPUP_ADD"], array('bxpublic'=>'Y')), 
								array("use_light_view" => "Y"), 
								false),
							"PARAMS" => Array("width" => 450, "height" => (($arParams["OBJECT"]->Type == "folder")?160:60), "content_url" => $arResult["URL"]["SECTION"]["~POPUP_ADD"])
						)
					), 
					"ICON" => "btn-new section-add"
				); 
			}

			if ($ob->CheckRight($arParams["PERMISSION"], "section_edit") >= "W")
			{
				$arButtons[] = array(
					"TEXT" => GetMessage("WD_TRASH"),
					"TITLE" => GetMessage("WD_TRASH"),
					"LINK" => $arParams["OBJECT"]->base_url . '/'. $arParams["OBJECT"]->meta_names["TRASH"]["alias"],
					"ICON" => "btn-new ". ($arParams["OBJECT"]->IsTrashEmpty() ? "trash-go": "trash-go-full") ); 
			}
		} else {
			if ($ob->CheckRight($arParams["PERMISSION"], "iblock_edit") >= "X")
			{
				$url = WDAddPageParams(str_replace("use_light_view=Y","",$arResult["URL"]["SECTION"]["EMPTY_TRASH"]), array("sessid" => bitrix_sessid(), "edit_section"=>"Y"));
				$arButtons[] = array(
					"TEXT" => GetMessage("WD_CLEAN_TRASH"),
					"TITLE" => GetMessage("WD_CLEAN_TRASH"),
					"LINK" => "javascript:WDConfirm('".CUtil::JSEscape(GetMessage("WD_CLEAN_TRASH"))."', '".CUtil::JSEscape(GetMessage("WD_CONFIRM_CLEAN_TRASH"))."', function() {jsUtils.Redirect({}, '".$url."');} );",
					"ICON" => "btn-new trash-clean"); 
			}
		}
	}
	if ($arParams["SHOW_WEBDAV"] == "Y" && !$bInTrash /*&& $bIsIE*/)
	{
		$mapShow = true;
		if ($ob->e_rights && (!$ob->GetPermission('SECTION', $ob->arParams['item_id'], 'section_read')))
			$mapShow = false;
		if ($mapShow)
		{
			$arBtnMount = array(
				"TEXT" => GetMessage("WD_MAPING"),
				"TITLE" => GetMessage("WD_MAPING_ALT"),
				"LINK" => "javascript:".$APPLICATION->GetPopupLink(
					Array(
						"URL"=> WDAddPageParams(
							$arResult["URL"]["CONNECTOR"], 
							array("use_light_view" => "Y"), 
							false),
						//"PARAMS" => Array("width" => 450, "height" => 200)
					)
				),
				"ICON" => "btn-list mapping"
			); 

			if ($bBitrix24Tpl)
				$arSubButtons[] = $arBtnMount;
			else
				$arButtons[] = $arBtnMount;
		}
	}

	$arBtnHelp = array(
		"TEXT" => GetMessage("WD_HELP"),
		"TITLE" => GetMessage("WD_HELP_ALT"),
		"LINK" => $arResult["URL"]["HELP"],
		"ICON" => "btn-list help"
	);
	if ($bBitrix24Tpl)
		$arSubButtons[] = $arBtnHelp;
	else
		$arButtons[] = $arBtnHelp;


	if ($bIsFF) {
		$arSubButtons[] = array(
			"TEXT" => GetMessage("WD_MENU_FF_EXTENSION_TEXT"),
			"TITLE" => GetMessage("WD_MENU_FF_EXTENSION_TITLE"),
			"LINK" => "javascript:FFWDExtDialog()",
			"ICON" => "btn-list help"); 
	}

	if ($arParams["USE_BIZPROC"] == "Y" && $ob->CheckRight($arParams["PERMISSION"], "iblock_edit") > "U" && $arParams["CHECK_CREATOR"] != "Y")
	{
		$arSubButtons[] = array(
			"TEXT" => GetMessage("WD_BP"),
			"TITLE" => GetMessage("WD_BP"),
			"LINK" => $arResult["URL"]["WEBDAV_BIZPROC_WORKFLOW_ADMIN"],
			"ICON" => "btn-list bizproc"); 
	}
	if ($this->__component->__parent)
	{
		// if no filter (and go-back) button exists
		if (!(isset($this->__component->__parent->arResult["arButtons"]) && 
			isset($this->__component->__parent->arResult["arButtons"][0]['PREORDER']) &&
			$this->__component->__parent->arResult["arButtons"])) 
		{
			$link = false;
			$ob = $arParams['OBJECT'];
			if ($ob->arParams['not_found'] == false && $ob->_path != '/')
				$link = $ob->base_url.$ob->_get_path($ob->arParams['parent_id']);
			if ($link)
			{
				array_unshift($arButtons, array(
					"TEXT" => GetMessage("WD_GO_BACK"),
					"TITLE" => GetMessage("WD_GO_BACK_ALT"),
					"LINK" => $link,
					"ICON" => "btn-list go-back"));
			}
		}
	}
}
else
{
	if ($this->__component->__parent)
	{
		$bElmInTrash = false;
		if (
			isset($arParams['ELEMENT_ID']) 
			&& intval($arParams['ELEMENT_ID']) > 0
		)
		{
			$oElement = $ob->GetObject(array('element_id' => intval($arParams['ELEMENT_ID'])));
			$bElmInTrash = $ob->InTrash($oElement);
		}

		$link = false;
		$arChain = $GLOBALS['APPLICATION']->arAdditionalChain;

		if ($bElmInTrash)
		{
			$link = $ob->base_url . '/' . $ob->MetaNamesReverse('.Trash', 'name', 'alias');
		}
		elseif (sizeof($arChain) > 1)
		{
			$lastChain = array_pop($arChain);
			if (!empty($lastChain)) $lastChain = array_pop($arChain);
			if (!empty($lastChain)) $link = $lastChain['LINK'];
		} 
		elseif (sizeof($arChain) > 0)
		{
			$link = $ob->base_url;
		}

		if ($link)
		{
			array_unshift($arButtons, array(
				"TEXT" => GetMessage("WD_GO_BACK"),
				"TITLE" => GetMessage("WD_GO_BACK_ALT"),
				"LINK" => $link,
				"ICON" => "btn-list go-back"));
		}
	}
}

if (empty($arButtons))
	$arButtons = array();

if ($this->__component->__parent && is_array($this->__component->__parent->arResult["arButtons"]))
{
	foreach ($this->__component->__parent->arResult["arButtons"] as $arButton)
	{
		if (isset($arButton["PREORDER"]) && $arButton["PREORDER"])
			array_unshift($arButtons, $arButton);
		else
			$arButtons[] = $arButton;
	}
}

foreach($arButtons as $buttonID => $arButton)
{
	if (strpos($arButton['ICON'], 'settings') !== false)
	{
		$arSubButtons[] = $arButton;
		unset($arButtons[$buttonID]);
	}
}

if (
	(sizeof($arButtons) < 4)
	&& (sizeof($arSubButtons) > 0)
)
{
	for ($i=sizeof($arButtons); $i<4; $i++)
	{
		if (sizeof($arSubButtons) > 0)
			$arButtons[] = array_shift($arSubButtons);
	}
}

if (sizeof($arSubButtons) > 0)
{
	$arButtons[] = array("NEWBAR" => true);
	$arButtons = array_merge($arButtons, $arSubButtons);
}

if ($arParams["SHOW_WEBDAV"] == "Y"):
?>
<script>
if (document.attachEvent && navigator.userAgent.toLowerCase().indexOf('opera') == -1)
{
	if (document.getElementById('wd_create_in_ie'))
		document.getElementById('wd_create_in_ie').style.display = '';
	if (document.getElementById('wd_create_in_ie_separator'))
		document.getElementById('wd_create_in_ie_separator').style.display = '';
	if (document.getElementById('wd_map_in_ie'))
		document.getElementById('wd_map_in_ie').style.display = '';
	if (document.getElementById('wd_map_in_ie_separator'))
		document.getElementById('wd_map_in_ie_separator').style.display = '';
}
function WDMappingDrive(path)
{
	if (!jsUtils.IsIE())
	{
		return false;
	}
	if (!path || path.length <= 0)
	{
		alert('<?=GetMessageJS("WD_EMPTY_PATH")?>');
		return false;
	}

	var sizer = false;
	var text = '';
	var src = "";
	sizer = window.open("",'',"height=600,width=800,top=0,left=0");

	text = '<HTML><BODY>' +
			'<SPAN ID="oWebFolder" style="BEHAVIOR:url(#default#httpFolder)">' +
				'<?=CUtil::JSEscape(str_replace("#BASE_URL#", str_replace(":443", "", $arParams["BASE_URL"]), GetMessage("WD_HELP_TEXT")))?>' +
			'</SPAN>' +
		'<script>' +
			'var res = oWebFolder.navigate(\'' + path + '\');' +
		'<' + '/' + 'script' + '>' +
		'</BODY></HTML>';
	sizer.document.write(text);
}
function FFWDExtDialog()
{
	(new BXFFDocLink()).ShowDialog();
	try
	{
		return BX.PreventDefault();
	} catch (err) {}
}
</script>
<?
endif;
?>
<script>
if (typeof oText != "object")
	var oText = {};
oText['error_create_1'] = '<?=CUtil::JSEscape(GetMessage("WD_ERROR_1"))?>';
oText['error_create_2'] = '<?=CUtil::JSEscape(GetMessage("WD_ERROR_2"))?>';
oText['message01'] = '<?=CUtil::JSEscape(GetMessage("WD_DELETE_CONFIRM"))?>';
oText['delete_title'] = '<?=CUtil::JSEscape(GetMessage("WD_DELETE_TITLE"))?>';
oText['yes'] = '<?=CUtil::JSEscape(GetMessage("WD_Y"))?>';
oText['no'] = '<?=CUtil::JSEscape(GetMessage("WD_N"))?>';
oText['ff_extension_update'] = '<?=CUtil::JSEscape(GetMessage("WD_FF_EXTENSION_UPDATE", array("#NAME#" => GetMessage("WD_FF_EXTENSION_NAME") )))?>';
oText['ff_extension_install'] = '<?=CUtil::JSEscape(GetMessage("WD_FF_EXTENSION_INSTALL", array("#NAME#" => GetMessage("WD_FF_EXTENSION_NAME") )))?>';
oText['ff_extension_title'] = '<?=CUtil::JSEscape(GetMessage("WD_FF_EXTENSION_TITLE"))?>';
oText['ff_extension_help'] = '<?=CUtil::JSEscape(GetMessage("WD_FF_EXTENSION_HELP"))?>';
oText['ff_extension_disable'] = '<?=CUtil::JSEscape(GetMessage("WD_FF_EXTENSION_DISABLE"))?>';
oText['wd_install'] = '<?=CUtil::JSEscape(GetMessage("WD_BTN_INSTALL"))?>';
oText['wd_update'] = '<?=CUtil::JSEscape(GetMessage("WD_BTN_UPDATE"))?>';
oText['wd_open'] = '<?=CUtil::JSEscape(GetMessage("WD_BTN_OPEN"))?>';
oText['wd_edit_in'] = '<?=CUtil::JSEscape(GetMessage("WD_MENU_EDIT_IN"))?>';
oText['wd_edit_in_other'] = '<?=CUtil::JSEscape(GetMessage("WD_MENU_EDIT_IN_OTHER"))?>';
oText['wd_install_cancel'] = '<?=CUtil::JSEscape(GetMessage("WD_BTN_INSTALL_CANCEL"))?>';
<? if ($bIsFF) {
	$arUserOptions = CUserOptions::GetOption('webdav', 'suggest', array('ff_extension' => true));
	if ($arUserOptions['ff_extension'] === true) {
?>
window.suggest_ff_extension = true;
<? }} ?>
</script><?
if (!empty($arButtons))
{
	$APPLICATION->IncludeComponent(
		"bitrix:main.interface.toolbar",
		"",
		array(
			"BUTTONS" => $arButtons
		),
		($this->__component->__parent ? $this->__component->__parent : $component)
	);
}
?>
