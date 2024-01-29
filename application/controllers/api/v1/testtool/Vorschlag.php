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

class Vorschlag extends API_Controller
{
	/**
	 * Vorschlag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Vorschlag' => 'basis/vorschlag:rw'));
		// Load model VorschlagModel
		$this->load->model('testtool/vorschlag_model', 'VorschlagModel');


	}

	/**
	 * @return void
	 */
	public function getVorschlag()
	{
		$vorschlagID = $this->get('vorschlag_id');

		if (isset($vorschlagID))
		{
			$result = $this->VorschlagModel->load($vorschlagID);

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
	public function postVorschlag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['vorschlag_id']))
			{
				$result = $this->VorschlagModel->update($this->post()['vorschlag_id'], $this->post());
			}
			else
			{
				$result = $this->VorschlagModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($vorschlag = NULL)
	{
		return true;
	}
}
