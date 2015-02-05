<?php

class Form_Element
{
	protected $_name;
	protected $_value;
	protected $_label;
	protected $_attributes;
	protected $_decorator;
	
	public function __construct($options = array())
	{
		if(is_array($options))
		{
			$this->setOptions($options);
		}
	}
	
	public function setOptions($options)
    {
        foreach ($options as $key => $value)
		{
            $method = 'set' . ucfirst($key);

            if(method_exists($this, $method))
			{
                $this->$method($value);
            }
			else
			{
                // Assume it's element attributes
                $this->setAttrib($key, $value);
            }
        }
        return $this;
    }
	
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
	
	public function setName($name)
	{
		$this->_name = $name;
	}
	
	public function setLabel($label)
	{
		$this->_label = $label;
	}
	
	public function setValue($value)
	{
		$this->_value = $value;
	}
	
	public function setDecorator($decorator)
	{
		$this->_decorator = $decorator;
	}
}

?>