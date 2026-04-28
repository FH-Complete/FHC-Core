<?php
class Kalender_Event_Rolle_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_event_rolle';
		$this->pk = array('rolle_kurzbz');
		$this->hasSequence = false;
	}
}
