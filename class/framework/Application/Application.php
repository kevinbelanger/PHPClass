<?php

class Application
{
	static private $_adapter = null;
	
	protected function __construct()
    {
        // Prevent initialization of the singleton
    }

    protected function __clone()
    {
        // Prevent cloning of the singleton
    }

	static function getAdapter()
	{
		if(is_null(self::$_adapter))
		{
			$applicationAdapterClass = APPLICATION_NAME;
			$applicationAdapterFile = APPLICATION_PATH . "/". $applicationAdapterClass .".php";

			if(is_file($applicationAdapterFile))
			{
				require_once $applicationAdapterFile;

				$applicationAdapter = new $applicationAdapterClass();
				self::setAdapter($applicationAdapter);
			}
			else
			{
				throw new Exception("The file '". $applicationAdapterFile ."' do no exist!");
			}
		}
		
		return self::$_adapter;
	}

    static function setAdapter($value)
	{
		self::$_adapter = $value;
    }
}

?>