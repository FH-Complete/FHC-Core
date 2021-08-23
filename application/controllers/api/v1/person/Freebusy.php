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

class Freebusy extends API_Controller
{
	/**
	 * Freebusy API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Freebusy' => 'basis/freebusy:rw'));
		// Load model FreebusyModel
		$this->load->model('person/freebusy_model', 'FreebusyModel');


	}

	/**
	 * @return void
	 */
	public function getFreebusy()
	{
		$freebusyID = $this->get('freebusy_id');

		if (isset($freebusyID))
		{
			$result = $this->FreebusyModel->load($freebusyID);

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
	public function postFreebusy()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['freebusy_id']))
			{
				$result = $this->FreebusyModel->update($this->post()['freebusy_id'], $this->post());
			}
			else
			{
				$result = $this->FreebusyModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($freebusy = NULL)
	{
		return true;
	}
}
