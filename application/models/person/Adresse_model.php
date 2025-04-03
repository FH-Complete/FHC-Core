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
	 * Get Zustelladress of given person.
	 * @param string $person_id
	 * @param string $select
	 * @return array
	 */
	public function getZustellAdresse($person_id, $select = '*')
	{
		$this->addSelect($select);
		return $this->loadWhere(array('person_id' => $person_id, 'zustelladresse'=> true));
	}
}