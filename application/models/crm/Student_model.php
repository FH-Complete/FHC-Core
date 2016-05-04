<?php
class Student_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_student';
		$this->pk = 'student_uid';
	}
}
