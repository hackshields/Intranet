<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();  
if (!empty($arResult["FatalError"]))
{
	echo  $arResult["FatalError"];
	return;
} 

global $USER;
$arUser = $arResult["User"];
if ($arUser["ACTIVITY_STATUS"] == "fired") $userActive = "Y"; elseif($arUser["ACTIVITY_STATUS"] == "invited")  $userActive = "D"; else $userActive = "N";

$APPLICATION->SetPageProperty("BodyClass", "page-one-column");
?>

<div class="user-profile-block-wrap">
	<div class="user-profile-block-wrap-l">
		<table class="user-profile-img-wrap" cellspacing="0">
			<tr>
				<td><?if(is_array($arResult["User"]["PersonalPhotoFile"])):?>
						<?=$arResult["User"]["PersonalPhotoImg"]?>
					<?else:?>
						<span class="user-profile-img-default"></span>
					<?endif?>
				</td>
			</tr>
		</table>
		
		<?if (
			$arUser["ACTIVITY_STATUS"] != "fired" 
			&& $arUser["ACTIVITY_STATUS"] != "invited" 
			&& $USER->GetID() != $arUser['ID']
		):?>
			<a class="webform-small-button webform-small-button-accept" href="javascript:void(0)" onclick="if (BX.IM) { BXIM.openMessenger(<?=$arUser['ID']?>); return false; } else { window.open('<?echo $url ?>', '', 'status=no,scrollbars=yes,resizable=yes,width=700,height=550,top='+Math.floor((screen.height - 550)/2-14)+',left='+Math.floor((screen.width - 700)/2-5)); return false; }"><span class="webform-small-button-left"></span><span class="webform-small-button-text"><?=GetMessage("SONET_SEND_MESSAGE")?></span><span class="webform-small-button-right"></span></a>
		<?endif;?>
	</div>
	<div class="user-profile-block-wrap-r">
		<?if ($arResult['CAN_EDIT_USER'] || $USER->GetID() == $arUser["ID"]):?>
		<div class="user-profile-events">
			<div class="user-profile-events-title"><?=GetMessage("SONET_ACTIONS")?></div>
			<div class="user-profile-events-cont">
				<a href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER_EDIT'], array("user_id" => $arUser["ID"]))?>" class="user-profile-events-item user-profile-edit"><i></i><?=GetMessage("SONET_EDIT_PROFILE")?></a><?
				if ($arUser["ACTIVITY_STATUS"] == "invited" && $USER->CanDoOperation('bitrix24_invite') && CModule::IncludeModule('bitrix24')):
					?><a id="link" href="javascript:void(0)" class="user-profile-events-item  user-profile-add-sub" onclick="var user_reinvite = 'reinvite_user_id_';
						<?if ($arUser["IS_EXTRANET"]):?> user_reinvite = user_reinvite + 'extranet_';<?endif;?>							
						BX.ajax.post(
							'/bitrix/tools/invite_dialog.php',
							{
								lang: BX.message('LANGUAGE_ID'),
								site_id: BX.message('SITE_ID') || '',
								reinvite: user_reinvite+<?=$arUser["ID"]?>,
								sessid: BX.bitrix_sessid()
							},
							BX.delegate(function(result)
							{							
								var InviteAccessPopup = BX.PopupWindowManager.create('invite_access', this, {
									content: '<p><?=GetMessageJS("SONET_REINVITE_ACCESS")?></p>',
									offsetLeft:27,
									offsetTop:7,
									autoHide:true
								});
							
								InviteAccessPopup.show();
							},
							this)
						);">
						<i></i><?=GetMessage("SONET_REINVITE")?>
					</a><?
				endif;

				if ($USER->CanDoOperation("edit_all_users") && $USER->GetID() != $arUser['ID'] && !(IsModuleInstalled("bitrix24") && $arUser["ID"] === "1")):
					?><a  href="javascript:void(0)" onclick="if (confirmUser(event))
						BX.ajax.get(
							'<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER_EDIT'], array("user_id" => $arUser["ID"]))."?ACTIVE=".$userActive?>' + '&js=1', 
							BX.delegate(
								function() 
								{
									window.location.reload();
								}, 
								this
							)
						);
						return false;" class="user-profile-events-item user-profile-dismiss"><i></i><?if ($arUser["ACTIVITY_STATUS"] == "invited") echo GetMessage('SONET_DELETE'); elseif ($arUser["ACTIVITY_STATUS"] == "fired") echo GetMessage('SONET_RESTORE');else echo GetMessage('SONET_DEACTIVATE');?></a><?
				endif;
				//extrnaet to intrnaet
				if ($arUser["ACTIVITY_STATUS"] == "extranet" && IsModuleInstalled('bitrix24') && $USER->CanDoOperation("edit_all_users")):
					$arParamsExtr2Intr["MESS"] = array(
						"BX24_TITLE" => GetMessage("BX24_TITLE"),
						"BX24_BUTTON" => GetMessage("BX24_BUTTON"),
						"BX24_CLOSE_BUTTON" => GetMessage("BX24_CLOSE_BUTTON"),
						"BX24_LOADING" => GetMessage("BX24_LOADING"),
						"BX24_EXTR_USER_ID" => $arUser["ID"],						
					);

					?><a href="javascript:void(0)" onclick="showExtranet2IntranetForm(<?echo CUtil::PhpToJSObject($arParamsExtr2Intr)?>); return false;"  class="user-profile-events-item user-profile-add-sub"><i></i><?=GetMessage("SONET_EXTRANET_TO_INTRANET")?></a><?
				endif;
			?></div>

			<div class="user-profile-events-cont"><?
				if (
					file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/socialnetwork.admin.set")
					&& $arResult["SHOW_SONET_ADMIN"]
					
				):
					?><?
					$APPLICATION->IncludeComponent(
						"bitrix:socialnetwork.admin.set",
						"",
						Array(
							"PROCESS_ONLY" => "Y"
						),
						$component,
						array("HIDE_ICONS" => "Y")
					);
					?><?
					?><a href="#" class="user-profile-events-item" onclick="__SASSetAdmin(); return false;"><i></i><?=GetMessage("SONET_SONET_ADMIN_ON")?></a><?
				endif;
			?></div>

		</div><?
		endif;

		if(CModule::IncludeModule("socialnetwork") && CModule::IncludeModule("intranet"))
		{						
			$APPLICATION->IncludeComponent(
				"bitrix:intranet.absence.user",
				"gadget",
				array(
					"ID" => $arUser["ID"],						
				),
				false,
				Array("HIDE_ICONS"=>"Y")
			);
		}

	?></div>
	<div class="user-profile-block-wrap-cont">
		<table class="user-profile-block" cellspacing="0">
			<tr>
				<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_CONTACT_TITLE")?></td>
			</tr><?
			foreach ($arResult["UserFieldsContact"]["DATA"] as $field => $arUserField):
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):?>
				<tr>
					<td class="user-profile-nowrap"><?=$arUserField["NAME"].":"?></td>
					<td><?=$arUserField["VALUE"];?></td>
				</tr><?					 
				endif;										
			endforeach;                    
			foreach ($arResult["UserPropertiesContact"]["DATA"] as $field => $arUserField): 
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):?>	
				<tr>
					<td class="user-profile-nowrap"><?=$arUserField["EDIT_FORM_LABEL"].":"?></td>
					<td><?
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
							case "UF_SKYPE":?>
								<a href="callto:<?=$value?>"><?=$value?></a><?
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
					</td>
				</tr><? 									
				endif;   
			endforeach;				
			?><tr>
				<td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_COMMON_TITLE")?></td>
			</tr><?
			foreach ($arResult["UserFieldsMain"]["DATA"] as $field => $arUserField): 
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):?>
				<tr>
					<td class="user-profile-nowrap"><?=$arUserField["NAME"].":"?></td>
					<td><?=$arUserField["VALUE"];?></td>
				</tr><?					 
				endif;										
			endforeach;                    
			foreach ($arResult["UserPropertiesMain"]["DATA"] as $field => $arUserField):   
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0):?>	
				<tr>
					<td class="user-profile-nowrap"><?=$arUserField["EDIT_FORM_LABEL"].":"?></td>
					<td><?  $bInChain = ($field == "UF_DEPARTMENT" ? "Y" : "N");
							$GLOBALS["APPLICATION"]->IncludeComponent(
								"bitrix:system.field.view", 
								$arUserField["USER_TYPE"]["USER_TYPE_ID"], 
								array("arUserField" => $arUserField, "inChain" => $bInChain),
								null,
								array("HIDE_ICONS"=>"Y")
							);
					?>							
					</td>
				</tr><? 										
				endif;   
			endforeach;
			if (is_array($arResult['MANAGERS']) && count($arResult['MANAGERS'])>0):?>
				<tr>
				<td class="user-profile-nowrap"><?=GetMessage("SONET_MANAGERS").":"?></td>
				<td><?$bFirst = true;
						foreach ($arResult['MANAGERS'] as $id => $sub_user):   
							if (!$bFirst) echo ', '; else $bFirst = false;
							$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);?>
							<a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?if (strlen($sub_user["WORK_POSITION"]) > 0) echo " (".$sub_user["WORK_POSITION"].")";?><?endforeach;?></td></tr><?						

			endif;
			if (is_array($arResult['SUBORDINATE']) && count($arResult['SUBORDINATE'])>0):?>
				<tr>
				<td class="user-profile-nowrap"><?=GetMessage("SONET_SUBORDINATE").":"?></td>
				<td><?$bFirst = true;
						foreach ($arResult['SUBORDINATE'] as $id => $sub_user):   
							if (!$bFirst) echo ', '; else $bFirst = false;
							$name = CUser::FormatName($arParams['NAME_TEMPLATE'], $sub_user, true, false);?>
							<a class="user-profile-link" href="<?=CComponentEngine::MakePathFromTemplate($arParams['PATH_TO_USER'], array("user_id" => $sub_user["ID"]))?>"><?=$name?></a><?if (strlen($sub_user["WORK_POSITION"]) > 0) echo " (".$sub_user["WORK_POSITION"].")";?><?endforeach;?></td></tr><?						
			endif;?>

			<?if($arResult["User"]["ACTIVITY_STATUS"] != "active"):?>
			<tr class="user-profile-status"><td class="user-profile-nowrap"><?=GetMessage("SONET_ACTIVITY_STATUS").":"?></td><td>
				<?if ($arResult["User"]["ACTIVITY_STATUS"] == "admin"):
					?><span class="employee-admin"><span class="employee-admin-left"></span><span class="employee-admin-text"><?=GetMessage("SONET_USER_".$arUser["ACTIVITY_STATUS"])?></span><span class="employee-admin-right"></span></span><?
				else:
					?><span class="employee-dept-post employee-dept-<?=$arUser["ACTIVITY_STATUS"]?>"><?=GetMessage("SONET_USER_".$arUser["ACTIVITY_STATUS"])?></span>
				<?endif?>
			</td></tr>
			<?endif;?>

			<?
			$additional = "";
			foreach ($arResult["UserFieldsPersonal"]["DATA"] as $field => $arUserField)
			{
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0)
				{
					$additional .= '<tr>
						<td class="user-profile-nowrap">'.$arUserField["NAME"].':</td>
						<td>'.$arUserField["VALUE"].'</td></tr>';
				}									
			};

			foreach ($arResult["UserPropertiesPersonal"]["DATA"] as $field => $arUserField)
			{ 
				if (is_array($arUserField["VALUE"]) && count($arUserField["VALUE"]) > 0 || !is_array($arUserField["VALUE"]) && StrLen($arUserField["VALUE"]) > 0)
				{	
					$additional .= '<tr><td class="user-profile-nowrap">'.$arUserField["EDIT_FORM_LABEL"].':</td><td>';

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

					$additional .= '</td></tr>';
				}  
			}	

			if (is_array($arResult["Groups"]["List"]) && count($arResult["Groups"]["List"]) > 0)
			{
				$additional .= '<tr><td class="user-profile-nowrap">'.GetMessage("SONET_GROUPS").':</td><td>';
				$bFirst = true;								
				foreach ($arResult["Groups"]["List"] as $key => $group)
				{
						if (!$bFirst) 
							$additional .= ', '; 
						$bFirst = false;
						$additional .= '<a class="user-profile-link" href="'.$group["GROUP_URL"].'">'.$group["GROUP_NAME"].'</a>';					
				}			
					$additional .= '</td></tr>';
			}
			?> 
			<? if (strlen($additional) > 0):?>
				<tr><td class="user-profile-block-title" colspan="2"><?=GetMessage("SONET_ADDITIONAL_TITLE")?></td></tr><?=$additional?>
			<?endif?>

		</table>		
	</div>
</div>

<script type="text/javascript">
function confirmUser(event) 
{ 
	if (confirm("<?
		if ($arUser["ACTIVITY_STATUS"] == "fired")
			echo GetMessage('SOCNET_CONFIRM_RECOVER');
		elseif ($arUser["ACTIVITY_STATUS"] == "invited")
			echo GetMessage('SOCNET_CONFIRM_DELETE');
		else
			echo GetMessage('SOCNET_CONFIRM_FIRE');?>")) 
		return true; 
	else
		return false;
}
</script>