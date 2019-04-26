<?php
class Infoscreen_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_infoscreen';
		$this->pk = 'infoscreen_id';
	}
}
