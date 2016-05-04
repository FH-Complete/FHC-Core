<?php

class Studiengang_model extends DB_Model
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_studienplan';
		$this->pk = 'studienplan_id';
	}
	
	/**
	 * 
	 */
	public function getAllForBewerbung()
	{
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
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['lehre.vw_studienplan'], 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['lehre.vw_studienplan'], FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->acl['bis.tbl_lgartcode'], 's'))
				return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->acl['lehre.vw_studienplan'], FHC_MODEL_ERROR);
		
		return $this->db->query($allForBewerbungQuery);
	}
}