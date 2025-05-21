<?php

class Profil_update_topic_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_profil_update_topic';
		$this->pk = ['topic_kurzbz'];
		$this->hasSequence = false;
	
	}
}