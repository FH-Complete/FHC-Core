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

class Betriebsmitteltyp extends APIv1_Controller
{
	/**
	 * Betriebsmitteltyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BetriebsmitteltypModel
		$this->load->model('ressource/betriebsmitteltyp_model', 'BetriebsmitteltypModel');
		// Load set the uid of the model to let to check the permissions
		$this->BetriebsmitteltypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBetriebsmitteltyp()
	{
		$betriebsmitteltypID = $this->get('betriebsmitteltyp_id');
		
		if(isset($betriebsmitteltypID))
		{
			$result = $this->BetriebsmitteltypModel->load($betriebsmitteltypID);
			
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
	public function postBetriebsmitteltyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['betriebsmitteltyp_id']))
			{
				$result = $this->BetriebsmitteltypModel->update($this->post()['betriebsmitteltyp_id'], $this->post());
			}
			else
			{
				$result = $this->BetriebsmitteltypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($betriebsmitteltyp = NULL)
	{
		return true;
	}
}