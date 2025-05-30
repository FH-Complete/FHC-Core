<?php
/**
 * Copyright (C) 2024 fhcomplete.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

if (! defined('BASEPATH')) exit('No direct script access allowed');

class Profil extends FHCAPI_Controller
{

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		parent::__construct([
			'getView' => self::PERM_LOGGED,
            'fotoSperre' => self::PERM_LOGGED,
			'getGemeinden' => self::PERM_LOGGED,
			'getAllNationen' => self::PERM_LOGGED,
			'isMitarbeiter' => self::PERM_LOGGED,
			
		]);
		
		$this->load->library('PermissionLib');

		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('person/Profil_update_model', 'ProfilUpdateModel');
		$this->load->model('content/DmsVersion_model', 'DmsVersionModel');


		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	//------------------------------------------------------------------------------------------------------------------
	// Public methods

	
    /**
	 * function that returns the data used for the corresponding view
	 * the client side parses the @param $uid and calls this function to get the data to the correct view 
	 * @access public
	 * @param  boolean $uid the userID used to identify which information should be retrieved for which view 
	 * @return stdClass all the data corresponding to a view of a user
	 */
	public function getView($uid)
	{
		$res = new stdClass();
		$editAllowed = getAuthUID() == $uid || $this->permissionlib->isBerechtigt('admin');

		// if parsing the URL did not found a UID then the UID of the logged in user is used
		if ($uid == "Profil" || $uid == $this->uid) {
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($this->uid);
			if (isError($isMitarbeiter)) {
				show_error("error while checking if UID: " . $this->uid . " is a mitarbeiter");
			}
			$isMitarbeiter = getData($isMitarbeiter);
			if ($isMitarbeiter) {
				$res->view = "MitarbeiterProfil";
				$res->data = $this->mitarbeiterProfil();
				$res->data->pid = $this->pid;
			} else {
				$res->view = "StudentProfil";
				$res->data = $this->studentProfil();
				$res->data->pid = $this->pid;
			}
			// editing your own profil - true
			$editAllowed = true;
		}
		// UID is availabe when accessing Profil/View/:uid
		else {
			$this->PersonModel->addSelect(["person_id"]);
			$pid = $this->PersonModel->getByUid($uid);
			if (isError($pid)) {
				show_error("error while trying to update table public.tbl_person while searching for a person with UID: " . $uid);
			}
			$pid = hasData($pid) ? getData($pid)[0] : null;
			if (!$pid) {
				show_error("Person with UID: " . $uid . " does not exist");
			}
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($uid);
			if (isError($isMitarbeiter)) {
				show_error("error while checking if UID: " . $uid . " is a mitarbeiter");
			}
			$isMitarbeiter = getData($isMitarbeiter);
			if ($isMitarbeiter) {
				$res->view = "ViewMitarbeiterProfil";
				$res->data = $this->viewMitarbeiterProfil($uid);

			} else {
				$res->view = "ViewStudentProfil";
				$res->data = $this->viewStudentProfil($uid);
			}
		}
		$res->data->editAllowed = $editAllowed;
		$this->terminateWithSuccess($res);
	}

    /**
	 * update column foto_sperre in public.tbl_person
	 * @access public
	 * @param  boolean $value  new value for the column
	 * @return boolean the new value added to the column in public.tbl_person
	 */
	public function fotoSperre($value)
	{
        if(!isset($value)){
            $this->terminateWithError("Missing parameter", self::ERROR_TYPE_GENERAL);
        }

		$res = $this->PersonModel->update($this->pid, ["foto_sperre" => $value]);
		if (isError($res)) {
			show_error("error while trying to update table public.tbl_person");
		}
		$this->PersonModel->addSelect("foto_sperre");
		$res = $this->PersonModel->load($this->pid);
		if (isError($res)) {
			show_error("error while trying to query table public.tbl_person");
		}

        $res = $this->getDataOrTerminateWithError($res);
		
        $this->terminateWithSuccess(current($res));
	}

	/**
	 * gets all nations in the table bis.tbl_nation
	 *
	 * @access public
	 * @return array all the nations in table bis.tbl_nation
	 */
	public function getAllNationen()
	{
		// load the nationen from the database
		$this->load->model('codex/Nation_model', "NationModel");
		$this->NationModel->addSelect(["nation_code as code", "langtext"]);
		$nation_res = $this->NationModel->load();

		if (isError($nation_res)) {
			$this->terminateWithError("error while trying to query table codex.tbl_nation", self::ERROR_TYPE_GENERAL);
		}
		
		$nation_res = $this->getDataOrTerminateWithError($nation_res);

		$this->terminateWithSuccess($nation_res);
	}

	public function getGemeinden($nation, $zip)
	{
		if(!isset($nation) || !isset($zip)){
			echo json_encode(error("Missing parameters"));
			return;
		}
		
		$this->load->model('codex/Gemeinde_model', "GemeindeModel");
		

		$gemeinde_res = $this->GemeindeModel->getGemeindeByPlz($zip);
		
		if (isError($gemeinde_res)) {
			$this->terminateWithError(getError($gemeinde_res),self::ERROR_TYPE_GENERAL);
		}
		$gemeinde_res = $this->getDataOrTerminateWithError($gemeinde_res);
		
		/* $gemeinde_res = array_map(function ($obj) {
			return $obj->ortschaftsname;
		}, $gemeinde_res); */

		$this->terminateWithSuccess($gemeinde_res);	
		
	}


    // -----------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * function that returns the data used for viewing another mitarbeiter profile
	 * @access private 
	 * @param  integer $uid the userID to retrieve the mitarbeiter data
	 * @return stdClass restricted mitarbeiter data
	 */
	private function viewMitarbeiterProfil($uid)
	{
		$mailverteiler_res = $this->getMailverteiler($uid);
		$benutzer_funktion_res = $this->getBenutzerFunktion($uid);
		$benutzer_res = $this->getBenutzerAlias($uid);
		$person_res = $this->getPersonInfo($uid);
		$mitarbeiter_res = $this->getMitarbeiterInfo($uid);
		$telefon_res = $this->getTelefonInfo($uid);

		$res = new stdClass();
		$res->username = $uid;

		//? Person Info
		foreach ($person_res as $key => $val) {
			$res->$key = $val;
		}

		//? Mitarbeiter Info
		foreach ($mitarbeiter_res as $key => $val) {
			$res->$key = $val;

		}

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $uid . "@" . DOMAIN;
		$extern_email = array();
		$extern_email["type"] = "alias";
		$extern_email["email"] = $benutzer_res->alias . "@" . DOMAIN;
		$res->emails = array($intern_email, $extern_email);

		$res->funktionen = $benutzer_funktion_res;
		$res->mailverteiler = $mailverteiler_res;
		$res->standort_telefon = isset($telefon_res) ? $telefon_res->kontakt : null;

		return $res;
	}

	/**
	 * function that returns the data used for viewing another student profile
	 * @access private 
	 * @param  integer $uid the userID to retrieve the student data
	 * @return stdClass restricted student data
	 */
	private function viewStudentProfil($uid)
	{
		$mailverteiler_res = $this->getMailverteiler($uid);
		$person_res = $this->getPersonInfo($uid);
		$student_res = $this->getStudentInfo($uid);
		$matr_res = $this->getMatrikelNummer($uid);

		$res = new stdClass();
		$res->username = $uid;

		//? Person Information
		foreach ($person_res as $key => $value) {
			$res->$key = $value;
		}

		//? Student Information
		foreach ($student_res as $key => $value) {
			$res->$key = $value;
		}

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $uid . "@" . DOMAIN;

		$res->emails = [$intern_email];
		$res->matrikelnummer = $matr_res->matr_nr;
		$res->mailverteiler = $mailverteiler_res;

		return $res;
	}

	/**
	 * checks whether a specific userID is a mitarbeiter or not (foreword declaration of the function isMitarbeiter in Mitarbeiter_model.php)
	 * @access public
	 * @param  $uid the userID used to check if it is a mitarbeiter
	 * @return boolean 
	 */
	public function isMitarbeiter($uid)
	{

		if(!$uid) $this->terminateWithError("No uid provided", self::ERROR_TYPE_GENERAL);
		
		
		$result = $this->MitarbeiterModel->isMitarbeiter($uid);
		
		if (isError($result)) {
			$this->terminateWithError("error when calling Mitarbeiter_model function isMitarbeiter with uid " . $uid, self::ERROR_TYPE_GENERAL);
		}

		$result = $this->getDataOrTerminateWithError($result);
		
		$this->terminateWithSuccess($result);
	}

	/**
	 * function that returns the data used for the mitarbeiter profile
	 * @access private 
	 * @return stdClass mitarbeiter data
	 */
	private function mitarbeiterProfil()
	{

		$zutrittskarte_ausgegebenam = $this->getZutrittskarteDatum($this->uid);
		$adresse_res = $this->getAdressenInfo($this->pid);
		$kontakte_res = $this->getKontaktInfo($this->pid);
		$mailverteiler_res = $this->getMailverteiler($this->uid);
		$person_res = $this->getPersonInfo($this->uid, true);
		$benutzer_funktion_res = $this->getBenutzerFunktion($this->uid);
		$betriebsmittelperson_res = $this->getBetriebsmittelInfo($this->pid);
		$profilUpdates = $this->getProfilUpdates($this->uid);
		$telefon_res = $this->getTelefonInfo($this->uid);
		$mitarbeiter_res = $this->getMitarbeiterInfo($this->uid);

		$res = new stdClass();
		$res->username = $this->uid;

		//? Person Information
		foreach ($person_res as $key => $value) {
			$res->$key = $value;
		}

		//? Mitarbeiter Information
		foreach ($mitarbeiter_res as $key => $value) {
			$res->$key = $value;
		}

		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;
		$res->mailverteiler = $mailverteiler_res;

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $this->uid . "@" . DOMAIN;
		$extern_email = array();
		$extern_email["type"] = "alias";
		$extern_email["email"] = $mitarbeiter_res->alias . "@" . DOMAIN;
		$res->emails = [$intern_email, $extern_email];

		$res->funktionen = $benutzer_funktion_res;
		$res->standort_telefon = $telefon_res;
		$res->profilUpdates = $profilUpdates;

		return $res;
	}

	/**
	 * function that returns the data used for the student profile
	 * @access private 
	 * @return stdClass student data
	 */
	private function studentProfil()
	{
		$betriebsmittelperson_res = $this->getBetriebsmittelInfo($this->pid);
		$kontakte_res = $this->getKontaktInfo($this->pid);
		$zutrittskarte_ausgegebenam = $this->getZutrittskarteDatum($this->uid);
		$adresse_res = $this->getAdressenInfo($this->pid);
		$mailverteiler_res = $this->getMailverteiler($this->uid);
		$person_res = $this->getPersonInfo($this->uid, true);
		$zutrittsgruppe_res = $this->getZutrittsgruppen($this->uid);
		$student_res = $this->getStudentInfo($this->uid);
		$matr_res = $this->getMatrikelNummer($this->uid);
		$profilUpdates = $this->getProfilUpdates($this->uid);

		$res = new stdClass();
		$res->username = $this->uid;

		//? Person Information
		foreach ($person_res as $key => $value) {
			$res->$key = $value;
		}

		//? Student Information
		foreach ($student_res as $key => $value) {
			$res->$key = trim($value);
		}

		$intern_email = array();
		$intern_email["type"] = "intern";
		$intern_email["email"] = $this->uid . "@" . DOMAIN;

		$res->emails = [$intern_email];
		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;
		$res->matrikelnummer = $matr_res->matr_nr;
		$res->zuttritsgruppen = $zutrittsgruppe_res;
		$res->mailverteiler = $mailverteiler_res;
		$res->profilUpdates = $profilUpdates;

		return $res;
	}


    /**
	 * gets all the mailverteiler using the tables: tbl_benutzer, tbl_benutzergruppe, tbl_gruppe
	 * @access private
	 * @param  integer $uid  the userID used to retrieve the mailverteiler 
	 * @return array returns the mailvertailer corresponding to a userID 
	 */
	private function getMailverteiler($uid)
	{
		$this->PersonModel->addSelect('gruppe_kurzbz, beschreibung');
		$this->PersonModel->addJoin('tbl_benutzer', 'person_id');
		$this->PersonModel->addJoin('tbl_benutzergruppe', 'uid');
		$this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz');

		$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $uid));
		if (isError($mailverteiler_res)) {
			show_error("was not able to query the table public.tbl_benutzer:" . getData($mailverteiler_res));
		}
		$mailverteiler_res = hasData($mailverteiler_res) ? getData($mailverteiler_res) : null;
		$mailverteiler_res = array_map(function ($element) {
			$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
			return $element;
		}, $mailverteiler_res);
		return $mailverteiler_res;
	}

	/**
	 * gets all the Benutzerfunktionen of a corresponding user
	 * @access private
	 * @param  integer $uid  the userID used to retrieve the Benutzerfunktionen 
	 * @return array returns the Benutzerfunktionen corresponding to a userID 
	 */
	private function getBenutzerFunktion($uid)
	{
		$this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung", "tbl_organisationseinheit.bezeichnung as Organisationseinheit", "datum_von as Gültig_von", "datum_bis as Gültig_bis", "wochenstunden as Wochenstunden"]);
		$this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit", "oe_kurzbz");

		$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid' => $uid));
		if (isError($benutzer_funktion_res)) {
			show_error("was not able to query the table public.tbl_benutzerfunktion:" . getData($benutzer_funktion_res));
		}
		$benutzer_funktion_res = hasData($benutzer_funktion_res) ? getData($benutzer_funktion_res) : null;
		return $benutzer_funktion_res;
	}

	/**
	 * gets all the Betriebsmittel of a corresponding user
	 * @access private
	 * @param  integer $uid  the userID used to retrieve the Betriebsmittel 
	 * @return array returns the Betriebsmittel corresponding to a userID 
	 */
	private function getBetriebsmittelInfo($pid)
	{
		$this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel", "nummer as Nummer", "ausgegebenam as Ausgegeben_am"]);

		//? betriebsmittel are not needed in a view
		$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel($pid);
		if (isError($betriebsmittelperson_res)) {
			show_error("was not able to query the table public.tbl_betriebsmittelperson:" . getData($betriebsmittelperson_res));
		}
		$betriebsmittelperson_res = hasData($betriebsmittelperson_res) ? getData($betriebsmittelperson_res) : null;
		return $betriebsmittelperson_res;
	}

	/**
	 * gets the alias of a corresponding user
	 * @access private
	 * @param  integer $uid the userID used to get the alias
	 * @return string the alias of the userID
	 */
	private function getBenutzerAlias($uid)
	{
		$this->BenutzerModel->addSelect(["alias"]);
		$benutzer_res = $this->BenutzerModel->load([$uid]);
		if (isError($benutzer_res)) {
			show_error("was not able to query the table public.tbl_benutzer:" . getData($benutzer_res));
		} else {
			$benutzer_res = hasData($benutzer_res) ? getData($benutzer_res)[0] : null;
		}

		return $benutzer_res;
	}

	/**
	 * gets the person information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the person information
	 * @param  integer $geburtsInfo flag wether to add the columns gebort, gebdatum, foto_sperre or not
	 * @return array all the person informaion corresponding to a userID
	 */
	private function getPersonInfo($uid, $geburtsInfo = null)
	{
		$selectClause = ["foto", "foto_sperre", "anrede", "titelpost as postnomen", "titelpre as titel", "vorname", "nachname"];
		/** @param integer $geburtsInfo */
		if ($geburtsInfo) {
			array_push($selectClause, "gebort");
			array_push($selectClause, "TO_CHAR(gebdatum, 'DD.MM.YYYY') as gebdatum");
		}
		$this->BenutzerModel->addSelect($selectClause);
		$this->BenutzerModel->addJoin("tbl_person", "person_id");

		$person_res = $this->BenutzerModel->load([$uid]);
		if (isError($person_res)) {
			show_error("was not able to query the table public.tbl_benutzer:" . getData($person_res));
		} else {
			$person_res = hasData($person_res) ? getData($person_res)[0] : null;
		}

		if( ($person_res->foto === null) || (($this->uid !== $uid) && ($person_res->foto_sperre !== false)) )
		{
			$dummy_foto = base64_encode(file_get_contents(DOC_ROOT.'skin/images/profilbild_dummy.jpg'));
			$person_res->foto = $dummy_foto;
		}

		return $person_res;
	}

	/**
	 * gets the mitarbeiter information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the mitarbeiter information
	 * @return array all the mitarbeiter informaion corresponding to a userID
	 */
	private function getMitarbeiterInfo($uid)
	{
		$this->MitarbeiterModel->addSelect(["kurzbz", "telefonklappe", "alias", "ort_kurzbz"]);
		$this->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid");
		$mitarbeiter_res = $this->MitarbeiterModel->load($uid);
		if (isError($mitarbeiter_res)) {
			show_error("was not able to query the table public.tbl_mitarbeiter:" . getData($mitarbeiter_res));
		} else {
			$mitarbeiter_res = hasData($mitarbeiter_res) ? getData($mitarbeiter_res)[0] : null;
		}

		return $mitarbeiter_res;
	}

	/**
	 * gets the telefon information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the telefon information
	 * @return array all the telefon informaion corresponding to a userID
	 */
	private function getTelefonInfo($uid)
	{
		$this->MitarbeiterModel->addSelect(["kontakt"]);
		$this->MitarbeiterModel->addJoin("tbl_kontakt", "tbl_mitarbeiter.standort_id = tbl_kontakt.standort_id");
		$this->MitarbeiterModel->addLimit(1);
		$telefon_res = $this->MitarbeiterModel->loadWhere(["mitarbeiter_uid" => $uid, "kontakttyp" => "telefon"]);
		if (isError($telefon_res)) {
			show_error("was not able to query the table public.tbl_mitarbeiter:" . getData($telefon_res));
		}
		$telefon_res = hasData($telefon_res) ? getData($telefon_res)[0] : null;
		return $telefon_res;
	}

	/**
	 * gets the student information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the student information
	 * @return array all the student informaion corresponding to a userID
	 */
	private function getStudentInfo($uid)
	{
		$this->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang', 'tbl_studiengang.studiengang_kz as studiengang_kz', 'tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe', 'tbl_student.matrikelnr as personenkennzeichen']);
		$this->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz");

		$student_res = $this->StudentModel->load([$uid]);
		if (isError($student_res)) {
			show_error("was not able to query the table public.tbl_student:" . getData($student_res));
		}
		$student_res = hasData($student_res) ? getData($student_res)[0] : null;
		return $student_res;
	}

	/**
	 * gets the profil updates corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the profil updates
	 * @return array all the profil updates corresponding to a userID
	 */
	private function getProfilUpdates($uid)
	{
		$profilUpdates = $this->ProfilUpdateModel->getProfilUpdatesWhere(['uid' => $uid]);
		if (isError($profilUpdates)) {
			show_error("was not able to query the table public.tbl_profil_update:" . getData($profilUpdates));
		}
		$profilUpdates = hasData($profilUpdates) ? getData($profilUpdates) : null;
		return $profilUpdates;
	}

	/**
	 * gets the Matrikelnummer corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the Matrikelnummer
	 * @return integer the Matrikelnummer corresponding to a userID
	 */
	private function getMatrikelNummer($uid)
	{
		$this->BenutzerModel->addSelect(["matr_nr"]);
		$this->BenutzerModel->addJoin("tbl_person", "person_id");

		$matr_res = $this->BenutzerModel->load([$uid]);
		if (isError($matr_res)) {
			show_error("was not able to query the table public.tbl_benutzer:" . getData($matr_res));
		}
		$matr_res = hasData($matr_res) ? getData($matr_res)[0] : [];
		return $matr_res;
	}

	/**
	 * gets the Zutrittsgruppen corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the Zutrittsgruppen
	 * @return array all the Zutrittsgruppen corresponding to a userID
	 */
	private function getZutrittsgruppen($uid)
	{
		$this->BenutzergruppeModel->addSelect(['bezeichnung']);
		$this->BenutzergruppeModel->addJoin('tbl_gruppe', 'gruppe_kurzbz');

		$zutrittsgruppe_res = $this->BenutzergruppeModel->loadWhere(array("uid" => $uid, "zutrittssystem" => true));
		if (isError($zutrittsgruppe_res)) {
			show_error("was not able to query the table public.tbl_benutzergruppe:" . getData($zutrittsgruppe_res));
		}
		$zutrittsgruppe_res = hasData($zutrittsgruppe_res) ? getData($zutrittsgruppe_res) : null;
		return $zutrittsgruppe_res;
	}

	/**
	 * gets the address information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the address information
	 * @return array all the address information corresponding to a userID
	 */
	private function getAdressenInfo($pid)
	{
		$adresse_res = $this->AdresseModel->addSelect(["adresse_id", "strasse", "tbl_adressentyp.bezeichnung as typ", "plz", "ort", "zustelladresse", "gemeinde", "nation"]);
		$adresse_res = $this->AdresseModel->addOrder("zustelladresse", "DESC");
		$adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp", "typ=adressentyp_kurzbz");

		$adresse_res = $this->AdresseModel->loadWhere(["person_id" => $pid]);
		if (isError($adresse_res)) {
			show_error("was not able to query the table public.tbl_adresse:" . getData($adresse_res));
		}
		$adresse_res = hasData($adresse_res) ? getData($adresse_res) : null;
		return $adresse_res;
	}

	/**
	 * gets the kontakt information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the kontakt information
	 * @return array all the kontakt information corresponding to a userID
	 */
	private function getKontaktInfo($pid)
	{
		$this->KontaktModel->addSelect(['kontakttyp', 'kontakt_id', 'kontakt', 'tbl_kontakt.anmerkung', 'tbl_kontakt.zustellung']);
		$this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
		$this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT');
		$this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum');

		$kontakte_res = $this->KontaktModel->loadWhere(['person_id' => $pid]);
		if (isError($kontakte_res)) {
			show_error("was not able to query the table public.tbl_kontakt:" . getData($kontakte_res));
		}
		$kontakte_res = hasData($kontakte_res) ? getData($kontakte_res) : null;
		return $kontakte_res;
	}

	/**
	 * gets the date of issue of the FH access card corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the date of issue of the FH access card
	 * @return string the date of issue of the FH access card corresponding to a userID
	 */
	private function getZutrittskarteDatum($uid)
	{
		$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($uid, "Zutrittskarte");
		if (isError($zutrittskarte_ausgegebenam)) {
			show_error("was not able to query the table wavi.tbl_bentriebsmittelperson:" . getData($zutrittskarte_ausgegebenam));
		}
		$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam) ? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;

		//? formats date from 01-01-2000 to 01.01.2000
		$zutrittskarte_ausgegebenam = str_replace("-", ".", $zutrittskarte_ausgegebenam);
		return $zutrittskarte_ausgegebenam;
	}
}

