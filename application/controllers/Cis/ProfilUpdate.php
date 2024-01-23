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
			'acceptProfilRequest'=>['user:r'],
			'denyProfilRequest'=>['user:r'],

		]);
		//? put the uid and pid inside the controller to reuse in controller
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();

		$this->load->model('person/Profil_change_model','ProfilChangeModel');
		$this->load->model('person/Kontakt_model','KontaktModel');
		$this->load->model('person/Adresse_model','AdresseModel');
		$this->load->model('person/Adressentyp_model', 'AdressenTypModel');
		$this->load->model('person/Person_model','PersonModel');
	}


	public function index(){
		$this->load->view('Cis/ProfilUpdate');
	}

	public function getAllRequests(){
		$res = $this->ProfilChangeModel->getProfilUpdate();
		$res = hasData($res)? getData($res) : null;
		echo json_encode($res);
	}

	public function acceptProfilRequest(){
		$_POST = json_decode($this->input->raw_input_stream,true);
		
		$id = $this->input->post('profil_update_id',true);
		$uid = $this->input->post('uid',true);	
		$status_message = $this->input->post('status_message',true);
		$topic = $this->input->post('topic',true);
		$requested_change = $this->input->post('requested_change',true);
		
		
		print_r($_POST);
		//! PROPERTY EXISTS DOES NOT WORK FOR ASSOCIATIVE ARRAYS
		if(property_exists($requested_change,"adresse_id")){
			echo 'if';
			return;

			$this->AdressenTypModel->addSelect(["adressentyp_kurzbz"]);
			$adr_kurzbz = $this->AdressenTypModel->loadWhere(["bezeichnung"=>$requested_change['typ']]);
			$adr_kurzbz = hasData($adr_kurzbz)? getData($adr_kurzbz)[0]->adressentyp_kurzbz : null;
			//? replace the address_typ with its correct kurzbz foreign key
			$requested_change['typ']= $adr_kurzbz;
			
			$adresse_id = $requested_change["adresse_id"];
			//? removes the adresse_id because we don't want to update the kontakt_id in the database
			unset($requested_change["adresse_id"]);

			$res = $this->AdresseModel->update($adresse_id, $requested_change);
			echo json_encode($res);
		}else if (property_exists($requested_change,"kontakt_id")){
			echo 'else if';
			return;
			
			$kontakt_id = $requested_change["kontakt_id"];
			//? removes the kontakt_id because we don't want to update the kontakt_id in the database
			unset($requested_change["kontakt_id"]);
			
			
			$res = $this->KontaktModel->update($kontakt_id,$requested_change);
			
			echo json_encode($res);

		}else{
			echo 'else';
			return;
			//? fetching person_id using UID
			$personID = $this->PersonModel->getByUid($uid);
			$personID = hasData($personID)? getData($personID)[0]->person_id : null;
			
			switch($topic){
				case "titel": $topic ="titelpre"; break;
				case "postnomen": $topic = "titelpost"; break;
			}
			$res = $this->PersonModel->update($personID,[$topic=>$requested_change]);
		
			echo json_encode($res);
		}

		return;
		if(isset($id)){
			$res =$this->ProfilChangeModel->update([$id], ["status"=>"accepted","status_timestamp"=>"NOW()","status_message"=>$status_message]);
			echo json_encode($res);
		} 

	}

	public function denyProfilRequest(){
		$_POST = json_decode($this->input->raw_input_stream,true);
		
		$id = $this->input->post('profil_update_id',true);
		$status_message = $this->input->post('status_message',true);
		
		if(isset($id)){
			$res = $this->ProfilChangeModel->update([$id],["status"=>"rejected","status_timestamp"=>"NOW()","status_mesage"=>$status_message]);
			echo json_encode($res);
			
		}
		

	}
}