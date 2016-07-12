<?
IncludeModuleLangFile(__FILE__);

class CXDILFSchemeRSS
{
	function Request($server, $page, $port, $params)
	{
		if (!CModule::IncludeModule("iblock"))
			return false;
			
		if (
			strlen($port) <= 0
			|| intval($port) <= 0
		)
			$port = 80;
		else
			$port = intval($port);

		$arRSSResult = CIBlockRSS::GetNewsEx($server, $port, $page, $params);
		$arRSSResult = CIBlockRSS::FormatArray($arRSSResult);
		if (
			!empty($arRSSResult)
			&& array_key_exists("item", $arRSSResult)
			&& is_array($arRSSResult["item"])
			&& !empty($arRSSResult["item"])
		)
			$arRSSResult["item"] = array_reverse($arRSSResult["item"]);
		
		return $arRSSResult;
	}
}
?>