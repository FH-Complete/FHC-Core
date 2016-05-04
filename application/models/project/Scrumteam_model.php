<?php
class Scrumteam_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_scrumteam';
		$this->pk = 'scrumteam_kurzbz';
	}
}
