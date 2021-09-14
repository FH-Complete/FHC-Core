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

class Veranstaltung extends API_Controller
{
	/**
	 * Veranstaltung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Veranstaltung' => 'basis/veranstaltung:rw'));
		// Load model VeranstaltungModel
		$this->load->model('content/veranstaltung_model', 'VeranstaltungModel');
	}

	/**
	 * @return void
	 */
	public function getVeranstaltung()
	{
		$veranstaltungID = $this->get('veranstaltung_id');

		if (isset($veranstaltungID))
		{
			$result = $this->VeranstaltungModel->load($veranstaltungID);

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
	public function postVeranstaltung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['veranstaltung_id']))
			{
				$result = $this->VeranstaltungModel->update($this->post()['veranstaltung_id'], $this->post());
			}
			else
			{
				$result = $this->VeranstaltungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($veranstaltung = NULL)
	{
		return true;
	}
}
