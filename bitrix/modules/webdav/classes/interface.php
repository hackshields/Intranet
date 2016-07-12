<?
##############################################
# Bitrix Site Manager						 #
# Copyright (c) 2002-2012 Bitrix			 #
# http://www.bitrixsoft.com					 #
# mailto:admin@bitrixsoft.com				 #
##############################################
IncludeModuleLangFile(__FILE__);

class CWebDavInterface
{
	static public function UserFieldEdit(&$arParams, &$arResult, $component=null)
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			'bitrix:webdav.user.field',
			'',
			array(
				'EDIT' => 'Y',
				'PARAMS' => $arParams,
				'RESULT' => $arResult,
			),
			$component,
			array( "HIDE_ICONS" => "Y")
		);
	}

	static public function UserFieldViewThumb(&$arParams, &$arResult, $component=null, $size = array())
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			'bitrix:webdav.user.field',
			'',
			array(
				'VIEW_THUMB' => "Y",
				'SIZE' => $size,
				'PARAMS' => $arParams,
				'RESULT' => $arResult,
			),
			$component,
			array( "HIDE_ICONS" => "Y")
		);
	}

	static public function UserFieldView(&$arParams, &$arResult, $component=null)
	{
		global $APPLICATION;

		$APPLICATION->IncludeComponent(
			'bitrix:webdav.user.field',
			'',
			array(
				'PARAMS' => $arParams,
				'RESULT' => $arResult,
			),
			$component,
			array( "HIDE_ICONS" => "Y")
		);
	}
}
?>
