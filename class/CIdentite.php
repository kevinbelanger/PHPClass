<?php

require_once APPLICATION_PATH . DIRECTORY_SEPARATOR ."framework". DIRECTORY_SEPARATOR ."Application". DIRECTORY_SEPARATOR ."ApplicationAdapter.php";

class CIdentite extends ApplicationAdapter
{
	protected static $_lang;
	protected static $_config;
	protected static $_cryptography;
	protected static $_licenseInfo;

	public function __construct()
	{
		parent::__construct();
		self::$_config = array();
	}
	
	public static function getCryptography()
	{
		return self::$_cryptography;
	}

	public static function getLang()
	{
		return self::$_lang;
	}
	
	public static function getConfig()
	{
		return self::$_config;
	}
	
	public static function setConfig($arrConfiguration)
	{
		self::$_config = $arrConfiguration;
	}
	
	public static function getLicenseInfo()
	{
		return self::$_licenseInfo;
	}

	public function run()
	{
		/*******************************************************************************/
		/*************************** STUFF TO REFACTOR *********************************/
		/*******************************************************************************/
		require_once(APPLICATION_PATH."/inc/functions.inc.php");
		require_once(APPLICATION_PATH."/inc/sql.inc.php");

		require_once(APPLICATION_PATH."/class/Ldap.php");
		require_once(APPLICATION_PATH."/class/Language.php");
		require_once(APPLICATION_PATH."/class/Cryptography.php");
		require_once(APPLICATION_PATH."/class/Module/ModuleLoader.php");
		require_once(APPLICATION_PATH."/class/Module/ModuleRegistry.php");
		require_once(APPLICATION_PATH."/class/Task/TaskRegistry.php");
		require_once(APPLICATION_PATH."/class/Mail/MailTypeRegistry.php");
		require_once(APPLICATION_PATH."/class/Mail/EmailBuilder.php");
		require_once(APPLICATION_PATH."/class/Application/Updater/ApplicationUpdater.php");
		require_once(APPLICATION_PATH."/class/DbRegistry.php");
		require_once(APPLICATION_PATH."/class/userService.php");
		/*******************************************************************************/
		/*******************************************************************************/
		/*******************************************************************************/

		ModuleRegistry::load();
		TaskRegistry::load();
		MailTypeRegistry::load();
		DbRegistry::load();
		
		// Handle php error
		$this->handlePhpError();
		
		// Load Zend Framework
		$this->loadZendFramework();

		// Connection to the database
		$this->loadDatabase();
		
		// Initialize the session
		$this->initializeSession();

		// Load language file
		$this->loadLocalization();
		
		// Load all module
		$this->loadModules();
		
		// Load custom assets
		$this->loadCustomAssets();
		
		// Load liscence info
		$this->loadLicenseInfo();
		
		// Load cryptography
		$this->initializeCryptography();

		mb_internal_encoding(getConfigValue("core", "INTERNAL_CHARSET"));

		$this->executeRequest();
	}
	
	public function runCommandLine()
	{
		/*******************************************************************************/
		/*************************** STUFF TO REFACTOR *********************************/
		/*******************************************************************************/
		require_once(APPLICATION_PATH."/inc/functions.inc.php");
		require_once(APPLICATION_PATH."/inc/sql.inc.php");

		require_once(APPLICATION_PATH."/class/Ldap.php");
		require_once(APPLICATION_PATH."/class/Language.php");
		require_once(APPLICATION_PATH."/class/Cryptography.php");
		require_once(APPLICATION_PATH."/class/Module/ModuleLoader.php");
		require_once(APPLICATION_PATH."/class/Module/ModuleRegistry.php");
		require_once(APPLICATION_PATH."/class/Task/TaskRegistry.php");
		require_once(APPLICATION_PATH."/class/Mail/MailTypeRegistry.php");
		require_once(APPLICATION_PATH."/class/Mail/EmailBuilder.php");
		require_once(APPLICATION_PATH."/class/Application/Updater/ApplicationUpdater.php");
		require_once(APPLICATION_PATH."/class/DbRegistry.php");
		require_once(APPLICATION_PATH."/class/userService.php");
		/*******************************************************************************/
		/*******************************************************************************/
		/*******************************************************************************/

		ModuleRegistry::load();
		TaskRegistry::load();
		MailTypeRegistry::load();
		DbRegistry::load();
		
		// Load Zend Framework
		$this->loadZendFramework();

		// Connection to the database
		$this->loadDatabase();

		// Load language file
		$this->loadLocalization();
		
		// Load all module
		$this->loadModules();
		
		// Load custom assets
		$this->loadCustomAssets();
		
		// Load cryptography
		$this->initializeCryptography();

		mb_internal_encoding(getConfigValue("core", "INTERNAL_CHARSET"));
	}
	
	private function handlePhpError()
	{
		error_reporting(0);
		set_error_handler("exception_error_handler"); // Convert notice, warning in exception
		register_shutdown_function("shutdown"); // Do special thing at the end of php exection
	}

	private function executeRequest()
	{
		if(is_dir(APPLICATION_PATH . DIRECTORY_SEPARATOR . "modules". DIRECTORY_SEPARATOR . getRequest()->getModule()))
		{
			$currentModulePath = APPLICATION_PATH . DIRECTORY_SEPARATOR . "modules". DIRECTORY_SEPARATOR . getRequest()->getModule();
		}
		else
		{
			$currentModulePath = DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."custom". DIRECTORY_SEPARATOR ."modules". DIRECTORY_SEPARATOR . getRequest()->getModule();
		}

		$controller = self::$_request->getController();
		$action = self::$_request->getAction();

		$controllerClassName = ucfirst($controller) ."Controller";
		$controllerPath = $currentModulePath . DIRECTORY_SEPARATOR ."controller" . DIRECTORY_SEPARATOR . $controllerClassName .".php";

		if(file_exists($controllerPath))
		{
			require_once($controllerPath);
			$controllerClass = new $controllerClassName();
			$controllerClass->_view = new Smarty();
			$controllerClass->_view->setCompileDir(DATA_PATH . DIRECTORY_SEPARATOR ."templates_c");
			$controllerClass->_view->muteExpectedErrors();

			if(method_exists($controllerClass, "init"))
			{
				$controllerClass->init();
			}

			if(method_exists($controllerClass, $action))
			{
				$controllerClass->$action();
				
				if(getRequest()->isAjax())
				{
					$controllerClass->_view->display($currentModulePath . DIRECTORY_SEPARATOR ."view". DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action .".tpl");
				}
				else
				{
					$controllerClass->_view->assign("viewPath", $currentModulePath . DIRECTORY_SEPARATOR ."view". DIRECTORY_SEPARATOR . $controller . DIRECTORY_SEPARATOR . $action .".tpl");
					$controllerClass->_view->display(APPLICATION_PATH . DIRECTORY_SEPARATOR ."templates". DIRECTORY_SEPARATOR ."default". DIRECTORY_SEPARATOR ."global.tpl");
				}
			}
			else
			{
				throw new Exception("The action '". $action ."' doesn't exist into the controller '". $controller ."'!");
			}
		}
		else
		{
			throw new Exception("The controller file '". $controllerPath ."' doesn't exist!");
		}
	}

	private function initializeSession()
	{
		ini_set("session.save_path", DATA_PATH . DIRECTORY_SEPARATOR ."sessions");

		// Vérification de la présence d'une autre session.
		$a = session_id();
		if(empty($a))
		{
			session_start();
		}

		// S'il n'y a rien dans la session l'initialiser avec les valeurs par défaut.
		if(count($_SESSION) == 0)
		{
			setSession(null, true);
		}
		// Si la session a déjà initialisé avec les valeur par défaut et que personne n'est connecté.
		elseif(getSessionVar("user", "identifiantReseau") == "")
		{
			setSession(null);
		}
		// Si usager est connecté rafraichir les données en session.
		else
		{
			setSession(getSessionVar("user", "identifiantReseau"));
		}
	}
	
	private function initializeCryptography()
	{
		$arrOptions = array(
			"key" => getConfigValue("core", "CRYPTOGRAPHY_KEY"),
			"iv" => getConfigValue("core", "CRYPTOGRAPHY_IV")
		);
		self::$_cryptography = new Cryptography($arrOptions);
	}
	
	private function loadLocalization()
	{
		$xmlPath = DATA_PATH . DIRECTORY_SEPARATOR ."lang". DIRECTORY_SEPARATOR ."lang.xml";
		
		$sql = sql_getModules();
		$arrModules = db()->fetchArray($sql);

		generateLanguageFile($arrModules, $xmlPath);

		// Instanciation de l'objet pour les langues
		self::$_lang = new Language($xmlPath);
	}

	private function loadModules()
	{
		$sql = sql_getModules();
		$arrModules = db()->fetchArray($sql);
		
		$moduleLoader = new ModuleLoader();
		$moduleInstance = null;

		foreach($arrModules as $module)
		{
			$moduleInstance = $moduleLoader->load($module);
		}
	}
	
	private function loadCustomAssets()
	{
		$publicAssetsDir = PUBLIC_PATH . DIRECTORY_SEPARATOR ."assets". DIRECTORY_SEPARATOR . "custom";

		if(!is_dir($publicAssetsDir))
		{
			// Create the folder with the module name
			mkdir($publicAssetsDir);
		
			// Copy js file to the public assets folder
			recursiveCopy(DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."custom" . DIRECTORY_SEPARATOR ."assets" . DIRECTORY_SEPARATOR ."images", $publicAssetsDir . DIRECTORY_SEPARATOR . "images");
		}
	}
	
	private function loadDatabase()
	{
		$params = array(
			"host" => BD_HOST,
			"username" => BD_USER,
			"password" => BD_PASSWORD,
			"database" => BD_DATABASE,
			"charset" => BD_CHARSET
		);
		DbRegistry::add("primary", Db::factory(BD_TYPE, $params));
		DbRegistry::add("secondary", array());
	}
	
	private function loadZendFramework()
	{
		// Inclusion de Zend Framework
		set_include_path(implode(PATH_SEPARATOR, array(APPLICATION_PATH . DIRECTORY_SEPARATOR ."library". DIRECTORY_SEPARATOR . "Zend", get_include_path())));

		require_once 'Zend/Loader/Autoloader.php';

		$autoloader = Zend_Loader_Autoloader::getInstance();

		$autoloader->registerNamespace('Zend_');
	}
	
	private function loadLicenseInfo()
	{
		$sql = sql_getLicenceInfo();
		$licenseInfo = db()->fetchRow($sql);
		
		if(!$licenseInfo || $licenseInfo["dateModified"]+86400 < time())
		{
			try
			{
				$arrLatestVersionInfo = ApplicationUpdater::getLatestVersionInfo();
				$arrServicePlanRenewal = ApplicationUpdater::getServicePlanRenewalDate();

				$licenseInfo = array(
					"lastVersion" => $arrLatestVersionInfo["revision"],
					"servicePlanRenewalDate" => $arrServicePlanRenewal["servicePlanRenewalDate"],
				);

				$sql = sql_setLicenseInfo($licenseInfo);
				db()->query($sql);
				
				self::$_licenseInfo = $licenseInfo;
			}
			catch(Exception $e)
			{
				self::$_licenseInfo = null;
			}
			
			$sql = sql_getLicenceInfo();
			$licenseInfo = db()->fetchRow($sql);
		}
		else
		{
			self::$_licenseInfo = unserialize($licenseInfo["licenseInfo"]);
		}
	}
}

?>