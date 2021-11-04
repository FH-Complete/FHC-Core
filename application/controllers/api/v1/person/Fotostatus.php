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

class Fotostatus extends API_Controller
{
	/**
	 * Fotostatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Fotostatus' => 'basis/fotostatus:rw'));
		// Load model FotostatusModel
		$this->load->model('person/fotostatus_model', 'FotostatusModel');


	}

	/**
	 * @return void
	 */
	public function getFotostatus()
	{
		$fotostatus_kurzbz = $this->get('fotostatus_kurzbz');

		if (isset($fotostatus_kurzbz))
		{
			$result = $this->FotostatusModel->load($fotostatus_kurzbz);

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
	public function postFotostatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['fotostatus_kurzbz']))
			{
				$result = $this->FotostatusModel->update($this->post()['fotostatus_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->FotostatusModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($fotostatus = NULL)
	{
		return true;
	}
}
