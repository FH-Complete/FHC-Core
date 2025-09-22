<?php

class Fotostatusperson_model extends DB_Model
{

	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_person_fotostatus';
		$this->pk = 'person_fotostatus_id';
	}

	public function getLastFotoStatus($person_id)
	{
		$this->addOrder('datum', 'DESC');
		$this->addOrder('person_fotostatus_id', 'DESC');
		$this->addLimit(1);

		return $this->loadWhere(array('person_id' => $person_id));
	}

}