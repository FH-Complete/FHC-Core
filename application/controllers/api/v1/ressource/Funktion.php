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

class Funktion extends API_Controller
{
	/**
	 * Funktion API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Funktion' => 'basis/funktion:rw'));
		// Load model FunktionModel
		$this->load->model('ressource/funktion_model', 'FunktionModel');


	}

	/**
	 * @return void
	 */
	public function getFunktion()
	{
		$funktion_kurzbz = $this->get('funktion_kurzbz');

		if (isset($funktion_kurzbz))
		{
			$result = $this->FunktionModel->load($funktion_kurzbz);

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
	public function postFunktion()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['funktion_kurzbz']))
			{
				$result = $this->FunktionModel->update($this->post()['funktion_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->FunktionModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($funktion = NULL)
	{
		return true;
	}
}
