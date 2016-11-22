<?php
require_once(dirname(__FILE__).'/../include/wochenplan.class.php');
require_once(dirname(__FILE__).'/../include/functions.inc.php');
require_once(dirname(__FILE__).'/../include/mitarbeiter.class.php');
require_once(dirname(__FILE__).'/../include/datum.class.php');
/**
 * CalDAV backend
 */
class MySabre_CalDAV_Backend extends \Sabre\CalDAV\Backend\AbstractBackend
{
    /**
     * Creates the backend
     *
     * @param AuthBackend $auth
     */
    public function __construct($auth)
	{
		$this->auth = $auth;
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
		//error_log("Caldav_Backend.php/getCalendarsForUser($principalUri)");
		//$user = $this->getUser();
		$user = mb_substr($principalUri,11);
        $calendars = array();
		$calendar = array(
                'id' => $user,
                'uri' => 'LVPlan-'.$user,
                'principaluri' => 'principals/'.$user,
                '{' . \Sabre\CalDAV\Plugin::NS_CALENDARSERVER . '}getctag' => 'LVPlan-'.$user.'-'.time(),
                '{' . \Sabre\CalDAV\Plugin::NS_CALDAV . '}supported-calendar-component-set' => new \Sabre\CalDAV\Property\SupportedCalendarComponentSet(array('VEVENT','VTODO')),
				'{DAV:}displayname'                          => 'LVPlan',
		        '{urn:ietf:params:xml:ns:caldav}calendar-description' => 'description comes here',
		        '{urn:ietf:params:xml:ns:caldav}calendar-timezone'    => 'Europe/Vienna',
		        '{http://apple.com/ns/ical/}calendar-order'  => '1',
		        '{http://apple.com/ns/ical/}calendar-color'  => '#FF0000'
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
		throw new \Sabre\DAV\Exception('Not Implemented');
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
    public function updateCalendar($calendarId, array $mutations)
	{
        return false;
    }

    /**
     * Delete a calendar and all it's objects
     *
     * @param string $calendarId
     * @return void
     */
    public function deleteCalendar($calendarId)
	{
		throw new \Sabre\DAV\Exception('Not Implemented');
    }


	public function getCalendarData($user, $objectUri=null)
	{
		$datum_obj = new datum();
		$starttime = microtime(true);
		$bn = new benutzer();
		if(!$bn->load($user))
			die('User invalid');

		if(check_lektor($user))
			$type='lektor';
		else
			$type='student';

		// Stundenplanobjekt erzeugen
		$stdplan = new wochenplan($type);
		$stdplan->crlf="\n";

		// Zusaetzliche Daten laden
		if(!$stdplan->load_data($type,$user))
		{
			die($stdplan->errormsg);
		}
		if(!is_null($objectUri))
		{
			$unr = mb_substr($objectUri, (mb_strpos($objectUri,'-')+1), mb_strpos($objectUri,'@')-(mb_strpos($objectUri,'-')+1));
			$dtstart = mb_substr($objectUri,0,mb_strpos($objectUri,'-'));

			if(mb_strlen($dtstart)==15)
			{
				//dtstart: 19700325T020000
				$jahr = mb_substr($dtstart,0,4);
				$monat = mb_substr($dtstart,4,2);
				$tag = mb_substr($dtstart,6,2);
				$stunde = mb_substr($dtstart,9,2);
				$minute = mb_substr($dtstart,11,2);
				$sekunde = mb_substr($dtstart,13,2);
				$begin = mktime($stunde, $minute, $sekunde, $monat, $tag-1, $jahr);
				$ende = mktime($stunde, $minute, $sekunde, $monat, $tag+1, $jahr);
				//error_log("getCalendarData unr: $unr dtstart: $dtstart size:".(mb_strlen($objectUri)-mb_strpos($objectUri,'@')));
				//error_log($begin.'/'.$ende);
			}
			else
			{
				//error_log("dtstart laenge abnormal: $dtstart");
				$begin = mktime(0,0,0,date('m'),date('d')-14,date('Y'));
				$ende = mktime(0,0,0,date('m')+6,date('d'),date('Y'));
			}
		}
		else
		{
			$begin = mktime(0,0,0,date('m'),date('d')-14,date('Y'));
			$ende = mktime(0,0,0,date('m')+6,date('d'),date('Y'));
		}
		$db_stpl_table = 'stundenplan';
		$i=0;
		$data = array();
		// Kalender erstellen
		while($begin<$ende)
		{
			//error_log("while");
			$i++;
			if(!date("w",$begin))
				$begin=jump_day($begin,1);

			$stdplan->init_stdplan();
			$datum=$begin;
			$begin = $datum_obj->jump_week($begin,1);

			// Stundenplan einer Woche laden
			if(!$stdplan->load_week($datum,$db_stpl_table))
			{
				die($stdplan->errormsg);
			}
			$val = $stdplan->draw_week_csv('return', LVPLAN_KATEGORIE);
			if(!is_null($objectUri))
			{
				foreach($val as $row)
				{
					//einzelnen Eintrag holen
					if($row['dtstart']==$dtstart && ($row['unr'][0]==$unr) || $unr=='R'.$row['reservierung_id'])
					{
						return $row;
					}
				}
			}
			else
				$data=array_merge($data, $val);
		}
		$endtime = microtime(true);
		//error_log("\n\nDATA".print_r($data,true));
		//error_log("getCalendarData time:".($endtime-$starttime));
		//$data.="\nEND:VCALENDAR";
		return $data;
	}
	public function makeCal($event)
	{
		return "BEGIN:VCALENDAR
VERSION:2.0
PRODID:FH Technikum Wien
BEGIN:VTIMEZONE
TZID:Europe/Vienna
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:GMT+02:00
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE\n".$event."\nEND:VCALENDAR";
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
		foreach($data as $row)
		{
			// Reservierungen werden mit einem R markiert und mit der ReservierungID da sonst
			// Termine verloren gehen koennen wenn zur selben Zeit eine Reservierung und ein LVPlan Eintrag vorhanden ist
			if($row['reservierung'])
				$uri = $row['dtstart'].'-R'.$row['reservierung_id'];
			else
				$uri = $row['dtstart'].'-'.$row['unr'][0];

			$return[] = array("id"=>$row['UID'],
			"calendardata"=>$this->makeCal($row['data']),
			"uri"=>$uri.'@'.md5($row['UID']),
			"lastmodified"=>$row['updateamum'],
			"etag"=>'"'.$row['UID'].'"',
			"calendarid"=>$calendarId);

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
		//error_log("Caldav_Backend.php/getCalendarObject($calendarId, $objectUri)");
		//$user = $this->getUser();
		$user = $calendarId;
		$data = $this->getCalendarData($user,$objectUri);
		if(count($data)==0)
		{
			$ret=array("id"=>'',
			"calendardata"=>'',
			"uri"=>'',
			"lastmodified"=>'',
			"etag"=>'',
			"calendarid"=>$calendarId);
		}
		else
		{
			$ret = array("id"=>$data['UID'],
			"calendardata"=>$this->makeCal($data['data']),
			"uri"=>'principals/'.$user.'/LVPlan/'.$data['dtstart'].'-'.$data['unr'][0].'@'.md5($data['UID']),
			"lastmodified"=>$data['updateamum'],
			"etag"=>'"'.$data['UID'].'"',
			"calendarid"=>$calendarId);
		}
		return $ret;
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
		throw new \Sabre\DAV\Exception('Not Implemented');
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
		throw new \Sabre\DAV\Exception('Not Implemented');
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
		throw new Sabre\DAV\Exception('Not Implemented');
    }
}
