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
	}

	/**
	 * Gets unique Groupstrings for Lehrveranstaltungen, e.g. WS2018_BIF_1_PRJM_VZ_LV12345
	 * @param $studiensemester_kurzbz
	 * @param null $ausbildungssemester
	 * @param null $studiengang_kz
	 * @param null $lehrveranstaltung_ids
	 * @return StdClass
	 */
	public function getLehrveranstaltungGroupNames($studiensemester_kurzbz, $ausbildungssemester = null, $studiengang_kz = null, $lehrveranstaltung_ids = null)
	{
		$this->load->model('system/studiensemester_model', 'StudiensemesterModel');
		$this->load->model('organisation/studienplan_model', 'StudiengangModel');

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
					return error($result->retval);

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

			SELECT lehrveranstaltung_id, ? || '_' || kuerzel || '_' || lvpostfix AS lvgroupname
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
	 * @return array|null
	 */
	public function getStudentsByLv($studiensemester_kurzbz, $lehrveranstaltung_id)
	{
		$query = "SELECT
			distinct on(nachname, vorname, person_id) vorname, nachname, matrikelnr,
			tbl_studentlehrverband.semester, tbl_studentlehrverband.verband, tbl_studentlehrverband.gruppe,
			(SELECT status_kurzbz FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_student.prestudent_id ORDER BY datum DESC, insertamum DESC, ext_id DESC LIMIT 1) as status,
			tbl_bisio.bisio_id, tbl_bisio.von, tbl_bisio.bis, tbl_student.studiengang_kz AS stg_kz_student,
			tbl_zeugnisnote.note, tbl_mitarbeiter.mitarbeiter_uid, tbl_person.matr_nr, tbl_benutzer.uid,
			UPPER(tbl_studiengang.typ::varchar(1) || tbl_studiengang.kurzbz) as kuerzel, tbl_studiengang.orgform_kurzbz, vw_student_lehrveranstaltung.semester, vw_student_lehrveranstaltung.studiensemester_kurzbz, vw_student_lehrveranstaltung.bezeichnung

		FROM
			campus.vw_student_lehrveranstaltung JOIN public.tbl_benutzer USING(uid)
			JOIN public.tbl_person USING(person_id) LEFT JOIN public.tbl_student ON(uid=student_uid)
			LEFT JOIN public.tbl_mitarbeiter ON(uid=mitarbeiter_uid)
			LEFT JOIN public.tbl_studentlehrverband USING(student_uid,studiensemester_kurzbz)
			LEFT JOIN lehre.tbl_zeugnisnote on(vw_student_lehrveranstaltung.lehrveranstaltung_id=tbl_zeugnisnote.lehrveranstaltung_id AND tbl_zeugnisnote.student_uid=tbl_student.student_uid AND tbl_zeugnisnote.studiensemester_kurzbz=tbl_studentlehrverband.studiensemester_kurzbz)
			LEFT JOIN bis.tbl_bisio ON(uid=tbl_bisio.student_uid)
			LEFT JOIN public.tbl_studiengang ON(vw_student_lehrveranstaltung.studiengang_kz=tbl_studiengang.studiengang_kz)
		WHERE
			vw_student_lehrveranstaltung.studiensemester_kurzbz=?
		AND
			vw_student_lehrveranstaltung.lehrveranstaltung_id=? 
		ORDER BY nachname, vorname, person_id, tbl_bisio.bis DESC";

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
}
