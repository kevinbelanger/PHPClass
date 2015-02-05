<?php

class Cryptography
{
	private $key = "abcdefg_abcdefg_abcdefg_abcdefg_"; // 32 caractères ici.
	private $iv = "abcdefg_abcdefg_"; // 16 caractères ici.
	
	function __construct($arrOptions)
	{
		if(isset($arrOptions["key"]))
		{
			$this->key = $arrOptions["key"];
		}
		if(isset($arrOptions["iv"]))
		{
			$this->iv = $arrOptions["iv"];
		}
		
		if(!function_exists("mcrypt_module_open"))
		{
			die("la librarie mcrypt.dll n'est pas pris en charge dans le fichier PHP.ini");
		}
	}

	// Encryption function  
	function encrypt($encrypt)  
	{  
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		mcrypt_generic_init($td, $this->key, $this->iv);  
		$encrypted = mcrypt_generic($td, $encrypt);  
		$encode = base64_encode($encrypted);  
		mcrypt_generic_deinit($td);  
		mcrypt_module_close($td);  

		return $encode;
	}  

	// Decryption function  
	function decrypt($decrypt)  
	{
		$decoded = base64_decode($decrypt);
		$td = mcrypt_module_open(MCRYPT_RIJNDAEL_128, '', MCRYPT_MODE_ECB, '');
		mcrypt_generic_init($td, $this->key, $this->iv);
		$decrypted = mdecrypt_generic($td, $decoded);
		mcrypt_generic_deinit($td);
		mcrypt_module_close($td);

		return trim($decrypted);
	}
}

?>