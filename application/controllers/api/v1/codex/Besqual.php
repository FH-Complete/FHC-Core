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

class Besqual extends API_Controller
{
	/**
	 * Besqual API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Besqual' => 'basis/besqual:rw'));
		// Load model BesqualModel
		$this->load->model('codex/besqual_model', 'BesqualModel');
	}

	/**
	 * @return void
	 */
	public function getBesqual()
	{
		$besqualcode = $this->get('besqualcode');

		if (isset($besqualcode))
		{
			$result = $this->BesqualModel->load($besqualcode);

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
	public function postBesqual()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['besqualcode']))
			{
				$result = $this->BesqualModel->update($this->post()['besqualcode'], $this->post());
			}
			else
			{
				$result = $this->BesqualModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($besqual = NULL)
	{
		return true;
	}
}
