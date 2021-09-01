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

class Studienplatz extends API_Controller
{
	/**
	 * Studienplatz API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Studienplatz' => 'basis/studienplatz:rw'));
		// Load model StudienplatzModel
		$this->load->model('organisation/studienplatz_model', 'StudienplatzModel');


	}

	/**
	 * @return void
	 */
	public function getStudienplatz()
	{
		$studienplatzID = $this->get('studienplatz_id');

		if (isset($studienplatzID))
		{
			$result = $this->StudienplatzModel->load($studienplatzID);

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
	public function postStudienplatz()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['studienplatz_id']))
			{
				$result = $this->StudienplatzModel->update($this->post()['studienplatz_id'], $this->post());
			}
			else
			{
				$result = $this->StudienplatzModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($studienplatz = NULL)
	{
		return true;
	}
}
