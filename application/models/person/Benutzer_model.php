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
		$this->pk = 'uid';
	}

	public function getFromPersonId($person_id)
	{
		/*$this->addSelect('uid, aktiv, alias');*/
		$this->loadWhere(array('person_id' => $person_id));
	}

}
