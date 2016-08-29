<?php
/**
 * FH-Complete
 *
 * @package		FHC-API
 * @author		FHC-Team
 * @copyright	Copyright (c) 2016, fhcomplete.org
 * @license		GPLv3
 * @link		http://fhcomplete.org
 * @since		Version 1.0
 * @filesource
 */
// ------------------------------------------------------------------------

if (!defined("BASEPATH")) exit("No direct script access allowed");

class Dms extends APIv1_Controller
{
	/**
	 * 
	 */
	public function __construct()
	{
		parent::__construct();
		// Load library DmsLib
		$this->load->library("DmsLib");
	}
	
	/**
	 * 
	 */
	public function getDms()
	{
		$dms_id = $this->get("dms_id");
		$version = $this->get("version");
		
		if (isset($dms_id))
		{
			$result = $this->dmslib->read($dms_id, $version);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	/**
	 * 
	 */
	public function postDms()
	{
		$dms = $this->_parseData($this->post());
		
		if ($this->_validate($dms))
		{
			$result = $this->dmslib->save($dms);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($dms = NULL)
	{
		if (!isset($dms["file_content"]) || (isset($dms["file_content"]) && $dms["file_content"] == ""))
		{
			return false;
		}
		if (!isset($dms["name"]) || (isset($dms["name"]) && $dms["name"] == ""))
		{
			return false;
		}
		
		return true;
	}
}