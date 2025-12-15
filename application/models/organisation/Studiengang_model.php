<?php

class Studiengang_model extends DB_Model
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_studiengang';
		$this->pk = 'studiengang_kz';
	}

	/**
	 * getAllForBewerbung
	 */
	public function getAllForBewerbung()
	{
		$allForBewerbungQuery = 'SELECT DISTINCT studiengang_kz,
										typ,
										organisationseinheittyp_kurzbz,
										studiengangbezeichnung,
										standort,
										studiengangbezeichnung_englisch,
										lgartcode,
										tbl_lgartcode.bezeichnung
								   FROM (SELECT tbl_organisationseinheit.organisationseinheittyp_kurzbz,
												tbl_studiengang.oe_kurzbz,
												tbl_studienordnung.studiengang_kz,
												tbl_studienplan.studienordnung_id,
												tbl_studienplan.studienplan_id,
												tbl_studienplan.orgform_kurzbz,
												tbl_studienplan.version,
												tbl_studienplan.bezeichnung,
												tbl_studienplan.regelstudiendauer,
												tbl_studienplan.sprache,
												tbl_studienplan.aktiv,
												tbl_studienplan.semesterwochen,
												tbl_studienplan.testtool_sprachwahl,
												tbl_studienplan.insertamum,
												tbl_studienplan.insertvon,
												tbl_studienplan.updateamum,
												tbl_studienplan.updatevon,
												tbl_studienordnung.gueltigvon,
												tbl_studienordnung.gueltigbis,
												tbl_studienordnung.ects,
												tbl_studienordnung.studiengangbezeichnung,
												tbl_studienordnung.studiengangbezeichnung_englisch,
												tbl_studienordnung.studiengangkurzbzlang,
												tbl_studienordnung.akadgrad_id,
												tbl_studiengang.kurzbz,
												tbl_studiengang.kurzbzlang,
												tbl_studiengang.typ,
												tbl_studiengang.english,
												tbl_studiengang.farbe,
												tbl_studiengang.email,
												tbl_studiengang.telefon,
												tbl_studiengang.max_semester,
												tbl_studiengang.max_verband,
												tbl_studiengang.max_gruppe,
												tbl_studiengang.erhalter_kz,
												tbl_studiengang.bescheid,
												tbl_studiengang.bescheidbgbl1,
												tbl_studiengang.bescheidbgbl2,
												tbl_studiengang.bescheidgz,
												tbl_studiengang.bescheidvom,
												tbl_studiengang.titelbescheidvom,
												tbl_studiengang.zusatzinfo_html,
												tbl_studiengang.moodle,
												tbl_studiengang.studienplaetze,
												tbl_studiengang.lgartcode,
												tbl_studiengang.mischform,
												tbl_studiengang.projektarbeit_note_anzeige,
												tbl_studiengang.onlinebewerbung,
												tbl_organisationseinheit.oe_parent_kurzbz,
												tbl_organisationseinheit.mailverteiler,
												tbl_organisationseinheit.freigabegrenze,
												tbl_organisationseinheit.kurzzeichen,
												tbl_organisationseinheit.lehre,
												tbl_organisationseinheittyp.beschreibung,
												tbl_organisationseinheit.standort
										   FROM (((((lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING (studienordnung_id))
														JOIN public.tbl_studiengang USING (studiengang_kz))
														JOIN public.tbl_organisationseinheit USING (oe_kurzbz))
														JOIN public.tbl_organisationseinheittyp USING (organisationseinheittyp_kurzbz))
														LEFT JOIN lehre.tbl_studienplan_semester USING (studienplan_id))
										) t1 LEFT JOIN bis.tbl_lgartcode USING (lgartcode)
								  WHERE t1.onlinebewerbung IS TRUE
									AND t1.aktiv IS TRUE
							   ORDER BY typ, studiengangbezeichnung, tbl_lgartcode.bezeichnung ASC';

		return $this->execQuery($allForBewerbungQuery);
	}

	/**
	 * getStudienplan
	 */
	public function getStudienplan($studiensemester_kurzbz, $ausbildungssemester, $aktiv, $onlinebewerbung)
	{
		// Join table public.tbl_studiengang with table lehre.tbl_studienordnung on column studiengang_kz
		$this->addJoin('lehre.tbl_studienordnung', 'studiengang_kz');
		// Then join with table lehre.tbl_studienplan on column studienordnung_id
		$this->addJoin('lehre.tbl_studienplan', 'studienordnung_id');
		// Then join with table lehre.tbl_studienplan_semester on column studienplan_id
		$this->addJoin('lehre.tbl_studienplan_semester', 'studienplan_id');

		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.studiengang_kz');
		$this->addOrder('lehre.tbl_studienplan.studienplan_id');

		$result = $this->loadTree(
			'public.tbl_studiengang',
			array(
				'lehre.tbl_studienplan'
			),
			array(
				'lehre.tbl_studienplan_semester.studiensemester_kurzbz' => $studiensemester_kurzbz,
				'lehre.tbl_studienplan_semester.semester' => $ausbildungssemester,
				'public.tbl_studiengang.aktiv' => $aktiv,
				'public.tbl_studiengang.onlinebewerbung' => $onlinebewerbung
			),
			array(
				'studienplaene'
			)
		);

		return $result;
	}

	/**
	 * getStudiengangBewerbung
	 */
	public function getStudiengangBewerbung($oe_kurzbz = null)
	{
		// Join table public.tbl_studiengang with table lehre.tbl_studienordnung on column studiengang_kz
		$this->addJoin('lehre.tbl_studienordnung', 'studiengang_kz');
		// Join table lehre.tbl_studienordnung with table lehre.tbl_akadgrad on column akadgrad_id
		$this->addJoin('lehre.tbl_akadgrad', 'akadgrad_id', 'LEFT');
		// Then join with table lehre.tbl_studienplan on column studienordnung_id
		$this->addJoin('lehre.tbl_studienplan', 'studienordnung_id');
		// Then join with table lehre.tbl_studienplan_semester on column studienplan_id
		$this->addJoin('lehre.tbl_studienplan_semester ss', 'studienplan_id');
		// Then join with table lehre.tbl_bewerbungsfrist on column studiensemester_kurzbz
		$this->addJoin(
			'public.tbl_bewerbungstermine',
			'tbl_bewerbungstermine.studiensemester_kurzbz = ss.studiensemester_kurzbz
			AND tbl_bewerbungstermine.studienplan_id = ss.studienplan_id',
			'LEFT'
		);
		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.bezeichnung');
		$this->addOrder('lehre.tbl_studienplan.studienplan_id');

		$where = 'public.tbl_studiengang.aktiv = TRUE
					AND public.tbl_studiengang.onlinebewerbung = TRUE
					AND (
						(tbl_bewerbungstermine.beginn <= NOW() AND tbl_bewerbungstermine.ende >= NOW())
						OR tbl_bewerbungstermine.beginn IS NULL
					)
					AND ss.studiensemester_kurzbz IN (
						SELECT DISTINCT studiensemester_kurzbz
						  FROM public.tbl_bewerbungstermine
						 WHERE beginn <= NOW() AND ende >= NOW()
					)
					AND ss.semester = 1
					AND lehre.tbl_studienplan.aktiv = TRUE';

		if ($oe_kurzbz != null)
		{
			$where .= ' AND public.tbl_studiengang.oe_kurzbz IN (
							WITH RECURSIVE organizations(_pk, _ppk) AS
								(
									SELECT o.oe_kurzbz, o.oe_parent_kurzbz
									  FROM public.tbl_organisationseinheit o
									 WHERE o.oe_kurzbz = '.$this->escape($oe_kurzbz).'
								 UNION ALL
									SELECT o.oe_kurzbz, o.oe_parent_kurzbz
									  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
								)
								SELECT orgs._pk
								FROM organizations orgs
							)';
		}

		$result = $this->loadTree(
			'public.tbl_studiengang',
			array(
				'lehre.tbl_studienplan',
				'lehre.tbl_akadgrad'
			),
			$where,
			array(
				'studienplaene',
				'akadgrad'
			)
		);

		return $result;
	}

	/**
	 * getAppliedStudiengang
	 */
	public function getAppliedStudiengang($person_id, $studiensemester_kurzbz, $titel)
	{
		// Then join with table public.tbl_prestudent
		$this->addJoin('public.tbl_prestudent', 'studiengang_kz');
		// Join table public.tbl_prestudentstatus
		$this->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
		// Then join with table lehre.tbl_studienplan
		$this->addJoin('lehre.tbl_studienplan', 'studienplan_id');
		// Then join with table public.tbl_notizzuordnung + public.tbl_notiz
		$this->addJoin(
			'(
				SELECT public.tbl_notiz.*, public.tbl_notizzuordnung.prestudent_id
				  FROM public.tbl_notiz JOIN public.tbl_notizzuordnung USING(notiz_id)
				 WHERE titel = '.$this->escape($titel).
			') tbl_notiz',
			'prestudent_id',
			'LEFT'
		);

		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.bezeichnung');

		$result = $this->loadTree(
			'public.tbl_studiengang',
			array(
				'public.tbl_prestudent',
				'public.tbl_prestudentstatus',
				'lehre.tbl_studienplan',
				'public.tbl_notiz'
			),
			'public.tbl_prestudent.person_id = '.$this->escape($person_id).
			' AND public.tbl_prestudentstatus.studiensemester_kurzbz = '.$this->escape($studiensemester_kurzbz).
			' AND (public.tbl_prestudentstatus.status_kurzbz = \'Interessent\')',
			array(
				'prestudenten',
				'prestudentstatus',
				'studienplaene',
				'notizen'
			)
		);

		return $result;
	}

	/**
	 * getAppliedStudiengangFromNow
	 */
	public function getAppliedStudiengangFromNow($person_id, $titel)
	{
		// Then join with table public.tbl_prestudent
		$this->addJoin('public.tbl_prestudent', 'studiengang_kz');
		// Join table public.tbl_prestudentstatus
		$this->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
		// Then join with table lehre.tbl_studienplan
		$this->addJoin('lehre.tbl_studienplan', 'studienplan_id');
		// Then join with table public.tbl_notizzuordnung + public.tbl_notiz
		$this->addJoin(
			'(
				SELECT public.tbl_notiz.*, public.tbl_notizzuordnung.prestudent_id
				  FROM public.tbl_notiz JOIN public.tbl_notizzuordnung USING(notiz_id)
				 WHERE titel = '.$this->escape($titel).
			') tbl_notiz',
			'prestudent_id',
			'LEFT'
		);

		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.bezeichnung');

		$result = $this->loadTree(
			'public.tbl_studiengang',
			array(
				'public.tbl_prestudent',
				'public.tbl_prestudentstatus',
				'lehre.tbl_studienplan',
				'public.tbl_notiz'
			),
			'public.tbl_prestudent.person_id = '.$this->escape($person_id).
			' AND public.tbl_prestudentstatus.studiensemester_kurzbz IN (
				SELECT studiensemester_kurzbz
				  FROM public.tbl_studiensemester
				 WHERE ende >= NOW()
			)'.
			' AND (public.tbl_prestudentstatus.status_kurzbz = \'Interessent\')',
			array(
				'prestudenten',
				'prestudentstatus',
				'studienplaene',
				'notizen'
			)
		);

		return $result;
	}

	/**
	 * getAppliedStudiengangFromNowOE
	 */
	public function getAppliedStudiengangFromNowOE($person_id, $titel, $oe_kurzbz)
	{
		// Then join with table public.tbl_prestudent
		$this->addJoin('public.tbl_prestudent', 'studiengang_kz');
		// Join table public.tbl_prestudentstatus
		$this->addJoin('public.tbl_prestudentstatus', 'prestudent_id');
		// Then join with table lehre.tbl_studienplan
		$this->addJoin('lehre.tbl_studienplan', 'studienplan_id');
		// Then join with table public.tbl_notizzuordnung + public.tbl_notiz
		$this->addJoin(
			'(
				SELECT public.tbl_notiz.*, public.tbl_notizzuordnung.prestudent_id
				  FROM public.tbl_notiz JOIN public.tbl_notizzuordnung USING(notiz_id)
				 WHERE titel = '.$this->escape($titel).
			') tbl_notiz',
			'prestudent_id',
			'LEFT'
		);

		// Ordering by studiengang_kz and studienplan_id
		$this->addOrder('public.tbl_studiengang.bezeichnung');

		$result = $this->loadTree(
			'public.tbl_studiengang',
			array(
				'public.tbl_prestudent',
				'public.tbl_prestudentstatus',
				'lehre.tbl_studienplan',
				'public.tbl_notiz'
			),
			'public.tbl_prestudent.person_id = '.$this->escape($person_id).
			' AND public.tbl_prestudentstatus.studiensemester_kurzbz IN (
				SELECT studiensemester_kurzbz
				  FROM public.tbl_studiensemester
				 WHERE ende >= NOW()
			)
			AND (public.tbl_prestudentstatus.status_kurzbz = \'Interessent\')
			AND public.tbl_studiengang.oe_kurzbz IN (
				WITH RECURSIVE organizations(_pk, _ppk) AS
					(
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz
						  FROM public.tbl_organisationseinheit o
						 WHERE o.oe_kurzbz = '.$this->escape($oe_kurzbz).'
					 UNION ALL
						SELECT o.oe_kurzbz, o.oe_parent_kurzbz
						  FROM public.tbl_organisationseinheit o INNER JOIN organizations orgs ON (o.oe_parent_kurzbz = orgs._pk)
					)
					SELECT orgs._pk
					FROM organizations orgs
				)',
			array(
				'prestudenten',
				'prestudentstatus',
				'studienplaene',
				'notizen'
			)
		);

		return $result;
	}

	/**
	 * getAvailableReihungstestByPersonId
	 */
	public function getAvailableReihungstestByPersonId($person_id)
	{
		$this->addJoin('lehre.tbl_studienordnung', 'studiengang_kz');

		$this->addJoin('lehre.tbl_studienplan', 'studienordnung_id');

		$this->addJoin('public.tbl_prestudentstatus', 'studienplan_id');

		$this->addJoin('public.tbl_prestudent', 'prestudent_id');

		$this->addFrom(
			'(SELECT * FROM public.tbl_reihungstest LEFT JOIN public.tbl_rt_studienplan USING(reihungstest_id))',
			'tbl_reihungstest'
		);

		$this->addOrder('tbl_studiengang.bezeichnung, tbl_reihungstest.stufe, tbl_reihungstest.datum');

		return $this->loadTree(
			'public.tbl_studiengang',
			array('public.tbl_reihungstest'),
			'tbl_prestudentstatus.status_kurzbz = \'Interessent\'
			AND (tbl_prestudentstatus.rt_stufe >= tbl_reihungstest.stufe OR tbl_reihungstest.stufe IS NULL)
			AND (tbl_prestudent.aufnahmegruppe_kurzbz = tbl_reihungstest.aufnahmegruppe_kurzbz OR tbl_reihungstest.aufnahmegruppe_kurzbz IS NULL)
			AND (tbl_prestudentstatus.studienplan_id = tbl_reihungstest.studienplan_id OR tbl_reihungstest.studienplan_id IS NULL)
			AND tbl_reihungstest.oeffentlich = TRUE
			AND tbl_reihungstest.datum > NOW()
			AND tbl_reihungstest.anmeldefrist >= NOW()
			AND COALESCE (
					tbl_reihungstest.max_teilnehmer,
					(
						SELECT SUM(arbeitsplaetze)
						  FROM public.tbl_ort JOIN public.tbl_rt_ort USING(ort_kurzbz)
						 WHERE rt_id = tbl_reihungstest.reihungstest_id
					)
				) - (
					SELECT COUNT(*)
					  FROM public.tbl_rt_person
					 WHERE rt_id = tbl_reihungstest.reihungstest_id
				) > 0
			AND person_id = ' . $this->escape($person_id),
			array('reihungstest')
		);
	}

	/**
	 * Gets Studiengaenge of a Studiensemesester
	 * @param $studiensemester_kurzbz
	 * @return array|null
	 */
	public function getStudiengaengeByStudiensemester($studiensemester_kurzbz)
	{
		$query = "SELECT
					distinct tbl_studiengang.*, UPPER(typ::varchar(1) || kurzbz) AS kuerzel
				FROM
					public.tbl_studiengang
					JOIN lehre.tbl_studienordnung USING(studiengang_kz)
					JOIN lehre.tbl_studienplan USING(studienordnung_id)
					JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
				WHERE
					tbl_studienplan_semester.studiensemester_kurzbz=?
				ORDER BY
					typ, kurzbz";

		return $this->execQuery($query, array($studiensemester_kurzbz));
	}

	/**
	 * Loads degree programs of the given type
	 * @param string $typ Type of degree programs to be loaded
	 * @return array
	 */
	public function loadStudiengaengeFromTyp($typ)
	{
		$query = "SELECT
					distinct tbl_studiengang.*
				FROM
					public.tbl_studiengang
				WHERE
					tbl_studiengang.typ=?
				ORDER BY
					kurzbz";

		return $this->execQuery($query, array($typ));
	}

	/**
	 * Get Studiengangsleitung/en of Studiengang/Studiengaenge.
     *
	 * @param null $studiengang_kz Numeric or Array
	 * @return array
	 */
	public function getLeitung($studiengang_kz = null)
	{
		$this->addSelect('uid, studiengang_kz, oe_kurzbz, vorname, nachname, email, titelpre, titelpost, alias');
		$this->addJoin('public.tbl_benutzerfunktion', 'oe_kurzbz');
		$this->addJoin('public.tbl_benutzer', 'uid');
		$this->addJoin('public.tbl_person', 'person_id');

		if (!is_numeric($studiengang_kz) && !is_array($studiengang_kz))
		{
			return error('Studiengangskennzahl ungültig');
		}

		if (is_null($studiengang_kz))
		{
			$condition = '
                funktion_kurzbz = \'Leitung\'
                AND ( datum_von <= NOW() OR datum_von IS NULL )
                AND ( datum_bis >= NOW() OR datum_bis IS NULL )
            ';
		}
		elseif (is_numeric($studiengang_kz) || is_array($studiengang_kz))
		{
            if (is_array($studiengang_kz))
            {
				$studiengang_kz = array_map(array($this,'escape'), $studiengang_kz);
                $studiengang_kz = implode(', ', $studiengang_kz);
            }
			$condition =  '
               funktion_kurzbz = \'Leitung\'
                AND ( datum_von <= NOW() OR datum_von IS NULL )
                AND ( datum_bis >= NOW() OR datum_bis IS NULL )
                AND studiengang_kz IN (' . $studiengang_kz. ')';
			;
		}

		return $this->loadWhere($condition);
	}

	/**
	 * Get Studiengangsleitung/en of Studiengang/Studiengaenge. With Details
	 *
	 * @param null $studiengang_kz Numeric or Array
	 * @return array
	 */
	public function getLeitungDetailed($studiengang_kz = null)
	{
		$this->addSelect('studiengang_kz, email, f.oe_kurzbz, b.uid, b.alias, b.aktiv, p.vorname, p.nachname, p.titelpre, p.titelpost, m.telefonklappe, k.kontakt, o.planbezeichnung');
		$this->addJoin('public.tbl_benutzerfunktion f', 'oe_kurzbz');
		$this->addJoin('public.tbl_benutzer b', 'uid');
		$this->addJoin('public.tbl_person p', 'person_id');
		$this->addJoin('public.tbl_mitarbeiter m', 'mitarbeiter_uid=uid', 'LEFT');
		$this->addJoin('public.tbl_kontakt k', 'k.standort_id=m.standort_id AND kontakttyp=\'telefon\'', 'LEFT');
		$this->addJoin('public.tbl_ort o', 'ort_kurzbz', 'LEFT');

		if (!is_numeric($studiengang_kz) && !is_array($studiengang_kz))
		{
			return error('Studiengangskennzahl ungültig');
		}

		if (is_null($studiengang_kz))
		{
			$condition = '
			funktion_kurzbz = \'Leitung\'
			AND ( datum_von <= NOW() OR datum_von IS NULL )
			AND ( datum_bis >= NOW() OR datum_bis IS NULL )
			';
		}
		elseif (is_numeric($studiengang_kz) || is_array($studiengang_kz))
		{
			if (is_array($studiengang_kz))
			{
				$studiengang_kz = array_map(array($this,'escape'), $studiengang_kz);
				$studiengang_kz = implode(', ', $studiengang_kz);
			}
			$condition =  '
			funktion_kurzbz = \'Leitung\'
			AND ( datum_von <= NOW() OR datum_von IS NULL )
			AND ( datum_bis >= NOW() OR datum_bis IS NULL )
			AND studiengang_kz IN (' . $studiengang_kz. ')';
			;
		}

		return $this->loadWhere($condition);
	}

	public function getStudiengaengeWithOrgForm($typ, $semester)
	{
		$query = "SELECT DISTINCT (UPPER(so.studiengangkurzbzlang || ':' || sp.orgform_kurzbz)) AS Studiengang
					FROM public.tbl_studiengang sg
					JOIN lehre.tbl_studienordnung USING (studiengang_kz)
					JOIN lehre.tbl_studienplan sp USING (studienordnung_id)
					JOIN lehre.tbl_studienplan_semester spsem USING (studienplan_id)
					JOIN lehre.tbl_studienordnung so USING(studienordnung_id)
					WHERE sp.aktiv = TRUE AND sg.aktiv = TRUE AND sg.typ IN ?
					AND spsem.studiensemester_kurzbz = ?
					ORDER BY Studiengang";

		return $this->execQuery($query, array($typ, $semester));
	}

	public function getStudiengangTyp($studiengang_kz, $typ = null)
	{
		$query = "SELECT DISTINCT(sgt.*)
					FROM tbl_studiengangstyp sgt JOIN tbl_studiengang sg on sgt.typ = sg.typ
					WHERE studiengang_kz IN ?";

		$params = [$studiengang_kz];

		if (!is_null($typ))
		{
			$query .= " AND sgt.typ IN ?";
			$params[] = $typ;
		}

		return $this->execQuery($query, $params);
	}

	/**
	 * @param array		$studiengang_kzs
	 * @param array		$not_antrag_typ		(optional) If the prestudent has an antrag with one of the specified types it will be excluded from the result
	 * @param array		$prestudent_stati	(optional)
	 *
	 * @return stdClass
	 */
	public function getAktivePrestudenten($studiengang_kzs, $not_antrag_typ = null, $query = null)
	{
		$this->load->config('studierendenantrag');

		$sql = "SELECT index FROM public.tbl_sprache WHERE sprache='" . getUserLanguage() . "' LIMIT 1";

		$this->addSelect($this->dbTable . '.studiengang_kz');
		$this->addSelect($this->dbTable . '.bezeichnung');
		$this->addSelect('o.orgform_kurzbz');
		$this->addSelect('o.bezeichnung_mehrsprachig[(' . $sql . ')] AS orgform', false);
		$this->addSelect('ps.ausbildungssemester AS semester');
		$this->addSelect('ps.studiensemester_kurzbz');
		$this->addSelect('p.prestudent_id');
		$this->addSelect('pers.vorname');
		$this->addSelect('pers.nachname');
		$this->addSelect("CONCAT(UPPER(pers.nachname), ' ', pers.vorname, ' (', " . $this->dbTable . ".bezeichnung, ')') AS name");

		$this->addJoin('public.tbl_prestudent p', 'studiengang_kz');
		$this->addJoin(
			'public.tbl_prestudentstatus ps',
			'ps.prestudent_id=p.prestudent_id
				AND ps.studiensemester_kurzbz=get_stdsem_prestudent(p.prestudent_id, NULL)
				AND ps.ausbildungssemester=get_absem_prestudent(p.prestudent_id, NULL)
				AND ps.status_kurzbz=get_rolle_prestudent(p.prestudent_id, NULL)'
		);
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id');
		$this->addJoin('bis.tbl_orgform o', 'COALESCE(plan.orgform_kurzbz, ps.orgform_kurzbz, ' . $this->dbTable . '.orgform_kurzbz)=o.orgform_kurzbz');
		$this->addJoin('public.tbl_person pers', 'person_id');
		$this->addJoin('public.tbl_student stud', 'p.prestudent_id=stud.prestudent_id', 'LEFT');

		$this->db->where_in($this->dbTable . '.studiengang_kz', $studiengang_kzs);
		$this->db->where_in('ps.status_kurzbz', $this->config->item('antrag_prestudentstatus_whitelist_abmeldung'));
		$this->db->where($this->dbTable . ".aktiv", true);

		if ($not_antrag_typ !== null && is_array($not_antrag_typ)) {
			foreach($not_antrag_typ as $k => $v)
				$not_antrag_typ[$k] = $this->db->escape($v);
			$this->addJoin(
				'campus.tbl_studierendenantrag a',
				'a.prestudent_id=p.prestudent_id and a.typ in ('.
				implode(',', $not_antrag_typ).
				") AND campus.get_status_studierendenantrag (a.studierendenantrag_id)<>'" .
				Studierendenantragstatus_model::STATUS_CANCELLED . "'",
				'LEFT'
			);
			$this->db->where('a.typ IS NULL');
		}

		if ($query) {
			$query = explode(' ', $query);
			$this->db->group_start();
			foreach ($query as $q) {
				$this->db->group_start();
					$this->db->where('pers.vorname ILIKE', "%" . $q . "%");
					$this->db->or_where('pers.nachname ILIKE', "%" . $q . "%");
					$this->db->or_where('stud.student_uid ILIKE', "%" . $q . "%");
					$this->db->or_where($this->dbTable . '.bezeichnung ILIKE', "%" . $q . "%");
					if (is_numeric($q))
						$this->db->or_where('p.prestudent_id', $q);
				$this->db->group_end();
			}
			$this->db->group_end();
		}

		$this->addOrder('name');

		return $this->load();
	}

	/**
	 * @return stdClass
	 */
	public function getStudiengangInfoForNews()
	{

		$this->load->model('person/Benutzerfunktion_model', 'BenutzerfunktionModel');
		$this->load->model('person/Person_model', 'PersonModel');
		$this->load->model('crm/Student_model', 'StudentModel');

		$addEmailProperty= function(&$benutzerfunktionen){
			if(count($benutzerfunktionen) && defined('DOMAIN'))
			{
				$benutzerfunktionen = array_map(function($benutzer)
				{
					$benutzer->email = $benutzer->alias."@".DOMAIN;
					return $benutzer;
				},$benutzerfunktionen) ;
			}

		};
		$addFotoProperty= function(&$collection){
			$collection = array_map(function($item){
				$person_id = $this->PersonModel->getByUid($item->uid);
				if(isError($person_id))
					return error($person_id);
				$person_id = current(getData($person_id))->person_id;
				$this->PersonModel->addSelect('foto');
				$foto = $this->PersonModel->loadWhere(array('person_id'=>$person_id));
				if(isError($foto))
					return error($foto);	
				$foto = current(getData($foto))->foto;
				$item->foto = $foto;
				return $item;
			},$collection);
		};
		

		$this->load->model('crm/Student_model', 'StudentModel');

		$student = $this->StudentModel->loadWhere(['student_uid' => getAuthUID()]);
		if (isError($student))
			return error($student);
		if (getData($student)) {
			$student = current(getData($student));
			$studiengang_kz = $student->studiengang_kz;
			$semester = $student->semester;
		}
		
		$stg_obj = $this->load($studiengang_kz);
		if(isError($stg_obj))
			return error($stg_obj);
		if(getData($stg_obj))
		{
			$stg_obj = current(getData($stg_obj));
		}		

		$stg_ltg = $this->getLeitungDetailed($stg_obj->studiengang_kz);
		if (isError($stg_ltg))
			return $stg_ltg;
		$stg_ltg = getData($stg_ltg) ?: [];
		$stg_ltg = array_values(array_filter($stg_ltg, function($stg_leitung){
			return $stg_leitung->aktiv;
		}));
		$addFotoProperty($stg_ltg);

		$gf_ltg = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('gLtg', $stg_obj->oe_kurzbz);
		if (isError($gf_ltg))
			return $gf_ltg;
		$gf_ltg = getData($gf_ltg) ?: [];
		$gf_ltg = array_values(array_filter($gf_ltg, function($gf_leitung){
			return $gf_leitung->aktiv;
		}));
		$addEmailProperty($gf_ltg);
		$addFotoProperty($gf_ltg);

		$stv_ltg = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('stvLtg', $stg_obj->oe_kurzbz);
		if (isError($stv_ltg))
			return $stv_ltg;
		$stv_ltg = getData($stv_ltg) ?: [];
		$stv_ltg = array_values(array_filter($stv_ltg, function($stv_leitung){
			return $stv_leitung->aktiv;
		}));
		$addEmailProperty($stv_ltg);
		$addFotoProperty($stv_ltg);

		$ass = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('ass', $stg_obj->oe_kurzbz);
		if (isError($ass))
			return $ass;
		$ass = getData($ass) ?: [];
		$ass = array_values(array_filter($ass, function($assistenz){
			return $assistenz->aktiv;
		}));
		$addEmailProperty($ass);
		$addFotoProperty($ass);

		$hochschulvertr = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('hsv');
		if (isError($hochschulvertr))
			return $hochschulvertr;
		$hochschulvertr = getData($hochschulvertr) ?: [];
		$hochschulvertr = array_values(array_filter($hochschulvertr, function($hochschul_vertreter){
			return $hochschul_vertreter->aktiv;
		}));
		$addEmailProperty($hochschulvertr);


		$stdv = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('stdv', $stg_obj->oe_kurzbz);
		if (isError($stdv))
			return $stdv;
		$stdv = getData($stdv) ?: [];
		$stdv = array_values(array_filter($stdv, function($std_vertreter){
			return $std_vertreter->aktiv;
		}));
		$addEmailProperty($stdv);


		$jahrgangsvertr = $this->BenutzerfunktionModel->getBenutzerFunktionenDetailed('jgv', $stg_obj->oe_kurzbz, $semester);
		if (isError($jahrgangsvertr))
			return $jahrgangsvertr;
		$jahrgangsvertr = getData($jahrgangsvertr) ?: [];
		$jahrgangsvertr = array_values(array_filter($jahrgangsvertr, function($jahrgang_vertreter){
			return $jahrgang_vertreter->aktiv;
		}));
		$addEmailProperty($jahrgangsvertr);


		$result_object = new stdClass();
		$result_object->studiengang = $stg_obj;
		$result_object->semester = $semester;
		$result_object->stg_ltg = $stg_ltg;
		$result_object->gf_ltg = $gf_ltg;
		$result_object->stv_ltg = $stv_ltg;
		$result_object->ass = $ass;
		$result_object->hochschulvertr = $hochschulvertr;
		$result_object->stdv = $stdv;
		$result_object->jahrgangsvertr = $jahrgangsvertr;

		return success($result_object);
	}
	
	public function getLvaForStudiengangInStudiensemester($studiengang_kz, $orgform_kurzbz, $studiensemester_kurzbz) {
		$qry = '
		SELECT DISTINCT ON (lehre.tbl_lehrveranstaltung.lehrveranstaltung_id,
			kurzbz, bezeichnung, semester,
			lehre.tbl_lehrveranstaltung.sprache, orgform_kurzbz,
			lehre.tbl_lehrveranstaltung.lehrform_kurzbz)
			lehre.tbl_lehrveranstaltung.lehrveranstaltung_id, kurzbz, bezeichnung,
			semester, lehre.tbl_lehrveranstaltung.sprache, orgform_kurzbz, lehre.tbl_lehrveranstaltung.lehrform_kurzbz
		FROM lehre.tbl_lehrveranstaltung JOIN lehre.tbl_lehreinheit USING(lehrveranstaltung_id) JOIN lehre.tbl_lehreinheitmitarbeiter USING(lehreinheit_id)
		WHERE aktiv = TRUE AND studiengang_kz = ? AND orgform_kurzbz = ? AND tbl_lehreinheit.studiensemester_kurzbz IN ?';

		return $this->execReadOnlyQuery($qry, array($studiengang_kz, $orgform_kurzbz, $studiensemester_kurzbz));
	}

	/**
	 * Get active Studiengänge with Kuerzel by given Studiengang-Kennzahlen.
	 * Helpful to easily get Studiengänge the user is entitled for.
	 *
	 * @param $studiengang_kz_arr
	 * @param $studiensemester_kurzbz
	 * @return array|stdClass|null	Returns one row per Studiengang. Not considering the Orgforms.
	 */
	public function getByStgs($studiengang_kz_arr, $studiensemester_kurzbz)
	{
		if (is_numeric($studiengang_kz_arr))
		{
			$studiengang_kz_arr = [$studiengang_kz_arr];
		}

		$qry = '
			SELECT
				DISTINCT stg.*, UPPER(typ::varchar(1) || kurzbz) AS kuerzel
			FROM
				public.tbl_studiengang stg
				JOIN lehre.tbl_studienordnung sto USING(studiengang_kz)
				JOIN lehre.tbl_studienplan stpl USING(studienordnung_id)
				JOIN lehre.tbl_studienplan_semester stplsem USING(studienplan_id)
			WHERE
				stg.studiengang_kz IN ?
				AND stg.aktiv = TRUE
				AND stplsem.studiensemester_kurzbz = ?
			ORDER BY
				stg.kurzbzlang
		';

		return $this->execQuery($qry, [$studiengang_kz_arr, $studiensemester_kurzbz]);
	}

	/**
	 * Get OrgForms of given Studiengang and Studiensemester.
	 *
	 * @param $studiengang_kz
	 * @param $studiensemester_kurzbz
	 * @return array|stdClass|null
	 */
	public function getOrgformsByStg($studiengang_kz, $studiensemester_kurzbz)
	{
		$qry = '
			SELECT
				stpl.orgform_kurzbz
			FROM
				public.tbl_studiengang stg
				JOIN lehre.tbl_studienordnung sto USING(studiengang_kz)
				JOIN lehre.tbl_studienplan stpl USING(studienordnung_id)
				JOIN lehre.tbl_studienplan_semester stplsem USING(studienplan_id)
			WHERE
				stg.studiengang_kz = ?
				AND stg.aktiv = TRUE
				AND stplsem.studiensemester_kurzbz = ?
			GROUP BY
				stpl.orgform_kurzbz
			ORDER BY
				CASE stpl.orgform_kurzbz
					WHEN \'VZ\' THEN 1
					WHEN \'BB\' THEN 2
					WHEN \'DUA\' THEN 3
					ELSE 4
				END,
				stpl.orgform_kurzbz; 
		';

		return $this->execQuery($qry, [$studiengang_kz, $studiensemester_kurzbz]);
	}
}
