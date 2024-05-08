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

class Vertragsstatus extends API_Controller
{
	/**
	 * Vertragsstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Vertragsstatus' => 'basis/vertragsstatus:rw'));
		// Load model VertragsstatusModel
		$this->load->model('accounting/vertragsstatus_model', 'VertragsstatusModel');
	}

	/**
	 * @return void
	 */
	public function getVertragsstatus()
	{
		$vertragsstatus_kurzbz = $this->get('vertragsstatus_kurzbz');

		if (isset($vertragsstatus_kurzbz))
		{
			$result = $this->VertragsstatusModel->load($vertragsstatus_kurzbz);

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
	public function postVertragsstatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['vertragsstatus_kurzbz']))
			{
				$result = $this->VertragsstatusModel->update($this->post()['vertragsstatus_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->VertragsstatusModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($vertragsstatus = NULL)
	{
		return true;
	}
}
