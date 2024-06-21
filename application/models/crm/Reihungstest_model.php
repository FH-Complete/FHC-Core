<?php

class Reihungstest_model extends DB_Model
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_reihungstest';
		$this->pk = 'reihungstest_id';
	}	
	
	/**
	 * Gets a test from a test id only if it is available
	 */
	public function checkAvailability($reihungstest_id)
	{
		$query = 'SELECT public.tbl_reihungstest.*
					FROM public.tbl_reihungstest LEFT JOIN public.tbl_rt_studienplan USING(reihungstest_id)
				   WHERE tbl_reihungstest.oeffentlich = TRUE
					 AND tbl_reihungstest.datum > NOW()
					 AND tbl_reihungstest.anmeldefrist >= NOW()
					 AND COALESCE (
							tbl_reihungstest.max_teilnehmer,
							(
								SELECT SUM(arbeitsplaetze)
								  FROM public.tbl_ort JOIN public.tbl_rt_ort USING(ort_kurzbz)
								 WHERE rt_id = tbl_reihungstest.reihungstest_id
							)
							) - (
								SELECT COUNT(*)
								  FROM public.tbl_rt_person
								 WHERE rt_id = tbl_reihungstest.reihungstest_id
							) > 0
					  AND reihungstest_id = ?';
		
		return $this->execQuery($query, array($reihungstest_id));
	}
	
	/**
	 * Checks if there are active studyplans which have no public placement tests assigned yet.
	 * Only check assignment to studyplans that are
	 *	- Bachelor, 
	 *	- active, 
	 *	- set as online application
	 *  - valid for 1st terms
	 * @return array Returns object array with studyplans that have no public placement tests assigned yet.
	 */
	public function checkMissingReihungstest()
	{
		$query = '
			SELECT 
				bezeichnung
			FROM
				lehre.tbl_studienplan
			WHERE
				studienplan_id
			IN 
			(
				SELECT DISTINCT 
					studienplan_id
				FROM 
					public.tbl_studiensemester
				JOIN
					lehre.tbl_studienplan_semester 
					USING (studiensemester_kurzbz)
				JOIN
					lehre.tbl_studienplan
					USING (studienplan_id)
				JOIN
					lehre.tbl_studienordnung
					USING (studienordnung_id)
				JOIN
					public.tbl_studiengang
					USING (studiengang_kz)
				WHERE
					tbl_studiengang.aktiv = \'t\'
						AND
					tbl_studiensemester.onlinebewerbung = \'t\'
				AND
					tbl_studienplan.onlinebewerbung_studienplan = \'t\'
				AND 
					semester = 1
				AND
					typ = \'b\'

				EXCEPT

				SELECT DISTINCT 
					studienplan_id
				FROM 
					public.tbl_reihungstest 
				JOIN
					public.tbl_rt_studienplan
					USING (reihungstest_id)
				WHERE 
					datum >= now() 
				AND 
					oeffentlich = \'t\'
				)
			';
		
		return $this->execQuery($query);
	}
	
	/**	
	 *  Gets amount of free places.
	 * @return array Returns object array with faculty and amount of free places
	 * for each public actual placement test date.
	 */
	public function getFreePlaces()
	{
		$query = '
			SELECT
				datum,
				fakultaet,
				max_plaetze - anzahl_angemeldet AS freie_plaetze
			FROM
			(
				SELECT
					studiengang_kz,
					oeffentlich,
					tbl_studiengang.bezeichnung,
					reihungstest_id,
					tbl_reihungstest.datum,
					COALESCE
					(
						max_teilnehmer,
						(
							SELECT
								sum(arbeitsplaetze) - ceil(sum(arbeitsplaetze)/100.0*'. REIHUNGSTEST_ARBEITSPLAETZE_SCHWUND. ')
							FROM
								public.tbl_rt_ort  
							JOIN
								public.tbl_ort 
							ON (tbl_rt_ort.ort_kurzbz = tbl_ort.ort_kurzbz)
							WHERE
								tbl_rt_ort.rt_id = tbl_reihungstest.reihungstest_id
						)
					)
					AS max_plaetze,
					(
					SELECT
						count(*)
					FROM
						public.tbl_rt_person
					WHERE
						rt_id = tbl_reihungstest.reihungstest_id
					)
					AS anzahl_angemeldet,
					(
						WITH RECURSIVE meine_oes
						(
							oe_kurzbz,
							oe_parent_kurzbz,
							organisationseinheittyp_kurzbz
						)
						AS
						(
							SELECT
								oe_kurzbz, oe_parent_kurzbz, organisationseinheittyp_kurzbz
							FROM
								public.tbl_organisationseinheit
							WHERE
								oe_kurzbz in
								(
									SELECT
										oe_kurzbz
									FROM
										public.tbl_rt_studienplan
									JOIN 	
										lehre.tbl_studienplan sp USING (studienplan_id)
									JOIN 
										lehre.tbl_studienordnung USING (studienordnung_id)
									JOIN
										public.tbl_studiengang sg USING (studiengang_kz)
										WHERE
										tbl_rt_studienplan.reihungstest_id = tbl_reihungstest.reihungstest_id
								)
							AND 
								aktiv = true
								
							UNION ALL
							
							SELECT
								o.oe_kurzbz, o.oe_parent_kurzbz, o.organisationseinheittyp_kurzbz
							FROM
								public.tbl_organisationseinheit o, meine_oes
							WHERE
								o.oe_kurzbz = meine_oes.oe_parent_kurzbz
							AND 
								aktiv = true
						)
					SELECT
						ARRAY_TO_STRING(ARRAY_AGG(DISTINCT tbl_organisationseinheit.bezeichnung),\', \')
					FROM
						meine_oes
						JOIN public.tbl_organisationseinheit USING(oe_kurzbz)
					WHERE
						meine_oes.organisationseinheittyp_kurzbz=\'Fakultaet\'
				)
				AS fakultaet
			FROM
				public.tbl_reihungstest
			JOIN
				public.tbl_studiengang
				USING (studiengang_kz)
			WHERE 
				tbl_reihungstest.datum >= now()
			AND
				tbl_reihungstest.oeffentlich = \'t\'
			GROUP BY
				tbl_studiengang.bezeichnung,
				oe_kurzbz,
				reihungstest_id
			)
			AS tbl
		ORDER BY
			fakultaet,
			freie_plaetze
		';
		
		return $this->execQuery($query);
	}
	
	/**
	 * Checks if a registration date (Anmeldefrist) of a placement test has been reached yesterday.
	 * @param integer $studiengang_kz Optional. Kennzahl of degree program whose registration date should be checked.
	 * @return array Returns object array with reihungstest_ids whose registration date has been reached yesterday.
	 */
	public function checkReachedRegistrationDate($studiengang_kz = null)
	{
		$query = '
			SELECT *
			FROM PUBLIC.tbl_reihungstest
			WHERE anmeldefrist = (
					SELECT CURRENT_DATE - 1
					)
			';
		
		$parametersArray = array();
		
		if (!isEmptyString($studiengang_kz))
		{
			$query .= ' AND studiengang_kz = ?';
			array_push($parametersArray, $studiengang_kz);
		}

		return $this->execQuery($query, $parametersArray);
	}
	
	/**
	 * Loads all applicants of a placement test for runZentraleReihungstestAnmeldefristAssistenzJob
	 * @param integer $reihungstest_id ID of placement test
	 * @return array Returns object array with data of applicants.
	 */
	public function getApplicantsOfPlacementTestForCronjob($reihungstest_id)
	{
		$query = '
			SELECT tbl_rt_person.person_id,
				ps.prestudent_id,
				tbl_studienplan.orgform_kurzbz,
				tbl_prestudentstatus.studienplan_id,
				tbl_prestudentstatus.ausbildungssemester,
				nachname,
				vorname,
				tbl_zgv.zgv_kurzbz,
				ps.studiengang_kz,
				CASE WHEN tbl_prestudentstatus.statusgrund_id=9
				THEN \'Ja\'
				ELSE \'Nein\'
				END AS "qualifikationskurs",
				(
					SELECT count(*) AS prio_relativ
					FROM (
						SELECT *,
							(
								SELECT status_kurzbz
								FROM PUBLIC.tbl_prestudentstatus
								WHERE prestudent_id = pst.prestudent_id
								ORDER BY datum DESC,
									tbl_prestudentstatus.insertamum DESC LIMIT 1
								) AS laststatus
						FROM PUBLIC.tbl_prestudent pst
						JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
						WHERE person_id = (
								SELECT person_id
								FROM PUBLIC.tbl_prestudent
								WHERE prestudent_id = ps.prestudent_id
								)
							AND studiensemester_kurzbz = (
								SELECT studiensemester_kurzbz
								FROM PUBLIC.tbl_prestudentstatus
								WHERE prestudent_id = ps.prestudent_id
									AND status_kurzbz = \'Interessent\' LIMIT 1
								)
							AND status_kurzbz = \'Interessent\'
						) prest
					WHERE laststatus NOT IN (\'Abbrecher\', \'Abgewiesener\', \'Absolvent\')
						AND priorisierung <= (
							SELECT priorisierung
							FROM PUBLIC.tbl_prestudent
							WHERE prestudent_id = ps.prestudent_id
							)
					) AS "prioritaet",
				(
					SELECT kontakt
					FROM PUBLIC.tbl_kontakt
					WHERE kontakttyp = \'email\'
						AND zustellung = true
						AND person_id = tbl_rt_person.person_id
					ORDER BY insertamum DESC,
						updateamum DESC LIMIT 1
					) AS "email"
			FROM PUBLIC.tbl_rt_person
			JOIN PUBLIC.tbl_person ON (tbl_rt_person.person_id = tbl_person.person_id)
			JOIN PUBLIC.tbl_reihungstest rt ON (rt_id = reihungstest_id)
			JOIN PUBLIC.tbl_prestudent ps ON (ps.person_id = tbl_rt_person.person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			JOIN lehre.tbl_studienplan ON (tbl_prestudentstatus.studienplan_id = tbl_studienplan.studienplan_id)
			LEFT JOIN bis.tbl_zgv ON (ps.zgv_code = tbl_zgv.zgv_code)
			WHERE rt_id = ?
				AND get_rolle_prestudent(prestudent_id, rt.studiensemester_kurzbz) IN (\'Interessent\', \'Bewerber\')
				AND tbl_prestudentstatus.studiensemester_kurzbz = rt.studiensemester_kurzbz
				AND bewerbung_abgeschicktamum IS NOT NULL
				AND bestaetigtam IS NOT NULL
			ORDER BY studiengang_kz, 
				orgform_kurzbz, 
				prioritaet, 
				nachname, 
				vorname, 
				person_id
			';

		return $this->execQuery($query, array($reihungstest_id));
	}

	/**
	 * Checks if an Applicant was assigned to a plament test after Anmeldefrist and before Test-Date
	 * @param integer $studiengang_kz Kennzahl of degree program those tests should be checked
	 * @return array Returns object array with data of applicants.
	 */
	public function getApplicantAssignedAfterDate($studiengang_kz)
	{
		$query = '
			SELECT tbl_rt_person.person_id,
				ps.prestudent_id,
				rt.datum,
				rt.uhrzeit,
				rt.reihungstest_id,
				tbl_studienplan.orgform_kurzbz,
				tbl_prestudentstatus.studienplan_id,
				tbl_prestudentstatus.ausbildungssemester,
				nachname,
				vorname,
				tbl_zgv.zgv_kurzbz,
				ps.studiengang_kz,
				CASE WHEN tbl_prestudentstatus.statusgrund_id=9
				THEN \'Ja\'
				ELSE \'Nein\'
				END AS "qualifikationskurs",
				(
					SELECT count(*) AS prio_relativ
					FROM (
						SELECT *,
							(
								SELECT status_kurzbz
								FROM PUBLIC.tbl_prestudentstatus
								WHERE prestudent_id = pst.prestudent_id
								ORDER BY datum DESC,
									tbl_prestudentstatus.insertamum DESC LIMIT 1
								) AS laststatus
						FROM PUBLIC.tbl_prestudent pst
						JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
						WHERE person_id = (
								SELECT person_id
								FROM PUBLIC.tbl_prestudent
								WHERE prestudent_id = ps.prestudent_id
								)
							AND studiensemester_kurzbz = (
								SELECT studiensemester_kurzbz
								FROM PUBLIC.tbl_prestudentstatus
								WHERE prestudent_id = ps.prestudent_id
									AND status_kurzbz = \'Interessent\' LIMIT 1
								)
							AND status_kurzbz = \'Interessent\'
						) prest
					WHERE laststatus NOT IN (\'Abbrecher\', \'Abgewiesener\', \'Absolvent\')
						AND priorisierung <= (
							SELECT priorisierung
							FROM PUBLIC.tbl_prestudent
							WHERE prestudent_id = ps.prestudent_id
							)
					) AS "prioritaet",
				(
					SELECT kontakt
					FROM PUBLIC.tbl_kontakt
					WHERE kontakttyp = \'email\'
						AND zustellung = true
						AND person_id = tbl_rt_person.person_id
					ORDER BY insertamum DESC,
						updateamum DESC LIMIT 1
					) AS "email"
			FROM PUBLIC.tbl_rt_person
			JOIN PUBLIC.tbl_person ON (tbl_rt_person.person_id = tbl_person.person_id)
			JOIN PUBLIC.tbl_reihungstest rt ON (rt_id = reihungstest_id)
			JOIN PUBLIC.tbl_prestudent ps ON (ps.person_id = tbl_rt_person.person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			JOIN lehre.tbl_studienplan ON (tbl_prestudentstatus.studienplan_id = tbl_studienplan.studienplan_id)
			LEFT JOIN bis.tbl_zgv ON (ps.zgv_code = tbl_zgv.zgv_code)
			WHERE rt.studiengang_kz = ?
				AND get_rolle_prestudent(prestudent_id, rt.studiensemester_kurzbz) IN (\'Interessent\', \'Bewerber\')
				AND tbl_prestudentstatus.studiensemester_kurzbz = rt.studiensemester_kurzbz
				AND bewerbung_abgeschicktamum IS NOT NULL
				AND bestaetigtam IS NOT NULL
				AND anmeldefrist < (SELECT CURRENT_DATE)
				AND rt.datum > (SELECT CURRENT_DATE)
				--AND tbl_rt_person.insertamum > anmeldefrist
				--AND tbl_rt_person.insertamum < rt.datum
				AND tbl_rt_person.insertamum::date = (SELECT CURRENT_DATE -1)
			ORDER BY studiengang_kz,
				orgform_kurzbz,
				prioritaet,
				nachname,
				vorname,
				person_id
			';

		return $this->execQuery($query, array($studiengang_kz));
	}

	/**
 * Loads all applicants of a placement test
 * @param integer $reihungstest_id ID of placement test
 * @return array Returns object array with data of applicants.
 */
	public function getApplicantsOfPlacementTest($reihungstest_id)
	{
		$query = '
			SELECT DISTINCT tbl_rt_person.person_id,
				anrede,
				nachname,
				vorname,
				(
					SELECT kontakt
					FROM PUBLIC.tbl_kontakt
					WHERE kontakttyp = \'email\'
						AND zustellung = true
						AND person_id = tbl_rt_person.person_id
					ORDER BY insertamum DESC,
						updateamum DESC LIMIT 1
					) AS "email",
				tbl_ort.planbezeichnung,
				tbl_ort.lageplan
			FROM PUBLIC.tbl_rt_person
			JOIN PUBLIC.tbl_person ON (tbl_rt_person.person_id = tbl_person.person_id)
			JOIN PUBLIC.tbl_reihungstest rt ON (rt_id = reihungstest_id)
			JOIN PUBLIC.tbl_prestudent ps ON (ps.person_id = tbl_rt_person.person_id)
			JOIN PUBLIC.tbl_prestudentstatus USING (prestudent_id)
			LEFT JOIN bis.tbl_zgv ON (ps.zgv_code = tbl_zgv.zgv_code)
			LEFT JOIN PUBLIC.tbl_ort ON (tbl_rt_person.ort_kurzbz = tbl_ort.ort_kurzbz)
			WHERE rt_id = ?
				AND get_rolle_prestudent(prestudent_id, rt.studiensemester_kurzbz) IN (\'Interessent\', \'Bewerber\')
				AND tbl_prestudentstatus.studiensemester_kurzbz = rt.studiensemester_kurzbz
				AND bewerbung_abgeschicktamum IS NOT NULL
				AND bestaetigtam IS NOT NULL
			ORDER BY nachname,
				vorname,
				person_id
			';

		return $this->execQuery($query, array($reihungstest_id));
	}

	/**
	 * Loads the dates of the next placement tests within $days days to the given degree program
	 * @param integer $studiengang_kz Kennzahl of degree program to load the next placement tests
	 * @param integer $days Number of days in the future to load
	 * @return string Returns dates of the next placement test
	 */
	public function getNextPlacementtests($studiengang_kz, $days)
	{
		$query = '
			SELECT *
			FROM PUBLIC.tbl_reihungstest
			WHERE studiengang_kz = ?
			AND datum > now()
			AND datum <= now() + interval ?
			ORDER BY datum ASC
			';

		return $this->execQuery($query, array($studiengang_kz, $days.' days'));
	}

	/**
	 * Loads all placement tests of the given day and optional degree program
	 * @param string $date Date of the tests to be loaded (YYYY-MM-DD)
	 * @param integer $studiengang_kz Optional. Kennzahl of degree program to load
	 * @return array Returns object array with data of placement tests
	 */
	public function getTestsOnDate($date, $studiengang_kz = null)
	{
		$query = '
			SELECT *
			FROM PUBLIC.tbl_reihungstest
			WHERE datum = ?
				AND studiengang_kz = ?
			';

		return $this->execQuery($query, array($date, $studiengang_kz));
	}
}