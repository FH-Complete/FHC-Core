<?php
class Ressource_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_ressource';
		$this->pk = 'ressource_id';
	}
}
