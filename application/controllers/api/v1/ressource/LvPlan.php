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

class LvPlan extends API_Controller
{
	/**
	 * LvPlan API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Stundenplan' => 'basis/stundenplan:rw'));
		// Load model LvPlanModel
		$this->load->model('ressource/stundenplan_model', 'LvPlanModel');


	}

	/**
	 * @return void
	 */
	public function getLvPlan()
	{
		$lvPlanID = $this->get('stundenplan_id');

		if (isset($lvPlanID))
		{
			$result = $this->LvPlanModel->load($lvPlanID);

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
	public function postLvPlan()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['stundenplan_id']))
			{
				$result = $this->LvPlanModel->update($this->post()['stundenplan_id'], $this->post());
			}
			else
			{
				$result = $this->LvPlanModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lvplan = NULL)
	{
		return true;
	}
}
