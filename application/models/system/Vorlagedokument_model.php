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
	 * loadDokumenteFromVorlagestudiengang
	 */
	public function loadDokumenteFromVorlagestudiengang($vorlagestudiengang_id)
	{
		$qry = 'SELECT vorlagedokument_id,
						sort,
						vorlagestudiengang_id,
						dokument_kurzbz,
						bezeichnung
				  FROM public.tbl_vorlagedokument
				  JOIN public.tbl_dokument USING(dokument_kurzbz)
				 WHERE vorlagestudiengang_id = ?
			  ORDER BY sort ASC';

		return $this->execQuery($qry, array($vorlagestudiengang_id));
	}
}
