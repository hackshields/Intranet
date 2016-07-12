<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$arParams["AVATAR_SIZE"] = (isset($arParams["AVATAR_SIZE"]) ? intval($arParams["AVATAR_SIZE"]) : 58);

$arParams["PAGE_SIZE"] = intval($arParams["PAGE_SIZE"]);
if($arParams["PAGE_SIZE"] <= 0)
	$arParams["PAGE_SIZE"] = 20;

if (is_array($arResult["userCache"]))
{
	foreach ($arResult["userCache"] as $user_id => $arUser)
	{
		if (intval($arUser["PERSONAL_PHOTO"]) > 0)
		{
			$image_resize = CFile::ResizeImageGet(
				$arUser["PERSONAL_PHOTO"], 
				array(
					"width" => $arParams["AVATAR_SIZE"],
					"height" => $arParams["AVATAR_SIZE"]
				),
				BX_RESIZE_IMAGE_EXACT
			);
			$arResult["userCache"][$user_id]["PERSONAL_PHOTO_RESIZED"] = array("SRC" => $image_resize["src"]);
		}
	}
}

$arResult["urlToPostMobile"] = CComponentEngine::MakePathFromTemplate(htmlspecialcharsBack($arParams["PATH_TO_POST_MOBILE"]), array("post_id" => CBlogPost::GetPostID($arParams["POST_DATA"]["ID"], $arParams["POST_DATA"]["CODE"], $arParams["ALLOW_POST_CODE"]), "user_id" => $arParams["BLOG_DATA"]["ID"]["OWNER_ID"]));
$arResult["urlToPostMobile"] .= (strpos($arResult["urlToPostMobile"], "?") !== false ? "&" : "?");

$arResult["urlToMore"] = $arResult["urlToPostMobile"]."last_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y";
$arResult["urlToNew"] = $arResult["urlToPostMobile"]."new_comment_id=#comment_id#&comment_post_id=#post_id#&IFRAME=Y";

$arResult["newCount"] = (intval($arResult["newCountWOMark"]) > 0 ? $arResult["newCountWOMark"] : $arResult["newCount"]);
$arResult["newCount"] = ($arResult["newCount"] > 3 ? $arResult["newCount"] : 3);
?>