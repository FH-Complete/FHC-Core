<?php
class Lehrveranstaltung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_lehrveranstaltung';
		$this->pk = 'lehrveranstaltung_id';

		$this->load->model('organisation/studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/studiensemester_model', 'StudiensemesterModel');
	}

	/**
	 * Gets unique Groupstrings for Lehrveranstaltungen, e.g. WS2018_BIF_1_PRJM_VZ_LV12345
	 * @param string $studiensemester_kurzbz
	 * @param string $ausbildungssemester
	 * @param string $studiengang_kz
	 * @param string|array $lehrveranstaltung_ids
	 * @return array|null
	 */
	public function getLehrveranstaltungGroupNames($studiensemester_kurzbz, $ausbildungssemester = null, $studiengang_kz = null, $lehrveranstaltung_ids = null)
	{
		$studiengang_kz_arr = array();
		$ausbildungssemester_arr = array();
		$lehrveranstaltung_id_arr = array();

		if (is_numeric($studiengang_kz))
		{
			$studiengang_kz_arr[] = $studiengang_kz;
		}
		elseif (is_array($studiengang_kz))
		{
			$studiengang_kz_arr = $studiengang_kz;
		}
		else
		{
			$studiengangdata = $this->StudiengangModel->getStudiengaengeByStudiensemester($studiensemester_kurzbz);

			if (!hasData($studiengangdata))
				show_error('no studiengaenge retrieved');

			foreach ($studiengangdata->retval as $studiengang)
			{
				$studiengang_kz_arr[] = $studiengang->studiengang_kz;
			}
		}

		if (is_numeric($ausbildungssemester))
		{
			$ausbildungssemester_arr[] = $ausbildungssemester;
		}
		elseif (is_array($ausbildungssemester))
		{
			$ausbildungssemester_arr = $ausbildungssemester;
		}
		else
		{
			foreach ($studiengang_kz_arr as $studiengang_kz_item)
			{
				$result = $this->StudiensemesterModel->getAusbildungssemesterByStudiensemesterAndStudiengang($studiensemester_kurzbz, $studiengang_kz_item);

				if (isError($result))
					return error(getError($result));

				foreach ($result->retval as $semester)
				{
					if (!in_array($semester->semester, $ausbildungssemester_arr))
						$ausbildungssemester_arr[] = $semester->semester;
				}
			}
		}

		if (is_numeric($lehrveranstaltung_ids))
		{
			$lehrveranstaltung_id_arr[] = $lehrveranstaltung_ids;
		}
		elseif (is_array($lehrveranstaltung_ids))
		{
			$lehrveranstaltung_id_arr = $lehrveranstaltung_ids;
		}

		$parametersarray = array($studiensemester_kurzbz, $studiensemester_kurzbz);

		$query = "

			SELECT lehrveranstaltung_id, ? || '_' || kuerzel || '_' || replace(lvpostfix, ' ', '-') AS lvgroupname
				FROM(
						SELECT DISTINCT ON (kuerzel, lvpostfix)
						  lehrveranstaltung_id,
						  UPPER(tbl_studiengang.typ :: VARCHAR(1) || tbl_studiengang.kurzbz)      AS kuerzel,
						  tbl_lehrveranstaltung.semester || '_' || tbl_lehrveranstaltung.kurzbz || '_' || COALESCE(tbl_lehrveranstaltung.orgform_kurzbz, tbl_studiengang.orgform_kurzbz) || '_LV' || lehrveranstaltung_id AS lvpostfix
						FROM lehre.tbl_lehrveranstaltung
						  JOIN public.tbl_studiengang ON tbl_lehrveranstaltung.studiengang_kz = tbl_studiengang.studiengang_kz
						WHERE tbl_lehrveranstaltung.lehrtyp_kurzbz != 'modul'
						AND EXISTS (SELECT 1 FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id=tbl_lehrveranstaltung.lehrveranstaltung_id AND studiensemester_kurzbz = ?)";

		if (count($ausbildungssemester_arr) > 0)
			$query .= " AND tbl_lehrveranstaltung.semester IN (". implode(", ", $ausbildungssemester_arr).")";

		if (count($studiengang_kz_arr) > 0)
			$query .= " AND tbl_lehrveranstaltung.studiengang_kz IN (". implode(", ", $studiengang_kz_arr).")";

		if (count($lehrveranstaltung_id_arr) > 0)
		{
			$query .= " AND tbl_lehrveranstaltung.lehrveranstaltung_id IN (". implode(', ', $lehrveranstaltung_id_arr).")";
		}

		$query .= ") lvgroups ORDER BY lvgroupname";

		return $this->execQuery($query, $parametersarray);
	}

	/**
	 * Gets all students of a Lehrveranstaltung
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @param $active optional, if true, only active students retrieved, false - only inactive, all students otherwise
	 * @return array|null
	 */
	public function getStudentsByLv($studiensemester_kurzbz, $lehrveranstaltung_id, $active = null)
	{
		$query = "SELECT
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.von, tbl_bisio.bis, tbl_student.studiengang_kz AS stg_kz_student,
			tbl_zeugnisnote.note, tbl_mitarbeiter.mitarbeiter_uid, tbl_person.matr_nr, tbl_benutzer.uid,
			UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel, tbl_studiengang.orgform_kurzbz, vw_student_lehrveranstaltung.semester, vw_student_lehrveranstaltung.studiensemester_kurzbz, vw_student_lehrveranstaltung.bezeichnung

		FROM
			campus.vw_student_lehrveranstaltung
			JOIN public.tbl_benutzer USING(uid)
			JOIN public.tbl_person USING(person_id)
			LEFT JOIN public.tbl_student ON(uid=student_uid)
			LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
			LEFT JOIN public.tbl_studiengang ON(vw_student_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
		WHERE
			vw_student_lehrveranstaltung.studiensemester_kurzbz=?
		AND
			vw_student_lehrveranstaltung.lehrveranstaltung_id=?";

		if (isset($active))
		{
			if ($active === true)
				$query .= " AND tbl_benutzer.aktiv";
			elseif ($active === false)
				$query .= " AND tbl_benutzer.aktiv = false";
		}

		$query .=
		" ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC";

		return $this->execQuery($query, array($studiensemester_kurzbz, $lehrveranstaltung_id));
	}

	/**
	 * Gets all lecturers of a Lehrveranstaltung
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @return array|null
	 */
	public function getLecturersByLv($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$query = "SELECT * FROM (SELECT distinct on(uid) vorname, nachname, tbl_benutzer.uid as uid,
	    			CASE WHEN lehrfunktion_kurzbz='LV-Leitung' THEN true ELSE false END as lvleiter
	    		FROM lehre.tbl_lehreinheit, lehre.tbl_lehreinheitmitarbeiter, public.tbl_benutzer, public.tbl_person
	    		WHERE
	    			tbl_lehreinheit.lehreinheit_id=tbl_lehreinheitmitarbeiter.lehreinheit_id AND
					tbl_lehreinheitmitarbeiter.mitarbeiter_uid=tbl_benutzer.uid AND
					tbl_person.person_id=tbl_benutzer.person_id AND
					lehrveranstaltung_id=? AND
					tbl_lehreinheitmitarbeiter.mitarbeiter_uid NOT like '_Dummy%' AND
					tbl_benutzer.aktiv=true AND tbl_person.aktiv=true AND
					studiensemester_kurzbz=?) AS a
					ORDER BY lvleiter DESC, nachname, vorname";

		return $this->execQuery($query, array($lehrveranstaltung_id, $studiensemester_kurzbz));
	}

	/**
	 * Gets Lehrveranstaltungen of a student
	 * @param $student_uid
	 * @param null $studiensemester_kurzbz
	 * @return array|null
	 */
	public function getLvsByStudent($student_uid, $studiensemester_kurzbz = null)
	{
		$params = array($student_uid);

		$qry = "SELECT * FROM lehre.tbl_lehrveranstaltung
				WHERE lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM campus.vw_student_lehrveranstaltung
											  WHERE uid=?";
		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND studiensemester_kurzbz=?";
			$params[] = $studiensemester_kurzbz;
		}
		$qry .= ") OR lehrveranstaltung_id IN(SELECT lehrveranstaltung_id FROM lehre.tbl_zeugnisnote WHERE student_uid=?";
		$params[] = $student_uid;
		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND studiensemester_kurzbz=?";
			$params[] = $studiensemester_kurzbz;
		}
		$qry .= ") ORDER BY semester, bezeichnung";

		return $this->execQuery($qry, $params);
	}

	/**
	 * Gets valid Lehrveranstaltungen with incoming places for a Studiensemester.
	 * Only
	 * 1. Lvs with incoming places > 0
	 * 2. Studienplan valid in current semester or with Lehrauftrag (i.e. assigned Lehreinheit)
	 * @param $studiensemester_kurzbz
	 * @return object
	 */
	public function getLvsWithIncomingPlaces($studiensemester_kurzbz)
	{
		$studsemres = $this->StudiensemesterModel->load($studiensemester_kurzbz);

		if (!hasData($studsemres))
			return success(array());

		$parametersarray = array($studiensemester_kurzbz, $studsemres->retval[0]->studienjahr_kurzbz, $studiensemester_kurzbz, $studiensemester_kurzbz);

		$query = "
			SELECT * FROM (
				SELECT DISTINCT ON (lv.lehrveranstaltung_id) lv.lehrveranstaltung_id, lv.bezeichnung AS lv_bezeichnung, lv.kurzbz AS lv_kurzbz, lv.sprache, lv.ects, lv.lehre,
				lv.lehreverzeichnis, lv.sws, lv.lvs, lv.alvs, lv.lvps, lv.las, lv.incoming, lv.lehrform_kurzbz, lv.orgform_kurzbz AS lv_orgform, lv.semester,
				? AS studiensemester_kurzbz, ? AS studienjahr_kurzbz, UPPER(stg.typ::VARCHAR(1) || stg.kurzbz) AS studiengang_kuerzel,
				stg.bezeichnung AS studiengang_bezeichnung, stg.english AS studiengang_bezeichnung_english, stg.typ, stg.orgform_kurzbz AS studiengang_orgform,
				tbl_sprache.locale, CASE WHEN lv.orgform_kurzbz NOTNULL THEN lv.orgform_kurzbz ELSE stg.orgform_kurzbz END AS orgform_kurzbz
				FROM lehre.tbl_lehrveranstaltung lv
				JOIN public.tbl_studiengang stg ON lv.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_sprache ON lv.sprache = tbl_sprache.sprache
				WHERE lv.lehrtyp_kurzbz != 'modul'
						  AND (
							EXISTS
							(
								WITH gueltige_studienplaene AS (
									SELECT studienplan_id, semester
									FROM lehre.tbl_studienplan
									JOIN lehre.tbl_studienplan_semester USING(studienplan_id)
									WHERE tbl_studienplan.aktiv
									AND lehre.tbl_studienplan_semester.studiensemester_kurzbz = ?
								)
								SELECT 1
								FROM lehre.tbl_studienplan_lehrveranstaltung
								WHERE tbl_studienplan_lehrveranstaltung.studienplan_id IN (SELECT studienplan_id FROM gueltige_studienplaene)
									  AND tbl_studienplan_lehrveranstaltung.semester IN
										  (SELECT semester FROM gueltige_studienplaene
											 WHERE gueltige_studienplaene.studienplan_id = tbl_studienplan_lehrveranstaltung.studienplan_id)
									  AND tbl_studienplan_lehrveranstaltung.lehrveranstaltung_id = lv.lehrveranstaltung_id
									  AND tbl_studienplan_lehrveranstaltung.export
							)
							OR EXISTS (SELECT 1 FROM lehre.tbl_lehreinheit WHERE lehrveranstaltung_id = lv.lehrveranstaltung_id AND studiensemester_kurzbz = ?)
						  )
				AND lv.incoming > 0
				AND lv.aktiv
				AND (stg.typ IN ('b', 'm') OR stg.studiengang_kz = 10006)-- ECI Studiengang Campus International
			) lvs
			ORDER BY studiengang_kuerzel, orgform_kurzbz, lv_bezeichnung, lehrform_kurzbz, lehrveranstaltung_id;
		";

		return $this->execQuery($query, $parametersarray);
	}
	
	/**
	 * Gets Lehrveranstaltung and its Lehreinheiten (multiple rows possible).
	 * Returns empty array if student has no Lehrveranstaltung.
	 * @param $uid
	 * @param $studiensemester_kurzbz
	 * @param $lehrveranstaltung_id
	 * @return array|null
	 */
	public function getLvByStudent($uid, $studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$query = '
			SELECT * FROM campus.vw_student_lehrveranstaltung
			WHERE uid = ?
			AND studiensemester_kurzbz = ?
			AND lehrveranstaltung_id = ?;
		';
		
		return $this->execQuery($query, array($uid, $studiensemester_kurzbz, $lehrveranstaltung_id));
	}
}
