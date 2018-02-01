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
		if (isError($ent = $this->isEntitled('public.tbl_person', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_kontakt', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_benutzer', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;

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
		// Checks if the operation is permitted by the API caller
		if (isError($ent = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;

		$this->addJoin('public.tbl_prestudent', 'person_id');

		$result = $this->loadTree(
			'public.tbl_person',
			array(
				'public.tbl_prestudent'
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
				'prestudenten'
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
	 * @return array, null when no person found
	 */
	public function getPersonStammdaten($person_id)
	{
		$this->addSelect('public.tbl_person.*, s.kurztext as staatsbuergerschaft, g.kurztext as geburtsnation');
		$this->addJoin('bis.tbl_nation s', 'public.tbl_person.staatsbuergerschaft = s.nation_code', 'LEFT');
		$this->addJoin('bis.tbl_nation g', 'public.tbl_person.geburtsnation = g.nation_code', 'LEFT');

		$person = $this->load($person_id);

		if($person->error)
			return error($person->retval);

		//return null if not found
		if(count($person->retval) < 1)
			return success(null);

		$this->load->model('person/kontakt_model', 'KontaktModel');
		$this->load->model('person/adresse_model', 'AdresseModel');

		$this->KontaktModel->addDistinct();
		$this->KontaktModel->addSelect('kontakttyp, anmerkung, kontakt, zustellung');
		$this->KontaktModel->addOrder('kontakttyp');
		$kontakte = $this->KontaktModel->loadWhere(array('person_id' => $person_id));
		if($kontakte->error)
			return error($kontakte->retval);

		$adressen = $this->AdresseModel->loadWhere(array('person_id' => $person_id));
		if($adressen->error)
			return error($adressen->retval);

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
		$this->addSelect('vorname, nachname, gebdatum, person_id');
		$this->addJoin('tbl_benutzer', 'person_id');

		return $this->loadWhere(array('uid' => $uid));
	}

}
