<?php

class Task
{
	private $_taskUid;
	private $_taskIdModule;
	private $_taskKey;
	private $_taskDescription;

	public function __construct($taskInfo = array())
	{
		if(isset($taskInfo["uid"]))
		{
			$this->setTaskUid($taskInfo["uid"]);
		}
		
		if(isset($taskInfo["idModule"]))
		{
			$this->setTaskIdModule($taskInfo["idModule"]);
		}
		
		if(isset($taskInfo["taskKey"]))
		{
			$this->setTaskKey($taskInfo["taskKey"]);
		}
		
		if(isset($taskInfo["description"]))
		{
			$this->setTaskDescription($taskInfo["description"]);
		}
	}
	
	public function getTaskUid()
	{
		return $this->_taskUid;
	}
	
	public function setTaskUid($value)
	{
		$this->_taskUid = $value;
	}
	
	public function getTaskIdModule()
	{
		return $this->_taskIdModule;
	}
	
	public function setTaskIdModule($value)
	{
		$this->_taskIdModule = $value;
	}
	
	public function getTaskKey()
	{
		return $this->_taskKey;
	}
	
	public function setTaskKey($value)
	{
		$this->_taskKey = $value;
	}
	
	public function getTaskDescription()
	{
		return $this->_taskDescription;
	}
	
	public function setTaskDescription($value)
	{
		$this->_taskDescription = $value;
	}

	public function writeSchedulerLog($idTaskQueue = 0, $logText = "")
	{
		// On écrit un log dans la BD
		$arrSchedulerLog = array(
			"idTaskQueue" => $idTaskQueue,
			"result" => $logText
		);

		$sql = sql_insertSchedulerLog($arrSchedulerLog);
		db()->query($sql);
	}
}

?>