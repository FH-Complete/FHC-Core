<?php
class Kennzeichen_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_kennzeichen';
		$this->pk = 'kennzeichen_id';
	}

}
