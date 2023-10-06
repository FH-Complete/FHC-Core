<?php
class Projektarbeit_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_projektarbeit';
		$this->pk = 'projektarbeit_id';
	}

	/**
	 * Gets Projektarbeit(en) of a student for a Studiengang, Semester, Projekttyp, final.
	 * @param $student_uid
	 * @param $studiengang_kz
	 * @param $studiensemester_kurzbz
	 * @param $projekttyp
	 * @param $final
	 * @return object
	 */
	public function getProjektarbeit($student_uid, $studiengang_kz = null, $studiensemester_kurzbz = null, $projekttyp = null, $final = null)
	{
		$qry = "SELECT
					tbl_projektarbeit.* , tbl_projekttyp.bezeichnung
				FROM
					lehre.tbl_projektarbeit
				JOIN
					lehre.tbl_projekttyp USING (projekttyp_kurzbz), lehre.tbl_lehreinheit, lehre.tbl_lehrveranstaltung

				WHERE
					tbl_projektarbeit.lehreinheit_id=tbl_lehreinheit.lehreinheit_id AND
					tbl_lehreinheit.lehrveranstaltung_id = tbl_lehrveranstaltung.lehrveranstaltung_id AND
					tbl_projektarbeit.student_uid = ?";

		$params = array($student_uid);

		if (isset($studiengang_kz))
		{
			$qry .= ' AND tbl_lehrveranstaltung.studiengang_kz=?';
			$params[] = $studiengang_kz;
		}

		if (isset($studiensemester_kurzbz))
		{
			$qry .= ' AND tbl_lehreinheit.studiensemester_kurzbz=?';
			$params[] = $studiensemester_kurzbz;
		}

		if (isset($projekttyp))
		{
			if (is_array($projekttyp))
				$qry .= ' AND tbl_projektarbeit.projekttyp_kurzbz IN ?';
			else
				$qry .= ' AND tbl_projektarbeit.projekttyp_kurzbz=?';

			$params[] = $projekttyp;
		}

		if (isset($final))
		{
			$qry .= ' AND tbl_projektarbeit.final=?';
			$params[] = $final;
		}

		$qry .= ' ORDER BY beginn DESC, projektarbeit_id DESC';

		return $this->execQuery($qry, $params);
	}

	public function getMissedProjektarbeiten()
	{
		//TODO(MANU) check values
		$allowedProjekttypes = array();
		$allowedProjekttypes[] = 'Bachelor';
		$allowedProjekttypes[] = 'Diplom';

		$now = new DateTime();


		//$this->addSelect($this->dbTable.'.*');
		$this->addSelect($this->dbTable.'.projektarbeit_id');
		$this->addSelect($this->dbTable.'.titel');
		$this->addSelect($this->dbTable.'.student_uid');
		$this->addSelect($this->dbTable.'.note');
		$this->addSelect($this->dbTable.'.seitenanzahl');
		$this->addSelect('p' . '.person_id');
		$this->addSelect('le' . '.lehreinheit_id');
		$this->addSelect('sg' . '.studiengang_kz');

		$this->addJoin('campus.tbl_paabgabe pa','projektarbeit_id');
		$this->addJoin('public.tbl_benutzer ben','ben.uid = tbl_projektarbeit.student_uid');
		$this->addJoin('public.tbl_person p', 'person_id', 'LEFT');
		$this->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id', 'LEFT');
		$this->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id', 'LEFT');
		$this->addJoin('public.tbl_studiengang sg', 'studiengang_kz', 'LEFT');

		$this->db->where('pa.paabgabetyp_kurzbz', 'end');
		$this->db->where_in($this->dbTable. '.projekttyp_kurzbz', $allowedProjekttypes);

		//TODO(MANU) Config date
		$this->db->where('pa.datum >', '2022-09-01');
		//$this->db->where('pa.fixtermin', 'TRUE');
		$this->db->where('pa.abgabedatum', NULL);
		//TODO(MANU) get date with NOW
		$this->db->where('pa.datum < ', '2023-10-04');

		$this->addLimit(10);

		return $this->loadWhere([
			'pa.fixtermin' => 'TRUE',
			'sg.studiengang_kz' => 328
		]);

	}
}
