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

class Rolle extends API_Controller
{
	/**
	 * Rolle API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Rolle' => 'basis/rolle:rw'));
		// Load model RolleModel
		$this->load->model('system/rolle_model', 'RolleModel');


	}

	/**
	 * @return void
	 */
	public function getRolle()
	{
		$rolle_kurzbz = $this->get('rolle_kurzbz');

		if (isset($rolle_kurzbz))
		{
			$result = $this->RolleModel->load($rolle_kurzbz);

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
	public function postRolle()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['rolle_kurzbz']))
			{
				$result = $this->RolleModel->update($this->post()['rolle_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->RolleModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($rolle = NULL)
	{
		return true;
	}
}
