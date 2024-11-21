<?php
class Stundenplan_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_stundenplan';
		$this->pk = 'stundenplan_id';
	}

	/**
	 * @param string $ort_kurzbz
	 * @param string $date
	 *
	 * @return stdClass
	 */
	public function getRoomDataOnInterval($ort_kurzbz,$start_date,$end_date){



		/*$raum_stundenplan= $this->execReadOnlyQuery("
		-- merging all reservierungs information with the stundenplan information but with different types
		SELECT 'stundenplan_eintrag' as eintrags_type, CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/')) AS stg, CONCAT(lehrfach,'-',lehrform) AS lv_info, ort_kurzbz, studiengang_kz, uid, stunde, datum, titel, semester, verband, gruppe, gruppe_kurzbz, stg_kurzbz, * FROM lehre.vw_stundenplan sp
		WHERE ort_kurzbz = ? AND datum >= ? AND datum <= ?
		UNION ALL
		SELECT 'reservierungs_eintrag' as eintrags_type, NULL, NULL, ort_kurzbz, studiengang_kz, uid, stunde, datum, titel, semester, verband, gruppe, gruppe_kurzbz, stg_kurzbz, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL FROM lehre.vw_reservierung res
		WHERE ort_kurzbz = ? AND datum >= ? AND datum <= ?
		", [$ort_kurzbz, $start_date, $end_date,$ort_kurzbz, $start_date, $end_date]);
		*/


		$raum_stundenplan= $this->execReadOnlyQuery("
		SELECT CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/')) AS stg, CONCAT(lehrfach,'-',lehrform) AS lv_info, * FROM lehre.vw_stundenplan sp
		WHERE ort_kurzbz = ? AND datum >= ? AND datum <= ?
		", [$ort_kurzbz, $start_date, $end_date]);

		return $raum_stundenplan;
	}

	/**
	 * @param string $ort_kurzbz The room to query the planning for
	 * @param string $start_date The start date of the query interval
	 * @param string $end_date The end date of the query interval
	 *
	 * @return stdClass
	 */
	public function groupedCalendarEvents($ort_kurzbz,$start_date,$end_date){


		$gruppierteEvents= $this->execReadOnlyQuery("
		SELECT

			'reservierung' as type,
			NULL as unr,datum, stunde,
			titel AS topic,
			beschreibung as beschreibung,
			string_agg(DISTINCT gruppe, '/') as gruppe,
			string_agg(DISTINCT lektor, '/') as lektor,
			res.ort_kurzbz,res.studiengang_kz,  res.titel, res.beschreibung,NULL as lehreinheit_id,NULL as lehrfach_id,NULL as anmerkung, NULL as fix,NULL as lehrveranstaltung_id,NULL as stg_kurzbzlang,NULL as stg_bezeichnung,NULL as stg_typ, NULL as fachbereich_kurzbz,NULL as lehrfach,NULL as lehrfach_bez,NULL as farbe,NULL as lehrform, NULL as anmerkung_lehreinheit

		FROM

		(
			SELECT
			NULL as unr,datum, stunde,
			CASE
				WHEN gruppe_kurzbz IS NOT NULL THEN gruppe_kurzbz
				ELSE CONCAT(UPPER(studg.typ),UPPER(res.stg_kurzbz),'-',COALESCE(CAST(res.semester AS varchar),'/'),COALESCE(CAST(res.verband AS varchar),'/'))
			END as gruppe,
			CASE
				WHEN mit.kurzbz IS NOT NULL THEN mit.kurzbz
				ELSE uid
			END as lektor,
			res.ort_kurzbz,res.studiengang_kz,  res.titel, res.beschreibung,NULL as lehreinheit_id,NULL as lehrfach_id,NULL as anmerkung, NULL as fix,NULL as lehrveranstaltung_id,NULL as stg_kurzbzlang,NULL as stg_bezeichnung,NULL as stg_typ, NULL as fachbereich_kurzbz,NULL as lehrfach,NULL as lehrfach_bez,NULL as farbe,NULL as lehrform, NULL as anmerkung_lehreinheit
			FROM lehre.vw_reservierung res

			LEFT JOIN public.tbl_mitarbeiter mit ON mit.mitarbeiter_uid=uid
			JOIN public.tbl_studiengang studg ON studg.studiengang_kz=res.studiengang_kz

			WHERE
				res.ort_kurzbz = ?
				AND res.datum >= ?
				AND res.datum <= ?
		) as res

		GROUP BY res.ort_kurzbz,res.studiengang_kz, res.datum, res.stunde, res.titel, res.beschreibung

		UNION ALL

		SELECT

		'stundenplan' as type,
		unr,datum, stunde,
		CONCAT(lehrfach,'-',lehrform) as topic,
		'' as beschreibung,
		string_agg(DISTINCT gruppe, '/') as gruppe,
		string_agg(DISTINCT lektor, '/') as lektor,
		ort_kurzbz, studiengang_kz, titel,'' as beschreibung,lehreinheit_id,lehrfach_id,anmerkung,fix,lehrveranstaltung_id,stg_kurzbzlang,stg_bezeichnung,stg_typ,fachbereich_kurzbz,lehrfach,lehrfach_bez,farbe,lehrform,anmerkung_lehreinheit

		FROM
		(
			SELECT
 			unr,datum, stunde,
			CASE
				WHEN gruppe_kurzbz IS NOT NULL THEN gruppe_kurzbz
				ELSE CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/'))
			END as gruppe,
			CASE
				WHEN sp.mitarbeiter_kurzbz IS NOT NULL THEN sp.mitarbeiter_kurzbz
				ELSE lektor
			END as lektor,
			ort_kurzbz, studiengang_kz, titel,'' as beschreibung,lehreinheit_id,lehrfach_id,anmerkung,fix,lehrveranstaltung_id,stg_kurzbzlang,stg_bezeichnung,stg_typ,fachbereich_kurzbz,lehrfach,lehrfach_bez,farbe,lehrform,anmerkung_lehreinheit

			FROM lehre.vw_stundenplan sp

			WHERE ort_kurzbz = ?
			AND datum >= ?
			AND datum <= ?

		) as sp

		GROUP BY

			ort_kurzbz,unr, datum, stunde, lehreinheit_id, lehrfach_id,studiengang_kz,titel,anmerkung,fix,lehrveranstaltung_id,stg_kurzbzlang,stg_bezeichnung,stg_typ,fachbereich_kurzbz,lehrfach,lehrfach_bez,farbe,lehrform,anmerkung_lehreinheit

		ORDER BY datum, stunde
		", [$ort_kurzbz, $start_date, $end_date, $ort_kurzbz, $start_date, $end_date]);

		return $gruppierteEvents;
	}


	/**
	 * function that takes a query that fetches lehre.vw_stundenplan rows and groups them so that they can be displayed in a calendar
	 * @param string $stundenplanViewQuery the subquery used to group the result
	 *
	 * @return stdClass
	 */
	public function stundenplanGruppierung($stundenplanViewQuery)
	{
		$query_result = $this->execReadOnlyQuery("
		SELECT
		'lehreinheit' as type, beginn, ende, datum,
		CONCAT(lehrfach,'-',lehrform) as topic,
		array_agg(DISTINCT lektor) as lektor,
		array_agg(DISTINCT (gruppe,verband,semester,studiengang_kz,gruppen_kuerzel)) as gruppe,
		string_agg(DISTINCT ort_kurzbz, '/') as ort_kurzbz,
		array_agg(DISTINCT lehreinheit_id) as lehreinheit_id,

		titel, lehrfach, lehrform, lehrfach_bez, organisationseinheit, farbe, lehrveranstaltung_id

		FROM
		(
			SELECT unr,datum,beginn, ende,
			CASE
				WHEN sp.mitarbeiter_kurzbz IS NOT NULL THEN sp.mitarbeiter_kurzbz
				ELSE lektor
			END as lektor,
			CASE
				WHEN gruppe_kurzbz IS NOT NULL THEN gruppe_kurzbz
				ELSE CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/'))
			END as gruppen_kuerzel,
			(SELECT bezeichnung
			FROM public.tbl_organisationseinheit
			WHERE oe_kurzbz IN(
				SELECT oe_kurzbz
				FROM lehre.tbl_lehrveranstaltung
				WHERE lehrveranstaltung_id = sp.lehrveranstaltung_id
			)) as organisationseinheit,
			ort_kurzbz, studiengang_kz, titel,lehreinheit_id,lehrfach_id,anmerkung,fix,lehrveranstaltung_id,stg_kurzbzlang,stg_bezeichnung,stg_typ,fachbereich_kurzbz,lehrfach,lehrfach_bez,farbe,lehrform,anmerkung_lehreinheit,gruppe, verband, semester,stg_kurzbz

			FROM (".$stundenplanViewQuery.") sp
			JOIN lehre.tbl_stunde ON lehre.tbl_stunde.stunde = sp.stunde

		) as subquery

		GROUP BY unr, datum, beginn, ende, ort_kurzbz, titel, lehrform, lehrfach, lehrfach_bez, organisationseinheit, farbe, lehrveranstaltung_id

		ORDER BY datum, beginn
		");

		return $query_result;
	}

	/**
	 * NO STANDALONE FUNCTION - Generates a SQL query string to fetch 'stundenplan' events for a specific student within the current semester.
	 * @param string $uid the user id that is used to fetch the stundenplan rows from the lehre.vw_stundenplan table
	 *
	 * @return string
	 */
	public function getStundenplanQuery($start_date, $end_date,$semester,$gruppen,$studentlehrverbaende){
		
		// helper function to check if either $gruppen or $studentlehrverbaende are empty for each semester
		$emptyCheck = function($toBeCheckedArray) use ($semester){
			$result = true;
			$sem = array_keys($semester);
			foreach($sem as $s){
				if(count($toBeCheckedArray[$s]) > 0){
					$result = false;
					break;
				}
			}
			return $result;
		};
		
		$query =
		"select sp.*
		from lehre.vw_stundenplan sp
		WHERE
		sp.datum >= ".$this->escape($start_date)."
		AND sp.datum <= ".$this->escape($end_date);
		
		// adds the AND sql chain only if both $gruppen and $studentlehrverbaende are not empty
		if(!$emptyCheck($gruppen) || !$emptyCheck($studentlehrverbaende))
		{
			$query .= " AND ( ";
		} 
		
		foreach($semester as $sem => $semester_date_range)
		{
			
			foreach($semester_date_range as $sem_date => $sem_date_range)
			{
				// if there are not groups for the semester skip the iteration step
				if(!array_key_exists($sem_date,$gruppen) || count($gruppen[$sem_date]) == 0)
				{
					continue;
				}
				// converts the array of gruppen strings into a sql IN (_,_,_) chain
				$query .="(sp.gruppe_kurzbz IN (" .implode(',',$gruppen[$sem_date]).") AND sp.datum BETWEEN ".$this->escape($sem_date_range->start)." AND ".$this->escape($sem_date_range->ende)." )";
				
				// adds the OR sql chain only if the $studentlehrverbaende array is not empty 
				// DOES not include the sql OR if the $studentlehrverbaende are empty and it is the last gruppen element in the iteration 
				if(key($semester) != $sem || !$emptyCheck($studentlehrverbaende))
				{
					$query .="OR";
				}
				
			}
		} 
		
		foreach($semester as $sem=>$semester_date_range)
		{
			foreach($semester_date_range as $sem_date => $sem_date_range)
			{
				if(!array_key_exists($sem_date,$studentlehrverbaende) || count($studentlehrverbaende[$sem_date]) == 0)
				{
					continue;
				}
				foreach($studentlehrverbaende[$sem_date] as $key=>$lehrverband)
				{
					// adds the OR sql chain only if its not the first element in the first semester of the $studentlehrverbaende array
					if($sem != array_keys($semester)[0] || $key != array_keys($semester)[0])
					{
						$query .="OR";
					}
					$query .= "((sp.studiengang_kz = ".$this->escape($lehrverband->studiengang_kz)." AND sp.semester = ".$this->escape($lehrverband->semester)." AND sp.verband = ".$this->escape($lehrverband->verband)." AND sp.gruppe = ".$this->escape($lehrverband->gruppe)." AND sp.datum BETWEEN ".$this->escape($sem_date_range->start)." AND ".$this->escape($sem_date_range->ende).")";
					// Eintraege fuer den ganzen Verband
					$query .= "OR (sp.studiengang_kz = ".$this->escape($lehrverband->studiengang_kz)." AND sp.semester = ".$this->escape($lehrverband->semester)." AND sp.verband = ".$this->escape($lehrverband->verband)." AND (sp.gruppe is null OR sp.gruppe='') AND sp.datum BETWEEN ".$this->escape($sem_date_range->start)." AND ".$this->escape($sem_date_range->ende).")";
					// Eintraege fuer das ganze Semester
					$query .= "OR (sp.studiengang_kz = ".$this->escape($lehrverband->studiengang_kz)." AND sp.semester = ".$this->escape($lehrverband->semester)." AND (sp.verband is null OR sp.verband='') AND sp.datum BETWEEN ".$this->escape($sem_date_range->start)." AND ".$this->escape($sem_date_range->ende).") AND gruppe_kurzbz is null)";
				}
			}	
		}

		// closes the AND sql chain only if it was opened previously
		if(!$emptyCheck($gruppen) || !$emptyCheck($studentlehrverbaende))
		{
			$query .= ")";
		} 

		return $query;
	}

	/**
	 * NO STANDALONE FUNCTION - Generates a SQL query string to fetch 'stundenplan' events for a specific room within a date range.
	 * @param string $ort_kurzbz the ort from which we want to query the stundenplan events
	 * @param string $start_date (inclusive) the minimum date that an event should have to be fetched
	 * @param string $end_date (inclusive) the maximum date that an event should not extend to be fetched
	 *
	 * @return string
	 */
	public function getRoomQuery($ort_kurzbz, $start_date, $end_date)
	{
		return
			"select sp.*
			FROM lehre.vw_stundenplan sp
			WHERE ort_kurzbz = ".$this->escape($ort_kurzbz)."
			AND datum >= ".$this->escape($start_date)."
			AND datum <= ".$this->escape($end_date);
	}

	/**
	 * @param string $uid
	 *
	 * @return stdClass
	 */
	public function loadForUid($uid)
	{
		$this->addSelect(['sp.*','le.studiensemester_kurzbz']);
		$this->db->join('public.tbl_benutzergruppe bg', 'sp.gruppe_kurzbz=bg.gruppe_kurzbz AND bg.uid=?', 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss1', 'bg.studiensemester_kurzbz=ss1.studiensemester_kurzbz AND ss1.start<=sp.datum AND ss1.ende>=sp.datum', 'LEFT');
		$this->db->join('public.tbl_studentlehrverband slv', "sp.studiengang_kz=slv.studiengang_kz AND slv.student_uid=? AND (slv.semester=sp.semester OR sp.semester IS NULL) AND (slv.verband=sp.verband OR sp.verband IS NULL OR sp.verband='' OR sp.verband='0') AND (slv.gruppe=sp.gruppe OR sp.gruppe IS NULL OR sp.gruppe='' OR sp.gruppe='0') AND sp.gruppe_kurzbz IS NULL", 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss2', 'slv.studiensemester_kurzbz=ss2.studiensemester_kurzbz AND ss2.start<=sp.datum AND ss2.ende>=sp.datum', 'LEFT');
		$this->db->join('lehre.tbl_lehreinheit le', 'le.lehreinheit_id=sp.lehreinheit_id', 'LEFT');
		$this->db->or_where('ss1.studiensemester_kurzbz IS NOT NULL', null, false);
		$this->db->or_where('ss2.studiensemester_kurzbz IS NOT NULL', null, false);

		$query = $this->db->get_compiled_select('lehre.vw_stundenplan sp');

		return $this->execQuery($query, [$uid, $uid]);
	}

}
