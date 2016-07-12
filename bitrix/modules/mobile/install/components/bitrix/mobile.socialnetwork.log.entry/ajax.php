<?
define("NO_KEEP_STATISTIC", true);
define("BX_STATISTIC_BUFFER_USED", false);
define("NO_LANG_FILES", true);
define("NOT_CHECK_PERMISSIONS", true);
define("BX_PUBLIC_TOOLS", true);

$site_id = isset($_REQUEST["site"]) && is_string($_REQUEST["site"]) ? trim($_REQUEST["site"]) : "";
$site_id = substr(preg_replace("/[^a-z0-9_]/i", "", $site_id), 0, 2);

define("SITE_ID", $site_id);

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/bx_root.php");

$action = isset($_REQUEST["action"]) && is_string($_REQUEST["action"]) ? trim($_REQUEST["action"]) : "";

$lng = isset($_REQUEST["lang"]) && is_string($_REQUEST["lang"]) ? trim($_REQUEST["lang"]) : "";
$lng = substr(preg_replace("/[^a-z0-9_]/i", "", $lng), 0, 2);

$ls = isset($_REQUEST["ls"]) && is_string($_REQUEST["ls"]) ? trim($_REQUEST["ls"]) : "";
$ls_arr = isset($_REQUEST["ls_arr"])? $_REQUEST["ls_arr"]: "";

$as = isset($_REQUEST["as"]) ? intval($_REQUEST["as"]) : 58;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

$rsSite = CSite::GetByID($site_id);
if ($arSite = $rsSite->Fetch())
	define("LANGUAGE_ID", $arSite["LANGUAGE_ID"]);
else
	define("LANGUAGE_ID", "en");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bitrix/mobile.socialnetwork.log.entry/include.php");

$APPLICATION->IncludeComponent("bitrix:mobile.data", "", Array(
		"START_PAGE" => "/mobile/index.php",
		"MENU_PAGE" => "/mobile/left.php"
	),
	false,
	Array("HIDE_ICONS" => "Y")
);

__IncludeLang(dirname(__FILE__)."/lang/".$lng."/ajax.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if(CModule::IncludeModule("socialnetwork"))
{
	$bCurrentUserIsAdmin = CSocNetUser::IsCurrentUserModuleAdmin();

	// write and close session to prevent lock;
	session_write_close();

	$arResult = array();

	if (!$GLOBALS["USER"]->IsAuthorized())
		$arResult[0] = "*";
	elseif (!check_bitrix_sessid())
		$arResult[0] = "*";
	elseif ($action == "add_comment")
	{
		$log_id = $_REQUEST["log_id"];
		if ($arLog = CSocNetLog::GetByID($log_id))
		{
			$arCommentEvent = CSocNetLogTools::FindLogCommentEventByLogEventID($arLog["EVENT_ID"]);
			if ($arCommentEvent)
			{
				$feature = CSocNetLogTools::FindFeatureByEventID($arCommentEvent["EVENT_ID"]);

				if ($feature && array_key_exists("OPERATION_ADD", $arCommentEvent) && strlen($arCommentEvent["OPERATION_ADD"]) > 0)
					$bCanAddComments = CSocNetFeaturesPerms::CanPerformOperation($GLOBALS["USER"]->GetID(), $arLog["ENTITY_TYPE"], $arLog["ENTITY_ID"], ($feature == "microblog" ? "blog" : $feature), $arCommentEvent["OPERATION_ADD"], $bCurrentUserIsAdmin);
				else
					$bCanAddComments = true;

				if ($bCanAddComments)
				{
					// add source object and get source_id, $source_url
					$arParams = array(
						"PATH_TO_SMILE" => $_REQUEST["p_smile"],
						"PATH_TO_USER_BLOG_POST" => $_REQUEST["p_ubp"],
						"PATH_TO_GROUP_BLOG_POST" => $_REQUEST["p_gbp"],
						"PATH_TO_USER_MICROBLOG_POST" => $_REQUEST["p_umbp"],
						"PATH_TO_GROUP_MICROBLOG_POST" => $_REQUEST["p_gmbp"],
						"BLOG_ALLOW_POST_CODE" => $_REQUEST["bapc"]
					);
					$parser = new logTextParser(LANGUAGE_ID, $arParams["PATH_TO_SMILE"]);

					$comment_text = $_REQUEST["message"];
					CUtil::decodeURIComponent($comment_text);
					$comment_text = Trim($comment_text);

					if (strlen($comment_text) > 0)
					{
						$arAllow = array(
							"HTML" => "N",
							"ANCHOR" => "Y",
							"LOG_ANCHOR" => "N",
							"BIU" => "N",
							"IMG" => "N",
							"LIST" => "N",
							"QUOTE" => "N",
							"CODE" => "N",
							"FONT" => "N",
							"UPLOAD" => $arForum["ALLOW_UPLOAD"],
							"NL2BR" => "N",
							"SMILES" => "N"
						);

						$arFields = array(
							"ENTITY_TYPE" => $arLog["ENTITY_TYPE"],
							"ENTITY_ID" => $arLog["ENTITY_ID"],
							"EVENT_ID" => $arCommentEvent["EVENT_ID"],
							"=LOG_DATE" => $GLOBALS["DB"]->CurrentTimeFunction(),
							"MESSAGE" => $parser->convert($comment_text, array(), $arAllow),
							"TEXT_MESSAGE" => $comment_text,
							"URL" => $source_url,
							"MODULE_ID" => false,
							"SOURCE_ID" => $source_id,
							"LOG_ID" => $arLog["TMP_ID"],
							"USER_ID" => $GLOBALS["USER"]->GetID(),
							"PATH_TO_USER_BLOG_POST" => $arParams["PATH_TO_USER_BLOG_POST"],
							"PATH_TO_GROUP_BLOG_POST" => $arParams["PATH_TO_GROUP_BLOG_POST"],
							"PATH_TO_USER_MICROBLOG_POST" => $arParams["PATH_TO_USER_MICROBLOG_POST"],
							"PATH_TO_GROUP_MICROBLOG_POST" => $arParams["PATH_TO_GROUP_MICROBLOG_POST"],
							"BLOG_ALLOW_POST_CODE" => $arParams["BLOG_ALLOW_POST_CODE"]
						);

						$comment = CSocNetLogComments::Add($arFields, true);
						if (!is_array($comment) && intval($comment) > 0)
							$arResult["commentID"] = $comment;
						elseif (is_array($comment) &&  array_key_exists("MESSAGE", $comment) && strlen($comment["MESSAGE"]) > 0)
						{
							$arResult["strMessage"] = $comment["MESSAGE"];
							$arResult["commentText"] = $comment_text;
						}
					}
					else
						$arResult["strMessage"] = GetMessage("SONET_LOG_COMMENT_EMPTY");
				}
				else
					$arResult["strMessage"] = GetMessage("SONET_LOG_COMMENT_NO_PERMISSIONS");
			}
		}
	}
	elseif ($action == "get_comment")
	{
		$comment_id = $_REQUEST["cid"];

		if ($arComment = CSocNetLogComments::GetByID($comment_id))
		{
			$arParams["DATE_TIME_FORMAT"] = $_REQUEST["dtf"];

			$dateFormated = FormatDate(
				$GLOBALS['DB']->DateFormatToPHP(FORMAT_DATE),
				MakeTimeStamp($arComment["LOG_DATE"])
			);
			$timeFormated = FormatDateFromDB($arComment["LOG_DATE"], (stripos($arParams["DATE_TIME_FORMAT"], 'a') || ($arParams["DATE_TIME_FORMAT"] == 'FULL' && IsAmPmMode()) !== false ? 'H:MI T' : 'HH:MI'));
			$dateTimeFormated = FormatDate(
				(!empty($arParams['DATE_TIME_FORMAT']) ? ($arParams['DATE_TIME_FORMAT'] == 'FULL' ? $GLOBALS['DB']->DateFormatToPHP(str_replace(':SS', '', FORMAT_DATETIME)) : $arParams['DATE_TIME_FORMAT']) : $GLOBALS['DB']->DateFormatToPHP(FORMAT_DATETIME)),
				MakeTimeStamp($arComment["LOG_DATE"])
			);
			if (strcasecmp(LANGUAGE_ID, 'EN') !== 0 && strcasecmp(LANGUAGE_ID, 'DE') !== 0)
			{
				$dateFormated = ToLower($dateFormated);
				$dateTimeFormated = ToLower($dateTimeFormated);
			}
			// strip current year
			if (!empty($arParams['DATE_TIME_FORMAT']) && ($arParams['DATE_TIME_FORMAT'] == 'j F Y G:i' || $arParams['DATE_TIME_FORMAT'] == 'j F Y g:i a'))
			{
				$dateTimeFormated = ltrim($dateTimeFormated, '0');
				$curYear = date('Y');
				$dateTimeFormated = str_replace(array('-'.$curYear, '/'.$curYear, ' '.$curYear, '.'.$curYear), '', $dateTimeFormated);
			}

			if (intval($arComment["USER_ID"]) > 0)
			{
				$arParams = array(
					"PATH_TO_USER" => $_REQUEST["p_user"],
					"NAME_TEMPLATE" => $_REQUEST["nt"],
					"SHOW_LOGIN" => $_REQUEST["sl"],
					"AVATAR_SIZE" => $as,
					"PATH_TO_SMILE" => $_REQUEST["p_smile"]
				);

				$arUser = array(
					"ID" => $arComment["USER_ID"],
					"NAME" => $arComment["~CREATED_BY_NAME"],
					"LAST_NAME" => $arComment["~CREATED_BY_LAST_NAME"],
					"SECOND_NAME" => $arComment["~CREATED_BY_SECOND_NAME"],
					"LOGIN" => $arComment["~CREATED_BY_LOGIN"],
					"PERSONAL_PHOTO" => $arComment["~CREATED_BY_PERSONAL_PHOTO"],
					"PERSONAL_GENDER" => $arComment["~CREATED_BY_PERSONAL_GENDER"],
				);
				$bUseLogin = $arParams["SHOW_LOGIN"] != "N" ? true : false;
				$arCreatedBy = array(
					"FORMATTED" => CUser::FormatName($arParams["NAME_TEMPLATE"], $arUser, $bUseLogin),
					"URL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_USER"], array("user_id" => $arComment["USER_ID"], "id" => $arComment["USER_ID"]))
				);

			}
			else
				$arCreatedBy = array("FORMATTED" => GetMessage("SONET_LOG_CREATED_BY_ANONYMOUS"));

			$arTmpCommentEvent = array(
				"LOG_DATE" => $arComment["LOG_DATE"],
				"LOG_DATE_FORMAT" => $arComment["LOG_DATE_FORMAT"],
				"LOG_DATE_DAY" => ConvertTimeStamp(MakeTimeStamp($arComment["LOG_DATE"]), "SHORT"),
				"LOG_TIME_FORMAT" => $timeFormated,
				"MESSAGE" => $arComment["MESSAGE"],
				"MESSAGE_FORMAT" => $arComment["~MESSAGE"],
				"CREATED_BY" => $arCreatedBy,
				"AVATAR_SRC" => CSocNetLogTools::FormatEvent_CreateAvatar($arUser, $arParams, ""),
				"USER_ID" => $arComment["USER_ID"]
			);

			$arEventTmp = CSocNetLogTools::FindLogCommentEventByID($arComment["EVENT_ID"]);
			if (
				$arEventTmp
				&& array_key_exists("CLASS_FORMAT", $arEventTmp)
				&& array_key_exists("METHOD_FORMAT", $arEventTmp)
			)
			{
				$arFIELDS_FORMATTED = call_user_func(array($arEventTmp["CLASS_FORMAT"], $arEventTmp["METHOD_FORMAT"]), $arComment, $arParams);
				$arTmpCommentEvent["MESSAGE_FORMAT"] = htmlspecialcharsback($arFIELDS_FORMATTED["EVENT_FORMATTED"]["MESSAGE"]);
			}

			$arResult["arCommentFormatted"] = $arTmpCommentEvent;
		}
	}
	elseif ($action == "get_comments")
	{
		$arResult["arComments"] = array();

		$log_tmp_id = $_REQUEST["logid"];
		$last_comment_id = $_REQUEST["last_comment_id"];

		if (intval($log_tmp_id) > 0)
		{
			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"AVATAR_SIZE_COMMENT" => $as,
				"PATH_TO_SMILE" => $_REQUEST["p_smile"],
				"DATE_TIME_FORMAT" => $_REQUEST["dtf"]
			);

			$cache_time = 31536000;
			$cache = new CPHPCache;

			$arCacheID = array();
			$arKeys = array(
				"AVATAR_SIZE_COMMENT",
				"NAME_TEMPLATE",
				"NAME_TEMPLATE_WO_NOBR",
				"SHOW_LOGIN",
				"DATE_TIME_FORMAT",
				"PATH_TO_USER",
				"PATH_TO_GROUP",
				"PATH_TO_CONPANY_DEPARTMENT"
			);
			foreach($arKeys as $param_key)
			{
				if (array_key_exists($param_key, $arParams))
					$arCacheID[$param_key] = $arParams[$param_key];
				else
					$arCacheID[$param_key] = false;
			}

			$cache_id = "log_comments_".$log_tmp_id."_".md5(serialize($arCacheID))."_mobile_app_".SITE_ID."_".LANGUAGE_ID."_".CTimeZone::GetOffset();
			$cache_path = "/sonet/log/".$log_tmp_id."/comments/";

			if (
				is_object($cache)
				&& $cache->InitCache($cache_time, $cache_id, $cache_path)
			)
			{
				$arCacheVars = $cache->GetVars();
				$arResult["arComments"] = $arCacheVars["COMMENTS_FULL_LIST"];
			}
			else
			{
				$arCommentsFullList = array();

				if (is_object($cache))
					$cache->StartDataCache($cache_time, $cache_id, $cache_path);

				if (defined("BX_COMP_MANAGED_CACHE"))
				{
					$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
					$GLOBALS["CACHE_MANAGER"]->RegisterTag("SONET_LOG_".$log_tmp_id);
				}

				$arFilter = array("LOG_ID" => $log_tmp_id);
				$arListParams = array("USE_SUBSCRIBE" => "N");

				$dbComments = CSocNetLogComments::GetList(
					array("LOG_DATE" => "ASC"),
					$arFilter,
					false,
					false,
					$arSelect,
					$arListParams
				);

				while($arComments = $dbComments->GetNext())
				{
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("USER_NAME_".intval($arComments["USER_ID"]));
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("SONET_LOG_COMMENT_".intval($arComments["ID"]));
					}

					$arResult["arComments"][] = __SLMGetLogCommentRecord($arComments, $arParams, false);
				}

				if (is_object($cache))
				{
					$arCacheData = Array(
						"COMMENTS_FULL_LIST" => $arResult["arComments"]
					);
					$cache->EndDataCache($arCacheData);
					if(defined("BX_COMP_MANAGED_CACHE"))
						$GLOBALS["CACHE_MANAGER"]->EndTagCache();
				}				
			}

			foreach ($arResult["arComments"] as $key => $arCommentTmp)
			{
				if ($key === 0)
					$rating_entity_type = $arCommentTmp["EVENT"]["RATING_TYPE_ID"];

				if ($arCommentTmp["EVENT"]["ID"] >= $last_comment_id)
					unset($arResult["arComments"][$key]);
				else
					$arCommentID[] = $arCommentTmp["EVENT"]["RATING_ENTITY_ID"];
			}

			$arRatingComments = array();
			if(
				!empty($arCommentID)
				&& strlen($rating_entity_type) > 0
			)
				$arRatingComments = CRatings::GetRatingVoteResult($rating_entity_type, $arCommentID);

			foreach($arResult["arComments"] as $key => $arCommentTmp)
			{
				if (array_key_exists($arCommentTmp["EVENT"]["RATING_ENTITY_ID"], $arRatingComments))
				{
					$arResult["arComments"][$key]["EVENT"]["RATING_USER_VOTE_VALUE"] = $arRatingComments[$arCommentTmp["EVENT"]["RATING_ENTITY_ID"]]["USER_VOTE"];
					$arResult["arComments"][$key]["EVENT"]["RATING_USER_HAS_VOTED"] = $arRatingComments[$arCommentTmp["EVENT"]["RATING_ENTITY_ID"]]["USER_HAS_VOTED"];
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"] = $arRatingComments[$arCommentTmp["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_POSITIVE_VOTES"];
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_NEGATIVE_VOTES"] = $arRatingComments[$arCommentTmp["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_NEGATIVE_VOTES"];
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_VALUE"] = $arRatingComments[$arCommentTmp["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_VALUE"];
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_VOTES"] = $arRatingComments[$arCommentTmp["EVENT"]["RATING_ENTITY_ID"]]["TOTAL_VOTES"];
				}
				else
				{
					$arResult["arComments"][$key]["EVENT"]["RATING_USER_VOTE_VALUE"] = 0;
					$arResult["arComments"][$key]["EVENT"]["RATING_USER_HAS_VOTED"] = "N";
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_POSITIVE_VOTES"] = 0;
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_NEGATIVE_VOTES"] = 0;
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_VALUE"] = 0;
					$arResult["arComments"][$key]["EVENT"]["RATING_TOTAL_VOTES"] = 0;
				}
				
				if (strlen($rating_entity_type) > 0)
					$arResult["arComments"][$key]["EVENT_FORMATTED"]["ALLOW_VOTE"] = CRatings::CheckAllowVote(
						array(
							"ENTITY_TYPE_ID" => $rating_entity_type,
							"OWNER_ID" => $arResult["arComments"][$key]["EVENT"]["USER_ID"]
						)
					);
			}			
		}
	}
	elseif ($action == "get_more_destination")
	{
		$arResult["arDestinations"] = false;
		$log_id = intval($_REQUEST["log_id"]);
		$author_id = intval($_REQUEST["author_id"]);
		$iDestinationLimit = intval($_REQUEST["dlim"]);

		if ($log_id > 0)
		{
			$dbRight = CSocNetLogRights::GetList(array(), array("LOG_ID" => $log_id));
			while ($arRight = $dbRight->Fetch())
				$arRights[] = $arRight["GROUP_CODE"];

			$arParams = array(
				"PATH_TO_USER" => $_REQUEST["p_user"],
				"PATH_TO_GROUP" => $_REQUEST["p_group"],
				"PATH_TO_CONPANY_DEPARTMENT" => $_REQUEST["p_dep"],
				"NAME_TEMPLATE" => $_REQUEST["nt"],
				"SHOW_LOGIN" => $_REQUEST["sl"],
				"DESTINATION_LIMIT" => 100,
				"CHECK_PERMISSIONS_DEST" => "N"
			);

			$arDestinations = CSocNetLogTools::FormatDestinationFromRights($arRights, array_merge($arParams, array("CREATED_BY" => $author_id)), $iMoreCount);
			if (is_array($arDestinations))
			{
				$iDestinationsHidden = 0;
				$arGroupID = array();

				// get tagged cached available groups and intersect
				$cache = new CPHPCache;	
				$cache_id = $GLOBALS["USER"]->GetID();
				$cache_path = "/sonet/groups_available/".$GLOBALS["USER"]->GetID()."/";

				if ($cache->InitCache($cache_time, $cache_id, $cache_path))
				{
					$arCacheVars = $cache->GetVars();
					$arGroupID = $arCacheVars["arGroupID"];
				}
				else
				{
					$cache->StartDataCache($cache_time, $cache_id, $cache_path);
					if (defined("BX_COMP_MANAGED_CACHE"))
					{
						$GLOBALS["CACHE_MANAGER"]->StartTagCache($cache_path);
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_user2group_U".$GLOBALS["USER"]->GetID());
						$GLOBALS["CACHE_MANAGER"]->RegisterTag("sonet_group");
					}

					$rsGroup = CSocNetGroup::GetList(
						array(),
						array("CHECK_PERMISSIONS" => $GLOBALS["USER"]->GetID()),
						false,
						false,
						array("ID")
					);
					while($arGroup = $rsGroup->Fetch())
						$arGroupID[] = $arGroup["ID"];

					$arCacheData = array(
						"arGroupID" => $arGroupID
					);
					$cache->EndDataCache($arCacheData);
					if(defined("BX_COMP_MANAGED_CACHE"))
						$GLOBALS["CACHE_MANAGER"]->EndTagCache();
				}

				foreach($arDestinations as $key => $arDestination)
				{
					if (
						array_key_exists("TYPE", $arDestination)
						&& array_key_exists("ID", $arDestination)
						&& $arDestination["TYPE"] == "SG"
						&& !in_array(intval($arDestination["ID"]), $arGroupID)
					)
					{
						unset($arDestinations[$key]);
						$iDestinationsHidden++;
					}
				}

				$arResult["arDestinations"] = array_slice($arDestinations, $iDestinationLimit);
				$arResult["iDestinationsHidden"] = $iDestinationsHidden;
			}
		}
	}

	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	echo CUtil::PhpToJSObject($arResult);
}

define('PUBLIC_AJAX_MODE', true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_after.php");
?>