<?php
class Resturlaub_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_resturlaub';
		$this->pk = 'mitarbeiter_uid';
	}
}
