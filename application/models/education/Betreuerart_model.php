<?php
class Betreuerart_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_betreuerart';
		$this->pk = 'betreuerart_kurzbz';
	}
}
