<?php
class Feedback_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_feedback';
		$this->pk = 'feedback_id';
	}
}
