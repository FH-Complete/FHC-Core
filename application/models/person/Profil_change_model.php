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
	 */
	public function getTimestamp($uid){
		$this->addSelect(['change_timestamp']);
		$res = $this->load([$uid]);
		return hasData($res) ? getData($res)[0]->change_timestamp : null;
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
		//?
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
					$update->change_timestamp = date_create($update->change_timestamp)->format('d.m.Y');
					$update->status_timestamp = date_create($update->status_timestamp)->format('d.m.Y');
				}
			}
		}
		return $res;

	}

}
