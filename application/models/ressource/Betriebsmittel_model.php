<?php
class Betriebsmittel_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittel';
		$this->pk = 'betriebsmittel_id';
	}
}
