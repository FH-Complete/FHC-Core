<?php
class Studiensemester_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiensemester';
		$this->pk = 'studiensemester_kurzbz';
	}
}
