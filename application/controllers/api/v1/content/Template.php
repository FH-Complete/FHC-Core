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

class Template extends APIv1_Controller
{
	/**
	 * Template API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model TemplateModel
		$this->load->model('content/template_model', 'TemplateModel');
		// Load set the uid of the model to let to check the permissions
		$this->TemplateModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getTemplate()
	{
		$templateID = $this->get('template_id');
		
		if(isset($templateID))
		{
			$result = $this->TemplateModel->load($templateID);
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * @return void
	 */
	public function postTemplate()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['template_id']))
			{
				$result = $this->TemplateModel->update($this->post()['template_id'], $this->post());
			}
			else
			{
				$result = $this->TemplateModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($template = NULL)
	{
		return true;
	}
}