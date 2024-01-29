<?php
class Studentbeispiel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_studentbeispiel';
		$this->pk = array('beispiel_id', 'student_uid');
	}
}
