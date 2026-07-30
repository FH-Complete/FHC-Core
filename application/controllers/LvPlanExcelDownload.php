<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/functions.inc.php');

class LvPlanExcelDownload extends Auth_Controller
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
		$this->load->model('ressource/Ort_model', 'OrtModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('organisation/Studiensemester_model', 'StudiensemesterModel');
		$this->loadPhrases(array('global'));
	}

	/**
	 * Serves an authenticated Excel file download.
	 *
	 * @return void
	 */
	public function index()
	{
		$type = $this->getType();
		$uid = getAuthUID();
		$ortKurzbz = null;
		$verbandFilters = null;
		if ($type === 'ort')
			$ortKurzbz = $this->getRoom();
		elseif ($type === 'verband')
			$verbandFilters = $this->getVerbandFilters();
		else
		{
			$uid = $this->getUid();
			$this->authorizeUid($uid);
			$this->validateUid($uid);
		}
		list($begin, $ende) = $this->getDateRange();

		$this->load->library('LvPlanExcelLib');
		$content = $this->lvplanexcellib->getContent(
			$uid,
			$begin,
			$ende,
			$type,
			$ortKurzbz,
			$verbandFilters
		);

		$filename = $this->getFilename($begin, $ende);

		$this->output
			->set_content_type('application/vnd.ms-excel')
			->set_header('Content-Disposition: attachment; filename="'.$filename.'"')
			->set_header('Pragma: public')
			->set_header('Expires: 0')
			->set_output($content);
	}

	/**
	 * @return string Requested timetable type.
	 */
	private function getType()
	{
		$type = $this->input->get('type', true);

		if (!in_array($type, array('student', 'lektor', 'ort', 'verband'), true))
			show_error('Ungueltiger Parameter: type', 400);

		return $type;
	}

	/**
	 * @return string Requested user UID.
	 */
	private function getUid()
	{
		$uid = $this->input->get('pers_uid', true);

		if (!is_string($uid) || $uid === '' || !check_utf8($uid))
			show_error('Ungueltiger Parameter: pers_uid', 400);

		return $uid;
	}

	/**
	 * @return string Requested room identifier.
	 */
	private function getRoom()
	{
		$ortKurzbz = $this->input->get('ort_kurzbz', true);
		if (!is_string($ortKurzbz) || $ortKurzbz === '' || !check_utf8($ortKurzbz))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		$this->OrtModel->addSelect('ort_kurzbz');
		$room = $this->OrtModel->load($ortKurzbz);
		if (isError($room))
			show_error(getError($room), 500);
		if (!hasData($room))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		return $ortKurzbz;
	}

	/**
	 * @return array Requested Lehrverband filters.
	 */
	private function getVerbandFilters()
	{
		$stgKz = $this->getInteger('stg_kz', true);
		$semester = $this->getInteger('sem', false, true);
		$verband = $this->getOptionalFilter('ver');
		$gruppe = $this->getOptionalFilter('grp');

		$this->StudiengangModel->addSelect('studiengang_kz');
		$studiengang = $this->StudiengangModel->load($stgKz);
		if (isError($studiengang))
			show_error(getError($studiengang), 500);
		if (!hasData($studiengang))
			show_error('Ungueltiger Parameter: stg_kz', 400);

		return array(
			'stg_kz' => $stgKz,
			'sem' => $semester,
			'ver' => $verband,
			'grp' => $gruppe
		);
	}

	private function getInteger($name, $signed, $optional = false)
	{
		$value = $this->input->get($name, true);
		if ($optional && ($value === null || $value === ''))
			return null;

		$pattern = $signed ? '/^-?\d+$/' : '/^\d+$/';
		if (!is_string($value) || !preg_match($pattern, $value))
			show_error('Ungueltiger Parameter: '.$name, 400);

		$integer = filter_var($value, FILTER_VALIDATE_INT);
		if ($integer === false || (!$signed && $integer < 0))
			show_error('Ungueltiger Parameter: '.$name, 400);

		return $integer;
	}

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
	 * Builds the Excel download filename.
	 *
	 * @param int $begin Range start timestamp.
	 * @param int $ende Range end timestamp.
	 * @return string Filename.
	 */
	private function getFilename($begin, $ende)
	{
		$studiensemester = $this->getStudiensemester($begin, $ende);

		$filename = $this->p->t('global', 'termine');
		if ($studiensemester !== null)
			$filename .= '_'.$studiensemester->studiensemester_kurzbz;
		$filename .= '-'.date('Ymd').'.xls';

		return $filename;
	}

	/**
	 * Gets the study semester covered by the requested date range.
	 *
	 * @param int $begin Range start timestamp.
	 * @param int $ende Range end timestamp.
	 * @return object|null Study semester, if one overlaps the range.
	 */
	private function getStudiensemester($begin, $ende)
	{
		$semesterRange = $this->StudiensemesterModel->getByDateRange(
			date('Y-m-d', $begin),
			date('Y-m-d', $ende)
		);

		if (isError($semesterRange) || !hasData($semesterRange))
			return null;

		$studiensemester = current(getData($semesterRange));

		return $studiensemester;
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

	/**
	 * Ensures access to another user's timetable is explicitly permitted.
	 *
	 * @param string $uid Requested user UID.
	 * @return void
	 */
	private function authorizeUid($uid)
	{
		if ($uid !== getAuthUID()
			&& !$this->permissionlib->isBerechtigt('basis/other_lv_plan'))
		{
			show_error('Forbidden', 403);
		}
	}

	/**
	 * Ensures the requested user exists.
	 *
	 * @param string $uid Requested user UID.
	 * @return void
	 */
	private function validateUid($uid)
	{
		$this->BenutzerModel->addSelect('uid');
		$user = $this->BenutzerModel->load(array($uid));

		if (isError($user))
			show_error(getError($user), 500);

		if (!hasData($user))
			show_error('Ungueltiger Parameter: pers_uid', 400);
	}
}
