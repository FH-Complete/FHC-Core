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

class Studienjahr extends APIv1_Controller
{
	/**
	 * Studienjahr API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model StudienjahrModel
		$this->load->model('organisation/studienjahr_model', 'StudienjahrModel');
		// Load set the uid of the model to let to check the permissions
		$this->StudienjahrModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getStudienjahr()
	{
		$studienjahrID = $this->get('studienjahr_id');
		
		if(isset($studienjahrID))
		{
			$result = $this->StudienjahrModel->load($studienjahrID);
			
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
	public function postStudienjahr()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['studienjahr_id']))
			{
				$result = $this->StudienjahrModel->update($this->post()['studienjahr_id'], $this->post());
			}
			else
			{
				$result = $this->StudienjahrModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($studienjahr = NULL)
	{
		return true;
	}
}