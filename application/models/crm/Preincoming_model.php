<?php
class Preincoming_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_preincoming';
		$this->pk = 'preincoming_id';
	}
}
