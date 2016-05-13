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

class Bestellungtag extends APIv1_Controller
{
	/**
	 * Bestellungtag API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BestellungtagModel
		$this->load->model('accounting/bestellungtag_model', 'BestellungtagModel');
		// Load set the uid of the model to let to check the permissions
		$this->BestellungtagModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBestellungtag()
	{
		$bestellungtagID = $this->get('bestellungtag_id');
		
		if(isset($bestellungtagID))
		{
			$result = $this->BestellungtagModel->load($bestellungtagID);
			
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
	public function postBestellungtag()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['bestellungtag_id']))
			{
				$result = $this->BestellungtagModel->update($this->post()['bestellungtag_id'], $this->post());
			}
			else
			{
				$result = $this->BestellungtagModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($bestellungtag = NULL)
	{
		return true;
	}
}