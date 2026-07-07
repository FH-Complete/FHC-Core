<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class TestPermissions extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'mainRoleR' => 'dummymainroler:r',
				'mainRoleRW' => 'dummymainrolerw:rw',
				'mainRoleW' => 'dummymainrolew:w',
				'basicRoleR' => 'dummybasicroler:r',
				'basicRoleRW' => 'dummybasicrolerw:rw',
				'basicRoleW' => 'dummybasicrolew:w',
				'singlePermissionR' => 'dummypermissionr:r',
				'singlePermissionRW' => 'dummypermissionrw:rw',
				'singlePermissionW' => 'dummypermissionw:w',
			)
		);
	}

	// 
	public function mainRoleR() { echo __METHOD__; }
	// 
	public function mainRoleRW() { echo __METHOD__; }
	// 
	public function mainRoleW() { echo __METHOD__; }
	// 
	public function basicRoleR() { echo __METHOD__; }
	// 
	public function basicRoleRW() { echo __METHOD__; }
	// 
	public function basicRoleW() { echo __METHOD__; }
	// 
	public function singlePermissionR() { echo __METHOD__; }
	// 
	public function singlePermissionRW() { echo __METHOD__; }
	// 
	public function singlePermissionW() { echo __METHOD__; }
}

