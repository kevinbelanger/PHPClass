<?php

class Form
{
	private $_attributes = array();
	private $_elements = array();
	
	public function setAttrib($attribute, $value)
	{
		$this->_attributes[$attribute] = $value;
	}
	
	public function setAttribs($attributes)
	{
		foreach($attributes as $attributeName => $value)
		{
			$this->setAttrib($attributeName, $value);
		}
	}
	
	public function addElement($element)
	{
		$this->_elements[] = $element;
	}
	
	public function addElements($elements)
	{
		foreach($elements as $element)
		{
			$this->addElement($element);
		}
	}
}

?>