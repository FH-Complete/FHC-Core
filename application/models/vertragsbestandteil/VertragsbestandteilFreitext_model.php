<?php

class VertragsbestandteilFreitext_model extends DB_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_freitext';
		$this->pk = 'vertragsbestandteil_id';
	}
}
