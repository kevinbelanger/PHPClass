<?php

require_once(APPLICATION_PATH . DIRECTORY_SEPARATOR ."class". DIRECTORY_SEPARATOR ."Mail". DIRECTORY_SEPARATOR . "IMailType.php");

class MailType implements IMailType
{
	private $_mailTypeUid;
	private $_mailTypeKey;
	private $_mailTypeTemplate;

	public function __construct($mailTypeInfo = array())
	{
		if(isset($mailTypeInfo["uid"]))
		{
			$this->setMailTypeUid($mailTypeInfo["uid"]);
		}
		
		if(isset($mailTypeInfo["mailTypeKey"]))
		{
			$this->setMailTypeKey($mailTypeInfo["mailTypeKey"]);
		}
		
		if(isset($mailTypeInfo["mailTypeTemplate"]))
		{
			$this->setMailTypeTemplate($mailTypeInfo["mailTypeTemplate"]);
		}
	}
	
	public function getMailTypeUid()
	{
		return $this->_mailTypeUid;
	}
	
	public function setMailTypeUid($value)
	{
		$this->_mailTypeUid = $value;
	}
	
	public function getMailTypeKey()
	{
		return $this->_mailTypeKey;
	}
	
	public function setMailTypeKey($value)
	{
		$this->_mailTypeKey = $value;
	}
	
	public function getMailTypeTemplate()
	{
		return $this->_mailTypeTemplate;
	}
	
	public function setMailTypeTemplate($value)
	{
		$this->_mailTypeTemplate = $value;
	}
	
	public function getMarkers()
	{
		$markers["[###CONTENT###]"] = $this->getMailTypeTemplate();

		return $markers;
	}
}

?>
