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
	public function getRoomReservierungen($ort_kurzbz, $start_date, $end_date)
	{

		$raum_reservierungen= $this->execReadOnlyQuery("
		SELECT ort_kurzbz, studiengang_kz, array_agg(uid) as lektor, stunde, datum, titel, beschreibung, gruppe, gruppe_kurzbz, stg_kurzbz, stg 
		FROM 
		(
			SELECT res.* , 
			CASE
				WHEN res.verband IS NOT NULL OR res.semester IS NOT NULL THEN
					CONCAT(UPPER(studg.typ),UPPER(studg.kurzbz),'-',res.verband,res.semester) 
				ELSE
					CONCAT(UPPER(studg.typ),UPPER(studg.kurzbz)) 
			END AS stg
			FROM lehre.vw_reservierung res
			JOIN public.tbl_studiengang studg ON studg.studiengang_kz=res.studiengang_kz
			WHERE res.ort_kurzbz = ? AND datum >= ? AND datum <= ?
		) AS reservierungen
		GROUP BY ort_kurzbz, studiengang_kz, stunde, datum, titel, beschreibung, gruppe, stg, gruppe_kurzbz, stg_kurzbz
		", [$ort_kurzbz, $start_date, $end_date]);

		if(isError($raum_reservierungen)){
			show_error(getError($raum_reservierungen));
		}

		$raum_reservierungen = getData($raum_reservierungen) ?? [];
		
		$this->load->model("ressrouce/Mitarbeiter_model","MitarbeiterModel");
		foreach($raum_reservierungen as $reservierung){
			$lektoren_array = array();
			foreach($reservierung->lektor as $lektor){
				$this->MitarbeiterModel->addLimit(1);
				$lektor_obj= $this->MitarbeiterModel->load($lektor);
				if(isError($lektor_obj)){
					show_error(getError($lektor_obj));
				}
				$lektor_obj = current(getData($lektor_obj));
				$lektoren_array[] = $lektor_obj;
			}

			$reservierung->lektor = $lektoren_array;
			
		}
		return success($raum_reservierungen);
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
