<?php

class Ldap
{
	private $_ldap = null;
	private $_rootDse = null;
	private $_debugMode = false;
	private $_debugFilePath = "";
	private $_arrResponse = array();

	/**
	 * Construct objet
	 */
	public function __construct()
	{
		
	}

	/**
	 * Destruct object of the class
	 */
	public function __destruct()
	{
		if($this->_ldap)
		{
			$this->_ldap->disconnect();
		}

		unset($this->_ldap);
		unset($this->_rootDse);
	}

	/**
	 * Initialize LDAP server.
	 * 
	 * @param array $initializeInformation
	 * @param string $callFrom
	 */
	private function initialize($initializeInformation)
	{	
		$this->setDebugMode(($initializeInformation["general"]["debug"] == 1) ? true : false);
		$this->setDebugFilePath($initializeInformation["general"]["debugFilePath"]);
		
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);

		$blnInitialize = false;

		// Récupération des paramètres serveur
		$options = array(
			"host" => $initializeInformation["server"]["host"],
			"port" => $initializeInformation["server"]["port"],
			"baseDn" => $initializeInformation["server"]["baseDn"],
			"username" => $initializeInformation["server"]["username"],
			"password" => $initializeInformation["server"]["password"],
			"accountDomainName" => $initializeInformation["server"]["accountDomainName"]
		);

		// Création de l'objet Zend_Ldap
		try
		{
			$this->_ldap = new Zend_Ldap($options);
			$this->_rootDse = $this->_ldap->getRootDse();

			$this->_ldap->bind();

			$blnInitialize = true;
		}
		catch(Exception $exception)
		{
			$this->log("An error occured during the initialisation with the LDAP server. Please check your configuration.");
		}

		$this->log("End ". __METHOD__);

		return $blnInitialize;
	}

	/**
	 * Find user into LDAP server.
	 * 
	 * @param string $searchField
	 * @param string $username
	 */
	private function search($searchField, $searchValue)
	{
		$filter = Zend_Ldap_Filter::equals($searchField, $searchValue);
		$searchResults = $this->_ldap->search($filter);
		$arrSearchResults = $searchResults->toArray();
		return $arrSearchResults;
	}

	/**
	 * Find user into LDAP server.
	 * 
	 * @param string $dn
	 * 
	 * @return bool
	 */
	private function exists($dn)
	{
		return $this->_ldap->exists($dn);
	}
	
	/**
	 * Update LDAP object.
	 * 
	 * @param string $dn
	 * @param array $entry
	 * 
	 */
	private function update($dn, $entry)
	{
		try
		{
			$bool = $this->_ldap->update($dn, $entry);
		}
		catch(Exception $e)
		{
			$bool = false;
		}

		return $bool;
	}
	
	/**
	 * 
	 * @param string $message
	 * 
	 */
	private function log($message)
	{
		if($this->getDebugMode())
		{
			$fp = fopen($this->getDebugFilePath() . DIRECTORY_SEPARATOR . "ldap.txt", "a");
			fwrite($fp, "[". date("Y-m-d H:i:s") ."] - ". $message ."\r\n");
			fclose($fp);
		}
	}
	
	/**
	 * 
	 * @param string $key
	 * @param mixed $value
	 * 
	 * @return void
	 * 
	 */
	private function setResponseValue($key, $value)
	{
		$this->_arrResponse[$key] = $value;
	}
	
	/**
	 * 
	 * @return array
	 * 
	 */
	private function getResponseValue()
	{
		return (array)$this->_arrResponse;
	}
	
	/**
	 * 
	 * @param string $debugFilePath
	 * 
	 * @return void
	 * 
	 */
	private function setDebugFilePath($debugFilePath)
	{
		$this->_debugFilePath = $debugFilePath;
	}
	
	/**
	 * 
	 * @return string
	 * 
	 */
	private function getDebugFilePath()
	{
		return $this->_debugFilePath;
	}
	
	/**
	 * 
	 * @param bool $debugMode
	 * 
	 * @return void
	 * 
	 */
	private function setDebugMode($debugMode)
	{
		$this->_debugMode = $debugMode;
	}
	
	/**
	 * 
	 * @return bool
	 * 
	 */
	private function getDebugMode()
	{
		return $this->_debugMode;
	}
	
	/**
	 * Connect user to LDAP.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param string $password
	 *
	 * @return array
	 */
	public function login($initializeInformation, $searchField, $username, $password)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);

		$blnLogin = false;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$arrSearchResults = $this->search($searchField, $username);
			if(count($arrSearchResults) > 0)
			{
				foreach($arrSearchResults as $searchResult)
				{
					try
					{
						$this->_ldap->bind($searchResult["dn"], $password);
						$blnLogin = true;
					}
					catch(Exception $e)
					{
						$this->log("The username/password provided did not match.");
					}
				}
			}
		}

		$this->setResponseValue("functionReturn", $blnLogin);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}
	
	/**
	 * Check if the attribute exist into the current LDAP schema
	 * 
	 * @param array $initializeInformation
	 * @param string $attributeName
	 * 
	 * @return array
	 */
	public function attributeExists($initializeInformation, $attributeName)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$blnExists = $this->_rootDse->existsAttribute($attributeName);
		
		$this->setResponseValue("functionReturn", $blnExists);
		$this->log("End ". __METHOD__);
		
		return $this->getResponseValue();
	}
	
	/**
	 * Return object entry
	 * 
	 * @param array $initializeInformation
	 * @param string $dn
	 * 
	 * @return array
	 */
	public function getObject($initializeInformation, $dn)
	{
		$this->log("Start ". __METHOD__);

		$this->setResponseValue("functionName", __METHOD__);

		$arrObject = array();

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			if($this->exists($dn))
			{
				$arrObject = $this->_ldap->getEntry($dn);
			}
			else
			{
				$this->log("The specified DN '". $dn ."' was not found into LDAP directory.");
			}
		}
		
		$this->setResponseValue("functionReturn", $arrObject);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}
	
	/**
	 * Add or remove user to a group.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param array $arrGroupToProcess
	 * @param string $mode
	 * 
	 * @return array
	 */
	public function processGroup($initializeInformation, $searchField, $username, $arrGroupToProcess, $mode)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$arrResult["error"] = false;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$arrSearchResults = $this->search($searchField, $username);
			if(count($arrSearchResults) > 0)
			{
				foreach($arrSearchResults as $searchResult)
				{
					foreach($arrGroupToProcess as $groupDn)
					{
						if($this->exists($groupDn))
						{
							if($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_ACTIVEDIRECTORY)
							{
								$success = false;

								if($mode == "add")
								{
									$memberOf = isset($searchResult["memberof"]) && is_array($searchResult["memberof"]) ? array_map('strtolower', $searchResult["memberof"]) : array();
									if(!isset($searchResult["memberof"]) || !in_array(strtolower($groupDn), $memberOf))
									{
										$success = @ldap_mod_add($this->_ldap->getResource(), $groupDn, array("member" => $searchResult["dn"]));
									}
									else
									{
										$success = true;
									}
								}
								elseif($mode == "delete")
								{
									$success = @ldap_mod_del($this->_ldap->getResource(), $groupDn, array("member" => $searchResult["dn"]));
								}

								if($success)
								{
									$arrResult[$groupDn]["error"] = false;
								}
								else
								{
									$arrResult[$groupDn]["error"] = true;
									$arrResult[$groupDn]["message"] = "La mise à jour du groupe n'a pas fonctionné.";
								}
							}
							elseif($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_EDIRECTORY)
							{
								$success1 = false;
								$success2 = false;
								
								if($mode == "add")
								{
									if(!isset($searchResult["groupmembership"]) || !in_array($groupDn, $searchResult["groupmembership"]))
									{
										$success1 = @ldap_mod_add($this->_ldap->getResource(), $searchResult["dn"], array("securityequals" => $groupDn, "groupmembership" => $groupDn));
										$success2 = @ldap_mod_add($this->_ldap->getResource(), $groupDn, array("member" => $searchResult["dn"], "equivalenttome" => $searchResult["dn"]));
									}
									else
									{
										$success1 = true;
										$success2 = true;
									}
								}
								elseif($mode == "delete")
								{
									$success1 = @ldap_mod_del($this->_ldap->getResource(), $searchResult["dn"], array("securityequals" => $groupDn, "groupmembership" => $groupDn));
									$success2 = @ldap_mod_del($this->_ldap->getResource(), $groupDn, array("member" => $searchResult["dn"], "equivalenttome" => $searchResult["dn"]));
								}
								
								if($success1 && $success2)
								{
									$arrResult[$groupDn]["error"] = false;
								}
								else
								{
									$arrResult[$groupDn]["error"] = true;
									$arrResult[$groupDn]["message"] = "La mise à jour du groupe n'a pas fonctionné.";
								}
							}
							elseif($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_OPENLDAP)
							{
								// TODO: Coder l'ajout de groupe pour openLdap.
							}
						}
						else
						{
							$arrResult[$groupDn]["error"] = true;
							$arrResult[$groupDn]["message"] = "Le groupe n'existe pas.";
						}
					}
				}
			}
			else
			{
				$this->log("The user does not exists into the LDAP directory.");
				$arrResult["error"] = true;
				$arrResult["message"] = "L'usager '". $username ."' n'existe pas dans l'annuaire.";
			}
		}

		$this->setResponseValue("functionReturn", $arrResult);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}

	/**
	 * Determines if domain is valid.
	 *
	 * @param array $initializeInformation
	 * 
	 * @return array
	 */
	public function validateConnexion($initializeInformation)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$blnValideConnexion = 0;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			//TODO: Ajouter une facon de tester si on peut créer un user... mettre un status different (2) si on ne peut pas ecrire.
			$blnValideConnexion = 1;
		}
		
		$this->setResponseValue("functionReturn", $blnValideConnexion);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}
	
	/**
	 * Check account state on LDAP.
	 * 
	 * @param array $initializeInformation
	 * @param string $usernameField
	 * @param string $username
	 * @param string $inactiveField
	 *
	 * @return array
	 */
	public function checkAccountState($initializeInformation, $usernameField, $username, $inactiveField = "useraccountcontrol")
	{
		$this->log("Start ". __METHOD__);

		$this->setResponseValue("functionName", __METHOD__);

		$accountState = 3;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$arrSearchResults = $this->search($usernameField, $username);
			if(count($arrSearchResults) > 0)
			{
				foreach($arrSearchResults as $searchResult)
				{
					$inactiveField = strtolower($inactiveField);
					
					if(isset($searchResult[$inactiveField][0]))
					{
						if($searchResult[$inactiveField][0] === "FALSE" || $searchResult[$inactiveField][0] === "0" || $searchResult[$inactiveField][0] === 0 || $searchResult[$inactiveField][0] == 544 || $searchResult[$inactiveField][0] == 512 || $searchResult[$inactiveField][0] == 66048)
						{
							$accountState = 0;
						}
						else
						{
							$accountState = 2;
						}
						break;
					}
				}
			}
			else
			{
				$accountState = 1;
			}
		}
		
		$this->log("End ". __METHOD__);
		
		$this->setResponseValue("functionReturn", $accountState);

		return $this->getResponseValue();
	}
	
	/**
	 * Activate LDAP account.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param string $inactiveField
	 *
	 * @return array
	 */
	public function activateAccount($initializeInformation, $searchField, $username, $inactiveField)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$blnActivateAccount = false;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$searchResults = $this->search($searchField, $username);
			if(count($searchResults) > 0)
			{
				foreach($searchResults as $searchResult)
				{
					// Activer le compte
					if($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_ACTIVEDIRECTORY)
					{
						$value = 512;
					}
					elseif($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_EDIRECTORY)
					{
						$value = "FALSE";
					}
					elseif($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_OPENLDAP)
					{
						$value = "0";
					}

					$entry[$inactiveField] = $value;

					// TODO: Idéalement, il faudrait valider l'attribut $inactiveField dans le schéma LDAP

					if($this->update($searchResult["dn"], $entry))
					{
						$blnActivateAccount = true;
					}
				}
			}
			else
			{
				$this->log("trouve pas!");
				$this->log($searchField);
				$this->log($username);
			}
		}
		
		$this->setResponseValue("functionReturn", $blnActivateAccount);
		
		$this->log("End ". __METHOD__);
		
		return $this->getResponseValue();
	}
	
	/**
	 * Change password for a LDAP account.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param string $newPassword
	 * 
	 * @return array
	 */
	public function changePasswordAccount($initializeInformation, $searchField, $username, $newPassword)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$blnChangePasswordAccount = false;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$searchResults = $this->search($searchField, $username);
			if(count($searchResults) > 0)
			{
				foreach($searchResults as $searchResult)
				{
					if($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_ACTIVEDIRECTORY)
					{
						$blnChangePasswordAccount = true;
					}
					elseif($this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_EDIRECTORY || $this->_rootDse->getServerType() == Zend_Ldap_Node_RootDse::SERVER_TYPE_OPENLDAP)
					{
						$entry["userPassword"] = $newPassword;
						$this->update($searchResult['dn'], $entry);
						
						$blnChangePasswordAccount = true;
					}
				}
			}
		}

		$this->setResponseValue("functionReturn", $blnChangePasswordAccount);

		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}

	/**
	 * Create LDAP object.
	 * 
	 * @param array $initializeInformation
	 * @param string $dn
	 * @param array $entry
	 *
	 * @return array
	 */
	public function createObject($initializeInformation, $dn, $entry)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$blnCreate = false;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			// Création de l'objet
			try
			{
				$this->_ldap->add($dn, $entry);
				$blnCreate = true;
			}
			catch(Zend_Ldap_Exception $e)
			{
				$this->log($e);
			}
		}
		
		$this->setResponseValue("functionReturn", $blnCreate);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}
	
	/**
	 * Update LDAP object attribute.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param array $arrEntry
	 * 
	 * @return array
	 * 
	 */
	public function updateObjectAttribute($initializeInformation, $searchField, $username, $arrEntry)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);
		
		$blnUpdateAttribute = false;
		
		$this->log("Search field: ". $searchField);

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$searchResults = $this->search($searchField, $username);
			if(count($searchResults) > 0)
			{
				$this->log(print_r($arrEntry, true));
				foreach($searchResults as $searchResult)
				{
					if($this->update($searchResult["dn"], $arrEntry))
					{
						$blnUpdateAttribute = true;
					}
				}
			}
		}
		
		$this->setResponseValue("functionReturn", $blnUpdateAttribute);
		$this->log("End ". __METHOD__);
		
		return $this->getResponseValue();
	}
	
	/**
	 * 
	 * Move LDAP object.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param string $dnTo
	 *
	 * @return array
	 * 
	 */
	public function moveObject($initializeInformation, $searchField, $username, $dnTo)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);

		$blnMove = false;

		// Initialize connection on LDAP directory
		if($this->initialize($initializeInformation))
		{
			$searchResults = $this->search($searchField, $username);
			if(count($searchResults) > 0)
			{
				foreach($searchResults as $searchResult)
				{
					$dnFrom = $searchResult["dn"];
					if($this->_ldap->moveToSubtree($dnFrom, $dnTo))
					{
						$blnMove = true;
					}
				}
			}
		}
		
		$this->setResponseValue("functionReturn", $blnMove);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}
	
	/**
	 * 
	 * Delete LDAP object.
	 * 
	 * @param array $initializeInformation
	 * @param string $searchField
	 * @param string $username
	 * @param bool $recursively
	 *
	 * @return array
	 * 
	 */
	public function deleteObject($initializeInformation, $searchField, $username, $recursively = false)
	{
		$this->log("Start ". __METHOD__);
		
		$this->setResponseValue("functionName", __METHOD__);

		$blnDelete = false;

		// Initialise la connection sur l'anuaire LDAP
		if($this->initialize($initializeInformation))
		{
			$searchResults = $this->search($searchField, $username);
			if(count($searchResults) > 0)
			{
				foreach($searchResults as $searchResult)
				{
					$dnToDelete = $searchResult["dn"];
					if($this->_ldap->delete($dnToDelete, $recursively))
					{
						$blnDelete = true;
					}
				}
			}
		}

		$this->setResponseValue("functionReturn", $blnDelete);
		$this->log("End ". __METHOD__);

		return $this->getResponseValue();
	}
}

?>