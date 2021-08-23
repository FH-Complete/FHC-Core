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

class Lepruefung extends API_Controller
{
	/**
	 * LePruefung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('LePruefung' => 'basis/lepruefung:rw'));
		// Load model LePruefungModel
		$this->load->model('education/LePruefung_model', 'LePruefungModel');
	}

	/**
	 * @return void
	 */
	public function getLePruefung()
	{
		$lepruefung_id = $this->get('lepruefung_id');

		if (isset($lepruefung_id))
		{
			$result = $this->LePruefungModel->load($lepruefung_id);

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
	public function postLePruefung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['lepruefung_id']))
			{
				$result = $this->LePruefungModel->update($this->post()['lepruefung_id'], $this->post());
			}
			else
			{
				$result = $this->LePruefungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lepruefung = NULL)
	{
		return true;
	}
}
