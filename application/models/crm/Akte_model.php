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
		if (($isEntitled = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_dokument', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_dokumentstudiengang', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_dokumentprestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
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
		
		return $this->execQuery($query, $parametersArray);
	}
	
	/**
	 * 
	 */
	public function getAktenAccepted($person_id, $dokument_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (($isEntitled = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_dokumentprestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
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
						 CASE WHEN MAX(dp.dokument_kurzbz) IS NOT NULL THEN TRUE ELSE FALSE END AS accepted
					FROM public.tbl_akte a
			  INNER JOIN public.tbl_prestudent p USING(person_id)
			   LEFT JOIN public.tbl_dokumentprestudent dp USING(prestudent_id, dokument_kurzbz)
				   WHERE a.person_id = ?';
		
		$parametersArray = array($person_id);
		
		if (!empty($dokument_kurzbz))
		{
			$query .= ' AND a.dokument_kurzbz = ?';
			array_push($parametersArray, $dokument_kurzbz);
		}
		
		$query .= ' GROUP BY a.akte_id ORDER BY a.erstelltam';
		
		return $this->execQuery($query, $parametersArray);
	}
	
	/**
	 * 
	 */
	public function getAktenAcceptedDms($person_id, $dokument_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (($isEntitled = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('public.tbl_dokumentprestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('campus.tbl_dms', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		if (($isEntitled = $this->isEntitled('campus.tbl_dms_version', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $isEntitled;
		
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
						 CASE WHEN MAX(dp.dokument_kurzbz) IS NOT NULL THEN TRUE ELSE FALSE END AS accepted,
						 d.oe_kurzbz,
						 d.kategorie_kurzbz,
						 dv.version,
						 dv.filename,
						 dv.mimetype,
						 dv.name,
						 dv.beschreibung
					FROM public.tbl_akte a
			  INNER JOIN public.tbl_prestudent p USING(person_id)
			   LEFT JOIN public.tbl_dokumentprestudent dp ON(p.prestudent_id = dp.prestudent_id AND a.dokument_kurzbz = dp.dokument_kurzbz)
			  INNER JOIN campus.tbl_dms d ON (a.dms_id = d.dms_id AND a.dokument_kurzbz = d.dokument_kurzbz)
			  INNER JOIN (SELECT dms_id, MAX(version) AS version FROM campus.tbl_dms_version GROUP BY dms_id) dvv ON (d.dms_id = dvv.dms_id)
			  INNER JOIN campus.tbl_dms_version dv ON (dv.dms_id = dvv.dms_id AND dv.version = dvv.version)
				   WHERE a.person_id = ?';
		
		$parametersArray = array($person_id);
		
		if (!empty($dokument_kurzbz))
		{
			$query .= ' AND a.dokument_kurzbz = ?';
			array_push($parametersArray, $dokument_kurzbz);
		}
		
		$query .= ' GROUP BY a.akte_id, d.dms_id, dv.dms_id, dv.version ORDER BY a.erstelltam';
		
		return $this->execQuery($query, $parametersArray);
	}
}