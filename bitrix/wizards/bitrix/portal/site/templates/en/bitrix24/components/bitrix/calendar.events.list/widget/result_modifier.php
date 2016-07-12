<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (count($arResult["ITEMS"]) < 1)
	return;

$todayEnd = mktime(23, 59, 59, date("m"), date("d")+3, date("Y"));

$arEvents = Array();
foreach($arResult["ITEMS"] as $i => $arItem)
{
	$dateFrom = MakeTimeStamp($arItem["DATE_FROM"]);
	if ($dateFrom < $todayEnd)
	{
		$arItem['DT_FROM'] = FormatDateFromDB($arItem['DT_FROM']);
		$arItem['DT_TO'] = FormatDateFromDB($arItem['DT_TO']);
		$arItem['DATE_FROM'] = FormatDateFromDB($arItem['DATE_FROM']);
		$arEvents[] = $arItem;
	}
}

$arResult["ITEMS"] = $arEvents;

?>