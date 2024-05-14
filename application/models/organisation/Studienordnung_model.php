<?php
class Studienordnung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_studienordnung';
		$this->pk = 'studienordnung_id';
	}
}
