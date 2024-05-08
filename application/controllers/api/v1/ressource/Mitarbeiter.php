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

class Mitarbeiter extends API_Controller
{
	/**
	 * Mitarbeiter API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array(
			'Mitarbeiter' => 'basis/mitarbeiter:rw',
			'Update' => 'basis/mitarbeiter:rw'
			)
		);
		// Load model MitarbeiterModel
		$this->load->model('ressource/mitarbeiter_model', 'MitarbeiterModel');


	}

	/**
	 * @return void
	 */
	public function getMitarbeiter()
	{
		$mitarbeiter_uid = $this->get('mitarbeiter_uid');

		if (isset($mitarbeiter_uid))
		{
			$result = $this->MitarbeiterModel->load($mitarbeiter_uid);

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
	public function postMitarbeiter()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['mitarbeiter_uid']))
			{
				$result = $this->MitarbeiterModel->update($this->post()['mitarbeiter_uid'], $this->post());
			}
			else
			{
				$result = $this->MitarbeiterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	/**
	 * Updates an Employee Record
	 *
	 * @return json Response Object
	 */
	public function postUpdate()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['mitarbeiter_uid']))
			{
				$result_load = $this->MitarbeiterModel->load($this->post()['mitarbeiter_uid']);
				if(isSuccess($result_load) && hasData($result_load))
				{
					$result = $this->MitarbeiterModel->update($this->post()['mitarbeiter_uid'], $this->post());
					$this->response($result, REST_Controller::HTTP_OK);
				}
				else
				{
					$result = error('mitarbeiter_uid not found');
					$this->response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
				}
			}
			else
			{
				$result = error('Parameter mitarbeiter_uid is missing');
				$this->response($result, REST_Controller::HTTP_INTERNAL_SERVER_ERROR);
			}
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($mitarbeiter = NULL)
	{
		return true;
	}
}
