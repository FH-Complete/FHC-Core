<?php
class Legesamtnote_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_legesamtnote';
		$this->pk = array('lehreinheit_id', 'student_uid');
	}
}
