<?php
class ClassTimeSlotType_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_unterrichtszeiten_typ';
		$this->pk = 'unterrichtszeit_id';
	}

}
