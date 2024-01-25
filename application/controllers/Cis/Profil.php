<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class Profil extends Auth_Controller
{

	/**
	 * Constructor
	 */

	public function __construct()
	{
		parent::__construct([
			'index' => ['student/anrechnung_beantragen:r', 'user:r'], // TODO(chris): permissions?
			'foto_sperre_function' => ['student/anrechnung_beantragen:r', 'user:r'],
			'getView' => ['student/anrechnung_beantragen:r', 'user:r'],
			'View' => ['student/anrechnung_beantragen:r', 'user:r'],
			'insertProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'updateProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'deleteProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'selectProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],

		]);
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		$this->load->model('person/Profil_change_model', 'ProfilChangeModel');

		//? put the uid and pid inside the controller to reuse in controller
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */


	public function index()
	{
		$this->load->view('Cis/Profil');
	}
	public function View($uid)
	{

		if ($uid === $this->uid) {
			$this->index();
		} else {
			$this->load->view('Cis/Profil');
		}

	}



	public function selectProfilRequest(){
		$_GET = json_decode($this->input->raw_input_stream, true);
		$uid = $this->input->get('uid');
		$id = $this->input->get('id');
		
		
		if($uid && $id){
			$res= $this->ProfilChangeModel->getProfilUpdate($uid, $id);
		}elseif($uid){
			$res= $this->ProfilChangeModel->getProfilUpdate($uid);
		}elseif($id){
			$res= $this->ProfilChangeModel->getProfilUpdate($this->uid, $id);
		}else{
			$res= $this->ProfilChangeModel->getProfilUpdate($this->uid);
		}

		echo json_encode($res);
		
	}



	public function insertProfilRequest()
	{

		$json = json_decode($this->input->raw_input_stream);
		$payload = $json->payload;
	
		$type = property_exists($json->payload,"kontakt_id")? "kontakt_id" : "adresse_id";
		
		$name = $this->PersonModel->getFullName($this->uid);
		if(isError($name)){
			// error handling
			var_dump($name);
			return;
		}
		$data = ["topic"=>$json->topic,"uid" => $this->uid, "name"=>getData($name), "requested_change" => json_encode($payload), "change_timestamp" => "NOW()","status"=>"pending" ];

		//? loops over all updateRequests from a user to validate if the new request is valid
		$res = $this->ProfilChangeModel->loadWhere(["uid"=>$this->uid]);
		$res = hasData($res) ? getData($res) : null;
		
		if($res){
		foreach($res as $update_request){
			$existing_change = json_decode($update_request->requested_change);
			
			 
			if(!isset($existing_change->add) && property_exists($existing_change,$type) && property_exists($payload,$type) && $existing_change->$type == $payload->$type){
				//? the kontakt_id / adresse_id of a change has to be unique 
				
				echo json_encode(error("cannot change the same resource twice"));
				return;
			}

			
			if(isset($payload->add)){
				//TODO: add functionality for adding a new kontakt or address
	
			}elseif($update_request->topic == $json->topic ){
				
				//? if it is not a delete or add request than the topic in combination with the uid of the user have to be unique
				echo json_encode(error("A request to change " . $json->topic . " is already open"));
				return;
			}
		}}
		
		
		
		
		
			$insert_res = $this->ProfilChangeModel->insert($data);
			
		
			if(isError($insert_res)){
				//catch error
			}else{
				$editTimestamp = $this->ProfilChangeModel->getTimestamp($insert_res->retval);
				
				$insert_res->retval = date_create($editTimestamp)->format('d.m.Y');
				echo json_encode($insert_res);
			}
		



	}

	public function updateProfilRequest()
	{

		$json = json_decode($this->input->raw_input_stream);
		

		$data = ["topic"=>$json->topic,"uid" => $this->uid, "requested_change" => json_encode($json->payload), "change_timestamp" => "NOW()","status"=>"pending" ];

		//? gets all the requested changes from a user
		
		if(isSuccess($this->ProfilChangeModel->addSelect(["profil_update_id"])) ){
			$requestID = $this->ProfilChangeModel->loadWhere(["uid"=>$this->uid,"topic"=>$json->topic]);
			$requestID = hasData($requestID) ? getData($requestID)[0]->profil_update_id : null;
		};
		
		$update_res =$this->ProfilChangeModel->update([$requestID],$data);
		if(isError($update_res)){
			//catch error
		}else{
			$editTimestamp = $this->ProfilChangeModel->getTimestamp($update_res->retval[0]);
			
			$update_res->retval = date_create($editTimestamp)->format('d.m.Y'); 
			echo json_encode($update_res);
		}
	}

	public function deleteProfilRequest(){

		$json = json_decode($this->input->raw_input_stream);
		$delete_res = $this->ProfilChangeModel->delete([$json]);
		echo json_encode($delete_res);
	}



	private function viewMitarbeiterProfil($uid)
	{


		if (
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
			isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $uid));
			if (isError($mailverteiler_res)) {
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res) ? getData($mailverteiler_res) : null;

			$mailverteiler_res = array_map(function ($element) {
				$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
				return $element; }, $mailverteiler_res);
		}




		if (
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
			isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung", "tbl_organisationseinheit.bezeichnung as Organisationseinheit", "datum_von as Gültig_von", "datum_bis as Gültig_bis", "wochenstunden as Wochenstunden"])) &&
			isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit", "oe_kurzbz"))
		) {
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid' => $uid));
			if (isError($benutzer_funktion_res)) {
				// error handling
			} else {
				$benutzer_funktion_res = hasData($benutzer_funktion_res) ? getData($benutzer_funktion_res) : null;
			}
		}

		if (isSuccess($this->BenutzerModel->addSelect(["alias"]))) {
			$benutzer_res = $this->BenutzerModel->load([$uid]);
			if (isError($benutzer_res)) {
				// error handling
			} else {
				$benutzer_res = hasData($benutzer_res) ? getData($benutzer_res)[0] : null;
			}
		}

		if (
			isSuccess($this->BenutzerModel->addSelect(["foto", "foto_sperre", "anrede", "titelpost", "titelpre", "vorname", "nachname"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {

			$person_res = $this->BenutzerModel->load([$uid]);
			if (isError($person_res)) {
				// error handling
			} else {
				$person_res = hasData($person_res) ? getData($person_res)[0] : null;
			}
		}


		if (
			isSuccess($this->MitarbeiterModel->addSelect(["kurzbz", "telefonklappe", "alias", "ort_kurzbz"]))
			&& isSuccess($this->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid"))
		) {
			$mitarbeiter_res = $this->MitarbeiterModel->load($uid);
			if (isError($mitarbeiter_res)) {
				// error handling
			} else {
				$mitarbeiter_res = hasData($mitarbeiter_res) ? getData($mitarbeiter_res)[0] : null;
			}
		}

		//? querying the telefon number of the office
		if (
			isSuccess($this->MitarbeiterModel->addSelect(["kontakt"])) &&
			isSuccess($this->MitarbeiterModel->addJoin("tbl_kontakt", "tbl_mitarbeiter.standort_id = tbl_kontakt.standort_id"))


		) {
			$this->MitarbeiterModel->addLimit(1);
			$telefon_res = $this->MitarbeiterModel->loadWhere(["mitarbeiter_uid" => $uid, "kontakttyp" => "telefon"]);
			if (isError($telefon_res)) {
				// error handling
			} else {
				$telefon_res = hasData($telefon_res) ? getData($telefon_res)[0] : null;
			}
		}

		
		print_r($telefon_res);

		$res = new stdClass();
		$res->username = $uid;


		//? Person Info
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;

		$res->anrede = $person_res->anrede;
		$res->titelpre = $person_res->titelpre;
		$res->titelpost = $person_res->titelpost;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;


		//? Mitarbeiter Info
		foreach ($mitarbeiter_res as $key => $val) {
			$res->$key = $val;

		}
		//? Email Info 
		$intern_email = array();
		$intern_email += array("type" => "intern");
		$intern_email += array("email" => $uid . "@" . DOMAIN);
		$extern_email = array();
		$extern_email += array("type" => "alias");
		$extern_email += array("email" => $benutzer_res->alias . "@" . DOMAIN);
		$res->emails = array($intern_email, $extern_email);

		//? Benutzerfunktion Info
		$res->funktionen = $benutzer_funktion_res;

		//? Mailverteiler Info
		$res->mailverteiler = $mailverteiler_res;

		$res->standort_telefon = isset($telefon_res)? $telefon_res->kontakt : null;

		return $res;

	}



	private function viewStudentProfil($uid)
	{

		if (
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
			isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $uid));
			if (isError($mailverteiler_res)) {
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res) ? getData($mailverteiler_res) : null;

			$mailverteiler_res = array_map(function ($element) {
				$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
				return $element; }, $mailverteiler_res);
		}



		if (
			isSuccess($this->BenutzerModel->addSelect(["foto", "foto_sperre", "anrede", "titelpost", "titelpre", "vorname", "nachname"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {

			$person_res = $this->BenutzerModel->load([$uid]);
			if (isError($person_res)) {
				// error handling
			} else {
				$person_res = hasData($person_res) ? getData($person_res)[0] : null;
			}
		}


		//? personenkennzeichen ist die Spalte Matrikelnr in der Tabelle Student
		if (
			isSuccess($this->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang', 'tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe', 'tbl_student.matrikelnr as personenkennzeichen']))
			&& isSuccess($this->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz"))
		) {
			$student_res = $this->StudentModel->load([$uid]);
			if (isError($student_res)) {
				// catch error
			}
			$student_res = hasData($student_res) ? getData($student_res)[0] : null;

		}



		//? Matrikelnummer ist die Spalte matr_nr in Person
		if (
			isSuccess($this->BenutzerModel->addSelect(["matr_nr"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {
			$matr_res = $this->BenutzerModel->load([$uid]);
			if (isError($matr_res)) {
				// catch error
			} else {
				$matr_res = hasData($matr_res) ? getData($matr_res)[0] : [];

			}
		}

		$res = new stdClass();


		$res->foto = $person_res->foto;
		$res->username = $uid;

		$res->anrede = $person_res->anrede;
		$res->titel = $person_res->titelpre;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;


		$res->postnomen = $person_res->titelpost;

		$intern_email = array();
		$intern_email += array("type" => "intern");
		$intern_email += array("email" => $uid . "@" . DOMAIN);

		$res->emails = array($intern_email);




		$res->matrikelnummer = $matr_res->matr_nr;
		foreach ($student_res as $key => $value) {
			$res->$key = $value;
		}



		$res->mailverteiler = $mailverteiler_res;

		return $res;


	}

	private function mitarbeiterProfil()
	{


		//? betriebsmittel soll nur der user selber sehen
		if (

			isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel", "nummer as Nummer", "ausgegebenam as Ausgegeben_am"]))

		) {
			//? betriebsmittel are not needed in a view
			$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel($this->pid);
			if (isError($betriebsmittelperson_res)) {
				// error handling
			} else {
				$betriebsmittelperson_res = hasData($betriebsmittelperson_res) ? getData($betriebsmittelperson_res) : null;
			}
		}

		if (

			//? kontaktdaten soll auch nur der user selbst sehen
			//DISTINCT ON (kontakttyp)
			isSuccess($this->KontaktModel->addSelect(['kontakttyp','kontakt_id','kontakt', 'tbl_kontakt.anmerkung', 'tbl_kontakt.zustellung'])) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
		) {
			$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => $this->pid));
			if (isError($kontakte_res)) {
				// handle error	
			} else {
				$kontakte_res = hasData($kontakte_res) ? getData($kontakte_res) : null;
				
				
			}

		}


		//$this->MitarbeiterModel->load($this->uid);

		//? FH Ausweis Austellungsdatum soll auch nur der user selbst sehen
		$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($this->uid, "Zutrittskarte");
		if (isError($zutrittskarte_ausgegebenam)) {
			// error handling
		} else {
			$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam) ? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
			//? formats the date from 01-01-2000 to 01.01.2000
			$zutrittskarte_ausgegebenam = str_replace("-", ".", $zutrittskarte_ausgegebenam);
		}


		//? Die Adressen soll auch nur der user selber sehen

		if (

			isSuccess($adresse_res = $this->AdresseModel->addSelect(["adresse_id","strasse", "tbl_adressentyp.bezeichnung as typ", "plz", "ort"])) &&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse", "DESC")) &&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("sort")) &&
			isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp", "typ=adressentyp_kurzbz"))
		) {
			$adresse_res = $this->AdresseModel->loadWhere(array("person_id" => $this->pid));
			if (isError($adresse_res)) {
				// error handling
			} else {
				$adresse_res = hasData($adresse_res) ? getData($adresse_res) : null;
			}
		}

		//? die folgenden Informationen darf nur der eigene user sehen





		if (
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
			isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $this->uid));
			if (isError($mailverteiler_res)) {
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res) ? getData($mailverteiler_res) : null;

			$mailverteiler_res = array_map(function ($element) {
				$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
				return $element; }, $mailverteiler_res);
		}



		if (
			isSuccess($this->BenutzerModel->addSelect(["foto", "foto_sperre", "anrede", "titelpost", "titelpre", "vorname", "nachname", "gebort", "gebdatum"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {

			$person_res = $this->BenutzerModel->load([$this->uid]);
			if (isError($person_res)) {
				// error handling
			} else {
				$person_res = hasData($person_res) ? getData($person_res)[0] : null;
			}
		}


		if (
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
			isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung", "tbl_organisationseinheit.bezeichnung as Organisationseinheit", "datum_von as Gültig_von", "datum_bis as Gültig_bis", "wochenstunden as Wochenstunden"])) &&
			isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit", "oe_kurzbz"))
		) {
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid' => $this->uid));
			if (isError($benutzer_funktion_res)) {
				// error handling
			} else {
				$benutzer_funktion_res = hasData($benutzer_funktion_res) ? getData($benutzer_funktion_res) : null;
			}
		}


		//? querying the telefon number of the office
		if (
			isSuccess($this->MitarbeiterModel->addSelect(["mitarbeiter_uid"])) &&
			isSuccess($this->MitarbeiterModel->addJoin("tbl_kontakt", "tbl_mitarbeiter.standort_id = tbl_kontakt.standort_id"))


		) {
			$this->MitarbeiterModel->addLimit(1);
			$telefon_res = $this->MitarbeiterModel->loadWhere(["mitarbeiter_uid" => $this->uid, "kontakttyp" => "telefon"]);
			if (isError($telefon_res)) {
				// error handling
			} else {
				$telefon_res = hasData($telefon_res) ? getData($telefon_res)[0] : null;
			}
		}





		if (
			isSuccess($this->MitarbeiterModel->addSelect(["kurzbz", "tbl_mitarbeiter.telefonklappe", "alias", "ort_kurzbz"]))
			&& isSuccess($this->MitarbeiterModel->addJoin("tbl_benutzer", "tbl_benutzer.uid = tbl_mitarbeiter.mitarbeiter_uid"))

		) {
			$mitarbeiter_res = $this->MitarbeiterModel->load($this->uid);
			if (isError($mitarbeiter_res)) {
				// error handling
			} else {
				$mitarbeiter_res = hasData($mitarbeiter_res) ? getData($mitarbeiter_res)[0] : null;
			}
		}


		//? querying if the user has profil update requests
		$profilUpdates = $this->ProfilChangeModel->getProfilUpdate($this->uid);
		if(isError($profilUpdates)){
			//error handling
		}else{
			//? array containing all the requested profil information changes from the current user
			$profilUpdates = hasData($profilUpdates) ? getData($profilUpdates) : null;
			
		} 

		$res = new stdClass();
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		$res->username = $this->uid;

		$res->anrede = $person_res->anrede;
		$res->titel = $person_res->titelpre;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;

		$res->gebort = $person_res->gebort;
		$res->gebdatum = $person_res->gebdatum;

		$res->postnomen = $person_res->titelpost;


		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;

		$res->mailverteiler = $mailverteiler_res;

		foreach ($mitarbeiter_res as $key => $value) {
			$res->$key = $value;
		}
		$intern_email = array();
		$intern_email += array("type" => "intern");
		$intern_email += array("email" => $this->uid . "@" . DOMAIN);
		$extern_email = array();
		$extern_email += array("type" => "alias");
		$extern_email += array("email" => $mitarbeiter_res->alias . "@" . DOMAIN);
		$res->emails = array($intern_email, $extern_email);

		$res->funktionen = $benutzer_funktion_res;

		//telefon nummer von dem Standort
		$res->standort_telefon = $telefon_res;

		$res->profilUpdates = $profilUpdates?: null;
		
		

		return $res;
	}


	private function studentProfil()
	{



		//? betriebsmittel soll nur der user selber sehen
		if (

			isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel", "nummer as Nummer", "ausgegebenam as Ausgegeben_am"]))

		) {
			//? betriebsmittel are not needed in a view
			$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel($this->pid);
			if (isError($betriebsmittelperson_res)) {
				// error handling
			} else {
				$betriebsmittelperson_res = hasData($betriebsmittelperson_res) ? getData($betriebsmittelperson_res) : null;
			}
		}

		if (

			//? kontaktdaten soll auch nur der user selbst sehen
			//DISTINCT ON (kontakttyp) 
			isSuccess($this->KontaktModel->addSelect(['kontakttyp', 'kontakt_id', 'kontakt', 'tbl_kontakt.anmerkung', 'tbl_kontakt.zustellung'])) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT')) &&
			isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
		) {
			$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => $this->pid));
			if (isError($kontakte_res)) {
				// handle error	
			} else {
				$kontakte_res = hasData($kontakte_res) ? getData($kontakte_res) : null;
				
			}

		}

		//? FH Ausweis Austellungsdatum soll auch nur der user selbst sehen
		$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittelByUid($this->uid, "Zutrittskarte");
		if (isError($zutrittskarte_ausgegebenam)) {
			// error handling
		} else {
			$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam) ? getData($zutrittskarte_ausgegebenam)[0]->ausgegebenam : null;
			//? formats the date from 01-01-2000 to 01.01.2000
			$zutrittskarte_ausgegebenam = str_replace("-", ".", $zutrittskarte_ausgegebenam);
		}


		//? Die Adressen soll auch nur der user selber sehen

		if (

			isSuccess($adresse_res = $this->AdresseModel->addSelect(["adresse_id","strasse", "tbl_adressentyp.bezeichnung as adr_typ", "plz", "ort"])) &&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse", "DESC")) &&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("sort")) &&
			isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp", "typ=adressentyp_kurzbz"))
		) {
			$adresse_res = $this->AdresseModel->loadWhere(array("person_id" => $this->pid));
			if (isError($adresse_res)) {
				// error handling
			} else {
				$adresse_res = hasData($adresse_res) ? getData($adresse_res) : null;
			}
		}

		//? die folgenden Informationen darf nur der eigene user sehen





		if (
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) &&
			isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
			isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid' => $this->uid));
			if (isError($mailverteiler_res)) {
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res) ? getData($mailverteiler_res) : null;

			$mailverteiler_res = array_map(function ($element) {
				$element->mailto = "mailto:" . $element->gruppe_kurzbz . "@" . DOMAIN;
				return $element; }, $mailverteiler_res);
		}



		if (
			isSuccess($this->BenutzerModel->addSelect(["foto", "foto_sperre", "anrede", "titelpost", "titelpre", "vorname", "nachname", "gebort", "gebdatum"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {

			$person_res = $this->BenutzerModel->load([$this->uid]);
			if (isError($person_res)) {
				// error handling
			} else {
				$person_res = hasData($person_res) ? getData($person_res)[0] : null;
			}
		}

		if (

			isSuccess($this->BenutzergruppeModel->addSelect(['bezeichnung']))
			&& isSuccess($this->BenutzergruppeModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))
		) {
			$zutrittsgruppe_res = $this->BenutzergruppeModel->loadWhere(array("uid" => $this->uid, "zutrittssystem" => true));
			if (isError($zutrittsgruppe_res)) {
				// catch error
			}
			$zutrittsgruppe_res = hasData($zutrittsgruppe_res) ? getData($zutrittsgruppe_res) : null;

		}



		//? personenkennzeichen ist die Spalte Matrikelnr in der Tabelle Student
		if (
			isSuccess($this->StudentModel->addSelect(['tbl_studiengang.bezeichnung as studiengang', 'tbl_student.semester', 'tbl_student.verband', 'tbl_student.gruppe', 'tbl_student.matrikelnr as personenkennzeichen']))
			&& isSuccess($this->StudentModel->addJoin('tbl_studiengang', "tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz"))
		) {
			$student_res = $this->StudentModel->load([$this->uid]);
			if (isError($student_res)) {
				// catch error
			}
			$student_res = hasData($student_res) ? getData($student_res)[0] : null;

		}



		//? Matrikelnummer ist die Spalte matr_nr in Person
		if (
			isSuccess($this->BenutzerModel->addSelect(["matr_nr"]))
			&& isSuccess($this->BenutzerModel->addJoin("tbl_person", "person_id"))
		) {
			$matr_res = $this->BenutzerModel->load([$this->uid]);
			if (isError($matr_res)) {
				// catch error
			} else {
				$matr_res = hasData($matr_res) ? getData($matr_res)[0] : [];

			}
		}

		//? querying if the user has profil update requests
		$profilUpdates = $this->ProfilChangeModel->getProfilUpdate($this->uid);
		if(isError($profilUpdates)){
			//error handling
		}else{
			//? array containing all the requested profil information changes from the current user
			$profilUpdates = hasData($profilUpdates) ? getData($profilUpdates) : null;
			
		} 


		$res = new stdClass();


		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		$res->username = $this->uid;

		$res->anrede = $person_res->anrede;
		$res->titel = $person_res->titelpre;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;

		$res->gebort = $person_res->gebort;
		$res->gebdatum = $person_res->gebdatum;

		$res->postnomen = $person_res->titelpost;


		$intern_email = array();
		$intern_email += array("type" => "intern");
		$intern_email += array("email" => $this->uid . "@" . DOMAIN);

		$res->emails = array($intern_email);
		$res->adressen = $adresse_res;
		$res->zutrittsdatum = $zutrittskarte_ausgegebenam;
		$res->kontakte = $kontakte_res;
		$res->mittel = $betriebsmittelperson_res;
		$res->matrikelnummer = $matr_res->matr_nr;
		foreach ($student_res as $key => $value) {
			$res->$key = trim($value);
		}
		$res->zuttritsgruppen = $zutrittsgruppe_res;



		$res->mailverteiler = $mailverteiler_res;
		$res->profilUpdates = $profilUpdates?: null;
	
		
		return $res;



	}

	public function getView($uid)
	{


		$uid = $uid != "Profil" ? $uid : null;



		$isMitarbeiter = null;
		if ($uid) {

			if (isSuccess($this->PersonModel->addSelect(["person_id"]))) {
				$pid = $this->PersonModel->getByUid($uid);
				$pid = hasData($pid) ? getData($pid)[0] : null;

			}
			if (!$pid) {
				//! if no Person_ID was found, null is returned and the vue component will show a 404 View
				return null;
			}

			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($uid);
		} else
			$isMitarbeiter = $this->MitarbeiterModel->isMitarbeiter($this->uid);


		if (isError($isMitarbeiter)) {
			//catch error
		}
		$isMitarbeiter = hasData($isMitarbeiter) ? getData($isMitarbeiter) : null;


		$res = new stdClass();

		if ($uid == $this->uid || !$uid) {
			// if the $uid is empty, then no payload was supplied and the own profile is being requested
			if ($isMitarbeiter) {
				$res->view = "MitarbeiterProfil";
				$res->data = $this->mitarbeiterProfil();
				$res->data->pid = $this->pid;
			} else {
				$res->view = "StudentProfil";
				$res->data = $this->studentProfil();
				$res->data->pid = $this->pid;
			}
		} elseif ($uid) {
			// if an $uid was passed as payload to the function then the user is trying to view another profile
			if ($isMitarbeiter) {
				$res->view = "ViewMitarbeiterProfil";
				$res->data = $this->viewMitarbeiterProfil($uid);

			} else {
				$res->view = "ViewStudentProfil";
				$res->data = $this->viewStudentProfil($uid);

			}
		}


		echo json_encode($res);

	}

	public function foto_sperre_function($value)
	{
		//? Nur der Index User hat die Erlaubniss das Profilbild zu sperren 
		$res = $this->PersonModel->update($this->pid, array("foto_sperre" => $value));

		if (isError($res)) {
			echo json_encode("error encountered when updating foto_sperre");
			return;
			// error handling
		} else {
			//? select the value of the column foto_sperre to return 
			if (isSuccess($this->PersonModel->addSelect("foto_sperre"))) {
				$res = $this->PersonModel->load($this->pid);
				if (isError($res)) {
					// error handling
				}
				$res = hasData($res) ? getData($res)[0] : null;
			}

		}
		echo json_encode($res);
	}

}
