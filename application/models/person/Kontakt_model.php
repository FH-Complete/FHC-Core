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
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
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
	 * Laedt einen Kontakt eines Standortes
	 * Es wird nur der erste Eintrag zurueckgeliefert!
	 * @param $standort_id
	 * @param $kontakttyp
	 */
	public function getFirmaKontakttyp($standort_id, $kontakttyp)
	{
		if (!is_numeric($standort_id))
		{
			return error('StandortID ist ungueltig');
		}

		$qry = "SELECT kontakt, kontakt_id FROM public.tbl_kontakt WHERE standort_id=? AND kontakttyp=? ORDER BY kontakt_id LIMIT 1;";

		return $this->execQuery($qry, array('standort_id' => $standort_id, 'kontakttyp' => $kontakttyp));
	}

	/**
	 * Gets Firmentelefon for a uid, can be Vorwahl with Telefonklappe or Firmenhandy
	 * @param $uid
	 */
	public function getFirmentelefon($uid)
	{
		$firmentelefon = success(array());

		$this->MitarbeiterModel->addSelect('standort_id, telefonklappe, person_id');
		$this->MitarbeiterModel->addJoin('public.tbl_benutzer', 'tbl_mitarbeiter.mitarbeiter_uid = tbl_benutzer.uid');
		$mitarbeiter = $this->MitarbeiterModel->load(array('uid' => $uid));

		if (hasData($mitarbeiter))
		{
			$mitarbeiter = getData($mitarbeiter);
			if (isEmptyString($mitarbeiter[0]->telefonklappe))
			{
				$this->addSelect('kontakt');
				$this->addOrder('updateamum, insertamum', 'DESC');
				$this->addLimit(1);
				$firmenhandy = $this->loadWhere(array('person_id' => $mitarbeiter[0]->person_id, 'kontakttyp' => 'firmenhandy'));
				if (hasData($firmenhandy))
				{
					$firmenhandy = getData($firmenhandy);
					$firmentelefon = success(array('kontakt' => $firmenhandy[0]->kontakt, 'telefonklappe' => ''));
				}
			}
			else
			{
				$firmaKontakttyp = $this->getFirmaKontakttyp($mitarbeiter[0]->standort_id, 'telefon');
				if (hasData($firmaKontakttyp))
				{
					$vorwahl = getData($firmaKontakttyp);
					$vorwahl = $vorwahl[0]->kontakt;
					$firmentelefon = success(array('kontakt' => $vorwahl, 'telefonklappe' => $mitarbeiter[0]->telefonklappe));
				}
			}
		}
		return $firmentelefon;
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

	/**
	 * Get all latest phones of person where zustellung is true. Ordered by
	 * telefon > mobil > firmenhandy > else.
	 * @param string person_id
	 */
	public function getPhones_byPerson($person_id)
	{
		$qry = '
		WITH latest_phones AS(
			SELECT DISTINCT ON (kontakttyp) kontakttyp, kontakt
			FROM public.tbl_kontakt kontakt
			LEFT JOIN public.tbl_standort USING (standort_id)
			LEFT JOIN public.tbl_firma USING (firma_id)
			WHERE person_id = ?
			AND zustellung
			AND kontakttyp IN (\'telefon\', \'mobil\', \'firmenhandy\')
			ORDER BY kontakttyp, kontakt, kontakt.updateamum
			)

		SELECT * FROM latest_phones
		ORDER BY
		CASE
			WHEN kontakttyp = \'telefon\' THEN 0
			WHEN kontakttyp = \'mobil\' THEN 1
			WHEN kontakttyp = \'firmenhandy\' THEN 2
			ELSE 3
		END
		';

		return $this->execQuery($qry, array($person_id));
	}

	/**
	 * Loads main contact, i.e. most recent Zustellkontakt with the given kontakttypes.
	 * @param $person_id
	 * @param $kontakttypen array of kontakttypen, one chronologically last Zustellkontakt for all given types
	 * @return object
	 */
	public function getZustellKontakt($person_id, $kontakttypen)
	{
		if (is_string($kontakttypen))
			$kontakttypen = array($kontakttypen);

		if (!isEmptyArray($kontakttypen))
		{
			$qry = "
			SELECT
				kontakt
			FROM
				public.tbl_kontakt
			WHERE person_id = ?
			AND kontakttyp IN ?
			ORDER BY zustellung DESC NULLS LAST, updateamum DESC, insertamum DESC
			LIMIT 1";

			return $this->execQuery($qry, array($person_id, $kontakttypen));
		}
		else
			return success(array());
	}
}
