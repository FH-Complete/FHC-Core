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

class Beispiel extends API_Controller
{
	/**
	 * Beispiel API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Beispiel' => 'basis/beispiel:rw'));
		// Load model BeispielModel
		$this->load->model('education/Beispiel_model', 'BeispielModel');
	}

	/**
	 * @return void
	 */
	public function getBeispiel()
	{
		$beispiel_id = $this->get('beispiel_id');

		if (isset($beispiel_id))
		{
			$result = $this->BeispielModel->load($beispiel_id);

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
	public function postBeispiel()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['beispiel_id']))
			{
				$result = $this->BeispielModel->update($this->post()['beispiel_id'], $this->post());
			}
			else
			{
				$result = $this->BeispielModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($beispiel = NULL)
	{
		return true;
	}
}
