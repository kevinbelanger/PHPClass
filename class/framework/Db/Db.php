<?php

class Db
{
	public static function factory($adapterName, $config = array())
	{
		$dbAdapter = null;
		
		if(!is_string($adapterName) || empty($adapterName))
		{
			throw new Exception("The adapter name must be provided in a string!");
		}
		
		if(!is_array($config))
		{
			throw new Exception("The adapter parameters must be provided in an array!");
		}
		
		$adapterClassName = "Db_Adapter_". $adapterName;

		if(class_exists($adapterClassName))
		{
			$dbAdapter = new $adapterClassName($config);
		}
		else
		{
			throw new Exception("The adapter '". $adapterName ."' doesn't exist!");
		}
		
		return $dbAdapter;
	}
}

?>