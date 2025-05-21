<?php
class Funktion_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_funktion';
		$this->pk = 'funktion_kurzbz';
	}

	/**
	 * Get Functions by eventQuery string. Use with autocomplete event queries in Function Component
	 * @param $eventQuery String
	 * @return array
	 */
	public function getAutocompleteSuggestions($eventQuery)
	{
		$this->addSelect('funktion_kurzbz, beschreibung, aktiv');
		$this->addSelect("beschreibung AS label");
		$this->addOrder('beschreibung', 'ASC');

		if($eventQuery === null)
		{
			return $this->load();
		}

		return $this->loadWhere("
			funktion_kurzbz ILIKE '%". $this->escapeLike($eventQuery). "%'
			OR beschreibung ILIKE '%". $this->escapeLike($eventQuery). "%'
		");
	}
}
