<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

// we shouldn't check any access rights here 
// if(!($USER->CanDoOperation('view_subordinate_users') || $USER->CanDoOperation('view_all_users')))
	// $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//echo __FILE__;
CModule::IncludeModule('intranet');
IncludeModuleLangFile(__FILE__);

if ($_REQUEST['MODE'] == 'EMPLOYEES')
{
	$SECTION_ID = intval($_REQUEST['SECTION_ID']);
	$arFilter = array('ACTIVE' => 'Y', 'UF_DEPARTMENT' => $SECTION_ID);
	/*	
	if(!$USER->CanDoOperation('view_all_users'))
	{
		$arUserSubordinateGroups = array();
		$arUserGroups = CUser::GetUserGroup($USER->GetID());
		foreach($arUserGroups as $grp)
			$arUserSubordinateGroups = array_merge($arUserSubordinateGroups, CGroup::GetSubordinateGroups($grp));

		$arFilter["CHECK_SUBORDINATE"] = array_unique($arUserSubordinateGroups);
	}
	*/
	
	$dbRes = CUser::GetList($by = 'last_name', $order = 'asc', $arFilter);
	$arUsers = array();
	
	while ($arRes = $dbRes->Fetch())
	{
		$arPhoto = array('IMG' => '');
		if ($arRes['PERSONAL_PHOTO'] > 0)
			$arPhoto = CIntranetUtils::InitImage($arRes['PERSONAL_PHOTO'], 50);
	
		$arUsers[] = array(
			'ID' => $arRes['ID'],
			'NAME' => CUser::FormatName('#LAST_NAME# #NAME#', $arRes),
			'WORK_POSITION' => $arRes['WORK_POSITION'] ? $arRes['WORK_POSITION'] : $arRes['PERSONAL_PROFESSION'],
			'PHOTO' => $arPhoto['IMG'],
		);
	}
	
	$APPLICATION->RestartBuffer();
	Header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
?>	
BXShowEmployees('<?echo $SECTION_ID?>', <?echo CUtil::PhpToJsObject($arUsers)?>);
<?
	die();
}

$current_user = 0;
$opened_section = 0;
if (isset($_GET['value']))
{
	$USER_ID = intval($_GET['value']);
	$dbRes = CUser::GetByID($USER_ID);
	if (($arUser = $dbRes->Fetch()) && is_array($arUser['UF_DEPARTMENT']) && count($arUser['UF_DEPARTMENT']) > 0)
	{
		$opened_section = $arUser['UF_DEPARTMENT'][0];
		$current_user = $arUser['ID'];
	}
}

?>
<script>
jsUtils.loadCSSFile('/bitrix/themes/.default/intranet.css');
</script>
<div class="title">
<table cellspacing="0" width="100%">
	<tr>
		<td width="100%" class="title-text" onmousedown="jsFloatDiv.StartDrag(arguments[0], document.getElementById('employee_select_control'));"><?echo GetMessage('INTR_EMP_WINDOW_TITLE')?></td>
		<td width="0%"><a class="close" href="javascript:document.getElementById('employee_select_control').CloseDialog();" title="<?=GetMessage("INTR_EMP_WINDOW_CLOSE")?>"></a></td>
	</tr>
</table>
</div>
<script>
var current_selected = <?echo $current_user?>;
function BXEmployeeSelect()
{
	if (current_selected > 0)
	{
		document.getElementById('bx_employee_' + current_selected).className = 'bx-employee-row';
	}
	else
	{
		document.getElementById('submitbtn').disabled = false;
	}
	
	current_selected = this.BX_ID;
	document.getElementById('bx_employee_' + current_selected).className = 'bx-employee-row bx-emp-selected';
}

function BXEmployeeSet()
{
	if (current_selected > 0)
	{
		document.getElementById('<?echo CUtil::JSEscape($_GET['control_name'])?>').value = current_selected;
		document.getElementById('employee_select_control').CloseDialog();
	}
}

function BXShowEmployees(SECTION_ID, arEmployees)
{
	var obSection = document.getElementById('bx_employee_section_' + SECTION_ID);
	
	if (!obSection.BX_LOADED)
	{
		obSection.BX_LOADED = true;
		
		var obSectionDiv = document.getElementById('bx_employees_' + SECTION_ID);
		if (obSectionDiv)
		{
			obSectionDiv.innerHTML = '';
			for (var i = 0; i < arEmployees.length; i++)
			{
				var obUserRow = document.createElement('DIV');
				obUserRow.id = 'bx_employee_' + arEmployees[i].ID;
				obUserRow.className = 'bx-employee-row';
				
				obUserRow.onclick = BXEmployeeSelect;
				obUserRow.ondblclick = BXEmployeeSet;
				
				obUserRow.BX_ID = arEmployees[i].ID;
				if (obUserRow.BX_ID == current_selected)
					obUserRow.className += ' bx-emp-selected';
				
				obUserRow.innerHTML = '<div class="bx-employee-photo' + (arEmployees[i].PHOTO ? '' : ' bx-no-photo') + '">' + arEmployees[i].PHOTO + '</div><div class="bx-employee-info"><div class="bx-employee-name">' + arEmployees[i].NAME + '</div><div class="bx-employee-position">' + arEmployees[i].WORK_POSITION + '</div></div>';
				obSectionDiv.appendChild(obUserRow);
			}
			
			var obClearer = obSectionDiv.appendChild(document.createElement('DIV'));
			obClearer.style.clear = 'both';
		}
	}
}

function BXLoadEmployees(SECTION_ID)
{
	document.getElementById('_f_popup_content').style.overflow = 'auto';
	
	var obSection = document.getElementById('bx_employee_section_' + SECTION_ID);
	
	if (null == obSection.BX_LOADED)
		jsUtils.loadJSFile('/bitrix/admin/intranet_user_search.php?lang=<?echo LANGUAGE_ID?>&MODE=EMPLOYEES&SECTION_ID=' + parseInt(SECTION_ID));
	
	var obChildren = document.getElementById('bx_children_' + SECTION_ID);
	if (obChildren.style.display == 'block')
	{
		obSection.firstChild.className = obSection.firstChild.className.replace('bx-emp-opened', 'bx-emp-closed');
		obChildren.style.display = 'none';
	}
	else
	{
		obSection.firstChild.className = obSection.firstChild.className.replace('bx-emp-closed', 'bx-emp-opened');
		obChildren.style.display = 'block';
	}
}
</script>
<div class="content" id="_f_popup_content" style="height: 400px; oveflow: scroll !important; padding: 0px;">
<?
	function EmployeeDrawStructure($arStructure, $arSections, $key)
	{
		foreach ($arStructure[$key] as $ID)
		{
			$arRes = $arSections[$ID];
			
			echo '<div class="bx-employee-section'.($key == 0 ? '-first' : '').'" style="padding-left: '.(($arRes['DEPTH_LEVEL']-1)*15).'px" onclick="BXLoadEmployees('.$ID.')" id="bx_employee_section_'.$ID.'">';
			echo '<div class="bx-employee-section-name bx-emp-closed">'.$arRes['NAME'].'</div>';
			echo '</div>';
			
				echo '<div style="display: none" id="bx_children_'.$arRes['ID'].'">';
				if (is_array($arStructure[$ID]))
				{
					EmployeeDrawStructure($arStructure, $arSections, $ID);
				}
				echo '<div class="bx-employees-list" id="bx_employees_'.$ID.'" style="margin-left: '.($arRes['DEPTH_LEVEL']*15).'px"><i>'.GetMessage('INTR_EMP_WAIT').'</i></div>';
				echo '</div>';
			
		}
	}

	$dbRes = CIBlockSection::GetTreeList(array('IBLOCK_ID' => COption::GetOptionInt('intranet', 'iblock_structure')));
	$arStructure = array(0 => array());
	$arSections = array();
	while ($arRes = $dbRes->Fetch())
	{
		if (!$arRes['IBLOCK_SECTION_ID'])
			$arStructure[0][] = $arRes['ID'];
		elseif (!is_array($arStructure[$arRes['IBLOCK_SECTION_ID']]))
			$arStructure[$arRes['IBLOCK_SECTION_ID']] = array($arRes['ID']);
		else
			$arStructure[$arRes['IBLOCK_SECTION_ID']][] = $arRes['ID'];
			
		$arSections[$arRes['ID']] = $arRes;
	}

	EmployeeDrawStructure($arStructure, $arSections, 0);
	
	if ($opened_section > 0)
	{
?>
<script>
<?
		while ($opened_section > 0)
		{
?>
BXLoadEmployees(<?echo $opened_section?>);
<?
			$opened_section = $arSections[$opened_section]['IBLOCK_SECTION_ID'];
		}
?>
</script>
<?
	}
?>
</div>
<div class="buttons">
	<input type="button" id="submitbtn" value="<?echo GetMessage('INTR_EMP_SUBMIT')?>" onclick="BXEmployeeSet();" title="<?echo GetMessage('INTR_EMP_SUBMIT_TITLE')?>"<? if ($current_user <= 0):?> disabled="disabled"<?endif;?> />
	<input type="button" value="<?echo GetMessage('INTR_EMP_CANCEL')?>" onclick="document.getElementById('employee_select_control').CloseDialog();" title="<?echo GetMessage('INTR_EMP_CANCEL_TITLE')?>" />
</div>