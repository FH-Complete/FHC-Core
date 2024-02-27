<?php
class Student_model extends DB_Model
{

	/**
	 * Constructor
	 */
	public function __construct()
	{
		parent::__construct();
		$this->dbTable = 'public.tbl_student';
		$this->pk = array('student_uid');
		$this->hasSequence = false;
	}

	// ****
	// * Generiert die Matrikelnummer
	// * FORMAT: 0710254001
	// * 07 = Jahr
	// * 1/2/0  = WS/SS/incoming
	// * 0254 = Studiengangskennzahl vierstellig
	// * 001 = Laufende Nummer
	// ****
	public function generateMatrikelnummer($studiengang_kz, $studiensemester_kurzbz)
	{
		$jahr = mb_substr($studiensemester_kurzbz, 4);
		$sem = mb_substr($studiensemester_kurzbz, 0, 2);
		if ($sem == 'SS')
			$jahr = $jahr - 1;
		$art = 0;

		$matrikelnummer = sprintf("%02d", $jahr).$art.sprintf("%04d", $studiengang_kz);

		$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE ? ORDER BY matrikelnr DESC LIMIT 1";

		$matrikelnrres = $this->execQuery($qry, array($matrikelnummer.'%'));

		if (hasData($matrikelnrres))
		{
			$max = mb_substr($matrikelnrres->retval[0]->matrikelnr, 7);
		}
		else
			$max = 0;

		$max += 1;
		return $matrikelnummer.sprintf("%03d", $max);
	}

	/**
	 * Get students UID by PrestudentID.
	 * @param $prestudent_id
	 * @return mixed
	 */
	public function getUID($prestudent_id)
	{
		$this->addSelect('student_uid');

		$result = $this->loadWhere(
			array('prestudent_id' => $prestudent_id)
		);

		if (!hasData($result))
		{
			show_error('Failed getting UID by prestudent_id');
		}

		return $result->retval[0]->student_uid;
	}

	/**
	 * Get students UID by PrestudentID.
	 * @param $prestudent_id
	 * @return mixed
	 */
	public function checkIfUID($prestudent_id)
	{
		$this->addSelect('student_uid');

		$result = $this->loadWhere(
			array('prestudent_id' => $prestudent_id)
		);

		if(isError($result))
		{
			return error("0", "Error while checking student_uid");
		}

		if (!hasData($result))
		{
			return success("0","Keine Student_uid vorhanden");
		}

		$student_uid = $result->retval[0]->student_uid;

		return success ($student_uid);

	}

	public function searchStudent($filter)
	{
		$this->addSelect('vorname, nachname, gebdatum, person.person_id, student_uid');
		$this->addJoin('public.tbl_prestudent ps', 'prestudent_id');
		$this->addJoin('public.tbl_person person', 'person_id');

		$result = $this->loadWhere(
			"lower(student_uid) like ".$this->db->escape('%'.$filter.'%')."
			OR lower(person.nachname) like ".$this->db->escape('%'.$filter.'%')."
			OR lower(person.vorname) like ".$this->db->escape('%'.$filter.'%')."
			OR lower(person.nachname || ' ' || person.vorname) like ".$this->db->escape('%'.$filter.'%')."
			OR lower(person.vorname || ' ' || person.nachname) like ".$this->db->escape('%'.$filter.'%'));

		return $result;
	}

	/**
	 * Get the FH-Email for a student (not the private kontakt emailt)
	 * @param $student_uid
	 * @return string
	 */
	public function getEmailFH($student_uid)
	{
		return $student_uid . '@' . DOMAIN;
	}

	/**
	 * Check if StudentenRolle already exists
	 * @param integer $prestudent_id
	 * @return 0 if not exists, count(rollen) if it does
	 * Copy from studentDBDML.php
	 */
	public function checkIfExistingStudentRolle($prestudent_id)
	{
		$qry = "SELECT 
    				count(*) as anzahl 
				FROM 
				    public.tbl_student 
				WHERE 
				    prestudent_id = ? ";

		$result = $this->execQuery($qry, array($prestudent_id));

		if (isError($result))
		{
			return error($result);
		}
		else {
			$resultObject = current(getData($result));

			if (property_exists($resultObject, 'anzahl')) {
				$resultValue = (int) $resultObject->anzahl;

				if ($resultValue > 0)
				{
					return success($resultValue, $resultValue . " vorhandene Rollen");
				}
				else
				{
					return success("0", "Ein Studentenstatus kann hier nur hinzugefuegt werden wenn die Person bereits Student ist. Um einen Bewerber zum Studenten zu machen waehlen Sie bitte unter 'Status aendern' den Punkt 'Student'");
				}
			} else {
				return error("StudentModel: Error During Check if Existing Student Rolle.");
			}
		}
	}
}
