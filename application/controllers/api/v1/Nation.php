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

class Nation extends APIv1_Controller
{
	/**
	 * Course API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PersonModel
		$this->load->model('nation_model', 'NationModel');
		// Load set the addonID of the model to let to check the permissions
		$this->NationModel->setAddonID($this->_getAddonID());
	}
	
	public function getAll()
	{
		$result = $this->NationModel->getAll($this->get('ohnesperre'), $this->get('orderEnglish'));
		
		if(is_object($result))
		{
			$payload = [
				'success'	=>	TRUE,
				'message'	=>	'Nation found',
				'data'		=>	$result->result()
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		else
		{
			$payload = [
				'success'	=>	FALSE,
				'message'	=>	'Nation not found'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		
		$this->response($payload, $httpstatus);
	}
	
	public function getBundesland()
	{
		$result = $this->NationModel->getBundesland();
		
		if(is_object($result))
		{
			$payload = [
				'success'	=>	TRUE,
				'message'	=>	'Bundesland found',
				'data'		=>	$result->result()
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		else
		{
			$payload = [
				'success'	=>	FALSE,
				'message'	=>	'Bundesland not found'
			];
			$httpstatus = REST_Controller::HTTP_OK;
		}
		
		$this->response($payload, $httpstatus);
	}
}