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
		if (! $this->fhc_db_acl->isBerechtigt($this->getBerechtigungKurzbz('public.tbl_vorlagedokument'), 's'))
			return $this->_error(lang('fhc_'.FHC_NORIGHT).' -> '.$this->getBerechtigungKurzbz('public.tbl_vorlagedokument'), FHC_MODEL_ERROR);

		$result = null;

		$qry = "SELECT vorlagedokument_id, sort, vorlagestudiengang_id, dokument_kurzbz, bezeichnung
						FROM public.tbl_vorlagedokument
							JOIN public.tbl_dokument USING(dokument_kurzbz)
						WHERE vorlagestudiengang_id=?
						ORDER BY sort ASC
						";

		$result = $this->db->query($qry, array($vorlagestudiengang_id));


		if (is_object($result))
			return $this->_success($result->result());
		else
			return $this->_error($this->db->error(), FHC_DB_ERROR);
	}
}
