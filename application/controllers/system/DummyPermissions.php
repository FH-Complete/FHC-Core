<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

/**
 *
 */
class DummyPermissions extends Auth_Controller
{
	/**
	 *
	 */
	public function __construct()
	{
		parent::__construct(
			array(
				'permissionToMainRole' => 'dummymainpermission:r',
				'permissionToBasicRole' => 'dummybasicpermission:r',
				'permissionToUser' => 'dummyuserpermission:r',
			)
		);
	}

	// 
	public function permissionToMainRole() { echo __METHOD__; }
	// 
	public function permissionToBasicRole() { echo __METHOD__; }
	// 
	public function permissionToUser() { echo __METHOD__; }
}

