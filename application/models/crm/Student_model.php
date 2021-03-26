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
}
