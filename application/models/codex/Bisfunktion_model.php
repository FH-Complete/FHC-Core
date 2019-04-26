<?php
class Bisfunktion_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_bisfunktion';
		$this->pk = array('studiengang_kz', 'bisverwendung_id');
	}
}
