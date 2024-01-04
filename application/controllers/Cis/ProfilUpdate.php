<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

/**
 *
 */
class ProfilUpdate extends Auth_Controller
{

	public function __construct(){
		parent::__construct([
			'index' => ['student/anrechnung_beantragen:r', 'user:r'], // TODO(chris): permissions?
			'getAllRequests' => ['student/anrechnung_beantragen:r', 'user:r'],

		]);
		//? put the uid and pid inside the controller to reuse in controller
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

		$this->load->model('person/Profil_change_model','ProfilChangeModel');
	}


	public function index(){
		$this->load->view('Cis/ProfilUpdate');
	}

	public function getAllRequests(){
		
		$res = $this->ProfilChangeModel->load();
		if(isError($res)){
			// catch exception
			echo $res->retval->data;
			return;
		}else{
			if(hasData($res)){
				$res = getData($res);
				foreach($res as $element){
					$element->change_timestamp = date_create($element->change_timestamp)->format('d/m/Y H:i');
				}
			}else{
				$res = null;
			}
		}
		echo json_encode($res);
	}
}