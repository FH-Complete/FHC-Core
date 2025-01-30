<?php
use PHPUnit\Framework\TestCase;

class CI_TestCase extends TestCase
{
	public $_ci;
	
	public function setUp()
	{
		$this->_ci =& get_instance();
	}
	
	public function __get($name)
	{
		return $this->_ci->$name;
	}
}
