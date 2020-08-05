<?php

class Kontakt_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_kontakt';
		$this->pk = 'kontakt_id';
	}

	public function getWholeKontakt($kontakt_id, $person_id = null, $kontakttyp = null)
    {
		$result = null;

		$this->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
		$this->addJoin('public.tbl_firma', 'firma_id', 'LEFT');

		if (isset($kontakt_id))
		{
			$result = $this->load($kontakt_id);
		}
		else
		{
			$parametersArray = array();

			if (!is_null($person_id))
			{
				$parametersArray['person_id'] = $person_id;
			}
			if (!is_null($kontakttyp))
			{
				$parametersArray['kontakttyp'] = $kontakttyp;
			}

			if (count($parametersArray) > 0)
			{
				$result = $this->loadWhere($parametersArray);
			}
		}

		return $result;
    }

	/**
	 *
	 */
	public function getContactByPersonId($person_id, $kontakttyp)
	{
		$sql = 'SELECT kontakt
				  FROM public.tbl_kontakt
				 WHERE zustellung = TRUE
				   AND person_id = ?
				   AND kontakttyp = ?
			  ORDER BY updateamum, insertamum';

		return $this->execQuery($sql, array($person_id, $kontakttyp));
	}
	
	/**
	 * Get all latest contact data of person, where Zustellung is true
	 * @param $person_id
	 * @return array
	 */
	public function getAll_byPersonID($person_id)
	{
		$this->addSelect('DISTINCT ON (kontakttyp) kontakttyp, kontakt');
		$this->addJoin('public.tbl_standort', 'standort_id', 'LEFT');
		$this->addJoin('public.tbl_firma', 'firma_id', 'LEFT');
		$this->addOrder('kontakttyp, kontakt, tbl_kontakt.updateamum, tbl_kontakt.insertamum');
		
		return $this->loadWhere(array(
			'zustellung' => TRUE,
			'person_id' => $person_id
		));
	}
}
