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
	public function getRoomDataOnDay($ort_kurzbz,$date){


		$this->addSelect(['*',"CONCAT(UPPER(sp.stg_typ),UPPER(sp.stg_kurzbz),'-',COALESCE(CAST(sp.semester AS varchar),'/'),COALESCE(CAST(sp.verband AS varchar),'/')) as eintrag","lektor","CONCAT(lehrfach,'-',lehrform)"]);
		/* $this->addSelect(["lehre.tbl_stundenplan.*","CONCAT(UPPER(sg.typ),UPPER(sg.kurzbz),'-',lehre.tbl_stundenplan.semester,lehre.tbl_stundenplan.verband) as simml"]);
		$this->addJoin("public.tbl_lehrverband as lv","lv.studiengang_kz=lehre.tbl_stundenplan.studiengang_kz AND lv.gruppe=lehre.tbl_stundenplan.gruppe AND lv.verband=lehre.tbl_stundenplan.verband AND lv.semester=lehre.tbl_stundenplan.semester","LEFT"); 
		$this->addJoin("public.tbl_studiengang as sg","sg.studiengang_kz=lehre.tbl_stundenplan.studiengang_kz","LEFT"); 
		$res = $this->loadWhere(['ort_kurzbz'=>$ort_kurzbz,'datum'=>$date]);
		$res = hasData($res) ? getData($res): null;
		return $res; */
		$this->db->where('ort_kurzbz','EDV_A2.06',true);
		$this->db->where('datum','2024-05-21',true);

		$query = $this->db->get_compiled_select('lehre.vw_stundenplan sp');
		
		return $this->execQuery($query, [$ort_kurzbz, $date]);
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
