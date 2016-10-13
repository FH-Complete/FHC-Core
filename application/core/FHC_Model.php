<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		
		// Load languages files
		$this->lang->load('fhc_model');
		$this->lang->load('fhcomplete');
		
		// Load return message helper
		$this->load->helper('message');
		
		// Loads the permission library
		$this->load->library('PermissionLib');
	}
	
	/**
	 * Check if the user is entitled to get access to a source with the given access type
	 * This is a wrapper for the same method present in the PermissionLib
	 */
	public function isEntitled($sourceName, $accessType, $languageMessageCode, $msgErrorCode)
	{
		if ($this->permissionlib->isEntitled($sourceName, $accessType) === false)
		{
			$retval = sprintf(
				'%s -> %s:%s',
				lang('fhc_' . $languageMessageCode),
				$this->permissionlib->getBerechtigungKurzbz($sourceName),
				$accessType
			);
			return error($retval, $msgErrorCode);
		}
		else
		{
			return true;
		}
	}
}