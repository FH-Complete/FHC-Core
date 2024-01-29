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

class Verwendung extends API_Controller
{
	/**
	 * Verwendung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Verwendung' => 'basis/verwendung:rw'));
		// Load model VerwendungModel
		$this->load->model('codex/verwendung_model', 'VerwendungModel');
	}

	/**
	 * @return void
	 */
	public function getVerwendung()
	{
		$verwendung_code = $this->get('verwendung_code');

		if (isset($verwendung_code))
		{
			$result = $this->VerwendungModel->load($verwendung_code);

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
	public function postVerwendung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['verwendung_code']))
			{
				$result = $this->VerwendungModel->update($this->post()['verwendung_code'], $this->post());
			}
			else
			{
				$result = $this->VerwendungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($verwendung = NULL)
	{
		return true;
	}
}
