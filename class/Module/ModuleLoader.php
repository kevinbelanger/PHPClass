<?php

require_once(APPLICATION_PATH."/class/Module/Module.php");

class ModuleLoader
{
	private $_application;

	public function __construct()
	{
		$this->_application = Application::getAdapter();
	}
	
	public function load($module)
	{
		// Instanciate the module
		if($module["isSystem"])
		{
			require_once(APPLICATION_PATH . DIRECTORY_SEPARATOR ."modules". DIRECTORY_SEPARATOR . $module["nom"] . DIRECTORY_SEPARATOR ."Module.php");
		}
		else
		{
			require_once(DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."custom". DIRECTORY_SEPARATOR ."modules". DIRECTORY_SEPARATOR . $module["nom"] . DIRECTORY_SEPARATOR ."Module.php");
		}

		$partsModuleName = explode("_", $module["nom"]);
		
		$camelCaseModuleName = "";
		foreach($partsModuleName as $partModuleName)
		{
			$camelCaseModuleName .= ucfirst($partModuleName);
		}
		
		$moduleClassName = $camelCaseModuleName."Module";
		$theModule = new $moduleClassName();

		// Load the module configuration from the database
		$this->loadModuleConfig($theModule);
		
		// Initialize the module
		$theModule->init();
		
		// Load assets of the module
		$this->loadModuleAssets($module);
		
		// Add the module to the module registry
		ModuleRegistry::add($theModule->getName(), $theModule);
	}
	
	private function loadModuleConfig($theModule)
	{
		$moduleConfig = array();
		if(method_exists($theModule, "getModuleConfiguration"))
		{
			$moduleConfig = $theModule->getModuleConfiguration();
		}
		
		// Merge the module configuration with the master configuration
		$globalConfig = $this->_application->getConfig();
		$globalConfig[$theModule->getName()] = $moduleConfig;
		$this->_application->setConfig($globalConfig);
	}
	
	private function loadModuleAssets($module)
	{
		$blnRegenerateAssets = false;

		if(APPLICATON_ENV == 'dev')
		{
			$blnRegenerateAssets = true;
		}
		generateAssets($module, $blnRegenerateAssets);
	}
}

?>