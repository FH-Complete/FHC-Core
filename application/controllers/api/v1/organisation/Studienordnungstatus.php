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

class Studienordnungstatus extends API_Controller
{
	/**
	 * Studienordnungstatus API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studienordnungstatus' => 'lehre/studienordnungstatus:rw'));
		// Load model StudienordnungstatusModel
		$this->load->model('organisation/studienordnungstatus_model', 'StudienordnungstatusModel');


	}

	/**
	 * @return void
	 */
	public function getStudienordnungstatus()
	{
		$status_kurzbz = $this->get('status_kurzbz');

		if (isset($status_kurzbz))
		{
			$result = $this->StudienordnungstatusModel->load($status_kurzbz);

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
	public function postStudienordnungstatus()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['status_kurzbz']))
			{
				$result = $this->StudienordnungstatusModel->update($this->post()['status_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->StudienordnungstatusModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studienordnungstatus = NULL)
	{
		return true;
	}
}
