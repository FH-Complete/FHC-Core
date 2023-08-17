<?php
class Studierendenantraglehrveranstaltung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_studierendenantrag_lehrveranstaltung';
		$this->pk = 'studierendenantrag_lehrveranstaltung_id';
	}

	public function insertBatch($data)
	{
		// Check class properties
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		// DB-INSERT
		$insert = $this->db->insert_batch($this->dbTable, $data);

		if ($insert)
		{
			return success();
		}
		else
		{
			return error($this->db->error(), EXIT_DATABASE);
		}
	}

	public function deleteWhere($where)
	{
		if (is_null($this->dbTable)) return error('The given database table name is not valid', EXIT_MODEL);

		$delete = $this->db->delete($this->dbTable, $where);

		if ($delete)
		{
			return success();
		}
		else
		{
			return error($this->db->error(), EXIT_DATABASE);
		}
	}

	public function getLvsForPrestudent($prestudent_id, $studiensemester_kurzbz)
	{
		$this->addSelect($this->dbTable . '.*');
		$this->addSelect('a.prestudent_id');
		$this->addSelect('lv.bezeichnung as lv_bezeichnung');
		$this->addSelect('stat.insertamum as freigabedatum');
		$this->addSelect('n.bezeichnung as note_bezeichnung');
		$this->addSelect('stg.bezeichnung as stg_bezeichnung');

		$this->addJoin('campus.tbl_studierendenantrag a', 'studierendenantrag_id');
		$this->addJoin('lehre.tbl_note n', 'note');
		$this->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id');
		$this->addJoin('public.tbl_prestudent ps', 'prestudent_id');
		$this->addJoin('public.tbl_studiengang stg', 'ps.studiengang_kz = stg.studiengang_kz');
		$this->addJoin(
			'campus.tbl_studierendenantrag_status stat',
			'stat.studierendenantrag_status_id = campus.get_status_id_studierendenantrag(a.studierendenantrag_id)'
		);
		$this->addJoin('public.tbl_student s', 'prestudent_id');
		$this->addJoin(
			'lehre.tbl_zeugnisnote z',
			'z.lehrveranstaltung_id=lv.lehrveranstaltung_id AND z.student_uid=s.student_uid AND z.studiensemester_kurzbz=a.studiensemester_kurzbz',
			'LEFT'
		);
		$this->addJoin('lehre.tbl_note zn', 'z.note = zn.note', 'LEFT');

		return $this->loadWhere([
			'ps.prestudent_id' => $prestudent_id,
			'a.typ' => Studierendenantrag_model::TYP_WIEDERHOLUNG,
			'stat.studierendenantrag_statustyp_kurzbz' => Studierendenantragstatus_model::STATUS_APPROVED,
			'n.note <> ' => 0,
			$this->dbTable . '.studiensemester_kurzbz' => $studiensemester_kurzbz,
			'(n.note<>19 OR (z.note IS NOT NULL AND zn.positiv))' => null
		]);
	}
}
