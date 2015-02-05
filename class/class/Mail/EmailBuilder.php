<?php

class EmailBuilder
{
	private $_template = "";
	private $_markers = array();

	public function __construct($options = array())
	{
		if(isset($options["template"]))
		{
			$this->__set("template", $options["template"]);
		}
		
		if(isset($options["markers"]))
		{
			$this->__set("markers", $options["markers"]);
		}
	}
	
	public function __set($option, $value)
	{
		$option = "_". $option;
		$this->$option = $value;
	}
	
	public function __get($option)
	{
		$option = "_". $option;
		return $this->$option;
	}
	
	public function prepareEmail()
	{
		$email = $this->replaceTemplateMarker();
		$email = wordwrap($email, 70);
		
		return $email;
	}
	
	private function replaceTemplateMarker()
	{
		$email = @str_replace(array_keys($this->__get("markers")), array_values($this->__get("markers")), $this->__get("template"));
		
		while(true)
		{
			$emailBefore = $email;

			$email = @str_replace(array_keys($this->__get("markers")), array_values($this->__get("markers")), $email);
			if($emailBefore == $email)
			{
				break;
			}
		}

		return $email;
	}
}

?>
