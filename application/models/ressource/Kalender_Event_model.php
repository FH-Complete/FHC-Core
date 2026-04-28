<?php
class Kalender_Event_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_event';
		$this->pk = array('kalender_id');
		$this->hasSequence = false;
	}
}
