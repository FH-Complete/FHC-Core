<?php
class Studierendenantrag_model extends DB_Model
{

	const TYP_ABMELDUNG = 'Abmeldung';
	const TYP_ABMELDUNG_STGL = 'AbmeldungStgl';
	const TYP_UNTERBRECHUNG = 'Unterbrechung';
	const TYP_WIEDERHOLUNG = 'Wiederholung';

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_studierendenantrag';
		$this->pk = 'studierendenantrag_id';

		$this->load->config('studierendenantrag');

		$this->load->model('education/Studierendenantragstatus_model', 'StudierendenantragstatusModel');
	}

	public function loadCreatedForStudiengaenge($studiengaenge, $typ)
	{
		return $this->loadForStudiengaenge($studiengaenge, $typ, $this->StudierendenantragstatusModel::STATUS_CREATED);
	}

	public function loadForStudiengaenge($studiengaenge, $typ = null, $status = null, $sql = null)
	{
		if ($sql == null)
			$sql = "SELECT index FROM public.tbl_sprache WHERE sprache='" . getUserLanguage() . "' LIMIT 1";

		$this->addSelect('UPPER(stg.typ) || UPPER(stg.kurzbz) || \' \' || stg.bezeichnung AS bezeichnung');
		$this->addSelect('bezeichnung_mehrsprachig[(' . $sql . ')] AS orgform', false);
		$this->addSelect('s.studierendenantrag_id');
		$this->addSelect('matrikelnr');
		$this->addSelect('studienjahr_kurzbz');
		$this->addSelect('vorname');
		$this->addSelect('nachname');
		$this->addSelect('unruly');
		$this->addSelect('p.prestudent_id');
		$this->addSelect('p.studiengang_kz');
		$this->addSelect('semester');
		$this->addSelect($this->dbTable . '.grund');
		$this->addSelect($this->dbTable . '.datum');
		$this->addSelect('datum_wiedereinstieg');
		$this->addSelect($this->dbTable . '.typ');
		$this->addSelect('st.studierendenantrag_statustyp_kurzbz as status');
		$this->addSelect('s.insertvon as status_insertvon');
		$this->addSelect('s.insertamum as status_insertamum');
		$this->addSelect('dms_id');
		$this->addSelect('st.bezeichnung[(' . $sql . ')] as statustyp');

		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');
		$this->addJoin('public.tbl_student', 'prestudent_id');
		$this->addJoin('public.tbl_person', 'person_id');
		$this->addJoin('public.tbl_studiengang stg', 'p.studiengang_kz=stg.studiengang_kz');
		$this->addJoin('public.tbl_studiensemester ss', 'studiensemester_kurzbz');
		$this->addJoin(
			'public.tbl_prestudentstatus ps',
			'ps.prestudent_id=p.prestudent_id 
			AND ps.studiensemester_kurzbz=ss.studiensemester_kurzbz 
			AND ps.status_kurzbz=get_rolle_prestudent(p.prestudent_id, ss.studiensemester_kurzbz)',
			'LEFT'
		);
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');
		$this->addJoin('bis.tbl_orgform of', 'of.orgform_kurzbz=COALESCE(plan.orgform_kurzbz, ps.orgform_kurzbz, stg.orgform_kurzbz)');
		$this->addJoin(
			'campus.tbl_studierendenantrag_status as s',
			'campus.get_status_id_studierendenantrag('. $this->dbTable .'.studierendenantrag_id) = studierendenantrag_status_id'
		);
		$this->addJoin('campus.tbl_studierendenantrag_statustyp as st', 'studierendenantrag_statustyp_kurzbz');

		$this->db->where_in('p.studiengang_kz', $studiengaenge);

		$where = [];
		if ($status !== null)
			$where['st.studierendenantrag_statustyp_kurzbz'] = $status;
		if ($typ !== null)
			$where[$this->dbTable . '.typ'] = $typ;

		return $this->loadWhere($where);
	}

	public function loadActiveForStudiengaenge($studiengaenge)
	{
		// NOTE(chris): get language before changing things in the global
		// db object because getUserLanguage() might use it and it should
		// not have been tampered with
		$sql = "SELECT index FROM public.tbl_sprache WHERE sprache='" . getUserLanguage() . "' LIMIT 1";

		$this->db->group_start();
		$this->db->where_not_in('s.studierendenantrag_statustyp_kurzbz', [
			Studierendenantragstatus_model::STATUS_CANCELLED,
			Studierendenantragstatus_model::STATUS_APPROVED,
			Studierendenantragstatus_model::STATUS_REJECTED,
			Studierendenantragstatus_model::STATUS_OBJECTION_DENIED,
			Studierendenantragstatus_model::STATUS_DEREGISTERED,
			Studierendenantragstatus_model::STATUS_PAUSE,
			Studierendenantragstatus_model::STATUS_REMINDERSENT
		]);
		$this->db->or_group_start();
		$this->db->where('s.studierendenantrag_statustyp_kurzbz', Studierendenantragstatus_model::STATUS_APPROVED);
		$this->db->where('tbl_studierendenantrag.typ', Studierendenantrag_model::TYP_ABMELDUNG_STGL);
		$this->db->group_end();
		$this->db->group_end();

		return $this->loadForStudiengaenge($studiengaenge, null, null, $sql);
	}

	public function loadStgsWithAntraege($studiengaenge)
	{
		$this->addDistinct();
		$this->addSelect('UPPER(stg.typ) || UPPER(stg.kurzbz) || \' \' || stg.bezeichnung AS bezeichnung');
		$this->addSelect('p.studiengang_kz');

		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');
		$this->addJoin('public.tbl_studiengang stg', 'p.studiengang_kz=stg.studiengang_kz');

		$this->addOrder('UPPER(stg.typ) || UPPER(stg.kurzbz) || \' \' || stg.bezeichnung');

		$this->db->where_in('p.studiengang_kz', $studiengaenge);

		return $this->load();
	}

	public function isInStudiengang($studierendenantrag_id, $studiengaenge)
	{
		$this->addJoin('public.tbl_prestudent', 'prestudent_id');

		$this->db->where_in('studiengang_kz', $studiengaenge);

		return $this->load($studierendenantrag_id);
	}

	public function loadIdAndStatusWhere($where)
	{
		$this->addSelect('studierendenantrag_id');
		$this->addSelect('campus.get_status_studierendenantrag(studierendenantrag_id) status');
		return $this->loadWhere($where);
	}

	public function loadWithStatusWhere($where, $types = null)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());

		$this->addSelect('*');
		$this->addSelect($this->dbTable . '.grund AS grund');
		$this->addSelect('s.studierendenantrag_statustyp_kurzbz status');
		$this->addSelect('s.insertvon status_insertvon');
		$this->addSelect('t.bezeichnung[(' . $lang . ')] statustyp');
		$this->addSelect('p.unruly AS unruly');

		$this->addJoin(
			'campus.tbl_studierendenantrag_status s',
			'campus.get_status_id_studierendenantrag(' . $this->dbTable . '.studierendenantrag_id)=s.studierendenantrag_status_id'
		);
		$this->addJoin(
			'campus.tbl_studierendenantrag_statustyp t',
			's.studierendenantrag_statustyp_kurzbz=t.studierendenantrag_statustyp_kurzbz'
		);
		$this->addJoin(
			'public.tbl_student st',
			'st.prestudent_id=tbl_studierendenantrag.prestudent_id'
		);
		$this->addJoin(
			'public.tbl_benutzer b',
			'st.student_uid=b.uid'
		);
		$this->addJoin(
			'public.tbl_person p',
			'b.person_id=p.person_id'
		);

		if ($types && is_array($types)) {
			$this->db->where_in('typ', $types);
		}

		$this->addOrder('datum', 'DESC');

		return $this->loadWhere($where);
	}

	/**
	 * Get the studiengang and ausbildungssemester the student was in
	 * for the studiensemester the antrag was committed for
	 *
	 * @param integer		$antrag_id
	 *
	 * @return stdClass
	 */
	public function getStgAndSem($antrag_id)
	{
		$this->addSelect('p.studiengang_kz');
		$this->addSelect('stg.bezeichnung');
		$this->addSelect('s.ausbildungssemester');
		$this->addSelect('plan.sprache');
		$this->addSelect('COALESCE(plan.orgform_kurzbz, s.orgform_kurzbz, stg.orgform_kurzbz) AS orgform_kurzbz');

		$this->addJoin(
			'public.tbl_prestudentstatus s',
			$this->dbTable . '.prestudent_id=s.prestudent_id 
			AND ' .
			$this->dbTable . '.studiensemester_kurzbz=s.studiensemester_kurzbz 
			AND ' .
			$this->dbTable . '.insertamum > s.insertamum'
		);
		$this->addJoin('public.tbl_prestudent p', $this->dbTable . '.prestudent_id=p.prestudent_id');
		$this->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');

		$this->addOrder('s.datum', 'DESC');
		$this->addOrder('s.insertamum', 'DESC');
		$this->addOrder('s.ext_id', 'DESC');

		$this->addLimit(1);

		return $this->loadWhere([
			$this->pk => $antrag_id
		]);
	}

	/**
	 * Get the studiengang the student is in
	 *
	 * @param integer		$antrag_id
	 *
	 * @return stdClass
	 */
	public function getStg($antrag_id)
	{
		$this->addSelect('p.studiengang_kz');
		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');

		$this->addLimit(1);

		return $this->load(
			$antrag_id
		);
	}

	public function getStgEmail($antrag_id)
	{
		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');
		$this->addJoin('public.tbl_studiengang sg', 'studiengang_kz');
		$this->addSelect('sg.email');

		return $this->load($antrag_id);
	}

	public function loadForPerson($person_id)
	{
		$lang = 'SELECT index FROM public.tbl_sprache WHERE sprache=' . $this->escape(getUserLanguage());
		$this->addSelect('stg.bezeichnung');
		$this->addSelect('bezeichnung_mehrsprachig[(' . $lang . ')] as orgform');
		$this->addSelect('p.studiengang_kz');
		$this->addSelect('st.studierendenantrag_statustyp_kurzbz as status');
		$this->addSelect('st.bezeichnung[(' . $lang . ')] as status_bezeichnung');
		$this->addSelect('p.prestudent_id');
		$this->addSelect($this->dbTable . '.studierendenantrag_id');
		$this->addSelect($this->dbTable . '.studiensemester_kurzbz');
		$this->addSelect($this->dbTable . '.datum');
		$this->addSelect($this->dbTable . '.typ');
		$this->addSelect($this->dbTable . '.insertamum');
		$this->addSelect($this->dbTable . '.insertvon');
		$this->addSelect($this->dbTable . '.datum_wiedereinstieg');
		$this->addSelect($this->dbTable . '.grund');
		$this->addSelect($this->dbTable . '.dms_id');
		$this->addSelect('s.insertvon AS status_insertvon');
		$this->addSelect(
			"(SELECT count(1) FROM campus.tbl_studierendenantrag_status WHERE studierendenantrag_id = " .
			$this->dbTable .
			".studierendenantrag_id AND studierendenantrag_statustyp_kurzbz = 'Genehmigt') AS isapproved",
			false
		);

		$this->addJoin('public.tbl_prestudent p', 'prestudent_id', 'RIGHT');
		$this->addJoin('public.tbl_studiengang stg', 'p.studiengang_kz=stg.studiengang_kz');
		$this->addJoin(
			'public.tbl_prestudentstatus ps',
			'ps.prestudent_id=p.prestudent_id AND ps.studiensemester_kurzbz=' .
			$this->dbTable .
			'.studiensemester_kurzbz AND ps.status_kurzbz=get_rolle_prestudent(p.prestudent_id, ' .
			$this->dbTable .
			'.studiensemester_kurzbz)',
			'LEFT'
		);
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');
		$this->addJoin('bis.tbl_orgform of', 'of.orgform_kurzbz=COALESCE(plan.orgform_kurzbz, ps.orgform_kurzbz, stg.orgform_kurzbz)');
		$this->addJoin(
			'campus.tbl_studierendenantrag_status s',
			'campus.get_status_id_studierendenantrag(' . $this->dbTable . '.studierendenantrag_id)=s.studierendenantrag_status_id',
			'LEFT'
		);
		$this->addJoin(
			'campus.tbl_studierendenantrag_statustyp st',
			's.studierendenantrag_statustyp_kurzbz=st.studierendenantrag_statustyp_kurzbz',
			'LEFT'
		);
		
		$this->db->where("(
			SELECT status_kurzbz 
			FROM public.tbl_prestudentstatus 
			WHERE prestudent_id=p.prestudent_id 
			AND status_kurzbz='Student' 
			LIMIT 1
		) IS NOT NULL", null, false);


		return $this->loadWhere([
			'p.person_id' => $person_id
		]);
	}

	public function getAntraegeWhereWiedereinstiegBetween($start, $end)
	{
		$this->addSelect('sg.email');
		$this->addSelect('vorname');
		$this->addSelect('nachname');
		$this->addSelect($this->dbTable.'.*');

		$this->addJoin(
			'campus.tbl_studierendenantrag_status s',
			'campus.get_status_id_studierendenantrag(' . $this->dbTable . '.studierendenantrag_id)=s.studierendenantrag_status_id'
		);
		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');
		$this->addJoin('public.tbl_person', 'person_id');
		$this->addJoin('public.tbl_studiengang sg', 'studiengang_kz');

		$this->db->where('datum_wiedereinstieg >=', $start->format('Y-m-d'));
		$this->db->where('datum_wiedereinstieg <=', $end->format('Y-m-d'));

		return $this->loadWhere([
			's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
			$this->dbTable.'.typ' => self::TYP_UNTERBRECHUNG,
		]);
	}

	public function getWithLastStatusWhere($where)
	{
		$this->addJoin(
			'campus.tbl_studierendenantrag_status s',
			'campus.get_status_id_studierendenantrag(' . $this->dbTable . '.studierendenantrag_id)=s.studierendenantrag_status_id'
		);

		return $this->loadWhere($where);
	}

	/**
	 * Checks if the Prestudent has an active Unterbrechung between
	 * the start of the given semester and the given enddate.
	 * If the enddate is omitted the end of the given semester is used.
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz
	 * @param string		$enddate					(optional)
	 *
	 * @return boolean
	 */
	public function hasRunningUnterbrechungBetween($prestudent_id, $studiensemester, $enddate = null)
	{
		$start = '(SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=' . $this->db->escape($studiensemester) . ')';
		$end = $enddate
			? $this->db->escape($enddate)
			: '(SELECT ende FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=' . $this->db->escape($studiensemester) . ')';

		$this->addJoin('public.tbl_studiensemester', 'studiensemester_kurzbz');
		$this->db->where([
			'prestudent_id' => $prestudent_id,
			'typ' => Studierendenantrag_model::TYP_UNTERBRECHUNG,
			'campus.get_status_studierendenantrag(studierendenantrag_id) NOT IN (\'' . Studierendenantragstatus_model::STATUS_CANCELLED . '\', \'' . Studierendenantragstatus_model::STATUS_REJECTED . '\')' => null,
			'start < ' . $end => null,
			'datum_wiedereinstieg > ' . $start => null,
		]);
		return (boolean)$this->db->count_all_results($this->dbTable);
	}

	/**
	 * Gets free semester slots for a new Unterbrechung.
	 *
	 * @param integer		$prestudent_id
	 * @param string		$studiensemester_kurzbz		(optional)
	 *
	 * @return stdClass
	 */
	public function getFreeSlotsForUnterbrechung($prestudent_id, $studiensemester = null)
	{
		$max_starters = 2;
		$max_length = max(
			2,
			(integer)$this->config->item('unterbrecher_semester_max_length')
		);


		$subquery = '';
		if ($studiensemester)
			$subquery = 'SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=?';
		else
			$subquery = 'SELECT start FROM public.tbl_studiensemester WHERE studiensemester_kurzbz=public.get_stdsem_prestudent (?, null)';

		$sql = "WITH numbered_sems AS (
		    SELECT
		        a.studienjahr_kurzbz AS studienjahr_kurzbz,
		        a.studiensemester_kurzbz AS von,
		        b.studiensemester_kurzbz AS bis,
		        a.start AS start,
		        b.start AS ende,
		        ROW_NUMBER() OVER (
		            PARTITION BY a.studiensemester_kurzbz
		            ORDER BY b.start
		        ) AS row_number
		    FROM public.tbl_studiensemester a
		    LEFT JOIN public.tbl_studiensemester b ON (b.start > a.ende)
		),
		last_sems AS (
		    SELECT *
		    FROM numbered_sems 
		    WHERE numbered_sems.row_number <= ?
		)
		SELECT s.von, s.bis, s.start, s.ende, studierendenantrag_id, studienjahr_kurzbz
		FROM last_sems s
		LEFT JOIN (
			SELECT studierendenantrag_id, start, datum_wiedereinstieg AS ende 
			FROM campus.tbl_studierendenantrag 
			LEFT JOIN public.tbl_studiensemester USING(studiensemester_kurzbz) 
			WHERE typ=? 
			AND campus.get_status_studierendenantrag(studierendenantrag_id) NOT IN ?
			AND prestudent_id=?
		) a ON (s.start < a.ende AND s.ende > a.start)
		WHERE s.start >= (" . $subquery . ")
		ORDER BY s.start, s.ende
		LIMIT ?;";

		return $this->execQuery($sql, [
			$max_length,
			self::TYP_UNTERBRECHUNG,
			array(
			    Studierendenantragstatus_model::STATUS_CANCELLED,
			    Studierendenantragstatus_model::STATUS_REJECTED
			),
			$prestudent_id,
			$studiensemester ?: $prestudent_id,
			$max_length * $max_starters
		]);
	}

	/**
	 * Returns if an Antrag is manually paused
	 *
	 * @param integer		$antrag_id
	 *
	 * @return boolean
	 */
	public function isManuallyPaused($antrag_id)
	{
		$this->addJoin(
			'campus.tbl_studierendenantrag_status s',
			'campus.get_status_id_studierendenantrag(' . $this->dbTable . '.studierendenantrag_id)=s.studierendenantrag_status_id'
		);

		$this->db->where([
			's.studierendenantrag_id' => $antrag_id,
			's.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_PAUSE
		]);

		$this->db->group_start();
		$this->db->where_not_in('s.insertvon', [
			Studierendenantragstatus_model::INSERTVON_DEREGISTERED,
			Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL
		]);
		$this->db->or_group_start();
		$this->db->where('s.insertvon', Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL);
		$this->db->where('1 !=', '(
			SELECT COUNT(*)%2 
			FROM campus.tbl_studierendenantrag_status i 
			WHERE i.studierendenantrag_id = s.studierendenantrag_id 
			AND i.insertamum > (
				SELECT ii.insertamum 
				FROM campus.tbl_studierendenantrag_status ii 
				WHERE ii.studierendenantrag_id = s.studierendenantrag_id 
				AND ii.insertvon <> ' . $this->escape(Studierendenantragstatus_model::INSERTVON_ABMELDUNGSTGL) . ' 
				ORDER BY ii.insertamum DESC 
				LIMIT 1
			)
		)', false);
		$this->db->group_end();
		$this->db->group_end();
		
		return hasData($this->load());
	}
}