<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

class CWebDavTmpFile
{
	const TABLE_NAME = 'b_webdav_storage_tmp_file';

	public $id;
	public $name;
	public $path;
	public $version;

	public static function getList(array $order = array(), array $filter = array())
	{
		$t = static::TABLE_NAME;
		$columns = array(
			'NAME' => true,
			'PATH' => true,
			'ID' => true,
			'VERSION' => true,
		);
		$order = array_intersect_key($order, $columns);
		$where = array_intersect_key($filter, $columns);
		$sqlWhere = array();
		foreach ($where as $field => $value)
		{
			switch($field)
			{
				case 'ID':
					$value = (int)$value;
					$sqlWhere[] = $field . '=' . $value;
					break;
				case 'NAME':
					$value = static::getDb()->forSql($value);
					$sqlWhere[] = $field . '=' . '\'' . $value . '\'';
					break;
				case 'PATH':
					$value = static::getDb()->forSql($value);
					$sqlWhere[] = $field . '=' . '\'' . $value . '\'';
					break;
				case 'VERSION':
					//todo version is long int
					$value = (int)$value;
					$sqlWhere[] = $field . '>=' . $value;
					break;
			}
		}
		unset($value);

		if($sqlWhere)
		{
			$sqlWhere = ' WHERE ' . implode(' AND ', $sqlWhere);
		}
		else
		{
			$sqlWhere = '';
		}

		$sqlOrder = '';
		if($order)
		{
			$sqlOrder = array();
			foreach ($order as $by => $ord)
			{
				$by = strtoupper($by);
				$sqlOrder[] = $by . ' ' . (strtoupper($ord) == 'DESC' ? 'DESC' : 'ASC');
			}
			unset($by);
			$sqlOrder = ' ORDER BY ' . implode(', ', $sqlOrder);
		}

		return static::getDb()->query("SELECT * FROM {$t} {$sqlWhere} {$sqlOrder}");
	}

	protected function deleteRow()
	{
		$t = static::TABLE_NAME;
		$this->id = (int)$this->id;

		return $this->getDb()->query("DELETE FROM {$t} WHERE id = {$this->id}");
	}

	protected function deleteTmpFile()
	{
		if($this->existsFile())
		{
			unlink($this->getAbsolutePath());
		}
	}

	protected function existsFile()
	{
		return file_exists($this->getAbsolutePath()) && is_file($this->getAbsolutePath());
	}

	public function delete()
	{
		if($this->deleteRow())
		{
			$this->deleteTmpFile();
			return true;
		}
		return false;
	}

	public function getAbsolutePath()
	{
		return CTempFile::GetAbsoluteRoot() . '/' . $this->path;
	}

	public static function getOne($name)
	{
		$query = static::getList(array(), array('NAME' => $name));

		return !empty($query)? $query->fetch() : false;
	}

	/**
	 * @param $name
	 * @return bool|CWebDavTmpFile
	 */
	public static function buildByName($name)
	{
		/** @var CWebDavTmpFile $model  */
		$model = new static();
		$row = static::getOne($name);
		if(empty($row) || (is_array($row) && !array_filter($row)))
		{
			return false;
		}
		//todo may path convert to 32 symbols hash (md5).
		$model->id = $row['ID'];
		$model->name = $row['NAME'];
		$model->path = $row['PATH'];
		$model->version = $row['VERSION'];

		if(!$model->existsFile())
		{
			$model->deleteRow();

			return false;
		}

		return $model;
	}

	protected static function generatePath()
	{
		$tmpName = md5(mt_rand() . mt_rand());
		$dir = rtrim(CTempFile::GetDirectoryName(2), '/') . '/';
		CheckDirPath($dir); //make folder recursive
		$pathItems = explode(CTempFile::GetAbsoluteRoot(), $dir . $tmpName);

		return array(array_pop($pathItems), $tmpName);
	}

	public static function buildFromDownloaded(array $downloadedFile)
	{
		/** @var CWebDavTmpFile $model  */
		$model = new static();
		$model->version = time();
		list($model->path, $model->name) = static::generatePath();

		if (($downloadedFile['error'] = intval($downloadedFile['error'])) > 0)
		{
			if ($downloadedFile['error'] < 3)
			{
				throw new WebDavTmpFileErrorException('UPLOAD_MAX_FILESIZE: ' . intval(ini_get('upload_max_filesize')));
			}
			else
			{
				throw new WebDavTmpFileErrorException('UPLOAD_ERROR ' . $downloadedFile['error']);
			}
		}
		else
		{
			//chech permission? success download
		}
		if(!is_uploaded_file($downloadedFile['tmp_name']))
		{
			throw new WebDavTmpFileErrorException('UPLOAD_ERROR');
		}

		if(!move_uploaded_file($downloadedFile['tmp_name'], $model->getAbsolutePath()))
		{
			throw new WebDavTmpFileErrorException('Error in move');
		}

		return $model;
	}

	public function save()
	{
		$t = static::TABLE_NAME;
		list($cols, $vals) = static::getDb()->prepareInsert($t, array(
			'NAME' => $this->name,
			'PATH' => $this->path,
			'VERSION' => (int)$this->version,
		));

		return $this->getDb()->query("INSERT INTO {$t} ({$cols}) VALUES({$vals})");
	}

	/**
	 * @return CDatabase
	 */
	protected static function getDb()
	{
		global $DB;

		return $DB;
	}
}
class WebDavTmpFileErrorException extends Exception
{}
