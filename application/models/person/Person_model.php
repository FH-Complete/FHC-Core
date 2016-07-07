<?php

class Person_model extends DB_Model
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_person';
		$this->pk = 'person_id';
	}

	/**
	 * 
	 */
	public function checkBewerbung($email, $studiensemester_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_person'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_person'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_kontakt'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_kontakt'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_benutzer'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_benutzer'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_prestudent'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_prestudent'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_prestudentstatus'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_prestudentstatus'), FHC_MODEL_ERROR);
		
		$result = null;
		
		if (is_null($studiensemester_kurzbz))
		{
			$checkBewerbungQuery = "SELECT DISTINCT p.person_id, p.zugangscode, p.insertamum
 									  FROM public.tbl_person p JOIN public.tbl_kontakt k ON p.person_id = k.person_id
							     LEFT JOIN public.tbl_benutzer b ON p.person_id = b.person_id
								     WHERE k.kontakttyp = 'email'
									   AND (kontakt = ? OR alias || '@technikum-wien.at' = ? OR uid || '@technikum-wien.at' = ?)
								  ORDER BY p.insertamum DESC
								     LIMIT 1";
			
			$result = $this->db->query($checkBewerbungQuery, array($email, $email, $email));
		}
		else
		{
			$checkBewerbungQuery = "SELECT DISTINCT p.person_id, p.zugangscode, p.insertamum
									  FROM public.tbl_person p JOIN public.tbl_kontakt k ON p.person_id = k.person_id
								 LEFT JOIN public.tbl_benutzer b ON p.person_id = b.person_id
									  JOIN public.tbl_prestudent ps ON p.person_id = ps.person_id
									  JOIN public.tbl_prestudentstatus pst ON pst.prestudent_id = ps.prestudent_id
									 WHERE k.kontakttyp = 'email'
									   AND (kontakt = ? OR alias || '@technikum-wien.at' = ? OR uid || '@technikum-wien.at' = ?)
									   AND studiensemester_kurzbz = ?
								  ORDER BY p.insertamum DESC
									 LIMIT 1";
			
			$result = $this->db->query($checkBewerbungQuery, array($email, $email, $email, $studiensemester_kurzbz));
		}
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}