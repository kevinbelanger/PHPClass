<?php

class Zip
{
	private $_archive = null;
	
	const OPENING_MODE_ARCHIVE_CREATE = ZipArchive::CREATE;
	const OPENING_MODE_ARCHIVE_OVERWRITE = ZipArchive::OVERWRITE;

	public function __construct($options = array())
	{
		$this->_archive = new ZipArchive();
	}
	
	public function open($archivePath, $mode = ZipArchive::CREATE)
	{
		return $this->_archive->open($archivePath, $mode);
	}
	
	public function close()
	{
		return $this->_archive->close();
	}
	
	public function getError()
	{
		return $this->_archive->getStatusString();
	}			
	
	public function addDir($source)
	{
		if(is_dir($source) === true)
		{
			$files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

			foreach($files as $file)
			{
				if(is_file($file) === true)
				{
					$filename = str_replace($source . DIRECTORY_SEPARATOR, '', $file);
					$this->_archive->addFromString($filename, file_get_contents($file));
				}
			}
		}
	}
	
	public function addFile($path, $name)
	{
		$return = false;

		if(is_file($path))
		{
			$return = $this->_archive->addFile($path, $name);
		}
		
		return $return;
	}
	
	public function addEmptyDir($name)
	{
		$this->_archive->addEmptyDir($name);
	}
	
	public function uncompress($archivePath, $extractToPath)
	{
		$success = false;
		if($this->_archive->open($archivePath))
		{
			$this->_archive->extractTo($extractToPath);
			$this->_archive->close();
			$success = true;
		}
		
		return $success;
	}
}

?>