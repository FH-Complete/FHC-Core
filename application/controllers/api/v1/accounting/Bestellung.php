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

class Bestellung extends APIv1_Controller
{
	/**
	 * Bestellung API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model BestellungModel
		$this->load->model('accounting/bestellung_model', 'BestellungModel');
		// Load set the uid of the model to let to check the permissions
		$this->BestellungModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getBestellung()
	{
		$bestellungID = $this->get('bestellung_id');
		
		if (isset($bestellungID))
		{
			$result = $this->BestellungModel->load($bestellungID);
			
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
	public function postBestellung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bestellung_id']))
			{
				$result = $this->BestellungModel->update($this->post()['bestellung_id'], $this->post());
			}
			else
			{
				$result = $this->BestellungModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($bestellung = NULL)
	{
		return true;
	}
}