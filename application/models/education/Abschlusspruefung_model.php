<?php
class Abschlusspruefung_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'lehre.tbl_abschlusspruefung';
		$this->pk = 'abschlusspruefung_id';
	}

	/**
	 * Gets data of an Abschlusspruefung, including Abschlussarbeit.
	 * @param $abschlusspruefung_id
	 * @return object
	 */
	public function getAbschlusspruefung($abschlusspruefung_id)
	{
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('crm/Prestudentstatus_model', 'PrestudentstatusModel');
		$this->load->model('education/Projektarbeit_model', 'ProjektarbeitModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');

		$abschlusspruefungdata = array();

		$this->addSelect('tbl_abschlusspruefung.datum, tbl_abschlusspruefung.abschlussbeurteilung_kurzbz,
													studentpers.vorname AS vorname_student, studentpers.nachname AS nachname_student, studentpers.titelpre AS titelpre_student, studentpers.titelpost AS titelpost_student, studentben.uid AS uid_student, matrikelnr, 
													vorsitzenderpers.vorname AS vorname_vorsitz, vorsitzenderpers.nachname AS nachname_vorsitz, vorsitzenderpers.titelpre AS titelpre_vorsitz, vorsitzenderpers.titelpost AS titelpost_vorsitz,
													erstprueferpers.vorname AS vorname_erstpruefer, erstprueferpers.nachname AS nachname_erstpruefer, erstprueferpers.titelpre AS titelpre_erstpruefer, erstprueferpers.titelpost AS titelpost_erstpruefer, 
													zweitprueferpers.vorname AS vorname_zweitpruefer, zweitprueferpers.nachname AS nachname_zweitpruefer, zweitprueferpers.titelpre AS titelpre_zweitpruefer, zweitprueferpers.titelpost AS titelpost_zweitpruefer 
													');
		$this->addJoin('public.tbl_benutzer studentben', 'tbl_abschlusspruefung.student_uid = studentben.uid');
		$this->addJoin('public.tbl_person studentpers', 'studentben.person_id = studentpers.person_id');
		$this->addJoin('public.tbl_student', 'studentben.uid = tbl_student.student_uid');
		$this->addJoin('public.tbl_benutzer vorsitzenderben', 'vorsitz = vorsitzenderben.uid', 'LEFT');
		$this->addJoin('public.tbl_person vorsitzenderpers', 'vorsitzenderben.person_id = vorsitzenderpers.person_id', 'LEFT');
		$this->addJoin('public.tbl_person erstprueferpers', 'pruefer1 = erstprueferpers.person_id', 'LEFT');
		$this->addJoin('public.tbl_person zweitprueferpers', 'pruefer2 = zweitprueferpers.person_id', 'LEFT');
		
		$abschlusspruefung = $this->load($abschlusspruefung_id);
		
		if (isError($abschlusspruefung))
			return $abschlusspruefung;
		elseif (hasData($abschlusspruefung))
		{
			$abschlusspruefungdata = getData($abschlusspruefung)[0];

			// get Studiengang of Student
			$student_uid = $abschlusspruefungdata->uid_student;
			$this->StudentModel->addSelect('prestudent_id');
			$prestudent_id = $this->StudentModel->load(array('student_uid' => $student_uid));
			
			if (isError($prestudent_id))
				return $prestudent_id;
			elseif (hasData($prestudent_id))
			{
				//get Studiengangname from Studienplan and -ordnung
				$studienordnung = $this->PrestudentstatusModel->getStudienordnungFromPrestudent(getData($prestudent_id)[0]->prestudent_id);
				
				if (isError($studienordnung))
					return $studienordnung;
				elseif (hasData($studienordnung))
				{
					$studienordnungdata = getData($studienordnung)[0];

					$abschlusspruefungdata->studiengang_kz = $studienordnungdata->studiengang_kz;
					$abschlusspruefungdata->studiengangbezeichnung = $studienordnungdata->studiengangbezeichnung;
					$abschlusspruefungdata->studiengangbezeichnung_englisch = $studienordnungdata->studiengangbezeichnung_englisch;

					$this->StudiengangModel->addSelect('typ');
					$typ = $this->StudiengangModel->load($studienordnungdata->studiengang_kz);
					if (isError($typ))
						return $typ;
					elseif (hasData($typ))
					{
						$abschlusspruefungdata->studiengangstyp = getData($typ)[0]->typ;
					}

					// get Abschlussarbeit
					$projekttyp = array('Bachelor','Diplom','Master','Dissertation','Lizenziat','Magister');
					$abschlussarbeit = $this->ProjektarbeitModel->getProjektarbeit($student_uid, $studienordnungdata->studiengang_kz, null, $projekttyp, true);

					if (isError($abschlussarbeit))
						return $abschlussarbeit;
					if (hasData($abschlussarbeit))
					{
							$abschlussarbeit = getData($abschlussarbeit)[0];
							$abschlusspruefungdata->projektarbeit_studiengangstyp_name = $abschlussarbeit->projekttyp_kurzbz;
							$abschlusspruefungdata->abschlussarbeit_titel = $abschlussarbeit->titel;
							$abschlusspruefungdata->abschlussarbeit_note = $abschlussarbeit->note;
						}
				}
			}
		}
		
		return success($abschlusspruefungdata);
	}
}
