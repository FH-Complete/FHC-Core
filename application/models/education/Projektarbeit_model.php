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

		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
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

	/**
	 * Gets all Projektarbeiten not uploaded in Time
	 * with typ endupload and marked as fixtermin
	 * @param String $startDate project works before this date will be ignored (in Dateformat: 'Y'-'m'-'d')
	 * @return array of objects
	 */
	public function getAllProjektarbeitenNotUploadedInTime($startDate = null)
	{
		$allowedProjekttypes = ['Bachelor', 'Diplom'];
		$now = new DateTime();

		$this->db->distinct();
		$this->addSelect($this->dbTable. '.projektarbeit_id');
		$this->addSelect($this->dbTable. '.titel');
		$this->addSelect($this->dbTable. '.student_uid');
		$this->addSelect($this->dbTable. '.note');
		$this->addSelect('p'. '.person_id');
		$this->addSelect('p'. '.nachname');
		$this->addSelect('p'. '.vorname');
		$this->addSelect('le'. '.lehreinheit_id');
		$this->addSelect('sg'. '.studiengang_kz');

		$this->addJoin('campus.tbl_paabgabe pa', 'projektarbeit_id');
		$this->addJoin('lehre.tbl_projektbetreuer pb', 'projektarbeit_id');
		$this->addJoin('public.tbl_benutzer ben', 'ben.uid = tbl_projektarbeit.student_uid');
		$this->addJoin('public.tbl_person p', 'p.person_id = ben.person_id', 'LEFT');
		$this->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id', 'LEFT');
		$this->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id', 'LEFT');
		$this->addJoin('public.tbl_studiengang sg', 'studiengang_kz', 'LEFT');

		$this->db->where_in($this->dbTable. '.projekttyp_kurzbz', $allowedProjekttypes);

		if($startDate)
			$this->db->where('pa.datum >', $startDate);

		$this->db->where($this->dbTable. '.note', null);

		//TODO(Manu) remove comments for testdata sg.studiengang_kz
		$result =  $this->loadWhere([
			'ben.aktiv' => 'true',
			'pa.fixtermin' => 'true',
			'pa.paabgabetyp_kurzbz' => 'end',
			'pa.abgabedatum' => null,
			'pa.datum < ' => $now->format('c'),
/*			'sg.studiengang_kz' => 255*/
		]);

		//Testausgaben
/*		if ($result)
		{
			echo "(UPDATE lehre.tbl_projektarbeit pa
					SET note = NULL
					WHERE projektarbeit_id in(";

			$resultIds = getData($result);
			if($resultIds){
				foreach ($resultIds as $item)
				{
					echo $item->projektarbeit_id . ',';
				}

				echo ")\n";
			}
		}*/

		return $result;
	}

	/**
	 * Gets all Projektarbeiten that have been negative
	 * for student_uids with no open (null) or projektarbeiten with positive marks
	 * @param String $startDate project works before this date will be ignored (in Dateformat: 'Y'-'m'-'d')
	 * @return array of objects
	 */
	public function getAllProjektarbeitenNegative($startDate = null)
	{
		$allowedProjekttypes = ['Bachelor', 'Diplom'];
		$now = new DateTime();

		$this->db->distinct();
		$this->addSelect($this->dbTable. '.projektarbeit_id');
		$this->addSelect($this->dbTable. '.titel');
		$this->addSelect($this->dbTable. '.student_uid');
		$this->addSelect($this->dbTable. '.note');
		$this->addSelect('p'. '.person_id');
		$this->addSelect('p'. '.nachname');
		$this->addSelect('p'. '.vorname');
		$this->addSelect('le'. '.lehreinheit_id');
		$this->addSelect('sg'. '.studiengang_kz');
		$this->addJoin('campus.tbl_paabgabe pa', 'projektarbeit_id');
		$this->addJoin('lehre.tbl_projektbetreuer pb', 'projektarbeit_id');
		$this->addJoin('public.tbl_benutzer ben', 'ben.uid = tbl_projektarbeit.student_uid');
		$this->addJoin('public.tbl_person p', 'p.person_id = ben.person_id', 'LEFT');
		$this->addJoin('lehre.tbl_lehreinheit le', 'lehreinheit_id', 'LEFT');
		$this->addJoin('lehre.tbl_lehrveranstaltung lv', 'lehrveranstaltung_id', 'LEFT');
		$this->addJoin('public.tbl_studiengang sg', 'studiengang_kz', 'LEFT');

		$this->db->where_in($this->dbTable. '.projekttyp_kurzbz', $allowedProjekttypes);

		if($startDate)
			$this->db->where('pa.datum >', $startDate);

		$this->db->where($this->dbTable. '.note', 5);
		$this->db->where('NOT EXISTS (SELECT 1 FROM lehre.tbl_projektarbeit AS sub WHERE sub.student_uid = lehre.tbl_projektarbeit.student_uid AND sub.note IS null)', null, false);
		$this->db->where('NOT EXISTS (SELECT 1 FROM lehre.tbl_projektarbeit AS sub WHERE sub.student_uid = lehre.tbl_projektarbeit.student_uid AND sub.note IN (1, 2, 3, 4))', null, false);

		$this->db->where('ben.aktiv', true);
		//TODO(Manu) 	remove comments for Testdata		'sg.studiengang_kz' => 331
		$result =  $this->loadWhere([
			'pa.fixtermin' => 'true',
			'pa.paabgabetyp_kurzbz' => 'end',
			'pa.datum < ' => $now->format('c'),
/*			'sg.studiengang_kz' => 255*/
		]);

		return $result;
	}

	/**
	 * Gets the count of Projektarbeiten
	 * @param String student_uid student_uid to check
	 * @return true: maximum of allowed projektarbeiten reached
	 * $return false: maximum of allowed projektarbeiten not reached
	 */
	public function checkifCountMaxProjektarbeiten($student_uid, $end_of_copy_bachelor, $end_of_copy_master)
	{
		$qry = "SELECT COUNT(*), projekttyp_kurzbz
   				FROM lehre.tbl_projektarbeit
    			WHERE student_uid = ?
    			GROUP BY projekttyp_kurzbz";

		$params = array($student_uid);

		$result =  $this->execQuery($qry, $params);

		//TODO(Manu) wait for final logic or adaptions quality gates
		//meanwhile copying of all enduploads with fixed entries
		//counting after each step: example for testdata with student_uid where 1 project is copied before hitting limit
		//	if($student_uid == "mr21m015")

		if (!empty($result->retval))
		{
			foreach ($result->retval as $row)
			{
				$count = $row->count;
				$projekttyp = $row->projekttyp_kurzbz;

				if ($projekttyp === 'Bachelor' && $count > $end_of_copy_bachelor)
				{
					//TODO(Manu) remove comments testdata
					//print_r(PHP_EOL . 'LIMIT REACHED Bakk: ' . $student_uid .' Anzahl Abgaben ' . $count . PHP_EOL);
					return true;
				}

				if ($projekttyp === 'Diplom' && $count > $end_of_copy_master)
				{
					//TODO(Manu) remove comments testdata
					//print_r(PHP_EOL . 'LIMIT REACHED Dipl: ' . $student_uid .' Anzahl Abgaben ' . $count .  PHP_EOL);
					return true;
				}
			}
		}
		return false;
	}
}
