<?php
class Zeugnisnote_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_zeugnisnote';
		$this->pk = array('studiensemester_kurzbz', 'student_uid', 'lehrveranstaltung_id');
		$this->hasSequence = false;
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
		SELECT note.*, pers.matr_nr, lv.ects, stg.studiengang_kz, prst.prestudent_id, stg.erhalter_kz,
       			UPPER(stg.typ||stg.kurzbz) AS studiengang, stg.bezeichnung AS studiengang_bezeichnung
		FROM public.tbl_person pers
		JOIN public.tbl_prestudent prst USING (person_id)
		JOIN public.tbl_student USING (prestudent_id)
		JOIN lehre.tbl_zeugnisnote note USING (student_uid)
		JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
		JOIN public.tbl_studiengang stg ON prst.studiengang_kz = stg.studiengang_kz 
		WHERE pers.person_id = ?
		AND note.studiensemester_kurzbz = ?
		ORDER BY note.benotungsdatum';

		return $this->execQuery($qry, array($person_id, $studiensemester_kurzbz));
	}
}
