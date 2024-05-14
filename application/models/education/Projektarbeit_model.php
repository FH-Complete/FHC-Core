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
}
