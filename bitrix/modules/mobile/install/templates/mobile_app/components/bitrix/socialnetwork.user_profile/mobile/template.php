<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", "employee-card");

if (!empty($arResult["FatalError"]))
{
	echo  $arResult["FatalError"];
	return;
}

global $USER;
$arUser = $arResult["User"];
?>
<script type="text/javascript">
	var pullParams = {
			enable:true,
			pulltext:"<?=GetMessage("PULL_TEXT");?>",
			downtext:"<?=GetMessage("DOWN_TEXT");?>",
			loadtext:"<?=GetMessage("LOAD_TEXT");?>",
		};
	if(app.enableInVersion(2))
		pullParams.action = "RELOAD";
	else
		pullParams.callback = function(){document.location.reload();}
	app.pullDown(pullParams);

	if (app.enableInVersion(3))
	{
		app.menuCreate({
			items: [
				{
					name: '<?php echo GetMessageJS('MB_TASKS_AT_SOCNET_USER_CPT_MENU_ITEM_LIST'); ?>',
					icon: 'checkbox',
					action: function() {
						var path = '<?php echo CUtil::JSEscape($arParams['PATH_TO_TASKS_SNM_ROUTER']); ?>';
						path = path
							.replace('#ROUTE_PAGE#', 'list')
							.replace('#USER_ID#', <?php echo (int) $arResult["User"]['ID']; ?>);

						app.openNewPage(path);
					}
				},
				{
					name: '<?php echo GetMessageJS('MB_FILES_AT_SOCNET_USER_CPT_MENU_ITEM_LIST'); ?>',
					icon: 'file',
					action: function() {
						app.openBXTable({
							url: '/mobile/webdav/user/<?php echo (int) $arResult["User"]['ID']; ?>/',
							TABLE_SETTINGS : {
								cache : "NO",
								type : "files",
								useTagsInSearch : "NO"
							}
						});
					}
				},
				<?if (IsModuleInstalled("bitrix24") && $USER->CanDoOperation('bitrix24_invite') && $arResult["User"]["ACTIVITY_STATUS"] == "invited") :?>
				{
					name: '<?php echo GetMessageJS('MB_REINVITE_USER_CPT_MENU_ITEM_LIST'); ?>',
					icon: 'adduser',
					action: function() {
						app.showPopupLoader({test:""});
						BX.ajax.post(
							"/mobile/users/invite.php",
							{
								user_id:"<?=$arUser["ID"]?>",
								reinvite:"Y",
								sessid: BX.bitrix_sessid()
							},
							function(result)
							{
								app.hidePopupLoader();
								alert("<?=GetMessage("MB_INVITE_MESSAGE")?>");
							}
						);
					}
				}
				<?endif?>
			]
		});

		app.addButtons({menuButton: {
			type:    'context-menu',
			style:   'custom',
			callback: function()
			{
				app.menuShow();
			}
		}});
	}
</script>
<div class="emp-wrap">
	<div class="emp-top">
		<div class="avatar emp-img" style="<?
		if (strlen($arResult["USER_PERSONAL_PHOTO_SRC"]["src"]) > 0):
		?>background-image:url('<?=$arResult["USER_PERSONAL_PHOTO_SRC"]["src"]?>'); background-size:<?=round($arResult["USER_PERSONAL_PHOTO_SRC"]["width"]/2)?>px <?=round($arResult["USER_PERSONAL_PHOTO_SRC"]["height"]/2)?>px; height:<?=round($arResult["USER_PERSONAL_PHOTO_SRC"]["height"]/2)?>px;<?
		else:
		?>background-image:url('/bitrix/templates/mobile_app/images/avatar.png'); background-size:50px 50px;<?
		endif?>">
		</div>
		<div class="emp-top-name"><?=$arResult["USER_FULL_NAME"]/*CUser::FormatName(CSite::GetNameFormat(false), $arUser, true)*/?></div>
		<div class="emp-top-position"><?=$arUser["WORK_POSITION"]?></div>
	</div>
	<div class="emp-info-block">
		<div class="emp-info-title"><?=GetMessage("SONET_CONTACT_TITLE")?></div>
			<?
//Contact INFO
			foreach ($arResult["UserFieldsContact"]["DATA"] as $field => $arUserField):
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):
					?><span class="emp-info-cell"><?=$arUserField["NAME"].":"?></span><span class="emp-info-cell"><?
					switch ($field)
					{
						case "PERSONAL_MOBILE":
						case "WORK_PHONE":
							?><a href="tel:<?=$arUser[$field]?>"><?=$arUser[$field]?></a><?
							break;
						default:
							echo $arUserField["VALUE"];
					}
					?></span><?
				endif;
			endforeach;
			foreach ($arResult["UserPropertiesContact"]["DATA"] as $field => $arUserField):
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):
					?><span class="emp-info-cell"><?=$arUserField["EDIT_FORM_LABEL"].":"?></span><span class="emp-info-cell"><?
						$value = htmlspecialcharsbx($arUserField["VALUE"]);
						switch ($field)
						{
							case "UF_FACEBOOK":
							case "UF_LINKEDIN":
							case "UF_XING":
								$href = ((strpos($arUserField["VALUE"], "http") === false)? "http://" : "").htmlspecialcharsbx($arUserField["VALUE"]);?>
								<a href="<?=$href?>"><?=$value?></a>
								<?break;
							case "UF_TWITTER":?>
								<a href="http://twitter.com/<?=$value?>"><?=$value?></a><?
								break;
							case "UF_SKYPE":
								echo $value;
								break;
							default:
								$GLOBALS["APPLICATION"]->IncludeComponent(
									"bitrix:system.field.view",
									$arUserField["USER_TYPE"]["USER_TYPE_ID"],
									array("arUserField" => $arUserField, "inChain" => "N"),
									null,
									array("HIDE_ICONS"=>"Y")
								);
						}
					?>
					</span><?
				endif;
			endforeach;?>
	</div>
	<div class="emp-info-block">
		<div class="emp-info-title"><?=GetMessage("SONET_COMMON_TITLE")?></div><?
			//Common INFO
			foreach ($arResult["UserFieldsMain"]["DATA"] as $field => $arUserField):
				if ($field != "SECOND_NAME" && (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0)):
					?><span class="emp-info-cell"><?=$arUserField["NAME"].":"?></span><span class="emp-info-cell"><?=$arUserField["VALUE"];?></span><?
				endif;
			endforeach;
			foreach ($arResult["UserPropertiesMain"]["DATA"] as $field => $arUserField):
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):
					?><span class="emp-info-cell"><?=$arUserField["EDIT_FORM_LABEL"].":"?></span><span
					class="emp-info-cell"><?
						switch ($field)
						{
							case "UF_DEPARTMENT1":
								echo htmlspecialcharsbx($arUserField["VALUE"]);
								break;
							default:
								$bInChain = ($field == "UF_DEPARTMENT" ? "Y" : "N");
								$GLOBALS["APPLICATION"]->IncludeComponent(
									"bitrix:system.field.view",
									$arUserField["USER_TYPE"]["USER_TYPE_ID"],
									array("arUserField" => $arUserField, "inChain" => $bInChain),
									null,
									array("HIDE_ICONS"=>"Y")
								);
						}
					?>
					</span><?
				endif;
			endforeach;
			if (is_array($arResult['MANAGERS']) && count($arResult['MANAGERS'])>0):
				?><span class="emp-info-cell"><?=GetMessage("SONET_MANAGERS").":"?></span><span
				class="emp-info-cell"><?$bFirst = true;
						foreach ($arResult['MANAGERS'] as $id => $sub_user):
							if (!$bFirst) echo ', '; else $bFirst = false;
							$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);?>
							<a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?endforeach;?></span><?

			endif;
			if (is_array($arResult['SUBORDINATE']) && count($arResult['SUBORDINATE'])>0):
				?><span class="emp-info-cell"><?=GetMessage("SONET_SUBORDINATE").":"?></span><span class="emp-info-cell"><?
					$bFirst = true;
					foreach ($arResult['SUBORDINATE'] as $id => $sub_user):
						if (!$bFirst) echo ', '; else $bFirst = false;
						$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);?>
						<a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?endforeach;?></span><?
			endif;?>
			<?if($arResult["User"]["ACTIVITY_STATUS"] != "active"):?>
			<span class="emp-info-cell"><?=GetMessage("SONET_ACTIVITY_STATUS").":"?></span><span class="emp-info-cell">
				<?if ($arResult["User"]["ACTIVITY_STATUS"] == "admin"):
					?><span class="employee-admin"><span class="employee-admin-left"></span><span class="employee-admin-text"><?=GetMessage("SONET_USER_".$arUser["ACTIVITY_STATUS"])?></span><span class="employee-admin-right"></span></span><?
				else:
					?><span class="employee-dept-post employee-dept-<?=$arUser["ACTIVITY_STATUS"]?>"><?=GetMessage("SONET_USER_".$arUser["ACTIVITY_STATUS"])?></span>
				<?endif?>
			</span>
			<?endif;?>
	</div>
	<?
//Additional INFO
	$additional = "";
	foreach ($arResult["UserFieldsPersonal"]["DATA"] as $field => $arUserField)
	{
		if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0)
		{
			$additional .= '<span class="emp-info-cell">'.$arUserField["NAME"].': '.$arUserField["VALUE"].'</span>';
		}
	};
	foreach ($arResult["UserPropertiesPersonal"]["DATA"] as $field => $arUserField)
	{
		if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0)
		{
			$additional .= '<span class="emp-info-cell">'.$arUserField["EDIT_FORM_LABEL"].': ';
			ob_start();
			$GLOBALS["APPLICATION"]->IncludeComponent(
				"bitrix:system.field.view",
				$arUserField["USER_TYPE"]["USER_TYPE_ID"],
				array("arUserField" => $arUserField, "inChain" => $field == "UF_DEPARTMENT" ? "Y" : "N"),
				null,
				array("HIDE_ICONS"=>"Y")
			);
			$additional .= ob_get_contents();
			ob_end_clean();
			$additional .= '</span>';
		}
	}

	/*if (is_array($arResult["Groups"]["List"]) && count($arResult["Groups"]["List"]) > 0)
	{
		$additional .= '<span class="emp-info-cell">'.GetMessage("SONET_GROUPS").': ';
		$bFirst = true;
		foreach ($arResult["Groups"]["List"] as $key => $group)
		{
				if (!$bFirst)
					$additional .= ', ';
				$bFirst = false;
				$additional .= '<a class="user-profile-link" href="'.CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_GROUP'], array("group_id" => $group["GROUP_ID"])).'">'.$group["GROUP_NAME"].'</a>';
		}
			$additional .= '</span>';
	}*/
	?>
	<?if (strlen($additional) > 0):?>
	<div class="emp-info-block emp-info-block-addit">
		<div class="emp-info-title"><?=GetMessage("SONET_ADDITIONAL_TITLE")?></div>
		<?=$additional?>
	</div>
	<?endif?>
</div>
<div class="emp-buttons-block">
	<?if (strlen($arResult["User"]["PERSONAL_MOBILE"]) > 0 || strlen($arResult["User"]["WORK_PHONE"]) > 0):?>
	<div class="emp-button-wrap">
		<a href="tel:<?=(strlen($arResult["User"]["PERSONAL_MOBILE"]) > 0) ? $arResult["User"]["PERSONAL_MOBILE"] : $arResult["User"]["WORK_PHONE"]?>" class="button emp-info-button accept-button" ontouchstart="BX.toggleClass(this, 'accept-button-press');" ontouchend="BX.toggleClass(this, 'accept-button-press');">
			<?=GetMessage("SONET_CALL_USER")?>
		</a>
	</div>
	<?endif;?>
	<?if ($arUser["ID"] != $USER->GetID()):?>
	<div class="emp-button-wrap">
		<?if (!isset($_REQUEST['FROM_DIALOG'])):?>
		<a href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_MESSAGES_CHAT'], array("user_id" => $arUser["ID"]))?>&FROM_PROFILE=Y" class="button usual-button emp-info-button" ontouchstart="BX.toggleClass(this, 'usual-button-press');" ontouchend="BX.toggleClass(this, 'usual-button-press');">
			<?=GetMessage("SONET_SEND_MESSAGE")?>
		</a>
		<?else:?>
		<a href="javascript:void(0)" onclick="app.closeController();" class="button usual-button emp-info-button" ontouchstart="BX.toggleClass(this, 'usual-button-press');" ontouchend="BX.toggleClass(this, 'usual-button-press');">
			<?=GetMessage("SONET_SEND_MESSAGE")?>
		</a>
		<?endif?>
	</div>
	<?endif?>
</div>