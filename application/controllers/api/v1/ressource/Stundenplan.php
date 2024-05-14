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

class Stundenplan extends API_Controller
{
	/**
	 * Stundenplan API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Stundenplan' => 'basis/stundenplan:rw'));
		// Load model StundenplanModel
		$this->load->model('ressource/stundenplan_model', 'StundenplanModel');


	}

	/**
	 * @return void
	 */
	public function getStundenplan()
	{
		$stundenplanID = $this->get('stundenplan_id');

		if (isset($stundenplanID))
		{
			$result = $this->StundenplanModel->load($stundenplanID);

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
	public function postStundenplan()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['stundenplan_id']))
			{
				$result = $this->StundenplanModel->update($this->post()['stundenplan_id'], $this->post());
			}
			else
			{
				$result = $this->StundenplanModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($stundenplan = NULL)
	{
		return true;
	}
}
