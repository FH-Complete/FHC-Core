<?php
class Adresse_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_adresse';
		$this->pk = 'adresse_id';
	}


	/**
	 * gets person data from uid
	 * @param $uid
	 * @return array
	 */
	public function getZustellAdresse($person_id)
	{
		return $this->loadWhere(array('person_id' => $person_id, 'zustelladresse'=> true));
	}
}
