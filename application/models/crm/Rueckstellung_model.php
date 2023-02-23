<?php

class Rueckstellung_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_rueckstellung';
		$this->pk = 'rueckstellung_id';
		$this->hasSequence = true;
	}
	
	public function getByPersonId($person_id, $status = null)
	{
		$language_index = getUserLanguage() == 'German' ? 0 : 1;
		
		$this->addLimit(1);
		$this->addJoin('tbl_rueckstellung_status', 'status_kurzbz');
		$this->addSelect('*,
							array_to_json(bezeichnung_mehrsprachig::varchar[])->>'.$language_index . 'as bezeichnung');
		$this->addOrder('datum_bis', 'DESC');
		
		$where['person_id'] = $person_id;
		
		if (!isEmptyString($status))
			$where['status_kurzbz'] = $status;

		return $this->loadWhere($where);
	}

	public function set($person_id, $datum_bis, $status_kurzbz, $uid)
	{
		$exists = $this->getByPersonId($person_id, $status_kurzbz);
		
		if (hasData($exists))
			return error("Rueckstellung entry already exists");
		
		return $this->insert(array('person_id' => $person_id, 'status_kurzbz' => $status_kurzbz, 'datum_bis' => $datum_bis, 'insertvon' => $uid));
	}
}