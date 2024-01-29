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

class Log extends API_Controller
{
	/**
	 * Log API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Log' => 'basis/log:rw'));
		// Load model LogModel
		$this->load->model('system/log_model', 'LogModel');


	}

	/**
	 * @return void
	 */
	public function getLog()
	{
		$logID = $this->get('log_id');

		if (isset($logID))
		{
			$result = $this->LogModel->load($logID);

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
	public function postLog()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['log_id']))
			{
				$result = $this->LogModel->update($this->post()['log_id'], $this->post());
			}
			else
			{
				$result = $this->LogModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($log = NULL)
	{
		return true;
	}
}
