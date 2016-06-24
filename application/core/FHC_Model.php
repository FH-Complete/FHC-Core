<?php

if (! defined('BASEPATH')) exit('No direct script access allowed');

class FHC_Model extends CI_Model
{
	function __construct()
	{
		parent::__construct();
		
		$this->lang->load('fhc_model');
		$this->lang->load('fhcomplete');
		
		$this->load->helper('language');
		$this->load->helper('Message');
		$this->load->helper('fhcauth');
		
		$this->load->library('FHC_DB_ACL');
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
}