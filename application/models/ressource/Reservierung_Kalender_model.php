<?php
class Reservierung_Kalender_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'sync.tbl_reservierung_kalender';
		$this->pk = 'reservierung_kalender_id';
	}
}
