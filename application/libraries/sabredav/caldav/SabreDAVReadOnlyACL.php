<?php

class SabreDAVReadOnlyACL
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
