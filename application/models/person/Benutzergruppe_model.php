<?php
class Benutzergruppe_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzergruppe';
		$this->pk = array('gruppe_kurzbz', 'uid');
		$this->hasSequence = false;
	}
}
