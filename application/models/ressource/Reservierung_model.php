<?php
class Reservierung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'campus.tbl_reservierung';
		$this->pk = 'reservierung_id';
	}


	/**
	 * @param $uid
	 * 
	 * @return stdClass
	 */
	public function getReservierungen($start_date, $end_date, $ort_kurzbz = null)
	{
		
		$stundenplan_reservierungen_query="SELECT r.* , stund.beginn, stund.ende,
			CASE
				WHEN r.gruppe_kurzbz IS NOT NULL THEN r.gruppe_kurzbz 
				ELSE CONCAT(UPPER(studg.typ),UPPER(studg.kurzbz),'-',COALESCE(CAST(r.semester AS varchar),'/'),COALESCE(CAST(r.verband AS varchar),'/')) 
			END as gruppen_kuerzel
			FROM campus.vw_reservierung r
			JOIN public.tbl_studiengang studg ON studg.studiengang_kz=r.studiengang_kz
			JOIN lehre.tbl_stunde stund ON stund.stunde = r.stunde
			LEFT JOIN public.tbl_benutzergruppe bg ON r.gruppe_kurzbz=bg.gruppe_kurzbz AND bg.uid=?
			LEFT JOIN public.tbl_studiensemester ss1 ON bg.studiensemester_kurzbz=ss1.studiensemester_kurzbz AND ss1.start <= r.datum AND ss1.ende >= r.datum
			LEFT JOIN public.tbl_studentlehrverband slv ON r.studiengang_kz=slv.studiengang_kz AND slv.student_uid=? AND (slv.semester=r.semester OR r.semester IS NULL) AND (slv.verband=r.verband OR r.verband IS NULL OR r.verband='' OR r.verband='0') AND (slv.gruppe=r.gruppe OR r.gruppe IS NULL OR r.gruppe ='' OR r.gruppe ='0') AND r.gruppe_kurzbz IS NULL 
			LEFT JOIN public.tbl_studiensemester ss2 ON slv.studiensemester_kurzbz = ss2.studiensemester_kurzbz AND ss2.start <=r.datum AND ss2.ende >= r.datum 
			WHERE datum >= ? AND datum <= ? AND (ss1.studiensemester_kurzbz IS NOT NULL
			OR ss2.studiensemester_kurzbz IS NOT NULL)";
		
		$raum_reservierungen_query = "SELECT res.*, beginn, ende,
			CASE
				WHEN res.gruppe_kurzbz IS NOT NULL THEN res.gruppe_kurzbz 
				ELSE CONCAT(UPPER(studg.typ),UPPER(studg.kurzbz),'-',COALESCE(CAST(res.semester AS varchar),'/'),COALESCE(CAST(res.verband AS varchar),'/')) 
			END as gruppen_kuerzel
			FROM campus.vw_reservierung res
			JOIN public.tbl_studiengang studg ON studg.studiengang_kz=res.studiengang_kz
			JOIN lehre.tbl_stunde ON lehre.tbl_stunde.stunde = res.stunde
			WHERE res.ort_kurzbz = ? AND datum >= ? AND datum <= ?";

		$subquery = is_null($ort_kurzbz)? $stundenplan_reservierungen_query:$raum_reservierungen_query;
		
		$query_result= $this->execReadOnlyQuery("
		SELECT 
		'reservierung' as type, beginn, ende, datum,
		COALESCE(titel, beschreibung) as topic,
		array_agg(DISTINCT mitarbeiter_kurzbz) as lektor,
		array_agg(DISTINCT (gruppe,verband,semester,studiengang_kz,gruppen_kuerzel)) as gruppe, 
		
		ort_kurzbz, 'FFFFFF' as farbe
		
		FROM 
		(
			". $subquery ."
		) AS subquery

		GROUP BY datum, beginn, ende, ort_kurzbz, titel, beschreibung
		
		ORDER BY datum, beginn
		", is_null($ort_kurzbz) ?[getAuthUID(), getAuthUID(),$start_date,$end_date]: [$ort_kurzbz, $start_date, $end_date]);

		
		return $query_result;
	}

	/**
	 * @param $uid
	 * 
	 * @return stdClass
	 */
	public function loadForUid($uid)
	{
		$this->addSelect('r.*');
		$this->db->join('public.tbl_benutzergruppe bg', 'r.gruppe_kurzbz=bg.gruppe_kurzbz AND bg.uid=?', 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss1', 'bg.studiensemester_kurzbz=ss1.studiensemester_kurzbz AND ss1.start<=r.datum AND ss1.ende>=r.datum', 'LEFT');
		$this->db->join('public.tbl_studentlehrverband slv', "r.studiengang_kz=slv.studiengang_kz AND slv.student_uid=? AND (slv.semester=r.semester OR r.semester IS NULL) AND (slv.verband=r.verband OR r.verband IS NULL OR r.verband='' OR r.verband='0') AND (slv.gruppe=r.gruppe OR r.gruppe IS NULL OR r.gruppe='' OR r.gruppe='0') AND r.gruppe_kurzbz IS NULL", 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss2', 'slv.studiensemester_kurzbz=ss2.studiensemester_kurzbz AND ss2.start<=r.datum AND ss2.ende>=r.datum', 'LEFT');
		$this->db->or_where('ss1.studiensemester_kurzbz IS NOT NULL', null, false);
		$this->db->or_where('ss2.studiensemester_kurzbz IS NOT NULL', null, false);
		
		$query = $this->db->get_compiled_select('campus.vw_reservierung r');
		
		return $this->execQuery($query, [$uid, $uid]);
	}

}
