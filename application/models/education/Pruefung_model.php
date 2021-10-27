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
}
