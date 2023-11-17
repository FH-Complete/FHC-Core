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
			
		]);
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('ressource/student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('person/Adresse_model', 'AdresseModel');
		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		
		
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
		
		$benutzer_funktion_res = $this->BenutzerfunktionModel->loadWhere();
		if(isError($benutzer_funktion_res)){
			// error handling
		}else{
			$benutzer_funktion_res = hasData($benutzer_funktion_res)? getData($benutzer_funktion_res) : null;
		}

		//! THERE COULD BE MULTIPLE ADRESSES
		$adresse_res = $this->AdresseModel->load(getAuthPersonId());
		if(isError($adresse_res)){
			// error handling
		}else{										//! not only one
			$adresse_res = hasData($adresse_res)? getData($adresse_res)[0] : null;
		}

		$benutzer_res = $this->PersonModel->load(getAuthUID());
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
		
		//? get the person information using the benutzer uid
		//! verwendet getAuthUID()
		$mitarbeiter_res = $this->MitarbeiterModel->load(getAuthUID());
		if(isError($mitarbeiter_res)){
			// error handling
		}else{
			//? checks whether the getData does not return null 
			//? and then checks that the current does return an empty array 
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
		$res->email_intern = getAuthUID() . "@technikum-wien.at";
		$res->email_extern = $benutzer_res->alias . "@technikum-wien.at";
		//? Adresse Info 
		$res->strasse = $adresse_res->strasse;
		$res->heimatadresse = $adresse_res->heimatadresse;
		$res->zustelladresse = $adresse_res->zustelladresse;
		$res->plz = $adresse_res->plz;
		$res->ort = $adresse_res->ort;
		//? Benutzerfunktion Info
		$res->funktionen = $benutzer_funktion_res;
		
		
		
		echo json_encode($res);
		
		return;
		/*
		$res = getData($this->PersonModel->getPersonStammdaten($pid));
		$json_result = new stdClass();
		$json_result->anrede = $res->anrede;
		$json_result->titelpre = $res->titelpre;
		$json_result->titelpost = $res->titelpost;
		$json_result->vorname = $res->vorname;
		$json_result->nachname = $res->nachname;
		$json_result->gebdatum = $res->gebdatum;
		$json_result->gebort = $res->gebort;
		$json_result->adressen = $res->adressen;
		
		anrede:this.person_info.anrede,
                titelpre:this.person_info.titelpre,
                titelpost:this.person_info.titelpost,
                vorname:this.person_info.vorname,
                nachname:this.person_info.nachname,
                gebdatum:this.person_info.gebdatum,
                gebort:this.person_info.gebort,
                adresse:this.person_info.adressen[0].strasse + " " + this.person_info.adressen[0].plz,
		*/

		//! the following line is not needed because it is already included in the getPersonStammdaten function
		//$json_result->addresse_info = $this->AdresseModel->getZustellAdresse($pid)->retval;
		echo json_encode($res);
		return;
		
		
		
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
