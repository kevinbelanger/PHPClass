<?php

class Language
{
	protected $m_xml;         // objet xmldom pour les textes
	protected $m_errCode;      // code d'erreur
	protected $m_lang;         // langue à utiliser
	public $langue = "fr";

	function __construct($xmlPath)
	{
		$this->m_errCode = 0; // par défaut, aucune erreur n'est présente
		$this->m_xml = new DOMDocument(); // création de l'objet xmldom pour la langue

		$this->LoadUserLang();
		$this->Load($xmlPath);
	}

	public function __destruct()
	{
		unset($this->m_xml);
	}

	// Retourne la langue de l'utilisateur
	function Langue()
	{
		return $this->m_lang;
	}

	// Permet d'obtenir la dernière erreur en format texte
	public function GetLastError()
	{
		return "NO_ERROR";
	}

	// Charge un fichier de language en format xml
	function Load($xmlPath)
	{
		if(@$this->m_xml->load($xmlPath))
		{
			$this->m_errCode = 0;
		}
	}

	// Charge la langue de l'usager
	function LoadUserLang()
	{
		$this->m_lang = "fr";

		if(isset($_SESSION["general"]["codeLangueCourante"]))
		{
			$this->m_lang = $_SESSION["general"]["codeLangueCourante"];
		}
	}

	// Retourner un message selon sa langue msgId = id du message
	function getMsg($msgId, $lang = null)
	{
		$lang = ($lang != null)?$lang:$this->m_lang;
		
		$domxpath = new DOMXPath($this->m_xml);
		$nodes = $domxpath->query("/language/text" . '[@' . 'id' . "='$msgId']/".$lang);
		$value="TRADUCTION_MANQUANTE";
		foreach($nodes as $node)
		{
			$value = $node->nodeValue;
		}
		return $value;
	}
}

?>