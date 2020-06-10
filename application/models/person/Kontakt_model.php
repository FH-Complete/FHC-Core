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
}
