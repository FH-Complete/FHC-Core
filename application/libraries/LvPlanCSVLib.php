<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class LvPlanCSVLib
{
	const TARGET_OUTLOOK = 'outlook';

	const GENERIC_HEADER = array(
		'title',
		'category',
		'location',
		'description',
		'keywords',
		'start_date',
		'start_time',
		'end_date',
		'end_time',
		'alarm',
		'recur_type',
		'recur_end_date',
		'recur_interval',
		'recur_data'
	);

	const OUTLOOK_HEADER = array(
		'Betreff',
		'Beginnt am',
		'Beginnt um',
		'Endet am',
		'Endet um',
		'Ganztaegiges Ereignis',
		'Erinnerung Ein/Aus',
		'Erinnerung am',
		'Erinnerung um',
		'Besprechungsplanung',
		'Erforderliche Teilnehmer',
		'Optionale Teilnehmer',
		'Besprechungsressourcen',
		'Abrechnungsinformationen',
		'Beschreibung',
		'Kategorien',
		'Ort',
		'Priorität',
		'Privat',
		'Reisekilometer',
		'Vertraulichkeit',
		'Zeitspanne zeigen als'
	);

	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Builds CSV content for a user's timetable.
	 *
	 * @param string $uid User UID.
	 * @param int $begin First included date as Unix timestamp.
	 * @param int $ende Last included date as Unix timestamp.
	 * @param string $type Timetable type (student, lektor, ort or verband).
	 * @param string|null $target Optional CSV target (outlook).
	 * @param string|null $ortKurzbz Room identifier for room plans.
	 * @param array|null $verbandFilters Lehrverband filters.
	 * @return string
	 */
	public function getContent(
		$uid,
		$begin,
		$ende,
		$type,
		$target = null,
		$ortKurzbz = null,
		$verbandFilters = null
	) {
		if (!in_array($type, array('student', 'lektor', 'ort', 'verband'), true))
			throw new InvalidArgumentException('Unsupported timetable type');
		if ($target !== null && $target !== self::TARGET_OUTLOOK)
			throw new InvalidArgumentException('Unsupported CSV target');
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

		$content = $this->buildCSVLine(
			$target === self::TARGET_OUTLOOK ? self::OUTLOOK_HEADER : self::GENERIC_HEADER
		);

		foreach ($events as $event)
		{
			$eventData = $this->getEventData($event);
			if ($eventData === null)
				continue;

			$content .= $this->buildCSVLine(
				$target === self::TARGET_OUTLOOK
					? $this->buildOutlookRow($eventData)
					: $this->buildGenericRow($eventData)
			);
		}

		return $content;
	}

	/**
	 * @param object $event Calendar event.
	 * @return array|null Normalized CSV values or null for invalid dates.
	 */
	private function getEventData($event)
	{
		$start = $this->createDateTime(isset($event->isostart) ? $event->isostart : null);
		$end = $this->createDateTime(isset($event->isoend) ? $event->isoend : null);

		if ($start === null || $end === null)
			return null;

		$timezone = new DateTimeZone('Europe/Vienna');
		$start->setTimezone($timezone);
		$end->setTimezone($timezone);

		$isReservation = isset($event->type) && $event->type === 'reservierung';
		$title = $isReservation
			? (isset($event->titel) ? $event->titel : '')
			: $this->joinValues(isset($event->topic) ? $event->topic : array());
		$location = $this->joinValues(
			isset($event->ort_kurzbz) ? $event->ort_kurzbz : array(),
			' / '
		);
		$groups = $this->getGroups($event);
		$lecturers = $this->getLecturers($event);

		if ($isReservation)
		{
			$description = isset($event->beschreibung) ? $event->beschreibung : '';
		}
		else
		{
			$descriptionParts = array(
				'Stundenplan',
				isset($event->lehrfach_bez) ? $event->lehrfach_bez : '',
				$lecturers,
				$groups,
				$location,
				isset($event->beschreibung) ? $event->beschreibung : ''
			);
			$description = implode("\r\n", array_values(array_filter(
				$descriptionParts,
				function ($value) {
					return $value !== '';
				}
			)));
		}

		return array(
			'title' => $title,
			'location' => $location,
			'description' => $description,
			'groups' => $groups,
			'start_date' => $start->format('d.m.Y'),
			'start_time' => $start->format('H:i:s'),
			'end_date' => $end->format('d.m.Y'),
			'end_time' => $end->format('H:i:s')
		);
	}

	/**
	 * @param array $eventData Normalized event values.
	 * @return array Generic CSV row.
	 */
	private function buildGenericRow(array $eventData)
	{
		$category = defined('LVPLAN_KATEGORIE') ? LVPLAN_KATEGORIE : 'StundenplanTW';

		return array(
			$eventData['title'],
			$category,
			$eventData['location'],
			$eventData['description'],
			'Stundenplan',
			$eventData['start_date'],
			$eventData['start_time'],
			$eventData['end_date'],
			$eventData['end_time'],
			'',
			'',
			'',
			'',
			''
		);
	}

	/**
	 * @param array $eventData Normalized event values.
	 * @return array Outlook CSV row.
	 */
	private function buildOutlookRow(array $eventData)
	{
		$subject = $eventData['title'];
		if ($eventData['groups'] !== '')
			$subject .= ' - '.$eventData['groups'];

		return array(
			$subject,
			$eventData['start_date'],
			$eventData['start_time'],
			$eventData['end_date'],
			$eventData['end_time'],
			'Aus',
			'Aus',
			'',
			'',
			'',
			'',
			'',
			'',
			'',
			$eventData['description'],
			'StundenplanFH',
			$eventData['location'],
			'Normal',
			'Aus',
			'',
			'Normal',
			'2'
		);
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

		return implode(' / ', array_unique($lecturers));
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

		return implode(' / ', array_unique($groups));
	}

	/**
	 * @param mixed $value String or list of strings.
	 * @param string $separator Join separator.
	 * @return string
	 */
	private function joinValues($value, $separator = ' / ')
	{
		return is_array($value) ? implode($separator, $value) : (string)$value;
	}

	/**
	 * Builds one fully quoted RFC-style CSV row.
	 *
	 * @param array $values Row values.
	 * @return string
	 */
	private function buildCSVLine(array $values)
	{
		$escapedValues = array_map(function ($value) {
			$value = preg_replace('/\r\n|\r|\n/', "\r\n", (string)$value);
			return '"'.str_replace('"', '""', $value).'"';
		}, $values);

		return implode(',', $escapedValues)."\r\n";
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
