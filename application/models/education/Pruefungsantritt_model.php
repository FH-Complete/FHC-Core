<?php
class Pruefungsantritt_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_abschlusspruefung_antritt';
		$this->pk = 'pruefungsantritt_kurzbz';
	}
}