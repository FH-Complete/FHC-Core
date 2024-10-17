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

class Aufnahmetermin extends API_Controller
{
	/**
	 * Aufnahmetermin API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Aufnahmetermin' => 'basis/aufnahmetermin:rw'));
		// Load model AufnahmeterminModel
		$this->load->model('crm/aufnahmetermin_model', 'AufnahmeterminModel');


	}

	/**
	 * @return void
	 */
	public function getAufnahmetermin()
	{
		$aufnahmeterminID = $this->get('aufnahmetermin_id');

		if (isset($aufnahmeterminID))
		{
			$result = $this->AufnahmeterminModel->load($aufnahmeterminID);

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
	public function postAufnahmetermin()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['aufnahmetermin_id']))
			{
				$result = $this->AufnahmeterminModel->update($this->post()['aufnahmetermin_id'], $this->post());
			}
			else
			{
				$result = $this->AufnahmeterminModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($aufnahmetermin = NULL)
	{
		return true;
	}
}
