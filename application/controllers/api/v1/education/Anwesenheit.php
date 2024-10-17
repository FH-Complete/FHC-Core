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

class Anwesenheit extends API_Controller
{
	/**
	 * Anwesenheit API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Anwesenheit' => 'basis/anwesenheit:rw'));
		// Load model AnwesenheitModel
		$this->load->model('education/Anwesenheit_model', 'AnwesenheitModel');
	}

	/**
	 * @return void
	 */
	public function getAnwesenheit()
	{
		$anwesenheit_id = $this->get('anwesenheit_id');

		if (isset($anwesenheit_id))
		{
			$result = $this->AnwesenheitModel->load($anwesenheit_id);

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
	public function postAnwesenheit()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['anwesenheit_id']))
			{
				$result = $this->AnwesenheitModel->update($this->post()['anwesenheit_id'], $this->post());
			}
			else
			{
				$result = $this->AnwesenheitModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($anwesenheit = NULL)
	{
		return true;
	}
}
