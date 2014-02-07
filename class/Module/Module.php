<?php

require_once(APPLICATION_PATH . DIRECTORY_SEPARATOR ."class". DIRECTORY_SEPARATOR ."Module" . DIRECTORY_SEPARATOR . "IModule.php");

class Module implements IModule
{
	private $_name;
	private $_moduleRootPath;

	public function init()
	{
		$this->loadEventListener();
	}
	
	public function getModuleConfiguration()
	{
		return retournerConfigurationModule($this->_name);
	}
	
	protected function loadEventListener()
	{
		if(file_exists($this->_moduleRootPath . DIRECTORY_SEPARATOR ."events.php"))
		{
			require_once($this->_moduleRootPath . DIRECTORY_SEPARATOR ."events.php");
		
			foreach($events as $eventName => $eventFunction)
			{
				event()->register($eventName, $eventFunction);
			}
		}
	}

	public function getName()
	{
		return $this->_name;
	}
	
	public function getModuleRootPath()
	{
		return $this->_moduleRootPath;
	}
	
	protected function setModuleRootPath($value)
	{
		$this->_moduleRootPath = $value;
	}
	
	protected function setName($name)
	{
		$this->_name = $name;
	}
}

?>