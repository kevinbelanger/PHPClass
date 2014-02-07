<?php

class Application
{
	static private $_adapter;
	
	static function loadAdapter()
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
	
	static function &getAdapter()
	{
		return self::$_adapter;
    }

    static function setAdapter($value)
	{
		self::$_adapter = $value;
    }
}

?>