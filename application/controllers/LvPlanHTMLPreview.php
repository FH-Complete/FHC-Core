<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/functions.inc.php');

class LvPlanHTMLPreview extends Auth_Controller
{
	const MAX_DATE_RANGE = 34560000; // 400 days

	/**
	 * Object initialization.
	 */
	public function __construct()
	{
		parent::__construct([
			'index' => ['basis/cis:r']
		]);

		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('crm/Student_model', 'StudentModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('ressource/Ort_model', 'OrtModel');
	}

	/**
	 * Serves an authenticated, printable timetable preview.
	 *
	 * @return void
	 */
	public function index()
	{
		$type = $this->getType();
		list($begin, $ende) = $this->getDateRange();
		if ($type === 'verband')
			$filters = $this->getVerbandFilters();
		elseif ($type === 'ort')
			$filters = $this->getRoomFilters();
		else
			$filters = $this->getPersonalFilters($type);

		$this->load->library('LvPlanHTMLLib');
		$content = $this->lvplanhtmllib->getContent(
			$begin,
			$ende,
			$type,
			$filters
		);

		$this->output
			->set_content_type('text/html', 'UTF-8')
			->set_header('X-Content-Type-Options: nosniff')
			->set_output($content);
	}

	/**
	 * @return string Requested timetable type.
	 */
	private function getType()
	{
		$type = $this->input->get('type', true);

		if (!in_array($type, array('student', 'lektor', 'verband', 'ort'), true))
			show_error('Ungueltiger Parameter: type', 400);

		return $type;
	}

	/**
	 * @return array Personal preview filters.
	 */
	private function getPersonalFilters($type)
	{
		// Legacy wochenplan calendar links include empty Lehrverband parameters
		// for personal plans. They are irrelevant when type is student or lektor.
		if ($this->hasVerbandParameters())
			show_error('Ungueltige Parameterkombination', 400);

		$uid = $this->input->get('pers_uid', true);
		if (!is_string($uid) || $uid === '' || !check_utf8($uid))
			show_error('Ungueltiger Parameter: pers_uid', 400);

		if ($uid !== getAuthUID()
			&& !$this->permissionlib->isBerechtigt('basis/other_lv_plan'))
		{
			show_error('Forbidden', 403);
		}

		$this->BenutzerModel->addSelect(
			'tbl_benutzer.uid, tbl_person.titelpre, tbl_person.vorname, '
			.'tbl_person.nachname, tbl_person.titelpost'
		);
		$this->BenutzerModel->addJoin('public.tbl_person', 'person_id', 'LEFT');
		$user = $this->BenutzerModel->load(array($uid));

		if (isError($user))
			show_error(getError($user), 500);
		if (!hasData($user))
			show_error('Ungueltiger Parameter: pers_uid', 400);
		$users = getData($user);
		$userData = reset($users);
		$nameParts = array();
		foreach (array('titelpre', 'vorname', 'nachname', 'titelpost') as $property)
		{
			if (isset($userData->{$property}) && trim($userData->{$property}) !== '')
				$nameParts[] = trim($userData->{$property});
		}

		$filters = array(
			'uid' => $uid,
			'full_name' => implode(' ', $nameParts)
		);

		if ($type === 'student')
		{
			$this->StudentModel->addSelect(
				'studiengang_kz, semester, verband, gruppe'
			);
			$student = $this->StudentModel->load(array($uid));

			if (isError($student))
				show_error(getError($student), 500);
			if (!hasData($student))
				show_error('Ungueltiger Parameter: pers_uid', 400);

			$students = getData($student);
			$studentData = reset($students);
			$filters['stg_kz'] = $studentData->studiengang_kz;
			$filters['sem'] = $studentData->semester;
			$filters['ver'] = $studentData->verband;
			$filters['grp'] = $studentData->gruppe;
		}

		return $filters;
	}

	/**
	 * Legacy room links also carry pers_uid, but the UID does not filter or
	 * authorize a room plan. Only ort_kurzbz identifies the requested plan.
	 *
	 * @return array Room preview filters.
	 */
	private function getRoomFilters()
	{
		$ortKurzbz = $this->input->get('ort_kurzbz', true);
		if (!is_string($ortKurzbz) || $ortKurzbz === '' || !check_utf8($ortKurzbz))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		$this->OrtModel->addSelect('ort_kurzbz, bezeichnung');
		$room = $this->OrtModel->load($ortKurzbz);

		if (isError($room))
			show_error(getError($room), 500);
		if (!hasData($room))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		$rooms = getData($room);
		$roomData = reset($rooms);

		return array(
			'ort_kurzbz' => $ortKurzbz,
			'bezeichnung' => isset($roomData->bezeichnung) ? $roomData->bezeichnung : ''
		);
	}

	/**
	 * @return array Lehrverband preview filters.
	 */
	private function getVerbandFilters()
	{
		// Legacy wochenplan links always carry pers_uid. It does not filter or
		// authorize a Lehrverband plan and is intentionally ignored here.
		$stgKz = $this->getInteger('stg_kz', true);
		$semester = $this->getInteger('sem', false);
		$verband = $this->getOptionalFilter('ver');
		$gruppe = $this->getOptionalFilter('grp');

		$this->StudiengangModel->addSelect(
			'studiengang_kz, UPPER(typ || kurzbz) AS stg_kurzbz'
		);
		$studiengang = $this->StudiengangModel->load($stgKz);

		if (isError($studiengang))
			show_error(getError($studiengang), 500);
		if (!hasData($studiengang))
			show_error('Ungueltiger Parameter: stg_kz', 400);
		$studiengaenge = getData($studiengang);
		$studiengangData = reset($studiengaenge);

		return array(
			'stg_kz' => $stgKz,
			'stg_kurzbz' => isset($studiengangData->stg_kurzbz)
				? $studiengangData->stg_kurzbz
				: '',
			'sem' => $semester,
			'ver' => $verband,
			'grp' => $gruppe
		);
	}

	/**
	 * @return bool Whether any non-empty Lehrverband-only parameters were supplied.
	 */
	private function hasVerbandParameters()
	{
		foreach (array('stg_kz', 'sem', 'ver', 'grp') as $name)
		{
			$value = $this->input->get($name, true);
			if ($value !== null && $value !== '')
				return true;
		}

		return false;
	}

	/**
	 * @param string $name GET parameter name.
	 * @param bool $signed Whether negative values are allowed.
	 * @return int
	 */
	private function getInteger($name, $signed)
	{
		$value = $this->input->get($name, true);
		$pattern = $signed ? '/^-?\d+$/' : '/^\d+$/';

		if (!is_string($value) || !preg_match($pattern, $value))
			show_error('Ungueltiger Parameter: '.$name, 400);

		$isNegative = $signed && substr($value, 0, 1) === '-';
		$digits = $isNegative ? substr($value, 1) : $value;
		$digits = ltrim($digits, '0');
		if ($digits === '')
			$digits = '0';
		$normalizedValue = $isNegative && $digits !== '0' ? '-'.$digits : $digits;

		$integer = filter_var($normalizedValue, FILTER_VALIDATE_INT);
		if ($integer === false || (!$signed && $integer < 0))
			show_error('Ungueltiger Parameter: '.$name, 400);

		return $integer;
	}

	/**
	 * Returns null for the legacy wildcard values "" and "0".
	 *
	 * @param string $name GET parameter name.
	 * @return string|null
	 */
	private function getOptionalFilter($name)
	{
		$value = $this->input->get($name, true);

		if ($value === null || $value === '' || $value === '0')
			return null;

		if (!is_string($value)
			|| !check_utf8($value)
			|| mb_strlen($value) > 20
			|| !preg_match('/^[[:alnum:]_-]+$/u', $value))
		{
			show_error('Ungueltiger Parameter: '.$name, 400);
		}

		return $value;
	}

	/**
	 * Validates and expands the requested range to complete weeks.
	 *
	 * @return array{0: int, 1: int} Begin and end timestamps.
	 */
	private function getDateRange()
	{
		$begin = $this->getTimestamp('begin');
		$ende = $this->getTimestamp('ende');

		if ($ende < $begin)
			show_error('Ungueltiger Datumsbereich', 400);
		if ($ende - $begin > self::MAX_DATE_RANGE)
			show_error('Datumsbereich zu gross', 400);

		$begin = strtotime('monday this week', $begin);
		$ende = strtotime('sunday this week', $ende);

		return array($begin, $ende);
	}

	/**
	 * @param string $name GET parameter name.
	 * @return int Unix timestamp.
	 */
	private function getTimestamp($name)
	{
		$value = $this->input->get($name, true);

		if (!is_string($value) || !ctype_digit($value))
			show_error('Ungueltiger Parameter: '.$name, 400);

		$timestamp = filter_var(
			$value,
			FILTER_VALIDATE_INT,
			array('options' => array('min_range' => 0))
		);

		if ($timestamp === false)
			show_error('Ungueltiger Parameter: '.$name, 400);

		return $timestamp;
	}
}
