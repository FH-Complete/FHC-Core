<?php
class Preoutgoing_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_preoutgoing';
		$this->pk = 'preoutgoing_id';
	}
}
