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
		$this->ci->load->model('ressource/Mitarbeiter_model', 'MitarbeiterModel');
	}

	/**
	 * Builds the complete iCalendar subscription content for a user.
	 *
	 * @param string $uid Authenticated user UID.
	 * @param int $begin First included date as Unix timestamp.
	 * @param int $ende Last included date as Unix timestamp.
	 * @return string
	 */
	public function getContent($uid, $begin, $ende)
	{
		$this->ci->load->library('KalenderLib', array('uid' => $uid));

		$isLecturerResult = $this->ci->MitarbeiterModel->isMitarbeiter($uid);
		$isLecturer = isSuccess($isLecturerResult) && getData($isLecturerResult) === true;

		$startDate = date('Y-m-d', $begin);
		$endDate = date('Y-m-d', $ende);

		if ($isLecturer)
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

		$content = $this->buildCalendarHeader();

		foreach ($events as $event)
			$content .= $this->buildEvent($event);

		return $content.$this->buildICalLine('END', 'VCALENDAR');
	}

	private function buildCalendarHeader()
	{
		$productId = defined('CAMPUS_NAME') && CAMPUS_NAME !== ''
			? CAMPUS_NAME
			: 'FHComplete';

		return $this->buildICalLine('BEGIN', 'VCALENDAR')
			.$this->buildICalLine('VERSION', '2.0')
			.$this->buildICalTextLine('PRODID', $productId)
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
		$summaryIcon = '';
		$summary = $event->type === 'reservierung'
			? $event->titel
			: $this->joinValues($event->topic);

		if ($event->type === 'reservierung')
		{
			$description = $event->beschreibung;
		}
		else
		{
			$description = $event->lehrfach_bez."\r\n";

			if (isset($event->lektor) && is_array($event->lektor) && count($event->lektor) > 0)
			{
				$description .= 'Lektor*in: '.implode(', ', array_map(function ($teacher) {
					return $teacher['kurzbz'];
				}, $event->lektor))."\r\n";
			}
		}

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
}
