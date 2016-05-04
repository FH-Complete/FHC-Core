<?php
class Beschaeftigungsausmass_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_beschaeftigungsausmass';
		$this->pk = 'beschausmasscode';
	}
}
