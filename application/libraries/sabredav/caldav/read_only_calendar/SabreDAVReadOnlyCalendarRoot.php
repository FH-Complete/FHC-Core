<?php

class SabreDAVReadOnlyCalendarRoot extends \Sabre\CalDAV\CalendarRoot
{
	protected $readOnlyCaldavBackend;

	public function __construct(\Sabre\DAVACL\PrincipalBackend\BackendInterface $principalBackend, \Sabre\CalDAV\Backend\BackendInterface $caldavBackend, $principalPrefix = 'principals')
	{
		parent::__construct($principalBackend, $caldavBackend, $principalPrefix);
		$this->readOnlyCaldavBackend = $caldavBackend;
	}

	public function getChildForPrincipal(array $principal)
	{
		return new SabreDAVReadOnlyCalendarHome($this->readOnlyCaldavBackend, $principal);
	}
}
