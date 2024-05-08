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

class Abgabe extends API_Controller
{
	/**
	 * Abgabe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Abgabe' => 'basis/abgabe:rw'));
		// Load model AbgabeModel
		$this->load->model('education/Abgabe_model', 'AbgabeModel');
	}

	/**
	 * @return void
	 */
	public function getAbgabe()
	{
		$abgabe_id = $this->get('abgabe_id');

		if (isset($abgabe_id))
		{
			$result = $this->AbgabeModel->load($abgabe_id);

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
	public function postAbgabe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['abgabe_id']))
			{
				$result = $this->AbgabeModel->update($this->post()['abgabe_id'], $this->post());
			}
			else
			{
				$result = $this->AbgabeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($abgabe = NULL)
	{
		return true;
	}
}
