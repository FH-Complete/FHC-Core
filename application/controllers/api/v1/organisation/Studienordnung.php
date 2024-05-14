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

class Studienordnung extends API_Controller
{
	/**
	 * Studienordnung API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studienordnung' => 'lehre/studienordnung:rw'));
		// Load model StudienordnungModel
		$this->load->model('organisation/studienordnung_model', 'StudienordnungModel');


	}

	/**
	 * @return void
	 */
	public function getStudienordnung()
	{
		$studienordnungID = $this->get('studienordnung_id');

		if (isset($studienordnungID))
		{
			$result = $this->StudienordnungModel->load($studienordnungID);

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
	public function postStudienordnung()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studienordnung_id']))
			{
				$result = $this->StudienordnungModel->update($this->post()['studienordnung_id'], $this->post());
			}
			else
			{
				$result = $this->StudienordnungModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studienordnung = NULL)
	{
		return true;
	}
}
