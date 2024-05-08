<?php

class Profil_update_status_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_profil_update_status';
		$this->pk = ['status_kurzbz'];
		$this->hasSequence = false;


	
	}
}