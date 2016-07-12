<?
if (!CModule::IncludeModule('bizproc'))
	return;

IncludeModuleLangFile(__FILE__);

class CCrmDocument
{
	static public function GetDocumentFieldTypes($documentType)
	{
		global $USER_FIELD_MANAGER;
		$arDocumentID = self::GetDocumentInfo($documentType.'_0');
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$arResult = array(
			'string' => array('Name' => GetMessage('BPVDX_STRING'), 'BaseType' => 'string'),
			'int' => array('Name' => GetMessage('BPVDX_NUMINT'), 'BaseType' => 'int'),
			'email' => array('Name' => GetMessage('BPVDX_EMAIL'), 'BaseType' => 'string'),
			'phone' => array('Name' => GetMessage('BPVDX_PHONE'), 'BaseType' => 'string'),
			'web' => array('Name' => GetMessage('BPVDX_WEB'), 'BaseType' => 'string'),
			'im' => array('Name' => GetMessage('BPVDX_MESSANGER'), 'BaseType' => 'string'),
			'text' => array('Name' => GetMessage('BPVDX_TEXT'), 'BaseType' => 'text'),
			'double' => array('Name' => GetMessage('BPVDX_NUM'), 'BaseType' => 'double'),
			'select' => array('Name' => GetMessage('BPVDX_LIST'), 'BaseType' => 'select', "Complex" => true),
			'file' => array('Name' => GetMessage('BPVDX_FILE'), 'BaseType' => 'file'),
			'user' => array('Name' => GetMessage('BPVDX_USER'), 'BaseType' => 'user'),
			'bool' => array('Name' => GetMessage('BPVDX_YN'), 'BaseType' => 'bool'),
			'datetime' => array('Name' => GetMessage('BPVDX_DATETIME'), 'BaseType' => 'datetime')
		);

		$arTypes = $USER_FIELD_MANAGER->GetUserType();
		foreach ($arTypes as $arType)
		{
			if (in_array($arType['USER_TYPE_ID'], array('string', 'double', 'boolean', 'integer', 'datetime', 'file', 'employee', 'enumeration', 'video', 'string_formatted')))
				continue;
			if ($arType['BASE_TYPE'] == 'enum')
				$arType['BASE_TYPE'] = 'select';
			$arResult['UF:'.$arType['USER_TYPE_ID']] = array('Name' => $arType['DESCRIPTION'], 'BaseType' => $arType['BASE_TYPE']);
			if (in_array($arType['USER_TYPE_ID'], array('crm', 'crm_status', 'iblock_element', 'iblock_section')))
				$arResult['UF:'.$arType['USER_TYPE_ID']]['Complex'] = true;
		}

		return $arResult;
	}

	static public function GetFieldInputControl($documentType, $arFieldType, $arFieldName, $fieldValue, $bAllowSelection = false, $publicMode = false)
	{
		global $USER_FIELD_MANAGER, $APPLICATION;

		$arDocumentID = self::GetDocumentInfo($documentType.'_0');
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		static $arDocumentFieldTypes = array();
		if (!array_key_exists($documentType, $arDocumentFieldTypes))
			$arDocumentFieldTypes[$documentType] = self::GetDocumentFieldTypes($documentType);

		$arFieldType["BaseType"] = "string";
		$arFieldType["Complex"] = false;
		if (array_key_exists($arFieldType["Type"], $arDocumentFieldTypes[$documentType]))
		{
			$arFieldType["BaseType"] = $arDocumentFieldTypes[$documentType][$arFieldType["Type"]]["BaseType"];
			$arFieldType["Complex"] = $arDocumentFieldTypes[$documentType][$arFieldType["Type"]]["Complex"];
		}

		//$customMethodName = '';
		$_fieldValue = $fieldValue;
		if (!is_array($fieldValue) || is_array($fieldValue) && CBPHelper::IsAssociativeArray($fieldValue))
			$fieldValue = array($fieldValue);

		ob_start();
		if ($arFieldType['Type'] == 'select')
		{
			$fieldValueTmp = $fieldValue;
			?>
			<select id="id_<?= htmlspecialcharsbx($arFieldName["Field"]) ?>" style="width:280px" name="<?= htmlspecialcharsbx($arFieldName["Field"]).($arFieldType["Multiple"] ? "[]" : "") ?>"<?= ($arFieldType["Multiple"] ? ' size="5" multiple' : '') ?>>
				<?
				if (!$arFieldType['Required'])
					echo '<option value="">['.GetMessage('BPVDX_NOT_SET').']</option>';
				foreach ($arFieldType['Options'] as $k => $v)
				{
					$ind = array_search($k, $fieldValueTmp);
					echo '<option value="'.htmlspecialcharsbx($k).'"'.($ind !== false ? ' selected' : '').'>'.htmlspecialcharsbx($v).'</option>';
					if ($ind !== false)
						unset($fieldValueTmp[$ind]);
				}
				?>
			</select>
			<?
			if ($bAllowSelection)
			{
				?>
				<br /><input type="text" id="id_<?= htmlspecialcharsbx($arFieldName['Field']) ?>_text" name="<?= htmlspecialcharsbx($arFieldName['Field']) ?>_text" value="<?
				if (count($fieldValueTmp) > 0)
				{
					$a = array_values($fieldValueTmp);
					echo htmlspecialcharsbx($a[0]);
				}
				?>">
				<input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($arFieldName['Field']) ?>_text', 'select');">
				<?
			}
		}
		elseif ($arFieldType['Type'] == 'web' || $arFieldType['Type'] == 'phone' || $arFieldType['Type'] == 'email' || $arFieldType['Type'] == 'im')
		{
			/*$fkeys = array_keys($fieldValue);
			foreach ($fkeys as $key)
			{
				if (preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", trim($fieldValue[$key])) || substr(trim($fieldValue[$key]), 0, 1) == "=")
				{
					$
				}
			}*/

			$value1 = $_fieldValue;
			$value2 = null;
			if ($bAllowSelection && !is_array($value1) && (preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", trim($value1)) || substr(trim($value1), 0, 1) == "="))
			{
				$value1 = null;
				$value2 = $_fieldValue;
			}

			$APPLICATION->IncludeComponent('bitrix:crm.field_multi.edit', '',
				Array(
					'FM_MNEMONIC' => $arFieldName['Field'],
					'ENTITY_ID' => $arDocumentID['TYPE'],
					'ELEMENT_ID' => $arDocumentID['ID'],
					'TYPE_ID' => strtoupper($arFieldType['Type']),
					'VALUES' => $value1
				),
				null,
				array('HIDE_ICONS' => 'Y')
			);
			if ($bAllowSelection)
			{
				?>
				<br /><input type="text" id="id_<?= htmlspecialcharsbx($arFieldName['Field']) ?>_text" name="<?= htmlspecialcharsbx($arFieldName['Field']) ?>_text" value="<?
					echo $value2;
				?>">
				<input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($arFieldName['Field']) ?>_text', 'select');">
				<?
			}
			/*$arUserFieldType = $USER_FIELD_MANAGER->GetUserType($sType);
			$arUserField = array(
				'ENTITY_ID' => 'CRM_'.$arDocumentID['TYPE'],
				'FIELD_NAME' => $arFieldName['Field'],
				'USER_TYPE_ID' => $sType,
				'SORT' => 100,
				'MULTIPLE' => $arFieldType['Multiple'] ? 'Y' : 'N',
				'MANDATORY' => $arFieldType['Required'] ? 'Y' : 'N',
				'EDIT_FORM_LABEL' => $arUserFieldType['DESCRIPTION'],
				'VALUE' => $fieldValue, //
				'USER_TYPE' => $arUserFieldType,
				'SETTINGS' => array(
				)
			);
			if (
				$arFieldType['Type'] == 'UF:iblock_element' ||
				$arFieldType['Type'] == 'UF:iblock_section' ||
				$arFieldType['Type'] == 'UF:crm_status' ||
				$arFieldType['Type'] == 'UF:boolean'
			)
			{
				if ($arFieldType['Type'] == 'UF:crm_status')
					$arUserField['SETTINGS']['ENTITY_TYPE'] = $arFieldType['Options'];
				else
					$arUserField['SETTINGS'] = $arFieldType['Options'];
			}
			elseif ($arFieldType['Type'] == 'UF:crm')
			{
				$arUserField['SETTINGS'] = $arFieldType['Options'];
				if (empty($arUserField['SETTINGS']))
					$arUserField['SETTINGS'] = array('LEAD' => 'Y', 'CONTACT' => 'Y', 'COMPANY' => 'Y', 'DEAL' => 'Y');//
			}

			$APPLICATION->IncludeComponent(
				'bitrix:system.field.edit',
				$sType,
				array(
					'arUserField' => $arUserField,
					'bVarsFromForm' => true,
					'form_name' => $arFieldName['Form'],
					'FILE_MAX_HEIGHT' => 400,
					'FILE_MAX_WIDTH' => 400,
					'FILE_SHOW_POPUP' => true
				),
				false,
				array('HIDE_ICONS' => 'Y')
			);*/

		}
		elseif ($arFieldType['Type'] == 'user')
		{
			$fieldValue = CBPHelper::UsersArrayToString($fieldValue, null, $arDocumentID["DOCUMENT_TYPE"]);
			?><input type="text" size="40" id="id_<?= htmlspecialcharsbx($arFieldName['Field']) ?>" name="<?= htmlspecialcharsbx($arFieldName['Field']) ?>" value="<?= htmlspecialcharsbx($fieldValue) ?>"><input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($arFieldName['Field']) ?>', 'user');"><?
		}
		else
		{
			if (!array_key_exists('CBPVirtualDocumentCloneRowPrinted_'.$documentType, $GLOBALS) && $arFieldType['Multiple'])
			{
				$GLOBALS['CBPVirtualDocumentCloneRowPrinted_'.$documentType] = 1;
				?>
				<script language="JavaScript">
				<!--
				function CBPVirtualDocumentCloneRow(tableID)
				{
					var tbl = document.getElementById(tableID);
					var cnt = tbl.rows.length;
					var oRow = tbl.insertRow(cnt);
					var oCell = oRow.insertCell(0);
					var sHTML = tbl.rows[cnt - 1].cells[0].innerHTML;
					var p = 0;
					while (true)
					{
						var s = sHTML.indexOf('[n', p);
						if (s < 0)
							break;
						var e = sHTML.indexOf(']', s);
						if (e < 0)
							break;
						var n = parseInt(sHTML.substr(s + 2, e - s));
						sHTML = sHTML.substr(0, s) + '[n' + (++n) + ']' + sHTML.substr(e + 1);
						p = s + 1;
					}
					var p = 0;
					while (true)
					{
						var s = sHTML.indexOf('__n', p);
						if (s < 0)
							break;
						var e = sHTML.indexOf('_', s + 2);
						if (e < 0)
							break;
						var n = parseInt(sHTML.substr(s + 3, e - s));
						sHTML = sHTML.substr(0, s) + '__n' + (++n) + '_' + sHTML.substr(e + 1);
						p = e + 1;
					}
					oCell.innerHTML = sHTML;
					var patt = new RegExp('<' + 'script' + '>[^\000]*?<' + '\/' + 'script' + '>', 'ig');
					var code = sHTML.match(patt);
					if (code)
					{
						for (var i = 0; i < code.length; i++)
						{
							if (code[i] != '')
							{
								var s = code[i].substring(8, code[i].length - 9);
								jsUtils.EvalGlobal(s);
							}
						}
					}
				}
				//-->
				</script>
				<?
			}

			if ($arFieldType['Multiple'])
				echo '<table width="100%" border="0" cellpadding="2" cellspacing="2" id="CBPVirtualDocument_'.htmlspecialcharsbx($arFieldName["Field"]).'_Table">';

			$fieldValueTmp = $fieldValue;

			$ind = -1;
			foreach ($fieldValue as $key => $value)
			{
				$ind++;
				$fieldNameId = 'id_'.htmlspecialcharsbx($arFieldName['Field']).'__n'.$ind.'_';
				$fieldNameName = htmlspecialcharsbx($arFieldName['Field']).($arFieldType['Multiple'] ? '[n'.$ind.']' : '');

				//if ($arFieldType["Type"] == 'file')
				//	continue;

				if ($arFieldType['Multiple'])
					echo '<tr><td>';

				if (strpos($arFieldType['Type'], 'UF:') === 0)
				{
					$value1 = $value;
					if ($bAllowSelection && (preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", trim($value1)) || substr(trim($value1), 0, 1) == "="))
						$value1 = null;
					else
						unset($fieldValueTmp[$key]);

					$sType = str_replace('UF:', '', $arFieldType['Type']);

					$_REQUEST[$arFieldName['Field']] = $value1;
					if ($sType == 'crm')
					{
						?>
						<script>
						BX.loadCSS('/bitrix/js/crm/css/crm.css');
						</script>
						<?
					}
					$arUserFieldType = $USER_FIELD_MANAGER->GetUserType($sType);

					$arUserField = array(
						'ENTITY_ID' => 'CRM_'.$arDocumentID['TYPE'],
						'FIELD_NAME' => $arFieldName['Field'],
						'USER_TYPE_ID' => $sType,
						'SORT' => 100,
						'MULTIPLE' => $arFieldType['Multiple'] ? 'Y' : 'N',
						'MANDATORY' => $arFieldType['Required'] ? 'Y' : 'N',
						'EDIT_IN_LIST' => 'Y',
						'EDIT_FORM_LABEL' => $arUserFieldType['DESCRIPTION'],
						'VALUE' => $value1, //
						'USER_TYPE' => $arUserFieldType,
						'SETTINGS' => array(
						)
					);
					if (
						$arFieldType['Type'] == 'UF:iblock_element' ||
						$arFieldType['Type'] == 'UF:iblock_section' ||
						$arFieldType['Type'] == 'UF:crm_status' ||
						$arFieldType['Type'] == 'UF:boolean'
					)
					{
						if ($arFieldType['Type'] == 'UF:crm_status')
							$arUserField['SETTINGS']['ENTITY_TYPE'] = $arFieldType['Options'];
						else
							$arUserField['SETTINGS']["IBLOCK_ID"] = $arFieldType['Options'];
					}
					elseif ($arFieldType['Type'] == 'UF:crm')
					{
						$arUserField['SETTINGS'] = $arFieldType['Options'];
						if (empty($arUserField['SETTINGS']))
							$arUserField['SETTINGS'] = array('LEAD' => 'Y', 'CONTACT' => 'Y', 'COMPANY' => 'Y', 'DEAL' => 'Y');
					}

					$APPLICATION->IncludeComponent(
						'bitrix:system.field.edit',
						$sType,
						array(
							'arUserField' => $arUserField,
							'bVarsFromForm' => true,
							'form_name' => $arFieldName['Form'],
							'FILE_MAX_HEIGHT' => 400,
							'FILE_MAX_WIDTH' => 400,
							'FILE_SHOW_POPUP' => true
						),
						false,
						array('HIDE_ICONS' => 'Y')
					);
				}
				else
				{
					switch ($arFieldType['Type'])
					{
						case 'int':
							unset($fieldValueTmp[$key]);
							?><input type='text' size='10' id='<?= $fieldNameId ?>' name='<?= $fieldNameName ?>' value='<?= htmlspecialcharsbx($value) ?>'><?
							break;
						case 'file':
							if ($publicMode)
							{
								//unset($fieldValueTmp[$key]);
								?><input type="file" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?
							}
							break;
						case 'bool':
							if (in_array($value, array('Y', 'N')))
								unset($fieldValueTmp[$key]);
							?>
							<select id='<?= $fieldNameId ?>' name='<?= $fieldNameName ?>'>
								<?
								if (!$arFieldType['Required'])
									echo '<option value="">['.GetMessage("BPVDX_NOT_SET").']</option>';
								?>
								<option value="Y"<?= (in_array("Y", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPVDX_YES") ?></option>
								<option value="N"<?= (in_array("N", $fieldValue) ? ' selected' : '') ?>><?= GetMessage("BPVDX_NO") ?></option>
							</select>
							<?
							break;
						case "date":
						case "datetime":
							$v = "";
							if (!preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", trim($value))
								&& (substr(trim($value), 0, 1) != "="))
							{
								$v = $value;
								unset($fieldValueTmp[$key]);
							}

							$APPLICATION->IncludeComponent(
								'bitrix:main.calendar',
								'',
								array(
									'SHOW_INPUT' => 'Y',
									'FORM_NAME' => $arFieldName['Form'],
									'INPUT_NAME' => $fieldNameName,
									'INPUT_VALUE' => $v,
									'SHOW_TIME' => 'Y'
								),
								false,
								array('HIDE_ICONS' => 'Y')
							);
							break;
						case 'text':
							unset($fieldValueTmp[$key]);
							?><textarea rows="5" cols="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>"><?= htmlspecialcharsbx($value) ?></textarea><?
							break;
						default:
							unset($fieldValueTmp[$key]);
							?><input type="text" size="40" id="<?= $fieldNameId ?>" name="<?= $fieldNameName ?>" value="<?= htmlspecialcharsbx($value) ?>"><?
					}
				}

				if ($bAllowSelection)
				{
					if (!in_array($arFieldType["Type"], array("file", "bool", "date", "datetime")) && (strpos($arFieldType['Type'], 'UF:') !== 0))
					{
						?><input type="button" value="..." onclick="BPAShowSelector('<?= $fieldNameId ?>', '<?= $arFieldType["BaseType"] ?>');"><?
					}
				}

				if ($arFieldType['Multiple'])
					echo '</td></tr>';
			}

			if ($arFieldType['Multiple'])
				echo '</table>';

			if ($arFieldType["Multiple"] && (($arFieldType["Type"] != "file") || $publicMode))
				echo '<input type="button" value="'.GetMessage("BPVDX_ADD").'" onclick="CBPVirtualDocumentCloneRow(\'CBPVirtualDocument_'.$arFieldName["Field"].'_Table\')"/><br />';

			if ($bAllowSelection)
			{
				if (in_array($arFieldType['Type'], array('file', 'bool', "date", "datetime")) || (strpos($arFieldType['Type'], 'UF:') === 0))
				{
					?>
					<input type="text" id="id_<?= htmlspecialcharsbx($arFieldName["Field"]) ?>_text" name="<?= htmlspecialcharsbx($arFieldName["Field"]) ?>_text" value="<?
					if (count($fieldValueTmp) > 0)
					{
						$a = array_values($fieldValueTmp);
						echo htmlspecialcharsbx($a[0]);
					}
					?>">
					<input type="button" value="..." onclick="BPAShowSelector('id_<?= htmlspecialcharsbx($arFieldName["Field"]) ?>_text', '<?= htmlspecialcharsbx($arFieldType["BaseType"]) ?>');">
					<?
				}
			}
		}

		$s = ob_get_contents();
		ob_end_clean();

		return $s;
	}

	static public function GetFieldInputControlOptions($documentType, &$arFieldType, $jsFunctionName, &$value)
	{
		$result = '';
		static $arDocumentFieldTypes = array();
		if (!array_key_exists($documentType, $arDocumentFieldTypes))
			$arDocumentFieldTypes[$documentType] = self::GetDocumentFieldTypes($documentType);

		if (!array_key_exists($arFieldType['Type'], $arDocumentFieldTypes[$documentType])
			|| !$arDocumentFieldTypes[$documentType][$arFieldType['Type']]['Complex'])
		{
			return '';
		}

		if ($arFieldType['Type'] == 'UF:iblock_element' || $arFieldType['Type'] == 'UF:iblock_section')
		{
			if (is_array($value))
			{
				reset($value);
				$valueTmp = intval(current($value));
			}
			else
				$valueTmp = intval($value);

			$iblockId = 0;
			if ($valueTmp > 0)
			{
				$dbResult = CIBlockElement::GetList(array(), array(($arFieldType['Type'] == 'UF:iblock_section' ? 'SECTION_ID' : 'ID') => $valueTmp), false, false, array('ID', 'IBLOCK_ID'));
				if ($arResult = $dbResult->Fetch())
					$iblockId = $arResult['IBLOCK_ID'];
			}

			if ($iblockId <= 0 && intval($arFieldType['Options']) > 0)
				$iblockId = intval($arFieldType['Options']);

			$defaultIBlockId = 0;

			$result .= '<select id="WFSFormOptionsX" onchange="'.$jsFunctionName.'(this.options[this.selectedIndex].value)">';
			$arIBlockType = CIBlockParameters::GetIBlockTypes();
			foreach ($arIBlockType as $iblockTypeId => $iblockTypeName)
			{
				$result .= '<optgroup label="'.$iblockTypeName.'">';
				$dbIBlock = CIBlock::GetList(array('SORT' => 'ASC'), array('TYPE' => $iblockTypeId, 'ACTIVE' => 'Y'));
				while ($arIBlock = $dbIBlock->GetNext())
				{
					$result .= '<option value="'.$arIBlock['ID'].'"'.(($arIBlock['ID'] == $iblockId) ? ' selected="selected"' : '').'>'.$arIBlock['NAME'].'</option>';
					if (($defaultIBlockId <= 0) || ($arIBlock['ID'] == $iblockId))
						$defaultIBlockId = $arIBlock['ID'];
				}

				$result .= '</optgroup>';
			}
			$result .= '</select><!--__defaultOptionsValue:'.$defaultIBlockId.'--><!--__modifyOptionsPromt:'.GetMessage('CRM_DOCUMENT_IBLOCK').'-->';

			$arFieldType['Options'] = $defaultIBlockId;
		}
		else if ($arFieldType['Type'] == 'UF:crm_status')
		{
			$statusID = $arFieldType['Options'];
			$arEntityTypes = CCrmStatus::GetEntityTypes();
			$default = 'STATUS';
			$result .= '<select id="WFSFormOptionsX" onchange="'.$jsFunctionName.'(this.options[this.selectedIndex].value)">';
			foreach ($arEntityTypes as $arEntityType)
			{
				$result .= '<option value="'.$arEntityType['ID'].'"'.(($arEntityType['ID'] == $statusID) ? ' selected="selected"' : '').'>'.htmlspecialcharsbx($arEntityType['NAME']).'</option>';
				if ($arEntityType['ID'] == $statusID)
					$default = $arEntityType['ID'];
			}
			$result .= '</select><!--__defaultOptionsValue:'.$default.'--><!--__modifyOptionsPromt:'.GetMessage('CRM_DOCUMENT_CRM_STATUS').'-->';
		}
		else if ($arFieldType['Type'] == 'UF:crm')
		{
				$arEntity = $arFieldType['Options'];
				if (empty($arEntity))
					$arEntity = array('LEAD' => 'Y', 'CONTACT' => 'Y', 'COMPANY' => 'Y', 'DEAL' => 'Y');
				$result .= '<input type="checkbox" id="WFSFormOptionsXL" name="ENITTY[]" value="LEAD" '.($arEntity['LEAD'] == 'Y'? 'checked="checked"': '').'> '.GetMessage('CRM_DOCUMENT_CRM_ENTITY_TYPE_LEAD').' <br/>';
				$result .= '<input type="checkbox" id="WFSFormOptionsXC"  name="ENITTY[]" value="CONTACT" '.($arEntity['CONTACT'] == 'Y'? 'checked="checked"': '').'> '.GetMessage('CRM_DOCUMENT_CRM_ENTITY_TYPE_CONTACT').'<br/>';
				$result .= '<input type="checkbox" id="WFSFormOptionsXCO" name="ENITTY[]" value="COMPANY" '.($arEntity['COMPANY'] == 'Y'? 'checked="checked"': '').'> '.GetMessage('CRM_DOCUMENT_CRM_ENTITY_TYPE_COMPANY').'<br/>';
				$result .= '<input type="checkbox" id="WFSFormOptionsXD"  name="ENITTY[]" value="DEAL" '.($arEntity['DEAL'] == 'Y'? 'checked="checked"': '').'> '.GetMessage('CRM_DOCUMENT_CRM_ENTITY_TYPE_DEAL').'<br/>';
				$result .= '<input type="button" onclick="'.$jsFunctionName.'(WFSFormOptionsXCRM())" value="'.GetMessage('CRM_DOCUMENT_CRM_ENTITY_OK').'" />';
				$result .= '<script>
					function WFSFormOptionsXCRM()
					{
						var a = {};
						a["LEAD"] = BX("WFSFormOptionsXL").checked ? "Y" : "N";
						a["CONTACT"] = BX("WFSFormOptionsXC").checked ? "Y" : "N";
						a["COMPANY"] = BX("WFSFormOptionsXCO").checked ? "Y" : "N";
						a["DEAL"] = BX("WFSFormOptionsXD").checked ? "Y" : "N";
						return a;
					}
				</script>';
				$result .= '<!--__modifyOptionsPromt:'.GetMessage('CRM_DOCUMENT_CRM_ENTITY').'-->';
		}
		elseif ($arFieldType["Type"] == "select")
		{
			$valueTmp = $arFieldType["Options"];
			if (!is_array($valueTmp))
				$valueTmp = array($valueTmp => $valueTmp);

			$str = '';
			foreach ($valueTmp as $k => $v)
			{
				if (is_array($v) && count($v) == 2)
				{
					$v1 = array_values($v);
					$k = $v1[0];
					$v = $v1[1];
				}

				if ($k != $v)
					$str .= '['.$k.']'.$v;
				else
					$str .= $v;

				$str .= "\n";
			}
			$result .= '<textarea id="WFSFormOptionsX" rows="5" cols="30">'.htmlspecialcharsbx($str).'</textarea><br />';
			$result .= GetMessage("IBD_DOCUMENT_XFORMOPTIONS1").'<br />';
			$result .= GetMessage("IBD_DOCUMENT_XFORMOPTIONS2").'<br />';
			$result .= '<script type="text/javascript">
				function WFSFormOptionsXFunction()
				{
					var result = {};
					var i, id, val, str = document.getElementById("WFSFormOptionsX").value;

					var arr = str.split(/[\r\n]+/);
					var p, re = /\[([^\]]+)\].+/;
					for (i in arr)
					{
						str = arr[i].replace(/^\s+|\s+$/g, \'\');
						if (str.length > 0)
						{
							id = str.match(re);
							if (id)
							{
								p = str.indexOf(\']\');
								id = id[1];
								val = str.substr(p + 1);
							}
							else
							{
								val = str;
								id = val;
							}
							result[id] = val;
						}
					}

					return result;
				}
				</script>';
			$result .= '<input type="button" onclick="'.htmlspecialcharsbx($jsFunctionName).'(WFSFormOptionsXFunction())" value="'.GetMessage("IBD_DOCUMENT_XFORMOPTIONS3").'">';
		}

		return $result;
	}

	static public function GetFieldInputValue($documentType, $arFieldType, $arFieldName, $arRequest, &$arErrors)
	{
		if (strpos($documentType, '_') === false)
			$documentType .= '_0';

		$arDocumentID = self::GetDocumentInfo($documentType);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$result = array();

		if ($arFieldType["Type"] == "user")
		{
			$value = array_key_exists($arFieldName["Field"], $arRequest) ? $arRequest[$arFieldName["Field"]] : '';
			if ($value !== '')
			{
				$arErrorsTmp1 = array();
				$result = CBPHelper::UsersStringToArray($value, $arDocumentID["DOCUMENT_TYPE"], $arErrorsTmp1);
				if (count($arErrorsTmp1) > 0)
				{
					foreach ($arErrorsTmp1 as $e)
						$arErrors[] = $e;
				}
			}
			elseif(array_key_exists($arFieldName["Field"]."_text", $arRequest))
			{
				$result[] = $arRequest[$arFieldName["Field"]."_text"];
			}
		}
		elseif (array_key_exists($arFieldName["Field"], $arRequest) || array_key_exists($arFieldName["Field"]."_text", $arRequest))
		{
			$arValue = array();
			if (array_key_exists($arFieldName["Field"], $arRequest))
			{
				$arValue = $arRequest[$arFieldName["Field"]];
				if (!is_array($arValue) || is_array($arValue) && CBPHelper::IsAssociativeArray($arValue))
					$arValue = array($arValue);
			}
			if (array_key_exists($arFieldName["Field"]."_text", $arRequest))
				$arValue[] = $arRequest[$arFieldName["Field"]."_text"];

			foreach ($arValue as $value)
			{
				if (is_array($value) || !is_array($value) && !preg_match("#^\{=[a-z0-9_]+:[a-z0-9_]+\}$#i", trim($value)) && (substr(trim($value), 0, 1) != "="))
				{
					if ($arFieldType['Type'] == 'email' || $arFieldType['Type'] == 'im' || $arFieldType['Type'] == 'web' || $arFieldType['Type'] == 'phone')
					{
						if (is_array($value))
						{
							$keys1 = array_keys($value);
							foreach ($keys1 as $key1)
							{
								if (is_array($value[$key1]))
								{
									$keys2 = array_keys($value[$key1]);
									foreach ($keys2 as $key2)
									{
										if (!isset($value[$key1][$key2]["VALUE"]) || empty($value[$key1][$key2]["VALUE"]))
											unset($value[$key1][$key2]);
									}
									if (count($value[$key1]) <= 0)
										unset($value[$key1]);
								}
								else
								{
									unset($value[$key1]);
								}
							}
							if (count($value) <= 0)
								$value = null;
						}
						else
						{
							$value = null;
						}
					}
					elseif ($arFieldType["Type"] == "int")
					{
						if (strlen($value) > 0)
						{
							$value = str_replace(" ", "", $value);
							if ($value."|" == intval($value)."|")
							{
								$value = intval($value);
							}
							else
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID1"),
									"parameter" => $arFieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($arFieldType["Type"] == "double")
					{
						if (strlen($value) > 0)
						{
							$value = str_replace(" ", "", str_replace(",", ".", $value));
							if (is_numeric($value))
							{
								$value = doubleval($value);
							}
							else
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID11"),
									"parameter" => $arFieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif ($arFieldType["Type"] == "select")
					{
						if (!is_array($arFieldType["Options"]) || count($arFieldType["Options"]) <= 0 || strlen($value) <= 0)
						{
							$value = null;
						}
						else
						{
							$ar = array_values($arFieldType["Options"]);
							if (is_array($ar[0]))
							{
								$b = false;
								foreach ($ar as $a)
								{
									if ($a[0] == $value)
									{
										$b = true;
										break;
									}
								}
								if (!$b)
								{
									$value = null;
									$arErrors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("BPCGWTL_INVALID35"),
										"parameter" => $arFieldName["Field"],
									);
								}
							}
							else
							{
								if (!array_key_exists($value, $arFieldType["Options"]))
								{
									$value = null;
									$arErrors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("BPCGWTL_INVALID35"),
										"parameter" => $arFieldName["Field"],
									);
								}
							}
						}
					}
					elseif ($arFieldType["Type"] == "bool")
					{
						if ($value !== "Y" && $value !== "N")
						{
							if ($value === true)
							{
								$value = "Y";
							}
							elseif ($value === false)
							{
								$value = "N";
							}
							elseif (strlen($value) > 0)
							{
								$value = strtolower($value);
								if (in_array($value, array("y", "yes", "true", "1")))
								{
									$value = "Y";
								}
								elseif (in_array($value, array("n", "no", "false", "0")))
								{
									$value = "N";
								}
								else
								{
									$value = null;
									$arErrors[] = array(
										"code" => "ErrorValue",
										"message" => GetMessage("BPCGWTL_INVALID45"),
										"parameter" => $arFieldName["Field"],
									);
								}
							}
							else
							{
								$value = null;
							}
						}
					}
					elseif ($arFieldType["Type"] == "file")
					{
						if (is_array($value) && array_key_exists("name", $value) && strlen($value["name"]) > 0)
						{
							if (!array_key_exists("MODULE_ID", $value) || strlen($value["MODULE_ID"]) <= 0)
								$value["MODULE_ID"] = "bizproc";

							$value = CFile::SaveFile($value, "bizproc_wf", true, true);
							if (!$value)
							{
								$value = null;
								$arErrors[] = array(
									"code" => "ErrorValue",
									"message" => GetMessage("BPCGWTL_INVALID915"),
									"parameter" => $arFieldName["Field"],
								);
							}
						}
						else
						{
							$value = null;
						}
					}
					elseif (strpos($arFieldType["Type"], ":") !== false)
					{
						$customTypeID = str_replace('UF:', '', $arFieldType['Type']);
						$arCustomType = $GLOBALS["USER_FIELD_MANAGER"]->GetUserType($customTypeID);

						if($customTypeID === 'crm' && $value === '')
						{
							//skip empty crm entity references
							$value = null;
						}
						elseif ($value !== null && array_key_exists("CheckFields", $arCustomType))
						{
							$arErrorsTmp1 = call_user_func_array(
								$arCustomType["CheckFields"],
								array(
									array("LINK_IBLOCK_ID" => $arFieldType["Options"]),
									array("VALUE" => $value)
								)
							);
							if (count($arErrorsTmp1) > 0)
							{
								$value = null;
								foreach ($arErrorsTmp1 as $e)
									$arErrors[] = array(
										"code" => "ErrorValue",
										"message" => $e,
										"parameter" => $arFieldName["Field"],
									);
							}
						}
					}
					else
					{
						if (!is_array($value) && strlen($value) <= 0)
							$value = null;
					}
				}

				if ($value !== null)
					$result[] = $value;
			}
		}

		if (!$arFieldType["Multiple"])
		{
			if (count($result) > 0)
				$result = $result[0];
			else
				$result = null;
		}

		return $result;
	}

	static public function GetFieldInputValuePrintable($documentType, $arFieldType, $fieldValue)
	{
		global $USER_FIELD_MANAGER, $APPLICATION;
		$arDocumentID = self::GetDocumentInfo($documentType.'_0');
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$result = $fieldValue;
		switch ($arFieldType['Type'])
		{
			case 'datetime':
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $_fieldValue)
						$result[] = empty($_fieldValue) ? FormatDate('x', MakeTimeStamp($_fieldValue)) : '';
				}
				else
					$result = !empty($fieldValue) ? FormatDate('x', MakeTimeStamp($fieldValue)) : '';
				break;

			case 'user':
				if (!is_array($fieldValue))
					$fieldValue = array($fieldValue);

				$result = CBPHelper::UsersArrayToString($fieldValue, null, $arDocumentID["DOCUMENT_TYPE"]);
				break;

			case 'bool':
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
						$result[] = ((strtoupper($r) != "N" && !empty($r)) ? GetMessage('BPVDX_YES') : GetMessage('BPVDX_NO'));
				}
				else
				{
					$result = ((strtoupper($fieldValue) != "N" && !empty($fieldValue)) ? GetMessage('BPVDX_YES') : GetMessage('BPVDX_NO'));
				}
				break;

			case 'file':
				if (is_array($fieldValue))
				{
					$result = array();
					foreach ($fieldValue as $r)
					{
						$r = intval($r);
						$dbImg = CFile::GetByID($r);
						if ($arImg = $dbImg->Fetch())
							$result[] = "[url=/bitrix/tools/bizproc_show_file.php?f=".htmlspecialcharsbx($arImg["FILE_NAME"])."&i=".$r."]".htmlspecialcharsbx($arImg["ORIGINAL_NAME"])."[/url]";
					}
				}
				else
				{
					$fieldValue = intval($fieldValue);
					$dbImg = CFile::GetByID($fieldValue);
					if ($arImg = $dbImg->Fetch())
						$result = "[url=/bitrix/tools/bizproc_show_file.php?f=".htmlspecialcharsbx($arImg["FILE_NAME"])."&i=".$fieldValue."]".htmlspecialcharsbx($arImg["ORIGINAL_NAME"])."[/url]";
				}
				break;

			case 'select':
				if (is_array($arFieldType["Options"]))
				{
					if (is_array($fieldValue))
					{
						$result = array();
						foreach ($fieldValue as $r)
						{
							if (array_key_exists($r, $arFieldType["Options"]))
								$result[] = $arFieldType["Options"][$r];
						}
					}
					else
					{
						if (array_key_exists($fieldValue, $arFieldType["Options"]))
							$result = $arFieldType["Options"][$fieldValue];
					}
				}

				break;
			case 'web':
			case 'im':
			case 'email':
			case 'phone':
					$result = array();

					if (is_array($fieldValue) && !CBPHelper::IsAssociativeArray($fieldValue))
						$fieldValue = $fieldValue[0];

					if (is_array($fieldValue) && is_array($fieldValue[strtoupper($arFieldType['Type'])]))
					{
						foreach ($fieldValue[strtoupper($arFieldType['Type'])] as $val)
						{
							if (!empty($val))
								$result[] = CCrmFieldMulti::GetEntityNameByComplex(strtoupper($arFieldType['Type']).'_'.$val['VALUE_TYPE'], false).': '.$val['VALUE'];
						}
					}
				break;
		}

		if (strpos($arFieldType['Type'], 'UF:') === 0)
		{
			$sType = str_replace('UF:', '', $arFieldType['Type']);
			$arUserFieldType = $USER_FIELD_MANAGER->GetUserType($sType);
			$arUserField = array(
				'ENTITY_ID' => 'CRM_LEAD',
				'FIELD_NAME' => 'UF_XXXXXXX',
				'USER_TYPE_ID' => $sType,
				'SORT' => 100,
				'MULTIPLE' => $arFieldType['Multiple'] ? 'Y' : 'N',
				'MANDATORY' => $arFieldType['Required'] ? 'Y' : 'N',
				'EDIT_FORM_LABEL' => $arUserFieldType['DESCRIPTION'],
				'VALUE' => $fieldValue, //
				'USER_TYPE' => $arUserFieldType
			);
			if ($arFieldType['Type'] == 'UF:iblock_element' || $arFieldType['Type'] == 'UF:iblock_section')
				$arUserField['SETTINGS']['IBLOCK_ID'] = $arFieldType['Options'];
			elseif ($arFieldType['Type'] == 'UF:crm_status')
				$arUserField['SETTINGS']['ENTITY_TYPE'] = $arFieldType['Options'];
			elseif ($arFieldType['Type'] == 'UF:crm')
			{
				$arUserField['SETTINGS'] = $arFieldType['Options'];
				if (empty($arUserField['SETTINGS']))
					$arUserField['SETTINGS'] = array('LEAD' => 'Y', 'CONTACT' => 'Y', 'COMPANY' => 'Y', 'DEAL' => 'Y');//
			}
			$APPLICATION->IncludeComponent(
				'bitrix:system.field.view',
				$sType,
				array(
					'arUserField' => $arUserField,
					'bVarsFromForm' => false,
					'form_name' => "",
					'FILE_MAX_HEIGHT' => 400,
					'FILE_MAX_WIDTH' => 400,
					'FILE_SHOW_POPUP' => true
				),
				false,
				array('HIDE_ICONS' => 'Y')
			);
			$result = ob_get_contents();
			ob_end_clean();
		}

		return $result;
	}

	static public function GetGUIFieldEdit($documentType, $formName, $fieldName, $fieldValue, $arDocumentField = null, $bAllowSelection = false)
	{
		return self::GetFieldInputControl(
			$documentType,
			$arDocumentField,
			array('Form' => $formName, 'Field' => $fieldName),
			$fieldValue,
			$bAllowSelection
		);
	}

	static public function SetGUIFieldEdit($documentType, $fieldName, $arRequest, &$arErrors, $arDocumentField = null)
	{
		return self::GetFieldInputValue($documentType, $arDocumentField, array('Field' => $fieldName), $arRequest, $arErrors);
	}

	static public function GetJSFunctionsForFields()
	{
		return '';
	}

	static public function GetDocumentAdminPage($documentId)
	{
		return null;
	}

	static public function GetDocument($documentId)
	{
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$arResult = null;

		switch ($arDocumentID['TYPE'])
		{
			case 'CONTACT':
				$dbDocumentList = CCrmContact::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N")
				);
				break;
			case 'COMPANY':
				$dbDocumentList = CCrmCompany::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N")
				);
				break;
			case 'DEAL':
				$dbDocumentList = CCrmDeal::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N")
				);
				break;
			case 'LEAD':
				$dbDocumentList = CCrmLead::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N")
				);
				break;
		}

		if (($objDocument = $dbDocumentList->Fetch()) !== false)
		{
			$arUserField = array('CREATED_BY', 'MODIFY_BY', 'ASSIGNED_BY', 'ASSIGNED_BY_ID');
			foreach ($arUserField as $sField)
				if (isset($objDocument[$sField]))
					$objDocument[$sField] = 'user_'.$objDocument[$sField];

			$res = CCrmFieldMulti::GetList(
				array('ID' => 'asc'),
				array('ENTITY_ID' => $arDocumentID['TYPE'], 'ELEMENT_ID' => $arDocumentID['ID'])
			);
			while ($ar = $res->Fetch())
			{
				if (!isset($objDocument[$ar['TYPE_ID']]))
					$objDocument[$ar['TYPE_ID']] = array();
				$objDocument[$ar['TYPE_ID']]['n0'.$ar['ID']] = array('VALUE' => $ar['VALUE'], 'VALUE_TYPE' => $ar['VALUE_TYPE']);

				if (!isset($objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']]))
					$objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']] = array();
				$objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']][] = $ar['VALUE'];

				if (!isset($objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']."_PRINTABLE"]))
					$objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']."_PRINTABLE"] = "";
				$objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']."_PRINTABLE"] .= (strlen($objDocument[$ar['TYPE_ID']."_".$ar['VALUE_TYPE']."_PRINTABLE"]) > 0 ? ", " : "").$ar['VALUE'];

				if (!isset($objDocument[$ar['TYPE_ID']."_PRINTABLE"]))
					$objDocument[$ar['TYPE_ID']."_PRINTABLE"] = "";
				$objDocument[$ar['TYPE_ID']."_PRINTABLE"] .= (strlen($objDocument[$ar['TYPE_ID']."_PRINTABLE"]) > 0 ? ", " : "").$ar['VALUE'];
			}

			return $objDocument;
		}
		return null;
	}

	static public function GetDocumentForHistory($documentId, $historyIndex)
	{
		global $USER_FIELD_MANAGER;
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$arResult = self::GetDocument($documentId);

		switch ($arDocumentID['TYPE'])
		{
			case 'CONTACT':
				if (!empty($arResult['PHOTO']))
					$arResult['PHOTO'] = CBPDocument::PrepareFileForHistory(
						array('crm', 'CCrmDocument'.ucfirst(strtolower($arDocumentID['TYPE'])), $documentId),
						$arResult['PHOTO'],
						$historyIndex
					);
			break;
			case 'COMPANY':
				if (!empty($arResult['LOGO']))
					$arResult['LOGO'] = CBPDocument::PrepareFileForHistory(
						array('crm', 'CCrmDocument'.ucfirst(strtolower($arDocumentID['TYPE'])), $documentId),
						$arResult['LOGO'],
						$historyIndex
					);
			break;
		}

		$arUserFields = $USER_FIELD_MANAGER->GetUserFields('CRM_'.$arDocumentID['TYPE'], $arDocumentID['ID'], LANGUAGE_ID);
		foreach($arUserFields as $FIELD_NAME => $arUserField)
		{
			if ($arUserField['USER_TYPE']['BASE_TYPE'] == 'file')
			{
				$arFiles = !is_array($arUserField[$FIELD_NAME]) ? array($arUserField[$FIELD_NAME]) : $arUserField[$FIELD_NAME];
				foreach ($arFiles as $sFilePath)
				{
					$sFilePath = CBPDocument::PrepareFileForHistory(
						array('crm', 'CCrmDocument'.ucfirst(strtolower($arDocumentID['TYPE'])), $documentId),
						$sFilePath,
						$historyIndex
					);
					if (!is_array($arUserField[$FIELD_NAME]))
					{
						$arResult[$FIELD_NAME] = $sFilePath;
						break;
					}
					else
						$arResult[$FIELD_NAME][] = $sFilePath;
				}
			}
		}

		return $arResult;
	}

	static public function RecoverDocumentFromHistory($documentId, $arDocument)
	{
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$arFields = $arDocument['FIELDS'];

		switch ($arDocumentID['TYPE'])
		{
			case 'CONTACT':
				$CCrmEntity = new CCrmContact();
				if (!empty($arFields['PHOTO']))
					$arFields['PHOTO'] = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$arFields['PHOTO']);
			break;
			case 'COMPANY':
				$CCrmEntity = new CCrmCompany();
				if (!empty($arFields['LOGO']))
					$arFields['LOGO'] = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT'].$arFields['LOGO']);
			break;
			case 'DEAL':
				$CCrmEntity = new CCrmDeal();
			break;
			case 'LEAD':
				$CCrmEntity = new CCrmLead();
			break;
		}

		$res = $CCrmEntity->Update($arDocumentID['ID'], $arFields);
		if (intVal($arFields['WF_STATUS_ID']) > 1 && intVal($arFields['WF_PARENT_ELEMENT_ID']) <= 0)
			self::UnpublishDocument($documentId);
		if (!$res)
			throw new Exception($CCrmEntity->LAST_ERROR);

		return true;
	}

	static public function LockDocument($documentId, $workflowId)
	{
		global $DB;

		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$strSql = "
			SELECT * FROM b_crm_entity_lock
			WHERE ENTITY_ID = ".$arDocumentID["ID"]." AND ENTITY_TYPE = '".$arDocumentID["TYPE"]."'
			AND LOCKED_BY = '".$DB->ForSQL($workflowId, 32)."'
		";
		$z = $DB->Query($strSql, false, 'FILE: '.__FILE__.'<br/>LINE: '.__LINE__);
		if($z->Fetch())
		{
			//Success lock because documentId already locked by workflowId
			return true;
		}
		else
		{
			$strSql = "
				INSERT INTO b_crm_entity_lock (ENTITY_ID, ENTITY_TYPE, DATE_LOCK, LOCKED_BY)
				SELECT E.ID, '".$arDocumentID["TYPE"]."', ".$DB->GetNowFunction().", '".$DB->ForSQL($workflowId, 32)."'
				FROM b_crm_".strtolower($arDocumentID['TYPE'])." E
				LEFT JOIN b_crm_entity_lock EL on EL.ENTITY_ID = E.ID
				WHERE ID = ".$arDocumentID["ID"]."
				AND EL.ENTITY_ID IS NULL
			";
			$z = $DB->Query($strSql, false, 'FILE: '.__FILE__.'<br/>LINE: '.__LINE__);
			return $z->AffectedRowsCount() > 0;
		}
	}

	static public function UnlockDocument($documentId, $workflowId)
	{
		global $DB;

		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$strSql = "
			SELECT * FROM b_crm_entity_lock
			WHERE ENTITY_ID = ".$arDocumentID["ID"]."
		";
		$z = $DB->Query($strSql, false, 'FILE: '.__FILE__.'<br/>LINE: '.__LINE__);
		if($z->Fetch())
		{
			$strSql = "
				DELETE FROM b_crm_entity_lock
				WHERE ENTITY_ID = ".$arDocumentID["ID"]."
				AND (LOCKED_BY = '".$DB->ForSQL($workflowId, 32)."' OR '".$DB->ForSQL($workflowId, 32)."' = '')
			";
			$z = $DB->Query($strSql, false, 'FILE: '.__FILE__.'<br/>LINE: '.__LINE__);
			$result = $z->AffectedRowsCount();
		}
		else
		{//Success unlock when there is no locks at all
			$result = 1;
		}

		if ($result > 0)
		{
			$db_events = GetModuleEvents('crm', 'CCrmDocument'.ucfirst(strtolower($arDocumentID['TYPE'])).'OnUnlockDocument');
			while ($arEvent = $db_events->Fetch())
				ExecuteModuleEventEx($arEvent, array(array('crm', 'CCrmDocument'.ucfirst(strtolower($arDocumentID['TYPE'])), $documentId)));
		}

		return $result > 0;
	}

	static public function IsDocumentLocked($documentId, $workflowId)
	{
		global $DB;

		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$strSql = "
			SELECT * FROM b_crm_entity_lock
			WHERE ENTITY_ID = ".$arDocumentID["ID"]."
			AND LOCKED_BY <> '".$DB->ForSQL($workflowId, 32)."'
		";
		$z = $DB->Query($strSql, false, 'FILE: '.__FILE__.'<br/>LINE: '.__LINE__);
		if($z->Fetch())
			return true;
		else
			return false;
	}

	static function CanUserOperateDocument($operation, $userId, $documentId, $arParameters = array())
	{
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$userId = intval($userId);
		if (!array_key_exists('AllUserGroups', $arParameters))
		{
			if (!array_key_exists('UserGroups', $arParameters))
			{
				$arParameters['UserGroups'] = CUser::GetUserGroup($userId);
				if (!array_key_exists('CreatedBy', $arParameters))
				{
					$authorFieldName = "";
					switch ($arDocumentID['TYPE'])
					{
						case 'CONTACT':
							$dbDocumentList = CCrmContact::GetList(
								array(),
								array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
								array('ASSIGNED_BY')
							);
							break;
							$authorFieldName = "ASSIGNED_BY";
						case 'COMPANY':
							$dbDocumentList = CCrmCompany::GetList(
								array(),
								array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
								array('CREATED_BY')
							);
							$authorFieldName = "CREATED_BY";
							break;
						case 'DEAL':
							$dbDocumentList = CCrmDeal::GetList(
								array(),
								array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
								array('ASSIGNED_BY')
							);
							$authorFieldName = "ASSIGNED_BY";
							break;
						case 'LEAD':
							$dbDocumentList = CCrmLead::GetList(
								array(),
								array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
								array('ASSIGNED_BY')
							);
							$authorFieldName = "ASSIGNED_BY";
							break;
					}
					$arElement = $dbDocumentList->Fetch();

					if (!$arElement)
						return false;

					$arParameters['CreatedBy'] = $arElement[$authorFieldName];
				}
			}

			$arParameters['AllUserGroups'] = $arParameters['UserGroups'];
			if ($userId == $arParameters['CreatedBy'])
				$arParameters['AllUserGroups'][] = 'Author';
		}

		if (array_key_exists('UserIsAdmin', $arParameters))
			if ($arParameters['UserIsAdmin'] === true)
				return true;
		else
			if (in_array(1, $arParameters['AllUserGroups']))
				return true;

		switch ($operation)
		{
			case CBPCanUserOperateOperation::ViewWorkflow:
				$op = 'READ';
				break;
			case CBPCanUserOperateOperation::StartWorkflow:
				$op = 'WRITE';
				break;
			case CBPCanUserOperateOperation::CreateWorkflow:
				$op = 'WRITE';
				break;
			case CBPCanUserOperateOperation::WriteDocument:
				$op = 'WRITE';
				break;
			case CBPCanUserOperateOperation::ReadDocument:
				$op = 'READ';
				break;
			default:
				$op = 'WRITE';
		}

		$CCrmPerms = new CCrmPerms($userId);
		if ($arDocumentID['ID'] > 0)
		{
			if (!array_key_exists('CRMEntityAttr', $arParameters))
			{
				$arEntityAttr = $CCrmPerms->GetEntityAttr($arDocumentID['TYPE'], $arDocumentID['ID']);
				$arParameters['CRMEntityAttr'] = $arEntityAttr[$arDocumentID['ID']];
			}

			return $CCrmPerms->CheckEnityAccess($arDocumentID['TYPE'], $op, $arParameters['CRMEntityAttr']);
		}
		else
			return !$CCrmPerms->HavePerm($arDocumentID['TYPE'], BX_CRM_PERM_NONE, 'ADD');
	}

	static function CanUserOperateDocumentType($operation, $userId, $documentType, $arParameters = array())
	{
		$arDocumentID = self::GetDocumentInfo($documentType.'_0');
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$userId = intval($userId);
		if (!array_key_exists('AllUserGroups', $arParameters))
		{
			if (!array_key_exists('UserGroups', $arParameters))
				$arParameters['UserGroups'] = CUser::GetUserGroup($userId);

			$arParameters['AllUserGroups'] = $arParameters['UserGroups'];
			$arParameters['AllUserGroups'][] = 'Author';
		}

		if (array_key_exists('UserIsAdmin', $arParameters))
		{
			if ($arParameters['UserIsAdmin'] === true)
				return true;
		}
		else
		{
			if (in_array(1, $arParameters['AllUserGroups']))
				return true;
		}

		if (!array_key_exists('CRMPermission', $arParameters))
		{
			switch ($operation)
			{
				case CBPCanUserOperateOperation::ViewWorkflow:
					$op = 'READ';
					break;
				case CBPCanUserOperateOperation::StartWorkflow:
					$op = 'ADD';
					break;
				case CBPCanUserOperateOperation::CreateWorkflow:
					$op = 'ADD';
					break;
				case CBPCanUserOperateOperation::WriteDocument:
					$op = 'ADD';
					break;
				case CBPCanUserOperateOperation::ReadDocument:
					$op = 'READ';
					break;
				default:
					$op = 'ADD';
			}
			$CCrmPerms = new CCrmPerms($userId);
			return !$CCrmPerms->HavePerm($arDocumentID['TYPE'], BX_CRM_PERM_NONE, $op);
		}
		else
			return $arParameters['CRMPermission'] > BX_CRM_PERM_NONE;
	}

	static public function DeleteDocument($documentId)
	{
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		switch ($arDocumentID['TYPE'])
		{
			case 'CONTACT':
				$CCrmEntity = new CCrmContact(false);
				break;
			case 'COMPANY':
				$CCrmEntity = new CCrmCompany(false);
				break;
			case 'DEAL':
				$CCrmEntity = new CCrmDeal(false);
				break;
			case 'LEAD':
				$CCrmEntity = new CCrmLead(false);
				break;
		}

		$CCrmEntity->Delete($arDocumentID['ID']);
	}


	static public function PublishDocument($documentId)
	{
		return false;
	}


	static public function UnpublishDocument($documentId)
	{
	}


	static public function GetAllowableOperations($documentType)
	{
		return array(
			'read' => GetMessage('CRM_DOCUMENT_OPERATION_READ'),
			'add' => GetMessage('CRM_DOCUMENT_OPERATION_ADD'),
			'write' => GetMessage('CRM_DOCUMENT_OPERATION_WRITE'),
			'delete' => GetMessage('CRM_DOCUMENT_OPERATION_DELETE')
		);
	}


	static public function GetAllowableUserGroups($documentType)
	{
		$documentType = trim($documentType);
		if (strlen($documentType) <= 0)
			return false;

		$arDocumentID = self::GetDocumentInfo($documentType);
		if ($arDocumentID !== false)
			$documentType = $arDocumentID['TYPE'];

		$arResult = array('author' => GetMessage('CRM_DOCUMENT_AUTHOR'));

		$arRes = array(1);
		$arGroup = CCrmPerms::GetEntityGroup($documentType, BX_CRM_PERM_SELF);

		foreach ($arGroup as $iGroupId)
			$arRes[] = $iGroupId;

		$dbGroupsList = CGroup::GetListEx(array('NAME' => 'ASC'), array('ID' => $arRes));
		while ($arGroup = $dbGroupsList->Fetch())
			$arResult[$arGroup['ID']] = $arGroup['NAME'];

		return $arResult;
	}

	static public function GetUsersFromUserGroup($group, $documentId)
	{
		if (strtolower($group) == 'author')
		{
			$arDocumentID = self::GetDocumentInfo($documentId);
			if (empty($arDocumentID))
				return array();

			switch ($arDocumentID['TYPE'])
			{
				case 'CONTACT':
					$dbDocumentList = CCrmContact::GetList(
						array(),
						array('ID' => $arDocumentID['ID']),
						array('ASSIGNED_BY')
					);
				break;
				case 'COMPANY':
					$dbDocumentList = CCrmCompany::GetList(
						array(),
						array('ID' => $arDocumentID['ID']),
						array('CREATED_BY')
					);
				break;
				case 'DEAL':
					$dbDocumentList = CCrmDeal::GetList(
						array(),
						array('ID' => $arDocumentID['ID']),
						array('ASSIGNED_BY')
					);
				break;
				case 'LEAD':
					$dbDocumentList = CCrmLead::GetList(
						array(),
						array('ID' => $arDocumentID['ID']),
						array('ASSIGNED_BY')
					);
				break;
			}
			if ($ar = $dbDocumentList->Fetch())
				return array(isset($ar['CREATED_BY']) ? $ar['CREATED_BY'] : $ar['ASSIGNED_BY']);

			return array();
		}

		$group = (int)$group;
		if ($group <= 0)
			return array();

		$arResult = array();
		$dbUsersList = CUser::GetList(($b = 'ID'), ($o = 'ASC'), array('GROUPS_ID' => $group, 'ACTIVE' => 'Y'));
		while ($arUser = $dbUsersList->Fetch())
			$arResult[] = $arUser['ID'];

		return $arResult;
	}

	static public function GetDocumentType($documentId)
	{
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		switch ($arDocumentID['TYPE'])
		{
			case 'CONTACT':
				$dbDocumentList = CCrmContact::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
					array('ID')
				);
			break;
			case 'COMPANY':
				$dbDocumentList = CCrmCompany::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
					array('ID')
				);
			break;
			case 'DEAL':
				$dbDocumentList = CCrmDeal::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
					array('ID')
				);
			break;
			case 'LEAD':
				$dbDocumentList = CCrmLead::GetList(
					array(),
					array('ID' => $arDocumentID['ID'], "CHECK_PERMISSIONS" => "N"),
					array('ID')
				);
			break;
		}

		$arResult = $dbDocumentList->Fetch();
		if (!$arResult)
			throw new Exception(GetMessage('CRM_DOCUMENT_ELEMENT_IS_NOT_FOUND'));

		return $arDocumentID['TYPE'];
	}

	protected static function GetDocumentInfo($documentId)
	{
		$arDocumentId = explode('_', $documentId);

		$cnt = count($arDocumentId);
		if ($cnt < 1)
			return false;
		if ($cnt < 2)
			$arDocumentId[] = 0;

		$arDocumentId[0] = strtoupper($arDocumentId[0]);
		if (!in_array($arDocumentId[0], array('LEAD', 'CONTACT', 'DEAL', 'COMPANY')))
			return false;
		$arDocumentId[1] = intval($arDocumentId[1]);

		static $arMap = array('LEAD' => "CCrmDocumentLead", 'CONTACT' => "CCrmDocumentContact", 'DEAL' => "CCrmDocumentDeal", 'COMPANY' => "CCrmDocumentCompany");

		return array(
			'TYPE' => $arDocumentId[0],
			'ID' => $arDocumentId[1],
			'DOCUMENT_TYPE' => array("crm", $arMap[$arDocumentId[0]], $arDocumentId[0])
		);
	}

	static public function SetPermissions($documentId, $arPermissions)
	{
		$arDocumentID = self::GetDocumentInfo($documentId);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');


	}

	static public function AddDocumentField($documentType, $arFields)
	{
		if (strpos($documentType, '_') === false)
			$documentType .= '_0';

		$arDocumentID = self::GetDocumentInfo($documentType);
		if (empty($arDocumentID))
			throw new CBPArgumentNullException('documentId');

		$arFieldsTmp = array(
			'USER_TYPE_ID' => $arFields["type"],
			'FIELD_NAME' => "UF_CRM_".strtoupper($arFields["code"]),
			'ENTITY_ID' => "CRM_".$arDocumentID['TYPE'],
			'SORT' => 150,
			'MULTIPLE' => $arFields["multiple"] == 'Y' ? 'Y' : 'N',
			'MANDATORY' => $arFields["required"] == 'Y' ? 'Y' : 'N',
			'SHOW_FILTER' => 'E',
		);

		$arFieldsTmp["EDIT_FORM_LABEL"][LANGUAGE_ID] = $arFields["name"];
		$arFieldsTmp["LIST_COLUMN_LABEL"][LANGUAGE_ID] = $arFields["name"];
		$arFieldsTmp["LIST_FILTER_LABEL"][LANGUAGE_ID] = $arFields["name"];

		if (array_key_exists("additional_type_info", $arFields))
			$arField['SETTINGS']['IBLOCK_ID'] = intval($arFields["additional_type_info"]);

		switch ($arFields["type"])
		{
			case "select":
			case 'enumeration':
				$arFieldsTmp['USER_TYPE_ID'] = 'enumeration';

				if(!is_array($arFieldsTmp['LIST']))
					$arFieldsTmp['LIST'] = array();
				if (is_array($arFields["options"]))
				{
					$i = 10;
					foreach ($arFields["options"] as $k => $v)
					{
						$arFieldsTmp["LIST"]["n".$i] = array("XML_ID" => $k, "VALUE" => $v, "DEF" => "N", "SORT" => $i);
						$i = $i + 10;
					}
				}
				break;

			case 'text':
				$arFieldsTmp['USER_TYPE_ID'] = "string";
				break;

			case 'iblock_section':
			case 'iblock_element':
				$arFieldsTmp['SETTINGS']['IBLOCK_ID'] = $arFields["options"];
				break;

			case 'crm_status':
				$arFieldsTmp['SETTINGS']['ENTITY_TYPE'] = $arDocumentID['TYPE'];
				break;

			/*case 'crm':
				$arFieldsTmp['SETTINGS']['LEAD'] = $_POST['ENTITY_TYPE_LEAD'];
				$arFieldsTmp['SETTINGS']['CONTACT'] = $_POST['ENTITY_TYPE_CONTACT'];
				$arFieldsTmp['SETTINGS']['COMPANY'] = $_POST['ENTITY_TYPE_COMPANY'];
				$arFieldsTmp['SETTINGS']['DEAL'] = $_POST['ENTITY_TYPE_DEAL'];
				break;*/

			case 'employee':
				$arFieldsTmp['SHOW_FILTER'] = 'I';
				break;
		}

		$crmFields = new CCrmFields($GLOBALS['USER_FIELD_MANAGER'], "CRM_".$arDocumentID['TYPE']);

		//$fieldId = $crmFields->GetNextFieldId();

		$crmFields->AddField($arFieldsTmp);

		$GLOBALS['CACHE_MANAGER']->ClearByTag('crm_fields_list_'.$arFieldsTmp["FIELD_NAME"]);

		return $arFieldsTmp["FIELD_NAME"];
	}
}