<?php
class Preinteressent_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_preinteressent';
		$this->pk = 'preinteressent_id';
	}
}
