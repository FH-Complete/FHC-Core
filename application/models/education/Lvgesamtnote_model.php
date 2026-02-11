<?php
class Lvgesamtnote_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_lvgesamtnote';
		$this->pk = array('student_uid', 'studiensemester_kurzbz', 'lehrveranstaltung_id');
		$this->hasSequence = false;
	}

	/**
	 * Laedt die Noten - lvgesamtnote (Vorschlag) JOIN tbl.note (zeugnisnote)
	 *
	 * @param integer				$lehrveranstaltung_id
	 * @param string				$student_uid
	 * @param string				$studiensemester_kurzbz
	 *
	 * @return stdClass
	 */
	public function getLvGesamtNoten($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$this->addSelect($this->dbTable . ".*");
		$this->addSelect("n.bezeichnung AS note_bezeichnung");
		$this->addSelect("lv.bezeichnung AS lehrveranstaltung_bezeichnung");
		$this->addSelect("lv.studiengang_kz");
		$this->addSelect("UPPER(stg.typ || stg.kurzbz) AS studiengang");

		$this->addJoin("lehre.tbl_note n", "note");
		$this->addJoin("lehre.tbl_lehrveranstaltung lv", "lehrveranstaltung_id");
		$this->addJoin("public.tbl_studiengang stg", "studiengang_kz");

		$this->db->where($this->dbTable . ".freigabedatum <", "NOW()", false);

		$where = [];
		if ($studiensemester_kurzbz)
			$where[$this->dbTable . ".studiensemester_kurzbz"] = $studiensemester_kurzbz;
		if ($lehrveranstaltung_id)
			$where[$this->dbTable . ".lehrveranstaltung_id"] = $lehrveranstaltung_id;
		if ($student_uid)
			$where[$this->dbTable . ".student_uid"] = $student_uid;

		return $this->loadWhere($where);
	}

	public function getLvGesamtNoteVorschlag($lehrveranstaltung_id, $student_uid, $studiensemester_kurzbz)
	{
		$qry = "SELECT * FROM campus.tbl_lvgesamtnote
				WHERE campus.tbl_lvgesamtnote.student_uid = ? 
				  AND campus.tbl_lvgesamtnote.studiensemester_kurzbz = ?";
		$params = [$student_uid, $studiensemester_kurzbz];
		
		if ($lehrveranstaltung_id) {
			$qry .= "AND campus.tbl_lvgesamtnote.lehrveranstaltung_id = ?";
			$params[] = $lehrveranstaltung_id;
		}

		return $this->execReadOnlyQuery($qry, $params);
	}
}
