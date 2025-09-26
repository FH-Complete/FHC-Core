<?php

if (! defined("BASEPATH")) exit("No direct script access allowed");

class ProfilLib{
	public function __construct()
	{
		$this->ci =& get_instance();

		
	}

	public function getView($uid)
	{
		// loading required models
		$this->ci->load->model("ressource/Mitarbeiter_model","MitarbeiterModel");
		$this->ci->load->model("person/Person_model","PersonModel");

		$res = new stdClass();

		// checking the uid
		if ($uid == getAuthUID()) {
			$isMitarbeiter = $this->ci->MitarbeiterModel->isMitarbeiter(getAuthUID());
			if(isError($isMitarbeiter))
			{
				return error(getData($isMitarbeiter));
			}
			$isMitarbeiter = getData($isMitarbeiter);
			if ($isMitarbeiter) {
				$res->view = "MitarbeiterProfil";
				$res->data = $this->mitarbeiterProfil();
				$res->data->pid = getAuthPersonId();
			} else {
				$res->view = "StudentProfil";
				$res->data = $this->studentProfil();
				$res->data->pid = getAuthPersonId();
			}
			$res->data->fotoStatus=$this->isFotoAkzeptiert(getAuthPersonId());
		}
		// UID is availabe when accessing Profil/View/:uid
		else {
			$isMitarbeiter = $this->ci->MitarbeiterModel->isMitarbeiter($uid);
			if(isError($isMitarbeiter))
			{
				return error(getData($isMitarbeiter));
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
		
		return success($res);
	}

	//PRIVATE METHODS ###############################################

	/**
	 * function that returns the data used for the student profile
	 * @access private 
	 * @return stdClass student data
	 */
	private function studentProfil()
	{
		$pid = getAuthPersonId();
		$uid = getAuthUID();
		$betriebsmittelperson_res = $this->getBetriebsmittelInfo($pid);
		$kontakte_res = $this->getKontaktInfo($pid);
		$zutrittskarte_ausgegebenam = $this->getZutrittskarteDatum($uid);
		$adresse_res = $this->getAdressenInfo($pid);
		$mailverteiler_res = $this->getMailverteiler($uid);
		$person_res = $this->getPersonInfo($uid, true);
		$zutrittsgruppe_res = $this->getZutrittsgruppen($uid);
		$student_res = $this->getStudentInfo($uid);
		$matr_res = $this->getMatrikelNummer($uid);
		$profilUpdates = $this->getProfilUpdates($uid);

		$res = new stdClass();
		$res->username = $uid;

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
		$intern_email["email"] = DOMAIN? $uid . "@" . DOMAIN :"";

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
	 * function that returns the data used for the mitarbeiter profile
	 * @access private 
	 * @return stdClass mitarbeiter data
	 */
	private function mitarbeiterProfil()
	{
		$pid = getAuthPersonId();
		$uid = getAuthUID();
		$zutrittskarte_ausgegebenam = $this->getZutrittskarteDatum($uid);
		$adresse_res = $this->getAdressenInfo($pid);
		$kontakte_res = $this->getKontaktInfo($pid);
		$mailverteiler_res = $this->getMailverteiler($uid);
		$person_res = $this->getPersonInfo($uid, true);
		$benutzer_funktion_res = $this->getBenutzerFunktion($uid);
		$betriebsmittelperson_res = $this->getBetriebsmittelInfo($pid);
		$profilUpdates = $this->getProfilUpdates($uid);
		$telefon_res = $this->getTelefonInfo($uid);
		$mitarbeiter_res = $this->getMitarbeiterInfo($uid);

		$res = new stdClass();
		$res->username = $uid;

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
		$intern_email["email"] = DOMAIN? $uid . "@" . DOMAIN : "";
		$extern_email = array();
		$extern_email["type"] = "alias";
		
		$extern_email["email"] = $mitarbeiter_res->alias? ($mitarbeiter_res->alias . "@" . DOMAIN) : null;
		$res->emails = $extern_email["email"]?[$intern_email, $extern_email]:[$intern_email];

		$res->funktionen = $benutzer_funktion_res;
		$res->standort_telefon = $telefon_res;
		$res->profilUpdates = $profilUpdates;

		return $res;
	}

	/**
	 * gets the date of issue of the FH access card corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the date of issue of the FH access card
	 * @return string the date of issue of the FH access card corresponding to a userID
	 */
	private function getZutrittskarteDatum($uid)
	{
		$this->ci->load->model("ressource/Betriebsmittelperson_model","BetriebsmittelpersonModel");
		$zutrittskarte_ausgegebenam = $this->ci->BetriebsmittelpersonModel->getBetriebsmittelByUid($uid, "Zutrittskarte");
		
		if(isError($zutrittskarte_ausgegebenam)){
			return error(getData($zutrittskarte_ausgegebenam));
		}
		$zutrittskarte_ausgegebenam = getData($zutrittskarte_ausgegebenam);
		$zutrittskarte_ausgegebenam = $zutrittskarte_ausgegebenam ? current($zutrittskarte_ausgegebenam)->ausgegebenam : null;
		
		//? formats date from 01-01-2000 to 01.01.2000
		$zutrittskarte_ausgegebenam = str_replace("-", ".", $zutrittskarte_ausgegebenam);
		return $zutrittskarte_ausgegebenam;
	}

	/**
	 * gets the address information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the address information
	 * @return array all the address information corresponding to a userID
	 */
	private function getAdressenInfo($pid)
	{
		$this->ci->load->model("person/Adresse_model","AdresseModel");
		$adresse_res = $this->ci->AdresseModel->addSelect(["adresse_id", "strasse", "tbl_adressentyp.bezeichnung as typ", "plz", "ort", "zustelladresse", "gemeinde", "nation"]);
		$adresse_res = $this->ci->AdresseModel->addOrder("zustelladresse", "DESC");
		$adresse_res = $this->ci->AdresseModel->addJoin("tbl_adressentyp", "typ=adressentyp_kurzbz");

		$adresse_res = $this->ci->AdresseModel->loadWhere(["person_id" => $pid]);
		if(isError($adresse_res)){
			return error(getData($adresse_res));
		}
		$adresse_res = getData($adresse_res) ?? [];
		return $adresse_res;
	}

	/**
	 * gets the kontakt information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the kontakt information
	 * @return array all the kontakt information corresponding to a userID
	 */
	private function getKontaktInfo($pid, $includehidden=false)
	{
		$this->ci->load->model("person/Kontakt_model","KontaktModel");
		$this->ci->KontaktModel->addSelect(['kontakttyp', 'kontakt_id', 'kontakt', 'tbl_kontakt.anmerkung', 'tbl_kontakt.zustellung']);
		$this->ci->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
		$this->ci->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT');
		$this->ci->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum');

		$params = array('person_id' => $pid);
		if(!$includehidden)
		{
			$params['kontakttyp <>'] = 'hidden';
		}

		$kontakte_res = $this->ci->KontaktModel->loadWhere($params);
		if(isError($kontakte_res)){
			return error(getData($kontakte_res));
		}
		$kontakte_res = getData($kontakte_res);
		return $kontakte_res;
	}

	/**
	 * gets all the mailverteiler using the tables: tbl_benutzer, tbl_benutzergruppe, tbl_gruppe
	 * @access private
	 * @param  integer $uid  the userID used to retrieve the mailverteiler 
	 * @return array returns the mailvertailer corresponding to a userID 
	 */
	private function getMailverteiler($uid)
	{
		$this->ci->load->model("person/Person_model","PersonModel");
		$this->ci->PersonModel->addSelect('gruppe_kurzbz, beschreibung');
		$this->ci->PersonModel->addJoin('tbl_benutzer', 'person_id');
		$this->ci->PersonModel->addJoin('tbl_benutzergruppe', 'uid');
		$this->ci->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz');

		$mailverteiler_res = $this->ci->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $uid));
		if(isError($mailverteiler_res)){
			return error(getData($mailverteiler_res));
		}
		$mailverteiler_res = getData($mailverteiler_res) ?? [];
		$mailverteiler_res = gettype($mailverteiler_res) === 'array' ? $mailverteiler_res : [];
		$mailverteiler_res = array_map(function ($element) {
			$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
			return $element;
		}, $mailverteiler_res);
		return $mailverteiler_res;
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
		$this->ci->load->model("person/Benutzer_model","BenutzerModel");
		$selectClause = ["foto", "foto_sperre", "anrede", "titelpost as postnomen", "titelpre as titel", "vorname", "nachname"];
		/** @param integer $geburtsInfo */
		if ($geburtsInfo) {
			array_push($selectClause, "gebort");
			array_push($selectClause, "TO_CHAR(gebdatum, 'DD.MM.YYYY') as gebdatum");
		}
		$this->ci->BenutzerModel->addSelect($selectClause);
		$this->ci->BenutzerModel->addJoin("tbl_person", "person_id");

		$person_res = $this->ci->BenutzerModel->load([$uid]);
		if(isError($person_res)){
			return error(getData($person_res));
		}
		$person_res = getData($person_res);
		$person_res = $person_res ? current($person_res) : null;

		if(isset($person_res)){
			if( ($person_res->foto === null) || ((getAuthUID() !== $uid) && ($person_res->foto_sperre !== false)) )
			{
				$dummy_foto = base64_encode(file_get_contents(DOC_ROOT.'skin/images/profilbild_dummy.jpg'));
				$person_res->foto = $dummy_foto;
			}
		}

		return $person_res;
	}

	/**
	 * gets all the Benutzerfunktionen of a corresponding user
	 * @access private
	 * @param  integer $uid  the userID used to retrieve the Benutzerfunktionen 
	 * @return array returns the Benutzerfunktionen corresponding to a userID 
	 */
	private function getBenutzerFunktion($uid)
	{
		$this->ci->load->model("person/Benutzerfunktion_model","BenutzerfunktionModel");
		$this->ci->BenutzerfunktionModel->addSelect([
			"CASE WHEN (tbl_benutzerfunktion.bezeichnung IS NOT NULL AND tbl_benutzerfunktion.bezeichnung <> '' AND tbl_benutzerfunktion.bezeichnung <> tbl_funktion.beschreibung) THEN tbl_funktion.beschreibung || ' - ' || tbl_benutzerfunktion.bezeichnung ELSE tbl_funktion.beschreibung END as \"Bezeichnung\"",
			"tbl_organisationseinheit.bezeichnung as Organisationseinheit",
			"datum_von as Gültig_von",
			"datum_bis as Gültig_bis",
			"COALESCE(wochenstunden, '0'::numeric(5,2)) AS \"Wochenstunden\""
		]);
		$this->ci->BenutzerfunktionModel->addJoin("tbl_funktion", "funktion_kurzbz");
		$this->ci->BenutzerfunktionModel->addJoin("tbl_organisationseinheit", "oe_kurzbz");

		$benutzer_funktion_res = $this->ci->BenutzerfunktionModel->loadWhere(
			array(
				'uid' => $uid,
				'NOW()::date BETWEEN COALESCE(datum_von, \'1970-01-01\'::date) AND COALESCE(datum_bis, \'2170-12-01\'::date)' => null
			)
		);
		if(isError($benutzer_funktion_res)){
			return error(getData($benutzer_funktion_res));
		}
		$benutzer_funktion_res = getData($benutzer_funktion_res);
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
		$this->ci->load->model("ressource/Betriebsmittelperson_model","BetriebsmittelpersonModel");
		$this->ci->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel", "nummer as Nummer", "ausgegebenam as Ausgegeben_am"]);

		//? betriebsmittel are not needed in a view
		$betriebsmittelperson_res = $this->ci->BetriebsmittelpersonModel->getBetriebsmittel($pid);
		if(isError($betriebsmittelperson_res)){
			return error(getData($betriebsmittelperson_res));
		}
		$betriebsmittelperson_res = getData($betriebsmittelperson_res);
		return $betriebsmittelperson_res;
	}

	/**
	 * gets the profil updates corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the profil updates
	 * @return array all the profil updates corresponding to a userID
	 */
	private function getProfilUpdates($uid)
	{
		$this->ci->load->model("person/Profil_update_model","ProfilUpdateModel");
		$profilUpdates = $this->ci->ProfilUpdateModel->getProfilUpdatesWhere(['uid' => $uid]);
		if(isError($profilUpdates)){
			return error(getData($profilUpdates));
		}
		$profilUpdates = getData($profilUpdates);
		return $profilUpdates;
	}

	/**
	 * gets the telefon information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the telefon information
	 * @return array all the telefon informaion corresponding to a userID
	 */
	private function getTelefonInfo($uid)
	{
		$this->ci->load->model("ressource/Mitarbeiter_model","MitarbeiterModel");
		$this->ci->MitarbeiterModel->addSelect(["kontakt"]);
		$this->ci->MitarbeiterModel->addJoin("tbl_kontakt", "tbl_mitarbeiter.standort_id = tbl_kontakt.standort_id");
		$this->ci->MitarbeiterModel->addLimit(1);
		$telefon_res = $this->ci->MitarbeiterModel->loadWhere(["mitarbeiter_uid" => $uid, "kontakttyp" => "telefon"]);
		if(isError($telefon_res)){
			return error(getData($telefon_res));
		}
		$telefon_res = getData($telefon_res);
		$telefon_res = $telefon_res ? current($telefon_res) : null;
		return $telefon_res;
	}

	/**
	 * gets the mitarbeiter information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the mitarbeiter information
	 * @return array all the mitarbeiter informaion corresponding to a userID
	 */
	private function getMitarbeiterInfo($uid)
	{
		$this->ci->load->model("ressource/Mitarbeiter_model","MitarbeiterModel");
		$this->ci->MitarbeiterModel->addSelect(["kurzbz", "telefonklappe", "alias", "ort_kurzbz"]);
		$this->ci->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid");
		$mitarbeiter_res = $this->ci->MitarbeiterModel->load($uid);
		if(isError($mitarbeiter_res)){
			return error(getData($mitarbeiter_res));
		}
		$mitarbeiter_res = getData($mitarbeiter_res);
		$mitarbeiter_res = $mitarbeiter_res ? current($mitarbeiter_res) : null;

		return $mitarbeiter_res;
	}

	/**
	 * gets the Zutrittsgruppen corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the Zutrittsgruppen
	 * @return array all the Zutrittsgruppen corresponding to a userID
	 */
	private function getZutrittsgruppen($uid)
	{
		$this->ci->load->model("person/Benutzergruppe_model","BenutzergruppeModel");
		$this->ci->BenutzergruppeModel->addSelect(['bezeichnung']);
		$this->ci->BenutzergruppeModel->addJoin('tbl_gruppe', 'gruppe_kurzbz');

		$zutrittsgruppe_res = $this->ci->BenutzergruppeModel->loadWhere(array("uid" => $uid, "zutrittssystem" => true));
		if(isError($zutrittsgruppe_res)){
			return error(getData($zutrittsgruppe_res));	
		}
		$zutrittsgruppe_res = getData($zutrittsgruppe_res);
		return $zutrittsgruppe_res;
	}

	/**
	 * gets the student information corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the student information
	 * @return array all the student informaion corresponding to a userID
	 */
	private function getStudentInfo($uid)
	{
		$this->ci->load->model("crm/Student_model","StudentModel");
		$this->ci->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang', 'tbl_studiengang.studiengang_kz as studiengang_kz', 'tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe', 'tbl_student.matrikelnr as personenkennzeichen']);
		$this->ci->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz");

		$student_res = $this->ci->StudentModel->load([$uid]);
		
		if(isError($student_res)){
			return error(getData($student_res));
		}
		$student_res = getData($student_res);
		$student_res = $student_res ? current($student_res) : null;
		return $student_res;
	}

	/**
	 * gets the Matrikelnummer corresponding to a user
	 * @access private
	 * @param  integer $uid the userID used to get the Matrikelnummer
	 * @return object the Matrikelnummer corresponding to a userID
	 */
	private function getMatrikelNummer($uid)
	{
		$this->ci->load->model("person/Benutzer_model","BenutzerModel");
		$this->ci->BenutzerModel->addSelect(["matr_nr"]);
		$this->ci->BenutzerModel->addJoin("tbl_person", "person_id");

		$matr_res = $this->ci->BenutzerModel->load([$uid]);
		
		if(isError($matr_res)){
			return error(getData($matr_res));
		}
		$matr_res = getData($matr_res);
		$matr_res = $matr_res ? current($matr_res) : [];
		return $matr_res;
	}

	/**
	 * checks whether the foto of a user is accepted or not
	 * @access private
	 * @param  integer $pid the personId of the student or mitarbeiter
	 * @return bool whether the foto is accepted or not
	 */
	private function isFotoAkzeptiert($pid)
	{
		$this->ci->load->model('person/Fotostatusperson_model','FotostatusModel');
		$fotostatus = $this->ci->FotostatusModel->execReadOnlyQuery("
		select distinct on (person_id) person_id, insertamum, fotostatus_kurzbz
		from public.tbl_person_fotostatus
		where person_id = ? 
		order by person_id, insertamum desc",[$pid]);
		if(isError($fotostatus)){
			return error(getData($fotostatus));
		}
		$fotostatus = getData($fotostatus);
		if(is_array($fotostatus) && count($fotostatus) > 0){
			$fotostatus = current($fotostatus)->fotostatus_kurzbz == 'akzeptiert';
		}
		else
			$fotostatus = false;
		return $fotostatus;
	}

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
		$intern_email["email"] = DOMAIN? $uid . "@" . DOMAIN:"";
		$extern_email = array();
		$extern_email["type"] = "alias";
		
		$extern_email["email"] = $benutzer_res->alias ? ($benutzer_res->alias . "@" . DOMAIN) : null;
		$res->emails = $extern_email?[$intern_email, $extern_email]:[$intern_email];

		$res->funktionen = $benutzer_funktion_res;
		$res->mailverteiler = $mailverteiler_res;
		$res->standort_telefon = isset($telefon_res) ? $telefon_res->kontakt : null;

		return $res;
	}

	/**
	 * gets the alias of a corresponding user
	 * @access private
	 * @param  integer $uid the userID used to get the alias
	 * @return string the alias of the userID
	 */
	private function getBenutzerAlias($uid)
	{
		$this->ci->load->model("person/Benutzer_model","BenutzerModel");
		$this->ci->BenutzerModel->addSelect(["alias"]);
		$benutzer_res = $this->ci->BenutzerModel->load([$uid]);
		if(isError($benutzer_res)){
			return error(getData($benutzer_res));
		}

		$benutzer_res = getData($benutzer_res);
		$benutzer_res = $benutzer_res ? current($benutzer_res) : null;
			
		return $benutzer_res;
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
		$intern_email["email"] = DOMAIN? $uid . "@" . DOMAIN:"";

		$res->emails = [$intern_email];
		$res->matrikelnummer = $matr_res->matr_nr;
		$res->mailverteiler = $mailverteiler_res;

		return $res;
	}
}