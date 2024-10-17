<?php
class Zeugnis_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_zeugnis';
		$this->pk = 'zeugnis_id';
	}
}
