<?php
// tests/Integration/ApiControllerTest.php

use PHPUnit\Framework\TestCase;

class BookmarkIntegrationTest extends TestCase
{
	/** @var CI_Controller */
	protected $CI;

	public function setUp()
	{
		// Grab the global CI instance
		$this->CI =& get_instance();

		// Make sure we're in a clean state:
		//  - no lingering URI segments
		//  - fresh router rules
		$this->CI->uri->uri_string = '';
		$this->CI->router->_set_routing([]);
	}

	/**
	 * Simulate a GET to /api/users and assert JSON list
	 */
	public function testGetUsersReturnsJsonArray()
	{
		// 1) Fake server variables
		$_SERVER['REQUEST_METHOD'] = 'GET';
		// you can use PATH_INFO or REQUEST_URI depending on your config
		$_SERVER['PATH_INFO']     = '/api/users';
		$_GET = []; $_POST = [];

		// 2) Re-run routing so CI picks up our fake path
		$this->CI->router->_set_routing();
		$this->CI->uri->_set_uri_string('/api/users');
		$this->CI->router->set_class('api');
		$this->CI->router->set_method('users');

		// 3) Capture the output
		ob_start();
		// This is the entry point:
		//   core/CodeIgniter.php will look at $RTR->class/method
		//   instantiate your Api controller, call ->users()
		require BASEPATH . 'core/CodeIgniter.php';
		$output = ob_get_clean();

		// 4) Assert JSON shape, status header, etc.
		$this->assertNotEmpty($output, 'No output at all');
		$decoded = json_decode($output, true);
		$this->assertIsArray($decoded, 'Expected JSON array');
		// more fine‐grained assertions…
	}

	/**
	 * Simulate a POST to /api/users
	 */
	public function testCreateUser()
	{
		$_SERVER['REQUEST_METHOD'] = 'POST';
		$_SERVER['PATH_INFO']     = '/api/users';
		$_POST = [
			'name'  => 'Alice',
			'email' => 'alice@example.com',
		];
		$_GET = [];

		$this->CI->router->_set_routing();
		$this->CI->uri->_set_uri_string('/api/users');
		$this->CI->router->set_class('api');
		$this->CI->router->set_method('users');

		ob_start();
		require BASEPATH . 'core/CodeIgniter.php';
		$out = ob_get_clean();

		$this->assertStringContainsString('"id":', $out);
		$this->assertMatchesRegularExpression('/201 Created/', xdebug_get_headers());
	}
}
