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

class Zgvgruppe extends API_Controller
{
	/**
	 * Zgvgruppe API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Zgvgruppe' => 'basis/zgvgruppe:rw'));
		// Load model ZgvgruppeModel
		$this->load->model('codex/zgvgruppe_model', 'ZgvgruppeModel');
	}

	/**
	 * @return void
	 */
	public function getZgvgruppe()
	{
		$gruppe_kurzbz = $this->get('gruppe_kurzbz');

		if (isset($gruppe_kurzbz))
		{
			$result = $this->ZgvgruppeModel->load($gruppe_kurzbz);

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
	public function postZgvgruppe()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['zgvgruppe_id']))
			{
				$result = $this->ZgvgruppeModel->update($this->post()['zgvgruppe_id'], $this->post());
			}
			else
			{
				$result = $this->ZgvgruppeModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($zgvgruppe = NULL)
	{
		return true;
	}
}
