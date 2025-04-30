<?php

namespace BookmarkTest;
echo "BookmarkTest loaded\n";


echo "Resolved path: " . APPPATH.'controllers/api/frontend/v1/Bookmark.php' . "\n";


//require_once(APPPATH.'controllers/api/frontend/v1/Bookmark.php');
//require_once(APPPATH.'libraries/AuthLib.php');

use Bookmark;
use PHPUnit\Framework\TestCase;

class BookmarkTest extends TestCase
{
	private $_ci;

//	public function setUp(): void
//	{
//		error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
//
//		echo 'in the setUp';
//		// Load CodeIgniter instance
//		$this->_ci = &get_instance();
//
//		if (!is_object($this->_ci)) {
//			throw new \Exception('CI instance is not available');
//		}
//		
//		$this->_ci->load->library('AuthLib', null, 'AuthLib');
//
//		$this->_ci->AuthLib->loginLDAP('horauer', 'FHCompleteDemo42!');
//		$this->_ci->load->controller('api/v1/Bookmark');
//
//		$this->obj = new Bookmark();
//	}

	/** @test */
	public function test_true()
	{
		echo 'in the test_true case';
		$this->assertTrue(true);
	}


}