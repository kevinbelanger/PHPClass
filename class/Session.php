<?php

class Session
{
	private static $_instance;
	private static $_sessionStarted = false;
	private static $_defaultOptions = array(
		"save_path" => null
	);

	private function __construct()
	{
		
	}
	
	private static function setOptions($options = array())
	{
		// set the options the user has requested to set
        foreach($options as $optionName => $optionValue)
		{
			$optionName = strtolower($optionName);

            // set the ini based values
            if(array_key_exists($optionName, self::$_defaultOptions))
			{
                ini_set("session.".$optionName, $optionValue);
            }
		}
	}
	
	public static function getInstance()
	{
		if(is_null(self::$_instance))
		{
            self::$_instance = new Session();
        }
        return self::$_instance;
	}
	
	public static function start($options = null)
	{
		if(self::$_sessionStarted)
		{
            return true;
        }

		self::setOptions(is_array($options) ? $options : array());
		
		self::$_sessionStarted = session_start();
		
		if(!self::$_sessionStarted)
		{
			session_write_close();
			throw new Exception("An error occurs during the startup");
		}
	}
	
	public static function createNamespace($namespace)
	{
		if(!isset($_SESSION[$namespace]))
		{
			$_SESSION[$namespace] = array();
		}
		else
		{
			throw new Exception("The namespace '". $namespace ."' already exist.");
		}
	}
	
	public static function deleteNamespace($namespace)
	{
		if(isset($_SESSION[$namespace]))
		{
			unset($_SESSION[$namespace]);
		}
		else
		{
			throw new Exception("The namespace '". $namespace ."' do not exist.");
		}
	}
	
	public static function issetNamespace($namespace)
	{
		if(isset($_SESSION[$namespace]))
		{
			return true;
		}
		
		return false;
	}
	
	public function getValue($namespace, $preference)
	{
		if(self::issetNamespace($namespace))
		{
			if(isset($_SESSION[$namespace][$preference]))
			{
				return $_SESSION[$namespace][$preference];
			}
			else
			{
				throw new Exception("The preference '". $preference ."' do not exist in the namespace '". $namespace ."'");
			}
		}
		else
		{
			throw new Exception("The namespace '". $namespace ."' do not exist.");
		}
	}
	
	public function setValue($namespace, $preference, $value)
	{
		if(self::issetNamespace($namespace))
		{
			$_SESSION[$namespace][$preference] = $value;
			return true;
		}
		else
		{
			throw new Exception("The namespace '". $namespace ."' do not exist.");
		}
	}
}

?>