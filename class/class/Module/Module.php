<?php

require_once(APPLICATION_PATH . DIRECTORY_SEPARATOR ."class". DIRECTORY_SEPARATOR ."Module" . DIRECTORY_SEPARATOR . "IModule.php");

class Module implements IModule
{
	private $_name;
	private $_moduleRootPath;
	private $_sql;
	private $_moduleInfo;

	public function init()
	{
		$this->loadEventListener();
		$this->loadTask();
		$this->loadMailType();
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

	protected function loadTask()
	{
		if(is_dir($this->_moduleRootPath . DIRECTORY_SEPARATOR ."task"))
		{
			foreach(glob($this->_moduleRootPath . DIRECTORY_SEPARATOR ."task". DIRECTORY_SEPARATOR ."*.php") as $filename)
			{
				require_once($filename);
				$pathParts = pathinfo($filename);
				$taskClassname = $pathParts['filename'];
				
				// Get task related info from the database
				$sql = sql_getSchedulerTask(null, null, $taskClassname);
				$taskInfo = db()->fetchRow($sql);

				TaskRegistry::add($pathParts['filename'], new $taskClassname($taskInfo));
			}
		}
	}
	
	protected function loadMailType()
	{
		if(is_dir($this->_moduleRootPath . DIRECTORY_SEPARATOR ."mailType"))
		{
			foreach(glob($this->_moduleRootPath . DIRECTORY_SEPARATOR ."mailType". DIRECTORY_SEPARATOR ."*.php") as $filename)
			{
				require_once($filename);
				$pathParts = pathinfo($filename);
				$taskClassname = $pathParts['filename'];
				
				// Get task related info from the database
				$sql = sql_getMailType(null, $taskClassname);
				$mailTypeInfo = db()->fetchRow($sql);

				MailTypeRegistry::add($pathParts['filename'], new $taskClassname($mailTypeInfo));
			}
		}
	}
	
	public function getModuleInfo()
	{
		return $this->_moduleInfo;
	}
	
	public function setModuleInfo($value)
	{
		$this->_moduleInfo = $value;
	}

	public function getName()
	{
		return $this->_name;
	}

	public function getModuleRootPath()
	{
		return $this->_moduleRootPath;
	}
	
	public function getSql()
	{
		return $this->_sql;
	}

	protected function setModuleRootPath($value)
	{
		$this->_moduleRootPath = $value;
	}

	protected function setName($name)
	{
		$this->_name = $name;
	}
	
	protected function setSql($value)
	{
		$this->_sql = $value;
	}
}

?>