<?php
class Zeugnisnote_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_zeugnisnote';
		$this->pk = array('studiensemester_kurzbz', 'student_uid', 'lehrveranstaltung_id');
		$this->hasSequence = false;
	}
}
