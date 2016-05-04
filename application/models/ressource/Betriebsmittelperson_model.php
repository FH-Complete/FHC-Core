<?php
class Betriebsmittelperson_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmittelperson';
		$this->pk = 'betriebsmittelperson_id';
	}
}
