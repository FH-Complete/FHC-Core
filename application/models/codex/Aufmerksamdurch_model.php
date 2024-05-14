<?php
class Aufmerksamdurch_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_aufmerksamdurch';
		$this->pk = 'aufmerksamdurch_kurzbz';
	}
}
