<?php
class Beispiel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_beispiel';
		$this->pk = 'beispiel_id';
	}
}
