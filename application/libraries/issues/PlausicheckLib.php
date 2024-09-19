<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class PlausicheckLib
{
	private $_ci; // Code igniter instance
	private $_db; // database object

	/**
	 * Object initialization
	 */
	public function __construct()
	{
		$this->_ci =& get_instance(); // get ci instance

		// load models
		$this->_ci->load->model('crm/Prestudent_model', 'PrestudentModel');
		$this->_ci->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->_ci->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		// get database for queries
		$this->_db = new DB_Model();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Studiengang checks

	/**
	 * Studiengang should be the same for prestudent and student.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStgPrestudentUngleichStgStudent($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz prestudent_stg_oe_kurzbz, student_stg.oe_kurzbz student_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_studiengang student_stg ON stud.studiengang_kz = student_stg.studiengang_kz
			WHERE
				stud.studiengang_kz != pre.studiengang_kz";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Orgform of a Studiengang in Studienplan should be the same as orgform of student.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getOrgformStgUngleichOrgformPrestudent($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, status.studiensemester_kurzbz,
				studiengang.orgform_kurzbz AS stg_orgform, status.orgform_kurzbz AS student_orgform,
				prestudent.studiengang_kz AS student_studiengang, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_studiengang studiengang
				JOIN public.tbl_student student USING(studiengang_kz)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid = student.student_uid)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv = true
				AND status.status_kurzbz='Student'
				AND studiengang.studiengang_kz < 10000
				AND status.studiensemester_kurzbz = ?
				AND NOT EXISTS(
					SELECT 1 FROM lehre.tbl_studienplan
					JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE
						tbl_studienordnung.studiengang_kz = prestudent.studiengang_kz
						AND tbl_studienplan.orgform_kurzbz = status.orgform_kurzbz)";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		$qry .= "
			ORDER BY student_uid";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students in "mixed" Studiengang should have Orgform.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentMischformOhneOrgform($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status.status_kurzbz IN ('Bewerber', 'Student')
				AND stg.mischform
				AND (status.orgform_kurzbz='' OR status.orgform_kurzbz IS NULL)
				AND status.studiensemester_kurzbz=?";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang should be the same for prestudent and studienplan.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @param studienordnung_id int if check is to be executed only for a certain studienordnung_id
	 * @return success with prestudents or error
	 */
	public function getStgPrestudentUngleichStgStudienplan($studiengang_kz = null, $prestudent_id = null, $studienordnung_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (ps.prestudent_id) ps.person_id, ps.prestudent_id, stordnung.studienordnung_id,
				stplan.bezeichnung AS studienplan, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent ps
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN lehre.tbl_studienplan stplan USING(studienplan_id)
				JOIN lehre.tbl_studienordnung stordnung USING(studienordnung_id)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_studiengang stg ON ps.studiengang_kz = stg.studiengang_kz
			WHERE
				ps.studiengang_kz<>stordnung.studiengang_kz
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND ps.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		if (isset($studienordnung_id))
		{
			$qry .= " AND stordnung.studienordnung_id = ?";
			$params[] = $studienordnung_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Studentstatus checks

	/**
	 * Abbrecher cannot be active.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAbbrecherAktiv($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudentstatus pre_status
				JOIN public.tbl_prestudent pre USING(prestudent_id)
				JOIN public.tbl_student student USING(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				pre_status.status_kurzbz ='Abbrecher'
				AND benutzer.aktiv=true";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * There shouldn't be any status after Abbrecher status.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStudentstatusNachAbbrecher($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				prestatus.status_kurzbz = 'Abbrecher'
				AND get_rolle_prestudent(prestudent.prestudent_id, prestatus.studiensemester_kurzbz) <> 'Abbrecher'";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Ausbildungssemester of prestudent (lehrverband) must be the same as Ausbildungssemester of prestudentstatus.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAusbildungssemPrestudentUngleichAusbildungssemStatus($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz, $studiensemester_kurzbz, $studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT(student.student_uid), student.student_uid, prestudent.person_id, prestudent.prestudent_id,
				status.ausbildungssemester AS status_ausbildungssemester, lv.semester AS student_ausbildungssemester, status.studiensemester_kurzbz,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_studentlehrverband lv USING(student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				status.studiensemester_kurzbz = ?
				AND lv.studiensemester_kurzbz = ?
				AND status.status_kurzbz NOT IN ('Interessent','Bewerber','Aufgenommener','Wartender','Abgewiesener','Unterbrecher')
				AND get_rolle_prestudent (prestudent_id, ?)='Student'
				AND status.ausbildungssemester != lv.semester";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students with active status should have an active Benutzer.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getInaktiverStudentAktiverStatus($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$aktStudiensemesterRes = $this->_ci->StudiensemesterModel->getAkt();

		if (isError($aktStudiensemesterRes)) return $aktStudiensemesterRes;

		$studiensemester_kurzbz = hasData($aktStudiensemesterRes) ? getData($aktStudiensemesterRes)[0]->studiensemester_kurzbz : '';

		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT(student.student_uid), prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=false
				AND EXISTS (SELECT 1 FROM public.tbl_prestudentstatus WHERE prestudent_id = prestudent.prestudent_id AND studiensemester_kurzbz = ?)
				AND get_rolle_prestudent(prestudent_id, NULL) IN ('Student', 'Diplomand', 'Unterbrecher', 'Praktikant')
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students of a semester shouldn't start studies before the date of Bismeldung.
	 * e.g. If student studies in WS2022 datum of status shouldn't be before 15.4.2020
	 * e.g. If student studies in SS2022 datum of status shouldn't be before 15.11.2022
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getInskriptionVorLetzerBismeldung($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		// get Bismeldedatum
		$datumBis = $this->_getBisdateFromSemester($studiensemester_kurzbz);

		$params = array($datumBis, $studiensemester_kurzbz, $datumBis);

		// get active students
		$qry = "
			SELECT
				DISTINCT ON (student.student_uid) ? AS datum_bismeldung,
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=true
				AND status.studiensemester_kurzbz = ?
				/* inscription date before date of first student status */
				AND (
					SELECT datum
					FROM public.tbl_prestudentstatus
					WHERE prestudent_id = prestudent.prestudent_id
					AND studiensemester_kurzbz = status.studiensemester_kurzbz
					AND status_kurzbz = 'Student'
					ORDER BY datum, insertamum, ext_id
					LIMIT 1
				) < ?
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Status Dates and status studysemester dates should be in correct order.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumStudiensemesterFalscheReihenfolge($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		// all active students with Status student in current semester
		$qry = "
			SELECT DISTINCT ON (prestudent_id) *
			FROM (
				SELECT
					prestudent.person_id, prestudent.prestudent_id,
					stg.studiengang_kz, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
					ROW_NUMBER () OVER (
						PARTITION BY prestudent.prestudent_id
						ORDER BY sem.start DESC, status.datum DESC, status.insertamum DESC, status.ext_id DESC
					) AS reihenfolge_semester,
					ROW_NUMBER () OVER (
						PARTITION BY prestudent.prestudent_id
						ORDER BY status.datum DESC, status.insertamum DESC, status.ext_id DESC
					) AS reihenfolge_datum
				FROM
					public.tbl_student student
					JOIN public.tbl_benutzer benutzer on(student.student_uid = benutzer.uid)
					JOIN public.tbl_prestudent prestudent USING(prestudent_id)
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiensemester sem USING(studiensemester_kurzbz)
					JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
				WHERE
					benutzer.aktiv=true
					AND status.status_kurzbz='Student'
			) reihenfolge
			WHERE reihenfolge_semester <> reihenfolge_datum";

		if (isset($studiengang_kz))
		{
			$qry .= " AND studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students with active Benutzer should have a status in the current semester.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAktiverStudentOhneStatus($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT (student_uid), prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student student
				JOIN public.tbl_benutzer benutzer on (benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON prestudent.studiengang_kz = stg.studiengang_kz
			WHERE
				benutzer.aktiv=TRUE
				AND stg.melderelevant
				AND prestudent.bismelden
				AND NOT EXISTS (
					SELECT 1
					FROM public.tbl_prestudentstatus
					JOIN public.tbl_studiensemester sem USING (studiensemester_kurzbz)
					WHERE prestudent_id = prestudent.prestudent_id
					/* buffer of four months, as status are often entered later */
					AND sem.ende::date > NOW() - interval '4 months'
				)";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studienplan should be valid in current Ausbildungssemester of prestudent.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStudienplanUngueltig($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pre.person_id, pre.prestudent_id,
				tbl_studienplan.bezeichnung AS studienplan,
				status.status_kurzbz,
				status.studiensemester_kurzbz,
				status.ausbildungssemester,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_person USING(person_id)
				JOIN lehre.tbl_studienplan USING(studienplan_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				status_kurzbz in('Student', 'Interessent','Bewerber','Aufgenommener')
				AND NOT EXISTS (
					SELECT
						1
					FROM
						lehre.tbl_studienplan_semester
					WHERE
						studienplan_id=status.studienplan_id
						AND tbl_studienplan_semester.semester = status.ausbildungssemester
						AND tbl_studienplan_semester.studiensemester_kurzbz = status.studiensemester_kurzbz
				)
				AND status.studiensemester_kurzbz=?
				AND pre.bismelden
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students with finished studies should have exactly one final exam.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlAbschlusspruefungen($studiensemester_kurzbz = null, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT * FROM (
				SELECT
					DISTINCT ON(pre.prestudent_id) pre.person_id, pre.prestudent_id, student_uid, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz,
					(
						SELECT COUNT(*)
						FROM lehre.tbl_abschlusspruefung
						WHERE student_uid = stud.student_uid
						AND abschlussbeurteilung_kurzbz != 'nicht'
						AND abschlussbeurteilung_kurzbz IS NOT NULL
					) AS anzahl_abschlusspruefungen
				FROM
					public.tbl_prestudent pre
					JOIN public.tbl_student stud USING(prestudent_id)
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				WHERE
					status_kurzbz = 'Absolvent'
					AND pre.bismelden
					AND stg.melderelevant
					AND NOT EXISTS ( /* exclude gs */
						SELECT 1
						FROM bis.tbl_mobilitaet
						WHERE prestudent_id = pre.prestudent_id
						AND studiensemester_kurzbz = status.studiensemester_kurzbz
					)";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qry .= ") studenten
			WHERE anzahl_abschlusspruefungen != 1";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Date of final exam shouldn't be missing for Absolvent.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param abschlusspruefung_id int if check is to be executed for a certain Abschlussprüfung
	 * @return success with prestudents or error
	 */
	public function getDatumAbschlusspruefungFehlt($studiensemester_kurzbz = null, $studiengang_kz = null, $abschlusspruefung_id = null)
	{
		$results = array();

		$pruefungenRes = $this->_getInvalidAbschlusspruefungen($studiensemester_kurzbz, $studiengang_kz, $abschlusspruefung_id);

		if (isError($pruefungenRes)) return $pruefungenRes;

		if (hasData($pruefungenRes))
		{
			$pruefungen = getData($pruefungenRes);

			foreach ($pruefungen as $pruefung)
			{
				if (isEmptyString($pruefung->datum)) $results[] = $pruefung;
			}
		}

		return success($results);
	}

	/**
	 * Date of sponsion shouldn't be missing for Absolvent.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param abschlusspruefung_id int if check is to be executed only for a certain Abschlussprüfung
	 * @return success with prestudents or error
	 */
	public function getDatumSponsionFehlt($studiensemester_kurzbz = null, $studiengang_kz = null, $abschlusspruefung_id = null)
	{
		$results = array();

		$pruefungenRes = $this->_getInvalidAbschlusspruefungen($studiensemester_kurzbz, $studiengang_kz, $abschlusspruefung_id);

		if (isError($pruefungenRes)) return $pruefungenRes;

		if (hasData($pruefungenRes))
		{
			$pruefungen = getData($pruefungenRes);

			foreach ($pruefungen as $pruefung)
			{
				if (isEmptyString($pruefung->sponsion)) $results[] = $pruefung;
			}
		}

		return success($results);
	}

	/**
	 * Bewerber should have participated in Reihungstest.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getBewerberNichtZumRtAngetreten($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$previousStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

		if (isError($previousStudiensemesterRes)) return $previousStudiensemesterRes;

		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				prestudent.person_id, prestudent.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent prestudent
				JOIN public.tbl_prestudentstatus status ON(prestudent.prestudent_id=status.prestudent_id)
				JOIN public.tbl_person USING(person_id)
				LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status_kurzbz='Bewerber'
				AND reihungstestangetreten=false
				AND stg.melderelevant
				AND prestudent.bismelden";

		if (hasData($previousStudiensemesterRes))
		{
			$previousStudiensemester = getData($previousStudiensemesterRes)[0]->studiensemester_kurzbz;
			$qry .= " AND (studiensemester_kurzbz=? OR studiensemester_kurzbz=?)";
			$params[] = $previousStudiensemester;
		}
		else
		{
			$qry .= " AND studiensemester_kurzbz=?";
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Current Ausbildungssemester shouldn't be 0.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAktSemesterNull($studiensemester_kurzbz, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, prestat.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus prestat USING(prestudent_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				prestat.status_kurzbz != 'Incoming'
				AND prestat.studiensemester_kurzbz = ?
				AND ausbildungssemester = 0
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Prestudent should have a final status.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAbschlussstatusFehlt($studiensemester_kurzbz = null, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON (pre.prestudent_id)
				pre.person_id, pre.prestudent_id, stg.oe_kurzbz AS prestudent_stg_oe_kurzbz, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				NOT EXISTS( /*student does not study anymore*/
					SELECT
						1
					FROM
						public.tbl_prestudentstatus ps
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						prestudent_id=pre.prestudent_id
						/* 4 months: There might be Diplomanden, in summer months end status is often not entered yet */
						AND tbl_studiensemester.ende>now() - interval '4 months'
				)
				/* check only valid begininng with 2018 */
				AND '2018-01-01'<(SELECT max(datum) FROM public.tbl_prestudentstatus WHERE prestudent_id=pre.prestudent_id)
				AND NOT EXISTS( /* no end status */
					SELECT 1
					FROM public.tbl_prestudentstatus ps
					WHERE
						prestudent_id=pre.prestudent_id
						AND status_kurzbz IN('Abbrecher','Abgewiesener','Absolvent','Incoming')
				)
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$prevStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

			if (isError($prevStudiensemesterRes)) return $prevStudiensemesterRes;

			if (hasData($prevStudiensemesterRes))
			{
				// if Studiensemester given, check only if has status in current or previous semester
				$prevStudiensemester = getData($prevStudiensemesterRes)[0]->studiensemester_kurzbz;
				$qry .= " AND EXISTS (
							SELECT 1
							FROM public.tbl_prestudentstatus ps
							WHERE studiensemester_kurzbz IN (?, ?)
							AND ps.prestudent_id = pre.prestudent_id
						)";
				$params[] = $prevStudiensemester;
				$params[] = $studiensemester_kurzbz;
			}
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Person checks

	/**
	 * Birthdate is too long ago.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @return success with prestudents or error
	 */
	public function getGbDatumWeitZurueck($studiensemester_kurzbz = null, $studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pers.person_id
			FROM
				public.tbl_person pers
			WHERE
				pers.gebdatum < '1920-01-01'
				AND EXISTS (
					SELECT 1
					FROM public.tbl_prestudent
					JOIN public.tbl_prestudentstatus status USING(prestudent_id)
					JOIN public.tbl_studiengang stg USING(studiengang_kz)
					WHERE person_id = pers.person_id";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		$qry .= ")";

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Nation is not Austria, but address has austrian Gemeinde.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @return success with prestudents or error
	 */
	public function getNationNichtOesterreichAberGemeinde($studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "SELECT DISTINCT tbl_person.person_id, adr.gemeinde, adr.adresse_id
				FROM
					public.tbl_adresse adr
					JOIN public.tbl_prestudent USING(person_id)
					JOIN public.tbl_person USING(person_id)
					JOIN public.tbl_student USING(prestudent_id)
					JOIN public.tbl_benutzer ON(uid=student_uid)
					JOIN public.tbl_studiengang stg ON tbl_prestudent.studiengang_kz = stg.studiengang_kz
				WHERE
					adr.nation!='A'
					AND tbl_benutzer.aktiv
					AND gemeinde NOT IN ('Münster')
					AND EXISTS(SELECT 1 FROM bis.tbl_gemeinde WHERE name = adr.gemeinde)";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND tbl_person.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students should have exactly one home address.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlHeimatadressen($studiensemester_kurzbz = null, $studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT person_id
			FROM
				(
					SELECT person_id, COUNT(adresse_id) AS anzahl_adressen
					FROM public.tbl_adresse addr
					WHERE heimatadresse IS TRUE
					GROUP BY person_id
				) adressen
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent pre USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_student USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				anzahl_adressen != 1
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Students should have exactly one delivery address.
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlZustelladressen($studiensemester_kurzbz = null, $studiengang_kz = null, $person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT person_id
			FROM
				(
					SELECT person_id, COUNT(adresse_id) AS anzahl_adressen
					FROM public.tbl_adresse addr
					WHERE zustelladresse IS TRUE
					GROUP BY person_id
				) adressen
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent pre USING(person_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_student USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
			WHERE
				anzahl_adressen != 1
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// I/O checks

	/**
	 * Incoming shouldn't have austrian home address.
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param person_id int if check is to be executed only for one person
	 * @return success with prestudents or error
	 */
	public function getIncomingHeimatNationOesterreich($studiensemester_kurzbz, $studiengang_kz = null, $person_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				DISTINCT pers.person_id, status.studiensemester_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_adresse addr USING(person_id)
				JOIN public.tbl_studiengang stg USING(studiengang_kz)
			WHERE
				status.status_kurzbz = 'Incoming'
				AND addr.nation = 'A'
				AND addr.heimatadresse
				AND status.studiensemester_kurzbz = ?
				AND stg.melderelevant
				AND pre.bismelden";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Incoming should have IN/OUT data.
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getIncomingOhneIoDatensatz($studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON(student_uid, nachname, vorname)
				tbl_person.person_id,
				tbl_prestudent.prestudent_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student
				JOIN public.tbl_benutzer ON(student_uid=uid)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
				JOIN public.tbl_studiengang stg ON(stg.studiengang_kz=tbl_student.studiengang_kz)
			WHERE
				bismelden=TRUE
				AND status_kurzbz='Incoming' AND NOT EXISTS (SELECT 1 FROM bis.tbl_bisio WHERE student_uid=tbl_student.student_uid)
				AND stg.melderelevant";

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Incoming or gemeinsame Studien students should not receive funding (not be förderrelevant).
	 * @param studiensemester_kurzbz string check is to be executed for certain Studiensemester
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param prestudent_id int if check is to be executed only for one prestudent
	 * @return object success or error
	 */
	public function getIncomingOrGsFoerderrelevant($studiensemester_kurzbz = null, $studiengang_kz = null, $prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON(prestudent_id)
				pers.person_id,
				ps.prestudent_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_student stud
				JOIN public.tbl_benutzer ON(student_uid=uid)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_prestudent ps USING(prestudent_id)
				JOIN public.tbl_prestudentstatus status USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON(stg.studiengang_kz=stud.studiengang_kz)
			WHERE
				(
					status.status_kurzbz = 'Incoming'
					OR EXISTS (
						SELECT 1
						FROM
							bis.tbl_mobilitaet
							JOIN public.tbl_prestudent USING(prestudent_id)
						WHERE
							prestudent_id = ps.prestudent_id
							AND gsstudientyp_kurzbz = 'Extern'
					)
				)
				AND (ps.foerderrelevant <> FALSE OR ps.foerderrelevant IS NULL)
				AND bismelden=TRUE
				AND stg.melderelevant";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND status.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND ps.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	/**
	 * Get final exams in a semester which are invalid (e.g. missing data)
	 * @param studiensemester_kurzbz string if check is to be executed for certain Studiengang
	 * @param studiengang_kz int if check is to be executed for certain Studiengang
	 * @param abschlusspruefung_id int if check is to be executed for certain Abschlussprüfung
	 */
	private function _getInvalidAbschlusspruefungen($studiensemester_kurzbz = null, $studiengang_kz = null, $abschlusspruefung_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.person_id, pre.prestudent_id,
				pruefung.sponsion, pruefung.datum, pruefung.abschlusspruefung_id,
				stg.oe_kurzbz AS prestudent_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN lehre.tbl_abschlusspruefung pruefung ON stud.student_uid = pruefung.student_uid
			WHERE
				status_kurzbz = 'Absolvent'
				AND NOT EXISTS ( /* exclude gs */
					SELECT 1
					FROM bis.tbl_mobilitaet
					WHERE prestudent_id = pre.prestudent_id
					AND studiensemester_kurzbz = prestatus.studiensemester_kurzbz
				)
				AND abschlussbeurteilung_kurzbz!='nicht'
				AND abschlussbeurteilung_kurzbz IS NOT NULL
				AND (pruefung.datum IS NULL OR pruefung.sponsion IS NULL)
				AND pre.bismelden
				AND stg.melderelevant";

		if (isset($studiensemester_kurzbz))
		{
			$qry .= " AND prestatus.studiensemester_kurzbz = ?";
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($studiengang_kz))
		{
			$qry .= " AND stg.studiengang_kz = ?";
			$params[] = $studiengang_kz;
		}

		if (isset($abschlusspruefung_id))
		{
			$qry .= " AND pruefung.abschlusspruefung_id = ?";
			$params[] = $abschlusspruefung_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Gets Bismeldedate from Studiensemester.
	 * @param studiensemester_kurzbz string
	 */
	private function _getBisdateFromSemester($studiensemester_kurzbz)
	{
		$semesterYear = substr($studiensemester_kurzbz, 2, 6);
		$semesterType = substr($studiensemester_kurzbz, 0, 2);

		if ($semesterType == 'SS')
		{
			return date_format(date_create(($semesterYear - 1)."-11-15"), 'Y-m-d');
		}

		if ($semesterType == 'WS')
		{
			return date_format(date_create($semesterYear."-04-15"), 'Y-m-d');
		}
	}
}
