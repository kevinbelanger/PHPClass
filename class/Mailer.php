<?php

class Mail
{
	// Tableau contenant les propriété du courriel
	private $_arrProprieteMail = array(
		"FromName" => "",
		"From" => "",
		"To" => array(),
		"Cc" => array(),
		"Bcc" => array(),
		"EstHTML" => 1,
		"Sujet" => "",
		"Message" => "",
		"Priorite" => 3,	// 1(Haute) à 5(Basse), 3 est normal
		"PieceJointe" => array(),
		"Charset" => "utf-8",
		"TransfertEncoding" => "7bit"
	);
	
	private $_strMailEntetes; // Les entêtes du courriel.
	private $_intBoundary; // La valeur unique pour le courriel boundary
	private $_strSautLigne = "\r\n"; // Saut de ligne dans les courriels.

	/**
	 * Constructeur de la classe mail
	 *
	 * @param Array $arrProprieteMail : Tableau conenant les infos pour le mail
	 *
	 * @return Void
	 */
	function __construct($arrProprieteMail = null)
	{
		$this->creerBoundary();
		
		if(isset($arrProprieteMail))
		{
			if(array_key_exists('FromName', $arrProprieteMail))
			{
				$this->__set("FromName", $arrProprieteMail["FromName"]);
			}

			if(array_key_exists('From', $arrProprieteMail))
			{
				$this->__set("From", $arrProprieteMail["From"]);
			}

			if(array_key_exists('To', $arrProprieteMail))
			{
				$this->__set("To", $arrProprieteMail["To"]);
			}

			if(array_key_exists('Cc', $arrProprieteMail))
			{
				$this->__set("Cc", $arrProprieteMail["Cc"]);
			}

			if(array_key_exists('Bcc', $arrProprieteMail))
			{
				$this->__set("Bcc", $arrProprieteMail["Bcc"]);
			}

			if(array_key_exists('EstHTML', $arrProprieteMail))
			{
				$this->__set("EstHTML", $arrProprieteMail["EstHTML"]);
			}

			if(array_key_exists('Sujet', $arrProprieteMail))
			{
				$this->__set("Sujet", $arrProprieteMail["Sujet"]);
			}

			if(array_key_exists('Message', $arrProprieteMail))
			{
				$this->__set("Message", $this->creerCorpMessage($arrProprieteMail["Message"]));
			}

			if(array_key_exists('Priorite', $arrProprieteMail))
			{
				$this->__set("Priorite", $arrProprieteMail["Priorite"]);
			}

			if(array_key_exists('PieceJointe', $arrProprieteMail))
			{
				$this->__set("PieceJointe", $arrProprieteMail["PieceJointe"]);
			}

			if(array_key_exists('Charset', $arrProprieteMail))
			{
				$this->__set("Charset", $arrProprieteMail["Charset"]);
			}

			if(array_key_exists('TransfertEncoding', $arrProprieteMail))
			{
				$this->__set("TransfertEncoding", $arrProprieteMail["TransfertEncoding"]);
			}
		}

		$this->_strMailEntetes = $this->creerEntete();
	}
	
	/**
	 * Destructeur de la classe mail
	 *
	 * @return Void
	 */
	function __destruct()
	{
	
	}

	/**
	 * Setter de la classe
	 *
	 * @param String $strCle : Le nom de la propriété
	 * @param Mixed $mixValue : La valeur à setter
	 *
	 * @return Void
	 */
	function __set($strCle, $mixValue)
	{
		if(is_int($this->_arrProprieteMail[$strCle]) && !is_int($mixValue))
		{
			throw new Exception("Type invalide, doit être un entier.");
		}
		else if(is_string($this->_arrProprieteMail[$strCle]) && !is_string($mixValue))
		{
			throw new Exception("Type invalide, doit être une chaîne de caractère.");
		}
		else if(is_array($this->_arrProprieteMail[$strCle]) && !is_array($mixValue))
		{
			throw new Exception("Type invalide, doit être un tableau.");
		}
		else
		{
			$this->_arrProprieteMail[$strCle] = $mixValue;
		}
	}

	/**
	 * Crée l'identifiant unique du mail
	 *
	 * @return Void
	 */
	private function creerBoundary()
	{
		$this->_intBoundary = "_" . md5(uniqid(time()));
	}

	/**
	 * Retourne le contenu du fichier
	 * @param String $strCheminFichier : Le chemin vers le fichier
	 *
	 * @return $strContenuFichier : Le contenu du fichier.
	 */
	private function obtenirContenuFichier($strCheminFichier)
	{
		if(file_exists($strCheminFichier))
		{
			if(!$strContenuFichier = file_get_contents($strCheminFichier))
			{
				throw new Exception("Erreur, il est impossible d'ouvrir cette pièce jointe : " . basename($strCheminFichier));
			}
			else
			{
				return $strContenuFichier;
			}
		}
		else
		{
			throw new Exception("Erreur, le fichier ". basename($strCheminFichier) . " n'existe pas.");
			return;
		}
	}

	/**
	 * Crée le corp du courriel
	 */
	private function creerCorpMessage($strCorpMessage)
	{
		$strMessage = "--" . $this->_intBoundary . $this->_strSautLigne;
		if($this->_arrProprieteMail["EstHTML"])
		{
			$strMessage .= "Content-type:text/html; charset=" . $this->_arrProprieteMail["Charset"] . $this->_strSautLigne;
		}
		else
		{
			$strMessage .= "Content-type:text; charset=" . $this->_arrProprieteMail["Charset"] . $this->_strSautLigne;
		}

		$strMessage .= "Content-Transfer-Encoding: " . $this->_arrProprieteMail["TransfertEncoding"] . $this->_strSautLigne . $this->_strSautLigne;
		$strMessage .= trim($strCorpMessage) . $this->_strSautLigne . $this->_strSautLigne;

		return $strMessage;
	}

	/**
	 * Crée les entetes du mail
	 *
	 * @ return String $strEntete : Entete du courriel
	 */
	private function creerEntete()
	{
		if($this->_arrProprieteMail["FromName"] != "")
		{
			$strEntete = "From: " . $this->_arrProprieteMail["FromName"] . " <" . $this->_arrProprieteMail["From"] . ">" . $this->_strSautLigne;
			$strEntete .= "Reply-To: " . $this->_arrProprieteMail["FromName"] . " <" . $this->_arrProprieteMail["From"] . ">" . $this->_strSautLigne;
		}
		else
		{
			$strEntete = "From: " . $this->_arrProprieteMail["From"] . $this->_strSautLigne;
			$strEntete .= "Reply-To: " . $this->_arrProprieteMail["From"] . $this->_strSautLigne;
		}
		if(count($this->_arrProprieteMail["Cc"]) > 0)
		{
			$strEntete .= "Cc: " . implode(", ", $this->_arrProprieteMail["Cc"]) . $this->_strSautLigne;
		}
		if(count($this->_arrProprieteMail["Bcc"]) > 0)
		{
			$strEntete .= "Bcc: " . implode(", ", $this->_arrProprieteMail["Bcc"]) . $this->_strSautLigne;
		}
		$strEntete .= "MIME-Version: 1.0" . $this->_strSautLigne;
		$strEntete .= "X-Mailer: Attachment Mailer ver. 1.0" . $this->_strSautLigne;
		$strEntete .= "X-Priority: " . $this->_arrProprieteMail["Priorite"] . $this->_strSautLigne;
		$strEntete .= "Content-Type: multipart/mixed;" . $this->_strSautLigne . chr(9) . " boundary=\"" . $this->_intBoundary . "\"" . $this->_strSautLigne . $this->_strSautLigne;
		$strEntete .= "This is a multi-part message in MIME format." . $this->_strSautLigne . $this->_strSautLigne;
		return $strEntete;
	}

	/**
	 * Ajout de pièce jointe
	 *
	 * @param String $strCheminFichier : Le chemin vers le fichier
	 * @param String $dispo : La disposition de l'attachement
	 *
	 * @return Void
	 */
	public function ajouterPieceJointe($strCheminFichier, $dispo = "attachment")
	{
		$strContenuFichier = $this->obtenirContenuFichier($strCheminFichier);
		if($strContenuFichier != "")
		{
			$filename = basename($strCheminFichier);
			$file_type = mime_content_type($strCheminFichier);
			$chunks = chunk_split(base64_encode($strContenuFichier));
			$mail_part = "--".$this->_intBoundary . $this->_strSautLigne;
			$mail_part .= "Content-type:" . $file_type . ";" . $this->_strSautLigne . chr(9) . " name=\"" . $filename . "\"" . $this->_strSautLigne;
			$mail_part .= "Content-Transfer-Encoding: base64".$this->_strSautLigne;
			$mail_part .= "Content-Disposition: " . $dispo.";" . chr(9) . "filename=\"" . $filename . "\"" . $this->_strSautLigne . $this->_strSautLigne;
			$mail_part .= $chunks;
			$mail_part .= $this->_strSautLigne . $this->_strSautLigne;
			$this->_arrProprieteMail["PieceJointe"][] = $mail_part;
		}
	}

	/**
	 * Envoi du courriel
	 *
	 * @return
	 */
	public function envoyerCourriel()
	{
		$strMessageCourriel = $this->_arrProprieteMail["Message"];

		if(count($this->_arrProprieteMail["PieceJointe"]) > 0)
		{
			foreach($this->_arrProprieteMail["PieceJointe"] as $val)
			{
				$strMessageCourriel .= $val;
			}
			$strMessageCourriel .= "--".$this->_intBoundary."--";
		}

		if(@mail(implode(", ", $this->_arrProprieteMail["To"]), $this->_arrProprieteMail["Sujet"], $strMessageCourriel, $this->_strMailEntetes))
		{
			return true;
		}
		else
		{
			return false;
		}
	}
}

?>