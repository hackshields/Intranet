<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CWebDavDiskDispatcher
{
	const VERSION            = 2;
	const STATUS_SUCCESS     = 'success';
	const STATUS_DENIED      = 'denied';
	const STATUS_ERROR       = 'error';
	const STATUS_TOO_BIG     = 'too_big';
	const STATUS_NOT_FOUND   = 'not_found';
	const STATUS_OLD_VERSION = 'old_version';
	const STATUS_NO_SPACE    = 'no_space';
	const STATUS_UNLIMITED   = 'unlimited';
	const STATUS_LIMITED     = 'limited';

	protected $ignoreQuotaError = false;
	protected static $dataDeletingMark = array();

	/**
	 * @return float
	 */
	public static function getTimestampFloat()
	{
		return round(microtime(true) * 1000);
	}

	public static function getVersion()
	{
		return static::VERSION;
	}

	public function enableIgnoreQuotaError()
	{
		$this->ignoreQuotaError = true;
	}

	public function disableIgnoreQuotaError()
	{
		$this->ignoreQuotaError = false;
	}

	public function ignoreQuotaError()
	{
		return (bool)$this->ignoreQuotaError;
	}

	/**
	 * @param string $message
	 * @return array
	 */
	public function sendError($message)
	{
		return $this->sendResponse(array(
			'status' => static::STATUS_ERROR,
			'message' => $message,
		));
	}

	public function sendSuccess(array $response = array())
	{
		$response['status'] = static::STATUS_SUCCESS;
		return $this->sendResponse($response);
	}

	public function sendResponse($response)
	{
		if(!$this->ignoreQuotaError() && $this->isQuotaError())
		{
			return array(
				'status' => static::STATUS_NO_SPACE,
			);
		}
		return $response;
	}

	/**
	 * @return CAllUser
	 */
	protected function getUser()
	{
		global $USER;

		return $USER;
	}

	/**
	 * @return CDatabase
	 */
	protected function getDb()
	{
		global $DB;

		return $DB;
	}

	/**
	 * @return CAllMain
	 */
	protected static function getApplication()
	{
		global $APPLICATION;

		return $APPLICATION;
	}

	/**
	 * @param array  $extra
	 * @param string $storageId
	 * @throws Exception
	 * @return CWebDavAbstractStorage
	 */
	protected function getStorageObject(array $extra = array(), $storageId = '')
	{
		$storage = new CWebDavStorageCore();
		if(!empty($extra))
		{
			$extra = $storage->parseStorageExtra($extra);
			$storage->setStorageId(array(
				'IBLOCK_ID' => $extra['iblockId'],
				'IBLOCK_SECTION_ID' => $extra['sectionId'],
			));

			if($storageId)
			{
				if($storageId != $storage->getStringStorageId())
				{
					throw new Exception('Wrong storage id!');
				}
			}
		}

		return $storage;
	}

	protected function checkRequiredParams(array $target, array $required)
	{
		$success = true;
		foreach ($required as $item)
		{
			if(!isset($target[$item]) || (!$target[$item] && !(is_string($target[$item]) && strlen($target[$item]))))
			{
				$success = false;
				break;
			}
		}
		unset($item);
		if(!$success)
		{
			throw new Exception('Wrong params');
		}

		return;
	}

	/**
	 * @return array
	 * @throws Exception
	 */
	protected function getUserStorageId()
	{
		$userFilesOptions = COption::getOptionString('webdav', 'user_files', null);
		if($userFilesOptions == null)
		{
			throw new Exception('Where are options "user_files"?');
		}
		$userFilesOptions = unserialize($userFilesOptions);
		$iblockId = $userFilesOptions[CSite::getDefSite()]['id'];
		$userSectionId = CWebDavIblock::getRootSectionIdForUser($iblockId, $this->getUser()->getId());
		if(!$userSectionId)
		{
			throw new Exception('Wrong section for user ' . $this->getUser()->getLastName());
		}

		return array(
			'IBLOCK_ID' => $iblockId,
			'IBLOCK_SECTION_ID' => $userSectionId,
		);
	}

	public function getStorageList()
	{
	}

	public function getSubscriptionsStorageList()
	{
	}

	//todo version is long int
	public static function convertFromExternalVersion($version)
	{
		if(substr($version, -3, 3) === '000')
		{
			return substr($version, 0, -3);
		}
		return $version;
	}

	public static function convertToExternalVersion($version)
	{
		return ((string)$version) . '000';
	}

	public function processActionSnapshot(array $params = array())
	{
		$this->enableIgnoreQuotaError();
		//todo version is long int
		$version = $params['version'];

		$userStorageId = $this->getUserStorageId();
		$storage = $this->getStorageObject();
		$items = $storage
			->setStorageId($userStorageId)
			->getSnapshot($version)
		;
		$quota = $this->processActionGetDiskQuota();

		return $this->sendResponse(array('quota' => $quota, 'snapshot' => $items));
	}

	public static function sendEventToOwners($element = null, $section = null, $debug = '')
	{
		if($element && is_array($element) && !empty($element['IBLOCK_SECTION_ID']))
		{
			if($element['IBLOCK_CODE'] != 'user_files')
			{
				return false;
			}
			$section = CIBlockSection::GetList(array(), array(
				'IBLOCK_ID' => (int)$element['IBLOCK_ID'],
				'ID' => (int)$element['IBLOCK_SECTION_ID'],
				), false,
				array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID',
				'PATH', 'NAME', 'TIMESTAMP_X', 'XML_ID', 'IBLOCK_CODE',
				'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'CREATED_BY'
			))->fetch();
		}

		if($section && is_array($section) && !empty($section['IBLOCK_CODE']) &&  $section['IBLOCK_CODE'] == 'user_files')
		{
			$debug = 'sec_' . $debug;
			$userId = static::findOwnerIdSection($section);
			static::sendChangeStatus($userId, $debug . '_puper');
		}
	}

	/**
	 * @param array $section
	 * @param bool  $returnUserAndSection todo rework
	 * @return array|bool|mixed
	 */
	protected static function findOwnerIdSection(array $section, $returnUserAndSection = false)
	{
		if(isset($section['DEPTH_LEVEL']) && $section['DEPTH_LEVEL'] == 1)
		{
			$sectionOwnerElement = $section;
		}
		else
		{
			//todo so normally to look for root section?
			$sectionOwnerElement = CIBlockSection::GetList(array('LEFT_MARGIN' => 'DESC'), array(
				'IBLOCK_ID'         => $section['IBLOCK_ID'],
				'DEPTH_LEVEL'       => 1,
				'IBLOCK_SECTION_ID' => null,
				'!LEFT_MARGIN'      => $section['LEFT_MARGIN'],
				'!RIGHT_MARGIN'     => $section['RIGHT_MARGIN'],
			), false, array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'CREATED_BY', 'NAME'))->fetch();
		}
		//$user = CUser::GetById($sectionOwnerElement['CREATED_BY'])->fetch();
		if(empty($sectionOwnerElement))
			return false;

		return !$returnUserAndSection? $sectionOwnerElement['CREATED_BY'] : array($sectionOwnerElement['CREATED_BY'], $sectionOwnerElement);
	}


	/**
	 * @param        $userId
	 * @param string $debug
	 */
	public static function sendChangeStatus($userId, $debug = '')
	{
		static::sendEvent($userId, array(
			'params' => array(
				'change' => true,
				'timestamp' => static::getTimestampFloat(),
				//'debug' => $debug,
			),
		));
	}

	/**
	 * @param       $userId
	 * @param array $data
	 * @return bool
	 */
	public static function sendEvent($userId, array $data)
	{
		if(empty($userId))
		{
			return false;
		}
		if(!CModule::IncludeModule('pull'))
		{
			return false;
		}
		$data['module_id'] = 'webdav';
		$data['command'] = 'notify';
		CPullStack::AddByUser($userId, $data);
	}

	public function processActionDownload(array $params)
	{
		$this->checkRequiredParams($params, array('id', 'version', 'extra', 'storageExtra', 'storageId'));

		$id = $params['id'];
		$version = $params['version'];

		$storage = $this->getStorageObject($params['storageExtra'], $params['storageId']);
		$extra = $storage->parseElementExtra($params['extra']);

		$file = $storage->getFile($id, $extra);
		//not found or we have new version
		if( !$file || (!isset($file['version']) || $storage::compareVersion($file['version'], $version) != 0) )
		{
			return $this->sendResponse(array(
				'status' => static::STATUS_NOT_FOUND,
			));
		}
		else
		{
			$storage->sendFile($file);
		}
	}

	public function processActionDelete(array $params)
	{
		$this->enableIgnoreQuotaError();
		$this->checkRequiredParams($params, array('id', 'version', 'extra', 'storageExtra', 'storageId')); //isDirectory

		$id = $params['id'];
		$version = $params['version'];
		$isDirectory = (bool)$params['isDirectory'];

		$lastVersion = null;
		$isDirectory = (bool)$isDirectory;
		$storage = $this->getStorageObject($params['storageExtra'], $params['storageId']);
		$extra = $storage->parseElementExtra($params['extra']);

		$element = $isDirectory?
			$storage->getDirectory($id, $extra):
			$storage->getFile($id, $extra);

		if($element)
		{
			if($storage::compareVersion($element['version'], $version) > 0)
			{
				$element['status'] = static::STATUS_OLD_VERSION;
				return $this->sendResponse($element);
			}
			$lastVersion = $isDirectory?
				$storage->deleteDirectory($element):
				$storage->deleteFile($element);
		}
		else //is already removed?
		{
			$lastVersion = $storage->getVersionDelete(array(
				'id' => $id,
				'version' => $version,
				'isDirectory' => $isDirectory,
				'extra' => $extra,
			));
		}

		if((bool)$lastVersion)
		{
			return $this->sendSuccess(array('version' => $this->convertToExternalVersion((string)$lastVersion)));
		}
		return $this->sendResponse(array('status' => static::STATUS_NOT_FOUND));
	}

	public function processActionDirectory(array $params)
	{
		$this->checkRequiredParams($params, array('name', 'storageExtra', 'storageId'));

		$folderName = $params['name'];
		$inRoot = (bool)$params['inRoot'];
		$isUpdate = (bool)$params['update'];

		$storage = $this->getStorageObject($params['storageExtra'], $params['storageId']);

		if(!$storage->isCorrectName($folderName, $msg))
		{
			return $this->sendResponse(array(
				'status' => static::STATUS_DENIED,
				'message' => $msg,
			));
		}

		$parentFolderId = null;
		if(!$inRoot)
		{
			$parentFolderExtra = $storage->parseElementExtra($params['parentExtra']);
			$parentFolderId = $parentFolderExtra['id'];
			//$parentFolderVersion = $_POST['version'];
		}

		if($isUpdate)
		{
			$this->checkRequiredParams($params, array('id', 'version'));
			$id = $params['id'];
			$version = $params['version'];

			$folderExtra = $storage->parseElementExtra($params['extra']);
			$targetFolder = $storage->getDirectory($id, $folderExtra);
			if(empty($targetFolder))
			{
				return $this->sendError('Not found directory to update');
			}

			$storageKey = $storage->getStorageId();
			//it is the same directory todo this logic $storage->moveDirectory, but ....we have many query. Or refactor signature
			if($targetFolder['extra']['sectionId'] == $storageKey['IBLOCK_SECTION_ID'] && $folderName == $targetFolder['name'])
			{
				return $this->sendSuccess($targetFolder);
			}
			if(!($item = $storage->moveDirectory($folderName, $targetFolder['extra']['id'], $parentFolderId)))
			{
				return $this->sendError('Error in action move');
			}
			return $this->sendSuccess($item);
		}
		else
		{
			//todo folder may make in storage root, but parentFolder not exist
			$item = $storage->addDirectory($folderName, $parentFolderId);
		}

		if(empty($item))
		{
			return $this->sendError('Error in makeDirectory');
		}
		return $this->sendSuccess($item);
	}

	public function processActionUpload()
	{
		$storage = $this->getStorageObject();

		if(
			$storage::compareVersion(
				$_SERVER['CONTENT_LENGTH'],
				(string)CUtil::unformat(ini_get('upload_max_filesize'))) > 0 ||
			$storage::compareVersion(
				$_SERVER['CONTENT_LENGTH'],
				(string)CUtil::unformat(ini_get('post_max_size'))) > 0
		)
		{
			return $this->sendResponse(array('status' => static::STATUS_TOO_BIG));
		}

		if(empty($_FILES['file']) || !is_array($_FILES['file']))
		{
			throw new Exception('Please load file!');
		}
		$tmpFile = CWebDavTmpFile::buildFromDownloaded($_FILES['file']);
		if(!$tmpFile->save())
		{
			throw new Exception('Error in DB');
		}

		return $this->sendSuccess(array(
			'token' => $tmpFile->name,
		));
	}

	public function processActionRollbackUpload(array $params)
	{
		$this->enableIgnoreQuotaError();
		$this->checkRequiredParams($params, array('token'));

		$token = $params['token'];
		if(!($tmpFile = CWebDavTmpFile::buildByName($token)))
		{
			throw new Exception('Not found file by token');
		}
		if($tmpFile->delete())
		{
			return $this->sendSuccess();
		}
		return $this->sendError('Bad attempt to delete token');
	}

	public function processActionUpdate(array $params)
	{
		$this->checkRequiredParams($params, array('storageExtra', 'storageId', 'name'));

		$tmpFile = $parentFolderId = $targetSectionId = $elementId = null;
		$storage = $this->getStorageObject($params['storageExtra'], $params['storageId']);
		$filename = $params['name'];
		$token = empty($params['token'])? null : $params['token'];
		$inRoot = (bool)$params['inRoot'];
		$isUpdate = (bool)$params['update'];

		if($token && !($tmpFile = CWebDavTmpFile::buildByName($token)))
		{
			throw new Exception('Not found file by token');
		}

		if(!$storage->isCorrectName($filename, $msg))
		{
			$tmpFile && ($tmpFile->delete());
			return $this->sendResponse(array(
				'status' => static::STATUS_DENIED,
				'message' => $msg,
			));
		}

		if($inRoot)
		{
			$storageExtra = $storage->getStorageExtra();
			$targetSectionId = $storageExtra['sectionId'];
			$parentFolderId = $storageExtra['sectionId'];
		}
		else
		{
			$parentFolderExtra = $storage->parseElementExtra($params['parentExtra']);
			$targetSectionId = $parentFolderExtra['id'];
			$parentFolderId = $parentFolderExtra['id'];
		}

		if($isUpdate)
		{
			$this->checkRequiredParams($params, array('id', 'version'));
			$version = $params['version'];
			$fileExtra = $storage->parseElementExtra($params['extra']);
			$elementId = $fileExtra['id'];

			$file = $storage->getFile($params['id'], $fileExtra);
			if(empty($file))
			{
				return $this->sendResponse(array(
					'status' => static::STATUS_NOT_FOUND,
				));
			}
			if($storage::compareVersion($file['version'], $version) > 0)
			{
				$file['status'] = static::STATUS_OLD_VERSION;
				return $this->sendResponse($file);
			}

			//todo simple check for move/rename
			if($filename != $file['extra']['name'] || $parentFolderId != $file['extra']['sectionId'])
			{
				if(!$storage->isUnique($filename, $parentFolderId))
				{
					$file['status'] = static::STATUS_OLD_VERSION;
					return $this->sendResponse($file);
				}
				$file = $storage->moveFile($filename, $elementId, $parentFolderId);
				if(!$file)
				{
					return $this->sendError('Error in move/rename (update) file');
				}
				if(!$tmpFile)
				{
					return $this->sendSuccess($file);
				}
			}
			unset($file);

			if($tmpFile) //update content
			{
				$file = $storage->updateFile($filename, $elementId, $tmpFile);
				if($file)
				{
					return $this->sendSuccess($file);
				}
				return $this->sendResponse(array(
					'status' => static::STATUS_DENIED,
					'message'=> 'Error in updateFile',
				));
			}
		}
		else
		{
			if(!$storage->isUnique($filename, $targetSectionId))
			{
				return $this->sendResponse(array(
					'status' => static::STATUS_OLD_VERSION,
				));
			}
			$newFile = $storage->addFile($filename, $targetSectionId, $tmpFile);
			if($newFile)
			{
				return $this->sendSuccess($newFile);
			}
			//else denied
		}

		return $this->sendResponse(array(
			'status' => static::STATUS_DENIED,
			'message'=> 'Error in add/update file',
		));
	}

	public function processActionInitialize()
	{
		$this->enableIgnoreQuotaError();
		$userStorageId = $this->getUserStorageId();
		$storage = $this->getStorageObject();
		$storage->setStorageId($userStorageId);

		return $this->sendResponse(array(
			'status' => static::STATUS_SUCCESS,
			'userStorageId' => $storage->getStringStorageId(),
			'userStorageExtra' => array(
				'iblockId' => (string)$userStorageId['IBLOCK_ID'],
				'sectionId'=> (string)$userStorageId['IBLOCK_SECTION_ID'],
			),
			'version'=> static::VERSION,
		));
	}

	public function processActionGetDiskQuota()
	{
		$this->enableIgnoreQuotaError();
		$quota = CDiskQuota::GetDiskQuota();
		if($quota === true)
		{
			return $this->sendResponse(array(
				'status' => static::STATUS_UNLIMITED,
				'quota' => null,
			));
		}

		return $this->sendResponse(array(
			'status' => static::STATUS_LIMITED,
			'quota' => $quota === false? 0 : $quota,
		));
	}

	protected function isQuotaError()
	{
		foreach($this->getApplication()->ERROR_STACK + array($this->getApplication()->LAST_ERROR) as $error)
		{
			if(!($error instanceof CAdminException))
			{
				continue;
			}
			if($error->GetID() == 'QUOTA_BAD')
			{
				return true;
			}
			if(!is_array($error->GetMessages()))
			{
				continue;
			}
			foreach ($error->GetMessages() as $msg)
			{
				if($msg['id'] == 'QUOTA_BAD')
				{
					return true;
				}
			}
			unset($msg);
		}

		return false;
	}

	//to add an element for a deleting mark
	public static function addElementForDeletingMark(array $data, $dataDetermineOwner = array(), $isSection = true)
	{
		static $cacheDataDetermineOwner = array();

		if(empty($data['IBLOCK_CODE']) || $data['IBLOCK_CODE'] != 'user_files')
		{
			return false;
		}
		if(!$isSection && empty($data['IBLOCK_SECTION_ID']))
		{
			return false;
		}

		$hashKey = empty($dataDetermineOwner)? false : md5(serialize($dataDetermineOwner));
		if($hashKey && isset($cacheDataDetermineOwner[$hashKey]))
		{
			list($userId, $sectionOwnerElement) = $cacheDataDetermineOwner[$hashKey];
		}
		else
		{
			if(!$isSection)
			{
				//todo may section not exist. Only iblock. Attention! If you multi-storage
				$section = CIBlockSection::GetList(array(), array(
						'IBLOCK_ID' => (int)$data['IBLOCK_ID'],
						'ID' => (int)$data['IBLOCK_SECTION_ID'],
					), false,
					array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID',
						'PATH', 'NAME', 'TIMESTAMP_X', 'XML_ID', 'IBLOCK_CODE',
						'DEPTH_LEVEL', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'CREATED_BY'
					))->fetch();

				if(empty($section) || !is_array($section))
				{
					return false;
				}
			}
			//todo $isSection? $data : $section - refactor!!!
			list($userId, $sectionOwnerElement) = static::findOwnerIdSection($isSection? $data : $section, true);
			if($hashKey)
			{
				$cacheDataDetermineOwner[$hashKey] = array($userId, $sectionOwnerElement);
			}
		}

		if(empty($sectionOwnerElement))
		{
			return false;
		}

		$data['isSection'] = (bool)$isSection;
		$data['ownerData'] = array($userId, $sectionOwnerElement);
		static::$dataDeletingMark[] = $data;

		return true;
	}

	public static function clearDeleteBatch()
	{
		static::$dataDeletingMark = array();
	}

	public static function markDeleteBatch()
	{
		if(empty(static::$dataDeletingMark))
		{
			return false;
		}

		$keeper = array();
		foreach (static::$dataDeletingMark as $key => $data)
		{
			if(empty($data['IBLOCK_CODE']) || $data['IBLOCK_CODE'] != 'user_files')
			{
				continue;
			}
			list($userId, $sectionOwnerElement) = $data['ownerData'];

			$component = new static();
			/** @var CWebDavAbstractStorage $storage  */
			$storage = $component
					->getStorageObject()
					->setStorageId(array(
						'IBLOCK_ID' => $sectionOwnerElement['IBLOCK_ID'],
						'IBLOCK_SECTION_ID' => $sectionOwnerElement['ID'],
			));
			$uniqueId = $storage->generateId(array('FILE' => !$data['isSection'], 'ID' => $data['ID']));
			$keeper[] = array(
				'IBLOCK_ID' => $sectionOwnerElement['IBLOCK_ID'],
				'SECTION_ID'=> $sectionOwnerElement['ID'],
				'ELEMENT_ID'=> $uniqueId,
				'IS_DIR'=> (int)$data['isSection'],
			);
			unset(static::$dataDeletingMark[$key]);
		}
		unset($data);

		CWebDavLogDeletedElement::addBatch($keeper);
	}
}