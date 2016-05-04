<?php
class Dokumentprestudent_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_dokumentprestudent';
		$this->pk = array('prestudent_id', 'dokument_kurzbz');
	}
}
