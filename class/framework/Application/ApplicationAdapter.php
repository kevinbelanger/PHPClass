<?php

abstract class ApplicationAdapter
{
	protected static $_request;
	protected static $_eventSystem;
	
	public function __construct()
	{
		// Load the framework
		$this->loadFramework();
		
		// Load render engine
		$this->loadRenderEngine();
		
		// Load Event system
		$this->loadEventSystem();
		
		// Handle the request
		$this->handleRequest();
	}
	
	public static function getRequest()
	{
		return self::$_request;
	}
	
	public static function getEventSystem()
	{
		return self::$_eventSystem;
	}
	
	private function handleRequest()
	{
		self::$_request = new Request();
	}
	
	private function loadEventSystem()
	{
		self::$_eventSystem = new EventSystem();
	}
	
	private function loadFramework()
	{
		$path = APPLICATION_PATH . DIRECTORY_SEPARATOR ."framework";
		$objects = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path), RecursiveIteratorIterator::SELF_FIRST);

		foreach($objects as $name => $object)
		{
			if(!is_dir($name))
			{
				require_once($name);
			}
		}
	}
	
	public function loadRenderEngine()
	{
		require_once(APPLICATION_PATH."/library/smarty/SmartyBC.class.php");
	}
}

?>