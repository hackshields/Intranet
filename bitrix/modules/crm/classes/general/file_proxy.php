<?php
class CCrmFileProxy
{
	public static function WriteFileToResponse($ownerTypeID, $ownerID, $fieldName, $fileID, &$errors, $options = array())
	{
		$ownerTypeID = intval($ownerTypeID);
		$ownerID = intval($ownerID);
		$fieldName = strval($fieldName);
		$fileID = intval($fileID);

		if(!CCrmOwnerType::IsDefined($ownerTypeID) || $ownerID <= 0 || $fieldName === '' || $fileID <= 0)
		{
			$errors[] = 'File not found';
			return false;
		}

		if(!CCrmPerms::IsAdmin())
		{
			$userPermissions = CCrmPerms::GetCurrentUserPermissions();
			if($userPermissions->HavePerm(CCrmOwnerType::ResolveName($ownerTypeID), BX_CRM_PERM_NONE, 'READ'))
			{
				$errors[] = 'Access denied.';
				return false;
			}
		}

		$userFields = $GLOBALS['USER_FIELD_MANAGER']->GetUserFields(
			CCrmOwnerType::ResolveUserFieldEntityID($ownerTypeID),
			$ownerID,
			LANGUAGE_ID
		);

		$field = is_array($userFields) && isset($userFields[$fieldName]) ? $userFields[$fieldName] : null;
		if(!(is_array($field) && $field['USER_TYPE_ID'] === 'file'))
		{
			$errors[] = 'File not found';
			return false;
		}

		$fileIDs = isset($field['VALUE'])
			? (is_array($field['VALUE'])
				? $field['VALUE']
				: array($field['VALUE']))
			: array();

		//The 'strict' flag must be 'false'. In MULTIPLE mode value is an array of integers. In SIGLE mode value is a string.
		if(!in_array($fileID, $fileIDs, false))
		{
			$errors[] = 'File not found';
			return false;
		}

		$fileInfo = CFile::GetFileArray($fileID);
		if(!is_array($fileInfo))
		{
			$errors[] = 'File not found';
			return false;
		}

		$options = is_array($options) ? $options : array();
		set_time_limit(0);

		$name = $fileInfo['ORIGINAL_NAME'];
		$contentType = strtolower($fileInfo['CONTENT_TYPE']);
		if(CFile::IsImage($name, $contentType))
		{
			$options['content_type'] = $contentType;
		}
		elseif(strpos($contentType, 'excel') !== false)
		{
			$options['content_type'] = 'application/vnd.ms-excel';
		}
		elseif(strpos($contentType, 'word') !== false)
		{
			$options['content_type'] = 'application/msword';
		}
		else
		{
			$options['content_type'] = 'application/octet-stream';
			$options['force_download'] = true;
		}

		CFile::ViewByUser($fileInfo, $options);
		return true;
	}
}
?>