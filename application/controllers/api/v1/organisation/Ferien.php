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

class Ferien extends API_Controller
{
	/**
	 * Ferien API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Ferien' => 'basis/ferien:rw'));
		// Load model FerienModel
		$this->load->model('organisation/ferien_model', 'FerienModel');


	}

	/**
	 * @return void
	 */
	public function getFerien()
	{
		$studiengang_kz = $this->get('studiengang_kz');
		$bezeichnung = $this->get('bezeichnung');

		if (isset($studiengang_kz) && isset($bezeichnung))
		{
			$result = $this->FerienModel->load(array($studiengang_kz, $bezeichnung));

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
	public function postFerien()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studiengang_kz']) && isset($this->post()['bezeichnung']))
			{
				$result = $this->FerienModel->update(array($this->post()['ferien_id'], $this->post()['bezeichnung']), $this->post());
			}
			else
			{
				$result = $this->FerienModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($ferien = NULL)
	{
		return true;
	}
}
