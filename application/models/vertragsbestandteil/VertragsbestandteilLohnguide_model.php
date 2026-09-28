<?php

class VertragsbestandteilLohnguide_model extends DB_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_lohnguide';
		$this->pk = 'vertragsbestandteil_id';
	}
}
