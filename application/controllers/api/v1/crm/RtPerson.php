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

if (!defined("BASEPATH")) exit("No direct script access allowed");

class RtPerson extends API_Controller
{
	/**
	 * Status API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('RtPerson' => 'basis/rtperson:rw'));
		// Load model StatusModel
		$this->load->model('crm/RtPerson_model', 'RtPersonModel');
	}

	/**
	 * @return void
	 */
	public function getRtPerson()
	{
		$rt_person_id = $this->get("rt_person_id");

		if (isset($rt_person_id))
		{
			$result = $this->RtPersonModel->load($rt_person_id);

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
	public function postRtPerson()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()["rt_person_id"]))
			{
				$result = $this->RtPersonModel->update($this->post()["rt_person_id"], $this->post());
			}
			else
			{
				$result = $this->RtPersonModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($rtPerson = NULL)
	{
		return true;
	}
}
