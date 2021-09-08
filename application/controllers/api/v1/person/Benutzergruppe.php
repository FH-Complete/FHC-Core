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

class Benutzergruppe extends API_Controller
{
	/**
	 * Benutzergruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Benutzergruppe' => 'basis/benutzergruppe:rw'));
		// Load model BenutzergruppeModel
		$this->load->model('person/benutzergruppe_model', 'BenutzergruppeModel');


	}

	/**
	 * @return void
	 */
	public function getBenutzergruppe()
	{
		$gruppe_kurzbz = $this->get('gruppe_kurzbz');
		$uid = $this->get('uid');

		if (isset($gruppe_kurzbz) && isset($uid))
		{
			$result = $this->BenutzergruppeModel->load(array($gruppe_kurzbz, $uid));

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
	public function postBenutzergruppe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['benutzergruppe_id']))
			{
				$result = $this->BenutzergruppeModel->update($this->post()['benutzergruppe_id'], $this->post());
			}
			else
			{
				$result = $this->BenutzergruppeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($benutzergruppe = NULL)
	{
		return true;
	}
}
