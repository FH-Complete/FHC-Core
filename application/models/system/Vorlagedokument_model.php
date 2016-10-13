<?php

class Vorlagedokument_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlagedokument';
		$this->pk = 'vorlagedokument_id';
	}

	/**
	 *
	 */
	public function loadDokumenteFromVorlagestudiengang($vorlagestudiengang_id)
	{
		// Checks if the operation is permitted by the API caller
		if (($chkRights = $this->isEntitled('public.tbl_vorlagedokument', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)) !== true)
			return $chkRights;
		
		$result = null;
		
		$qry = 'SELECT vorlagedokument_id,
						sort,
						vorlagestudiengang_id,
						dokument_kurzbz,
						bezeichnung
				  FROM public.tbl_vorlagedokument
				  JOIN public.tbl_dokument USING(dokument_kurzbz)
				 WHERE vorlagestudiengang_id = ?
			  ORDER BY sort ASC';
		
		$result = $this->db->query($qry, array($vorlagestudiengang_id));
		
		if (is_object($result))
			return success($result->result());
		else
			return error($this->db->error(), FHC_DB_ERROR);
	}
}