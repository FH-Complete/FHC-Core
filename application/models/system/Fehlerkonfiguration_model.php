<?php
class Fehlerkonfiguration_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'system.tbl_fehler_konfiguration';
		$this->pk = array('konfigurationstyp_kurzbz', 'fehlercode');
	}

	/**
	 *
	 * @param
	 * @return object success or error
	 */
	public function getKonfiguration()
	{

	}
}
