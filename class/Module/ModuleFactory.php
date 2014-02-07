<?php

require_once(APPLICATION_PATH . DIRECTORY_SEPARATOR ."class". DIRECTORY_SEPARATOR ."IFactory.php");

class ModuleFactory implements IFactory
{
	private static $_instance = null;
	
	private function __construct()
	{
		
	}
	
	public static function getInstance()
    {
        if(is_null(self::$_instance))
		{
            self::$_instance = new ModuleFactory();
        }
        return self::$_instance;
    }
	
	public function make($options)
	{
		//return new Module($options);
	}
}

?>
