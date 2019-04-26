<?php
class Entwicklungsteam_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_entwicklungsteam';
		$this->pk = array('studiengang_kz', 'mitarbeiter_uid');
	}
}
