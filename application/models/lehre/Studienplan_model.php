<?php

class Studienplan_model extends DB_Model
{
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
		$studienplaeneQuery = "SELECT DISTINCT tbl_studienplan.*
								 FROM lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING(studienordnung_id)
							    WHERE tbl_studienordnung.studiengang_kz = ?";
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->getAddonID(), 'plan'))
		{
			$result = $this->db->query($studienplaeneQuery, array($studiengang_kz));
		}
		
		return $result;
	}
}