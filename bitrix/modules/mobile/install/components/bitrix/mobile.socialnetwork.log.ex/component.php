<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

CPageOption::SetOptionString("main", "nav_page_in_session", "N");

if (!CModule::IncludeModule("socialnetwork"))
{
	ShowError(GetMessage("SONET_MODULE_NOT_INSTALL"));
	return;
}

if (!$GLOBALS["USER"]->IsAuthorized())
{
	ShowError(GetMessage("SONET_SLM_NOT_AUTHORIZED"));
	return;
}

if (!array_key_exists("USE_FOLLOW", $arParams) || strLen($arParams["USE_FOLLOW"]) <= 0)
	$arParams["USE_FOLLOW"] = "Y";

// rating
$arParams["RATING_TYPE"] = "like";

$arParams["PATH_TO_USER"] = trim($arParams["PATH_TO_USER"]);
$arParams["PATH_TO_GROUP"] = trim($arParams["PATH_TO_GROUP"]);
$arParams["PATH_TO_SMILE"] = trim($arParams["PATH_TO_SMILE"]);
if (strlen($arParams["PATH_TO_SMILE"]) <= 0)
	$arParams["PATH_TO_SMILE"] = "/bitrix/images/socialnetwork/smile/";

$arParams["GROUP_ID"] = IntVal($arParams["GROUP_ID"]); // group page
$arParams["USER_ID"] = IntVal($arParams["USER_ID"]); // profile page
$arParams["LOG_ID"] = IntVal($arParams["LOG_ID"]); // log entity page

$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE"] ? $arParams["NAME_TEMPLATE"] : CSite::GetNameFormat();
$arParams["SHOW_RATING"] = (isset($arParams["SHOW_RATING"]) ? $arParams["SHOW_RATING"] : "Y");

$arParams["NAME_TEMPLATE_WO_NOBR"] = str_replace(
	array("#NOBR#", "#/NOBR#"),
	array("", ""),
	$arParams["NAME_TEMPLATE"]
);
$arParams["NAME_TEMPLATE"] = $arParams["NAME_TEMPLATE_WO_NOBR"];
$bUseLogin = $arParams['SHOW_LOGIN'] != "N" ? true : false;

$arParams["AVATAR_SIZE"] = (isset($arParams["AVATAR_SIZE"]) ? intval($arParams["AVATAR_SIZE"]) : 58);
$arParams["AVATAR_SIZE_COMMENT"] = (isset($arParams["AVATAR_SIZE_COMMENT"]) ? intval($arParams["AVATAR_SIZE_COMMENT"]) : 58);

$arResult["AJAX_CALL"] = (array_key_exists("AJAX_CALL", $_REQUEST) && $_REQUEST["AJAX_CALL"] == "Y");

$arParams["COMMENTS_IN_EVENT"] = (isset($arParams["COMMENTS_IN_EVENT"]) && intval($arParams["COMMENTS_IN_EVENT"]) > 0 ? $arParams["COMMENTS_IN_EVENT"] : "3");
$arParams["DESTINATION_LIMIT"] = (isset($arParams["DESTINATION_LIMIT"]) ? intval($arParams["DESTINATION_LIMIT"]) : 100);
$arParams["DESTINATION_LIMIT_SHOW"] = (isset($arParams["DESTINATION_LIMIT_SHOW"]) ? intval($arParams["DESTINATION_LIMIT_SHOW"]) : 3);

if (
	$_REQUEST["ACTION"] == "CONVERT"
	&& $arParams["LOG_ID"] <= 0
)
{
	$arConvertRes = CSocNetLogTools::GetDataFromRatingEntity($_REQUEST["ENTITY_TYPE_ID"], $_REQUEST["ENTITY_ID"]);
	if (
		is_array($arConvertRes)
		&& $arConvertRes["LOG_ID"] > 0
	)
		$arParams["LOG_ID"] = $arConvertRes["LOG_ID"];
}

$arParams["SET_LOG_CACHE"] = (
	isset($arParams["SET_LOG_CACHE"]) 
	&& $arParams["LOG_ID"] <= 0 
	&& !$arResult["AJAX_CALL"] 
		? $arParams["SET_LOG_CACHE"] 
		: "N"
);
$arParams["SET_LOG_COUNTER"] = (
	$arParams["SET_LOG_CACHE"] == "Y" 
	&& !$arResult["AJAX_CALL"] 
		? "Y" 
		: "N"
);
$arParams["SET_LOG_PAGE_CACHE"] = ($arParams["LOG_ID"] <= 0 ? "Y" : "N");
$arParams["PAGE_SIZE"] = (intval($arParams["PAGE_SIZE"]) > 0 ? $arParams["PAGE_SIZE"] : 7);

if (array_key_exists("pplogid", $_REQUEST))
{
	$arPrevPageLogID = explode("|", trim($_REQUEST["pplogid"]));
	if (is_array($arPrevPageLogID))
	{
		foreach($arPrevPageLogID as $key => $val)
		{
			preg_match('/^(\d+)$/', $val, $matches);
			if (count($matches) <= 0)
				unset($arPrevPageLogID[$key]);
		}
		$arPrevPageLogID = array_unique($arPrevPageLogID);
	}
}

if(strlen($arParams["PATH_TO_USER_BLOG_POST"]) > 0)
	$arParams["PATH_TO_USER_MICROBLOG_POST"] = $arParams["PATH_TO_USER_BLOG_POST"];

if (intval($arParams["PHOTO_COUNT"]) <= 0)
	$arParams["PHOTO_COUNT"] = 5;
if (intval($arParams["PHOTO_THUMBNAIL_SIZE"]) <= 0)
	$arParams["PHOTO_THUMBNAIL_SIZE"] = 76;

$GLOBALS["APPLICATION"]->SetPageProperty("BodyClass", ($arParams["LOG_ID"] > 0 ? "post-card" : "lenta-page"));

if(
	(
		$arParams["GROUP_ID"] <= 0
		&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_USER, $GLOBALS["USER"]->GetID(), "blog")
	)
	|| (
		$arParams["GROUP_ID"] > 0
		&& CSocNetFeatures::IsActiveFeature(SONET_ENTITY_GROUP, $arParams["GROUP_ID"], "blog")
	)
)
	$arResult["MICROBLOG_USER_ID"] = $GLOBALS["USER"]->GetID();

$arResult["TZ_OFFSET"] = CTimeZone::GetOffset();

$GLOBALS["arExtranetGroupID"] = array();
$GLOBALS["arExtranetUserID"] = array();

if(defined("BX_COMP_MANAGED_CACHE"))
	$ttl = 2592000;
else
	$ttl = 600;

$cache_id = 'sonet_ex_gr_'.SITE_ID;
$obCache = new CPHPCache;
$cache_dir = '/bitrix/sonet_log_sg';

if($obCache->InitCache($ttl, $cache_id, $cache_dir))
{
	$tmpVal = $obCache->GetVars();
	$GLOBALS["arExtranetGroupID"] = $tmpVal['EX_GROUP_ID'];
	$GLOBALS["arExtranetUserID"] = $tmpVal['EX_USER_ID'];
	unset($tmpVal);
}
elseif (CModule::IncludeModule("extranet") && !CExtranet::IsExtranetSite())
{
	global $CACHE_MANAGER;
	$CACHE_MANAGER->StartTagCache($cache_dir);
	$dbGroupTmp = CSocNetGroup::GetList(
		array(),
		array(
			"SITE_ID" => CExtranet::GetExtranetSiteID()
		),
		false,
		false,
		array("ID")
	);
	while($arGroupTmp = $dbGroupTmp->Fetch())
	{
		$GLOBALS["arExtranetGroupID"][] = $arGroupTmp["ID"];
		$CACHE_MANAGER->RegisterTag('sonet_group_'.$arGroupTmp["ID"]);
	}

	$rsUsers = CUser::GetList(
		($by="ID"),
		($order="asc"),
		array(
			"GROUPS_ID" => array(CExtranet::GetExtranetUserGroupID()),
			"UF_DEPARTMENT" => false
		),
		array("FIELDS" => array("ID"))
	);
	while($arUser = $rsUsers->Fetch())
	{
		$GLOBALS["arExtranetUserID"][] = $arUser["ID"];
		$CACHE_MANAGER->RegisterTag('sonet_user2group_U'.$arUser["ID"]);
	}
	$CACHE_MANAGER->EndTagCache();
	if($obCache->StartDataCache())
		$obCache->EndDataCache(array(
			'EX_GROUP_ID' => $GLOBALS["arExtranetGroupID"],
			'EX_USER_ID' => $GLOBALS["arExtranetUserID"]
		));
}
unset($obCache);

$arTmpEventsNew = array();

$arResult["Events"] = false;

$arFilter = array();

if ($arParams["LOG_ID"] > 0)
	$arFilter["ID"] = $arParams["LOG_ID"];
elseif(
	$arResult["AJAX_CALL"]
	&& intval($arParams["NEW_LOG_ID"]) > 0
)
	$arFilter["ID"] = $arParams["NEW_LOG_ID"];
else
{
	if ($arParams["DESTINATION"] > 0)
		$arFilter["LOG_RIGHTS"] = $arParams["DESTINATION"];
	elseif ($arParams["GROUP_ID"] > 0)
		$arFilter["LOG_RIGHTS"] = "SG".intval($arParams["GROUP_ID"]);
}

if (
	$arParams["LOG_ID"] <= 0
	&& intval($arParams["NEW_LOG_ID"]) <= 0
)
{
	if (isset($arParams["EXACT_EVENT_ID"]))
		$arFilter["EVENT_ID"] = array($arParams["EXACT_EVENT_ID"]);
	elseif (is_array($arParams["EVENT_ID"]))
	{
		$event_id_fullset_tmp = array();
		foreach($arParams["EVENT_ID"] as $event_id_tmp)
			$event_id_fullset_tmp = array_merge($event_id_fullset_tmp, CSocNetLogTools::FindFullSetByEventID($event_id_tmp));
		$arFilter["EVENT_ID"] = array_unique($event_id_fullset_tmp);
	}
	elseif ($arParams["EVENT_ID"])
		$arFilter["EVENT_ID"] = CSocNetLogTools::FindFullSetByEventID($arParams["EVENT_ID"]);

	if (IntVal($arParams["CREATED_BY_ID"]) > 0) // from preset
		$arFilter["USER_ID"] = $arParams["CREATED_BY_ID"];
}

if (
	(
		$arParams["GROUP_ID"] > 0
		|| $arParams["USER_ID"] > 0
	)
	&& !array_key_exists("EVENT_ID", $arFilter)
)
{
	$arFilter["EVENT_ID"] = array();

	foreach($GLOBALS["arSocNetLogEvents"] as $event_id_tmp => $arEventTmp)
	{
		if (
			array_key_exists("HIDDEN", $arEventTmp)
			&& $arEventTmp["HIDDEN"]
		)
			continue;

		$arFilter["EVENT_ID"][] = $event_id_tmp;
	}

	$arFeatures = CSocNetFeatures::GetActiveFeatures(($arParams["GROUP_ID"] > 0 ? SONET_ENTITY_GROUP : SONET_ENTITY_GROUP), ($arParams["GROUP_ID"] > 0 ? $arParams["GROUP_ID"] : $arParams["USER_ID"]));
	foreach($arFeatures as $feature_id)
	{
		if(
			array_key_exists($feature_id, $GLOBALS["arSocNetFeaturesSettings"])
			&& array_key_exists("subscribe_events", $GLOBALS["arSocNetFeaturesSettings"][$feature_id])
		)
			foreach ($GLOBALS["arSocNetFeaturesSettings"][$feature_id]["subscribe_events"] as $event_id_tmp => $arEventTmp)
				$arFilter["EVENT_ID"][] = $event_id_tmp;
	}
}

if (
	!$arFilter["EVENT_ID"]
	|| (is_array($arFilter["EVENT_ID"]) && count($arFilter["EVENT_ID"]) <= 0)
)
	unset($arFilter["EVENT_ID"]);

if (CModule::IncludeModule('extranet') && CExtranet::IsExtranetSite())
	$arFilter["SITE_ID"] = SITE_ID;
else
	$arFilter["SITE_ID"] = array(SITE_ID, false);

$arFilter["<=LOG_DATE"] = "NOW";

if ($arParams["LOG_ID"] <= 0)
{
	if (!$arResult["AJAX_CALL"])
	{
		$arNavStartParams = array("nTopCount" => $arParams["PAGE_SIZE"]);
		$arResult["PAGE_NUMBER"] = 1;
		$bFirstPage = true;
	}
	else
	{
		if (intval($_REQUEST["PAGEN_".($GLOBALS["NavNum"] + 1)]) > 0)
			$arResult["PAGE_NUMBER"] = intval($_REQUEST["PAGEN_".($GLOBALS["NavNum"] + 1)]);

		$arNavStartParams = array(
			"nPageSize" => $arParams["PAGE_SIZE"],
			"bDescPageNumbering" => false,
			"bShowAll" => false,
			"iNavAddRecords" => 1,
			"bSkipPageReset" => true
		);
	}
}

if (
	$arParams["LOG_ID"] <= 0
	&& intval($arParams["NEW_LOG_ID"]) <= 0
	&& $arParams["FAVORITES"] == "Y"
)
	$arFilter[">FAVORITES_USER_ID"] = 0;

if (intval($arParams["GROUP_ID"]) > 0)
{
	$arResult["COUNTER_TYPE"] = "SG".intval($arParams["GROUP_ID"]);
	$arParams["SET_LOG_PAGE_CACHE"] = "N";
	$arParams["USE_FOLLOW"] = "N";
}
else
	$arResult["COUNTER_TYPE"] = "**";

if ($arParams["SET_LOG_COUNTER"] == "Y")
{
	$arResult["LAST_LOG_TS"] = CUserCounter::GetLastDate($GLOBALS["USER"]->GetID(), $arResult["COUNTER_TYPE"]);	
	$counterLastDate = ConvertTimeStamp($arResult["LAST_LOG_TS"], "FULL");

	if($arResult["LAST_LOG_TS"] == 0)
		$arResult["LAST_LOG_TS"] = 1;
	else
	{
		//We substruct TimeZone offset in order to get server time
		//because of template compatibility
		$arResult["LAST_LOG_TS"] -= $arResult["TZ_OFFSET"];
	}
}
elseif (
	($arResult["COUNTER_TYPE"] == "**")
	&& (
		$arParams["LOG_ID"] > 0
		|| $arResult["AJAX_CALL"]
	)
	&& intval($_REQUEST["LAST_LOG_TS"]) > 0
)
	$arResult["LAST_LOG_TS"] = intval($_REQUEST["LAST_LOG_TS"]);

if ($arParams["SET_LOG_PAGE_CACHE"] == "Y")
{
	$rsLogPages = CSocNetLogPages::GetList(
		array(),
		array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
			"SITE_ID" => SITE_ID,
			"PAGE_SIZE" => $arParams["PAGE_SIZE"],
			"PAGE_NUM" => $arResult["PAGE_NUMBER"]
		),
		false,
		false,
		array("PAGE_LAST_DATE")
	);

	if ($arLogPages = $rsLogPages->Fetch())
		$arFilter[">=LOG_UPDATE"] = $dateLastPageStart = $arLogPages["PAGE_LAST_DATE"];
	else
	{
		$rsLogPages = CSocNetLogPages::GetList(
			array("PAGE_LAST_DATE" => "DESC"),
			array(
				"SITE_ID" => SITE_ID,
				"PAGE_SIZE" => $arParams["PAGE_SIZE"],
				"PAGE_NUM" => $arResult["PAGE_NUMBER"]
			),
			false,
			false,
			array("PAGE_LAST_DATE")
		);
		if ($arLogPages = $rsLogPages->Fetch())
		{
			$arFilter[">=LOG_UPDATE"] = $dateLastPageStart = $arLogPages["PAGE_LAST_DATE"];
			$bNeedSetLogPage = true;
		}
	}
}

$arListParams = array(
	"CHECK_RIGHTS" => "Y",
	"USE_SUBSCRIBE" => "N"
);

if ($arParams["USE_FOLLOW"] == "Y")
{
	$arListParams["USE_FOLLOW"] = "Y";
	$arOrder = array("DATE_FOLLOW" => "DESC");
}
else
	$arOrder = array("LOG_UPDATE" => "DESC");

$dbEventsID = CSocNetLog::GetList(
	$arOrder,
	$arFilter,
	false,
	$arNavStartParams,
	array(
		"ID", 
		"LOG_DATE", "LOG_UPDATE", "DATE_FOLLOW", 
		"ENTITY_TYPE", "ENTITY_ID", "EVENT_ID", "SOURCE_ID", "USER_ID", "COMMENTS_COUNT",
		"FOLLOW", "FAVORITES_USER_ID"
	),
	$arListParams
);
	
if (
	$arParams["LOG_ID"] <= 0
	&& intval($arParams["NEW_LOG_ID"]) <= 0
)
{
	if ($bFirstPage)
	{
		$arResult["PAGE_NAVNUM"] = $GLOBALS["NavNum"] + 1;
		$arResult["PAGE_NAVCOUNT"] = 1000000;
	}
	else
	{
		$arResult["PAGE_NUMBER"] = $dbEventsID->NavPageNomer;
		$arResult["PAGE_NAVNUM"] = $dbEventsID->NavNum;
		$arResult["PAGE_NAVCOUNT"] = $dbEventsID->NavPageCount;
	}
}

$arResult["arLogTmpID"] = array();

while ($arEvents = $dbEventsID->GetNext())
{
	$arTmpEventsNew[] = $arEvents;
	$arResult["arLogTmpID"][] = $arEvents["ID"];
}

foreach ($arTmpEventsNew as $key => $arTmpEvent)
{
	if (
		!is_array($arPrevPageLogID)
		|| !in_array($arTmpEvent["ID"], $arPrevPageLogID)
	)
		$arTmpEventsNew[$key]["EVENT_ID_FULLSET"] = CSocNetLogTools::FindFullSetEventIDByEventID($arTmpEvent["EVENT_ID"]);
	else
		unset($arTmpEventsNew[$key]);
}

$arResult["Events"] = $arTmpEventsNew;

if ($arTmpEvent["DATE_FOLLOW"])
	$dateLastPage = ConvertTimeStamp(MakeTimeStamp($arTmpEvent["DATE_FOLLOW"], CSite::GetDateFormat("FULL")), "FULL");

if (
	$arParams["LOG_ID"] <= 0
	&& intval($arParams["NEW_LOG_ID"]) <= 0
	&& $GLOBALS["USER"]->IsAuthorized()
)
{
	$arCounters = CUserCounter::GetValues($GLOBALS["USER"]->GetID(), SITE_ID);
	if (isset($arCounters[$arResult["COUNTER_TYPE"]]))
		$arResult["LOG_COUNTER"] = intval($arCounters[$arResult["COUNTER_TYPE"]]);
	else
	{
		$bEmptyCounter = true;
		$arResult["LOG_COUNTER"] = 0;
	}
}

if (
	$GLOBALS["USER"]->IsAuthorized()
	&& $arParams["SET_LOG_COUNTER"] == "Y"
	&& (intval($arResult["LOG_COUNTER"]) > 0 || $bEmptyCounter)
)
	CUserCounter::ClearByUser(
		$GLOBALS["USER"]->GetID(), 
		array(SITE_ID, "**"),
		$arResult["COUNTER_TYPE"]
	);

if (
	$GLOBALS["USER"]->IsAuthorized()
	&& $arParams["SET_LOG_PAGE_CACHE"] == "Y"
	&& $dateLastPage
	&& (
		!$dateLastPageStart
		|| $dateLastPageStart != $dateLastPage
		|| $bNeedSetLogPage
	)
)
	CSocNetLogPages::Set(
		$GLOBALS["USER"]->GetID(),
		$dateLastPage,
		$arParams["PAGE_SIZE"],
		$arResult["PAGE_NUMBER"],
		SITE_ID
	);

if (
	$GLOBALS["USER"]->IsAuthorized()
	&& $arParams["USE_FOLLOW"] == "Y"
)
{
	$rsFollow = CSocNetLogFollow::GetList(
		array(
			"USER_ID" => $GLOBALS["USER"]->GetID(),
			"CODE" => "**"
		),
		array("TYPE")
	);
	if ($arFollow = $rsFollow->Fetch())
		$arResult["FOLLOW_DEFAULT"] = $arFollow["TYPE"];
	else
		$arResult["FOLLOW_DEFAULT"] = COption::GetOptionString("socialnetwork", "follow_default_type", "Y");
}

$this->IncludeComponentTemplate();
?>