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
