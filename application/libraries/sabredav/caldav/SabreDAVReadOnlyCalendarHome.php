<?php

class SabreDAVReadOnlyCalendarHome extends \Sabre\CalDAV\CalendarHome
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
			$objs[] = new SabreDAVReadOnlyCalendar($this->readOnlyCaldavBackend, $calendar);
		}

		return $objs;
	}

	public function createExtendedCollection($name, \Sabre\DAV\MkCol $mkCol)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function createFile($filename, $data = null)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function createDirectory($filename)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function delete()
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function setName($name)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function getACL()
	{
		return SabreDAVReadOnlyACL::getAcl($this->readOnlyPrincipalInfo['uri']);
	}
}
