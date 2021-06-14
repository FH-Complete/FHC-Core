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
	 * @param bool $aktiv
	 * @param bool $lehre
	 * @param bool $offiziell
	 * @param bool $positiv
	 * @return object
	 */
	public function getByPerson($person_id, $studiensemester_kurzbz, $aktiv = true, $lehre = null, $offiziell = null, $positiv = null)
	{
		$params = array($person_id, $studiensemester_kurzbz);

		$qry = '
		SELECT zgnisnote.*, pers.matr_nr, lv.ects, stg.studiengang_kz, prst.prestudent_id, stg.erhalter_kz,
       			UPPER(stg.typ||stg.kurzbz) AS studiengang, stg.bezeichnung AS studiengang_bezeichnung
		FROM public.tbl_person pers
		JOIN public.tbl_prestudent prst USING (person_id)
		JOIN public.tbl_student USING (prestudent_id)
		JOIN lehre.tbl_zeugnisnote zgnisnote USING (student_uid)
		JOIN lehre.tbl_note note ON zgnisnote.note = note.note
		JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
		JOIN public.tbl_studiengang stg ON prst.studiengang_kz = stg.studiengang_kz 
		WHERE pers.person_id = ?
		AND zgnisnote.studiensemester_kurzbz = ?';

		if (isset($aktiv))
		{
			$qry .= ' AND note.aktiv = ?';
			$params[] = $aktiv;
		}

		if (isset($lehre))
		{
			$qry .= ' AND note.lehre = ?';
			$params[] = $lehre;
		}

		if (isset($offiziell))
		{
			$qry .= ' AND note.offiziell = ?';
			$params[] = $offiziell;
		}

		if (isset($positiv))
		{
			$qry .= ' AND note.positiv = ?';
			$params[] = $positiv;
		}

		$qry .= ' ORDER BY zgnisnote.benotungsdatum';

		return $this->execQuery($qry, $params);
	}
}
