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
			'show'=>['student/anrechnung_beantragen:r','user:r'],
		
			'insertProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'updateProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'deleteProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'selectProfilRequest' => ['student/anrechnung_beantragen:r', 'user:r'],
			'insertFile' => ['student/anrechnung_beantragen:r', 'user:r'],
			'getProfilRequestFiles' => ['student/anrechnung_beantragen:r', 'user:r'],
			
		]);
		

		$this->load->model('person/Profil_update_model','ProfilUpdateModel');
		$this->load->model('person/Kontakt_model','KontaktModel');
		$this->load->model('person/Adresse_model','AdresseModel');
		$this->load->model('person/Adressentyp_model', 'AdressenTypModel');
		$this->load->model('person/Person_model','PersonModel');
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('person/Benutzer_model', 'BenutzerModel');


		$this->load->library('DmsLib');
		$this->load->library('PermissionLib');

		//? put the uid and pid inside the controller for reusability
		$this->uid = getAuthUID();
		$this->pid = getAuthPersonID();
	}


	public function index(){
		$this->load->view('Cis/ProfilUpdate');
	}

	public function show($dms_id){
	
		$profil_update = $this->ProfilUpdateModel->loadWhere(['attachment_id'=>$dms_id]);
		$profil_update = hasData($profil_update) ? getData($profil_update)[0] : null;
		
		//? checks if an profil update exists with the dms_id requested from the user
		if($profil_update){ 
			$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($profil_update->uid));
			$is_student_profil_update = getData($this->StudentModel->isStudent($profil_update->uid));
			
			if(
				$this->permissionlib->isBerechtigt('student/stammdaten:r') && $is_student_profil_update || 
				$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten:r') && $is_mitarbeiter_profil_update || 
				$this->uid == $profil_update->uid)
			{
				// Get file to be downloaded from DMS
				$newFilename= $this->uid."/document_".$dms_id;
				$download = $this->dmslib->download($dms_id, $newFilename);
				if (isError($download)) return $download;
				
				// Download file
				$this->outputFile(getData($download));
				

			}else{
				show_error("Missing necessary permissions");
				return;
			}
			
		}else{
			show_error("The requested document is not an attachment for any profil update");
			return;
		}
		
	}




	public function insertFile($replace){
		$replace = json_decode($replace);
		
		if(!count($_FILES)){
			echo json_encode([]);
			return;
		}
		
		//? if replace is set it contains the profil_update_id in which the attachment_id has to be replaced
		if(isset($replace)){
			$this->ProfilUpdateModel->addSelect(["attachment_id"]);
			$attachmentID = $this->ProfilUpdateModel->load([$replace]);
			if(isError($attachmentID)){
				return json_encode(error("Error loading ProfilUpdate resource"));
			}
			//? get the attachmentID
			$dms_id = hasData($attachmentID) ? getData($attachmentID)[0]->attachment_id : null;
			
			//? delete old dms_file of Profil Update
			$this->deleteOldVersionFile($dms_id);
		}
		
		
		$files = $_FILES['files'];
        $file_count = count($files['name']);
		
		$res=[];

        for ($i = 0; $i < $file_count; $i++) {
            $_FILES['files']['name'] = $files['name'][$i];
            $_FILES['files']['type'] = $files['type'][$i];
            $_FILES['files']['tmp_name'] = $files['tmp_name'][$i];
            $_FILES['files']['error'] = $files['error'][$i];
            $_FILES['files']['size'] = $files['size'][$i];
			
			$dms = [
				"kategorie_kurzbz"=>"profil_aenderung",
				"version"=>0, 
				"name"=>$_FILES['files']['name'],
				"mimetype"=>$_FILES['files']['type'],
				"beschreibung"=>$this->uid . " Profil Änderung",
				"insertvon"=>$this->uid,
				"insertamum"=>"NOW()",
			];
			
            $tmp_res=$this->dmslib->upload($dms , 'files');
			
			$tmp_res = hasData($tmp_res)? getData($tmp_res) : null;
			array_push($res,$tmp_res);
		}

		echo json_encode($res);
	}



	private function deleteOldVersionFile($dms_id){
		if(!isset($dms_id)){
			return;
		}

		//? collect all the results of the deleted versions in an array 
		$res =	array();
		
		//? delete all the different versions of the dms_file
		$dmsVersions = $this->DmsVersionModel->loadWhere(["dms_id"=>$dms_id]);
		$dmsVersions = hasData($dmsVersions) ? getData($dmsVersions) : null;
		if(isset($dmsVersions)){
			$zwischen_res = array_map(function($item){ return $item->version;},$dmsVersions);
			foreach($zwischen_res as $version){
				array_push($res, $this->DmsVersionModel->delete([$dms_id,$version]));
			}
		}else{
			echo json_encode(error("No version of the file has been found"));
		}
		
		//? returns a result for each deleted dms_file
		return $res;
	}

	



	public function selectProfilRequest(){
		$_GET = json_decode($this->input->raw_input_stream, true);
		$uid = $this->input->get('uid');
		$id = $this->input->get('id');
		$whereClause=['uid'=> $this->uid];
		
		if(isset($uid)) $whereClause['uid'] = $uid;
		if(isset($id)) $whereClause['id'] = $id;
		
		$res= $this->ProfilUpdateModel->getProfilUpdatesWhere($whereClause);

		echo json_encode($res);
		
	}


	public function getProfilRequestFiles(){
		$id = json_decode($this->input->raw_input_stream);

		$this->ProfilUpdateModel->addSelect(["attachment_id"]);
		$attachmentID = $this->ProfilUpdateModel->load([$id]);
		if(isError($attachmentID)){
			return json_encode(error("Error loading ProfilUpdate resource"));
		}
		//? get the attachmentID
		$dms_id = hasData($attachmentID) ? getData($attachmentID)[0]->attachment_id : null;
		
		//? get the name to the file
		$this->DmsVersionModel->addSelect(["name", "dms_id"]);
		$attachment = $this->DmsVersionModel->load([$dms_id,0]);
		if(isError($attachment)){
			return json_encode(error("Error loading DmsVersion resource"));
		}
		$attachment = hasData($attachment) ? getData($attachment) : null;
		//? returns {name:..., dms_id:...}
		echo json_encode($attachment);
	}

	public function insertProfilRequest()
	{
		//! deprecated code
		//? Name of user is now queried in the database table model Profil_update_model.php
		/* $name = $this->PersonModel->getFullName($this->uid);
		if(isError($name)){
			// error handling
			var_dump($name);
			return;
		} */

		$json = json_decode($this->input->raw_input_stream);
		$payload = $json->payload;
		
		$identifier = property_exists($json->payload,"kontakt_id")? "kontakt_id" : (property_exists($json->payload,"adresse_id")? "adresse_id" : null);
		
		$data = ["topic"=>$json->topic,"uid" => $this->uid, "requested_change" => json_encode($payload), "insertamum" => "NOW()", "insertvon"=>$this->uid,"status"=>"pending" ];
		//? insert fileID in the dataset if sent with post request
		if(isset($json->fileID)){
			$data['attachment_id'] = $json->fileID;
			
		} 
		

		//? loops over all updateRequests from a user to validate if the new request is valid
		$res = $this->ProfilUpdateModel->getProfilUpdatesWhere(["uid"=>$this->uid]);
		
		if($res){
		$pending_changes = array_filter($res, function($element) {
			return $element->status == 'pending';
		});
		foreach($pending_changes as $update_request){
			$existing_change = $update_request->requested_change;
			
			 //? the user can add as many new kontakt/adresse as he likes
			 
			 if( !isset($payload->add) && property_exists($existing_change,$identifier) && property_exists($payload,$identifier) && $existing_change->$identifier == $payload->$identifier){
				//? the kontakt_id / adresse_id of a change has to be unique 
				echo json_encode(error("cannot change the same resource twice"));
				return;
			}
			
			elseif(!$identifier && $update_request->topic == $json->topic ){
				//? if it is not a delete or add request than the topic has to be unique
				echo json_encode(error("A request to change " . $json->topic . " is already open"));
				return;
			}
		}}
		
			$insertID = $this->ProfilUpdateModel->insert($data);
				
			if(isError($insertID)){
				//catch error
			}else{
				$insertID = hasData($insertID)? getData($insertID): null;
				$editTimestamp = $this->ProfilUpdateModel->getTimestamp($insertID);
				
				$date = success(date_create($editTimestamp)->format('d.m.Y'));
				echo json_encode($date);
			}
	}

	public function updateProfilRequest()
	{
		$json = json_decode($this->input->raw_input_stream);
		
		$updateData = ["requested_change" => json_encode($json->payload), "updateamum" => "NOW()", "updatevon" => $this->uid];
		if(isset($json->fileID)){
			$updateData['attachment_id'] = json_decode($json->fileID);
		}
		$updateID =$this->ProfilUpdateModel->update([$json->ID],$updateData);
		//? insert fileID in the dataset if sent with post request
		
		if(isError($updateID)){
			//catch error
		}else{
			$updateID = hasData($updateID)? getData($updateID)[0]: null;
			$editTimestamp = $this->ProfilUpdateModel->getTimestamp($updateID,true);
			
			$date = success(date_create($editTimestamp)->format('d.m.Y')); 
			echo json_encode($date);
		}
	}

	public function deleteProfilRequest(){

		$json = json_decode($this->input->raw_input_stream);
		$delete_res = $this->ProfilUpdateModel->delete([$json]);
		echo json_encode($delete_res);
	}


	public function getProfilUpdateWithPermission($status=null){
		
		
		$res = $this->ProfilUpdateModel->getProfilUpdateWithPermission(isset($status)?['status'=>$status]:null);
		
		echo json_encode($res);
	}

	
	private function checkIfPermissionsContainStudentOE($student_uid){

		$oe_berechtigung = $this->permissionlib->getOE_isEntitledFor('student/stammdaten');
		
		//? query that checks if the oe_berechtigungen is contained in the organisations_einheiten that are connected to the studiengang of the student
		$query ="SELECT TRUE
		FROM public.tbl_student
		JOIN public.tbl_prestudent ON public.tbl_student.prestudent_id = public.tbl_prestudent.prestudent_id
		JOIN public.tbl_studiengang ON tbl_prestudent.studiengang_kz = public.tbl_studiengang.studiengang_kz
		JOIN public.tbl_organisationseinheit ON public.tbl_organisationseinheit.oe_kurzbz = public.tbl_studiengang.oe_kurzbz
		WHERE public.tbl_student.student_uid = ? AND public.tbl_studiengang.oe_kurzbz IN ?
		LIMIT 1;";
		//TODO: hardcoded student_uid replace with variable student_uid
		$res = $this->StudentModel->execReadOnlyQuery($query,[$student_uid,$oe_berechtigung]);
		return hasData($res) ? true : false;
		
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

		$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($uid));
		$is_student_profil_update = getData($this->StudentModel->isStudent($uid));

		
		//? check if the permissions are set correctly
		if(
			$this->permissionlib->isBerechtigt('student/stammdaten:rw') && $is_student_profil_update && $this->checkIfPermissionsContainStudentOE($uid) || 
			$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten:rw') && $is_mitarbeiter_profil_update 
		)
		{
		
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
	}else{
		show_error("You have not the necessary permissions to accept this profil_update");
	}
		
		
	}

	public function denyProfilRequest(){
		
		$_POST = json_decode($this->input->raw_input_stream,true);
		$id = $this->input->post('profil_update_id',true);
		$uid = $this->input->post('uid',true);
		$status_message = $this->input->post('status_message',true);

		$is_mitarbeiter_profil_update = getData($this->MitarbeiterModel->isMitarbeiter($uid));
		$is_student_profil_update = getData($this->StudentModel->isStudent($uid));


		if(
			$this->permissionlib->isBerechtigt('student/stammdaten:rw') && $is_student_profil_update && $this->checkIfPermissionsContainStudentOE($uid) || 
			$this->permissionlib->isBerechtigt('mitarbeiter/stammdaten:rw') && $is_mitarbeiter_profil_update 
		)
		{
			echo json_encode($this->setStatusOnUpdateRequest($id, "rejected", $status_message));
		}else{
			show_error("You have not the necessary permissions to accept this profil_update");
		}
		
		
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