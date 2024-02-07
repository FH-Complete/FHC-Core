<?php

class Profil_update_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_profil_update';
		$this->pk = ['profil_update_id'];
        $this->hasSequence = true;


		$this->load->model('crm/Student_model','StudentModel');
		$this->load->model('ressource/Mitarbeiter_model','MitarbeiterModel');

		$this->load->library('PermissionLib');
	}

	/**
	 * getTimestamp
	 * returns insert or update timestamp of a certain profil update
	 * 
	 * @param boolean $update: conditional whether to return insertamum or updateamum
	 */
	public function getTimestamp($id, $update=false){
		$selectStatement = $update? 'updateamum' : 'insertamum';
		$this->addSelect([$selectStatement]);
		$res = $this->load([$id]);
		return hasData($res) ? getData($res)[0]->$selectStatement : null;
	}

	/**
	 * getFilesFromChangeRequest
	 * 
	 * returns all files associated to a profil update request in the following format:
	 * {dms_id:123 , name:"test"}
	 * 
	 * @param boolean $profil_update_id primary key of the profil update request
	 * @return Array 
	 */
	public function getFilesFromChangeRequest($profil_update_id){
		$this->addSelect(["requested_change"]);
		$res = $this->load([$profil_update_id]);
		$res = hasData($res) ? getData($res)[0] : null;
		return json_decode($res->requested_change)->files?:[];
	}

	/**
	 * 
	 * getProfilUpdate
	 * returns a profil update with id 
	 * returns all profil updates if id is set to null
	 */
	public function getProfilUpdate($whereClause=null){
		
		$studentBerechtigung = $this->permissionlib->isBerechtigt('student/stammdaten','s');
		$mitarbeiterBerechtigung = $this->permissionlib->isBerechtigt('mitarbeiter/stammdaten','s');
		
		$res =[];
		if($studentBerechtigung) {
			$this->addJoin('tbl_student','tbl_student.student_uid=tbl_profil_update.uid');
			$studentRequests = $this->loadWhere($whereClause);
			if(isError($studentRequests)) return error("db error: ". getData($studentRequests));
			$studentRequests = getData($studentRequests)?:[]; 
			foreach($studentRequests as $request){
				array_push($res,$request);
			}
		}
		if($mitarbeiterBerechtigung) {
			$this->addJoin('tbl_mitarbeiter','tbl_mitarbeiter.mitarbeiter_uid=tbl_profil_update.uid');
			$mitarbeiterRequests = $this->loadWhere($whereClause);
			if(isError($mitarbeiterRequests)) return error("db error: ". getData($mitarbeiterRequests));
			$mitarbeiterRequests = getData($mitarbeiterRequests)?:[]; 
			foreach($mitarbeiterRequests as $request){
				array_push($res,$request);
			}
		}
		
		
	
		if($res){
			
			foreach($res as $update){
			
				
				$update->requested_change = json_decode($update->requested_change);
				$update->insertamum = !is_null($update->insertamum)?date_create($update->insertamum)->format('d.m.Y'):null;
				$update->updateamum = !is_null($update->updateamum)?date_create($update->updateamum)->format('d.m.Y'):null;
				$update->status_timestamp = !is_null($update->status_timestamp)?date_create($update->status_timestamp)->format('d.m.Y'):null;
					
			}
		}
		
		return $res;

	}

}
