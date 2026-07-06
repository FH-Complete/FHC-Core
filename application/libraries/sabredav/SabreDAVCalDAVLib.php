<?php

/**
 * SabreDAV CalDAV classes for the LVPlan calendar endpoint.
 */
class SabreDAVCalDAVLib
{
}

/**
 * PDO principal backend
 *
 * This is a simple principal backend that maps exactly to the users table, as
 * used by Sabre_DAV_Auth_Backend_PDO.
 *
 * It assumes all principals are in a single collection. The default collection
 * is 'principals/', but this can be overriden.
 *
 * @package Sabre
 * @subpackage DAVACL
 * @copyright Copyright (C) 2007-2011 Rooftop Solutions. All rights reserved.
 * @author Evert Pot (http://www.rooftopsolutions.nl/)
 * @license http://code.google.com/p/sabredav/wiki/License Modified BSD License
 */
class SabreDAVACLPrincipalBackendLib implements \Sabre\DAVACL\PrincipalBackend\BackendInterface
{
    /**
     * PDO table name for 'principals'
     *
     * @var string
     */
    protected $tableName;

    /**
     * PDO table name for 'group members'
     *
     * @var string
     */
    protected $groupMembersTableName;

	protected $result_ma;
	protected $auth;
    /**
     * Sets up the backend.
     *
     * @param PDO $pdo
     * @param string $tableName
     */
    public function __construct($auth)
	{
		$this->auth = $auth;

		/*
		$ma = new mitarbeiter();
		$this->result_ma = $ma->getMitarbeiter(null,null,null);
		*/
    }

	/**
	 * Liefert den eingeloggten User
	 */
	function getUser()
	{
		return $this->auth->getCurrentUser();
	}


    /**
     * Returns a list of principals based on a prefix.
     *
     * This prefix will often contain something like 'principals'. You are only
     * expected to return principals that are in this base path.
     *
     * You are expected to return at least a 'uri' for every user, you can
     * return any additional properties if you wish so. Common properties are:
     *   {DAV:}displayname
     *   {http://sabredav.org/ns}email-address - This is a custom SabreDAV
     *     field that's actualy injected in a number of other properties. If
     *     you have an email address, use this property.
     *
     * @param string $prefixPath
     * @return array
     */
    public function getPrincipalsByPrefix($prefixPath)
	{
		//$prefixPath = principals
		//error_log('Principal.php/getPrincipalsByPrefix('.$prefixPath.')');
        $principals = array();
		$user = $this->getUser();

		if($prefixPath=='principals')
		{

		    $principals[] = array(
					'id' => $user,
		            'uri' => 'principals/'.$user,
		            '{DAV:}displayname' => $user,
		            '{http://sabredav.org/ns}email-address' => $user.'@example.com',
		        );
		}

        return $principals;

    }

    /**
     * Returns a specific principal, specified by it's path.
     * The returned structure should be the exact same as from
     * getPrincipalsByPrefix.
     *
     * @param string $path
     * @return array
     */
    public function getPrincipalByPath($path)
	{
		$user = mb_substr($path,11);

        $result = array(
            'id'  => $user,
            'uri' => 'principals/'.$user,
            '{DAV:}displayname' => $user,
            '{http://sabredav.org/ns}email-address' => $user.'@example.com',
        );

		return $result;
    }

    /**
     * Returns the list of members for a group-principal
     *
     * @param string $principal
     * @return array
     */
    public function getGroupMemberSet($principal)
	{
        $result = array();

		return $result;
    }

    /**
     * Returns the list of groups a principal is a member of
     *
     * @param string $principal
     * @return array
     */
    public function getGroupMembership($principal)
	{
		$result = array();
		if(preg_match('/^principals\/[0-9A-Za-z\-]*$/',$principal))
		{
			$user = mb_substr($principal,11);
		}
        return $result;

    }

    /**
     * Updates the list of group members for a group principal.
     *
     * The principals should be passed as a list of uri's.
     *
     * @param string $principal
     * @param array $members
     * @return void
     */
    public function setGroupMemberSet($principal, array $members)
	{
		throw new \Sabre\DAV\Exception('Not implemented');
    }

   public function updatePrincipal($path, \Sabre\DAV\PropPatch $propPatch)
   {
	$propPatch->setRemainingResultCode(403);
   }

	public function searchPrincipals($prefixPath, array $searchProperties, $test = 'allof')
	{
		return array();
	}

	public function findByUri($uri, $principalPrefix)
	{
		return null;
	}
}

/**
 * CalDAV backend
 */
class SabreDAVCalDAVBackendLib extends \Sabre\CalDAV\Backend\AbstractBackend
{
	protected $auth;

	protected $CI;

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
			'uri' => 'LVPlan-'.$user,
			'principaluri' => 'principals/'.$user,
			'{' . \Sabre\CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => $this->buildCalendarCtag($user),
			'{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new \Sabre\CalDAV\Xml\Property\SupportedCalendarComponentSet(array('VEVENT')),
			'{DAV:}displayname'                          => 'LVPlan',
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
		$summary = $item->type == 'reservierung' ? $item->titel : $item->topic;

		$description = "";
		if ($item->type == 'reservierung') {
			$description = $item->beschreibung;
		} else {
			$description = $item->lehrfach_bez . "\r\n";

			if (isset($item->lektor) && is_array($item->lektor) && count($item->lektor) > 0) {
				$description .= "Lehrer: " . join(", ", array_map(function($teacher) { return $teacher["kurzbz"]; }, $item->lektor)) . "\r\n";
			}
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
			.$this->buildICalTextLine('DESCRIPTION', 'TODO')
			.$this->buildICalTextLine('LOCATION', isset($item->ort_kurzbz) ? $item->ort_kurzbz : '')
			.$this->buildICalTextLine('CATEGORIES', 'Stundenplans')
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
		return 'LVPlan-'.$userUID.'-'.md5(implode('|', $fragments));
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

/**
 * Read-only CalDAV node wrappers for DB-owned calendars.
 */
class SabreDAVReadOnlyACLLib
{
	public static function getAcl($principalUri, $includeFreeBusy = false)
	{
		$acl = array(
			array(
				'privilege' => '{DAV:}read',
				'principal' => $principalUri,
				'protected' => true,
			),
			array(
				'privilege' => '{DAV:}read',
				'principal' => $principalUri.'/calendar-proxy-read',
				'protected' => true,
			),
		);

		if($includeFreeBusy)
		{
			$acl[] = array(
				'privilege' => '{'.\Sabre\CalDAV\Plugin::NS_CALDAV.'}read-free-busy',
				'principal' => '{DAV:}authenticated',
				'protected' => true,
			);
		}

		return $acl;
	}

	public static function ignoreWrite()
	{
		return null;
	}
}

class SabreDAVReadOnlyCalendarRootLib extends \Sabre\CalDAV\CalendarRoot
{
	protected $readOnlyCaldavBackend;

	public function __construct(\Sabre\DAVACL\PrincipalBackend\BackendInterface $principalBackend, \Sabre\CalDAV\Backend\BackendInterface $caldavBackend, $principalPrefix = 'principals')
	{
		parent::__construct($principalBackend, $caldavBackend, $principalPrefix);
		$this->readOnlyCaldavBackend = $caldavBackend;
	}

	public function getChildForPrincipal(array $principal)
	{
		return new SabreDAVReadOnlyCalendarHomeLib($this->readOnlyCaldavBackend, $principal);
	}
}

class SabreDAVReadOnlyCalendarHomeLib extends \Sabre\CalDAV\CalendarHome
{
	protected $readOnlyCaldavBackend;
	protected $readOnlyPrincipalInfo;

	public function __construct(\Sabre\CalDAV\Backend\BackendInterface $caldavBackend, $principalInfo)
	{
		parent::__construct($caldavBackend, $principalInfo);
		$this->readOnlyCaldavBackend = $caldavBackend;
		$this->readOnlyPrincipalInfo = $principalInfo;
	}

	public function getChildren()
	{
		$calendars = $this->readOnlyCaldavBackend->getCalendarsForUser($this->readOnlyPrincipalInfo['uri']);
		$objs = array();

		foreach($calendars as $calendar)
		{
			$objs[] = new SabreDAVReadOnlyCalendarLib($this->readOnlyCaldavBackend, $calendar);
		}

		return $objs;
	}

	public function createExtendedCollection($name, \Sabre\DAV\MkCol $mkCol)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function createFile($filename, $data = null)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function createDirectory($filename)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function delete()
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function setName($name)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function getACL()
	{
		return SabreDAVReadOnlyACLLib::getAcl($this->readOnlyPrincipalInfo['uri']);
	}
}

class SabreDAVReadOnlyCalendarLib extends \Sabre\CalDAV\Calendar
{
	protected $readOnlyCaldavBackend;
	protected $readOnlyCalendarInfo;

	public function __construct(\Sabre\CalDAV\Backend\BackendInterface $caldavBackend, $calendarInfo)
	{
		parent::__construct($caldavBackend, $calendarInfo);
		$this->readOnlyCaldavBackend = $caldavBackend;
		$this->readOnlyCalendarInfo = $calendarInfo;
	}

	public function getChild($name)
	{
		$obj = $this->readOnlyCaldavBackend->getCalendarObject($this->readOnlyCalendarInfo['id'], $name);

		if(!$obj)
			throw new \Sabre\DAV\Exception\NotFound('Calendar object not found');

		$obj['acl'] = $this->getCalendarObjectAcl();

		return new SabreDAVReadOnlyCalendarObjectLib($this->readOnlyCaldavBackend, $this->readOnlyCalendarInfo, $obj);
	}

	public function getChildren()
	{
		$objs = $this->readOnlyCaldavBackend->getCalendarObjects($this->readOnlyCalendarInfo['id']);
		$children = array();

		foreach($objs as $obj)
		{
			$obj['acl'] = $this->getCalendarObjectAcl();
			$children[] = new SabreDAVReadOnlyCalendarObjectLib($this->readOnlyCaldavBackend, $this->readOnlyCalendarInfo, $obj);
		}

		return $children;
	}

	public function createFile($name, $calendarData = null)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function createDirectory($name)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function delete()
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function setName($newName)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function updateProperties($mutations)
	{
		return true;
	}

	public function propPatch(\Sabre\DAV\PropPatch $propPatch)
	{
		$propPatch->setRemainingResultCode(200);
	}

	public function getACL()
	{
		return SabreDAVReadOnlyACLLib::getAcl($this->getOwner(), true);
	}

	protected function getCalendarObjectAcl()
	{
		return SabreDAVReadOnlyACLLib::getAcl($this->getOwner());
	}
}

class SabreDAVReadOnlyCalendarObjectLib extends \Sabre\CalDAV\CalendarObject
{
	protected $readOnlyCalendarInfo;

	public function __construct(\Sabre\CalDAV\Backend\BackendInterface $caldavBackend, array $calendarInfo, array $objectData)
	{
		parent::__construct($caldavBackend, $calendarInfo, $objectData);
		$this->readOnlyCalendarInfo = $calendarInfo;
	}

	public function put($calendarData)
	{
		return $this->getETag();
	}

	public function getETag()
	{
		if(isset($_SERVER['REQUEST_METHOD']) && in_array($_SERVER['REQUEST_METHOD'], array('PUT', 'DELETE'), true))
		{
			$ifMatch = isset($_SERVER['HTTP_IF_MATCH']) ? trim($_SERVER['HTTP_IF_MATCH']) : '';
			if($ifMatch !== '' && $ifMatch !== '*')
			{
				$requestedEtags = explode(',', $ifMatch);
				foreach($requestedEtags as $requestedEtag)
				{
					$requestedEtag = trim($requestedEtag);
					if($requestedEtag !== '')
						return $requestedEtag;
				}
			}
		}

		return parent::getETag();
	}

	public function delete()
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function setName($name)
	{
		return SabreDAVReadOnlyACLLib::ignoreWrite();
	}

	public function getACL()
	{
		return SabreDAVReadOnlyACLLib::getAcl($this->readOnlyCalendarInfo['principaluri']);
	}
}
