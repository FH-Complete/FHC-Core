<?php

class SabreDAVReadOnlyCalendarObject extends \Sabre\CalDAV\CalendarObject
{
	protected $readOnlyCalendarInfo;

	public function __construct(\Sabre\CalDAV\Backend\BackendInterface $caldavBackend, array $calendarInfo, array $objectData)
	{
		parent::__construct($caldavBackend, $calendarInfo, $objectData);
		$this->readOnlyCalendarInfo = $calendarInfo;
	}

	public function put($calendarData)
	{
		return $this->getCurrentETag();
	}

	public function getETag()
	{
		if($this->isWriteRequest())
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

	public function getLastModified()
	{
		if($this->isWriteRequest())
		{
			$ifUnmodifiedSince = isset($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) ? trim($_SERVER['HTTP_IF_UNMODIFIED_SINCE']) : '';
			if($ifUnmodifiedSince !== '')
			{
				$timestamp = strtotime($ifUnmodifiedSince);
				if($timestamp !== false)
					return max(0, $timestamp - 1);
			}
		}

		return parent::getLastModified();
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
		return SabreDAVReadOnlyACL::getAcl($this->readOnlyCalendarInfo['principaluri']);
	}

	protected function isWriteRequest()
	{
		if(!isset($_SERVER['REQUEST_METHOD']))
			return false;

		return in_array(strtoupper($_SERVER['REQUEST_METHOD']), array('PUT', 'DELETE', 'PROPPATCH', 'MOVE', 'COPY', 'PATCH'), true);
	}

	protected function getCurrentETag()
	{
		if(isset($this->objectData['etag']) && $this->objectData['etag'] !== '')
			return $this->objectData['etag'];

		return parent::getETag();
	}
}
