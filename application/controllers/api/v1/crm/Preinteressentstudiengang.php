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

class Preinteressentstudiengang extends API_Controller
{
	/**
	 * Preinteressentstudiengang API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Preinteressentstudiengang' => 'basis/preinteressentstudiengang:rw'));
		// Load model PreinteressentstudiengangModel
		$this->load->model('crm/preinteressentstudiengang_model', 'PreinteressentstudiengangModel');


	}

	/**
	 * @return void
	 */
	public function getPreinteressentstudiengang()
	{
		$preinteressent_id = $this->get('preinteressent_id');
		$studiengang_kz = $this->get('studiengang_kz');

		if (isset($preinteressent_id) && isset($studiengang_kz))
		{
			$result = $this->PreinteressentstudiengangModel->load(array($preinteressent_id, $studiengang_kz));

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
	public function postPreinteressentstudiengang()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['preinteressent_id']) && isset($this->post()['studiengang_kz']))
			{
				$result = $this->PreinteressentstudiengangModel->update(array($this->post()['preinteressent_id'], $this->post()['studiengang_kz']), $this->post());
			}
			else
			{
				$result = $this->PreinteressentstudiengangModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($preinteressentstudiengang = NULL)
	{
		return true;
	}
}
