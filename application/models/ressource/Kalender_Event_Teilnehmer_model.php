<?php
class
Kalender_Event_Teilnehmer_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_kalender_event_teilnehmer';
		$this->pk = array('kalender_event_teilnehmer_id');
		$this->hasSequence = false;
	}
}
