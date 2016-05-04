<?php
class Studienplan_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_studienplan';
		$this->pk = 'studienplan_id';
	}
}