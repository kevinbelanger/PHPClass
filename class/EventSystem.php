<?php

class EventSystem
{
	public static $_events = array();

	public function register($event, Closure $func)
	{
		self::$_events[$event][] = $func;
	}
	
	public function fire($event, $args = array())
	{
		if(isset(self::$_events[$event]))
        {
            foreach(self::$_events[$event] as $func)
            {
				call_user_func_array($func, $args);
            }
        }
	}
}

?>