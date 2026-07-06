<?php

class SabreDAVReadOnlyCalendar extends \Sabre\CalDAV\Calendar
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

		return new SabreDAVReadOnlyCalendarObject($this->readOnlyCaldavBackend, $this->readOnlyCalendarInfo, $obj);
	}

	public function getChildren()
	{
		$objs = $this->readOnlyCaldavBackend->getCalendarObjects($this->readOnlyCalendarInfo['id']);
		$children = array();

		foreach($objs as $obj)
		{
			$obj['acl'] = $this->getCalendarObjectAcl();
			$children[] = new SabreDAVReadOnlyCalendarObject($this->readOnlyCaldavBackend, $this->readOnlyCalendarInfo, $obj);
		}

		return $children;
	}

	public function createFile($name, $calendarData = null)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function createDirectory($name)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function delete()
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
	}

	public function setName($newName)
	{
		return SabreDAVReadOnlyACL::ignoreWrite();
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
		return SabreDAVReadOnlyACL::getAcl($this->getOwner(), true);
	}

	protected function getCalendarObjectAcl()
	{
		return SabreDAVReadOnlyACL::getAcl($this->getOwner());
	}
}
