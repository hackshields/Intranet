<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/user_counter.php");

class CUserCounter extends CAllUserCounter
{
	public static function Set($user_id, $code, $value, $site_id = SITE_ID, $tag = '')
	{
		global $DB, $CACHE_MANAGER;

		$value = intval($value);
		$user_id = intval($user_id);
		if ($user_id <= 0 || strlen($code) <= 0)
			return false;

		$ssql = "";
		if ($tag != "")
			$ssql = ", b_user_counter.TAG = '".$DB->ForSQL($tag)."'";

		$strSQL = "
			MERGE INTO b_user_counter USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = ".$value." ".$ssql.", b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE, TAG)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', ".$value.", ".$DB->CurrentTimeFunction().", '".$DB->ForSQL($tag)."')
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id == '**')
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					self::$counters[$user_id][$key][$code] = $value;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				self::$counters[$user_id][$site_id][$code] = $value;
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		return true;
	}

	public static function Increment($user_id, $code, $site_id = SITE_ID)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if ($user_id <= 0 || strlen($code) <= 0)
			return false;

		$strSQL = "
			MERGE INTO b_user_counter USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = b_user_counter.CNT + 1, b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', 1, ".$DB->CurrentTimeFunction().")
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id == '**')
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$code]))
						self::$counters[$user_id][$key][$code]++;
					else
						self::$counters[$user_id][$key][$code] = 1;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				if (isset(self::$counters[$user_id][$site_id][$code]))
					self::$counters[$user_id][$site_id][$code]++;
				else
					self::$counters[$user_id][$site_id][$code] = 1;
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		return true;
	}

	public static function Decrement($user_id, $code, $site_id = SITE_ID)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if ($user_id <= 0 || strlen($code) <= 0)
			return false;

		$strSQL = "
			MERGE INTO b_user_counter USING (SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = b_user_counter.CNT - 1, b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
				VALUES (".$user_id.", '".$DB->ForSQL($site_id)."', '".$DB->ForSQL($code)."', -1, ".$DB->CurrentTimeFunction().")
		";
		$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			if ($site_id == '**')
			{
				foreach(self::$counters[$user_id] as $key => $tmp)
				{
					if (isset(self::$counters[$user_id][$key][$code]))
						self::$counters[$user_id][$key][$code]--;
					else
						self::$counters[$user_id][$key][$code] = -1;
				}
			}
			else
			{
				if (!isset(self::$counters[$user_id][$site_id]))
					self::$counters[$user_id][$site_id] = array();

				if (isset(self::$counters[$user_id][$site_id][$code]))
					self::$counters[$user_id][$site_id][$code]--;
				else
					self::$counters[$user_id][$site_id][$code] = -1;
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		return true;
	}

	public static function IncrementWithSelect($sub_select)
	{
		global $DB, $CACHE_MANAGER;

		if (strlen($sub_select) > 0)
		{
			$strSQL = "
				MERGE INTO b_user_counter USING (".$sub_select.")
				source ON (
					source.ID = b_user_counter.USER_ID
					AND source.SITE_ID = b_user_counter.SITE_ID
					AND source.CODE = b_user_counter.CODE
				)
				WHEN MATCHED THEN
					UPDATE SET b_user_counter.CNT = b_user_counter.CNT + 1
				WHEN NOT MATCHED THEN
					INSERT (USER_ID, CNT, SITE_ID, CODE) VALUES (source.ID, 1, source.SITE_ID, source.CODE)
			";
			$DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			self::$counters = false;
			$CACHE_MANAGER->CleanDir("user_counter");
		}
	}

	public static function Clear($user_id, $code, $site_id = SITE_ID)
	{
		global $DB, $CACHE_MANAGER;

		$user_id = intval($user_id);
		if ($user_id <= 0 || strlen($code) <= 0)
			return false;

		if (!is_array($site_id))
			$site_id = array($site_id);

		$strSQL = "
			MERGE INTO b_user_counter USING (
				";

		foreach ($site_id as $i => $site_id_tmp)
		{
			if ($i > 0)
				$strSQL .= " UNION ";
			$strSQL .= " SELECT ".$user_id." USER_ID, '".$DB->ForSQL($site_id_tmp)."' SITE_ID, '".$DB->ForSQL($code)."' CODE FROM dual ";
		}

		$strSQL .= "
			)
			source ON
			(
				source.USER_ID = b_user_counter.USER_ID
				AND source.SITE_ID = b_user_counter.SITE_ID
				AND source.CODE = b_user_counter.CODE
			)
			WHEN MATCHED THEN
				UPDATE SET b_user_counter.CNT = 0, b_user_counter.LAST_DATE = ".$DB->CurrentTimeFunction()."
			WHEN NOT MATCHED THEN
				INSERT (USER_ID, SITE_ID, CODE, CNT, LAST_DATE)
				VALUES (source.USER_ID, source.SITE_ID, source.CODE, -1, ".$DB->CurrentTimeFunction().")
		";
		$res = $DB->Query($strSQL, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

		if (self::$counters && self::$counters[$user_id])
		{
			foreach ($site_id as $site_id_tmp)
			{
				if ($site_id_tmp == '**')
				{
					foreach(self::$counters[$user_id] as $key => $tmp)
						self::$counters[$user_id][$key][$code] = 0;
					break;
				}
				else
				{
					if (!isset(self::$counters[$user_id][$site_id_tmp]))
						self::$counters[$user_id][$site_id_tmp] = array();

					self::$counters[$user_id][$site_id_tmp][$code] = 0;
				}
			}
		}

		$CACHE_MANAGER->Clean("user_counter".$user_id, "user_counter");

		return true;
	}

	protected static function dbIF($condition, $yes, $no)
	{
		return "case when ".$condition." then ".$yes." else ".$no." end ";
	}

	// legacy function
	public static function ClearByUser($user_id, $site_id = SITE_ID, $code = "**")
	{
		return self::Clear($user_id, $code, $site_id);
	}
}
?>