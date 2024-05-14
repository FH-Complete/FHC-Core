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

class Berufstaetigkeit extends API_Controller
{
	/**
	 * Berufstaetigkeit API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Berufstaetigkeit' => 'basis/berufstaetigkeit:rw'));
		// Load model BerufstaetigkeitModel
		$this->load->model('codex/berufstaetigkeit_model', 'BerufstaetigkeitModel');
	}

	/**
	 * @return void
	 */
	public function getBerufstaetigkeit()
	{
		$berufstaetigkeit_code = $this->get('berufstaetigkeit_code');

		if (isset($berufstaetigkeit_code))
		{
			$result = $this->BerufstaetigkeitModel->load($berufstaetigkeit_code);

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
	public function postBerufstaetigkeit()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['berufstaetigkeit_code']))
			{
				$result = $this->BerufstaetigkeitModel->update($this->post()['berufstaetigkeit_code'], $this->post());
			}
			else
			{
				$result = $this->BerufstaetigkeitModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($berufstaetigkeit = NULL)
	{
		return true;
	}
}
