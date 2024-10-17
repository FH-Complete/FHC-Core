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

class Fachbereich2 extends API_Controller
{
	/**
	 * Fachbereich API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Fachbereich' => 'basis/fachbereich:rw'));
		// Load model FachbereichModel
		$this->load->model('organisation/fachbereich_model', 'FachbereichModel');


	}

	/**
	 * @return void
	 */
	public function getFachbereich()
	{
		$fachbereich_kurzbz = $this->get('fachbereich_kurzbz');

		if (isset($fachbereich_kurzbz))
		{
			$result = $this->FachbereichModel->load($fachbereich_kurzbz);

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
	public function postFachbereich()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['fachbereich_id']))
			{
				$result = $this->FachbereichModel->update($this->post()['fachbereich_id'], $this->post());
			}
			else
			{
				$result = $this->FachbereichModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($fachbereich = NULL)
	{
		return true;
	}
}
