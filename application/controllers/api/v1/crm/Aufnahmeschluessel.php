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

class Aufnahmeschluessel extends API_Controller
{
	/**
	 * Aufnahmeschluessel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Aufnahmeschluessel' => 'basis/aufnahmeschluessel:rw'));
		// Load model AufnahmeschluesselModel
		$this->load->model('crm/aufnahmeschluessel_model', 'AufnahmeschluesselModel');


	}

	/**
	 * @return void
	 */
	public function getAufnahmeschluessel()
	{
		$aufnahmeschluessel = $this->get('aufnahmeschluessel');

		if (isset($aufnahmeschluessel))
		{
			$result = $this->AufnahmeschluesselModel->load($aufnahmeschluessel);

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
	public function postAufnahmeschluessel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['aufnahmeschluessel']))
			{
				$result = $this->AufnahmeschluesselModel->update($this->post()['aufnahmeschluessel'], $this->post());
			}
			else
			{
				$result = $this->AufnahmeschluesselModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($aufnahmeschluessel = NULL)
	{
		return true;
	}
}
