<?php
class ClassTimeSlotValidityPeriod_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_unterrichtszeiten_gueltigkeit';
		$this->pk = 'unterrichtszeitengueltigkeit_id';
	}

}
