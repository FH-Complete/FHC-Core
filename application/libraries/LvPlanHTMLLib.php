<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

class LvPlanHTMLLib
{
	const TIMEZONE = 'Europe/Vienna';

	private $ci;

	public function __construct()
	{
		$this->ci =& get_instance();
	}

	/**
	 * Builds a complete printable timetable document.
	 *
	 * @param int $begin First included date as Unix timestamp.
	 * @param int $ende Last included date as Unix timestamp.
	 * @param string $type Timetable type.
	 * @param array $filters Type-specific filters.
	 * @return string
	 */
	public function getContent($begin, $ende, $type, array $filters)
	{
		if (!in_array($type, array('student', 'lektor', 'verband'), true))
			throw new InvalidArgumentException('Unsupported timetable type');

		$events = $type === 'verband'
			? $this->getVerbandEvents($begin, $ende, $filters)
			: $this->getPersonalEvents($begin, $ende, $type, $filters);
		$hourRaster = $this->loadHourRaster();
		$normalizedEvents = $this->normalizeEvents($events);

		return $this->renderDocument(
			$begin,
			$ende,
			$type,
			$filters,
			$hourRaster,
			$normalizedEvents
		);
	}

	/**
	 * @return array Personal plan events.
	 */
	private function getPersonalEvents($begin, $ende, $type, array $filters)
	{
		if (!isset($filters['uid']) || !is_string($filters['uid']) || $filters['uid'] === '')
			throw new InvalidArgumentException('Missing personal timetable UID');

		$uid = $filters['uid'];
		$isLecturer = $type === 'lektor';
		$this->ci->load->library('KalenderLib', array('uid' => $uid));

		$startDate = $this->formatLocalDate($begin);
		$endDate = $this->formatLocalDate($ende);

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

		return is_array($events) ? $events : array();
	}

	/**
	 * @return array Lehrverband plan events.
	 */
	private function getVerbandEvents($begin, $ende, array $filters)
	{
		foreach (array('stg_kz', 'sem') as $name)
		{
			if (!array_key_exists($name, $filters))
				throw new InvalidArgumentException('Missing Lehrverband filter: '.$name);
		}

		$this->ci->load->library('KalenderLib', array('uid' => getAuthUID()));
		$events = $this->ci->kalenderlib->getPlanForVerband(
			$this->formatLocalDate($begin),
			$this->formatLocalDate($ende),
			$filters['stg_kz'],
			$filters['sem'],
			isset($filters['ver']) ? $filters['ver'] : null,
			isset($filters['grp']) ? $filters['grp'] : null
		);

		return is_array($events) ? $events : array();
	}

	/**
	 * @return array Timetable-hour raster.
	 */
	private function loadHourRaster()
	{
		$this->ci->load->model('ressource/Stunde_model', 'StundeModel');
		$result = $this->ci->StundeModel->execReadOnlyQuery(
			'SELECT stunde, beginn, ende
			FROM lehre.tbl_stunde
			ORDER BY stunde'
		);

		if (isError($result))
			throw new RuntimeException(getError($result));
		if (!hasData($result))
			return array();

		$raster = array();
		foreach (getData($result) as $hour)
		{
			$start = $this->timeToSeconds($hour->beginn);
			$end = $this->timeToSeconds($hour->ende);

			if ($start === null || $end === null || $end <= $start)
				continue;

			$raster[] = array(
				'number' => (string)$hour->stunde,
				'begin' => $start,
				'end' => $end,
				'begin_label' => substr((string)$hour->beginn, 0, 5),
				'end_label' => substr((string)$hour->ende, 0, 5)
			);
		}

		return $raster;
	}

	/**
	 * @param array $events Events from KalenderLib.
	 * @return array
	 */
	private function normalizeEvents(array $events)
	{
		$normalized = array();

		foreach ($events as $event)
		{
			$start = $this->getEventDateTime($event, 'isostart', 'datum', 'beginn');
			$end = $this->getEventDateTime($event, 'isoend', 'datum', 'ende');

			if ($start === null || $end === null || $end <= $start)
				continue;

			$isReservation = isset($event->type) && $event->type === 'reservierung';
			$title = $isReservation
				? $this->getScalar($event, 'titel')
				: $this->joinValue(isset($event->topic) ? $event->topic : '');

			if ($title === '')
				$title = $this->getScalar($event, 'lehrfach_bez');

			$normalized[] = array(
				'start' => $start,
				'end' => $end,
				'date' => $start->format('Y-m-d'),
				'title' => $title,
				'location' => $this->joinValue(
					isset($event->ort_kurzbz) ? $event->ort_kurzbz : '',
					' / '
				),
				'lecturers' => $this->getLecturers($event),
				'groups' => $this->getGroups($event),
				'note' => $this->getEventNote($event, $isReservation),
				'color' => $this->getColor($event)
			);
		}

		usort($normalized, function ($first, $second) {
			return $first['start']->getTimestamp() <=> $second['start']->getTimestamp();
		});

		return $normalized;
	}

	/**
	 * Renders the complete HTML document.
	 */
	private function renderDocument(
		$begin,
		$ende,
		$type,
		array $filters,
		array $hourRaster,
		array $events
	) {
		$timezone = new DateTimeZone(self::TIMEZONE);
		$rangeStart = (new DateTime('@'.$begin))->setTimezone($timezone)->setTime(0, 0, 0);
		$rangeEnd = (new DateTime('@'.$ende))->setTimezone($timezone)->setTime(23, 59, 59);
		$weeks = $this->buildWeeks($rangeStart, $rangeEnd, $hourRaster, $events);
		$title = $this->getDocumentTitle($type, $filters);
		$appRoot = $this->getAppRoot();

		$html = '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		$html .= '<title>'.$this->escape($title).'</title>';
		$html .= '<link rel="stylesheet" href="'.$this->escape($appRoot.'skin/style.css.php').'" type="text/css">';
		$html .= '<link rel="stylesheet" media="print" href="'.$this->escape($appRoot.'skin/cis.css').'" type="text/css">';
		$html .= '<link rel="stylesheet" media="print" href="'.$this->escape($appRoot.'skin/print.css').'" type="text/css">';
		$html .= '<style>'.$this->getStyles().'</style></head>';
		$html .= '<body id="inhalt"><h1>'.$this->escape($title).'</h1>';

		foreach ($weeks as $week)
			$html .= $this->renderWeek($week, $hourRaster, $type);

		if (empty($weeks))
			$html .= '<p>Keine Termine im gewählten Zeitraum.</p>';

		$html .= '</body></html>';
		return $html;
	}

	/**
	 * Builds week/day/cell collections for rendering.
	 */
	private function buildWeeks(DateTime $rangeStart, DateTime $rangeEnd, array $raster, array $events)
	{
		$weeks = array();
		$weekStart = clone $rangeStart;

		while ($weekStart <= $rangeEnd)
		{
			$weekKey = $weekStart->format('Y-m-d');
			$weekEnd = (clone $weekStart)->modify('+6 days');
			$days = array();

			for ($dayOffset = 0; $dayOffset < 7; $dayOffset++)
			{
				$date = (clone $weekStart)->modify('+'.$dayOffset.' days');
				$days[$date->format('Y-m-d')] = array(
					'date' => $date,
					'cells' => array_fill(0, count($raster), array())
				);
			}

			$weeks[$weekKey] = array(
				'start' => clone $weekStart,
				'end' => $weekEnd,
				'days' => $days,
				'outside' => array()
			);

			$weekStart->modify('+7 days');
		}

		foreach ($events as $event)
		{
			$eventWeek = clone $event['start'];
			$eventWeek->modify('monday this week')->setTime(0, 0, 0);
			$weekKey = $eventWeek->format('Y-m-d');
			$dateKey = $event['date'];

			if (!isset($weeks[$weekKey]['days'][$dateKey]))
				continue;

			$matchingSlots = $this->getMatchingSlots($event, $raster);
			if (empty($matchingSlots))
			{
				$weeks[$weekKey]['outside'][] = $event;
				continue;
			}

			foreach ($matchingSlots as $slot)
				$weeks[$weekKey]['days'][$dateKey]['cells'][$slot][] = $event;
		}

		return array_values($weeks);
	}

	/**
	 * @return array Raster indexes overlapping an event.
	 */
	private function getMatchingSlots(array $event, array $raster)
	{
		if ($event['start']->format('Y-m-d') !== $event['end']->format('Y-m-d'))
			return array();

		$start = $this->timeToSeconds($event['start']->format('H:i:s'));
		$end = $this->timeToSeconds($event['end']->format('H:i:s'));
		$matches = array();

		foreach ($raster as $index => $hour)
		{
			if ($start < $hour['end'] && $end > $hour['begin'])
				$matches[] = $index;
		}

		return $matches;
	}

	/**
	 * Renders one printable week.
	 */
	private function renderWeek(array $week, array $raster, $type)
	{
		$weekdayNames = array(
			1 => 'Montag',
			2 => 'Dienstag',
			3 => 'Mittwoch',
			4 => 'Donnerstag',
			5 => 'Freitag',
			6 => 'Samstag',
			7 => 'Sonntag'
		);
		$html = '<div style="padding-top: 10px;" class="page-break-after">';
		$html .= '<table class="stdplan" width="100%" border="0" cellpadding="1" cellspacing="1" name="Stundenplantabelle" align="center">';
		$html .= '<thead><tr>';
		$html .= '<th align="right">Stunde&nbsp;<br>Beginn&nbsp;<br>Ende&nbsp;</th>';

		foreach ($raster as $hour)
		{
			$html .= '<th><div align="center">'.$this->escape($hour['number']).'<br>';
			$html .= '&nbsp;'.$this->escape($hour['begin_label']).'&nbsp;<br>';
			$html .= '&nbsp;'.$this->escape($hour['end_label']).'&nbsp;</div></th>';
		}

		$html .= '</tr></thead><tbody>';

		foreach ($week['days'] as $day)
		{
			$weekday = (int)$day['date']->format('N');
			$html .= '<tr><td>'.$weekdayNames[$weekday].'<br>';
			$html .= $day['date']->format('d.m.Y').'<br></td>';

			foreach ($day['cells'] as $events)
			{
				if (empty($events))
				{
					$html .= '<td valign="center" align="center"></td>';
					continue;
				}

				$html .= '<td nowrap valign="top" align="center">';
				foreach ($events as $event)
					$html .= $this->renderEvent($event, $type, false);
				$html .= '</td>';
			}

			$html .= '</tr>';
		}

		$html .= '</tbody></table>';

		if (!empty($week['outside']))
		{
			$html .= '<div class="outside"><h3>Termine außerhalb des Stundenrasters</h3>';
			foreach ($week['outside'] as $event)
				$html .= $this->renderEvent($event, $type, true);
			$html .= '</div>';
		}

		return $html.'</div>';
	}

	/**
	 * Renders an escaped event card.
	 */
	private function renderEvent(array $event, $type, $showDate)
	{
		$style = $event['color'] !== ''
			? ' style="background-color: #'.$event['color'].'; margin-bottom: 3px;"'
			: '';
		$html = '<div align="center"'.$style.'>';
		$html .= '<a class="stpl_detail" title="'.$this->escape($event['note']).'">';

		if ($showDate)
			$html .= $event['start']->format('d.m.Y H:i').'–'.$event['end']->format('H:i').'<br>';

		$html .= $this->escape($event['title']);
		if ($event['note'] !== '')
		{
			$html .= '<img src="'.$this->escape($this->getAppRoot().'skin/images/sticky.png').'"';
			$html .= ' title="'.$this->escape($event['note']).'" alt="">';
		}

		if (($type === 'lektor' || $type === 'verband') && $event['groups'] !== '')
			$html .= '<br>'.$this->escape($event['groups']);
		if ($type !== 'lektor' && $event['lecturers'] !== '')
			$html .= '<br>'.$this->renderLines($event['lecturers']);
		if ($event['location'] !== '')
			$html .= '<br>'.$this->escape($event['location']);
		if ($event['note'] !== '' && $this->shouldShowNotes())
			$html .= '<br>'.$this->escape($event['note']);

		return $html.'</a></div>';
	}

	private function getDocumentTitle($type, array $filters)
	{
		if ($type === 'verband')
		{
			$title = 'Lehrverband: '.$filters['stg_kz'].'-'.$filters['sem'];
			if (isset($filters['ver']) && $filters['ver'] !== null)
				$title .= $filters['ver'];
			if (isset($filters['grp']) && $filters['grp'] !== null)
				$title .= $filters['grp'];
			return $title;
		}

		return ($type === 'lektor' ? 'Lektor: ' : 'Student: ').$filters['uid'];
	}

	private function getEventDateTime($event, $isoProperty, $dateProperty, $timeProperty)
	{
		$value = isset($event->{$isoProperty}) ? $event->{$isoProperty} : null;
		if (($value === null || $value === '')
			&& isset($event->{$dateProperty})
			&& isset($event->{$timeProperty}))
		{
			$value = $event->{$dateProperty}.' '.$event->{$timeProperty};
		}

		if ($value === null || $value === '')
			return null;

		try
		{
			$date = is_numeric($value) ? new DateTime('@'.(int)$value) : new DateTime($value);
			$date->setTimezone(new DateTimeZone(self::TIMEZONE));
			return $date;
		}
		catch (Exception $e)
		{
			return null;
		}
	}

	private function getLecturers($event)
	{
		$values = array();
		if (!isset($event->lektor) || !is_array($event->lektor))
			return '';

		foreach ($event->lektor as $lecturer)
		{
			$value = $this->getNestedValue($lecturer, array('kurzbz', 'mitarbeiter_uid'));
			if ($value !== '')
				$values[] = $value;
		}

		return implode(', ', array_unique($values));
	}

	private function getGroups($event)
	{
		$values = array();
		foreach (array('gruppe', 'teilnehmer_gruppe') as $property)
		{
			if (!isset($event->{$property}) || !is_array($event->{$property}))
				continue;

			foreach ($event->{$property} as $group)
			{
				$value = $this->getNestedValue(
					$group,
					array('bezeichnung', 'kuerzel', 'gruppe_kurzbz')
				);
				if ($value !== '')
					$values[] = $value;
			}
		}

		return implode(', ', array_unique($values));
	}

	private function getEventNote($event, $isReservation)
	{
		$note = $this->getScalar($event, 'beschreibung');
		if ($note === '')
			$note = $this->getScalar($event, 'anmerkung');
		if ($note === '' && !$isReservation)
			$note = $this->getScalar($event, 'titel');
		return $note;
	}

	private function getColor($event)
	{
		$color = ltrim($this->getScalar($event, 'farbe'), '#');
		return preg_match('/^[0-9a-fA-F]{6}$/', $color) ? strtoupper($color) : '';
	}

	private function getNestedValue($value, array $properties)
	{
		foreach ($properties as $property)
		{
			if (is_array($value) && isset($value[$property]))
				return (string)$value[$property];
			if (is_object($value) && isset($value->{$property}))
				return (string)$value->{$property};
		}

		return is_scalar($value) ? (string)$value : '';
	}

	private function getScalar($event, $property)
	{
		return isset($event->{$property}) && is_scalar($event->{$property})
			? (string)$event->{$property}
			: '';
	}

	private function joinValue($value, $separator = ', ')
	{
		if (!is_array($value))
			return is_scalar($value) ? (string)$value : '';

		$values = array();
		foreach ($value as $item)
		{
			if (is_scalar($item) && (string)$item !== '')
				$values[] = (string)$item;
		}

		return implode($separator, array_unique($values));
	}

	private function timeToSeconds($time)
	{
		if (!preg_match('/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', (string)$time, $matches))
			return null;

		$hours = (int)$matches[1];
		$minutes = (int)$matches[2];
		$seconds = isset($matches[3]) ? (int)$matches[3] : 0;

		if ($hours > 23 || $minutes > 59 || $seconds > 59)
			return null;

		return $hours * 3600 + $minutes * 60 + $seconds;
	}

	private function formatLocalDate($timestamp)
	{
		$date = new DateTime('@'.(int)$timestamp);
		$date->setTimezone(new DateTimeZone(self::TIMEZONE));
		return $date->format('Y-m-d');
	}

	private function escape($value)
	{
		return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function renderLines($value)
	{
		$lines = array_map('trim', explode(', ', (string)$value));
		$lines = array_filter($lines, function ($line) {
			return $line !== '';
		});
		$lines = array_map(array($this, 'escape'), $lines);

		return implode('<br>', $lines);
	}

	private function getAppRoot()
	{
		return defined('APP_ROOT') ? rtrim(APP_ROOT, '/').'/' : '/';
	}

	private function shouldShowNotes()
	{
		return !defined('LVPLAN_ANMERKUNG_ANZEIGEN') || LVPLAN_ANMERKUNG_ANZEIGEN;
	}

	private function getStyles()
	{
		return '@page{size:landscape;margin:10mm}'
			.'.outside{margin-top:10px}.outside>div{display:inline-block;vertical-align:top;min-width:180px;margin:0 3px 3px 0;padding:3px}'
			.'@media print{.outside>div{break-inside:avoid}}';
	}
}
