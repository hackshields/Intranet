<?
require($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/im/classes/general/im_contact_list.php");

class CIMContactList extends CAllIMContactList
{
	public static function SetRecent($entityId, $messageId, $isChat = false, $userId = false)
	{
		$entityId = intval($entityId);
		$messageId = intval($messageId);
		if ($entityId <= 0 || $messageId <= 0)
			return false;

		$userId = intval($userId);
		if ($userId <= 0)
			$userId = $GLOBALS['USER']->GetID();

		if (!$isChat && $userId == $entityId)
			return false;

		global $DB;

		$strSQL = "
			MERGE INTO b_im_recent USING (SELECT ".$userId." USER_ID, '".($isChat? IM_MESSAGE_GROUP: IM_MESSAGE_PRIVATE)."' ITEM_TYPE, ".$entityId." ITEM_ID FROM dual)
			source ON
			(
				source.USER_ID = b_im_recent.USER_ID
				AND source.ITEM_TYPE = b_im_recent.ITEM_TYPE
				AND source.ITEM_ID = b_im_recent.ITEM_ID
			)
			WHEN MATCHED THEN
				UPDATE SET b_im_recent.ITEM_MID = ".$messageId."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, ITEM_TYPE, ITEM_ID, ITEM_MID)
				VALUES (".$userId.", '".($isChat? IM_MESSAGE_GROUP: IM_MESSAGE_PRIVATE)."', ".$entityId.", ".$messageId.")
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		$obCache = new CPHPCache();
		$obCache->CleanDir('/bx/im/rec'.CIMMessenger::GetCachePath($userId));

		if ($isChat)
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_GROUP);
		else
			CIMMessenger::SpeedFileDelete($userId, IM_SPEED_MESSAGE);

		return true;
	}
}
?>