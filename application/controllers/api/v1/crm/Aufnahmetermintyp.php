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

class Aufnahmetermintyp extends API_Controller
{
	/**
	 * Aufnahmetermintyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Aufnahmetermintyp' => 'basis/aufnahmetermintyp:rw'));
		// Load model AufnahmetermintypModel
		$this->load->model('crm/aufnahmetermintyp_model', 'AufnahmetermintypModel');


	}

	/**
	 * @return void
	 */
	public function getAufnahmetermintyp()
	{
		$aufnahmetermintyp_kurzbz = $this->get('aufnahmetermintyp_kurzbz');

		if (isset($aufnahmetermintyp_kurzbz))
		{
			$result = $this->AufnahmetermintypModel->load($aufnahmetermintyp_kurzbz);

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
	public function postAufnahmetermintyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['aufnahmetermintyp_kurzbz']))
			{
				$result = $this->AufnahmetermintypModel->update($this->post()['aufnahmetermintyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->AufnahmetermintypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($aufnahmetermintyp = NULL)
	{
		return true;
	}
}
