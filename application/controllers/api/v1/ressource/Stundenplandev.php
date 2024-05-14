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

class Stundenplandev extends API_Controller
{
	/**
	 * Stundenplandev API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Stundenplandev' => 'basis/stundenplandev:rw'));
		// Load model StundenplandevModel
		$this->load->model('ressource/stundenplandev_model', 'StundenplandevModel');


	}

	/**
	 * @return void
	 */
	public function getStundenplandev()
	{
		$stundenplandevID = $this->get('stundenplandev_id');

		if (isset($stundenplandevID))
		{
			$result = $this->StundenplandevModel->load($stundenplandevID);

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
	public function postStundenplandev()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['stundenplandev_id']))
			{
				$result = $this->StundenplandevModel->update($this->post()['stundenplandev_id'], $this->post());
			}
			else
			{
				$result = $this->StundenplandevModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($stundenplandev = NULL)
	{
		return true;
	}
}
