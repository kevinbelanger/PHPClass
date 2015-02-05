<?php

interface IModule
{
	public function init();
	public function getModuleConfiguration();
	public function getName();
}

?>