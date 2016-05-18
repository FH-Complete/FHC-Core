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

class Geschaeftsjahr extends APIv1_Controller
{
	/**
	 * Geschaeftsjahr API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model GeschaeftsjahrModel
		$this->load->model('organisation/geschaeftsjahr_model', 'GeschaeftsjahrModel');
		// Load set the uid of the model to let to check the permissions
		$this->GeschaeftsjahrModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getGeschaeftsjahr()
	{
		$geschaeftsjahrID = $this->get('geschaeftsjahr_id');
		
		if(isset($geschaeftsjahrID))
		{
			$result = $this->GeschaeftsjahrModel->load($geschaeftsjahrID);
			
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
	public function postGeschaeftsjahr()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['geschaeftsjahr_id']))
			{
				$result = $this->GeschaeftsjahrModel->update($this->post()['geschaeftsjahr_id'], $this->post());
			}
			else
			{
				$result = $this->GeschaeftsjahrModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($geschaeftsjahr = NULL)
	{
		return true;
	}
}