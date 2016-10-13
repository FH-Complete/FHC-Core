<?php

class Dokumentprestudent_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_dokumentprestudent';
		$this->pk = array('prestudent_id', 'dokument_kurzbz');
	}
	
	public function setAccepted($prestudent_id, $studiengang_kz)
	{
		if (($chkRights = $this->isEntitled('public.tbl_dokumentprestudent', 'i', FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $chkRights;
		
		$result = null;
		
		if (is_numeric($prestudent_id) && is_numeric($studiengang_kz))
		{
			$query = 'INSERT INTO public.tbl_dokumentprestudent (dokument_kurzbz, prestudent_id, insertamum) (
					SELECT ds.dokument_kurzbz,
							p.prestudent_id,
							NOW() AS insertamum
						FROM (SELECT DISTINCT person_id, dokument_kurzbz FROM public.tbl_akte) a
				INNER JOIN public.tbl_prestudent p USING(person_id)
				INNER JOIN public.tbl_dokumentstudiengang ds USING(dokument_kurzbz, studiengang_kz)
				LEFT JOIN public.tbl_dokumentprestudent dp USING(dokument_kurzbz, prestudent_id)
					WHERE ds.onlinebewerbung IS TRUE
						AND (dp.dokument_kurzbz IS NULL AND dp.prestudent_id IS NULL)
						AND p.prestudent_id = ?
						AND ds.studiengang_kz = ?
					)';
			
			$result = $this->db->query($query, array($prestudent_id, $studiengang_kz));
		}
		
		if ($result)
			return $this->_success($result);
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
	
	public function setAcceptedDocuments($prestudent_id, $dokument_kurzbz)
	{
		if (($chkRights = $this->isEntitled('public.tbl_dokumentprestudent', 'i', FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $chkRights;
		
		$result = null;
		
		if (is_numeric($prestudent_id) && is_array($dokument_kurzbz) && count($dokument_kurzbz) > 0)
		{
			$query = 'INSERT INTO public.tbl_dokumentprestudent (dokument_kurzbz, prestudent_id, insertamum) (
						SELECT d.dokument_kurzbz,
								? AS prestudent_id,
								NOW() AS insertamum
						  FROM public.tbl_dokument d
				 		 WHERE d.dokument_kurzbz IN ?
						   AND d.dokument_kurzbz NOT IN (
								SELECT dokument_kurzbz
								  FROM public.tbl_dokumentprestudent
								 WHERE prestudent_id = ?
							)
					)';
			
			$result = $this->db->query($query, array($prestudent_id, $dokument_kurzbz, $prestudent_id));
		}
		
		if ($result)
			return $this->_success($result);
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}