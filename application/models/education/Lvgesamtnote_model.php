<?php
class Lvgesamtnote_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_lvgesamtnote';
		$this->pk = array('student_uid', 'studiensemester_kurzbz', 'lehrveranstaltung_id');
		$this->hasSequence = false;
	}
}
