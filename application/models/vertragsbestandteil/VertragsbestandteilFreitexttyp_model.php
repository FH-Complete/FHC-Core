<?php

class VertragsbestandteilFreitexttyp_model extends DB_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_freitexttyp';
		$this->pk = 'freitexttyp_kurzbz';
	}
}
