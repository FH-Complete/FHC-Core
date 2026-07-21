<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/Excel/excel.php');

class LvPlanExcelLib
{
	const WORKSHEET_NAME = 'Termine';

	const HEADER = array(
		'Datum',
		'Von',
		'Bis',
		'Ort',
		'Lektoren',
		'Gruppen',
		'Lehrfach',
		'Anmerkung',
		'StundeVon',
		'StundeBis'
	);

	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Builds a BIFF8 Excel workbook for a user's timetable.
	 *
	 * @param string $uid User UID.
	 * @param int $begin First included date as Unix timestamp.
	 * @param int $ende Last included date as Unix timestamp.
	 * @param string $type Timetable type (student, lektor, ort or verband).
	 * @param string|null $ortKurzbz Room identifier for room plans.
	 * @param array|null $verbandFilters Lehrverband filters.
	 * @return string Binary workbook content.
	 */
	public function getContent(
		$uid,
		$begin,
		$ende,
		$type,
		$ortKurzbz = null,
		$verbandFilters = null
	) {
		if (!in_array($type, array('student', 'lektor', 'ort', 'verband'), true))
			throw new InvalidArgumentException('Unsupported timetable type');
		if ($type === 'ort' && (!is_string($ortKurzbz) || $ortKurzbz === ''))
			throw new InvalidArgumentException('Missing room timetable filter');
		if ($type === 'verband'
			&& (!is_array($verbandFilters)
				|| !array_key_exists('stg_kz', $verbandFilters)
				|| !array_key_exists('sem', $verbandFilters)))
		{
			throw new InvalidArgumentException('Missing Lehrverband timetable filters');
		}

		$isLecturer = $type === 'lektor';
		$this->ci->load->library('KalenderLib', array('uid' => $uid));
		$this->ci->load->model('ressource/Stunde_model', 'StundeModel');

		$startDate = date('Y-m-d', $begin);
		$endDate = date('Y-m-d', $ende);

		if ($type === 'verband')
		{
			$events = $this->ci->kalenderlib->getPlanForVerband(
				$startDate,
				$endDate,
				$verbandFilters['stg_kz'],
				$verbandFilters['sem'],
				isset($verbandFilters['ver']) ? $verbandFilters['ver'] : null,
				isset($verbandFilters['grp']) ? $verbandFilters['grp'] : null
			);
		}
		elseif ($type === 'ort')
		{
			$events = $this->ci->kalenderlib
				->getPlanForRoom($startDate, $endDate, $ortKurzbz);
		}
		elseif ($isLecturer)
		{
			$events = $this->ci->kalenderlib
				->getPlanForLecturerByLecturer($startDate, $endDate, $uid);
		}
		else
		{
			$events = $this->ci->kalenderlib
				->getPlanForStudentByStudent($startDate, $endDate, $uid);
		}

		if (!is_array($events))
			$events = array();

		usort($events, array($this, 'compareEventsByStart'));
		$hourRaster = $this->loadHourRaster();
		$rows = array();

		foreach ($events as $event)
		{
			$row = $this->buildEventRow($event, $hourRaster);
			if ($row !== null)
				$rows[] = $row;
		}

		return $this->buildWorkbook($rows);
	}

	/**
	 * @return array Timetable-hour raster ordered by hour number.
	 */
	private function loadHourRaster()
	{
		$this->ci->StundeModel->addSelect('stunde, beginn, ende');
		$this->ci->StundeModel->addOrder('stunde', 'ASC');
		$result = $this->ci->StundeModel->load();

		if (isError($result))
			throw new RuntimeException(getError($result));

		if (!hasData($result))
			return array();

		$raster = array();
		foreach (getData($result) as $hour)
		{
			$begin = $this->timeToSeconds($hour->beginn);
			$end = $this->timeToSeconds($hour->ende);

			if ($begin === null || $end === null)
				continue;

			$raster[] = array(
				'stunde' => $hour->stunde,
				'begin' => $begin,
				'end' => $end
			);
		}

		return $raster;
	}

	/**
	 * @param object $event Calendar event.
	 * @param array $hourRaster Timetable-hour raster.
	 * @return array|null Excel row or null for invalid dates.
	 */
	private function buildEventRow($event, array $hourRaster)
	{
		$start = $this->createDateTime(isset($event->isostart) ? $event->isostart : null);
		$end = $this->createDateTime(isset($event->isoend) ? $event->isoend : null);

		if ($start === null || $end === null)
			return null;

		$timezone = new DateTimeZone('Europe/Vienna');
		$start->setTimezone($timezone);
		$end->setTimezone($timezone);

		$isReservation = isset($event->type) && $event->type === 'reservierung';
		$subject = $isReservation
			? (isset($event->titel) ? $event->titel : '')
			: (isset($event->lehrfach_bez) ? $event->lehrfach_bez : '');

		if ($subject === '')
			$subject = $this->joinValues(isset($event->topic) ? $event->topic : array());

		$note = isset($event->beschreibung) ? $event->beschreibung : '';
		if ($note === '' && isset($event->titel) && !$isReservation)
			$note = $event->titel;

		return array(
			$start->format('d.m.Y'),
			$start->format('H:i:s'),
			$end->format('H:i:s'),
			$this->joinValues(isset($event->ort_kurzbz) ? $event->ort_kurzbz : array()),
			$this->getLecturers($event),
			$this->getGroups($event),
			$subject,
			$note,
			$this->findHour($start->format('H:i:s'), $hourRaster),
			$this->findHour($end->format('H:i:s'), $hourRaster)
		);
	}

	/**
	 * @param array $rows Workbook rows.
	 * @return string Binary BIFF8 workbook content.
	 */
	private function buildWorkbook(array $rows)
	{
		$tempFile = tempnam(sys_get_temp_dir(), 'lvplan_excel_');
		if ($tempFile === false)
			throw new RuntimeException('Could not create temporary Excel file');

		try
		{
			$workbook = new Spreadsheet_Excel_Writer($tempFile);
			$workbook->setVersion(8);
			$workbook->setTempDir(sys_get_temp_dir());

			$worksheet =& $workbook->addWorksheet(self::WORKSHEET_NAME);
			$worksheet->setInputEncoding('utf-8');

			$boldFormat =& $workbook->addFormat();
			$boldFormat->setBold();

			$maxLengths = array();
			foreach (self::HEADER as $column => $title)
			{
				$worksheet->writeString(0, $column, $title, $boldFormat);
				$maxLengths[$column] = $this->getMaxLineLength($title);
			}

			foreach ($rows as $rowIndex => $row)
			{
				foreach ($row as $column => $value)
				{
					$value = (string)$value;
					$worksheet->writeString($rowIndex + 1, $column, $value);
					$maxLengths[$column] = max(
						$maxLengths[$column],
						$this->getMaxLineLength($value)
					);
				}
			}

			foreach ($maxLengths as $column => $length)
				$worksheet->setColumn($column, $column, min($length + 2, 255));

			$result = $workbook->close();
			if ($result !== true)
				throw new RuntimeException('Could not generate Excel workbook');

			$content = file_get_contents($tempFile);
			if ($content === false)
				throw new RuntimeException('Could not read generated Excel workbook');

			return $content;
		}
		finally
		{
			if (is_file($tempFile))
				unlink($tempFile);
		}
	}

	/**
	 * @param object $event Calendar event.
	 * @return string Lecturer names.
	 */
	private function getLecturers($event)
	{
		$lecturers = array();

		if (isset($event->lektor) && is_array($event->lektor))
		{
			foreach ($event->lektor as $lecturer)
			{
				$name = trim(
					(isset($lecturer['vorname']) ? $lecturer['vorname'] : '').' '
					.(isset($lecturer['nachname']) ? $lecturer['nachname'] : '')
				);

				if ($name === '' && isset($lecturer['kurzbz']))
					$name = $lecturer['kurzbz'];
				if ($name !== '')
					$lecturers[] = $name;
			}
		}

		return implode(', ', array_unique($lecturers));
	}

	/**
	 * @param object $event Calendar event.
	 * @return string Participant group labels.
	 */
	private function getGroups($event)
	{
		$groups = array();

		if (isset($event->gruppe) && is_array($event->gruppe))
		{
			foreach ($event->gruppe as $group)
			{
				if (isset($group['bezeichnung']) && $group['bezeichnung'] !== '')
					$groups[] = $group['bezeichnung'];
			}
		}

		if (isset($event->teilnehmer_gruppe) && is_array($event->teilnehmer_gruppe))
		{
			foreach ($event->teilnehmer_gruppe as $group)
			{
				if (isset($group['gruppe_kurzbz']) && $group['gruppe_kurzbz'] !== '')
					$groups[] = $group['gruppe_kurzbz'];
			}
		}

		return implode(',', array_unique($groups));
	}

	/**
	 * @param string $time Local time.
	 * @param array $hourRaster Timetable-hour raster.
	 * @return string|int Timetable-hour number or an empty string.
	 */
	private function findHour($time, array $hourRaster)
	{
		$seconds = $this->timeToSeconds($time);
		if ($seconds === null || count($hourRaster) === 0)
			return '';

		$firstHour = current($hourRaster);
		$lastHour = end($hourRaster);
		if ($seconds < $firstHour['begin'] || $seconds > $lastHour['end'])
			return '';

		foreach ($hourRaster as $hour)
		{
			if ($seconds <= $hour['end'])
				return $hour['stunde'];
		}

		return '';
	}

	/**
	 * @param string $time Time in HH:MM[:SS] format.
	 * @return int|null Seconds since midnight.
	 */
	private function timeToSeconds($time)
	{
		if (!is_string($time) || !preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $matches))
			return null;

		$hour = (int)$matches[1];
		$minute = (int)$matches[2];
		$second = isset($matches[3]) ? (int)$matches[3] : 0;

		if ($hour > 23 || $minute > 59 || $second > 59)
			return null;

		return $hour * 3600 + $minute * 60 + $second;
	}

	/**
	 * @param mixed $value String or list of strings.
	 * @return string
	 */
	private function joinValues($value)
	{
		return is_array($value) ? implode(' / ', $value) : (string)$value;
	}

	/**
	 * @param string $value Cell content.
	 * @return int Maximum visual line length.
	 */
	private function getMaxLineLength($value)
	{
		$lines = preg_split('/\r\n|\r|\n/', (string)$value);
		$maxLength = 0;

		foreach ($lines as $line)
			$maxLength = max($maxLength, mb_strlen($line));

		return $maxLength;
	}

	/**
	 * @param object $first First event.
	 * @param object $second Second event.
	 * @return int Comparison result.
	 */
	private function compareEventsByStart($first, $second)
	{
		$firstTimestamp = isset($first->isostart) ? strtotime($first->isostart) : false;
		$secondTimestamp = isset($second->isostart) ? strtotime($second->isostart) : false;

		$firstTimestamp = $firstTimestamp === false ? PHP_INT_MAX : $firstTimestamp;
		$secondTimestamp = $secondTimestamp === false ? PHP_INT_MAX : $secondTimestamp;

		if ($firstTimestamp === $secondTimestamp)
			return 0;

		return $firstTimestamp < $secondTimestamp ? -1 : 1;
	}

	/**
	 * @param mixed $dateTime Date/time input.
	 * @return DateTime|null
	 */
	private function createDateTime($dateTime)
	{
		if ($dateTime instanceof DateTime)
			return clone $dateTime;

		if ($dateTime === null || $dateTime === '')
			return null;

		try
		{
			if (is_numeric($dateTime))
				return new DateTime('@'.(int)$dateTime);

			return new DateTime($dateTime);
		}
		catch (Exception $e)
		{
			return null;
		}
	}
}
