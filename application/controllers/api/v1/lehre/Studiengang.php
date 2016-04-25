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

if(!defined('BASEPATH')) exit('No direct script access allowed');

class Studiengang extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('lehre/studiengang_model', 'StudiengangModel');
		// Load set the addonID of the model to let to check the permissions
		$this->StudiengangModel->setAddonID($this->_getAddonID());
	}
	
	public function getAllForBewerbung()
	{
		$result = $this->StudiengangModel->getAllForBewerbung();
		
		if(is_object($result) && $result->num_rows() > 0)
		{
			$payload = [
				'success'	=>	TRUE,
				'message'	=>	'Courses found',
				'data'		=>	$result->result()
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		else
		{
			$payload = [
				'success'	=>	FALSE,
				'message'	=>	'No courses found'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		
		$this->response($payload, $httpstatus);
	}
}