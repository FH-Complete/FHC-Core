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

class LvPlandev extends API_Controller
{
	/**
	 * LvPlandev API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Stundenplandev' => 'basis/stundenplandev:rw'));
		// Load model LvPlandevModel
		$this->load->model('ressource/stundenplandev_model', 'LvPlandevModel');


	}

	/**
	 * @return void
	 */
	public function getLvPlandev()
	{
		$lvplandevID = $this->get('stundenplandev_id');

		if (isset($lvplandevID))
		{
			$result = $this->LvPlandevModel->load($lvplandevID);

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
	public function postLvPlandev()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['stundenplandev_id']))
			{
				$result = $this->LvPlandevModel->update($this->post()['stundenplandev_id'], $this->post());
			}
			else
			{
				$result = $this->LvPlandevModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($lvplandev = NULL)
	{
		return true;
	}
}
