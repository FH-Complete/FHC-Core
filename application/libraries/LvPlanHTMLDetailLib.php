<?php

if (!defined('BASEPATH'))
	exit('No direct script access allowed');

require_once(FHCPATH.'include/functions.inc.php');
require_once(FHCPATH.'include/phrasen.class.php');

class LvPlanHTMLDetailLib
{
	const TIMEZONE = 'Europe/Vienna';

	private $ci;
	private $legacyPhrases;

	public function __construct()
	{
		$this->ci =& get_instance();
		$this->legacyPhrases = new phrasen(getSprache());
	}

	/**
	 * Builds a timetable detail document for one date and hour.
	 *
	 * @param string $datum Date in YYYY-MM-DD format.
	 * @param array $hour Selected timetable hour.
	 * @param string $type Timetable type.
	 * @param array $filters Type-specific filters.
	 * @return string
	 */
	public function getContent($datum, array $hour, $type, array $filters)
	{
		if (!in_array($type, array('student', 'lektor', 'verband', 'ort'), true))
			throw new InvalidArgumentException('Unsupported timetable type');
		foreach (array('number', 'begin', 'end') as $property)
		{
			if (!isset($hour[$property]) || !is_scalar($hour[$property]))
				throw new InvalidArgumentException('Invalid timetable hour');
		}

		$events = $this->loadEvents($datum, $type, $filters);
		$events = $this->filterEventsByHour($events, $datum, $hour);
		if ($type === 'verband'
			&& isset($filters['ort_kurzbz'])
			&& $filters['ort_kurzbz'] !== '')
		{
			$events = $this->filterEventsByRoom(
				$events,
				$filters['ort_kurzbz']
			);
		}
		$sections = array(
			'teaching' => array(),
			'reservations' => array(),
			'events' => array()
		);

		foreach ($events as $event)
		{
			if ($event->type === 'lehreinheit')
				$sections['teaching'][] = $event;
			elseif ($event->type === 'reservierung')
				$sections['reservations'][] = $event;
			else
				$sections['events'][] = $event;
		}

		return $this->renderDocument($datum, $hour, $sections);
	}

	/**
	 * Loads the same published calendar data used by the HTML preview.
	 *
	 * @return array
	 */
	private function loadEvents($datum, $type, array $filters)
	{
		$uid = isset($filters['uid']) && is_string($filters['uid'])
			? $filters['uid']
			: getAuthUID();
		$this->ci->load->library('KalenderLib', array('uid' => $uid));

		if ($type === 'verband')
		{
			$events = $this->ci->kalenderlib->getPlanForVerband(
				$datum,
				$datum,
				$filters['stg_kz'],
				$filters['sem'],
				isset($filters['ver']) ? $filters['ver'] : null,
				isset($filters['grp']) ? $filters['grp'] : null
			);
		}
		elseif ($type === 'ort')
		{
			$events = $this->ci->kalenderlib->getPlanForRoom(
				$datum,
				$datum,
				$filters['ort_kurzbz']
			);
		}
		elseif ($type === 'lektor')
		{
			$events = $this->ci->kalenderlib->getPlanForLecturerByLecturer(
				$datum,
				$datum,
				$filters['uid']
			);
		}
		else
		{
			$events = $this->ci->kalenderlib->getPlanForStudentByStudent(
				$datum,
				$datum,
				$filters['uid']
			);
		}

		return is_array($events) ? $events : array();
	}

	/**
	 * Keeps events that overlap the selected timetable hour.
	 *
	 * @return array
	 */
	private function filterEventsByHour(array $events, $datum, array $hour)
	{
		$timezone = new DateTimeZone(self::TIMEZONE);
		$hourStart = DateTime::createFromFormat(
			'!Y-m-d H:i:s',
			$datum.' '.$this->normalizeTime($hour['begin']),
			$timezone
		);
		$hourEnd = DateTime::createFromFormat(
			'!Y-m-d H:i:s',
			$datum.' '.$this->normalizeTime($hour['end']),
			$timezone
		);

		if ($hourStart === false || $hourEnd === false || $hourEnd <= $hourStart)
			throw new InvalidArgumentException('Invalid timetable hour range');

		$matches = array();
		foreach ($events as $event)
		{
			$start = $this->getEventDateTime($event, 'isostart', 'datum', 'beginn');
			$end = $this->getEventDateTime($event, 'isoend', 'datum', 'ende');

			if ($start === null || $end === null || $end <= $start)
				continue;
			if ($start < $hourEnd && $end > $hourStart)
			{
				$matches[] = array(
					'event' => $event,
					'start' => $start,
					'end' => $end
				);
			}
		}

		usort($matches, function ($first, $second) {
			$comparison = $first['start']->getTimestamp()
				<=> $second['start']->getTimestamp();
			if ($comparison !== 0)
				return $comparison;

			$firstId = isset($first['event']->kalender_id)
				? (int)$first['event']->kalender_id
				: 0;
			$secondId = isset($second['event']->kalender_id)
				? (int)$second['event']->kalender_id
				: 0;
			return $firstId <=> $secondId;
		});

		return array_map(function ($match) {
			return $match['event'];
		}, $matches);
	}

	/**
	 * Keeps Verband events assigned to the room selected in the preview cell.
	 *
	 * @return array
	 */
	private function filterEventsByRoom(array $events, $ortKurzbz)
	{
		$matches = array();
		foreach ($events as $event)
		{
			$rooms = isset($event->ort_kurzbz)
				? $event->ort_kurzbz
				: array();
			if (!is_array($rooms))
				$rooms = array($rooms);

			foreach ($rooms as $room)
			{
				if (is_scalar($room) && (string)$room === (string)$ortKurzbz)
				{
					$matches[] = $event;
					continue 2;
				}
			}

			if (!isset($event->ort_details) || !is_array($event->ort_details))
				continue;

			foreach ($event->ort_details as $room)
			{
				if ($this->getNestedValue($room, 'ort_kurzbz') === (string)$ortKurzbz)
				{
					$matches[] = $event;
					continue 2;
				}
			}
		}

		return $matches;
	}

	/**
	 * Renders the complete HTML document.
	 */
	private function renderDocument($datum, array $hour, array $sections)
	{
		$title = $this->phrase('lvplan', 'lehrveranstaltungsplanDetails');
		$html = '<!DOCTYPE html><html lang="de"><head><meta charset="UTF-8">';
		$html .= '<meta name="viewport" content="width=device-width, initial-scale=1">';
		$html .= '<title>'.$this->escape($title).'</title>';
		$html .= '<link rel="stylesheet" href="'
			.$this->escape($this->getAppRoot().'skin/style.css.php')
			.'" type="text/css">';
		$html .= '<style>'
			.'.stdplan td{vertical-align:top}.detail-lines{white-space:normal}'
			.'.detail-empty{color:#666}.stdplan{margin-bottom:1em}'
			.'</style></head><body id="inhalt">';
		$html .= '<h2>'.$this->escape($this->phrase('lvplan', 'lehrveranstaltungsplan'))
			.' &rArr; '.$this->escape($this->phrase('abgabetool', 'details')).'</h2>';
		$html .= $this->escape($this->phrase('abgabetool', 'datum')).': '
			.$this->escape($this->formatDate($datum)).'<br>';
		$html .= $this->escape($this->phrase('global', 'stunde')).': '
			.$this->escape($hour['number']).' ('
			.$this->escape(substr((string)$hour['begin'], 0, 5)).'–'
			.$this->escape(substr((string)$hour['end'], 0, 5)).')<br><br>';

		if (!empty($sections['teaching']))
			$html .= $this->renderTeachingTable($sections['teaching']);
		if (!empty($sections['reservations']))
			$html .= $this->renderReservationTable($sections['reservations']);
		if (!empty($sections['events']))
			$html .= $this->renderEventTable($sections['events']);

		if (empty($sections['teaching'])
			&& empty($sections['reservations'])
			&& empty($sections['events']))
		{
			$html .= '<p class="detail-empty">0 '
				.$this->escape($this->phrase('global', 'termine')).'</p>';
		}

		if (defined('MAIL_LVPLAN'))
		{
			$html .= '<p>'.$this->phrase(
				'lvplan',
				'FragenZuLvPlan',
				array(constant('MAIL_LVPLAN'))
			).'</p>';
		}

		return $html.'</body></html>';
	}

	/**
	 * Renders teaching-unit details.
	 */
	private function renderTeachingTable(array $events)
	{
		$html = '<table class="stdplan"><thead><tr>';
		foreach (array(
			array('lvplan', 'unr'),
			array('lvaliste', 'lektor'),
			array('lvplan', 'ort'),
			array('lvaliste', 'lehrfach'),
			array('global', 'bezeichnung'),
			array('global', 'verband'),
			array('lvplan', 'einheit'),
			array('lvplan', 'info')
		) as $heading)
		{
			$html .= '<th>'.$this->escape($this->phrase($heading[0], $heading[1])).'</th>';
		}
		$html .= '</tr></thead><tbody>';

		foreach ($events as $index => $event)
		{
			$html .= '<tr class="liste'.(($index + 1) % 2).'">';
			$html .= $this->cell($this->renderScalarLines(
				isset($event->unr) ? $event->unr : array()
			));
			$html .= $this->cell($this->renderPeople(
				isset($event->lektor) ? $event->lektor : array(),
				'profile'
			));
			$html .= $this->cell($this->renderRooms($event));
			$html .= $this->cell($this->renderSubjectValues($event, 'kurzbz'));
			$html .= $this->cell($this->renderSubjectValues($event, 'bezeichnung'));
			$html .= $this->cell($this->renderTeachingGroups($event, false));
			$html .= $this->cell($this->renderTeachingGroups($event, true));
			$html .= $this->cell($this->renderOldTempusNoticeTagNote($event));
			$html .= '</tr>';
		}

		return $html.'</tbody></table>';
	}

	/**
	 * Renders reservation details.
	 */
	private function renderReservationTable(array $events)
	{
		$html = '<h2>'.$this->escape($this->phrase('lvplan', 'reservierungen')).'</h2>';
		$html .= '<table class="stdplan"><thead><tr>';
		foreach (array(
			array('global', 'titel'),
			array('lvplan', 'ort'),
			array('global', 'person'),
			array('global', 'beschreibung'),
			array('lvplan', 'reserviertVon')
		) as $heading)
		{
			$html .= '<th>'.$this->escape($this->phrase($heading[0], $heading[1])).'</th>';
		}
		$html .= '</tr></thead><tbody>';

		foreach ($events as $index => $event)
		{
			$html .= '<tr class="liste'.(($index + 1) % 2).'">';
			$html .= $this->cell($this->escape($this->getScalar($event, 'titel')));
			$html .= $this->cell($this->renderRooms($event));
			$html .= $this->cell($this->renderParticipants($event));
			$html .= $this->cell($this->renderText(
				$this->getScalar($event, 'beschreibung')
			));
			$html .= $this->cell($this->renderCreator($event));
			$html .= '</tr>';
		}

		return $html.'</tbody></table>';
	}

	/**
	 * Renders non-teaching, non-reservation calendar events.
	 */
	private function renderEventTable(array $events)
	{
		$html = '<h2>'.$this->escape($this->phrase('global', 'termine')).'</h2>';
		$html .= '<table class="stdplan"><thead><tr>';
		foreach (array(
			array('global', 'titel'),
			array('lvplan', 'ort'),
			array('ui', 'organisierende'),
			array('ui', 'teilnehmende'),
			array('global', 'beschreibung')
		) as $heading)
		{
			$html .= '<th>'.$this->escape($this->phrase($heading[0], $heading[1])).'</th>';
		}
		$html .= '</tr></thead><tbody>';

		foreach ($events as $index => $event)
		{
			$html .= '<tr class="liste'.(($index + 1) % 2).'">';
			$html .= $this->cell($this->escape($this->getEventTitle($event)));
			$html .= $this->cell($this->renderRooms($event));
			$html .= $this->cell($this->renderPeople(
				isset($event->lektor) ? $event->lektor : array(),
				'mail'
			));
			$html .= $this->cell($this->renderParticipants($event));
			$html .= $this->cell($this->renderText(
				$this->getScalar($event, 'beschreibung')
			));
			$html .= '</tr>';
		}

		return $html.'</tbody></table>';
	}

	private function renderSubjectValues($event, $property)
	{
		$values = array();
		if (isset($event->lehrfach_details) && is_array($event->lehrfach_details))
		{
			foreach ($event->lehrfach_details as $subject)
			{
				$value = $this->getNestedValue($subject, $property);
				if ($value !== '')
					$values[] = $value;
			}
		}

		if (empty($values))
		{
			$fallback = $property === 'kurzbz'
				? $this->getScalar($event, 'lehrfach')
				: $this->getScalar($event, 'lehrfach_bez');
			if ($fallback !== '')
				$values[] = $fallback;
		}

		return $this->renderScalarLines($values);
	}

	private function renderTeachingGroups($event, $specialGroups)
	{
		$values = array();
		if (isset($event->gruppe) && is_array($event->gruppe))
		{
			foreach ($event->gruppe as $group)
			{
				$groupCode = $this->getNestedValue($group, 'gruppe_kurzbz');
				if ($specialGroups && $groupCode !== '')
					$values[] = $groupCode;
				elseif (!$specialGroups && $groupCode === '')
				{
					$designation = $this->getNestedValue($group, 'bezeichnung');
					if ($designation !== '')
						$values[] = $designation;
				}
			}
		}

		return $this->renderGroupLinks($values);
	}

	private function renderParticipants($event)
	{
		$parts = array();
		$people = $this->renderPeople(
			isset($event->teilnehmer_person) ? $event->teilnehmer_person : array(),
			'mail'
		);
		if ($people !== '')
			$parts[] = $people;

		$groups = array();
		if (isset($event->teilnehmer_gruppe) && is_array($event->teilnehmer_gruppe))
		{
			foreach ($event->teilnehmer_gruppe as $group)
			{
				$value = $this->getNestedValue($group, 'bezeichnung');
				if ($value === '')
					$value = $this->getNestedValue($group, 'gruppe_kurzbz');
				if ($value !== '')
					$groups[] = $value;
			}
		}
		$groupLinks = $this->renderGroupLinks($groups);
		if ($groupLinks !== '')
			$parts[] = $groupLinks;

		return implode('<br>', $parts);
	}

	private function renderCreator($event)
	{
		if (isset($event->created_by) && (is_array($event->created_by) || is_object($event->created_by)))
			return $this->renderPeople(array($event->created_by), 'plain');

		return $this->renderPeople(
			isset($event->lektor) ? $event->lektor : array(),
			'plain'
		);
	}

	private function renderPeople(array $people, $linkType)
	{
		$values = array();
		$seen = array();
		foreach ($people as $person)
		{
			$uid = $this->getNestedValue($person, 'mitarbeiter_uid');
			if ($uid === '')
				$uid = $this->getNestedValue($person, 'uid');

			$nameParts = array();
			foreach (array('titelpre', 'vorname', 'nachname', 'titelpost') as $property)
			{
				$value = trim($this->getNestedValue($person, $property));
				if ($value !== '')
					$nameParts[] = $value;
			}
			$name = implode(' ', $nameParts);
			if ($name === '')
				$name = $uid !== '' ? $uid : $this->getNestedValue($person, 'kurzbz');
			if ($name === '')
				continue;

			$key = $uid !== '' ? $uid : $name;
			if (isset($seen[$key]))
				continue;
			$seen[$key] = true;

			$label = $this->escape($name);
			if ($linkType === 'profile' && $uid !== '')
			{
				$personalNumber = $this->getNestedValue($person, 'personalnummer');
				$profileAllowed = $personalNumber === ''
					|| !is_numeric($personalNumber)
					|| (int)$personalNumber >= 0;
				if ($profileAllowed)
				$label = $this->link($this->getProfileUrl($uid), $label);
			}
			elseif ($linkType === 'mail' && $uid !== '' && defined('DOMAIN'))
			{
				$label = $this->link(
					'mailto:'.$uid.'@'.constant('DOMAIN'),
					$label
				);
			}

			$values[] = $label;
		}

		return implode('<br>', $values);
	}

	private function renderRooms($event)
	{
		$values = array();
		$seen = array();
		if (isset($event->ort_details) && is_array($event->ort_details))
		{
			foreach ($event->ort_details as $room)
			{
				$code = $this->getNestedValue($room, 'ort_kurzbz');
				if ($code === '' || isset($seen[$code]))
					continue;
				$seen[$code] = true;

				$label = $this->escape($code);
				$contentId = $this->getNestedValue($room, 'content_id');
				$title = $this->getNestedValue($room, 'bezeichnung');
				if ($contentId !== '')
				$label = $this->link($this->getRoomUrl($contentId), $label, $title);
				elseif ($title !== '')
					$label = '<span title="'.$this->escape($title).'">'.$label.'</span>';

				$values[] = $label;
			}
		}

		if (isset($event->ort_kurzbz) && is_array($event->ort_kurzbz))
		{
			foreach ($event->ort_kurzbz as $code)
			{
				$code = is_scalar($code) ? (string)$code : '';
				if ($code === '' || isset($seen[$code]))
					continue;
				$seen[$code] = true;
				$values[] = $this->escape($code);
			}
		}

		if (isset($event->locations) && is_array($event->locations))
		{
			foreach ($event->locations as $location)
			{
				$location = is_scalar($location) ? trim((string)$location) : '';
				if ($location !== '' && !isset($seen['location:'.$location]))
				{
					$seen['location:'.$location] = true;
					$values[] = $this->escape($location);
				}
			}
		}

		return implode('<br>', $values);
	}

	private function renderGroupLinks(array $groups)
	{
		$values = array();
		foreach ($this->uniqueScalarValues($groups) as $group)
		{
			$label = $this->escape($group);
			if (defined('DOMAIN'))
			{
				$label = $this->link(
					'mailto:'.mb_strtolower($group).'@'.constant('DOMAIN'),
					$label
				);
			}
			$values[] = $label;
		}

		return implode('<br>', $values);
	}

	private function renderScalarLines($values)
	{
		if (!is_array($values))
			$values = array($values);

		$escaped = array_map(array($this, 'escape'), $this->uniqueScalarValues($values));
		return implode('<br>', $escaped);
	}

	private function uniqueScalarValues(array $values)
	{
		$result = array();
		$seen = array();
		foreach ($values as $value)
		{
			if (!is_scalar($value))
				continue;
			$value = trim((string)$value);
			if ($value === '' || isset($seen[$value]))
				continue;
			$seen[$value] = true;
			$result[] = $value;
		}

		sort($result, SORT_STRING | SORT_FLAG_CASE);
		return $result;
	}

	private function renderOldTempusNoticeTagNote($event)
	{
		if (empty($event->tags)) return null;

		$parsedTags = $event->tags;
		if (!is_array($event->tags)) 
			$parsedTags = json_decode($event->tags, true);

		foreach ($parsedTags as $tag)
		{
			if (isset($tag['typ_kurzbz']) && $tag['typ_kurzbz'] === 'hinweis' && isset($tag['insertvon']) && $tag['insertvon'] === 'oldToNewTempusMigration')
				return $tag['notiz'] ?? null;
		}

		return null;
	}

	private function renderText($value)
	{
		return nl2br($this->escape($value), false);
	}

	private function getEventTitle($event)
	{
		foreach (array('titel', 'lehrfach_bez', 'lehrfach') as $property)
		{
			$value = trim($this->getScalar($event, $property));
			if ($value !== '')
				return $value;
		}

		return '';
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
			$date = is_numeric($value)
				? new DateTime('@'.(int)$value)
				: new DateTime($value, new DateTimeZone(self::TIMEZONE));
			$date->setTimezone(new DateTimeZone(self::TIMEZONE));
			return $date;
		}
		catch (Exception $exception)
		{
			return null;
		}
	}

	private function normalizeTime($time)
	{
		$time = (string)$time;
		if (preg_match('/^(\d{1,2}:\d{2}:\d{2})(?:\.\d+)?$/', $time, $matches))
			return $matches[1];
		if (preg_match('/^\d{1,2}:\d{2}$/', $time))
			return $time.':00';

		return $time;
	}

	private function formatDate($datum)
	{
		$date = DateTime::createFromFormat(
			'!Y-m-d',
			$datum,
			new DateTimeZone(self::TIMEZONE)
		);
		return $date === false ? $datum : $date->format('d.m.Y');
	}

	private function getScalar($object, $property)
	{
		return isset($object->{$property}) && is_scalar($object->{$property})
			? (string)$object->{$property}
			: '';
	}

	private function getNestedValue($value, $property)
	{
		if (is_array($value) && isset($value[$property]) && is_scalar($value[$property]))
			return (string)$value[$property];
		if (is_object($value) && isset($value->{$property}) && is_scalar($value->{$property}))
			return (string)$value->{$property};

		return '';
	}

	private function cell($content)
	{
		return '<td class="detail-lines">'.$content.'</td>';
	}

	private function link($url, $escapedLabel, $title = '')
	{
		$html = '<a href="'.$this->escape($url).'"';
		if ($title !== '')
			$html .= ' title="'.$this->escape($title).'"';
		return $html.'>'.$escapedLabel.'</a>';
	}

	private function getProfileUrl($uid)
	{
		return $this->getAppRoot().'cis/private/profile/index.php?'
			.http_build_query(array('uid' => $uid), '', '&', PHP_QUERY_RFC3986);
	}

	private function getRoomUrl($contentId)
	{
		return $this->getAppRoot().'cms/content.php?'
			.http_build_query(
				array('content_id' => $contentId),
				'',
				'&',
				PHP_QUERY_RFC3986
			);
	}

	private function phrase($category, $phrase, array $parameters = array())
	{
		if (($category === 'global' && $phrase === 'termine')
			|| ($category === 'ui'
				&& in_array($phrase, array('organisierende', 'teilnehmende'), true)))
		{
			return $this->ci->p->t($category, $phrase, $parameters);
		}

		return $this->legacyPhrases->t($category.'/'.$phrase, $parameters);
	}

	private function escape($value)
	{
		return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	private function getAppRoot()
	{
		return defined('APP_ROOT') ? rtrim(APP_ROOT, '/').'/' : '/';
	}
}
