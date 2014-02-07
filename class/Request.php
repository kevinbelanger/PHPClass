<?php

class Request
{
	private $_get;
	private $_post;
	private $_currentModule;
	private $_currentController;
	private $_currentAction;
	private $_private;
	private $_mode;
	private $_fancypage;

	public function __construct()
	{
		$this->_get = $_GET;
		$this->_post = $_POST;
		
		$this->_currentModule = "core";
		if(isset($this->_get["module"]))
		{
			$this->_currentModule = $this->_get["module"];
		}
		
		$this->_currentController = "index";
		if(isset($this->_get["controller"]))
		{
			$this->_currentController = $this->_get["controller"];
		}
		
		$this->_currentAction = "index";
		if(isset($this->_get["contenu"]))
		{
			$this->_currentAction = $this->_get["contenu"];
		}
		
		$this->_private = 0;
		if(isset($this->_get["private"]))
		{
			$this->_private = $this->_get["private"];
		}
		
		$this->_mode = "index";
		if(isset($this->_get["mode"]))
		{
			$this->_mode = $this->_get["mode"];
		}
		
		$this->_fancypage = 0;
		if(isset($this->_get["fancypage"]))
		{
			$this->_fancypage = $this->_get["fancypage"];
		}
	}
	
	public function getModule()
	{
		return $this->_currentModule;
	}
	
	public function getController()
	{
		return $this->_currentController;
	}
	
	public function getAction()
	{
		return $this->_currentAction;
	}
	
	public function getParam($paramName)
	{
		if(isset($this->_get[$paramName]))
		{
			return $this->_get[$paramName];
		}
		elseif(isset($this->_post[$paramName]))
		{
			return $this->_post[$paramName];
		}
		else
		{
			return "";
		}
	}
	
	public function getParams()
	{
		return array_merge($this->_get, $this->_post);
	}
	
	public function getPostParam($paramName)
	{
		if(isset($this->_post[$paramName]))
		{
			return $this->_post[$paramName];
		}
		else
		{
			return "";
		}
	}
	
	public function getPostParams()
	{
		return $this->_post;
	}
	
	public function getGetParam($paramName)
	{
		if(isset($this->_get[$paramName]))
		{
			return $this->_get[$paramName];
		}
		else
		{
			return "";
		}
	}
	
	public function getGetParams()
	{
		return $this->_get;
	}
}

?>