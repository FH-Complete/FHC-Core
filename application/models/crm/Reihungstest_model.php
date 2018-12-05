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
	
	/**	Gets amount of free places.
	 *  Retrieves faculty and amount of free places for each public placement test date.
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
}