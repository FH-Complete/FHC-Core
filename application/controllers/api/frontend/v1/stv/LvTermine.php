<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

use \DateTime as DateTime;

class LvTermine extends FHCAPI_Controller
{
	public function __construct()
	{
		parent::__construct([
			'getStundenplan' => ['admin:r', 'assistenz:r'],
			'getStudiensemester' => ['admin:r', 'assistenz:r'],
			'getStundenplanForStudiensemester' => ['admin:r', 'assistenz:r'],
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');
		$this->load->library('StundenplanLib');

		// Load language phrases
		$this->loadPhrases([
			'ui',
		]);

		// Load models
		$this->load->model('ressource/Stundenplan_model', 'StundenplanModel');

		//query verwenden wie im Cis endpoint
		$this->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->load->model('education/Studentlehrverband_model', 'StudentlehrverbandModel');
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');
	}

	//TODO(check if this function is still used/needed
	public function getStundenplan($uid, $start_date = null, $end_date = null, $dbStundenplanTable = "stundenplan", $groupConsecutiveHours = false)
	{
		$student_uid = $uid;
		$semester_range = $this->stundenplanlib->studienSemesterErmitteln($start_date, $end_date);
		if (isError($semester_range))
			return $semester_range;
		$semester_range = getData($semester_range);

		$this->stundenplanlib->sortStudienSemester($semester_range);
		$this->stundenplanlib->applyLoadUeberSemesterHaelfte($semester_range);

		$benutzer_gruppen = $this->stundenplanlib->fetchBenutzerGruppenFromStudiensemester($student_uid, $semester_range);
		$student_lehrverband = $this->stundenplanlib->fetchStudentlehrverband($semester_range, $student_uid);

		if(!$groupConsecutiveHours)
			$stundenplan_query = $this->StundenplanModel->getStundenplanQuery(
				$start_date,
				$end_date,
				$semester_range,
				$benutzer_gruppen,
				$student_lehrverband
			);
		else
			$stundenplan_query = $this->StundenplanModel->getStundenplanQuery(
				$start_date,
				$end_date,
				$semester_range,
				$benutzer_gruppen,
				$student_lehrverband,
				true,
				$dbStundenplanTable
			);

		if(!$stundenplan_query)
		{
			$this->terminateWithSuccess([]);
		}

		if($groupConsecutiveHours)
		{
			$stundenplan_data = $this->StundenplanModel->stundenplanGruppierungConsecutive($stundenplan_query);
		}
		else
		{
			$stundenplan_data = $this->StundenplanModel->stundenplanGruppierung($stundenplan_query);
		}

		$stundenplan_data = $this->getDataOrTerminateWithError($stundenplan_data) ?? [];
		$this->terminateWithSuccess($stundenplan_data);
	}

	public function getStundenplanForStudiensemester($uid, $studiensemester_kurzbz, $dbStundenplanTable = "stundenplan", $groupConsecutiveHours = false)
	{
		$resstudiensemester = $this->StudiensemesterModel->getStartEndeFromStudiensemester($studiensemester_kurzbz);
		if(isError($resstudiensemester))
			return $resstudiensemester;
		$resstudiensemester = getData($resstudiensemester);


		$resnextstudiensemester = $this->StudiensemesterModel->getNextFrom($studiensemester_kurzbz);
				if(isError($resnextstudiensemester))
					return $resnextstudiensemester;
		$resnextstudiensemester = getData($resnextstudiensemester);

		$studiensemester = ($resstudiensemester && count($resstudiensemester) > 0) ? $resstudiensemester[0] : null;
		$studiensemester->studiensemester_kurzbz = $studiensemester_kurzbz;
		$nextstudiensemester = ($resnextstudiensemester && count($resnextstudiensemester) > 0) ? $resnextstudiensemester[0] : null;

		$start_date = $studiensemester->start;
		$end_date = $nextstudiensemester->ende;

		$student_uid = $uid;
		$semester_range = $this->stundenplanlib->studienSemesterErmitteln($start_date, $end_date);
		if (isError($semester_range))
			return $semester_range;
		$semester_range = getData($semester_range);

		$this->stundenplanlib->sortStudienSemester($semester_range);
		$function_error = $this->stundenplanlib->applyLoadUeberSemesterHaelfte($semester_range);
		if ($function_error)
			return $function_error;

		$benutzer_gruppen = $this->stundenplanlib->fetchBenutzerGruppenFromStudiensemester($student_uid, $semester_range, $nextstudiensemester->studiensemester_kurzbz);
		if (isError($benutzer_gruppen))
			return $benutzer_gruppen;
		$benutzer_gruppen = getData($benutzer_gruppen);

		/*
		getting the student_lehrverbaende of the student in the different studiensemester
		$semester_range = array(
			'SS2026' => array(
				'WS2025' => (object) array(
					'start' => '2026-02-04',
					'ende' => '2026-05-19'
				),
				'SS2026' => (object) array(
					'start' => '2026-02-04',
					'ende' => '2026-11-15'
				),
				'WS2026' => (object) array(
					'start' => '2026-09-01',
					'ende' => '2027-02-03'
				)
			)
		);
		*/

		$student_lehrverband = $this->stundenplanlib->fetchStudentlehrverbandFromStudiensemester($student_uid, $semester_range, $studiensemester->studiensemester_kurzbz, $nextstudiensemester->studiensemester_kurzbz);
		if (isError($student_lehrverband))
			return $student_lehrverband;
		$student_lehrverband = getData($student_lehrverband);

		/*
		exampledata
		$student_lehrverband = array (
			'SS2026' =>
				array (
				  0 =>
					  (object) array(
					 'verbandsgruppe' => 'BEE1A',
					 'studiengang_kz' => 476,
					 'semester' => 1,
					 'verband' => 'A',
					 'gruppe' => ' ',
					 'studiensemester_kurzbz' => 'WS2025',
				  ),
				  1 =>
					  (object) array(
					 'verbandsgruppe' => 'BEE2A',
					 'studiengang_kz' => 476,
					 'semester' => 2,
					 'verband' => 'A',
					 'gruppe' => ' ',
					 'studiensemester_kurzbz' => 'SS2026',
				  ),
				  2 =>
					  (object) array(
					 'verbandsgruppe' => 'BEE3A',
					 'studiengang_kz' => 476,
					 'semester' => 3,
					 'verband' => 'A',
					 'gruppe' => ' ',
					 'studiensemester_kurzbz' => 'SS2026',
				  ),
				),
			);
		*/

		$stundenplan_query = $this->StundenplanModel->getStundenplanQuery(
			$start_date,
			$end_date,
			$semester_range,
			$benutzer_gruppen,
			$student_lehrverband,
			$dbStundenplanTable,
			false
		);

		if(!$stundenplan_query)
		{
			$this->terminateWithSuccess([]);
		}

		if($groupConsecutiveHours)
		{
			$stundenplan_data = $this->StundenplanModel->stundenplanGruppierungConsecutive($stundenplan_query);
		}
		else
		{
			$stundenplan_data = $this->StundenplanModel->stundenplanGruppierung($stundenplan_query);
		}

		$stundenplan_data = $this->getDataOrTerminateWithError($stundenplan_data) ?? [];
		$this->terminateWithSuccess($stundenplan_data);
	}

	public function getStudiensemester()
	{
			$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

			$this->StudiensemesterModel->addOrder('studienjahr_kurzbz', 'DESC');
			$result = $this->StudiensemesterModel->load();
			$data = $this->getDataOrTerminateWithError($result);
			$this->terminateWithSuccess($data);
	}
}
