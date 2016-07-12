<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CWebDavStorageCore extends CWebDavAbstractStorage
{
	/** @var CWebDavIblock|null */
	protected $webDav = null;

	public function __construct()
	{
	}

	/**
	 * @param \CWebDavIblock|null $webDav
	 * @return $this
	 */
	protected function setWebDav($webDav)
	{
		$this->webDav = $webDav;

		return $this;
	}

	/**
	 * @return \CWebDavIblock|null
	 */
	protected function getWebDav()
	{
		return $this->webDav;
	}

	/**
	 * @return bool
	 */
	protected function isSetWebDav()
	{
		return isset($this->webDav);
	}

	public function isCorrectName($name, &$msg)
	{
		if(substr($name, 0, 1) == '.')
		{
			$msg = 'File/Directory name should not start with "."';
			return false;
		}
		if(strpbrk($name, '/\:*?"\'|{}%&~'))
		{
			$msg = 'File/Directory name should not have /\:*?"\'|{}%&~ ';
			return false;
		}

		return true;
	}

	/**
	 * @param array $source
	 * @return array
	 */
	public function parseStorageExtra(array $source)
	{
		static::setStorageExtra(array(
			'iblockId' => empty($source['iblockId'])? null : (int)$source['iblockId'],
			'sectionId' => empty($source['sectionId'])? null : (int)$source['sectionId'],
		));

		return $this->getStorageExtra();
	}

	/**
	 * @param array $source
	 * @return array
	 */
	public function parseElementExtra(array $source)
	{
		return array(
			'id' => empty($source['id'])? null : (int)$source['id'],
			'iblockId' => empty($source['iblockId'])? null : (int)$source['iblockId'],
			'sectionId' => empty($source['sectionId'])? null : (int)$source['sectionId'],
			'rootSectionId' => empty($source['rootSectionId'])? null : (int)$source['rootSectionId'],
		);
	}

	/**
	 * @param bool  $reinit
	 * @param array $params
	 * @return $this
	 */
	protected function init($reinit = false, $params = array())
	{
		if($this->isSetWebDav() && !$reinit)
		{
			return $this;
		}

		CModule::IncludeModule('webdav');

		$key = $this->getStorageId();
		//$params = array();
		if(!empty($key['IBLOCK_SECTION_ID']))
		{
			$params['ROOT_SECTION_ID'] = $key['IBLOCK_SECTION_ID'];
		}
		//todo what is it? socnet magic to published docs
		global $USER;
		$params['DOCUMENT_TYPE'] = array('webdav', 'CIBlockDocumentWebdavSocnet', 'iblock_'.$key['IBLOCK_SECTION_ID'].'_user_'.intval($USER->getId()));

		return $this->setWebDav(new CWebDavIblock($key['IBLOCK_ID'], '', $params));
	}

	public function isUnique($name, $targetDirectoryId)
	{
		/** @noinspection PhpUndefinedVariableInspection */
		return $this->init()->getWebDav()->checkUniqueName($name, $targetDirectoryId, $res);
	}

	/**
	 * @param                $name
	 * @param                $targetDirectoryId
	 * @param CWebDavTmpFile $tmpFile
	 * @throws WebDavStorageBreakDownException
	 * @return bool|array
	 */
	public function addFile($name, $targetDirectoryId, CWebDavTmpFile $tmpFile)
	{
		$key = $this->getStorageId();
		if(!$targetDirectoryId)
		{
			//in root
			$targetDirectoryId = $key['IBLOCK_SECTION_ID'];
		}
		$name = $this->init()->getWebDav()->correctName($name);

		if(!$this->checkRights('create'))
		{
			return false;
		}

		$options = array(
			'new' => true,
			'dropped' => false,
			'arDocumentStates' => array(),
			'arUserGroups' => $this->getWebDav()->USER['GROUPS'],
			'TMP_FILE' => $tmpFile->getAbsolutePath(),
			'FILE_NAME' => $name,
			'IBLOCK_ID' => $key['IBLOCK_ID'],
			'IBLOCK_SECTION_ID' => $targetDirectoryId,
			'WF_STATUS_ID' => 1,
			'USER_FIELDS' => array(),
		);
		$options['arUserGroups'][] = 'Author';
		$GLOBALS['USER_FIELD_MANAGER']->EditFormAddFields($this->getWebDav()->getUfEntity(), $options['USER_FIELDS']);

		$this->getDb()->startTransaction();
		if (!$this->getWebDav()->put_commit($options))
		{
			$this->getDb()->rollback();
			$tmpFile->delete();
			return false;
		}
		$this->getDb()->commit();
		$tmpFile->delete();
		if(!empty($options['ELEMENT_ID']))
		{
			$this->clearCache();
			//todo needed?
			CIBlockElement::RecalcSections($options['ELEMENT_ID']);
			$file = $this->getFile(null, array('id' => $options['ELEMENT_ID']), true);
			if($file)
			{
				return $file;
			}
		}

		throw new WebDavStorageBreakDownException('bd addFile');
	}

	/**
	 * @param                $name
	 * @param                $targetElementId
	 * @param CWebDavTmpFile $tmpFile
	 * @throws WebDavStorageBreakDownException
	 * @return bool|array
	 */
	public function updateFile($name, $targetElementId, CWebDavTmpFile $tmpFile)
	{
		$this->init();
		$name = $this->getWebDav()->correctName($name);
		if(!$this->checkRights('update', array('name' => $name)))
		{
			return false;
		}

		$options = array(
			'new' => false,
			'FILE_NAME' => $name,
			'ELEMENT_ID' => $targetElementId,
			'arUserGroups' => $this->getWebDav()->USER['GROUPS'],
			'TMP_FILE' => $tmpFile->getAbsolutePath(),
		);

		$this->getDb()->startTransaction();
		if (!$this->getWebDav()->put_commit($options))
		{
			$this->getDb()->rollback();
			$tmpFile->delete();
			return false;
		}
		$this->getDb()->commit();
		$tmpFile->delete();

		if(!empty($options['ELEMENT_ID']))
		{
			$file = $this->getFile(null, array('id' => $options['ELEMENT_ID']), true);
			if($file)
			{
				return $file;
			}
		}

		throw new WebDavStorageBreakDownException('bd updateFile');
	}

	/**
	 * @param $name
	 * @param $targetElementId
	 * @param $newParentDirectoryId
	 * @throws WebDavStorageBreakDownException
	 * @return bool|array
	 */
	public function moveFile($name, $targetElementId, $newParentDirectoryId)
	{
		$this->init();
		$key = $this->getStorageId();
		if(!$newParentDirectoryId)
		{
			//in root
			$newParentDirectoryId = $key['IBLOCK_SECTION_ID'];
		}
		$name = $this->init()->getWebDav()->correctName($name);

		$name = $this->getWebDav()->correctName($name);
		$pathArray = $this->getPathArrayForSection($newParentDirectoryId);
		$pathArray[] = $name;

		$newPath = '/' . implode('/', $pathArray);
		$options = array(
			'element_id' => $targetElementId,
			'dest_url' => $newPath,
			'overwrite' => false,
		);

		$response = $this->getWebDav()->move($options);
		$oError = $this->getLastException();
		if(intval($response) == 412) //FILE_OR_FOLDER_ALREADY_EXISTS
		{
			return false;
		}
		elseif(!$oError && intval($response) >= 300)
		{
			return false;
		}
		elseif($oError)
		{
			return false;
		}

		$file = $this->getFile(null, array('id' => $targetElementId), true);
		if($file)
		{
			return $file;
		}

		throw new WebDavStorageBreakDownException('bd moveFile');
	}

	public function getVersionDelete($element)
	{
		if(empty($element) || !is_array($element))
		{
			return false;
		}

		return CWebDavLogDeletedElement::isAlreadyRemoved(array(
			'IBLOCK_ID' => $element['extra']['iblockId'],
			'IBLOCK_SECTION_ID' => $element['extra']['rootSectionId'],
			'IS_DIR' => (bool)$element['isDirectory'],
			'ELEMENT_ID' => $element['id'],
		));
	}

	public function deleteFile($file)
	{
		if(empty($file) || !is_array($file))
		{
			return false;
		}
		$this->init();
		$result = $this->getWebDav()->delete(array('element_id' => $file['extra']['id']));
		if (intval($result) != 204)
		{
			//$this->getWebDav()->LAST_ERROR;
			return false;
		}
		$lastVersion = $this->getVersionDelete($file);
		$this->clearCache();

		return $lastVersion;
	}

	public function deleteDirectory($directory)
	{
		if(empty($directory) || !is_array($directory))
		{
			return false;
		}
		$this->init();
		$result = $this->getWebDav()->delete(array('section_id' => $directory['extra']['id']));
		if (intval($result) != 204)
		{
			//$this->getWebDav()->LAST_ERROR;
			return false;
		}
		$lastVersion = $this->getVersionDelete($directory);
		$this->clearCache();

		return $lastVersion;
	}

	public function getDirectory($id, array $extra, $skipCheckId = false)
	{
		if(!$skipCheckId && $this->generateId(array('ID' => $extra['id'], 'FILE' => false)) != $id)
		{
			return false;
		}
		//todo usage propfind with section_id options
		$storageId = $this->getStorageId();
		CTimeZone::Disable();
		$dir = CIBlockSection::GetList(array(), array(
			'IBLOCK_ID' => (int)$storageId['IBLOCK_ID'],
			'ID' => (int)$extra['id'],
		), false, array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'PATH', 'NAME', 'TIMESTAMP_X', 'XML_ID', 'IBLOCK_CODE'));
		CTimeZone::Enable();

		$dir = $dir->fetch();
		if(!$dir || !is_array($dir))
		{
			return array();
		}

		$dir['PATH'] = implode('/', ($this->getPathArrayForSection($extra['id'])));
		//format to exchange format and return file
		$formatDirs = $this->formatSectionsToResponse(array($dir));

		return array_shift($formatDirs)?:array();
	}

	/**
	 * @param $name
	 * @param $targetDirectoryId
	 * @param $newParentDirectoryId
	 * @return bool|string
	 */
	public function moveDirectory($name, $targetDirectoryId, $newParentDirectoryId)
	{
		$this->init();

		$key = $this->getStorageId();
		if(!$newParentDirectoryId)
		{
			//in root
			$newParentDirectoryId = $key['IBLOCK_SECTION_ID'];
		}

		$pathArray = $this->getPathArrayForSection($newParentDirectoryId);
		$pathArray[] = $name;

		$newPath = '/' . implode('/', $pathArray);
		$options = array(
			'section_id' => $targetDirectoryId,
			'dest_url' => $newPath,
			'overwrite' => false,
		);

		$response = $this->getWebDav()->move($options);
		$oError = $this->getLastException();
		if(!$oError && intval($response) >= 300)
		{
			return false;
		}
		elseif($oError)
		{
			return false;
		}
		$this->clearCache();

		$directory = $this->getDirectory(null, array('id' => $targetDirectoryId), true);
		//todo getPathArrayForSection() static cached. And we set path manual.
		$directory['path'] = $newPath;

		return $directory;
	}

	/**
	 * @param $id
	 * @return array
	 */
	protected function getPathArrayForSection($id)
	{
		return $this->init()->getWebDav()->getNavChain(array('section_id' => (int)$id));
	}

	public function getFile($id, array $extra, $skipCheckId = false)
	{
		if(!$skipCheckId && $this->generateId(array('ID' => $extra['id'], 'FILE' => true)) != $id)
		{
			return false;
		}
		CTimeZone::Disable();
		$storageId = $this->getStorageId();
		$options = array(
			'path' => '/',
			'depth' => '10000',
			//'element_id' => (int)$extra['id']
		);
		/** @noinspection PhpUndefinedVariableInspection */
		$element = $this
			->init()
			->getWebDav()
			->propFind($options, $files, array(
				'COLUMNS' => array(),
				'return'  => 'array',
				//todo fix to $arParentPerms = $this->GetPermissions in CWebDavIblock::_get_mixed_list()
				'PARENT_ID' => $storageId['IBLOCK_SECTION_ID'],
				'FILTER'  => array(
					'ID' => (int)$extra['id'],
					'IBLOCK_ID' => (int)$storageId['IBLOCK_ID'],
					'SECTION_ID' => (int)$storageId['IBLOCK_SECTION_ID'],
				),
			))
		;
		CTimeZone::Enable();
		//fetch first from result
		$file = array_shift($element['RESULT']);
		//format to exchange format and return file
		$formatFiles = $this->formatFilesToResponse($file? array($file) :array());
		return array_shift($formatFiles)?:array();
	}

	/**
	 * @param $name
	 * @param $parentDirectoryId
	 * @return bool|array
	 */
	public function addDirectory($name, $parentDirectoryId)
	{
		$key = $this->getStorageId();
		if(!$parentDirectoryId)
		{
			//in root
			$parentDirectoryId = $key['IBLOCK_SECTION_ID'];
		}
		/** @var CWebDavIblock $webDav  */
		$webDav = $this
			->init()
			->getWebDav()
		;

		$sectionId = null;
		$name = $webDav->correctName($name);
		$pathArray = $this->getPathArrayForSection($parentDirectoryId);
		$pathArray[] = $name;
		$path = '/' . implode('/', $pathArray);
		$response = $webDav->MKCOL(array('path' => $path));

		if($exception = $this->getLastException())
		{
			if($exception['code'] == 'FOLDER_IS_EXISTS')
			{
				$sectionId = $webDav->arParams['item_id'];
			}
			else
			{
				return array();
			}
		}
		elseif($response == '201 Created')
		{
			$sectionId = $webDav->arParams['changed_element_id'];
		}

		if(!$sectionId)
		{
			return array();
		}
		//todo usage getDirectory
		CTimeZone::Disable();
		$section = CIBlockSection::GetList(array(), array(
			'ID' => (int)$sectionId,
			'IBLOCK_SECTION_ID' => (int)$webDav->arParams['parent_id'],
			'IBLOCK_ID' => (int)$webDav->IBLOCK_ID,
		), false, array('ID', 'TIMESTAMP_X'));
		CTimeZone::Enable();
		if(!($section = $section->fetch()))
		{
			return array();
		}
		$this->clearCache();
		$dirs = $this->formatSectionsToResponse(array(
			array(
				'ID' => $section['ID'],
				'IBLOCK_SECTION_ID' => $webDav->arParams['parent_id'],
				'IBLOCK_ID' => $webDav->IBLOCK_ID,
				'PATH' => $path,
				'NAME' => $name,
				'TIMESTAMP_X' => $section['TIMESTAMP_X'],
			)
		));

		return array_shift($dirs)?:array();
	}

	protected function getLastException()
	{
		/** @var CAllMain */
		global $APPLICATION;

		$exception = $APPLICATION->GetException();
		if($exception instanceof CApplicationException)
		{
			return array(
				'code' => $exception->getId(),
			);
		}

		return false;
	}

	public function sendFile($file)
	{
		if(empty($file['extra']['id']))
		{
			throw new Exception('Wrong file id');
		}

		//todo session_commit() ?
		//session_write_close();
		$this
			->getWebDav()
			->fileViewByUser($file['extra']['id'])
			//->sendHistoryFile($file['extra']['id'])
		;
	}

	/**
	 * @param        $version
	 * @param string $path
	 * @return array
	 */
	protected function searchFilesByPropFind($version, $path = '/')
	{
		$storageId = $this->getStorageId();
		$version = convertTimeStamp((int)$version, 'FULL');
		$options = array('path' => $path, 'depth' => '10000');
		CTimeZone::Disable();
		/** @noinspection PhpUndefinedVariableInspection */
		$result  = $this->getWebDav()->propFind($options, $files, array(
			'COLUMNS' => array(),
			'return'  => 'array',
			//todo fix to $arParentPerms = $this->GetPermissions in CWebDavIblock::_get_mixed_list()
			'PARENT_ID' => $storageId['IBLOCK_SECTION_ID'],
			'FILTER'  => array(
				'timestamp_1' => $version,
			),
		));
		CTimeZone::Enable();

		return $this->formatFilesToResponse($result['RESULT']);
	}

	/**
	 * Hide folder or file if path contains trash, dropp
	 * @param array $file
	 * @return bool
	 */
	protected function isHiddenElement(array $file)
	{
		$nameDropped = $this->getWebDav()->meta_names['DROPPED']['alias'];
		$nameTrash = $this->getWebDav()->meta_names['TRASH']['alias'];
		if(strpos($file['PATH'], '/' . $nameDropped . '/') === 0)
		{
			return true;
		}
		if(strpos($file['PATH'], '/' . $nameTrash . '/') === 0)
		{
			return true;
		}
		return false;
	}

	protected function getDeletedElements($version)
	{
		$deletedItems = array();
		$version = CWebDavDiskDispatcher::convertFromExternalVersion($version);
		if(!$version)
		{
			return array();
		}
		$storageId = $this->getStorageId();

		$query = CWebDavLogDeletedElement::getList(array(), array(
			'VERSION' => $version,
			'IBLOCK_ID' => $storageId['IBLOCK_ID'],
			'IBLOCK_SECTION_ID' => $storageId['IBLOCK_SECTION_ID'],
		));
		if(!$query)
		{
			throw new Exception('Error in DB');
		}

		while($row = $query->fetch())
		{
			if(!$row)
			{
				continue;
			}
			$deletedItems[] = array(
				'id' => $row['ELEMENT_ID'],
				'isDirectory' => (bool)$row['IS_DIR'],
				'isDeleted' => true,
				'storageId' => $this->getStringStorageId(),
				'version' => CWebDavDiskDispatcher::convertToExternalVersion($row['VERSION']),
			);
		}

		return $deletedItems;
	}

	public function getSnapshot($version = 0)
	{
		CTimeZone::Disable();
		$sections = $this
			->init()
			->getWebDav()
			->getSectionsTree(array(
				'path' => '/'
		));
		//sortByColumn($sections, array('PATH' => SORT_ASC));

		$sections = $this->formatSectionsToResponse($sections);
		$sections = $this->filterSectionByVersion($sections, $version);
		$files    = $this->searchFilesByPropFind(CWebDavDiskDispatcher::convertFromExternalVersion($version));
		CTimeZone::Enable();

		return array_merge($sections, $files, $this->getDeletedElements($version) /*, $this->getSnapshotFromTrash($version)*/ );
	}

	/**
	 * @param $version
	 * @return array
	 */
	protected function getSnapshotFromTrash($version)
	{
		CTimeZone::Disable();
		$version = convertTimeStamp((int)$version, 'FULL');
		$options = array('path' => '/' . $this->getWebDav()->meta_names['TRASH']['name'], 'depth' => '1');
		$this->getWebDav()->meta_state = 'TRASH';
		/** @noinspection PhpUndefinedVariableInspection */
		$result  = $this->getWebDav()->propFind($options, $files, array(
			'COLUMNS' => array(),
			'return'  => 'array',
			'FILTER'  => array(
				'timestamp_1' => $version,
				'TIMESTAMP_X_1' => $version,
			),
		));
		CTimeZone::Enable();

		//file, folders format. This is not good. But it is not used.
		return $this->formatFilesToResponse($result['RESULT']);
	}

	/**
	 * @param array $element
	 * @return string
	 */
	public function generateId(array $element)
	{
		//this is unique id in this storage (pair iblock + id)
		return implode('|', array(
			'st' . $this->getStringStorageId(), (empty($element['FILE'])? 's' : 'f') . $element['ID']
		));
	}

	protected function skipSection(array $section)
	{
		return false;
		if(!empty($section['XML_ID']) && $section['XML_ID'] == 'SHARED_FOLDER')
		{
			return true;
		}
		return false;
	}

	/**
	 * @param array $sections
	 * @return array
	 */
	protected function formatSectionsToResponse(array $sections)
	{
		$storageId = $this->getStorageId();
		$rootSection = isset($storageId['IBLOCK_SECTION_ID'])? $storageId['IBLOCK_SECTION_ID'] : null;
		$result = array();
		foreach ($sections as $section)
		{
			if(empty($section) || $this->isHiddenElement($section))
			{
				continue;
			}
			$data = array(
				'id' => $this->generateId($section),
				'isDirectory' => true,
				'isDeleted' => false,
				'storageId' => $this->getStringStorageId(),
				'path' => isset($section['PATH']) ? '/' . trim($section['PATH'], '/') : null,
				'name' => $section['NAME'],
				'version' => (string)$this->generateTimestamp($section['TIMESTAMP_X']),
				'extra' => array(
					'id' => (string)$section['ID'],
					'iblockId' => (string)$section['IBLOCK_ID'],
					'sectionId' => (string)$section['IBLOCK_SECTION_ID'],
					'rootSectionId' => (string)$rootSection,
					'name' => $section['NAME'],
				),
			);
			if($rootSection != $section['IBLOCK_SECTION_ID'])
			{
				$data['parentId'] = $this->generateId(array('FILE' => false, 'ID' => $section['IBLOCK_SECTION_ID']));
			}
			$result[] = $data;
		}
		unset($section);

		return $result;
	}

	/**
	 * @param array $files
	 * @return array
	 */
	protected function formatFilesToResponse(array $files)
	{
		$storageId = $this->getStorageId();
		$rootSection = isset($storageId['IBLOCK_SECTION_ID'])? $storageId['IBLOCK_SECTION_ID'] : null;
		$result = array();

		foreach ($files as $file)
		{
			if(empty($file) || $this->isHiddenElement($file))
			{
				continue;
			}
			$data = array(
				'id' => $this->generateId($file),
				'isDirectory' => false,
				'isDeleted' => false,
				'storageId' => $this->getStringStorageId(),
				'path' => '/' . trim($file['PATH'], '/'),
				'name' => $file['NAME'],
				'revision' => (string)$file['PROPERTY_FILE_VALUE'],
				'version' => (string)$this->generateTimestamp($file['TIMESTAMP_X']),
				'extra' => array(
					'id' => (string)$file['ID'],
					'iblockId' => (string)$file['IBLOCK_ID'],
					'sectionId' => (string)$file['IBLOCK_SECTION_ID'],
					'rootSectionId' => (string)$rootSection,
					'name' => $file['NAME'],
				),
				'size' => $file['FILE']['FILE_SIZE'],
			);

			if($rootSection != $file['IBLOCK_SECTION_ID'])
			{
				$data['parentId'] = $this->generateId(array('FILE' => false, 'ID' => $file['IBLOCK_SECTION_ID']));
			}
			$result[] = $data;
		}
		unset($file);

		return $result;
	}

	/**
	 * @param array $sections
	 * @param int   $version
	 * @return array
	 */
	protected function filterSectionByVersion(array $sections, $version = 0)
	{
		if($version == 0)
		{
			return $sections;
		}

		$self = $this;
		return array_filter($sections, function($section) use($version, $self){
			return ($self::compareVersion($section['version'], $version) >= 0);
		});
	}

	protected function generateTimestamp($date)
	{
		return CWebDavDiskDispatcher::convertToExternalVersion(makeTimeStamp($date));
	}

	protected function clearCache()
	{
		WDClearComponentCache(array(
			'webdav.element.edit',
			'webdav.element.hist',
			'webdav.element.upload',
			'webdav.element.view',
			'webdav.menu',
			'webdav.section.edit',
			'webdav.section.list'));
	}

	protected function checkRights($action, array $element = array())
	{
		return true;
		//maybe throw expection?
		$action = strtolower($action);
		if($action == 'create')
		{
			if(!$this->init()->getWebDav()->checkWebRights('', array('action' => 'create'), false))
			{
				return false;
			}
		}
		elseif($action == 'update' && isset($element['name']) && $element['name'])
		{
			if(!$this->init()->getWebDav()->checkWebRights('PUT', array('arElement' => $element['name']), false))
			{
				return false;
			}
		}

		return true;
	}
}