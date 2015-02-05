<?php

class Acl
{
	const DENY = 0;
	const ALLOW = 1;

	private $_acl = array();

	public function __construct($options = array())
	{

	}

	/**
	 * 
	 * @param array $aclArray
	 * 
	 */
	public function loadAclFromArray($aclArray)
	{
		foreach($aclArray as $aclRow)
		{
			if(!array_key_exists($aclRow["roleName"], $this->_acl))
			{
				$this->addRole($aclRow["roleName"]);
			}
			
			$this->addRessource($aclRow["roleName"], $aclRow["ressourceName"], $aclRow["permission"]);
		}
	}
	
	public function addRole($roleName)
	{
		$this->_acl[$roleName] = array();
		
		return true;
	}
	
	/**
	 * Add ressource to a role
	 * 
	 * 
	 * @param type $roleName
	 * @param type $ressourceName
	 * @param type $permission
	 * 
	 * test
	 */
	public function addRessource($roleName, $ressourceName, $permission)
	{
		$this->_acl[$roleName][$ressourceName] = $permission;
	}
	
	/**
	 * Determine if the ressource is allowed in a determined role
	 * 
	 * @param string $role
	 * @param array $ressource
	 * 
	 * @return bool $isAllowed
	 */
	public function isAllowed($role, $ressource)
	{
		$isAllowed = $this->_acl[$role][$ressource];

		return $isAllowed;
	}
}

?>