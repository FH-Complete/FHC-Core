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

class Aufwandstyp extends API_Controller
{
	/**
	 * Aufwandstyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Aufwandstyp' => 'basis/aufwandstyp:rw'));
		// Load model AufwandstypModel
		$this->load->model('project/aufwandstyp_model', 'AufwandstypModel');


	}

	/**
	 * @return void
	 */
	public function getAufwandstyp()
	{
		$aufwandstyp_kurzbz = $this->get('aufwandstyp_kurzbz');

		if (isset($aufwandstyp_kurzbz))
		{
			$result = $this->AufwandstypModel->load($aufwandstyp_kurzbz);

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
	public function postAufwandstyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['aufwandstyp_kurzbz']))
			{
				$result = $this->AufwandstypModel->update($this->post()['aufwandstyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->AufwandstypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($aufwandstyp = NULL)
	{
		return true;
	}
}
