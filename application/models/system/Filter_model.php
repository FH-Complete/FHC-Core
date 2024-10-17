<?php
class Filter_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_filter';
		$this->pk = 'filter_id';
	}
}
