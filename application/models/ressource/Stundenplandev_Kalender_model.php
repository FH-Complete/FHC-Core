<?php
class Stundenplandev_Kalender_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'sync.tbl_stundenplandev_kalender';
		$this->pk = 'stundenplandev_kalender_id';
	}
}
