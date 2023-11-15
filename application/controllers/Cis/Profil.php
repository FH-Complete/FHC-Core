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
			'getUser' => ['student/anrechnung_beantragen:r','user:r'],
			'isMitarbeiterOrStudent' => ['student/anrechnung_beantragen:r','user:r'],
			'getPersonInformation' => ['student/anrechnung_beantragen:r','user:r'],
			
		]);
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('ressource/student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('person/Person_model', 'PersonModel');
		
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


	//? public function getUser returns information related to a user
	public function getUser()
	{
		//* retrieve info from the Mitarbeiter model
		$mitarbeiter_result = $this->MitarbeiterModel->load(getAuthUID());
		//* retrieve info from the Benutzer model
		$benutzer_result = $this->BenutzerModel->getFromPersonId(getAuthUID());
		//* return JSON with info
		//! was removed from $res for testing purposes: 'Benutzer' => $benutzer_result
		$res = ['mitarbeiter' => $mitarbeiter_result,'Benutzer' => $benutzer_result
				];
		
        echo json_encode($res);
	}

	public function getPersonInformation($pid){
		//? get the person information using the benutzer uid
		echo json_encode($this->PersonModel->getPersonStammdaten($pid)->retval);
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
