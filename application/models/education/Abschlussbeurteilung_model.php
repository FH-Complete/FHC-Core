<?php
class Abschlussbeurteilung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_abschlussbeurteilung';
		$this->pk = 'abschlussbeurteilung_kurzbz';
	}
}
