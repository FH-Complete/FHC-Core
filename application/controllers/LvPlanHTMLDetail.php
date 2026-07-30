<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/functions.inc.php');

class LvPlanHTMLDetail extends Auth_Controller
{
	const TIMEZONE = 'Europe/Vienna';

	/**
	 * Object initialization.
	 */
	public function __construct()
	{
		parent::__construct(array(
			'index' => array('basis/cis:r')
		));

		$this->load->model('person/Benutzer_model', 'BenutzerModel');
		$this->load->model('organisation/Studiengang_model', 'StudiengangModel');
		$this->load->model('ressource/Ort_model', 'OrtModel');
		$this->load->model('ressource/Stunde_model', 'StundeModel');
		$this->loadPhrases(array(
			'global' => array('termine'),
			'ui' => array('organisierende', 'teilnehmende')
		));
	}

	/**
	 * Serves details for one hour in the HTML timetable preview.
	 *
	 * @return void
	 */
	public function index()
	{
		$type = $this->getType();
		$datum = $this->getDate();
		$hour = $this->getHour();

		if ($type === 'verband')
			$filters = $this->getVerbandFilters();
		elseif ($type === 'ort')
			$filters = $this->getRoomFilters();
		else
			$filters = $this->getPersonalFilters();

		$this->load->library('LvPlanHTMLDetailLib');
		$content = $this->lvplanhtmldetaillib->getContent(
			$datum,
			$hour,
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
	 * @return string Date in YYYY-MM-DD format.
	 */
	private function getDate()
	{
		$value = $this->input->get('datum', true);
		if (!is_string($value) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $value))
			show_error('Ungueltiger Parameter: datum', 400);

		$date = DateTime::createFromFormat(
			'!Y-m-d',
			$value,
			new DateTimeZone(self::TIMEZONE)
		);
		$errors = DateTime::getLastErrors();
		if ($date === false
			|| ($errors !== false
				&& ($errors['warning_count'] > 0 || $errors['error_count'] > 0))
			|| $date->format('Y-m-d') !== $value)
		{
			show_error('Ungueltiger Parameter: datum', 400);
		}

		return $value;
	}

	/**
	 * @return array Selected timetable hour.
	 */
	private function getHour()
	{
		$number = $this->getInteger('stunde', false);
		$this->StundeModel->addSelect('stunde, beginn, ende');
		$result = $this->StundeModel->load($number);

		if (isError($result))
			show_error(getError($result), 500);
		if (!hasData($result))
			show_error('Ungueltiger Parameter: stunde', 400);

		$hours = getData($result);
		$hour = reset($hours);
		$begin = isset($hour->beginn) ? (string)$hour->beginn : '';
		$end = isset($hour->ende) ? (string)$hour->ende : '';
		$beginSeconds = $this->timeToSeconds($begin);
		$endSeconds = $this->timeToSeconds($end);

		if ($beginSeconds === null || $endSeconds === null || $endSeconds <= $beginSeconds)
			show_error('Ungueltiges Stundenraster', 500);

		return array(
			'number' => (string)$hour->stunde,
			'begin' => $begin,
			'end' => $end
		);
	}

	/**
	 * @return array Personal timetable filters.
	 */
	private function getPersonalFilters()
	{
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
		$result = $this->BenutzerModel->load(array($uid));

		if (isError($result))
			show_error(getError($result), 500);
		if (!hasData($result))
			show_error('Ungueltiger Parameter: pers_uid', 400);

		$users = getData($result);
		$user = reset($users);
		$nameParts = array();
		foreach (array('titelpre', 'vorname', 'nachname', 'titelpost') as $property)
		{
			if (isset($user->{$property}) && trim($user->{$property}) !== '')
				$nameParts[] = trim($user->{$property});
		}

		return array(
			'uid' => $uid,
			'full_name' => implode(' ', $nameParts)
		);
	}

	/**
	 * @return array Room timetable filters.
	 */
	private function getRoomFilters()
	{
		$ortKurzbz = $this->input->get('ort_kurzbz', true);
		if (!is_string($ortKurzbz) || $ortKurzbz === '' || !check_utf8($ortKurzbz))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		$this->OrtModel->addSelect('ort_kurzbz, bezeichnung, content_id');
		$result = $this->OrtModel->load($ortKurzbz);

		if (isError($result))
			show_error(getError($result), 500);
		if (!hasData($result))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		$rooms = getData($result);
		$room = reset($rooms);

		return array(
			'ort_kurzbz' => $ortKurzbz,
			'bezeichnung' => isset($room->bezeichnung) ? $room->bezeichnung : '',
			'content_id' => isset($room->content_id) ? $room->content_id : null
		);
	}

	/**
	 * @return array Lehrverband timetable filters.
	 */
	private function getVerbandFilters()
	{
		$stgKz = $this->getInteger('stg_kz', true);
		$semester = $this->getInteger('sem', false, true);
		$verband = $this->getOptionalFilter('ver');
		$gruppe = $this->getOptionalFilter('grp');
		$ortKurzbz = $this->getOptionalRoomFilter();

		$this->StudiengangModel->addSelect(
			'studiengang_kz, UPPER(typ || kurzbz) AS stg_kurzbz'
		);
		$result = $this->StudiengangModel->load($stgKz);

		if (isError($result))
			show_error(getError($result), 500);
		if (!hasData($result))
			show_error('Ungueltiger Parameter: stg_kz', 400);

		$programs = getData($result);
		$program = reset($programs);

		return array(
			'stg_kz' => $stgKz,
			'stg_kurzbz' => isset($program->stg_kurzbz)
				? $program->stg_kurzbz
				: '',
			'sem' => $semester,
			'ver' => $verband,
			'grp' => $gruppe,
			'ort_kurzbz' => $ortKurzbz
		);
	}

	/**
	 * Returns the room selected in a Verband preview cell, if present.
	 *
	 * @return string|null
	 */
	private function getOptionalRoomFilter()
	{
		$ortKurzbz = $this->input->get('ort_kurzbz', true);
		if ($ortKurzbz === null || $ortKurzbz === '')
			return null;
		if (!is_string($ortKurzbz) || !check_utf8($ortKurzbz))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		$this->OrtModel->addSelect('ort_kurzbz');
		$result = $this->OrtModel->load($ortKurzbz);

		if (isError($result))
			show_error(getError($result), 500);
		if (!hasData($result))
			show_error('Ungueltiger Parameter: ort_kurzbz', 400);

		return $ortKurzbz;
	}

	/**
	 * @param string $name GET parameter name.
	 * @param bool $signed Whether negative values are allowed.
	 * @param bool $optional Whether the parameter may be omitted or empty.
	 * @return int|null
	 */
	private function getInteger($name, $signed, $optional = false)
	{
		$value = $this->input->get($name, true);
		if ($optional && ($value === null || $value === ''))
			return null;

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
	 * @param string $time PostgreSQL time value.
	 * @return int|null
	 */
	private function timeToSeconds($time)
	{
		if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2})(?:\.\d+)?)?$/', $time, $matches))
			return null;

		$hours = (int)$matches[1];
		$minutes = (int)$matches[2];
		$seconds = isset($matches[3]) ? (int)$matches[3] : 0;

		if ($hours > 23 || $minutes > 59 || $seconds > 59)
			return null;

		return $hours * 3600 + $minutes * 60 + $seconds;
	}
}
