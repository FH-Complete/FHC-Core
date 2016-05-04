<?php

class Studiengang_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiengang';
		$this->pk = 'studiengang_kz';
	}
	
	/**
	 * 
	 */
	public function getAllForBewerbung()
	{
		$result = NULL;
		$allForBewerbungQuery = "SELECT DISTINCT studiengang_kz,
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
		
		// Checks if the operation is permitted by the API caller
		// All the code should be put inside this if statement
		if(isAllowed($this->getAddonID(), 'course'))
		{
			$result = $this->db->query($allForBewerbungQuery);
		}
		
		return $result;
	}
}
