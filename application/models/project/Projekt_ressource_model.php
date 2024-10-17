<?php
class Projekt_ressource_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'fue.tbl_projekt_ressource';
		$this->pk = 'projekt_ressource_id';
	}
}
