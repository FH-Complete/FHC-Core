<?php
class Stundenplandev_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stundenplandev';
		$this->pk = 'stundenplandev_id';
	}
}
