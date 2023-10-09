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
	 * Gets ECTS sums of completed courses (Zeugnisnoten) of a person for a Studiensemester.
	 * If no valid Noten for the course were entered, 0 ects is returned.
	 * @param int $person_id
	 * @param string $studiensemester_kurzbz
	 * @param bool $aktiv
	 * @param bool $lehre
	 * @param bool $offiziell
	 * @param bool $positiv
	 * @return object
	 */
	public function getEctsSumsByPerson($person_id, $studiensemester_kurzbz, $aktiv = true, $lehre = null, $offiziell = null, $positiv = null)
	{
		$params = array();

		$qry = "SELECT DISTINCT ON (prst.prestudent_id) pers.matr_nr, stg.studiengang_kz, prst.prestudent_id, stg.erhalter_kz,
       			UPPER(stg.typ||stg.kurzbz) AS studiengang, stg.bezeichnung AS studiengang_bezeichnung, COALESCE(summen.summe_ects, 0) AS summe_ects
				FROM public.tbl_person pers
				JOIN public.tbl_prestudent prst USING (person_id)
				JOIN public.tbl_prestudentstatus prstst USING (prestudent_id)
				JOIN public.tbl_studiengang stg ON prst.studiengang_kz = stg.studiengang_kz
				LEFT JOIN (
					SELECT zgnisnote.student_uid, prestudent_id, zgnisnote.studiensemester_kurzbz, sum(ects) AS summe_ects
				    FROM public.tbl_student
					LEFT JOIN lehre.tbl_zeugnisnote zgnisnote USING(student_uid)
					LEFT JOIN lehre.tbl_note note ON zgnisnote.note = note.note
					LEFT JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
					WHERE TRUE";

		if (isset($aktiv))
		{
			$qry .= ' AND (note.aktiv = ?)';
			$params[] = $aktiv;
		}

		if (isset($lehre))
		{
			$qry .= ' AND (note.lehre = ?)';
			$params[] = $lehre;
		}

		if (isset($offiziell))
		{
			$qry .= ' AND (note.offiziell = ?)';
			$params[] = $offiziell;
		}

		if (isset($positiv))
		{
			$qry .= ' AND (note.positiv = ?)';
			$params[] = $positiv;
		}

		$qry .= " GROUP BY zgnisnote.studiensemester_kurzbz, zgnisnote.student_uid, prestudent_id
				) summen ON prst.prestudent_id = summen.prestudent_id AND prstst.studiensemester_kurzbz = summen.studiensemester_kurzbz
				WHERE pers.person_id = ?
				AND prstst.studiensemester_kurzbz = ?
				ORDER BY prst.prestudent_id";

		$params[] = $person_id;
		$params[] = $studiensemester_kurzbz;

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gets courses (Zeugnisnoten) of a person for a Studiensemester.
	 * @param int $person_id
	 * @param string $studiensemester_kurzbz
	 * @param bool $aktiv
	 * @param bool $lehre
	 * @param bool $offiziell
	 * @param bool $positiv
	 * @param bool $zeugnis
	 * @return object
	 */
	public function getByPerson($person_id, $studiensemester_kurzbz, $aktiv = true, $lehre = null, $offiziell = null, $positiv = null, $zeugnis = null)
	{
		$params = array($person_id, $studiensemester_kurzbz);

		$qry = "SELECT zgnisnote.*, pers.matr_nr, lv.ects, stg.studiengang_kz, prst.prestudent_id, stg.erhalter_kz,
       			UPPER(stg.typ||stg.kurzbz) AS studiengang, stg.bezeichnung AS studiengang_bezeichnung, note.note,
       			note.bezeichnung AS note_bezeichnung
				FROM public.tbl_person pers
				JOIN public.tbl_prestudent prst USING (person_id)
				JOIN public.tbl_student USING (prestudent_id)
				JOIN lehre.tbl_zeugnisnote zgnisnote USING (student_uid)
				JOIN lehre.tbl_note note ON zgnisnote.note = note.note
				JOIN lehre.tbl_lehrveranstaltung lv USING (lehrveranstaltung_id)
				JOIN public.tbl_studiengang stg ON prst.studiengang_kz = stg.studiengang_kz
				WHERE pers.person_id = ?
				AND zgnisnote.studiensemester_kurzbz = ?";

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

		if (isset($zeugnis))
		{
			$qry .= ' AND lv.zeugnis = ?';
			$params[] = $zeugnis;
		}

		$qry .= ' ORDER BY zgnisnote.benotungsdatum';

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gets courses (Zeugnisnoten) for a student.
	 * @param string $student_uid,
	 * @param string $studiensemester_kurzbz
	 *
	 * @return object
	 */
	public function getZeugnisnoten($student_uid, $studiensemester_kurzbz)
	{
		$params = array();
		$where='';

		if ($student_uid != null)
		{
			$where .= " AND uid=?";
			$params[] = $student_uid;
		}
		if ($studiensemester_kurzbz !=null)
		{
			$where.=" AND vw_student_lehrveranstaltung.studiensemester_kurzbz= ?";
			$params[] = $studiensemester_kurzbz;
		}

		$where2='';

		if ($student_uid != null)
		{
			$where2 .= " AND student_uid=?";
			$params[] = $student_uid;
		}
		if ($studiensemester_kurzbz !=null)
		{
			$where2 .= " AND studiensemester_kurzbz= ?";
			$params[] = $studiensemester_kurzbz;
		}

		$qry = "SELECT vw_student_lehrveranstaltung.lehrveranstaltung_id, uid,
					   vw_student_lehrveranstaltung.studiensemester_kurzbz, note, punkte, uebernahmedatum, benotungsdatum,
					   vw_student_lehrveranstaltung.ects, vw_student_lehrveranstaltung.semesterstunden,
					   tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum,
					   tbl_zeugnisnote.insertvon, tbl_zeugnisnote.ext_id,
					   vw_student_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung,
					   vw_student_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
					   tbl_note.bezeichnung as note_bezeichnung,
					   tbl_note.positiv as note_positiv,
					   tbl_zeugnisnote.bemerkung as bemerkung,
					   vw_student_lehrveranstaltung.sort,
					   vw_student_lehrveranstaltung.zeugnis,
					   vw_student_lehrveranstaltung.studiengang_kz,
					   vw_student_lehrveranstaltung.lv_lehrform_kurzbz,
					   tbl_lehrveranstaltung.sws
				FROM
				(
					campus.vw_student_lehrveranstaltung LEFT JOIN lehre.tbl_zeugnisnote
						ON(uid=student_uid
						   AND vw_student_lehrveranstaltung.studiensemester_kurzbz=tbl_zeugnisnote.studiensemester_kurzbz
						   AND vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id
						  )
				) LEFT JOIN lehre.tbl_note USING(note)
				JOIN lehre.tbl_lehrveranstaltung ON(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
				WHERE true $where

				UNION
				SELECT lehre.tbl_lehrveranstaltung.lehrveranstaltung_id,student_uid AS uid,studiensemester_kurzbz, note, punkte,
					uebernahmedatum, benotungsdatum,lehre.tbl_lehrveranstaltung.ects,lehre.tbl_lehrveranstaltung.semesterstunden, tbl_zeugnisnote.updateamum, tbl_zeugnisnote.updatevon, tbl_zeugnisnote.insertamum,
					tbl_zeugnisnote.insertvon, tbl_zeugnisnote.ext_id, lehre.tbl_lehrveranstaltung.bezeichnung as lehrveranstaltung_bezeichnung, lehre.tbl_lehrveranstaltung.bezeichnung_english as lehrveranstaltung_bezeichnung_english,
					tbl_note.bezeichnung as note_bezeichnung, tbl_note.positiv as note_positiv, tbl_zeugnisnote.bemerkung as bemerkung, tbl_lehrveranstaltung.sort, tbl_lehrveranstaltung.zeugnis, tbl_lehrveranstaltung.studiengang_kz,
					tbl_lehrveranstaltung.lehrform_kurzbz as lv_lehrform_kurzbz, tbl_lehrveranstaltung.sws
				FROM
					lehre.tbl_zeugnisnote
					JOIN lehre.tbl_lehrveranstaltung USING (lehrveranstaltung_id)
					JOIN lehre.tbl_note USING(note)
				WHERE true $where2

				ORDER BY sort";

		return $this->execQuery($qry, $params);
	}
}
