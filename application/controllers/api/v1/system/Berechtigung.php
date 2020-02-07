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

class Berechtigung extends API_Controller
{
	/**
	 * Berechtigung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Berechtigung' => 'basis/berechtigung:rw'));
		// Load model BerechtigungModel
		$this->load->model('system/berechtigung_model', 'BerechtigungModel');


	}

	/**
	 * @return void
	 */
	public function getBerechtigung()
	{
		$berechtigung_kurzbz = $this->get('berechtigung_kurzbz');

		if (isset($berechtigung_kurzbz))
		{
			$result = $this->BerechtigungModel->load($berechtigung_kurzbz);

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
	public function postBerechtigung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['berechtigung_kurzbz']))
			{
				$result = $this->BerechtigungModel->update($this->post()['berechtigung_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->BerechtigungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($berechtigung = NULL)
	{
		return true;
	}
}
