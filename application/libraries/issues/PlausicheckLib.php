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

		// get database for queries
		$this->_db = new DB_Model();
	}

	//------------------------------------------------------------------------------------------------------------------
	// Studiengang checks

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentenStgUngleichStgStudent($prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre.prestudent_id, stud.student_uid, pers.person_id,
				pre.studiengang_kz prestudent_studiengang, stud.studiengang_kz student_studiengang,
				stg.oe_kurzbz prestudent_stg_oe_kurzbz, student_stg.oe_kurzbz student_stg_oe_kurzbz
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN public.tbl_studiengang student_stg ON stud.studiengang_kz = student_stg.studiengang_kz
			WHERE
				stud.studiengang_kz != pre.studiengang_kz";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getOrgformStgUngleichOrgformPrestudent($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				studiengang.orgform_kurzbz as studorgkz, student.student_uid,
				prestudentstatus.orgform_kurzbz as studentorgkz, student.studiengang_kz studiengang
			FROM
				public.tbl_studiengang studiengang
				JOIN public.tbl_student student using(studiengang_kz)
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
				JOIN public.tbl_prestudentstatus prestudentstatus using(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid = student.student_uid)
			WHERE
				benutzer.aktiv = true
				AND prestudentstatus.status_kurzbz='Student'
				AND studiengang.studiengang_kz < 10000
				AND prestudentstatus.studiensemester_kurzbz = ?
				AND NOT EXISTS(
					SELECT 1 FROM lehre.tbl_studienplan JOIN lehre.tbl_studienordnung USING(studienordnung_id)
					WHERE
						tbl_studienordnung.studiengang_kz = prestudent.studiengang_kz
						AND tbl_studienplan.orgform_kurzbz = prestudentstatus.orgform_kurzbz)";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qry .= "
			ORDER BY student_uid";

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentMischformOhneOrgform($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				tbl_prestudent.prestudent_id, tbl_person.vorname, tbl_person.nachname, tbl_prestudent.studiengang_kz as studiengang
			FROM
				public.tbl_prestudent
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN public.tbl_studiengang USING(studiengang_kz)
			WHERE
				tbl_prestudentstatus.status_kurzbz IN ('Bewerber', 'Student')
				AND tbl_studiengang.mischform
				AND (tbl_prestudentstatus.orgform_kurzbz='' OR tbl_prestudentstatus.orgform_kurzbz IS NULL)
				AND tbl_prestudentstatus.studiensemester_kurzbz=?";

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getPrestudentStgUngleichStgStudienplan($prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				distinct tbl_person.vorname, tbl_person.nachname,
				tbl_prestudent.studiengang_kz as studiengang,
				tbl_prestudent.prestudent_id
			FROM
				public.tbl_prestudent
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN lehre.tbl_studienplan USING(studienplan_id)
				JOIN lehre.tbl_studienordnung USING(studienordnung_id)
				JOIN public.tbl_person USING(person_id)
			WHERE
				tbl_prestudent.studiengang_kz<>tbl_studienordnung.studiengang_kz";

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Studentstatus checks

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAbbrecherAktiv($prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				pre_status.status_kurzbz, benutzer.aktiv, benutzer.uid, student.studiengang_kz studiengang
			FROM
				public.tbl_prestudentstatus pre_status
				JOIN public.tbl_prestudent pre using(prestudent_id)
				JOIN public.tbl_student student using(prestudent_id)
				JOIN public.tbl_benutzer benutzer on(benutzer.uid=student.student_uid)
			WHERE
				pre_status.status_kurzbz ='Abbrecher' and benutzer.aktiv=true";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStudentstatusNachAbbrecher($prestudent_id = null)
	{
		$params = array();
		$result = array();

		$qry = "
			SELECT
				student.student_uid, prestudent.prestudent_id, student.studiengang_kz studiengang
			FROM
				public.tbl_student student
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus using(prestudent_id)
			WHERE
				prestatus.status_kurzbz = 'Abbrecher'";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			foreach ($students as $student)
			{
				$lastStatusRes = $this->_ci->PrestudentModel->getLastStatus($student->prestudent_id);

				if (isError($lastStatusRes)) return $lastStatusRes;

				if (hasData($lastStatusRes))
				{
					$lastStatus = getData($lastStatusRes)[0]->status_kurzbz;

					if ($lastStatus != 'Abbrecher') $result[] = $student;
				}
			}
		}

		return success($result);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAusbildungssemPrestudentUngleichAusbildungssemStatus($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz, $studiensemester_kurzbz, $studiensemester_kurzbz);

		$qry = "
			SELECT
				distinct(student.student_uid), prestudent.prestudent_id, status.ausbildungssemester,
				lv.semester, student.studiengang_kz studiengang
			FROM
				public.tbl_student student
				JOIN public.tbl_studentlehrverband lv using(student_uid)
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
				JOIN public.tbl_prestudentstatus status using(prestudent_id)
			WHERE
				status.studiensemester_kurzbz = ?
				AND lv.studiensemester_kurzbz = ?
				AND status.status_kurzbz NOT IN ('Interessent','Bewerber','Aufgenommener','Wartender','Abgewiesener','Unterbrecher')
				AND get_rolle_prestudent (prestudent_id, ?)='Student'
				AND status.ausbildungssemester != lv.semester";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getInaktiverStudentAktiverStatus($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				distinct(student.student_uid), student.studiengang_kz studiengang
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
			WHERE
				benutzer.aktiv=false
				AND get_rolle_prestudent(prestudent_id, ?) IN ('Student', 'Diplomand', 'Unterbrecher', 'Praktikant')";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getInskriptionVorLetzerBismeldung($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);
		$results = array();

		// get active students
		$qry = "
			SELECT
				DISTINCT(student.student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
			FROM
				public.tbl_benutzer benutzer
				JOIN public.tbl_student student on(benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus using(prestudent_id)
			WHERE
				benutzer.aktiv=true
				AND prestatus.studiensemester_kurzbz = ?";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			// get Bismeldedatum
			$datumBis = $this->_getDateForInscription($studiensemester_kurzbz);

			foreach ($students as $student)
			{
				// get first Status of student
				$firstStatusRes = $this->_ci->PrestudentstatusModel->getFirstStatus($student->prestudent_id, 'Student');

				if (isError($firstStatusRes)) return $firstStatusRes;

				if (hasData($firstStatusRes))
				{
					$datumInscription = date_format(date_create(getData($firstStatusRes)[0]->datum), 'Y-m-d');

					// if student inscription was before Bismeldedatum
					if ($datumInscription < $datumBis)
					{
						// add the student to result with dates for info output
						$student->datum_inskription = $datumInscription;
						$student->datum_bismeldung = $datumBis;

						$results[] = $student;
					}
				}
			}
		}

		return success($results);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumStudiensemesterFalscheReihenfolge($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);
		$rsults = array();

		// all active students with Status student in current semester
		$qry = "
			SELECT
				DISTINCT(student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
			FROM
				public.tbl_student student
				JOIN public.tbl_benutzer benutzer on(student.student_uid = benutzer.uid)
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
				JOIN public.tbl_prestudentstatus status using(prestudent_id)
			WHERE
				benutzer.aktiv=true
				AND status.status_kurzbz='Student'
				AND status.studiensemester_kurzbz=?";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			foreach ($students as $student)
			{
				// get all status of student, sorted by semester start
				$qryOrderSemester = "
					SELECT
						status.*
					FROM
						public.tbl_prestudentstatus status
						JOIN public.tbl_studiensemester semester using(studiensemester_kurzbz)
					WHERE
						prestudent_id = ?
					ORDER BY semester.start DESC, status.datum DESC;"

					$qryOrderSemesterRes = $this->_db->execReadOnlyQuery($qryOrderSemester, array($student->prestudent_id));

				if (isError($qryOrderSemesterRes)) return $qryOrderSemesterRes;

				$prestudentsSemesterSorted = hasData($qryOrderSemesterRes) ? getData($qryOrderSemesterRes) : array();

				// get all status of student, sorted by status date
				$this->PrestudentstatusModel->addSelect('studiensemester_kurzbz');
				$this->PrestudentstatusModel->addOrder('datum DESC, insertamum DESC');
				$qryOrderDateRes=$this->PrestudentstatusModel->loadWhere(array('prestudent_id' => $student->prestudent_id));

				if (isError($qryOrderDateRes)) return $qryOrderDateRes;

				$prestudentsDateSorted = hasData($qryOrderDateRes) ? getData($qryOrderDateRes) : array();

				// check if differently sorted status have same Studiensemester order
				$countStatus = count($prestudentsSemesterSorted);

				for ($i = 0; $i < $countStatus; $i++)
				{
					if ($prestudentsSemesterSorted[$i]->studiensemester_kurzbz != $prestudentsDateSorted[$i]->studiensemester_kurzbz)
					{
						$results[] = $student;
						break;
					}
				}
			}
		}

		return success($results);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAktiverStudentOhneStatus($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array();
		$results = array();

		$qry = "
			SELECT
				DISTINCT (student_uid), prestudent.prestudent_id, student.studiengang_kz studiengang
			FROM
				public.tbl_student student
				JOIN public.tbl_benutzer benutzer on (benutzer.uid = student.student_uid)
				JOIN public.tbl_prestudent prestudent using(prestudent_id)
				JOIN public.tbl_prestudentstatus status using(prestudent_id)
			WHERE
				benutzer.aktiv=true";

		if (isset($prestudent_id))
		{
			$qry .= " AND prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		$qryRes = $this->_db->execReadOnlyQuery($qry, $params);

		if (isError($qryRes)) return $qryRes;

		if (hasData($qryRes))
		{
			$students = getData($qryRes);

			$nextStudiensemesterRes = $this->_ci->StudiensemesterModel->getNextFrom($studiensemester_kurzbz);

			if (isError($nextStudiensemesterRes)) return $nextStudiensemesterRes;

			if (hasData($nextStudiensemesterRes))
			{
				$nextStudiensemester = getData($nextStudiensemesterRes)[0];

				foreach ($students as $student)
				{
					$lastStatusCurrSemRes = $this->_ci->PrestudentstatusModel->getLastStatus($student->prestudent_id, $studiensemester_kurzbz);

					if (isError($lastStatusCurrSemRes)) return $lastStatusCurrSemRes;

					$lastStatusNextSemRes = $this->_ci->PrestudentstatusModel->getLastStatus($student->prestudent_id, $nextStudiensemester);

					if (isError($lastStatusNextSemRes)) return $lastStatusNextSemRes;

					if (!hasData($lastStatusCurrSemRes) && !hasData($lastStatusNextSemRes))
						$results[] = $student;
				}
			}
		}

		return success($results);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getStudienplanUngueltig($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				distinct tbl_person.vorname, tbl_person.nachname,
				tbl_prestudent.prestudent_id,
				tbl_studienplan.bezeichnung,
				tbl_prestudent.studiengang_kz as studiengang,
				tbl_prestudentstatus.status_kurzbz,
				tbl_prestudentstatus.studiensemester_kurzbz,
				tbl_prestudentstatus.ausbildungssemester
			FROM
				public.tbl_prestudent
				JOIN public.tbl_prestudentstatus USING(prestudent_id)
				JOIN public.tbl_person USING(person_id)
				JOIN lehre.tbl_studienplan USING(studienplan_id)
			WHERE
				status_kurzbz in('Student', 'Interessent','Bewerber','Aufgenommener')
				AND NOT EXISTS (
					SELECT
						1
					FROM
						lehre.tbl_studienplan_semester
					WHERE
						studienplan_id=tbl_prestudentstatus.studienplan_id
						AND tbl_studienplan_semester.semester = tbl_prestudentstatus.ausbildungssemester
						AND tbl_studienplan_semester.studiensemester_kurzbz = tbl_prestudentstatus.studiensemester_kurzbz
				)
				AND tbl_prestudentstatus.studiensemester_kurzbz=?";

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlAbschlusspruefungen($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT * FROM (
				SELECT
					DISTINCT pre.prestudent_id, student_uid,
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
					JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
					JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				WHERE
					status_kurzbz = 'Absolvent'
					AND studiensemester_kurzbz = ?
					AND stg.melderelevant
					AND NOT EXISTS ( /* exclude gs */
						SELECT 1
						FROM bis.tbl_mobilitaet
						WHERE prestudent_id = pre.prestudent_id
						AND studiensemester_kurzbz = prestatus.studiensemester_kurzbz
					)
			) studenten
			WHERE anzahl_abschlusspruefungen != 1";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumAbschlusspruefungFehlt($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array();
		$results = array();

		$pruefungenRes = $this->_getInvalidAbschlusspruefungen($studiensemester_kurzbz, $prestudent_id);

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
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getDatumSponsionFehlt($prestudent_id = null)
	{
		$params = array();
		$results = array();

		$pruefungenRes = $this->_getInvalidAbschlusspruefungen($studiensemester_kurzbz, $prestudent_id);

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
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getBewerberNichtZumRtAngetreten($studiensemester_kurzbz, $prestudent_id = null)
	{
		$previousStudiensemesterRes = $this->_ci->StudiensemesterModel->getPreviousFrom($studiensemester_kurzbz);

		if (isError($previousStudiensemesterRes)) return $previousStudiensemesterRes;

		$previousStudiensemester = hasData($previousStudiensemesterRes) ? getData($previousStudiensemesterRes)[0]->studiensemester_kurzbz : '';

		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				vorname, nachname, tbl_prestudent.prestudent_id, studiengang_kz
			FROM
				public.tbl_prestudent
				JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
				JOIN public.tbl_person USING(person_id)
				LEFT JOIN bis.tbl_orgform USING(orgform_kurzbz)
			WHERE
				AND status_kurzbz='Bewerber'
				AND reihungstestangetreten=false";

		if (!isEmptyString($previousStudiensemester))
		{
			$qry .= "AND (studiensemester_kurzbz=? OR studiensemester_kurzbz=?)";
			$params[] = $previousStudiensemester;
		}
		else
		{
			$qry .= "AND studiensemester_kurzbz=?";
		}

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAktSemesterNull($prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT pre.prestudent_id
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus prestat USING(prestudent_id)
			WHERE
				prestat.status_kurzbz != 'Incoming'
				AND ausbildungssemester = 0";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getAbschlussstatusFehlt($prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT tbl_prestudent.prestudent_id, tbl_person.vorname, tbl_person.nachname, tbl_prestudent.studiengang_kz as studiengang
			FROM
				public.tbl_prestudent
				JOIN public.tbl_person USING(person_id)
			WHERE
				NOT EXISTS(
					SELECT
						1
					FROM
						public.tbl_prestudentstatus ps
						JOIN public.tbl_studiensemester USING(studiensemester_kurzbz)
					WHERE
						prestudent_id=tbl_prestudent.prestudent_id
						AND tbl_studiensemester.ende>now()
				)
				AND '2018-01-01'<(SELECT max(datum) FROM public.tbl_prestudentstatus WHERE prestudent_id=tbl_prestudent.prestudent_id)
				AND NOT EXISTS(SELECT 1 FROM public.tbl_prestudentstatus ps
					WHERE
						prestudent_id=tbl_prestudent.prestudent_id
						AND status_kurzbz IN('Abbrecher','Abgewiesener','Absolvent','Incoming')
				)";

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
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getGbDatumWeitZurueck($studiensemester_kurzbz, $person_id = null)
	{
		$params = array($studiensemester_kurzbz);

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
					JOIN public.tbl_prestudentstatus USING(prestudent_id)
					WHERE person_id = pers.person_id AND studiensemester_kurzbz = ?)";

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getNationNichtOeAberGmeinde($person_id = null)
	{
		$params = array();

		$qry = "SELECT DISTINCT tbl_person.person_id
				FROM
					public.tbl_adresse
					JOIN public.tbl_prestudent USING(person_id)
					JOIN public.tbl_person USING(person_id)
					JOIN public.tbl_student USING(prestudent_id)
					JOIN public.tbl_benutzer ON(uid=student_uid)
				WHERE
					tbl_adresse.nation!='A'
					AND tbl_benutzer.aktiv
					AND gemeinde NOT IN ('Münster')
					AND EXISTS(SELECT 1 FROM bis.tbl_gemeinde WHERE name = tbl_adresse.gemeinde)";

		if (isset($person_id))
		{
			$qry .= " AND tbl_person.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlHeimatadressen($person_id = null)
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
				JOIN public.tbl_prestudent USING(person_id)
				JOIN public.tbl_student USING(prestudent_id)
			WHERE
				anzahl_adressen != 1";

		if (isset($person_id))
		{
			$qry .= " AND person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getFalscheAnzahlZustelladressen($person_id = null)
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
				JOIN public.tbl_prestudent USING(person_id)
				JOIN public.tbl_student USING(prestudent_id)
			WHERE
				anzahl_adressen != 1";

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
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getIncomingHeimatNationOesterreich($person_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT pers.person_id
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_prestudentstatus prestatus
				JOIN public.tbl_person pers USING(person_id)
				JOIN public.tbl_adresse addr USING(person_id)
			WHERE
				prestat.status_kurzbz = 'Incoming'
				AND addr.nation = 'A'
				AND addr.heimatadresse";

		if (isset($person_id))
		{
			$qry .= " AND pers.person_id = ?";
			$params[] = $person_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	/**
	 * Studiengang must be the same for prestudent and student.
	 * @param int prestudent_id if check is to be executed only for one prestudent
	 * @return success with prestudents or error
	 */
	public function getIncomingOhneIoDatensatz($prestudent_id = null)
	{
		$params = array();

		$qry = "
			SELECT
				DISTINCT ON(student_uid, nachname, vorname),
				tbl_person.person_id,
				tbl_prestudent.prestudent_id
			FROM
				public.tbl_student
				JOIN public.tbl_benutzer ON(student_uid=uid)
				JOIN public.tbl_person USING(person_id)
				JOIN public.tbl_prestudent USING(prestudent_id)
				JOIN public.tbl_prestudentstatus ON(tbl_prestudent.prestudent_id=tbl_prestudentstatus.prestudent_id)
				JOIN public.tbl_studiengang ON(tbl_studiengang.studiengang_kz=tbl_student.studiengang_kz)
			WHERE
				bismelden=TRUE
				-- AND tbl_student.studiengang_kz=?
				AND (status_kurzbz='Incoming' AND NOT EXISTS (SELECT 1 FROM bis.tbl_bisio WHERE student_uid=tbl_student.student_uid))";

		if (isset($prestudent_id))
		{
			$qry .= " AND tbl_prestudent.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	//------------------------------------------------------------------------------------------------------------------
	// Private methods

	private function _getInvalidAbschlusspruefungen($studiensemester_kurzbz, $prestudent_id = null)
	{
		$params = array($studiensemester_kurzbz);

		$qry = "
			SELECT
				pre.prestudent_id, stud.student_uid, pruefung.datum, pruefung.sponsion
			FROM
				public.tbl_prestudent pre
				JOIN public.tbl_student stud USING(prestudent_id)
				JOIN public.tbl_prestudentstatus prestatus USING(prestudent_id)
				JOIN public.tbl_studiengang stg ON pre.studiengang_kz = stg.studiengang_kz
				JOIN lehre.tbl_abschlusspruefung pruefung ON stud.student_uid = pruefung.student_uid
			WHERE
				status_kurzbz = 'Absolvent'
				AND studiensemester_kurzbz = ?
				AND stg.melderelevant
				AND NOT EXISTS ( /* exclude gs */
					SELECT 1
					FROM bis.tbl_mobilitaet
					WHERE prestudent_id = pre.prestudent_id
					AND studiensemester_kurzbz = prestatus.studiensemester_kurzbz
				)
				AND abschlussbeurteilung_kurzbz!='nicht'
				AND abschlussbeurteilung_kurzbz IS NOT NULL
				AND (pruefung.datum IS NULL OR pruefung.sponsion IS NULL)";

		if (isset($prestudent_id))
		{
			$qry .= " AND pre.prestudent_id = ?";
			$params[] = $prestudent_id;
		}

		return $this->_db->execReadOnlyQuery($qry, $params);
	}

	private function _getDateForInscription($studiensemester_kurzbz)
	{
		$semesterYear = substr($studiensemester_kurzbz, 2, 6);
		$semesterType = substr($studiensemester_kurzbz, 0, 2);

		if ($semesterType == 'SS')
		{
			$date = "15.11.".($semesterYear - 1);
			$date = date_format(date_create($date), 'Y-m-d');
			return $date;
		}

		if ($semesterType == 'WS')
		{
			$date = '15.04'.$semesterYear;
			$date = date_format(date_create($date, 'Y-m-d'));
			return $date;
		}
	}
}
