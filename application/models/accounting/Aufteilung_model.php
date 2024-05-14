<?php
class Aufteilung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_aufteilung';
		$this->pk = 'aufteilung_id';
	}
}
