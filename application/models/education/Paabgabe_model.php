<?php
class Paabgabe_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_paabgabe';
		$this->pk = 'paabgabe_id';
	}

	/**
	 * Gets last Endabgabe of a Projektarbeit, including filename.
	 * @param int $projektarbeit_id
	 * @return object
	 */
	public function getEndabgabe($projektarbeit_id)
	{
		$qry = "SELECT paabgabe_id, student_uid, paabg.datum, paabg.abgabedatum, projekttyp_kurzbz, titel, titel_english,
					paabgabe_id || '_' || student_uid || '.pdf' AS filename
				FROM campus.tbl_paabgabe paabg
				JOIN lehre.tbl_projektarbeit USING (projektarbeit_id)
				WHERE projektarbeit_id = ?
				AND paabgabetyp_kurzbz = 'end'
				AND paabg.abgabedatum IS NOT NULL
				ORDER BY paabg.abgabedatum DESC, paabg.datum DESC
				LIMIT 1";

		return $this->execQuery($qry, array($projektarbeit_id));
	}

	/**
	 * Gets all Paabgabe Termin Deadlines of zugewiesene Projektarbeiten as a Mitarbeiter for Terminübersicht Abgabetool.
	 * @param int $person_id
	 * @return object
	 */
	public function getDeadlines($person_id)
	{
		$qry = "SELECT
					DISTINCT TO_CHAR(tbl_paabgabe.datum, 'DD.MM.YYYY') as datum, tbl_paabgabe.fixtermin, tbl_paabgabe.kurzbz,
					person_student.vorname as stud_vorname, person_student.nachname as stud_nachname,
					person_student.titelpre as stud_titelpre, person_student.titelpost as stud_titelpost,
					tbl_lehrveranstaltung.semester, UPPER(tbl_studiengang.typ || tbl_studiengang.kurzbz) as stg,
					tbl_paabgabetyp.bezeichnung as typ_bezeichnung
				FROM
					campus.tbl_paabgabe
					JOIN lehre.tbl_projektarbeit USING(projektarbeit_id)
					JOIN lehre.tbl_projektbetreuer USING(projektarbeit_id)
					JOIN public.tbl_benutzer bn_student ON(tbl_projektarbeit.student_uid=bn_student.uid)
					JOIN public.tbl_person person_student ON(bn_student.person_id=person_student.person_id)
					JOIN lehre.tbl_lehreinheit ON(tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id)
					JOIN lehre.tbl_lehrveranstaltung ON(tbl_lehreinheit.lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id)
					JOIN public.tbl_studiengang ON(tbl_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
					JOIN campus.tbl_paabgabetyp USING(paabgabetyp_kurzbz)
				WHERE
					tbl_projektbetreuer.person_id= ? AND tbl_paabgabe.datum>=now() AND bn_student.aktiv
				ORDER BY datum";

		return $this->execReadOnlyQuery($qry, array($person_id));
	}

	public function getPaAbgaben(
		$projekttyp_kurzbz_arr,
		$studiengang_kz = null,
		$abgabetyp_kurzbz = null,
		$abgabedatum = null,
		$personSearchString = null,
		$limit = 100
	) {
		// convert search string
		if (is_numeric($personSearchString))
		{
			$personSearchString = (int) $personSearchString;
		}
		else
		{
			// remove empty spaces and lowercase
			$personSearchString = strtolower(str_replace(' ', '', $personSearchString));
		}

		$params = [];

		$qry = "
			SELECT
				tbl_studiengang.bezeichnung AS stgbez, tbl_paabgabe.datum AS termin,
				tbl_paabgabe.*, abgabetyp.bezeichnung AS paabgabetyp_bezeichnung, ben.uid, pers.vorname, pers.nachname, pa.projekttyp_kurzbz, pa.titel
			FROM
				lehre.tbl_projektarbeit pa
				JOIN campus.tbl_paabgabe USING(projektarbeit_id)
				JOIN campus.tbl_paabgabetyp abgabetyp USING(paabgabetyp_kurzbz)
				LEFT JOIN public.tbl_benutzer ben ON(uid=student_uid)
				LEFT JOIN public.tbl_person pers ON(ben.person_id=pers.person_id)
				LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				TRUE";

		if (isset($projekttyp_kurzbz_arr) && !isEmptyArray($projekttyp_kurzbz_arr))
		{
			$qry .= " AND projekttyp_kurzbz IN ?";
			$params[] = $projekttyp_kurzbz_arr;
		}

		if (isset($studiengang_kz) && is_numeric($studiengang_kz))
		{
			$qry .= " AND public.tbl_studiengang.studiengang_kz=?";
			$params[] = $studiengang_kz;
		}

		if (isset($abgabetyp_kurzbz))
		{
			$qry .= " AND campus.tbl_paabgabe.paabgabetyp_kurzbz=?";
			$params[] = $abgabetyp_kurzbz;
		}

		if (isset($abgabedatum))
		{
			$qry .= " AND campus.tbl_paabgabe.datum=?";
			$params[] = $abgabedatum;
		}

		if (is_integer($personSearchString))
		{
			$params = array_merge($params, [$personSearchString, $personSearchString]);
			$qry .= " AND (
				pers.person_id = ?
				OR EXISTS (SELECT 1 FROM public.tbl_prestudent WHERE person_id = pers.person_id AND prestudent_id = ?)
			)";
		}
		elseif (is_string($personSearchString))
		{
			$qry .= " AND (
				LOWER(REPLACE(pers.nachname || pers.vorname || pers.nachname, ' ', '')) LIKE ".$this->db->escape('%'.$personSearchString.'%')."
				OR ben.uid LIKE ".$this->db->escape('%'.$personSearchString.'%')."
			)";
		}

		$qry .= " ORDER BY nachname";

		if (isset($limit) && is_numeric($limit)
			&& (!isset($studiengang_kz) || !is_numeric($studiengang_kz))
			&& !isset($abgabetyp_kurzbz) && !isset($abgabedatum))
		{
			$qry .= " LIMIT ?";
			$params[] = $limit;
		}

		return $this->execReadOnlyQuery($qry, $params);
	}

	public function getTermine($projekttyp_kurzbz_arr, $studiengang_kz, $abgabetyp_kurzbz)
	{
		$params = [];

		$qry = "
			SELECT
				DISTINCT campus.tbl_paabgabe.datum as termin, to_char(campus.tbl_paabgabe.datum, 'DD.MM.YYYY') as termin_anzeige
			FROM
				lehre.tbl_projektarbeit
				JOIN campus.tbl_paabgabe USING(projektarbeit_id)
				LEFT JOIN public.tbl_benutzer ON(uid=student_uid)
				LEFT JOIN public.tbl_person ON(tbl_benutzer.person_id=tbl_person.person_id)
				LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				TRUE";

		if (isset($projekttyp_kurzbz_arr) && !isEmptyArray($projekttyp_kurzbz_arr))
		{
			$qry .= " AND projekttyp_kurzbz IN ?";
			$params[] = $projekttyp_kurzbz_arr;
		}

		if (isset($studiengang_kz) && is_numeric($studiengang_kz))
		{
			$qry .= " AND public.tbl_studiengang.studiengang_kz=?";
			$params[] = $studiengang_kz;
		}

		if (isset($abgabetyp_kurzbz))
		{
			$qry .= " AND campus.tbl_paabgabe.paabgabetyp_kurzbz=?";
			$params[] = $abgabetyp_kurzbz;
		}

		$qry .= " ORDER BY termin DESC";

		return $this->execReadOnlyQuery($qry, $params);
	}

	//~ public function searchPaAbgabenByPerson($projekttyp_kurzbz_arr, $searchString, $limit = 100)
	//~ {
		//~ if (is_numeric($searchString))
		//~ {
			//~ $searchString = (int) $searchString;
		//~ }
		//~ else
		//~ {
			//~ $searchString = strtolower(str_replace(' ', '', $searchString));
		//~ }

		//~ $params = [];

		//~ $qry = "
			//~ SELECT
				//~ tbl_studiengang.bezeichnung AS stgbez, tbl_paabgabe.datum AS termin,
				//~ tbl_paabgabe.*, abgabetyp.bezeichnung AS paabgabetyp_bezeichnung, ben.uid, pers.vorname, pers.nachname, pa.projekttyp_kurzbz, pa.titel
			//~ FROM
				//~ lehre.tbl_projektarbeit pa
				//~ JOIN campus.tbl_paabgabe USING(projektarbeit_id)
				//~ JOIN campus.tbl_paabgabetyp abgabetyp USING(paabgabetyp_kurzbz)
				//~ LEFT JOIN public.tbl_benutzer ben ON(uid=student_uid)
				//~ LEFT JOIN public.tbl_person pers ON(ben.person_id=pers.person_id)
				//~ LEFT JOIN lehre.tbl_lehreinheit USING(lehreinheit_id)
				//~ LEFT JOIN lehre.tbl_lehrveranstaltung USING(lehrveranstaltung_id)
				//~ LEFT JOIN public.tbl_studiengang USING(studiengang_kz)
			//~ WHERE
				//~ TRUE";

		//~ if (isset($projekttyp_kurzbz_arr) && !isEmptyArray($projekttyp_kurzbz_arr))
		//~ {
			//~ $qry .= " AND projekttyp_kurzbz IN ?";
			//~ $params[] = $projekttyp_kurzbz_arr;
		//~ }


		//~ if (is_integer($searchString))
		//~ {
			//~ $params = array_merge($params, [$searchString, $searchString]);
			//~ $qry .= " AND (
				//~ pers.person_id = ?
				//~ OR EXISTS (SELECT 1 FROM public.tbl_prestudent WHERE person_id = pers.person_id AND prestudent_id = ?)
			//~ )";
		//~ }
		//~ else
		//~ {
			//~ $qry .= " AND (
				//~ LOWER(REPLACE(pers.nachname || pers.vorname || pers.nachname, ' ', '')) LIKE ".$this->db->escape('%'.$searchString.'%')."
				//~ OR ben.uid LIKE ".$this->db->escape('%'.$searchString.'%')."
			//~ )";
		//~ }

		//~ $qry .= " ORDER BY nachname";

		//~ if (isset($limit) && is_numeric($limit))
		//~ {
			//~ $qry .= " LIMIT ?";
			//~ $params[] = $limit;
		//~ }

		//~ return $this->execReadOnlyQuery($qry, $params);
	//~ }
}
