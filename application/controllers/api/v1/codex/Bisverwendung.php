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

class Bisverwendung extends API_Controller
{
	/**
	 * Bisverwendung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bisverwendung' => 'basis/bisverwendung:rw'));
		// Load model BisverwendungModel
		$this->load->model('codex/bisverwendung_model', 'BisverwendungModel');
	}

	/**
	 * @return void
	 */
	public function getBisverwendung()
	{
		$bisverwendungID = $this->get('bisverwendung_id');

		if (isset($bisverwendungID))
		{
			$result = $this->BisverwendungModel->load($bisverwendungID);

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
	public function postBisverwendung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bisverwendung_id']))
			{
				$result = $this->BisverwendungModel->update($this->post()['bisverwendung_id'], $this->post());
			}
			else
			{
				$result = $this->BisverwendungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bisverwendung = NULL)
	{
		return true;
	}
}
