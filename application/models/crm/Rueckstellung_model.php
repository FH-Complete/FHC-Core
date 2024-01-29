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
							array_to_json(bezeichnung_mehrsprachig::varchar[])->>'.$language_index . ' as bezeichnung');
		$this->addOrder('datum_bis', 'DESC');
		
		$where['person_id'] = $person_id;
		
		if (!isEmptyString($status))
			$where['status_kurzbz'] = $status;

		return $this->loadWhere($where);
	}
}