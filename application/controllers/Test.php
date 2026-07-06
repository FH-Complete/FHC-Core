<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

/**
 */
class Test extends FHC_Controller
{
	/**
	 * Constructor
	 */
	public function __construct()
	{
        parent::__construct();
		$this->load->library('sabredav/SabreDAVAuthLib');
        $this->load->library('sabredav/SabreDAVCalDAVLib');
	}

	// -----------------------------------------------------------------------------------------------------------------
	// Public methods
	public function index()
	{
       
        $authBackend = new SabreDAVAuthLib();
        $principalBackend = new SabreDAVACLPrincipalBackend($authBackend);
        $calendarBackend = new SabreDAVCalDAVBackend($authBackend);

        $tree = array(
            new \Sabre\CalDAV\Principal\Collection($principalBackend),
            new SabreDAVReadOnlyCalendarRoot($principalBackend, $calendarBackend)
        );

        $server = new \Sabre\DAV\Server($tree);

        $path = "/ma1433/core/FHC-Core/index.ci.php/test"; 
        $server->setBaseUri($path);

        $authBackend->setRealm('SabreDAV');
        $authPlugin = new \Sabre\DAV\Auth\Plugin($authBackend);
        $server->addPlugin($authPlugin);

        $caldavPlugin = new \Sabre\CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        $aclPlugin = new \Sabre\DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        $browser = new \Sabre\DAV\Browser\Plugin();
        $server->addPlugin($browser);

        $server->exec();
	}
}
