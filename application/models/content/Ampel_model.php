<?php
class Ampel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_ampel';
		$this->pk = 'ampel_id';
	}
}
