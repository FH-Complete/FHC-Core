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

class Ausbildung extends API_Controller
{
	/**
	 * Ausbildung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Ausbildung' => 'basis/ausbildung:rw'));
		// Load model AusbildungModel
		$this->load->model('codex/ausbildung_model', 'AusbildungModel');
	}

	/**
	 * @return void
	 */
	public function getAusbildung()
	{
		$ausbildungcode = $this->get('ausbildungcode');

		if (isset($ausbildungcode))
		{
			$result = $this->AusbildungModel->load($ausbildungcode);

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
	public function postAusbildung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['ausbildungcode']))
			{
				$result = $this->AusbildungModel->update($this->post()['ausbildungcode'], $this->post());
			}
			else
			{
				$result = $this->AusbildungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($ausbildung = NULL)
	{
		return true;
	}
}
