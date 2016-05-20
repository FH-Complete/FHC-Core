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

class Paabgabetyp extends APIv1_Controller
{
	/**
	 * Paabgabetyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		// Load model PaabgabetypModel
		$this->load->model('education/paabgabetyp', 'PaabgabetypModel');
		// Load set the uid of the model to let to check the permissions
		$this->PaabgabetypModel->setUID($this->_getUID());
	}

	/**
	 * @return void
	 */
	public function getPaabgabetyp()
	{
		$paabgabetyp_kurzbz = $this->get('paabgabetyp_kurzbz');
		
		if(isset($paabgabetyp_kurzbz))
		{
			$result = $this->PaabgabetypModel->load($paabgabetyp_kurzbz);
			
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
	public function postPaabgabetyp()
	{
		if($this->_validate($this->post()))
		{
			if(isset($this->post()['paabgabetyp_kurzbz']))
			{
				$result = $this->PaabgabetypModel->update($this->post()['paabgabetyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->PaabgabetypModel->insert($this->post());
			}
			
			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}
	
	private function _validate($paabgabetyp = NULL)
	{
		return true;
	}
}