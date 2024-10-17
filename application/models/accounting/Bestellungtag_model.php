<?php
class Bestellungtag_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_bestellungtag';
		$this->pk = array('bestellung_id', 'tag');
	}
}
