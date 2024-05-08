<?php
class Bestelldetailtag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_bestelldetailtag';
		$this->pk = array('bestelldetail_id', 'tag');
	}
}
