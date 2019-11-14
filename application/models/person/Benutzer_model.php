<?php
class Benutzer_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_benutzer';
		$this->pk = array('uid');
		$this->hasSequence = false;
	}

	public function getFromPersonId($person_id)
	{
		return $this->loadWhere(array('person_id' => $person_id, 'aktiv' => true));
	}

}
