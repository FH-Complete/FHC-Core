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
		]);

		// Load Libraries
		$this->load->library('VariableLib', ['uid' => getAuthUID()]);
		$this->load->library('form_validation');

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

	//TODO Build own lib or combine with Controller Stundenplan.php
	//here use of logic of Stundenplan.php, extended with parameters uid, grouping, and used dbTable
	public function getStundenplan($uid, $start_date = null, $end_date = null, $dbStundenplanTable = "stundenplan", $groupConsecutiveHours = false)
	{
		$ci = get_instance();
		$student_uid = $uid;
		$semester_range = $this->studienSemesterErmitteln($start_date, $end_date);
		if (isError($semester_range))
			return $semester_range;
		$semester_range = getData($semester_range);
		$ci->addMeta('semester_range', $semester_range);

		$first_semester = $semester_range[0];

		$this->sortStudienSemester($semester_range);
		$function_error = $this->applyLoadUeberSemesterHaelfte($semester_range);
		$ci->addMeta('nach_apply_semester_range', $semester_range);
		if ($function_error)
			return $function_error;

		$benutzer_gruppen = $this->fetchBenutzerGruppenFromStudiensemester($student_uid, $semester_range);
		if (isError($benutzer_gruppen))
			return $benutzer_gruppen;
		$benutzer_gruppen = getData($benutzer_gruppen);
		$ci->addMeta('spezialgruppen', $benutzer_gruppen);
		// getting the student_lehrverbaende of the student in the different studiensemester

		$ende_semProlonged  = $semester_range[$first_semester][$first_semester]->ende;
		$ci->addMeta(' E N D E _semProlonged', $ende_semProlonged);

		$student_lehrverband = $this->fetchStudentlehrverbandFromStudiensemester($student_uid, $semester_range);

		//get semester
		if (isError($student_lehrverband))
			return $student_lehrverband;
		$student_lehrverband = getData($student_lehrverband);
		$ci->addMeta('studentlehrverbaende', $student_lehrverband);

		$this->load->model('crm/Student_model', 'StudentModel');
		$this->StudentModel->addSelect('ausbildungssemester');
		$this->StudentModel->addJoin('public.tbl_prestudentstatus ps', 'prestudent_id');
		$result = $this->StudentModel->loadWhere(array(
			"student_uid"=>$student_uid,
			'studiensemester_kurzbz' => $first_semester,
		));
		if (isError($result))
			$semesterStud = null;
		if(hasData($result)){
			$result = getData($result);
			$data = current($result);
			$semesterStud = $data->ausbildungssemester;
		}

		$ci->addMeta('semester of studentUid', $semesterStud);

		$stundenplan_query = $this->StundenplanModel->getStundenplanQuery(
			$start_date,
			$end_date, //will be overwritten in model if $ende_semProlonged
			$semester_range,
			$benutzer_gruppen,
			$student_lehrverband,
			$dbStundenplanTable,
			$groupConsecutiveHours,
			$ende_semProlonged,
			$semesterStud
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

	// TODO(Manu) following 5 private functions are copybased of StundenplanLib,
	// just applyLoadUeberSemesterHaelfte is different
	// after approval: change to public in StundenplanLib and use it here?

	private function sortStudienSemester(&$semester_range){
		usort(
			$semester_range,
			function($first, $second)
			{
				$sem_first = null;
				$year_first = null;
				$match_first = null;

				$sem_second = null;
				$year_second = null;
				$match_second = null;

				preg_match('/([WS]+)([0-9]+)/',$first,$match_first);
				preg_match('/([WS]+)([0-9]+)/',$second,$match_second);

				$sem_first = $match_first[1];
				$year_first = intval($match_first[2]);

				$sem_second = $match_second[1];
				$year_second = intval($match_second[2]);

				if($year_first < $year_second)
				{
					return -1;
				}
				else if($year_first > $year_second)
				{
					return 1;
				}
				else if($year_first == $year_second && $sem_first > $sem_second)
				{
					return 1;
				}
				else if($year_first == $year_second && $sem_first < $sem_second)
				{
					return -1;
				}
				return 0;
			}
		);
	}

	private function fetchBenutzerGruppenFromStudiensemester($student_uid, $semester_range)
	{
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');

		$benutzer_gruppen = [];
		// for each studiensemester fetch the benutzer gruppen and add them to an associate $bentuzer_gruppen array
		/*
		[
			['WS2023'] => [['gruppe1_SS2023','gruppe2_SS2023'],['gruppe1_WS2023','gruppe2_WS2023']],
			['SS2024'] => [['gruppe1_WS2023','gruppe2_WS2023'],['gruppe1_SS2024','gruppe2_SS2024']],
			['WS2024'] => [['gruppe1_SS2024','gruppe2_SS2024'],['gruppe1_WS2024','gruppe2_WS2024']],
		]
		*/
		foreach($semester_range as $semester_key => $semester_array)
		{
			$benutzer_gruppen[$semester_key] = [];
			// each semester could have ajoint semesters that need to be checked
			foreach($semester_array as $semester=>$semester_date_range)
			{
				// for each active semester query the benutzer_gruppen associated to the semester
				$benutzer_query = $this->BenutzergruppeModel->execReadOnlyQuery("
				SELECT * FROM tbl_benutzergruppe where uid = ? AND studiensemester_kurzbz = ?",[$student_uid, $semester]);
				if(isError($benutzer_query)){
					return error(getData($benutzer_query));
				}
				$benutzer_query_result = getData($benutzer_query)??[];

				array_push(
					$benutzer_gruppen[$semester_key],
					array_map(
						function($item)
						{
							return "'".$item->gruppe_kurzbz. "'";
						},
						$benutzer_query_result
					)
				);
			}
		}

		// merge the gruppen of each studiensemester together for the original studiensemester
		/*
		[
			['WS2023'] => ['gruppe1_SS2023','gruppe2_SS2023','gruppe1_WS2023','gruppe2_WS2023'],
			['SS2024'] => ['gruppe1_WS2023','gruppe2_WS2023','gruppe1_SS2024','gruppe2_SS2024'],
			['WS2024'] => ['gruppe1_SS2024','gruppe2_SS2024','gruppe1_WS2024','gruppe2_WS2024'],
		]
		*/
		$benutzer_gruppen = array_map(
			function($gruppe)
			{
				$merged_gruppe = [];
				foreach($gruppe as $gruppen_array)
				{
					$merged_gruppe = array_merge($merged_gruppe, $gruppen_array);
				}
				return $merged_gruppe;
			},
			$benutzer_gruppen
		);

		return success($benutzer_gruppen);
	}

	private function fetchStudentlehrverbandFromStudiensemester($student_uid, $semester_range)
	{
		$this->load->model('person/Benutzergruppe_model', 'BenutzergruppeModel');

		$student_lehrverband = [];
		// for each studiensemester fetch the studentlehrverbaende and add them to an associate $student_lehrverband array
		/*
		[
			['WS2023'] => [ [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""] ] ],
			['SS2024'] => [ [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""] ], [ ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""] ] ],
			['WS2024'] => [ [ ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""] ], [ ['stg_kz'=>298,'semester'=>3,'verband'=>"A",'gruppe'=>""] ] ],
		]
		*/
		foreach($semester_range as $semester_key => $semester_array)
		{
			$student_lehrverband[$semester_key] = [];
			foreach($semester_array as $semester=>$semester_date_range)
			{
				// for each active semester query the student_lehrverband associated to the semester
				$lehrverband_query = $this->BenutzergruppeModel->execReadOnlyQuery("
				SELECT * FROM tbl_studentlehrverband where student_uid = ? AND studiensemester_kurzbz = ?", [$student_uid, $semester]);
				if(isError($lehrverband_query)){
					return error(getData($lehrverband_query));
				}
				$lehrverband_query_result = getData($lehrverband_query)??[];

				$converted_studentLehrverband= array_map(
					function ($item)
					{
						$result = new stdClass();
						$result->studiengang_kz = $item->studiengang_kz;
						$result->semester = $item->semester;
						$result->verband = $item->verband;
						$result->gruppe = $item->gruppe;
						$result->studiensemester_kurzbz = $item->studiensemester_kurzbz;
						return $result;
					},
					$lehrverband_query_result);

				array_push($student_lehrverband[$semester_key], $converted_studentLehrverband);

			}

		}

		// merge the studentlehrverband of each studiensemester together for the original studiensemester
		/*
		[
			['WS2023'] => [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""] ],
			['SS2024'] => [ ['stg_kz'=>298,'semester'=>1,'verband'=>"A",'gruppe'=>""], ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""] ],
			['WS2024'] => [ ['stg_kz'=>298,'semester'=>2,'verband'=>"A",'gruppe'=>""], ['stg_kz'=>298,'semester'=>3,'verband'=>"A",'gruppe'=>""] ],
		]
		*/

		$student_lehrverband = array_map(
			function($studentlehrverband)
			{
				$merged_studentlehrverband = [];
				foreach($studentlehrverband as $studentlehrverband_array)
				{
					$merged_studentlehrverband = array_merge($merged_studentlehrverband, $studentlehrverband_array);
				}
				return $merged_studentlehrverband;
			},
			$student_lehrverband
		);

		return success($student_lehrverband);
	}

	//note: this is different to the version in StundenplanLib, changes apply just to LVTermine in StudVW
	private function applyLoadUeberSemesterHaelfte(&$semester_range)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		/*
		@var($semester_collection)
		convert the array of studiensemester into an associative array with the studiensemester as the key
		and the values of each key are the studiensemester needed for the query associated to that studiensemester
		example:

		#INPUT:
		['WS2023','SS2024','WS2024']
		#OUTPUT:
		[
			'WS2023' => ['SS2023','WS2023']
			'SS2024' => ['WS2023','SS2024']
			'WS2024' => ['SS2024','WS2024']
		]
		*/
		$semester_collection = [];
		foreach($semester_range as $studiensemester)
		{
			$previous_studiensemester = $this->StudiensemesterModel->getPreviousFrom($studiensemester);
			if(isError($previous_studiensemester))
			{
				return error(getData($previous_studiensemester));
			}
			$previous_studiensemester = getData($previous_studiensemester);
			if (count($previous_studiensemester) == 0) {
				return error('no previous semester');
			}
			$previous_studiensemester = current($previous_studiensemester)->studiensemester_kurzbz;
			$semester_collection[$studiensemester] = [$previous_studiensemester, $studiensemester];
		}

		/*
		@var($studienSemesterDateRanges)
		fetches for each studiensemester the start and end date, (SS) summer studiensemester are extended by 1 month to cover the summerbreak
		based on the LVPLAN_LOAD_UEBER_SEMESTERHAELFTE constant it will load both the semester and the previous semester with the full date range
		or the semester with the full date range and the previous semester with the half date range:

		#INPUT:
		[
			'WS2023' => ['SS2023','WS2023']
			'SS2024' => ['WS2023','SS2024']
			'WS2024' => ['SS2024','WS2024']
		]
		#OUTPUT: depends whether LVPLAN_LOAD_UEBER_SEMESTERHAELFTE is true or false
		~ if LVPLAN_LOAD_UEBER_SEMESTERHAELFTE == true
		[
			"SS2024": [
				"WS2023": [
					"start"=> "2024-02-03",
					"ende"=> "2024-08-31"
				],
				"SS2024": [
					"start"=> "2024-02-03",
					"ende"=> "2024-08-31"
				]
			]
		]
		~ if LVPLAN_LOAD_UEBER_SEMESTERHAELFTE == false
		[
			"SS2024": [
				"WS2023": [
					"start"=> "2024-02-03",
					"ende"=> "2024-05-17"
				],
				"SS2024": [
					"start"=> "2024-02-03",
					"ende"=> "2024-08-31"
				]
			]
		]
		*/
		$studienSemesterDateRanges=[];
		foreach($semester_collection as $semester_original => $semester_adjoint)
		{
			$semester_start_ende = $this->StudiensemesterModel->getStartEndeFromStudiensemester($semester_original);
			if(isError($semester_start_ende))
			{
				return error(getData($semester_start_ende));
			}
			$semester_start_ende = current(getData($semester_start_ende));

			// initialize empty arrays to add key value pairs
			$studienSemesterDateRanges[$semester_original] = [];

			// check if the studiensemester is a summer semester and add 1 month to bridge the school summer break
			$match = null;
			preg_match("/^(SS)([0-9]+)/",$semester_original,$match);
			if(count($match) >0)
			{
				$one_month = new DateInterval('P1M');
				$one_day = DateInterval::createFromDateString('1 days');
				$summer_studiensemester_end_date = DateTime::createFromFormat('Y-m-d',$semester_start_ende->ende);
				$summer_studiensemester_end_date->add($one_month);
				$summer_studiensemester_end_date->sub($one_day);
				$semester_start_ende->ende = date_format($summer_studiensemester_end_date,'Y-m-d');
			}

			//TODO(Manu) config not taken into account
			if (defined('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE') && LVPLAN_LOAD_UEBER_SEMESTERHAELFTE)
			{
				$this->addMeta('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE_TRUE', $semester_range);

				foreach($semester_adjoint as $adjoint)
				{
					$studienSemesterDateRanges[$semester_original][$adjoint]=$semester_start_ende;
				}
			}
			else
			{
				$this->addMeta('LVPLAN_LOAD_UEBER_SEMESTERHAELFTE_FALSE', $semester_range);
				//TODO: half of a DateInterval might not be correctly calculated
				// calculate the half of the studiensemester
				$studiensemester_start_date = DateTime::createFromFormat('Y-m-d',$semester_start_ende->start);
				$studiensemester_end_date = DateTime::createFromFormat('Y-m-d',$semester_start_ende->ende);
				$studiensemester_time_difference = $studiensemester_start_date->diff($studiensemester_end_date);
				$half_dateNumber = ceil($studiensemester_time_difference->d/2)+ceil(($studiensemester_time_difference->m*30)/2);
				$half_dateInterval = new DateInterval('P'.strval($half_dateNumber) .'D');
				$studiensemester_half = date_format($studiensemester_start_date->add($half_dateInterval),'Y-m-d');

				$first_half = new stdClass();
				$first_half->start = $semester_start_ende->start;
				$first_half->ende = $studiensemester_half;

				$studienSemesterDateRanges[$semester_original][$semester_adjoint[0]] = $first_half;
				$studiensemester_start_date = DateTime::createFromFormat(
					'Y-m-d',
					$semester_start_ende->start
				);

				$studiensemester_end_date = DateTime::createFromFormat(
					'Y-m-d',
					$semester_start_ende->ende
				);

				$studiensemester_time_difference = $studiensemester_start_date->diff(
					$studiensemester_end_date
				);

				$half_dateNumber =
					ceil($studiensemester_time_difference->d / 2) +
					ceil(($studiensemester_time_difference->m * 30) / 2);

				$half_dateInterval = new DateInterval(
					'P' . strval($half_dateNumber) . 'D'
				);

				$second_semester_end_date = clone $studiensemester_end_date;
				$second_semester_end_date->add($half_dateInterval);

				$second_semester_date_range = clone $semester_start_ende;
				$second_semester_date_range->ende = $second_semester_end_date->format('Y-m-d');

				// 2nd Adjacent-Semester: old time intervall plus half next semester
				$studienSemesterDateRanges[$semester_original][$semester_adjoint[1]] = $second_semester_date_range;
			}
			$semester_range = $studienSemesterDateRanges;
		}
	}

	private function studienSemesterErmitteln($start_date, $end_date)
	{
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');

		// gets all studiensemester from the student from start_date to end_date
		//$semester_range = $this->StudiensemesterModel->getByDateRange($start_date, $end_date);
		$semester_range = $this->StudiensemesterModel->getContainingOrNearestByDateRange($start_date, $end_date);
		if (isError($semester_range))
			return $semester_range;

		$semester_range = array_map(
			function ($sem) {
				return $sem->studiensemester_kurzbz;
			},
			getData($semester_range) ?: []
		);

		// if no studiensemester is found for the given timespan, get the nearest studiensemester
		if (count($semester_range) == 0)
		{
			$aktuelle_studiensemester = $this->StudiensemesterModel->getNearest();
			if (isError($aktuelle_studiensemester))
				return $aktuelle_studiensemester;

			$aktuelle_studiensemester = getData($aktuelle_studiensemester);
			if (count($aktuelle_studiensemester) == 0) {
				return error("No aktuelles semester");
			}
			$aktuelle_studiensemester = current($aktuelle_studiensemester)->studiensemester_kurzbz;
			// push aktuelles semester in active semester array
			array_push($semester_range, $aktuelle_studiensemester);
		}

		//auf jeden Fall Semester 2 dazunehmen, wenn nur eines vorhanden ist für Anzeige der ersten Hälfte des folgenden Semesters?
		if (count($semester_range) == 1)
		{
			$nextStudiensemester = $this->StudiensemesterModel->getNextFrom($semester_range[0]);
			if (isError($nextStudiensemester))
				return $nextStudiensemester;

			$nextStudiensemester = getData($nextStudiensemester);
			if (count($nextStudiensemester) == 0) {
				return error("No aktuelles semester");
			}
			$nextStudiensemester = current($nextStudiensemester)->studiensemester_kurzbz;
			// push aktuelles semester in active semester array
			array_push($semester_range, $nextStudiensemester);
		}


		return success($semester_range);
	}
}
