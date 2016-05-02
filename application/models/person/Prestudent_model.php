<?php

class Prestudent_model extends DB_Model
{

	
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable='public.tbl_prestudent';
		$this->pk='prestudent_id';
	}

	/**
	 * 
	 */
	public function loadPrestudentPerson($prestudentID)
	{
		$this->db->select('*')
					->from('public.tbl_prestudent')
					->join('public.tbl_person', 'person_id')
					->where('prestudent_id', $prestudentID);
		return $this->db->get()->result_array();
	}


}
