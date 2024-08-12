<?php

use \InvalidArgumentException as InvalidArgumentException;
use \CI3_Events as Events;

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
	 * Generiert die Matrikelnummer
	 * FORMAT: 0710254001
	 * 07 = Jahr
	 * 1/2/0  = WS/SS/incoming
	 * 0254 = Studiengangskennzahl vierstellig
	 * 001 = Laufende Nummer
	 * copy of generateMatrikelnummer plus
	 * logic FH Burgenland
	 *
	 * TODO(chris): replace function above with this?
	 * TODO(chris): rename to generatePersonenkennzeichen?
	 *
	 * @param integer					$studiengang_kz
	 * @param string					$studiensemester_kurzbz
	 * @param string					$typ
	 *
	 * @return stdClass
	 */
	public function generateMatrikelnummer2($studiengang_kz, $studiensemester_kurzbz, $typ = null)
	{
		$personenkennzeichen = false;

		Events::trigger(
			'generate_personenkennzeichen',
			function ($value) use ($personenkennzeichen) {
				$personenkennzeichen = $value;
			},
			$studiengang_kz,
			$studiensemester_kurzbz,
			$typ
		);

		if ($personenkennzeichen !== false)
			return success($personenkennzeichen);

		// Validierung der Eingabewerte
		if (strlen($studiensemester_kurzbz) < 6) {
			throw new InvalidArgumentException("Ungültiges studiensemester_kurzbz Format.");
		}

		$jahr = mb_substr($studiensemester_kurzbz, 4);
		$art = substr($studiensemester_kurzbz, 0, 2);

		if (($studiengang_kz < 0) || (isset($typ) && ($typ == 'l')))
		{
			$studiengang_kz=abs($studiengang_kz);
			//Lehrgang
			switch($art)
			{
				case 'WS':
					$art = '3';
					break;
				case 'SS':
					$art = '4';
					break;
				default:
					$art = '0';
					break;
			}
		}
		else
		{
			//Studiengang
			switch($art)
			{
				case 'WS':
					$art = '1';
					break;
				case 'SS':
					$art = '2';
					break;
				default:
					$art = '0';
					break;
			}
		}
		if($art=='2' || $art=='4')
			$jahr = $jahr-1;

		//FH-Burgenland - weil leider die AO Studiengänge aufgeteilt sind
		//(AO sind normal 9+erhalter Nummer, matrikelnr/personenkz wird auch im DVUH Extension berücksichtigt)
		if ($studiengang_kz >= 90010 && $studiengang_kz <= 90019)
		{
			$matrikelnummer = sprintf("%02d", $jahr).$art.substr($studiengang_kz, 0, 4);
		}
		else
		{
			$matrikelnummer = sprintf("%02d", $jahr).$art.sprintf("%04d", $studiengang_kz);
		}

		$qry = "SELECT matrikelnr FROM public.tbl_student WHERE matrikelnr LIKE ? ORDER BY matrikelnr DESC LIMIT 1";
		$matrikelnrres = $this->execQuery($qry, array($matrikelnummer.'%'));

		$max = 0;
		if ($matrikelnrres && hasData($matrikelnrres)) {
			$max = mb_substr($matrikelnrres->retval[0]->matrikelnr, 7);
			if (!is_numeric($max)) {
				$max = (int)$max;
			}
		}

		$max += 1;
		return success($matrikelnummer.sprintf("%03d", $max));
	}

	/**
	 * Generiert die UID
	 * FORMAT: el07b001
	 * $stgkzl: el = studiengangskuerzel
	 * $jahr: 07 = Jahr
	 * $stgtyp: b/m/d/x = Bachelor/Master/Diplom/Incoming
	 * $matrikelnummer
	 * 001 = Laufende Nummer  Wenn StSem==SS dann wird zur Nummer 500 dazugezaehlt
	 * Bei Incoming im Masterstudiengang wird auch 500 dazugezaehlt
	 *
	 * @param string					$stgkzl
	 * @param string					$jahr
	 * @param string					$stgtyp
	 * @param string					$matrikelnummer
	 * @param string					$vorname
	 * @param string					$nachname
	 *
	 * @return stdClass
	 */
	public function generateUID($stgkzl, $jahr, $stgtyp, $matrikelnummer, $vorname, $nachname)
	{
		$uid = false;

		Events::trigger(
			'generate_student_uid',
			function ($value) use ($uid) {
				$uid = $value;
			},
			$stgkzl,
			$jahr,
			$stgtyp,
			$matrikelnummer,
			$vorname,
			$nachname
		);

		if ($uid !== false)
			return success($uid);

		$art = mb_substr($matrikelnummer, 2, 1);
		$nr = mb_substr($matrikelnummer, mb_strlen(trim($matrikelnummer))-3);
		if($art=='2') //Sommersemester
			$nr = $nr+500;
		elseif($art=='0' && $stgtyp=='m') //Incoming im Masterstudiengang
			$nr = $nr+500;
		elseif($art=='4' && $stgtyp=='l') // Lehrgangsteilnehmer im Sommersemester
			$nr = $nr+500;


		return success(mb_strtolower($stgkzl.$jahr.($art!='0'?$stgtyp:'x').$nr));
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
			OR lower(person.vorname || ' ' || person.nachname) like ".$this->db->escape('%'.$filter.'%')
		);

		return $result;
	}

	/**
	 * Get the FH-Email for a student (not the private kontakt email)
	 * @param $student_uid
	 * @return string
	 */
	public function getEmailFH($student_uid)
	{
		return $student_uid . '@' . DOMAIN;
	}
}
