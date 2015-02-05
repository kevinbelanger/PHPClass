<?php

class Log
{	
	private $_objFile = null;
	private $_sectionDelimiter = "-";
	private $_sectionDelimiterMultiplier = 72;
	
	public function __construct($logCompletePath, $options = array())
	{
		$options = array(
			"openingMode" => "a"
		);
		$this->_objFile = new File($logCompletePath, $options);
		
		if(isset($options["sectionDelimiter"]))
		{
			$this->_sectionDelimiter = $options["sectionDelimiter"];
		}
		
		if(isset($options["sectionDelimiterMultiplier"]))
		{
			$this->_sectionDelimiterMultiplier = $options["sectionDelimiterMultiplier"];
		}
	}
	
	public function __destruct()
	{
		$this->_objFile->close();
	}
	
	public function log($string)
	{
		$logLine = date("Y-m-d H:i:s") ." - ". $string . PHP_EOL;
		$this->_objFile->write($logLine);
	}
	
	public function startSection()
	{
		$logLine = PHP_EOL . PHP_EOL . str_repeat($this->_sectionDelimiter, $this->_sectionDelimiterMultiplier) . PHP_EOL;
		$this->_objFile->write($logLine);
	}
	
	public function endSection()
	{
		$logLine = str_repeat($this->_sectionDelimiter, $this->_sectionDelimiterMultiplier);
		$this->_objFile->write($logLine);
	}
}

?>
