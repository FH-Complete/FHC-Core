<?php

class Prestudent_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_prestudent';
		$this->pk = 'prestudent_id';
	}

	/**
	 * getLastStatuses
	 */
	public function getLastStatuses($person_id, $studiensemester_kurzbz = null, $studiengang_kz = null, $status_kurzbz = null)
	{
		// Checks if the operation is permitted by the API caller
		if (isError($ent = $this->isEntitled('public.tbl_status', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_prestudent', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;
		if (isError($ent = $this->isEntitled('public.tbl_prestudentstatus', PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR)))
			return $ent;

		$query = 'SELECT *
					FROM public.tbl_prestudent p
					JOIN (
							SELECT DISTINCT ON(prestudent_id) *
							  FROM public.tbl_prestudentstatus
							 WHERE prestudent_id IN (SELECT prestudent_id FROM public.tbl_prestudent WHERE person_id = ?)
						  ORDER BY prestudent_id, datum desc, insertamum desc
						) ps USING(prestudent_id)
					JOIN public.tbl_status USING(status_kurzbz)
				   WHERE ps.ausbildungssemester = 1';

		$parametersArray = array($person_id);

		if ($studiensemester_kurzbz != '')
		{
			array_push($parametersArray, $studiensemester_kurzbz);
			$query .= ' AND ps.studiensemester_kurzbz = ?';
		}

		if (isset($studiengang_kz))
		{
			array_push($parametersArray, $studiengang_kz);
			$query .= ' AND p.studiengang_kz = ?';
		}

		if ($status_kurzbz != '')
		{
			array_push($parametersArray, $status_kurzbz);
			$query .= ' AND ps.status_kurzbz = ?';
		}

		return $this->execQuery($query, $parametersArray);
	}

	/**
	 * updateAufnahmegruppe
	 */
	public function updateAufnahmegruppe($prestudentIdArray, $aufnahmegruppe)
	{
		return $this->execQuery(
			'UPDATE public.tbl_prestudent
				SET aufnahmegruppe_kurzbz = ?
			  WHERE prestudent_id IN ?',
			array(
				$aufnahmegruppe,
				$prestudentIdArray
			)
        );
	}

	/**
	 * Returns a list of prestudent with additional information:
	 *	- person_id
	 *	- name, surname, gender and birthday
	 *	- email
	 *	- studiengang and orgform
	 *	- studienplan
	 *	- stufe and aufnahmegruppe
	 *	- reihungstest score
	 */
	public function getPrestudentMultiAssign(
		$studiengang = null, $studiensemester = null, $gruppe = null, $reihungstest = null, $stufe = null
	)
	{
		if (isError($ent = $this->isEntitled($this->dbTable, PermissionLib::SELECT_RIGHT, FHC_NORIGHT, FHC_MODEL_ERROR))) return $ent;

		$this->addSelect(
			'p.person_id,
			prestudent_id,
			p.nachname,
			p.vorname,
			p.geschlecht,
			p.gebdatum,
			k.kontakt AS email,
			sg.kurzbzlang,
			sg.bezeichnung,
			sg.orgform_kurzbz,
			sgt.bezeichnung AS typ,
			s.bezeichnung AS studienplan,
			ps.rt_stufe,
			aufnahmegruppe_kurzbz,
			SUM(rtp.punkte) AS punkte'
		);

		$this->addJoin('public.tbl_person p', 'person_id', 'LEFT');
		$this->addJoin(
			'(
					SELECT DISTINCT ON(person_id) person_id,
						   kontakt
					  FROM public.tbl_kontakt
					 WHERE zustellung = TRUE
					   AND kontakttyp = \'email\'
				  ORDER BY person_id, kontakt_id DESC
			) k',
			'person_id',
			'LEFT'
		);
		$this->addJoin('public.tbl_prestudentstatus ps', 'prestudent_id');
		$this->addJoin('lehre.tbl_studienplan s', 's.studienplan_id = ps.studienplan_id');
		$this->addJoin('lehre.tbl_studienordnung so', 'studienordnung_id');
		$this->addJoin('public.tbl_studiengang sg', 'sg.studiengang_kz = so.studiengang_kz');
		$this->addJoin('public.tbl_studiengangstyp sgt', 'typ');

		$this->addJoin('public.tbl_rt_person rtp', 'rtp.person_id = p.person_id AND rtp.studienplan_id = s.studienplan_id', 'LEFT');

		$this->addOrder('p.person_id', 'ASC');
		$this->addOrder('prestudent_id', 'ASC');

		$parametersArray = array('p.aktiv' => true, 'ps.status_kurzbz' => 'Interessent');

		if ($studiengang != null)
		{
			$parametersArray['public.tbl_prestudent.studiengang_kz'] = $studiengang;
		}

		if ($studiensemester != null)
		{
			$parametersArray['ps.studiensemester_kurzbz'] = $studiensemester;
		}

		if ($gruppe != null)
		{
			$parametersArray['aufnahmegruppe_kurzbz'] = $gruppe;
		}

		if ($reihungstest != null)
		{
			$parametersArray['rtp.rt_id'] = $reihungstest;
		}

		if ($stufe != null)
		{
			$parametersArray['ps.rt_stufe'] = $stufe;
		}

		$this->addGroupBy(
			array(
				'p.person_id',
				'prestudent_id',
				'p.nachname',
				'p.vorname',
				'p.geschlecht',
				'p.gebdatum',
				'k.kontakt',
				'sg.kurzbzlang',
				'sg.bezeichnung',
				'sg.orgform_kurzbz',
				'sgt.bezeichnung',
				's.bezeichnung',
				'ps.rt_stufe',
				'aufnahmegruppe_kurzbz'
			)
		);

		return $this->loadWhere($parametersArray);
	}

	/**
	 * getOrganisationunits
	 */
	public function getOrganisationunits($prestudent_id)
	{
		$query = 'SELECT p.prestudent_id, s.oe_kurzbz
					FROM public.tbl_prestudent p
			  INNER JOIN public.tbl_studiengang s USING(studiengang_kz)
				   WHERE prestudent_id %s ?';

		return $this->execQuery(sprintf($query, is_array($prestudent_id) ? 'IN' : '='), array($prestudent_id));
	}


	/**
	 * gets extended zgv data (with zgv bezeichnung) for a prestudent
	 * includes last status, Studiengang, zgv, zgv master
	 * @param $prestudent_id
	 */
	public function getPrestudentWithZgv($prestudent_id)
	{
		$this->addSelect('tbl_prestudent.*, tbl_studiengang.studiengang_kz, tbl_studiengang.kurzbzlang as studiengang, tbl_studiengang.bezeichnung as studiengangbezeichnung, tbl_studiengang.english as studiengangenglish,
		tbl_studiengang.email as studiengangmail, tbl_studiengang.typ as studiengangtyp, tbl_studiengangstyp.bezeichnung as studiengangtyp_bez,
		tbl_zgv.zgv_code, tbl_zgv.zgv_bez, tbl_prestudent.zgvnation as zgvnation_code, zgvnat.kurztext as zgvnation_kurzbez, zgvnat.langtext as zgvnation_bez, zgvnat.engltext as zgvnation_englbez,
		tbl_zgvmaster.zgvmas_code, tbl_zgvmaster.zgvmas_bez, tbl_prestudent.zgvmanation as zgvmanation_code, zgvmanat.kurztext as zgvmanation_kurzbez, zgvmanat.langtext as zgvmanation_bez, zgvmanat.engltext as zgvmanation_englbez');
		$this->addJoin('public.tbl_studiengang', 'studiengang_kz', 'LEFT');
		$this->addJoin('public.tbl_studiengangstyp', 'typ', 'LEFT');
		$this->addJoin('bis.tbl_zgv', 'zgv_code', 'LEFT');
		$this->addJoin('bis.tbl_zgvmaster', 'zgvmas_code', 'LEFT');
		$this->addJoin('bis.tbl_nation zgvnat', 'zgvnation = zgvnat.nation_code', 'LEFT');
		$this->addJoin('bis.tbl_nation zgvmanat', 'zgvmanation = zgvmanat.nation_code', 'LEFT');

		$prestudent = $this->load($prestudent_id);
		if($prestudent->error)
			return error($prestudent->retval);

		//Prestudentstatus
		$this->load->model('crm/prestudentstatus_model', 'PrestudentstatusModel');
		$lastStatus = $this->PrestudentstatusModel->getLastStatus($prestudent_id);

		if ($lastStatus->error)
		{
			return error($lastStatus->retval);
		}

		if (count($lastStatus->retval) > 0)
		{
			$this->load->model('system/sprache_model', 'SpracheModel');
			$language = $this->SpracheModel->load($lastStatus->retval[0]->sprache);

			if ($language->error)
				return error($language->retval);

			if (count($language->retval) > 0)
				$lastStatus->retval[0]->sprachedetails = $language->retval[0];

			//get Bewerbungsfrist
			$this->load->model('crm/bewerbungstermine_model', 'BewerbungstermineModel');
			$this->BewerbungstermineModel->addSelect('ende, nachfrist_ende');
			$this->BewerbungstermineModel->addOrder('ende', 'DESC');
			$this->BewerbungstermineModel->addLimit(1);
			$bewerbungstermin = $this->BewerbungstermineModel->loadWhere(
				array(
					'studienplan_id' => $lastStatus->retval[0]->studienplan_id,
					'studiensemester_kurzbz' => $lastStatus->retval[0]->studiensemester_kurzbz,
					'studiengang_kz' => $prestudent->retval[0]->studiengang_kz
				)
			);
			if ($bewerbungstermin->error)
				return error($bewerbungstermin->retval);

			if (count($bewerbungstermin->retval) > 0)
			{
				$lastStatus->retval[0]->bewerbungstermin = $bewerbungstermin->retval[0]->ende;
				$lastStatus->retval[0]->bewerbungsnachfrist = $bewerbungstermin->retval[0]->nachfrist_ende;
			}

			$prestudent->retval[0]->prestudentstatus = $lastStatus->retval[0];
		}

		return success($prestudent->retval);
	}

	/**
	 * gets the prestudent edited last.
	 * if no updateamum, sort by insertamum
	 * @param $person_id
	 * @param bool $withzgv if true, only prestudenten with zgv_code are taken
	 * @return array|null
	 */
	public function getLastPrestudent($person_id, $withzgv = false)
	{
		$qry = 'SELECT * FROM public.tbl_prestudent
				WHERE person_id = ?
				%s
				ORDER BY updateamum DESC NULLS LAST, insertamum DESC NULLS LAST
				LIMIT 1';

		$zgvwhere = $withzgv === true ? 'AND zgv_code IS NOT NULL' : '';

		$qry = sprintf($qry, $zgvwhere);

		$parametersArray = array($person_id);

		return $this->execQuery($qry, $parametersArray);
	}
}
