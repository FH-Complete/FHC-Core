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

class Pruefungsfenster extends APIv1_Controller
{
	/**
	 * Pruefungsfenster API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PruefungsfensterModel
		$this->load->model('education/pruefungsfenster', 'PruefungsfensterModel');
		// Load set the uid of the model to let to check the permissions
		$this->PruefungsfensterModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getPruefungsfenster()
	{
		$pruefungsfenster_id = $this->get('pruefungsfenster_id');
		
		if(isset($pruefungsfenster_id))
		{
			$result = $this->PruefungsfensterModel->load($pruefungsfenster_id);
			
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
	public function postPruefungsfenster()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['pruefungsfenster_id']))
			{
				$result = $this->PruefungsfensterModel->update($this->post()['pruefungsfenster_id'], $this->post());
			}
			else
			{
				$result = $this->PruefungsfensterModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($pruefungsfenster = NULL)
	{
		return true;
	}
}