<?php

class Course_model extends DB_Model
{
	// 
	protected $_enabledCoursesQuery = "SELECT DISTINCT studiengang_kz,
											  typ,
											  organisationseinheittyp_kurzbz,
											  studiengangbezeichnung,
											  standort,
											  studiengangbezeichnung_englisch,
											  lgartcode,
											  tbl_lgartcode.bezeichnung
										 FROM lehre.vw_studienplan LEFT JOIN bis.tbl_lgartcode USING (lgartcode)
									    WHERE onlinebewerbung IS TRUE
										  AND aktiv IS TRUE
									 ORDER BY typ, studiengangbezeichnung, tbl_lgartcode.bezeichnung ASC";

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
	public function getEnabledCourses()
	{
		$result = NULL;
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->_addonID, 'person'))
		{
			$result = $this->db->query($this->_enabledCoursesQuery);
		}
		
		return $result;
	}
}