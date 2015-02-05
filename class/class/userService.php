<?php

class userService
{
	private $_userService = array();
	private $_userServiceList = array();

	public function __construct($userId)
	{
		$this->loadServiceForUserId($userId);
	}
	
	private function getUserService()
	{
		return $this->_userService;
	}
	
	private function getUserServiceList()
	{
		return $this->_userServiceList;
	}

	private function setUserService($establishmentId, $jobId, $serviceId)
	{
		if(isset($this->_userService[$establishmentId][$jobId]))
		{
			$this->_userService[$establishmentId][$jobId] = array_merge($this->_userService[$establishmentId][$jobId], array($serviceId));
		}
		else
		{
			$this->_userService[$establishmentId][$jobId][] = $serviceId;
		}
		
		$this->setUserServiceList($serviceId);
	}
	
	private function setUserServiceList($serviceId)
	{
		if(!in_array($serviceId, $this->_userServiceList))
		{
			$this->_userServiceList[] = $serviceId;
		}
	}
	
	private function loadServiceForUserId($userId)
	{
		$userServices = $this->getServiceForUserId($userId);
		
		foreach($userServices as $userService)
		{
			extract($userService);
			$this->setUserService($idEtablissement, $idEmploi, $idService);
		}
	}
	
	private function getServiceForUserId($userId)
	{
		$sql = sql_getServicesUsager($userId);
		return db()->fetchArray($sql);
	}
	
	public function has($serviceId, $options = array())
	{
		$has = false;
		$userService = $this->getUserService();
		
		// Merge default options with options received in parameter
		$defaultOptions = array(
			"forEstablishmentId" => null,
			"forJobId" => null,
		);
		extract(array_replace_recursive($defaultOptions, $options));

		if(!is_null($forEstablishmentId) && !is_null($forJobId)) // Check if the user has service for particular establisementId AND jobId
		{
			$has = isset($userService[$forEstablishmentId][$forJobId]) && in_array($serviceId, $userService[$forEstablishmentId][$forJobId]);
		}
		elseif(!is_null($forEstablishmentId) && is_null($forJobId)) // Check if the user has service for particular establisementId
		{
			if(isset($userService[$forEstablishmentId]))
			{
				foreach($userService[$forEstablishmentId] as $jobId => $jobService)
				{
					$has = in_array($serviceId, $jobService);
				}
			}
		}
		elseif(is_null($forEstablishmentId) && !is_null($forJobId)) // Check if the user has service for particular jobId
		{
			foreach($userService as $establishmentId => $job)
			{
				$has = isset($job[$forJobId]) && in_array($serviceId, $job[$forJobId]);
			}
		}
		else // Check if the user have the service
		{
			return in_array($serviceId, $this->getUserServiceList());
		}
		
		return $has;
	}
}

?>