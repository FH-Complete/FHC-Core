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

class Lehreinheit extends API_Controller
{
	/**
	 * Lehreinheit API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehreinheit' => 'basis/lehreinheit:rw'));
		// Load model LehreinheitModel
		$this->load->model('education/Lehreinheit_model', 'LehreinheitModel');
	}

	/**
	 * @return void
	 */
	public function getLehreinheit()
	{
		$lehreinheit_id = $this->get('lehreinheit_id');

		if (isset($lehreinheit_id))
		{
			$result = $this->LehreinheitModel->load($lehreinheit_id);

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
	public function postLehreinheit()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lehreinheit_id']))
			{
				$result = $this->LehreinheitModel->update($this->post()['lehreinheit_id'], $this->post());
			}
			else
			{
				$result = $this->LehreinheitModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehreinheit = NULL)
	{
		return true;
	}
}
