<?php
class Bismeldestichtag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bismeldestichtag';
		$this->pk = 'meldestichtag_id';
	}
}
