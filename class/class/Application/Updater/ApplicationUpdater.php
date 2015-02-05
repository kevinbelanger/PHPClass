<?php

final class ApplicationUpdater
{
	const NUMBER_OF_STEP = 4;

	static protected $_updaterProcess = array();
	static protected $_currentStep = 1;
	
	static public function init($options = array())
	{
		self::$_currentStep = (isset($options["currentStep"])) ? $options["currentStep"] : 1;
		self::$_updaterProcess = (isset($options["updaterProcess"])) ? $options["updaterProcess"] : array();

		// If no update process in progress
		if(count(self::$_updaterProcess) == 0)
		{
			$return = self::startUpdate();
		}
		elseif(self::$_currentStep > self::NUMBER_OF_STEP)
		{
			// Redirect the user to endUpdate
			$return = self::endUpdate();
		}
		else
		{
			$updateFunctionName = "UpdateStep". self::$_currentStep;
			
			// Insert the new step in the database
			$idUpdaterProcessStep = self::startStep();
			
			// Execute the step
			$return = self::$updateFunctionName();
			
			// Update the database to put the step finished
			if(!$return["error"])
			{
				self::endStep($idUpdaterProcessStep);
			}
		}
		
		self::showAjaxResponse($return);
	}
	
	static private function showAjaxResponse($response)
	{
		echo json_encode($response);
		exit;
	}
	
	static public function startUpdate()
	{
		// Check if an update process is in progress if not, start a new one
		$sql = sql_getUpdaterProcess(null, "inProgress");
		$arrUpdaterProcess = db()->fetchArray($sql);
		
		if(count($arrUpdaterProcess) == 0)
		{
			try
			{
				$return = array(
					"error" => false
				);

				// Start a new update process into the database
				$licenseInfo = Application::getAdapter()->getLicenseInfo();
				$toRevision = $licenseInfo["lastVersion"];

				self::$_updaterProcess = $arrUpdaterProcess = array(
					"fromRevision" => self::getCurrentRevision(),
					"toRevision" => $toRevision,
					"inProgress" => 1,
					"startDate" => time(),
					"endDate" => 0,
				);

				$sql = sql_insertUpdaterProcess($arrUpdaterProcess);
				db()->query($sql);

				self::$_updaterProcess["uid"] = db()->insert_id();
			}
			catch(Exception $e)
			{
				
			}
		}
		else
		{
			$return = array(
				"error" => true,
				"errorMessage" => lang("ADMIN_SYSTEM_UPDATE_PROBLEM_INITIALIZATION")
			);
		}
		
		return $return;
	}
	
	static public function endUpdate()
	{
		try
		{
			$return = array(
				"error" => false
			);

			clearCache();
			
			// Add task to register DLL after the update
			$arrTaskQueue = array(
				"idTask" => TaskRegistry::get("RegisterDllTask")->getTaskUid(),
				"idUsager" => 0,
				"idLdapDomaine" => 0,
				"params" => "",
				"datePerform" => 0
			);
			addTaskQueue($arrTaskQueue);

			// End the upgrade progress in the database
			$arrUpdaterProcess = array(
				"inProgress" => 0,
				"endDate" => time(),
			);

			$sql = sql_updateUpdaterProcess(self::$_updaterProcess["uid"], $arrUpdaterProcess);
			db()->query($sql);
		}
		catch(Exception $e)
		{
			$return = array(
				"error" => true,
				"errorMessage" => lang("ADMIN_SYSTEM_UPDATE_PROBLEM_FINALIZATION")
			);
		}
		
		return $return;
	}
	
	static public function updateStep1()
	{
		try
		{
			$return = array(
				"error" => false
			);

			$archivePath = DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."upload". DIRECTORY_SEPARATOR . self::$_updaterProcess["toRevision"] .".zip";

			// Download the last version
			$returnedData = self::downloadLastVersion();
			
			if(!is_null(json_decode($returnedData)))
			{
				$returnedData = json_decode($returnedData);

				$return["error"] = true;
				$return["errorMessage"] = $returnedData->errorMessage;
			}
			else
			{
				// Save the archive on the disk
				file_put_contents($archivePath, $returnedData);
				
				// If the size of the file can't be check (false) or, if it equal to 0 raise an error
				$filesize = @filesize($archivePath);
				
				if($filesize === false || $filesize === 0)
				{
					$return["error"] = true;
					$return["errorMessage"] = lang("ADMIN_SYSTEM_UPDATE_PROBLEM_STEP_1");
				}
			}
		}
		catch(Exception $e)
		{
			$return = array(
				"error" => true,
				"errorMessage" => lang("ADMIN_SYSTEM_UPDATE_PROBLEM_STEP_1")
			);
		}
		
		return $return;
	}
	
	static public function updateStep2()
	{
		try
		{
			$return = array(
				"error" => false
			);

			$archivePath = DOCUMENT_ROOT . DIRECTORY_SEPARATOR ."upload". DIRECTORY_SEPARATOR . self::$_updaterProcess["toRevision"] .".zip";
			$extractToPath = DOCUMENT_ROOT . DIRECTORY_SEPARATOR . APPLICATION_NAME . DIRECTORY_SEPARATOR . self::$_updaterProcess["toRevision"];
			
			// Unpack the last version
			$zip = new Zip();
			$zip->uncompress($archivePath, $extractToPath);
		}
		catch(Exception $e)
		{
			$return = array(
				"error" => true,
				"errorMessage" => lang("ADMIN_SYSTEM_UPDATE_PROBLEM_STEP_2")
			);
		}
		
		return $return;
	}
	
	static public function updateStep3()
	{
		try
		{
			$return = array(
				"error" => false
			);

			// Upgrade the revision number and write the file
			$regexVersionNumber = '/^define\(\"APPLICATION_VERSION\"/';
			$lines = file(CONFIG_PATH . DIRECTORY_SEPARATOR ."version.php");
			foreach($lines as &$line)
			{
				if(preg_match($regexVersionNumber, $line))
				{
					$lineBeforeReplace = $line;
					$line = preg_replace('/(?:(\d+)\.)?(?:(\d+)\.)?(\*|\d+)/', self::$_updaterProcess["toRevision"], $line);

					// If the line is the same before the replace and after try to replace the version name "current"
					if($lineBeforeReplace == $line)
					{
						$line = str_replace("current", self::$_updaterProcess["toRevision"], $line);
					}
					
					// If the line before replace is the same than after the replace
					if($lineBeforeReplace == $line)
					{
						throw new Exception("Can't update the version number!");
					}
				}
			}
			
			$newFileString = implode("", $lines);
			$options = array(
				"openingMode" => "w"
			);
			$file = new File(CONFIG_PATH . DIRECTORY_SEPARATOR ."version.php", $options);
			$file->write($newFileString);
		}
		catch(Exception $e)
		{
			$return = array(
				"error" => true,
				"errorMessage" => lang("ADMIN_SYSTEM_UPDATE_PROBLEM_STEP_3")
			);
		}
		
		return $return;
	}
	
	static public function updateStep4()
	{
		try
		{
			$return = array(
				"error" => false
			);

			db()->beginTransaction();

			// Get all update script to perform update from revision X to the last revision
			$arrUpdaterScript = self::getAvailableScripts();

			if(count($arrUpdaterScript) > 0)
			{
				// Get all actions and execute them.
				foreach($arrUpdaterScript as $updateScript)
				{
					$arrActions = $updateScript->getActions();
					foreach($arrActions as $functionName => $functionDescription)
					{
						$updateScript->$functionName();
					}
				}
			}
			
			db()->commitTransaction();
		}
		catch(Exception $e)
		{
			db()->rollbackTransaction();
			$return = array(
				"error" => true,
				"errorMessage" => lang("ADMIN_SYSTEM_UPDATE_PROBLEM_STEP_4")
			);
		}
		
		return $return;
	}
	
	static private function startStep()
	{
		// Check if a step is already in progress into the database for this updater process
		$sql = sql_getUpdaterProcessStep(null, self::$_updaterProcess["uid"], self::$_currentStep, "inProgress");
		$arrCurrentStep = db()->fetchRow($sql);
		
		if(count($arrCurrentStep) == 0)
		{
			// Insert the new step in the database
			$arrUpdaterProcessStep = array(
				"idUpdaterProcess" => self::$_updaterProcess["uid"],
				"inProgress" => 1,
				"stepNumber" => self::$_currentStep,
				"startDate" => time(),
				"endDate" => 0,
			);
			$sql = sql_insertUpdaterProcessStep($arrUpdaterProcessStep);
			db()->query($sql);

			$idUpdaterProcessStep = db()->insert_id();
		}
		else
		{
			$idUpdaterProcessStep = $arrCurrentStep["uid"];
		}
		
		return $idUpdaterProcessStep;
	}
	
	static private function endStep($idUpdaterProcessStep)
	{
		// Update step in the database
		$arrUpdaterProcessStep = array(
			"inProgress" => 0,
			"endDate" => time(),
		);
		$sql = sql_updateUpdaterProcessStep($idUpdaterProcessStep, $arrUpdaterProcessStep);
		db()->query($sql);
	}
	
	static private function downloadLastVersion()
	{
		$key = md5(getConfigValue("core", "APPLICATION_KEY")) ."###SEPARATOR###". md5(getConfigValue("core", "CLIENT_KEY"));

		$url = sprintf(getConfigValue("core", "SERVER_UPDATER_URL"), getConfigValue("core", "SERVER_UPDATER_ACTION_LAST_PACKAGE"));

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

		$fields = array(
			"key" => $key
		);

		$fields_string = "";
		foreach($fields as $key => $value)
		{
			$fields_string .= $key .'='. $value .'&';
		}
		rtrim($fields_string, '&');
		
		curl_setopt($ch, CURLOPT_POST, $fields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);

		$data = curl_exec($ch);

		curl_close($ch);

		return $data;
	}
	
	static public function getLatestVersionInfo()
	{
		$key = md5(getConfigValue("core", "APPLICATION_KEY")) ."###SEPARATOR###". md5(getConfigValue("core", "CLIENT_KEY"));

		$url = sprintf(getConfigValue("core", "SERVER_UPDATER_URL"), getConfigValue("core", "SERVER_UPDATER_ACTION_LAST_VERSION"));

		//  Initiate curl
		$ch = curl_init();
		
		// Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// Set the url
		curl_setopt($ch, CURLOPT_URL, $url);
		
		$fields = array(
			"key" => $key
		);
		
		$fields_string = "";
		foreach($fields as $key => $value)
		{
			$fields_string .= $key .'='. $value .'&';
		}
		rtrim($fields_string, '&');
		
		curl_setopt($ch, CURLOPT_POST, $fields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		
		// Execute
		$result = curl_exec($ch);

		return json_decode($result, true);
	}
	
	static public function getServicePlanRenewalDate()
	{
		$key = md5(getConfigValue("core", "APPLICATION_KEY")) ."###SEPARATOR###". md5(getConfigValue("core", "CLIENT_KEY"));

		$url = sprintf(getConfigValue("core", "SERVER_UPDATER_URL"), getConfigValue("core", "SERVER_UPDATER_ACTION_SERVICE_PLAN_RENEWAL_DATE"));

		//  Initiate curl
		$ch = curl_init();
		
		// Disable SSL verification
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		// Will return the response, if false it print the response
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		
		// Set the url
		curl_setopt($ch, CURLOPT_URL, $url);
		
		$fields = array(
			"key" => $key
		);
		
		$fields_string = "";
		foreach($fields as $key => $value)
		{
			$fields_string .= $key .'='. $value .'&';
		}
		rtrim($fields_string, '&');
		
		curl_setopt($ch, CURLOPT_POST, $fields);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
		
		// Execute
		$result = curl_exec($ch);

		return json_decode($result, true);
	}
	
	static private function getAvailableScripts()
	{
		$files = self::getUpdateFiles();
		
		$fromRevision = self::getFromRevision();

		$result = array();
		if(is_array($files))
		{
			sort($files);

			foreach($files as $file)
			{
				require_once $file;
				
				$basename = basename($file);
				$class_name = substr($basename, 0,  strpos($basename, '.'));
				
				$script = new $class_name();

				if(version_compare($script->toRevision, $fromRevision, '>') && version_compare($script->toRevision, self::$_updaterProcess["toRevision"], '<='))
				{
					$result[] = $script;
				}
				else
				{
					unset($script);
				}
			}
		}

		return $result;
	}
	
	static private function getUpdateFiles()
	{
		$updateDir = APPLICATION_PATH . DIRECTORY_SEPARATOR ."update". DIRECTORY_SEPARATOR;
		$dh  = opendir($updateDir);
		while(false !== ($filename = readdir($dh)))
		{
			if(!is_dir($updateDir . $filename))
			{
				$files[] = $updateDir . $filename;
			}
		}

		return $files;
	}
	
	static private function getCurrentRevision()
	{
		return APPLICATION_VERSION;
	}
	
	static private function getFromRevision()
	{
		$sql = sql_getUpdaterProcess(null, "inProgress");
		$arrUpdaterProcess = db()->fetchRow($sql);
		
		$lastRevision = 0;
		if(count($arrUpdaterProcess) > 0)
		{
			$lastRevision = $arrUpdaterProcess["fromRevision"];
		}

		return $lastRevision;
	}
}

?>