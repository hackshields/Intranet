<?php
class CCrmAuthorizationHelper
{
	private static $USER_PERMISSIONS = null;

	public static function GetUserPermissions()
	{
		if(self::$USER_PERMISSIONS === null)
		{
			self::$USER_PERMISSIONS = CCrmPerms::GetCurrentUserPermissions();
		}

		return self::$USER_PERMISSIONS;
	}

	public static function CheckCreatePermission($enitityTypeName, $userPermissions = null)
	{
		$enitityTypeName = strval($enitityTypeName);

		if(!$userPermissions)
		{
			$userPermissions = self::GetUserPermissions();
		}

		return !$userPermissions->HavePerm($enitityTypeName, BX_CRM_PERM_NONE, 'ADD');
	}

	public static function CheckUpdatePermission($enitityTypeName, $entityID, $userPermissions = null)
	{
		$enitityTypeName = strval($enitityTypeName);
		$entityID = intval($entityID);

		if(!$userPermissions)
		{
			$userPermissions = self::GetUserPermissions();
		}

		if($entityID <= 0)
		{
			return !$userPermissions->HavePerm($enitityTypeName, BX_CRM_PERM_NONE, 'WRITE');
		}

		$attrs = $userPermissions->GetEntityAttr($enitityTypeName, $entityID);
		return !$userPermissions->HavePerm($enitityTypeName, BX_CRM_PERM_NONE, 'WRITE')
			&& $userPermissions->CheckEnityAccess($enitityTypeName, 'WRITE', isset($attrs[$entityID]) ? $attrs[$entityID] : array());
	}

	public static function CheckDeletePermission($enitityTypeName, $entityID, $userPermissions = null)
	{
		$enitityTypeName = strval($enitityTypeName);
		$entityID = intval($entityID);

		if($entityID <= 0)
		{
			return false;
		}

		if(!$userPermissions)
		{
			$userPermissions = self::GetUserPermissions();
		}

		$attrs = $userPermissions->GetEntityAttr($enitityTypeName, $entityID);
		return !$userPermissions->HavePerm($enitityTypeName, BX_CRM_PERM_NONE, 'DELETE')
			&& $userPermissions->CheckEnityAccess($enitityTypeName, 'DELETE', isset($attrs[$entityID]) ? $attrs[$entityID] : array());
	}

	public static function CheckReadPermission($enitityTypeName, $entityID, $userPermissions = null)
	{
		$enitityTypeName = strval($enitityTypeName);
		$entityID = intval($entityID);

		if(!$userPermissions)
		{
			$userPermissions = self::GetUserPermissions();
		}

		if($entityID <= 0)
		{
			return !$userPermissions->HavePerm($enitityTypeName, BX_CRM_PERM_NONE, 'READ');
		}

		$attrs = $userPermissions->GetEntityAttr($enitityTypeName, $entityID);
		return !$userPermissions->HavePerm($enitityTypeName, BX_CRM_PERM_NONE, 'READ')
			&& $userPermissions->CheckEnityAccess($enitityTypeName, 'READ', isset($attrs[$entityID]) ? $attrs[$entityID] : array());
	}
}