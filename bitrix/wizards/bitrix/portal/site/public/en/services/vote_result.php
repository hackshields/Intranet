<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Polls");
?><?$APPLICATION->IncludeComponent(
	"bitrix:voting.result",
	"",
	Array(
		"VOTE_ID" => $_REQUEST["VOTE_ID"], 
		"CACHE_TYPE" => "A", 
		"CACHE_TIME" => "1200" 
	)
);?> 
<br />
 
<br />
 <a href="/services/votes.php">Back to Polls list</a> 
<br />
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>