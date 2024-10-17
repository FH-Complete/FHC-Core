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

class Erhalter extends API_Controller
{
	/**
	 * Erhalter API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Erhalter' => 'basis/erhalter:rw'));
		// Load model ErhalterModel
		$this->load->model('organisation/erhalter_model', 'ErhalterModel');


	}

	/**
	 * @return void
	 */
	public function getErhalter()
	{
		$erhalter_kz = $this->get('erhalter_kz');

		if (isset($erhalter_kz))
		{
			$result = $this->ErhalterModel->load($erhalter_kz);

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
	public function postErhalter()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['erhalter_kz']))
			{
				$result = $this->ErhalterModel->update($this->post()['erhalter_kz'], $this->post());
			}
			else
			{
				$result = $this->ErhalterModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($erhalter = NULL)
	{
		return true;
	}
}
