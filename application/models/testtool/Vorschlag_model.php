<?php
class Vorschlag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'testtool.tbl_vorschlag';
		$this->pk = 'vorschlag_id';
	}
}
