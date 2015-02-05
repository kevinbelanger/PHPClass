<?php

abstract class Db_Adapter_Abstract
{
	protected $_config;

	public function __construct($config)
	{
		$this->_config = $config;
	}

	abstract protected function connect();
	abstract public function closeConnection();
	abstract public function query($sql);
	abstract public function insert_id();
	abstract public function numRows($result);
	abstract public function quote_smart($value);
	abstract public function beginTransaction();
	abstract public function commitTransaction();
	abstract public function rollbackTransaction();
	abstract public function fetchArray($sql);
	abstract public function fetchRow($sql);
}

?>