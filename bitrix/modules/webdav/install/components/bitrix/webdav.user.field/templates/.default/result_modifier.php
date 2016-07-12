<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (strpos($this->__page, "view") === 0)
{
	$arParams["THUMB_SIZE"] = array("width" => 69, "height" => 69);
	$arParams["MAX_SIZE"] = array("width" => 600, "height" => 600);
	$arParams["SCREEN_SIZE"] = array("width" => 1024, "height" => 1024);
	$images = array(); $files = array();
	foreach ($arResult['FILES'] as $id => $arWDFile)
	{
		if (CFile::IsImage($arWDFile['NAME'], $arWDFile["FILE"]["CONTENT_TYPE"]))
		{
			$src = $arWDFile["PATH"].(strpos($arWDFile["PATH"], "?") === false ? "?" : "&")."cache_image=Y";
			$res = array(
				"content_type" => $arWDFile["FILE"]["CONTENT_TYPE"],
				"src" => $src,
				"width" => $arWDFile["FILE"]["WIDTH"],
				"height" => $arWDFile["FILE"]["HEIGHT"],
				"basic" => array(
					"src" => $src,
					"width" => $arWDFile["FILE"]["WIDTH"],
					"height" => $arWDFile["FILE"]["HEIGHT"]
				),
				"thumb" => array(
					"src" => $src."&".http_build_query(array_merge($arParams["THUMB_SIZE"], array("exact" => "Y"))),
					"width" => $arParams["THUMB_SIZE"]["width"],
					"height" => $arParams["THUMB_SIZE"]["height"]
				)
			);

			$arSize = is_array($arParams["SIZE"][$arWDFile["ID"]]) ? $arParams["SIZE"][$arWDFile["ID"]] : array();
			$bExactly = !empty($arSize);
			if ($bExactly) {
				$arSize["width"] = intval(!!$arSize["width"] ? $arSize["width"] : $arSize["WIDTH"]);
				$arSize["height"] = intval(!!$arSize["height"] ? $arSize["height"] : $arSize["HEIGHT"]);
			}

			if (!empty($arParams["MAX_SIZE"]) && $arParams["MAX_SIZE"]["width"] > 0 && $arParams["MAX_SIZE"]["height"] > 0)
			{
				if ($arSize["height"] <= 0 && $arSize["width"] <= 0)
					$arSize = array("width" => $arWDFile["FILE"]["WIDTH"], "height" => $arWDFile["FILE"]["HEIGHT"]);
				$coeff = max($arSize["width"]/$arParams["MAX_SIZE"]["width"], $arSize["height"]/$arParams["MAX_SIZE"]["height"]);
				if ($coeff > 1) {
					$arSize["width"] = intval($arSize["width"]/$coeff);
					$arSize["height"] = intval($arSize["height"]/$coeff);
				}
				CFile::ScaleImage(
					$arWDFile["FILE"]["WIDTH"], $arWDFile["FILE"]["HEIGHT"],
					$arSize, BX_RESIZE_IMAGE_PROPORTIONAL,
					$bNeedCreatePicture,
					$arSourceSize, $arDestinationSize);
				$res["width"] = ($bExactly ? $arSize["width"] : $arDestinationSize["width"]);
				$res["height"] = ($bExactly ? $arSize["height"] : $arDestinationSize["height"]);
				if ($bNeedCreatePicture)
					$res["src"] .= "&".http_build_query($arSize);
			}
			else if ($bExactly)
			{
				$res["width"] = $arSize["width"];
				$res["height"] = $arSize["height"];
			}

			if (!empty($arParams["SCREEN_SIZE"]))
			{
				CFile::ScaleImage(
					$arWDFile["FILE"]["WIDTH"], $arWDFile["FILE"]["HEIGHT"],
					$arParams["SCREEN_SIZE"], BX_RESIZE_IMAGE_PROPORTIONAL,
					$bNeedCreatePicture,
					$arSourceSize, $arDestinationSize);
				if ($bNeedCreatePicture)
				{
					$res["original"] = $res["basic"];
					$res["basic"] = array(
						"src" => $res["original"]["src"]."&".http_build_query($arParams["SCREEN_SIZE"]),
						"width" => $arDestinationSize["width"],
						"height" => $arDestinationSize["height"]
					);
				}
			}

			$arResult["FILES"][$id] = array_merge($arResult["FILES"][$id], $res);
			$images[$id] = $arResult["FILES"][$id];
		}
		else
		{
			$files[$id] = $arWDFile;
		}
	}
	if ($this->__page == "view")
	{
		$arResult['IMAGES'] = $images;
		$arResult['FILES'] = $files;
	}
}
?>