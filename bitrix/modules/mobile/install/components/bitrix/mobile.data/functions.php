<?if(!Defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

	function AddTableData($source = Array(),$data = Array(), $data_name = "", $dataID = false)
	{
		global $APPLICATION;
		if($dataID == false)
			$dataID = "data".rand(1,10000);
		$source["data"][$dataID] = $data;
		if(ToUpper(SITE_CHARSET)!="UTF-8")
			$data_name = $APPLICATION->ConvertCharset($data_name, SITE_CHARSET, "utf-8");
		$source["names"][$dataID] = $data_name;

		return $source;
	}
?>