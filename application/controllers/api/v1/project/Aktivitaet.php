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

class Aktivitaet extends API_Controller
{
	/**
	 * Aktivitaet API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Aktivitaet' => 'basis/aktivitaet:rw'));
		// Load model AktivitaetModel
		$this->load->model('project/aktivitaet_model', 'AktivitaetModel');


	}

	/**
	 * @return void
	 */
	public function getAktivitaet()
	{
		$aktivitaet_kurzbz = $this->get('aktivitaet_kurzbz');

		if (isset($aktivitaet_kurzbz))
		{
			$result = $this->AktivitaetModel->load($aktivitaet_kurzbz);

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
	public function postAktivitaet()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['aktivitaet_kurzbz']))
			{
				$result = $this->AktivitaetModel->update($this->post()['aktivitaet_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->AktivitaetModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($aktivitaet = NULL)
	{
		return true;
	}
}
