<?php
class Studentuebung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_studentuebung';
		$this->pk = array('uebung_id', 'student_uid');
	}
}
