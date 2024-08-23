<?php
class LePruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_pruefung';
		$this->pk = 'pruefung_id';
	}

	/**
	 * CI_STYLE
	 * @param string		$student_uid
	 * @param string		$studiensemester_kurzbz
	 *
	 * @return stdClass
	 */
	public function getPruefungenByStudentuid($student_uid, $studiensemester_kurzbz = null)
	{
		$this->addSelect('tbl_pruefung.datum');
		$this->addSelect("TO_CHAR(tbl_pruefung.datum::timestamp, 'DD.MM.YYYY') AS format_datum");
		$this->addSelect('tbl_pruefung.anmerkung');
		$this->addSelect('tbl_pruefung.pruefungstyp_kurzbz');
		$this->addSelect('tbl_pruefung.pruefung_id');
		$this->addSelect('tbl_pruefung.lehreinheit_id');
		$this->addSelect('tbl_pruefung.student_uid');
		$this->addSelect('tbl_pruefung.mitarbeiter_uid');
		$this->addSelect('tbl_pruefung.punkte');

		$this->addSelect('tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung');
		$this->addSelect('tbl_lehrveranstaltung.lehrveranstaltung_id');
		$this->addSelect('tbl_note.bezeichnung as note_bezeichnung');
		$this->addSelect('tbl_pruefungstyp.beschreibung as typ_beschreibung');
		$this->addSelect('tbl_lehreinheit.studiensemester_kurzbz as studiensemester_kurzbz');

		$this->addJoin('lehre.tbl_lehreinheit',  'lehre.tbl_pruefung.lehreinheit_id=lehre.tbl_lehreinheit.lehreinheit_id');
		$this->addJoin('lehre.tbl_lehrveranstaltung', 'lehrveranstaltung_id');
		$this->addJoin('lehre.tbl_note', 'note');
		$this->addJoin('lehre.tbl_pruefungstyp', 'pruefungstyp_kurzbz');

		if ($studiensemester_kurzbz)
			$this->db->where("tbl_lehreinheit.studiensemester_kurzbz = ", $studiensemester_kurzbz);

		$this->addOrder('tbl_pruefung.datum', 'DESC');
		$this->addOrder('tbl_pruefung.pruefung_id', 'DESC');

		return $this->loadWhere([
			'student_uid' => $student_uid
		]);
	}
}
