<?php
class Pruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_pruefung';
		$this->pk = 'pruefung_id';
	}

	/**
	 * Gets Pruefungen of a person for a Studiensemester.
	 * @param int $person_id
	 * @param string $studiensemester_kurzbz
	 * @return object
	 */
	public function getByPerson($person_id, $studiensemester_kurzbz)
	{
		$qry = '
		SELECT prfg.*, pers.matr_nr, lv.ects, stg.studiengang_kz, prst.prestudent_id,
       			UPPER(stg.typ||stg.kurzbz) AS studiengang, stg.bezeichnung AS studiengang_bezeichnung
		FROM public.tbl_person pers
		JOIN public.tbl_prestudent prst USING (person_id)
		JOIN public.tbl_student USING (prestudent_id)
		JOIN lehre.tbl_pruefung prfg USING (student_uid)
		JOIN lehre.tbl_lehreinheit le USING (lehreinheit_id)
		JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
		JOIN public.tbl_studiengang stg ON prst.studiengang_kz = stg.studiengang_kz
		WHERE pers.person_id = ?
		AND le.studiensemester_kurzbz = ?
		ORDER BY prfg.datum, pruefung_id';

		return $this->execQuery($qry, array($person_id, $studiensemester_kurzbz));
	}


    /**
     * @return string
     */
    protected function loadWhereThreeExamsFailed()
    {
        $this->load->config('studierendenantrag');

        $this->dbTable = 'lehre.tbl_pruefung p';

        $sprache_index = "SELECT index FROM public.tbl_sprache WHERE sprache='" . getUserLanguage() . "' LIMIT 1";

        $this->addSelect('max(p.datum) as datum');
        $this->addSelect('pers.vorname');
        $this->addSelect('pers.nachname');
        $this->addSelect('pers.person_id');
        $this->addSelect('s.matrikelnr');
        $this->addSelect('g.bezeichnung');
        $this->addSelect('g.studiengang_kz');
        $this->addSelect('o.bezeichnung_mehrsprachig[(' . $sprache_index . ')] AS orgform', false);
        $this->addSelect('ps.prestudent_id');
        $this->addSelect('lv.bezeichnung as lvbezeichnung');
        $this->addSelect('le.studiensemester_kurzbz');
        $this->addSelect('a.typ');
        $this->addSelect('campus.get_status_studierendenantrag(a.studierendenantrag_id) status');
        $this->addSelect('count(1) as count');

        $this->addGroupBy([
			'pers.vorname',
			'pers.nachname',
			'pers.person_id',
			's.matrikelnr',
			'g.bezeichnung',
			'g.studiengang_kz',
			'o.bezeichnung_mehrsprachig',
			'ps.prestudent_id',
			'lv.bezeichnung',
			'le.studiensemester_kurzbz',
			'a.typ',
			'a.studierendenantrag_id'
		]);
        $this->addJoin('lehre.tbl_note n', 'note');
        $this->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id');
        $this->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
        $this->addJoin('public.tbl_student s', 'student_uid');
        $this->addJoin('public.tbl_prestudent ps', 'prestudent_id');
        $this->addJoin('public.tbl_person pers', 'person_id');
        $this->addJoin('public.tbl_benutzer b', 's.student_uid=b.uid');
        $this->addJoin('public.tbl_studiengang g', 'ps.studiengang_kz=g.studiengang_kz');
        $this->addJoin('bis.tbl_orgform o', 'g.orgform_kurzbz=o.orgform_kurzbz');
        $this->db->join('campus.tbl_studierendenantrag a', 'ps.prestudent_id=a.prestudent_id and a.typ = ?', 'LEFT', false);

        $this->db->where("n.positiv", false);
        /*		$this->db->where_in("p.pruefungstyp_kurzbz1", ['kommPruef','zusKommPruef']);*/
        $this->db->where_in("get_rolle_prestudent(ps.prestudent_id, null)", $this->config->item('antrag_prestudentstatus_whitelist'));

        $this->db->where("g.aktiv", true);

        $this->db->where('lv.studiengang_kz not in(
				SELECT ps.studiengang_kz
				FROM
				public.tbl_prestudent ps1
				JOIN public.tbl_prestudentstatus pss USING (prestudent_id)
				WHERE pss.statusgrund_id in ?
				AND ps.prestudent_id = ps1.prestudent_id)', null, false);

        // NOTE(chris): is Wiederholer without set statusgrund (legacy?)
        $this->db->where(
			'(SELECT COUNT(*) 
				FROM (SELECT DISTINCT studiensemester_kurzbz 
					FROM tbl_prestudentstatus _s 	
					WHERE ausbildungssemester=get_absem_prestudent(ps.prestudent_id, le.studiensemester_kurzbz) 
					AND prestudent_id=ps.prestudent_id) a) = 1',
			null,
			false
		);

        return $this->db->get_compiled_select($this->dbTable);
    }




	/**
	 * @param integer		$prestudent_id student_uid
	 *
	 * @return stdClass
	 */
	public function loadWhereCommitteeExamFailedForPrestudent($prestudent_id)
	{

		$sql = $this->loadWhereThreeExamsFailed();

        $statusgruende = $this->config->item('status_gruende_wiederholer');
        if (!is_array($statusgruende))
            $statusgruende = [];

        return $this->execQuery('select * from ( ' . $sql . ') temp where count >= 3 AND prestudent_id = ?', [
			Studierendenantrag_model::TYP_WIEDERHOLUNG,
			$statusgruende,
			$prestudent_id
		]);
	}

	public function getAllPrestudentsWhereCommitteeExamFailed($status, $maxDate, $minDate)
	{
		$this->load->model('education/Studierendenantrag_model', 'StudierendenantragModel');

		if ($maxDate)
			$this->db->where("p.datum < ", $maxDate->format('c'));
		if ($minDate)
			$this->db->where("p.datum > ", $minDate->format('c'));

		$this->db->where("b.aktiv", true);

		if (is_array($status)) {
			if (in_array(null, $status)) {
				$status = array_filter($status);
				if (count($status)) {
					$this->db->group_start();
					$this->db->where_in('campus.get_status_studierendenantrag(a.studierendenantrag_id)', $status);
					$this->db->or_where('campus.get_status_studierendenantrag(a.studierendenantrag_id)', null);
					$this->db->group_end();
				} else {
					$this->db->where('campus.get_status_studierendenantrag(a.studierendenantrag_id)', null);
				}
			} else {
				$this->db->where_in('campus.get_status_studierendenantrag(a.studierendenantrag_id)', $status);
			}
		} else {
			$this->db->where('campus.get_status_studierendenantrag(a.studierendenantrag_id)', $status);
		}

		$sql = $this->loadWhereThreeExamsFailed();

		$statusgruende = $this->config->item('status_gruende_wiederholer');
		if (!is_array($statusgruende))
			$statusgruende = [];

		return $this->execQuery('select * from ( ' . $sql . ') temp where count >= 3', [Studierendenantrag_model::TYP_WIEDERHOLUNG, $statusgruende]);
	}
}
