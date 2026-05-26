<?php

class VertragsbestandteilKollektivvertrag_model extends DB_Model
{
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'hr.tbl_vertragsbestandteil_kollektivvertrag';
		$this->pk = 'vertragsbestandteil_id';
	}
}
