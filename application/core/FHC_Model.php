<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	protected $acl;
	
	function __construct()
	{
		parent::__construct();
		
		$this->lang->load('fhc_model');
		$this->lang->load('fhcomplete');
		
		$this->load->helper('language');
		$this->load->helper('Message');
		$this->load->helper('fhcauth');
		
		$this->load->library('FHC_DB_ACL');
		
		$this->acl = $this->config->item('fhc_acl');
	}

	/** ---------------------------------------------------------------
	 * Success
	 *
	 * @param   mixed  $retval
	 * @return  array
	 */
	protected function _success($retval, $message = null)
	{
		return success($retval, $message);
	}

	/** ---------------------------------------------------------------
	 * General Error
	 *
	 * @return  array
	 */
	protected function _error($retval, $message = null)
	{
		return error($retval, $message);
	}
	
	protected function getBerechtigungKurzbz($sourceName)
	{
		if (isset($this->acl[$sourceName]))
		{
			return $this->acl[$sourceName];
		}
		else
		{
			return null;
		}
	}
}