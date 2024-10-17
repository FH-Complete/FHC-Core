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

class Erreichbarkeit extends API_Controller
{
	/**
	 * Erreichbarkeit API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Erreichbarkeit' => 'basis/erreichbarkeit:rw'));
		// Load model ErreichbarkeitModel
		$this->load->model('ressource/erreichbarkeit_model', 'ErreichbarkeitModel');


	}

	/**
	 * @return void
	 */
	public function getErreichbarkeit()
	{
		$erreichbarkeit_kurzbz = $this->get('erreichbarkeit_kurzbz');

		if (isset($erreichbarkeit_kurzbz))
		{
			$result = $this->ErreichbarkeitModel->load($erreichbarkeit_kurzbz);

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
	public function postErreichbarkeit()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['erreichbarkeit_kurzbz']))
			{
				$result = $this->ErreichbarkeitModel->update($this->post()['erreichbarkeit_kurzbz'], $this->post());
			}
			else
			{
				$result = $this->ErreichbarkeitModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($erreichbarkeit = NULL)
	{
		return true;
	}
}
