<?php
class Studentlehrverband_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studentlehrverband';
		$this->pk = array('studiensemester_kurzbz', 'student_uid');
		$this->hasSequence = false;

		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
	}
}
