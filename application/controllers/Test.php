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
        $principalBackend = new SabreDAVACLPrincipalBackendLib($authBackend);
        $calendarBackend = new SabreDAVCalDAVBackendLib($authBackend);

        $tree = array(
            new \Sabre\CalDAV\Principal\Collection($principalBackend),
            new SabreDAVReadOnlyCalendarRootLib($principalBackend, $calendarBackend)
        );

        $server = new \Sabre\DAV\Server($tree);

        $path = "/ma1433/core/FHC-Core/index.ci.php/test"; 
        $server->setBaseUri($path);

        $authBackend->setRealm('SabreDAV');
        $authPlugin = new \Sabre\DAV\Auth\Plugin($authBackend);
        $server->addPlugin($authPlugin);

        $this->_registerReadOnlyWriteInterceptor($server);

        $caldavPlugin = new \Sabre\CalDAV\Plugin();
        $server->addPlugin($caldavPlugin);

        $aclPlugin = new \Sabre\DAVACL\Plugin();
        $server->addPlugin($aclPlugin);

        $browser = new \Sabre\DAV\Browser\Plugin();
        $server->addPlugin($browser);

        $server->exec();
	}

	private function _registerReadOnlyWriteInterceptor($server)
	{
		$callback = function() use ($server) {
			return $this->_discardReadOnlyWriteRequest($server, func_get_args());
		};

		if (method_exists($server, 'on'))
		{
			$server->on('beforeMethod', $callback, 20);
			return;
		}

		if (method_exists($server, 'subscribeEvent'))
		{
			$server->subscribeEvent('beforeMethod', $callback, 20);
		}
	}

	private function _discardReadOnlyWriteRequest($server, array $eventArgs)
	{
		$method = $this->_getDavRequestMethod($eventArgs);
		if (!in_array($method, array('PUT', 'DELETE', 'PROPPATCH', 'MKCOL', 'MOVE', 'COPY'), true))
			return true;

		$this->_sendReadOnlyWriteAcceptedResponse($server, $eventArgs);
		return false;
	}

	private function _getDavRequestMethod(array $eventArgs)
	{
		foreach ($eventArgs as $argument)
		{
			if (is_string($argument))
				return strtoupper($argument);

			if (is_object($argument) && method_exists($argument, 'getMethod'))
				return strtoupper($argument->getMethod());
		}

		return isset($_SERVER['REQUEST_METHOD']) ? strtoupper($_SERVER['REQUEST_METHOD']) : '';
	}

	private function _sendReadOnlyWriteAcceptedResponse($server, array $eventArgs)
	{
		$response = $this->_getDavResponse($server, $eventArgs);

		if (is_object($response))
		{
			if (method_exists($response, 'setStatus'))
			{
				$response->setStatus(204);
				if (method_exists($response, 'setHeader'))
					$response->setHeader('Content-Length', '0');
				if (method_exists($response, 'setBody'))
					$response->setBody('');
				return;
			}

			if (method_exists($response, 'sendStatus'))
			{
				if (method_exists($response, 'setHeader'))
					$response->setHeader('Content-Length', '0');
				$response->sendStatus(204);
				return;
			}
		}

		if (!headers_sent())
		{
			header('HTTP/1.1 204 No Content');
			header('Content-Length: 0');
		}
	}

	private function _getDavResponse($server, array $eventArgs)
	{
		foreach ($eventArgs as $argument)
		{
			if (is_object($argument) && (
				method_exists($argument, 'setStatus')
				|| method_exists($argument, 'sendStatus')
			))
			{
				return $argument;
			}
		}

		return isset($server->httpResponse) ? $server->httpResponse : null;
	}
}
