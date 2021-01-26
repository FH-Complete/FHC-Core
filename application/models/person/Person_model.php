<?php

class Person_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_person';
		$this->pk = 'person_id';

		$this->load->model('person/kontakt_model', 'KontaktModel');
		$this->load->model('person/adresse_model', 'AdresseModel');
	}

	/**
	 * getPersonKontaktByZugangscode
	 */
	public function getPersonKontaktByZugangscode($zugangscode, $email)
	{
		$this->addJoin('public.tbl_kontakt', 'person_id');

		return $this->loadWhere(array('zugangscode' => $zugangscode, 'kontakt' => $email));
	}

	/**
	 * checkBewerbung
	 */
	public function checkBewerbung($email, $studiensemester_kurzbz = null)
	{
		$checkBewerbungQuery = '';
		$parametersArray = array($email, $email, $email);

		if (is_null($studiensemester_kurzbz))
		{
			$checkBewerbungQuery = 'SELECT DISTINCT p.person_id, p.zugangscode, p.insertamum
 									  FROM public.tbl_person p JOIN public.tbl_kontakt k ON p.person_id = k.person_id
							     LEFT JOIN public.tbl_benutzer b ON p.person_id = b.person_id
								     WHERE k.kontakttyp = \'email\'
									   AND (kontakt = ? OR alias || \'@' . DOMAIN . '\' = ? OR uid || \'@' . DOMAIN . '\' = ?)
								  ORDER BY p.insertamum DESC
								     LIMIT 1';
		}
		else
		{
			$checkBewerbungQuery = 'SELECT DISTINCT p.person_id, p.zugangscode, p.insertamum
									  FROM public.tbl_person p JOIN public.tbl_kontakt k ON p.person_id = k.person_id
								 LEFT JOIN public.tbl_benutzer b ON p.person_id = b.person_id
									  JOIN public.tbl_prestudent ps ON p.person_id = ps.person_id
									  JOIN public.tbl_prestudentstatus pst ON pst.prestudent_id = ps.prestudent_id
									 WHERE k.kontakttyp = \'email\'
									   AND (kontakt = ? OR alias || \'@' . DOMAIN . '\' = ? OR uid || \'@' . DOMAIN . '\' = ?)
									   AND studiensemester_kurzbz = ?
								  ORDER BY p.insertamum DESC
									 LIMIT 1';

			array_push($parametersArray, $studiensemester_kurzbz);
		}

		return $this->execQuery($checkBewerbungQuery, $parametersArray);
	}

	/**
	 * updatePerson
	 */
	public function updatePerson($person)
	{
		if (isset($person['svnr']) && $person['svnr'] != '')
		{
			$this->PersonModel->addOrder('svnr', 'DESC');
			$result =  $this->PersonModel->loadWhere(array(
				'person_id != ' => $person['person_id'],
				'SUBSTRING(svnr FROM 1 FOR 10) = ' => $person['svnr'])
			);
			if (hasData($result))
			{
				if (count($result->retval) == 1 && $result->retval[0]->svnr == $person['svnr'])
				{
					$person['svnr'] = $person['svnr'] . 'v1';
				}
				else
				{
					$person['svnr'] = $person['svnr'] . 'v' . ($result->retval[0]->svnr{11} + 1);
				}
			}
		}

		return $this->PersonModel->update($person['person_id'], $person);
	}

	/**
	 * getPersonFromStatus
	 */
	public function getPersonFromStatus($status_kurzbz, $von, $bis)
	{
		$this->addJoin('public.tbl_prestudent', 'person_id');

		$this->addJoin('public.tbl_benutzer', 'person_id');

		$result = $this->loadTree(
			'public.tbl_person',
			array(
				'public.tbl_prestudent',
				'public.tbl_benutzer'
			),
			'EXISTS (
				SELECT
					1
				FROM
					public.tbl_prestudentstatus
					JOIN public.tbl_prestudent USING(prestudent_id)
				WHERE
					person_id=tbl_person.person_id
					AND status_kurzbz='.$this->escape($status_kurzbz).'
					AND datum >= '.$this->escape($von).'
					AND datum <= '.$this->escape($bis).'
				)',
			array(
				'prestudenten',
				'benutzer'
			)
		);

		return $result;
	}

	/**
	 * Searches a Person
	 * @param $filter Term to search for.
	 * @return DB-result
	 */
	public function searchPerson($filter)
	{
		$this->addSelect('vorname, nachname, gebdatum, person_id');
		$result = $this->loadWhere(
			'lower(nachname) like '.$this->db->escape('%'.$filter.'%')."
			OR lower(vorname) like ".$this->db->escape('%'.$filter.'%')."
			OR lower(nachname || ' ' || vorname) like ".$this->db->escape('%'.$filter.'%')."
			OR lower(vorname || ' ' || nachname) like ".$this->db->escape('%'.$filter.'%'));

		return $result;
	}

	/**
	 * gets Stammdaten for a person, including contactdata in textform from other tables
	 * nation, kontakt, adresse
	 * @param $person_id
	 * @param bool $zustellung_only, when true, retrieve only Zustellkontakte
	 * @return array, null when no person found
	 */
	public function getPersonStammdaten($person_id, $zustellung_only = false)
	{
		$this->addSelect('public.tbl_person.*, s.kurztext as staatsbuergerschaft, g.kurztext as geburtsnation');
		$this->addJoin('bis.tbl_nation s', 'public.tbl_person.staatsbuergerschaft = s.nation_code', 'LEFT');
		$this->addJoin('bis.tbl_nation g', 'public.tbl_person.geburtsnation = g.nation_code', 'LEFT');

		$person = $this->load($person_id);

		if($person->error) return $person;

		//return null if not found
		if(count($person->retval) < 1)
			return success(null);

		$this->KontaktModel->addDistinct();
		$this->KontaktModel->addSelect('kontakttyp, anmerkung, kontakt, zustellung');
		$this->KontaktModel->addOrder('kontakttyp');
		$where = $zustellung_only === true ? array('person_id' => $person_id, 'zustellung' => true) : array('person_id' => $person_id);
		$kontakte = $this->KontaktModel->loadWhere($where);
		if($kontakte->error) return $kontakte;

		$where = $zustellung_only === true ? array('person_id' => $person_id, 'zustelladresse' => true) : array('person_id' => $person_id);
		$this->AdresseModel->addSelect('public.tbl_adresse.*, bis.tbl_nation.kurztext AS nationkurztext');
		$this->AdresseModel->addJoin('bis.tbl_nation', 'tbl_adresse.nation = tbl_nation.nation_code', 'LEFT');
		$adressen = $this->AdresseModel->loadWhere($where);
		if($adressen->error) return $adressen;

		$stammdaten = $person->retval[0];
		$stammdaten->kontakte = $kontakte->retval;
		$stammdaten->adressen = $adressen->retval;

		return success($stammdaten);
	}

	/**
	 * gets person data from uid
	 * @param $uid
	 * @return array
	 */
	public function getByUid($uid)
	{
		$this->addSelect('vorname, nachname, gebdatum, person_id, bpk, matr_nr');
		$this->addJoin('tbl_benutzer', 'person_id');

		return $this->loadWhere(array('uid' => $uid));
	}

	/**
	 * Retrieves the language of the user by the UID
	 * Gets all the persons related to the given UID and starting from the most recent person in DB
	 * tries to find a valid language (!= null), if found is returned, otherwise is returned the
	 * default global language of the system
	 */
	public function getLanguage($uid)
	{
		$this->addSelect('public.tbl_person.sprache');
		$this->addJoin('public.tbl_benutzer', 'person_id');
		$this->addJoin('public.tbl_sprache', 'sprache');
		$this->addOrder('public.tbl_person.updateamum', 'DESC');
		$this->addOrder('public.tbl_person.insertvon', 'DESC');

		return $this->loadWhere(array('uid' => $uid, 'content' => true));
	}

	/**
	 * Checks if a person has a Bewerberstatus and reihungstestangetreten = true
	 * @param $person_id
	 * @param $studiensemester_kurzbz
	 * @return array
	 */
	public function hasBewerber($person_id, $studiensemester_kurzbz, $studiengangtyp = null)
	{
		$parametersArray = array($person_id, $studiensemester_kurzbz);

		$qry = "SELECT count(*) AS anzahl_bewerber FROM public.tbl_person
				JOIN public.tbl_prestudent USING (person_id)
				JOIN public.tbl_prestudentstatus ON tbl_prestudentstatus.prestudent_id = tbl_prestudent.prestudent_id";

		if (isset($studiengangtyp))
		{
			$qry .= " JOIN lehre.tbl_studienplan USING(studienplan_id)
					 JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					 JOIN public.tbl_studiengang ON tbl_studienordnung.studiengang_kz = tbl_studiengang.studiengang_kz";
		}

		$qry .=	" WHERE person_id = ?
				AND studiensemester_kurzbz = ?
				AND tbl_prestudentstatus.status_kurzbz = 'Bewerber'
				AND reihungstestangetreten";

		if (isset($studiengangtyp))
		{
			$parametersArray[] = $studiengangtyp;
			$qry .= " AND tbl_studiengang.typ = ?";
		}

		return $this->execQuery($qry, $parametersArray);
	}
	
	/**
	 * Get full name of given uid. (Vorname Nachname)
	 * @param $uid
	 * @return array
	 */
	public function getFullName($uid)
	{
		if (!$result = getData($this->getByUid($uid))[0])
		{
			show_error('Failed loading person');
		}
		
		return success($result->vorname. ' '. $result->nachname);
	}
}
