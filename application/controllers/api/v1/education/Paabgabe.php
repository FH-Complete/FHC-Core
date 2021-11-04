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

class Paabgabe extends API_Controller
{
	/**
	 * Paabgabe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Paabgabe' => 'basis/paabgabe:rw'));
		// Load model PaabgabeModel
		$this->load->model('education/Paabgabe_model', 'PaabgabeModel');
	}

	/**
	 * @return void
	 */
	public function getPaabgabe()
	{
		$paabgabe_id = $this->get('paabgabe_id');

		if (isset($paabgabe_id))
		{
			$result = $this->PaabgabeModel->load($paabgabe_id);

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
	public function postPaabgabe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['paabgabe_id']))
			{
				$result = $this->PaabgabeModel->update($this->post()['paabgabe_id'], $this->post());
			}
			else
			{
				$result = $this->PaabgabeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($paabgabe = NULL)
	{
		return true;
	}
}
