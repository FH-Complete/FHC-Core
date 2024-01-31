<?php

class Profil_change_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_cis_profil_update';
		$this->pk = ['profil_update_id'];
        $this->hasSequence = true;

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
		return json_decode($res->requested_change)->files?:null;
	}

	/**
	 * 
	 * getProfilUpdate
	 * returns a profil update with id 
	 * returns all profil updates if id is set to null
	 */
	public function getProfilUpdate($uid=null,$id=null){
		$whereClause=[];

		if(!is_null($uid)){
			$whereClause['uid']=$uid;
		}
		if(!is_null($id)){
			$whereClause['profil_update_id']=$id;
		}
		
		$res = $this->loadWhere($whereClause);
		if(isError($res)){
			// catch error
		}else{
			if(hasData($res)){
				
				foreach($res->retval as $update){
					$update->requested_change = json_decode($update->requested_change);
					$update->insertamum = !is_null($update->insertamum)?date_create($update->insertamum)->format('d.m.Y'):null;
					$update->status_timestamp = !is_null($update->status_timestamp)?date_create($update->status_timestamp)->format('d.m.Y'):null;
					 
				}
			}
		}
		return $res;

	}

}
