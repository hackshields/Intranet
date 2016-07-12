<?php

class CCrmSecurityHelper
{
	private static $CURRENT_USER = null;

	private static function EnsureCurrentUser()
	{
		if(self::$CURRENT_USER)
		{
			return;
		}

		if(isset($USER) && ((get_class($USER) === 'CUser') || ($USER instanceof CUser)))
		{
			self::$CURRENT_USER = &$USER;
		}
		else
		{
			self::$CURRENT_USER = new CUser();
		}
	}

	public static function GetCurrentUserID()
	{
		self::EnsureCurrentUser();
		return self::$CURRENT_USER->GetID();
	}

	public static function GetCurrentUser()
	{
		self::EnsureCurrentUser();
		return self::$CURRENT_USER;
	}
}