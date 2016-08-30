<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Person extends APIv1_Controller
{
	/**
	 * Person API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model("person/person_model", "PersonModel");
	}

	/**
	 * @return void
	 */
	public function getPerson()
	{
		$personID = $this->get("person_id");
		$code = $this->get("code");
		$email = $this->get("email");
		
		if (isset($code) || isset($email) || isset($personID))
		{
			if (isset($code) && isset($email))
			{
				$result = $this->PersonModel->addJoin("public.tbl_kontakt", "person_id");
				if ($result->error == EXIT_SUCCESS)
				{
					$result = $this->PersonModel->loadWhere(array("zugangscode" => $code, "kontakt" => $email));
				}
			}
			elseif (isset($code))
			{
				$result = $this->PersonModel->loadWhere(array("zugangscode" => $code));
			}
			else
			{
				$result = $this->PersonModel->load($personID);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postPerson()
	{
		$person = $this->_parseData($this->post());
		$validation = $this->_validate($this->post());
		
		if (is_object($validation) && $validation->error == EXIT_SUCCESS)
		{
			if(isset($person["person_id"]) && !(is_null($person["person_id"])) && ($person["person_id"] != ""))
			{
				$this->PersonModel->addOrder("svnr", "DESC");
				$result =  $this->PersonModel->loadWhere(array(
					"person_id != " => $person["person_id"],
					"SUBSTRING(svnr FROM 1 FOR 10) = " => $person["svnr"])
				);
				if (is_object($result) && $result->error == EXIT_SUCCESS &&
					is_array($result->retval) && count($result->retval) > 0)
				{
					if (count($result->retval) == 1)
					{
						$person["svnr"] = $person["svnr"] . "v1";
					}
					else
					{
						$person["svnr"] = $person["svnr"] . "v" . ($result->retval[0]->svnr{11} + 1);
					}
				}
				
				$result = $this->PersonModel->update($person["person_id"], $person);
			}
			else
			{
				$result = $this->PersonModel->insert($person);
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response($validation, REST_Controller::HTTP_OK);
		}
	}
	
	/**
	 * @return void
	 */
	public function getCheckBewerbung()
	{
		$email = $this->get("email");
		$studiensemester_kurzbz = $this->get("studiensemester_kurzbz");
		
		if (isset($email))
		{
			$result = $this->PersonModel->checkBewerbung($email, $studiensemester_kurzbz);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($person = NULL)
	{
		if (!isset($person))
		{
			return $this->_error("Parameter is null");
		}
		
		if (isset($person["nachname"]))
			$person["nachname"] = trim($person["nachname"]);
		if (isset($person["vorname"]))
			$person["vorname"] = trim($person["vorname"]);
		if (isset($person["vornamen"]))
			$person["vornamen"] = trim($person["vornamen"]);
		if (isset($person["anrede"]))
			$person["anrede"] = trim($person["anrede"]);
		if (isset($person["titelpost"]))
			$person["titelpost"] = trim($person["titelpost"]);
		if (isset($person["titelpre"]))
			$person["titelpre"] = trim($person["titelpre"]);
		
		if (isset($person["sprache"]) && mb_strlen($person["sprache"]) > 16)
		{
			return $this->_error("Sprache darf nicht laenger als 16 Zeichen sein");
		}
		if (isset($person["anrede"]) && mb_strlen($person["anrede"]) > 16)
		{
			return $this->_error("Anrede darf nicht laenger als 16 Zeichen sein");
		}
		if (isset($person["titelpost"]) && mb_strlen($person["titelpost"]) > 32)
		{
			return $this->_error("Titelpost darf nicht laenger als 32 Zeichen sein");
		}
		if (isset($person["titelpre"]) && mb_strlen($person["titelpre"]) > 64)
		{
			return $this->_error("Titelpre darf nicht laenger als 64 Zeichen sein");
		}
		if (isset($person["nachname"]) && mb_strlen($person["nachname"]) > 64)
		{
			return $this->_error("Nachname darf nicht laenger als 64 Zeichen sein");
		}
		if (isset($person["nachname"]) && ($person["nachname"] == "" || is_null($person["nachname"])))
		{
			return $this->_error("Nachname muss eingegeben werden");
		}

		if (isset($person["vorname"]) && mb_strlen($person["vorname"]) > 32)
		{
			return $this->_error("Vorname darf nicht laenger als 32 Zeichen sein");
		}
		if (isset($person["vornamen"]) && mb_strlen($person["vornamen"]) > 128)
		{
			return $this->_error("Vornamen darf nicht laenger als 128 Zeichen sein");
		}
		if (isset($person["gebort"]) && mb_strlen($person["gebort"]) > 128)
		{
			return $this->_error("Geburtsort darf nicht laenger als 128 Zeichen sein");
		}
		if (isset($person["homepage"]) && mb_strlen($person["homepage"]) > 256)
		{
			return $this->_error("Homepage darf nicht laenger als 256 Zeichen sein");
		}
		if (isset($person["matr_nr"]) && mb_strlen($person["matr_nr"]) > 32)
		{
			return $this->_error("Matrikelnummer darf nicht laenger als 32 Zeichen sein");
			return false;
		}
		if (isset($person["svnr"]) && $person["svnr"] != "" && mb_strlen($person["svnr"]) != 16 &&
			mb_strlen($person["svnr"]) != 12 && mb_strlen($person["svnr"]) != 10)
		{
			return $this->_error("SVNR muss 10, 12 oder 16 Zeichen lang sein");
		}
		if (isset($person["svnr"]) && (mb_strlen($person["svnr"]) == 10 || mb_strlen($person["svnr"]) == 12))
		{
			//SVNR mit Pruefziffer pruefen
			//Die 4. Stelle in der SVNR ist die Pruefziffer
			//(Summe von (gewichtung[i]*svnr[i])) modulo 11 ergibt diese Pruefziffer
			//Falls nicht, ist die SVNR ungueltig
			$gewichtung = array(3, 7, 9, 0, 5, 8, 4, 2, 1, 6);
			$erg = 0;
			$tmpSvnr = substr($person["svnr"], 0, 10);
			//Quersumme bilden
			for ($i = 0; $i < 10; $i++)
			{
				$erg += $gewichtung[$i] * $tmpSvnr{$i};
			}

			if ($tmpSvnr{3} != ($erg % 11)) //Vergleichen der Pruefziffer mit Quersumme Modulo 11
			{
				return $this->_error("SVNR ist ungueltig");
			}
			
			if (mb_strlen($person["svnr"]) == 12)
			{
				$last = substr($person["svnr"], 10, 12);
				if ($last{0} != "v" || !is_numeric($last{1}))
				{
					return $this->_error("SVNR ist ungueltig");
				}
			}
		}
		if (isset($person["ersatzkennzeichen"]) && mb_strlen($person["ersatzkennzeichen"]) > 10)
		{
			return $this->_error("Ersatzkennzeichen darf nicht laenger als 10 Zeichen sein");
		}
		if (isset($person["familienstand"]) && mb_strlen($person["familienstand"]) > 1)
		{
			return $this->_error("Familienstand ist ungueltig");
		}
		if (isset($person["anzahlkinder"]) && $person["anzahlkinder"] != "" && !is_numeric($person["anzahlkinder"]))
		{
			return $this->_error("Anzahl der Kinder ist ungueltig");
		}
		if (!isset($person["aktiv"]) || (isset($person["aktiv"]) && $person["aktiv"] != "t" && $person["aktiv"] != "f"))
		{
			return $this->_error("Aktiv ist ungueltig");
		}
		if (!isset($person["person_id"]) && isset($person["insertvon"]) && mb_strlen($person["insertvon"]) > 32)
		{
			return $this->_error("Insertvon darf nicht laenger als 32 Zeichen sein");
		}
		if (isset($person["updatevon"]) && mb_strlen($person["updatevon"]) > 32)
		{
			return $this->_error("Updatevon darf nicht laenger als 32 Zeichen sein");
		}
		if (!isset($person["geschlecht"]) || (isset($person["geschlecht"]) && mb_strlen($person["geschlecht"]) > 1))
		{
			return $this->_error("Geschlecht darf nicht laenger als 1 Zeichen sein");
		}
		if (isset($person["geburtsnation"]) && mb_strlen($person["geburtsnation"]) > 3)
		{
			return $this->_error("Geburtsnation darf nicht laenger als 3 Zeichen sein");
		}
		if (isset($person["staatsbuergerschaft"]) && mb_strlen($person["staatsbuergerschaft"]) > 3)
		{
			return $this->_error("Staatsbuergerschaft darf nicht laenger als 3 Zeichen sein");
		}
		if (isset($person["geschlecht"]) && $person["geschlecht"] != "m" && $person["geschlecht"] != "w" && $person["geschlecht"] != "u")
		{
			return $this->_error("Geschlecht muss w, m oder u sein!");
		}

		//Pruefen ob das Geburtsdatum mit der SVNR uebereinstimmt.
		if (isset($person["svnr"]) && isset($person["gebdatum"]) && $person["svnr"] != "" && $person["gebdatum"] != "")
		{
			if (mb_ereg("([0-9]{1,2}).([0-9]{1,2}).([0-9]{4})", $person["gebdatum"], $regs))
			{
				//$day = sprintf("%02s",$regs[1]);
				//$month = sprintf("%02s",$regs[2]);
				//$year = mb_substr($regs[3],2,2);
			}
			elseif (mb_ereg("([0-9]{4})-([0-9]{2})-([0-9]{2})", $person["gebdatum"], $regs))
			{
				//$day = sprintf("%02s",$regs[3]);
				//$month = sprintf("%02s",$regs[2]);
				//$year = mb_substr($regs[1],2,2);
			}
			else
			{
				return $this->_error("Format des Geburtsdatums ist ungueltig");
			}
		}

		return $this->_success("Input data are valid");
	}
}