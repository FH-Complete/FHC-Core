<?php
class Ferien_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_ferien';
		$this->pk = array('studiengang_kz', 'bezeichnung');
	}
}
