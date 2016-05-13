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

class Rechnungstyp extends APIv1_Controller
{
	/**
	 * Rechnungstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model RechnungstypModel
		$this->load->model('accounting/rechnungstyp_model', 'RechnungstypModel');
		// Load set the uid of the model to let to check the permissions
		$this->RechnungstypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getRechnungstyp()
	{
		$rechnungstypID = $this->get('rechnungstyp_id');
		
		if(isset($rechnungstypID))
		{
			$result = $this->RechnungstypModel->load($rechnungstypID);
			
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
	public function postRechnungstyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['rechnungstyp_id']))
			{
				$result = $this->RechnungstypModel->update($this->post()['rechnungstyp_id'], $this->post());
			}
			else
			{
				$result = $this->RechnungstypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($rechnungstyp = NULL)
	{
		return true;
	}
}