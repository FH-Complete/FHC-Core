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

class Cronjob extends API_Controller
{
	/**
	 * Cronjob API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Cronjob' => 'basis/cronjob:rw'));
		// Load model CronjobModel
		$this->load->model('system/cronjob_model', 'CronjobModel');


	}

	/**
	 * @return void
	 */
	public function getCronjob()
	{
		$cronjobID = $this->get('cronjob_id');

		if (isset($cronjobID))
		{
			$result = $this->CronjobModel->load($cronjobID);

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
	public function postCronjob()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['cronjob_id']))
			{
				$result = $this->CronjobModel->update($this->post()['cronjob_id'], $this->post());
			}
			else
			{
				$result = $this->CronjobModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($cronjob = NULL)
	{
		return true;
	}
}
