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
	public function groupedRoomPlanning($ort_kurzbz,$start_date,$end_date){


		$gruppierteRaumVerplannung= $this->execReadOnlyQuery("
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

		return $gruppierteRaumVerplannung;
	}

	/**
	 * @param string $uid
	 * 
	 * @return stdClass
	 */
	public function loadForUid($uid)
	{
		$this->addSelect('sp.*');
		$this->db->join('public.tbl_benutzergruppe bg', 'sp.gruppe_kurzbz=bg.gruppe_kurzbz AND bg.uid=?', 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss1', 'bg.studiensemester_kurzbz=ss1.studiensemester_kurzbz AND ss1.start<=sp.datum AND ss1.ende>=sp.datum', 'LEFT');
		$this->db->join('public.tbl_studentlehrverband slv', "sp.studiengang_kz=slv.studiengang_kz AND slv.student_uid=? AND (slv.semester=sp.semester OR sp.semester IS NULL) AND (slv.verband=sp.verband OR sp.verband IS NULL OR sp.verband='' OR sp.verband='0') AND (slv.gruppe=sp.gruppe OR sp.gruppe IS NULL OR sp.gruppe='' OR sp.gruppe='0') AND sp.gruppe_kurzbz IS NULL", 'LEFT', false);
		$this->addJoin('public.tbl_studiensemester ss2', 'slv.studiensemester_kurzbz=ss2.studiensemester_kurzbz AND ss2.start<=sp.datum AND ss2.ende>=sp.datum', 'LEFT');
		$this->db->or_where('ss1.studiensemester_kurzbz IS NOT NULL', null, false);
		$this->db->or_where('ss2.studiensemester_kurzbz IS NOT NULL', null, false);
		
		$query = $this->db->get_compiled_select('lehre.vw_stundenplan sp');
		
		return $this->execQuery($query, [$uid, $uid]);
	}

}
