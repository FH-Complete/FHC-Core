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
			'index' => ['student/stammdaten:r','mitarbeiter/stammdaten:r'],
			'getProfilUpdateWithPermission' => ['student/stammdaten:r','mitarbeiter/stammdaten:r'],
			'acceptProfilRequest'=>['student/stammdaten:rw','mitarbeiter/stammdaten:rw'],
			'denyProfilRequest'=>['student/stammdaten:rw','mitarbeiter/stammdaten:rw'],
			'show'=>['student/stammdaten:r','mitarbeiter/stammdaten:r'],
			

		]);
		

		$this->load->model('person/Profil_update_model','ProfilUpdateModel');
		$this->load->model('person/Kontakt_model','KontaktModel');
		$this->load->model('person/Adresse_model','AdresseModel');
		$this->load->model('person/Adressentyp_model', 'AdressenTypModel');
		$this->load->model('person/Person_model','PersonModel');
	}


	public function index(){
		$this->load->view('Cis/ProfilUpdate');
	}

	public function show($dms_id){
		$this->load->library('DmsLib');
		//? downloads the file using the dms_id
		$file = $this->dmslib->download($dms_id);
		
		$file = hasData($file) ? getData($file) : null;
		//? returns the downloaded file to the user
		$res = $this->outputFile($file);

		echo json_encode($res);
	}

	public function getProfilUpdateWithPermission($status=null){
		
		
		$res = $this->ProfilUpdateModel->getProfilUpdateWithPermission(isset($status)?['status'=>$status]:null);
		
		echo json_encode($res);
	}


	public function acceptProfilRequest(){

		$_POST = json_decode($this->input->raw_input_stream,true);
		$id = $this->input->post('profil_update_id',true);
		$uid = $this->input->post('uid',true);	

		//? fetching person_id using UID
		$personID = $this->PersonModel->getByUid($uid);
		$personID = hasData($personID)? getData($personID)[0]->person_id : null;
		$status_message = $this->input->post('status_message',true);
		$topic = $this->input->post('topic',true);

		//! somehow the xss check converted boolean false to empty string
		$requested_change = $this->input->post('requested_change');
		
		//! check for required information
		if(!isset($id) || !isset($uid) || !isset($personID) || !isset($requested_change) || !isset($topic)){
			return json_encode(error("missing required information"));
		}
		
		if(is_array($requested_change) && array_key_exists("adresse_id",$requested_change)){
			$insertID = $this->handleAdresse($requested_change, $personID);
			$insertID = hasData($insertID) ? getData($insertID) : null;
			if(isset($insertID)) {
				$requested_change['adresse_id'] = $insertID;
				$update_res = $this->updateRequestedChange($id,$requested_change);
				if(isError($update_res)){
					echo json_encode(error("was not able to add addresse_id " . $insertID . " to profilRequest " . $id ));
					return;
				}
			}
		
		}else if (is_array($requested_change) && array_key_exists("kontakt_id", $requested_change)){
			$insertID = $this->handleKontakt($requested_change, $personID);
			$insertID = hasData($insertID) ? getData($insertID) : null;
			if(isset($insertID)) {
				$requested_change['kontakt_id'] = $insertID;
				$update_res = $this->updateRequestedChange($id,$requested_change);
				if(isError($update_res)){
					 echo json_encode(error("was not able to add kontakt_id " . $insertID . " to profilRequest " . $id ));
					 return;
				}
			}
			
			
		}else{
			switch($topic){
				case "titel": $topic ="titelpre"; break;
				case "postnomen": $topic = "titelpost"; break;
			}
			
			$result = $this->PersonModel->update($personID,[$topic=>$requested_change["value"]]);
			if(isError($result)){
				echo json_encode(error("was not able to update Person Information: " . $topic . " with value : " . $requested_change));
				return;
			}
		}
		
		echo json_encode($this->setStatusOnUpdateRequest($id, "accepted", $status_message, $requested_change));
	}

	public function denyProfilRequest(){
		
		$_POST = json_decode($this->input->raw_input_stream,true);
		$id = $this->input->post('profil_update_id',true);
		$status_message = $this->input->post('status_message',true);
		
		echo json_encode($this->setStatusOnUpdateRequest($id, "rejected", $status_message));
	}

	private function updateRequestedChange($id, $requested_change){
		return $this->ProfilUpdateModel->update([$id], ['requested_change'=>json_encode($requested_change)]);
	}

	private function setStatusOnUpdateRequest($id, $status, $status_message ){ 
		return $this->ProfilUpdateModel->update([$id], ["status"=>$status,"status_timestamp"=>"NOW()","status_message"=>$status_message]);
	}


	private function handleKontakt($requested_change, $personID){
		$kontakt_id = $requested_change["kontakt_id"];
		//? removes the kontakt_id because we don't want to update the kontakt_id in the database
		unset($requested_change["kontakt_id"]);
		

		//! ADD
		if(array_key_exists('add',$requested_change) && $requested_change['add']){
			//? removes add flag
			unset($requested_change['add']);
			$requested_change['person_id'] = $personID;
			$requested_change['insertamum'] = "NOW()";
			$requested_change['insertvon'] = getAuthUID();
			$insertID = $this->KontaktModel->insert($requested_change);
			
		}
		//! DELETE
		elseif(array_key_exists('delete',$requested_change) && $requested_change['delete']){
			$this->KontaktModel->delete($kontakt_id);
		}
		//! UPDATE
		else{
			$requested_change['updateamum']="NOW()";
			$requested_change['updatemvon']=getAuthUID();
			$this->KontaktModel->update($kontakt_id,$requested_change);
		}
		return isset($insertID) ? $insertID : null;
	}

	private function handleAdresse($requested_change, $personID){

		$this->AdressenTypModel->addSelect(["adressentyp_kurzbz"]);
		$adr_kurzbz = $this->AdressenTypModel->loadWhere(["bezeichnung"=>$requested_change['typ']]);
		$adr_kurzbz = hasData($adr_kurzbz)? getData($adr_kurzbz)[0]->adressentyp_kurzbz : null;
		//? replace the address_typ with its correct kurzbz foreign key
		$requested_change['typ']= $adr_kurzbz;
		
		$adresse_id = $requested_change["adresse_id"];
		//? removes the adresse_id because we don't want to update the kontakt_id in the database
		unset($requested_change["adresse_id"]);

		
		//! ADD
		if(array_key_exists('add',$requested_change) && $requested_change['add']){
			//? removes add flag
			unset($requested_change['add']);
			$requested_change['insertamum']="NOW()";
			$requested_change['insertvon']=getAuthUID();
			$requested_change['person_id'] = $personID;
			//TODO: zustelladresse, heimatadresse, rechnungsadresse und nation werden nicht beachtet
			$insertID = $this->AdresseModel->insert($requested_change);
		}
		//! DELETE
		elseif(array_key_exists('delete',$requested_change) && $requested_change['delete']){
			$this->AdresseModel->delete($adresse_id);
		}
		//! UPDATE
		else{
			$requested_change['updateamum']	= "NOW()";
			$requested_change['updatevon'] = getAuthUID();
			$this->AdresseModel->update($adresse_id,$requested_change);
		}
		return isset($insertID)? $insertID : null;
	}
}