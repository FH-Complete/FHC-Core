<?php
class Hauptberuf_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_hauptberuf';
		$this->pk = 'hauptberufcode';
	}
}
