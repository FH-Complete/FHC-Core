<?php
class Studiengangstyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiengangstyp';
		$this->pk = 'typ';
	}
}
