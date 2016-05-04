<?php
class Betriebsmitteltyp_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'wawi.tbl_betriebsmitteltyp';
		$this->pk = 'betriebsmitteltyp';
	}
}
