<?
define("STOP_STATISTICS", true);
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

CModule::IncludeModule("tasks");

$arParams = array(
	'FILE_ID'     => false,
	'TEMPLATE_ID' => false,
	'TASK_ID'     => false
);

if (isset($_REQUEST['fid']))
	$arParams['FILE_ID'] = (int) $_REQUEST['fid'];

if (isset($_REQUEST['tid']))
	$arParams['TEMPLATE_ID'] = (int) $_REQUEST['tid'];

if (isset($_REQUEST['TASK_ID']))
	$arParams['TASK_ID'] = (int) $_REQUEST['TASK_ID'];

$arResult["MESSAGE"] = array();
$arResult["FILE"] = array();

$bFound = false;
if ($arParams["FILE_ID"] > 0)
{
	if ($arParams["TEMPLATE_ID"])
	{
		$rsTemplate = CTaskTemplates::GetList(array(), array("ID" => $arParams["TEMPLATE_ID"], "CREATED_BY" => $USER->GetID()));
		if ($arTemplate = $rsTemplate->Fetch())
		{
			$arTemplate["FILES"] = unserialize($arTemplate["FILES"]);
			if (is_array($arTemplate["FILES"]) && in_array($arParams["FILE_ID"], $arTemplate["FILES"]))
			{
				$bFound = true;
			}
		}
	}
	else
	{
		if ($arParams['TASK_ID'])
		{
			if (CTaskFiles::isUserfieldFileAccessibleByUser($arParams['TASK_ID'], $arParams['FILE_ID'], $USER->GetID()))
				$bFound = true;
		}

		if ( !$bFound && CTaskFiles::isFileAccessibleByUser( (int) $arParams["FILE_ID"], $USER->GetID()))
			$bFound = true;
	}
}

if ($bFound)
{
	$arResult["FILE"] = CFile::GetFileArray($arParams["FILE_ID"]);
}

if (!$arResult["FILE"])
{
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
	echo ShowError("File not found");
	require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
	die();
}

set_time_limit(0);
if (CFile::IsImage(
		$arResult["FILE"]["ORIGINAL_NAME"], 
		$arResult["FILE"]["CONTENT_TYPE"]
	)
	&& (
		(
			file_exists($_SERVER["DOCUMENT_ROOT"].$arResult["FILE"]["SRC"])
			&& CFile::GetImageSize($_SERVER["DOCUMENT_ROOT"].$arResult["FILE"]["SRC"])
		) || (
			$arResult["FILE"]["WIDTH"] > 0
			&& $arResult["FILE"]["HEIGHT"] > 0
		)
	)
)
{
	CFile::ViewByUser($arResult["FILE"], array("content_type" => $arResult["FILE"]["CONTENT_TYPE"]));
}
elseif (isset($_GET['action']) && ($_GET['action'] === 'download'))
{
	CFile::ViewByUser($arResult['FILE'], array('force_download' => true));
}
else
{
	$ct = strtolower($arResult["FILE"]["CONTENT_TYPE"]);
	if (strpos($ct, "excel") !== false)
		CFile::ViewByUser($arResult["FILE"], array("content_type" => "application/vnd.ms-excel"));
	elseif (strpos($ct, "word") !== false)
		CFile::ViewByUser($arResult["FILE"], array("content_type" => "application/msword"));
	else
		CFile::ViewByUser($arResult["FILE"], array("content_type" => "application/octet-stream", "force_download" => true));
}

require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_after.php");
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog.php");
