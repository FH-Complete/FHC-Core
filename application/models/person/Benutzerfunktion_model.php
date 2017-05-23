<?php

class Benutzerfunktion_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzerfunktion';
		$this->pk = 'benutzerfunktion_id';
	}
	
	/**
	 * 
	 */
	public function getByPersonId($person_id)
	{
		// Join with the table 
		$this->addJoin('public.tbl_benutzer', 'uid');
		
		return $this->loadWhere(array('person_id' => $person_id));
	}
}