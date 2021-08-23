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

class Firma extends API_Controller
{
	/**
	 * Firma API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Firma' => 'basis/firma:rw'));
		// Load model FirmaModel
		$this->load->model('ressource/firma_model', 'FirmaModel');


	}

	/**
	 * @return void
	 */
	public function getFirma()
	{
		$firmaID = $this->get('firma_id');

		if (isset($firmaID))
		{
			$result = $this->FirmaModel->load($firmaID);

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
	public function postFirma()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['firma_id']))
			{
				$result = $this->FirmaModel->update($this->post()['firma_id'], $this->post());
			}
			else
			{
				$result = $this->FirmaModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($firma = NULL)
	{
		return true;
	}
}
