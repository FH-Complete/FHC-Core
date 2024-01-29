<?php

class RtPerson_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = "public.tbl_rt_person";
		$this->pk = "rt_person_id";
	}
}