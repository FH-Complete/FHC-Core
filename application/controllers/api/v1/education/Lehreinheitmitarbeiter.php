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

class Lehreinheitmitarbeiter extends API_Controller
{
	/**
	 * Lehreinheitmitarbeiter API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Lehreinheitmitarbeiter' => 'basis/lehreinheitmitarbeiter:rw'));
		// Load model LehreinheitmitarbeiterModel
		$this->load->model('education/Lehreinheitmitarbeiter_model', 'LehreinheitmitarbeiterModel');
	}

	/**
	 * @return void
	 */
	public function getLehreinheitmitarbeiter()
	{
		$mitarbeiter_uid = $this->get('mitarbeiter_uid');
		$lehreinheit_id = $this->get('lehreinheit_id');

		if (isset($mitarbeiter_uid) && isset($lehreinheit_id))
		{
			$result = $this->LehreinheitmitarbeiterModel->load(array($mitarbeiter_uid, $lehreinheit_id));

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
	public function postLehreinheitmitarbeiter()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['mitarbeiter_uid']) && isset($this->post()['lehreinheit_id']))
			{
				$result = $this->LehreinheitmitarbeiterModel->update(array($this->post()['mitarbeiter_uid'], $this->post()['lehreinheit_id']), $this->post());
			}
			else
			{
				$result = $this->LehreinheitmitarbeiterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lehreinheitmitarbeiter = NULL)
	{
		return true;
	}
}
