<?php

interface IFactory
{
	public function make($options);
	public static function getInstance();
}

?>