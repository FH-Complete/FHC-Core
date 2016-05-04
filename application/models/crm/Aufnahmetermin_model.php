<?php
class Aufnahmetermin_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_aufnahmetermin';
		$this->pk = 'aufnahmetermin_id';
	}
}
