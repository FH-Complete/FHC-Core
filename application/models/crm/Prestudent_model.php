<?php

class Prestudent_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_prestudent';
		$this->pk = 'prestudent_id';
	}

	/**
	 * 
	 */
	public function loadPrestudentPerson($prestudentID)
	{
		// Check the rights
		if (! $this->fhc_db_acl->isBerechtigt('basis/person', 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> basis/person', FHC_MODEL_ERROR);
		
		// Prepare SQL-Query
		$this->db->select('*')
					->from('public.tbl_prestudent')
					->join('public.tbl_person', 'person_id')
					->where('prestudent_id', $prestudentID);
		// Do the query
		$result = $this->db->get()->result_object();
		
		// Return the result
		if ($result)
			return $this->_success($result);
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}


}
