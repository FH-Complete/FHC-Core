<?php
class Ausbildung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'bis.tbl_ausbildung';
		$this->pk = 'ausbildungcode';
	}
}
