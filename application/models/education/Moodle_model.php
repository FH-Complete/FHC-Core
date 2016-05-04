<?php
class Moodle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_moodle';
		$this->pk = 'moodle_id';
	}
}
