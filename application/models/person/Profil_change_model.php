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
		$this->pk = ['uid'];
        $this->hasSequence = false;

	}

	/**
	 * getLastStatuses
	 */
	public function getTimestamp($uid){
		$this->addSelect(['change_timestamp']);
		$res = $this->load([$uid]);
		return hasData($res) ? getData($res)[0]->change_timestamp : null;
	}

}
