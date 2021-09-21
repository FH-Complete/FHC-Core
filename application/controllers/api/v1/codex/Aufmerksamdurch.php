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

class Aufmerksamdurch extends API_Controller
{
	/**
	 * Aufmerksamdurch API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Aufmerksamdurch' => 'basis/aufmerksamdurch:rw'));
		// Load model AufmerksamdurchModel
		$this->load->model('codex/aufmerksamdurch_model', 'AufmerksamdurchModel');
	}

	/**
	 * @return void
	 */
	public function getAufmerksamdurch()
	{
		$aufmerksamdurch_kurzbz = $this->get('aufmerksamdurch_kurzbz');

		if (isset($aufmerksamdurch_kurzbz))
		{
			$result = $this->AufmerksamdurchModel->load($aufmerksamdurch_kurzbz);

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
	public function postAufmerksamdurch()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['aufmerksamdurch_kurzbz']))
			{
				$result = $this->AufmerksamdurchModel->update($this->post()['aufmerksamdurch_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->AufmerksamdurchModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($aufmerksamdurch = NULL)
	{
		return true;
	}
}
