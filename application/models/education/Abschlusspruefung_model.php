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

		$this->addSelect('tbl_abschlusspruefung.abschlusspruefung_id, tbl_abschlusspruefung.datum, tbl_abschlusspruefung.pruefungstyp_kurzbz AS studiengangstyp, tbl_abschlusspruefung.abschlussbeurteilung_kurzbz, tbl_abschlusspruefung.uhrzeit AS pruefungsbeginn, tbl_abschlusspruefung.endezeit AS pruefungsende,
								tbl_abschlusspruefung.freigabedatum, tbl_abschlusspruefung_antritt.bezeichnung AS pruefungsantritt_bezeichnung, tbl_abschlusspruefung_antritt.bezeichnung_english AS pruefungsantritt_bezeichnung_english, tbl_abschlusspruefung.protokoll,
								studentpers.vorname AS vorname_student, studentpers.nachname AS nachname_student, studentpers.titelpre AS titelpre_student, studentpers.titelpost AS titelpost_student, studentben.uid AS uid_student, matrikelnr, 
								vorsitzenderben.uid AS uid_vorsitz, vorsitzenderpers.vorname AS vorname_vorsitz, vorsitzenderpers.nachname AS nachname_vorsitz, vorsitzenderpers.titelpre AS titelpre_vorsitz, vorsitzenderpers.titelpost AS titelpost_vorsitz,
								erstprueferpers.vorname AS vorname_erstpruefer, erstprueferpers.nachname AS nachname_erstpruefer, erstprueferpers.titelpre AS titelpre_erstpruefer, erstprueferpers.titelpost AS titelpost_erstpruefer, 
								zweitprueferpers.vorname AS vorname_zweitpruefer, zweitprueferpers.nachname AS nachname_zweitpruefer, zweitprueferpers.titelpre AS titelpre_zweitpruefer, zweitprueferpers.titelpost AS titelpost_zweitpruefer 
								');
		$this->addJoin('lehre.tbl_abschlusspruefung_antritt', 'pruefungsantritt_kurzbz', 'LEFT');
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
				}
				// if no Studienordnung available (e.g. Incomings), use Studiengangname provided by table student
				elseif (!hasData($studienordnung))
				{
					$this->resetQuery();
					
					$this->load->model('crm/Student_model', 'StudentModel');
					$this->addSelect('studiengang_kz, bezeichnung, english');
					$this->addJoin('public.tbl_studiengang', 'studiengang_kz');
					$result = $this->StudentModel->load(array(
						'student_uid' => $student_uid)
					);
					
					if ($result = getData($result)[0])
					{
						$abschlusspruefungdata->studiengang_kz = $result->studiengang_kz;
						$abschlusspruefungdata->studiengangbezeichnung = $result->bezeichnung;
						$abschlusspruefungdata->studiengangbezeichnung_englisch = $result->english;
					}
				}
				
				// get Abschlussarbeit
				if (isset($abschlusspruefungdata->studiengang_kz) &&  !empty($abschlusspruefungdata->studiengang_kz))
				{
					$projekttyp = array('Bachelor','Diplom','Master','Dissertation','Lizenziat','Magister');
					$abschlussarbeit = $this->ProjektarbeitModel->getProjektarbeit($student_uid, $abschlusspruefungdata->studiengang_kz, null, $projekttyp, true);

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

	/**
	 * Gets data of an Abschlusspruefung
	 * @param $student_uid
	 * @return object
	 */
	public function getAbschlusspruefungForPrestudent($student_uid)
	{
		$qry = "
			SELECT
				exam.*, 
				CONCAT(
					person_pruefer1.nachname || ' ',
					person_pruefer1.vorname,
					COALESCE(' ' || person_pruefer1.titelpre)
				) AS person_pruefer1,
				CONCAT(
					person_pruefer2.nachname || ' ',
					person_pruefer2.vorname,
					COALESCE(' ' || person_pruefer2.titelpre)
				) AS person_pruefer2,
				CONCAT(
					person_pruefer3.nachname || ' ',
					person_pruefer3.vorname,
					COALESCE(' ' || person_pruefer3.titelpre)
				) AS person_pruefer3,
				CONCAT(
					person_vorsitzender.nachname || ' ',
					person_vorsitzender.vorname,
					COALESCE(' ' || person_vorsitzender.titelpre)
				) AS person_vorsitzender,
				datum,
				freigabedatum,
				sponsion,
				uhrzeit,
				person_pruefer1.nachname as p1_nachname,
				person_pruefer2.nachname as p2_nachname,
				person_pruefer3.nachname as p3_nachname,
				person_vorsitzender.nachname as vorsitz_nachname,
				beurteilung.bezeichnung as beurteilung_bezeichnung,
				antritt.bezeichnung as antritt_bezeichnung
			FROM
				lehre.tbl_abschlusspruefung exam
				JOIN lehre.tbl_pruefungstyp USING (pruefungstyp_kurzbz)
				LEFT JOIN public.tbl_benutzer ben_vorsitzender ON (ben_vorsitzender.uid = vorsitz)
				LEFT JOIN public.tbl_person person_vorsitzender ON (ben_vorsitzender.person_id = person_vorsitzender.person_id)
				LEFT JOIN public.tbl_person person_pruefer1 ON (person_pruefer1.person_id = pruefer1)
				LEFT JOIN public.tbl_person person_pruefer2 ON (person_pruefer2.person_id = pruefer2)
				LEFT JOIN public.tbl_person person_pruefer3 ON (person_pruefer3.person_id = pruefer3)
				LEFT JOIN lehre.tbl_abschlussbeurteilung beurteilung USING (abschlussbeurteilung_kurzbz)
				LEFT JOIN lehre.tbl_abschlusspruefung_antritt antritt USING (pruefungsantritt_kurzbz)
				WHERE student_uid = ?
				ORDER BY exam.datum DESC	
					";

		return $this->execQuery($qry, array('student_uid' => $student_uid));
	}
}
