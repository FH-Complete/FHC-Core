<?php
class Reservierung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_reservierung';
		$this->pk = 'reservierung_id';
	}
}
