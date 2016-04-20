<?php

class Plan_model extends DB_Model
{
	// 
	protected $_curriculaQuery = "SELECT DISTINCT tbl_studienplan.*
							 	    FROM lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING(studienordnung_id)
						 		   WHERE tbl_studienordnung.studiengang_kz = ?";

	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * 
	 */
	public function getCurricula($courseOfStudiesID)
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if($this->_checkPermissions())
		{
			$result = $this->db->query($this->_curriculaQuery, array($courseOfStudiesID));
		}
		
		return $result;
	}
}