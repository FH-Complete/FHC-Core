<?php

class Studienplan_model extends DB_Model
{
	// 
	protected $_studienplaeneQuery = "SELECT DISTINCT tbl_studienplan.*
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
	public function getStudienplaene($studiengang_kz)
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->getAddonID(), 'plan'))
		{
			$result = $this->db->query($this->_studienplaeneQuery, array($studiengang_kz));
		}
		
		return $result;
	}
}