<?php

class VertragsbestandteilTyp_model extends DB_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteiltyp';
		$this->pk = 'vertragsbestandteiltyp_kurzbz';
	}
}
