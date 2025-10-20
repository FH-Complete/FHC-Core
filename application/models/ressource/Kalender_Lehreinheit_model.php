<?php
class Kalender_Lehreinheit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_lehreinheit';
		$this->pk = array('kalender_id','lehreinheit_id');
		$this->hasSequence = false;
	}
}
