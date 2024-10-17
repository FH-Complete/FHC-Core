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

class Vertrag extends API_Controller
{
	/**
	 * Vertrag API constructor.
	 */
	public function __construct()
	{
		parent::__construct(array('Vertrag' => 'basis/vertrag:rw'));
		// Load model VertragModel
		$this->load->model('accounting/vertrag_model', 'VertragModel');
	}

	/**
	 * @return void
	 */
	public function getVertrag()
	{
		$vertragID = $this->get('vertrag_id');

		if (isset($vertragID))
		{
			$result = $this->VertragModel->load($vertragID);

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
	public function postVertrag()
	{
		if ($this->_validate($this->post()))
		{
			if (isset($this->post()['vertrag_id']))
			{
				$result = $this->VertragModel->update($this->post()['vertrag_id'], $this->post());
			}
			else
			{
				$result = $this->VertragModel->insert($this->post());
			}

			$this->response($result, REST_Controller::HTTP_OK);
		}
		else
		{
			$this->response();
		}
	}

	private function _validate($vertrag = NULL)
	{
		return true;
	}
}
