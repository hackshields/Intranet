<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists("__blogUFfileEditMobile"))
{
	function __blogUFfileEditMobile($arResult, $arParams)
	{
		$result = false;
		if (strpos($arParams['arUserField']['FIELD_NAME'], 'UF_BLOG_POST_DOC') === 0 || strpos($arParams['arUserField']['FIELD_NAME'], 'UF_BLOG_COMMENT_DOC') === 0)
		{
			$componentParams = array(
				'INPUT_NAME' => $arParams["arUserField"]["FIELD_NAME"],
				'INPUT_NAME_UNSAVED' => 'FILE_NEW_TMP',
//				'INPUT_VALUE' => $arResult["VALUE"],
				'MAX_FILE_SIZE' => (intval($arParams['arUserField']['SETTINGS']['MAX_ALLOWED_SIZE']) > 0 ? $arParams['arUserField']['SETTINGS']['MAX_ALLOWED_SIZE'] : 5000000),
				'MULTIPLE' => $arParams['arUserField']['MULTIPLE'],
				'MODULE_ID' => 'uf',
				'ALLOW_UPLOAD' => 'I',
			);

			$GLOBALS["APPLICATION"]->IncludeComponent('bitrix:mobile.file.upload', '', $componentParams, false, Array("HIDE_ICONS" => "Y"));
		}

		return true;
	}
}

if (
	intval($arParams["SOCNET_GROUP_ID"]) > 0
	&& CModule::IncludeModule("socialnetwork")
)
{
	$arSonetGroup = CSocNetGroup::GetByID($arParams["SOCNET_GROUP_ID"]);
	if ($arSonetGroup)
		$arResult["SONET_GROUP_NAME"] = $arSonetGroup["NAME"];
}

if (!empty($arParams["POST_PROPERTY"]))
{
	$arPostFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("BLOG_POST", 0, LANGUAGE_ID);
	if (count($arParams["POST_PROPERTY"]) > 0)
	{
		foreach ($arPostFields as $FIELD_NAME => $arPostField)
		{
			if (!in_array($FIELD_NAME, $arParams["POST_PROPERTY"]))
				continue;

			$arPostField["EDIT_FORM_LABEL"] = strLen($arPostField["EDIT_FORM_LABEL"]) > 0 ? $arPostField["EDIT_FORM_LABEL"] : $arPostField["FIELD_NAME"];
			$arPostField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arPostField["EDIT_FORM_LABEL"]);
			$arPostField["~EDIT_FORM_LABEL"] = $arPostField["EDIT_FORM_LABEL"];
			if(strlen($arResult["ERROR_MESSAGE"]) > 0 && !empty($_POST[$FIELD_NAME]))
				$arPostField["VALUE"] = $_POST[$FIELD_NAME];

			$arPostPropertiesData[$FIELD_NAME] = $arPostField;
		}
	}
}

$arParams["USER_FIELDS"] = array(
	"SHOW" => (!empty($arPostPropertiesData) ? "Y" : "N"),
	"VALUE" => $arPostPropertiesData
);

$arPostUnSentJSON = CUserOptions::GetOption("mobile", "post_unsent", false);
if ($arPostUnSentJSON && is_array($arPostUnSentJSON) && isset($arPostUnSentJSON["data"]))
{
	if (
		isset($arResult["arPostUnSent"])
		&& isset($arResult["arPostUnSent"]["UF_BLOG_POST_DOC"])
		&& is_array($arResult["arPostUnSent"]["UF_BLOG_POST_DOC"])
		&& count($arResult["arPostUnSent"]["UF_BLOG_POST_DOC"]) > 0
	)
		$_SESSION["MFU_UPLOADED_FILES"] = $arResult["arPostUnSent"]["UF_BLOG_POST_DOC"];
}
?>