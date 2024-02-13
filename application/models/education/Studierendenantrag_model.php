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
		$this->addSelect('p.prestudent_id');
		$this->addSelect('p.studiengang_kz');
		$this->addSelect('semester');
		$this->addSelect($this->dbTable . '.grund');
		$this->addSelect($this->dbTable . '.datum');
		$this->addSelect('datum_wiedereinstieg');
		$this->addSelect($this->dbTable . '.typ');
		$this->addSelect('st.studierendenantrag_statustyp_kurzbz as status');
		$this->addSelect('dms_id');
		$this->addSelect('st.bezeichnung[(' . $sql . ')] as statustyp');

		$this->addJoin('public.tbl_prestudent p', 'prestudent_id');
		$this->addJoin('public.tbl_student', 'prestudent_id');
		$this->addJoin('public.tbl_person', 'person_id');
		$this->addJoin('public.tbl_studiengang stg', 'p.studiengang_kz=stg.studiengang_kz');
		$this->addJoin('public.tbl_studiensemester ss', 'studiensemester_kurzbz');
		$this->addJoin('public.tbl_prestudentstatus ps', 'ps.prestudent_id=p.prestudent_id AND ps.studiensemester_kurzbz=ss.studiensemester_kurzbz AND ps.status_kurzbz=get_rolle_prestudent(p.prestudent_id, ss.studiensemester_kurzbz)', 'LEFT');
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
		// NOTE(chris): get language before changing things in the global db object because getUserLanguage() might use it and it should not have been tampered with
		$sql = "SELECT index FROM public.tbl_sprache WHERE sprache='" . getUserLanguage() . "' LIMIT 1";

		$this->db->group_start();
		$this->db->where_not_in('s.studierendenantrag_statustyp_kurzbz', [
			Studierendenantragstatus_model::STATUS_CANCELLED,
			Studierendenantragstatus_model::STATUS_APPROVED,
			Studierendenantragstatus_model::STATUS_REJECTED,
			Studierendenantragstatus_model::STATUS_OBJECTION_DENIED,
			Studierendenantragstatus_model::STATUS_DEREGISTERED
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
		$this->addSelect('campus.get_status_studierendenantrag(studierendenantrag_id) status');
		$this->addSelect('t.bezeichnung[(' . $lang . ')] statustyp');

		$this->addJoin(
			'campus.tbl_studierendenantrag_statustyp t',
			'campus.get_status_studierendenantrag(studierendenantrag_id)=t.studierendenantrag_statustyp_kurzbz'
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
			$this->dbTable . '.prestudent_id=s.prestudent_id AND ' . $this->dbTable . '.studiensemester_kurzbz=s.studiensemester_kurzbz'
		);
		$this->addJoin('public.tbl_prestudent p', $this->dbTable . '.prestudent_id=p.prestudent_id');
		$this->addJoin('public.tbl_studiengang stg', 'studiengang_kz', 'LEFT');
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');

		$this->addOrder('s.datum', 'DESC');
		$this->addOrder('s.insertamum', 'DESC');
		$this->addOrder('s.ext_id', 'DESC');

		$this->addLimit(1);

		$this->db->where_in('s.status_kurzbz', $this->config->item('antrag_prestudentstatus_whitelist'));

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
		$this->addSelect("(SELECT count(1) FROM campus.tbl_studierendenantrag_status WHERE studierendenantrag_id = " . $this->dbTable . ".studierendenantrag_id AND studierendenantrag_statustyp_kurzbz = 'Genehmigt') AS isapproved", false);

		$this->addJoin('public.tbl_prestudent p', 'prestudent_id', 'RIGHT');
		$this->addJoin('public.tbl_studiengang stg', 'p.studiengang_kz=stg.studiengang_kz');
		$this->addJoin('public.tbl_prestudentstatus ps', 'ps.prestudent_id=p.prestudent_id AND ps.studiensemester_kurzbz=' . $this->dbTable . '.studiensemester_kurzbz AND ps.status_kurzbz=get_rolle_prestudent(p.prestudent_id, ' . $this->dbTable . '.studiensemester_kurzbz)', 'LEFT');
		$this->addJoin('lehre.tbl_studienplan plan', 'studienplan_id', 'LEFT');
		$this->addJoin('bis.tbl_orgform of', 'of.orgform_kurzbz=COALESCE(plan.orgform_kurzbz, ps.orgform_kurzbz, stg.orgform_kurzbz)');
		$this->addJoin(
			'campus.tbl_studierendenantrag_statustyp st',
			'campus.get_status_studierendenantrag(studierendenantrag_id)=st.studierendenantrag_statustyp_kurzbz',
			'LEFT'
		);
		
		$this->db->where("(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=p.prestudent_id AND status_kurzbz='Student' LIMIT 1) IS NOT NULL", null, false);


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
}
