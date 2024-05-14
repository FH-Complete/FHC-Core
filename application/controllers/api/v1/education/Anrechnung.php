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

class Anrechnung extends API_Controller
{
	/**
	 * Anrechnung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Anrechnung' => 'basis/anrechnung:rw'));
		// Load model AnrechnungModel
		$this->load->model('education/Anrechnung_model', 'AnrechnungModel');
	}

	/**
	 * @return void
	 */
	public function getAnrechnung()
	{
		$anrechnung_id = $this->get('anrechnung_id');

		if (isset($anrechnung_id))
		{
			$result = $this->AnrechnungModel->load($anrechnung_id);

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
	public function postAnrechnung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['anrechnung_id']))
			{
				$result = $this->AnrechnungModel->update($this->post()['anrechnung_id'], $this->post());
			}
			else
			{
				$result = $this->AnrechnungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($anrechnung = NULL)
	{
		return true;
	}
}
