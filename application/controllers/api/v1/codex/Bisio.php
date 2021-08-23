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

class Bisio extends API_Controller
{
	/**
	 * Bisio API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Bisio' => 'basis/bisio:rw'));
		// Load model BisioModel
		$this->load->model('codex/bisio_model', 'BisioModel');
	}

	/**
	 * @return void
	 */
	public function getBisio()
	{
		$bisioID = $this->get('bisio_id');

		if (isset($bisioID))
		{
			$result = $this->BisioModel->load($bisioID);

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
	public function postBisio()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['bisio_id']))
			{
				$result = $this->BisioModel->update($this->post()['bisio_id'], $this->post());
			}
			else
			{
				$result = $this->BisioModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($bisio = NULL)
	{
		return true;
	}
}
