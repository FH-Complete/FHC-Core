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

class Rolleberechtigung extends API_Controller
{
	/**
	 * Rolleberechtigung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Rolleberechtigung' => 'basis/rolleberechtigung:rw'));
		// Load model RolleberechtigungModel
		$this->load->model('system/rolleberechtigung_model', 'RolleberechtigungModel');


	}

	/**
	 * @return void
	 */
	public function getRolleberechtigung()
	{
		$rolle_kurzbz = $this->get('rolle_kurzbz');
		$berechtigung_kurzbz = $this->get('berechtigung_kurzbz');

		if (isset($rolle_kurzbz) && isset($berechtigung_kurzbz))
		{
			$result = $this->RolleberechtigungModel->load(array($rolle_kurzbz, $berechtigung_kurzbz));

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
	public function postRolleberechtigung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['rolle_kurzbz']) && isset($this->post()['berechtigung_kurzbz']))
			{
				$result = $this->RolleberechtigungModel->update(array($this->post()['rolle_kurzbz'], $this->post()['berechtigung_kurzbz']), $this->post());
			}
			else
			{
				$result = $this->RolleberechtigungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($rolleberechtigung = NULL)
	{
		return true;
	}
}
