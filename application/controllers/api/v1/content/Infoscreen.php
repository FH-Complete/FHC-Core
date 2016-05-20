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

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Infoscreen extends APIv1_Controller
{
	/**
	 * Infoscreen API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model InfoscreenModel
		$this->load->model('content/infoscreen_model', 'InfoscreenModel');
		// Load set the uid of the model to let to check the permissions
		$this->InfoscreenModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getInfoscreen()
	{
		$infoscreenID = $this->get('infoscreen_id');
		
		if (isset($infoscreenID))
		{
			$result = $this->InfoscreenModel->load($infoscreenID);
			
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
	public function postInfoscreen()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['infoscreen_id']))
			{
				$result = $this->InfoscreenModel->update($this->post()['infoscreen_id'], $this->post());
			}
			else
			{
				$result = $this->InfoscreenModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($infoscreen = NULL)
	{
		return true;
	}
}