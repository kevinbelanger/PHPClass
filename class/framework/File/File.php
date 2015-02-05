<?php

class File
{
	private $_fileHandle = null;
	private $_openingMode = "r";
	private $_fileCompletePath = "";
	private $_fileDirName = "";
	private $_fileName = "";
	private $_fileExtension = "";
	
	public function __construct($fileCompletePath, $options = array())
	{
		if($fileCompletePath != "")
		{
			$this->_fileCompletePath = $fileCompletePath;
			$arrPathInfo = $this->getPathInfo();
			
			$this->_fileName = $arrPathInfo["filename"];
			$this->_fileExtension = $arrPathInfo["extension"];
			$this->_fileDirName = $arrPathInfo["dirname"];
		}
		else
		{
			throw new Exception("The provided file path could not be empty");
		}
		
		if(isset($options["openingMode"]))
		{
			$this->_openingMode = $options["openingMode"];
		}
		
		$this->open();
	}
	
	public function open()
	{
		$this->_fileHandle = @fopen($this->_fileCompletePath, $this->_openingMode);
	}
	
	public function write($string)
	{
		return @fwrite($this->_fileHandle, $string);
	}
	
	public function close()
	{
		return @fclose($this->_fileHandle);
	}
	
	private function getPathInfo()
	{
		return @pathinfo($this->_fileCompletePath);
	}
	
	public static function rglob($pattern = "*", $path = "", $flags = 0)
	{
		$paths = glob($path.'*', GLOB_MARK|GLOB_ONLYDIR|GLOB_NOSORT);
		$files = glob($path.$pattern, $flags);
		foreach($paths as $path)
		{
		  $files = array_merge($files, self::rglob($pattern, $path, $flags));
		}
		return $files;
	}
	
	public static  function delTree($dir)
	{
		$files = array_diff(scandir($dir), array('.', '..'));

		foreach($files as $file)
		{
		  (is_dir("$dir/$file")) ? self::delTree("$dir/$file") : unlink("$dir/$file");
		}
		return rmdir($dir);
	}
}

?>
