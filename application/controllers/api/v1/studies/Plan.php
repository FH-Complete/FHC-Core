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

class Plan extends REST_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('studies/plan_model', 'PlanModel');
		// Load set the addonID of the model to let to check the permissions
		$this->PlanModel->setAddonID($this->_getAddonID());
	}
	
	public function getCurricula()
	{
		$courseOfStudiesID = $this->get('studiengang_kz');
		
		$result = $this->PlanModel->getCurricula($courseOfStudiesID);
		
		if(!is_null($result) && $result->num_rows() > 0)
		{
			$payload = [
				'success'	=>	TRUE,
				'message'	=>	'Curricula found',
				'data'		=>	$result->result()[0]
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		else
		{
			$payload = [
				'success'	=>	FALSE,
				'message'	=>	'Curricula not found'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		
		$this->response($payload, $httpstatus);
	}
}