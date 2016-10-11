<?php

class Akte_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_akte';
		$this->pk = 'akte_id';
	}
	
	/**
	 * 
	 */
	public function getAkten($person_id, $dokument_kurzbz = null, $stg_kz = null, $prestudent_id = null)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_dokument'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_dokument'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_dokumentstudiengang'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_dokumentstudiengang'), FHC_MODEL_ERROR);
		
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_dokumentprestudent'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_dokumentprestudent'), FHC_MODEL_ERROR);
		
		$query = 'SELECT akte_id,
						 person_id,
						 dokument_kurzbz,
						 mimetype,
						 erstelltam,
						 gedruckt,
						 titel_intern,
						 anmerkung_intern,
						 titel, bezeichnung,
						 updateamum,
						 insertamum,
						 updatevon,
						 insertvon,
						 uid,
						 dms_id,
						 anmerkung,
						 nachgereicht,
						 nachgereicht_am,
						 CASE WHEN inhalt is not null THEN true ELSE false END as inhalt_vorhanden
					FROM public.tbl_akte
				   WHERE person_id = ?';
		
		$parametersArray = array($person_id);
		
		if (!is_null($dokument_kurzbz))
		{
			$query .= ' AND dokument_kurzbz = ?';
			array_push($parametersArray, $dokument_kurzbz);
		}
		
		if (!is_null($stg_kz) && !is_null($prestudent_id))
		{
			$query .= ' AND dokument_kurzbz NOT IN (
							SELECT dokument_kurzbz
							  FROM public.tbl_dokument JOIN public.tbl_dokumentstudiengang USING (dokument_kurzbz)
							 WHERE studiengang_kz = ?
						)
						AND dokument_kurzbz NOT IN (\'Zeugnis\')
						AND dokument_kurzbz NOT IN (
							SELECT dokument_kurzbz
							  FROM public.tbl_dokumentprestudent JOIN public.tbl_dokument USING (dokument_kurzbz)
							 WHERE prestudent_id = ?
						)';
			array_push($parametersArray, $stg_kz, $prestudent_id);
		}
		
		$query .= ' ORDER BY erstelltam';
		
		$result = $this->db->query($query, $parametersArray);
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	/**
	 * 
	 */
	public function getAktenAccepted($person_id, $dokument_kurzbz)
	{
		// Checks if the operation is permitted by the API caller
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz($this->dbTable), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz($this->dbTable), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_prestudent'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_prestudent'), FHC_MODEL_ERROR);
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_dokumentprestudent'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_dokumentprestudent'), FHC_MODEL_ERROR);
		
		$query = 'SELECT a.akte_id,
						 a.person_id,
						 a.dokument_kurzbz,
						 a.mimetype,
						 a.erstelltam,
						 a.gedruckt,
						 a.titel_intern,
						 a.anmerkung_intern,
						 a.titel,
						 a.bezeichnung,
						 a.updateamum,
						 a.insertamum,
						 a.updatevon,
						 a.insertvon,
						 a.uid,
						 a.dms_id,
						 a.anmerkung,
						 a.nachgereicht,
						 a.nachgereicht_am,
						 CASE WHEN a.inhalt IS NOT NULL THEN TRUE ELSE FALSE END AS inhalt_vorhanden,
						 (
							SELECT CASE WHEN COUNT(dokument_kurzbz) > 0 THEN TRUE ELSE FALSE END
							  FROM public.tbl_prestudent p
						INNER JOIN public.tbl_dokumentprestudent dp USING(prestudent_id)
							 WHERE p.person_id = ?
							   AND dp.dokument_kurzbz = ?
						 ) AS accepted
					FROM public.tbl_akte a
				   WHERE a.person_id = ?
					 AND a.dokument_kurzbz = ?
				ORDER BY a.erstelltam';
		
		$result = $this->db->query($query, array($person_id, $dokument_kurzbz, $person_id, $dokument_kurzbz));
		
		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}