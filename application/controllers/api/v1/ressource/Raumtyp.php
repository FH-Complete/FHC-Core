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

class Raumtyp extends API_Controller
{
	/**
	 * Raumtyp API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Raumtyp' => 'basis/raumtyp:rw'));
		// Load model RaumtypModel
		$this->load->model('ressource/raumtyp_model', 'RaumtypModel');


	}

	/**
	 * @return void
	 */
	public function getRaumtyp()
	{
		$raumtyp_kurzbz = $this->get('raumtyp_kurzbz');

		if (isset($raumtyp_kurzbz))
		{
			$result = $this->RaumtypModel->load($raumtyp_kurzbz);

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
	public function postRaumtyp()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['raumtyp_kurzbz']))
			{
				$result = $this->RaumtypModel->update($this->post()['raumtyp_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->RaumtypModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($raumtyp = NULL)
	{
		return true;
	}
}
