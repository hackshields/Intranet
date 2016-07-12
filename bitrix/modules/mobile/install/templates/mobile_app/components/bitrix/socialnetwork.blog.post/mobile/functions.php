<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

if (!function_exists("__blogUFfileShowMobile"))
{
	function __blogUFfileShowMobile($arResult, $arParams)
	{
		$result = false;
		if ($arParams['arUserField']['FIELD_NAME'] == 'UF_BLOG_POST_DOC' || strpos($arParams['arUserField']['FIELD_NAME'], 'UF_BLOG_COMMENT_DOC') === 0)
		{
			if (sizeof($arResult['VALUE']) > 0)
			{
				?><div class="post-item-attached-file-wrap"><?

				foreach ($arResult['VALUE'] as $fileID)
				{
					$arFile = CFile::GetFileArray($fileID);
					if($arFile)
					{
						$name = $arFile['ORIGINAL_NAME'];
						$ext = '';
						$dotpos = strrpos($name, ".");
						if (($dotpos !== false) && ($dotpos+1 < strlen($name)))
							$ext = substr($name, $dotpos+1);
						if (strlen($ext) < 3 || strlen($ext) > 5)
							$ext = '';
						$arFile['EXTENSION'] = $ext;
						$arFile['LINK'] = "/bitrix/components/bitrix/blog/show_file.php?bp_fid=".$fileID;
						$arFile["FILE_SIZE"] = CFile::FormatSize($arFile["FILE_SIZE"]);
						?><div class="post-item-attached-file"><?
							?><a href="<?=$arFile['LINK']?>" class="post-item-attached-file-link"><span><?=htmlspecialcharsbx($arFile['ORIGINAL_NAME'])?></span><span>(<?=$arFile['FILE_SIZE']?>)</span></a><?
						?></div><?
					}
				}

				?></div><?
			}
			$result = true;
		}
		return $result;
	}
}

if (!function_exists("ResizeMobileLogImages"))
{
	function ResizeMobileLogImages($res, $strImage, $db_img_arr, $f, $arDestinationSize)
	{
		$stubSrc = "data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIW2N88f7jfwAJWAPJBTw90AAAAABJRU5ErkJggg==";
		$previewSrc = preg_replace("/width=([0-9]+)&height=([0-9]+)/", "width=75&height=150", $strImage);
		$res = '<img src="'.$stubSrc.'" data-preview-src="'.$previewSrc.'" data-src="'.$strImage.'" title="'.htmlspecialcharsbx($f["TITLE"]).'" alt="'.htmlspecialcharsbx($f["TITLE"]).'" border="0" width="'.round($arDestinationSize["width"]/2).'" height="'.round($arDestinationSize["height"]/2).'" />';
	}
}

if (!function_exists('__SMLFormatDate'))
{
	function __SMLFormatDate($timestamp)
	{
		$days_ago = intval((time() - $timestamp) / 60 / 60 / 24);
		$days_ago = ($days_ago <= 0 ? 1 : $days_ago);

		return str_replace("#DAYS#", $days_ago, GetMessage("BLOG_MOBILE_DATETIME_DAYS"));
	}
}
?>