<?php
// tests/Integration/CIIntegrationTestCase.php

abstract class CIIntegrationTestCase extends PHPUnit\Framework\TestCase
{
	/** @var CI_Controller */
	protected $CI;

	protected function setUp()
	{
		// grab the existing CI “superobject”
		$this->CI =& get_instance();

		// ensure any old URI/router state is cleared
		$this->CI->uri->uri_string = '';
		$this->CI->router->_set_routing([]);
	}

	/**
	 * Simulate an HTTP request through CI’s front controller.
	 *
	 * @param string $method  GET|POST|PUT|DELETE
	 * @param string $uri     URI string, e.g. "bookmark/getBookmarks"
	 * @param array  $params  query‐params if GET, post‐data otherwise
	 * @return string         raw response body
	 */
	protected function request(string $method, string $uri, array $params = []): string
	{
		// 1) fake the server vars
		$_SERVER['REQUEST_METHOD'] = strtoupper($method);
		$_SERVER['PATH_INFO']     = '/' . ltrim($uri, '/');
		if (strtoupper($method) === 'GET') {
			$_GET  = $params;
			$_POST = [];
		} else {
			$_POST = $params;
			$_GET  = [];
		}

		// 2) re-initialize routing/URI so CI picks up our fake PATH_INFO
		$this->CI->router->_set_routing();
		$this->CI->uri->_set_uri_string($_SERVER['PATH_INFO']);
		// pull class/method out of the path
		$segments = explode('/', trim($_SERVER['PATH_INFO'], '/'));
		$class    = array_shift($segments) ?: 'welcome';
		$method   = array_shift($segments) ?: 'index';
		$this->CI->router->set_class($class);
		$this->CI->router->set_method($method);

		// 3) capture output
		ob_start();
		require BASEPATH . 'core/CodeIgniter.php';
		return ob_get_clean();
	}
}
