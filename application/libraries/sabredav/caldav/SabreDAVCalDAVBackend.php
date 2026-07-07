<?php

/**
 * CalDAV backend
 */
class SabreDAVCalDAVBackend extends \Sabre\CalDAV\Backend\AbstractBackend
{
	protected $auth;

	protected $CI;

	CONST CALENDAR_NAME = 'LVPlan';
	CONST CAL_CATEGORY_STUNDENPLAN = 'Stundenplan';
	CONST CAL_CATEGORY_STUNDENPLAN_EXAM = 'StundenplanExam';
	CONST CAL_CATEGORY_STUNDENPLAN_REMOTE = 'StundenplanRemote';

	CONST CAL_CATEGORY_STUNDENPLAN_EXAM_ICON = '📝';
	CONST CAL_CATEGORY_STUNDENPLAN_ON_SITE_ICON = '🏫';
	CONST CAL_CATEGORY_STUNDENPLAN_REMOTE_ICON = '📍';
	CONST CAL_CATEGORY_RESERVATION_ICON = '📌';
	/**
     * Creates the backend
     *
     * @param AuthBackend $auth
     */
    public function __construct($auth)
	{
		$this->auth = $auth;

		$this->CI =& get_instance();
    }

	/**
	 * Liefert den eingeloggten User
	 */
	function getUser()
	{
		return $this->auth->getCurrentUser();
	}

    /**
     * Returns a list of calendars for a principal.
     *
     * Every project is an array with the following keys:
     *  * id, a unique id that will be used by other functions to modify the
     *    calendar. This can be the same as the uri or a database key.
     *  * uri, which the basename of the uri with which the calendar is
     *    accessed.
     *  * principalUri. The owner of the calendar. Almost always the same as
     *    principalUri passed to this method.
     *
     * Furthermore it can contain webdav properties in clark notation. A very
     * common one is '{DAV:}displayname'.
     *
     * @param string $principalUri
     * @return array
     */
    public function getCalendarsForUser($principalUri)
	{
		$user = mb_substr($principalUri,11);
        $calendars = array();
		$calendar = array(
			'id' => $user,
			'uri' => self::CALENDAR_NAME.'-'.$user,
			'principaluri' => 'principals/'.$user,
			'{' . \Sabre\CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => $this->buildCalendarCtag($user),
			'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet(array('VEVENT')),
			'{DAV:}displayname'                          => self::CALENDAR_NAME,
			'{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description comes here',
			'{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'Europe/Vienna',
			'{http://apple.com/ns/ical/}calendar-order'  => '1',
			'{http://apple.com/ns/ical/}calendar-color'  => '#FF0000',
			'{http://sabredav.org/ns}read-only' => 1
        );
		$calendars[] = $calendar;

        return $calendars;
    }

    /**
     * Creates a new calendar for a principal.
     *
     * If the creation was a success, an id must be returned that can be used to reference
     * this calendar in other methods, such as updateCalendar
     *
     * @param string $principalUri
     * @param string $calendarUri
     * @param array $properties
     */
    public function createCalendar($principalUri,$calendarUri, array $properties)
	{
		return $calendarUri;
    }

    /**
     * Updates properties for a calendar.
     *
     * The mutations array uses the propertyName in clark-notation as key,
     * and the array value for the property value. In the case a property
     * should be deleted, the property value will be null.
     *
     * This method must be atomic. If one property cannot be changed, the
     * entire operation must fail.
     *
     * If the operation was successful, true can be returned.
     * If the operation failed, false can be returned.
     *
     * Deletion of a non-existant property is always succesful.
     *
     * Lastly, it is optional to return detailed information about any
     * failures. In this case an array should be returned with the following
     * structure:
     *
     * array(
     *   403 => array(
     *      '{DAV:}displayname' => null,
     *   ),
     *   424 => array(
     *      '{DAV:}owner' => null,
     *   )
     * )
     *
     * In this example it was forbidden to update {DAV:}displayname.
     * (403 Forbidden), which in turn also caused {DAV:}owner to fail
     * (424 Failed Dependency) because the request needs to be atomic.
     *
     * @param string $calendarId
     * @param array $mutations
     * @return bool|array
     */
    public function updateCalendar($calendarId, \Sabre\DAV\PropPatch $propPatch)
	{
        $propPatch->setRemainingResultCode(200);
    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     * @return void
     */
    public function deleteCalendar($calendarId)
	{
		return null;
    }


	public function getCalendarData($userUID, $objectUri=null)
	{
		$this->CI->load->model("ressource/Mitarbeiter_model", "MitarbeiterModel");
		$this->CI->load->model("person/Benutzer_model", "BenutzerModel");

		$user = $this->CI->BenutzerModel->loadWhere(array("uid" => $userUID));
		if(!$user)
			die('User invalid');

		$this->CI->load->library('KalenderLib', ['uid' => $userUID]);

		$isUserEmployeeResult = $this->CI->MitarbeiterModel->isMitarbeiter($userUID);
		if (isError($isUserEmployeeResult)) {
			return;
		}
		$isUserEmployee = getData($isUserEmployeeResult);

		$startDate = date('Y-m-d', mktime(0,0,0,date('m'),date('d')-14,date('Y')));
		$endDate = date('Y-m-d', mktime(0,0,0,date('m')+6,date('d'),date('Y')));

		$data = [];

		if ($isUserEmployee) {
			$data = $this->CI->kalenderlib->getPlanForLecturerByLecturer($startDate, $endDate, $userUID);
		} else {
			$data = $this->CI->kalenderlib->getPlanForStudentByStudent($startDate, $endDate, $userUID);
		}

		if(!is_array($data))
			$data = array();

		foreach($data as $item)
		{
			$item->calendarDataFragment = $this->buildCalendarDataFragment($item);
		}

		if (is_null($objectUri))
		{
			return $data;
		}

		$normalizedObjectUri = $this->normalizeObjectUri($objectUri);
		foreach($data as $row)
		{
			if(md5($row->eindeutige_gruppen_id) == $normalizedObjectUri)
			{
				return $row;
			}
		}

		return null;
	}

	public function makeCal($event)
	{
		return $this->buildICalLine('BEGIN', 'VCALENDAR')
			.$this->buildICalLine('VERSION', '2.0')
			.$this->buildICalTextLine('PRODID', 'FH Technikum Wien')
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
			.$this->buildICalLine('END', 'VTIMEZONE')
			.$event
			.$this->buildICalLine('END', 'VCALENDAR');
	}

	protected function buildCalendarDataFragment($item)
	{
		$summaryIcon = '';
		$summary = $item->type == 'reservierung' ? $item->titel : $item->topic;

		$description = "";
		if ($item->type == 'reservierung') {
			$description = $item->beschreibung;
		} else {
			$description = $item->lehrfach_bez . "\r\n";

			if (isset($item->lektor) && is_array($item->lektor) && count($item->lektor) > 0) {
				$description .= "Lektor*in: " . join(", ", array_map(function($teacher) { return $teacher["kurzbz"]; }, $item->lektor)) . "\r\n";
			}
		}


		$category = self::CAL_CATEGORY_STUNDENPLAN;
		if ($item->type === 'lehreinheit') {
			if ($item->lehrform === 'EXAM') {
				$summaryIcon = self::CAL_CATEGORY_STUNDENPLAN_EXAM_ICON;
				$category = self::CAL_CATEGORY_STUNDENPLAN_EXAM;
			} else if (!isset($item->ko_ort_kurzbz) || $item->ko_ort_kurzbz === '') {
				$summaryIcon = self::CAL_CATEGORY_STUNDENPLAN_REMOTE_ICON;
				$category = self::CAL_CATEGORY_STUNDENPLAN_REMOTE;
			}
		} else if ($item->type === 'reservierung' && (!isset($item->ko_ort_kurzbz) || $item->ko_ort_kurzbz === '')) {
			$summaryIcon = self::CAL_CATEGORY_RESERVATION_ICON;
			$category = self::CAL_CATEGORY_STUNDENPLAN_REMOTE;
		}
		
		if ($summaryIcon !== '') {
			$summary = $summaryIcon . ' ' . $summary;
		}

		$parsedStartDate = $this->formatICalLocalDateTime($item->isostart);
		$parsedEndDate = $this->formatICalLocalDateTime($item->isoend);
		$lastModified = $this->formatICalUtcDateTime(isset($item->updateamum) ? $item->updateamum : null);
		$dtStamp = $lastModified ?: $this->formatICalUtcDateTime($item->isostart);

		if($dtStamp === null)
			$dtStamp = '19700101T000000Z';

		$fragment = $this->buildICalLine('BEGIN', 'VEVENT')
			.$this->buildICalTextLine('UID', $item->eindeutige_gruppen_id)
			.$this->buildICalLine('SEQUENCE', (int)$item->kalender_id)
			.$this->buildICalTextLine('SUMMARY', $summary)
			.$this->buildICalTextLine('DESCRIPTION', $description)
			.$this->buildICalTextLine('LOCATION', isset($item->ort_kurzbz) ? $item->ort_kurzbz : '')
			.$this->buildICalTextLine('CATEGORIES', $category)
			.$this->buildICalLine('DTSTART', $parsedStartDate, array('TZID' => 'Europe/Vienna'))
			.$this->buildICalLine('DTEND', $parsedEndDate, array('TZID' => 'Europe/Vienna'));

		if($lastModified !== null)
			$fragment .= $this->buildICalLine('LAST-MODIFIED', $lastModified);

		return $fragment
			.$this->buildICalLine('DTSTAMP', $dtStamp)
			.$this->buildICalLine('END', 'VEVENT');
	}

	protected function buildCalendarCtag($userUID)
	{
		$data = $this->getCalendarData($userUID);
		if(!is_array($data))
			$data = array();

		$fragments = array();
		foreach($data as $row)
		{
			$lastModified = isset($row->updateamum) ? $this->getLastModifiedTimestamp($row->updateamum) : null;
			$calendarData = isset($row->calendarDataFragment) ? $this->makeCal($row->calendarDataFragment) : '';
			$fragments[] = $this->getObjectUri($row).':'.$lastModified.':'.md5($calendarData);
		}

		sort($fragments, SORT_STRING);
		return self::CALENDAR_NAME.'-'.$userUID.'-'.md5(implode('|', $fragments));
	}

	protected function buildICalTextLine($name, $value, array $parameters = array())
	{
		return $this->buildICalLine($name, $this->escapeICalText($value), $parameters);
	}

	protected function buildICalLine($name, $value, array $parameters = array())
	{
		$line = strtoupper($name);

		foreach($parameters as $parameterName => $parameterValue)
		{
			$parameterValues = is_array($parameterValue) ? $parameterValue : array($parameterValue);
			$escapedValues = array();

			foreach($parameterValues as $singleParameterValue)
				$escapedValues[] = $this->escapeICalParameterValue($singleParameterValue);

			$line .= ';'.strtoupper($parameterName).'='.implode(',', $escapedValues);
		}

		return $this->foldICalLine($line.':'.(string)$value);
	}

	protected function foldICalLine($line)
	{
		$line = (string)$line;
		$folded = '';

		while(strlen($line) > 75)
		{
			$part = mb_strcut($line, 0, 75, 'UTF-8');
			$folded .= $part."\r\n";
			$line = ' '.mb_strcut($line, strlen($part), strlen($line) - strlen($part), 'UTF-8');
		}

		return $folded.$line."\r\n";
	}

	protected function escapeICalText($value)
	{
		$value = (string)$value;
		$value = str_replace('\\', '\\\\', $value);
		$value = str_replace(array("\r\n", "\r", "\n"), '\n', $value);
		return str_replace(array(';', ','), array('\;', '\,'), $value);
	}

	protected function escapeICalParameterValue($value)
	{
		$value = (string)$value;
		$value = str_replace(array('\\', '"', "\r", "\n"), array('\\\\', '\"', '', ''), $value);

		if(preg_match('/[;:,]/', $value))
			return '"'.$value.'"';

		return $value;
	}

	protected function formatICalLocalDateTime($dateTime)
	{
		$dateTime = $this->createDateTime($dateTime);
		if($dateTime === null)
			return null;

		$dateTime->setTimezone(new DateTimeZone('Europe/Vienna'));
		return $dateTime->format('Ymd\THis');
	}

	protected function formatICalUtcDateTime($dateTime)
	{
		$dateTime = $this->createDateTime($dateTime);
		if($dateTime === null)
			return null;

		$dateTime->setTimezone(new DateTimeZone('UTC'));
		return $dateTime->format('Ymd\THis\Z');
	}

	protected function createDateTime($dateTime)
	{
		if($dateTime instanceof DateTime)
			return clone $dateTime;

		if($dateTime === null || $dateTime === '')
			return null;

		try
		{
			if(is_numeric($dateTime))
				return new DateTime('@'.(int)$dateTime);

			return new DateTime($dateTime);
		}
		catch(Exception $e)
		{
			return null;
		}
	}

	protected function getObjectUri($row)
	{
		return md5($row->eindeutige_gruppen_id).'.ics';
	}

	protected function normalizeObjectUri($objectUri)
	{
		$normalizedObjectUri = (string)$objectUri;

		if(substr($normalizedObjectUri, -4) === '.ics')
			$normalizedObjectUri = substr($normalizedObjectUri, 0, -4);

		if(mb_strpos($normalizedObjectUri, '@') !== false)
			$normalizedObjectUri = mb_substr($normalizedObjectUri, mb_strpos($normalizedObjectUri, '@') + 1);

		if($this->hasObjectUriDatePrefix($normalizedObjectUri))
			return mb_substr($normalizedObjectUri, mb_strpos($normalizedObjectUri, '-') + 1);

		return $normalizedObjectUri;
	}

	protected function hasObjectUriDatePrefix($objectUri)
	{
		$hyphenPosition = mb_strpos($objectUri, '-');
		if($hyphenPosition !== 15)
			return false;

		return preg_match('/^\d{8}T\d{6}$/', mb_substr($objectUri, 0, 15)) === 1;
	}

	protected function buildCalendarObject($row, $calendarId)
	{
		$calendarData = $this->makeCal($row->calendarDataFragment);

		return array(
			"id"=>$row->eindeutige_gruppen_id,
			"calendardata"=>$calendarData,
			"uri"=>$this->getObjectUri($row),
			"lastmodified"=>$this->getLastModifiedTimestamp($row->updateamum),
			"etag"=>'"'.md5($calendarData).'"',
			"calendarid"=>$calendarId,
			"size"=>strlen($calendarData),
			"component"=>'vevent'
		);
	}

	protected function getLastModifiedTimestamp($lastModified)
	{
		if($lastModified === null || $lastModified === '')
			return null;

		if(is_numeric($lastModified))
			return (int)$lastModified;

		$timestamp = strtotime($lastModified);
		if($timestamp === false)
			return null;

		return $timestamp;
	}

    /**
     * Returns all calendar objects within a calendar.
     *
     * Every item contains an array with the following keys:
     *   * id - unique identifier which will be used for subsequent updates
     *   * calendardata - The iCalendar-compatible calnedar data
     *   * uri - a unique key which will be used to construct the uri. This can be any arbitrary string.
     *   * lastmodified - a timestamp of the last modification time
     *   * etag - An arbitrary string, surrounded by double-quotes. (e.g.:
     *   '  "abcdef"')
     *   * calendarid - The calendarid as it was passed to this function.
     *
     * Note that the etag is optional, but it's highly encouraged to return for
     * speed reasons.
     *
     * The calendardata is also optional. If it's not returned
     * 'getCalendarObject' will be called later, which *is* expected to return
     * calendardata.
     *
     * @param string $calendarId
     * @return array
     */
    public function getCalendarObjects($calendarId)
	{
		//$user = $this->getUser();
		$user = $calendarId;
		$data = $this->getCalendarData($user);

		//error_log("Caldav_Backend.php/getCalendarObjects($calendarId) ");
		$return  = array();

		if(!is_array($data))
			return $return;

		foreach($data as $row)
		{
			$return[] = $this->buildCalendarObject($row, $calendarId);
		}
		return $return;
    }

    /**
     * Returns information from a single calendar object, based on it's object
     * uri.
     *
     * The returned array must have the same keys as getCalendarObjects. The
     * 'calendardata' object is required here though, while it's not required
     * for getCalendarObjects.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return array
     */
    public function getCalendarObject($calendarId,$objectUri)
	{
		$user = $calendarId;
		$data = $this->getCalendarData($user,$objectUri);

		if(empty($data))
		{
			return null;
		}
		elseif(is_object($data))
		{
			$ret = $this->buildCalendarObject($data, $calendarId);
		}

		return isset($ret) ? $ret : null;
    }

    /**
     * Creates a new calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return void
     */
    public function createCalendarObject($calendarId,$objectUri,$calendarData)
	{
		return null;
    }

    /**
     * Updates an existing calendarobject, based on it's uri.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @param string $calendarData
     * @return void
     */
    public function updateCalendarObject($calendarId,$objectUri,$calendarData)
	{
		if(is_resource($calendarData))
			$calendarData = stream_get_contents($calendarData);

		return '"'.md5((string)$calendarData).'"';
    }

    /**
     * Deletes an existing calendar object.
     *
     * @param string $calendarId
     * @param string $objectUri
     * @return void
     */
    public function deleteCalendarObject($calendarId,$objectUri)
	{
		return null;
    }
}
