<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class LvPlanICALLib
{
	const CATEGORY_TIMETABLE = 'Stundenplan';
	const CATEGORY_EXAM = 'StundenplanExam';
	const CATEGORY_REMOTE = 'StundenplanRemote';

	const ICON_EXAM = '📝';
	const ICON_REMOTE = '📍';
	const ICON_RESERVATION = '📌';

	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Builds complete iCalendar content for a user.
	 *
	 * If no type is given, the user's role is inferred for subscription feeds.
	 * Downloads pass the type explicitly so dual-role users receive the selected
	 * timetable.
	 *
	 * @param string $uid User UID.
	 * @param int $begin First included date as Unix timestamp.
	 * @param int $ende Last included date as Unix timestamp.
	 * @param string|null $type Timetable type (student, lektor, ort or verband).
	 * @param int $version iCalendar major version (1 or 2).
	 * @param string|null $ortKurzbz Room identifier for room plans.
	 * @param array|null $verbandFilters Lehrverband filters.
	 * @param string|null $calendarName Display name for calendar subscriptions.
	 * @return string
	 */
	public function getContent(
		$uid,
		$begin,
		$ende,
		$type = null,
		$version = 2,
		$ortKurzbz = null,
		$verbandFilters = null,
		$calendarName = null
	) {
		if (!in_array($version, array(1, 2), true))
			throw new InvalidArgumentException('Unsupported iCalendar version');
		if ($type !== null && !in_array($type, array('student', 'lektor', 'ort', 'verband'), true))
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

		$this->ci->load->library('KalenderLib', array('uid' => $uid));

		if ($type === null)
		{
			$this->ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
			$isLecturerResult = $this->ci->MitarbeiterModel->isMitarbeiter($uid);
			$isLecturer = isSuccess($isLecturerResult) && getData($isLecturerResult) === true;
		}
		else
		{
			$isLecturer = $type === 'lektor';
		}

		$startDate = date('Y-m-d', $begin);
		// KalenderLib uses an exclusive end date; $ende is inclusive here.
		$endDate = date('Y-m-d', strtotime('+1 day', $ende));

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

		$content = $this->buildCalendarHeader($version, $calendarName);

		foreach ($events as $event)
			$content .= $this->buildEvent($event);

		return $content.$this->buildICalLine('END', 'VCALENDAR');
	}

	private function buildCalendarHeader($version, $calendarName = null)
	{
		$productId = defined('CAMPUS_NAME') && CAMPUS_NAME !== ''
			? CAMPUS_NAME
			: 'FHComplete';

		$header = $this->buildICalLine('BEGIN', 'VCALENDAR')
			.$this->buildICalLine('VERSION', $version.'.0')
			.$this->buildICalTextLine('PRODID', $productId);

		if (is_string($calendarName) && $calendarName !== '')
		{
			$header .= $this->buildICalTextLine('NAME', $calendarName);
			$header .= $this->buildICalTextLine('X-WR-CALNAME', $calendarName);
		}

		return $header
			.$this->buildICalLine('BEGIN', 'VTIMEZONE')
			.$this->buildICalLine('TZID', 'Europe/Vienna')
			.$this->buildICalLine('BEGIN', 'DAYLIGHT')
			.$this->buildICalLine('TZOFFSETFROM', '+0100')
			.$this->buildICalLine('RRULE', 'FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU')
			.$this->buildICalLine('DTSTART', '19810329T020000')
			.$this->buildICalLine('TZNAME', 'GMT+02:00')
			.$this->buildICalLine('TZOFFSETTO', '+0200')
			.$this->buildICalLine('END', 'DAYLIGHT')
			.$this->buildICalLine('BEGIN', 'STANDARD')
			.$this->buildICalLine('TZOFFSETFROM', '+0200')
			.$this->buildICalLine('RRULE', 'FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU')
			.$this->buildICalLine('DTSTART', '19961027T030000')
			.$this->buildICalLine('TZNAME', 'GMT+01:00')
			.$this->buildICalLine('TZOFFSETTO', '+0100')
			.$this->buildICalLine('END', 'STANDARD')
			.$this->buildICalLine('END', 'VTIMEZONE');
	}

	private function buildEvent($event)
	{
		$isReservation = $event->type === 'reservierung';

		$summaryIcon = '';
		$summary = $isReservation
			? implode(' - ', array_filter(array($event->titel, implode('/', $event->ort_kurzbz), $this->getGroups($event))))
			: $this->joinValues($event->topic);

		$descriptionParts = array(
			$event->titel,
			isset($event->lehrfach_bez) ? $event->lehrfach_bez : '',
			$isReservation ? $this->getParticipants($event) : $this->getLecturers($event),
			$this->getGroups($event),
			implode('/', $event->ort_kurzbz),
			isset($event->beschreibung) ? $event->beschreibung : ''
		);
		$description = implode("\r\n", array_values(array_filter(
			$descriptionParts,
			function ($value) {
				return $value !== '';
			}
		)));

		$category = self::CATEGORY_TIMETABLE;
		if ($event->type === 'lehreinheit')
		{
			if ($event->lehrform === 'EXAM')
			{
				$summaryIcon = self::ICON_EXAM;
				$category = self::CATEGORY_EXAM;
			}
			elseif (!isset($event->ko_ort_kurzbz) || $event->ko_ort_kurzbz === '')
			{
				$summaryIcon = self::ICON_REMOTE;
				$category = self::CATEGORY_REMOTE;
			}
		}
		elseif ($event->type === 'reservierung'
			&& (!isset($event->ko_ort_kurzbz) || $event->ko_ort_kurzbz === ''))
		{
			$summaryIcon = self::ICON_RESERVATION;
			$category = self::CATEGORY_REMOTE;
		}

		if ($summaryIcon !== '')
			$summary = $summaryIcon.' '.$summary;

		$startDate = $this->formatICalLocalDateTime($event->isostart);
		$endDate = $this->formatICalLocalDateTime($event->isoend);
		$lastModified = $this->formatICalUtcDateTime(
			isset($event->updateamum) ? $event->updateamum : null
		);
		$dtStamp = $lastModified ?: $this->formatICalUtcDateTime($event->isostart);

		if ($dtStamp === null)
			$dtStamp = '19700101T000000Z';

		$fragment = $this->buildICalLine('BEGIN', 'VEVENT')
			.$this->buildICalTextLine('UID', $event->eindeutige_gruppen_id)
			.$this->buildICalLine('SEQUENCE', (int)$event->kalender_id)
			.$this->buildICalTextLine('SUMMARY', $summary)
			.$this->buildICalTextLine('DESCRIPTION', $description)
			.$this->buildICalTextLine('LOCATION', $this->joinValues($event->ort_kurzbz))
			.$this->buildICalTextLine('CATEGORIES', $category)
			.$this->buildICalLine('DTSTART', $startDate, array('TZID' => 'Europe/Vienna'))
			.$this->buildICalLine('DTEND', $endDate, array('TZID' => 'Europe/Vienna'));

		if ($lastModified !== null)
			$fragment .= $this->buildICalLine('LAST-MODIFIED', $lastModified);

		return $fragment
			.$this->buildICalLine('DTSTAMP', $dtStamp)
			.$this->buildICalLine('END', 'VEVENT');
	}

	private function joinValues($value)
	{
		return is_array($value) ? implode(', ', $value) : (string)$value;
	}

	private function buildICalTextLine($name, $value, array $parameters = array())
	{
		return $this->buildICalLine($name, $this->escapeICalText($value), $parameters);
	}

	private function buildICalLine($name, $value, array $parameters = array())
	{
		$line = strtoupper($name);

		foreach ($parameters as $parameterName => $parameterValue)
		{
			$parameterValues = is_array($parameterValue) ? $parameterValue : array($parameterValue);
			$escapedValues = array();

			foreach ($parameterValues as $singleParameterValue)
				$escapedValues[] = $this->escapeICalParameterValue($singleParameterValue);

			$line .= ';'.strtoupper($parameterName).'='.implode(',', $escapedValues);
		}

		return $this->foldICalLine($line.':'.(string)$value);
	}

	private function foldICalLine($line)
	{
		$line = (string)$line;
		$folded = '';

		while (strlen($line) > 75)
		{
			$part = mb_strcut($line, 0, 75, 'UTF-8');
			$folded .= $part."\r\n";
			$line = ' '.mb_strcut($line, strlen($part), strlen($line) - strlen($part), 'UTF-8');
		}

		return $folded.$line."\r\n";
	}

	private function escapeICalText($value)
	{
		$value = (string)$value;
		$value = str_replace('\\', '\\\\', $value);
		$value = str_replace(array("\r\n", "\r", "\n"), '\\n', $value);
		return str_replace(array(';', ','), array('\\;', '\\,'), $value);
	}

	private function escapeICalParameterValue($value)
	{
		$value = (string)$value;
		$value = str_replace(array('\\', '"', "\r", "\n"), array('\\\\', '\\"', '', ''), $value);

		if (preg_match('/[;:,]/', $value))
			return '"'.$value.'"';

		return $value;
	}

	private function formatICalLocalDateTime($dateTime)
	{
		$dateTime = $this->createDateTime($dateTime);
		if ($dateTime === null)
			return null;

		$dateTime->setTimezone(new DateTimeZone('Europe/Vienna'));
		return $dateTime->format('Ymd\THis');
	}

	private function formatICalUtcDateTime($dateTime)
	{
		$dateTime = $this->createDateTime($dateTime);
		if ($dateTime === null)
			return null;

		$dateTime->setTimezone(new DateTimeZone('UTC'));
		return $dateTime->format('Ymd\THis\Z');
	}

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

		sort($groups, SORT_NATURAL | SORT_FLAG_CASE);

		return implode(',', array_unique($groups));
	}

	/**
	 * @param object $event Calendar event.
	 * @return string Lecturer abbreviations.
	 */
	private function getLecturers($event)
	{
		$lecturers = array();

		if (isset($event->lektor) && is_array($event->lektor))
		{
			foreach ($event->lektor as $lecturer)
			{
				if (isset($lecturer['kurzbz']) && $lecturer['kurzbz'] !== '')
					$lecturers[] = $lecturer['kurzbz'];
			}
		}

		sort($lecturers, SORT_NATURAL | SORT_FLAG_CASE);

		return implode(' / ', array_unique($lecturers));
	}

	private function getParticipants($event)
	{
		$values = array();
		if (!isset($event->teilnehmer_person) || !is_array($event->teilnehmer_person))
			return '';

		foreach ($event->teilnehmer_person as $participant)
		{
			$value = $participant['vorname'] . ' ' . $participant['nachname'];

			if ($value !== '')
				$values[] = $value;
		}

		sort($values, SORT_NATURAL | SORT_FLAG_CASE);

		return implode(', ', array_unique($values));
	}
}
