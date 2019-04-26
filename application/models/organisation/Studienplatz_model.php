<?php
class Studienplatz_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_studienplatz';
		$this->pk = 'studienplatz_id';
	}
}
