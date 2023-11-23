<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

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
			'index' => ['student/anrechnung_beantragen:r','user:r'], // TODO(chris): permissions?
			'isMitarbeiterOrStudent' => ['student/anrechnung_beantragen:r','user:r'],
			'getMitarbeiterAnsicht' => ['student/anrechnung_beantragen:r','user:r'],
			'foto_sperre_function' => ['student/anrechnung_beantragen:r','user:r'],
			
			
			
		]);
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('ressource/Betriebsmittelperson_model', 'BetriebsmittelpersonModel');
		$this->load->model('person/Kontakt_model', 'KontaktModel');
		
		
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		
		//? we can pass data to the view by using the second parameter of the load->view() function 
		//* the first parameter is the route that Code Igniter will switch to
		//* the second parameter can be used to pass data to the view  
		$this->load->view('Cis/Profil', ["uid" => getAuthUID(),"pid" => getAuthPersonId()]);
	}


	

	public function getMitarbeiterAnsicht(){

		if(
			isSuccess($this->PersonModel->addSelect('gruppe_kurzbz, beschreibung'))&&
			isSuccess($this->PersonModel->addJoin('tbl_benutzer', 'person_id')) && 
		isSuccess($this->PersonModel->addJoin('tbl_benutzergruppe', 'uid')) &&
		isSuccess($this->PersonModel->addJoin('tbl_gruppe', 'gruppe_kurzbz'))){

			$mailverteiler_res = $this->PersonModel->loadWhere(array('mailgrp' => true, 'uid'=>getAuthUID()));
			if( isError($mailverteiler_res)){
				// catch error
			}
			$mailverteiler_res = hasData($mailverteiler_res)? getData($mailverteiler_res) : null;
			
			$mailverteiler_res = array_map(function($element) { $element->mailto="mailto:".$element->gruppe_kurzbz."@".DOMAIN; return $element;},$mailverteiler_res);
		}

	

		if(isSuccess($this->KontaktModel->addSelect('DISTINCT ON (kontakttyp) kontakttyp, kontakt, tbl_kontakt.anmerkung, tbl_kontakt.zustellung')) &&
		isSuccess($this->KontaktModel->addJoin('public.tbl_standort', 'standort_id', 'LEFT')) &&
		isSuccess($this->KontaktModel->addJoin('public.tbl_firma', 'firma_id', 'LEFT'))&&
		isSuccess($this->KontaktModel->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum'))
		){
			$kontakte_res = $this->KontaktModel->loadWhere(array('person_id' => getAuthPersonID()));
			if(isError($kontakte_res)){
				// handle error	
			}else{
				$kontakte_res = hasData($kontakte_res)? getData($kontakte_res) : null;
			}
			
		}

		$zutrittskarte_ausgegebenam = $this->BetriebsmittelpersonModel->getBetriebsmittel(getAuthPersonId());
		if(isError($zutrittskarte_ausgegebenam)){
			// error handling
		}else{
			$zutrittskarte_ausgegebenam = hasData($zutrittskarte_ausgegebenam)? getData($zutrittskarte_ausgegebenam)[0] : null;
		}

		if(
			isSuccess($this->BetriebsmittelpersonModel->addSelect(["CONCAT(betriebsmitteltyp, ' ' ,beschreibung) as Betriebsmittel","nummer as Nummer","ausgegebenam as Ausgegeben_am"]))
			
		){
			$betriebsmittelperson_res = $this->BetriebsmittelpersonModel->getBetriebsmittel(getAuthPersonId());
			if(isError($betriebsmittelperson_res)){
				   // error handling
			   }else{
				   $betriebsmittelperson_res = hasData($betriebsmittelperson_res)? getData($betriebsmittelperson_res) : null;
			   }
		   }

		if(
			//! Summe der Wochenstunden wird jetzt in der hr/tbl_dienstverhaeltnis gespeichert
		 isSuccess($this->BenutzerfunktionModel->addSelect(["tbl_benutzerfunktion.bezeichnung as Bezeichnung","tbl_organisationseinheit.bezeichnung as Organisationseinheit","datum_von as Gültig_von","datum_bis as Gültig_bis","wochenstunden as Wochenstunden"]))&&
	  	  isSuccess($this->BenutzerfunktionModel->addJoin("tbl_organisationseinheit","oe_kurzbz"))
		){
			$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere(array('uid'=>getAuthUID()));
			if(isError($benutzer_funktion_res)){
				// error handling
			}else{
				$benutzer_funktion_res = hasData($benutzer_funktion_res)? getData($benutzer_funktion_res) : null;
			}
		}


		if(
			isSuccess($adresse_res = $this->AdresseModel->addSelect(array("strasse","tbl_adressentyp.bezeichnung as adr_typ","plz","ort")))&&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("zustelladresse","DESC"))&&
			isSuccess($adresse_res = $this->AdresseModel->addOrder("sort"))&&
			isSuccess($adresse_res = $this->AdresseModel->addJoin("tbl_adressentyp","typ=adressentyp_kurzbz"))
		){
			$adresse_res = $this->AdresseModel->loadWhere(array("person_id"=>getAuthPersonID()));
			if(isError($adresse_res)){
				// error handling
			}else{									
				$adresse_res = hasData($adresse_res)? getData($adresse_res) : null;
			}
		}

		$benutzer_res = $this->BenutzerModel->load([getAuthUID()]);
		if(isError($benutzer_res)){
			// error handling
		}else{
			$benutzer_res = hasData($benutzer_res)? getData($benutzer_res)[0] : null;
		}

		$person_res = $this->PersonModel->load(getAuthPersonId());
		if(isError($person_res)){
			// error handling
		}else{
			$person_res = hasData($person_res)? getData($person_res)[0] : null;
		}
		
		$mitarbeiter_res = $this->MitarbeiterModel->load(getAuthUID());
		if(isError($mitarbeiter_res)){
			// error handling
		}else{
			$mitarbeiter_res = hasData($mitarbeiter_res)? getData($mitarbeiter_res)[0] : null;
		}

		$res = new stdClass();
		$res->username = getAuthUID();
		//? Person Info
		$res->foto = $person_res->foto;
		$res->foto_sperre = $person_res->foto_sperre;
		$res->anrede = $person_res->anrede;
		$res->titelpre = $person_res->titelpre;
		$res->titelpost = $person_res->titelpost;
		$res->vorname = $person_res->vorname;
		$res->nachname = $person_res->nachname;
		//$res->postnomen = $person_res->postnomen; //!POSTNOMEN?
		$res->gebdatum = $person_res->gebdatum;
		$res->gebort = $person_res->gebort;
		//? Mitarbeiter Info
		$res->kurzbz = $mitarbeiter_res->kurzbz;
		$res->telefonklappe = $mitarbeiter_res->telefonklappe;
		//? Benutzer Info
		$res->email_intern = getAuthUID() . DOMAIN;
		$res->email_extern = $benutzer_res->alias . DOMAIN;
		//? Adresse Info 
		$res->adressen = $adresse_res;
		//? Benutzerfunktion Info
		$res->funktionen = $benutzer_funktion_res;
		//? Betriebsmittel Info
		$res->mittel = $betriebsmittelperson_res;
		//? Austellungsdatum von der Zutrittskarte
		$res->zutrittskarte_ausgegebenam = $zutrittskarte_ausgegebenam->ausgegebenam;
		//? Kontakt Info
		$res->kontakte = $kontakte_res;
		//? Mailverteiler Info
		$res->mailverteiler = $mailverteiler_res;
		
		echo json_encode($res);
		
	}

	//? check wheter the parameter uid is a Mitarbeiter or a Student
	public function isMitarbeiterOrStudent($uid){
		if($this->MitarbeiterModel->isMitarbeiter($uid)->retval){
			echo json_encode("Mitarbeiter");
		}//! not sure if the user is automatically a student if he is not a mitarbeiter
		else if($this->StudentModel->isStudent($uid)->retval){
			echo json_encode("Student");
		}else{
			echo json_encode("Not a Mitarbeiter or Student");
		}

		
	}

	public function foto_sperre_function($value){
		$res = $this->PersonModel->update(getAuthPersonID(),array("foto_sperre"=>$value));
		if(isError($res)){
			// error handling
		}else{
			//? select the value of the column foto_sperre to return 
			if(isSuccess($this->PersonModel->addSelect("foto_sperre"))){
				$res = $this->PersonModel->load(getAuthPersonID());
				if(isError($res)){
					// error handling
				}
				$res = hasData($res) ? getData($res)[0] : null;
			}
			
		}
		echo json_encode($res);
	}



	

	

	



	//? this idea was to use _remap, to call different views based on the type of user
	/*
	public function _remap($param)
	{
		/$uid wird als global variable weiter gegeben
			 /get the data from the database with the uid
			 /give the data to the view 
			 /put the queried data in global array and access properties needed in the specific view $profile_information = array()
			/$this -> load->view('Cis/StudentProfile', ["uid" => $uid])
			
			
			if ($param === 'some_method')
			{
					echo "if";
			}
			else
			{
					echo "else";
			}
	}*/

	

}
