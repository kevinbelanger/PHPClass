<?php

abstract class ApplicationUpdaterScript
{
	public $fromRevision = null;	
	public $toRevision = null;
	
	function getActions()
	{
    	return null;
    }
	
	function startUpdate()
	{
		// TODO: Backup of the database
	}
	
	function endUpdate()
	{
		// TODO: Finalize the installation
	}
}

?>