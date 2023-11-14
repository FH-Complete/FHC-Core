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
			'getUser' => ['student/anrechnung_beantragen:r','user:r']
		]);
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods

	/**
	 * @return void
	 */
	public function index()
	{
		//echo = getAuthUID();
		
		$this->load->view('Cis/Profil', ["uid" => getAuthUID()]);
	}


	//? public function getUser returns information related to a user
	public function getUser()
	{
		//* retrieve info from the Mitarbeiter model
		$mitarbeiter_result = $this->MitarbeiterModel->load(getAuthUID());
		//* retrieve info from the Benutzer model
		$benutzer_result = $this->BenutzerModel->load([getAuthUID()]);
		//* return JSON with info
		$res = ['mitarbeiter' => $mitarbeiter_result,
				'Benutzer' => $benutzer_result];
		
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
