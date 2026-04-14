<?php
class ClassTimeSlot_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_unterrichtszeiten';
		$this->pk = 'unterrichtszeit_id';
	}

}
