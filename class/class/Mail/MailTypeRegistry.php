<?php

class MailTypeRegistry implements IRegistry
{
	private static $_me;
	private static $_registry;
	private static $_registryType = 1;
	
	/**
	 * 
	 * @param int $registryType
	 * 
	 */
	private function __construct($registryType)
	{
		if($registryType === self::REGISTRY_TYPE_ARRAY)
		{
			self::$_registry = array();
			self::$_registryType = self::REGISTRY_TYPE_ARRAY;
		}
		else
		{
			throw new Exception("The registry type '". $registryType ."' doesn't exists.");
		}
	}
	
	/**
	 * 
	 * @param int $registryType
	 * @return TaskRegistry
	 * 
	 */
	public static function load()
    {
        if(is_null(self::$_me))
		{
            self::$_me = new MailTypeRegistry(IRegistry::REGISTRY_TYPE_ARRAY);
        }
        return self::$_me;
    }
	
	public static function add($itemId, $item)
	{
		self::$_registry[$itemId] = $item;
	}
	
	public static function get($itemId)
	{
		if(isset(self::$_registry[$itemId]))
		{
			return self::$_registry[$itemId];
		}
		else
		{
			throw new Exception("The item '". $itemId ."' doesn't exist into the MailTypeRegistry");
		}
	}
	
	public static function getAll()
	{
		return self::$_registry;
	}
	
	public static function remove($itemId)
	{
		unset(self::$_registry[$itemId]);
	}
	
	public static function removeAll()
	{
		unset(self::$_registry);
		self::$_registry = array();
	}
}

?>