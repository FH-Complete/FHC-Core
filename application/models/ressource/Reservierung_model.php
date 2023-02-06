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
