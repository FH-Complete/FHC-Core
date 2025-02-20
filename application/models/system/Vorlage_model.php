<?php

class Vorlage_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_vorlage';
		$this->pk = 'vorlage_kurzbz';
	}

	/**
	 * Returns mime types
	 */
	public function getMimeTypes()
	{
		$query = 'SELECT DISTINCT mimetype FROM public.tbl_vorlage ORDER BY mimetype';

		return $this->execQuery($query);
	}

	/**
	 * Returns all Vorlagen for archive
	 */
	public function getArchivVorlagen()
	{
		$query ="SELECT * FROM public.tbl_vorlage WHERE archivierbar=true ORDER BY bezeichnung";

		return $this->execQuery($query);
	}
}
