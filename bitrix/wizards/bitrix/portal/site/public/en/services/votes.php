<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Polls");
?><p>This page shows all the currently active polls, and the poll history. You can vote in any active poll.</p>

<?$APPLICATION->IncludeComponent(
	"bitrix:voting.list",
	"",
	Array(
		"CHANNEL_SID" => "", 
		"VOTE_FORM_TEMPLATE" => "vote_new.php?VOTE_ID=#VOTE_ID#", 
		"VOTE_RESULT_TEMPLATE" => "vote_result.php?VOTE_ID=#VOTE_ID#" 
	)
);?><?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>